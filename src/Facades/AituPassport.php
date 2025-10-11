<?php

namespace MadArlan\AituMessenger\Facades;

use Illuminate\Support\Facades\Facade;
use MadArlan\AituMessenger\AituPassportClient;

/**
 * @method static string getAuthorizationUrl(array $scopes = [], string $state = null)
 * @method static array exchangeCodeForToken(string $code, string $state = null)
 * @method static array refreshToken(string $refreshToken)
 * @method static array getUserInfo(string $accessToken)
 * @method static bool revokeToken(string $token, string $tokenType = 'access_token')
 * @method static bool verifySignature(array $data, string $signature, string $secret = null)
 * @method static string generateSignature(array $data, string $secret = null)
 * @method static bool verifyOAuthSignature(string $method, string $url, array $params, string $signature, string $consumerSecret, string $tokenSecret = '')
 * @method static string generateOAuthSignature(string $method, string $url, array $params, string $consumerSecret, string $tokenSecret = '')
 *
 * @see \MadArlan\AituMessenger\AituPassportClient
 */
class AituPassport extends Facade
{
    /**
     * Получить зарегистрированное имя компонента
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return AituPassportClient::class;
    }
}