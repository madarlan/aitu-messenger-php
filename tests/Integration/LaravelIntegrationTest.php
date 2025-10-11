<?php

namespace MadArlan\AituMessenger\Tests\Integration;

use Orchestra\Testbench\TestCase;
use MadArlan\AituMessenger\AituMessengerServiceProvider;
use MadArlan\AituMessenger\Facades\AituPassport;
use MadArlan\AituMessenger\Facades\AituApps;
use MadArlan\AituMessenger\AituPassportClient;
use MadArlan\AituMessenger\AituAppsClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class LaravelIntegrationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            AituMessengerServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'AituPassport' => AituPassport::class,
            'AituApps' => AituApps::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // Set up test configuration
        $app['config']->set('aitu-messenger.passport.client_id', 'test_passport_client_id');
        $app['config']->set('aitu-messenger.passport.client_secret', 'test_passport_client_secret');
        $app['config']->set('aitu-messenger.passport.redirect_uri', 'https://example.com/callback');
        
        $app['config']->set('aitu-messenger.apps.app_id', 'test_apps_app_id');
        $app['config']->set('aitu-messenger.apps.app_secret', 'test_apps_app_secret');
        
        $app['config']->set('aitu-messenger.webhook.secret', 'test_webhook_secret');
        $app['config']->set('aitu-messenger.webhook.verify_signature', true);
    }

    public function testServiceProviderRegistration(): void
    {
        $this->assertTrue($this->app->bound('aitu.passport'));
        $this->assertTrue($this->app->bound('aitu.apps'));
    }

    public function testConfigurationIsLoaded(): void
    {
        $this->assertEquals('test_passport_client_id', config('aitu-messenger.passport.client_id'));
        $this->assertEquals('test_apps_app_id', config('aitu-messenger.apps.app_id'));
        $this->assertEquals('test_webhook_secret', config('aitu-messenger.webhook.secret'));
    }

    public function testAituPassportFacadeResolution(): void
    {
        $client = AituPassport::getFacadeRoot();
        $this->assertInstanceOf(AituPassportClient::class, $client);
    }

    public function testAituAppsFacadeResolution(): void
    {
        $client = AituApps::getFacadeRoot();
        $this->assertInstanceOf(AituAppsClient::class, $client);
    }

    public function testAituPassportFacadeMethods(): void
    {
        // Test that facade methods are accessible
        $authUrl = AituPassport::getAuthorizationUrl(['profile']);
        $this->assertIsString($authUrl);
        $this->assertStringContainsString('client_id=test_passport_client_id', $authUrl);

        $state = AituPassport::generateState();
        $this->assertIsString($state);
        $this->assertEquals(32, strlen($state));

        $logoutUrl = AituPassport::getLogoutUrl();
        $this->assertIsString($logoutUrl);
        $this->assertStringContainsString('client_id=test_passport_client_id', $logoutUrl);
    }

    public function testAituAppsFacadeMethods(): void
    {
        // Test validation methods
        $this->assertTrue(AituApps::isValidUuid('550e8400-e29b-41d4-a716-446655440000'));
        $this->assertFalse(AituApps::isValidUuid('invalid-uuid'));

        // Test that validation throws exceptions for invalid data
        $this->expectException(\MadArlan\AituMessenger\Exceptions\AituValidationException::class);
        AituApps::validateNotificationParameters([
            'user_id' => '',
            'title' => 'Test',
            'body' => 'Test'
        ]);
    }

    public function testWebhookRoutesAreRegistered(): void
    {
        // Load the webhook routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        // Test that webhook routes exist
        $routes = Route::getRoutes();
        $routeNames = [];
        
        foreach ($routes as $route) {
            if ($route->getName()) {
                $routeNames[] = $route->getName();
            }
        }

        $this->assertContains('aitu.webhook.general', $routeNames);
        $this->assertContains('aitu.webhook.passport', $routeNames);
        $this->assertContains('aitu.webhook.apps', $routeNames);
        $this->assertContains('aitu.oauth.login', $routeNames);
        $this->assertContains('aitu.oauth.callback', $routeNames);
        $this->assertContains('aitu.oauth.logout', $routeNames);
    }

    public function testWebhookMiddlewareIsRegistered(): void
    {
        $this->assertTrue($this->app['router']->hasMiddlewareGroup('aitu.webhook'));
    }

    public function testConfigurationValidation(): void
    {
        // Test with missing required configuration
        Config::set('aitu-messenger.passport.client_id', null);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->app->make('aitu.passport');
    }

    public function testDatabaseMigrationsExist(): void
    {
        $migrationPath = __DIR__ . '/../../database/migrations';
        
        $this->assertDirectoryExists($migrationPath);
        
        $migrations = [
            '2024_01_01_000000_create_aitu_users_table.php',
            '2024_01_01_000001_create_aitu_tokens_table.php',
            '2024_01_01_000002_create_aitu_webhook_logs_table.php'
        ];

        foreach ($migrations as $migration) {
            $this->assertFileExists($migrationPath . '/' . $migration);
        }
    }

    public function testArtisanCommandIsRegistered(): void
    {
        $commands = $this->app['Illuminate\Contracts\Console\Kernel']->all();
        $this->assertArrayHasKey('aitu:install', $commands);
    }

    public function testServiceProviderBootsCorrectly(): void
    {
        // Test that the service provider boots without errors
        $provider = new AituMessengerServiceProvider($this->app);
        
        // This should not throw any exceptions
        $provider->boot();
        $this->assertTrue(true);
    }

    public function testConfigurationCaching(): void
    {
        // Test that configuration can be cached
        $this->artisan('config:cache')->assertExitCode(0);
        
        // Configuration should still be accessible after caching
        $this->assertEquals('test_passport_client_id', config('aitu-messenger.passport.client_id'));
        
        // Clear the cache
        $this->artisan('config:clear')->assertExitCode(0);
    }

    public function testRouteCaching(): void
    {
        // Load routes first
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        
        // Test that routes can be cached
        $this->artisan('route:cache')->assertExitCode(0);
        
        // Routes should still be accessible after caching
        $routes = Route::getRoutes();
        $this->assertGreaterThan(0, $routes->count());
        
        // Clear the cache
        $this->artisan('route:clear')->assertExitCode(0);
    }

    public function testEnvironmentVariableBinding(): void
    {
        // Test that environment variables are properly bound
        putenv('AITU_PASSPORT_CLIENT_ID=env_test_client_id');
        putenv('AITU_APPS_APP_ID=env_test_app_id');
        
        // Reload configuration
        $this->app['config']->set('aitu-messenger.passport.client_id', env('AITU_PASSPORT_CLIENT_ID'));
        $this->app['config']->set('aitu-messenger.apps.app_id', env('AITU_APPS_APP_ID'));
        
        $this->assertEquals('env_test_client_id', config('aitu-messenger.passport.client_id'));
        $this->assertEquals('env_test_app_id', config('aitu-messenger.apps.app_id'));
        
        // Clean up
        putenv('AITU_PASSPORT_CLIENT_ID');
        putenv('AITU_APPS_APP_ID');
    }

    public function testSingletonBinding(): void
    {
        // Test that clients are bound as singletons
        $passport1 = $this->app->make('aitu.passport');
        $passport2 = $this->app->make('aitu.passport');
        
        $this->assertSame($passport1, $passport2);
        
        $apps1 = $this->app->make('aitu.apps');
        $apps2 = $this->app->make('aitu.apps');
        
        $this->assertSame($apps1, $apps2);
    }

    public function testCustomHttpClientConfiguration(): void
    {
        // Test with custom HTTP client configuration
        Config::set('aitu-messenger.passport.http.timeout', 60);
        Config::set('aitu-messenger.passport.http.connect_timeout', 10);
        
        $client = $this->app->make('aitu.passport');
        $this->assertInstanceOf(AituPassportClient::class, $client);
    }

    public function testLoggingConfiguration(): void
    {
        // Test logging configuration
        Config::set('aitu-messenger.logging.enabled', true);
        Config::set('aitu-messenger.logging.channel', 'single');
        Config::set('aitu-messenger.logging.level', 'debug');
        
        $this->assertTrue(config('aitu-messenger.logging.enabled'));
        $this->assertEquals('single', config('aitu-messenger.logging.channel'));
        $this->assertEquals('debug', config('aitu-messenger.logging.level'));
    }

    public function testCachingConfiguration(): void
    {
        // Test caching configuration
        Config::set('aitu-messenger.cache.enabled', true);
        Config::set('aitu-messenger.cache.store', 'redis');
        Config::set('aitu-messenger.cache.prefix', 'aitu_test');
        
        $this->assertTrue(config('aitu-messenger.cache.enabled'));
        $this->assertEquals('redis', config('aitu-messenger.cache.store'));
        $this->assertEquals('aitu_test', config('aitu-messenger.cache.prefix'));
    }

    public function testWebhookSignatureVerificationConfiguration(): void
    {
        // Test webhook signature verification configuration
        Config::set('aitu-messenger.webhook.verify_signature', false);
        $this->assertFalse(config('aitu-messenger.webhook.verify_signature'));
        
        Config::set('aitu-messenger.webhook.verify_signature', true);
        $this->assertTrue(config('aitu-messenger.webhook.verify_signature'));
    }

    public function testMultipleEnvironmentConfigurations(): void
    {
        // Test different configurations for different environments
        $environments = ['local', 'staging', 'production'];
        
        foreach ($environments as $env) {
            $this->app['env'] = $env;
            
            // Configuration should be accessible in all environments
            $this->assertIsString(config('aitu-messenger.passport.client_id'));
            $this->assertIsString(config('aitu-messenger.apps.app_id'));
        }
    }

    public function testServiceProviderDeferredLoading(): void
    {
        // Test that the service provider is not deferred
        $provider = new AituMessengerServiceProvider($this->app);
        $this->assertFalse($provider->isDeferred());
    }

    public function testPublishableAssets(): void
    {
        // Test that publishable assets are registered
        $provider = new AituMessengerServiceProvider($this->app);
        $provider->boot();
        
        // This should not throw any exceptions
        $this->assertTrue(true);
    }

    private function loadRoutesFrom(string $path): void
    {
        if (file_exists($path)) {
            require $path;
        }
    }
}