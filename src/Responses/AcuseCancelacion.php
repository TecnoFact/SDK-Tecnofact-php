<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Responses;

/**
 * Acuse de una solicitud de cancelación de CFDI (CancelacionService::cancelar()).
 */
final class AcuseCancelacion
{
    /**
     * @param array<string, mixed> $raw Respuesta cruda del panel
     */
    private function __construct(
        private bool $success,
        private bool $validado,
        private ?string $uuid,
        private ?string $statusSat,
        private ?string $xml,
        private ?string $pdfBase64,
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
            (bool) ($data['validado'] ?? false),
            self::asString($data['uuid'] ?? null),
            self::asString($data['status_sat'] ?? null),
            self::asString($data['xml'] ?? null),
            self::asString($data['pdf'] ?? null),
            $response
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isValidado(): bool
    {
        return $this->validado;
    }

    /**
     * UUID de la solicitud de cancelación devuelto por el panel.
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getStatusSat(): ?string
    {
        return $this->statusSat;
    }

    /**
     * Indica si el SAT aceptó la solicitud de cancelación (estatus 201).
     */
    public function isAceptadaPorSat(): bool
    {
        return $this->statusSat !== null && str_starts_with($this->statusSat, '201');
    }

    /**
     * XML del acuse de cancelación (con el sello del SAT).
     */
    public function getXml(): ?string
    {
        return $this->xml;
    }

    /**
     * PDF del acuse codificado en base64, tal como lo devuelve el panel.
     */
    public function getPdfBase64(): ?string
    {
        return $this->pdfBase64;
    }

    /**
     * PDF del acuse ya decodificado (bytes binarios), listo para guardar en archivo.
     */
    public function getPdfBinario(): ?string
    {
        if ($this->pdfBase64 === null) {
            return null;
        }

        $decoded = base64_decode($this->pdfBase64, true);

        return $decoded === false ? null : $decoded;
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
