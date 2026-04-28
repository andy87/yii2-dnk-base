---
name: yii2-dnk-flow
description: >-
  Генерация, проектирование, рефакторинг и анализ Yii2-кода по DNK flow:
  Controller, Handler, Service, Repository, Producer, Killer, Payload,
  ViewModel, Resource и Domain registry. Используй, когда пользователь просит
  Yii2 CRUD, endpoint, handler, service, repository, payload, view model,
  resource или domain, когда в prompt есть DNK ENABLE, когда проект использует
  composer-пакет andy87/yii2-dnk-base или классы пакета yii2dnk Base.
---

# Yii2 DNK Flow Skill

## 1. Runtime-зависимость

DNK Base-уровень не генерируется внутри прикладного проекта. Он поставляется composer-пакетом.

Основной способ установки runtime-пакета — через Packagist:

```bash
composer require andy87/yii2-dnk-base:^0.1
```

Для установки текущей ветки разработки:

```bash
composer require andy87/yii2-dnk-base:dev-master
```

При генерации доменного кода используй классы пакета:

```php
use andy87\yii2dnk\BaseModel;
use andy87\yii2dnk\BasePayload;
use andy87\yii2dnk\controllers\handlers\BaseConsoleController;
use andy87\yii2dnk\controllers\handlers\BaseWebController;
use andy87\yii2dnk\controllers\handlers\ControllerDomainTrait;
use andy87\yii2dnk\domain\BaseActiveDataProvider;
use andy87\yii2dnk\domain\BaseDomain;
use andy87\yii2dnk\domain\BaseHandler;
use andy87\yii2dnk\domain\BaseKiller;
use andy87\yii2dnk\domain\BaseProducer;
use andy87\yii2dnk\domain\BaseQueryStorage;
use andy87\yii2dnk\domain\BaseRepository;
use andy87\yii2dnk\domain\BaseService;
use andy87\yii2dnk\viewModels\BaseResource;
use andy87\yii2dnk\viewModels\BaseTemplateResource;
use andy87\yii2dnk\viewModels\BaseViewModel;
use andy87\yii2dnk\viewModels\crud\BaseCreateResource;
use andy87\yii2dnk\viewModels\crud\BaseFormResource;
use andy87\yii2dnk\viewModels\crud\BaseIndexResource;
use andy87\yii2dnk\viewModels\crud\BaseUpdateResource;
use andy87\yii2dnk\viewModels\crud\BaseViewResource;
```

Не создавай в проекте свои копии:

- `BaseDomain`
- `BaseHandler`
- `BaseService`
- `BaseRepository`
- `BaseProducer`
- `BaseKiller`
- `BasePayload`
- `BaseModel`
- `BaseViewModel`
- `BaseResource`
- `BaseViewResource`
- `BaseTemplateResource`
- `BaseWebController`
- `BaseConsoleController`
- `BaseActiveDataProvider`
- `BaseQueryStorage`
- `BaseFormResource`
- `BaseIndexResource`
- `BaseCreateResource`
- `BaseUpdateResource`

Если в проекте уже есть локальные `Base*` классы с тем же назначением, не дублируй их. Предложи миграцию на `andy87/yii2-dnk-base` или явно зафиксируй, что проект пока остается на локальной реализации.

## 1.1. Ограничения runtime-пакета

`andy87/yii2-dnk-base` — минимальный DNK base layer, а не полноценный framework.

Пакет НЕ предоставляет:

- `BaseSearchModel`;
- публичный `BaseDomain::searchModelClass()`;
- generic `BaseProducer::update(...)`;
- сложную orchestration-транзакций: nested transactions, saga/outbox, after-commit hooks;
- полный transport framework для web/API: redirect, file response, JSON:API, problem-details strategy.

Не генерируй вызовы методов, которых нет на соответствующем runtime-классе:

```php
ItemDomain::createSearchModel(); // нет на BaseDomain; используй BaseProducer::createSearchModel()
ItemDomain::searchModelClass();  // нет публичного Domain API
$this->getProducer()->update($model, $data);
$this->getRepository()->findBySlugOrFail($slug);
```

Runtime namespace сгруппирован по назначению:

- `andy87\yii2dnk\domain\*` — Domain/Handler/Service/Repository/Producer/Killer/DataProvider/QueryStorage.
- `andy87\yii2dnk\controllers\handlers\*` — Web/Console handler controllers пакета.
- `andy87\yii2dnk\viewModels\*` и `andy87\yii2dnk\viewModels\crud\*` — ViewModel/Resource классы.
- `andy87\yii2dnk\BasePayload`, `BaseModel`, `DomainAwareTrait`, `ModelClassTrait` остаются в корневом namespace пакета.

## 1.2. API Base-классов

Сигнатуры ниже синхронизированы с runtime-пакетом `github/andy87/yii2-dnk-base/src` на момент проверки. При изменении исходников пакета сначала сверяй эти сигнатуры с текущим кодом.

