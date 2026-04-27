<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Retención de impuestos
 */
class Retencion
{
    /**
     * @param float $base Base del impuesto
     * @param string $impuesto Clave del impuesto
     * @param string $tipoFactor Tipo de factor
     * @param string $tasaOCuota Tasa o cuota
     * @param float $importe Importe retenido
     */
    public function __construct(
        private float $base,
        private string $impuesto,
        private string $tipoFactor,
        private string $tasaOCuota,
        private float $importe
    ) {
    }

    public function getBase(): float
    {
        return $this->base;
    }

    public function getImpuesto(): string
    {
        return $this->impuesto;
    }

    public function getTipoFactor(): string
    {
        return $this->tipoFactor;
    }

    public function getTasaOCuota(): string
    {
        return $this->tasaOCuota;
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
            'base' => $this->base,
            'impuesto' => $this->impuesto,
            'tipo_factor' => $this->tipoFactor,
            'tasa_o_cuota' => $this->tasaOCuota,
            'importe' => $this->importe,
        ];
    }
}
