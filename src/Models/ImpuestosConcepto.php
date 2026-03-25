<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Impuestos aplicables a un concepto
 */
class ImpuestosConcepto
{
    /**
     * @param array<Traslado> $traslados Impuestos trasladados
     * @param array<Retencion> $retenciones Impuestos retenidos
     */
    public function __construct(
        private array $traslados = [],
        private array $retenciones = []
    ) {
    }

    /**
     * @return array<Traslado>
     */
    public function getTraslados(): array
    {
        return $this->traslados;
    }

    /**
     * @return array<Retencion>
     */
    public function getRetenciones(): array
    {
        return $this->retenciones;
    }

    /**
     * Convertir a array para serialización JSON
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if (!empty($this->traslados)) {
            $data['traslados'] = array_map(fn(Traslado $t) => $t->toArray(), $this->traslados);
        }

        if (!empty($this->retenciones)) {
            $data['retenciones'] = array_map(fn(Retencion $r) => $r->toArray(), $this->retenciones);
        }

        return $data;
    }
}