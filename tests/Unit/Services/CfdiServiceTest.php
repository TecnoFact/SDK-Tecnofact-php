<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Contracts\HttpClientInterface;
use TecnoFact\Sdk\Enums\Environment;
use TecnoFact\Sdk\Exceptions\TecnoFactException;
use TecnoFact\Sdk\Models\Cfdi4Request;
use TecnoFact\Sdk\Models\Concepto;
use TecnoFact\Sdk\Models\Emisor;
use TecnoFact\Sdk\Models\Receptor;
use TecnoFact\Sdk\Services\CfdiService;

final class CfdiServiceTest extends TestCase
{
    private Config $config;
    private HttpClientInterface $httpClient;
    private CfdiService $cfdiService;

    protected function setUp(): void
    {
        $this->config = new Config(
            email: 'test@example.com',
            password: 'password123',
            environment: Environment::PRODUCTION
        );
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->cfdiService = new CfdiService($this->config, $this->httpClient);
    }

    public function testValidarEnviaXmlComoMultipart(): void
    {
        $xml = '<?xml version="1.0"?><cfdi:Comprobante/>';
        $expected = [
            'success' => true,
            'data' => [
                'estado' => 'Vigente',
                'codigo' => 'S - Cancelable con aceptación',
                'es_cancellable' => 'Cancelable con aceptación',
                'efos' => 'excluido',
            ],
        ];

        $this->httpClient
            ->expects(self::once())
            ->method('postMultipart')
            ->with(
                self::stringContains('/api/v1/validation-cfdi'),
                self::isType('array'),
                self::equalTo(['xml' => $xml])
            )
            ->willReturn($expected);

        $result = $this->cfdiService->validar($xml);

        self::assertTrue($result->isSuccess());
        self::assertTrue($result->isVigente());
        self::assertSame('Vigente', $result->getEstado());
        self::assertSame('excluido', $result->getEfos());
    }

    public function testValidarFallidoLanzaExcepcion(): void
    {
        $this->expectException(TecnoFactException::class);
        $this->expectExceptionMessage('Failed to validate CFDI');

        $this->httpClient
            ->expects(self::once())
            ->method('postMultipart')
            ->willThrowException(new \RuntimeException('boom'));

        $this->cfdiService->validar('<xml/>');
    }

    public function testTimbrarConstruyeXmlYLoEnviaAStampCfdi(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('post')
            ->with(
                self::stringContains('/api/v1/stamp-cfdi'),
                self::isType('array'),
                self::callback(function (array $data): bool {
                    return isset($data['xml'])
                        && is_string($data['xml'])
                        && str_contains($data['xml'], '<cfdi:Comprobante')
                        && str_contains($data['xml'], 'TipoDeComprobante="I"');
                })
            )
            ->willReturn(['success' => true, 'code' => 200, 'xml_timbrado' => '<xml/>']);

        $result = $this->cfdiService->timbrar($this->minimalRequest());

        self::assertTrue($result->isSuccess());
        self::assertSame(200, $result->getCode());
        self::assertSame('<xml/>', $result->getXmlTimbrado());
    }

    private function minimalRequest(): Cfdi4Request
    {
        return new Cfdi4Request(
            emisor: new Emisor(rfc: 'IOI170714AMA', nombre: 'EMISOR', regimenFiscal: '601', cp: '82103'),
            receptor: new Receptor(
                rfc: 'XAXX010101000',
                nombre: 'PUBLICO EN GENERAL',
                usoCfdi: 'S01',
                domicilioFiscalReceptor: '82103',
                regimenFiscalReceptor: '616'
            ),
            conceptos: [
                new Concepto(
                    claveProdServ: '01010101',
                    cantidad: 1.0,
                    claveUnidad: 'E48',
                    unidad: 'Unidad de servicio',
                    descripcion: 'Servicio',
                    valorUnitario: 100.00,
                    importe: 100.00,
                    objetoImp: '01'
                ),
            ],
            formaPago: '03',
            metodoPago: 'PUE',
            tipoComprobante: 'I',
            lugarExpedicion: '82103',
            subTotal: 100.00,
            total: 100.00,
            fecha: new \DateTime('2025-02-04T13:38:31')
        );
    }
}
