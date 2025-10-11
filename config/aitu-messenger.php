<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Aitu Passport Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки для интеграции с Aitu Passport API
    |
    */
    'passport' => [
        /*
        |--------------------------------------------------------------------------
        | Client Credentials
        |--------------------------------------------------------------------------
        |
        | Учетные данные вашего приложения в Aitu Passport
        |
        */
        'client_id' => env('AITU_PASSPORT_CLIENT_ID'),
        'client_secret' => env('AITU_PASSPORT_CLIENT_SECRET'),
        'redirect_uri' => env('AITU_PASSPORT_REDIRECT_URI'),

        /*
        |--------------------------------------------------------------------------
        | API Endpoints
        |--------------------------------------------------------------------------
        |
        | URL-адреса для различных эндпоинтов Aitu Passport API
        |
        */
        'base_url' => env('AITU_PASSPORT_BASE_URL', 'https://passport.aitu.io'),
        'auth_url' => env('AITU_PASSPORT_AUTH_URL', 'https://passport.aitu.io/oauth/authorize'),
        'token_url' => env('AITU_PASSPORT_TOKEN_URL', 'https://passport.aitu.io/oauth/token'),
        'user_info_url' => env('AITU_PASSPORT_USER_INFO_URL', 'https://passport.aitu.io/api/user'),
        'revoke_url' => env('AITU_PASSPORT_REVOKE_URL', 'https://passport.aitu.io/oauth/revoke'),

        /*
        |--------------------------------------------------------------------------
        | Default Scopes
        |--------------------------------------------------------------------------
        |
        | Области доступа по умолчанию для OAuth авторизации
        |
        */
        'default_scopes' => [
            'profile',
            'email',
        ],

        /*
        |--------------------------------------------------------------------------
        | Signature Settings
        |--------------------------------------------------------------------------
        |
        | Настройки для работы с цифровой подписью
        |
        */
        'signature' => [
            'secret' => env('AITU_PASSPORT_SIGNATURE_SECRET'),
            'algorithm' => env('AITU_PASSPORT_SIGNATURE_ALGORITHM', 'sha256'),
        ],

        /*
        |--------------------------------------------------------------------------
        | HTTP Client Settings
        |--------------------------------------------------------------------------
        |
        | Настройки HTTP клиента для запросов к API
        |
        */
        'http' => [
            'timeout' => env('AITU_PASSPORT_TIMEOUT', 30),
            'connect_timeout' => env('AITU_PASSPORT_CONNECT_TIMEOUT', 10),
            'verify_ssl' => env('AITU_PASSPORT_VERIFY_SSL', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Aitu Apps Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки для интеграции с Aitu Apps API
    |
    */
    'apps' => [
        /*
        |--------------------------------------------------------------------------
        | App Credentials
        |--------------------------------------------------------------------------
        |
        | Учетные данные вашего приложения в Aitu Apps
        |
        */
        'app_id' => env('AITU_APPS_APP_ID'),
        'api_key' => env('AITU_APPS_API_KEY'),

        /*
        |--------------------------------------------------------------------------
        | API Endpoints
        |--------------------------------------------------------------------------
        |
        | URL-адреса для различных эндпоинтов Aitu Apps API
        |
        */
        'base_url' => env('AITU_APPS_BASE_URL', 'https://api.aitu.io'),
        'notifications_url' => env('AITU_APPS_NOTIFICATIONS_URL', 'https://api.aitu.io/v1/notifications'),

        /*
        |--------------------------------------------------------------------------
        | Push Notifications Settings
        |--------------------------------------------------------------------------
        |
        | Настройки для отправки push-уведомлений
        |
        */
        'notifications' => [
            'default_ttl' => env('AITU_APPS_DEFAULT_TTL', 3600), // 1 час
            'max_batch_size' => env('AITU_APPS_MAX_BATCH_SIZE', 100),
            'retry_attempts' => env('AITU_APPS_RETRY_ATTEMPTS', 3),
        ],

        /*
        |--------------------------------------------------------------------------
        | HTTP Client Settings
        |--------------------------------------------------------------------------
        |
        | Настройки HTTP клиента для запросов к API
        |
        */
        'http' => [
            'timeout' => env('AITU_APPS_TIMEOUT', 30),
            'connect_timeout' => env('AITU_APPS_CONNECT_TIMEOUT', 10),
            'verify_ssl' => env('AITU_APPS_VERIFY_SSL', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки для обработки webhook'ов от Aitu
    |
    */
    'webhooks' => [
        /*
        |--------------------------------------------------------------------------
        | Webhook Secret
        |--------------------------------------------------------------------------
        |
        | Секретный ключ для верификации webhook'ов
        |
        */
        'secret' => env('AITU_WEBHOOK_SECRET'),

        /*
        |--------------------------------------------------------------------------
        | Webhook Routes
        |--------------------------------------------------------------------------
        |
        | Маршруты для обработки различных типов webhook'ов
        |
        */
        'routes' => [
            'passport' => env('AITU_WEBHOOK_PASSPORT_ROUTE', '/api/webhooks/aitu/passport'),
            'apps' => env('AITU_WEBHOOK_APPS_ROUTE', '/api/webhooks/aitu/apps'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Webhook Verification
        |--------------------------------------------------------------------------
        |
        | Настройки верификации webhook'ов
        |
        */
        'verify_signature' => env('AITU_WEBHOOK_VERIFY_SIGNATURE', true),
        'signature_header' => env('AITU_WEBHOOK_SIGNATURE_HEADER', 'X-Aitu-Signature'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки логирования для SDK
    |
    */
    'logging' => [
        'enabled' => env('AITU_LOGGING_ENABLED', false),
        'channel' => env('AITU_LOGGING_CHANNEL', 'default'),
        'level' => env('AITU_LOGGING_LEVEL', 'info'),
        'log_requests' => env('AITU_LOG_REQUESTS', false),
        'log_responses' => env('AITU_LOG_RESPONSES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки кеширования для SDK
    |
    */
    'cache' => [
        'enabled' => env('AITU_CACHE_ENABLED', true),
        'store' => env('AITU_CACHE_STORE', 'default'),
        'prefix' => env('AITU_CACHE_PREFIX', 'aitu_messenger'),
        'ttl' => [
            'tokens' => env('AITU_CACHE_TOKENS_TTL', 3600), // 1 час
            'user_info' => env('AITU_CACHE_USER_INFO_TTL', 1800), // 30 минут
        ],
    ],
];