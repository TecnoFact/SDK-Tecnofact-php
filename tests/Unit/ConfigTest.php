<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Enums\Environment;
use InvalidArgumentException;

final class ConfigTest extends TestCase
{
    public function testConstructorWithDefaultValues(): void
    {
        $config = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890'
        );

        self::assertSame(Environment::SANDBOX, $config->getEnvironment());
        self::assertTrue($config->isSandbox());
        self::assertFalse($config->isProduction());
        self::assertSame(30, $config->getTimeout());
        self::assertSame(3, $config->getRetries());
    }

    public function testConstructorWithProductionEnvironment(): void
    {
        $config = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            environment: Environment::PRODUCTION
        );

        self::assertSame(Environment::PRODUCTION, $config->getEnvironment());
        self::assertTrue($config->isProduction());
        self::assertFalse($config->isSandbox());
    }

    public function testConstructorWithCustomTimeoutAndRetries(): void
    {
        $config = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            timeout: 60,
            retries: 5
        );

        self::assertSame(60, $config->getTimeout());
        self::assertSame(5, $config->getRetries());
    }

    public function testInvalidApiKeyThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API Key no puede estar vacío');

        new Config(
            apiKey: '',
            apiSecret: 'test-api-secret-12345678901234567890'
        );
    }

    public function testShortApiKeyThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API Key debe tener al menos 10 caracteres');

        new Config(
            apiKey: 'short',
            apiSecret: 'test-api-secret-12345678901234567890'
        );
    }

    public function testInvalidApiSecretThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API Secret no puede estar vacío');

        new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: ''
        );
    }

    public function testShortApiSecretThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API Secret debe tener al menos 20 caracteres');

        new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'short-secret'
        );
    }

    public function testInvalidTimeoutThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout debe estar entre 1 y 300 segundos');

        new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            timeout: 500
        );
    }

    public function testInvalidRetriesThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Reintentos debe estar entre 0 y 10');

        new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            retries: 20
        );
    }

    public function testToArrayReturnsCorrectFormat(): void
    {
        $config = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            environment: Environment::SANDBOX,
            timeout: 30,
            retries: 3
        );

        $array = $config->toArray();

        self::assertArrayHasKey('environment', $array);
        self::assertArrayHasKey('baseUrl', $array);
        self::assertArrayHasKey('timeout', $array);
        self::assertArrayHasKey('retries', $array);
        self::assertSame('sandbox', $array['environment']);
    }
}
