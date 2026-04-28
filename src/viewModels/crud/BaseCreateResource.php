<?php

declare(strict_types=1);

namespace andy87\yii2dnk\viewModels\crud;

/**
 * Базовый resource для CRUD create-view.
 */
abstract class BaseCreateResource extends BaseFormResource
{
    /** Идентификатор шаблона для create-view. */
    public const TEMPLATE = 'create';

    /**
     * Идентификатор действия формы.
     *
     * @var string
     */
    public string $action = 'create';
}
