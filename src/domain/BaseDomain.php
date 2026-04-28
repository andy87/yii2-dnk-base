<?php

declare(strict_types=1);

namespace andy87\yii2dnk\domain;

use andy87\yii2dnk\BasePayload;
use andy87\yii2dnk\viewModels\BaseViewModel;
use ReflectionException;
use ReflectionProperty;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * Базовый реестр домена для DNK flow.
 *
 * Основной DNK контракт хранит registry в protected typed properties,
 * а наружу отдаёт определения только через getter/factory methods.
 * Legacy const arrays остаются fallback-слоем для обратной совместимости.
 *
 * Константы имён ключей реестра:
 *
 * @see self::MODEL         Класс ActiveRecord-модели домена.
 * @see self::SEARCH_MODEL  Класс поисковой модели домена.
 * @see self::DATA_PROVIDER Класс ActiveDataProvider для list/index сценариев.
 * @see self::SERVICE       Класс сервиса бизнес-логики.
 * @see self::REPOSITORY    Класс репозитория чтения.
 * @see self::PRODUCER      Класс продюсера создания/сохранения.
 * @see self::KILLER        Класс удалёнщика.
 * @see self::QUERY_STORAGE Класс хранилища нативных SQL-запросов.
 * @see self::HANDLER       Класс оркестратора use-case.
 */
abstract class BaseDomain
{
    /** Ключ реестра: класс ActiveRecord-модели домена. */
    public const MODEL = 'model';
    /** Ключ реестра: класс поисковой модели домена. */
    public const SEARCH_MODEL = 'searchModel';
    /** Ключ реестра: класс ActiveDataProvider для list/index сценариев. */
    public const DATA_PROVIDER = 'dataProvider';
    /** Ключ реестра: класс сервиса бизнес-логики (BaseService). */
    public const SERVICE = 'service';
    /** Ключ реестра: класс репозитория чтения (BaseRepository). */
    public const REPOSITORY = 'repository';
    /** Ключ реестра: класс продюсера создания/сохранения (BaseProducer). */
    public const PRODUCER = 'producer';
    /** Ключ реестра: класс удалёнщика (BaseKiller). */
    public const KILLER = 'killer';
    /** Ключ реестра: класс хранилища нативных SQL-запросов (BaseQueryStorage). */
    public const QUERY_STORAGE = 'queryStorage';
    /** Ключ реестра: класс оркестратора use-case (BaseHandler). */
    public const HANDLER = 'handler';

    /** Идентификатор действия: список (index). */
    public const ACTION_INDEX = 'index';
    /** Идентификатор действия: просмотр (view). */
    public const ACTION_VIEW = 'view';
    /** Идентификатор действия: создание (create). */
    public const ACTION_CREATE = 'create';
    /** Идентификатор действия: обновление (update). */
    public const ACTION_UPDATE = 'update';
    /** Идентификатор действия: удаление (delete). */
    public const ACTION_DELETE = 'delete';

    /**
     * Legacy const-определения объектов домена, сгруппированные по ключам self::*.
     *
     * Каждый элемент — строка (FQCN) или массив (Yii object definition).
     * В основном registry flow предпочитай protected typed properties.
     *
     * @var array<string, class-string|array<string, mixed>>
     */
    protected const CLASSES = [];

    /**
     * Legacy const mapping action id -> класс payload.
     *
     * В основном registry flow предпочитай protected property $payloads.
     *
     * @var array<string, class-string<BasePayload>>
     */
    protected const PAYLOADS = [];

    /**
     * Legacy const mapping action id -> класс view model.
     *
     * В основном registry flow предпочитай protected property $viewModels.
     *
     * @var array<string, class-string<BaseViewModel>>
     */
    protected const VIEW_MODELS = [];

    /**
     * Mapping ключей registry на protected properties.
     *
     * @var array<string, string>
     */
    private const DEFINITION_PROPERTIES = [
        self::MODEL => 'model',
        self::SEARCH_MODEL => 'searchModel',
        self::DATA_PROVIDER => 'dataProvider',
        self::SERVICE => 'service',
        self::REPOSITORY => 'repository',
        self::PRODUCER => 'producer',
        self::KILLER => 'killer',
        self::QUERY_STORAGE => 'queryStorage',
        self::HANDLER => 'handler',
    ];

