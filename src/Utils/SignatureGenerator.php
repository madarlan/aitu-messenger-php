<?php

namespace MadArlan\AituMessenger\Utils;

class SignatureGenerator
{
    /**
     * Генерировать подпись для данных
     *
     * @param array $data Данные для подписи
     * @param string $secretKey Секретный ключ
     * @return string Base64 закодированная подпись
     */
    public static function generate(array $data, string $secretKey): string
    {
        // Исключаем поле sign из данных, если оно есть
        $dataForSigning = $data;
        unset($dataForSigning['sign']);

        // Сортируем ключи для консистентности
        ksort($dataForSigning);

        // Создаем строку для подписи
        $stringToSign = self::buildSignatureString($dataForSigning);

        // Генерируем HMAC-SHA256 подпись
        $signature = hash_hmac('sha256', $stringToSign, $secretKey, true);

        // Возвращаем base64 закодированную подпись
        return base64_encode($signature);
    }

    /**
     * Построить строку для подписи из массива данных
     *
     * @param array $data
     * @return string
     */
    private static function buildSignatureString(array $data): string
    {
        $parts = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } else {
                $value = (string) $value;
            }

            $parts[] = $key . '=' . $value;
        }

        return implode('&', $parts);
    }

    /**
     * Генерировать подпись для OAuth запроса
     *
     * @param string $method HTTP метод
     * @param string $url URL запроса
     * @param array $params Параметры запроса
     * @param string $clientSecret Секрет клиента
     * @return string
     */
    public static function generateOAuthSignature(
        string $method,
        string $url,
        array $params,
        string $clientSecret
    ): string {
        // Сортируем параметры
        ksort($params);

        // Создаем строку параметров
        $paramString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        // Создаем базовую строку для подписи
        $baseString = strtoupper($method) . '&' . 
                     rawurlencode($url) . '&' . 
                     rawurlencode($paramString);

        // Создаем ключ для подписи
        $signingKey = rawurlencode($clientSecret) . '&';

        // Генерируем подпись
        $signature = hash_hmac('sha1', $baseString, $signingKey, true);

        return base64_encode($signature);
    }
}