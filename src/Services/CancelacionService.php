<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Services;

use TecnoFact\Sdk\Exceptions\CancelacionException;

final class CancelacionService extends Service
{
    /**
     * @return array<string, mixed>
     */
    public function cancelar(string $uuid, string $motivo, ?string $sustitutoUuid = null): array
    {
        try {
            $data = [
                'uuid' => $uuid,
                'motivo' => $motivo,
            ];

            if ($sustitutoUuid !== null) {
                $data['sustituto'] = $sustitutoUuid;
            }

            $response = $this->httpClient->post(
                $this->getBaseUrl() . '/cancelacion/cancelar',
                $this->getHeaders(),
                $data
            );

            return $response;
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