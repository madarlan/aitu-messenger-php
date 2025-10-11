<?php

use Illuminate\Support\Facades\Route;
use MadArlan\AituMessenger\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Aitu Messenger API Routes
|--------------------------------------------------------------------------
|
| API маршруты для обработки webhook'ов от Aitu Passport и Aitu Apps
| Эти роуты не имеют web middleware и подходят для API интеграции
|
*/

// Группа маршрутов для webhook'ов
Route::prefix('webhooks/aitu')->name('aitu.webhooks.')->middleware('aitu.webhook')->group(function () {
    
    // Webhook для Aitu Passport
    Route::post('passport', [WebhookController::class, 'handlePassportWebhook'])
        ->name('passport');
    
    // Webhook для Aitu Apps
    Route::post('apps', [WebhookController::class, 'handleAppsWebhook'])
        ->name('apps');
    
    // Общий webhook (если нужен)
    Route::post('general', [WebhookController::class, 'handleGeneralWebhook'])
        ->name('general');
});

// Маршруты для OAuth callback'ов (только для API использования)
Route::prefix('auth/aitu')->name('aitu.auth.')->group(function () {
    
    // OAuth callback для Aitu Passport
    Route::get('callback', [WebhookController::class, 'handleOAuthCallback'])
        ->name('callback');
    
    // Маршрут для инициации OAuth авторизации
    Route::get('login', [WebhookController::class, 'redirectToProvider'])
        ->name('login');
    
    // Маршрут для выхода
    Route::post('logout', [WebhookController::class, 'logout'])
        ->name('logout');
});