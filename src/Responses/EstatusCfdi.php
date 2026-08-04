<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Responses;

/**
 * Resultado de la consulta de estatus/validez de un CFDI (CfdiService::validar()).
 */
final class EstatusCfdi
{
    /**
     * @param array<string, mixed> $raw Respuesta cruda del panel
     */
    private function __construct(
        private bool $success,
        private ?string $estado,
        private ?string $codigo,
        private ?string $esCancelable,
        private ?string $estatusCancelacion,
        private ?string $efos,
        private array $raw
    ) {
    }

    /**
     * @param array<string, mixed> $response
     */
    public static function fromResponse(array $response): self
    {
        $data = is_array($response['data'] ?? null) ? $response['data'] : [];

        return new self(
            (bool) ($response['success'] ?? false),
            self::asString($data['estado'] ?? null),
            self::asString($data['codigo'] ?? null),
            self::asString($data['es_cancellable'] ?? null),
            self::asString($data['estatus_cancelacion'] ?? null),
            self::asString($data['efos'] ?? null),
            $response
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function isVigente(): bool
    {
        return $this->estado !== null && strcasecmp($this->estado, 'Vigente') === 0;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function getEsCancelable(): ?string
    {
        return $this->esCancelable;
    }

    public function getEstatusCancelacion(): ?string
    {
        return $this->estatusCancelacion;
    }

    public function getEfos(): ?string
    {
        return $this->efos;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRaw(): array
    {
        return $this->raw;
    }

    private static function asString(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        return is_scalar($value) ? (string) $value : null;
    }
}
