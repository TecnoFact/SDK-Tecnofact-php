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
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    /**
     * Realizar petición POST
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    /**
     * Realizar petición PUT
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    /**
     * Realizar petición DELETE
     */
    public function delete(string $endpoint, array $data = []): array
    {
        return $this->request('DELETE', $endpoint, ['json' => $data]);
    }

    /**
     * Realizar petición HTTP
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws TecnoFactException
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        $url = $this->config->getBaseUrl() . $endpoint;

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
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-API-Key' => $this->config->getApiKey(),
                'X-API-Secret' => $this->config->getApiSecret(),
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

        return $data;
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

        if (!$response) {
            throw new TecnoFactException(
                'Error de conexión: ' . $e->getMessage(),
                0,
                $e,
                $requestId
            );
        }

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $data = json_decode($body, true) ?? [];

        match ($statusCode) {
            400 => throw new ValidationException(
                $data['message'] ?? 'Error de validación',
                $data['errors'] ?? [],
                $requestId
            ),
            401 => throw new AuthenticationException(
                $data['message'] ?? 'Error de autenticación',
                $requestId
            ),
            404 => throw new NotFoundException(
                $data['message'] ?? 'Recurso no encontrado',
                $requestId
            ),
            422 => throw new ValidationException(
                $data['message'] ?? 'Error de validación',
                $data['errors'] ?? [],
                $requestId
            ),
            429 => throw new RateLimitException(
                $data['message'] ?? 'Límite de peticiones excedido',
                $data['retry_after'] ?? 60,
                $requestId
            ),
            default => throw new ServerException(
                $data['message'] ?? 'Error interno del servidor',
                $statusCode,
                $requestId
            ),
        };
    }
}
