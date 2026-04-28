<?php

declare(strict_types=1);

namespace {{dataProviderNamespace}};

use andy87\yii2dnk\domain\BaseActiveDataProvider;
use {{domainFqcn}};
use {{modelFqcn}};
use {{searchFqcn}};
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * Описание класса {{dataProviderClass}}.
 *
 * ActiveDataProvider домена {{domainName}}. Настраивает list/index сценарий:
 * критерии фильтрации, сортировку, pagination.
 */
final class {{dataProviderClass}} extends BaseActiveDataProvider
{
    protected const DOMAIN = {{domainClass}}::class;

    /**
     * Описание метода search.
     *
     * Назначение: выполнить Gii-like поиск для GridView.
     *
     * SearchModel создаётся через Producer, загружает параметры через стандартный
     * Yii formName и сохраняется для `getSearchModel()`.
     *
     * @param array<string, mixed> $params Query/request параметры поиска.
     * @return ActiveDataProvider DataProvider для списка.
     * @throws \yii\base\InvalidConfigException Если $searchModel или repository настроены некорректно.
     */
    public function search(array $params = []): ActiveDataProvider
    {
        /** @var {{searchClass}} $searchModel */
        $searchModel = $this->getProducer()->createSearchModel($params);
        $this->setSearchModel($searchModel);

        $query = $this->getQuery();

        if (!$searchModel->validate()) {
            $query->where('0=1');

            return $this->getDataProvider($query);
        }

        $query->andFilterWhere([
            {{modelClass}}::ATTR_ID => $searchModel->id,
        ]);

        // Добавляй доменные фильтры только по реально существующим колонкам:
        // $query->andFilterWhere([{{modelClass}}::ATTR_STATUS => $searchModel->status]);
        // $query->andFilterWhere(['like', {{modelClass}}::ATTR_TITLE, $searchModel->title]);

        return $this->getDataProvider($query);
    }

    /**
     * Применяет доменные критерии к запросу.
     *
     * Переопределяй для добавления WHERE-условий, ORDER BY, scopes.
     * Метод не заменяет Gii-like search(), а задаёт default scopes/conditions.
     *
     * @param ActiveQuery $query Базовый query.
     * @param array<string, mixed> $criteria Критерии фильтрации. Если пусты — используются $this->criteria.
     * @return ActiveQuery Query с критериями.
     */
    protected function applyCriteria(ActiveQuery $query, array $criteria = []): ActiveQuery
    {
        return parent::applyCriteria($query, $criteria);
    }
}
