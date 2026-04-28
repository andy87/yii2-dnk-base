<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var {{domainName}}IndexResource $resource */
/** @var {{searchClass}} $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = '{{domainName}} list';
extract($resource->release());

?>

<div class="{{domainName}}-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create {{domainName}}', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            {{modelClass}}::ATTR_ID,

            // Добавляй реальные колонки домена здесь:
            // {{modelClass}}::ATTR_STATUS => 'status',
            // [
            //     'attribute' => {{modelClass}}::ATTR_STATUS,
            //     'value' => static fn({{modelClass}} $model) => $model->getStatusLabel(),
            //     'filter' => {{modelClass}}::getStatusList(),
            // ],
            // {{modelClass}}::ATTR_CREATED_AT . ':datetime',

            [
                'class' => 'yii\grid\ActionColumn',
            ],
        ],
    ]) ?>

</div>
