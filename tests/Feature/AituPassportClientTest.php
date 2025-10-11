<?php

namespace MadArlan\AituMessenger\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use MadArlan\AituMessenger\AituPassportClient;
use MadArlan\AituMessenger\Http\HttpClient;
use MadArlan\AituMessenger\Exceptions\AituApiException;
use MadArlan\AituMessenger\Exceptions\AituAuthException;

class AituPassportClientTest extends TestCase
{
    private AituPassportClient $client;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        
        $httpClient = new HttpClient([
            'base_uri' => 'https://passport-api.aitu.io',
            'timeout' => 30
        ], $guzzleClient);

        $this->client = new AituPassportClient(
            'test_client_id',
            'test_client_secret',
            'https://example.com/callback',
            $httpClient
        );
    }

    public function testGetAuthorizationUrl(): void
    {
        $scopes = ['profile', 'email'];
        $state = 'random_state_string';
        
        $url = $this->client->getAuthorizationUrl($scopes, $state);
        
        $this->assertStringContainsString('https://passport.aitu.io/oauth/authorize', $url);
        $this->assertStringContainsString('client_id=test_client_id', $url);
        $this->assertStringContainsString('redirect_uri=' . urlencode('https://example.com/callback'), $url);
        $this->assertStringContainsString('scope=' . urlencode('profile email'), $url);
        $this->assertStringContainsString('state=' . $state, $url);
        $this->assertStringContainsString('response_type=code', $url);
    }

    public function testGetAuthorizationUrlWithDefaultScopes(): void
    {
        $url = $this->client->getAuthorizationUrl();
        
        $this->assertStringContainsString('scope=' . urlencode('profile'), $url);
    }

    public function testExchangeCodeForTokens(): void
    {
        $tokenResponse = [
            'access_token' => 'access_token_123',
            'refresh_token' => 'refresh_token_456',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => 'profile email'
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($tokenResponse))
        );

        $tokens = $this->client->exchangeCodeForTokens('auth_code_123');

        $this->assertEquals('access_token_123', $tokens['access_token']);
        $this->assertEquals('refresh_token_456', $tokens['refresh_token']);
        $this->assertEquals('Bearer', $tokens['token_type']);
        $this->assertEquals(3600, $tokens['expires_in']);
        $this->assertEquals('profile email', $tokens['scope']);
    }

    public function testExchangeCodeForTokensWithError(): void
    {
        $errorResponse = [
            'error' => 'invalid_grant',
            'error_description' => 'The authorization code is invalid'
        ];

        $this->mockHandler->append(
            new Response(400, ['Content-Type' => 'application/json'], json_encode($errorResponse))
        );

        $this->expectException(AituAuthException::class);
        $this->expectExceptionMessage('invalid_grant');

        $this->client->exchangeCodeForTokens('invalid_code');
    }

    public function testRefreshToken(): void
    {
        $refreshResponse = [
            'access_token' => 'new_access_token_789',
            'refresh_token' => 'new_refresh_token_012',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => 'profile email'
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($refreshResponse))
        );

        $tokens = $this->client->refreshToken('refresh_token_456');

        $this->assertEquals('new_access_token_789', $tokens['access_token']);
        $this->assertEquals('new_refresh_token_012', $tokens['refresh_token']);
    }

    public function testRefreshTokenWithError(): void
    {
        $errorResponse = [
            'error' => 'invalid_grant',
            'error_description' => 'The refresh token is invalid'
        ];

        $this->mockHandler->append(
            new Response(400, ['Content-Type' => 'application/json'], json_encode($errorResponse))
        );

        $this->expectException(AituAuthException::class);
        $this->expectExceptionMessage('invalid_grant');

        $this->client->refreshToken('invalid_refresh_token');
    }

    public function testGetUserInfo(): void
    {
        $userInfo = [
            'id' => 'user_123',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+77001234567',
            'avatar' => 'https://example.com/avatar.jpg',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'city' => 'Almaty',
            'country' => 'KZ',
            'language' => 'ru',
            'timezone' => 'Asia/Almaty',
            'is_verified' => true
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($userInfo))
        );

        $result = $this->client->getUserInfo('access_token_123');

        $this->assertEquals('user_123', $result['id']);
        $this->assertEquals('John Doe', $result['name']);
        $this->assertEquals('john@example.com', $result['email']);
        $this->assertTrue($result['is_verified']);
    }

    public function testGetUserInfoWithInvalidToken(): void
    {
        $errorResponse = [
            'error' => 'invalid_token',
            'error_description' => 'The access token is invalid'
        ];

        $this->mockHandler->append(
            new Response(401, ['Content-Type' => 'application/json'], json_encode($errorResponse))
        );

        $this->expectException(AituAuthException::class);
        $this->expectExceptionMessage('invalid_token');

        $this->client->getUserInfo('invalid_token');
    }

    public function testRevokeToken(): void
    {
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'revoked']))
        );

        $result = $this->client->revokeToken('access_token_123');

        $this->assertTrue($result);
    }

    public function testRevokeTokenWithError(): void
    {
        $errorResponse = [
            'error' => 'invalid_token',
            'error_description' => 'Token not found'
        ];

        $this->mockHandler->append(
            new Response(400, ['Content-Type' => 'application/json'], json_encode($errorResponse))
        );

        $this->expectException(AituAuthException::class);
        $this->expectExceptionMessage('invalid_token');

        $this->client->revokeToken('invalid_token');
    }

    public function testValidateToken(): void
    {
        $validationResponse = [
            'valid' => true,
            'client_id' => 'test_client_id',
            'user_id' => 'user_123',
            'scope' => 'profile email',
            'expires_at' => time() + 3600
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($validationResponse))
        );

        $result = $this->client->validateToken('access_token_123');

        $this->assertTrue($result['valid']);
        $this->assertEquals('test_client_id', $result['client_id']);
        $this->assertEquals('user_123', $result['user_id']);
    }

    public function testValidateInvalidToken(): void
    {
        $validationResponse = [
            'valid' => false,
            'error' => 'token_expired'
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($validationResponse))
        );

        $result = $this->client->validateToken('expired_token');

        $this->assertFalse($result['valid']);
        $this->assertEquals('token_expired', $result['error']);
    }

    public function testGetClientCredentialsToken(): void
    {
        $tokenResponse = [
            'access_token' => 'client_credentials_token_123',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => 'app'
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($tokenResponse))
        );

        $tokens = $this->client->getClientCredentialsToken(['app']);

        $this->assertEquals('client_credentials_token_123', $tokens['access_token']);
        $this->assertEquals('Bearer', $tokens['token_type']);
        $this->assertEquals(3600, $tokens['expires_in']);
        $this->assertEquals('app', $tokens['scope']);
    }

    public function testGetClientCredentialsTokenWithError(): void
    {
        $errorResponse = [
            'error' => 'invalid_client',
            'error_description' => 'Client authentication failed'
        ];

        $this->mockHandler->append(
            new Response(401, ['Content-Type' => 'application/json'], json_encode($errorResponse))
        );

        $this->expectException(AituAuthException::class);
        $this->expectExceptionMessage('invalid_client');

        $this->client->getClientCredentialsToken(['app']);
    }

    public function testGenerateState(): void
    {
        $state1 = $this->client->generateState();
        $state2 = $this->client->generateState();

        $this->assertIsString($state1);
        $this->assertIsString($state2);
        $this->assertNotEquals($state1, $state2);
        $this->assertEquals(32, strlen($state1));
        $this->assertEquals(32, strlen($state2));
    }

    public function testVerifyState(): void
    {
        $state = 'test_state_string';
        
        $this->assertTrue($this->client->verifyState($state, $state));
        $this->assertFalse($this->client->verifyState($state, 'different_state'));
        $this->assertFalse($this->client->verifyState($state, ''));
    }

    public function testGetLogoutUrl(): void
    {
        $logoutUrl = $this->client->getLogoutUrl('https://example.com/logged-out');

        $this->assertStringContainsString('https://passport.aitu.io/oauth/logout', $logoutUrl);
        $this->assertStringContainsString('client_id=test_client_id', $logoutUrl);
        $this->assertStringContainsString('redirect_uri=' . urlencode('https://example.com/logged-out'), $logoutUrl);
    }

    public function testGetLogoutUrlWithoutRedirect(): void
    {
        $logoutUrl = $this->client->getLogoutUrl();

        $this->assertStringContainsString('https://passport.aitu.io/oauth/logout', $logoutUrl);
        $this->assertStringContainsString('client_id=test_client_id', $logoutUrl);
        $this->assertStringNotContainsString('redirect_uri=', $logoutUrl);
    }

    public function testNetworkError(): void
    {
        $this->mockHandler->append(
            new \GuzzleHttp\Exception\ConnectException(
                'Connection timeout',
                new \GuzzleHttp\Psr7\Request('POST', '/oauth/token')
            )
        );

        $this->expectException(\MadArlan\AituMessenger\Exceptions\AituHttpException::class);
        $this->expectExceptionMessage('Connection timeout');

        $this->client->exchangeCodeForTokens('auth_code');
    }

    public function testServerError(): void
    {
        $this->mockHandler->append(
            new Response(500, [], 'Internal Server Error')
        );

        $this->expectException(AituApiException::class);
        $this->expectExceptionCode(500);

        $this->client->getUserInfo('access_token');
    }

    public function testCustomApiEndpoints(): void
    {
        $customClient = new AituPassportClient(
            'test_client_id',
            'test_client_secret',
            'https://example.com/callback',
            null,
            [
                'auth_url' => 'https://custom.aitu.io/oauth/authorize',
                'token_url' => 'https://custom-api.aitu.io/oauth/token',
                'user_info_url' => 'https://custom-api.aitu.io/user/info'
            ]
        );

        $authUrl = $customClient->getAuthorizationUrl();
        
        $this->assertStringContainsString('https://custom.aitu.io/oauth/authorize', $authUrl);
    }

    public function testTokenExpirationCalculation(): void
    {
        $tokenResponse = [
            'access_token' => 'access_token_123',
            'refresh_token' => 'refresh_token_456',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => 'profile'
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($tokenResponse))
        );

        $beforeTime = time();
        $tokens = $this->client->exchangeCodeForTokens('auth_code_123');
        $afterTime = time();

        $this->assertArrayHasKey('expires_at', $tokens);
        $this->assertGreaterThanOrEqual($beforeTime + 3600, $tokens['expires_at']);
        $this->assertLessThanOrEqual($afterTime + 3600, $tokens['expires_at']);
    }

    public function testScopeHandling(): void
    {
        $scopes = ['profile', 'email', 'phone'];
        $url = $this->client->getAuthorizationUrl($scopes);
        
        $this->assertStringContainsString('scope=' . urlencode('profile email phone'), $url);
    }

    public function testEmptyScopeHandling(): void
    {
        $url = $this->client->getAuthorizationUrl([]);
        
        $this->assertStringContainsString('scope=', $url);
    }
}