<?php

declare(strict_types=1);

namespace {{serviceNamespace}};

use andy87\yii2dnk\domain\BaseService;
use {{domainFqcn}};
use {{modelFqcn}};

/**
 * Описание класса {{serviceClass}}.
 *
 * Сервис домена {{domainName}}. Содержит бизнес-сценарии и использует
 * repository/producer/killer через BaseService.
 *
 * Если домен имеет list/index/search сценарий, добавь метод search()
 * из SKILL.md и задай $searchModel/$dataProvider в Domain registry.
 */
final class {{serviceClass}} extends BaseService
{
    protected const DOMAIN = {{domainClass}}::class;

    /**
     * Описание метода getById.
     *
     * Назначение: получить модель по id или выбросить NotFoundException.
     *
     * @param int $id Идентификатор модели.
     * @return {{modelClass}} Найденная модель.
     * @throws \Throwable Если repository не нашёл модель или настроен некорректно.
     */
    public function getById(int $id): {{modelClass}}
    {
        /** @var {{modelClass}} $model */
        $model = $this->getRepository()->findOrFail($id);

        return $model;
    }
}
