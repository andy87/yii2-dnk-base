Отвечай на русском.

# DNK Domain Registry — обязательный контекст

Этот пакет намеренно переведён от прежнего основного `static const CLASSES/PAYLOADS/VIEW_MODELS` к Domain registry через `protected typed properties` в `BaseDomain`.

## Почему реализация именно такая

- `BaseDomain` остаётся совместимым с текущим runtime API: `ItemDomain::create(...)`, `definition(...)`, `payloadClass(...)`, `createPayload(...)`, `createViewModel(...)` должны продолжать работать.
- Static facade в `BaseDomain` — это BC bridge для старого кода, а не основной стиль новой разработки.
- `protected const DOMAIN` в Handler/Service/Repository/etc — это pointer на Domain-класс, не Domain registry и не возврат к static registry.
- Новый основной стиль — instance-based Domain registry через protected properties:
  - `$model`, `$handler`, `$service`, `$repository`, `$producer`, `$killer`;
  - optional `$searchModel`, `$dataProvider`, `$queryStorage`;
  - mappings `$payloads`, `$viewModels`;
  - infrastructure config `$definitions`.
- Protected properties выбраны вместо public properties, чтобы сохранить возможность подмены через наследование и Yii DI, но не открыть случайную runtime-мутацию извне.
- Typed properties и `@var class-string<...>` выбраны для статического анализа и понятного контракта генерации.
- Говорящие `InvalidConfigException` в `BaseDomain` нужны вместо сырых PHP typed-property errors.
- `$definitions` предназначен только для config overrides создаваемых через Yii объектов (`db`, `pageSize`, `criteria`, `queryStorage`), а не для подмены классов и не для request/body/business data. Top-level `$definitions[$key]['class']` запрещён; вложенный Yii definition внутри значения вроде `queryStorage` может содержать `class`.
- `BaseDomain::instance()` намеренно не кэширует Domain object: это сохраняет смену Yii DI mapping между вызовами в тестах, dev и playground сценариях.
- Подмена классов для mocks/dev/playground должна делаться через наследование Domain-класса или Yii DI mapping самого Domain.
- Domain-классы не должны быть `final`, если проекту нужна dev/mock/playground-подмена через наследование.

## Что нельзя откатывать без отдельного решения

- Не возвращай новый scaffold к `protected const CLASSES`, `PAYLOADS`, `VIEW_MODELS`.
- Не делай registry через голые public mutable properties.
- Не переноси registry в большой Yii container config: цель registry flow — держать конфигурацию рядом с доменом и не создавать длинную внешнюю config-бороду.
- Не меняй публичный контракт `DomainAwareTrait::domainClass()` — он должен возвращать `class-string<BaseDomain>`.
- Не удаляй legacy const fallback в первом рефакторинге: он нужен для обратной совместимости.
- Не используй top-level `$definitions[$key]['class']` как способ подмены класса; `BaseDomain::getDefinition()` должен брать `class` из protected property. Вложенный Yii definition внутри значения вроде `queryStorage` может содержать `class`.

## Как поддерживать это дальше

- При изменении `BaseDomain` синхронизируй scaffold-шаблоны и skill-доки.
- При изменении registry API обновляй проверки в `tests/BaseDomainRegistryTest.php`.
- Новый Domain scaffold должен генерировать protected typed properties и `$definitions`.
- Legacy `CLASSES/PAYLOADS/VIEW_MODELS` допустимы только как fallback для старого кода.
- `tests/BaseDomainRegistryTest.php` намеренно содержит legacy fixture с `CLASSES/PAYLOADS/VIEW_MODELS`; это проверка BC, а не пример нового scaffold.
- После правок проверяй:
  - `php -l` по `src/**/*.php`;
  - `php tests/BaseDomainRegistryTest.php`;
  - `composer validate --no-check-publish composer.json`.
