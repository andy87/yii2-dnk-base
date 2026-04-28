<?php

declare(strict_types=1);

namespace andy87\yii2dnk\viewModels\crud;

/**
 * Базовый resource для CRUD update-view.
 */
abstract class BaseUpdateResource extends BaseFormResource
{
    /** Идентификатор шаблона для update-view. */
    public const TEMPLATE = 'update';

    /**
     * Идентификатор действия формы.
     *
     * @var string
     */
    public string $action = 'update';
}
