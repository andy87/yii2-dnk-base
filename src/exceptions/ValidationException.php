<?php

declare(strict_types=1);

namespace andy87\yii2dnk\exceptions;

use Throwable;

/**
 * Исключение ошибки валидации входных данных.
 *
 * Используется для ошибок валидации payload, search model или другого Yii Model-объекта.
 */
class ValidationException extends DnkException
{
    /**
     * Ошибки валидации.
     *
     * @var array<string, mixed>
     */
    private array $errors;

    /**
     * Создаёт исключение валидации с массивом ошибок.
     *
     * @param array<string, mixed> $errors Ошибки валидации.
     * @param string $message Сообщение исключения.
     * @param int $code Код исключения.
     * @param Throwable|null $previous Предыдущее исключение.
     */
    public function __construct(
        array $errors,
        string $message = 'Validation failed.',
        int $code = 422,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * Возвращает ошибки валидации.
     *
     * @return array<string, mixed> Ошибки валидации.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
