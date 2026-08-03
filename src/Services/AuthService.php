<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Services;

use TecnoFact\Sdk\Exceptions\AuthenticationException;

final class AuthService extends Service
{
    /**
     * @return array<string, mixed>
     */
    public function login(string $email, string $password): array
    {
        try {
            $response = $this->httpClient->post(
                $this->getBaseUrl() . '/api/login',
                $this->getHeaders(),
                [
                    'email' => $email,
                    'password' => $password,
                ]
            );

            if (isset($response['access_token']) && is_string($response['access_token'])) {
                $this->config->setToken($response['access_token']);
            }

            return $response;
        } catch (\Throwable $e) {
            throw new AuthenticationException(
                'Failed to authenticate: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function refreshToken(string $refreshToken): array
    {
        try {
            $response = $this->httpClient->post(
                $this->getBaseUrl() . '/auth/refresh',
                $this->getHeaders(),
                [
                    'refresh_token' => $refreshToken,
                ]
            );

            if (isset($response['access_token']) && is_string($response['access_token'])) {
                $this->config->setToken($response['access_token']);
            }

            return $response;
        } catch (\Throwable $e) {
            throw new AuthenticationException(
                'Failed to refresh token: ' . $e->getMessage()
            );
        }
    }

    public function logout(): bool
    {
        try {
            $this->httpClient->post(
                $this->getBaseUrl() . '/auth/logout',
                $this->getHeaders(),
                []
            );

            $this->config->setToken(null);

            return true;
        } catch (\Throwable $e) {
            throw new AuthenticationException(
                'Failed to logout: ' . $e->getMessage()
            );
        }
    }
}
