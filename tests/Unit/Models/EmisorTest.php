<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Models\Emisor;

final class EmisorTest extends TestCase
{
    public function testConstructorWithRequiredFields(): void
    {
        $emisor = new Emisor(
            rfc: 'XAXX010101000',
            nombre: 'Empresa de Prueba SA de CV',
            regimenFiscal: '601',
            cp: '12345'
        );

        self::assertSame('XAXX010101000', $emisor->getRfc());
        self::assertSame('Empresa de Prueba SA de CV', $emisor->getNombre());
        self::assertSame('601', $emisor->getRegimenFiscal());
        self::assertSame('12345', $emisor->getCp());
        self::assertNull($emisor->getFacAtrAdm());
    }

    public function testConstructorWithOptionalFields(): void
    {
        $emisor = new Emisor(
            rfc: 'XAXX010101000',
            nombre: 'Empresa de Prueba SA de CV',
            regimenFiscal: '601',
            cp: '12345',
            facAtrAdm: 'FAC123456'
        );

        self::assertSame('FAC123456', $emisor->getFacAtrAdm());
    }

    public function testToArrayWithRequiredFields(): void
    {
        $emisor = new Emisor(
            rfc: 'XAXX010101000',
            nombre: 'Empresa de Prueba SA de CV',
            regimenFiscal: '601',
            cp: '12345'
        );

        $array = $emisor->toArray();

        self::assertArrayHasKey('rfc', $array);
        self::assertArrayHasKey('nombre', $array);
        self::assertArrayHasKey('regimen_fiscal', $array);
        self::assertArrayHasKey('cp', $array);
        self::assertArrayNotHasKey('facAtrAdm', $array);
        self::assertSame('XAXX010101000', $array['rfc']);
        self::assertSame('601', $array['regimen_fiscal']);
    }

    public function testToArrayWithOptionalFields(): void
    {
        $emisor = new Emisor(
            rfc: 'XAXX010101000',
            nombre: 'Empresa de Prueba SA de CV',
            regimenFiscal: '601',
            cp: '12345',
            facAtrAdm: 'FAC123456'
        );

        $array = $emisor->toArray();

        self::assertArrayHasKey('facAtrAdm', $array);
        self::assertSame('FAC123456', $array['facAtrAdm']);
    }

    public function testRfcPersonaMoral(): void
    {
        $emisor = new Emisor(
            rfc: 'ABC123456789',
            nombre: 'ABC Empresa SA',
            regimenFiscal: '601',
            cp: '54000'
        );

        self::assertSame(12, strlen($emisor->getRfc()));
    }

    public function testRfcPersonaFisica(): void
    {
        $emisor = new Emisor(
            rfc: 'XAXX010101000',
            nombre: 'Juan Pérez',
            regimenFiscal: '612',
            cp: '54000'
        );

        self::assertSame(13, strlen($emisor->getRfc()));
    }

    public function testCodigoPostalFormat(): void
    {
        $emisor = new Emisor(
            rfc: 'XAXX010101000',
            nombre: 'Empresa Test',
            regimenFiscal: '601',
            cp: '01234'
        );

        self::assertSame(5, strlen($emisor->getCp()));
        self::assertMatchesRegularExpression('/^\d{5}$/', $emisor->getCp());
    }

    public function testRegimenFiscalFormat(): void
    {
        $emisor = new Emisor(
            rfc: 'XAXX010101000',
            nombre: 'Empresa Test',
            regimenFiscal: '601',
            cp: '12345'
        );

        self::assertSame(3, strlen($emisor->getRegimenFiscal()));
        self::assertMatchesRegularExpression('/^\d{3}$/', $emisor->getRegimenFiscal());
    }
}
