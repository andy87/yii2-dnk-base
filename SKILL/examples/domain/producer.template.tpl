<?php

declare(strict_types=1);

namespace {{producerNamespace}};

use andy87\yii2dnk\domain\BaseProducer;
use {{domainFqcn}};

/**
 * Описание класса {{producerClass}}.
 *
 * Producer домена {{domainName}}. Содержит create/save-логику,
 * специфичную для домена.
 */
final class {{producerClass}} extends BaseProducer
{
    protected const DOMAIN = {{domainClass}}::class;
}
