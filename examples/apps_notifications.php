<?php

/**
 * Пример использования Aitu Apps API для отправки push-уведомлений
 * 
 * Этот пример показывает различные способы отправки уведомлений:
 * 1. Уведомление конкретному пользователю
 * 2. Массовая отправка
 * 3. Групповые уведомления
 * 4. Broadcast уведомления
 * 5. Отложенная отправка
 * 6. Получение статистики
 */

require_once __DIR__ . '/../vendor/autoload.php';

use MadArlan\AituMessenger\AituAppsClient;
use MadArlan\AituMessenger\Exceptions\AituApiException;

// Конфигурация
$config = [
    'app_id' => 'your_app_id',
    'app_secret' => 'your_app_secret'
];

// Создание клиента
$client = new AituAppsClient($config['app_id'], $config['app_secret']);

// Определяем действие
$action = $_GET['action'] ?? 'menu';

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Aitu Apps Notifications Example</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        .menu { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .menu a { margin-right: 15px; padding: 8px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 3px; }
        .menu a:hover { background: #0056b3; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
        .btn { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; }
        .btn:hover { background: #218838; }
        .result { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin-top: 20px; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin-top: 20px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>";

// Меню навигации
echo "<div class='menu'>
    <a href='?action=menu'>🏠 Главная</a>
    <a href='?action=targeted'>👤 Персональное</a>
    <a href='?action=multiple'>👥 Массовое</a>
    <a href='?action=group'>🏢 Групповое</a>
    <a href='?action=broadcast'>📢 Всем</a>
    <a href='?action=scheduled'>⏰ Отложенное</a>
    <a href='?action=stats'>📊 Статистика</a>
</div>";

switch ($action) {
    case 'menu':
        showMenu();
        break;
        
    case 'targeted':
        handleTargetedNotification($client);
        break;
        
    case 'multiple':
        handleMultipleNotifications($client);
        break;
        
    case 'group':
        handleGroupNotification($client);
        break;
        
    case 'broadcast':
        handleBroadcastNotification($client);
        break;
        
    case 'scheduled':
        handleScheduledNotification($client);
        break;
        
    case 'stats':
        handleNotificationStats($client);
        break;
        
    default:
        showMenu();
}

echo "</body></html>";

/**
 * Главное меню
 */
function showMenu(): void
{
    echo "<h1>🚀 Aitu Apps Notifications Example</h1>";
    echo "<p>Выберите тип уведомления для отправки:</p>";
    
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;'>";
    
    $options = [
        'targeted' => ['👤 Персональное уведомление', 'Отправить уведомление конкретному пользователю'],
        'multiple' => ['👥 Массовое уведомление', 'Отправить уведомление нескольким пользователям'],
        'group' => ['🏢 Групповое уведомление', 'Отправить уведомление участникам группы'],
        'broadcast' => ['📢 Broadcast уведомление', 'Отправить уведомление всем пользователям'],
        'scheduled' => ['⏰ Отложенное уведомление', 'Запланировать отправку уведомления'],
        'stats' => ['📊 Статистика уведомлений', 'Посмотреть статистику отправленных уведомлений']
    ];
    
    foreach ($options as $action => $info) {
        echo "<div style='border: 1px solid #ddd; padding: 20px; border-radius: 5px;'>";
        echo "<h3>{$info[0]}</h3>";
        echo "<p>{$info[1]}</p>";
        echo "<a href='?action={$action}' class='btn'>Перейти</a>";
        echo "</div>";
    }
    
    echo "</div>";
}

/**
 * Персональное уведомление
 */
function handleTargetedNotification(AituAppsClient $client): void
{
    echo "<h1>👤 Персональное уведомление</h1>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $notification = [
                'title' => $_POST['title'],
                'body' => $_POST['body'],
                'user_id' => $_POST['user_id']
            ];
            
            // Добавляем дополнительные параметры если указаны
            if (!empty($_POST['icon'])) {
                $notification['icon'] = $_POST['icon'];
            }
            
            if (!empty($_POST['image'])) {
                $notification['image'] = $_POST['image'];
            }
            
            if (!empty($_POST['click_action'])) {
                $notification['click_action'] = $_POST['click_action'];
            }
            
            if (!empty($_POST['custom_data'])) {
                $notification['data'] = json_decode($_POST['custom_data'], true) ?: [];
            }
            
            $response = $client->sendTargetedNotification($notification);
            
            if ($response->isSuccessful()) {
                echo "<div class='result'>";
                echo "<h3>✅ Уведомление отправлено успешно!</h3>";
                echo "<p><strong>ID уведомления:</strong> " . $response->getData('notification_id') . "</p>";
                echo "<pre>" . json_encode($response->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                echo "</div>";
            } else {
                $error = $response->getError();
                echo "<div class='error'>";
                echo "<h3>❌ Ошибка отправки</h3>";
                echo "<p><strong>Сообщение:</strong> {$error['message']}</p>";
                echo "<p><strong>Код:</strong> {$error['code']}</p>";
                echo "</div>";
            }
            
        } catch (AituApiException $e) {
            echo "<div class='error'>";
            echo "<h3>❌ Ошибка API</h3>";
            echo "<p><strong>Сообщение:</strong> {$e->getMessage()}</p>";
            echo "<p><strong>Код:</strong> {$e->getCode()}</p>";
            echo "</div>";
        }
    }
    
    // Форма отправки
    echo "<form method='POST'>";
    
    echo "<div class='form-group'>";
    echo "<label>ID пользователя (UUID):</label>";
    echo "<input type='text' name='user_id' required placeholder='xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx' value='" . ($_POST['user_id'] ?? '') . "'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label>Заголовок:</label>";
    echo "<input type='text' name='title' required placeholder='Заголовок уведомления' value='" . ($_POST['title'] ?? '') . "'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label>Текст:</label>";
    echo "<textarea name='body' required placeholder='Текст уведомления'>" . ($_POST['body'] ?? '') . "</textarea>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label>Иконка (URL, необязательно):</label>";
    echo "<input type='url' name='icon' placeholder='https://example.com/icon.png' value='" . ($_POST['icon'] ?? '') . "'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label>Изображение (URL, необязательно):</label>";
    echo "<input type='url' name='image' placeholder='https://example.com/image.jpg' value='" . ($_POST['image'] ?? '') . "'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label>Действие при клике (URL, необязательно):</label>";
    echo "<input type='url' name='click_action' placeholder='https://example.com/action' value='" . ($_POST['click_action'] ?? '') . "'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label>Дополнительные данные (JSON, необязательно):</label>";
    echo "<textarea name='custom_data' placeholder='{\"key\": \"value\", \"order_id\": 123}'>" . ($_POST['custom_data'] ?? '') . "</textarea>";
    echo "</div>";
    
    echo "<button type='submit' class='btn'>📤 Отправить уведомление</button>";
    echo "</form>";
}

/**
 * Массовое уведомление
 */
function handleMultipleNotifications(AituAppsClient $client): void
{
    echo "<h1>👥 Массовое уведомление</h1>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $notification = $client->createNotification(
                $_POST['title'],
                $_POST['body']
            );
            
            // Парсим список пользователей
            $userIds = array_filter(array_map('trim', explode("\n", $_POST['user_ids'])));
            
            if (empty($userIds)) {
                throw new Exception("Список пользователей не может быть пустым");
            }
            
            $response = $client->sendMultipleNotifications($notification, $userIds);
            
            if ($response->isSuccessful()) {
                echo "<div class='result'>";
                echo "<h3>✅ Массовое уведомление отправлено!</h3>";
                echo "<p><strong>Отправлено пользователям:</strong> " . count($userIds) . "</p>";
                echo "<pre>" . json_encode($response->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                echo "</div>";
            } else {
                $error = $response->getError();
                echo "<div class='error'>";
                echo "<h3>❌ Ошибка отправки</h3>";
                echo "<p><strong>Сообщение:</strong> {$error['message']}</p>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<h3>❌ Ошибка</h3>";
            echo "<p><strong>Сообщение:</strong> {$e->getMessage()}</p>";
            echo "</div>";
        }
    }
    
    // Форма отправки
    echo "<form method='POST'>";
    
    echo "<div class='form-group'>";
    echo "<label>Заголовок:</label>";
    echo "<input type='text' name='title' required placeholder='Заголовок уведомления' value='" . ($_POST['title'] ?? '') . "'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label>Текст:</label>";
    echo "<textarea name='body' required placeholder='Текст уведомления'>" . ($_POST['body'] ?? '') . "</textarea>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label>ID пользователей (по одному на строку):</label>";
    echo "<textarea name='user_ids' required placeholder='xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx\nyyyyyyyy-yyyy-yyyy-yyyy-yyyyyyyyyyyy' rows='5'>" . ($_POST['user_ids'] ?? '') . "</textarea>";
    echo "</div>";
    
    echo "<button type='submit' class='btn'>📤 Отправить массовое уведомление</button>";
    echo "</form>";
}

/**
 * Групповое уведомление
 */
function handleGroupNotification(AituAppsClient $client): void
{
    echo "<h1>🏢 Групповое уведомление</h1>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $notification = $client->createNotification(
                $_POST['title'],
                $_POST['body']
            );
            
            $response = $client->sendGroupNotification($notification, $_POST['group_id']);
            
            if ($response->isSuccessful()) {
                echo "<div class='result'>";
                echo "<h3>✅ Групповое уведомление отправлено!</h3>";
                echo "<pre>" . json_encode($response->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                echo "</div>";
            } else {
                $error = $response->getError();
                echo "<div class='error'>";
                echo "<h3>❌ Ошибка отправки</h3>";
                echo "<p><strong>Сообщение:</strong> {$error['message']}</p>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<h3>❌ Ошибка</h3>";
            echo "<p><strong>Сообщение:</strong> {$e->getMessage()}</p>";
            echo "</div>";
        }
    }
    
    // Форма отправки
    echo "<form method='POST'>";
    
    echo "<div class='form-group'>";
    echo "<label>ID группы:</label>";
    echo "<input type='text' name='group_id' required placeholder='group_id_here' value='" . ($_POST['group_id'] ?? '') . "'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label>Заголовок:</label>";
    echo "<input type='text' name='title' required placeholder='Заголовок уведомления' value='" . ($_POST['title'] ?? '') . "'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label>Текст:</label>";
    echo "<textarea name='body' required placeholder='Текст уведомления'>" . ($_POST['body'] ?? '') . "</textarea>";
    echo "</div>";
    
    echo "<button type='submit' class='btn'>📤 Отправить групповое уведомление</button>";
    echo "</form>";
}

