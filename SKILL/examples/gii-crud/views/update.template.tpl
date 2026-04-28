<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var {{domainName}}UpdateResource $resource */
/** @var {{modelClass}} $model */

$this->title = 'Update {{domainName}}: ' . $model->id;
extract($resource->release());

?>

<div class="{{domainName}}-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
