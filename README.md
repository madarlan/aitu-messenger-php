![Aitu Messenger PHP SDK](https://github.com/user-attachments/assets/6069cad7-d993-4ec4-a08d-0835ecd2e88d)

# 🚀 Aitu Messenger PHP SDK

> 🇰🇿 **Қазақ тіліндегі құжаттама** | 🇷🇺 **[Русская версия](README_ru.md)**

<div align="center">

[![Latest Version](https://img.shields.io/packagist/v/madarlan/aitu-messenger-php?style=flat-square&color=blue)](https://packagist.org/packages/madarlan/aitu-messenger-php)
[![License](https://img.shields.io/packagist/l/madarlan/aitu-messenger-php?style=flat-square&color=purple)](https://packagist.org/packages/madarlan/aitu-messenger-php)
[![PHP Version](https://img.shields.io/packagist/php-v/madarlan/aitu-messenger-php?style=flat-square&color=777BB4)](https://packagist.org/packages/madarlan/aitu-messenger-php)

**🎯 Aitu Messenger API-мен интеграциялауға арналған заманауи PHP SDK**

*⚡ Laravel-мен терең интеграциясы бар Aitu Passport (OAuth) және Aitu Apps (Push хабарландырулар) толық қолдауы*

<p align="center">
  <img src="https://img.shields.io/badge/Made%20with-❤️-red?style=flat-square" alt="Made with Love">
  <img src="https://img.shields.io/badge/Built%20for-Developers-blue?style=flat-square" alt="Built for Developers">
  <img src="https://img.shields.io/badge/Production-Ready-green?style=flat-square" alt="Production Ready">
</p>

[📖 Құжаттама](#-құжаттама) • [🚀 Жылдам бастау](#-жылдам-бастау) • [💡 Мысалдар](#-мысалдар) • [🤝 Қолдау](#-қолдау)

</div>

---

## ✨ Мүмкіндіктер

<table>
<tr>
<td width="50%">

### 🔐 **Aitu Passport OAuth**

- ✅ OAuth 2.0 толық қолдауы
- ✅ Токендерді автоматты жаңарту
- ✅ Қауіпсіз қолтаңба тексеру
- ✅ Сессия күйлерін басқару

</td>
<td width="50%">

### 📱 **Aitu Apps Push хабарландырулары**

- ✅ Мақсатты хабарландырулар
- ✅ Жаппай жіберу
- ✅ Топтық хабарландырулар
- ✅ Кейінге қалдырылған жіберу

</td>
</tr>
<tr>
<td width="50%">

### 🔒 **Қауіпсіздік**

- ✅ Webhook қолтаңбаларын тексеру
- ✅ Timing шабуылдардан қорғау
- ✅ Барлық кіріс деректерін валидациялау
- ✅ Токендерді қауіпсіз сақтау

</td>
<td width="50%">

### 🚀 **Laravel интеграциясы**

- ✅ Дайын Facades және Service Providers
- ✅ Орнату үшін Artisan командалары
- ✅ Дерекқор миграциялары
- ✅ Webhook үшін Middleware

</td>
</tr>
</table>

### 🛠️ **Қосымша мүмкіндіктер**

- 📊 **Аналитика** - Хабарландыру жеткізу статистикасы
- 🧪 **Толық тестілеу** - Unit, Feature және Integration тесттер
- 📚 **Толық құжаттама** - Мысалдар мен нұсқаулықтар
- 🔄 **Автоматты қалпына келтіру** - API сұраулары үшін Retry механизмдері
- 🌐 **Көптілділік** - Хабарландыруларды локализациялау қолдауы

---

## 📋 Талаптар

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

## 📦 Орнату

### 🎯 Composer арқылы жылдам орнату

```bash
composer require madarlan/aitu-messenger-php
```

### ⚡ Laravel үшін автоматты баптау

Толық автоматты баптау үшін Artisan командасын пайдаланыңыз:

```bash
php artisan aitu:install
```

<details>
<summary>🔧 <strong>Орнату командасы не істейді?</strong></summary>

- ✅ Конфигурация файлдарын жариялайды
- ✅ Webhook үшін маршруттар жасайды
- ✅ Дерекқор миграцияларын жариялайды
- ✅ `.env` файлына орта айнымалыларын қосады
- ✅ Келесі баптау қадамдарын көрсетеді

</details>

### 🎛️ Орнату опциялары

```bash
# Тек Aitu Passport орнату
php artisan aitu:install --passport

# Тек Aitu Apps орнату  
php artisan aitu:install --apps

# Бар файлдарды қайта жазу
php artisan aitu:install --force
```

### 🔧 Қолмен орнату

<details>
<summary><strong>Қолмен орнату нұсқаулықтарын ашу</strong></summary>

```bash
# 1. Конфигурацияны жариялау
php artisan vendor:publish --tag="aitu-messenger-config"

# 2. Маршруттарды жариялау
php artisan vendor:publish --tag="aitu-messenger-routes"

# 3. Миграцияларды жариялау
php artisan vendor:publish --tag="aitu-messenger-migrations"

# 4. Миграцияларды орындау
php artisan migrate
```

</details>

---

## ⚙️ Конфигурация

### 🔑 Орта айнымалылары

Келесі айнымалыларды `.env` файлыңызға қосыңыз:

```env
# 🔐 Aitu Passport OAuth
AITU_PASSPORT_CLIENT_ID=your_passport_client_id
AITU_PASSPORT_CLIENT_SECRET=your_passport_client_secret
AITU_PASSPORT_REDIRECT_URI=https://yourapp.com/auth/aitu/callback

# 📱 Aitu Apps Push хабарландырулары
AITU_APPS_APP_ID=your_apps_app_id
AITU_APPS_APP_SECRET=your_apps_app_secret

# 🔒 Webhook қауіпсіздігі
AITU_WEBHOOK_SECRET=your_webhook_secret
AITU_WEBHOOK_VERIFY_SIGNATURE=true

# 🛠️ Қосымша баптаулар
AITU_LOGGING_ENABLED=true
AITU_CACHE_ENABLED=true
AITU_API_TIMEOUT=30
AITU_RETRY_ATTEMPTS=3
```

### 🏗️ Aitu Passport баптау

<details>
<summary><strong>Aitu Passport қадамдық баптау</strong></summary>

1. **Қосымша жасау**
    - [Aitu Passport басқару панеліне](https://passport.aitu.io/) өтіңіз
    - Жаңа қосымша жасаңыз
    - `Client ID` және `Client Secret` алыңыз

2. **Redirect URI баптау**
   ```
   https://your-domain.com/auth/aitu/callback
   ```

3. **Webhook баптау**
    - **Authorization webhook**: `https://your-domain.com/api/webhooks/aitu/passport`
    - **General webhook**: `https://your-domain.com/api/webhooks/aitu`

</details>

### 📱 Aitu Apps баптау

<details>
<summary><strong>Aitu Apps қадамдық баптау</strong></summary>

1. **Қосымша жасау**
    - [Aitu Apps басқару панеліне](https://apps.aitu.io/) өтіңіз
    - Жаңа қосымша жасаңыз
    - `App ID` және `App Secret` алыңыз

2. **Webhook баптау**
    - **Apps webhook**: `https://your-domain.com/api/webhooks/aitu/apps`
    - **General webhook**: `https://your-domain.com/api/webhooks/aitu`

</details>

---

## 🚀 Жылдам бастау

### 🔐 Aitu Passport OAuth

```php
use MadArlan\AituMessenger\Facades\AituPassport;

// 🎯 Авторизация URL алу
$authUrl = AituPassport::getAuthorizationUrl(['profile', 'email']);
return redirect($authUrl);

// 🔄 Callback өңдеу
$tokens = AituPassport::exchangeCodeForTokens($request->get('code'));
$userInfo = AituPassport::getUserInfo($tokens['access_token']);

// 👤 Пайдаланушы туралы ақпарат
echo "👋 Сәлем, " . $userInfo->getName();
echo "📧 Email: " . $userInfo->getEmail();
echo "📱 Телефон: " . $userInfo->getPhone();
```

### 📱 Aitu Apps Push хабарландырулары

```php
use MadArlan\AituMessenger\Facades\AituApps;

// 🎯 Нақты пайдаланушыға хабарландыру
$result = AituApps::sendTargetedNotification(
    userId: 'user_123',
    title: '🎉 Құттықтаймыз!',
    body: 'Сіздің тапсырысыңыз сәтті рәсімделді',
    data: ['order_id' => 12345, 'status' => 'confirmed']
);

// 👥 Топтық хабарландыру
$result = AituApps::sendGroupNotification(
    groupId: 'group_456',
    title: '📢 Маңызды хабарландыру',
    body: 'Жаңа функциялар енді қолжетімді!'
);

// ⏰ Жоспарланған хабарландыру
$scheduledTime = now()->addHours(2);
$result = AituApps::scheduleNotification(
    userId: 'user_123',
    title: '⏰ Еске салу',
    body: '15:00-де кездесуді ұмытпаңыз',
    scheduledAt: $scheduledTime
);
```

### 🔗 Webhook өңдеу

Webhook-тар қолтаңбаны тексеру үшін `aitu.webhook` middleware арқылы автоматты өңделеді:

```php
// 🌐 Автоматты қосылған маршруттар
Route::post('/api/webhooks/aitu', [WebhookController::class, 'handleGeneralWebhook'])
    ->middleware('aitu.webhook');

Route::post('/api/webhooks/aitu/passport', [WebhookController::class, 'handlePassportWebhook'])
    ->middleware('aitu.webhook');

Route::post('/api/webhooks/aitu/apps', [WebhookController::class, 'handleAppsWebhook'])
    ->middleware('aitu.webhook');
```

> **🔒 Қауіпсіздік**: `aitu.webhook` middleware конфигурациядан құпия кілтті пайдаланып webhook қолтаңбасын автоматты
> тексереді.

---

## 💡 Пайдалану мысалдары

### 🔐 OAuth авторизациясының толық циклі

<details>
<summary><strong>Авторизация контроллері</strong></summary>

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
     * 🚀 Авторизацияға бағыттау
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
     * 🔄 Callback өңдеу
     */
    public function callback(Request $request)
    {
        try {
            // Қауіпсіздік үшін state тексереміз
            if (!$request->has('state') || !str_starts_with($request->state, 'custom_state_')) {
                throw new \Exception('Invalid state parameter');
            }
            
            // Кодты токенге айырбастаймыз
            $tokenData = AituPassport::exchangeCodeForTokens($request->code);
            $user = AituPassport::getUserInfo($tokenData['access_token']);
            
            // Пайдаланушыны жасаймыз немесе жаңартамыз
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
            
            return redirect('/dashboard')->with('success', '🎉 Aitu арқылы сәтті авторизация!');
            
        } catch (\Exception $e) {
            return redirect('/login')->with('error', '❌ Авторизация қатесі: ' . $e->getMessage());
        }
    }
    
    /**
     * 🚪 Жүйеден шығу
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        if ($user && $user->aitu_access_token) {
            // Aitu-да токенді кері қайтарамыз
            AituPassport::revokeToken($user->aitu_access_token);
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/')->with('success', '👋 Сау болыңыз!');
    }
}
```

</details>

### 📱 Хабарландыру жүйесі

<details>
<summary><strong>Хабарландыру жіберу үшін Job</strong></summary>

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
     * 🚀 Тапсырманы орындау
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

            // Қосымша параметрлер бар болса қосамыз
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
                Log::info('✅ Aitu хабарландыруы сәтті жіберілді', [
                    'user_id' => $this->userId,
                    'notification_id' => $response->getData('notification_id')
                ]);
            } else {
                Log::error('❌ Aitu хабарландыруын жіберу сәтсіз', [
                    'user_id' => $this->userId,
                    'error' => $response->getError()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('💥 Aitu хабарландыруын жіберу кезінде қате', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Retry механизмі үшін қатені қайта лақтырамыз
        }
    }
}
```

**Пайдалану:**

```php
// 🎯 Қарапайым хабарландыру
SendAituNotificationJob::dispatch(
    userId: 'user-uuid-123',
    title: '🛒 Жаңа тапсырыс',
    body: 'Сіздің #12345 тапсырысыңыз өңдеуге қабылданды'
);

// 🎨 Медиа және әрекетпен хабарландыру
SendAituNotificationJob::dispatch(
    userId: 'user-uuid-123',
    title: '🎉 Арнайы ұсыныс!',
    body: 'Апта соңына дейін барлық тауарларға 50% жеңілдік',
    data: ['promo_code' => 'SALE50', 'discount' => 50],
    icon: 'https://example.com/sale-icon.png',
    image: 'https://example.com/sale-banner.jpg',
    clickAction: 'https://example.com/sale'
);

// ⏰ Кейінге қалдырылған жіберу
SendAituNotificationJob::dispatch(
    userId: 'user-uuid-123',
    title: '⏰ Еске салу',
    body: 'Бір сағаттан кейін сіздің кездесуіңіз басталады'
)->delay(now()->addMinutes(50));
```

</details>

### 🔔 Кеңейтілген хабарландырулар

<details>
<summary><strong>Әртүрлі хабарландыру түрлері</strong></summary>

```php
use MadArlan\AituMessenger\Facades\AituApps;

// 🎯 Толық параметрлермен мақсатты хабарландыру
$response = AituApps::sendTargetedNotification([
    'title' => '🎁 Жеке ұсыныс',
    'body' => 'Арнайы сіз үшін 30% жеңілдік!',
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

// 👥 Жаппай жіберу
$userIds = ['user-1', 'user-2', 'user-3', 'user-4', 'user-5'];
$response = AituApps::sendMultipleNotifications([
    'title' => '📢 Маңызды хабарландыру',
    'body' => 'Жүйені жаңарту ертең 02:00-де жоспарланған'
], $userIds);

// 🏷️ Тегтер бойынша хабарландырулар
$response = AituApps::sendNotificationByTags([
    'title' => '⭐ VIP клиенттер үшін',
    body: 'Тек сіз үшін эксклюзивті сауда!'
], ['vip', 'premium']);

// 📡 Барлық пайдаланушыларға Broadcast
$response = AituApps::sendBroadcastNotification([
    'title' => '🚨 Жүйелік хабарландыру',
    'body' => 'Жоспарлы техникалық жұмыстар 02:00-ден 04:00-ге дейін'
]);

// ⏰ Жоспарланған хабарландыру
$scheduledTime = now()->addDays(1)->setTime(9, 0); // Ертең 9:00-де
$response = AituApps::scheduleNotification(
    userId: 'user-uuid-123',
    title: '☀️ Қайырлы таң!',
    body: 'Күнді біздің жаңалықтармен бастаңыз',
    scheduledAt: $scheduledTime,
    data: ['type' => 'morning_digest']
);
```

</details>

### 📊 Аналитика және статистика

<details>
<summary><strong>Хабарландыру жеткізуін бақылау</strong></summary>

```php
use MadArlan\AituMessenger\Facades\AituApps;

// 📊 Хабарландыру статистикасын алу
$notificationId = 'notification-uuid-from-response';
$stats = AituApps::getNotificationStatistics($notificationId);

if ($stats->isSuccessful()) {
    $data = $stats->getData();
    
    echo "📤 Жіберілді: " . $data['sent_count'] . "\n";
    echo "✅ Жеткізілді: " . $data['delivered_count'] . "\n";
    echo "👀 Қаралды: " . $data['viewed_count'] . "\n";
    echo "👆 Басылды: " . $data['clicked_count'] . "\n";
    echo "📈 CTR: " . round(($data['clicked_count'] / $data['delivered_count']) * 100, 2) . "%\n";
}

// 🔍 Хабарландыру күйін тексеру
$status = AituApps::getNotificationStatus($notificationId);

if ($status->isSuccessful()) {
    $statusData = $status->getData();
    
    switch ($statusData['status']) {
        case 'pending':
            echo "⏳ Хабарландыру жіберу кезегінде";
            break;
        case 'sending':
            echo "🚀 Хабарландыру жіберіліп жатыр";
            break;
        case 'sent':
            echo "✅ Хабарландыру жіберілді";
            break;
        case 'failed':
            echo "❌ Жіберу қатесі: " . $statusData['error_message'];
            break;
    }
}

// ❌ Жоспарланған хабарландыруды болдырмау
$scheduledNotificationId = 'scheduled-notification-uuid';
$cancelResponse = AituApps::cancelScheduledNotification($scheduledNotificationId);

if ($cancelResponse->isSuccessful()) {
    echo "✅ Жоспарланған хабарландыру болдырылмады";
} else {
    echo "❌ Хабарландыруды болдырмау мүмкін болмады: " . $cancelResponse->getError()['message'];
}
```

</details>

---

## 🛠️ Laravel-сыз пайдалану

SDK кез келген PHP жобасында пайдаланылуы мүмкін:

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
    'title' => 'Тақырып',
    'body' => 'Хабарландыру мәтіні',
    'user_id' => 'user-uuid-123'
]);
```

---

## 🧪 Тестілеу

### 🚀 Тесттерді іске қосу

```bash
# Барлық тесттер
composer test

# Код жамылуымен тесттер
composer test-coverage

# Тек unit тесттер
composer test -- --testsuite=Unit

# Тек feature тесттер  
composer test -- --testsuite=Feature

# Тек integration тесттер
composer test -- --testsuite=Integration
```

### 📊 Код талдау

```bash
# PHPStan талдау
composer analyse

# PHP CS Fixer
composer format

# Код стилін тексеру
composer check-style
```

### ✅ Орнатуды тексеру

Тест маршрутын жасаңыз:

```php
// routes/web.php
Route::get('/test-aitu', function () {
    try {
        // 🧪 Aitu Passport тесті
        $passportClient = app(\MadArlan\AituMessenger\AituPassportClient::class);
        $authUrl = $passportClient->getAuthorizationUrl(['profile']);
        
        // 🧪 Aitu Apps тесті
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

## 🔧 Мәселелерді шешу

<details>
<summary><strong>Жиі кездесетін мәселелер мен олардың шешімдері</strong></summary>

### 🚫 Автожүктеу мәселесі

```bash
composer dump-autoload
php artisan optimize:clear
```

### 🗂️ Кэш мәселелері

```bash
# Барлық кэштерді тазалау
php artisan optimize:clear

# Немесе жеке-жеке
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 🔐 Рұқсат мәселелері

```bash
# Linux/Mac
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/

# Windows (PowerShell администратор ретінде)
icacls storage /grant "IIS_IUSRS:(OI)(CI)F" /T
```

### 🌐 Webhook мәселелері

1. **Aitu басқару панелінде webhook URL-дерін тексеріңіз**
2. **HTTPS дұрыс бапталғанына көз жеткізіңіз**
3. **`.env` файлындағы құпия кілтті тексеріңіз**
4. **Жөндеу үшін логтауды қосыңыз**:

```env
AITU_LOGGING_ENABLED=true
LOG_LEVEL=debug
```

### 📡 API сұраулар мәселелері

```php
// Конфигурацияда таймаутты ұлғайтыңыз
'http' => [
    'timeout' => 60, // секунд
    'retry_attempts' => 5,
    'retry_delay' => 1000, // миллисекунд
],
```

</details>

---

## 📖 Құжаттама

### 📚 Толық нұсқаулықтар

- 📋 [**Талаптар мен орнату**](#-орнату) - Орнатудың толық нұсқаулығы
- ⚙️ [**Конфигурация**](#️-конфигурация) - Барлық параметрлерді баптау
- 🚀 [**Жылдам бастау**](#-жылдам-бастау) - 5 минутта пайдалануды бастаңыз
- 💡 [**Пайдалану мысалдары**](#-пайдалану-мысалдары) - Дайын шешімдер
- 🧪 [**Тестілеу**](#-тестілеу) - Кодыңызды қалай тестілеу керек

### 📁 Код мысалдары

`examples/` қалтасында дайын мысалдарды табасыз:

- 🔐 [`passport_oauth.php`](examples/passport_oauth.php) - OAuth авторизациясы
- 📱 [`apps_notifications.php`](examples/apps_notifications.php) - Push хабарландырулары
- 🔗 [`webhook_handler.php`](examples/webhook_handler.php) - Webhook өңдеу

### 🧪 Тесттер

Барлық мүмкіндіктерді түсіну үшін тесттерді зерттеңіз:

- 🔬 [`tests/Unit/`](tests/Unit/) - Unit тесттер
- 🎯 [`tests/Feature/`](tests/Feature/) - Feature тесттер
- 🔗 [`tests/Integration/`](tests/Integration/) - Integration тесттер

---

## 🤝 Қолдау

<div align="center">

### 💬 Бізбен байланыс

[![Email](https://img.shields.io/badge/Email-madinovarlan%40gmail.com-blue?style=for-the-badge&logo=gmail)](mailto:madinovarlan@gmail.com)
[![GitHub Issues](https://img.shields.io/badge/GitHub-Issues-green?style=for-the-badge&logo=github)](https://github.com/madarlan/aitu-messenger-php/issues)
[![Documentation](https://img.shields.io/badge/Docs-Wiki-orange?style=for-the-badge&logo=gitbook)](https://github.com/madarlan/aitu-messenger-php/wiki)

</div>

### 🆘 Көмек алу

1. 📖 **Алдымен құжаттаманы тексеріңіз** - сұрақтардың көпшілігі жауабы бар
2. 🔍 **Issues-та іздеңіз** - мүмкін сіздің сұрағыңыз талқыланған
3. 🐛 **Жаңа Issue жасаңыз** - жауап таппасаңыз
4. 📧 **Бізге жазыңыз** - шұғыл сұрақтар үшін

### 🚨 Қауіпсіздік туралы хабарлау

Қауіпсіздік осалдығын тапсаңыз, **жария Issue жасамаңыз**.
[madinovarlan@gmail.com](mailto:madinovarlan@gmail.com) мекенжайына email жіберіңіз.

---

## 🤝 Дамытуға қатысу

Біз дамытуға қатысуды қуаттаймыз!

### 🛠️ Үлес қосу жолы

1. 🍴 Репозиторийді **Fork** жасаңыз
2. 🌿 Функцияңыз үшін **тармақ жасаңыз** (`git checkout -b feature/amazing-feature`)
3. ✅ Жаңа функционалдық үшін **тесттер қосыңыз**
4. 🧪 **Барлық тесттер өтетініне көз жеткізіңіз** (`composer test`)
5. 📝 Өзгерістеріңізді **Commit** жасаңыз (`git commit -m 'Add amazing feature'`)
6. 📤 Тармаққа **Push** жасаңыз (`git push origin feature/amazing-feature`)
7. 🔄 **Pull Request жасаңыз**

### 📋 Дамыту ережелері

- ✅ **PSR-12** кодтау стандартын ұстаныңыз
- 🧪 Жаңа функционалдықты **тесттермен жабыңыз**
- 📝 Коддағы өзгерістерді **құжаттаңыз**
- 🔄 Қажет болса **CHANGELOG.md жаңартыңыз**

---

## 📄 Лицензия

Бұл жоба **MIT License** лицензиясымен лицензияланған - толық ақпарат [**LICENSE**](LICENSE.md) файлында.

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

### ⭐ Жоба пайдалы болса, жұлдызша қойыңыз!

[![GitHub stars](https://img.shields.io/github/stars/madarlan/aitu-messenger-php?style=social)](https://github.com/madarlan/aitu-messenger-php/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/madarlan/aitu-messenger-php?style=social)](https://github.com/madarlan/aitu-messenger-php/network/members)
[![GitHub watchers](https://img.shields.io/github/watchers/madarlan/aitu-messenger-php?style=social)](https://github.com/madarlan/aitu-messenger-php/watchers)

---

*Күрделі интеграцияларды қарапайым шешімдерге айналдырамыз*

</div>
