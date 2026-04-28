<?php

declare(strict_types=1);

namespace andy87\yii2dnk\domain;

use andy87\yii2dnk\BasePayload;
use andy87\yii2dnk\DomainAwareTrait;
use andy87\yii2dnk\viewModels\BaseViewModel;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\Connection;

/**
 * Базовый оркестратор use-case для DNK flow.
 *
 * Handler принимает payload от контроллера, создаёт привязанный view model
 * через domain mapping и диспетчеризирует вызов в метод provider(),
 * который реализует конкретную бизнес-логику.
 *
 * Содержит встроенный механизм транзакций для сценариев, требующих атомарности.
 *
 * @property string $db Имя компонента БД приложения (по умолчанию 'db'). Используется transaction().
 */
abstract class BaseHandler extends BaseObject
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
     * Имя компонента БД приложения, используемое для транзакций.
     *
     * @var string
     */
    public string $db = 'db';

    /**
     * Кэшированный экземпляр сервиса бизнес-логики домена.
     *
     * @var BaseService|null
     */
    private ?BaseService $serviceInstance = null;

    /**
     * Выполняет payload через provider() с автоматическим созданием view model.
     *
     * Создаёт view model через domain mapping по классу payload,
     * затем передаёт оба объекта в provider() для диспетчеризации.
     *
     * @param BasePayload $payload Входной payload действия.
     * @return BaseViewModel|bool|array|null Результат выполнения действия.
     * @throws InvalidConfigException Если mapping классов невалиден.
     */
    public function run(BasePayload $payload): BaseViewModel|bool|array|null
    {
        return $this->provider($payload, $this->createViewModel($payload));
    }

    /**
     * Выполняет payload внутри транзакции БД.
     *
     * Оборачивает вызов run() в транзакцию. При выбросе исключения
     * транзакция откатывается, исключение пробрасывается дальше.
     *
     * @param BasePayload $payload Входной payload действия.
     * @return BaseViewModel|bool|array|null Результат выполнения действия.
     * @throws Throwable При ошибке в provider или транзакции.
     */
    public function runTransactional(BasePayload $payload): BaseViewModel|bool|array|null
    {
        return $this->transaction(fn (): BaseViewModel|bool|array|null => $this->run($payload));
    }

    /**
     * Диспетчеризирует payload в конкретный метод use-case.
     *
     * Реализация обычно использует match($payload::class) для маршрутизации
     * в приватные методы processView(), processCreate() и т.д.
     *
     * @param BasePayload $payload Входной payload действия.
     * @param BaseViewModel|null $viewModel Привязанный view model из domain mapping, может быть null.
     * @return BaseViewModel|bool|array|null Результат выполнения действия.
     * @throws InvalidConfigException Если payload не поддерживается.
     */
    abstract protected function provider(BasePayload $payload, ?BaseViewModel $viewModel = null): BaseViewModel|bool|array|null;

    /**
     * Создаёт экземпляр сервиса бизнес-логики домена через реестр.
     *
     * @return BaseService Экземпляр сервиса домена.
     * @throws InvalidConfigException Если определение сервиса невалидно.
     */
    protected function getService(): BaseService
    {
        if ($this->serviceInstance === null) {
            $service = static::domainClass()::create(BaseDomain::SERVICE);

            if (!$service instanceof BaseService) {
                throw new InvalidConfigException(sprintf(
                    'Service for "%s" must extend "%s".',
                    static::class,
                    BaseService::class
                ));
            }

            $this->serviceInstance = $service;
        }

        return $this->serviceInstance;
    }

    /**
     * Создаёт привязанный view model для переданного payload.
     *
     * Использует domain mapping для определения класса view model
     * по классу payload, затем создаёт экземпляр через Yii DI.
     *
     * @param BasePayload $payload Входной payload действия.
     * @return BaseViewModel|null Созданный view model или null если mapping отсутствует.
     * @throws InvalidConfigException Если класс view model невалиден.
     */
    protected function createViewModel(BasePayload $payload): ?BaseViewModel
    {
        return static::domainClass()::createViewModelForPayload($payload);
    }

    /**
     * Выполняет callback внутри транзакции БД.
     *
     * Открывает транзакцию через компонент {@see $db}, выполняет callback,
     * при успехе коммитит, при исключении — откатывает.
     *
     * @param callable(): mixed $callback Тело транзакции.
     * @return mixed Результат выполнения callback.
     * @throws InvalidConfigException Если компонент БД не является Connection.
     * @throws Throwable При ошибке в callback или транзакции.
     */
    protected function transaction(callable $callback): mixed
    {
        $db = Yii::$app->get($this->db);

        if (!$db instanceof Connection) {
            throw new InvalidConfigException(sprintf(
                'Component "%s" must be instance of "%s".',
                $this->db,
                Connection::class
            ));
        }

        $transaction = $db->beginTransaction();

        try {
            $result = $callback();
            $transaction->commit();

            return $result;
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }
    }
}
