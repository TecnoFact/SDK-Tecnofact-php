<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Traslado global de impuestos
 */
class TrasladoGlobal
{
    /**
     * @param string $impuesto Clave del impuesto
     * @param string $tipoFactor Tipo de factor (Tasa, Cuota, Exento)
     * @param string|null $tasaOCuota Tasa o cuota
     * @param float $importe Importe
     */
    public function __construct(
        private string $impuesto,
        private string $tipoFactor,
        private ?string $tasaOCuota,
        private float $importe
    ) {
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
