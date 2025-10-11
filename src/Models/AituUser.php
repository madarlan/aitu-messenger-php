<?php

namespace MadArlan\AituMessenger\Models;

class AituUser
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Получить ID пользователя
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->data['id'] ?? null;
    }

    /**
     * Получить имя пользователя
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->data['name'] ?? null;
    }

    /**
     * Получить фамилию пользователя
     *
     * @return string|null
     */
    public function getSurname(): ?string
    {
        return $this->data['surname'] ?? null;
    }

    /**
     * Получить полное имя пользователя
     *
     * @return string|null
     */
    public function getFullName(): ?string
    {
        $name = $this->getName();
        $surname = $this->getSurname();

        if ($name && $surname) {
            return $name . ' ' . $surname;
        }

        return $name ?? $surname;
    }

    /**
     * Получить email пользователя
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->data['email'] ?? null;
    }

    /**
     * Получить номер телефона пользователя
     *
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->data['phone'] ?? null;
    }

    /**
     * Получить аватар пользователя
     *
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        return $this->data['avatar'] ?? null;
    }

    /**
     * Получить дату рождения пользователя
     *
     * @return string|null
     */
    public function getBirthDate(): ?string
    {
        return $this->data['birth_date'] ?? null;
    }

    /**
     * Получить пол пользователя
     *
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->data['gender'] ?? null;
    }

    /**
     * Получить город пользователя
     *
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->data['city'] ?? null;
    }

    /**
     * Получить страну пользователя
     *
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->data['country'] ?? null;
    }

    /**
     * Получить язык пользователя
     *
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return $this->data['language'] ?? null;
    }

    /**
     * Получить временную зону пользователя
     *
     * @return string|null
     */
    public function getTimezone(): ?string
    {
        return $this->data['timezone'] ?? null;
    }

    /**
     * Проверить, верифицирован ли пользователь
     *
     * @return bool
     */
    public function isVerified(): bool
    {
        return (bool) ($this->data['is_verified'] ?? false);
    }

    /**
     * Получить дату создания аккаунта
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->data['created_at'] ?? null;
    }

    /**
     * Получить дату последнего обновления
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->data['updated_at'] ?? null;
    }

    /**
     * Получить все данные пользователя
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Получить JSON представление пользователя
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Получить значение по ключу
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Проверить, существует ли ключ
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }
}