<?php

declare(strict_types=1);

namespace {{resourceNamespace}};

use andy87\yii2dnk\viewModels\crud\BaseIndexResource;
/**
 * Описание класса {{domainName}}IndexResource.
 *
 * Resource действия index домена {{domainName}}.
 * Передаёт searchModel и dataProvider в стандартный Gii GridView.
 *
 * Используется controller action index и стандартным Gii view `index.php`.
 */
final class {{domainName}}IndexResource extends BaseIndexResource
{
}
