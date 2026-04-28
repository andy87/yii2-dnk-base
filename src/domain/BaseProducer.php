<?php

declare(strict_types=1);

namespace andy87\yii2dnk\domain;

use andy87\yii2dnk\DomainAwareTrait;
use andy87\yii2dnk\ModelClassTrait;
use RuntimeException;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * Базовый продюсер создания и сохранения ActiveRecord для DNK flow.
 *
 * Отвечает за создание новых экземпляров моделей, заполнение атрибутов
 * и сохранение в БД. Поддерживает Yii scenario для валидации.
 * Не выполняет поиск коллекций — это ответственность Repository.
 */
abstract class BaseProducer extends BaseObject
{
    use DomainAwareTrait;
    use ModelClassTrait;

    /**
     * Явно указанный класс реестра домена.
     * Если пустая строка — определяется автоматически через DomainAwareTrait.
     *
     * @var class-string<BaseDomain>|''
     */
    protected const DOMAIN = '';

    /**
     * Создаёт новую модель, заполняет данными и сохраняет в БД.
     *
     * Последовательно вызывает createModel(), fillModel(), saveModel().
     * При ошибке сохранения бросает RuntimeException с деталями ошибок валидации.
     *
     * @param array<string, mixed> $data Атрибуты модели для заполнения.
     * @param string|null $scenario Сценарий валидации Yii (null — сценарий по умолчанию).
     * @param bool $runValidation Запускать ли валидацию при save().
     * @return ActiveRecord Сохранённая модель.
     * @throws InvalidConfigException Если определение модели невалидно.
     * @throws RuntimeException Если сохранение завершилось неудачей.
     */
    public function create(array $data, ?string $scenario = null, bool $runValidation = true): ActiveRecord
    {
        $model = $this->createModel($scenario);
        $this->fillModel($model, $data);

        return $this->saveModel($model, $runValidation);
    }

    /**
     * Создаёт поисковую модель домена из searchModel definition.
     *
     * В основном registry flow этот key обычно заполняется через protected property
     * BaseDomain::$searchModel, а не через legacy CLASSES.
     *
     * SearchModel не является отдельным Base-классом пакета и обычно наследуется
     * от боевой ActiveRecord-модели домена. Загружает данные через Model::load()
     * со стандартным formName, как Gii SearchModel::search().
     *
     * @param array<string, mixed> $data Атрибуты поисковой модели.
     * @param string|null $scenario Yii scenario.
     * @return ActiveRecord Созданная поисковая модель.
     * @throws InvalidConfigException Если searchModel definition отсутствует или невалиден.
     */
    public function createSearchModel(array $data = [], ?string $scenario = null): ActiveRecord
    {
        $searchModelClass = $this->getSearchModelClass();
        $model = Yii::createObject($searchModelClass);

        if (!$model instanceof ActiveRecord) {
            throw new InvalidConfigException(sprintf(
                'Search model "%s" must extend "%s".',
                $searchModelClass,
                ActiveRecord::class
            ));
        }

        if ($scenario !== null) {
            $model->setScenario($scenario);
        }

        if ($data !== []) {
            $model->load($data);
        }

        return $model;
    }

    /**
     * Создаёт новый пустой экземпляр модели домена.
     *
     * Получает класс модели через {@see ModelClassTrait::getModelClass()},
     * создаёт экземпляр через Yii DI, при необходимости устанавливает scenario.
     *
     * @param string|null $scenario Сценарий валидации Yii (null — сценарий по умолчанию).
     * @return ActiveRecord Новый пустой экземпляр модели.
     * @throws InvalidConfigException Если определение модели невалидно.
     */
    public function createModel(?string $scenario = null): ActiveRecord
    {
        $modelClass = $this->getModelClass();
        $model = Yii::createObject($modelClass);

        if (!$model instanceof ActiveRecord) {
            throw new InvalidConfigException(sprintf(
                'Model "%s" must extend "%s".',
                $modelClass,
                ActiveRecord::class
            ));
        }

        if ($scenario !== null) {
            $model->setScenario($scenario);
        }

        return $model;
    }

