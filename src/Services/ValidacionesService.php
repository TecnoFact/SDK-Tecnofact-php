<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Services;

use TecnoFact\Sdk\Exceptions\ValidationException;

final class ValidacionesService extends Service
{
    /**
     * @return array<string, mixed>
     */
    public function validarXml(string $xml): array
    {
        try {
            $response = $this->httpClient->post(
                $this->getBaseUrl() . '/validaciones/xml',
                $this->getHeaders(),
                [
                    'xml' => base64_encode($xml),
                ]
            );

            return $response;
        } catch (\Throwable $e) {
            throw new ValidationException(
                'Failed to validate XML: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function validarRfc(string $rfc): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/validaciones/rfc/' . $rfc,
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new ValidationException(
                'Failed to validate RFC: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function validarNoCertificado(string $noCertificado): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/validaciones/certificado/' . $noCertificado,
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new ValidationException(
                'Failed to validate certificate: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getCatalogos(): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/validaciones/catalogos',
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new ValidationException(
                'Failed to get catalogos: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getUnidadesMedida(): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/validaciones/catalogos/unidades',
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new ValidationException(
                'Failed to get unidades de medida: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getProductosServicios(): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/validaciones/catalogos/productos',
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new ValidationException(
                'Failed to get productos y servicios: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getImpuestos(): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/validaciones/catalogos/impuestos',
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new ValidationException(
                'Failed to get impuestos: ' . $e->getMessage()
            );
        }
    }
}
