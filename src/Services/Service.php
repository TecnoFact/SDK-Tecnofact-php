<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Services;

use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Contracts\HttpClientInterface;

abstract class Service
{
    protected Config $config;

    protected HttpClientInterface $httpClient;

    public function __construct(Config $config, HttpClientInterface $httpClient)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
    }

    protected function getBaseUrl(): string
    {
        return $this->config->getBaseUrl();
    }

    /**
     * @return array<string, string>
     */
    protected function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->config->getToken(),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }
}