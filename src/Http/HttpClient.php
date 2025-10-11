<?php

namespace MadArlan\AituMessenger\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use MadArlan\AituMessenger\Exceptions\AituApiException;

class HttpClient
{
    private Client $client;
    private array $defaultOptions;

    public function __construct(?Client $client = null, array $defaultOptions = [])
    {
        $this->client = $client ?? new Client();
        $this->defaultOptions = array_merge([
            RequestOptions::TIMEOUT => 30,
            RequestOptions::CONNECT_TIMEOUT => 10,
            RequestOptions::HEADERS => [
                'User-Agent' => 'AituMessenger-PHP-SDK/1.0',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ], $defaultOptions);
    }

    /**
     * Выполнить GET запрос
     *
     * @param string $url
     * @param array $options
     * @return ResponseInterface
     * @throws AituApiException
     */
    public function get(string $url, array $options = []): ResponseInterface
    {
        return $this->request('GET', $url, $options);
    }

    /**
     * Выполнить POST запрос
     *
     * @param string $url
     * @param array $data
     * @param array $options
     * @return ResponseInterface
     * @throws AituApiException
     */
    public function post(string $url, array $data = [], array $options = []): ResponseInterface
    {
        if (!empty($data)) {
            $options[RequestOptions::JSON] = $data;
        }

        return $this->request('POST', $url, $options);
    }

    /**
     * Выполнить PUT запрос
     *
     * @param string $url
     * @param array $data
     * @param array $options
     * @return ResponseInterface
     * @throws AituApiException
     */
    public function put(string $url, array $data = [], array $options = []): ResponseInterface
    {
        if (!empty($data)) {
            $options[RequestOptions::JSON] = $data;
        }

        return $this->request('PUT', $url, $options);
    }

    /**
     * Выполнить PATCH запрос
     *
     * @param string $url
     * @param array $data
     * @param array $options
     * @return ResponseInterface
     * @throws AituApiException
     */
    public function patch(string $url, array $data = [], array $options = []): ResponseInterface
    {
        if (!empty($data)) {
            $options[RequestOptions::JSON] = $data;
        }

        return $this->request('PATCH', $url, $options);
    }

    /**
     * Выполнить DELETE запрос
     *
     * @param string $url
     * @param array $options
     * @return ResponseInterface
     * @throws AituApiException
     */
    public function delete(string $url, array $options = []): ResponseInterface
    {
        return $this->request('DELETE', $url, $options);
    }

    /**
     * Выполнить HTTP запрос
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @return ResponseInterface
     * @throws AituApiException
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $options = $this->mergeOptions($options);

        try {
            $response = $this->client->request($method, $url, $options);
            
            // Логирование успешного запроса (если включено)
            $this->logRequest($method, $url, $options, $response);
            
            return $response;
        } catch (GuzzleException $e) {
            // Логирование ошибки (если включено)
            $this->logError($method, $url, $options, $e);
            
            throw new AituApiException(
                'HTTP request failed: ' . $e->getMessage(),
                $e->getCode(),
                $e,
                [
                    'method' => $method,
                    'url' => $url,
                    'options' => $this->sanitizeOptions($options),
                ]
            );
        }
    }

    /**
     * Выполнить запрос с формой
     *
     * @param string $method
     * @param string $url
     * @param array $formData
     * @param array $options
     * @return ResponseInterface
     * @throws AituApiException
     */
    public function requestWithForm(string $method, string $url, array $formData = [], array $options = []): ResponseInterface
    {
        $options[RequestOptions::FORM_PARAMS] = $formData;
        $options[RequestOptions::HEADERS]['Content-Type'] = 'application/x-www-form-urlencoded';

        return $this->request($method, $url, $options);
    }

    /**
     * Выполнить запрос с multipart данными
     *
     * @param string $method
     * @param string $url
     * @param array $multipartData
     * @param array $options
     * @return ResponseInterface
     * @throws AituApiException
     */
    public function requestWithMultipart(string $method, string $url, array $multipartData = [], array $options = []): ResponseInterface
    {
        $options[RequestOptions::MULTIPART] = $multipartData;
        unset($options[RequestOptions::HEADERS]['Content-Type']); // Guzzle установит автоматически

        return $this->request($method, $url, $options);
    }

    /**
     * Получить JSON ответ
     *
     * @param ResponseInterface $response
     * @return array
     * @throws AituApiException
     */
    public function getJsonResponse(ResponseInterface $response): array
    {
        $content = $response->getBody()->getContents();
        
        if (empty($content)) {
            return [];
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new AituApiException(
                'Invalid JSON response: ' . json_last_error_msg(),
                0,
                null,
                ['response_body' => $content]
            );
        }

        return $data;
    }

    /**
     * Объединить опции с настройками по умолчанию
     *
     * @param array $options
     * @return array
     */
    private function mergeOptions(array $options): array
    {
        $merged = $this->defaultOptions;

        foreach ($options as $key => $value) {
            if ($key === RequestOptions::HEADERS && isset($merged[RequestOptions::HEADERS])) {
                $merged[RequestOptions::HEADERS] = array_merge($merged[RequestOptions::HEADERS], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Очистить опции от чувствительных данных для логирования
     *
     * @param array $options
     * @return array
     */
    private function sanitizeOptions(array $options): array
    {
        $sanitized = $options;

        // Удаляем чувствительные заголовки
        if (isset($sanitized[RequestOptions::HEADERS])) {
            $sensitiveHeaders = ['Authorization', 'X-API-Key', 'Cookie'];
            foreach ($sensitiveHeaders as $header) {
                if (isset($sanitized[RequestOptions::HEADERS][$header])) {
                    $sanitized[RequestOptions::HEADERS][$header] = '***';
                }
            }
        }

        // Удаляем чувствительные данные из тела запроса
        if (isset($sanitized[RequestOptions::JSON])) {
            $sensitiveFields = ['password', 'secret', 'token', 'key'];
            foreach ($sensitiveFields as $field) {
                if (isset($sanitized[RequestOptions::JSON][$field])) {
                    $sanitized[RequestOptions::JSON][$field] = '***';
                }
            }
        }

        return $sanitized;
    }

    /**
     * Логировать запрос (заглушка для будущего логирования)
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @param ResponseInterface $response
     */
    private function logRequest(string $method, string $url, array $options, ResponseInterface $response): void
    {
        // Здесь можно добавить логирование запросов
        // Например, через Laravel Log или Monolog
    }

    /**
     * Логировать ошибку (заглушка для будущего логирования)
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @param GuzzleException $exception
     */
    private function logError(string $method, string $url, array $options, GuzzleException $exception): void
    {
        // Здесь можно добавить логирование ошибок
        // Например, через Laravel Log или Monolog
    }

    /**
     * Установить базовый URL для всех запросов
     *
     * @param string $baseUrl
     * @return self
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->defaultOptions[RequestOptions::BASE_URI] = rtrim($baseUrl, '/') . '/';
        return $this;
    }

    /**
     * Установить заголовок авторизации
     *
     * @param string $token
     * @param string $type
     * @return self
     */
    public function setAuthorizationHeader(string $token, string $type = 'Bearer'): self
    {
        $this->defaultOptions[RequestOptions::HEADERS]['Authorization'] = $type . ' ' . $token;
        return $this;
    }

    /**
     * Получить базовый Guzzle клиент
     *
     * @return Client
     */
    public function getGuzzleClient(): Client
    {
        return $this->client;
    }
}