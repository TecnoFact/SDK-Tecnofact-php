<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Models\Receptor;

final class ReceptorTest extends TestCase
{
    public function testConstructorWithRequiredFields(): void
    {
        $receptor = new Receptor(
            rfc: 'XAXX010101000',
            nombre: 'Cliente de Prueba',
            usoCfdi: 'G03',
            domicilioFiscalReceptor: '12345',
            regimenFiscalReceptor: '612'
        );

        self::assertSame('XAXX010101000', $receptor->getRfc());
        self::assertSame('Cliente de Prueba', $receptor->getNombre());
        self::assertSame('G03', $receptor->getUsoCfdi());
        self::assertSame('12345', $receptor->getDomicilioFiscalReceptor());
        self::assertSame('612', $receptor->getRegimenFiscalReceptor());
        self::assertNull($receptor->getResidenciaFiscal());
        self::assertNull($receptor->getNumRegIdTrib());
    }

    public function testConstructorWithOptionalFields(): void
    {
        $receptor = new Receptor(
            rfc: 'XEXX010101000',
            nombre: 'Cliente Extranjero',
            usoCfdi: 'G03',
            domicilioFiscalReceptor: '00000',
            regimenFiscalReceptor: '616',
            residenciaFiscal: 'USA',
            numRegIdTrib: '123456789'
        );

        self::assertSame('USA', $receptor->getResidenciaFiscal());
        self::assertSame('123456789', $receptor->getNumRegIdTrib());
    }

    public function testToArrayWithRequiredFields(): void
    {
        $receptor = new Receptor(
            rfc: 'XAXX010101000',
            nombre: 'Cliente de Prueba',
            usoCfdi: 'G03',
            domicilioFiscalReceptor: '12345',
            regimenFiscalReceptor: '612'
        );

        $array = $receptor->toArray();

        self::assertArrayHasKey('rfc', $array);
        self::assertArrayHasKey('nombre', $array);
        self::assertArrayHasKey('uso_cfdi', $array);
        self::assertArrayHasKey('domicilio_fiscal_receptor', $array);
        self::assertArrayHasKey('regimen_fiscal_receptor', $array);
        self::assertArrayNotHasKey('residencia_fiscal', $array);
        self::assertArrayNotHasKey('num_reg_id_trib', $array);
    }

    public function testToArrayWithOptionalFields(): void
    {
        $receptor = new Receptor(
            rfc: 'XEXX010101000',
            nombre: 'Cliente Extranjero',
            usoCfdi: 'G03',
            domicilioFiscalReceptor: '00000',
            regimenFiscalReceptor: '616',
            residenciaFiscal: 'USA',
            numRegIdTrib: '123456789'
        );

        $array = $receptor->toArray();

        self::assertArrayHasKey('residencia_fiscal', $array);
        self::assertArrayHasKey('num_reg_id_trib', $array);
        self::assertSame('USA', $array['residencia_fiscal']);
        self::assertSame('123456789', $array['num_reg_id_trib']);
    }

    public function testUsoCfdiGastosGenerales(): void
    {
        $receptor = new Receptor(
            rfc: 'XAXX010101000',
            nombre: 'Cliente',
            usoCfdi: 'G03',
            domicilioFiscalReceptor: '12345',
            regimenFiscalReceptor: '612'
        );

        self::assertSame('G03', $receptor->getUsoCfdi());
    }

    public function testUsoCfdiAdquisicionMercancias(): void
    {
        $receptor = new Receptor(
            rfc: 'XAXX010101000',
            nombre: 'Cliente',
            usoCfdi: 'G01',
            domicilioFiscalReceptor: '12345',
            regimenFiscalReceptor: '601'
        );

        self::assertSame('G01', $receptor->getUsoCfdi());
    }

    public function testReceptorExtranjero(): void
    {
        $receptor = new Receptor(
            rfc: 'XEXX010101000',
            nombre: 'Foreign Client Inc',
            usoCfdi: 'P01',
            domicilioFiscalReceptor: '00000',
            regimenFiscalReceptor: '616',
            residenciaFiscal: 'USA',
            numRegIdTrib: 'US123456789'
        );

        self::assertSame('XEXX010101000', $receptor->getRfc());
        self::assertSame('USA', $receptor->getResidenciaFiscal());
        self::assertNotNull($receptor->getNumRegIdTrib());
    }
}