    /**
     * Ожидаемые базовые классы для registry definitions.
     *
     * @var array<string, class-string>
     */
    private const DEFINITION_EXPECTED_TYPES = [
        self::MODEL => ActiveRecord::class,
        self::SEARCH_MODEL => ActiveRecord::class,
        self::DATA_PROVIDER => BaseActiveDataProvider::class,
        self::SERVICE => BaseService::class,
        self::REPOSITORY => BaseRepository::class,
        self::PRODUCER => BaseProducer::class,
        self::KILLER => BaseKiller::class,
        self::QUERY_STORAGE => BaseQueryStorage::class,
        self::HANDLER => BaseHandler::class,
    ];

    /**
     * Обязательные registry keys для стандартного домена.
     *
     * @var array<int, string>
     */
    private const REQUIRED_DEFINITION_KEYS = [
        self::MODEL,
        self::SERVICE,
        self::REPOSITORY,
        self::PRODUCER,
        self::KILLER,
        self::HANDLER,
    ];

    /**
     * Класс ActiveRecord-модели домена.
     *
     * @var class-string<ActiveRecord>
     */
    protected string $model;

    /**
     * Класс поисковой ActiveRecord-модели домена.
     *
     * @var class-string<ActiveRecord>|null
     */
    protected ?string $searchModel = null;

    /**
     * Класс ActiveDataProvider builder для list/index сценариев.
     *
     * @var class-string<BaseActiveDataProvider>|null
     */
    protected ?string $dataProvider = null;

    /**
     * Класс сервиса бизнес-логики домена.
     *
     * @var class-string<BaseService>
     */
    protected string $service;

    /**
     * Класс репозитория чтения домена.
     *
     * @var class-string<BaseRepository>
     */
    protected string $repository;

    /**
     * Класс producer домена.
     *
     * @var class-string<BaseProducer>
     */
    protected string $producer;

    /**
     * Класс killer домена.
     *
     * @var class-string<BaseKiller>
     */
    protected string $killer;

    /**
     * Класс query storage домена.
     *
     * @var class-string<BaseQueryStorage>|null
     */
    protected ?string $queryStorage = null;

    /**
     * Класс handler домена.
     *
     * @var class-string<BaseHandler>
     */
    protected string $handler;

    /**
     * Mapping action id -> класс payload.
     *
     * @var array<string, class-string<BasePayload>>
     */
    protected array $payloads = [];

    /**
     * Mapping action id -> класс view model.
     *
     * @var array<string, class-string<BaseViewModel>>
     */
    protected array $viewModels = [];

    /**
     * Config overrides для создаваемых через Yii registry-объектов.
     *
     * Ключи совпадают с self::* registry keys. Top-level значение не должно
     * содержать бизнес-данные request/body или ключ 'class'; только инфраструктурную конфигурацию.
     * Вложенные Yii object definitions внутри config values могут содержать 'class'.
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $definitions = [];

    /**
     * Возвращает FQCN текущего класса реестра домена.
     *
     * @return class-string<static> FQCN текущего класса.
     */
    public static function className(): string
    {
        return static::class;
    }

    /**
     * Создаёт instance текущего Domain через Yii DI.
     *
     * Метод нужен для BC static facade: старые вызовы
     * ItemDomain::create(...) продолжают работать, но внутри используют
     * instance-based registry.
     * Instance намеренно не кэшируется, чтобы Yii DI mapping домена можно
     * было менять между вызовами в тестах, dev и playground сценариях.
     *
     * @return static Экземпляр текущего Domain.
     * @throws InvalidConfigException Если Yii DI вернул объект неверного типа.
     */
    public static function instance(): static
    {
        $instance = Yii::createObject(static::class);

        if (!is_object($instance) || !is_a($instance, static::class)) {
            throw new InvalidConfigException(sprintf(
                'Domain "%s" must create instance of itself.',
                static::class
            ));
        }

        return $instance;
    }

    /**
     * Возвращает все definitions объектов домена.
     *
     * Static facade для обратной совместимости.
     *
     * @return array<string, class-string|array<string, mixed>> Массив definitions по ключам.
     * @throws InvalidConfigException Если registry definition невалиден.
     */
    public static function classes(): array
    {
        return static::instance()->getDefinitions();
    }

    /**
     * Возвращает все mapping действий на классы payload.
     *
     * Static facade для обратной совместимости.
     *
     * @return array<string, class-string<BasePayload>> Массив action id -> FQCN payload.
     */
    public static function payloads(): array
    {
        return static::instance()->getPayloads();
    }

