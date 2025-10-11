<?php

namespace MadArlan\AituMessenger\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use MadArlan\AituMessenger\AituAppsClient;
use MadArlan\AituMessenger\Http\HttpClient;
use MadArlan\AituMessenger\Exceptions\AituApiException;
use MadArlan\AituMessenger\Exceptions\AituValidationException;

class AituAppsClientTest extends TestCase
{
    private AituAppsClient $client;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        
        $httpClient = new HttpClient([
            'base_uri' => 'https://apps-api.aitu.io',
            'timeout' => 30
        ], $guzzleClient);

        $this->client = new AituAppsClient(
            'test_app_id',
            'test_app_secret',
            $httpClient
        );
    }

    public function testSendTargetedNotification(): void
    {
        $successResponse = [
            'success' => true,
            'notification_id' => 'notif_123456',
            'message' => 'Notification sent successfully'
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($successResponse))
        );

        $result = $this->client->sendTargetedNotification(
            'user_123',
            'Test Title',
            'Test message body',
            ['custom_data' => 'value']
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('notif_123456', $result['notification_id']);
    }

    public function testSendTargetedNotificationWithInvalidUserId(): void
    {
        $this->expectException(AituValidationException::class);
        $this->expectExceptionMessage('User ID cannot be empty');

        $this->client->sendTargetedNotification('', 'Title', 'Body');
    }

    public function testSendTargetedNotificationWithEmptyTitle(): void
    {
        $this->expectException(AituValidationException::class);
        $this->expectExceptionMessage('Title cannot be empty');

        $this->client->sendTargetedNotification('user_123', '', 'Body');
    }

    public function testSendTargetedNotificationWithEmptyBody(): void
    {
        $this->expectException(AituValidationException::class);
        $this->expectExceptionMessage('Body cannot be empty');

        $this->client->sendTargetedNotification('user_123', 'Title', '');
    }

    public function testSendMultipleNotifications(): void
    {
        $successResponse = [
            'success' => true,
            'sent_count' => 2,
            'failed_count' => 0,
            'notification_ids' => ['notif_123', 'notif_456']
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($successResponse))
        );

        $notifications = [
            [
                'user_id' => 'user_123',
                'title' => 'Title 1',
                'body' => 'Body 1'
            ],
            [
                'user_id' => 'user_456',
                'title' => 'Title 2',
                'body' => 'Body 2',
                'data' => ['key' => 'value']
            ]
        ];

        $result = $this->client->sendMultipleNotifications($notifications);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['sent_count']);
        $this->assertEquals(0, $result['failed_count']);
        $this->assertCount(2, $result['notification_ids']);
    }

    public function testSendMultipleNotificationsWithEmptyArray(): void
    {
        $this->expectException(AituValidationException::class);
        $this->expectExceptionMessage('Notifications array cannot be empty');

        $this->client->sendMultipleNotifications([]);
    }

    public function testSendMultipleNotificationsWithTooMany(): void
    {
        $notifications = array_fill(0, 1001, [
            'user_id' => 'user_123',
            'title' => 'Title',
            'body' => 'Body'
        ]);

        $this->expectException(AituValidationException::class);
        $this->expectExceptionMessage('Cannot send more than 1000 notifications at once');

        $this->client->sendMultipleNotifications($notifications);
    }

    public function testCreateNotification(): void
    {
        $successResponse = [
            'success' => true,
            'notification_id' => 'notif_789',
            'created_at' => '2024-01-01T12:00:00Z'
        ];

        $this->mockHandler->append(
            new Response(201, ['Content-Type' => 'application/json'], json_encode($successResponse))
        );

        $result = $this->client->createNotification(
            'Test Notification',
            'This is a test notification',
            'https://example.com/icon.png',
            'https://example.com/click-action',
            ['custom' => 'data']
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('notif_789', $result['notification_id']);
    }

    public function testSendGroupNotification(): void
    {
        $successResponse = [
            'success' => true,
            'notification_id' => 'group_notif_123',
            'group_id' => 'group_456',
            'sent_count' => 25
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($successResponse))
        );

        $result = $this->client->sendGroupNotification(
            'group_456',
            'Group Title',
            'Group message body',
            ['group_data' => 'value']
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('group_notif_123', $result['notification_id']);
        $this->assertEquals(25, $result['sent_count']);
    }

    public function testSendNotificationByTags(): void
    {
        $successResponse = [
            'success' => true,
            'notification_id' => 'tag_notif_123',
            'estimated_recipients' => 150
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($successResponse))
        );

        $result = $this->client->sendNotificationByTags(
            ['premium', 'active'],
            'Tagged Title',
            'Tagged message body'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('tag_notif_123', $result['notification_id']);
        $this->assertEquals(150, $result['estimated_recipients']);
    }

    public function testSendNotificationByTagsWithEmptyTags(): void
    {
        $this->expectException(AituValidationException::class);
        $this->expectExceptionMessage('Tags array cannot be empty');

        $this->client->sendNotificationByTags([], 'Title', 'Body');
    }

    public function testSendBroadcastNotification(): void
    {
        $successResponse = [
            'success' => true,
            'notification_id' => 'broadcast_123',
            'estimated_recipients' => 10000
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($successResponse))
        );

        $result = $this->client->sendBroadcastNotification(
            'Broadcast Title',
            'Broadcast message to all users'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('broadcast_123', $result['notification_id']);
        $this->assertEquals(10000, $result['estimated_recipients']);
    }

    public function testScheduleNotification(): void
    {
        $successResponse = [
            'success' => true,
            'notification_id' => 'scheduled_123',
            'scheduled_at' => '2024-12-31T23:59:59Z',
            'status' => 'scheduled'
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($successResponse))
        );

        $scheduledTime = new \DateTime('2024-12-31 23:59:59');
        
        $result = $this->client->scheduleNotification(
            'user_123',
            'Scheduled Title',
            'Scheduled message',
            $scheduledTime
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('scheduled_123', $result['notification_id']);
        $this->assertEquals('scheduled', $result['status']);
    }

    public function testScheduleNotificationWithPastDate(): void
    {
        $pastTime = new \DateTime('2020-01-01 00:00:00');

        $this->expectException(AituValidationException::class);
        $this->expectExceptionMessage('Scheduled time must be in the future');

        $this->client->scheduleNotification('user_123', 'Title', 'Body', $pastTime);
    }

    public function testGetNotificationStatus(): void
    {
        $statusResponse = [
            'notification_id' => 'notif_123',
            'status' => 'delivered',
            'sent_at' => '2024-01-01T12:00:00Z',
            'delivered_at' => '2024-01-01T12:00:05Z',
            'recipients_count' => 1,
            'delivered_count' => 1,
            'failed_count' => 0
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($statusResponse))
        );

        $result = $this->client->getNotificationStatus('notif_123');

        $this->assertEquals('notif_123', $result['notification_id']);
        $this->assertEquals('delivered', $result['status']);
        $this->assertEquals(1, $result['delivered_count']);
        $this->assertEquals(0, $result['failed_count']);
    }

    public function testGetNotificationStatusWithInvalidId(): void
    {
        $errorResponse = [
            'error' => 'not_found',
            'message' => 'Notification not found'
        ];

        $this->mockHandler->append(
            new Response(404, ['Content-Type' => 'application/json'], json_encode($errorResponse))
        );

        $this->expectException(AituApiException::class);
        $this->expectExceptionCode(404);

        $this->client->getNotificationStatus('invalid_id');
    }

    public function testCancelScheduledNotification(): void
    {
        $cancelResponse = [
            'success' => true,
            'notification_id' => 'scheduled_123',
            'status' => 'cancelled',
            'cancelled_at' => '2024-01-01T12:00:00Z'
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($cancelResponse))
        );

        $result = $this->client->cancelScheduledNotification('scheduled_123');

        $this->assertTrue($result['success']);
        $this->assertEquals('scheduled_123', $result['notification_id']);
        $this->assertEquals('cancelled', $result['status']);
    }

    public function testCancelAlreadySentNotification(): void
    {
        $errorResponse = [
            'error' => 'invalid_status',
            'message' => 'Cannot cancel notification that has already been sent'
        ];

        $this->mockHandler->append(
            new Response(400, ['Content-Type' => 'application/json'], json_encode($errorResponse))
        );

        $this->expectException(AituApiException::class);
        $this->expectExceptionMessage('Cannot cancel notification that has already been sent');

        $this->client->cancelScheduledNotification('sent_notification_123');
    }

    public function testGetNotificationStatistics(): void
    {
        $statsResponse = [
            'notification_id' => 'notif_123',
            'total_sent' => 100,
            'delivered' => 95,
            'failed' => 5,
            'clicked' => 25,
            'delivery_rate' => 95.0,
            'click_rate' => 26.32,
            'created_at' => '2024-01-01T12:00:00Z',
            'sent_at' => '2024-01-01T12:01:00Z'
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($statsResponse))
        );

        $result = $this->client->getNotificationStatistics('notif_123');

        $this->assertEquals('notif_123', $result['notification_id']);
        $this->assertEquals(100, $result['total_sent']);
        $this->assertEquals(95, $result['delivered']);
        $this->assertEquals(5, $result['failed']);
        $this->assertEquals(25, $result['clicked']);
        $this->assertEquals(95.0, $result['delivery_rate']);
        $this->assertEquals(26.32, $result['click_rate']);
    }

    public function testValidateNotificationParametersWithValidData(): void
    {
        // Should not throw any exception
        $this->client->validateNotificationParameters([
            'user_id' => 'user_123',
            'title' => 'Valid Title',
            'body' => 'Valid body message'
        ]);

        $this->assertTrue(true); // If we reach here, validation passed
    }

    public function testValidateNotificationParametersWithMissingUserId(): void
    {
        $this->expectException(AituValidationException::class);
        $this->expectExceptionMessage('User ID is required');

        $this->client->validateNotificationParameters([
            'title' => 'Title',
            'body' => 'Body'
        ]);
    }

    public function testValidateNotificationParametersWithMissingTitle(): void
    {
        $this->expectException(AituValidationException::class);
        $this->expectExceptionMessage('Title is required');

        $this->client->validateNotificationParameters([
            'user_id' => 'user_123',
            'body' => 'Body'
        ]);
    }

    public function testValidateNotificationParametersWithMissingBody(): void
    {
        $this->expectException(AituValidationException::class);
        $this->expectExceptionMessage('Body is required');

        $this->client->validateNotificationParameters([
            'user_id' => 'user_123',
            'title' => 'Title'
        ]);
    }

    public function testValidateNotificationParametersWithTooLongTitle(): void
    {
        $longTitle = str_repeat('A', 101);

        $this->expectException(AituValidationException::class);
        $this->expectExceptionMessage('Title cannot be longer than 100 characters');

        $this->client->validateNotificationParameters([
            'user_id' => 'user_123',
            'title' => $longTitle,
            'body' => 'Body'
        ]);
    }

    public function testValidateNotificationParametersWithTooLongBody(): void
    {
        $longBody = str_repeat('A', 501);

        $this->expectException(AituValidationException::class);
        $this->expectExceptionMessage('Body cannot be longer than 500 characters');

        $this->client->validateNotificationParameters([
            'user_id' => 'user_123',
            'title' => 'Title',
            'body' => $longBody
        ]);
    }

    public function testIsValidUuidWithValidUuid(): void
    {
        $validUuids = [
            '550e8400-e29b-41d4-a716-446655440000',
            '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
            '6ba7b811-9dad-11d1-80b4-00c04fd430c8'
        ];

        foreach ($validUuids as $uuid) {
            $this->assertTrue($this->client->isValidUuid($uuid));
        }
    }

    public function testIsValidUuidWithInvalidUuid(): void
    {
        $invalidUuids = [
            'not-a-uuid',
            '550e8400-e29b-41d4-a716',
            '550e8400-e29b-41d4-a716-446655440000-extra',
            '',
            '550e8400-e29b-41d4-a716-44665544000g'
        ];

        foreach ($invalidUuids as $uuid) {
            $this->assertFalse($this->client->isValidUuid($uuid));
        }
    }

    public function testApiErrorHandling(): void
    {
        $errorResponse = [
            'error' => 'rate_limit_exceeded',
            'message' => 'Too many requests',
            'retry_after' => 60
        ];

        $this->mockHandler->append(
            new Response(429, ['Content-Type' => 'application/json'], json_encode($errorResponse))
        );

        $this->expectException(AituApiException::class);
        $this->expectExceptionCode(429);
        $this->expectExceptionMessage('Too many requests');

        $this->client->sendTargetedNotification('user_123', 'Title', 'Body');
    }

    public function testNetworkError(): void
    {
        $this->mockHandler->append(
            new \GuzzleHttp\Exception\ConnectException(
                'Connection timeout',
                new \GuzzleHttp\Psr7\Request('POST', '/notifications/send')
            )
        );

        $this->expectException(\MadArlan\AituMessenger\Exceptions\AituHttpException::class);
        $this->expectExceptionMessage('Connection timeout');

        $this->client->sendTargetedNotification('user_123', 'Title', 'Body');
    }

    public function testCustomApiEndpoints(): void
    {
        $customClient = new AituAppsClient(
            'test_app_id',
            'test_app_secret',
            null,
            [
                'send_url' => 'https://custom-api.aitu.io/notifications/send',
                'status_url' => 'https://custom-api.aitu.io/notifications/status'
            ]
        );

        // Test that custom endpoints are used (this would require inspecting the actual HTTP requests)
        $this->assertInstanceOf(AituAppsClient::class, $customClient);
    }

    public function testNotificationWithAllOptionalParameters(): void
    {
        $successResponse = [
            'success' => true,
            'notification_id' => 'full_notif_123'
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($successResponse))
        );

        $result = $this->client->sendTargetedNotification(
            'user_123',
            'Full Title',
            'Full body message',
            ['custom_key' => 'custom_value'],
            'https://example.com/icon.png',
            'https://example.com/click-action',
            'high',
            'default',
            3600
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('full_notif_123', $result['notification_id']);
    }

    public function testBatchNotificationValidation(): void
    {
        $notifications = [
            [
                'user_id' => 'user_123',
                'title' => 'Title 1',
                'body' => 'Body 1'
            ],
            [
                'user_id' => '', // Invalid: empty user_id
                'title' => 'Title 2',
                'body' => 'Body 2'
            ]
        ];

        $this->expectException(AituValidationException::class);
        $this->expectExceptionMessage('Invalid notification at index 1: User ID cannot be empty');

        $this->client->sendMultipleNotifications($notifications);
    }

    public function testScheduleNotificationWithTimezone(): void
    {
        $successResponse = [
            'success' => true,
            'notification_id' => 'scheduled_tz_123',
            'scheduled_at' => '2024-12-31T18:59:59Z', // UTC conversion
            'timezone' => 'Asia/Almaty'
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($successResponse))
        );

        $scheduledTime = new \DateTime('2024-12-31 23:59:59', new \DateTimeZone('Asia/Almaty'));
        
        $result = $this->client->scheduleNotification(
            'user_123',
            'Timezone Title',
            'Timezone message',
            $scheduledTime
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('scheduled_tz_123', $result['notification_id']);
    }
}