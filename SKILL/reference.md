# Yii2 DNK Reference

Основной источник правил: `SKILL/SKILL.md`.

Этот файл не дублирует полный skill, чтобы не расходиться с ним при изменениях.

## Runtime

Base-уровень находится в composer-пакете.

Основной способ установки runtime-пакета — через Packagist:

```bash
composer require andy87/yii2-dnk-base:^0.1
```

Для установки текущей ветки разработки:

```bash
composer require andy87/yii2-dnk-base:dev-master
```

При генерации доменов не создавай локальные копии `Base*` классов. Наследуйся от:

- `andy87\yii2dnk\BaseModel`
- `andy87\yii2dnk\BasePayload`
- `andy87\yii2dnk\domain\BaseDomain`
- `andy87\yii2dnk\domain\BaseHandler`
- `andy87\yii2dnk\domain\BaseService`
- `andy87\yii2dnk\domain\BaseRepository`
- `andy87\yii2dnk\domain\BaseProducer`
- `andy87\yii2dnk\domain\BaseKiller`
- `andy87\yii2dnk\domain\BaseActiveDataProvider`
- `andy87\yii2dnk\domain\BaseQueryStorage`
- `andy87\yii2dnk\controllers\handlers\BaseWebController`
- `andy87\yii2dnk\controllers\handlers\BaseConsoleController`
- `andy87\yii2dnk\controllers\handlers\ControllerDomainTrait`
- `andy87\yii2dnk\viewModels\BaseViewModel`
- `andy87\yii2dnk\viewModels\BaseResource`
- `andy87\yii2dnk\viewModels\BaseTemplateResource`
- `andy87\yii2dnk\viewModels\crud\BaseCreateResource`
- `andy87\yii2dnk\viewModels\crud\BaseUpdateResource`
- `andy87\yii2dnk\viewModels\crud\BaseIndexResource`
- `andy87\yii2dnk\viewModels\crud\BaseViewResource`
- `andy87\yii2dnk\viewModels\crud\BaseFormResource`
- `andy87\yii2dnk\exceptions\DnkException`
- `andy87\yii2dnk\exceptions\ValidationException`
- `andy87\yii2dnk\exceptions\NotFoundException`
- `andy87\yii2dnk\exceptions\ForbiddenException`

SearchModel не имеет отдельного `BaseSearchModel`: доменная search-модель наследуется от боевой модели и указывается в Domain через `protected ?string $searchModel = ItemSearchModel::class`. Для явной маркировки можно реализовать `andy87\yii2dnk\interfaces\SearchModelInterface`.

Model chain: `Item extends ItemSource extends BaseModel extends ActiveRecord`. Gii-модель (`ItemSource`) наследует `BaseModel`, боевая модель наследует `ItemSource`. `BaseModel` предоставляет дефолтные `attributeLabels()` для `ATTR_ID`, `ATTR_CREATED_AT`, `ATTR_UPDATED_AT`. Если Gii Source-модель генерирует `attributeLabels()`, Gii-шаблон должен возвращать `return array_merge(parent::attributeLabels(), [...])`, иначе labels из BaseModel будут перекрыты Source-моделью.

Service `getById()` использует `BaseRepository::findOrFail()` — выбрасывает `NotFoundException`, если модель не найдена. Handler не проверяет null.

`Service::search()` добавляется только для list/index/search сценариев, когда в registry заданы `$searchModel` и `$dataProvider`. SearchModel доступна через `$this->getSearchModel()` (прокси к `BaseActiveDataProvider::getSearchModel()`) после вызова `search()`. `getDataProvider()` кэширует builder — повторный вызов возвращает тот же экземпляр.

BaseService API:

- `getRepository(): BaseRepository` — кэшированный экземпляр репозитория домена.
- `getProducer(): BaseProducer` — кэшированный экземпляр продюсера домена.
- `getKiller(): BaseKiller` — кэшированный экземпляр killer домена.
- `getDataProvider(): BaseActiveDataProvider` — кэшированный data provider builder; требует `$dataProvider` в registry.
- `getSearchModel(): ActiveRecord` — публичный метод. SearchModel после search(). Бросает RuntimeException если search() не вызывался.

