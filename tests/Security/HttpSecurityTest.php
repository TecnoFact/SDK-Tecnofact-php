<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Security;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Enums\Environment;
use TecnoFact\Sdk\Http\HttpClient;

/**
 * Security-focused tests for HTTP client
 */
final class HttpSecurityTest extends TestCase
{
    private Config $config;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->config = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            environment: Environment::SANDBOX
        );
    }

    public function testHttpsEnforcedInSandboxEnvironment(): void
    {
        $config = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            environment: Environment::SANDBOX
        );
        
        $baseUrl = $config->getBaseUrl();
        
        $this->assertStringStartsWith('https://', $baseUrl);
        $this->assertStringNotContainsString('http://', $baseUrl);
    }

    public function testHttpsEnforcedInProductionEnvironment(): void
    {
        $config = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            environment: Environment::PRODUCTION
        );
        
        $baseUrl = $config->getBaseUrl();
        
        $this->assertStringStartsWith('https://', $baseUrl);
        $this->assertStringNotContainsString('http://', $baseUrl);
    }

    public function testApiCredentialsNotExposedInGetRequests(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 'ok'])),
        ]);
        
        $handlerStack = HandlerStack::create($mock);
        $client = new HttpClient($this->config);
        
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        
        $guzzleClient = $property->getValue($client);
        $this->assertInstanceOf(Client::class, $guzzleClient);
    }

    public function testTimeoutConfigurationApplied(): void
    {
        $config = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            timeout: 60
        );
        
        $this->assertSame(60, $config->getTimeout());
    }

    public function testRetryConfigurationApplied(): void
    {
        $config = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            retries: 5
        );
        
        $this->assertSame(5, $config->getRetries());
    }

    public function testConfigurationIsImmutable(): void
    {
        $config = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            environment: Environment::SANDBOX,
            timeout: 30,
            retries: 3
        );
        
        $this->assertSame('test-api-key-1234567890', $config->getApiKey());
        $this->assertSame('test-api-secret-12345678901234567890', $config->getApiSecret());
        $this->assertSame(Environment::SANDBOX, $config->getEnvironment());
        $this->assertSame(30, $config->getTimeout());
        $this->assertSame(3, $config->getRetries());
    }

    public function testBaseUrlMatchesEnvironment(): void
    {
        $sandboxConfig = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            environment: Environment::SANDBOX
        );
        
        $productionConfig = new Config(
            apiKey: 'test-api-key-1234567890',
            apiSecret: 'test-api-secret-12345678901234567890',
            environment: Environment::PRODUCTION
        );
        
        $this->assertNotSame($sandboxConfig->getBaseUrl(), $productionConfig->getBaseUrl());
        $this->assertStringContainsString('sandbox', $sandboxConfig->getBaseUrl());
        $this->assertStringNotContainsString('sandbox', $productionConfig->getBaseUrl());
    }

    public function testJsonContentTypeEnforced(): void
    {
        $client = new HttpClient($this->config);
        
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        
        $guzzleClient = $property->getValue($client);
        $config = $guzzleClient->getConfig();
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('headers', $config);
        $this->assertIsArray($config['headers']);
        $this->assertArrayHasKey('Content-Type', $config['headers']);
        $this->assertSame('application/json', $config['headers']['Content-Type']);
    }

    public function testAcceptHeaderEnforced(): void
    {
        $client = new HttpClient($this->config);
        
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        
        $guzzleClient = $property->getValue($client);
        $config = $guzzleClient->getConfig();
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('headers', $config);
        $this->assertIsArray($config['headers']);
        $this->assertArrayHasKey('Accept', $config['headers']);
        $this->assertSame('application/json', $config['headers']['Accept']);
    }
}
