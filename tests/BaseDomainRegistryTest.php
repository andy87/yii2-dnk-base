<?php

declare(strict_types=1);

/**
 * Dependency-free smoke/unit test для DNK Domain registry.
 *
 * Файл намеренно содержит локальные stub-классы Yii, чтобы проверять BaseDomain
 * без vendor-директории и без отдельного test framework.
 */

namespace {
    final class Yii
    {
        public static function createObject(string|array $definition, array $params = []): object
        {
            $containerDefinitions = $GLOBALS['yiiCreateObjectDefinitions'] ?? [];

            if (is_string($definition) && array_key_exists($definition, $containerDefinitions)) {
                $definition = $containerDefinitions[$definition];
            }

            if (is_string($definition)) {
                return new $definition(...$params);
            }

            $class = $definition['class'] ?? null;

            if (!is_string($class)) {
                throw new \yii\base\InvalidConfigException('Object definition must contain class.');
            }

            unset($definition['class']);

            return new $class($definition);
        }

        public static function define(string $class, string|array|null $definition): void
        {
            if (!isset($GLOBALS['yiiCreateObjectDefinitions']) || !is_array($GLOBALS['yiiCreateObjectDefinitions'])) {
                $GLOBALS['yiiCreateObjectDefinitions'] = [];
            }

            if ($definition === null) {
                unset($GLOBALS['yiiCreateObjectDefinitions'][$class]);

                return;
            }

            $GLOBALS['yiiCreateObjectDefinitions'][$class] = $definition;
        }
    }
}

namespace yii\base {
    class InvalidConfigException extends \Exception
    {
    }

    class BaseObject
    {
        public function __construct(array $config = [])
        {
            foreach ($config as $name => $value) {
                $this->{$name} = $value;
            }

            $this->init();
        }

        public function init(): void
        {
        }
    }

    class Model extends BaseObject
    {
        public function load(array $data, ?string $formName = null): bool
        {
            $values = $formName === '' ? $data : ($data[$formName] ?? $data);

            if (!is_array($values)) {
                return false;
            }

            foreach ($values as $name => $value) {
                $this->{$name} = $value;
            }

            return true;
        }

        public function validate(?array $attributeNames = null, bool $clearErrors = true): bool
        {
            return true;
        }

        public function getErrors(): array
        {
            return [];
        }

        public function toArray(): array
        {
            return get_object_vars($this);
        }
    }
}

namespace yii\db {
    class ActiveRecord extends \yii\base\Model
    {
    }

    class ActiveQuery
    {
    }

    class Connection
    {
    }
}

namespace yii\data {
    class ActiveDataProvider
    {
    }
}

namespace {
    require_once __DIR__ . '/../src/DomainAwareTrait.php';
    require_once __DIR__ . '/../src/ModelClassTrait.php';
    require_once __DIR__ . '/../src/BasePayload.php';
    require_once __DIR__ . '/../src/viewModels/BaseViewModel.php';
    require_once __DIR__ . '/../src/domain/BaseRepository.php';
    require_once __DIR__ . '/../src/domain/BaseProducer.php';
    require_once __DIR__ . '/../src/domain/BaseKiller.php';
    require_once __DIR__ . '/../src/domain/BaseQueryStorage.php';
    require_once __DIR__ . '/../src/domain/BaseService.php';
    require_once __DIR__ . '/../src/domain/BaseHandler.php';
    require_once __DIR__ . '/../src/domain/BaseActiveDataProvider.php';
    require_once __DIR__ . '/../src/domain/BaseDomain.php';
}

namespace andy87\yii2dnk\tests\fixtures {
    use andy87\yii2dnk\BasePayload;
    use andy87\yii2dnk\domain\BaseActiveDataProvider;
    use andy87\yii2dnk\domain\BaseDomain;
    use andy87\yii2dnk\domain\BaseHandler;
    use andy87\yii2dnk\domain\BaseKiller;
    use andy87\yii2dnk\domain\BaseProducer;
    use andy87\yii2dnk\domain\BaseQueryStorage;
    use andy87\yii2dnk\domain\BaseRepository;
    use andy87\yii2dnk\domain\BaseService;
    use andy87\yii2dnk\viewModels\BaseViewModel;
    use yii\data\ActiveDataProvider;
    use yii\db\ActiveRecord;

    class Item extends ActiveRecord
    {
    }

    final class ItemSearchModel extends Item
    {
    }

    final class ItemPayload extends BasePayload
    {
        public ?int $id = null;
    }

    final class ItemResource extends BaseViewModel
    {
    }

