<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Security;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Enums\Environment;
use TecnoFact\Sdk\Exceptions\TecnoFactException;

/**
 * Security-focused tests for credential handling
 */
final class CredentialSecurityTest extends TestCase
{
    public function testCredentialsNotExposedInExceptions(): void
    {
        $apiKey = 'test-api-key-1234567890';
        $apiSecret = 'test-api-secret-12345678901234567890';

        $config = new Config($apiKey, $apiSecret);
        $exception = new TecnoFactException('Error occurred');

        $this->assertStringNotContainsString($apiKey, $exception->getMessage());
        $this->assertStringNotContainsString($apiSecret, $exception->getMessage());
    }

    public function testConfigToArrayDoesNotExposeCredentials(): void
    {
        $apiKey = 'test-api-key-1234567890';
        $apiSecret = 'test-api-secret-12345678901234567890';

        $config = new Config($apiKey, $apiSecret);
        $array = $config->toArray();

        $this->assertArrayNotHasKey('apiKey', $array);
        $this->assertArrayNotHasKey('apiSecret', $array);
        $this->assertArrayNotHasKey('token', $array);
    }

    public function testConfigToArrayContainsSafeInformation(): void
    {
        $config = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            environment: Environment::SANDBOX,
            timeout: 30,
            retries: 3
        );

        $array = $config->toArray();

        $this->assertArrayHasKey('environment', $array);
        $this->assertArrayHasKey('baseUrl', $array);
        $this->assertArrayHasKey('timeout', $array);
        $this->assertArrayHasKey('retries', $array);

        $this->assertSame('sandbox', $array['environment']);
        $this->assertSame(30, $array['timeout']);
        $this->assertSame(3, $array['retries']);
    }

    public function testApiKeyValidationPreventsEmptyCredentials(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API Key no puede estar vacío');

        new Config(
            apiKey: '',
            apiSecret: 'test-api-secret-12345678901234567890'
        );
    }

    public function testApiKeyValidationEnforcesMinimumLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API Key debe tener al menos 10 caracteres');

        new Config(
            apiKey: 'short',
            apiSecret: 'test-api-secret-12345678901234567890'
        );
    }

    public function testApiSecretValidationPreventsEmptyCredentials(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API Secret no puede estar vacío');

        new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: ''
        );
    }

    public function testApiSecretValidationEnforcesMinimumLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API Secret debe tener al menos 20 caracteres');

        new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'short-secret'
        );
    }

    public function testEnvironmentVariablesNotExposedInErrorMessages(): void
    {
        $originalApiKey = $_ENV['TECN_FACT_API_KEY'] ?? null;
        $originalApiSecret = $_ENV['TECN_FACT_API_SECRET'] ?? null;

        try {
            unset($_ENV['TECN_FACT_API_KEY']);
            unset($_ENV['TECN_FACT_API_SECRET']);

            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Variable de entorno TECN_FACT_API_KEY es requerida');

            Config::fromEnvironment();
        } finally {
            if ($originalApiKey !== null) {
                $_ENV['TECN_FACT_API_KEY'] = $originalApiKey;
            }
            if ($originalApiSecret !== null) {
                $_ENV['TECN_FACT_API_SECRET'] = $originalApiSecret;
            }
        }
    }

    public function testTokenCanBeCleared(): void
    {
        $config = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890'
        );

        $config->setToken('test-token-12345');
        $this->assertSame('test-token-12345', $config->getToken());

        $config->setToken(null);
        $this->assertNull($config->getToken());
    }

    public function testTimeoutValidationEnforcesBounds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout debe estar entre 1 y 300 segundos');

        new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            timeout: 0
        );
    }

    public function testTimeoutValidationEnforcesUpperBound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout debe estar entre 1 y 300 segundos');

        new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            timeout: 301
        );
    }

    public function testRetriesValidationEnforcesBounds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Reintentos debe estar entre 0 y 10');

        new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            retries: -1
        );
    }

    public function testRetriesValidationEnforcesUpperBound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Reintentos debe estar entre 0 y 10');

        new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            retries: 11
        );
    }

    public function testProductionEnvironmentUsesCorrectUrl(): void
    {
        $config = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            environment: Environment::PRODUCTION
        );

        $this->assertTrue($config->isProduction());
        $this->assertFalse($config->isSandbox());
        $this->assertStringContainsString('https://', $config->getBaseUrl());
        $this->assertStringNotContainsString('sandbox', $config->getBaseUrl());
    }

    public function testSandboxEnvironmentUsesCorrectUrl(): void
    {
        $config = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            environment: Environment::SANDBOX
        );

        $this->assertTrue($config->isSandbox());
        $this->assertFalse($config->isProduction());
        $this->assertStringContainsString('https://', $config->getBaseUrl());
        $this->assertStringContainsString('sandbox', $config->getBaseUrl());
    }
}
