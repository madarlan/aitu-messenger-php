<?php

namespace MadArlan\AituMessenger\Providers;

use Illuminate\Support\ServiceProvider;
use MadArlan\AituMessenger\AituPassportClient;
use MadArlan\AituMessenger\AituAppsClient;
use MadArlan\AituMessenger\Http\HttpClient;
use MadArlan\AituMessenger\Middleware\VerifyAituWebhook;

class AituMessengerServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов
     */
    public function register(): void
    {
        // Объединяем конфигурацию
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/aitu-messenger.php',
            'aitu-messenger'
        );

        // Регистрируем HTTP клиент
        $this->app->singleton(HttpClient::class, function ($app) {
            return new HttpClient();
        });

        // Регистрируем Aitu Passport клиент
        $this->app->singleton(AituPassportClient::class, function ($app) {
            $config = $app['config']['aitu-messenger.passport'];
            
            return new AituPassportClient(
                $config['client_id'],
                $config['client_secret'],
                $config['redirect_uri'],
                $app->make(HttpClient::class),
                $config
            );
        });

        // Регистрируем Aitu Apps клиент
        $this->app->singleton(AituAppsClient::class, function ($app) {
            $config = $app['config']['aitu-messenger.apps'];
            
            return new AituAppsClient(
                $config['app_id'],
                $config['api_key'],
                $app->make(HttpClient::class),
                $config
            );
        });

        // Регистрируем алиасы для фасадов
        $this->app->alias(AituPassportClient::class, 'aitu.passport');
        $this->app->alias(AituAppsClient::class, 'aitu.apps');
    }

    /**
     * Загрузка сервисов
     */
    public function boot(): void
    {
        // Регистрация middleware
        $this->app['router']->aliasMiddleware('aitu.webhook', VerifyAituWebhook::class);

        // Публикация конфигурационного файла
        $this->publishes([
            __DIR__ . '/../../config/aitu-messenger.php' => config_path('aitu-messenger.php'),
        ], 'aitu-messenger-config');

        // Публикация миграций (если будут нужны)
        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
        ], 'aitu-messenger-migrations');

        // Регистрация команд Artisan (если будут нужны)
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Здесь можно добавить команды Artisan
            ]);
        }

        // Регистрация маршрутов для webhook'ов (API роуты без web middleware)
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');

        // Регистрация представлений (если будут нужны)
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'aitu-messenger');

        // Публикация представлений
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/aitu-messenger'),
        ], 'aitu-messenger-views');

        // Регистрация переводов
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'aitu-messenger');

        // Публикация переводов
        $this->publishes([
            __DIR__ . '/../../resources/lang' => resource_path('lang/vendor/aitu-messenger'),
        ], 'aitu-messenger-lang');
    }

    /**
     * Получить сервисы, предоставляемые провайдером
     *
     * @return array
     */
    public function provides(): array
    {
        return [
            HttpClient::class,
            AituPassportClient::class,
            AituAppsClient::class,
            'aitu.passport',
            'aitu.apps',
        ];
    }
}