/**
 * Broadcast уведомление
 */
function handleBroadcastNotification(AituAppsClient $client): void
{
    echo "<h1>📢 Broadcast уведомление</h1>";
    echo "<p><strong>⚠️ Внимание:</strong> Это уведомление будет отправлено ВСЕМ пользователям вашего приложения!</p>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $notification = $client->createNotification(
                $_POST['title'],
                $_POST['body']
            );
            
            $response = $client->sendBroadcastNotification($notification);
            
            if ($response->isSuccessful()) {
                echo "<div class='result'>";
                echo "<h3>✅ Broadcast уведомление отправлено!</h3>";
                echo "<pre>" . json_encode($response->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                echo "</div>";
            } else {
                $error = $response->getError();
                echo "<div class='error'>";
                echo "<h3>❌ Ошибка отправки</h3>";
                echo "<p><strong>Сообщение:</strong> {$error['message']}</p>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<h3>❌ Ошибка</h3>";
            echo "<p><strong>Сообщение:</strong> {$e->getMessage()}</p>";
            echo "</div>";
        }
    }
    
    // Форма отправки
    echo "<form method='POST' onsubmit='return confirm(\"Вы уверены, что хотите отправить уведомление ВСЕМ пользователям?\");'>";
    
    echo "<div class='form-group'>";
    echo "<label>Заголовок:</label>";
    echo "<input type='text' name='title' required placeholder='Важное объявление' value='" . ($_POST['title'] ?? '') . "'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label>Текст:</label>";
    echo "<textarea name='body' required placeholder='Текст важного объявления для всех пользователей'>" . ($_POST['body'] ?? '') . "</textarea>";
    echo "</div>";
    
    echo "<button type='submit' class='btn' style='background: #dc3545;'>📢 Отправить всем пользователям</button>";
    echo "</form>";
}

