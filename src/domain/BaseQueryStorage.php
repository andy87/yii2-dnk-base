<?php

declare(strict_types=1);

namespace andy87\yii2dnk\domain;

use andy87\yii2dnk\DomainAwareTrait;
use yii\base\BaseObject;

/**
 * Базовое хранилище нативных SQL-запросов для DNK flow.
 *
 * Разделяет ActiveRecord-конструкции (ActiveQuery в Repository) и чистый SQL.
 * Подклассы определяют методы, возвращающие SQL-строки для сложных отчётов,
 * аналитики и других запросов, которые неудобно или невозможно выразить через AR.
 *
 * Repository получает экземпляр через queryStorage definition домена и выполняет SQL
 * через методы execSql($sql, $params) / execSqlOne($sql, $params) из BaseRepository.
 *
 * Пример использования в ItemRepository:
 *
 * ```php
 * [$sql, $params] = $this->queryStorage->getSqlWeeklyReport($filter);
 * return $this->execSql($sql, $params);
 * ```
 *
 * @property string $db Имя компонента БД приложения (по умолчанию 'db').
 */
abstract class BaseQueryStorage extends BaseObject
{
    use DomainAwareTrait;

    /**
     * Явно указанный класс реестра домена.
     * Если пустая строка — определяется автоматически через DomainAwareTrait.
     *
     * @var class-string<BaseDomain>|''
     */
    protected const DOMAIN = '';

    /**
     * Имя компонента БД приложения, через который выполняются запросы.
     *
     * @var string
     */
    public string $db = 'db';
}
