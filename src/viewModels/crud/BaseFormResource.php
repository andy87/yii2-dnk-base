<?php

declare(strict_types=1);

namespace andy87\yii2dnk\viewModels\crud;

use andy87\yii2dnk\viewModels\BaseTemplateResource;
use yii\db\ActiveRecord;

/**
 * Базовый resource для CRUD form-view.
 *
 * Используется create/update ресурсами и передаёт во view `$model`,
 * совместимый со стандартными Gii CRUD шаблонами.
 */
abstract class BaseFormResource extends BaseTemplateResource
{
    /**
     * Идентификатор действия формы: `create` или `update`.
     *
     * @var string
     */
    public string $action = '';

    /**
     * Модель формы для create/update view.
     *
     * @var ActiveRecord
     */
    public ActiveRecord $model;

    /**
     * Флаг успешного сохранения формы.
     *
     * Handler выставляет true после успешного create/update, controller
     * использует флаг для flash-alert и redirect.
     *
     * @var bool
     */
    public bool $saved = false;

    /**
     * Возвращает правила валидации resource.
     *
     * Разрешает передачу model, action и saved во view как safe-атрибуты.
     *
     * @return array<int, array<int|string, mixed>> Правила валидации resource.
     */
    public function rules(): array
    {
        return [
            [['model', 'action', 'saved'], 'safe'],
        ];
    }
}
