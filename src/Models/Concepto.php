<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Representa un concepto/producto en el CFDI
 */
class Concepto
{
    /**
     * @param string $claveProdServ Clave del producto/servicio (catálogo SAT)
     * @param float $cantidad Cantidad del concepto
     * @param string $claveUnidad Clave de unidad de medida
     * @param string|null $unidad Descripción de la unidad (opcional)
     * @param string $descripcion Descripción del concepto (hasta 1000 caracteres)
     * @param float $valorUnitario Valor unitario del concepto
     * @param float $importe Importe total del concepto (cantidad * valorUnitario)
     * @param string $objetoImp Indica si es objeto de impuesto (01=No, 02=Sí, 03=Sí y no obligado)
     * @param ImpuestosConcepto|null $impuestos Impuestos del concepto
     * @param string|null $noIdentificacion Número de identificación del producto
     * @param CuentaPredial|null $cuentaPredial Información de cuenta predial (para bienes inmuebles)
     * @param array<Parte>|null $partes Partes o componentes del concepto
     * @param InformacionAduanera|null $informacionAduanera Información aduanera (para importaciones)
     */
    public function __construct(
        private string $claveProdServ,
        private float $cantidad,
        private string $claveUnidad,
        private ?string $unidad,
        private string $descripcion,
        private float $valorUnitario,
        private float $importe,
        private string $objetoImp,
        private ?ImpuestosConcepto $impuestos = null,
        private ?string $noIdentificacion = null,
        private ?CuentaPredial $cuentaPredial = null,
        private ?array $partes = null,
        private ?InformacionAduanera $informacionAduanera = null
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

    public function getClaveUnidad(): string
    {
        return $this->claveUnidad;
    }

    public function getUnidad(): ?string
    {
        return $this->unidad;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function getValorUnitario(): float
    {
        return $this->valorUnitario;
    }

    public function getImporte(): float
    {
        return $this->importe;
    }

    public function getObjetoImp(): string
    {
        return $this->objetoImp;
    }

    public function getImpuestos(): ?ImpuestosConcepto
    {
        return $this->impuestos;
    }

    public function getNoIdentificacion(): ?string
    {
        return $this->noIdentificacion;
    }

    public function getCuentaPredial(): ?CuentaPredial
    {
        return $this->cuentaPredial;
    }

    public function getPartes(): ?array
    {
        return $this->partes;
    }

    public function getInformacionAduanera(): ?InformacionAduanera
    {
        return $this->informacionAduanera;
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
            'clave_unidad' => $this->claveUnidad,
            'descripcion' => $this->descripcion,
            'valor_unitario' => $this->valorUnitario,
            'importe' => $this->importe,
            'objeto_imp' => $this->objetoImp,
        ];

        if ($this->unidad !== null) {
            $data['unidad'] = $this->unidad;
        }

        if ($this->noIdentificacion !== null) {
            $data['no_identificacion'] = $this->noIdentificacion;
        }

        if ($this->impuestos !== null) {
            $data['impuestos'] = $this->impuestos->toArray();
        }

        if ($this->cuentaPredial !== null) {
            $data['cuenta_predial'] = $this->cuentaPredial->toArray();
        }

        if ($this->partes !== null) {
            $data['partes'] = array_map(fn(Parte $parte) => $parte->toArray(), $this->partes);
        }

        if ($this->informacionAduanera !== null) {
            $data['informacion_aduanera'] = $this->informacionAduanera->toArray();
        }

        return $data;
    }
}