<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Unit\Enums;

use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Enums\Environment;

final class EnvironmentTest extends TestCase
{
    public function testSandboxEnvironment(): void
    {
        $env = Environment::SANDBOX;

        self::assertSame('sandbox', $env->value);
        self::assertSame(Environment::SANDBOX, $env);
    }

    public function testProductionEnvironment(): void
    {
        $env = Environment::PRODUCTION;

        self::assertSame('production', $env->value);
        self::assertSame(Environment::PRODUCTION, $env);
    }

    public function testFromStringValue(): void
    {
        $sandbox = Environment::from('sandbox');
        $production = Environment::from('production');

        self::assertSame(Environment::SANDBOX, $sandbox);
        self::assertSame(Environment::PRODUCTION, $production);
    }

    public function testTryFromValidValue(): void
    {
        $sandbox = Environment::tryFrom('sandbox');
        $production = Environment::tryFrom('production');

        self::assertInstanceOf(Environment::class, $sandbox);
        self::assertInstanceOf(Environment::class, $production);
    }

    public function testTryFromInvalidValue(): void
    {
        $invalid = Environment::tryFrom('invalid');

        self::assertNull($invalid);
    }

    public function testCasesCount(): void
    {
        $cases = Environment::cases();

        self::assertCount(2, $cases);
        self::assertContains(Environment::SANDBOX, $cases);
        self::assertContains(Environment::PRODUCTION, $cases);
    }
}
