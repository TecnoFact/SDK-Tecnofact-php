<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Exceptions\AuthenticationException;
use TecnoFact\Sdk\Exceptions\CancelacionException;
use TecnoFact\Sdk\Exceptions\NotFoundException;
use TecnoFact\Sdk\Exceptions\RateLimitException;
use TecnoFact\Sdk\Exceptions\ServerException;
use TecnoFact\Sdk\Exceptions\TecnoFactException;
use TecnoFact\Sdk\Exceptions\TimbradoException;
use TecnoFact\Sdk\Exceptions\ValidationException;

final class ExceptionsTest extends TestCase
{
    public function testTecnoFactExceptionIsBaseException(): void
    {
        $exception = new TecnoFactException('Test error');

        self::assertInstanceOf(\Exception::class, $exception);
        self::assertSame('Test error', $exception->getMessage());
    }

    public function testAuthenticationExceptionExtendsTecnoFactException(): void
    {
        $exception = new AuthenticationException('Authentication failed');

        self::assertInstanceOf(TecnoFactException::class, $exception);
        self::assertSame('Authentication failed', $exception->getMessage());
    }

    public function testValidationExceptionExtendsTecnoFactException(): void
    {
        $exception = new ValidationException('Validation error');

        self::assertInstanceOf(TecnoFactException::class, $exception);
        self::assertSame('Validation error', $exception->getMessage());
    }

    public function testNotFoundExceptionExtendsTecnoFactException(): void
    {
        $exception = new NotFoundException('Resource not found');

        self::assertInstanceOf(TecnoFactException::class, $exception);
        self::assertSame('Resource not found', $exception->getMessage());
    }

    public function testRateLimitExceptionExtendsTecnoFactException(): void
    {
        $exception = new RateLimitException('Rate limit exceeded');

        self::assertInstanceOf(TecnoFactException::class, $exception);
        self::assertSame('Rate limit exceeded', $exception->getMessage());
    }

    public function testServerExceptionExtendsTecnoFactException(): void
    {
        $exception = new ServerException('Server error');

        self::assertInstanceOf(TecnoFactException::class, $exception);
        self::assertSame('Server error', $exception->getMessage());
    }

    public function testTimbradoExceptionExtendsTecnoFactException(): void
    {
        $exception = new TimbradoException('Timbrado failed');

        self::assertInstanceOf(TecnoFactException::class, $exception);
        self::assertSame('Timbrado failed', $exception->getMessage());
    }

    public function testCancelacionExceptionExtendsTecnoFactException(): void
    {
        $exception = new CancelacionException('Cancelacion failed');

        self::assertInstanceOf(TecnoFactException::class, $exception);
        self::assertSame('Cancelacion failed', $exception->getMessage());
    }

    public function testExceptionWithCode(): void
    {
        $exception = new TecnoFactException('Error with code', 500);

        self::assertSame(500, $exception->getCode());
    }

    public function testExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous error');
        $exception = new TecnoFactException('Current error', 0, $previous);

        self::assertSame($previous, $exception->getPrevious());
    }

    public function testAuthenticationExceptionCanBeCaught(): void
    {
        try {
            throw new AuthenticationException('Auth error');
        } catch (TecnoFactException $e) {
            self::assertInstanceOf(AuthenticationException::class, $e);
            self::assertSame('Auth error', $e->getMessage());
        }
    }

    public function testValidationExceptionCanBeCaught(): void
    {
        try {
            throw new ValidationException('Validation error');
        } catch (TecnoFactException $e) {
            self::assertInstanceOf(ValidationException::class, $e);
            self::assertSame('Validation error', $e->getMessage());
        }
    }

    public function testServerExceptionCanBeCaught(): void
    {
        try {
            throw new ServerException('Server error');
        } catch (TecnoFactException $e) {
            self::assertInstanceOf(ServerException::class, $e);
            self::assertSame('Server error', $e->getMessage());
        }
    }
}
