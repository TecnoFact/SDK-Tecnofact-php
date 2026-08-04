<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Contracts\HttpClientInterface;
use TecnoFact\Sdk\Enums\Environment;
use TecnoFact\Sdk\Exceptions\CancelacionException;
use TecnoFact\Sdk\Services\CancelacionService;

final class CancelacionServiceTest extends TestCase
{
    private Config $config;
    private HttpClientInterface $httpClient;
    private CancelacionService $cancelacionService;

    protected function setUp(): void
    {
        $this->config = new Config(
            email: 'test@example.com',
            password: 'password123',
            environment: Environment::PRODUCTION
        );
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->cancelacionService = new CancelacionService($this->config, $this->httpClient);
    }

    public function testCancelarEnviaRfcUuidYMotivo(): void
    {
        $expected = [
            'success' => true,
            'code' => 200,
            'message' => 'procesado',
            'data' => [
                'validado' => true,
                'uuid' => 'A0048319-F108-435D-8BE1-ADEB5DE14FB1',
                'status_sat' => '201 - Solicitud de cancelación aceptada por el SAT',
                'xml' => '<Acuse/>',
                'pdf' => base64_encode('%PDF-1.7 fake'),
            ],
        ];

        $this->httpClient
            ->expects(self::once())
            ->method('post')
            ->with(
                self::stringContains('/api/v1/cancelled-cfdi'),
                self::isType('array'),
                self::equalTo([
                    'rfc' => 'IIA040805DZ4',
                    'uuid' => 'e9a311f0-62e7-4f28-a218-76cef85dc6ba',
                    'motivo' => '03',
                ])
            )
            ->willReturn($expected);

        $result = $this->cancelacionService->cancelar(
            'IIA040805DZ4',
            'e9a311f0-62e7-4f28-a218-76cef85dc6ba',
            '03'
        );

        self::assertTrue($result->isSuccess());
        self::assertTrue($result->isValidado());
        self::assertTrue($result->isAceptadaPorSat());
        self::assertSame('A0048319-F108-435D-8BE1-ADEB5DE14FB1', $result->getUuid());
        self::assertSame('%PDF-1.7 fake', $result->getPdfBinario());
    }

    public function testCancelarFallidoLanzaExcepcion(): void
    {
        $this->expectException(CancelacionException::class);
        $this->expectExceptionMessage('Failed to cancel CFDI');

        $this->httpClient
            ->expects(self::once())
            ->method('post')
            ->willThrowException(new \RuntimeException('boom'));

        $this->cancelacionService->cancelar('IIA040805DZ4', 'uuid-x', '03');
    }
}
