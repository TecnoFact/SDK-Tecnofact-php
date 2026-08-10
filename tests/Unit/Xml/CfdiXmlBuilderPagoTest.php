<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Unit\Xml;

use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Models\DoctoRelacionado;
use TecnoFact\Sdk\Models\Emisor;
use TecnoFact\Sdk\Models\Pago;
use TecnoFact\Sdk\Models\PagoRequest;
use TecnoFact\Sdk\Models\Receptor;
use TecnoFact\Sdk\Xml\CfdiXmlBuilder;

final class CfdiXmlBuilderPagoTest extends TestCase
{
    private const CFDI_NS = 'http://www.sat.gob.mx/cfd/4';
    private const PAGO20_NS = 'http://www.sat.gob.mx/Pagos20';

    public function testBuildPagoMatchesTuXmlDeEjemplo(): void
    {
        $request = new PagoRequest(
            emisor: new Emisor(
                rfc: 'KFR250210TQ1',
                nombre: 'KBA FILTERS Y REFACCIONES',
                regimenFiscal: '601',
                cp: '06300'
            ),
            receptor: new Receptor(
                rfc: 'XAXX010101000',
                nombre: 'PÚBLICO EN GENERAL',
                usoCfdi: 'CP01',
                domicilioFiscalReceptor: '06300',
                regimenFiscalReceptor: '616'
            ),
            pagos: [
                new Pago(
                    fechaPago: new \DateTime('2026-06-04T09:34:41'),
                    formaDePagoP: '01',
                    monedaP: 'MXN',
                    monto: '1.00',
                    doctosRelacionados: [
                        new DoctoRelacionado(
                            idDocumento: '4EE306E1-59B0-4F0D-BA73-9C3126034CBC',
                            monedaDR: 'MXN',
                            equivalenciaDR: '1',
                            numParcialidad: 1,
                            impSaldoAnt: '1.00',
                            impPagado: '1.00',
                            impSaldoInsoluto: '0.00',
                            objetoImpDR: '01',
                            folio: '107'
                        ),
                    ],
                    tipoCambioP: '1'
                ),
            ],
            fecha: new \DateTime('2026-06-04T09:34:42'),
            lugarExpedicion: '06300',
            serie: 'PAG',
            folio: '105'
        );

        $xpath = $this->xpath((new CfdiXmlBuilder())->buildPago($request));

        // Comprobante: atributos fijos del tipo P
        $comprobante = $this->element($xpath, '//c:Comprobante');
        self::assertSame('4.0', $comprobante->getAttribute('Version'));
        self::assertSame('P', $comprobante->getAttribute('TipoDeComprobante'));
        self::assertSame('XXX', $comprobante->getAttribute('Moneda'));
        self::assertSame('0', $comprobante->getAttribute('SubTotal'));
        self::assertSame('0', $comprobante->getAttribute('Total'));
        self::assertSame('PAG', $comprobante->getAttribute('Serie'));
        self::assertSame('105', $comprobante->getAttribute('Folio'));
        // Sin FormaPago, MetodoPago, CondicionesDePago
        self::assertSame('', $comprobante->getAttribute('FormaPago'));
        self::assertSame('', $comprobante->getAttribute('MetodoPago'));

        // Namespace pago20 declarado en el Comprobante
        self::assertStringContainsString(
            'http://www.sat.gob.mx/Pagos20',
            $comprobante->lookupNamespaceUri('pago20') ?? ''
        );

        // Concepto fijo
        $concepto = $this->element($xpath, '//c:Concepto');
        self::assertSame('84111506', $concepto->getAttribute('ClaveProdServ'));
        self::assertSame('ACT', $concepto->getAttribute('ClaveUnidad'));
        self::assertSame('Pago', $concepto->getAttribute('Descripcion'));
        self::assertSame('0', $concepto->getAttribute('ValorUnitario'));
        self::assertSame('0', $concepto->getAttribute('Importe'));
        self::assertSame('01', $concepto->getAttribute('ObjetoImp'));

        // pago20:Totales
        $totales = $this->element($xpath, '//p:Pagos/p:Totales');
        self::assertSame('1.00', $totales->getAttribute('MontoTotalPagos'));

        // pago20:Pago
        $pagoNode = $this->element($xpath, '//p:Pago');
        self::assertSame('2026-06-04T09:34:41', $pagoNode->getAttribute('FechaPago'));
        self::assertSame('01', $pagoNode->getAttribute('FormaDePagoP'));
        self::assertSame('MXN', $pagoNode->getAttribute('MonedaP'));
        self::assertSame('1', $pagoNode->getAttribute('TipoCambioP'));
        self::assertSame('1.00', $pagoNode->getAttribute('Monto'));

        // pago20:DoctoRelacionado
        $docto = $this->element($xpath, '//p:DoctoRelacionado');
        self::assertSame('4EE306E1-59B0-4F0D-BA73-9C3126034CBC', $docto->getAttribute('IdDocumento'));
        self::assertSame('107', $docto->getAttribute('Folio'));
        self::assertSame('MXN', $docto->getAttribute('MonedaDR'));
        self::assertSame('1', $docto->getAttribute('NumParcialidad'));
        self::assertSame('1.00', $docto->getAttribute('ImpSaldoAnt'));
        self::assertSame('1.00', $docto->getAttribute('ImpPagado'));
        self::assertSame('0.00', $docto->getAttribute('ImpSaldoInsoluto'));
        self::assertSame('01', $docto->getAttribute('ObjetoImpDR'));
    }

