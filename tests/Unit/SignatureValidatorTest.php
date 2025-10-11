<?php

namespace MadArlan\AituMessenger\Tests\Unit;

use PHPUnit\Framework\TestCase;
use MadArlan\AituMessenger\Utils\SignatureValidator;

class SignatureValidatorTest extends TestCase
{
    private string $testSecret = 'test_secret_key';
    private string $testData = '{"test": "data", "timestamp": 1234567890}';

    public function testGenerateSignature(): void
    {
        $signature = SignatureValidator::generateSignature($this->testData, $this->testSecret);
        
        $this->assertIsString($signature);
        $this->assertNotEmpty($signature);
        $this->assertStringStartsWith('sha256=', $signature);
    }

    public function testVerifySignatureWithValidSignature(): void
    {
        $signature = SignatureValidator::generateSignature($this->testData, $this->testSecret);
        
        $isValid = SignatureValidator::verifySignature($this->testData, $signature, $this->testSecret);
        
        $this->assertTrue($isValid);
    }

    public function testVerifySignatureWithInvalidSignature(): void
    {
        $invalidSignature = 'sha256=invalid_signature';
        
        $isValid = SignatureValidator::verifySignature($this->testData, $invalidSignature, $this->testSecret);
        
        $this->assertFalse($isValid);
    }

    public function testVerifySignatureWithWrongSecret(): void
    {
        $signature = SignatureValidator::generateSignature($this->testData, $this->testSecret);
        $wrongSecret = 'wrong_secret';
        
        $isValid = SignatureValidator::verifySignature($this->testData, $signature, $wrongSecret);
        
        $this->assertFalse($isValid);
    }

    public function testVerifySignatureWithEmptyData(): void
    {
        $signature = SignatureValidator::generateSignature('', $this->testSecret);
        
        $isValid = SignatureValidator::verifySignature('', $signature, $this->testSecret);
        
        $this->assertTrue($isValid);
    }

    public function testVerifySignatureWithEmptySecret(): void
    {
        $signature = SignatureValidator::generateSignature($this->testData, '');
        
        $isValid = SignatureValidator::verifySignature($this->testData, $signature, '');
        
        $this->assertTrue($isValid);
    }

    public function testVerifySignatureWithMalformedSignature(): void
    {
        $malformedSignature = 'not_a_valid_signature_format';
        
        $isValid = SignatureValidator::verifySignature($this->testData, $malformedSignature, $this->testSecret);
        
        $this->assertFalse($isValid);
    }

    public function testVerifySignatureWithoutSha256Prefix(): void
    {
        $signature = SignatureValidator::generateSignature($this->testData, $this->testSecret);
        $signatureWithoutPrefix = substr($signature, 7); // Remove 'sha256=' prefix
        
        $isValid = SignatureValidator::verifySignature($this->testData, $signatureWithoutPrefix, $this->testSecret);
        
        $this->assertFalse($isValid);
    }

    public function testVerifyOAuthSignatureWithValidSignature(): void
    {
        $params = [
            'oauth_consumer_key' => 'test_key',
            'oauth_nonce' => 'test_nonce',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1234567890',
            'oauth_version' => '1.0'
        ];
        
        $httpMethod = 'POST';
        $baseUrl = 'https://api.example.com/oauth/request_token';
        $consumerSecret = 'consumer_secret';
        $tokenSecret = 'token_secret';
        
        $signature = SignatureValidator::generateOAuthSignature(
            $params,
            $httpMethod,
            $baseUrl,
            $consumerSecret,
            $tokenSecret
        );
        
        $isValid = SignatureValidator::verifyOAuthSignature(
            $params,
            $signature,
            $httpMethod,
            $baseUrl,
            $consumerSecret,
            $tokenSecret
        );
        
        $this->assertTrue($isValid);
    }

    public function testVerifyOAuthSignatureWithInvalidSignature(): void
    {
        $params = [
            'oauth_consumer_key' => 'test_key',
            'oauth_nonce' => 'test_nonce',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1234567890',
            'oauth_version' => '1.0'
        ];
        
        $httpMethod = 'POST';
        $baseUrl = 'https://api.example.com/oauth/request_token';
        $consumerSecret = 'consumer_secret';
        $tokenSecret = 'token_secret';
        $invalidSignature = 'invalid_signature';
        
        $isValid = SignatureValidator::verifyOAuthSignature(
            $params,
            $invalidSignature,
            $httpMethod,
            $baseUrl,
            $consumerSecret,
            $tokenSecret
        );
        
        $this->assertFalse($isValid);
    }

    public function testVerifyWebhookSignatureWithValidSignature(): void
    {
        $payload = '{"event": "test", "data": {"key": "value"}}';
        $secret = 'webhook_secret';
        
        $signature = SignatureValidator::generateSignature($payload, $secret);
        
        $isValid = SignatureValidator::verifyWebhookSignature($payload, $signature, $secret);
        
        $this->assertTrue($isValid);
    }

