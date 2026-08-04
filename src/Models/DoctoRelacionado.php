<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Documento relacionado dentro de un Pago (Complemento de Pagos 2.0).
 *
 * Representa el comprobante de ingreso (o egreso) que se está liquidando
 * total o parcialmente con este pago.
 */
class DoctoRelacionado
{
    /**
     * @param string $idDocumento UUID del comprobante relacionado
     * @param string $monedaDR Moneda del documento relacionado (c_Moneda)
     * @param string $equivalenciaDR Tipo de cambio entre MonedaDR y MonedaP
     * @param int $numParcialidad Número de parcialidad que se está pagando
     * @param string $impSaldoAnt Saldo insoluto antes de este pago
     * @param string $impPagado Importe pagado en esta parcialidad
     * @param string $impSaldoInsoluto Saldo insoluto después de este pago
     * @param string $objetoImpDR Indica si el documento es objeto de impuesto (c_ObjetoImp)
     * @param string|null $serie Serie del comprobante relacionado (opcional)
     * @param string|null $folio Folio del comprobante relacionado (opcional)
     */
    public function __construct(
        private string $idDocumento,
        private string $monedaDR,
        private string $equivalenciaDR,
        private int $numParcialidad,
        private string $impSaldoAnt,
        private string $impPagado,
        private string $impSaldoInsoluto,
        private string $objetoImpDR,
        private ?string $serie = null,
        private ?string $folio = null
    ) {
    }

    public function getIdDocumento(): string
    {
        return $this->idDocumento;
    }

    public function getMonedaDR(): string
    {
        return $this->monedaDR;
    }

    public function getEquivalenciaDR(): string
    {
        return $this->equivalenciaDR;
    }

    public function getNumParcialidad(): int
    {
        return $this->numParcialidad;
    }

    public function getImpSaldoAnt(): string
    {
        return $this->impSaldoAnt;
    }

    public function getImpPagado(): string
    {
        return $this->impPagado;
    }

    public function getImpSaldoInsoluto(): string
    {
        return $this->impSaldoInsoluto;
    }

    public function getObjetoImpDR(): string
    {
        return $this->objetoImpDR;
    }

    public function getSerie(): ?string
    {
        return $this->serie;
    }

    public function getFolio(): ?string
    {
        return $this->folio;
    }
}
