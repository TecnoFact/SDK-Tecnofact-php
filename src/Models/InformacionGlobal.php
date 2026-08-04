<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Nodo InformacionGlobal del CFDI 4.0.
 *
 * Expresa la información del comprobante global de operaciones con el público
 * en general (facturación global). Solo debe registrarse en ese supuesto.
 */
class InformacionGlobal
{
    /**
     * @param string $periodicidad Clave de la periodicidad (catálogo c_Periodicidad)
     * @param string $meses Clave del/los mes(es) (catálogo c_Meses)
     * @param string $anio Año al que corresponde la información (atributo "Año")
     */
    public function __construct(
        private string $periodicidad,
        private string $meses,
        private string $anio
    ) {
    }

    public function getPeriodicidad(): string
    {
        return $this->periodicidad;
    }

    public function getMeses(): string
    {
        return $this->meses;
    }

    public function getAnio(): string
    {
        return $this->anio;
    }

    /**
     * Convertir a array para serialización JSON
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'periodicidad' => $this->periodicidad,
            'meses' => $this->meses,
            'anio' => $this->anio,
        ];
    }
}
