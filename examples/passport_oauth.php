<?php

/**
 * Пример использования Aitu Passport OAuth
 * 
 * Этот пример показывает полный цикл OAuth авторизации:
 * 1. Перенаправление пользователя на Aitu для авторизации
 * 2. Обработка callback с кодом авторизации
 * 3. Обмен кода на токен доступа
 * 4. Получение информации о пользователе
 */

require_once __DIR__ . '/../vendor/autoload.php';

use MadArlan\AituMessenger\AituPassportClient;
use MadArlan\AituMessenger\Exceptions\AituApiException;
use MadArlan\AituMessenger\Exceptions\AituAuthenticationException;

// Конфигурация
$config = [
    'client_id' => 'your_client_id',
    'client_secret' => 'your_client_secret',
    'redirect_uri' => 'http://localhost:8000/callback.php'
];

// Создание клиента
$client = new AituPassportClient(
    $config['client_id'],
    $config['client_secret'],
    $config['redirect_uri']
);

// Запуск сессии для хранения состояния
session_start();

// Определяем действие на основе URL
$action = $_GET['action'] ?? 'login';

switch ($action) {
    case 'login':
        handleLogin($client);
        break;
        
    case 'callback':
        handleCallback($client);
        break;
        
    case 'profile':
        showProfile($client);
        break;
        
    case 'refresh':
        refreshToken($client);
        break;
        
    case 'logout':
        handleLogout($client);
        break;
        
    default:
        showHomePage();
}

/**
 * Обработка начала авторизации
 */
function handleLogin(AituPassportClient $client): void
{
    // Генерируем случайное состояние для защиты от CSRF
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;
    
    // Запрашиваемые разрешения
    $scopes = ['profile', 'email', 'phone'];
    
    // Получаем URL для авторизации
    $authUrl = $client->getAuthorizationUrl($scopes, $state);
    
    echo "<h1>Авторизация через Aitu Passport</h1>";
    echo "<p>Нажмите на ссылку ниже для авторизации:</p>";
    echo "<a href='{$authUrl}' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Войти через Aitu</a>";
}

/**
 * Обработка callback после авторизации
 */
function handleCallback(AituPassportClient $client): void
{
    try {
        // Проверяем наличие кода авторизации
        $code = $_GET['code'] ?? null;
        $state = $_GET['state'] ?? null;
        $error = $_GET['error'] ?? null;
        
        if ($error) {
            throw new Exception("Ошибка авторизации: {$error}");
        }
        
        if (!$code) {
            throw new Exception("Код авторизации не получен");
        }
        
        // Проверяем состояние для защиты от CSRF
        if (!$state || $state !== ($_SESSION['oauth_state'] ?? '')) {
            throw new Exception("Недействительное состояние OAuth");
        }
        
        // Обмениваем код на токен
        $tokenData = $client->exchangeCodeForToken($code);
        
        // Сохраняем токен в сессии
        $_SESSION['access_token'] = $tokenData['access_token'];
        $_SESSION['refresh_token'] = $tokenData['refresh_token'] ?? null;
        $_SESSION['expires_at'] = time() + ($tokenData['expires_in'] ?? 3600);
        
        // Получаем информацию о пользователе
        $user = $client->getUserInfo($tokenData['access_token']);
        $_SESSION['user'] = $user->toArray();
        
        echo "<h1>✅ Авторизация успешна!</h1>";
        echo "<p>Добро пожаловать, {$user->getName()}!</p>";
        echo "<p><a href='?action=profile'>Посмотреть профиль</a></p>";
        
    } catch (AituAuthenticationException $e) {
        echo "<h1>❌ Ошибка аутентификации</h1>";
        echo "<p>Ошибка: {$e->getMessage()}</p>";
        echo "<p><a href='?action=login'>Попробовать снова</a></p>";
        
    } catch (AituApiException $e) {
        echo "<h1>❌ Ошибка API</h1>";
        echo "<p>Ошибка: {$e->getMessage()}</p>";
        echo "<p>Код: {$e->getCode()}</p>";
        
        $context = $e->getContext();
        if ($context) {
            echo "<p>Детали: " . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</p>";
        }
        
        echo "<p><a href='?action=login'>Попробовать снова</a></p>";
        
    } catch (Exception $e) {
        echo "<h1>❌ Общая ошибка</h1>";
        echo "<p>Ошибка: {$e->getMessage()}</p>";
        echo "<p><a href='?action=login'>Попробовать снова</a></p>";
    }
}

/**
 * Показ профиля пользователя
 */
