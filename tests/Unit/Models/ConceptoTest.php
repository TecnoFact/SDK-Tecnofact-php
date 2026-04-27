<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Models\Concepto;

final class ConceptoTest extends TestCase
{
    public function testConstructorWithRequiredFields(): void
    {
        $concepto = new Concepto(
            claveProdServ: '01010101',
            cantidad: 1.0,
            claveUnidad: 'E48',
            unidad: null,
            descripcion: 'Producto de prueba',
            valorUnitario: 100.00,
            importe: 100.00,
            objetoImp: '02'
        );

        self::assertSame('01010101', $concepto->getClaveProdServ());
        self::assertSame(1.0, $concepto->getCantidad());
        self::assertSame('E48', $concepto->getClaveUnidad());
        self::assertSame('Producto de prueba', $concepto->getDescripcion());
        self::assertSame(100.00, $concepto->getValorUnitario());
        self::assertSame(100.00, $concepto->getImporte());
        self::assertSame('02', $concepto->getObjetoImp());
    }

    public function testConstructorWithOptionalFields(): void
    {
        $concepto = new Concepto(
            claveProdServ: '01010101',
            cantidad: 2.0,
            claveUnidad: 'E48',
            unidad: 'Pieza',
            descripcion: 'Producto completo',
            valorUnitario: 150.00,
            importe: 300.00,
            objetoImp: '02',
            impuestos: null,
            noIdentificacion: 'PROD-001'
        );

        self::assertSame('PROD-001', $concepto->getNoIdentificacion());
        self::assertSame('Pieza', $concepto->getUnidad());
        self::assertSame('02', $concepto->getObjetoImp());
    }

    public function testToArrayWithRequiredFields(): void
    {
        $concepto = new Concepto(
            claveProdServ: '01010101',
            cantidad: 1.0,
            claveUnidad: 'E48',
            unidad: null,
            descripcion: 'Producto test',
            valorUnitario: 100.00,
            importe: 100.00,
            objetoImp: '02'
        );

        $array = $concepto->toArray();

        self::assertArrayHasKey('clave_prod_serv', $array);
        self::assertArrayHasKey('cantidad', $array);
        self::assertArrayHasKey('clave_unidad', $array);
        self::assertArrayHasKey('descripcion', $array);
        self::assertArrayHasKey('valor_unitario', $array);
        self::assertArrayHasKey('importe', $array);
        self::assertArrayNotHasKey('no_identificacion', $array);
        self::assertArrayNotHasKey('unidad', $array);
    }

    public function testToArrayWithOptionalFields(): void
    {
        $concepto = new Concepto(
            claveProdServ: '01010101',
            cantidad: 2.0,
            claveUnidad: 'E48',
            unidad: 'Pieza',
            descripcion: 'Producto completo',
            valorUnitario: 150.00,
            importe: 300.00,
            objetoImp: '02',
            impuestos: null,
            noIdentificacion: 'PROD-001'
        );

        $array = $concepto->toArray();

        self::assertArrayHasKey('no_identificacion', $array);
        self::assertArrayHasKey('unidad', $array);
        self::assertSame('PROD-001', $array['no_identificacion']);
        self::assertSame('Pieza', $array['unidad']);
    }

    public function testCantidadDecimal(): void
    {
        $concepto = new Concepto(
            claveProdServ: '01010101',
            cantidad: 2.5,
            claveUnidad: 'E48',
            unidad: null,
            descripcion: 'Producto fraccionado',
            valorUnitario: 50.00,
            importe: 125.00,
            objetoImp: '02'
        );

        self::assertSame(2.5, $concepto->getCantidad());
        self::assertSame(125.00, $concepto->getImporte());
    }

    public function testImporteCalculation(): void
    {
        $cantidad = 10.0;
        $valorUnitario = 100.00;
        $importe = $cantidad * $valorUnitario;

        $concepto = new Concepto(
            claveProdServ: '01010101',
            cantidad: $cantidad,
            claveUnidad: 'E48',
            unidad: null,
            descripcion: 'Producto para cálculo',
            valorUnitario: $valorUnitario,
            importe: $importe,
            objetoImp: '02'
        );

        self::assertSame(1000.00, $concepto->getImporte());
        self::assertSame($cantidad * $valorUnitario, $concepto->getImporte());
    }

    public function testObjetoImpuestoValues(): void
    {
        $concepto01 = new Concepto(
            claveProdServ: '01010101',
            cantidad: 1.0,
            claveUnidad: 'E48',
            unidad: null,
            descripcion: 'Sin impuestos',
            valorUnitario: 100.00,
            importe: 100.00,
            objetoImp: '01'
        );

        $concepto02 = new Concepto(
            claveProdServ: '01010101',
            cantidad: 1.0,
            claveUnidad: 'E48',
            unidad: null,
            descripcion: 'Con impuestos',
            valorUnitario: 100.00,
            importe: 100.00,
            objetoImp: '02'
        );

        self::assertSame('01', $concepto01->getObjetoImp());
        self::assertSame('02', $concepto02->getObjetoImp());
    }
}