/**
 * Отложенное уведомление
 */
function handleScheduledNotification(AituAppsClient $client): void
{
    echo "<h1>⏰ Отложенное уведомление</h1>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $notification = $client->createNotification(
                $_POST['title'],
                $_POST['body'],
                $_POST['user_id']
            );
            
            $scheduleTime = new DateTime($_POST['schedule_time']);
            
            $response = $client->scheduleNotification($notification, $_POST['user_id'], $scheduleTime);
            
            if ($response->isSuccessful()) {
                echo "<div class='result'>";
                echo "<h3>✅ Уведомление запланировано!</h3>";
                echo "<p><strong>Время отправки:</strong> {$scheduleTime->format('Y-m-d H:i:s')}</p>";
                echo "<pre>" . json_encode($response->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                echo "</div>";
            } else {
                $error = $response->getError();
                echo "<div class='error'>";
                echo "<h3>❌ Ошибка планирования</h3>";
                echo "<p><strong>Сообщение:</strong> {$error['message']}</p>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<h3>❌ Ошибка</h3>";
            echo "<p><strong>Сообщение:</strong> {$e->getMessage()}</p>";
            echo "</div>";
        }
    }
    
    // Форма отправки
    echo "<form method='POST'>";
    
    echo "<div class='form-group'>";
    echo "<label>ID пользователя:</label>";
    echo "<input type='text' name='user_id' required placeholder='xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx' value='" . ($_POST['user_id'] ?? '') . "'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label>Заголовок:</label>";
    echo "<input type='text' name='title' required placeholder='Отложенное уведомление' value='" . ($_POST['title'] ?? '') . "'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label>Текст:</label>";
    echo "<textarea name='body' required placeholder='Текст отложенного уведомления'>" . ($_POST['body'] ?? '') . "</textarea>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label>Время отправки:</label>";
    $defaultTime = (new DateTime('+1 hour'))->format('Y-m-d\TH:i');
    echo "<input type='datetime-local' name='schedule_time' required value='" . ($_POST['schedule_time'] ?? $defaultTime) . "'>";
    echo "</div>";
    
    echo "<button type='submit' class='btn'>⏰ Запланировать уведомление</button>";
    echo "</form>";
}