    /**
     * Возвращает все mapping действий на классы view model.
     *
     * Static facade для обратной совместимости.
     *
     * @return array<string, class-string<BaseViewModel>> Массив action id -> FQCN view model.
     */
    public static function viewModels(): array
    {
        return static::instance()->getViewModels();
    }

    /**
     * Проверяет наличие definition объекта домена по ключу.
     *
     * Static facade для обратной совместимости.
     *
     * @param string $key Ключ объекта домена.
     * @return bool True если definition существует.
     */
    public static function hasDefinition(string $key): bool
    {
        return static::instance()->hasInstanceDefinition($key);
    }

    /**
     * Возвращает definition объекта домена по ключу.
     *
     * Static facade для обратной совместимости.
     *
     * @param string $key Ключ объекта домена.
     * @return class-string|array<string, mixed> FQCN или Yii object definition.
     * @throws InvalidConfigException Если definition отсутствует или невалиден.
     */
    public static function definition(string $key): string|array
    {
        return static::instance()->getDefinition($key);
    }

    /**
     * Создаёт объект домена через Yii DI по ключу реестра.
     *
     * Static facade для обратной совместимости.
     *
     * @param string $key Ключ объекта домена.
     * @param array<int, mixed> $params Параметры конструктора.
     * @return object Созданный объект.
     * @throws InvalidConfigException Если definition отсутствует или объект не создан.
     */
    public static function create(string $key, array $params = []): object
    {
        return static::instance()->createObject($key, $params);
    }

    /**
     * Возвращает класс payload для указанного действия контроллера.
     *
     * Static facade для обратной совместимости.
     *
     * @param string $action Идентификатор действия контроллера.
     * @return class-string<BasePayload> FQCN класса payload.
     * @throws InvalidConfigException Если mapping отсутствует или класс не наследует BasePayload.
     */
    public static function payloadClass(string $action): string
    {
        return static::instance()->getPayloadClass($action);
    }

    /**
     * Возвращает класс view model для указанного действия контроллера.
     *
     * Static facade для обратной совместимости.
     *
     * @param string $action Идентификатор действия контроллера.
     * @return class-string<BaseViewModel> FQCN класса view model.
     * @throws InvalidConfigException Если mapping отсутствует или класс не наследует BaseViewModel.
     */
    public static function viewModelClass(string $action): string
    {
        return static::instance()->getViewModelClass($action);
    }

    /**
     * Создаёт экземпляр payload через Yii DI для указанного действия.
     *
     * Static facade для обратной совместимости.
     *
     * @param string $action Идентификатор действия контроллера.
     * @param array<string, mixed> $data Входные данные для заполнения payload.
     * @return BasePayload Созданный и валидированный экземпляр payload.
     * @throws InvalidConfigException Если создание payload завершилось неудачей.
     */
    public static function createPayload(string $action, array $data = []): BasePayload
    {
        return static::instance()->createPayloadObject($action, $data);
    }

    /**
     * Создаёт экземпляр view model через Yii DI для указанного действия.
     *
     * Static facade для обратной совместимости.
     *
     * @param string $action Идентификатор действия контроллера.
     * @return BaseViewModel Созданный экземпляр view model.
     * @throws InvalidConfigException Если создание view model завершилось неудачей.
     */
    public static function createViewModel(string $action): BaseViewModel
    {
        return static::instance()->createViewModelObject($action);
    }

    /**
     * Находит класс view model по классу payload через обратный mapping.
     *
     * Static facade для обратной совместимости.
     *
     * @param class-string<BasePayload> $payloadClass FQCN класса payload.
     * @return class-string<BaseViewModel>|null FQCN view model или null если mapping отсутствует.
     * @throws InvalidConfigException Если найденный класс view model невалиден.
     */
    public static function viewModelClassByPayload(string $payloadClass): ?string
    {
        return static::instance()->getViewModelClassByPayload($payloadClass);
    }

    /**
     * Создаёт экземпляр view model, связанный с переданным payload.
     *
     * Static facade для обратной совместимости.
     *
     * @param BasePayload $payload Входной payload, для которого ищется view model.
     * @return BaseViewModel|null Созданный view model или null если mapping отсутствует.
     * @throws InvalidConfigException Если создание view model завершилось неудачей.
     */
    public static function createViewModelForPayload(BasePayload $payload): ?BaseViewModel
    {
        return static::instance()->createViewModelObjectForPayload($payload);
    }

