<?php

declare(strict_types=1);

namespace {{payloadNamespace}};

use andy87\yii2dnk\BasePayload;

/**
 * Описание класса {{viewPayloadClass}}.
 *
 * Payload view-действия. Хранит id модели.
 */
final class {{viewPayloadClass}} extends BasePayload
{
    /** @var int|null Идентификатор модели. */
    public ?int $id = null;

    /**
     * @return array<int, array<int|string, mixed>> Правила валидации.
     */
    public function rules(): array
    {
        return [
            ['id', 'required'],
            ['id', 'integer'],
        ];
    }
}
