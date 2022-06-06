<?php

use common\models\Store;
use common\models\StoreProduct;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StoreProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Store Products');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="store-product-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Store Product'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Import Via CSV'), ['import-dialog'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Import Via xlsx'), ['import-dialog'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

//            'id',

            [
                'attribute' => 'store_id',
                'value' => function ($data) {
                    $store = Store::find()->where(['id' => $data['store_id']])->asArray()->one();
                    return $store['title'];
                }
            ],
            'title',
            'upc',
            'price',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, StoreProduct $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