    /**
     * Возвращает все instance definitions объектов домена.
     *
     * @return array<string, class-string|array<string, mixed>> Массив definitions по ключам.
     * @throws InvalidConfigException Если definition невалиден.
     */
    public function getDefinitions(): array
    {
        $definitions = [];
        $keys = array_unique(array_merge(array_keys(self::DEFINITION_PROPERTIES), array_keys(static::CLASSES)));

        foreach ($keys as $key) {
            if (in_array($key, self::REQUIRED_DEFINITION_KEYS, true)) {
                $definitions[$key] = $this->getDefinition($key);

                continue;
            }

            if ($this->hasInstanceDefinition($key)) {
                $definitions[$key] = $this->getDefinition($key);
            }
        }

        return $definitions;
    }

    /**
     * Возвращает Yii object definition для ключа домена.
     *
     * @param string $key Ключ объекта домена.
     * @return class-string|array<string, mixed> FQCN или Yii object definition.
     * @throws InvalidConfigException Если definition отсутствует или невалиден.
     */
    public function getDefinition(string $key): string|array
    {
        if (!array_key_exists($key, self::DEFINITION_PROPERTIES)) {
            if (array_key_exists($key, static::CLASSES)) {
                return static::CLASSES[$key];
            }

            throw new InvalidConfigException(sprintf(
                'Domain "%s" has no "%s" definition.',
                static::class,
                $key
            ));
        }

        $property = self::DEFINITION_PROPERTIES[$key];
        $expectedParent = self::DEFINITION_EXPECTED_TYPES[$key];
        $required = in_array($key, self::REQUIRED_DEFINITION_KEYS, true);

        if ($required) {
            if ($this->propertyInitialized($property)) {
                return $this->buildDefinition($key, $this->requiredClass($property, $expectedParent, $key));
            }

            if (array_key_exists($key, static::CLASSES)) {
                return $this->validatedDefinition(static::CLASSES[$key], $expectedParent, $key);
            }

            throw new InvalidConfigException(sprintf(
                'Domain "%s" has uninitialized required property "$%s".',
                static::class,
                $property
            ));
        }

        if ($this->propertyInitialized($property)) {
            $class = $this->optionalClass($property, $expectedParent, $key);

            if ($class !== null) {
                return $this->buildDefinition($key, $class);
            }
        }

        if (array_key_exists($key, static::CLASSES)) {
            return $this->validatedDefinition(static::CLASSES[$key], $expectedParent, $key);
        }

        throw new InvalidConfigException(sprintf(
            'Domain "%s" has no "%s" definition.',
            static::class,
            $key
        ));
    }

    /**
     * Проверяет наличие instance definition по ключу.
     *
     * @param string $key Ключ объекта домена.
     * @return bool True если definition может быть получен.
     */
    public function hasInstanceDefinition(string $key): bool
    {
        if (!array_key_exists($key, self::DEFINITION_PROPERTIES)) {
            return array_key_exists($key, static::CLASSES);
        }

        $property = self::DEFINITION_PROPERTIES[$key];

        if ($this->propertyInitialized($property)) {
            if ($this->{$property} !== null) {
                return true;
            }
        }

        return array_key_exists($key, static::CLASSES);
    }

    /**
     * Создаёт объект домена через Yii DI по ключу реестра.
     *
     * @param string $key Ключ объекта домена.
     * @param array<int, mixed> $params Параметры конструктора.
     * @return object Созданный объект.
     * @throws InvalidConfigException Если definition отсутствует или объект не создан.
     */
    public function createObject(string $key, array $params = []): object
    {
        $object = Yii::createObject($this->getDefinition($key), $params);

        if (!is_object($object)) {
            throw new InvalidConfigException(sprintf(
                'Domain "%s" definition "%s" must create an object.',
                static::class,
                $key
            ));
        }

        return $object;
    }

    /**
     * Возвращает все mapping действий на классы payload.
     *
     * @return array<string, class-string<BasePayload>> Массив action id -> FQCN payload.
     */
    public function getPayloads(): array
    {
        return $this->payloads !== [] ? $this->payloads : static::PAYLOADS;
    }

    /**
     * Возвращает все mapping действий на классы view model.
     *
     * @return array<string, class-string<BaseViewModel>> Массив action id -> FQCN view model.
     */
    public function getViewModels(): array
    {
        return $this->viewModels !== [] ? $this->viewModels : static::VIEW_MODELS;
    }

