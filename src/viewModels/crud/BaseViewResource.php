<?php

declare(strict_types=1);

namespace andy87\yii2dnk\viewModels\crud;

use andy87\yii2dnk\viewModels\BaseTemplateResource;
use yii\db\ActiveRecord;

/**
 * Базовый resource для CRUD detail-view.
 */
abstract class BaseViewResource extends BaseTemplateResource
{
    /** Идентификатор шаблона для view-view. */
    public const TEMPLATE = 'view';

    /**
     * Модель для просмотра.
     *
     * @var ActiveRecord
     */
    public ActiveRecord $model;

    /**
     * Описание метода rules.
     *
     * Назначение: разрешить передачу модели во view.
     *
     * @return array<int, array<int|string, mixed>> Правила валидации resource.
     */
    public function rules(): array
    {
        return [
            ['model', 'safe'],
        ];
    }
}
