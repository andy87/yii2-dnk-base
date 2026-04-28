# yii2-dnk-base

Базовые классы для Yii2-проектов, следующих DNK flow:

```text
Controller -> Handler -> Service -> (Repository | Producer | Killer)
```

## Установка

Основной способ установки — через Packagist:

```bash
composer require andy87/yii2-dnk-base:^0.1
```

Для установки текущей ветки разработки:

```bash
composer require andy87/yii2-dnk-base:dev-master
```

## Базовые классы

- `andy87\yii2dnk\domain\BaseDomain` — реестр домена и маппинг действий.
- `andy87\yii2dnk\controllers\handlers\BaseWebController` и `BaseConsoleController` — создают payload и handler через реестр домена.
- `andy87\yii2dnk\domain\BaseHandler` — выполняет payload, создаёт привязанную view-model и диспетчеризирует use case в `provider()`.
- `andy87\yii2dnk\domain\BaseService` — создаёт объекты repository, producer, killer и data provider через реестр домена (все getter-ы кэшируют экземпляр).
- `andy87\yii2dnk\domain\BaseRepository` — помощники чтения и запросов: `findById()`, `findOrFail()`, `exists()`, `count()`, `queryForGrid()`.
- `andy87\yii2dnk\domain\BaseProducer` — помощники создания runtime-моделей, новых ActiveRecord и search model.
- `andy87\yii2dnk\domain\BaseKiller` — помощники жёсткого и опционального мягкого удаления.
- `andy87\yii2dnk\BasePayload` — входные данные действия.
- `andy87\yii2dnk\BaseModel` — базовая ActiveRecord-модель с константами `ATTR_*` и дефолтными `attributeLabels()`.
- `andy87\yii2dnk\viewModels\BaseViewModel` — выходные данные действия.
- `andy87\yii2dnk\viewModels\BaseResource` — базовый resource для API/REST-ответов.
- `andy87\yii2dnk\viewModels\BaseTemplateResource` — базовый resource для web-view с `TEMPLATE`.
- `andy87\yii2dnk\viewModels\crud\BaseIndexResource`, `BaseFormResource`, `BaseCreateResource`, `BaseUpdateResource`, `BaseViewResource` — готовые CRUD resources для Gii-like views.
- `andy87\yii2dnk\DomainAwareTrait` — trait для автоматического определения класса домена по имени класса.
- `andy87\yii2dnk\ModelClassTrait` — trait для получения класса ActiveRecord-модели из реестра домена.
- `andy87\yii2dnk\controllers\handlers\ControllerDomainTrait` — общие методы getHandler()/getPayload() для web/console контроллеров.
- `andy87\yii2dnk\domain\BaseActiveDataProvider` — базовый builder `ActiveDataProvider` для list/index сценариев.
- `andy87\yii2dnk\domain\BaseQueryStorage` — базовый класс хранилища нативных SQL-запросов.
- `andy87\yii2dnk\interfaces\SearchModelInterface` — маркерный интерфейс для search-моделей.
- `andy87\yii2dnk\exceptions\DnkException`, `andy87\yii2dnk\exceptions\ValidationException`, `andy87\yii2dnk\exceptions\NotFoundException`, `andy87\yii2dnk\exceptions\ForbiddenException` — базовые доменные исключения. `DnkException` именован так, чтобы избежать конфликта с PHP SPL `\DomainException`.

## Domain registry

Основной registry-flow задаётся через instance-based `BaseDomain` с protected typed properties:

- `$model`, `$handler`, `$service`, `$repository`, `$producer`, `$killer`;
- optional `$searchModel`, `$dataProvider`, `$queryStorage`;
- mappings `$payloads`, `$viewModels`;
- config overrides `$definitions`.

Static methods `classes()`, `definition()`, `create()`, `payloadClass()`, `createPayload()`, `createViewModel()` и связанные lookup methods остаются BC facade и делегируют в `static::instance()`.

Legacy `protected const CLASSES`, `PAYLOADS`, `VIEW_MODELS` остаются только fallback для старого кода. Новый scaffold должен использовать protected typed properties, а не static/const registry.

`protected const DOMAIN` в `Handler`, `Service`, `Repository`, `Producer`, `Killer`, `DataProvider`, `QueryStorage` и controller-классах — это только pointer на Domain-класс, не Domain registry и не возврат к static/const registry.

`$definitions` предназначен для infrastructure config overrides. Top-level `$definitions[$key]['class']` запрещён: class registry-объекта берётся из protected property. Вложенный Yii definition внутри значения вроде `queryStorage` может содержать `class`.

## Ограничения

- `BaseProducer` создает runtime-модели, search-модели и новые записи, но не содержит generic `update(...)`.
- `BaseActiveDataProvider::search()` является abstract contract: конкретный доменный DataProvider реализует Gii-like фильтры.
- `BaseWebController::display(...)` покрывает простой HTML/JSON ответ и HTTP status code для JSON. Для redirect, file response и API envelope лучше добавить собственный метод в прикладном controller.
- `runTransactional(...)` открывает одну Yii DB transaction. Nested transactions, несколько БД и after-commit hooks не реализованы.

## Документация

Полная документация по архитектуре, flow, правилам генерации и шаблонам:

- **SKILL/SKILL.md** — canonical source правил DNK flow, ответственности слоёв, антипаттернов и scaffold-шаблонов.
- **SKILL/reference.md** — краткий справочник, ссылается на SKILL/SKILL.md.
- **SKILL/CHECKLIST.md** — чеклист проверок при работе с DNK.

## AI Skill

Пакет поставляется как runtime-библиотека и как AI skill. После установки через Composer skill-файлы доступны в:

```text
vendor/andy87/yii2-dnk-base/SKILL/
```

Основной файл skill-а:

```text
vendor/andy87/yii2-dnk-base/SKILL/SKILL.md
```

## Проверки

```bash
composer check
```
