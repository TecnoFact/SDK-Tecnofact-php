<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;
use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Contracts\HttpClientInterface;
use TecnoFact\Sdk\Exceptions\AuthenticationException;
use TecnoFact\Sdk\Exceptions\NotFoundException;
use TecnoFact\Sdk\Exceptions\RateLimitException;
use TecnoFact\Sdk\Exceptions\ServerException;
use TecnoFact\Sdk\Exceptions\TecnoFactException;
use TecnoFact\Sdk\Exceptions\ValidationException;

/**
 * Cliente HTTP para comunicación con la API de TecnoFact
 */
final class HttpClient implements HttpClientInterface
{
    private Client $client;
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->client = $this->createClient();
    }

    /**
     * Realizar petición GET
     *
     * @param array<string, string> $headers
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public function get(string $endpoint, array $headers = [], array $query = []): array
    {
        return $this->request('GET', $endpoint, $headers, ['query' => $query]);
    }

    /**
     * Realizar petición POST
     *
     * @param array<string, string> $headers
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function post(string $endpoint, array $headers = [], array $data = []): array
    {
        return $this->request('POST', $endpoint, $headers, ['json' => $data]);
    }

    /**
     * Realizar petición PUT
     *
     * @param array<string, string> $headers
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function put(string $endpoint, array $headers = [], array $data = []): array
    {
        return $this->request('PUT', $endpoint, $headers, ['json' => $data]);
    }

    /**
     * Realizar petición DELETE
     *
     * @param array<string, string> $headers
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function delete(string $endpoint, array $headers = [], array $data = []): array
    {
        return $this->request('DELETE', $endpoint, $headers, ['json' => $data]);
    }

    /**
     * Realizar petición HTTP
     *
     * @param array<string, string> $headers
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws TecnoFactException
     */
    private function request(string $method, string $endpoint, array $headers = [], array $options = []): array
    {
        // Los services ya construyen la URL absoluta con Config::getBaseUrl(),
        // por eso el endpoint se usa tal cual (evita duplicar la base URL).
        $url = $endpoint;

        $existingHeaders = $this->client->getConfig('headers');
        $options['headers'] = array_merge(
            is_array($existingHeaders) ? $existingHeaders : [],
            $headers
        );

        try {
            $response = $this->client->request($method, $url, $options);

            return $this->parseResponse($response);
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }
    }

    /**
     * Crear cliente Guzzle con configuración
     */
    private function createClient(): Client
    {
        $stack = HandlerStack::create();

        $stack->push(Middleware::retry(
            function (int $retries, $request, $response, ?\Throwable $exception): bool {
                if ($retries >= $this->config->getRetries()) {
                    return false;
                }

                if ($exception instanceof RequestException && $exception->getResponse()) {
                    $statusCode = $exception->getResponse()->getStatusCode();

                    return $statusCode >= 500 || $statusCode === 429;
                }

                return false;
            },
            function (int $retries): int {
                return (int) (1000 * pow(2, $retries));
            }
        ));

        return new Client([
            'handler' => $stack,
            'timeout' => $this->config->getTimeout(),
            'verify' => $this->config->getVerifySsl(),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Parsear respuesta HTTP
     *
     * @return array<string, mixed>
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        if (empty($body)) {
            return [];
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TecnoFactException('Error al decodificar respuesta JSON: ' . json_last_error_msg());
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Manejar excepciones de petición
     *
     * @throws TecnoFactException
     */
    private function handleRequestException(RequestException $e): never
    {
        $response = $e->getResponse();
        $requestId = $e->getRequest()->getHeaderLine('X-Request-ID');

        if (! $response) {
            throw new TecnoFactException(
                'Error de conexión: ' . $e->getMessage(),
                0,
                $e,
                $requestId
            );
        }

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $rawData = json_decode($body, true);
        $data = is_array($rawData) ? $rawData : [];

        // El panel de TecnoFact responde los errores como {"success":false,"error":"..."},
        // mientras que otros endpoints usan {"message":"..."}. Se contemplan ambos formatos
        // para no perder el detalle real del error (antes caía en "Error desconocido").
        $message = $this->extractErrorMessage($data);
        $errors = is_array($data['errors'] ?? null) ? $data['errors'] : [];
        $retryAfter = is_numeric($data['retry_after'] ?? null) ? (int) $data['retry_after'] : 60;

        match ($statusCode) {
            400 => throw new ValidationException(
                $message,
                $errors,
                $requestId
            ),
            401 => throw new AuthenticationException(
                $message,
                $requestId
            ),
            404 => throw new NotFoundException(
                $message,
                $requestId
            ),
            422 => throw new ValidationException(
                $message,
                $errors,
                $requestId
            ),
            429 => throw new RateLimitException(
                $message,
                $retryAfter,
                $requestId
            ),
            default => throw new ServerException(
                $message,
                $statusCode,
                $requestId
            ),
        };
    }

    /**
     * Extrae el mensaje de error del cuerpo de la respuesta contemplando los
     * distintos formatos que devuelve la API de TecnoFact.
     *
     * @param array<string, mixed> $data
     */
    private function extractErrorMessage(array $data): string
    {
        foreach (['message', 'error', 'mensaje'] as $key) {
            if (isset($data[$key]) && is_string($data[$key]) && $data[$key] !== '') {
                return $data[$key];
            }
        }

        return 'Error desconocido';
    }
}
