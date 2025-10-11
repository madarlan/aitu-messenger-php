<?php

namespace MadArlan\AituMessenger;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use MadArlan\AituMessenger\Exceptions\AituApiException;
use MadArlan\AituMessenger\Exceptions\AituAuthenticationException;
use MadArlan\AituMessenger\Models\AituUser;
use MadArlan\AituMessenger\Utils\SignatureValidator;

class AituPassportClient
{
    private Client $httpClient;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private string $baseUrl;

    public function __construct(
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        string $baseUrl = 'https://passport.aitu.io',
        ?Client $httpClient = null
    ) {
        if (empty($clientId)) {
            throw new AituAuthenticationException('Client ID cannot be empty');
        }

        if (empty($clientSecret)) {
            throw new AituAuthenticationException('Client Secret cannot be empty');
        }

        if (empty($redirectUri)) {
            throw new AituAuthenticationException('Redirect URI cannot be empty');
        }

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->httpClient = $httpClient ?? new Client();
    }

    /**
     * Получить URL для OAuth авторизации
     *
     * @param array $scopes Запрашиваемые разрешения
     * @param string|null $state Состояние для защиты от CSRF
     * @return string
     */
    public function getAuthorizationUrl(array $scopes = [], ?string $state = null): string
    {
        $params = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
        ];

        if (!empty($scopes)) {
            $params['scope'] = implode(' ', $scopes);
        }

        if ($state !== null) {
            $params['state'] = $state;
        }

        return $this->baseUrl . '/oauth/authorize?' . http_build_query($params);
    }

    /**
     * Обменять код авторизации на токен доступа
     *
     * @param string $code Код авторизации
     * @return array Данные токена
     * @throws AituApiException
     * @throws AituAuthenticationException
     */
    public function exchangeCodeForToken(string $code): array
    {
        if (empty($code)) {
            throw new AituAuthenticationException('Authorization code cannot be empty');
        }

        try {
            $response = $this->httpClient->post($this->baseUrl . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'code' => $code,
                    'redirect_uri' => $this->redirectUri,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new AituApiException('Invalid JSON response from token endpoint');
            }

            if (isset($data['error'])) {
                throw new AituAuthenticationException(
                    'OAuth error: ' . ($data['error_description'] ?? $data['error'])
                );
            }

            return $data;
        } catch (GuzzleException $e) {
            throw new AituApiException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Обновить токен доступа
     *
     * @param string $refreshToken Токен обновления
     * @return array Новые данные токена
     * @throws AituApiException
     * @throws AituAuthenticationException
     */
    public function refreshToken(string $refreshToken): array
    {
        if (empty($refreshToken)) {
            throw new AituAuthenticationException('Refresh token cannot be empty');
        }

        try {
            $response = $this->httpClient->post($this->baseUrl . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'refresh_token' => $refreshToken,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new AituApiException('Invalid JSON response from token endpoint');
            }

            if (isset($data['error'])) {
                throw new AituAuthenticationException(
                    'OAuth error: ' . ($data['error_description'] ?? $data['error'])
                );
            }

            return $data;
        } catch (GuzzleException $e) {
            throw new AituApiException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Получить информацию о пользователе
     *
     * @param string $accessToken Токен доступа
     * @return AituUser
     * @throws AituApiException
     * @throws AituAuthenticationException
     */
    public function getUserInfo(string $accessToken): AituUser
    {
        if (empty($accessToken)) {
            throw new AituAuthenticationException('Access token cannot be empty');
        }

        try {
            $response = $this->httpClient->get($this->baseUrl . '/api/user/me', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new AituApiException('Invalid JSON response from user info endpoint');
            }

            if (isset($data['error'])) {
                throw new AituApiException('API error: ' . ($data['error_description'] ?? $data['error']));
            }

            return new AituUser($data);
        } catch (GuzzleException $e) {
            if ($e->getCode() === 401) {
                throw new AituAuthenticationException('Invalid or expired access token');
            }
            throw new AituApiException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Отозвать токен доступа
     *
     * @param string $token Токен для отзыва
     * @return bool
     * @throws AituApiException
     */
    public function revokeToken(string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        try {
            $response = $this->httpClient->post($this->baseUrl . '/oauth/revoke', [
                'form_params' => [
                    'token' => $token,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            throw new AituApiException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Проверить подпись от Aitu API
     *
     * @param array $data Данные для проверки
     * @param string $signature Подпись
     * @return bool
     */
    public function verifySignature(array $data, string $signature): bool
    {
        return SignatureValidator::verify($data, $signature, $this->clientSecret);
    }
}