    final class ItemHandler extends BaseHandler
    {
        protected function provider(BasePayload $payload, ?BaseViewModel $viewModel = null): BaseViewModel|bool|array|null
        {
            return $viewModel;
        }
    }

    final class ItemService extends BaseService
    {
    }

    final class ItemRepository extends BaseRepository
    {
    }

    final class ItemMockRepository extends BaseRepository
    {
        protected const DOMAIN = PlaygroundDomain::class;
    }

    final class QueryStorageRepository extends BaseRepository
    {
        protected const DOMAIN = QueryStorageDomain::class;
    }

    final class ItemProducer extends BaseProducer
    {
    }

    final class ItemKiller extends BaseKiller
    {
    }

    final class ItemQueryStorage extends BaseQueryStorage
    {
    }

    final class InvalidPayloadClass extends ActiveRecord
    {
    }

    final class InvalidViewModelClass extends ActiveRecord
    {
    }

    final class ItemDataProvider extends BaseActiveDataProvider
    {
        public function search(array $params = []): ActiveDataProvider
        {
            return $this->getDataProvider();
        }
    }

    class RegistryDomain extends BaseDomain
    {
        /** @var class-string<Item> */
        protected string $model = Item::class;

        /** @var class-string<ItemSearchModel>|null */
        protected ?string $searchModel = ItemSearchModel::class;

        /** @var class-string<ItemDataProvider>|null */
        protected ?string $dataProvider = ItemDataProvider::class;

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

        /** @var array<string, class-string<BasePayload>> */
        protected array $payloads = [
            self::ACTION_VIEW => ItemPayload::class,
        ];

        /** @var array<string, class-string<BaseViewModel>> */
        protected array $viewModels = [
            self::ACTION_VIEW => ItemResource::class,
        ];

        /** @var array<string, array<string, mixed>> */
        protected array $definitions = [
            self::HANDLER => [
                'db' => 'dbReporting',
            ],
            self::DATA_PROVIDER => [
                'pageSize' => 50,
                'criteria' => [
                    'active' => 1,
                ],
            ],
        ];
    }

    class PlaygroundDomain extends RegistryDomain
    {
        /** @var class-string<ItemMockRepository> */
        protected string $repository = ItemMockRepository::class;
    }

    class QueryStorageDomain extends RegistryDomain
    {
        /** @var class-string<QueryStorageRepository> */
        protected string $repository = QueryStorageRepository::class;

        /** @var class-string<ItemQueryStorage>|null */
        protected ?string $queryStorage = ItemQueryStorage::class;

        /** @var array<string, array<string, mixed>> */
        protected array $definitions = [
            self::QUERY_STORAGE => [
                'db' => 'dbAnalytics',
            ],
        ];
    }

    class RepositoryQueryStorageConfigDomain extends RegistryDomain
    {
        /** @var array<string, array<string, mixed>> */
        protected array $definitions = [
            self::REPOSITORY => [
                'queryStorage' => [
                    'class' => ItemQueryStorage::class,
                    'db' => 'dbArchive',
                ],
            ],
        ];
    }

    class MissingHandlerDomain extends BaseDomain
    {
        protected string $model = Item::class;
        protected string $service = ItemService::class;
        protected string $repository = ItemRepository::class;
        protected string $producer = ItemProducer::class;
        protected string $killer = ItemKiller::class;
    }

    class InvalidHandlerDomain extends BaseDomain
    {
        protected string $model = Item::class;
        protected string $handler = Item::class;
        protected string $service = ItemService::class;
        protected string $repository = ItemRepository::class;
        protected string $producer = ItemProducer::class;
        protected string $killer = ItemKiller::class;
    }

    class InvalidDefinitionClassDomain extends RegistryDomain
    {
        /** @var array<string, array<string, mixed>> */
        protected array $definitions = [
            self::HANDLER => [
                'class' => ItemRepository::class,
            ],
        ];
    }

    class InvalidPayloadMappingDomain extends RegistryDomain
    {
        /** @var array<string, class-string<BasePayload>> */
        protected array $payloads = [
            self::ACTION_VIEW => InvalidPayloadClass::class,
        ];
    }

    class InvalidViewModelMappingDomain extends RegistryDomain
    {
        /** @var array<string, class-string<BaseViewModel>> */
        protected array $viewModels = [
            self::ACTION_VIEW => InvalidViewModelClass::class,
        ];
    }