    /**
     * Возвращает класс payload для указанного действия контроллера.
     *
     * @param string $action Идентификатор действия контроллера.
     * @return class-string<BasePayload> FQCN класса payload.
     * @throws InvalidConfigException Если mapping отсутствует или класс не наследует BasePayload.
     */
    public function getPayloadClass(string $action): string
    {
        $payloads = $this->getPayloads();

        if (!array_key_exists($action, $payloads)) {
            throw new InvalidConfigException(sprintf(
                'Domain "%s" has no payload for action "%s".',
                static::class,
                $action
            ));
        }

        $payloadClass = $payloads[$action];

        if (!is_subclass_of($payloadClass, BasePayload::class)) {
            throw new InvalidConfigException(sprintf(
                'Domain "%s" payload for action "%s" must be class-string of "%s".',
                static::class,
                $action,
                BasePayload::class
            ));
        }

        return $payloadClass;
    }

    /**
     * Возвращает класс view model для указанного действия контроллера.
     *
     * @param string $action Идентификатор действия контроллера.
     * @return class-string<BaseViewModel> FQCN класса view model.
     * @throws InvalidConfigException Если mapping отсутствует или класс не наследует BaseViewModel.
     */
    public function getViewModelClass(string $action): string
    {
        $viewModels = $this->getViewModels();

        if (!array_key_exists($action, $viewModels)) {
            throw new InvalidConfigException(sprintf(
                'Domain "%s" has no view model for action "%s".',
                static::class,
                $action
            ));
        }

        $viewModelClass = $viewModels[$action];

        if (!is_subclass_of($viewModelClass, BaseViewModel::class)) {
            throw new InvalidConfigException(sprintf(
                'Domain "%s" view model for action "%s" must be class-string of "%s".',
                static::class,
                $action,
                BaseViewModel::class
            ));
        }

        return $viewModelClass;
    }

    /**
     * Создаёт экземпляр payload через Yii DI для указанного действия.
     *
     * @param string $action Идентификатор действия контроллера.
     * @param array<string, mixed> $data Входные данные для заполнения payload.
     * @return BasePayload Созданный и валидированный экземпляр payload.
     * @throws InvalidConfigException Если создание payload завершилось неудачей.
     */
    public function createPayloadObject(string $action, array $data = []): BasePayload
    {
        $payload = Yii::createObject($this->getPayloadClass($action));

        if (!$payload instanceof BasePayload) {
            throw new InvalidConfigException(sprintf(
                'Payload for action "%s" must extend "%s".',
                $action,
                BasePayload::class
            ));
        }

        return $payload->loadData($data)->validateOrFail();
    }

    /**
     * Создаёт экземпляр view model через Yii DI для указанного действия.
     *
     * @param string $action Идентификатор действия контроллера.
     * @return BaseViewModel Созданный экземпляр view model.
     * @throws InvalidConfigException Если создание view model завершилось неудачей.
     */
    public function createViewModelObject(string $action): BaseViewModel
    {
        $viewModel = Yii::createObject($this->getViewModelClass($action));

        if (!$viewModel instanceof BaseViewModel) {
            throw new InvalidConfigException(sprintf(
                'View model for action "%s" must extend "%s".',
                $action,
                BaseViewModel::class
            ));
        }

        return $viewModel;
    }

    /**
     * Находит класс view model по классу payload через обратный mapping.
     *
     * @param class-string<BasePayload> $payloadClass FQCN класса payload.
     * @return class-string<BaseViewModel>|null FQCN view model или null если mapping отсутствует.
     * @throws InvalidConfigException Если найденный класс view model невалиден.
     */
    public function getViewModelClassByPayload(string $payloadClass): ?string
    {
        $action = array_search($payloadClass, $this->getPayloads(), true);

        if ($action === false || !array_key_exists($action, $this->getViewModels())) {
            return null;
        }

        return $this->getViewModelClass((string) $action);
    }

    /**
     * Создаёт экземпляр view model, связанный с переданным payload.
     *
     * @param BasePayload $payload Входной payload, для которого ищется view model.
     * @return BaseViewModel|null Созданный view model или null если mapping отсутствует.
     * @throws InvalidConfigException Если создание view model завершилось неудачей.
     */
    public function createViewModelObjectForPayload(BasePayload $payload): ?BaseViewModel
    {
        $viewModelClass = $this->getViewModelClassByPayload($payload::class);

        if ($viewModelClass === null) {
            return null;
        }

        $viewModel = Yii::createObject($viewModelClass);

        if (!$viewModel instanceof BaseViewModel) {
            throw new InvalidConfigException(sprintf(
                'View model "%s" must extend "%s".',
                $viewModelClass,
                BaseViewModel::class
            ));
        }

        return $viewModel;
    }

