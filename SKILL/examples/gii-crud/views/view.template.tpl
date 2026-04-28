<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var {{domainName}}ViewResource $resource */
/** @var {{modelClass}} $model */

$this->title = '{{domainName}}: ' . $model->id;
extract($resource->release());

?>

<div class="{{domainName}}-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data-method' => 'post',
            'data-confirm' => 'Are you sure?',
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            {{modelClass}}::ATTR_ID,

            // Добавляй атрибуты домена:
            // {{modelClass}}::ATTR_STATUS,
            // [
            //     'attribute' => {{modelClass}}::ATTR_STATUS,
            //     'value' => $model->getStatusLabel(),
            // ],
            // {{modelClass}}::ATTR_CREATED_AT . ':datetime',
            // {{modelClass}}::ATTR_UPDATED_AT . ':datetime',
        ],
    ]) ?>

</div>
