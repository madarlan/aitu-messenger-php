<?php

namespace MadArlan\AituMessenger\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AituInstallCommand extends Command
{
    /**
     * Название и подпись консольной команды
     *
     * @var string
     */
    protected $signature = 'aitu:install 
                            {--force : Перезаписать существующие файлы}
                            {--passport : Установить только Aitu Passport}
                            {--apps : Установить только Aitu Apps}';

    /**
     * Описание консольной команды
     *
     * @var string
     */
    protected $description = 'Установить и настроить Aitu Messenger SDK';

    /**
     * Выполнить консольную команду
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('🚀 Установка Aitu Messenger SDK...');

        // Публикуем конфигурацию
        $this->publishConfig();

        // Публикуем маршруты
        $this->publishRoutes();

        // Публикуем миграции (если есть)
        $this->publishMigrations();

        // Создаем .env переменные
        $this->createEnvVariables();

        // Показываем следующие шаги
        $this->showNextSteps();

        $this->info('✅ Установка завершена успешно!');

        return Command::SUCCESS;
    }

    /**
     * Публикация конфигурационного файла
     */
    private function publishConfig(): void
    {
        $this->info('📝 Публикация конфигурационного файла...');

        $this->call('vendor:publish', [
            '--provider' => 'MadArlan\AituMessenger\AituMessengerServiceProvider',
            '--tag' => 'aitu-config',
            '--force' => $this->option('force')
        ]);
    }

    /**
     * Публикация маршрутов
     */
    private function publishRoutes(): void
    {
        $this->info('🛣️  Публикация маршрутов...');

        $this->call('vendor:publish', [
            '--provider' => 'MadArlan\AituMessenger\AituMessengerServiceProvider',
            '--tag' => 'aitu-routes',
            '--force' => $this->option('force')
        ]);
    }

    /**
     * Публикация миграций
     */
    private function publishMigrations(): void
    {
        if ($this->option('passport') || (!$this->option('apps') && !$this->option('passport'))) {
            $this->info('🗄️  Публикация миграций...');

            $this->call('vendor:publish', [
                '--provider' => 'MadArlan\AituMessenger\AituMessengerServiceProvider',
                '--tag' => 'aitu-migrations',
                '--force' => $this->option('force')
            ]);
        }
    }

    /**
     * Создание переменных окружения
     */
    private function createEnvVariables(): void
    {
        $this->info('🔧 Настройка переменных окружения...');

        $envPath = base_path('.env');
        $envContent = File::exists($envPath) ? File::get($envPath) : '';

        $envVariables = $this->getEnvVariables();

        $newVariables = [];
        foreach ($envVariables as $key => $value) {
            if (!str_contains($envContent, $key)) {
                $newVariables[] = "{$key}={$value}";
            }
        }

        if (!empty($newVariables)) {
            $envAddition = "\n# Aitu Messenger SDK\n" . implode("\n", $newVariables) . "\n";
            File::append($envPath, $envAddition);
            
            $this->info('✅ Переменные окружения добавлены в .env файл');
        } else {
            $this->info('ℹ️  Переменные окружения уже существуют');
        }
    }

    /**
     * Получить переменные окружения для добавления
     *
     * @return array
     */
    private function getEnvVariables(): array
    {
        $variables = [];

        if ($this->option('passport') || (!$this->option('apps') && !$this->option('passport'))) {
            $variables = array_merge($variables, [
                'AITU_PASSPORT_CLIENT_ID' => 'your_passport_client_id',
                'AITU_PASSPORT_CLIENT_SECRET' => 'your_passport_client_secret',
                'AITU_PASSPORT_REDIRECT_URI' => 'https://your-domain.com/aitu/passport/callback',
                'AITU_PASSPORT_SIGNATURE_SECRET' => 'your_signature_secret',
            ]);
        }

        if ($this->option('apps') || (!$this->option('apps') && !$this->option('passport'))) {
            $variables = array_merge($variables, [
                'AITU_APPS_APP_ID' => 'your_app_id',
                'AITU_APPS_APP_SECRET' => 'your_app_secret',
            ]);
        }

        $variables['AITU_WEBHOOK_SECRET'] = 'your_webhook_secret';

        return $variables;
    }

    /**
     * Показать следующие шаги
     */
    private function showNextSteps(): void
    {
        $this->info('');
        $this->info('🎯 Следующие шаги:');
        $this->info('');

        if ($this->option('passport') || (!$this->option('apps') && !$this->option('passport'))) {
            $this->info('1. Настройте Aitu Passport:');
            $this->info('   - Зарегистрируйте приложение в Aitu Passport');
            $this->info('   - Обновите переменные AITU_PASSPORT_* в .env файле');
            $this->info('   - Запустите миграции: php artisan migrate');
            $this->info('');
        }

        if ($this->option('apps') || (!$this->option('apps') && !$this->option('passport'))) {
            $this->info('2. Настройте Aitu Apps:');
            $this->info('   - Создайте приложение в Aitu Apps');
            $this->info('   - Обновите переменные AITU_APPS_* в .env файле');
            $this->info('');
        }

        $this->info('3. Настройте webhook\'ы:');
        $this->info('   - Обновите AITU_WEBHOOK_SECRET в .env файле');
        $this->info('   - Настройте URL webhook\'ов в панели управления Aitu');
        $this->info('');

        $this->info('4. Документация и примеры:');
        $this->info('   - Изучите README.md для подробной документации');
        $this->info('   - Проверьте config/aitu-messenger.php для всех настроек');
        $this->info('');

        $this->info('📚 Полезные ссылки:');
        $this->info('   - Aitu Passport: https://docs.passport.aitu.io/');
        $this->info('   - Aitu Apps: https://docs.aitu.io/aituapps/aitu.apps/');
    }
}