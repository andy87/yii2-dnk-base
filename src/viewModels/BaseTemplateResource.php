<?php

declare(strict_types=1);

namespace andy87\yii2dnk\viewModels;

/**
 * Базовый класс для web-view ответов с привязкой к шаблону.
 *
 * Добавляет константу TEMPLATE для определения view-файла.
 * Используется как базовый класс для CRUD resources (Index, Create, Update, View).
 *
 * @see BaseViewModel Родительский класс.
 */
abstract class BaseTemplateResource extends BaseViewModel
{
    /** Имя шаблона по умолчанию для представления. */
    public const TEMPLATE = 'index';
}