    /**
     * Fixture для проверки обратной совместимости const registry.
     *
     * Новый scaffold не должен использовать CLASSES/PAYLOADS/VIEW_MODELS,
     * но runtime обязан поддерживать этот fallback для старых доменов.
     */
    class LegacyDomain extends BaseDomain
    {
        protected const CLASSES = [
            self::MODEL => Item::class,
            self::SEARCH_MODEL => ItemSearchModel::class,
            self::DATA_PROVIDER => ItemDataProvider::class,
            self::HANDLER => ItemHandler::class,
            self::SERVICE => ItemService::class,
            self::REPOSITORY => ItemRepository::class,
            self::PRODUCER => ItemProducer::class,
            self::KILLER => ItemKiller::class,
        ];

        protected const PAYLOADS = [
            self::ACTION_VIEW => ItemPayload::class,
        ];

        protected const VIEW_MODELS = [
            self::ACTION_VIEW => ItemResource::class,
        ];
    }
}

namespace {
    use andy87\yii2dnk\domain\BaseDomain;
    use andy87\yii2dnk\tests\fixtures\InvalidHandlerDomain;
    use andy87\yii2dnk\tests\fixtures\InvalidDefinitionClassDomain;
    use andy87\yii2dnk\tests\fixtures\InvalidPayloadMappingDomain;
    use andy87\yii2dnk\tests\fixtures\InvalidViewModelMappingDomain;
    use andy87\yii2dnk\tests\fixtures\ItemDataProvider;
    use andy87\yii2dnk\tests\fixtures\ItemHandler;
    use andy87\yii2dnk\tests\fixtures\ItemPayload;
    use andy87\yii2dnk\tests\fixtures\ItemQueryStorage;
    use andy87\yii2dnk\tests\fixtures\ItemRepository;
    use andy87\yii2dnk\tests\fixtures\ItemResource;
    use andy87\yii2dnk\tests\fixtures\ItemSearchModel;
    use andy87\yii2dnk\tests\fixtures\LegacyDomain;
    use andy87\yii2dnk\tests\fixtures\MissingHandlerDomain;
    use andy87\yii2dnk\tests\fixtures\PlaygroundDomain;
    use andy87\yii2dnk\tests\fixtures\QueryStorageDomain;
    use andy87\yii2dnk\tests\fixtures\QueryStorageRepository;
    use andy87\yii2dnk\tests\fixtures\RegistryDomain;
    use andy87\yii2dnk\tests\fixtures\RepositoryQueryStorageConfigDomain;
    use yii\base\InvalidConfigException;

    function assertSameValue(mixed $expected, mixed $actual, string $message): void
    {
        if ($expected !== $actual) {
            throw new RuntimeException($message);
        }
    }

    function assertTrueValue(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }

    function assertFalseValue(bool $condition, string $message): void
    {
        if ($condition) {
            throw new RuntimeException($message);
        }
    }

    function assertThrowsConfig(string $messagePart, callable $callback): void
    {
        try {
            $callback();
        } catch (InvalidConfigException $exception) {
            if (!str_contains($exception->getMessage(), $messagePart)) {
                throw new RuntimeException('Unexpected exception message: ' . $exception->getMessage());
            }

            return;
        }

        throw new RuntimeException('Expected InvalidConfigException was not thrown.');
    }

    $handlerDefinition = RegistryDomain::definition(BaseDomain::HANDLER);
    assertTrueValue(is_array($handlerDefinition), 'Handler definition must include config array.');
    assertSameValue(ItemHandler::class, $handlerDefinition['class'], 'Handler class must come from protected property.');
    assertSameValue('dbReporting', $handlerDefinition['db'], 'Handler config must be preserved.');

    $handler = RegistryDomain::create(BaseDomain::HANDLER);
    assertTrueValue($handler instanceof ItemHandler, 'Handler must be created via Domain registry.');
    assertSameValue('dbReporting', $handler->db, 'Handler db config must be injected.');

    $dataProviderDefinition = RegistryDomain::definition(BaseDomain::DATA_PROVIDER);
    assertSameValue(ItemDataProvider::class, $dataProviderDefinition['class'], 'DataProvider class must come from property.');
    assertSameValue(50, $dataProviderDefinition['pageSize'], 'DataProvider config must be preserved.');
    assertSameValue(['active' => 1], $dataProviderDefinition['criteria'], 'DataProvider criteria config must be preserved.');

    $dataProvider = RegistryDomain::create(BaseDomain::DATA_PROVIDER);
    assertTrueValue($dataProvider instanceof ItemDataProvider, 'DataProvider must be created via Domain registry.');
    assertSameValue(50, $dataProvider->pageSize, 'DataProvider pageSize config must be injected.');
    assertSameValue(['active' => 1], $dataProvider->criteria, 'DataProvider criteria config must be injected.');

