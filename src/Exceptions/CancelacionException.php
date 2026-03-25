<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Exceptions;

/**
 * Error de cancelación
 */
class CancelacionException extends TecnoFactException
{
    public function __construct(
        string $message = 'Error al cancelar CFDI',
        ?string $requestId = null
    ) {
        parent::__construct($message, 422, null, $requestId);
    }
}