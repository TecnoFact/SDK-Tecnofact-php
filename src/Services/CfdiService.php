<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Services;

use TecnoFact\Sdk\Exceptions\TecnoFactException;
use TecnoFact\Sdk\Exceptions\TimbradoException;
use TecnoFact\Sdk\Models\Cfdi4Request;
use TecnoFact\Sdk\Models\PagoRequest;
use TecnoFact\Sdk\Responses\EstatusCfdi;
use TecnoFact\Sdk\Responses\ResultadoTimbrado;
use TecnoFact\Sdk\Xml\CfdiXmlBuilder;

final class CfdiService extends Service
{
    /**
     * Construye el XML del CFDI 4.0 a partir del request y lo envía a timbrar.
     *
     * El servicio del panel se encarga de sellar (con el CSD del emisor) y de
     * timbrar el comprobante ante el SAT; el SDK solo arma el XML estructural.
     */
    public function timbrar(Cfdi4Request $cfdi): ResultadoTimbrado
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

    public function timbrarXml(string $xml): ResultadoTimbrado
    {
        try {
            $response = $this->httpClient->post(
                $this->getBaseUrl() . '/api/v1/stamp-cfdi',
                $this->getHeaders(),
                [
                    'xml' => $xml,
                ]
            );

            return ResultadoTimbrado::fromResponse($response);
        } catch (\Throwable $e) {
            throw new TimbradoException(
                'Failed to timbrar XML: ' . $e->getMessage()
            );
        }
    }

    /**
     * Construye el XML del Complemento de Recepción de Pagos 2.0 y lo envía a timbrar.
     *
     * El SDK genera automáticamente el concepto fijo, los Totales y la estructura
     * pago20:Pagos. El panel sella y timbra el comprobante.
     */
    public function timbrarPago(PagoRequest $request): ResultadoTimbrado
    {
        try {
            $xml = (new CfdiXmlBuilder())->buildPago($request);
        } catch (\Throwable $e) {
            throw new TimbradoException(
                'Failed to build Pago XML: ' . $e->getMessage()
            );
        }

        return $this->timbrarXml($xml);
    }

    /**
     * Consulta el estatus/validez de un CFDI enviando su XML timbrado.
     *
     * El endpoint recibe el XML como multipart/form-data (campo "xml") y
     * devuelve el resultado de la validación del comprobante.
     */
    public function validar(string $xml): EstatusCfdi
    {
        try {
            $response = $this->httpClient->postMultipart(
                $this->getBaseUrl() . '/api/v1/validation-cfdi',
                $this->getHeaders(),
                ['xml' => $xml]
            );

            return EstatusCfdi::fromResponse($response);
        } catch (\Throwable $e) {
            throw new TecnoFactException(
                'Failed to validate CFDI: ' . $e->getMessage()
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
