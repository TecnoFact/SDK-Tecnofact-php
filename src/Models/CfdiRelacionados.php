<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * CFDIs relacionados
 */
class CfdiRelacionados
{
    /**
     * @param string $tipoRelacion Tipo de relación (01=Nota de crédito, 02=Nota de débito, etc.)
     * @param array<string> $uuids UUIDs de los CFDIs relacionados
     */
    public function __construct(
        private string $tipoRelacion,
        private array $uuids
    ) {
    }

    public function getTipoRelacion(): string
    {
        return $this->tipoRelacion;
    }

    /**
     * @return array<string>
     */
    public function getUuids(): array
    {
        return $this->uuids;
    }

    /**
     * Convertir a array para serialización JSON
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'tipo_relacion' => $this->tipoRelacion,
            'uuids' => $this->uuids,
        ];
    }
}