    /**
     * Возвращает обязательный class-string из protected property.
     *
     * @param string $property Имя свойства registry.
     * @param class-string $expectedParent Ожидаемый родительский класс или интерфейс.
     * @param string $key Ключ объекта домена.
     * @return class-string FQCN класса.
     * @throws InvalidConfigException Если свойство не инициализировано или класс невалиден.
     */
    protected function requiredClass(string $property, string $expectedParent, string $key): string
    {
        if (!$this->propertyInitialized($property)) {
            throw new InvalidConfigException(sprintf(
                'Domain "%s" has uninitialized required property "$%s".',
                static::class,
                $property
            ));
        }

        $class = $this->{$property};

        if (!is_string($class) || !is_subclass_of($class, $expectedParent)) {
            throw new InvalidConfigException(sprintf(
                'Domain "%s" property "$%s" for "%s" must be class-string of "%s".',
                static::class,
                $property,
                $key,
                $expectedParent
            ));
        }

        return $class;
    }

    /**
     * Возвращает optional class-string из protected property.
     *
     * @param string $property Имя свойства registry.
     * @param class-string $expectedParent Ожидаемый родительский класс или интерфейс.
     * @param string $key Ключ объекта домена.
     * @return class-string|null FQCN класса или null если optional definition не задан.
     * @throws InvalidConfigException Если класс невалиден.
     */
    protected function optionalClass(string $property, string $expectedParent, string $key): ?string
    {
        if (!$this->propertyInitialized($property)) {
            return null;
        }

        $class = $this->{$property};

        if ($class === null) {
            return null;
        }

        if (!is_string($class) || !is_subclass_of($class, $expectedParent)) {
            throw new InvalidConfigException(sprintf(
                'Domain "%s" property "$%s" for "%s" must be class-string of "%s".',
                static::class,
                $property,
                $key,
                $expectedParent
            ));
        }

        return $class;
    }

    /**
     * Проверяет, инициализировано ли typed property.
     *
     * @param string $property Имя свойства.
     * @return bool True если свойство инициализировано.
     * @throws InvalidConfigException Если свойства не существует.
     */
    protected function propertyInitialized(string $property): bool
    {
        try {
            return (new ReflectionProperty($this, $property))->isInitialized($this);
        } catch (ReflectionException $exception) {
            throw new InvalidConfigException(sprintf(
                'Domain "%s" has no property "$%s".',
                static::class,
                $property
            ), 0, $exception);
        }
    }

    /**
     * Собирает Yii object definition из class-string и config.
     *
     * @param string $key Ключ объекта домена.
     * @param class-string $class FQCN класса объекта.
     * @return class-string|array<string, mixed> FQCN или Yii object definition.
     * @throws InvalidConfigException Если config пытается переопределить top-level class.
     */
    private function buildDefinition(string $key, string $class): string|array
    {
        $config = $this->definitions[$key] ?? [];

        if (array_key_exists('class', $config)) {
            throw new InvalidConfigException(sprintf(
                'Domain "%s" definitions["%s"] must not contain top-level "class"; override protected property instead.',
                static::class,
                $key
            ));
        }

        return $config === [] ? $class : array_merge($config, ['class' => $class]);
    }

    /**
     * Валидирует legacy Yii object definition.
     *
     * @param class-string|array<string, mixed> $definition FQCN или Yii object definition.
     * @param class-string $expectedParent Ожидаемый родительский класс или интерфейс.
     * @param string $key Ключ объекта домена.
     * @return class-string|array<string, mixed> Исходный definition после проверки.
     * @throws InvalidConfigException Если definition невалиден.
     */
    private function validatedDefinition(string|array $definition, string $expectedParent, string $key): string|array
    {
        $class = is_string($definition) ? $definition : ($definition['class'] ?? null);

        if (!is_string($class) || !is_subclass_of($class, $expectedParent)) {
            throw new InvalidConfigException(sprintf(
                'Domain "%s" definition "%s" must be class-string of "%s".',
                static::class,
                $key,
                $expectedParent
            ));
        }

        return $definition;
    }
}
