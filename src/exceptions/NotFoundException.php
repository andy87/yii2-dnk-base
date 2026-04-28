<?php

declare(strict_types=1);

namespace andy87\yii2dnk\exceptions;

/**
 * Доменное исключение отсутствия сущности.
 */
class NotFoundException extends DnkException
{
    /**
     * Создаёт исключение отсутствия сущности.
     *
     * @param string $message Сообщение исключения.
     * @param int $code Код исключения.
     * @param \Throwable|null $previous Предыдущее исключение.
     */
    public function __construct(
        string $message = 'Entity not found.',
        int $code = 404,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
