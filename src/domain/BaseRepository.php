<?php

declare(strict_types=1);

namespace andy87\yii2dnk\domain;

use andy87\yii2dnk\DomainAwareTrait;
use andy87\yii2dnk\exceptions\NotFoundException;
use andy87\yii2dnk\ModelClassTrait;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * Базовый репозиторий чтения для DNK flow.
 *
 * Инкапсулирует запросы к ActiveRecord-модели домена.
 * Предоставляет стандартные методы query(), findOne(), findAll() и
 * механизм применения критериев фильтрации.
 * Не создаёт, не сохраняет и не удаляет модели — это ответственность Producer и Killer.
 */
abstract class BaseRepository extends BaseObject
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
     * Хранилище нативных SQL-запросов домена.
     *
     * Инжектируется через Yii DI, задаётся вручную или создаётся из class-string/definition.
     * Содержит методы, возвращающие SQL-строки для сложных отчётов и аналитики.
     * Результат SQL выполняется через {@see execSql()} или {@see execSqlOne()}.
     *
     * @var BaseQueryStorage|class-string<BaseQueryStorage>|array<string, mixed>|null
     */
    public BaseQueryStorage|string|array|null $queryStorage = null;

    /**
     * Инициализирует query storage из domain registry.
     *
     * Если queryStorage передан через DI/config как class-string или Yii definition,
     * нормализует его в BaseQueryStorage. Если не передан — проверяет наличие
     * queryStorage definition в domain registry и создаёт его через Yii DI.
     *
     * @return void
     * @throws InvalidConfigException Если query storage настроен некорректно.
     */
    public function init(): void
    {
        parent::init();

        if ($this->queryStorage !== null) {
            $this->queryStorage = $this->createQueryStorage($this->queryStorage);

            return;
        }

        if (static::domainClass()::hasDefinition(BaseDomain::QUERY_STORAGE)) {
            $this->queryStorage = $this->createQueryStorage(static::domainClass()::definition(BaseDomain::QUERY_STORAGE));
        }
    }

    /**
     * Создаёт и валидирует query storage из объекта, class-string или Yii definition.
     *
     * Назначение: поддержать как Domain `$queryStorage`, так и Repository config
     * override `self::REPOSITORY => ['queryStorage' => ...]` без TypeError на
     * публичном typed property.
     *
     * @param BaseQueryStorage|class-string<BaseQueryStorage>|array<string, mixed> $definition Объект или Yii definition.
     * @return BaseQueryStorage Нормализованное хранилище SQL-запросов.
     * @throws InvalidConfigException Если definition создаёт объект неверного типа.
     */
    protected function createQueryStorage(BaseQueryStorage|string|array $definition): BaseQueryStorage
    {
        $queryStorage = $definition instanceof BaseQueryStorage
            ? $definition
            : Yii::createObject($definition);

        if (!$queryStorage instanceof BaseQueryStorage) {
            throw new InvalidConfigException(sprintf(
                'Query storage for "%s" must extend "%s".',
                static::class,
                BaseQueryStorage::class
            ));
        }

        return $queryStorage;
    }

    /**
     * Создаёт базовый ActiveQuery для модели домена.
     *
     * Получает класс модели через {@see ModelClassTrait::getModelClass()}
     * и вызывает статический метод find().
     *
     * @return ActiveQuery Экземпляр запроса ActiveRecord.
     * @throws InvalidConfigException Если определение модели невалидно.
     */
    public function query(): ActiveQuery
    {
        $modelClass = $this->getModelClass();

        return $modelClass::find();
    }

    /**
     * Находит одну модель по критериям.
     *
     * Создаёт базовый запрос, применяет критерии через {@see applyCriteria()}
     * и возвращает первый результат или null.
     *
     * @param array<string, mixed> $criteria Критерии поиска вида ['attribute' => 'value'].
     * @return ActiveRecord|null Найденная модель или null.
     * @throws InvalidConfigException Если определение модели невалидно.
     */
    public function findOne(array $criteria): ?ActiveRecord
    {
        return $this->applyCriteria($this->query(), $criteria)->one();
    }

    /**
     * Находит модель по первичному ключу.
     *
     * @param int|string|array<int|string, mixed> $id Первичный ключ модели.
     * @return ActiveRecord|null Найденная модель или null.
     * @throws InvalidConfigException Если определение модели невалидно.
     */
    public function findById(int|string|array $id): ?ActiveRecord
    {
        $modelClass = $this->getModelClass();

        return $modelClass::findOne($id);
    }

    /**
     * Находит модель по критериям или первичному ключу.
     *
     * Если модель не найдена, выбрасывает NotFoundException.
     *
     * @param array<string, mixed>|int|string $criteria Критерии или первичный ключ.
     * @param string|null $message Сообщение исключения.
     * @return ActiveRecord Найденная модель.
     * @throws InvalidConfigException Если определение модели невалидно.
     * @throws NotFoundException Если модель не найдена.
     */
    public function findOrFail(array|int|string $criteria, ?string $message = null): ActiveRecord
    {
        $model = is_array($criteria) ? $this->findOne($criteria) : $this->findById($criteria);

        if ($model === null) {
            throw new NotFoundException($message ?? sprintf(
                'Model "%s" was not found.',
                $this->getModelClass()
            ));
        }

        return $model;
    }

    /**
     * Находит все модели по критериям.
     *
     * Создаёт базовый запрос, применяет критерии через {@see applyCriteria()}
     * и возвращает все результаты.
     *
     * @param array<string, mixed> $criteria Критерии поиска вида ['attribute' => 'value']. Пустой массив — без фильтрации.
     * @return array<int, ActiveRecord> Массив найденных моделей.
     * @throws InvalidConfigException Если определение модели невалидно.
     */
    public function findAll(array $criteria = []): array
    {
        return $this->applyCriteria($this->query(), $criteria)->all();
    }

    /**
     * Проверяет существование модели по критериям.
     *
     * @param array<string, mixed> $criteria Критерии поиска.
     * @return bool True, если запись существует.
     * @throws InvalidConfigException Если определение модели невалидно.
     */
    public function exists(array $criteria = []): bool
    {
        return $this->applyCriteria($this->query(), $criteria)->exists();
    }

    /**
     * Подсчитывает количество моделей по критериям.
     *
     * @param array<string, mixed> $criteria Критерии поиска.
     * @return int Количество найденных моделей.
     * @throws InvalidConfigException Если определение модели невалидно.
     */
    public function count(array $criteria = []): int
    {
        return (int) $this->applyCriteria($this->query(), $criteria)->count();
    }

    /**
     * Возвращает ActiveQuery для grid/list сценариев.
     *
     * Применяет фильтры из массива $filter к базовому запросу модели.
     *
     * @param array<string, mixed> $filter Фильтры grid/list.
     * @return ActiveQuery Query для DataProvider.
     * @throws InvalidConfigException Если определение модели невалидно.
     */
    public function queryForGrid(array $filter = []): ActiveQuery
    {
        return $this->applyCriteria($this->query(), $filter);
    }

    /**
     * Выполняет произвольный SQL-запрос и возвращает все строки.
     *
     * Используется вместе с {@see $queryStorage} для выполнения
     * нативных SQL-запросов, которые неудобно выразить через ActiveRecord.
     * DB-компонент определяется через {@see getDb()}: если queryStorage
     * имеет настроенный $db, используется он, иначе — 'db'.
     *
     * ```php
     * [$sql, $params] = $this->queryStorage->getSqlWeeklyReport($filter);
     * return $this->execSql($sql, $params);
     * ```
     *
     * @param string $sql SQL-запрос для выполнения.
     * @param array<string, mixed> $params Параметры SQL-запроса.
     * @return array<int, array<string, mixed>> Массив строк результата (каждая строка — ассоциативный массив).
     */
    protected function execSql(string $sql, array $params = []): array
    {
        return $this->getDb()->createCommand($sql, $params)->queryAll();
    }

    /**
     * Выполняет произвольный SQL-запрос и возвращает одну строку.
     *
     * Аналог {@see execSql()}, но возвращает только первую строку
     * или false если результат пустой.
     *
     * @param string $sql SQL-запрос для выполнения.
     * @param array<string, mixed> $params Параметры SQL-запроса.
     * @return array<string, mixed>|false Ассоциативный массив одной строки или false.
     */
    protected function execSqlOne(string $sql, array $params = []): array|false
    {
        return $this->getDb()->createCommand($sql, $params)->queryOne();
    }

    /**
     * Возвращает DB connection для нативных SQL-запросов.
     *
     * Если queryStorage настроен и имеет свойство $db, используется оно.
     * Иначе используется компонент 'db' по умолчанию.
     *
     * @return Connection DB connection.
     * @throws InvalidConfigException Если компонент не является DB connection.
     */
    protected function getDb(): Connection
    {
        $db = $this->queryStorage?->db ?? 'db';
        $connection = Yii::$app->get($db);

        if (!$connection instanceof Connection) {
            throw new InvalidConfigException(sprintf(
                'Component "%s" must be instance of "%s".',
                $db,
                Connection::class
            ));
        }

        return $connection;
    }

    /**
     * Применяет критерии точного совпадения к запросу.
     *
     * Для каждого элемента массива критериев добавляет WHERE condition
     * с оператором равенства. Переопределите в подклассе для кастомной логики.
     *
     * @param ActiveQuery $query Целевой запрос.
     * @param array<string, mixed> $criteria Критерии фильтрации.
     * @return ActiveQuery Запрос с применёнными критериями.
     */
    protected function applyCriteria(ActiveQuery $query, array $criteria = []): ActiveQuery
    {
        foreach ($criteria as $attribute => $value) {
            $query->andWhere([$attribute => $value]);
        }

        return $query;
    }
}
