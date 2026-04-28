<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var {{modelClass}} $model */

?>

<div class="{{domainName}}-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php // Добавляй поля формы домена: ?>
    <?php // <?= $form->field($model, {{modelClass}}::ATTR_STATUS)->dropDownList({{modelClass}}::getStatusList(), ['prompt' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
