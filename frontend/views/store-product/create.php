<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\StoreProduct */

$this->title = Yii::t('app', 'Create Store Product');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Store Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="store-product-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
