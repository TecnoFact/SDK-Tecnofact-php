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
        $email = 'test@example.com';
        $password = 'password123';

        $config = new Config($email, $password);
        $exception = new TecnoFactException('Error occurred');

        $this->assertStringNotContainsString($email, $exception->getMessage());
        $this->assertStringNotContainsString($password, $exception->getMessage());
    }

    public function testConfigToArrayDoesNotExposeCredentials(): void
    {
        $email = 'test@example.com';
        $password = 'password123';

        $config = new Config($email, $password);
        $array = $config->toArray();

        $this->assertArrayNotHasKey('email', $array);
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('token', $array);

        // Los valores de las credenciales tampoco deben aparecer en el array.
        $this->assertNotContains($email, $array);
        $this->assertNotContains($password, $array);
    }

    public function testConfigToArrayContainsSafeInformation(): void
    {
        $config = new Config(
            email: 'test@example.com',
            password: 'password123',
            environment: Environment::PRODUCTION,
            timeout: 30,
            retries: 3
        );

        $array = $config->toArray();

        $this->assertArrayHasKey('environment', $array);
        $this->assertArrayHasKey('baseUrl', $array);
        $this->assertArrayHasKey('timeout', $array);
        $this->assertArrayHasKey('retries', $array);

        $this->assertSame('production', $array['environment']);
        $this->assertSame(30, $array['timeout']);
        $this->assertSame(3, $array['retries']);
    }

    public function testEmailValidationPreventsEmptyCredentials(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email no puede estar vacío');

        new Config(
            email: '',
            password: 'password123'
        );
    }

    public function testEmailValidationEnforcesValidFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email no tiene un formato válido');

        new Config(
            email: 'not-an-email',
            password: 'password123'
        );
    }

    public function testPasswordValidationPreventsEmptyCredentials(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password no puede estar vacío');

        new Config(
            email: 'test@example.com',
            password: ''
        );
    }

    public function testEnvironmentVariablesNotExposedInErrorMessages(): void
    {
        $originalEmail = $_ENV['TECN_FACT_EMAIL'] ?? null;
        $originalPassword = $_ENV['TECN_FACT_PASSWORD'] ?? null;

        try {
            unset($_ENV['TECN_FACT_EMAIL']);
            unset($_ENV['TECN_FACT_PASSWORD']);

            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Variable de entorno TECN_FACT_EMAIL es requerida');

            Config::fromEnvironment();
        } finally {
            if ($originalEmail !== null) {
                $_ENV['TECN_FACT_EMAIL'] = $originalEmail;
            }
            if ($originalPassword !== null) {
                $_ENV['TECN_FACT_PASSWORD'] = $originalPassword;
            }
        }
    }

    public function testTokenCanBeCleared(): void
    {
        $config = new Config(
            email: 'test@example.com',
            password: 'password123'
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
            email: 'test@example.com',
            password: 'password123',
            timeout: 0
        );
    }

    public function testTimeoutValidationEnforcesUpperBound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout debe estar entre 1 y 300 segundos');

        new Config(
            email: 'test@example.com',
            password: 'password123',
            timeout: 301
        );
    }

    public function testRetriesValidationEnforcesBounds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Reintentos debe estar entre 0 y 10');

        new Config(
            email: 'test@example.com',
            password: 'password123',
            retries: -1
        );
    }

    public function testRetriesValidationEnforcesUpperBound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Reintentos debe estar entre 0 y 10');

        new Config(
            email: 'test@example.com',
            password: 'password123',
            retries: 11
        );
    }

    public function testProductionEnvironmentUsesCorrectUrl(): void
    {
        $config = new Config(
            email: 'test@example.com',
            password: 'password123',
            environment: Environment::PRODUCTION
        );

        $this->assertTrue($config->isProduction());
        $this->assertStringContainsString('https://', $config->getBaseUrl());
        $this->assertStringNotContainsString('sandbox', $config->getBaseUrl());
    }
}
