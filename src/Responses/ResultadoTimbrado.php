<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Responses;

/**
 * Resultado de un timbrado (CfdiService::timbrar() / timbrarXml()).
 *
 * El mapeo se hace de forma defensiva: además de los getters tipados, se conserva
 * la respuesta cruda del panel en getRaw() por si algún campo cambia de nombre.
 */
final class ResultadoTimbrado
{
    /**
     * @param array<string, mixed> $raw Respuesta cruda del panel
     */
    private function __construct(
        private bool $success,
        private ?int $code,
        private ?string $message,
        private ?string $xmlTimbrado,
        private ?string $uuid,
        private array $raw
    ) {
    }

    /**
     * @param array<string, mixed> $response
     */
    public static function fromResponse(array $response): self
    {
        $data = is_array($response['data'] ?? null) ? $response['data'] : [];

        $xml = $response['xml_timbrado'] ?? $data['xml_timbrado'] ?? $data['xml'] ?? null;
        $uuid = $response['uuid'] ?? $data['uuid'] ?? null;
        $message = $response['message'] ?? $response['error'] ?? null;
        $code = $response['code'] ?? null;

        return new self(
            (bool) ($response['success'] ?? false),
            is_numeric($code) ? (int) $code : null,
            self::asString($message),
            self::asString($xml),
            self::asString($uuid),
            $response
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * XML del comprobante ya timbrado (con el TimbreFiscalDigital del SAT).
     */
    public function getXmlTimbrado(): ?string
    {
        return $this->xmlTimbrado;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
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
