<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Services;

use TecnoFact\Sdk\Exceptions\ValidationException;

final class ReportesService extends Service
{
    /**
     * @return array<string, mixed>
     */
    public function getResumen(string $fechaInicio, string $fechaFin): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/reportes/resumen',
                $this->getHeaders(),
                [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                ]
            );

            return $response;
        } catch (\Throwable $e) {
            throw new ValidationException(
                'Failed to get resumen: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getVentas(?string $fechaInicio = null, ?string $fechaFin = null): array
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
                $this->getBaseUrl() . '/reportes/ventas',
                $this->getHeaders(),
                $queryParams
            );

            return $response;
        } catch (\Throwable $e) {
            throw new ValidationException(
                'Failed to get ventas report: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getCancelaciones(string $fechaInicio, string $fechaFin): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/reportes/cancelaciones',
                $this->getHeaders(),
                [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                ]
            );

            return $response;
        } catch (\Throwable $e) {
            throw new ValidationException(
                'Failed to get cancelaciones report: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getXml(string $uuid): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/reportes/xml/' . $uuid,
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new ValidationException(
                'Failed to get XML report: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getPdf(string $uuid): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/reportes/pdf/' . $uuid,
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new ValidationException(
                'Failed to get PDF report: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function exportarCsv(string $fechaInicio, string $fechaFin): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/reportes/exportar',
                $this->getHeaders(),
                [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'formato' => 'csv',
                ]
            );

            return $response;
        } catch (\Throwable $e) {
            throw new ValidationException(
                'Failed to export CSV: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function exportarExcel(string $fechaInicio, string $fechaFin): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/reportes/exportar',
                $this->getHeaders(),
                [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'formato' => 'xlsx',
                ]
            );

            return $response;
        } catch (\Throwable $e) {
            throw new ValidationException(
                'Failed to export Excel: ' . $e->getMessage()
            );
        }
    }
}