```php
// DomainAwareTrait
public static function domainClass(): string;
// private helpers: declaredDomainClass(), guessDomainClass(), domainShortName()

// BasePayload
public function __construct(array $config = []);
public static function fromArray(array $data): static;
public function loadData(array $data, string $formName = ''): static;
public function validateOrFail(?array $attributeNames = null, bool $clearErrors = true): static;

// BaseModel
public function attributeLabels(): array;

// ModelClassTrait
protected function getModelClass(): string;

// BaseDomain
public static function className(): string;
public static function instance(): static;
public static function classes(): array;
public static function payloads(): array;
public static function viewModels(): array;
public static function hasDefinition(string $key): bool;
public static function definition(string $key): string|array;
public static function create(string $key, array $params = []): object;
public static function payloadClass(string $action): string;
public static function viewModelClass(string $action): string;
public static function createPayload(string $action, array $data = []): BasePayload;
public static function createViewModel(string $action): BaseViewModel;
public static function viewModelClassByPayload(string $payloadClass): ?string;
public static function createViewModelForPayload(BasePayload $payload): ?BaseViewModel;
public function getDefinitions(): array;
public function getDefinition(string $key): string|array;
public function hasInstanceDefinition(string $key): bool;
public function createObject(string $key, array $params = []): object;
public function getPayloads(): array;
public function getViewModels(): array;
public function getPayloadClass(string $action): string;
public function getViewModelClass(string $action): string;
public function createPayloadObject(string $action, array $data = []): BasePayload;
public function createViewModelObject(string $action): BaseViewModel;
public function getViewModelClassByPayload(string $payloadClass): ?string;
public function createViewModelObjectForPayload(BasePayload $payload): ?BaseViewModel;

// BaseHandler
public function run(BasePayload $payload): BaseViewModel|bool|array|null;
public function runTransactional(BasePayload $payload): BaseViewModel|bool|array|null;
abstract protected function provider(BasePayload $payload, ?BaseViewModel $viewModel = null): BaseViewModel|bool|array|null;
protected function getService(): BaseService;
protected function createViewModel(BasePayload $payload): ?BaseViewModel;
protected function transaction(callable $callback): mixed;

// BaseService
protected function getRepository(): BaseRepository;
protected function getProducer(): BaseProducer;
protected function getKiller(): BaseKiller;
protected function getDataProvider(): BaseActiveDataProvider;
public function getSearchModel(): ActiveRecord;

// BaseRepository
public function init(): void;
public function query(): ActiveQuery;
public function findOne(array $criteria): ?ActiveRecord;
public function findById(int|string|array $id): ?ActiveRecord;
public function findOrFail(array|int|string $criteria, ?string $message = null): ActiveRecord;
public function findAll(array $criteria = []): array;
public function exists(array $criteria = []): bool;
public function count(array $criteria = []): int;
public function queryForGrid(array $filter = []): ActiveQuery;
protected function execSql(string $sql, array $params = []): array;
protected function execSqlOne(string $sql, array $params = []): array|false;
protected function getDb(): Connection;
protected function applyCriteria(ActiveQuery $query, array $criteria = []): ActiveQuery;
protected function createQueryStorage(BaseQueryStorage|string|array $definition): BaseQueryStorage;

// BaseProducer
public function create(array $data, ?string $scenario = null, bool $runValidation = true): ActiveRecord;
public function createSearchModel(array $data = [], ?string $scenario = null): ActiveRecord;
public function createModel(?string $scenario = null): ActiveRecord;
public function createFormModel(array $data = [], ?string $scenario = null): ActiveRecord;
protected function fillModel(ActiveRecord $model, array $data): ActiveRecord;
protected function fillModelUnsafe(ActiveRecord $model, array $data): ActiveRecord;
protected function saveModel(ActiveRecord $model, bool $runValidation = true, ?array $attributeNames = null): ActiveRecord;
protected function getSearchModelClass(): string;

// BaseKiller
public function delete(ActiveRecord $model): bool;
public function deleteAll(iterable $models): int;
public function useSoftDelete(string $attribute, mixed $value = 1): static;
protected function softDelete(ActiveRecord $model): bool;

// BaseActiveDataProvider
abstract public function search(array $params = []): ActiveDataProvider;
public function getSearchModel(): ActiveRecord;
public function getDataProvider(?ActiveQuery $query = null): ActiveDataProvider;
protected function setSearchModel(ActiveRecord $searchModel): void;
protected function getQuery(): ActiveQuery;
protected function getProducer(): BaseProducer;
protected function getRepository(): BaseRepository;
protected function applyCriteria(ActiveQuery $query, array $criteria = []): ActiveQuery;

// ControllerDomainTrait
protected function getHandler(): BaseHandler;
protected function getPayload(string $action, array $data = []): BasePayload;

// BaseWebController
protected function getPayloadFromRequest(string $action, ?array $data = null): BasePayload;
protected function display(BaseViewModel|array|bool|null $result, ?int $statusCode = null, ?string $view = null): string|Response;
protected function displayView(BaseViewModel $result, ?string $view = null, array $params = []): string;
protected function displayJson(array|bool|null $result, ?int $statusCode = null): Response;
protected function displayProblem(array|string $errors, int $statusCode = 400): Response;

// BaseConsoleController
protected function display(BaseViewModel|array|bool|null $result): int;

// BaseViewModel / BaseResource
public function __construct(array $config = []);
public function release(array $params = []): array;

// BaseTemplateResource
public const TEMPLATE = 'index';

// CRUD TemplateResource classes
public const TEMPLATE = 'create'; // BaseCreateResource
public const TEMPLATE = 'update'; // BaseUpdateResource
public const TEMPLATE = 'view';   // BaseViewResource
public function rules(): array;   // BaseFormResource, BaseIndexResource, BaseViewResource

// DNK exceptions
public function __construct(array $errors, string $message = 'Validation failed.', int $code = 422, ?Throwable $previous = null); // ValidationException
public function getErrors(): array; // ValidationException
public function __construct(string $message = 'Entity not found.', int $code = 404, ?Throwable $previous = null); // NotFoundException
public function __construct(string $message = 'Action is forbidden.', int $code = 403, ?Throwable $previous = null); // ForbiddenException
```

Публичные конфигурационные свойства Base-классов:

```php
// BaseHandler
public string $db = 'db';

// BaseQueryStorage
public string $db = 'db';

// BaseRepository
public BaseQueryStorage|string|array|null $queryStorage = null;
```

Protected registry-свойства `BaseDomain`:

```php
/** @var class-string<ActiveRecord> */
protected string $model;
/** @var class-string<ActiveRecord>|null */
protected ?string $searchModel = null;
/** @var class-string<BaseActiveDataProvider>|null */
protected ?string $dataProvider = null;
/** @var class-string<BaseService> */
protected string $service;
/** @var class-string<BaseRepository> */
protected string $repository;
/** @var class-string<BaseProducer> */
protected string $producer;
/** @var class-string<BaseKiller> */
protected string $killer;
/** @var class-string<BaseQueryStorage>|null */
protected ?string $queryStorage = null;
/** @var class-string<BaseHandler> */
protected string $handler;
/** @var array<string, class-string<BasePayload>> */
protected array $payloads = [];
/** @var array<string, class-string<BaseViewModel>> */
protected array $viewModels = [];
/** @var array<string, array<string, mixed>> */
protected array $definitions = [];
```

