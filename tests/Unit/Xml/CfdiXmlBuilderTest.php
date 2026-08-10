<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Unit\Xml;

use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Models\Cfdi4Request;
use TecnoFact\Sdk\Models\Concepto;
use TecnoFact\Sdk\Models\Emisor;
use TecnoFact\Sdk\Models\Impuestos;
use TecnoFact\Sdk\Models\ImpuestosConcepto;
use TecnoFact\Sdk\Models\InformacionGlobal;
use TecnoFact\Sdk\Models\Receptor;
use TecnoFact\Sdk\Models\Retencion;
use TecnoFact\Sdk\Models\RetencionGlobal;
use TecnoFact\Sdk\Models\Traslado;
use TecnoFact\Sdk\Models\TrasladoGlobal;
use TecnoFact\Sdk\Xml\CfdiXmlBuilder;

final class CfdiXmlBuilderTest extends TestCase
{
    private const NS = 'http://www.sat.gob.mx/cfd/4';

    public function testBuildsPublicoEnGeneralConRetencion(): void
    {
        $request = new Cfdi4Request(
            emisor: new Emisor(
                rfc: 'KFR250210TQ1',
                nombre: 'KBA FILTERS Y REFACCIONES',
                regimenFiscal: '601',
                cp: '20000'
            ),
            receptor: new Receptor(
                rfc: 'XAXX010101000',
                nombre: 'PÚBLICO EN GENERAL',
                usoCfdi: 'S01',
                domicilioFiscalReceptor: '20000',
                regimenFiscalReceptor: '616'
            ),
            conceptos: [
                new Concepto(
                    claveProdServ: '50211503',
                    cantidad: 1.0,
                    claveUnidad: 'H87',
                    unidad: 'Pieza',
                    descripcion: 'Cigarros',
                    valorUnitario: 1.00,
                    importe: 1.00,
                    objetoImp: '02',
                    impuestos: new ImpuestosConcepto(
                        retenciones: [
                            new Retencion(
                                base: 1.00,
                                impuesto: '001',
                                tipoFactor: 'Tasa',
                                tasaOCuota: '0.100000',
                                importe: 0.10
                            ),
                        ]
                    ),
                    noIdentificacion: 'UT421511'
                ),
            ],
            formaPago: '99',
            metodoPago: 'PUE',
            tipoComprobante: 'I',
            lugarExpedicion: '20000',
            subTotal: 1.00,
            total: 0.90,
            fecha: new \DateTime('2023-07-30T20:21:50'),
            moneda: 'MXN',
            serie: 'A',
            folio: '2210111113',
            tipoCambio: 1.0,
            impuestos: new Impuestos(
                totalImpuestosRetenidos: 0.10,
                retenciones: [new RetencionGlobal('001', 0.10)]
            ),
            confirmacion: null,
            cfdiRelacionados: null,
            exportacion: '01',
            condicionesPago: 'CondicionesDePago',
            descuento: null,
            informacionGlobal: new InformacionGlobal('01', '08', '2025')
        );

        $xpath = $this->xpath((new CfdiXmlBuilder())->build($request));

        $comprobante = $this->element($xpath, '//c:Comprobante');
        self::assertSame('4.0', $comprobante->getAttribute('Version'));
        self::assertSame('A', $comprobante->getAttribute('Serie'));
        self::assertSame('2210111113', $comprobante->getAttribute('Folio'));
        self::assertSame('I', $comprobante->getAttribute('TipoDeComprobante'));
        self::assertSame('1.00', $comprobante->getAttribute('SubTotal'));
        self::assertSame('0.90', $comprobante->getAttribute('Total'));
        self::assertSame('1', $comprobante->getAttribute('TipoCambio'));
        self::assertSame('PUE', $comprobante->getAttribute('MetodoPago'));
        self::assertSame('CondicionesDePago', $comprobante->getAttribute('CondicionesDePago'));

        // InformacionGlobal con atributo acentuado "Año".
        $info = $this->element($xpath, '//c:Comprobante/c:InformacionGlobal');
        self::assertSame('01', $info->getAttribute('Periodicidad'));
        self::assertSame('2025', $info->getAttribute('Año'));

        // Emisor NO debe tener el atributo Cp (no existe en CFDI 4.0).
        $emisor = $this->element($xpath, '//c:Comprobante/c:Emisor');
        self::assertFalse($emisor->hasAttribute('Cp'));
        self::assertSame('601', $emisor->getAttribute('RegimenFiscal'));

        $receptor = $this->element($xpath, '//c:Comprobante/c:Receptor');
        self::assertSame('S01', $receptor->getAttribute('UsoCFDI'));

        // Retención a nivel concepto: lleva Base, TipoFactor, TasaOCuota e Importe.
        $retConcepto = $this->element($xpath, '//c:Concepto/c:Impuestos/c:Retenciones/c:Retencion');
        self::assertSame('0.100000', $retConcepto->getAttribute('TasaOCuota'));
        self::assertSame('0.10', $retConcepto->getAttribute('Importe'));

        // Retención a nivel comprobante: SOLO Impuesto e Importe.
        $retGlobal = $this->element($xpath, '//c:Comprobante/c:Impuestos/c:Retenciones/c:Retencion');
        self::assertSame('001', $retGlobal->getAttribute('Impuesto'));
        self::assertSame('0.10', $retGlobal->getAttribute('Importe'));
        self::assertFalse($retGlobal->hasAttribute('TipoFactor'));
        self::assertFalse($retGlobal->hasAttribute('TasaOCuota'));
        self::assertFalse($retGlobal->hasAttribute('Base'));

        $impuestosGlobal = $this->element($xpath, '//c:Comprobante/c:Impuestos');
        self::assertSame('0.10', $impuestosGlobal->getAttribute('TotalImpuestosRetenidos'));
    }