function showProfile(AituPassportClient $client): void
{
    if (!isset($_SESSION['access_token']) || !isset($_SESSION['user'])) {
        echo "<h1>❌ Не авторизован</h1>";
        echo "<p><a href='?action=login'>Войти</a></p>";
        return;
    }
    
    // Проверяем, не истек ли токен
    if (time() >= ($_SESSION['expires_at'] ?? 0)) {
        echo "<h1>⏰ Токен истек</h1>";
        echo "<p><a href='?action=refresh'>Обновить токен</a> или <a href='?action=login'>Войти заново</a></p>";
        return;
    }
    
    $user = $_SESSION['user'];
    
    echo "<h1>👤 Профиль пользователя</h1>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    
    $fields = [
        'ID' => $user['id'] ?? 'Не указан',
        'Имя' => $user['name'] ?? 'Не указано',
        'Email' => $user['email'] ?? 'Не указан',
        'Телефон' => $user['phone'] ?? 'Не указан',
        'Аватар' => isset($user['avatar']) ? "<img src='{$user['avatar']}' width='50' height='50'>" : 'Нет',
        'Дата рождения' => $user['birth_date'] ?? 'Не указана',
        'Пол' => $user['gender'] ?? 'Не указан',
        'Город' => $user['city'] ?? 'Не указан',
        'Страна' => $user['country'] ?? 'Не указана',
        'Язык' => $user['language'] ?? 'Не указан',
        'Часовой пояс' => $user['timezone'] ?? 'Не указан',
        'Верифицирован' => ($user['is_verified'] ?? false) ? 'Да' : 'Нет',
    ];
    
    foreach ($fields as $label => $value) {
        echo "<tr><td><strong>{$label}</strong></td><td>{$value}</td></tr>";
    }
    
    echo "</table>";
    
    echo "<p>";
    echo "<a href='?action=refresh'>🔄 Обновить токен</a> | ";
    echo "<a href='?action=logout'>🚪 Выйти</a>";
    echo "</p>";
    
    // Показываем информацию о токене
    echo "<h2>🔑 Информация о токене</h2>";
    echo "<p><strong>Истекает:</strong> " . date('Y-m-d H:i:s', $_SESSION['expires_at']) . "</p>";
    echo "<p><strong>Осталось:</strong> " . gmdate('H:i:s', $_SESSION['expires_at'] - time()) . "</p>";
}

/**
 * Обновление токена
 */
function refreshToken(AituPassportClient $client): void
{
    if (!isset($_SESSION['refresh_token'])) {
        echo "<h1>❌ Refresh токен отсутствует</h1>";
        echo "<p><a href='?action=login'>Войти заново</a></p>";
        return;
    }
    
    try {
        $tokenData = $client->refreshToken($_SESSION['refresh_token']);
        
        // Обновляем токены в сессии
        $_SESSION['access_token'] = $tokenData['access_token'];
        $_SESSION['refresh_token'] = $tokenData['refresh_token'] ?? $_SESSION['refresh_token'];
        $_SESSION['expires_at'] = time() + ($tokenData['expires_in'] ?? 3600);
        
        echo "<h1>✅ Токен обновлен</h1>";
        echo "<p><a href='?action=profile'>Вернуться к профилю</a></p>";
        
    } catch (AituAuthenticationException $e) {
        echo "<h1>❌ Ошибка обновления токена</h1>";
        echo "<p>Ошибка: {$e->getMessage()}</p>";
        echo "<p>Требуется повторная авторизация.</p>";
        echo "<p><a href='?action=login'>Войти заново</a></p>";
        
        // Очищаем сессию
        session_destroy();
        
    } catch (Exception $e) {
        echo "<h1>❌ Ошибка</h1>";
        echo "<p>Ошибка: {$e->getMessage()}</p>";
        echo "<p><a href='?action=profile'>Вернуться к профилю</a></p>";
    }
}

/**
 * Выход из системы
 */
function handleLogout(AituPassportClient $client): void
{
    if (isset($_SESSION['access_token'])) {
        try {
            // Отзываем токен на сервере Aitu
            $client->revokeToken($_SESSION['access_token']);
        } catch (Exception $e) {
            // Игнорируем ошибки отзыва токена
        }
    }
    
    // Очищаем сессию
    session_destroy();
    
    echo "<h1>👋 Вы вышли из системы</h1>";
    echo "<p><a href='?action=login'>Войти снова</a></p>";
}

/**
 * Главная страница
 */
function showHomePage(): void
{
    echo "<h1>🚀 Aitu Passport OAuth Example</h1>";
    echo "<p>Этот пример демонстрирует работу с Aitu Passport API.</p>";
    
    if (isset($_SESSION['access_token'])) {
        echo "<p>✅ Вы авторизованы!</p>";
        echo "<p><a href='?action=profile'>Посмотреть профиль</a></p>";
    } else {
        echo "<p>❌ Вы не авторизованы.</p>";
        echo "<p><a href='?action=login'>Войти через Aitu</a></p>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Aitu Passport OAuth Example</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        table { width: 100%; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <!-- Контент уже выведен выше -->
</body>
</html>