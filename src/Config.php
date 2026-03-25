<?php

declare(strict_types=1);

namespace TecnoFact\Sdk;

use InvalidArgumentException;

/**
 * Configuración del SDK de TecnoFact
 */
class Config
{
    public const ENVIRONMENT_SANDBOX = 'sandbox';
    public const ENVIRONMENT_PRODUCTION = 'production';

    private const API_URL_SANDBOX = 'https://api-sandbox.tecnofact.com/v1';
    private const API_URL_PRODUCTION = 'https://api.tecnofact.com/v1';

    private string $apiKey;
    private string $apiSecret;
    private string $environment;
    private string $baseUrl;
    private int $timeout;
    private int $retries;

    /**
     * Constructor
     *
     * @param string $apiKey API Key proporcionada por TecnoFact
     * @param string $apiSecret API Secret proporcionado por TecnoFact
     * @param string $environment Entorno (sandbox o production)
     * @param int $timeout Tiempo de espera en segundos (default: 30)
     * @param int $retries Número de reintentos en caso de error (default: 3)
     * @throws InvalidArgumentException Si los parámetros son inválidos
     */
    public function __construct(
        string $apiKey,
        string $apiSecret,
        string $environment = self::ENVIRONMENT_SANDBOX,
        int $timeout = 30,
        int $retries = 3
    ) {
        $this->validateApiKey($apiKey);
        $this->validateApiSecret($apiSecret);
        $this->validateEnvironment($environment);
        $this->validateTimeout($timeout);
        $this->validateRetries($retries);

        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->environment = $environment;
        $this->baseUrl = $this->resolveBaseUrl($environment);
        $this->timeout = $timeout;
        $this->retries = $retries;
    }

    /**
     * Crear configuración desde variables de entorno
     *
     * Variables requeridas:
     * - TECN_FACT_API_KEY
     * - TECN_FACT_API_SECRET
     * - TECN_FACT_ENVIRONMENT (opcional, default: sandbox)
     * - TECN_FACT_TIMEOUT (opcional, default: 30)
     * - TECN_FACT_RETRIES (opcional, default: 3)
     *
     * @return static
     * @throws InvalidArgumentException Si faltan variables requeridas
     */
    public static function fromEnvironment(): self
    {
        $apiKey = $_ENV['TECN_FACT_API_KEY'] ?? $_SERVER['TECN_FACT_API_KEY'] ?? null;
        $apiSecret = $_ENV['TECN_FACT_API_SECRET'] ?? $_SERVER['TECN_FACT_API_SECRET'] ?? null;
        $environment = $_ENV['TECN_FACT_ENVIRONMENT'] ?? $_SERVER['TECN_FACT_ENVIRONMENT'] ?? self::ENVIRONMENT_SANDBOX;
        $timeout = (int) ($_ENV['TECN_FACT_TIMEOUT'] ?? $_SERVER['TECN_FACT_TIMEOUT'] ?? 30);
        $retries = (int) ($_ENV['TECN_FACT_RETRIES'] ?? $_SERVER['TECN_FACT_RETRIES'] ?? 3);

        if (empty($apiKey)) {
            throw new InvalidArgumentException('Variable de entorno TECN_FACT_API_KEY es requerida');
        }

        if (empty($apiSecret)) {
            throw new InvalidArgumentException('Variable de entorno TECN_FACT_API_SECRET es requerida');
        }

        return new self($apiKey, $apiSecret, $environment, $timeout, $retries);
    }

    /**
     * Obtener API Key
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Obtener API Secret
     */
    public function getApiSecret(): string
    {
        return $this->apiSecret;
    }

    /**
     * Obtener entorno
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Verificar si es entorno sandbox
     */
    public function isSandbox(): bool
    {
        return $this->environment === self::ENVIRONMENT_SANDBOX;
    }

    /**
     * Verificar si es entorno production
     */
    public function isProduction(): bool
    {
        return $this->environment === self::ENVIRONMENT_PRODUCTION;
    }

    /**
     * Obtener URL base de la API
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Obtener tiempo de espera
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Obtener número de reintentos
     */
    public function getRetries(): int
    {
        return $this->retries;
    }

    /**
     * Resolver URL base según el entorno
     */
    private function resolveBaseUrl(string $environment): string
    {
        return match ($environment) {
            self::ENVIRONMENT_SANDBOX => self::API_URL_SANDBOX,
            self::ENVIRONMENT_PRODUCTION => self::API_URL_PRODUCTION,
            default => throw new InvalidArgumentException("Entorno no soportado: {$environment}")
        };
    }

    /**
     * Validar API Key
     */
    private function validateApiKey(string $apiKey): void
    {
        if (empty($apiKey)) {
            throw new InvalidArgumentException('API Key no puede estar vacío');
        }

        if (strlen($apiKey) < 10) {
            throw new InvalidArgumentException('API Key debe tener al menos 10 caracteres');
        }
    }

    /**
     * Validar API Secret
     */
    private function validateApiSecret(string $apiSecret): void
    {
        if (empty($apiSecret)) {
            throw new InvalidArgumentException('API Secret no puede estar vacío');
        }

        if (strlen($apiSecret) < 20) {
            throw new InvalidArgumentException('API Secret debe tener al menos 20 caracteres');
        }
    }

    /**
     * Validar entorno
     */
    private function validateEnvironment(string $environment): void
    {
        $validEnvironments = [self::ENVIRONMENT_SANDBOX, self::ENVIRONMENT_PRODUCTION];

        if (!in_array($environment, $validEnvironments, true)) {
            throw new InvalidArgumentException(
                sprintf('Entorno inválido: %s. Use: %s', $environment, implode(', ', $validEnvironments))
            );
        }
    }

    /**
     * Validar timeout
     */
    private function validateTimeout(int $timeout): void
    {
        if ($timeout < 1 || $timeout > 300) {
            throw new InvalidArgumentException('Timeout debe estar entre 1 y 300 segundos');
        }
    }

    /**
     * Validar reintentos
     */
    private function validateRetries(int $retries): void
    {
        if ($retries < 0 || $retries > 10) {
            throw new InvalidArgumentException('Reintentos debe estar entre 0 y 10');
        }
    }

    /**
     * Convertir a array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'environment' => $this->environment,
            'baseUrl' => $this->baseUrl,
            'timeout' => $this->timeout,
            'retries' => $this->retries,
        ];
    }
}