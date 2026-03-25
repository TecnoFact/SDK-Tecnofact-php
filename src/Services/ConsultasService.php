<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Services;

use TecnoFact\Sdk\Exceptions\NotFoundException;

final class ConsultasService extends Service
{
    /**
     * @return array<string, mixed>
     */
    public function buscarPorUuid(string $uuid): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/consultas/uuid/' . $uuid,
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new NotFoundException(
                'CFDI not found: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function buscarPorRfc(string $rfc, ?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        try {
            $queryParams = [];
            if ($fechaInicio !== null) {
                $queryParams['fecha_inicio'] = $fechaInicio;
            }
            if ($fechaFin !== null) {
                $queryParams['fecha_fin'] = $fechaFin;
            }

            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/consultas/rfc/' . $rfc,
                $this->getHeaders(),
                $queryParams
            );

            return $response;
        } catch (\Throwable $e) {
            throw new NotFoundException(
                'Failed to search by RFC: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function buscarPorSerie(string $serie, string $folio): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/consultas/serie/' . $serie . '/folio/' . $folio,
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new NotFoundException(
                'CFDI not found by serie/folio: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function verificarSat(string $uuid): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/consultas/sat/' . $uuid,
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new NotFoundException(
                'SAT verification failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function listar(int $page = 1, int $perPage = 20): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/consultas',
                $this->getHeaders(),
                [
                    'page' => $page,
                    'per_page' => $perPage,
                ]
            );

            return $response;
        } catch (\Throwable $e) {
            throw new NotFoundException(
                'Failed to list CFDIs: ' . $e->getMessage()
            );
        }
    }
}