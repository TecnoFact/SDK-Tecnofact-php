<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Enums;

/**
 * Enumeración de entornos de la API
 *
 * Sandbox no está disponible por ahora. Cuando exista, reactivar el case y sus helpers.
 */
enum Environment: string
{
    // case SANDBOX = 'sandbox';
    case PRODUCTION = 'production';

    // /**
    //  * Verificar si es entorno de pruebas
    //  */
    // public function isSandbox(): bool
    // {
    //     return $this === self::SANDBOX;
    // }

    /**
     * Verificar si es entorno de producción
     */
    public function isProduction(): bool
    {
        return $this === self::PRODUCTION;
    }

    /**
     * Obtener nombre legible del entorno
     */
    public function label(): string
    {
        return match ($this) {
            // self::SANDBOX => 'Sandbox',
            self::PRODUCTION => 'Producción',
        };
    }
}
