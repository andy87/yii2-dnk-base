<?php

declare(strict_types=1);

namespace andy87\yii2dnk\exceptions;

/**
 * Базовое исключение доменного слоя DNK.
 *
 * Используется для бизнес-ошибок, которые не являются ошибками конфигурации runtime.
 * Название `DnkException` выбрано вместо `DomainException` во избежание конфликта
 * с PHP SPL `\DomainException`.
 */
class DnkException extends \RuntimeException
{
}
