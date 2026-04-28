<?php

declare(strict_types=1);

namespace andy87\yii2dnk;

use andy87\yii2dnk\domain\BaseDomain;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * Предоставляет разрешение класса ActiveRecord-модели из реестра домена.
 *
 * Trait извлекает определение модели по ключу MODEL из domain registry,
 * проверяет что класс наследует ActiveRecord, и возвращает FQCN.
 * Используется в BaseRepository и BaseProducer для получения класса модели.
 */
trait ModelClassTrait
{
    /**
     * Возвращает класс ActiveRecord-модели из реестра домена.
     *
     * Получает определение через domainClass()::definition(BaseDomain::MODEL),
     * поддерживает как строку (FQCN) так и массив (Yii definition с ключом 'class').
     * Проверяет что класс наследует ActiveRecord.
     *
     * @return class-string<ActiveRecord> FQCN класса ActiveRecord-модели.
     * @throws InvalidConfigException Если определение модели невалидно или класс не наследует ActiveRecord.
     */
    protected function getModelClass(): string
    {
        $definition = static::domainClass()::definition(BaseDomain::MODEL);
        $modelClass = is_string($definition) ? $definition : ($definition['class'] ?? null);

        if (!is_string($modelClass) || !is_subclass_of($modelClass, ActiveRecord::class)) {
            throw new InvalidConfigException(sprintf(
                'Model for "%s" must extend "%s".',
                static::class,
                ActiveRecord::class
            ));
        }

        return $modelClass;
    }
}
