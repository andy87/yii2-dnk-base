<?php

declare(strict_types=1);

namespace {{payloadNamespace}};

use andy87\yii2dnk\BasePayload;

/**
 * Описание класса {{payloadClass}}.
 *
 * Payload действия {{actionId}} домена {{domainName}}. Содержит только
 * входные данные действия.
 */
final class {{payloadClass}} extends BasePayload
{
    // Безопасно для route-параметров, уже нормализованных controller action type-hint.
    // Для raw query/body строк используй string|array DTO-поля и кастуй после validation.
    public ?int $id = null;

    /**
     * Описание метода rules.
     *
     * Назначение: вернуть правила валидации входных данных действия.
     *
     * @return array<int, array<int|string, mixed>> Правила валидации.
     */
    public function rules(): array
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
    }
}
