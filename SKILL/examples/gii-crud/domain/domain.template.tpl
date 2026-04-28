<?php

declare(strict_types=1);

namespace {{domainNamespace}};

use andy87\yii2dnk\BasePayload;
use andy87\yii2dnk\domain\BaseDomain;
use andy87\yii2dnk\viewModels\BaseViewModel;
use {{dataProviderFqcn}};
use {{handlerFqcn}};
use {{killerFqcn}};
use {{modelFqcn}};
use {{producerFqcn}};
use {{repositoryFqcn}};
use {{searchFqcn}};
use {{serviceFqcn}};
use {{indexPayloadFqcn}};
use {{createPayloadFqcn}};
use {{updatePayloadFqcn}};
use {{viewPayloadFqcn}};
use {{deletePayloadFqcn}};
use {{indexResourceFqcn}};
use {{createResourceFqcn}};
use {{updateResourceFqcn}};
use {{viewResourceFqcn}};

/**
 * Описание класса {{domainClass}}.
 *
 * Реестр классов домена {{domainName}} и CRUD mapping action -> payload/view model.
 */
class {{domainClass}} extends BaseDomain
{
    /** @var class-string<{{modelClass}}> */
    protected string $model = {{modelClass}}::class;

    /** @var class-string<{{searchClass}}>|null */
    protected ?string $searchModel = {{searchClass}}::class;

    /** @var class-string<{{handlerClass}}> */
    protected string $handler = {{handlerClass}}::class;

    /** @var class-string<{{serviceClass}}> */
    protected string $service = {{serviceClass}}::class;

    /** @var class-string<{{repositoryClass}}> */
    protected string $repository = {{repositoryClass}}::class;

    /** @var class-string<{{producerClass}}> */
    protected string $producer = {{producerClass}}::class;

    /** @var class-string<{{killerClass}}> */
    protected string $killer = {{killerClass}}::class;

    /**
     * @var class-string<\{{queryStorageFqcn}}>|null
     * Задай \{{queryStorageFqcn}}::class, если репозиторию нужны нативные SQL-запросы.
     */
    protected ?string $queryStorage = null;

    /** @var class-string<{{dataProviderClass}}>|null */
    protected ?string $dataProvider = {{dataProviderClass}}::class;

    /** @var array<string, array<string, mixed>> Config overrides без top-level ключа class. */
    protected array $definitions = [
        // self::HANDLER => ['db' => 'dbReporting'],
        // self::DATA_PROVIDER => ['pageSize' => 50],
        // self::QUERY_STORAGE => ['db' => 'dbReporting'],
        // self::REPOSITORY => ['queryStorage' => ['class' => \{{queryStorageFqcn}}::class, 'db' => 'dbReporting']],
    ];

    /** @var array<string, class-string<BasePayload>> */
    protected array $payloads = [
        self::ACTION_INDEX => {{indexPayloadClass}}::class,
        self::ACTION_CREATE => {{createPayloadClass}}::class,
        self::ACTION_UPDATE => {{updatePayloadClass}}::class,
        self::ACTION_VIEW => {{viewPayloadClass}}::class,
        self::ACTION_DELETE => {{deletePayloadClass}}::class,
    ];

    /** @var array<string, class-string<BaseViewModel>> */
    protected array $viewModels = [
        self::ACTION_INDEX => {{indexResourceClass}}::class,
        self::ACTION_CREATE => {{createResourceClass}}::class,
        self::ACTION_UPDATE => {{updateResourceClass}}::class,
        self::ACTION_VIEW => {{viewResourceClass}}::class,
    ];
}
