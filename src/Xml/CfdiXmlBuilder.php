<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Xml;

use DOMDocument;
use DOMElement;
use TecnoFact\Sdk\Models\Cfdi4Request;
use TecnoFact\Sdk\Models\Concepto;
use TecnoFact\Sdk\Models\Emisor;
use TecnoFact\Sdk\Models\ImpuestosConcepto;
use TecnoFact\Sdk\Models\Parte;
use TecnoFact\Sdk\Models\Receptor;
use TecnoFact\Sdk\Models\Retencion;
use TecnoFact\Sdk\Models\Traslado;

/**
 * Construye el XML del CFDI 4.0 a partir de los modelos del SDK.
 *
 * El XML se genera SIN sellar: no incluye los atributos Sello, NoCertificado ni
 * Certificado, ni el nodo Complemento > TimbreFiscalDigital. El sellado (con el
 * CSD del emisor) y el timbrado ante el SAT los realiza el servicio del panel
 * de TecnoFact. Este builder es puramente estructural.
 *
 * Alcance v1: comprobantes de tipo "I" (Ingreso) y "E" (Egreso), con conceptos,
 * traslados/retenciones, descuentos, CfdiRelacionados, InformacionGlobal y, a
 * nivel concepto, InformacionAduanera, CuentaPredial y Parte.
 */
final class CfdiXmlBuilder
{
    private const CFDI_NS = 'http://www.sat.gob.mx/cfd/4';
    private const XSI_NS = 'http://www.w3.org/2001/XMLSchema-instance';
    private const SCHEMA_LOCATION = 'http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd';
    private const VERSION = '4.0';

    public function build(Cfdi4Request $cfdi): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $comprobante = $dom->createElementNS(self::CFDI_NS, 'cfdi:Comprobante');
        $dom->appendChild($comprobante);

        $comprobante->setAttributeNS(self::XSI_NS, 'xsi:schemaLocation', self::SCHEMA_LOCATION);

        $this->buildComprobanteAttributes($comprobante, $cfdi);

        if ($cfdi->getInformacionGlobal() !== null) {
            $this->appendInformacionGlobal($dom, $comprobante, $cfdi->getInformacionGlobal());
        }

        if ($cfdi->getCfdiRelacionados() !== null) {
            $this->appendCfdiRelacionados($dom, $comprobante, $cfdi->getCfdiRelacionados());
        }

        $this->appendEmisor($dom, $comprobante, $cfdi->getEmisor());
        $this->appendReceptor($dom, $comprobante, $cfdi->getReceptor());
        $this->appendConceptos($dom, $comprobante, $cfdi->getConceptos());

        if ($cfdi->getImpuestos() !== null && ! $this->esTrasladoOPago($cfdi->getTipoComprobante())) {
            $this->appendImpuestosGlobales($dom, $comprobante, $cfdi->getImpuestos());
        }

        $xml = $dom->saveXML();

