<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Exceptions;

/**
 * Error de validación
 */
class ValidationException extends TecnoFactException
{
    private array $errors;

    public function __construct(
        string $message = 'Error de validación',
        array $errors = [],
        ?string $requestId = null
    ) {
        parent::__construct($message, 400, null, $requestId);
        $this->errors = $errors;
    }

    /**
     * Obtener errores de validación
     *
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}