    assertTrueValue(PlaygroundDomain::create(BaseDomain::REPOSITORY) instanceof \andy87\yii2dnk\tests\fixtures\ItemMockRepository, 'Subclassed Domain must override registry property.');
    \Yii::define(RegistryDomain::class, PlaygroundDomain::class);

    try {
        assertTrueValue(RegistryDomain::create(BaseDomain::REPOSITORY) instanceof \andy87\yii2dnk\tests\fixtures\ItemMockRepository, 'Yii DI Domain remap must override registry property.');
    } finally {
        \Yii::define(RegistryDomain::class, null);
    }

    assertFalseValue(RegistryDomain::hasDefinition(BaseDomain::QUERY_STORAGE), 'Missing optional queryStorage must not be a definition.');
    assertThrowsConfig('has no "queryStorage" definition', static fn (): string|array => RegistryDomain::definition(BaseDomain::QUERY_STORAGE));

    $queryStorageRepository = QueryStorageDomain::create(BaseDomain::REPOSITORY);
    assertTrueValue($queryStorageRepository instanceof QueryStorageRepository, 'Repository must be created for queryStorage Domain.');
    assertTrueValue($queryStorageRepository->queryStorage instanceof ItemQueryStorage, 'Repository must create queryStorage from Domain registry.');
    assertSameValue('dbAnalytics', $queryStorageRepository->queryStorage->db, 'QueryStorage db config must be injected from Domain registry.');

    $configuredRepository = RepositoryQueryStorageConfigDomain::create(BaseDomain::REPOSITORY);
    assertTrueValue($configuredRepository instanceof ItemRepository, 'Repository config override must create repository.');
    assertTrueValue($configuredRepository->queryStorage instanceof ItemQueryStorage, 'Repository config override must normalize queryStorage definition.');
    assertSameValue('dbArchive', $configuredRepository->queryStorage->db, 'Repository queryStorage config must be preserved.');

    assertSameValue(ItemPayload::class, RegistryDomain::payloadClass(BaseDomain::ACTION_VIEW), 'Payload lookup must use registry mapping.');
    assertSameValue(ItemResource::class, RegistryDomain::viewModelClass(BaseDomain::ACTION_VIEW), 'ViewModel lookup must use registry mapping.');

    $payload = RegistryDomain::createPayload(BaseDomain::ACTION_VIEW, ['id' => 42]);
    assertTrueValue($payload instanceof ItemPayload, 'Payload must be created via Yii DI.');
    assertSameValue(42, $payload->id, 'Payload data must be loaded.');

    $viewModel = RegistryDomain::createViewModelForPayload($payload);
    assertTrueValue($viewModel instanceof ItemResource, 'ViewModel must be resolved by payload class.');

    assertThrowsConfig('uninitialized required property "$handler"', static fn (): string|array => MissingHandlerDomain::definition(BaseDomain::HANDLER));
    assertThrowsConfig('uninitialized required property "$handler"', static fn (): array => MissingHandlerDomain::classes());
    assertThrowsConfig('must be class-string', static fn (): string|array => InvalidHandlerDomain::definition(BaseDomain::HANDLER));
    assertThrowsConfig('must not contain top-level "class"', static fn (): string|array => InvalidDefinitionClassDomain::definition(BaseDomain::HANDLER));
    assertThrowsConfig('payload for action "view" must be class-string', static fn (): string => InvalidPayloadMappingDomain::payloadClass(BaseDomain::ACTION_VIEW));
    assertThrowsConfig('view model for action "view" must be class-string', static fn (): string => InvalidViewModelMappingDomain::viewModelClass(BaseDomain::ACTION_VIEW));

    assertTrueValue(LegacyDomain::create(BaseDomain::HANDLER) instanceof ItemHandler, 'Legacy CLASSES fallback must create handler.');
    assertTrueValue(LegacyDomain::hasDefinition(BaseDomain::SEARCH_MODEL), 'Legacy optional SEARCH_MODEL fallback must be visible.');
    assertSameValue(ItemSearchModel::class, LegacyDomain::definition(BaseDomain::SEARCH_MODEL), 'Legacy SEARCH_MODEL fallback must work.');
    assertSameValue(ItemDataProvider::class, LegacyDomain::definition(BaseDomain::DATA_PROVIDER), 'Legacy DATA_PROVIDER fallback must work.');
    assertSameValue(ItemPayload::class, LegacyDomain::payloadClass(BaseDomain::ACTION_VIEW), 'Legacy PAYLOADS fallback must work.');
    assertSameValue(ItemResource::class, LegacyDomain::viewModelClass(BaseDomain::ACTION_VIEW), 'Legacy VIEW_MODELS fallback must work.');

    echo "BaseDomain registry tests passed.\n";
}
