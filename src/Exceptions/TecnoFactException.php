<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Exceptions;

use Exception;

/**
 * Excepción base del SDK
 */
class TecnoFactException extends Exception
{
    private ?string $requestId;
    /** @var array<string, mixed>|null */
    private ?array $responseData;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        ?string $requestId = null,
        /** @var array<string, mixed>|null */
        ?array $responseData = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->requestId = $requestId;
        $this->responseData = $responseData;
    }

    /**
     * Obtener ID de la petición
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * Obtener datos de la respuesta
     *
     * @return array<string, mixed>|null
     */
    public function getResponseData(): ?array
    {
        return $this->responseData;
    }
}
