# DNK Examples

Рабочие scaffold-шаблоны находятся в `examples/`.

Файлы используют placeholders вида `{{domainName}}`, `{{domainClass}}`, `{{modelFqcn}}`.
Перед вставкой в проект placeholders должны быть заменены конкретными namespace, class name и FQCN.

Base-уровень не копируется в проект. Все шаблоны наследуются от классов composer-пакета `andy87/yii2-dnk-base`.

## Группы шаблонов

- `examples/domain/` — доменный слой: Domain, Handler, Service, Repository, Producer, Killer, QueryStorage, DataProvider, Payload, Models.
- `examples/controller/` — минимальные web/console controller templates.
- `examples/viewModel/` — CRUD resource classes для index/create/update/view.
- `examples/app/` — app-level `BaseHandlerController`. Общий web-шаблон в `common/`, обёртки для `backend/` и `frontend/`, console-шаблон в `console/`.
- `examples/gii-crud/` — пример интеграции DNK с CRUD, сгенерированным стандартным Gii. Включает доменный killer, payload-ы для CRUD-действий и view-шаблоны в `views/`.
