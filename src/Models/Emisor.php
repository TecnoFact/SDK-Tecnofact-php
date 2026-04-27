<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Representa un emisor de CFDI
 */
class Emisor
{
    /**
     * @param string $rfc RFC del emisor (12-13 caracteres)
     * @param string $nombre Nombre o razón social (hasta 300 caracteres)
     * @param string $regimenFiscal Clave del régimen fiscal (3 dígitos)
     * @param string $cp Código postal del domicilio fiscal (5 dígitos)
     * @param string|null $facAtrAdm Opcional, clave de factibilidad
     */
    public function __construct(
        private string $rfc,
        private string $nombre,
        private string $regimenFiscal,
        private string $cp,
        private ?string $facAtrAdm = null
    ) {
    }

    public function getRfc(): string
    {
        return $this->rfc;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getRegimenFiscal(): string
    {
        return $this->regimenFiscal;
    }

    public function getCp(): string
    {
        return $this->cp;
    }

    public function getFacAtrAdm(): ?string
    {
        return $this->facAtrAdm;
    }

    /**
     * Convertir a array para serialización JSON
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'rfc' => $this->rfc,
            'nombre' => $this->nombre,
            'regimen_fiscal' => $this->regimenFiscal,
            'cp' => $this->cp,
        ];

        if ($this->facAtrAdm !== null) {
            $data['facAtrAdm'] = $this->facAtrAdm;
        }

        return $data;
    }
}
