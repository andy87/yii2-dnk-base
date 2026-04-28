<?php

declare(strict_types=1);

namespace {{payloadNamespace}};

use andy87\yii2dnk\BasePayload;

/**
 * Описание класса {{createPayloadClass}}.
 *
 * Payload create-действия. Хранит POST-данные формы и флаг отправки.
 */
final class {{createPayloadClass}} extends BasePayload
{
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
            ['data', 'safe'],
            ['submitted', 'boolean'],
        ];
    }
}
