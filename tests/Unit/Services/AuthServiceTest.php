<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Contracts\HttpClientInterface;
use TecnoFact\Sdk\Enums\Environment;
use TecnoFact\Sdk\Exceptions\AuthenticationException;
use TecnoFact\Sdk\Services\AuthService;

final class AuthServiceTest extends TestCase
{
    private Config $config;
    private HttpClientInterface $httpClient;
    private AuthService $authService;

    protected function setUp(): void
    {
        $this->config = new Config(
            email: 'test@example.com',
            password: 'password123',
            environment: Environment::PRODUCTION
        );

        $this->httpClient = $this->createMock(HttpClientInterface::class);

        $this->authService = new AuthService($this->config, $this->httpClient);
    }

    public function testLoginSuccess(): void
    {
        $expectedResponse = [
            'success' => true,
            'access_token' => 'test-access-token-123',
            'refresh_token' => 'test-refresh-token-456',
            'expires_in' => 3600,
        ];

        $this->httpClient
            ->expects(self::once())
            ->method('post')
            ->with(
                self::stringContains('/api/login'),
                self::isType('array'),
                self::equalTo([
                    'email' => 'test@example.com',
                    'password' => 'password123',
                ])
            )
            ->willReturn($expectedResponse);

        $result = $this->authService->login('test@example.com', 'password123');

        self::assertTrue($result['success']);
        self::assertSame('test-access-token-123', $result['access_token']);
        self::assertSame('test-access-token-123', $this->config->getToken());
    }

    public function testLoginFailureThrowsException(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Failed to authenticate');

        $this->httpClient
            ->expects(self::once())
            ->method('post')
            ->willThrowException(new \RuntimeException('Invalid credentials'));

        $this->authService->login('test@example.com', 'wrong-password');
    }

    public function testRefreshTokenSuccess(): void
    {
        $expectedResponse = [
            'success' => true,
            'access_token' => 'new-access-token-789',
            'refresh_token' => 'new-refresh-token-012',
            'expires_in' => 3600,
        ];

        $this->httpClient
            ->expects(self::once())
            ->method('post')
            ->with(
                self::stringContains('/auth/refresh'),
                self::isType('array'),
                self::equalTo([
                    'refresh_token' => 'old-refresh-token',
                ])
            )
            ->willReturn($expectedResponse);

        $result = $this->authService->refreshToken('old-refresh-token');

        self::assertTrue($result['success']);
        self::assertSame('new-access-token-789', $result['access_token']);
        self::assertSame('new-access-token-789', $this->config->getToken());
    }

    public function testRefreshTokenFailureThrowsException(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Failed to refresh token');

        $this->httpClient
            ->expects(self::once())
            ->method('post')
            ->willThrowException(new \RuntimeException('Invalid refresh token'));

        $this->authService->refreshToken('invalid-token');
    }

    public function testLogoutSuccess(): void
    {
        $this->config->setToken('current-token');

        $this->httpClient
            ->expects(self::once())
            ->method('post')
            ->with(
                self::stringContains('/auth/logout'),
                self::isType('array'),
                self::equalTo([])
            )
            ->willReturn(['success' => true]);

        $result = $this->authService->logout();

        self::assertTrue($result);
        self::assertNull($this->config->getToken());
    }

    public function testLogoutFailureThrowsException(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Failed to logout');

        $this->httpClient
            ->expects(self::once())
            ->method('post')
            ->willThrowException(new \RuntimeException('Logout failed'));

        $this->authService->logout();
    }

    public function testLoginWithoutAccessTokenInResponse(): void
    {
        $responseWithoutToken = [
            'success' => true,
            'message' => 'Logged in',
        ];

        $this->httpClient
            ->expects(self::once())
            ->method('post')
            ->willReturn($responseWithoutToken);

        $result = $this->authService->login('test@example.com', 'password123');

        self::assertTrue($result['success']);
        self::assertNull($this->config->getToken());
    }

    public function testRefreshTokenWithoutAccessTokenInResponse(): void
    {
        $responseWithoutToken = [
            'success' => true,
            'message' => 'Token refreshed',
        ];

        $this->httpClient
            ->expects(self::once())
            ->method('post')
            ->willReturn($responseWithoutToken);

        $result = $this->authService->refreshToken('refresh-token');

        self::assertTrue($result['success']);
        self::assertNull($this->config->getToken());
    }
}
