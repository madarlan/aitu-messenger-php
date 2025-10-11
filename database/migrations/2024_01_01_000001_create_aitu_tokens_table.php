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
        Schema::create('aitu_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('aitu_user_id')->comment('ID пользователя в Aitu');
            $table->text('access_token')->comment('Access token');
            $table->text('refresh_token')->nullable()->comment('Refresh token');
            $table->string('token_type')->default('Bearer')->comment('Тип токена');
            $table->integer('expires_in')->nullable()->comment('Время жизни токена в секундах');
            $table->timestamp('expires_at')->nullable()->comment('Дата истечения токена');
            $table->json('scopes')->nullable()->comment('Разрешения токена');
            $table->boolean('is_active')->default(true)->comment('Активен ли токен');
            $table->timestamp('last_used_at')->nullable()->comment('Последнее использование');
            $table->timestamps();

            // Индексы
            $table->index('aitu_user_id');
            $table->index('is_active');
            $table->index('expires_at');
            $table->index('last_used_at');

            // Внешние ключи
            $table->foreign('aitu_user_id')->references('aitu_id')->on('aitu_users')->onDelete('cascade');
        });
    }

    /**
     * Откатить миграцию
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('aitu_tokens');
    }
};