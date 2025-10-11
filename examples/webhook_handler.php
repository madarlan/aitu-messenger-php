<?php

/**
 * Пример обработчика Aitu Webhooks
 * 
 * Этот пример показывает как обрабатывать входящие webhook события от Aitu:
 * 1. Проверка подписи webhook
 * 2. Обработка различных типов событий
 * 3. Логирование и отладка
 * 4. Ответы на webhook запросы
 */

require_once __DIR__ . '/../vendor/autoload.php';

use MadArlan\AituMessenger\Utils\SignatureValidator;

// Конфигурация
$config = [
    'webhook_secret' => 'your_webhook_secret_here',
    'log_file' => __DIR__ . '/webhook.log'
];

// Получаем данные запроса
$method = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$payload = file_get_contents('php://input');
$signature = $headers['X-Aitu-Signature'] ?? '';

// Логирование
function logWebhook(string $message, array $data = []): void
{
    global $config;
    
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'data' => $data
    ];
    
    file_put_contents(
        $config['log_file'],
        json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n",
        FILE_APPEND | LOCK_EX
    );
}

// Функция ответа
function sendResponse(int $code, array $data = []): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Проверяем метод запроса
if ($method !== 'POST') {
    logWebhook('Invalid request method', ['method' => $method]);
    sendResponse(405, ['error' => 'Method not allowed']);
}

// Проверяем наличие подписи
if (empty($signature)) {
    logWebhook('Missing signature header');
    sendResponse(400, ['error' => 'Missing X-Aitu-Signature header']);
}

// Проверяем подпись
if (!SignatureValidator::verifyWebhookSignature($payload, $signature, $config['webhook_secret'])) {
    logWebhook('Invalid signature', [
        'signature' => $signature,
        'payload_length' => strlen($payload)
    ]);
    sendResponse(401, ['error' => 'Invalid signature']);
}

// Парсим payload
$data = json_decode($payload, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    logWebhook('Invalid JSON payload', ['error' => json_last_error_msg()]);
    sendResponse(400, ['error' => 'Invalid JSON payload']);
}

// Определяем тип события
$eventType = $data['event_type'] ?? 'unknown';
$source = $data['source'] ?? 'unknown';

logWebhook('Webhook received', [
    'event_type' => $eventType,
    'source' => $source,
    'data' => $data
]);

