<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запустить миграцию
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('aitu_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source')->comment('Источник webhook (passport, apps)');
            $table->string('event_type')->comment('Тип события');
            $table->json('payload')->comment('Данные webhook');
            $table->json('headers')->nullable()->comment('Заголовки запроса');
            $table->string('signature')->nullable()->comment('Подпись запроса');
            $table->boolean('signature_valid')->default(false)->comment('Валидна ли подпись');
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending')->comment('Статус обработки');
            $table->text('error_message')->nullable()->comment('Сообщение об ошибке');
            $table->timestamp('processed_at')->nullable()->comment('Время обработки');
            $table->timestamps();

            // Индексы
            $table->index(['source', 'event_type']);
            $table->index('status');
            $table->index('signature_valid');
            $table->index('processed_at');
            $table->index('created_at');
        });
    }

    /**
     * Откатить миграцию
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('aitu_webhook_logs');
    }
};