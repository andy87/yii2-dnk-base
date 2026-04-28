<?php

declare(strict_types=1);

namespace andy87\yii2dnk\domain;

use andy87\yii2dnk\DomainAwareTrait;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Базовый построитель ActiveDataProvider для DNK flow.
 *
 * Предназначен для list/index сценариев: создаёт ActiveDataProvider
 * с запросом из repository домена и настраиваемой пагинацией.
 * Подклассы переопределяют applyCriteria() для добавления фильтрации и сортировки.
 *
 * @property int $pageSize Размер страницы пагинации (по умолчанию 20).
 * @property array<string, mixed> $criteria Критерии фильтрации по умолчанию (для использования в подклассах).
 */
abstract class BaseActiveDataProvider extends BaseObject
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
     * Размер страницы пагинации по умолчанию.
     *
     * @var int
     */
    public int $pageSize = 20;

    /**
     * Критерии фильтрации по умолчанию.
     *
     * Доступны в подклассах для использования в applyCriteria().
     *
     * @var array<string, mixed>
     */
    public array $criteria = [];

    /**
     * SearchModel последнего выполненного поиска.
     *
     * Заполняется в search(), доступна через getSearchModel().
     *
     * @var ActiveRecord|null
     */
    private ?ActiveRecord $searchModel = null;

    /**
     * Кэшированный экземпляр репозитория домена.
     *
     * @var BaseRepository|null
     */
    private ?BaseRepository $repositoryInstance = null;

    /**
     * Кэшированный экземпляр продюсера домена.
     *
     * @var BaseProducer|null
     */
    private ?BaseProducer $producerInstance = null;

    /**
     * Создаёт ActiveDataProvider, настроенный для модели домена.
     *
     * Если $query не передан, создаёт базовый запрос через repository домена.
     * Пагинация настраивается через {@see $pageSize}.
     *
     * @param ActiveQuery|null $query Готовый запрос. Если null — получается из repository домена.
     * @return ActiveDataProvider Настроенный data provider.
     * @throws InvalidConfigException Если определение модели или repository невалидно.
     */
    public function getDataProvider(?ActiveQuery $query = null): ActiveDataProvider
    {
        return Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query ?? $this->getQuery(),
            'pagination' => [
                'pageSize' => $this->pageSize,
            ],
        ]);
    }

    /**
     * Возвращает базовый запрос из repository домена с применёнными критериями.
     *
     * Создаёт repository через реестр домена, получает базовый запрос query(),
     * затем применяет доменные критерии через applyCriteria().
     *
     * @return ActiveQuery Запрос с применёнными критериями.
     * @throws InvalidConfigException Если определение repository невалидно.
     */
    protected function getQuery(): ActiveQuery
    {
        $repository = $this->getRepository();

        $query = $repository->query();

        return $this->applyCriteria($query);
    }

    /**
     * Возвращает доменный репозиторий (кэшированный).
     *
     * Первый вызов создаёт репозиторий через реестр домена, последующие
     * вызовы возвращают тот же экземпляр.
     *
     * @return BaseRepository Экземпляр репозитория домена.
     * @throws InvalidConfigException Если определение репозитория невалидно.
     */
    protected function getRepository(): BaseRepository
    {
        if ($this->repositoryInstance === null) {
            $repository = static::domainClass()::create(BaseDomain::REPOSITORY);

            if (!$repository instanceof BaseRepository) {
                throw new InvalidConfigException(sprintf(
                    'Repository for "%s" must extend "%s".',
                    static::class,
                    BaseRepository::class
                ));
            }

            $this->repositoryInstance = $repository;
        }

        return $this->repositoryInstance;
    }

    /**
     * Создаёт producer домена для создания search model (кэшированный).
     *
     * Первый вызов создаёт producer через реестр домена, последующие
     * вызовы возвращают тот же экземпляр.
     *
     * @return BaseProducer Экземпляр producer.
     * @throws InvalidConfigException Если определение producer невалидно.
     */
    protected function getProducer(): BaseProducer
    {
        if ($this->producerInstance === null) {
            $producer = static::domainClass()::create(BaseDomain::PRODUCER);

            if (!$producer instanceof BaseProducer) {
                throw new InvalidConfigException(sprintf(
                    'Producer for "%s" must extend "%s".',
                    static::class,
                    BaseProducer::class
                ));
            }

            $this->producerInstance = $producer;
        }

        return $this->producerInstance;
    }

    /**
     * Выполняет доменный поиск и возвращает ActiveDataProvider.
     *
     * Конкретный домен реализует Gii-like flow: создаёт SearchModel через
     * producer, сохраняет её через setSearchModel(), валидирует и применяет
     * явные andFilterWhere() условия.
     *
     * @param array<string, mixed> $params Query/request параметры поиска.
     * @return ActiveDataProvider Настроенный data provider.
     */
    abstract public function search(array $params = []): ActiveDataProvider;

    /**
     * Возвращает SearchModel, созданную при последнем вызове search().
     *
     * @return ActiveRecord SearchModel для GridView/filterModel.
     * @throws \RuntimeException Если search() ещё не вызывался.
     */
    public function getSearchModel(): ActiveRecord
    {
        if ($this->searchModel === null) {
            throw new \RuntimeException('Search model is not initialized. Call search() first.');
        }

        return $this->searchModel;
    }

    /**
     * Сохраняет SearchModel последнего выполненного поиска.
     *
     * Используется конкретным DataProvider::search(), чтобы GridView мог
     * получить filterModel через getSearchModel().
     *
     * @param ActiveRecord $searchModel SearchModel домена.
     * @return void
     */
    protected function setSearchModel(ActiveRecord $searchModel): void
    {
        $this->searchModel = $searchModel;
    }

    /**
     * Применяет доменные критерии к запросу.
     *
     * По умолчанию фильтрует по точному совпадению атрибутов.
     * Переопределите в подклассе для добавления WHERE-условий, ORDER BY, scopes.
     *
     * @param ActiveQuery $query Базовый запрос из repository.
     * @param array $criteria Критерии фильтрации. Если пусты — используются $this->criteria.
     * @return ActiveQuery Запрос с применёнными критериями.
     */
    protected function applyCriteria(ActiveQuery $query, array $criteria = []): ActiveQuery
    {
        $resolved = $criteria ?: $this->criteria;

        foreach ($resolved as $attribute => $value) {
            $query->andWhere([$attribute => $value]);
        }

        return $query;
    }
}
