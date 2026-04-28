<?php

declare(strict_types=1);

namespace {{killerNamespace}};

use andy87\yii2dnk\domain\BaseKiller;
use {{domainFqcn}};

/**
 * Описание класса {{killerClass}}.
 *
 * Killer домена {{domainName}}. Содержит delete/soft-delete логику,
 * специфичную для CRUD-сценариев.
 *
 * Базовые методы BaseKiller: delete(), deleteAll(), useSoftDelete().
 * Переопределяй для кастомной логики удаления.
 */
final class {{killerClass}} extends BaseKiller
{
    protected const DOMAIN = {{domainClass}}::class;
}
