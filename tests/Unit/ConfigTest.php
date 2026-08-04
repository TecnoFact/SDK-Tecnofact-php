<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Enums\Environment;

final class ConfigTest extends TestCase
{
    public function testConstructorWithDefaultValues(): void
    {
        $config = new Config(
            email: 'test@example.com',
            password: 'password123'
        );

        self::assertSame(Environment::PRODUCTION, $config->getEnvironment());
        self::assertTrue($config->isProduction());
        self::assertSame(30, $config->getTimeout());
        self::assertSame(3, $config->getRetries());
    }

    public function testConstructorWithProductionEnvironment(): void
    {
        $config = new Config(
            email: 'test@example.com',
            password: 'password123',
            environment: Environment::PRODUCTION
        );

        self::assertSame(Environment::PRODUCTION, $config->getEnvironment());
        self::assertTrue($config->isProduction());
    }

    public function testConstructorWithCustomTimeoutAndRetries(): void
    {
        $config = new Config(
            email: 'test@example.com',
            password: 'password123',
            timeout: 60,
            retries: 5
        );

        self::assertSame(60, $config->getTimeout());
        self::assertSame(5, $config->getRetries());
    }

    public function testEmptyEmailThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email no puede estar vacío');

        new Config(
            email: '',
            password: 'password123'
        );
    }

    public function testInvalidEmailFormatThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email no tiene un formato válido');

        new Config(
            email: 'not-an-email',
            password: 'password123'
        );
    }

    public function testEmptyPasswordThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password no puede estar vacío');

        new Config(
            email: 'test@example.com',
            password: ''
        );
    }

    public function testInvalidTimeoutThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout debe estar entre 1 y 300 segundos');

        new Config(
            email: 'test@example.com',
            password: 'password123',
            timeout: 500
        );
    }

    public function testInvalidRetriesThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Reintentos debe estar entre 0 y 10');

        new Config(
            email: 'test@example.com',
            password: 'password123',
            retries: 20
        );
    }

    public function testVerifySslDefaultsToTrue(): void
    {
        $config = new Config(
            email: 'test@example.com',
            password: 'password123'
        );

        self::assertTrue($config->getVerifySsl());
    }

    public function testVerifySslAcceptsCustomBundlePath(): void
    {
        $config = new Config(
            email: 'test@example.com',
            password: 'password123',
            verifySsl: '/etc/ssl/certs/panel-bundle.pem'
        );

        self::assertSame('/etc/ssl/certs/panel-bundle.pem', $config->getVerifySsl());
    }

    public function testVerifySslCanBeDisabled(): void
    {
        $config = new Config(
            email: 'test@example.com',
            password: 'password123',
            verifySsl: false
        );

        self::assertFalse($config->getVerifySsl());
    }

    public function testEmptyVerifySslPathThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('La ruta del bundle de CA (verifySsl) no puede estar vacía');

        new Config(
            email: 'test@example.com',
            password: 'password123',
            verifySsl: '   '
        );
    }

    public function testToArrayReturnsCorrectFormat(): void
    {
        $config = new Config(
            email: 'test@example.com',
            password: 'password123',
            environment: Environment::PRODUCTION,
            timeout: 30,
            retries: 3
        );

        $array = $config->toArray();

        self::assertArrayHasKey('environment', $array);
        self::assertArrayHasKey('baseUrl', $array);
        self::assertArrayHasKey('timeout', $array);
        self::assertArrayHasKey('retries', $array);
        self::assertArrayHasKey('verifySsl', $array);
        self::assertSame('production', $array['environment']);
    }
}
