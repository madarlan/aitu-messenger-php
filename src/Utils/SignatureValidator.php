<?php

namespace MadArlan\AituMessenger\Utils;

class SignatureValidator
{
    /**
     * Проверить подпись данных
     *
     * @param array $data Данные для проверки
     * @param string $signature Подпись для проверки
     * @param string $secretKey Секретный ключ
     * @return bool
     */
    public static function verify(array $data, string $signature, string $secretKey): bool
    {
        try {
            $expectedSignature = SignatureGenerator::generate($data, $secretKey);
            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Проверить подпись OAuth запроса
     *
     * @param string $method HTTP метод
     * @param string $url URL запроса
     * @param array $params Параметры запроса
     * @param string $signature Подпись для проверки
     * @param string $clientSecret Секрет клиента
     * @return bool
     */
    public static function verifyOAuthSignature(
        string $method,
        string $url,
        array $params,
        string $signature,
        string $clientSecret
    ): bool {
        try {
            $expectedSignature = SignatureGenerator::generateOAuthSignature(
                $method,
                $url,
                $params,
                $clientSecret
            );
            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Проверить подпись webhook от Aitu
     *
     * @param string $payload Тело запроса
     * @param string $signature Подпись из заголовка
     * @param string $secretKey Секретный ключ
     * @return bool
     */
    public static function verifyWebhookSignature(
        string $payload,
        string $signature,
        string $secretKey
    ): bool {
        try {
            // Удаляем префикс sha256= если он есть
            $signature = str_replace('sha256=', '', $signature);
            
            $expectedSignature = hash_hmac('sha256', $payload, $secretKey);
            
            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Безопасное сравнение строк для предотвращения timing attacks
     *
     * @param string $expected
     * @param string $actual
     * @return bool
     */
    public static function safeCompare(string $expected, string $actual): bool
    {
        if (function_exists('hash_equals')) {
            return hash_equals($expected, $actual);
        }

        // Fallback для старых версий PHP
        if (strlen($expected) !== strlen($actual)) {
            return false;
        }

        $result = 0;
        for ($i = 0; $i < strlen($expected); $i++) {
            $result |= ord($expected[$i]) ^ ord($actual[$i]);
        }

        return $result === 0;
    }
}