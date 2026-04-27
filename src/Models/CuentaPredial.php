<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Cuenta predial para bienes inmuebles
 */
class CuentaPredial
{
    /**
     * @param string $numero Número de cuenta predial
     */
    public function __construct(
        private string $numero
    ) {
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    /**
     * Convertir a array para serialización JSON
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'numero' => $this->numero,
        ];
    }
}
