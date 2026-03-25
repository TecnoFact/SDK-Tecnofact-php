<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Contracts;

use TecnoFact\Sdk\Config\Config;

/**
 * Interfaz para el cliente HTTP
 */
interface HttpClientInterface
{
    /**
     * Constructor con configuración
     */
    public function __construct(Config $config);

    /**
     * Realizar petición GET
     *
     * @param string $endpoint
     * @param array<string, string> $headers
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public function get(string $endpoint, array $headers = [], array $query = []): array;

    /**
     * Realizar petición POST
     *
     * @param string $endpoint
     * @param array<string, string> $headers
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function post(string $endpoint, array $headers = [], array $data = []): array;

    /**
     * Realizar petición PUT
     *
     * @param string $endpoint
     * @param array<string, string> $headers
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function put(string $endpoint, array $headers = [], array $data = []): array;

    /**
     * Realizar petición DELETE
     *
     * @param string $endpoint
     * @param array<string, string> $headers
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function delete(string $endpoint, array $headers = [], array $data = []): array;
}
