<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Queue */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="queue-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'channel')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'job')->textInput() ?>

    <?= $form->field($model, 'pushed_at')->textInput() ?>

    <?= $form->field($model, 'ttr')->textInput() ?>

    <?= $form->field($model, 'delay')->textInput() ?>

    <?= $form->field($model, 'priority')->textInput() ?>

    <?= $form->field($model, 'reserved_at')->textInput() ?>

    <?= $form->field($model, 'attempt')->textInput() ?>

    <?= $form->field($model, 'done_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
