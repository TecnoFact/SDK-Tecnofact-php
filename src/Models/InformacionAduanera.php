<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Información aduanera para importaciones
 */
class InformacionAduanera
{
    /**
     * @param string $numeroPedimento Número de pedimento
     */
    public function __construct(
        private string $numeroPedimento
    ) {
    }

    public function getNumeroPedimento(): string
    {
        return $this->numeroPedimento;
    }

    /**
     * Convertir a array para serialización JSON
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'numero_pedimento' => $this->numeroPedimento,
        ];
    }
}
