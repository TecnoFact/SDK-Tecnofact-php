<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Exceptions;

/**
 * Error de timbrado
 */
class TimbradoException extends TecnoFactException
{
    private ?string $codigoError;
    private ?string $uuid;

    public function __construct(
        string $message = 'Error al timbrar CFDI',
        ?string $codigoError = null,
        ?string $uuid = null,
        ?string $requestId = null
    ) {
        parent::__construct($message, 422, null, $requestId);
        $this->codigoError = $codigoError;
        $this->uuid = $uuid;
    }

    /**
     * Obtener código de error del SAT
     */
    public function getCodigoError(): ?string
    {
        return $this->codigoError;
    }

    /**
     * Obtener UUID si fue generado
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }
}