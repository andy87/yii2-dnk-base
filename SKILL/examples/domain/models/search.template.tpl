<?php

declare(strict_types=1);

namespace {{searchNamespace}};

use andy87\yii2dnk\interfaces\SearchModelInterface;
use {{modelFqcn}};

/**
 * Описание класса {{searchClass}}.
 *
 * SearchModel домена {{domainName}}. Наследуется от боевой модели и содержит
 * только правила/атрибуты поиска для grid/list сценариев.
 */
final class {{searchClass}} extends {{modelClass}} implements SearchModelInterface
{
    /**
     * Описание метода rules.
     *
     * Назначение: вернуть правила валидации поисковых атрибутов.
     * Добавляй только те атрибуты, по которым реально нужен фильтр в GridView.
     *
     * @return array<int, array<int|string, mixed>> Правила валидации.
     */
    public function rules(): array
    {
        return [
            [['id'], 'integer'],
            // Добавляй поисковые атрибуты домена:
            // [['status', 'created_at'], 'safe'],
        ];
    }
}
