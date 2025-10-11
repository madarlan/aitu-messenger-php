# Установка Aitu Messenger PHP SDK

## Требования

- PHP 8.1 или выше
- Laravel 10.0 или выше
- Composer

## Установка через Composer

```bash
composer require madarlan/aitu-messenger-php
```

## Быстрая установка

Для быстрой установки и настройки используйте Artisan команду:

```bash
php artisan aitu:install
```

Эта команда:
- Опубликует конфигурационные файлы
- Создаст необходимые маршруты
- Опубликует миграции
- Добавит переменные окружения в `.env` файл
- Покажет следующие шаги для настройки

### Опции установки

```bash
# Установить только Aitu Passport
php artisan aitu:install --passport

# Установить только Aitu Apps
php artisan aitu:install --apps

# Перезаписать существующие файлы
php artisan aitu:install --force
```

## Ручная установка

### 1. Публикация конфигурации

```bash
php artisan vendor:publish --provider="MadArlan\AituMessenger\AituMessengerServiceProvider" --tag="aitu-config"
```

### 2. Публикация маршрутов

```bash
php artisan vendor:publish --provider="MadArlan\AituMessenger\AituMessengerServiceProvider" --tag="aitu-routes"
```

### 3. Публикация миграций

```bash
php artisan vendor:publish --provider="MadArlan\AituMessenger\AituMessengerServiceProvider" --tag="aitu-migrations"
```

### 4. Запуск миграций

```bash
php artisan migrate
```

## Настройка переменных окружения

Добавьте следующие переменные в ваш `.env` файл:

### Aitu Passport

```env
AITU_PASSPORT_CLIENT_ID=your_passport_client_id
AITU_PASSPORT_CLIENT_SECRET=your_passport_client_secret
AITU_PASSPORT_REDIRECT_URI=https://your-domain.com/aitu/passport/callback
AITU_PASSPORT_SIGNATURE_SECRET=your_signature_secret
```

### Aitu Apps

```env
AITU_APPS_APP_ID=your_app_id
AITU_APPS_APP_SECRET=your_app_secret
```

### Webhook'и

```env
AITU_WEBHOOK_SECRET=your_webhook_secret
```

## Регистрация Service Provider (для Laravel < 11)

Если вы используете Laravel версии ниже 11, добавьте Service Provider в `config/app.php`:

```php
'providers' => [
    // ...
    MadArlan\AituMessenger\AituMessengerServiceProvider::class,
],
```

## Регистрация Facade (опционально)

Добавьте Facade в `config/app.php`:

```php
'aliases' => [
    // ...
    'AituPassport' => MadArlan\AituMessenger\Facades\AituPassport::class,
    'AituApps' => MadArlan\AituMessenger\Facades\AituApps::class,
],
```

## Настройка Aitu Passport

### 1. Создание приложения

1. Перейдите в [панель управления Aitu Passport](https://passport.aitu.io/)
2. Создайте новое приложение
3. Получите `Client ID` и `Client Secret`
4. Настройте `Redirect URI`: `https://your-domain.com/aitu/passport/callback`

### 2. Настройка webhook'ов

Настройте следующие URL для webhook'ов в панели управления:

- **Authorization webhook**: `https://your-domain.com/aitu/passport/webhook`
- **General webhook**: `https://your-domain.com/aitu/webhook`

## Настройка Aitu Apps

### 1. Создание приложения

1. Перейдите в [панель управления Aitu Apps](https://apps.aitu.io/)
2. Создайте новое приложение
3. Получите `App ID` и `App Secret`

### 2. Настройка webhook'ов

Настройте следующие URL для webhook'ов в панели управления:

- **Apps webhook**: `https://your-domain.com/aitu/apps/webhook`
- **General webhook**: `https://your-domain.com/aitu/webhook`

## Проверка установки

Создайте тестовый маршрут для проверки работы SDK:

```php
// routes/web.php
Route::get('/test-aitu', function () {
    try {
        // Тест Aitu Passport
        $passportClient = app(\MadArlan\AituMessenger\AituPassportClient::class);
        $authUrl = $passportClient->getAuthorizationUrl(['profile', 'email']);
        
        // Тест Aitu Apps
        $appsClient = app(\MadArlan\AituMessenger\AituAppsClient::class);
        
        return response()->json([
            'status' => 'success',
            'passport_auth_url' => $authUrl,
            'apps_client' => 'initialized'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});
```

## Устранение проблем

### Проблема с автозагрузкой

Если возникают проблемы с автозагрузкой классов:

```bash
composer dump-autoload
```

### Проблема с кэшем конфигурации

Очистите кэш конфигурации:

```bash
php artisan config:clear
php artisan cache:clear
```

### Проблема с маршрутами

Очистите кэш маршрутов:

```bash
php artisan route:clear
```

## Следующие шаги

После успешной установки:

1. Изучите [документацию по использованию](USAGE.md)
2. Ознакомьтесь с [примерами](../examples/)
3. Настройте [webhook'и](WEBHOOKS.md)
4. Изучите [конфигурацию](CONFIGURATION.md)

## Поддержка

Если у вас возникли проблемы с установкой:

1. Проверьте [FAQ](FAQ.md)
2. Создайте [issue на GitHub](https://github.com/madarlan/aitu-messenger-php/issues)
3. Обратитесь к [документации Aitu](https://docs.aitu.io/)