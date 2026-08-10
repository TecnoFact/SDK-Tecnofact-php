<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Unit\Enums;

use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Enums\Environment;

final class EnvironmentTest extends TestCase
{
    public function testTryFromValidValue(): void
    {
        $production = Environment::tryFrom('production');

        self::assertInstanceOf(Environment::class, $production);
    }

    public function testTryFromInvalidValue(): void
    {
        $invalid = Environment::tryFrom('invalid');

        self::assertTrue($invalid === null);
    }

    public function testIsProduction(): void
    {
        self::assertTrue(Environment::PRODUCTION->isProduction());
    }

    public function testLabel(): void
    {
        self::assertSame('Producción', Environment::PRODUCTION->label());
    }

    public function testCasesCount(): void
    {
        $cases = Environment::cases();

        self::assertCount(1, $cases);
        self::assertContains(Environment::PRODUCTION, $cases);
    }
}
