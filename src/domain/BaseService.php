<?php

declare(strict_types=1);

namespace andy87\yii2dnk\domain;

use andy87\yii2dnk\DomainAwareTrait;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * Базовый сервис бизнес-логики для DNK flow.
 *
 * Содержит бизнес-сценарии домена и предоставляет доступ к объектам
 * repository (чтение), producer (создание/сохранение) и killer (удаление)
 * через реестр домена. Не читает request и не рендерит response.
 */
abstract class BaseService extends BaseObject
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
     * Кэшированный экземпляр killer домена.
     *
     * @var BaseKiller|null
     */
    private ?BaseKiller $killerInstance = null;

    /**
     * Кэшированный экземпляр data provider builder.
     *
     * @var BaseActiveDataProvider|null
     */
    private ?BaseActiveDataProvider $dataProviderBuilder = null;

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
     * Возвращает доменный продюсер (кэшированный).
     *
     * Первый вызов создаёт продюсер через реестр домена, последующие
     * вызовы возвращают тот же экземпляр.
     *
     * @return BaseProducer Экземпляр продюсера домена.
     * @throws InvalidConfigException Если определение продюсера невалидно.
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
     * Возвращает доменный killer (кэшированный).
     *
     * Первый вызов создаёт killer через реестр домена, последующие
     * вызовы возвращают тот же экземпляр.
     *
     * @return BaseKiller Экземпляр killer домена.
     * @throws InvalidConfigException Если определение killer невалидно.
     */
    protected function getKiller(): BaseKiller
    {
        if ($this->killerInstance === null) {
            $killer = static::domainClass()::create(BaseDomain::KILLER);

            if (!$killer instanceof BaseKiller) {
                throw new InvalidConfigException(sprintf(
                    'Killer for "%s" must extend "%s".',
                    static::class,
                    BaseKiller::class
                ));
            }

            $this->killerInstance = $killer;
        }

        return $this->killerInstance;
    }

    /**
     * Возвращает доменный data provider builder (кэшированный).
     *
     * Первый вызов создаёт builder через реестр домена, последующие
     * вызовы возвращают тот же экземпляр. Это позволяет вызывать
     * getSearchModel() после search() на том же builder.
     *
     * @return BaseActiveDataProvider Экземпляр data provider builder.
     * @throws InvalidConfigException Если определение data provider невалидно.
     */
    protected function getDataProvider(): BaseActiveDataProvider
    {
        if ($this->dataProviderBuilder === null) {
            $dataProvider = static::domainClass()::create(BaseDomain::DATA_PROVIDER);

            if (!$dataProvider instanceof BaseActiveDataProvider) {
                throw new InvalidConfigException(sprintf(
                    'Data provider for "%s" must extend "%s".',
                    static::class,
                    BaseActiveDataProvider::class
                ));
            }

            $this->dataProviderBuilder = $dataProvider;
        }

        return $this->dataProviderBuilder;
    }

    /**
     * Возвращает SearchModel после выполнения search() через data provider builder.
     *
     * Используется Handler-ом для заполнения index resource / GridView filterModel.
     * Прокси к BaseActiveDataProvider::getSearchModel(). Бросает RuntimeException,
     * если search() ещё не вызывался.
     *
     * @return ActiveRecord SearchModel домена.
     * @throws \RuntimeException Если search() не вызывался.
     * @throws InvalidConfigException Если data provider настроен некорректно.
     */
    public function getSearchModel(): ActiveRecord
    {
        return $this->getDataProvider()->getSearchModel();
    }
}
