<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Services;

use TecnoFact\Sdk\Exceptions\TimbradoException;
use TecnoFact\Sdk\Models\Cfdi4Request;

final class CfdiService extends Service
{
    /**
     * @return array<string, mixed>
     */
    public function timbrar(Cfdi4Request $cfdi): array
    {
        try {
            $response = $this->httpClient->post(
                $this->getBaseUrl() . '/cfdi/timbrar',
                $this->getHeaders(),
                $cfdi->toArray()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new TimbradoException(
                'Failed to timbrar CFDI: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function timbrarXml(string $xml): array
    {
        try {
            $response = $this->httpClient->post(
                $this->getBaseUrl() . '/cfdi/timbrar-xml',
                $this->getHeaders(),
                [
                    'xml' => base64_encode($xml),
                ]
            );

            return $response;
        } catch (\Throwable $e) {
            throw new TimbradoException(
                'Failed to timbrar XML: ' . $e->getMessage()
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
                $this->getBaseUrl() . '/cfdi/' . $uuid . '/xml',
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new TimbradoException(
                'Failed to get XML: ' . $e->getMessage()
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
                $this->getBaseUrl() . '/cfdi/' . $uuid . '/pdf',
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new TimbradoException(
                'Failed to get PDF: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getHtml(string $uuid): array
    {
        try {
            $response = $this->httpClient->get(
                $this->getBaseUrl() . '/cfdi/' . $uuid . '/html',
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new TimbradoException(
                'Failed to get HTML: ' . $e->getMessage()
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
                $this->getBaseUrl() . '/cfdi/' . $uuid . '/status',
                $this->getHeaders()
            );

            return $response;
        } catch (\Throwable $e) {
            throw new TimbradoException(
                'Failed to get CFDI status: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function sendByEmail(string $uuid, string $email): array
    {
        try {
            $response = $this->httpClient->post(
                $this->getBaseUrl() . '/cfdi/' . $uuid . '/send-email',
                $this->getHeaders(),
                [
                    'email' => $email,
                ]
            );

            return $response;
        } catch (\Throwable $e) {
            throw new TimbradoException(
                'Failed to send CFDI by email: ' . $e->getMessage()
            );
        }
    }
}