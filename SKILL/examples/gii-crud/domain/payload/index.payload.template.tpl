<?php

declare(strict_types=1);

namespace {{payloadNamespace}};

use andy87\yii2dnk\BasePayload;

/**
 * Описание класса {{indexPayloadClass}}.
 *
 * Payload index-действия. Хранит query params для SearchModel/DataProvider.
 */
final class {{indexPayloadClass}} extends BasePayload
{
    /** @var array<string, mixed> Query params index/filter запроса. */
    public array $params = [];

    /**
     * @return array<int, array<int|string, mixed>> Правила валидации.
     */
    public function rules(): array
    {
        return [
            ['params', 'safe'],
        ];
    }
}
