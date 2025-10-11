<?php

namespace MadArlan\AituMessenger\Facades;

use Illuminate\Support\Facades\Facade;
use MadArlan\AituMessenger\AituAppsClient;

/**
 * @method static array sendTargetedNotification(string $userId, string $title, string $body, array $options = [])
 * @method static array sendMultipleNotifications(array $notifications)
 * @method static array createNotification(string $title, string $body, array $options = [])
 * @method static bool validateNotificationParams(array $params)
 *
 * @see \MadArlan\AituMessenger\AituAppsClient
 */
class AituApps extends Facade
{
    /**
     * Получить зарегистрированное имя компонента
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return AituAppsClient::class;
    }
}