    public function testVerifyWebhookSignatureWithInvalidSignature(): void
    {
        $payload = '{"event": "test", "data": {"key": "value"}}';
        $secret = 'webhook_secret';
        $invalidSignature = 'sha256=invalid_signature';
        
        $isValid = SignatureValidator::verifyWebhookSignature($payload, $invalidSignature, $secret);
        
        $this->assertFalse($isValid);
    }

    public function testSafeCompareWithEqualStrings(): void
    {
        $string1 = 'test_string';
        $string2 = 'test_string';
        
        $result = SignatureValidator::safeCompare($string1, $string2);
        
        $this->assertTrue($result);
    }

    public function testSafeCompareWithDifferentStrings(): void
    {
        $string1 = 'test_string_1';
        $string2 = 'test_string_2';
        
        $result = SignatureValidator::safeCompare($string1, $string2);
        
        $this->assertFalse($result);
    }

    public function testSafeCompareWithDifferentLengths(): void
    {
        $string1 = 'short';
        $string2 = 'much_longer_string';
        
        $result = SignatureValidator::safeCompare($string1, $string2);
        
        $this->assertFalse($result);
    }

    public function testSafeCompareWithEmptyStrings(): void
    {
        $result = SignatureValidator::safeCompare('', '');
        
        $this->assertTrue($result);
    }

    public function testGenerateOAuthSignatureConsistency(): void
    {
        $params = [
            'oauth_consumer_key' => 'test_key',
            'oauth_nonce' => 'test_nonce',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1234567890',
            'oauth_version' => '1.0'
        ];
        
        $httpMethod = 'GET';
        $baseUrl = 'https://api.example.com/test';
        $consumerSecret = 'consumer_secret';
        $tokenSecret = 'token_secret';
        
        $signature1 = SignatureValidator::generateOAuthSignature(
            $params,
            $httpMethod,
            $baseUrl,
            $consumerSecret,
            $tokenSecret
        );
        
        $signature2 = SignatureValidator::generateOAuthSignature(
            $params,
            $httpMethod,
            $baseUrl,
            $consumerSecret,
            $tokenSecret
        );
        
        $this->assertEquals($signature1, $signature2);
    }

    public function testGenerateSignatureConsistency(): void
    {
        $data = '{"consistent": "test", "data": 123}';
        $secret = 'consistent_secret';
        
        $signature1 = SignatureValidator::generateSignature($data, $secret);
        $signature2 = SignatureValidator::generateSignature($data, $secret);
        
        $this->assertEquals($signature1, $signature2);
    }

    public function testVerifySignatureWithSpecialCharacters(): void
    {
        $dataWithSpecialChars = '{"message": "Тест с русскими символами и эмодзи 🚀", "special": "!@#$%^&*()"}';
        $secret = 'secret_with_спец_символы';
        
        $signature = SignatureValidator::generateSignature($dataWithSpecialChars, $secret);
        $isValid = SignatureValidator::verifySignature($dataWithSpecialChars, $signature, $secret);
        
        $this->assertTrue($isValid);
    }

    public function testVerifyOAuthSignatureWithAdditionalParams(): void
    {
        $params = [
            'oauth_consumer_key' => 'test_key',
            'oauth_nonce' => 'test_nonce',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1234567890',
            'oauth_version' => '1.0',
            'custom_param' => 'custom_value',
            'another_param' => 'another_value'
        ];
        
        $httpMethod = 'PUT';
        $baseUrl = 'https://api.example.com/oauth/access_token';
        $consumerSecret = 'consumer_secret';
        $tokenSecret = 'token_secret';
        
        $signature = SignatureValidator::generateOAuthSignature(
            $params,
            $httpMethod,
            $baseUrl,
            $consumerSecret,
            $tokenSecret
        );
        
        $isValid = SignatureValidator::verifyOAuthSignature(
            $params,
            $signature,
            $httpMethod,
            $baseUrl,
            $consumerSecret,
            $tokenSecret
        );
        
        $this->assertTrue($isValid);
    }

    public function testVerifyOAuthSignatureWithoutTokenSecret(): void
    {
        $params = [
            'oauth_consumer_key' => 'test_key',
            'oauth_nonce' => 'test_nonce',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1234567890',
            'oauth_version' => '1.0'
        ];
        
        $httpMethod = 'POST';
        $baseUrl = 'https://api.example.com/oauth/request_token';
        $consumerSecret = 'consumer_secret';
        
        $signature = SignatureValidator::generateOAuthSignature(
            $params,
            $httpMethod,
            $baseUrl,
            $consumerSecret
        );
        
        $isValid = SignatureValidator::verifyOAuthSignature(
            $params,
            $signature,
            $httpMethod,
            $baseUrl,
            $consumerSecret
        );
        
        $this->assertTrue($isValid);
    }
}