    /**
     * Создаёт модель формы из данных Yii ActiveForm POST-запроса.
     *
     * Метод корректно обрабатывает POST-данные в формате Yii ActiveForm
     * (например ['ModelName' => ['attr' => value]]), но не сохраняет модель.
     * Validation errors формы остаются в модели и обрабатываются Service/Controller
     * как обычный Gii CRUD flow.
     *
     * @param array<string, mixed> $data POST-данные формы.
     * @param string|null $scenario Yii scenario.
     * @return ActiveRecord Загруженная runtime-модель формы.
     * @throws InvalidConfigException Если модель не создана.
     */
    public function createFormModel(array $data = [], ?string $scenario = null): ActiveRecord
    {
        $model = $this->createModel($scenario);

        if ($data !== []) {
            $model->load($data);
        }

        return $model;
    }

    /**
     * Заполняет модель данными из массива.
     *
     * Использует setAttributes() с $safeOnly = true для безопасного массового
     * присвоения. Для доверенных системных данных используй fillModelUnsafe().
     *
     * @param ActiveRecord $model Целевая модель.
     * @param array<string, mixed> $data Атрибуты для заполнения.
     * @return ActiveRecord Заполненная модель.
     */
    protected function fillModel(ActiveRecord $model, array $data): ActiveRecord
    {
        $model->setAttributes($data, true);

        return $model;
    }

    /**
     * Заполняет модель без проверки safe-атрибутов.
     *
     * Использовать только для доверенных системных данных.
     * Не передавай сюда request/payload напрямую — это открывает mass assignment.
     *
     * @param ActiveRecord $model Целевая модель.
     * @param array<string, mixed> $data Доверенные атрибуты.
     * @return ActiveRecord Заполненная модель.
     */
    protected function fillModelUnsafe(ActiveRecord $model, array $data): ActiveRecord
    {
        $model->setAttributes($data, false);

        return $model;
    }

    /**
     * Сохраняет модель в БД с выбросом исключения при неудаче.
     *
     * Если save() возвращает false, выбрасывает RuntimeException
     * с JSON-закодированным списком ошибок валидации.
     *
     * @param ActiveRecord $model Целевая модель.
     * @param bool $runValidation Запускать ли валидацию перед сохранением.
     * @param array<int, string>|null $attributeNames Список атрибутов для сохранения (null — все).
     * @return ActiveRecord Сохранённая модель.
     * @throws RuntimeException Если сохранение завершилось неудачей.
     */
    protected function saveModel(ActiveRecord $model, bool $runValidation = true, ?array $attributeNames = null): ActiveRecord
    {
        if (!$model->save($runValidation, $attributeNames)) {
            throw new RuntimeException(sprintf(
                'Model "%s" was not saved: %s',
                $model::class,
                json_encode($model->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ));
        }

        return $model;
    }

    /**
     * Возвращает класс поисковой модели из domain registry.
     *
     * В основном registry flow обычно читает protected property BaseDomain::$searchModel,
     * в legacy fallback — definition по ключу SEARCH_MODEL.
     *
     * @return class-string<ActiveRecord> FQCN поисковой ActiveRecord-модели.
     * @throws InvalidConfigException Если searchModel definition отсутствует или невалиден.
     */
    protected function getSearchModelClass(): string
    {
        $definition = static::domainClass()::definition(BaseDomain::SEARCH_MODEL);
        $modelClass = is_string($definition) ? $definition : ($definition['class'] ?? null);

        if (!is_string($modelClass) || !is_subclass_of($modelClass, ActiveRecord::class)) {
            throw new InvalidConfigException(sprintf(
                'Search model for "%s" must extend "%s".',
                static::class,
                ActiveRecord::class
            ));
        }

        return $modelClass;
    }
}
