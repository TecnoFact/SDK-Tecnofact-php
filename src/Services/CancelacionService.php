<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Services;

use TecnoFact\Sdk\Exceptions\CancelacionException;

final class CancelacionService extends Service
{
    /**
     * Cancela un CFDI ante el SAT.
     *
     * @param string $rfc RFC del emisor del comprobante a cancelar
     * @param string $uuid Folio fiscal (UUID) del CFDI a cancelar
     * @param string $motivo Clave del motivo de cancelación (catálogo c_MotivoCancelacion)
     * @return array<string, mixed>
     */
    public function cancelar(string $rfc, string $uuid, string $motivo): array
    {
        try {
            return $this->httpClient->post(
                $this->getBaseUrl() . '/api/v1/cancelled-cfdi',
                $this->getHeaders(),
                [
                    'rfc' => $rfc,
                    'uuid' => $uuid,
                    'motivo' => $motivo,
                ]
            );
        } catch (\Throwable $e) {
            throw new CancelacionException(
                'Failed to cancel CFDI: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getStatus(string $uuid): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/cancelacion/' . $uuid . '/status',
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new CancelacionException(
                'Failed to get cancellation status: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function obtenerAcuse(string $uuid): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/cancelacion/' . $uuid . '/acuse',
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new CancelacionException(
                'Failed to get acuse: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getPending(): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/cancelacion/pendientes',
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new CancelacionException(
                'Failed to get pending cancellations: ' . $e->getMessage()
            );
        }
    }
}
