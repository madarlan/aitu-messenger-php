# Использование Aitu Messenger PHP SDK

## Содержание

- [Aitu Passport API](#aitu-passport-api)
- [Aitu Apps API](#aitu-apps-api)
- [Работа с подписями](#работа-с-подписями)
- [Обработка ошибок](#обработка-ошибок)
- [Laravel Facades](#laravel-facades)

## Aitu Passport API

### Инициализация клиента

```php
use MadArlan\AituMessenger\AituPassportClient;

$client = new AituPassportClient(
    clientId: 'your_client_id',
    clientSecret: 'your_client_secret',
    redirectUri: 'https://your-domain.com/callback'
);

// Или через DI контейнер Laravel
$client = app(AituPassportClient::class);
```

### OAuth авторизация

#### Получение URL для авторизации

```php
$authUrl = $client->getAuthorizationUrl([
    'profile',
    'email',
    'phone'
], 'your_state_parameter');

return redirect($authUrl);
```

#### Обмен кода на токен

```php
// В контроллере обработки callback
public function handleCallback(Request $request)
{
    $code = $request->get('code');
    $state = $request->get('state');
    
    try {
        $tokenData = $client->exchangeCodeForToken($code);
        
        // Сохраните токен в сессии или базе данных
        session(['aitu_token' => $tokenData]);
        
        return redirect('/dashboard');
    } catch (\Exception $e) {
        return redirect('/login')->with('error', 'Ошибка авторизации');
    }
}
```

### Работа с токенами

#### Обновление токена

```php
$refreshToken = 'user_refresh_token';

try {
    $newTokenData = $client->refreshToken($refreshToken);
    // Обновите токен в хранилище
} catch (\Exception $e) {
    // Токен недействителен, требуется повторная авторизация
}
```

#### Отзыв токена

```php
$accessToken = 'user_access_token';

$client->revokeToken($accessToken);
```

### Получение информации о пользователе

```php
$accessToken = 'user_access_token';

try {
    $user = $client->getUserInfo($accessToken);
    
    echo "ID: " . $user->getId();
    echo "Имя: " . $user->getName();
    echo "Email: " . $user->getEmail();
    echo "Телефон: " . $user->getPhone();
} catch (\Exception $e) {
    // Обработка ошибки
}
```

## Aitu Apps API

### Инициализация клиента

```php
use MadArlan\AituMessenger\AituAppsClient;

$client = new AituAppsClient(
    appId: 'your_app_id',
    appSecret: 'your_app_secret'
);

// Или через DI контейнер Laravel
$client = app(AituAppsClient::class);
```

### Отправка push-уведомлений

#### Уведомление конкретному пользователю

```php
$notification = $client->createNotification(
    title: 'Заголовок уведомления',
    body: 'Текст уведомления',
    userId: 'user-uuid-here'
);

$response = $client->sendTargetedNotification($notification);

if ($response->isSuccessful()) {
    echo "Уведомление отправлено: " . $response->getData('notification_id');
}
```

#### Массовая отправка

```php
$userIds = [
    'user-uuid-1',
    'user-uuid-2',
    'user-uuid-3'
];

$notification = $client->createNotification(
    title: 'Массовое уведомление',
    body: 'Текст для всех пользователей'
);

$response = $client->sendMultipleNotifications($notification, $userIds);
```

#### Групповые уведомления

```php
$notification = $client->createNotification(
    title: 'Групповое уведомление',
    body: 'Уведомление для группы'
);

$response = $client->sendGroupNotification($notification, 'group_id_here');
```

#### Уведомления по тегам

```php
$notification = $client->createNotification(
    title: 'Уведомление по тегам',
    body: 'Для пользователей с определенными тегами'
);

$response = $client->sendNotificationByTags($notification, ['premium', 'active']);
```

#### Broadcast уведомления

```php
$notification = $client->createNotification(
    title: 'Всем пользователям',
    body: 'Важное объявление'
);

$response = $client->sendBroadcastNotification($notification);
```

### Расширенные возможности уведомлений

#### Уведомление с дополнительными параметрами

```php
$notification = [
    'title' => 'Заголовок',
    'body' => 'Текст уведомления',
    'user_id' => 'user-uuid',
    'icon' => 'https://example.com/icon.png',
    'image' => 'https://example.com/image.jpg',
    'click_action' => 'https://example.com/action',
    'data' => [
        'custom_field' => 'custom_value',
        'order_id' => 12345
    ],
    'badge' => 1,
    'sound' => 'default',
    'priority' => 'high'
];

$response = $client->sendTargetedNotification($notification);
```

#### Отложенная отправка

```php
$notification = $client->createNotification(
    title: 'Отложенное уведомление',
    body: 'Будет отправлено позже'
);

$scheduleTime = now()->addHours(2);

$response = $client->scheduleNotification($notification, 'user-uuid', $scheduleTime);
```

### Управление уведомлениями

#### Получение статуса уведомления

```php
$notificationId = 'notification-id-from-response';

$status = $client->getNotificationStatus($notificationId);

echo "Статус: " . $status->getData('status');
echo "Доставлено: " . $status->getData('delivered_count');
```

#### Отмена запланированного уведомления

```php
$notificationId = 'scheduled-notification-id';

$response = $client->cancelScheduledNotification($notificationId);
```

#### Статистика уведомлений

```php
$stats = $client->getNotificationStats('notification-id');

echo "Отправлено: " . $stats->getData('sent_count');
echo "Доставлено: " . $stats->getData('delivered_count');
echo "Открыто: " . $stats->getData('opened_count');
echo "Кликнуто: " . $stats->getData('clicked_count');
```

## Работа с подписями

### Генерация подписи

```php
use MadArlan\AituMessenger\Utils\SignatureGenerator;

$generator = new SignatureGenerator();

// Подпись данных
$data = ['key' => 'value', 'timestamp' => time()];
$secret = 'your_secret_key';

$signature = $generator->generateSignature($data, $secret);
```

### Проверка подписи

```php
use MadArlan\AituMessenger\Utils\SignatureValidator;

$validator = new SignatureValidator();

$isValid = $validator->verifySignature($data, $signature, $secret);

if ($isValid) {
    echo "Подпись действительна";
} else {
    echo "Подпись недействительна";
}
```

### Проверка webhook подписи

```php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_AITU_SIGNATURE'] ?? '';
$secret = config('aitu-messenger.webhooks.secret');

$isValid = $validator->verifyWebhookSignature($payload, $signature, $secret);
```

## Обработка ошибок

### Типы исключений

```php
use MadArlan\AituMessenger\Exceptions\AituApiException;
use MadArlan\AituMessenger\Exceptions\AituAuthenticationException;

try {
    $user = $client->getUserInfo($accessToken);
} catch (AituAuthenticationException $e) {
    // Ошибка аутентификации - токен недействителен
    echo "Ошибка аутентификации: " . $e->getMessage();
    // Перенаправить на повторную авторизацию
} catch (AituApiException $e) {
    // Общая ошибка API
    echo "Ошибка API: " . $e->getMessage();
    echo "Код ошибки: " . $e->getCode();
    
    // Дополнительный контекст
    $context = $e->getContext();
    if ($context) {
        echo "Детали: " . json_encode($context);
    }
}
```

### Обработка HTTP ошибок

```php
use MadArlan\AituMessenger\Http\ApiResponse;

$response = $client->sendTargetedNotification($notification);

if (!$response->isSuccessful()) {
    $error = $response->getError();
    
    echo "Ошибка: " . $error['message'];
    echo "Код: " . $error['code'];
    
    // HTTP статус код
    echo "HTTP статус: " . $response->getStatusCode();
}
```

## Laravel Facades

### Использование Facade для Aitu Passport

```php
use MadArlan\AituMessenger\Facades\AituPassport;

// Получение URL авторизации
$authUrl = AituPassport::getAuthorizationUrl(['profile', 'email']);

// Обмен кода на токен
$tokenData = AituPassport::exchangeCodeForToken($code);

// Получение информации о пользователе
$user = AituPassport::getUserInfo($accessToken);

// Проверка подписи
$isValid = AituPassport::verifySignature($data, $signature, $secret);
```

### Использование Facade для Aitu Apps

```php
use MadArlan\AituMessenger\Facades\AituApps;

// Создание уведомления
$notification = AituApps::createNotification(
    'Заголовок',
    'Текст уведомления',
    'user-uuid'
);

// Отправка уведомления
$response = AituApps::sendTargetedNotification($notification);

// Массовая отправка
$response = AituApps::sendMultipleNotifications($notification, $userIds);

// Получение статистики
$stats = AituApps::getNotificationStats($notificationId);
```

## Примеры интеграции

### Полный цикл OAuth авторизации

```php
// Контроллер авторизации
class AituAuthController extends Controller
{
    public function login()
    {
        $authUrl = AituPassport::getAuthorizationUrl(['profile', 'email']);
        return redirect($authUrl);
    }
    
    public function callback(Request $request)
    {
        try {
            $tokenData = AituPassport::exchangeCodeForToken($request->code);
            $user = AituPassport::getUserInfo($tokenData['access_token']);
            
            // Создание или обновление пользователя в БД
            $localUser = User::updateOrCreate(
                ['aitu_id' => $user->getId()],
                [
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'phone' => $user->getPhone(),
                ]
            );
            
            Auth::login($localUser);
            
            return redirect('/dashboard');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Ошибка авторизации');
        }
    }
}
```

### Отправка уведомлений в фоне

```php
// Job для отправки уведомлений
class SendAituNotificationJob implements ShouldQueue
{
    public function __construct(
        private string $userId,
        private string $title,
        private string $body,
        private array $data = []
    ) {}
    
    public function handle()
    {
        $notification = AituApps::createNotification(
            $this->title,
            $this->body,
            $this->userId
        );
        
        if (!empty($this->data)) {
            $notification['data'] = $this->data;
        }
        
        AituApps::sendTargetedNotification($notification);
    }
}

// Использование
SendAituNotificationJob::dispatch(
    'user-uuid',
    'Новое сообщение',
    'У вас есть новое сообщение',
    ['message_id' => 123]
);
```