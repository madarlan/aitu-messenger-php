<?php

namespace MadArlan\AituMessenger;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use MadArlan\AituMessenger\Exceptions\AituApiException;
use MadArlan\AituMessenger\Exceptions\AituAuthenticationException;
use MadArlan\AituMessenger\Utils\SignatureGenerator;

class AituAppsClient
{
    private Client $httpClient;
    private string $secretKey;
    private string $baseUrl;

    public function __construct(
        string $secretKey,
        string $baseUrl = 'https://api.miniapps.aitu.io',
        ?Client $httpClient = null
    ) {
        if (empty($secretKey)) {
            throw new AituAuthenticationException('Secret key cannot be empty');
        }

        $this->secretKey = $secretKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->httpClient = $httpClient ?? new Client();
    }

    /**
     * Отправить таргетированное push-уведомление
     *
     * @param array $notification Данные уведомления
     * @return array Ответ API
     * @throws AituApiException
     * @throws AituAuthenticationException
     */
    public function sendPushNotification(array $notification): array
    {
        $requiredFields = ['user_id', 'app_id', 'title', 'message'];
        foreach ($requiredFields as $field) {
            if (empty($notification[$field])) {
                throw new AituApiException("Field '{$field}' is required for push notification");
            }
        }

        // Устанавливаем значения по умолчанию
        $notification = array_merge([
            'locale' => 1, // Русский язык по умолчанию
            'to_url' => null,
        ], $notification);

        // Генерируем подпись
        $notification['sign'] = SignatureGenerator::generate($notification, $this->secretKey);

        try {
            $response = $this->httpClient->post(
                $this->baseUrl . '/kz.btsd.messenger.apps.public.MiniAppsPublicService/SendPush',
                [
                    'json' => $notification,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new AituApiException('Invalid JSON response from push notification endpoint');
            }

            return $data;
        } catch (GuzzleException $e) {
            throw new AituApiException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Отправить множественные push-уведомления
     *
     * @param array $notifications Массив уведомлений
     * @return array Результаты отправки
     */
    public function sendMultiplePushNotifications(array $notifications): array
    {
        $results = [];
        
        foreach ($notifications as $index => $notification) {
            try {
                $results[$index] = [
                    'success' => true,
                    'data' => $this->sendPushNotification($notification),
                ];
            } catch (\Exception $e) {
                $results[$index] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Создать уведомление с базовыми параметрами
     *
     * @param string $userId ID пользователя
     * @param string $appId ID приложения
     * @param string $title Заголовок
     * @param string $message Сообщение
     * @param int $locale Локализация (1-русский, 2-казахский, 3-английский, 4-узбекский)
     * @param string|null $toUrl URL для перехода
     * @return array
     */
    public function createNotification(
        string $userId,
        string $appId,
        string $title,
        string $message,
        int $locale = 1,
        ?string $toUrl = null
    ): array {
        $notification = [
            'user_id' => $userId,
            'app_id' => $appId,
            'title' => $title,
            'message' => $message,
            'locale' => $locale,
        ];

        if ($toUrl !== null) {
            $notification['to_url'] = $toUrl;
        }

        return $notification;
    }

    /**
     * Валидировать параметры уведомления
     *
     * @param array $notification
     * @return array Массив ошибок валидации
     */
    public function validateNotification(array $notification): array
    {
        $errors = [];

        // Проверка обязательных полей
        $requiredFields = ['user_id', 'app_id', 'title', 'message'];
        foreach ($requiredFields as $field) {
            if (empty($notification[$field])) {
                $errors[] = "Field '{$field}' is required";
            }
        }

        // Проверка длины заголовка
        if (isset($notification['title']) && mb_strlen($notification['title']) > 40) {
            $errors[] = 'Title should not exceed 40 characters';
        }

        // Проверка длины сообщения
        if (isset($notification['message']) && mb_strlen($notification['message']) > 100) {
            $errors[] = 'Message should not exceed 100 characters';
        }

        // Проверка локализации
        if (isset($notification['locale']) && !in_array($notification['locale'], [1, 2, 3, 4])) {
            $errors[] = 'Locale must be 1 (Russian), 2 (Kazakh), 3 (English), or 4 (Uzbek)';
        }

        // Проверка UUID формата для user_id и app_id
        if (isset($notification['user_id']) && !$this->isValidUuid($notification['user_id'])) {
            $errors[] = 'user_id must be a valid UUID';
        }

        if (isset($notification['app_id']) && !$this->isValidUuid($notification['app_id'])) {
            $errors[] = 'app_id must be a valid UUID';
        }

        return $errors;
    }

    /**
     * Отправить таргетированное push-уведомление
     *
     * @param string $userId UUID пользователя
     * @param string $appId ID приложения
     * @param string $title Заголовок уведомления
     * @param string $message Текст уведомления
     * @param array $options Дополнительные опции
     * @return array
     * @throws AituApiException
     */
    public function sendTargetedNotification(string $userId, string $appId, string $title, string $message, array $options = []): array
    {
        $notification = $this->createNotification($userId, $appId, $title, $message, $options['locale'] ?? 1, $options['to_url'] ?? null);
        
        return $this->sendPushNotification($notification);
    }

    /**
     * Отправить уведомление группе пользователей
     *
     * @param array $userIds Массив UUID пользователей
     * @param string $appId ID приложения
     * @param string $title Заголовок уведомления
     * @param string $message Текст уведомления
     * @param array $options Дополнительные опции
     * @return array
     * @throws AituApiException
     */
    public function sendGroupNotification(array $userIds, string $appId, string $title, string $message, array $options = []): array
    {
        $notifications = [];
        
        foreach ($userIds as $userId) {
            $notifications[] = $this->createNotification($userId, $appId, $title, $message, $options['locale'] ?? 1, $options['to_url'] ?? null);
        }

        return $this->sendMultiplePushNotifications($notifications);
    }

    /**
     * Получить статистику уведомлений
     *
     * @param array $filters Фильтры для статистики
     * @return array
     * @throws AituApiException
     */
    public function getNotificationStatistics(array $filters = []): array
    {
        try {
            $response = $this->httpClient->get(
                $this->baseUrl . '/kz.btsd.messenger.apps.public.MiniAppsPublicService/GetPushStatistics',
                [
                    'query' => $filters,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new AituApiException('Invalid JSON response from statistics endpoint');
            }

            return $data;
        } catch (GuzzleException $e) {
            throw new AituApiException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Проверить, является ли строка валидным UUID
     *
     * @param string $uuid
     * @return bool
     */
    private function isValidUuid(string $uuid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === 1;
    }
}