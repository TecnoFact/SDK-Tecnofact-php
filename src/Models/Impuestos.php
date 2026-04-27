<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Impuestos globales del CFDI
 */
class Impuestos
{
    /**
     * @param float|null $totalImpuestosRetenidos Total de impuestos retenidos
     * @param float|null $totalImpuestosTrasladados Total de impuestos trasladados
     * @param array<TrasladoGlobal>|null $traslados Traslados globales
     * @param array<RetencionGlobal>|null $retenciones Retenciones globales
     */
    public function __construct(
        private ?float $totalImpuestosRetenidos = null,
        private ?float $totalImpuestosTrasladados = null,
        private ?array $traslados = null,
        private ?array $retenciones = null
    ) {
    }

    public function getTotalImpuestosRetenidos(): ?float
    {
        return $this->totalImpuestosRetenidos;
    }

    public function getTotalImpuestosTrasladados(): ?float
    {
        return $this->totalImpuestosTrasladados;
    }

    /**
     * @return array<TrasladoGlobal>|null
     */
    public function getTraslados(): ?array
    {
        return $this->traslados;
    }

    /**
     * @return array<RetencionGlobal>|null
     */
    public function getRetenciones(): ?array
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

        if ($this->totalImpuestosRetenidos !== null) {
            $data['total_impuestos_retenidos'] = $this->totalImpuestosRetenidos;
        }

        if ($this->totalImpuestosTrasladados !== null) {
            $data['total_impuestos_trasladados'] = $this->totalImpuestosTrasladados;
        }

        if ($this->traslados !== null && ! empty($this->traslados)) {
            $data['traslados'] = array_map(fn (TrasladoGlobal $t) => $t->toArray(), $this->traslados);
        }

        if ($this->retenciones !== null && ! empty($this->retenciones)) {
            $data['retenciones'] = array_map(fn (RetencionGlobal $r) => $r->toArray(), $this->retenciones);
        }

        return $data;
    }
}
