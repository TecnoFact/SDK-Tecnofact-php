<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Enums;

/**
 * Tipo de comprobante CFDI
 */
enum TipoComprobante: string
{
    case INGRESO = 'I';
    case EGRESO = 'E';
    case TRASLADO = 'T';
    case NOMINA = 'N';
    case PAGO = 'P';

    public function label(): string
    {
        return match ($this) {
            self::INGRESO => 'Ingreso',
            self::EGRESO => 'Egreso',
            self::TRASLADO => 'Traslado',
            self::NOMINA => 'Nómina',
            self::PAGO => 'Pago',
        };
    }
}