## 2. Flow

Основной поток:

```text
Controller -> Handler -> Service -> (Repository | Producer | Killer)
```

Дополнительные роли:

- `Payload` — входные данные конкретного действия.
- `ViewModel` / `Resource` — выходная модель данных для view/API.
- `Domain` — реестр классов домена, обязательный mapping action -> payload и опциональный mapping action -> view model.
- `SearchModel` / `DataProvider` — list/index сценарии (через `BaseActiveDataProvider`).
- `Model` — боевая ActiveRecord-модель домена. Цепочка наследования: `Item extends ItemSource extends BaseModel extends ActiveRecord`. Gii-модель (`ItemSource`) наследует `BaseModel` и генерируется/перегенерируется через Gii. Боевая модель добавляет константы атрибутов (`ATTR_*`), статусов (`STATUS_*`), доменно-специфичные labels через `array_merge(parent::attributeLabels(), [...])` и вспомогательные методы. `BaseModel` предоставляет дефолтные labels для `ATTR_ID`, `ATTR_CREATED_AT`, `ATTR_UPDATED_AT`. Gii-модель можно перегенерировать без потери доработок боевого класса. Если Gii Source-модель генерирует `attributeLabels()`, Gii-шаблон должен возвращать `return array_merge(parent::attributeLabels(), [...])`, иначе labels из BaseModel будут перекрыты Source-моделью.

## 3. Ответственность слоев

### DomainAwareTrait

DNK runtime-компоненты, которым нужен domain registry (`Handler`, `Service`, `Repository`, `Producer`, `Killer`, `DataProvider`, `QueryStorage`, `Controller`), используют `DomainAwareTrait` для определения класса домена:

1. Если объявлена `protected const DOMAIN = SomeDomain::class` — используется явно.
2. Если константа опущена или пуста — trait угадывает по имени: `ItemHandler` → `ItemDomain`, `ItemRepository` → `ItemDomain` и т.д.
3. Поиск идёт снизу вверх по namespace, пока не найдётся класс, наследующий `BaseDomain`.

Правило генерации: в scaffold-классах всегда указывай `protected const DOMAIN = SomeDomain::class`. Автоугадывание допустимо только для простых канонических namespace, но не должно быть единственной опорой при генерации нового кода.

`protected const DOMAIN` — не registry и не подмена `CLASSES/PAYLOADS/VIEW_MODELS`. Это только указатель runtime-компонента на Domain-класс, в котором registry хранится через protected typed properties.

`DomainAwareTrait::domainClass()` — runtime helper для Base-компонентов и редких инфраструктурных проверок. При генерации обычного domain-кода не вызывай его напрямую; указывай `protected const DOMAIN`, а handler/service/repository/controller получат domain class через базовые методы.

### Слои

`Controller`:
- принимает HTTP/console input;
- собирает payload через `getPayload(...)`;
- получает handler через `getHandler()`;
- вызывает `run(...)` или специальный метод handler;
- возвращает transport-level response через `display(...)`, JSON, HTML или exit code.

`getHandler()` и `getPayload()` предоставляются `ControllerDomainTrait`, который использует `DomainAwareTrait` для определения реестра домена. Оба метода унаследованы из trait — не переопределяй их без необходимости.

`Handler`:
- оркестрирует use-case;
- принимает `BasePayload`;
- получает `BaseViewModel` из domain mapping, если он нужен;
- вызывает service;
- собирает итоговый результат;
- управляет транзакцией через базовый механизм, если сценарий требует атомарности.

`Service`:
- содержит бизнес-логику;
- использует repository для чтения;
- использует producer для создания новых моделей;
- использует killer для удаления;
- не читает request и не рендерит response.

`Repository`:
- отвечает только за read/query;
- инкапсулирует критерии поиска;
- не создает, не сохраняет и не удаляет модели.

`Producer`:
- отвечает за создание новых моделей;
- задает scenario, заполняет данные, вызывает save;
- базово не обновляет существующие модели;
- не выполняет поиск коллекций.

`Killer`:
- отвечает за hard delete и optional soft delete;
- не занимается поиском и созданием.

`Payload`:
- содержит только входные данные действия;
- не читает request самостоятельно;
- не содержит бизнес-логику.

`ViewModel` / `Resource`:

- описывает структуру данных ответа;
- не содержит бизнес-логику.

CRUD web-view resources:
- `BaseIndexResource` — для `index.php`, содержит `searchModel` и `dataProvider`.
- `BaseCreateResource` — для `create.php`, наследуется от `BaseFormResource`.
- `BaseUpdateResource` — для `update.php`, наследуется от `BaseFormResource`.
- `BaseViewResource` — для `view.php`, содержит `model`.
- `BaseFormResource` содержит `model`, `action`, `saved`; `saved` используется controller-уровнем для redirect/alert после успешного сохранения.

`Domain`:

- хранит registry ключевых классов;
- хранит mapping `action -> payload`;
- хранит mapping `action -> view model`, если action возвращает `BaseViewModel`/resource. Для bool/array/null сценариев mapping view model можно не объявлять — тогда `BaseHandler::createViewModel()` передаст в provider `null`.
- хранит `$queryStorage`, если домен использует нативные SQL-запросы.

Action ID — используй константы `BaseDomain::ACTION_*` вместо строковых литералов:
- `BaseDomain::ACTION_INDEX` — `'index'`
- `BaseDomain::ACTION_VIEW` — `'view'`
- `BaseDomain::ACTION_CREATE` — `'create'`
- `BaseDomain::ACTION_UPDATE` — `'update'`
- `BaseDomain::ACTION_DELETE` — `'delete'`

