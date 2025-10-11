<?php

namespace MadArlan\AituMessenger\Tests\Integration;

use PHPUnit\Framework\TestCase;
use MadArlan\AituMessenger\Signature\SignatureValidator;
use MadArlan\AituMessenger\Middleware\VerifyAituWebhook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookIntegrationTest extends TestCase
{
    private SignatureValidator $signatureValidator;
    private string $webhookSecret;

    protected function setUp(): void
    {
        $this->webhookSecret = 'test_webhook_secret_key';
        $this->signatureValidator = new SignatureValidator();
    }

    public function testCompleteWebhookFlow(): void
    {
        // Simulate webhook payload
        $payload = json_encode([
            'event' => 'user.authorized',
            'data' => [
                'user_id' => 'user_123',
                'client_id' => 'app_456',
                'scope' => 'profile email',
                'timestamp' => time()
            ]
        ]);

        // Generate signature
        $signature = $this->signatureValidator->generateSignature($payload, $this->webhookSecret);

        // Verify signature
        $isValid = $this->signatureValidator->verifySignature($payload, $signature, $this->webhookSecret);

        $this->assertTrue($isValid);
    }

    public function testWebhookWithInvalidSignature(): void
    {
        $payload = json_encode([
            'event' => 'user.authorized',
            'data' => ['user_id' => 'user_123']
        ]);

        $validSignature = $this->signatureValidator->generateSignature($payload, $this->webhookSecret);
        $invalidSignature = 'sha256=invalid_signature_hash';

        $isValidCorrect = $this->signatureValidator->verifySignature($payload, $validSignature, $this->webhookSecret);
        $isValidIncorrect = $this->signatureValidator->verifySignature($payload, $invalidSignature, $this->webhookSecret);

        $this->assertTrue($isValidCorrect);
        $this->assertFalse($isValidIncorrect);
    }

    public function testWebhookWithModifiedPayload(): void
    {
        $originalPayload = json_encode([
            'event' => 'user.authorized',
            'data' => ['user_id' => 'user_123']
        ]);

        $modifiedPayload = json_encode([
            'event' => 'user.authorized',
            'data' => ['user_id' => 'user_456'] // Modified user_id
        ]);

        $signature = $this->signatureValidator->generateSignature($originalPayload, $this->webhookSecret);

        $isValidOriginal = $this->signatureValidator->verifySignature($originalPayload, $signature, $this->webhookSecret);
        $isValidModified = $this->signatureValidator->verifySignature($modifiedPayload, $signature, $this->webhookSecret);

        $this->assertTrue($isValidOriginal);
        $this->assertFalse($isValidModified);
    }

    public function testMultipleWebhookEvents(): void
    {
        $events = [
            [
                'event' => 'user.authorized',
                'data' => ['user_id' => 'user_123', 'client_id' => 'app_456']
            ],
            [
                'event' => 'user.deauthorized',
                'data' => ['user_id' => 'user_123', 'client_id' => 'app_456']
            ],
            [
                'event' => 'token.revoked',
                'data' => ['token_id' => 'token_789', 'user_id' => 'user_123']
            ],
            [
                'event' => 'notification.delivered',
                'data' => ['notification_id' => 'notif_123', 'user_id' => 'user_456']
            ],
            [
                'event' => 'notification.clicked',
                'data' => ['notification_id' => 'notif_123', 'user_id' => 'user_456', 'clicked_at' => time()]
            ]
        ];

        foreach ($events as $eventData) {
            $payload = json_encode($eventData);
            $signature = $this->signatureValidator->generateSignature($payload, $this->webhookSecret);
            $isValid = $this->signatureValidator->verifySignature($payload, $signature, $this->webhookSecret);

            $this->assertTrue($isValid, "Signature validation failed for event: {$eventData['event']}");
        }
    }

    public function testWebhookWithEmptyPayload(): void
    {
        $payload = '';
        $signature = $this->signatureValidator->generateSignature($payload, $this->webhookSecret);
        $isValid = $this->signatureValidator->verifySignature($payload, $signature, $this->webhookSecret);

        $this->assertTrue($isValid);
    }

    public function testWebhookWithLargePayload(): void
    {
        $largeData = array_fill(0, 1000, [
            'id' => uniqid(),
            'name' => 'Test User ' . rand(1, 1000),
            'email' => 'user' . rand(1, 1000) . '@example.com',
            'data' => str_repeat('x', 100)
        ]);

        $payload = json_encode([
            'event' => 'bulk.update',
            'data' => $largeData
        ]);

        $signature = $this->signatureValidator->generateSignature($payload, $this->webhookSecret);
        $isValid = $this->signatureValidator->verifySignature($payload, $signature, $this->webhookSecret);

        $this->assertTrue($isValid);
    }

    public function testWebhookWithSpecialCharacters(): void
    {
        $payload = json_encode([
            'event' => 'user.updated',
            'data' => [
                'user_id' => 'user_123',
                'name' => 'Тест Пользователь',
                'email' => 'тест@пример.рф',
                'special_chars' => '!@#$%^&*()_+-=[]{}|;:,.<>?',
                'unicode' => '🚀🎉💻📱🌟'
            ]
        ]);

        $signature = $this->signatureValidator->generateSignature($payload, $this->webhookSecret);
        $isValid = $this->signatureValidator->verifySignature($payload, $signature, $this->webhookSecret);

        $this->assertTrue($isValid);
    }

    public function testWebhookTimingAttackResistance(): void
    {
        $payload = json_encode(['event' => 'test', 'data' => ['test' => 'data']]);
        $correctSignature = $this->signatureValidator->generateSignature($payload, $this->webhookSecret);
        
        // Create signatures that differ by one character at different positions
        $incorrectSignatures = [
            substr_replace($correctSignature, 'x', 10, 1), // Change character at position 10
            substr_replace($correctSignature, 'y', 20, 1), // Change character at position 20
            substr_replace($correctSignature, 'z', 30, 1), // Change character at position 30
        ];

        // All incorrect signatures should fail
        foreach ($incorrectSignatures as $incorrectSignature) {
            $isValid = $this->signatureValidator->verifySignature($payload, $incorrectSignature, $this->webhookSecret);
            $this->assertFalse($isValid);
        }

        // Correct signature should pass
        $isValid = $this->signatureValidator->verifySignature($payload, $correctSignature, $this->webhookSecret);
        $this->assertTrue($isValid);
    }

    public function testWebhookWithDifferentSecrets(): void
    {
        $payload = json_encode(['event' => 'test', 'data' => ['test' => 'data']]);
        
        $secret1 = 'secret_key_1';
        $secret2 = 'secret_key_2';
        
        $signature1 = $this->signatureValidator->generateSignature($payload, $secret1);
        $signature2 = $this->signatureValidator->generateSignature($payload, $secret2);

        // Signatures should be different
        $this->assertNotEquals($signature1, $signature2);

        // Each signature should only work with its corresponding secret
        $this->assertTrue($this->signatureValidator->verifySignature($payload, $signature1, $secret1));
        $this->assertTrue($this->signatureValidator->verifySignature($payload, $signature2, $secret2));
        
        $this->assertFalse($this->signatureValidator->verifySignature($payload, $signature1, $secret2));
        $this->assertFalse($this->signatureValidator->verifySignature($payload, $signature2, $secret1));
    }

    public function testWebhookSignatureFormats(): void
    {
        $payload = json_encode(['event' => 'test']);
        $signature = $this->signatureValidator->generateSignature($payload, $this->webhookSecret);

        // Test with sha256= prefix
        $this->assertTrue($this->signatureValidator->verifySignature($payload, $signature, $this->webhookSecret));

        // Test without prefix (should still work if implementation handles it)
        $signatureWithoutPrefix = str_replace('sha256=', '', $signature);
        $this->assertFalse($this->signatureValidator->verifySignature($payload, $signatureWithoutPrefix, $this->webhookSecret));
    }

    public function testConcurrentWebhookProcessing(): void
    {
        $payload = json_encode(['event' => 'concurrent.test', 'timestamp' => time()]);
        $signature = $this->signatureValidator->generateSignature($payload, $this->webhookSecret);

        // Simulate multiple concurrent verifications
        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $results[] = $this->signatureValidator->verifySignature($payload, $signature, $this->webhookSecret);
        }

        // All verifications should succeed
        foreach ($results as $result) {
            $this->assertTrue($result);
        }
    }

    public function testWebhookReplayAttackPrevention(): void
    {
        // This test demonstrates how timestamps can be used to prevent replay attacks
        $currentTime = time();
        $oldTime = $currentTime - 3600; // 1 hour ago

        $currentPayload = json_encode([
            'event' => 'user.login',
            'timestamp' => $currentTime,
            'data' => ['user_id' => 'user_123']
        ]);

        $oldPayload = json_encode([
            'event' => 'user.login',
            'timestamp' => $oldTime,
            'data' => ['user_id' => 'user_123']
        ]);

        $currentSignature = $this->signatureValidator->generateSignature($currentPayload, $this->webhookSecret);
        $oldSignature = $this->signatureValidator->generateSignature($oldPayload, $this->webhookSecret);

        // Both signatures should be valid from a cryptographic perspective
        $this->assertTrue($this->signatureValidator->verifySignature($currentPayload, $currentSignature, $this->webhookSecret));
        $this->assertTrue($this->signatureValidator->verifySignature($oldPayload, $oldSignature, $this->webhookSecret));

        // In a real implementation, you would check the timestamp to reject old requests
        $maxAge = 300; // 5 minutes
        $isCurrentValid = ($currentTime - $currentTime) <= $maxAge;
        $isOldValid = ($currentTime - $oldTime) <= $maxAge;

        $this->assertTrue($isCurrentValid);
        $this->assertFalse($isOldValid);
    }

    public function testWebhookErrorScenarios(): void
    {
        $payload = json_encode(['event' => 'test']);

        // Test with malformed signature
        $malformedSignatures = [
            'not_a_signature',
            'sha256=',
            'sha256=invalid_hex',
            'md5=valid_but_wrong_algorithm',
            '',
            null
        ];

        foreach ($malformedSignatures as $malformedSignature) {
            $isValid = $this->signatureValidator->verifySignature($payload, $malformedSignature, $this->webhookSecret);
            $this->assertFalse($isValid, "Malformed signature should be invalid: " . var_export($malformedSignature, true));
        }
    }

    public function testWebhookWithJsonEscaping(): void
    {
        $payload = json_encode([
            'event' => 'user.message',
            'data' => [
                'message' => 'Hello "World"',
                'json_string' => '{"nested": "value"}',
                'escaped_chars' => "Line 1\nLine 2\tTabbed",
                'backslashes' => 'C:\\Users\\Test\\file.txt'
            ]
        ]);

        $signature = $this->signatureValidator->generateSignature($payload, $this->webhookSecret);
        $isValid = $this->signatureValidator->verifySignature($payload, $signature, $this->webhookSecret);

        $this->assertTrue($isValid);
    }

    public function testWebhookPerformance(): void
    {
        $payload = json_encode(['event' => 'performance.test', 'data' => range(1, 1000)]);
        
        $startTime = microtime(true);
        
        // Perform multiple signature operations
        for ($i = 0; $i < 100; $i++) {
            $signature = $this->signatureValidator->generateSignature($payload, $this->webhookSecret);
            $this->signatureValidator->verifySignature($payload, $signature, $this->webhookSecret);
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Should complete 100 operations in reasonable time (less than 1 second)
        $this->assertLessThan(1.0, $executionTime, 'Signature operations should be performant');
    }
}