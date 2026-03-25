<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Representa un receptor de CFDI
 */
class Receptor
{
    /**
     * @param string $rfc RFC del receptor (12-13 caracteres)
     * @param string $nombre Nombre o razón social (hasta 300 caracteres)
     * @param string $usoCfdi Clave de uso CFDI (3 caracteres)
     * @param string $domicilioFiscalReceptor Código postal del domicilio fiscal (5 dígitos)
     * @param string $regimenFiscalReceptor Clave del régimen fiscal del receptor (3 dígitos)
     * @param string|null $residenciaFiscal Opcional, clave de residencia fiscal
     * @param string|null $numRegIdTrib Opcional, número de registro de identidad tributaria
     */
    public function __construct(
        private string $rfc,
        private string $nombre,
        private string $usoCfdi,
        private string $domicilioFiscalReceptor,
        private string $regimenFiscalReceptor,
        private ?string $residenciaFiscal = null,
        private ?string $numRegIdTrib = null
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

    public function getUsoCfdi(): string
    {
        return $this->usoCfdi;
    }

    public function getDomicilioFiscalReceptor(): string
    {
        return $this->domicilioFiscalReceptor;
    }

    public function getRegimenFiscalReceptor(): string
    {
        return $this->regimenFiscalReceptor;
    }

    public function getResidenciaFiscal(): ?string
    {
        return $this->residenciaFiscal;
    }

    public function getNumRegIdTrib(): ?string
    {
        return $this->numRegIdTrib;
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
            'uso_cfdi' => $this->usoCfdi,
            'domicilio_fiscal_receptor' => $this->domicilioFiscalReceptor,
            'regimen_fiscal_receptor' => $this->regimenFiscalReceptor,
        ];

        if ($this->residenciaFiscal !== null) {
            $data['residencia_fiscal'] = $this->residenciaFiscal;
        }

        if ($this->numRegIdTrib !== null) {
            $data['num_reg_id_trib'] = $this->numRegIdTrib;
        }

        return $data;
    }
}