Для нестандартных action-ов используй строковые литералы или добавь собственные константы в Domain.

## 4. Генерация домена

Для нового домена по умолчанию генерируй:
1. `ItemDomain extends BaseDomain`
2. `ItemSource` — Gii-модель из таблицы БД (генерируется и перегенерируется через Gii model, имеет суффикс `Source`, наследует `BaseModel`)
3. `Item extends ItemSource` — боевая модель: константы атрибутов (`ATTR_*`), статусов (`STATUS_*`), `attributeLabels()`, вспомогательные методы. Используется во всём домене как основная ActiveRecord-модель
4. `ItemRepository extends BaseRepository`
5. `ItemProducer extends BaseProducer`
6. `ItemKiller extends BaseKiller`
7. `ItemService extends BaseService`
8. `ItemHandler extends BaseHandler`
9. `Item*Payload extends BasePayload` — один payload-класс на action, каждый в отдельном файле (PSR-4)
10. `Item*Resource` или `Item*ViewModel extends BaseViewModel`
11. Web или console controller: `BaseWebController` / `BaseConsoleController`

Для list/index/search сценариев дополнительно:
12. `ItemSearchModel extends Item` — поисковая модель для grid/list, наследуется от боевой модели домена, как в Gii CRUD SearchModel
13. `ItemActiveDataProvider extends BaseActiveDataProvider`

Опционально (если нужны нативные SQL-запросы):
14. `ItemQueryStorage extends BaseQueryStorage`

Если пользователь не указал структуру проекта:
- для advanced app предпочитай `common` как reusable domain layer;
- web-контроллеры размещай в `frontend/controllers` или `backend/controllers`;
- console-контроллеры размещай в `console/controllers`;
- app-level base controllers размещай отдельно:
  - `backend\components\controllers\handler\BaseHandlerController extends andy87\yii2dnk\controllers\handlers\BaseWebController`;
  - `frontend\components\controllers\handler\BaseHandlerController extends andy87\yii2dnk\controllers\handlers\BaseWebController`;
  - `console\components\controllers\handler\BaseHandlerController extends andy87\yii2dnk\controllers\handlers\BaseConsoleController`;
- доменную зону размещай в `common/components/domain/item/...`;
- ActiveRecord-модели размещай по принятой структуре проекта.

## 4.1. End-to-end CRUD сценарий

Минимальная сборка CRUD-экрана из таблицы БД:
1. Сгенерируй `ItemSource extends BaseModel` через Gii model из таблицы. Source-модель можно перегенерировать.
2. Создай боевую модель `Item extends ItemSource`: атрибутные константы, статусы, доменные labels и helper-методы.
3. Создай `ItemSearchModel extends Item` только для index/GridView сценария.
4. Создай domain layer из шаблонов: `domain`, `repository`, `producer`, `killer`, `service`, `handler`, `data.provider`, payload-классы.
5. В `ItemDomain` объяви registry properties: `$model`, `$searchModel`, `$dataProvider`, `$handler`, `$service`, `$repository`, `$producer`, `$killer`, `$payloads`, `$viewModels`.
6. Создай CRUD resources: `Index`, `Create`, `Update`, `View`; form-сценарии должны возвращать `BaseFormResource`-наследников.
7. Создай app-level `BaseHandlerController`, если его ещё нет, затем CRUD controller из `examples/gii-crud/.../controller.template.tpl`.
8. Создай view-файлы из `examples/gii-crud/views/*.template.tpl`; resource поля попадут во view как `$model`, `$searchModel`, `$dataProvider`, `$resource`.

Поток выполнения `actionCreate`:
```text
POST form -> Controller::getPayloadFromRequest('create')
-> Domain::createPayload('create', $requestData)
-> Handler::run($payload)
-> Handler::provider($payload, CreateResource)
-> Service::create/update form model
-> Controller::display(CreateResource)
-> BaseTemplateResource::TEMPLATE ('create')
-> views/create.php
```

Поток выполнения `actionIndex`:
```text
GET query -> IndexPayload
-> Handler::run($payload)
-> Service::search($params)
-> BaseActiveDataProvider::search($params)
-> IndexResource(searchModel, dataProvider)
-> views/index.php
```

## 5. Domain registry

Domain-класс должен наследоваться от `BaseDomain` и объявлять registry через protected typed properties, action mappings и infrastructure config overrides. Классы registry задаются properties, не `CLASSES/PAYLOADS/VIEW_MODELS` const arrays:

```php
class ItemDomain extends BaseDomain
{
    /** @var class-string<Item> */
    protected string $model = Item::class;

    /** @var class-string<ItemSearchModel>|null */
    protected ?string $searchModel = ItemSearchModel::class;

    /** @var class-string<ItemHandler> */
    protected string $handler = ItemHandler::class;

    /** @var class-string<ItemService> */
    protected string $service = ItemService::class;

    /** @var class-string<ItemRepository> */
    protected string $repository = ItemRepository::class;

    /** @var class-string<ItemProducer> */
    protected string $producer = ItemProducer::class;

    /** @var class-string<ItemKiller> */
    protected string $killer = ItemKiller::class;

    /** @var class-string<ItemActiveDataProvider>|null */
    protected ?string $dataProvider = ItemActiveDataProvider::class;

    /** @var class-string<ItemQueryStorage>|null */
    protected ?string $queryStorage = null;

    /** @var array<string, array<string, mixed>> Config overrides без top-level ключа class. */
    protected array $definitions = [
        self::DATA_PROVIDER => ['pageSize' => 50],
    ];

    /** @var array<string, class-string<BasePayload>> */
    protected array $payloads = [
        self::ACTION_VIEW => ItemViewPayload::class,
    ];

    /** @var array<string, class-string<BaseViewModel>> */
    protected array $viewModels = [
        self::ACTION_VIEW => ItemViewResource::class,
    ];
}
```

Старые `protected const CLASSES`, `PAYLOADS`, `VIEW_MODELS` — legacy fallback. Не генерируй их в новом scaffold-коде, если проект явно не остаётся на legacy const registry.

