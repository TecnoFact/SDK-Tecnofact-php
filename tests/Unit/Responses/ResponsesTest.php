<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Unit\Responses;

use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Responses\AcuseCancelacion;
use TecnoFact\Sdk\Responses\EstatusCfdi;
use TecnoFact\Sdk\Responses\ResultadoTimbrado;

final class ResponsesTest extends TestCase
{
    public function testEstatusCfdiMapeaVigente(): void
    {
        $estatus = EstatusCfdi::fromResponse([
            'success' => true,
            'code' => 200,
            'data' => [
                'codigo' => 'S - Cancelable con aceptación',
                'estado' => 'Vigente',
                'es_cancellable' => 'Cancelable con aceptación',
                'estatus_cancelacion' => '',
                'efos' => 'excluido',
            ],
        ]);

        self::assertTrue($estatus->isSuccess());
        self::assertTrue($estatus->isVigente());
        self::assertSame('Vigente', $estatus->getEstado());
        self::assertSame('excluido', $estatus->getEfos());
        self::assertArrayHasKey('data', $estatus->getRaw());
    }

    public function testEstatusCfdiNoVigente(): void
    {
        $estatus = EstatusCfdi::fromResponse([
            'success' => true,
            'data' => ['estado' => 'Cancelado'],
        ]);

        self::assertFalse($estatus->isVigente());
        self::assertSame('Cancelado', $estatus->getEstado());
    }

    public function testEstatusCfdiSinDataNoRompe(): void
    {
        $estatus = EstatusCfdi::fromResponse(['success' => false]);

        self::assertFalse($estatus->isSuccess());
        self::assertNull($estatus->getEstado());
        self::assertFalse($estatus->isVigente());
    }

    public function testAcuseCancelacionMapeaYDecodificaPdf(): void
    {
        $pdfBytes = '%PDF-1.7 contenido';

        $acuse = AcuseCancelacion::fromResponse([
            'success' => true,
            'code' => 200,
            'message' => 'procesado',
            'data' => [
                'validado' => true,
                'uuid' => 'A0048319-F108-435D-8BE1-ADEB5DE14FB1',
                'status_sat' => '201 - Solicitud de cancelación aceptada por el SAT',
                'xml' => '<Acuse/>',
                'pdf' => base64_encode($pdfBytes),
            ],
        ]);

        self::assertTrue($acuse->isSuccess());
        self::assertTrue($acuse->isValidado());
        self::assertTrue($acuse->isAceptadaPorSat());
        self::assertSame('A0048319-F108-435D-8BE1-ADEB5DE14FB1', $acuse->getUuid());
        self::assertSame('<Acuse/>', $acuse->getXml());
        self::assertSame($pdfBytes, $acuse->getPdfBinario());
    }

    public function testAcuseCancelacionSinPdfDevuelveNull(): void
    {
        $acuse = AcuseCancelacion::fromResponse([
            'success' => true,
            'data' => ['validado' => true, 'status_sat' => '202 - En proceso'],
        ]);

        self::assertNull($acuse->getPdfBase64());
        self::assertNull($acuse->getPdfBinario());
        self::assertFalse($acuse->isAceptadaPorSat());
    }

    public function testResultadoTimbradoLeeXmlTimbrado(): void
    {
        $resultado = ResultadoTimbrado::fromResponse([
            'success' => true,
            'code' => 200,
            'xml_timbrado' => '<cfdi:Comprobante/>',
        ]);

        self::assertTrue($resultado->isSuccess());
        self::assertSame(200, $resultado->getCode());
        self::assertSame('<cfdi:Comprobante/>', $resultado->getXmlTimbrado());
    }

    public function testResultadoTimbradoLeeMensajeDeError(): void
    {
        $resultado = ResultadoTimbrado::fromResponse([
            'success' => false,
            'error' => '401: fecha fuera de rango',
            'code' => 400,
            'xml_timbrado' => '',
        ]);

        self::assertFalse($resultado->isSuccess());
        self::assertSame('401: fecha fuera de rango', $resultado->getMessage());
        self::assertSame(400, $resultado->getCode());
    }
}
