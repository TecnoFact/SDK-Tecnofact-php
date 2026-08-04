<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Config;

use InvalidArgumentException;
use TecnoFact\Sdk\Enums\Environment;

/**
 * Configuración del SDK de TecnoFact
 */
final class Config
{
    private const API_URL_PRODUCTION = 'https://panelcfdi.tecnofact.mx';

    // Sandbox no está disponible por ahora. Cuando exista, reactivar aquí.
    // private const API_URL_SANDBOX = '';

    private string $baseUrl;
    private ?string $token = null;

    /**
     * Constructor
     *
     * @param string $email Correo electrónico de la cuenta TecnoFact
     * @param string $password Contraseña de la cuenta TecnoFact
     * @param Environment $environment Entorno (solo production disponible por ahora)
     * @param int $timeout Tiempo de espera en segundos (default: 30)
     * @param int $retries Número de reintentos en caso de error (default: 3)
     * @throws InvalidArgumentException Si los parámetros son inválidos
     */
    /**
     * @param bool|string $verifySsl Verificación TLS del servidor:
     *   - true (default, seguro): verifica con el bundle de CA del sistema.
     *   - string: ruta a un bundle de CA propio (PEM) que incluya, por ejemplo,
     *     el certificado intermedio faltante del servidor.
     *   - false (INSEGURO): desactiva la verificación TLS. Usar solo en desarrollo.
     */
    public function __construct(
        private string $email,
        private string $password,
        private Environment $environment = Environment::PRODUCTION,
        private int $timeout = 30,
        private int $retries = 3,
        private bool|string $verifySsl = true
    ) {
        $this->validateEmail($email);
        $this->validatePassword($password);
        $this->validateTimeout($timeout);
        $this->validateRetries($retries);
        $this->validateVerifySsl($verifySsl);

        $this->baseUrl = $this->resolveBaseUrl($environment);
    }

    /**
     * Crear configuración desde variables de entorno
     *
     * Variables requeridas:
     * - TECN_FACT_EMAIL
     * - TECN_FACT_PASSWORD
     * - TECN_FACT_ENVIRONMENT (opcional, default: production)
     * - TECN_FACT_TIMEOUT (opcional, default: 30)
     * - TECN_FACT_RETRIES (opcional, default: 3)
     * - TECN_FACT_VERIFY_SSL (opcional, default: true). Acepta "true"/"false" o
     *   la ruta a un bundle de CA propio.
     *
     * @return static
     * @throws InvalidArgumentException Si faltan variables requeridas
     */
    public static function fromEnvironment(): self
    {
        $email = $_ENV['TECN_FACT_EMAIL'] ?? $_SERVER['TECN_FACT_EMAIL'] ?? null;
        $password = $_ENV['TECN_FACT_PASSWORD'] ?? $_SERVER['TECN_FACT_PASSWORD'] ?? null;
        $environment = $_ENV['TECN_FACT_ENVIRONMENT'] ?? $_SERVER['TECN_FACT_ENVIRONMENT'] ?? 'production';
        $timeout = (int) ($_ENV['TECN_FACT_TIMEOUT'] ?? $_SERVER['TECN_FACT_TIMEOUT'] ?? 30);
        $retries = (int) ($_ENV['TECN_FACT_RETRIES'] ?? $_SERVER['TECN_FACT_RETRIES'] ?? 3);
        $verifyRaw = $_ENV['TECN_FACT_VERIFY_SSL'] ?? $_SERVER['TECN_FACT_VERIFY_SSL'] ?? null;

        if (empty($email)) {
            throw new InvalidArgumentException('Variable de entorno TECN_FACT_EMAIL es requerida');
        }

        if (empty($password)) {
            throw new InvalidArgumentException('Variable de entorno TECN_FACT_PASSWORD es requerida');
        }

        return new self(
            $email,
            $password,
            Environment::from($environment),
            $timeout,
            $retries,
            self::parseVerifySsl($verifyRaw)
        );
    }

    /**
     * Normaliza el valor de TECN_FACT_VERIFY_SSL a bool|string.
     *
     */
    private static function parseVerifySsl($value): bool|string
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (! is_string($value)) {
            return (bool) $value;
        }

        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'true', '1' => true,
            'false', '0' => false,
            default => $value, // se interpreta como ruta a un bundle de CA
        };
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /**
     * Verificar si es entorno production
     */
    public function isProduction(): bool
    {
        return $this->environment === Environment::PRODUCTION;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getRetries(): int
    {
        return $this->retries;
    }

    /**
     * Valor de verificación TLS para el cliente HTTP (Guzzle 'verify').
     */
    public function getVerifySsl(): bool|string
    {
        return $this->verifySsl;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    /**
     * Resolver URL base según el entorno
     *
     * Solo production está disponible por ahora.
     */
    private function resolveBaseUrl(Environment $environment): string
    {
        return match ($environment) {
            Environment::PRODUCTION => self::API_URL_PRODUCTION,
        };
    }

    /**
     * Validar email
     */
    private function validateEmail(string $email): void
    {
        if (empty($email)) {
            throw new InvalidArgumentException('Email no puede estar vacío');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Email no tiene un formato válido');
        }
    }

    /**
     * Validar password
     */
    private function validatePassword(string $password): void
    {
        if (empty($password)) {
            throw new InvalidArgumentException('Password no puede estar vacío');
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
     * Validar verificación TLS
     *
     */
    private function validateVerifySsl(bool|string $verifySsl): void
    {
        if (is_string($verifySsl) && trim($verifySsl) === '') {
            throw new InvalidArgumentException(
                'La ruta del bundle de CA (verifySsl) no puede estar vacía'
            );
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
            'environment' => $this->environment->value,
            'baseUrl' => $this->baseUrl,
            'timeout' => $this->timeout,
            'retries' => $this->retries,
            'verifySsl' => $this->verifySsl,
        ];
    }
}