Domain-класс не делай `final`, если проекту нужны dev/mock/playground-подмены через наследование и Yii DI mapping самого Domain.

Config overrides для Yii object creation задаются через `$definitions` и допустимы только для инфраструктурной конфигурации runtime-объектов:
- Handler: `db`
- DataProvider: `pageSize`, `criteria`
- Repository: `queryStorage` как `BaseQueryStorage` object, class-string или Yii object definition
- QueryStorage: `db`

Не передавай через registry request/body/business data.

Пример:

```php
protected array $definitions = [
    self::DATA_PROVIDER => [
        'pageSize' => 50,
        'criteria' => [
            Item::ATTR_STATUS => Item::STATUS_ACTIVE,
        ],
    ],
];
```

`BaseDomain::getDefinition()` сам добавит `class` из соответствующего protected property. Top-level `$definitions[$key]['class']` запрещён и должен приводить к `InvalidConfigException`; подменяй класс через наследование Domain и переопределение protected property. Вложенные Yii object definitions внутри config values допустимы, например `$definitions[self::REPOSITORY]['queryStorage']['class']`.

`BaseDomain::instance()` намеренно не кэширует объект Domain: это сохраняет возможность менять Yii DI mapping домена между вызовами в тестах, dev и playground сценариях. Не добавляй кэш без отдельной lifecycle-политики.

`$queryStorage` оставлен `null` по умолчанию — задавай только если домен использует нативные SQL-запросы. `BaseRepository::init()` проверяет `hasDefinition()` перед созданием QueryStorage, поэтому незаполненный optional definition не вызовет ошибку. Если QueryStorage нужно подменить только для конкретного Repository instance, используй `$definitions[self::REPOSITORY]['queryStorage']` со значением `BaseQueryStorage` object, class-string или Yii object definition.

`$searchModel` и `$dataProvider` обязательны только для list/index/search сценариев. Если домен не имеет index/search, оставляй эти properties `null`. Поисковая модель не имеет отдельного `BaseSearchModel`.
Поисковая модель создается через `BaseProducer::createSearchModel(...)` и используется в доменном DataProvider. Метод `createSearchModel($params)` загружает данные через стандартный `$model->load($params)`, как Gii SearchModel/GridView, не через `setAttributes(..., false)` и не через пустой `formName`.
Для явной маркировки search-моделей можно реализовать `andy87\yii2dnk\interfaces\SearchModelInterface` — маркерный интерфейс без методов.

Не добавляй в домен фабрики, которые уже есть в `BaseDomain`: `create(...)`, `createPayload(...)`, `createViewModel(...)`, mapping lookup и похожие методы.
Не добавляй `searchModelClass()` в домен, пока такого публичного API нет в пакете.

## 6. Controller rules

Для каждого action придерживайся схемы:
1. получить handler;
2. получить payload;
3. передать payload в handler;
4. вернуть response.

Пример:

```php
public function actionView(int $id): string|Response
{
    return $this->display(
        $this->getHandler()->run(
            $this->getPayload(BaseDomain::ACTION_VIEW, ['id' => $id])
        )
    );
}
```

Для request-driven action можно использовать:

```php
$payload = $this->getPayloadFromRequest(BaseDomain::ACTION_CREATE);
```

`getPayload(...)` и `getPayloadFromRequest(...)` создают payload через domain mapping и запускают базовую валидацию.

В PHPDoc доменных контроллеров добавляй class-level `@method` с конкретным payload-классом, чтобы IDE видела актуальный return type у inherited `getPayload(...)`:

```php
/**
 * @method UserIndexPayload getPayload(string $action, array $data = [])
 * @method UserViewPayload getPayload(string $action, array $data = [])
 */
final class UserController extends BaseHandlerController
{
}
```

Если action вызывает `getPayload(...)` или `getPayloadFromRequest(...)`, в PHPDoc action явно указывай `@throws \yii\base\InvalidConfigException` для ошибок domain/payload/handler mapping и оставляй `@throws \Throwable` только для handler/service/transaction/runtime ошибок.

Для простого HTML/JSON можно использовать `display($result, statusCode: 201, view: 'custom-view')`.
Если стандартный `display(...)` недостаточен, реализуй в конкретном controller собственный transport-метод и вызывай его явно:

```php
return $this->displayCreated($resource);
return $this->redirect(['view', 'id' => $model->id]);
return $this->asFile($path);
```

Transport-поведение `BaseWebController::display(...)`:
- `BaseTemplateResource` → HTML через `displayView(...)`;
- другой `BaseViewModel` → JSON через `$result->release()`;
- `array|bool|null` → JSON напрямую;
- `$statusCode` применяется только к JSON-ответам (`displayJson(...)`), не к HTML-render;
- `$view` переопределяет имя шаблона только для HTML-render.

`BaseTemplateResource` связывается с view-файлом через `public const TEMPLATE`. Если `$view` не передан в `display(...)`, `displayView(...)` берёт `$result::TEMPLATE`; если result не `BaseTemplateResource`, fallback — текущий `$this->action->id`. CRUD resources задают свои шаблоны: `index`, `create`, `update`, `view`.

Controller не должен:
- содержать бизнес-логику;
- строить ActiveRecord query;
- сохранять или удалять модели напрямую;
- читать request внутри service/repository/producer/killer вместо payload.

Для стандартного CRUD controller:
- в action вызывай handler;
- в handler/service держи бизнес-логику;
- redirect и flash-alert оставляй в controller или app-level `BaseHandlerController`;
- не создавай handler/service через `new`, используй runtime registry и `Yii::createObject()` внутри base-классов.

## 7. Handler rules

Handler наследуется от `BaseHandler`, указывает домен через `protected const DOMAIN`, а provider диспетчеризует payload:

```php
protected function provider(BasePayload $payload, ?BaseViewModel $viewModel = null): BaseViewModel|bool|array|null
{
    return match ($payload::class) {
        ItemViewPayload::class => $this->processView($payload, $viewModel),
        default => throw new InvalidConfigException('Unsupported payload: ' . $payload::class),
    };
}
```

