<?php

namespace MadArlan\AituMessenger\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MadArlan\AituMessenger\Utils\SignatureValidator;

class VerifyAituWebhook
{
    private SignatureValidator $signatureValidator;

    public function __construct(SignatureValidator $signatureValidator)
    {
        $this->signatureValidator = $signatureValidator;
    }

    /**
     * Обработка входящего запроса
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $source
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $source = null)
    {
        // Проверяем, включена ли верификация подписи
        if (!config('aitu-messenger.webhooks.verify_signature', true)) {
            return $next($request);
        }

        // Получаем подпись из заголовка
        $signatureHeader = config('aitu-messenger.webhooks.signature_header', 'X-Aitu-Signature');
        $signature = $request->header($signatureHeader);

        if (!$signature) {
            return response()->json([
                'error' => 'Missing signature header',
                'message' => "Header '{$signatureHeader}' is required"
            ], 401);
        }

        // Получаем тело запроса
        $payload = $request->getContent();
        
        // Получаем секретный ключ
        $secret = $this->getWebhookSecret($source);
        
        if (!$secret) {
            return response()->json([
                'error' => 'Webhook secret not configured',
                'message' => 'Unable to verify webhook signature'
            ], 500);
        }

        // Верифицируем подпись
        if (!$this->signatureValidator->verifyWebhookSignature($payload, $signature, $secret)) {
            return response()->json([
                'error' => 'Invalid signature',
                'message' => 'Webhook signature verification failed'
            ], 401);
        }

        return $next($request);
    }

    /**
     * Получить секретный ключ для webhook'а
     *
     * @param string|null $source
     * @return string|null
     */
    private function getWebhookSecret(?string $source): ?string
    {
        // Если источник не указан, используем общий секрет
        if (!$source) {
            return config('aitu-messenger.webhooks.secret');
        }

        // Пытаемся получить специфичный секрет для источника
        $sourceSecret = config("aitu-messenger.webhooks.secrets.{$source}");
        
        // Если специфичный секрет не найден, используем общий
        return $sourceSecret ?? config('aitu-messenger.webhooks.secret');
    }
}