<?php

declare(strict_types=1);

namespace {{repositoryNamespace}};

use andy87\yii2dnk\domain\BaseRepository;
use {{domainFqcn}};

/**
 * Описание класса {{repositoryClass}}.
 *
 * Repository домена {{domainName}}. Содержит read/query-логику:
 * - ActiveRecord-запросы через query(), findOne(), findAll() и т.д.
 * - Нативные SQL-запросы через $this->queryStorage + execSql($sql, $params).
 *   Доступно когда $queryStorage задан в Domain registry или Repository config override.
 *
 * Базовые методы BaseRepository: query(), findOne(), findById(), findOrFail(),
 * findAll(), exists(), count(), queryForGrid(). Переопределяй только если нужна
 * кастомная логика.
 */
final class {{repositoryClass}} extends BaseRepository
{
    protected const DOMAIN = {{domainClass}}::class;

    // Добавляй специфичные query-методы домена при необходимости.
    // Пример с queryStorage (задай $queryStorage в Domain или self::REPOSITORY config override):
    //
    // public function findCustomReport(array $filter): array
    // {
    //     [$sql, $params] = $this->queryStorage->getSqlCustomReport($filter);
    //     return $this->execSql($sql, $params);
    // }
}
