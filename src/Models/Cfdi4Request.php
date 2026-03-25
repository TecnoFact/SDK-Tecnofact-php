<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

use DateTime;

/**
 * Solicitud de timbrado CFDI 4.0
 */
class Cfdi4Request
{
    /**
     * @param Emisor $emisor Datos del emisor
     * @param Receptor $receptor Datos del receptor
     * @param array<Concepto> $conceptos Conceptos de la factura
     * @param string $formaPago Clave de forma de pago
     * @param string $metodoPago Clave de método de pago (PUE, PPD, etc.)
     * @param string $tipoComprobante Tipo de comprobante (I=Ingreso, E=Egreso, T=Traslado, N=Nómina, P=Pago)
     * @param string $lugarExpedicion Código postal del lugar de expedición
     * @param float $subTotal Subtotal de la factura
     * @param float $total Total de la factura
     * @param DateTime $fecha Fecha de emisión
     * @param string $moneda Moneda (ej. MXN)
     * @param string|null $serie Serie del comprobante
     * @param string|null $folio Folio del comprobante
     * @param float|null $tipoCambio Tipo de cambio (si aplica)
     * @param Impuestos|null $impuestos Impuestos globales
     * @param string|null $confirmacion Confirmación (solo para montos > $1,000,000)
     * @param CfdiRelacionados|null $cfdiRelacionados CFDIs relacionados
     * @param string|null $exportacion Indicador de exportación (01=No aplica, 02=Definitiva, etc.)
     * @param string|null $condicionesPago Condiciones de pago
     * @param float|null $descuento Descuento global
     * @param string|null $subTotalConDescuento Subtotal con descuento aplicado
     */
    public function __construct(
        private Emisor $emisor,
        private Receptor $receptor,
        private array $conceptos,
        private string $formaPago,
        private string $metodoPago,
        private string $tipoComprobante,
        private string $lugarExpedicion,
        private float $subTotal,
        private float $total,
        private DateTime $fecha,
        private string $moneda = 'MXN',
        private ?string $serie = null,
        private ?string $folio = null,
        private ?float $tipoCambio = null,
        private ?Impuestos $impuestos = null,
        private ?string $confirmacion = null,
        private ?CfdiRelacionados $cfdiRelacionados = null,
        private ?string $exportacion = '01',
        private ?string $condicionesPago = null,
        private ?float $descuento = null,
        private ?string $subTotalConDescuento = null
    ) {
    }

    public function getEmisor(): Emisor
    {
        return $this->emisor;
    }

    public function getReceptor(): Receptor
    {
        return $this->receptor;
    }

    /**
     * @return array<Concepto>
     */
    public function getConceptos(): array
    {
        return $this->conceptos;
    }

    public function getFormaPago(): string
    {
        return $this->formaPago;
    }

    public function getMetodoPago(): string
    {
        return $this->metodoPago;
    }

    public function getTipoComprobante(): string
    {
        return $this->tipoComprobante;
    }

    public function getLugarExpedicion(): string
    {
        return $this->lugarExpedicion;
    }

    public function getSubTotal(): float
    {
        return $this->subTotal;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getFecha(): DateTime
    {
        return $this->fecha;
    }

    public function getMoneda(): string
    {
        return $this->moneda;
    }

    public function getSerie(): ?string
    {
        return $this->serie;
    }

    public function getFolio(): ?string
    {
        return $this->folio;
    }

    public function getTipoCambio(): ?float
    {
        return $this->tipoCambio;
    }

    public function getImpuestos(): ?Impuestos
    {
        return $this->impuestos;
    }

    public function getConfirmacion(): ?string
    {
        return $this->confirmacion;
    }

    public function getCfdiRelacionados(): ?CfdiRelacionados
    {
        return $this->cfdiRelacionados;
    }

    public function getExportacion(): ?string
    {
        return $this->exportacion;
    }

    public function getCondicionesPago(): ?string
    {
        return $this->condicionesPago;
    }

    public function getDescuento(): ?float
    {
        return $this->descuento;
    }

    public function getSubTotalConDescuento(): ?string
    {
        return $this->subTotalConDescuento;
    }

    /**
     * Convertir a array para serialización JSON
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'emisor' => $this->emisor->toArray(),
            'receptor' => $this->receptor->toArray(),
            'conceptos' => array_map(fn(Concepto $c) => $c->toArray(), $this->conceptos),
            'forma_pago' => $this->formaPago,
            'metodo_pago' => $this->metodoPago,
            'tipo_comprobante' => $this->tipoComprobante,
            'lugar_expedicion' => $this->lugarExpedicion,
            'subtotal' => $this->subTotal,
            'total' => $this->total,
            'fecha' => $this->fecha->format('Y-m-d\TH:i:s'),
            'moneda' => $this->moneda,
        ];

        if ($this->serie !== null) {
            $data['serie'] = $this->serie;
        }

        if ($this->folio !== null) {
            $data['folio'] = $this->folio;
        }

        if ($this->tipoCambio !== null) {
            $data['tipo_cambio'] = $this->tipoCambio;
        }

        if ($this->impuestos !== null) {
            $data['impuestos'] = $this->impuestos->toArray();
        }

        if ($this->confirmacion !== null) {
            $data['confirmacion'] = $this->confirmacion;
        }

        if ($this->cfdiRelacionados !== null) {
            $data['cfdi_relacionados'] = $this->cfdiRelacionados->toArray();
        }

        if ($this->exportacion !== null) {
            $data['exportacion'] = $this->exportacion;
        }

        if ($this->condicionesPago !== null) {
            $data['condiciones_pago'] = $this->condicionesPago;
        }

        if ($this->descuento !== null) {
            $data['descuento'] = $this->descuento;
        }

        return $data;
    }
}