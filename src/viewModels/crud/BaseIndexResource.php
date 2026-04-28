<?php

declare(strict_types=1);

namespace andy87\yii2dnk\viewModels\crud;

use andy87\yii2dnk\viewModels\BaseTemplateResource;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * Базовый resource для CRUD index-view.
 *
 * Передаёт в `index.php` поисковую модель и готовый Yii ActiveDataProvider.
 * SearchModel в DNK наследуется от боевой модели домена, поэтому тип
 * здесь — ActiveRecord, совместимый с BaseActiveDataProvider::getSearchModel().
 */
abstract class BaseIndexResource extends BaseTemplateResource
{
    /** Идентификатор шаблона для index-view. */
    public const TEMPLATE = 'index';

    /**
     * Поисковая модель для фильтрации GridView.
     *
     * @var ActiveRecord
     */
    public ActiveRecord $searchModel;

    /**
     * Готовый provider данных для GridView.
     *
     * @var ActiveDataProvider
     */
    public ActiveDataProvider $dataProvider;

    /**
     * Описание метода rules.
     *
     * Назначение: разрешить передачу searchModel и dataProvider во view.
     *
     * @return array<int, array<int|string, mixed>> Правила валидации resource.
     */
    public function rules(): array
    {
        return [
            [['searchModel', 'dataProvider'], 'safe'],
        ];
    }
}