    public function testBuildsTrasladoIvaConBaseGlobal(): void
    {
        $request = $this->baseIngreso(
            new ImpuestosConcepto(
                traslados: [
                    new Traslado(
                        base: 1000.00,
                        impuesto: '002',
                        tipoFactor: 'Tasa',
                        tasaOCuota: '0.160000',
                        importe: 160.00
                    ),
                ]
            ),
            new Impuestos(
                totalImpuestosTrasladados: 160.00,
                traslados: [
                    new TrasladoGlobal(
                        base: 1000.00,
                        impuesto: '002',
                        tipoFactor: 'Tasa',
                        tasaOCuota: '0.160000',
                        importe: 160.00
                    ),
                ]
            )
        );

        $xpath = $this->xpath((new CfdiXmlBuilder())->build($request));

        $trasladoGlobal = $this->element($xpath, '//c:Comprobante/c:Impuestos/c:Traslados/c:Traslado');
        // Base es REQUERIDO en el traslado global de CFDI 4.0.
        self::assertSame('1000.00', $trasladoGlobal->getAttribute('Base'));
        self::assertSame('002', $trasladoGlobal->getAttribute('Impuesto'));
        self::assertSame('Tasa', $trasladoGlobal->getAttribute('TipoFactor'));
        self::assertSame('0.160000', $trasladoGlobal->getAttribute('TasaOCuota'));
        self::assertSame('160.00', $trasladoGlobal->getAttribute('Importe'));

        $impuestos = $this->element($xpath, '//c:Comprobante/c:Impuestos');
        self::assertSame('160.00', $impuestos->getAttribute('TotalImpuestosTrasladados'));
    }

    public function testExentoOmiteTasaEImporte(): void
    {
        $request = $this->baseIngreso(
            new ImpuestosConcepto(
                traslados: [
                    new Traslado(
                        base: 100.00,
                        impuesto: '002',
                        tipoFactor: 'Exento',
                        tasaOCuota: null,
                        importe: 0.0
                    ),
                ]
            ),
            null
        );

        $xpath = $this->xpath((new CfdiXmlBuilder())->build($request));

        $traslado = $this->element($xpath, '//c:Concepto/c:Impuestos/c:Traslados/c:Traslado');
        self::assertSame('100.00', $traslado->getAttribute('Base'));
        self::assertSame('Exento', $traslado->getAttribute('TipoFactor'));
        self::assertFalse($traslado->hasAttribute('TasaOCuota'));
        self::assertFalse($traslado->hasAttribute('Importe'));
    }

    public function testEmisorMapeaFacAtrAdquirente(): void
    {
        $request = $this->baseIngreso(null, null, new Emisor(
            rfc: 'KFR250210TQ1',
            nombre: 'KBA FILTERS Y REFACCIONES',
            regimenFiscal: '601',
            cp: '20000',
            facAtrAdm: '0000012345'
        ));

        $xpath = $this->xpath((new CfdiXmlBuilder())->build($request));

        $emisor = $this->element($xpath, '//c:Comprobante/c:Emisor');
        self::assertSame('0000012345', $emisor->getAttribute('FacAtrAdquirente'));
        self::assertFalse($emisor->hasAttribute('FacAtrAdm'));
    }

    public function testNoIncluyeSelloNiCertificadoNiComplemento(): void
    {
        $xml = (new CfdiXmlBuilder())->build($this->baseIngreso(null, null));
        $xpath = $this->xpath($xml);

        $comprobante = $this->element($xpath, '//c:Comprobante');
        self::assertFalse($comprobante->hasAttribute('Sello'));
        self::assertFalse($comprobante->hasAttribute('NoCertificado'));
        self::assertFalse($comprobante->hasAttribute('Certificado'));

        $complementos = $xpath->query('//c:Complemento');
        self::assertNotFalse($complementos);
        self::assertSame(0, $complementos->length);
    }

    public function testGeneraDeclaracionXmlUtf8(): void
    {
        $xml = (new CfdiXmlBuilder())->build($this->baseIngreso(null, null));

        self::assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        self::assertStringContainsString('xmlns:cfdi="http://www.sat.gob.mx/cfd/4"', $xml);
        self::assertStringContainsString('xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd"', $xml);
    }

