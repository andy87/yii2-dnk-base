<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var {{domainName}}CreateResource $resource */
/** @var {{modelClass}} $model */

$this->title = 'Create {{domainName}}';
extract($resource->release());

?>

<div class="{{domainName}}-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
