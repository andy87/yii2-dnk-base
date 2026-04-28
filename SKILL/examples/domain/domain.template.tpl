<?php

declare(strict_types=1);

namespace {{domainNamespace}};

use andy87\yii2dnk\BasePayload;
use andy87\yii2dnk\domain\BaseDomain;
use andy87\yii2dnk\viewModels\BaseViewModel;
use {{handlerFqcn}};
use {{killerFqcn}};
use {{modelFqcn}};
use {{payloadFqcn}};
use {{producerFqcn}};
use {{repositoryFqcn}};
use {{resourceFqcn}};
use {{serviceFqcn}};

/**
 * Описание класса {{domainClass}}.
 *
 * Реестр классов домена {{domainName}} и mapping action -> payload/view model.
 */
class {{domainClass}} extends BaseDomain
{
    /** @var class-string<{{modelClass}}> */
    protected string $model = {{modelClass}}::class;

    /**
     * @var class-string<\{{searchFqcn}}>|null
     * Задай \{{searchFqcn}}::class, если домен поддерживает list/index/search сценарий.
     */
    protected ?string $searchModel = null;

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

    /**
     * @var class-string<\{{dataProviderFqcn}}>|null
     * Задай \{{dataProviderFqcn}}::class, если домен поддерживает list/index/search сценарий.
     */
    protected ?string $dataProvider = null;

    /** @var array<string, array<string, mixed>> Config overrides без top-level ключа class. */
    protected array $definitions = [
        // self::HANDLER => ['db' => 'dbReporting'],
        // self::DATA_PROVIDER => ['pageSize' => 50],
        // self::QUERY_STORAGE => ['db' => 'dbReporting'],
        // self::REPOSITORY => ['queryStorage' => ['class' => \{{queryStorageFqcn}}::class, 'db' => 'dbReporting']],
    ];

    /** @var array<string, class-string<BasePayload>> */
    protected array $payloads = [
        '{{actionId}}' => {{payloadClass}}::class,
    ];

    /** @var array<string, class-string<BaseViewModel>> */
    protected array $viewModels = [
        '{{actionId}}' => {{resourceClass}}::class,
    ];
}
