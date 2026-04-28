<?php

declare(strict_types=1);

namespace {{queryStorageNamespace}};

use andy87\yii2dnk\domain\BaseQueryStorage;
use {{domainFqcn}};

/**
 * Описание класса {{queryStorageClass}}.
 *
 * Хранилище нативных SQL-запросов домена {{domainName}}.
 * Содержит методы, возвращающие SQL-строки для сложных отчётов, аналитики
 * и других запросов, которые неудобно или невозможно выразить через ActiveRecord.
 *
 * Используется в {{repositoryClass}} через $this->queryStorage->getSql...()
 * с выполнением через $this->execSql($sql, $params).
 */
final class {{queryStorageClass}} extends BaseQueryStorage
{
    protected const DOMAIN = {{domainClass}}::class;

    /**
     * Возвращает SQL для кастомного отчёта.
     *
     * Назначение: показать контракт QueryStorage без навязывания доменных
     * таблиц, колонок и фильтров. Замени SQL и params на реальные значения
     * конкретного домена.
     *
     * @param array<string, mixed> $filter Параметры фильтрации.
     * @return array{0: string, 1: array<string, mixed>} SQL-запрос и параметры.
     */
    public function getSqlCustomReport(array $filter): array
    {
        return ['SELECT 1 AS value', []];
    }
}
