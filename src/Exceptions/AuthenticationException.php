<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Exceptions;

/**
 * Error de autenticación
 */
class AuthenticationException extends TecnoFactException
{
    public function __construct(
        string $message = 'Error de autenticación',
        ?string $requestId = null
    ) {
        parent::__construct($message, 401, null, $requestId);
    }
}