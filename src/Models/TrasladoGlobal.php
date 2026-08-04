<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Traslado global de impuestos (resumen a nivel comprobante)
 */
class TrasladoGlobal
{
    /**
     * @param float $base Base del impuesto (requerido en CFDI 4.0)
     * @param string $impuesto Clave del impuesto
     * @param string $tipoFactor Tipo de factor (Tasa, Cuota, Exento)
     * @param string|null $tasaOCuota Tasa o cuota (se omite cuando TipoFactor es Exento)
     * @param float|null $importe Importe (se omite cuando TipoFactor es Exento)
     */
    public function __construct(
        private float $base,
        private string $impuesto,
        private string $tipoFactor,
        private ?string $tasaOCuota = null,
        private ?float $importe = null
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

    public function getImporte(): ?float
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
        ];

        if ($this->tasaOCuota !== null) {
            $data['tasa_o_cuota'] = $this->tasaOCuota;
        }

        if ($this->importe !== null) {
            $data['importe'] = $this->importe;
        }

        return $data;
    }
}