Не дублируй в handler:
- создание view model по mapping;
- создание service через `Yii::createObject(...)`;
- transaction helper.

Эти функции уже находятся в `BaseHandler`.

Транзакции:
- `run(...)` выполняет сценарий без транзакции;
- `runTransactional(...)` оборачивает `run(...)` в одну транзакцию Yii DB component;
- компонент БД задается через публичное свойство `BaseHandler::$db`, по умолчанию `db`;
- если нужна другая БД для транзакций handler-а, задай `protected array $definitions = [self::HANDLER => ['db' => 'dbReporting']]` в Domain;
- nested transactions, несколько DB, saga/outbox и after-commit hooks пакет не реализует.

## 8. Service rules

Service наследуется от `BaseService`. В нем описываются бизнес-сценарии. Базовый набор зависит от сценариев домена:

```php
public function getById(int $id): Item
public function createItem(array $data): Item
public function updateItem(Item $model, array $data): Item
public function deleteItem(Item $model): bool
```

`search(array $params): ActiveDataProvider` добавляй только для list/index/search сценариев, когда в Domain registry заданы `$searchModel` и `$dataProvider`. Если домен не имеет index/search, не добавляй search-метод и не вызывай `getDataProvider()`/`getSearchModel()`.

`getById()` использует `BaseRepository::findOrFail()` и выбрасывает `NotFoundException`, если модель не найдена. Handler не проверяет null — ответственность за исключение лежит на service/repository.

```php
protected function getRepository(): BaseRepository
protected function getProducer(): BaseProducer
protected function getKiller(): BaseKiller
protected function getDataProvider(): BaseActiveDataProvider
public function getSearchModel(): ActiveRecord
```

Они уже есть в `BaseService`. Все getter-ы (`getRepository()`, `getProducer()`, `getKiller()`, `getDataProvider()`) кэшируют экземпляр: повторный вызов возвращает тот же объект. `getDataProvider()` требует `$dataProvider` в Domain registry. `getSearchModel()` — публичный метод, прокси к `BaseActiveDataProvider::getSearchModel()`, доступен после вызова `search()`.

## 9. Repository / Producer / Killer rules

Repository наследуется от `BaseRepository` и содержит только специфичные query-методы домена:

```php
public function findActiveItems(): array
public function findByUserId(int $userId): array
public function findForGrid(array $filter): ActiveQuery
```

Не дублируй базовые методы, если стандартного поведения достаточно:

```php
query()
findOne(array $criteria)
findById(int|string|array $id)
findOrFail(array|int|string $criteria)
findAll(array $criteria = [])
exists(array $criteria = [])
count(array $criteria = [])
queryForGrid(array $filter = [])
```

Producer наследуется от `BaseProducer` и содержит только специфичную create-логику домена. Не дублируй стандартные:

```php
create(array $data, ?string $scenario = null, bool $runValidation = true)
createModel(?string $scenario = null)
createFormModel(array $data = [], ?string $scenario = null)
createSearchModel(array $data = [], ?string $scenario = null)
```

`fillModel()` использует safe-присвоение по умолчанию (`setAttributes($data, true)`). Для доверенных системных данных используй `fillModelUnsafe()`. Не передавай request/payload напрямую в `fillModelUnsafe()`.

Обновление существующей модели не является generic API пакета. Для update реализуй метод в `Service` или явный доменный метод, но не вызывай несуществующий `BaseProducer::update(...)`.
`createFormModel(...)` — load-only API для полного Yii ActiveForm POST. Метод создаёт модель, вызывает `$model->load($data)` при наличии данных и не сохраняет её. Обычные validation errors формы должны оставаться в модели и возвращаться во view.

Killer наследуется от `BaseKiller` и содержит только специфичную delete/soft-delete логику домена. Не дублируй стандартные `delete(...)`, `deleteAll(...)`, `useSoftDelete(...)`.

### QueryStorage

QueryStorage наследуется от `BaseQueryStorage` и содержит нативные SQL-запросы домена. Подключается в Repository через свойство `$queryStorage`:

```php
final class ItemQueryStorage extends BaseQueryStorage
{
    public function getSqlCustomReport(array $filter): array
    {
        return ['SELECT 1 AS value', []];
    }
}
```

Использование в Repository:

```php
[$sql, $params] = $this->queryStorage->getSqlCustomReport($filter);
return $this->execSql($sql, $params);       // array of rows
return $this->execSqlOne($sql, $params);    // one row or false
```

Назначение: разделяет ActiveRecord-конструкции (ActiveQuery) и чистый SQL. Сложные отчёты, аналитика, агрегации — в QueryStorage. Простые CRUD-запросы — через AR-методы BaseRepository.

QueryStorage регистрируется через `protected ?string $queryStorage = ItemQueryStorage::class`. `BaseRepository` автоматически создаёт `$queryStorage`, если queryStorage definition существует. Если SQL-запросы должны идти через отдельный DB component, задай `self::QUERY_STORAGE => ['db' => 'dbReporting']` в `$definitions`, потому что `BaseQueryStorage` содержит public `$db`, а `BaseRepository::getDb()` читает `$this->queryStorage?->db`.

Локальная подмена QueryStorage для repository instance допустима через Repository config override. Это не нарушает запрет на top-level `$definitions[$key]['class']`, потому что `class` находится во вложенном Yii definition значения `queryStorage`:

```php
protected array $definitions = [
    self::REPOSITORY => [
        'queryStorage' => [
            'class' => ItemQueryStorage::class,
            'db' => 'dbReporting',
        ],
    ],
];
```

`BaseRepository::init()` нормализует `queryStorage` из object, class-string или Yii object definition в `BaseQueryStorage` и выбросит `InvalidConfigException`, если тип неверный.

QueryStorage не должен склеивать пользовательские значения в SQL. Методы возвращают SQL и params.

## 10. ActiveDataProvider rules

ActiveDataProvider наследуется от `BaseActiveDataProvider` и настраивает list/index сценарий:

