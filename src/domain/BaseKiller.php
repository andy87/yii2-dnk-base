<?php

declare(strict_types=1);

namespace andy87\yii2dnk\domain;

use andy87\yii2dnk\DomainAwareTrait;
use RuntimeException;
use yii\base\BaseObject;
use yii\db\ActiveRecord;

/**
 * Базовый удалёнщик для DNK flow.
 *
 * Отвечает за удаление ActiveRecord-моделей: жёсткое (hard delete)
 * или мягкое (soft delete) через установку атрибута-флага.
 * Не занимается поиском и созданием моделей.
 *
 * @property string|null $softDeleteAttribute Атрибут модели для мягкого удаления. Null — жёсткое удаление.
 * @property mixed $softDeleteValue Значение, устанавливаемое при мягком удалении (по умолчанию 1).
 */
abstract class BaseKiller extends BaseObject
{
    use DomainAwareTrait;

    /**
     * Явно указанный класс реестра домена.
     * Если пустая строка — определяется автоматически через DomainAwareTrait.
     *
     * @var class-string<BaseDomain>|''
     */
    protected const DOMAIN = '';

    /**
     * Атрибут модели для мягкого удаления.
     * Если null — используется жёсткое удаление через delete().
     *
     * @var string|null
     */
    protected ?string $softDeleteAttribute = null;

    /**
     * Значение, устанавливаемое в {@see $softDeleteAttribute} при мягком удалении.
     *
     * @var mixed
     */
    protected mixed $softDeleteValue = 1;

    /**
     * Удаляет одну модель: жёстко или мягко.
     *
     * Если {@see $softDeleteAttribute} задан — вызывает softDelete(),
     * иначе — стандартный $model->delete().
     *
     * @param ActiveRecord $model Целевая модель для удаления.
     * @return bool True если удаление прошло успешно.
     * @throws RuntimeException Если мягкое удаление завершилось неудачей при сохранении.
     */
    public function delete(ActiveRecord $model): bool
    {
        if ($this->softDeleteAttribute !== null) {
            return $this->softDelete($model);
        }

        return $model->delete() !== false;
    }

    /**
     * Удаляет несколько моделей и возвращает количество успешных удалений.
     *
     * Итерирует по коллекции, вызывая delete() для каждой модели.
     *
     * @param iterable<ActiveRecord> $models Коллекция моделей для удаления.
     * @return int Количество успешно удалённых моделей.
     * @throws RuntimeException Если мягкое удаление завершилось неудачей при сохранении.
     */
    public function deleteAll(iterable $models): int
    {
        $count = 0;

        foreach ($models as $model) {
            if ($this->delete($model)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Настраивает мягкое удаление и возвращает текущий экземпляр для цепочки вызовов.
     *
     * После вызова delete() будет устанавливать указанный атрибут в указанное значение
     * вместо физического удаления записи из БД.
     *
     * @param string $attribute Атрибут модели для установки (например 'status', 'is_deleted').
     * @param mixed $value Значение атрибута при мягком удалении (по умолчанию 1).
     * @return static Текущий экземпляр killer для цепочки вызовов.
     */
    public function useSoftDelete(string $attribute, mixed $value = 1): static
    {
        $this->softDeleteAttribute = $attribute;
        $this->softDeleteValue = $value;

        return $this;
    }

    /**
     * Помечает модель как удалённую и сохраняет без валидации.
     *
     * Устанавливает {@see $softDeleteValue} в атрибут {@see $softDeleteAttribute},
     * затем вызывает save() только для этого атрибута.
     *
     * @param ActiveRecord $model Целевая модель.
     * @return bool True если мягкое удаление прошло успешно.
     * @throws RuntimeException Если сохранение модели завершилось неудачей.
     */
    protected function softDelete(ActiveRecord $model): bool
    {
        $attribute = (string) $this->softDeleteAttribute;
        $model->setAttribute($attribute, $this->softDeleteValue);

        if (!$model->save(false, [$attribute])) {
            throw new RuntimeException(sprintf(
                'Model "%s" was not soft deleted: %s',
                $model::class,
                json_encode($model->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ));
        }

        return true;
    }
}
