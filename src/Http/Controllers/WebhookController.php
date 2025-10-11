<?php

namespace MadArlan\AituMessenger\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use MadArlan\AituMessenger\AituPassportClient;
use MadArlan\AituMessenger\AituAppsClient;
use MadArlan\AituMessenger\Exceptions\AituApiException;
use MadArlan\AituMessenger\Utils\SignatureValidator;

class WebhookController extends Controller
{
    private AituPassportClient $passportClient;
    private AituAppsClient $appsClient;
    private SignatureValidator $signatureValidator;

    public function __construct(
        AituPassportClient $passportClient,
        AituAppsClient $appsClient,
        SignatureValidator $signatureValidator
    ) {
        $this->passportClient = $passportClient;
        $this->appsClient = $appsClient;
        $this->signatureValidator = $signatureValidator;
    }

    /**
     * Обработка webhook'а от Aitu Passport
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handlePassportWebhook(Request $request): JsonResponse
    {
        try {
            // Верификация подписи webhook'а
            if (config('aitu-messenger.webhooks.verify_signature')) {
                $this->verifyWebhookSignature($request, 'passport');
            }

            $payload = $request->all();
            
            // Логирование входящего webhook'а
            if (config('aitu-messenger.logging.enabled')) {
                Log::channel(config('aitu-messenger.logging.channel'))
                    ->info('Aitu Passport webhook received', [
                        'payload' => $payload,
                        'headers' => $request->headers->all(),
                    ]);
            }

            // Обработка различных типов событий
            $eventType = $payload['event_type'] ?? 'unknown';
            
            switch ($eventType) {
                case 'user.authorized':
                    $this->handleUserAuthorized($payload);
                    break;
                    
                case 'user.deauthorized':
                    $this->handleUserDeauthorized($payload);
                    break;
                    
                case 'token.revoked':
                    $this->handleTokenRevoked($payload);
                    break;
                    
                default:
                    $this->handleUnknownEvent($payload, 'passport');
            }

            return response()->json(['status' => 'success']);
            
        } catch (\Exception $e) {
            Log::error('Aitu Passport webhook error', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }

    /**
     * Обработка webhook'а от Aitu Apps
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleAppsWebhook(Request $request): JsonResponse
    {
        try {
            // Верификация подписи webhook'а
            if (config('aitu-messenger.webhooks.verify_signature')) {
                $this->verifyWebhookSignature($request, 'apps');
            }

            $payload = $request->all();
            
            // Логирование входящего webhook'а
            if (config('aitu-messenger.logging.enabled')) {
                Log::channel(config('aitu-messenger.logging.channel'))
                    ->info('Aitu Apps webhook received', [
                        'payload' => $payload,
                        'headers' => $request->headers->all(),
                    ]);
            }

            // Обработка различных типов событий
            $eventType = $payload['event_type'] ?? 'unknown';
            
            switch ($eventType) {
                case 'notification.delivered':
                    $this->handleNotificationDelivered($payload);
                    break;
                    
                case 'notification.clicked':
                    $this->handleNotificationClicked($payload);
                    break;
                    
                case 'notification.failed':
                    $this->handleNotificationFailed($payload);
                    break;
                    
                default:
                    $this->handleUnknownEvent($payload, 'apps');
            }

            return response()->json(['status' => 'success']);
            
        } catch (\Exception $e) {
            Log::error('Aitu Apps webhook error', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }

    /**
     * Обработка общего webhook'а
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleGeneralWebhook(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            
            // Определяем источник webhook'а
            $source = $payload['source'] ?? 'unknown';
            
            switch ($source) {
                case 'passport':
                    return $this->handlePassportWebhook($request);
                    
                case 'apps':
                    return $this->handleAppsWebhook($request);
                    
                default:
                    Log::warning('Unknown webhook source', ['payload' => $payload]);
                    return response()->json(['status' => 'ignored'], 200);
            }
            
        } catch (\Exception $e) {
            Log::error('General webhook error', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }

    /**
     * Обработка OAuth callback'а
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function handleOAuthCallback(Request $request): RedirectResponse
    {
        try {
            $code = $request->get('code');
            $state = $request->get('state');
            $error = $request->get('error');

            if ($error) {
                Log::error('OAuth callback error', [
                    'error' => $error,
                    'error_description' => $request->get('error_description'),
                ]);

                return redirect()->route('login')->withErrors([
                    'oauth' => 'Authorization failed: ' . $error
                ]);
            }

            if (!$code) {
                return redirect()->route('login')->withErrors([
                    'oauth' => 'Authorization code not received'
                ]);
            }

            // Обмен кода на токен
            $tokenData = $this->passportClient->exchangeCodeForToken($code, $state);
            
            // Получение информации о пользователе
            $userInfo = $this->passportClient->getUserInfo($tokenData['access_token']);

            // Здесь можно сохранить пользователя в базу данных
            // или выполнить другие действия после успешной авторизации

            return redirect()->intended('/dashboard')->with('success', 'Successfully authorized with Aitu');
            
        } catch (AituApiException $e) {
            Log::error('OAuth callback API error', [
                'error' => $e->getMessage(),
                'context' => $e->getContext(),
            ]);

            return redirect()->route('login')->withErrors([
                'oauth' => 'Authorization failed: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            Log::error('OAuth callback error', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login')->withErrors([
                'oauth' => 'Authorization failed'
            ]);
        }
    }

    /**
     * Перенаправление на провайдера OAuth
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function redirectToProvider(Request $request): RedirectResponse
    {
        $scopes = $request->get('scopes', config('aitu-messenger.passport.default_scopes'));
        $state = $request->get('state');

        $authUrl = $this->passportClient->getAuthorizationUrl($scopes, $state);

        return redirect($authUrl);
    }

    /**
     * Выход из системы
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->get('token');
            
            if ($token) {
                $this->passportClient->revokeToken($token);
            }

            return response()->json(['status' => 'success']);
            
        } catch (\Exception $e) {
            Log::error('Logout error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Верификация подписи webhook'а
     *
     * @param Request $request
     * @param string $type
     * @throws AituApiException
     */
    private function verifyWebhookSignature(Request $request, string $type): void
    {
        $signature = $request->header(config('aitu-messenger.webhooks.signature_header'));
        $payload = $request->getContent();
        $secret = config('aitu-messenger.webhooks.secret');

        if (!$signature || !$this->signatureValidator->verifyWebhookSignature($payload, $signature, $secret)) {
            throw new AituApiException('Invalid webhook signature');
        }
    }