        return $xml === false ? '' : $xml;
    }

    private function buildComprobanteAttributes(DOMElement $node, Cfdi4Request $cfdi): void
    {
        $tipo = $cfdi->getTipoComprobante();

        $node->setAttribute('Version', self::VERSION);

        if ($cfdi->getSerie() !== null) {
            $node->setAttribute('Serie', $cfdi->getSerie());
        }

        if ($cfdi->getFolio() !== null) {
            $node->setAttribute('Folio', $cfdi->getFolio());
        }

        $node->setAttribute('Fecha', $cfdi->getFecha()->format('Y-m-d\TH:i:s'));

        // FormaPago no aplica para Nómina.
        if ($tipo !== 'N') {
            $node->setAttribute('FormaPago', $cfdi->getFormaPago());
        }

        // CondicionesDePago solo para Ingreso o Egreso.
        if (($tipo === 'I' || $tipo === 'E') && $cfdi->getCondicionesPago() !== null) {
            $node->setAttribute('CondicionesDePago', $cfdi->getCondicionesPago());
        }

        $node->setAttribute('SubTotal', $this->importe($cfdi->getSubTotal()));

        if ($cfdi->getDescuento() !== null) {
            $node->setAttribute('Descuento', $this->importe($cfdi->getDescuento()));
        }

        $node->setAttribute('Moneda', $cfdi->getMoneda());

        if ($cfdi->getTipoCambio() !== null) {
            $node->setAttribute('TipoCambio', $this->cantidad($cfdi->getTipoCambio()));
        }

        $node->setAttribute('Total', $this->importe($cfdi->getTotal()));
        $node->setAttribute('TipoDeComprobante', $tipo);
        $node->setAttribute('Exportacion', $cfdi->getExportacion() ?? '01');

        // MetodoPago no aplica para Traslado ni Pago.
        if (! $this->esTrasladoOPago($tipo)) {
            $node->setAttribute('MetodoPago', $cfdi->getMetodoPago());
        }

        $node->setAttribute('LugarExpedicion', $cfdi->getLugarExpedicion());

        if ($cfdi->getConfirmacion() !== null) {
            $node->setAttribute('Confirmacion', $cfdi->getConfirmacion());
        }
    }

    private function appendInformacionGlobal(
        DOMDocument $dom,
        DOMElement $parent,
        \TecnoFact\Sdk\Models\InformacionGlobal $info
    ): void {
        $node = $dom->createElementNS(self::CFDI_NS, 'cfdi:InformacionGlobal');
        $node->setAttribute('Periodicidad', $info->getPeriodicidad());
        $node->setAttribute('Meses', $info->getMeses());
        $node->setAttribute('Año', $info->getAnio());
        $parent->appendChild($node);
    }

    private function appendCfdiRelacionados(
        DOMDocument $dom,
        DOMElement $parent,
        \TecnoFact\Sdk\Models\CfdiRelacionados $rel
    ): void {
        $node = $dom->createElementNS(self::CFDI_NS, 'cfdi:CfdiRelacionados');
        $node->setAttribute('TipoRelacion', $rel->getTipoRelacion());

        foreach ($rel->getUuids() as $uuid) {
            $child = $dom->createElementNS(self::CFDI_NS, 'cfdi:CfdiRelacionado');
            $child->setAttribute('UUID', $uuid);
            $node->appendChild($child);
        }

        $parent->appendChild($node);
    }

    private function appendEmisor(DOMDocument $dom, DOMElement $parent, Emisor $emisor): void
    {
        $node = $dom->createElementNS(self::CFDI_NS, 'cfdi:Emisor');
        $node->setAttribute('Rfc', $emisor->getRfc());
        $node->setAttribute('Nombre', $emisor->getNombre());
        $node->setAttribute('RegimenFiscal', $emisor->getRegimenFiscal());

        if ($emisor->getFacAtrAdm() !== null) {
            $node->setAttribute('FacAtrAdquirente', $emisor->getFacAtrAdm());
        }

        $parent->appendChild($node);
    }

    private function appendReceptor(DOMDocument $dom, DOMElement $parent, Receptor $receptor): void
    {
        $node = $dom->createElementNS(self::CFDI_NS, 'cfdi:Receptor');
        $node->setAttribute('Rfc', $receptor->getRfc());
        $node->setAttribute('Nombre', $receptor->getNombre());
        $node->setAttribute('DomicilioFiscalReceptor', $receptor->getDomicilioFiscalReceptor());

        if ($receptor->getResidenciaFiscal() !== null) {
            $node->setAttribute('ResidenciaFiscal', $receptor->getResidenciaFiscal());
        }

        if ($receptor->getNumRegIdTrib() !== null) {
            $node->setAttribute('NumRegIdTrib', $receptor->getNumRegIdTrib());
        }

        $node->setAttribute('RegimenFiscalReceptor', $receptor->getRegimenFiscalReceptor());
        $node->setAttribute('UsoCFDI', $receptor->getUsoCfdi());

        $parent->appendChild($node);
    }

    /**
     * @param array<Concepto> $conceptos
     */
    private function appendConceptos(DOMDocument $dom, DOMElement $parent, array $conceptos): void
    {
        $node = $dom->createElementNS(self::CFDI_NS, 'cfdi:Conceptos');

        foreach ($conceptos as $concepto) {
            $node->appendChild($this->buildConcepto($dom, $concepto));
        }

        $parent->appendChild($node);
    }

    private function buildConcepto(DOMDocument $dom, Concepto $concepto): DOMElement
    {
        $node = $dom->createElementNS(self::CFDI_NS, 'cfdi:Concepto');

        $node->setAttribute('ClaveProdServ', $concepto->getClaveProdServ());

        if ($concepto->getNoIdentificacion() !== null) {
            $node->setAttribute('NoIdentificacion', $concepto->getNoIdentificacion());
        }

        $node->setAttribute('Cantidad', $this->cantidad($concepto->getCantidad()));
        $node->setAttribute('ClaveUnidad', $concepto->getClaveUnidad());

        if ($concepto->getUnidad() !== null) {
            $node->setAttribute('Unidad', $concepto->getUnidad());
        }

        $node->setAttribute('Descripcion', $concepto->getDescripcion());
        $node->setAttribute('ValorUnitario', $this->importe($concepto->getValorUnitario()));
        $node->setAttribute('Importe', $this->importe($concepto->getImporte()));

        if ($concepto->getDescuento() !== null) {
            $node->setAttribute('Descuento', $this->importe($concepto->getDescuento()));
        }

        $node->setAttribute('ObjetoImp', $concepto->getObjetoImp());

        // Solo se desglosan impuestos cuando ObjetoImp = "02" (Sí objeto de impuesto).
        if ($concepto->getObjetoImp() === '02' && $concepto->getImpuestos() !== null) {
            $impuestos = $this->buildConceptoImpuestos($dom, $concepto->getImpuestos());

            if ($impuestos !== null) {
                $node->appendChild($impuestos);
            }
        }

        // Orden XSD del concepto: Impuestos, InformacionAduanera, CuentaPredial, Parte.
        $informacionAduanera = $concepto->getInformacionAduanera();
        if ($informacionAduanera !== null) {
            $ia = $dom->createElementNS(self::CFDI_NS, 'cfdi:InformacionAduanera');
            $ia->setAttribute('NumeroPedimento', $informacionAduanera->getNumeroPedimento());
            $node->appendChild($ia);
        }

        $cuentaPredial = $concepto->getCuentaPredial();
        if ($cuentaPredial !== null) {
            $cp = $dom->createElementNS(self::CFDI_NS, 'cfdi:CuentaPredial');
            $cp->setAttribute('Numero', $cuentaPredial->getNumero());
            $node->appendChild($cp);
        }

        $partes = $concepto->getPartes();
        if ($partes !== null) {
            foreach ($partes as $parte) {
                $node->appendChild($this->buildParte($dom, $parte));
            }
        }

        return $node;
    }

    private function buildParte(DOMDocument $dom, Parte $parte): DOMElement
    {
        $node = $dom->createElementNS(self::CFDI_NS, 'cfdi:Parte');
        $node->setAttribute('ClaveProdServ', $parte->getClaveProdServ());

        if ($parte->getNoIdentificacion() !== null) {
            $node->setAttribute('NoIdentificacion', $parte->getNoIdentificacion());
        }

        $node->setAttribute('Cantidad', $this->cantidad($parte->getCantidad()));

        if ($parte->getUnidad() !== null) {
            $node->setAttribute('Unidad', $parte->getUnidad());
        }

        $node->setAttribute('Descripcion', $parte->getDescripcion());

        if ($parte->getValorUnitario() !== null) {
            $node->setAttribute('ValorUnitario', $this->importe($parte->getValorUnitario()));
        }

        if ($parte->getImporte() !== null) {
            $node->setAttribute('Importe', $this->importe($parte->getImporte()));
        }

        return $node;
    }

    private function buildConceptoImpuestos(DOMDocument $dom, ImpuestosConcepto $impuestos): ?DOMElement
    {
        $traslados = $impuestos->getTraslados();
        $retenciones = $impuestos->getRetenciones();

        if (empty($traslados) && empty($retenciones)) {
            return null;
        }

        $node = $dom->createElementNS(self::CFDI_NS, 'cfdi:Impuestos');

        // Orden XSD a nivel concepto: Traslados, luego Retenciones.
        if (! empty($traslados)) {
            $trasladosNode = $dom->createElementNS(self::CFDI_NS, 'cfdi:Traslados');

            foreach ($traslados as $traslado) {
                $trasladosNode->appendChild($this->buildTrasladoConcepto($dom, $traslado));
            }

            $node->appendChild($trasladosNode);
        }

        if (! empty($retenciones)) {
            $retencionesNode = $dom->createElementNS(self::CFDI_NS, 'cfdi:Retenciones');

            foreach ($retenciones as $retencion) {
                $retencionesNode->appendChild($this->buildRetencionConcepto($dom, $retencion));
            }

            $node->appendChild($retencionesNode);
        }

        return $node;
    }

    private function buildTrasladoConcepto(DOMDocument $dom, Traslado $traslado): DOMElement
    {
        $node = $dom->createElementNS(self::CFDI_NS, 'cfdi:Traslado');
        $node->setAttribute('Base', $this->importe($traslado->getBase()));
        $node->setAttribute('Impuesto', $traslado->getImpuesto());
        $node->setAttribute('TipoFactor', $traslado->getTipoFactor());

        // En Exento no se registran TasaOCuota ni Importe.
        if ($traslado->getTipoFactor() !== 'Exento') {
            if ($traslado->getTasaOCuota() !== null) {
                $node->setAttribute('TasaOCuota', $traslado->getTasaOCuota());
            }

            $node->setAttribute('Importe', $this->importe($traslado->getImporte()));
        }

        return $node;
    }

    private function buildRetencionConcepto(DOMDocument $dom, Retencion $retencion): DOMElement
    {
        $node = $dom->createElementNS(self::CFDI_NS, 'cfdi:Retencion');
        $node->setAttribute('Base', $this->importe($retencion->getBase()));
        $node->setAttribute('Impuesto', $retencion->getImpuesto());
        $node->setAttribute('TipoFactor', $retencion->getTipoFactor());
        $node->setAttribute('TasaOCuota', $retencion->getTasaOCuota());
        $node->setAttribute('Importe', $this->importe($retencion->getImporte()));

        return $node;
    }

    private function appendImpuestosGlobales(
        DOMDocument $dom,
        DOMElement $parent,
        \TecnoFact\Sdk\Models\Impuestos $impuestos
    ): void {
        $node = $dom->createElementNS(self::CFDI_NS, 'cfdi:Impuestos');

        $retenciones = $impuestos->getRetenciones();
        $traslados = $impuestos->getTraslados();

        if ($impuestos->getTotalImpuestosRetenidos() !== null) {
            $node->setAttribute('TotalImpuestosRetenidos', $this->importe($impuestos->getTotalImpuestosRetenidos()));
        }

        if ($impuestos->getTotalImpuestosTrasladados() !== null) {
            $node->setAttribute('TotalImpuestosTrasladados', $this->importe($impuestos->getTotalImpuestosTrasladados()));
        }

        // Orden XSD a nivel comprobante: Retenciones, luego Traslados.
        if ($retenciones !== null && ! empty($retenciones)) {
            $retencionesNode = $dom->createElementNS(self::CFDI_NS, 'cfdi:Retenciones');

            foreach ($retenciones as $retencion) {
                $child = $dom->createElementNS(self::CFDI_NS, 'cfdi:Retencion');
                $child->setAttribute('Impuesto', $retencion->getImpuesto());
                $child->setAttribute('Importe', $this->importe($retencion->getImporte()));
                $retencionesNode->appendChild($child);
            }

            $node->appendChild($retencionesNode);
        }

        if ($traslados !== null && ! empty($traslados)) {
            $trasladosNode = $dom->createElementNS(self::CFDI_NS, 'cfdi:Traslados');

            foreach ($traslados as $traslado) {
                $child = $dom->createElementNS(self::CFDI_NS, 'cfdi:Traslado');
                $child->setAttribute('Base', $this->importe($traslado->getBase()));
                $child->setAttribute('Impuesto', $traslado->getImpuesto());
                $child->setAttribute('TipoFactor', $traslado->getTipoFactor());

                if ($traslado->getTipoFactor() !== 'Exento') {
                    if ($traslado->getTasaOCuota() !== null) {
                        $child->setAttribute('TasaOCuota', $traslado->getTasaOCuota());
                    }

                    if ($traslado->getImporte() !== null) {
                        $child->setAttribute('Importe', $this->importe($traslado->getImporte()));
                    }
                }

                $trasladosNode->appendChild($child);
            }

            $node->appendChild($trasladosNode);
        }

        $parent->appendChild($node);
    }

    private function esTrasladoOPago(string $tipoComprobante): bool
    {
        return $tipoComprobante === 'T' || $tipoComprobante === 'P';
    }

    /**
     * Formatea montos a los decimales de la moneda (2 para MXN/USD).
     * El redondeo se aplica aquí, como paso final del cálculo.
     */
    private function importe(?float $value): string
    {
        return number_format($value ?? 0.0, 2, '.', '');
    }

    /**
     * Formatea cantidades y tipo de cambio con hasta 6 decimales, omitiendo
     * los ceros no significativos (ambos criterios son aceptados por el SAT).
     */
    private function cantidad(float $value): string
    {
        $formatted = number_format($value, 6, '.', '');

        if (str_contains($formatted, '.')) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
        }

        return $formatted === '' ? '0' : $formatted;
    }
}
