Отвечай на русском.

# DNK skill/docs — обязательный контекст

Документация и scaffold-шаблоны должны описывать Domain registry через protected typed properties как основной стиль. Не возвращай документацию к const registry flow без отдельного решения.

## Почему текущий flow такой

- Runtime `BaseDomain` теперь использует protected typed properties как основной registry.
- Старые `protected const CLASSES/PAYLOADS/VIEW_MODELS` — только legacy fallback для обратной совместимости.
- `protected const DOMAIN` в Handler/Service/Repository/etc — это pointer на Domain-класс, не Domain registry.
- Новый scaffold должен быть удобен для static analysis, mocks, dev/playground-подмен и контроля конфигурации рядом с доменом.
- Подмена классов должна делаться через наследование Domain-класса или Yii DI mapping самого Domain, а не через длинный Yii container config.
- `$definitions` — только config overrides создаваемых через Yii объектов, например `db`, `pageSize`, `criteria`, `queryStorage`; top-level `$definitions[$key]['class']` запрещён, вложенный Yii definition внутри значения вроде `queryStorage` может содержать `class`.
- `BaseDomain::instance()` не кэширует Domain object, чтобы Yii DI mapping можно было менять между вызовами в тестах, dev и playground сценариях.
- Domain-классы не должны быть `final`, если нужна dev/mock/playground-подмена.

## Что нельзя генерировать как основной стиль

- Не генерируй `protected const CLASSES`, `PAYLOADS`, `VIEW_MODELS` в новых Domain templates.
- Не описывай `CLASSES` как основной registry в `SKILL.md`, `reference.md`, `CHECKLIST.md`.
- Не советуй подменять registry-классы через top-level `$definitions[$key]['class']`; вложенный Yii definition внутри значения вроде `queryStorage` может содержать `class`.
- Не предлагай голые public mutable registry properties.
- Не выноси registry в большой Yii container config как основной путь.
- Не считай `CLASSES/PAYLOADS/VIEW_MODELS` в `tests/BaseDomainRegistryTest.php` ошибкой: это fixture для проверки legacy BC fallback.

## Что синхронизировать при изменениях

- Если меняется `BaseDomain` runtime API — обнови секцию API Base-классов в `SKILL.md`.
- Если меняется Domain registry style — обнови:
  - `examples/domain/domain.template.tpl`;
  - `examples/gii-crud/domain/domain.template.tpl`;
  - `SKILL.md`;
  - `reference.md`;
  - `CHECKLIST.md`.
- После правок проверяй `quick_validate.py /mnt/c/AI/SKILLS/shared/yii2/dnk` и отсутствие CRLF.