    /**
     * Обработка события авторизации пользователя
     *
     * @param array $payload
     */
    private function handleUserAuthorized(array $payload): void
    {
        // Здесь можно добавить логику обработки авторизации пользователя
        Log::info('User authorized', ['user_id' => $payload['user_id'] ?? null]);
    }

    /**
     * Обработка события деавторизации пользователя
     *
     * @param array $payload
     */
    private function handleUserDeauthorized(array $payload): void
    {
        // Здесь можно добавить логику обработки деавторизации пользователя
        Log::info('User deauthorized', ['user_id' => $payload['user_id'] ?? null]);
    }

    /**
     * Обработка события отзыва токена
     *
     * @param array $payload
     */
    private function handleTokenRevoked(array $payload): void
    {
        // Здесь можно добавить логику обработки отзыва токена
        Log::info('Token revoked', ['token_id' => $payload['token_id'] ?? null]);
    }

    /**
     * Обработка доставки уведомления
     *
     * @param array $payload
     */
    private function handleNotificationDelivered(array $payload): void
    {
        // Здесь можно добавить логику обработки доставки уведомления
        Log::info('Notification delivered', ['notification_id' => $payload['notification_id'] ?? null]);
    }

    /**
     * Обработка клика по уведомлению
     *
     * @param array $payload
     */
    private function handleNotificationClicked(array $payload): void
    {
        // Здесь можно добавить логику обработки клика по уведомлению
        Log::info('Notification clicked', ['notification_id' => $payload['notification_id'] ?? null]);
    }

    /**
     * Обработка неудачной доставки уведомления
     *
     * @param array $payload
     */
    private function handleNotificationFailed(array $payload): void
    {
        // Здесь можно добавить логику обработки неудачной доставки
        Log::warning('Notification failed', [
            'notification_id' => $payload['notification_id'] ?? null,
            'error' => $payload['error'] ?? null,
        ]);
    }

    /**
     * Обработка неизвестного события
     *
     * @param array $payload
     * @param string $source
     */
    private function handleUnknownEvent(array $payload, string $source): void
    {
        Log::warning('Unknown webhook event', [
            'source' => $source,
            'event_type' => $payload['event_type'] ?? 'unknown',
            'payload' => $payload,
        ]);
    }
}