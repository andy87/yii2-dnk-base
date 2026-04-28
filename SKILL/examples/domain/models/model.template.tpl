<?php

declare(strict_types=1);

namespace {{modelNamespace}};

use {{sourceFqcn}};

/**
 * Описание класса {{modelClass}}.
 *
 * Боевая модель домена {{domainName}}.
 * Цепочка наследования: {{modelClass}} extends {{sourceClass}} extends BaseModel extends ActiveRecord.
 *
 * Gii-модель {{sourceClass}} наследует BaseModel и генерируется/перегенерируется через Gii.
 * Боевая модель добавляет константы, labels и методы, которые не затираются при Gii.
 *
 * В этом классе хранятся:
 * - константы атрибутов (ATTR_*) для безопасного обращения к колонкам таблицы;
 * - константы статусов и других значений (STATUS_*, и т.д.);
 * - человекочитаемые labels через attributeLabels() с array_merge(parent::attributeLabels(), [...]);
 * - вспомогательные методы (getStatusList(), getStatusLabel() и т.д.).
 *
 * Gii-модель {{sourceClass}} генерируется с суффиксом Source и может быть
 * перезаписана при повторной генерации. Все доработки ведутся здесь.
 */
class {{modelClass}} extends {{sourceClass}}
{
    // --- Константы атрибутов ---
    // ATTR_ID, ATTR_CREATED_AT, ATTR_UPDATED_AT унаследованы от BaseModel.
    // Добавляй только доменно-специфичные атрибуты:

    // public const ATTR_STATUS = 'status';
    // public const ATTR_TITLE = 'title';

    // --- Константы статусов (пример) ---
    // public const STATUS_ACTIVE = 1;
    // public const STATUS_DELETED = 0;

    /**
     * Возвращает человекочитаемые названия атрибутов.
     *
     * Gii-модель может генерировать attributeLabels(), поэтому боевая модель
     * должна использовать array_merge(parent::attributeLabels(), [...]),
     * чтобы сохранить labels от BaseModel.
     *
     * @return array<string, string> Массив атрибут => название.
     */
    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            // Добавляй доменно-специфичные labels:
            // self::ATTR_STATUS => 'Статус',
        ]);
    }

    // --- Пример вспомогательных методов (раскомментируй если нужен status) ---
    //
    // public static function getStatusList(): array
    // {
    //     return [
    //         self::STATUS_ACTIVE => 'Активен',
    //         self::STATUS_DELETED => 'Удалён',
    //     ];
    // }
    //
    // public function getStatusLabel(): string
    // {
    //     return self::getStatusList()[$this->status] ?? 'Неизвестный';
    // }
}
