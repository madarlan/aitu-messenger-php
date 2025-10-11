<?php

namespace MadArlan\AituMessenger\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use MadArlan\AituMessenger\Http\HttpClient;
use MadArlan\AituMessenger\Http\ApiResponse;
use MadArlan\AituMessenger\Exceptions\AituHttpException;

class HttpClientTest extends TestCase
{
    private HttpClient $httpClient;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        
        $this->httpClient = new HttpClient([
            'base_uri' => 'https://api.example.com',
            'timeout' => 30
        ], $guzzleClient);
    }

    public function testGetRequest(): void
    {
        $responseData = ['status' => 'success', 'data' => ['id' => 1, 'name' => 'Test']];
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $response = $this->httpClient->get('/test');

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals($responseData, $response->getData());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostRequest(): void
    {
        $requestData = ['name' => 'Test User', 'email' => 'test@example.com'];
        $responseData = ['status' => 'created', 'id' => 123];
        
        $this->mockHandler->append(
            new Response(201, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $response = $this->httpClient->post('/users', $requestData);

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals($responseData, $response->getData());
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testPutRequest(): void
    {
        $requestData = ['name' => 'Updated User'];
        $responseData = ['status' => 'updated', 'id' => 123];
        
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $response = $this->httpClient->put('/users/123', $requestData);

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals($responseData, $response->getData());
    }

    public function testPatchRequest(): void
    {
        $requestData = ['email' => 'newemail@example.com'];
        $responseData = ['status' => 'patched', 'id' => 123];
        
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $response = $this->httpClient->patch('/users/123', $requestData);

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals($responseData, $response->getData());
    }

    public function testDeleteRequest(): void
    {
        $responseData = ['status' => 'deleted'];
        
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $response = $this->httpClient->delete('/users/123');

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals($responseData, $response->getData());
    }

    public function testFormRequest(): void
    {
        $formData = ['username' => 'testuser', 'password' => 'secret'];
        $responseData = ['status' => 'authenticated', 'token' => 'abc123'];
        
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $response = $this->httpClient->form('/auth/login', $formData);

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals($responseData, $response->getData());
    }

    public function testMultipartRequest(): void
    {
        $multipartData = [
            ['name' => 'field1', 'contents' => 'value1'],
            ['name' => 'file', 'contents' => 'file content', 'filename' => 'test.txt']
        ];
        $responseData = ['status' => 'uploaded', 'file_id' => 456];
        
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $response = $this->httpClient->multipart('/upload', $multipartData);

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals($responseData, $response->getData());
    }

    public function testRequestWithCustomHeaders(): void
    {
        $responseData = ['status' => 'success'];
        
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $response = $this->httpClient->get('/test', [], [
            'Authorization' => 'Bearer token123',
            'X-Custom-Header' => 'custom-value'
        ]);

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }

    public function testRequestWithQueryParameters(): void
    {
        $responseData = ['results' => [], 'total' => 0];
        
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $response = $this->httpClient->get('/search', [
            'q' => 'test query',
            'limit' => 10,
            'offset' => 0
        ]);

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals($responseData, $response->getData());
    }

    public function testErrorResponse(): void
    {
        $errorData = ['error' => 'Not Found', 'message' => 'Resource not found'];
        
        $this->mockHandler->append(
            new Response(404, ['Content-Type' => 'application/json'], json_encode($errorData))
        );

        $response = $this->httpClient->get('/nonexistent');

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals($errorData, $response->getData());
        
        $error = $response->getError();
        $this->assertEquals('Not Found', $error['error']);
        $this->assertEquals('Resource not found', $error['message']);
    }

    public function testNetworkException(): void
    {
        $this->mockHandler->append(
            new RequestException(
                'Connection timeout',
                new Request('GET', '/test')
            )
        );

        $this->expectException(AituHttpException::class);
        $this->expectExceptionMessage('Connection timeout');

        $this->httpClient->get('/test');
    }

    public function testInvalidJsonResponse(): void
    {
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], 'invalid json content')
        );

        $response = $this->httpClient->get('/test');

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getData());
    }

    public function testNonJsonResponse(): void
    {
        $htmlContent = '<html><body>Not JSON</body></html>';
        
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'text/html'], $htmlContent)
        );

        $response = $this->httpClient->get('/test');

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals($htmlContent, $response->getRawContent());
    }

    public function testDefaultOptions(): void
    {
        $httpClient = new HttpClient();
        
        // Проверяем, что клиент создается с дефолтными опциями
        $this->assertInstanceOf(HttpClient::class, $httpClient);
    }

    public function testCustomTimeout(): void
    {
        $responseData = ['status' => 'success'];
        
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $response = $this->httpClient->get('/test', [], [], ['timeout' => 60]);

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }

    public function testMergeOptions(): void
    {
        $responseData = ['status' => 'success'];
        
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        // Тестируем, что опции корректно объединяются
        $response = $this->httpClient->get('/test', [], [], [
            'timeout' => 45,
            'connect_timeout' => 10
        ]);

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }

    public function testPostWithEmptyData(): void
    {
        $responseData = ['status' => 'created'];
        
        $this->mockHandler->append(
            new Response(201, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $response = $this->httpClient->post('/test', []);

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testFormWithSpecialCharacters(): void
    {
        $formData = [
            'message' => 'Тест с русскими символами и эмодзи 🚀',
            'special_chars' => '!@#$%^&*()'
        ];
        $responseData = ['status' => 'processed'];
        
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $response = $this->httpClient->form('/process', $formData);

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }

    public function testMultipartWithMultipleFiles(): void
    {
        $multipartData = [
            ['name' => 'title', 'contents' => 'Test Upload'],
            ['name' => 'file1', 'contents' => 'content1', 'filename' => 'file1.txt'],
            ['name' => 'file2', 'contents' => 'content2', 'filename' => 'file2.txt']
        ];
        $responseData = ['status' => 'uploaded', 'files' => 2];
        
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))
        );

        $response = $this->httpClient->multipart('/upload-multiple', $multipartData);

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(2, $response->getData('files'));
    }

    public function testServerErrorResponse(): void
    {
        $errorData = ['error' => 'Internal Server Error', 'code' => 500];
        
        $this->mockHandler->append(
            new Response(500, ['Content-Type' => 'application/json'], json_encode($errorData))
        );

        $response = $this->httpClient->get('/test');

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals(500, $response->getStatusCode());
        
        $error = $response->getError();
        $this->assertEquals('Internal Server Error', $error['error']);
        $this->assertEquals(500, $error['code']);
    }

    public function testEmptyResponse(): void
    {
        $this->mockHandler->append(
            new Response(204, [], '')
        );

        $response = $this->httpClient->delete('/test');

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull($response->getData());
    }
}