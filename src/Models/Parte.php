<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Parte o componente de un concepto
 */
class Parte
{
    /**
     * @param string $claveProdServ Clave del producto/servicio
     * @param float $cantidad Cantidad
     * @param string $descripcion Descripción
     * @param string|null $unidad Unidad (opcional)
     * @param string|null $noIdentificacion Número de identificación
     * @param float|null $valorUnitario Valor unitario
     * @param float|null $importe Importe
     */
    public function __construct(
        private string $claveProdServ,
        private float $cantidad,
        private string $descripcion,
        private ?string $unidad = null,
        private ?string $noIdentificacion = null,
        private ?float $valorUnitario = null,
        private ?float $importe = null
    ) {
    }

    public function getClaveProdServ(): string
    {
        return $this->claveProdServ;
    }

    public function getCantidad(): float
    {
        return $this->cantidad;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function getUnidad(): ?string
    {
        return $this->unidad;
    }

    public function getNoIdentificacion(): ?string
    {
        return $this->noIdentificacion;
    }

    public function getValorUnitario(): ?float
    {
        return $this->valorUnitario;
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
            'clave_prod_serv' => $this->claveProdServ,
            'cantidad' => $this->cantidad,
            'descripcion' => $this->descripcion,
        ];

        if ($this->unidad !== null) {
            $data['unidad'] = $this->unidad;
        }

        if ($this->noIdentificacion !== null) {
            $data['no_identificacion'] = $this->noIdentificacion;
        }

        if ($this->valorUnitario !== null) {
            $data['valor_unitario'] = $this->valorUnitario;
        }

        if ($this->importe !== null) {
            $data['importe'] = $this->importe;
        }

        return $data;
    }
}