// Обрабатываем события в зависимости от источника и типа
try {
    switch ($source) {
        case 'aitu_passport':
            handlePassportEvent($eventType, $data);
            break;
            
        case 'aitu_apps':
            handleAppsEvent($eventType, $data);
            break;
            
        default:
            logWebhook('Unknown webhook source', ['source' => $source]);
            sendResponse(400, ['error' => 'Unknown webhook source']);
    }
    
    // Успешная обработка
    sendResponse(200, ['status' => 'success', 'message' => 'Event processed']);
    
} catch (Exception $e) {
    logWebhook('Error processing webhook', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    sendResponse(500, ['error' => 'Internal server error']);
}

/**
 * Обработка событий Aitu Passport
 */
function handlePassportEvent(string $eventType, array $data): void
{
    switch ($eventType) {
        case 'user.authorized':
            handleUserAuthorized($data);
            break;
            
        case 'user.deauthorized':
            handleUserDeauthorized($data);
            break;
            
        case 'token.revoked':
            handleTokenRevoked($data);
            break;
            
        case 'user.updated':
            handleUserUpdated($data);
            break;
            
        default:
            logWebhook('Unknown Passport event type', ['event_type' => $eventType]);
    }
}

/**
 * Обработка событий Aitu Apps
 */
function handleAppsEvent(string $eventType, array $data): void
{
    switch ($eventType) {
        case 'notification.delivered':
            handleNotificationDelivered($data);
            break;
            
        case 'notification.clicked':
            handleNotificationClicked($data);
            break;
            
        case 'notification.failed':
            handleNotificationFailed($data);
            break;
            
        case 'app.installed':
            handleAppInstalled($data);
            break;
            
        case 'app.uninstalled':
            handleAppUninstalled($data);
            break;
            
        default:
            logWebhook('Unknown Apps event type', ['event_type' => $eventType]);
    }
}

/**
 * Пользователь авторизовался через Aitu Passport
 */
function handleUserAuthorized(array $data): void
{
    $userId = $data['user_id'] ?? null;
    $userInfo = $data['user_info'] ?? [];
    $tokens = $data['tokens'] ?? [];
    
    logWebhook('User authorized', [
        'user_id' => $userId,
        'has_user_info' => !empty($userInfo),
        'has_tokens' => !empty($tokens)
    ]);
    
    // Здесь можно:
    // 1. Сохранить пользователя в базу данных
    // 2. Сохранить токены
    // 3. Отправить welcome уведомление
    // 4. Синхронизировать данные с внутренней системой
    
    if ($userId) {
        // Пример: сохранение в файл (в реальном приложении используйте БД)
        $userFile = __DIR__ . "/users/{$userId}.json";
        @mkdir(dirname($userFile), 0755, true);
        
        $userData = [
            'user_id' => $userId,
            'user_info' => $userInfo,
            'tokens' => $tokens,
            'authorized_at' => date('Y-m-d H:i:s'),
            'last_activity' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($userFile, json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        logWebhook('User data saved', ['user_id' => $userId, 'file' => $userFile]);
    }
}

/**
 * Пользователь отозвал авторизацию
 */
function handleUserDeauthorized(array $data): void
{
    $userId = $data['user_id'] ?? null;
    
    logWebhook('User deauthorized', ['user_id' => $userId]);
    
    if ($userId) {
        // Удаляем токены и помечаем пользователя как неактивного
        $userFile = __DIR__ . "/users/{$userId}.json";
        
        if (file_exists($userFile)) {
            $userData = json_decode(file_get_contents($userFile), true);
            $userData['tokens'] = null;
            $userData['deauthorized_at'] = date('Y-m-d H:i:s');
            $userData['is_active'] = false;
            
            file_put_contents($userFile, json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            logWebhook('User marked as deauthorized', ['user_id' => $userId]);
        }
    }
}

/**
 * Токен был отозван
 */
function handleTokenRevoked(array $data): void
{
    $userId = $data['user_id'] ?? null;
    $tokenType = $data['token_type'] ?? 'unknown';
    
    logWebhook('Token revoked', [
        'user_id' => $userId,
        'token_type' => $tokenType
    ]);
    
    // Обновляем статус токенов пользователя
    if ($userId) {
        $userFile = __DIR__ . "/users/{$userId}.json";
        
        if (file_exists($userFile)) {
            $userData = json_decode(file_get_contents($userFile), true);
            
            if ($tokenType === 'access_token') {
                $userData['tokens']['access_token'] = null;
            } elseif ($tokenType === 'refresh_token') {
                $userData['tokens']['refresh_token'] = null;
            }
            
            $userData['token_revoked_at'] = date('Y-m-d H:i:s');
            
            file_put_contents($userFile, json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}

/**
 * Информация о пользователе обновилась
 */
function handleUserUpdated(array $data): void
{
    $userId = $data['user_id'] ?? null;
    $userInfo = $data['user_info'] ?? [];
    
    logWebhook('User updated', [
        'user_id' => $userId,
        'updated_fields' => array_keys($userInfo)
    ]);
    
    if ($userId) {
        $userFile = __DIR__ . "/users/{$userId}.json";
        
        if (file_exists($userFile)) {
            $userData = json_decode(file_get_contents($userFile), true);
            $userData['user_info'] = array_merge($userData['user_info'] ?? [], $userInfo);
            $userData['updated_at'] = date('Y-m-d H:i:s');
            
            file_put_contents($userFile, json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}

/**
 * Уведомление доставлено
 */
function handleNotificationDelivered(array $data): void
{
    $notificationId = $data['notification_id'] ?? null;
    $userId = $data['user_id'] ?? null;
    $deliveredAt = $data['delivered_at'] ?? date('Y-m-d H:i:s');
    
    logWebhook('Notification delivered', [
        'notification_id' => $notificationId,
        'user_id' => $userId,
        'delivered_at' => $deliveredAt
    ]);
    
    // Обновляем статистику доставки
    updateNotificationStats($notificationId, 'delivered', $userId);
}

/**
 * По уведомлению кликнули
 */
function handleNotificationClicked(array $data): void
{
    $notificationId = $data['notification_id'] ?? null;
    $userId = $data['user_id'] ?? null;
    $clickedAt = $data['clicked_at'] ?? date('Y-m-d H:i:s');
    $clickAction = $data['click_action'] ?? null;
    
    logWebhook('Notification clicked', [
        'notification_id' => $notificationId,
        'user_id' => $userId,
        'clicked_at' => $clickedAt,
        'click_action' => $clickAction
    ]);
    
    // Обновляем статистику кликов
    updateNotificationStats($notificationId, 'clicked', $userId);
    
    // Можно отправить аналитику или выполнить дополнительные действия
}

/**
 * Уведомление не удалось доставить
 */
function handleNotificationFailed(array $data): void
{
    $notificationId = $data['notification_id'] ?? null;
    $userId = $data['user_id'] ?? null;
    $error = $data['error'] ?? 'Unknown error';
    $failedAt = $data['failed_at'] ?? date('Y-m-d H:i:s');
    
    logWebhook('Notification failed', [
        'notification_id' => $notificationId,
        'user_id' => $userId,
        'error' => $error,
        'failed_at' => $failedAt
    ]);
    
    // Обновляем статистику ошибок
    updateNotificationStats($notificationId, 'failed', $userId, $error);
}

/**
 * Приложение установлено
 */
function handleAppInstalled(array $data): void
{
    $userId = $data['user_id'] ?? null;
    $installedAt = $data['installed_at'] ?? date('Y-m-d H:i:s');
    
    logWebhook('App installed', [
        'user_id' => $userId,
        'installed_at' => $installedAt
    ]);
    
    // Можно отправить welcome уведомление или выполнить onboarding
}

/**
 * Приложение удалено
 */
function handleAppUninstalled(array $data): void
{
    $userId = $data['user_id'] ?? null;
    $uninstalledAt = $data['uninstalled_at'] ?? date('Y-m-d H:i:s');
    
    logWebhook('App uninstalled', [
        'user_id' => $userId,
        'uninstalled_at' => $uninstalledAt
    ]);
    
    // Помечаем пользователя как неактивного
    if ($userId) {
        $userFile = __DIR__ . "/users/{$userId}.json";
        
        if (file_exists($userFile)) {
            $userData = json_decode(file_get_contents($userFile), true);
            $userData['app_installed'] = false;
            $userData['uninstalled_at'] = $uninstalledAt;
            
            file_put_contents($userFile, json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}

/**
 * Обновление статистики уведомлений
 */
function updateNotificationStats(string $notificationId, string $event, ?string $userId = null, ?string $error = null): void
{
    $statsFile = __DIR__ . "/stats/{$notificationId}.json";
    @mkdir(dirname($statsFile), 0755, true);
    
    $stats = [];
    if (file_exists($statsFile)) {
        $stats = json_decode(file_get_contents($statsFile), true) ?? [];
    }
    
    if (!isset($stats['events'])) {
        $stats['events'] = [];
    }
    
    $stats['events'][] = [
        'event' => $event,
        'user_id' => $userId,
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $error
    ];
    
    // Подсчитываем общую статистику
    $stats['summary'] = [
        'delivered' => 0,
        'clicked' => 0,
        'failed' => 0
    ];
    
    foreach ($stats['events'] as $eventData) {
        if (isset($stats['summary'][$eventData['event']])) {
            $stats['summary'][$eventData['event']]++;
        }
    }
    
    file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    logWebhook('Notification stats updated', [
        'notification_id' => $notificationId,
        'event' => $event,
        'summary' => $stats['summary']
    ]);
}

?>