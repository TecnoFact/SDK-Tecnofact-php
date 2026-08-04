<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Services;

use TecnoFact\Sdk\Exceptions\TimbradoException;
use TecnoFact\Sdk\Models\Cfdi4Request;
use TecnoFact\Sdk\Xml\CfdiXmlBuilder;

final class CfdiService extends Service
{
    /**
     * Construye el XML del CFDI 4.0 a partir del request y lo envía a timbrar.
     *
     * El servicio del panel se encarga de sellar (con el CSD del emisor) y de
     * timbrar el comprobante ante el SAT; el SDK solo arma el XML estructural.
     *
     * @return array<string, mixed>
     */
    public function timbrar(Cfdi4Request $cfdi): array
    {
        try {
            $xml = (new CfdiXmlBuilder())->build($cfdi);
        } catch (\Throwable $e) {
            throw new TimbradoException(
                'Failed to build CFDI XML: ' . $e->getMessage()
            );
        }

        return $this->timbrarXml($xml);
    }

    /**
     * @return array<string, mixed>
     */
    public function timbrarXml(string $xml): array
    {
        try {
            $response = $this->httpClient->post(
                $this->getBaseUrl() . '/api/v1/stamp-cfdi',
                $this->getHeaders(),
                [
                    'xml' => $xml,
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
