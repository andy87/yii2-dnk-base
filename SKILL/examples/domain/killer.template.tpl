<?php

declare(strict_types=1);

namespace {{killerNamespace}};

use andy87\yii2dnk\domain\BaseKiller;
use {{domainFqcn}};

/**
 * Описание класса {{killerClass}}.
 *
 * Killer домена {{domainName}}. Содержит delete/soft-delete логику,
 * специфичную для домена.
 */
final class {{killerClass}} extends BaseKiller
{
    protected const DOMAIN = {{domainClass}}::class;
}
