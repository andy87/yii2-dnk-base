<?php

declare(strict_types=1);

namespace andy87\yii2dnk;

use yii\db\ActiveRecord;

/**
 * Базовая ActiveRecord-модель DNK.
 *
 * Содержит общие имена технических атрибутов, которые часто используются
 * в боевых моделях домена и search-моделях.
 */
class BaseModel extends ActiveRecord
{
    /** Имя атрибута первичного ключа. */
    public const ATTR_ID = 'id';

    /** Имя атрибута даты создания. */
    public const ATTR_CREATED_AT = 'created_at';

    /** Имя атрибута даты обновления. */
    public const ATTR_UPDATED_AT = 'updated_at';

    /**
     * Возвращает человекочитаемые названия технических атрибутов.
     *
     * Боевые модели домена дополняют этот массив через array_merge(parent::attributeLabels(), [...]).
     *
     * @return array<string, string> Массив атрибут => название.
     */
    public function attributeLabels(): array
    {
        return [
            self::ATTR_ID => 'ID',
            self::ATTR_CREATED_AT => 'Дата создания',
            self::ATTR_UPDATED_AT => 'Дата обновления',
        ];
    }
}