    public function testConceptoConInformacionAduaneraCuentaPredialYPartes(): void
    {
        $concepto = new Concepto(
            claveProdServ: '01010101',
            cantidad: 2.0,
            claveUnidad: 'H87',
            unidad: 'Pieza',
            descripcion: 'KIT de herramientas importado',
            valorUnitario: 500.00,
            importe: 1000.00,
            objetoImp: '01',
            impuestos: null,
            noIdentificacion: 'KIT-1',
            cuentaPredial: new \TecnoFact\Sdk\Models\CuentaPredial('15956011002'),
            partes: [
                new \TecnoFact\Sdk\Models\Parte(
                    claveProdServ: '41116401',
                    cantidad: 4.0,
                    descripcion: 'Martillos de impacto',
                    unidad: 'Pieza',
                    noIdentificacion: 'MRT-1',
                    valorUnitario: 100.00,
                    importe: 400.00
                ),
            ],
            informacionAduanera: new \TecnoFact\Sdk\Models\InformacionAduanera('10 47 3807 8003832')
        );

        $request = new Cfdi4Request(
            emisor: new Emisor(rfc: 'KFR250210TQ1', nombre: 'EMISOR', regimenFiscal: '601', cp: '20000'),
            receptor: new Receptor(
                rfc: 'XAXX010101000',
                nombre: 'PUBLICO EN GENERAL',
                usoCfdi: 'S01',
                domicilioFiscalReceptor: '20000',
                regimenFiscalReceptor: '616'
            ),
            conceptos: [$concepto],
            formaPago: '01',
            metodoPago: 'PUE',
            tipoComprobante: 'I',
            lugarExpedicion: '20000',
            subTotal: 1000.00,
            total: 1000.00,
            fecha: new \DateTime('2025-02-04T13:38:31')
        );

        $xpath = $this->xpath((new CfdiXmlBuilder())->build($request));

        $ia = $this->element($xpath, '//c:Concepto/c:InformacionAduanera');
        self::assertSame('10 47 3807 8003832', $ia->getAttribute('NumeroPedimento'));

        $cp = $this->element($xpath, '//c:Concepto/c:CuentaPredial');
        self::assertSame('15956011002', $cp->getAttribute('Numero'));

        $parte = $this->element($xpath, '//c:Concepto/c:Parte');
        self::assertSame('41116401', $parte->getAttribute('ClaveProdServ'));
        self::assertSame('MRT-1', $parte->getAttribute('NoIdentificacion'));
        self::assertSame('4', $parte->getAttribute('Cantidad'));
        self::assertSame('Martillos de impacto', $parte->getAttribute('Descripcion'));
        self::assertSame('400.00', $parte->getAttribute('Importe'));

        // Orden XSD dentro del Concepto: InformacionAduanera -> CuentaPredial -> Parte.
        $hijos = [];
        $conceptoNode = $this->element($xpath, '//c:Concepto');
        foreach ($conceptoNode->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $hijos[] = $child->localName;
            }
        }
        self::assertSame(['InformacionAduanera', 'CuentaPredial', 'Parte'], $hijos);
    }

    private function baseIngreso(
        ?ImpuestosConcepto $impuestosConcepto,
        ?Impuestos $impuestosGlobal,
        ?Emisor $emisor = null
    ): Cfdi4Request {
        return new Cfdi4Request(
            emisor: $emisor ?? new Emisor(
                rfc: 'KFR250210TQ1',
                nombre: 'KBA FILTERS Y REFACCIONES',
                regimenFiscal: '601',
                cp: '20000'
            ),
            receptor: new Receptor(
                rfc: 'XAXX010101000',
                nombre: 'PÚBLICO EN GENERAL',
                usoCfdi: 'S01',
                domicilioFiscalReceptor: '20000',
                regimenFiscalReceptor: '616'
            ),
            conceptos: [
                new Concepto(
                    claveProdServ: '01010101',
                    cantidad: 1.0,
                    claveUnidad: 'H87',
                    unidad: 'Pieza',
                    descripcion: 'Producto',
                    valorUnitario: 1000.00,
                    importe: 1000.00,
                    objetoImp: '02',
                    impuestos: $impuestosConcepto
                ),
            ],
            formaPago: '01',
            metodoPago: 'PUE',
            tipoComprobante: 'I',
            lugarExpedicion: '20000',
            subTotal: 1000.00,
            total: 1160.00,
            fecha: new \DateTime('2023-07-30T20:21:50'),
            impuestos: $impuestosGlobal
        );
    }

    private function xpath(string $xml): DOMXPath
    {
        $dom = new DOMDocument();
        self::assertNotEmpty($xml, 'El XML generado no debe estar vacío');
        self::assertTrue($dom->loadXML($xml), 'El XML generado debe ser válido y parseable');

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('c', self::NS);

        return $xpath;
    }

    private function element(DOMXPath $xpath, string $query): DOMElement
    {
        $nodes = $xpath->query($query);
        self::assertNotFalse($nodes);
        self::assertGreaterThan(0, $nodes->length, "No se encontró el nodo: {$query}");

        $node = $nodes->item(0);
        self::assertInstanceOf(DOMElement::class, $node);

        return $node;
    }
}
