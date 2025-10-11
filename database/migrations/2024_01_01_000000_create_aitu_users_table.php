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
        Schema::create('aitu_users', function (Blueprint $table) {
            $table->id();
            $table->string('aitu_id')->unique()->comment('ID пользователя в Aitu');
            $table->string('name')->nullable()->comment('Имя пользователя');
            $table->string('email')->nullable()->comment('Email пользователя');
            $table->string('phone')->nullable()->comment('Телефон пользователя');
            $table->string('avatar')->nullable()->comment('URL аватара');
            $table->date('birth_date')->nullable()->comment('Дата рождения');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->comment('Пол');
            $table->string('city')->nullable()->comment('Город');
            $table->string('country')->nullable()->comment('Страна');
            $table->string('language', 10)->nullable()->comment('Язык интерфейса');
            $table->string('timezone')->nullable()->comment('Часовой пояс');
            $table->boolean('is_verified')->default(false)->comment('Верифицирован ли пользователь');
            $table->json('raw_data')->nullable()->comment('Сырые данные от Aitu API');
            $table->timestamp('aitu_created_at')->nullable()->comment('Дата создания в Aitu');
            $table->timestamp('aitu_updated_at')->nullable()->comment('Дата обновления в Aitu');
            $table->timestamps();

            // Индексы
            $table->index('email');
            $table->index('phone');
            $table->index('is_verified');
            $table->index('aitu_created_at');
        });
    }

    /**
     * Откатить миграцию
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('aitu_users');
    }
};