    public function testTotalesConMultiplesPagosSeCalculanCorrectamente(): void
    {
        $request = $this->pagoRequest([
            new Pago(new \DateTime(), '03', 'MXN', '1500.00', [], '1'),
            new Pago(new \DateTime(), '03', 'MXN', '2000.50', [], '1'),
        ]);

        $xpath = $this->xpath((new CfdiXmlBuilder())->buildPago($request));

        $totales = $this->element($xpath, '//p:Pagos/p:Totales');
        self::assertSame('3500.50', $totales->getAttribute('MontoTotalPagos'));
    }

    public function testComprobanteNoTieneSelloNiCertificadoNiTimbre(): void
    {
        $xml = (new CfdiXmlBuilder())->buildPago($this->pagoRequest([]));
        $xpath = $this->xpath($xml);

        $comprobante = $this->element($xpath, '//c:Comprobante');
        self::assertFalse($comprobante->hasAttribute('Sello'));
        self::assertFalse($comprobante->hasAttribute('NoCertificado'));
        self::assertFalse($comprobante->hasAttribute('Certificado'));

        $timbre = $xpath->query('//c:Complemento/tfd:TimbreFiscalDigital', null);
        self::assertNotFalse($timbre);
        self::assertSame(0, $timbre->length);
    }

    public function testSchemaLocationIncluyePago20(): void
    {
        $xml = (new CfdiXmlBuilder())->buildPago($this->pagoRequest([]));

        self::assertStringContainsString(
            'http://www.sat.gob.mx/Pagos20 http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos20.xsd',
            $xml
        );
    }

    /**
     * @param array<Pago> $pagos
     */
    private function pagoRequest(array $pagos): PagoRequest
    {
        return new PagoRequest(
            emisor: new Emisor(rfc: 'KFR250210TQ1', nombre: 'EMISOR', regimenFiscal: '601', cp: '06300'),
            receptor: new Receptor(
                rfc: 'XAXX010101000',
                nombre: 'RECEPTOR',
                usoCfdi: 'CP01',
                domicilioFiscalReceptor: '06300',
                regimenFiscalReceptor: '616'
            ),
            pagos: $pagos,
            fecha: new \DateTime('2026-06-04T09:34:42'),
            lugarExpedicion: '06300'
        );
    }

    private function xpath(string $xml): DOMXPath
    {
        $dom = new DOMDocument();
        self::assertNotEmpty($xml, 'El XML generado no debe estar vacío');
        self::assertTrue($dom->loadXML($xml), 'XML generado debe ser parseable');

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('c', self::CFDI_NS);
        $xpath->registerNamespace('p', self::PAGO20_NS);
        $xpath->registerNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');

        return $xpath;
    }

    private function element(DOMXPath $xpath, string $query): DOMElement
    {
        $nodes = $xpath->query($query);
        self::assertNotFalse($nodes);
        self::assertGreaterThan(0, $nodes->length, "No se encontró: {$query}");

        $node = $nodes->item(0);
        self::assertInstanceOf(DOMElement::class, $node);

        return $node;
    }
}