```php
final class ItemActiveDataProvider extends BaseActiveDataProvider
{
    public function search(array $params = []): ActiveDataProvider
    {
        /** @var ItemSearchModel $searchModel */
        $searchModel = $this->getProducer()->createSearchModel($params);
        $this->setSearchModel($searchModel);

        $query = $this->getQuery();

        if (!$searchModel->validate()) {
            $query->where('0=1');

            return $this->getDataProvider($query);
        }

        $query->andFilterWhere([
            Item::ATTR_ID => $searchModel->id,
            Item::ATTR_STATUS => $searchModel->status,
        ]);

        return $this->getDataProvider($query);
    }

    protected function applyCriteria(ActiveQuery $query, array $criteria = []): ActiveQuery
    {
        return $query->andWhere(['status' => Item::STATUS_ACTIVE])
            ->orderBy(['created_at' => SORT_DESC]);
    }
}
```

Базовый класс предоставляет инфраструктуру для search-сценариев:
- `search(array $params = []): ActiveDataProvider` — abstract contract. Конкретный доменный DataProvider реализует Gii-like search.
- `getSearchModel(): ActiveRecord` — возвращает SearchModel после вызова `search()`. Бросает `RuntimeException` если `search()` не вызывался.
- `getDataProvider(?ActiveQuery $query = null): ActiveDataProvider` — создаёт ADP с query из repository.
- `setSearchModel(ActiveRecord $searchModel): void` — protected helper для сохранения filterModel внутри доменного `search()`.

Использование через service:

```php
public function search(array $params): ActiveDataProvider
{
    /** @var ItemActiveDataProvider $dataProvider */
    $dataProvider = $this->getDataProvider();

    return $dataProvider->search($params);
}
```

Для получения SearchModel (например, для GridView filterModel):
```php
$adp = $service->search($params);
$searchModel = $service->getSearchModel();
```

`getDataProvider()` кэширует builder внутри service, поэтому `getSearchModel()` возвращает SearchModel
с того же builder-экземпляра, на котором выполнялся `search()`.

Не дублируй:
- создание `ActiveDataProvider` с query из repository — уже есть в `getDataProvider()`.
- pagination — настраивается через `$pageSize`.
- сохранение SearchModel — используй `setSearchModel()` внутри доменного `search()`.

`applyCriteria(ActiveQuery $query, array $criteria = [])` — сигнатура унифицирована с `BaseRepository::applyCriteria()`. Если `$criteria` пуст — используются `$this->criteria`. Переопределяй для доменной фильтрации и сортировки.

Для стандартного Gii GridView параметры фильтра приходят как `ItemSearchModel[attr]`. Поэтому SearchModel загружай через `BaseProducer::createSearchModel($params)`, который использует стандартный `$model->load($params)`.

**formName: Payload vs SearchModel.** Payload использует `load($data, '')` (пустой formName) — ожидает плоский массив `['id' => 5]` из controller/routing. SearchModel использует стандартный `$model->load($params)` (formName по умолчанию = имя класса) — ожидает данные вида `['ItemSearchModel' => ['id' => 5]]` из Gii GridView POST/GET. Разница обоснована контекстом: Payload получает данные из controller, SearchModel — из GridView form submission.

## 11. Payload / ViewModel rules

Payload и ViewModel должны быть typed DTO-like классами с публичными свойствами:

```php
final class ItemViewPayload extends BasePayload
{
    // Безопасно для route-параметров, уже нормализованных controller action type-hint.
    // Для raw query/body строк используй string|array DTO-поля и кастуй после validation.
    public ?int $id = null;

    public function rules(): array
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
    }
}
```

```php
final class ItemViewResource extends BaseViewModel
{
    public ?Item $model = null;
}
```

Один payload-класс = одно конкретное действие. Не переиспользуй один payload для нескольких action, если нужен разный mapping view model: runtime ищет view model по payload-классу. Если action не имеет записи в `$viewModels`, runtime передаст в handler `null`; это нормальный путь для delete/command-сценариев, которые возвращают `bool`, `array` или `null`.

Payload validation:
- `BaseDomain::createPayload(...)` создает payload через `Yii::createObject($payloadClass)`, затем `loadData($data)`, затем `validateOrFail()` — payload гарантированно валиден после создания;
- Payload создаёт `BaseDomain`, не Producer;
- `BaseHandler::run(...)` НЕ валидирует payload повторно — валидация происходит один раз при создании через domain;
- при ошибке выбрасывается `andy87\yii2dnk\exceptions\ValidationException`;
- rules должны быть объявлены в payload, если сценарий зависит от входной валидации.

Typed scalar свойства (`?int`, `bool`, `float`) безопасны только для нормализованных controller data, например route-аргументов Yii с типом `int`. Если payload создаётся из raw query/body params через `getPayloadFromRequest()`, строки request-а могут вызвать `TypeError` при assignment до Yii validator; `BasePayload::loadData()` обернёт это в `ValidationException`, но не выполнит integer/boolean validator. Для request-facing полей нормализуй данные в controller перед `getPayload(...)` или используй строковые/array DTO-поля и кастуй после успешной валидации в service/handler.

Выбор базового класса для выходных данных:
- `BaseViewModel` — универсальный базовый класс. Handler заполняет публичные свойства напрямую (конструктор не принимает данные).
- `BaseResource` (extends `BaseViewModel`) — abstract, для API/REST-ответов. При стандартном `display()` сериализуется в JSON.
- `BaseTemplateResource` (extends `BaseViewModel`) — abstract, для web-view ответов с константой `TEMPLATE`. При стандартном `display()` рендерится как HTML.
- `BaseIndexResource`, `BaseCreateResource`, `BaseUpdateResource`, `BaseViewResource` — готовые CRUD web-view resources.

Если проект уже использует термин `Resource`, класс может называться `ItemViewResource`, но базовая роль должна оставаться понятной: API resource, template resource или CRUD view resource.

`BaseWebController::displayView()` передаёт во view:

