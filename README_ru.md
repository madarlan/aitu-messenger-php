![Aitu Messenger PHP SDK](https://github.com/user-attachments/assets/6069cad7-d993-4ec4-a08d-0835ecd2e88d)

# 🚀 Aitu Messenger PHP SDK

> 🇷🇺 **Русская версия** | 🇰🇿 **[Қазақша нұсқасы](README.md)**

<div align="center">

[![Latest Version](https://img.shields.io/packagist/v/madarlan/aitu-messenger-php?style=flat-square&color=blue)](https://packagist.org/packages/madarlan/aitu-messenger-php)
[![License](https://img.shields.io/packagist/l/madarlan/aitu-messenger-php?style=flat-square&color=purple)](https://packagist.org/packages/madarlan/aitu-messenger-php)
[![PHP Version](https://img.shields.io/packagist/php-v/madarlan/aitu-messenger-php?style=flat-square&color=777BB4)](https://packagist.org/packages/madarlan/aitu-messenger-php)

**🎯 Современный PHP SDK для интеграции с Aitu Messenger API**

*⚡ Полная поддержка Aitu Passport (OAuth) и Aitu Apps (Push Notifications) с глубокой интеграцией в Laravel*

<p align="center">
  <img src="https://img.shields.io/badge/Made%20with-❤️-red?style=flat-square" alt="Made with Love">
  <img src="https://img.shields.io/badge/Built%20for-Developers-blue?style=flat-square" alt="Built for Developers">
  <img src="https://img.shields.io/badge/Production-Ready-green?style=flat-square" alt="Production Ready">
</p>

[📖 Документация](#-документация) • [🚀 Быстрый старт](#-быстрый-старт) • [💡 Примеры](#-примеры) • [🤝 Поддержка](#-поддержка)

</div>

---

## ✨ Возможности

<table>
<tr>
<td width="50%">

### 🔐 **Aitu Passport OAuth**

- ✅ Полная поддержка OAuth 2.0
- ✅ Автоматическое обновление токенов
- ✅ Безопасная проверка подписей
- ✅ Управление состояниями сессий

</td>
<td width="50%">

### 📱 **Aitu Apps Push Notifications**

- ✅ Таргетированные уведомления
- ✅ Массовая рассылка
- ✅ Групповые уведомления
- ✅ Отложенная отправка

</td>
</tr>
<tr>
<td width="50%">

### 🔒 **Безопасность**

- ✅ Проверка подписи webhook'ов
- ✅ Защита от timing атак
- ✅ Валидация всех входных данных
- ✅ Безопасное хранение токенов

</td>
<td width="50%">

### 🚀 **Laravel Integration**

- ✅ Готовые Facades и Service Providers
- ✅ Artisan команды для установки
- ✅ Миграции базы данных
- ✅ Middleware для webhook'ов

</td>
</tr>
</table>

### 🛠️ **Дополнительные возможности**

- 📊 **Аналитика** - Статистика доставки уведомлений
- 🧪 **Полное тестирование** - Unit, Feature и Integration тесты
- 📚 **Подробная документация** - Примеры и руководства
- 🔄 **Автоматическое восстановление** - Retry механизмы для API запросов
- 🌐 **Мультиязычность** - Поддержка локализации уведомлений

---

## 📋 Требования

<table>
<tr>
<td align="center">
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" width="50" height="50"><br>
<strong>PHP 8.1+</strong>
</td>
<td align="center">
<img src="https://laravel.com/img/logomark.min.svg" width="50" height="50"><br>
<strong>Laravel 10.0+</strong>
</td>
<td align="center">
<img src="https://avatars.githubusercontent.com/u/4334459?s=200&v=4" width="50" height="50"><br>
<strong>Guzzle HTTP 7.0+</strong>
</td>
<td align="center">
<img src="https://getcomposer.org/img/logo-composer-transparent2.png" width="50" height="50"><br>
<strong>Composer</strong>
</td>
</tr>
</table>

---

## 📦 Установка

### 🎯 Быстрая установка через Composer

```bash
composer require madarlan/aitu-messenger-php
```

### ⚡ Автоматическая настройка для Laravel

Используйте Artisan команду для полной автоматической настройки:

```bash
php artisan aitu:install
```

<details>
<summary>🔧 <strong>Что делает команда установки?</strong></summary>

- ✅ Публикует конфигурационные файлы
- ✅ Создает маршруты для webhook'ов
- ✅ Публикует миграции базы данных
- ✅ Добавляет переменные окружения в `.env`
- ✅ Показывает следующие шаги настройки

</details>

### 🎛️ Опции установки

```bash
# Установить только Aitu Passport
php artisan aitu:install --passport

# Установить только Aitu Apps  
php artisan aitu:install --apps

# Перезаписать существующие файлы
php artisan aitu:install --force
```

### 🔧 Ручная установка

<details>
<summary><strong>Развернуть инструкции по ручной установке</strong></summary>

```bash
# 1. Публикация конфигурации
php artisan vendor:publish --tag="aitu-messenger-config"

# 2. Публикация маршрутов
php artisan vendor:publish --tag="aitu-messenger-routes"

# 3. Публикация миграций
php artisan vendor:publish --tag="aitu-messenger-migrations"

# 4. Выполнение миграций
php artisan migrate
```

</details>

---

## ⚙️ Конфигурация

### 🔑 Переменные окружения

Добавьте следующие переменные в ваш `.env` файл:

```env
# 🔐 Aitu Passport OAuth
AITU_PASSPORT_CLIENT_ID=your_passport_client_id
AITU_PASSPORT_CLIENT_SECRET=your_passport_client_secret
AITU_PASSPORT_REDIRECT_URI=https://yourapp.com/auth/aitu/callback

# 📱 Aitu Apps Push Notifications
AITU_APPS_APP_ID=your_apps_app_id
AITU_APPS_APP_SECRET=your_apps_app_secret

# 🔒 Webhook Security
AITU_WEBHOOK_SECRET=your_webhook_secret
AITU_WEBHOOK_VERIFY_SIGNATURE=true

# 🛠️ Опциональные настройки
AITU_LOGGING_ENABLED=true
AITU_CACHE_ENABLED=true
AITU_API_TIMEOUT=30
AITU_RETRY_ATTEMPTS=3
```

### 🏗️ Настройка Aitu Passport

<details>
<summary><strong>Пошаговая настройка Aitu Passport</strong></summary>

1. **Создание приложения**
    - Перейдите в [панель управления Aitu Passport](https://passport.aitu.io/)
    - Создайте новое приложение
    - Получите `Client ID` и `Client Secret`

2. **Настройка Redirect URI**
   ```
   https://your-domain.com/auth/aitu/callback
   ```

3. **Настройка webhook'ов**
    - **Authorization webhook**: `https://your-domain.com/api/webhooks/aitu/passport`

- **General webhook**: `https://your-domain.com/api/webhooks/aitu`

</details>

### 📱 Настройка Aitu Apps

<details>
<summary><strong>Пошаговая настройка Aitu Apps</strong></summary>

1. **Создание приложения**
    - Перейдите в [панель управления Aitu Apps](https://apps.aitu.io/)
    - Создайте новое приложение
    - Получите `App ID` и `App Secret`

2. **Настройка webhook'ов**
    - **Apps webhook**: `https://your-domain.com/api/webhooks/aitu/apps`

- **General webhook**: `https://your-domain.com/api/webhooks/aitu`

</details>

---

## 🚀 Быстрый старт

### 🔐 Aitu Passport OAuth

```php
use MadArlan\AituMessenger\Facades\AituPassport;

// 🎯 Получить URL для авторизации
$authUrl = AituPassport::getAuthorizationUrl(['profile', 'email']);
return redirect($authUrl);

// 🔄 Обработать callback
$tokens = AituPassport::exchangeCodeForTokens($request->get('code'));
$userInfo = AituPassport::getUserInfo($tokens['access_token']);

// 👤 Информация о пользователе
echo "👋 Привет, " . $userInfo->getName();
echo "📧 Email: " . $userInfo->getEmail();
echo "📱 Телефон: " . $userInfo->getPhone();
```

### 📱 Aitu Apps Push Notifications

```php
use MadArlan\AituMessenger\Facades\AituApps;

// 🎯 Уведомление конкретному пользователю
$result = AituApps::sendTargetedNotification(
    userId: 'user_123',
    title: '🎉 Поздравляем!',
    body: 'Ваш заказ успешно оформлен',
    data: ['order_id' => 12345, 'status' => 'confirmed']
);

// 👥 Групповое уведомление
$result = AituApps::sendGroupNotification(
    groupId: 'group_456',
    title: '📢 Важное объявление',
    body: 'Новые функции уже доступны!'
);

// ⏰ Запланированное уведомление
$scheduledTime = now()->addHours(2);
$result = AituApps::scheduleNotification(
    userId: 'user_123',
    title: '⏰ Напоминание',
    body: 'Не забудьте про встречу в 15:00',
    scheduledAt: $scheduledTime
);
```

### 🔗 Обработка Webhook'ов

Webhook'и автоматически обрабатываются через маршруты с middleware `aitu.webhook` для проверки подписи:

```php
// 🌐 Автоматически подключенные маршруты
Route::post('/api/webhooks/aitu', [WebhookController::class, 'handleGeneralWebhook'])
    ->middleware('aitu.webhook');

Route::post('/api/webhooks/aitu/passport', [WebhookController::class, 'handlePassportWebhook'])
    ->middleware('aitu.webhook');

Route::post('/api/webhooks/aitu/apps', [WebhookController::class, 'handleAppsWebhook'])
    ->middleware('aitu.webhook');
```

> **🔒 Безопасность**: Middleware `aitu.webhook` автоматически проверяет подпись webhook'а с использованием секретного
> ключа из конфигурации.

---

## 💡 Примеры использования

### 🔐 Полный цикл OAuth авторизации

<details>
<summary><strong>Контроллер авторизации</strong></summary>

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MadArlan\AituMessenger\Facades\AituPassport;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AituAuthController extends Controller
{
    /**
     * 🚀 Перенаправление на авторизацию
     */
    public function login()
    {
        $authUrl = AituPassport::getAuthorizationUrl([
            'profile', 
            'email', 
            'phone'
        ], 'custom_state_' . time());
        
        return redirect($authUrl);
    }
    
    /**
     * 🔄 Обработка callback'а
     */
    public function callback(Request $request)
    {
        try {
            // Проверяем state для безопасности
            if (!$request->has('state') || !str_starts_with($request->state, 'custom_state_')) {
                throw new \Exception('Invalid state parameter');
            }
            
            // Обмениваем код на токены
            $tokenData = AituPassport::exchangeCodeForTokens($request->code);
            $user = AituPassport::getUserInfo($tokenData['access_token']);
            
            // Создаем или обновляем пользователя
            $localUser = User::updateOrCreate(
                ['aitu_id' => $user->getId()],
                [
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'phone' => $user->getPhone(),
                    'avatar' => $user->getAvatar(),
                    'aitu_access_token' => $tokenData['access_token'],
                    'aitu_refresh_token' => $tokenData['refresh_token'],
                    'aitu_token_expires_at' => now()->addSeconds($tokenData['expires_in']),
                ]
            );
            
            Auth::login($localUser);
            
            return redirect('/dashboard')->with('success', '🎉 Успешная авторизация через Aitu!');
            
        } catch (\Exception $e) {
            return redirect('/login')->with('error', '❌ Ошибка авторизации: ' . $e->getMessage());
        }
    }
    
    /**
     * 🚪 Выход из системы
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        if ($user && $user->aitu_access_token) {
            // Отзываем токен в Aitu
            AituPassport::revokeToken($user->aitu_access_token);
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/')->with('success', '👋 До свидания!');
    }
}
```

</details>

### 📱 Система уведомлений

<details>
<summary><strong>Job для отправки уведомлений</strong></summary>

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MadArlan\AituMessenger\Facades\AituApps;
use Illuminate\Support\Facades\Log;

class SendAituNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $userId,
        private string $title,
        private string $body,
        private array $data = [],
        private ?string $icon = null,
        private ?string $image = null,
        private ?string $clickAction = null
    ) {}

    /**
     * 🚀 Выполнение задачи
     */
    public function handle(): void
    {
        try {
            $notification = [
                'title' => $this->title,
                'body' => $this->body,
                'user_id' => $this->userId,
                'data' => $this->data,
            ];

            // Добавляем дополнительные параметры если они есть
            if ($this->icon) {
                $notification['icon'] = $this->icon;
            }
            
            if ($this->image) {
                $notification['image'] = $this->image;
            }
            
            if ($this->clickAction) {
                $notification['click_action'] = $this->clickAction;
            }

            $response = AituApps::sendTargetedNotification($notification);

            if ($response->isSuccessful()) {
                Log::info('✅ Aitu notification sent successfully', [
                    'user_id' => $this->userId,
                    'notification_id' => $response->getData('notification_id')
                ]);
            } else {
                Log::error('❌ Failed to send Aitu notification', [
                    'user_id' => $this->userId,
                    'error' => $response->getError()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('💥 Exception while sending Aitu notification', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Перебрасываем исключение для retry механизма
        }
    }
}
```

**Использование:**

```php
// 🎯 Простое уведомление
SendAituNotificationJob::dispatch(
    userId: 'user-uuid-123',
    title: '🛒 Новый заказ',
    body: 'Ваш заказ #12345 принят в обработку'
);

// 🎨 Уведомление с медиа и действием
SendAituNotificationJob::dispatch(
    userId: 'user-uuid-123',
    title: '🎉 Специальное предложение!',
    body: 'Скидка 50% на все товары до конца недели',
    data: ['promo_code' => 'SALE50', 'discount' => 50],
    icon: 'https://example.com/sale-icon.png',
    image: 'https://example.com/sale-banner.jpg',
    clickAction: 'https://example.com/sale'
);

// ⏰ Отложенная отправка
SendAituNotificationJob::dispatch(
    userId: 'user-uuid-123',
    title: '⏰ Напоминание',
    body: 'Через час начнется ваша встреча'
)->delay(now()->addMinutes(50));
```

</details>

### 🔔 Расширенные уведомления

<details>
<summary><strong>Различные типы уведомлений</strong></summary>

```php
use MadArlan\AituMessenger\Facades\AituApps;

// 🎯 Таргетированное уведомление с полными параметрами
$response = AituApps::sendTargetedNotification([
    'title' => '🎁 Персональное предложение',
    'body' => 'Специально для вас скидка 30%!',
    'user_id' => 'user-uuid-123',
    'icon' => 'https://example.com/gift-icon.png',
    'image' => 'https://example.com/personal-offer.jpg',
    'click_action' => 'https://example.com/personal-offer',
    'data' => [
        'offer_id' => 'PERSONAL_30',
        'discount' => 30,
        'expires_at' => '2024-12-31T23:59:59Z'
    ],
    'badge' => 1,
    'sound' => 'default',
    'priority' => 'high'
]);

// 👥 Массовая рассылка
$userIds = ['user-1', 'user-2', 'user-3', 'user-4', 'user-5'];
$response = AituApps::sendMultipleNotifications([
    'title' => '📢 Важное объявление',
    'body' => 'Обновление системы запланировано на завтра в 02:00'
], $userIds);

// 🏷️ Уведомления по тегам
$response = AituApps::sendNotificationByTags([
    'title' => '⭐ Для VIP клиентов',
    'body' => 'Эксклюзивная распродажа только для вас!'
], ['vip', 'premium']);

// 📡 Broadcast всем пользователям
$response = AituApps::sendBroadcastNotification([
    'title' => '🚨 Системное уведомление',
    'body' => 'Плановые технические работы с 02:00 до 04:00'
]);

// ⏰ Запланированное уведомление
$scheduledTime = now()->addDays(1)->setTime(9, 0); // Завтра в 9:00
$response = AituApps::scheduleNotification(
    userId: 'user-uuid-123',
    title: '☀️ Доброе утро!',
    body: 'Начните день с нашими новостями',
    scheduledAt: $scheduledTime,
    data: ['type' => 'morning_digest']
);
```

</details>

### 📊 Аналитика и статистика

<details>
<summary><strong>Отслеживание доставки уведомлений</strong></summary>

```php
use MadArlan\AituMessenger\Facades\AituApps;

// 📊 Получение статистики уведомления
$notificationId = 'notification-uuid-from-response';
$stats = AituApps::getNotificationStatistics($notificationId);

if ($stats->isSuccessful()) {
    $data = $stats->getData();
    
    echo "📤 Отправлено: " . $data['sent_count'] . "\n";
    echo "✅ Доставлено: " . $data['delivered_count'] . "\n";
    echo "👀 Просмотрено: " . $data['viewed_count'] . "\n";
    echo "👆 Кликнуто: " . $data['clicked_count'] . "\n";
    echo "📈 CTR: " . round(($data['clicked_count'] / $data['delivered_count']) * 100, 2) . "%\n";
}

// 🔍 Проверка статуса уведомления
$status = AituApps::getNotificationStatus($notificationId);

if ($status->isSuccessful()) {
    $statusData = $status->getData();
    
    switch ($statusData['status']) {
        case 'pending':
            echo "⏳ Уведомление в очереди на отправку";
            break;
        case 'sending':
            echo "🚀 Уведомление отправляется";
            break;
        case 'sent':
            echo "✅ Уведомление отправлено";
            break;
        case 'failed':
            echo "❌ Ошибка отправки: " . $statusData['error_message'];
            break;
    }
}

// ❌ Отмена запланированного уведомления
$scheduledNotificationId = 'scheduled-notification-uuid';
$cancelResponse = AituApps::cancelScheduledNotification($scheduledNotificationId);

if ($cancelResponse->isSuccessful()) {
    echo "✅ Запланированное уведомление отменено";
} else {
    echo "❌ Не удалось отменить уведомление: " . $cancelResponse->getError()['message'];
}
```

</details>

---

## 🛠️ Использование без Laravel

SDK можно использовать в любом PHP проекте:

```php
use MadArlan\AituMessenger\AituPassportClient;
use MadArlan\AituMessenger\AituAppsClient;

// 🔐 Aitu Passport
$passportClient = new AituPassportClient(
    clientId: 'your_client_id',
    clientSecret: 'your_client_secret',
    redirectUri: 'https://yourapp.com/callback'
);

$authUrl = $passportClient->getAuthorizationUrl(['profile', 'email']);
$tokens = $passportClient->exchangeCodeForTokens($authorizationCode);
$user = $passportClient->getUserInfo($tokens['access_token']);

// 📱 Aitu Apps
$appsClient = new AituAppsClient(
    appId: 'your_app_id',
    appSecret: 'your_app_secret'
);

$response = $appsClient->sendTargetedNotification([
    'title' => 'Заголовок',
    'body' => 'Текст уведомления',
    'user_id' => 'user-uuid-123'
]);
```

---

## 🧪 Тестирование

### 🚀 Запуск тестов

```bash
# Все тесты
composer test

# Тесты с покрытием кода
composer test-coverage

# Только unit тесты
composer test -- --testsuite=Unit

# Только feature тесты  
composer test -- --testsuite=Feature

# Только integration тесты
composer test -- --testsuite=Integration
```

### 📊 Анализ кода

```bash
# PHPStan анализ
composer analyse

# PHP CS Fixer
composer format

# Проверка стиля кода
composer check-style
```

### ✅ Проверка установки

Создайте тестовый маршрут:

```php
// routes/web.php
Route::get('/test-aitu', function () {
    try {
        // 🧪 Тест Aitu Passport
        $passportClient = app(\MadArlan\AituMessenger\AituPassportClient::class);
        $authUrl = $passportClient->getAuthorizationUrl(['profile']);
        
        // 🧪 Тест Aitu Apps
        $appsClient = app(\MadArlan\AituMessenger\AituAppsClient::class);
        
        return response()->json([
            'status' => '✅ success',
            'passport_auth_url' => $authUrl,
            'apps_client' => '✅ initialized',
            'timestamp' => now()->toISOString()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => '❌ error',
            'message' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});
```

---

## 🔧 Устранение проблем

<details>
<summary><strong>Частые проблемы и их решения</strong></summary>

### 🚫 Проблема с автозагрузкой

```bash
composer dump-autoload
php artisan optimize:clear
```

### 🗂️ Проблемы с кэшем

```bash
# Очистка всех кэшей
php artisan optimize:clear

# Или по отдельности
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 🔐 Проблемы с правами доступа

```bash
# Linux/Mac
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/

# Windows (в PowerShell от администратора)
icacls storage /grant "IIS_IUSRS:(OI)(CI)F" /T
```

### 🌐 Проблемы с webhook'ами

1. **Проверьте URL webhook'ов** в панели управления Aitu
2. **Убедитесь что HTTPS настроен** правильно
3. **Проверьте секретный ключ** в `.env` файле
4. **Включите логирование** для отладки:

```env
AITU_LOGGING_ENABLED=true
LOG_LEVEL=debug
```

### 📡 Проблемы с API запросами

```php
// Увеличьте таймаут в конфигурации
'http' => [
    'timeout' => 60, // секунд
    'retry_attempts' => 5,
    'retry_delay' => 1000, // миллисекунд
],
```

</details>

---

## 📖 Документация

### 📚 Подробные руководства

- 📋 [**Требования и установка**](#-установка) - Полное руководство по установке
- ⚙️ [**Конфигурация**](#️-конфигурация) - Настройка всех параметров
- 🚀 [**Быстрый старт**](#-быстрый-старт) - Начните использовать за 5 минут
- 💡 [**Примеры использования**](#-примеры-использования) - Готовые решения
- 🧪 [**Тестирование**](#-тестирование) - Как тестировать ваш код

### 📁 Примеры кода

В папке `examples/` вы найдете готовые примеры:

- 🔐 [`passport_oauth.php`](examples/passport_oauth.php) - OAuth авторизация
- 📱 [`apps_notifications.php`](examples/apps_notifications.php) - Push уведомления
- 🔗 [`webhook_handler.php`](examples/webhook_handler.php) - Обработка webhook'ов

### 🧪 Тесты

Изучите тесты для понимания всех возможностей:

- 🔬 [`tests/Unit/`](tests/Unit/) - Unit тесты
- 🎯 [`tests/Feature/`](tests/Feature/) - Feature тесты
- 🔗 [`tests/Integration/`](tests/Integration/) - Integration тесты

---

## 🤝 Поддержка

<div align="center">

### 💬 Связь с нами

[![Email](https://img.shields.io/badge/Email-madinovarlan%40gmail.com-blue?style=for-the-badge&logo=gmail)](mailto:madinovarlan@gmail.com)
[![GitHub Issues](https://img.shields.io/badge/GitHub-Issues-green?style=for-the-badge&logo=github)](https://github.com/madarlan/aitu-messenger-php/issues)
[![Documentation](https://img.shields.io/badge/Docs-Wiki-orange?style=for-the-badge&logo=gitbook)](https://github.com/madarlan/aitu-messenger-php/wiki)

</div>

### 🆘 Получение помощи

1. 📖 **Сначала проверьте документацию** - большинство вопросов уже освещены
2. 🔍 **Поищите в Issues** - возможно, ваш вопрос уже обсуждался
3. 🐛 **Создайте новый Issue** - если не нашли ответ
4. 📧 **Напишите нам** - для срочных вопросов

### 🚨 Сообщение о безопасности

Если вы обнаружили уязвимость в безопасности, **НЕ создавайте публичный Issue**.
Отправьте email на [madinovarlan@gmail.com](mailto:madinovarlan@gmail.com).

---

## 🤝 Участие в разработке

Мы приветствуем участие в разработке!

### 🛠️ Как внести вклад

1. 🍴 **Fork** репозиторий
2. 🌿 **Создайте ветку** для вашей функции (`git checkout -b feature/amazing-feature`)
3. ✅ **Добавьте тесты** для новой функциональности
4. 🧪 **Убедитесь что все тесты проходят** (`composer test`)
5. 📝 **Commit** ваши изменения (`git commit -m 'Add amazing feature'`)
6. 📤 **Push** в ветку (`git push origin feature/amazing-feature`)
7. 🔄 **Создайте Pull Request**

### 📋 Правила разработки

- ✅ Следуйте **PSR-12** стандарту кодирования
- 🧪 **Покрывайте тестами** новую функциональность
- 📝 **Документируйте** изменения в коде
- 🔄 **Обновляйте CHANGELOG.md** при необходимости

---

## 📄 Лицензия

Этот проект лицензирован под **MIT License** - подробности в файле [**LICENSE**](LICENSE.md).

```
MIT License

Copyright (c) 2025 Madinov Arlan

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
```

---

<div align="center">

### ⭐ Если проект оказался полезным, поставьте звездочку!

[![GitHub stars](https://img.shields.io/github/stars/madarlan/aitu-messenger-php?style=social)](https://github.com/madarlan/aitu-messenger-php/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/madarlan/aitu-messenger-php?style=social)](https://github.com/madarlan/aitu-messenger-php/network/members)
[![GitHub watchers](https://img.shields.io/github/watchers/madarlan/aitu-messenger-php?style=social)](https://github.com/madarlan/aitu-messenger-php/watchers)

---

*Превращаем сложные интеграции в простые решения*

</div>
