<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Exceptions;

/**
 * Límite de peticiones excedido
 */
class RateLimitException extends TecnoFactException
{
    private int $retryAfter;

    public function __construct(
        string $message = 'Límite de peticiones excedido',
        int $retryAfter = 60,
        ?string $requestId = null
    ) {
        parent::__construct($message, 429, null, $requestId);
        $this->retryAfter = $retryAfter;
    }

    /**
     * Obtener tiempo de espera antes de reintentar (segundos)
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}