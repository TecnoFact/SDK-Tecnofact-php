<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Traslado de impuestos (ej. IVA, IEPS)
 */
class Traslado
{
    /**
     * @param float $base Base del impuesto
     * @param string $impuesto Clave del impuesto (ej. 002 para IVA)
     * @param string $tipoFactor Tipo de factor (Tasa, Cuota, Exento)
     * @param string|null $tasaOCuota Tasa o cuota del impuesto
     * @param float $importe Importe del impuesto
     */
    public function __construct(
        private float $base,
        private string $impuesto,
        private string $tipoFactor,
        private ?string $tasaOCuota,
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

    public function getTasaOCuota(): ?string
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
        $data = [
            'base' => $this->base,
            'impuesto' => $this->impuesto,
            'tipo_factor' => $this->tipoFactor,
            'importe' => $this->importe,
        ];

        if ($this->tasaOCuota !== null) {
            $data['tasa_o_cuota'] = $this->tasaOCuota;
        }

        return $data;
    }
}