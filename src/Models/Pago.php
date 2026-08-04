<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Nodo Pago del Complemento para Recepción de Pagos 2.0 (pago20:Pago).
 */
class Pago
{
    /**
     * @param \DateTime $fechaPago Fecha y hora en que se realizó el pago
     * @param string $formaDePagoP Forma de pago (catálogo c_FormaPago)
     * @param string $monedaP Moneda en que se recibió el pago (catálogo c_Moneda)
     * @param string $monto Monto del pago (hasta 6 decimales)
     * @param array<DoctoRelacionado> $doctosRelacionados Documentos que se liquidan con este pago
     * @param string $tipoCambioP Tipo de cambio (default "1" para MXN)
     */
    public function __construct(
        private \DateTime $fechaPago,
        private string $formaDePagoP,
        private string $monedaP,
        private string $monto,
        private array $doctosRelacionados,
        private string $tipoCambioP = '1'
    ) {
    }

    public function getFechaPago(): \DateTime
    {
        return $this->fechaPago;
    }

    public function getFormaDePagoP(): string
    {
        return $this->formaDePagoP;
    }

    public function getMonedaP(): string
    {
        return $this->monedaP;
    }

    public function getMonto(): string
    {
        return $this->monto;
    }

    public function getTipoCambioP(): string
    {
        return $this->tipoCambioP;
    }

    /**
     * @return array<DoctoRelacionado>
     */
    public function getDoctosRelacionados(): array
    {
        return $this->doctosRelacionados;
    }
}
