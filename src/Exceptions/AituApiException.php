<?php

namespace MadArlan\AituMessenger\Exceptions;

use Exception;

class AituApiException extends Exception
{
    protected array $context = [];

    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Получить контекст ошибки
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Установить контекст ошибки
     *
     * @param array $context
     * @return self
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }
}