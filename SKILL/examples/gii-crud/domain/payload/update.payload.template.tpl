<?php

declare(strict_types=1);

namespace {{payloadNamespace}};

use andy87\yii2dnk\BasePayload;

/**
 * Описание класса {{updatePayloadClass}}.
 *
 * Payload update-действия. Хранит id модели, POST-данные и флаг отправки.
 */
final class {{updatePayloadClass}} extends BasePayload
{
    /** @var int|null Идентификатор модели. */
    public ?int $id = null;

    /** @var array<string, mixed> POST-данные формы. */
    public array $data = [];

    /** @var bool Флаг отправки формы. */
    public bool $submitted = false;

    /**
     * @return array<int, array<int|string, mixed>> Правила валидации.
     */
    public function rules(): array
    {
        return [
            ['id', 'required'],
            ['id', 'integer'],
            ['data', 'safe'],
            ['submitted', 'boolean'],
        ];
    }
}
