<?php

namespace MadArlan\AituMessenger\Http;

use Psr\Http\Message\ResponseInterface;
use MadArlan\AituMessenger\Exceptions\AituApiException;

class ApiResponse
{
    private ResponseInterface $response;
    private array $data;
    private bool $success;
    private ?string $error;
    private ?string $errorDescription;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
        $this->parseResponse();
    }

    /**
     * Парсинг ответа API
     */
    private function parseResponse(): void
    {
        $content = $this->response->getBody()->getContents();
        
        if (empty($content)) {
            $this->data = [];
            $this->success = $this->response->getStatusCode() >= 200 && $this->response->getStatusCode() < 300;
            $this->error = null;
            $this->errorDescription = null;
            return;
        }

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new AituApiException(
                'Invalid JSON response: ' . json_last_error_msg(),
                0,
                null,
                ['response_body' => $content]
            );
        }

        $this->data = $decoded;
        
        // Определяем успешность запроса
        $statusCode = $this->response->getStatusCode();
        $this->success = $statusCode >= 200 && $statusCode < 300;

        // Извлекаем информацию об ошибке, если есть
        $this->error = $this->data['error'] ?? null;
        $this->errorDescription = $this->data['error_description'] ?? 
                                 $this->data['message'] ?? 
                                 $this->data['detail'] ?? null;

        // Если статус код указывает на ошибку, но в данных нет информации об ошибке
        if (!$this->success && !$this->error) {
            $this->error = 'http_error';
            $this->errorDescription = "HTTP {$statusCode}: " . $this->response->getReasonPhrase();
        }
    }

    /**
     * Проверить, успешен ли запрос
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Проверить, есть ли ошибка
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return !$this->success;
    }

    /**
     * Получить данные ответа
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Получить конкретное поле из данных
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Получить код ошибки
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Получить описание ошибки
     *
     * @return string|null
     */
    public function getErrorDescription(): ?string
    {
        return $this->errorDescription;
    }

    /**
     * Получить HTTP статус код
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Получить HTTP заголовки
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    /**
     * Получить конкретный заголовок
     *
     * @param string $name
     * @return string|null
     */
    public function getHeader(string $name): ?string
    {
        $headers = $this->response->getHeader($name);
        return $headers[0] ?? null;
    }

    /**
     * Получить оригинальный PSR-7 ответ
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Бросить исключение, если запрос неуспешен
     *
     * @throws AituApiException
     */
    public function throwIfError(): void
    {
        if ($this->hasError()) {
            throw new AituApiException(
                $this->errorDescription ?? 'API request failed',
                $this->getStatusCode(),
                null,
                [
                    'error' => $this->error,
                    'error_description' => $this->errorDescription,
                    'status_code' => $this->getStatusCode(),
                    'response_data' => $this->data,
                ]
            );
        }
    }

    /**
     * Проверить, содержит ли ответ пагинацию
     *
     * @return bool
     */
    public function hasPagination(): bool
    {
        return isset($this->data['pagination']) || 
               isset($this->data['meta']) || 
               isset($this->data['page_info']);
    }

    /**
     * Получить информацию о пагинации
     *
     * @return array|null
     */
    public function getPagination(): ?array
    {
        return $this->data['pagination'] ?? 
               $this->data['meta'] ?? 
               $this->data['page_info'] ?? null;
    }

    /**
     * Получить данные результата (обычно в поле 'data' или 'result')
     *
     * @return array
     */
    public function getResult(): array
    {
        return $this->data['data'] ?? 
               $this->data['result'] ?? 
               $this->data['items'] ?? 
               $this->data;
    }

    /**
     * Преобразовать в массив
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'status_code' => $this->getStatusCode(),
            'data' => $this->data,
            'error' => $this->error,
            'error_description' => $this->errorDescription,
            'headers' => $this->getHeaders(),
        ];
    }

    /**
     * Преобразовать в JSON
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Магический метод для доступа к данным как к свойствам
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Магический метод для проверки существования свойства
     *
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Преобразовать в строку
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}