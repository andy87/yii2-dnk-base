<?php

declare(strict_types=1);

namespace andy87\yii2dnk\exceptions;

/**
 * Доменное исключение запрета действия.
 */
class ForbiddenException extends DnkException
{
    /**
     * @param string $message Сообщение исключения.
     * @param int $code Код исключения.
     * @param \Throwable|null $previous Предыдущее исключение.
     */
    public function __construct(
        string $message = 'Action is forbidden.',
        int $code = 403,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
