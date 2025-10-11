<?php

namespace MadArlan\AituMessenger\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use MadArlan\AituMessenger\Http\ApiResponse;
use MadArlan\AituMessenger\Exceptions\AituApiException;

class ApiResponseTest extends TestCase
{
    public function testSuccessfulJsonResponse(): void
    {
        $responseData = ['status' => 'success', 'data' => ['id' => 1, 'name' => 'Test']];
        $guzzleResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($responseData)
        );

        $apiResponse = new ApiResponse($guzzleResponse);

        $this->assertTrue($apiResponse->isSuccessful());
        $this->assertEquals(200, $apiResponse->getStatusCode());
        $this->assertEquals($responseData, $apiResponse->getData());
        $this->assertEquals('success', $apiResponse->getData('status'));
        $this->assertEquals(['id' => 1, 'name' => 'Test'], $apiResponse->getData('data'));
        $this->assertNull($apiResponse->getData('nonexistent'));
        $this->assertEquals('default', $apiResponse->getData('nonexistent', 'default'));
    }

    public function testErrorJsonResponse(): void
    {
        $errorData = ['error' => 'Not Found', 'message' => 'Resource not found', 'code' => 404];
        $guzzleResponse = new Response(
            404,
            ['Content-Type' => 'application/json'],
            json_encode($errorData)
        );

        $apiResponse = new ApiResponse($guzzleResponse);

        $this->assertFalse($apiResponse->isSuccessful());
        $this->assertEquals(404, $apiResponse->getStatusCode());
        $this->assertEquals($errorData, $apiResponse->getData());
        
        $error = $apiResponse->getError();
        $this->assertEquals('Not Found', $error['error']);
        $this->assertEquals('Resource not found', $error['message']);
        $this->assertEquals(404, $error['code']);
    }

    public function testNonJsonResponse(): void
    {
        $htmlContent = '<html><body>Not JSON</body></html>';
        $guzzleResponse = new Response(
            200,
            ['Content-Type' => 'text/html'],
            $htmlContent
        );

        $apiResponse = new ApiResponse($guzzleResponse);

        $this->assertTrue($apiResponse->isSuccessful());
        $this->assertEquals(200, $apiResponse->getStatusCode());
        $this->assertNull($apiResponse->getData());
        $this->assertEquals($htmlContent, $apiResponse->getRawContent());
    }

    public function testInvalidJsonResponse(): void
    {
        $invalidJson = '{"invalid": json content}';
        $guzzleResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            $invalidJson
        );

        $apiResponse = new ApiResponse($guzzleResponse);

        $this->assertTrue($apiResponse->isSuccessful());
        $this->assertEquals(200, $apiResponse->getStatusCode());
        $this->assertNull($apiResponse->getData());
        $this->assertEquals($invalidJson, $apiResponse->getRawContent());
    }

    public function testEmptyResponse(): void
    {
        $guzzleResponse = new Response(204, [], '');

        $apiResponse = new ApiResponse($guzzleResponse);

        $this->assertTrue($apiResponse->isSuccessful());
        $this->assertEquals(204, $apiResponse->getStatusCode());
        $this->assertNull($apiResponse->getData());
        $this->assertEquals('', $apiResponse->getRawContent());
    }

    public function testGetHeaders(): void
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Rate-Limit' => '1000',
            'X-Rate-Remaining' => '999'
        ];
        
        $guzzleResponse = new Response(200, $headers, '{"status": "success"}');
        $apiResponse = new ApiResponse($guzzleResponse);

        $responseHeaders = $apiResponse->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $responseHeaders);
        $this->assertArrayHasKey('X-Rate-Limit', $responseHeaders);
        $this->assertArrayHasKey('X-Rate-Remaining', $responseHeaders);
        
        $this->assertEquals(['application/json'], $responseHeaders['Content-Type']);
        $this->assertEquals(['1000'], $responseHeaders['X-Rate-Limit']);
        $this->assertEquals(['999'], $responseHeaders['X-Rate-Remaining']);
    }

    public function testGetSpecificHeader(): void
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Request-ID' => 'abc123'
        ];
        
        $guzzleResponse = new Response(200, $headers, '{"status": "success"}');
        $apiResponse = new ApiResponse($guzzleResponse);

        $this->assertEquals(['application/json'], $apiResponse->getHeader('Content-Type'));
        $this->assertEquals(['abc123'], $apiResponse->getHeader('X-Request-ID'));
        $this->assertEquals([], $apiResponse->getHeader('Nonexistent-Header'));
    }

    public function testThrowOnErrorWithSuccessfulResponse(): void
    {
        $guzzleResponse = new Response(200, [], '{"status": "success"}');
        $apiResponse = new ApiResponse($guzzleResponse);

        // Не должно выбрасывать исключение
        $apiResponse->throwOnError();
        
        $this->assertTrue(true); // Если дошли до этой строки, значит исключение не было выброшено
    }

    public function testThrowOnErrorWithErrorResponse(): void
    {
        $errorData = ['error' => 'Bad Request', 'message' => 'Invalid parameters'];
        $guzzleResponse = new Response(
            400,
            ['Content-Type' => 'application/json'],
            json_encode($errorData)
        );
        $apiResponse = new ApiResponse($guzzleResponse);

        $this->expectException(AituApiException::class);
        $this->expectExceptionMessage('Bad Request');
        $this->expectExceptionCode(400);

        $apiResponse->throwOnError();
    }

    public function testThrowOnErrorWithCustomErrorMessage(): void
    {
        $guzzleResponse = new Response(500, [], 'Internal Server Error');
        $apiResponse = new ApiResponse($guzzleResponse);

        $this->expectException(AituApiException::class);
        $this->expectExceptionMessage('HTTP 500: Internal Server Error');
        $this->expectExceptionCode(500);

        $apiResponse->throwOnError();
    }

    public function testGetErrorWithSuccessfulResponse(): void
    {
        $guzzleResponse = new Response(200, [], '{"status": "success"}');
        $apiResponse = new ApiResponse($guzzleResponse);

        $error = $apiResponse->getError();
        
        $this->assertNull($error);
    }

    public function testGetErrorWithJsonErrorResponse(): void
    {
        $errorData = [
            'error' => 'Validation Error',
            'message' => 'Field is required',
            'code' => 'VALIDATION_FAILED',
            'details' => ['field' => 'email']
        ];
        
        $guzzleResponse = new Response(
            422,
            ['Content-Type' => 'application/json'],
            json_encode($errorData)
        );
        $apiResponse = new ApiResponse($guzzleResponse);

        $error = $apiResponse->getError();
        
        $this->assertEquals('Validation Error', $error['error']);
        $this->assertEquals('Field is required', $error['message']);
        $this->assertEquals('VALIDATION_FAILED', $error['code']);
        $this->assertEquals(['field' => 'email'], $error['details']);
    }

    public function testGetErrorWithNonJsonErrorResponse(): void
    {
        $guzzleResponse = new Response(500, [], 'Internal Server Error');
        $apiResponse = new ApiResponse($guzzleResponse);

        $error = $apiResponse->getError();
        
        $this->assertEquals('HTTP 500', $error['error']);
        $this->assertEquals('Internal Server Error', $error['message']);
        $this->assertEquals(500, $error['code']);
    }

    public function testIsSuccessfulWithVariousStatusCodes(): void
    {
        // Успешные коды
        $successCodes = [200, 201, 202, 204, 206];
        foreach ($successCodes as $code) {
            $guzzleResponse = new Response($code, [], '{}');
            $apiResponse = new ApiResponse($guzzleResponse);
            $this->assertTrue($apiResponse->isSuccessful(), "Status code {$code} should be successful");
        }

        // Неуспешные коды
        $errorCodes = [400, 401, 403, 404, 422, 500, 502, 503];
        foreach ($errorCodes as $code) {
            $guzzleResponse = new Response($code, [], '{}');
            $apiResponse = new ApiResponse($guzzleResponse);
            $this->assertFalse($apiResponse->isSuccessful(), "Status code {$code} should not be successful");
        }
    }

    public function testGetDataWithNestedKeys(): void
    {
        $responseData = [
            'user' => [
                'profile' => [
                    'name' => 'John Doe',
                    'settings' => [
                        'theme' => 'dark',
                        'notifications' => true
                    ]
                ]
            ]
        ];
        
        $guzzleResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($responseData)
        );
        $apiResponse = new ApiResponse($guzzleResponse);

        // Тестируем доступ к вложенным данным
        $this->assertEquals($responseData['user'], $apiResponse->getData('user'));
        $this->assertEquals('John Doe', $apiResponse->getData('user.profile.name'));
        $this->assertEquals('dark', $apiResponse->getData('user.profile.settings.theme'));
        $this->assertTrue($apiResponse->getData('user.profile.settings.notifications'));
        $this->assertNull($apiResponse->getData('user.profile.nonexistent'));
        $this->assertEquals('default', $apiResponse->getData('user.profile.nonexistent', 'default'));
    }

    public function testGetDataWithArrayAccess(): void
    {
        $responseData = [
            'items' => [
                ['id' => 1, 'name' => 'Item 1'],
                ['id' => 2, 'name' => 'Item 2']
            ]
        ];
        
        $guzzleResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($responseData)
        );
        $apiResponse = new ApiResponse($guzzleResponse);

        $this->assertEquals($responseData['items'], $apiResponse->getData('items'));
        $this->assertEquals(['id' => 1, 'name' => 'Item 1'], $apiResponse->getData('items.0'));
        $this->assertEquals('Item 2', $apiResponse->getData('items.1.name'));
        $this->assertNull($apiResponse->getData('items.2'));
    }

    public function testResponseWithSpecialCharacters(): void
    {
        $responseData = [
            'message' => 'Тест с русскими символами и эмодзи 🚀',
            'special' => '!@#$%^&*()',
            'unicode' => '你好世界'
        ];
        
        $guzzleResponse = new Response(
            200,
            ['Content-Type' => 'application/json; charset=utf-8'],
            json_encode($responseData, JSON_UNESCAPED_UNICODE)
        );
        $apiResponse = new ApiResponse($guzzleResponse);

        $this->assertTrue($apiResponse->isSuccessful());
        $this->assertEquals($responseData, $apiResponse->getData());
        $this->assertEquals('Тест с русскими символами и эмодзи 🚀', $apiResponse->getData('message'));
        $this->assertEquals('你好世界', $apiResponse->getData('unicode'));
    }
}