/**
 * Статистика уведомлений
 */
function handleNotificationStats(AituAppsClient $client): void
{
    echo "<h1>📊 Статистика уведомлений</h1>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $notificationId = $_POST['notification_id'];
            
            // Получаем статус уведомления
            $status = $client->getNotificationStatus($notificationId);
            
            // Получаем статистику
            $stats = $client->getNotificationStats($notificationId);
            
            echo "<div class='result'>";
            echo "<h3>📈 Статистика уведомления</h3>";
            echo "<p><strong>ID уведомления:</strong> {$notificationId}</p>";
            
            if ($status->isSuccessful()) {
                echo "<h4>Статус:</h4>";
                echo "<pre>" . json_encode($status->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
            }
            
            if ($stats->isSuccessful()) {
                echo "<h4>Детальная статистика:</h4>";
                echo "<pre>" . json_encode($stats->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
            }
            
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<h3>❌ Ошибка получения статистики</h3>";
            echo "<p><strong>Сообщение:</strong> {$e->getMessage()}</p>";
            echo "</div>";
        }
    }
    
    // Форма запроса статистики
    echo "<form method='POST'>";
    
    echo "<div class='form-group'>";
    echo "<label>ID уведомления:</label>";
    echo "<input type='text' name='notification_id' required placeholder='notification_id_from_response' value='" . ($_POST['notification_id'] ?? '') . "'>";
    echo "<small>Введите ID уведомления, полученный при отправке</small>";
    echo "</div>";
    
    echo "<button type='submit' class='btn'>📊 Получить статистику</button>";
    echo "</form>";
}

?>