<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Exceptions;

/**
 * Error del servidor
 */
class ServerException extends TecnoFactException
{
    public function __construct(
        string $message = 'Error interno del servidor',
        int $code = 500,
        ?string $requestId = null
    ) {
        parent::__construct($message, $code, null, $requestId);
    }
}