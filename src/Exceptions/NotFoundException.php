<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Exceptions;

/**
 * Recurso no encontrado
 */
class NotFoundException extends TecnoFactException
{
    public function __construct(
        string $message = 'Recurso no encontrado',
        ?string $requestId = null
    ) {
        parent::__construct($message, 404, null, $requestId);
    }
}