BaseWebController::display():

- `BaseTemplateResource` рендерится как HTML-view через `displayView()`.
- `BaseResource` и другие `BaseViewModel` сериализуются в JSON через `release()`.
- Массив/bool/null возвращается как JSON.

BaseProducer::fillModel():

- По умолчанию `fillModel()` использует safe-присвоение (`setAttributes($data, true)`).
- `fillModelUnsafe()` — для доверенных системных данных (`setAttributes($data, false)`).

BaseRepository::applyCriteria():

- Сигнатура: `applyCriteria(ActiveQuery $query, array $criteria = []): ActiveQuery` — унифицирована с `BaseActiveDataProvider::applyCriteria()`.

Payload validation: `BaseDomain::createPayload(...)` вызывает `validateOrFail()` при создании. `BaseHandler::run()` НЕ валидирует payload повторно.

`$viewModels` mapping опционален для action-ов, которые возвращают `bool`, `array` или `null`. Если mapping отсутствует, `BaseHandler::createViewModel()` передаёт в `provider()` значение `null`.

BaseActiveDataProvider API:

- `search(array $params = []): ActiveDataProvider` — abstract contract; конкретный доменный DataProvider реализует Gii-like search.
- `getSearchModel(): ActiveRecord` — возвращает SearchModel после search(). Бросает RuntimeException если search() не вызывался.
- `getDataProvider(?ActiveQuery $query = null): ActiveDataProvider` — создаёт ADP с query из repository.
- `setSearchModel(ActiveRecord $searchModel): void` — protected helper для сохранения SearchModel внутри доменного search().
- `applyCriteria(ActiveQuery $query, array $criteria = []): ActiveQuery` — унифицированная сигнатура.

`$searchModel` и `$dataProvider` обязательны только для list/index/search сценариев. Если домен не имеет index/search, эти properties остаются `null`.

DNK Domain registry задаётся через protected typed properties: `$model`, `$handler`, `$service`, `$repository`, `$producer`, `$killer`, optional `$searchModel`, `$dataProvider`, `$queryStorage`, mapping arrays `$payloads`, `$viewModels`. Legacy `CLASSES/PAYLOADS/VIEW_MODELS` остаются fallback для старого кода.

Domain-классы не должны быть `final`, если проект использует dev/mock/playground-подмены через наследование и Yii DI mapping самого Domain.

Config overrides для Yii object creation задаются в `$definitions` и допустимы для инфраструктурной конфигурации: Handler `db`, DataProvider `pageSize`/`criteria`, Repository `queryStorage`, QueryStorage `db`. Repository `queryStorage` принимает `BaseQueryStorage` object, class-string или Yii object definition и нормализуется в `BaseRepository::init()`. Не передавай через registry request/body/business data и не задавай top-level `$definitions[$key]['class']`: class создаваемого registry-объекта берётся только из protected registry property. Вложенный Yii definition, например `$definitions[self::REPOSITORY]['queryStorage']['class']`, допустим.

`BaseDomain::instance()` не кэширует объект Domain, чтобы Yii DI mapping домена можно было менять между вызовами в тестах, dev и playground сценариях.

SearchModel загружается через стандартный `$model->load($params)`, совместимо с Gii GridView параметрами вида `ItemSearchModel[attr]`.

Producer form API:

- `createFormModel(array $data = [], ?string $scenario = null): ActiveRecord` — создаёт runtime-модель формы, вызывает `$model->load($data)` и не сохраняет.
- `create(array $data, ...)` — только для плоских атрибутов и немедленного сохранения новой записи.

`$queryStorage` в domain registry равен `null` по умолчанию. BaseRepository::init() проверяет hasDefinition() перед созданием. Задавай класс только если нужны нативные SQL-запросы. Для локальной подмены на уровне repository instance используй `$definitions[self::REPOSITORY]['queryStorage']`.

Gii CRUD integration examples лежат в `examples/gii-crud/`, app-level base controllers — в `examples/app/` (common web-шаблон + backend/frontend обёртки + console).

## Flow

```text
Controller -> Handler -> Service -> (Repository | Producer | Killer)
```

## Templates

Шаблоны генерации находятся в `examples/`.
