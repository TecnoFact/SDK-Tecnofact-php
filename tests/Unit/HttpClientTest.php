<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Enums\Environment;
use TecnoFact\Sdk\Exceptions\AuthenticationException;
use TecnoFact\Sdk\Exceptions\NotFoundException;
use TecnoFact\Sdk\Exceptions\RateLimitException;
use TecnoFact\Sdk\Exceptions\ServerException;
use TecnoFact\Sdk\Exceptions\TecnoFactException;
use TecnoFact\Sdk\Exceptions\ValidationException;
use TecnoFact\Sdk\Http\HttpClient;

final class HttpClientTest extends TestCase
{
    private Config $config;

    protected function setUp(): void
    {
        $this->config = new Config(
            email: 'test@example.com',
            password: 'password123',
            environment: Environment::PRODUCTION
        );
    }

    public function testGetRequestSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['success' => true, 'data' => ['id' => 1]])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $httpClient = new HttpClient($this->config);
        $reflection = new \ReflectionClass($httpClient);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($httpClient, $client);

        $result = $httpClient->get('/test', [], ['param' => 'value']);

        self::assertTrue($result['success']);
        self::assertArrayHasKey('data', $result);
    }

    public function testPostRequestSuccess(): void
    {
        $mock = new MockHandler([
            new Response(201, [], json_encode(['success' => true, 'id' => 123])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $httpClient = new HttpClient($this->config);
        $reflection = new \ReflectionClass($httpClient);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($httpClient, $client);

        $result = $httpClient->post('/test', [], ['name' => 'test']);

        self::assertTrue($result['success']);
        self::assertSame(123, $result['id']);
    }

    public function testPutRequestSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['success' => true, 'updated' => true])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $httpClient = new HttpClient($this->config);
        $reflection = new \ReflectionClass($httpClient);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($httpClient, $client);

        $result = $httpClient->put('/test/1', [], ['name' => 'updated']);

        self::assertTrue($result['success']);
        self::assertTrue($result['updated']);
    }

    public function testDeleteRequestSuccess(): void
    {
        $mock = new MockHandler([
            new Response(204, [], ''),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $httpClient = new HttpClient($this->config);
        $reflection = new \ReflectionClass($httpClient);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($httpClient, $client);

        $result = $httpClient->delete('/test/1');

        self::assertIsArray($result);
    }

    public function test401ResponseThrowsAuthenticationException(): void
    {
        $this->expectException(AuthenticationException::class);

        $mock = new MockHandler([
            new RequestException(
                'Unauthorized',
                new Request('GET', '/test'),
                new Response(401, [], json_encode(['error' => 'Unauthorized']))
            ),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $httpClient = new HttpClient($this->config);
        $reflection = new \ReflectionClass($httpClient);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($httpClient, $client);

        $httpClient->get('/test');
    }

    public function test404ResponseThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $mock = new MockHandler([
            new RequestException(
                'Not Found',
                new Request('GET', '/test'),
                new Response(404, [], json_encode(['error' => 'Not Found']))
            ),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $httpClient = new HttpClient($this->config);
        $reflection = new \ReflectionClass($httpClient);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($httpClient, $client);

        $httpClient->get('/test');
    }

    public function test422ResponseThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        $mock = new MockHandler([
            new RequestException(
                'Validation Error',
                new Request('POST', '/test'),
                new Response(422, [], json_encode(['error' => 'Validation failed']))
            ),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $httpClient = new HttpClient($this->config);
        $reflection = new \ReflectionClass($httpClient);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($httpClient, $client);

        $httpClient->post('/test', [], []);
    }

    public function test429ResponseThrowsRateLimitException(): void
    {
        $this->expectException(RateLimitException::class);

        $mock = new MockHandler([
            new RequestException(
                'Too Many Requests',
                new Request('GET', '/test'),
                new Response(429, [], json_encode(['error' => 'Rate limit exceeded']))
            ),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $httpClient = new HttpClient($this->config);
        $reflection = new \ReflectionClass($httpClient);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($httpClient, $client);

        $httpClient->get('/test');
    }

    public function test500ResponseThrowsServerException(): void
    {
        $this->expectException(ServerException::class);

        $mock = new MockHandler([
            new RequestException(
                'Internal Server Error',
                new Request('GET', '/test'),
                new Response(500, [], json_encode(['error' => 'Server error']))
            ),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $httpClient = new HttpClient($this->config);
        $reflection = new \ReflectionClass($httpClient);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($httpClient, $client);

        $httpClient->get('/test');
    }

    public function testInvalidJsonResponseThrowsException(): void
    {
        $this->expectException(TecnoFactException::class);
        $this->expectExceptionMessage('Error al decodificar respuesta JSON');

        $mock = new MockHandler([
            new Response(200, [], 'invalid json'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $httpClient = new HttpClient($this->config);
        $reflection = new \ReflectionClass($httpClient);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($httpClient, $client);

        $httpClient->get('/test');
    }
}
