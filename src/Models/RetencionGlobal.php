<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Retención global de impuestos (resumen a nivel comprobante).
 *
 * En CFDI 4.0 el nodo Impuestos > Retenciones > Retencion a nivel comprobante
 * solo admite los atributos Impuesto e Importe. La base, el tipo de factor y la
 * tasa se expresan únicamente a nivel concepto.
 */
class RetencionGlobal
{
    /**
     * @param string $impuesto Clave del impuesto retenido
     * @param float $importe Importe total retenido para ese impuesto
     */
    public function __construct(
        private string $impuesto,
        private float $importe
    ) {
    }

    public function getImpuesto(): string
    {
        return $this->impuesto;
    }

    public function getImporte(): float
    {
        return $this->importe;
    }

    /**
     * Convertir a array para serialización JSON
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'impuesto' => $this->impuesto,
            'importe' => $this->importe,
        ];
    }
}