- распакованные поля через `BaseViewModel::release()` (обёртка над `toArray()`) как обычные переменные view;
- дополнительную переменную `$resource`, указывающую на сам resource object.

Поэтому Gii-like view может использовать и привычные `$model`, `$dataProvider`, и типизированный `$resource`.

## 12. Naming

Используй нормализованные имена:
- `SERVICE`, не `SERVCIE`
- `RESPONSE`, не `REPONSE`
- `Response`, не `Responce`
- `release`, не `selease`
- `array`, не `arary`
- `extends`, не `extend`

Для ActiveRecord-моделей используй константы атрибутов в боевом классе (не в Gii-модели). `ATTR_ID`, `ATTR_CREATED_AT`, `ATTR_UPDATED_AT` унаследованы от BaseModel — переопределять не нужно:

```php
// Боевой класс Item extends ItemSource extends BaseModel
class Item extends ItemSource
{
    // ATTR_ID, ATTR_CREATED_AT, ATTR_UPDATED_AT — унаследованы от BaseModel

    public const ATTR_STATUS = 'status';

    public const STATUS_ACTIVE = 1;
    public const STATUS_DELETED = 0;
}
```

Использование в домене: `Item::ATTR_STATUS`, `Item::STATUS_ACTIVE`. Gii-модель `ItemSource` не модифицируется.

## 13. Антипаттерны

Не делай:
- бизнес-логику в controller;
- `Model::find()->where(...)` в controller;
- `new Repository()` вместо domain registry / `Yii::createObject(...)`;
- локальные копии Base-классов из `andy87/yii2-dnk-base`;
- один универсальный payload на все действия;
- несколько payload-классов в одном файле (PSR-4: один класс = один файл);
- смешение create/update/delete/find в repository;
- вызов несуществующих generic helper-методов пакета;
- HTML/rendering в service;
- чтение `Yii::$app->request` внутри service/repository/producer/killer.

## 14. Исключения

Пакет предоставляет базовые доменные исключения:
- `andy87\yii2dnk\exceptions\DnkException`
- `andy87\yii2dnk\exceptions\ValidationException`
- `andy87\yii2dnk\exceptions\NotFoundException`
- `andy87\yii2dnk\exceptions\ForbiddenException`

Transport mapping исключений в HTTP/console response проект реализует сам в controller, error handler или middleware.

## 15. Шаблоны

Готовые scaffold-шаблоны лежат в `examples/`:
- `examples/domain/domain.template.tpl`
- `examples/domain/models/model.template.tpl`
- `examples/domain/models/search.template.tpl`
- `examples/domain/data.provider.template.tpl`
- `examples/domain/handler.template.tpl`
- `examples/domain/service.template.tpl`
- `examples/domain/repository.template.tpl`
- `examples/domain/producer.template.tpl`
- `examples/domain/queryStorage.template.tpl`
- `examples/domain/killer.template.tpl`
- `examples/domain/payload.template.tpl`
- `examples/controller/controller.template.tpl`
- `examples/controller/console.controller.template.tpl`
- `examples/viewModel/Item.Index.Resource.template.tpl`
- `examples/viewModel/Item.Form.Resource.template.tpl`
- `examples/viewModel/Item.Create.Resource.template.tpl`
- `examples/viewModel/Item.Update.Resource.template.tpl`
- `examples/viewModel/Item.View.Resource.template.tpl`

Дополнительная группа Gii CRUD integration:
- `examples/app/common/base.handler.controller.template.tpl`
- `examples/app/backend/base.handler.controller.template.tpl`
- `examples/app/frontend/base.handler.controller.template.tpl`
- `examples/app/console/base.handler.controller.template.tpl`
- `examples/gii-crud/backend/controller.template.tpl`
- `examples/gii-crud/frontend/controller.template.tpl`
- `examples/gii-crud/console/controller.template.tpl`
- `examples/gii-crud/domain/domain.template.tpl`
- `examples/gii-crud/domain/handler.template.tpl`
- `examples/gii-crud/domain/service.template.tpl`
- `examples/gii-crud/domain/killer.template.tpl`
- `examples/gii-crud/domain/payload/index.payload.template.tpl`
- `examples/gii-crud/domain/payload/create.payload.template.tpl`
- `examples/gii-crud/domain/payload/update.payload.template.tpl`
- `examples/gii-crud/domain/payload/view.payload.template.tpl`
- `examples/gii-crud/domain/payload/delete.payload.template.tpl`
- `examples/gii-crud/views/index.template.tpl`
- `examples/gii-crud/views/create.template.tpl`
- `examples/gii-crud/views/update.template.tpl`
- `examples/gii-crud/views/view.template.tpl`
- `examples/gii-crud/views/_form.template.tpl`

Для smoke-рендера всех templates в любом проекте используй bundled script:

```bash
php /path/to/yii2-dnk-base/SKILL/scripts/generate-dnk-templates.php \
  --output=/path/to/project/generated \
  --domain=OrderItem \
  --namespace='app\GeneratedDnk' \
  --clean \
  --source-stub \
  --lint
```

Назначение renderer-а: проверить, что templates текущего skill раскрываются без unresolved placeholders и PHP syntax errors. Это не замена Gii и не runtime API. Для реального проекта передавай namespace через `--namespace`, а нестандартные placeholder-значения переопределяй через `--map-json=/path/map.json`. Скрипт хранится в `SKILL/scripts/generate-dnk-templates.php`; если нужен повторный smoke-test skill/package, не переписывай renderer в проекте заново — вызывай этот script.

Cursor skill является generated-копией этого master-файла, чтобы Cursor-агент загружал полный DNK flow в контекст и не зависел от перехода по относительным ссылкам. После изменения master `SKILL.md` синхронизируй Cursor-копию:

```bash
php /path/to/yii2-dnk-base/SKILL/scripts/sync-cursor-skill.php
```

Не редактируй `.cursor/skills/yii2-dnk/SKILL.md` вручную: правки потеряются при следующей синхронизации.
