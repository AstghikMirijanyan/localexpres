<?php

use common\models\Queue;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\QueueSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Queues');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="queue-index">

    <h1><?= Html::encode($this->title) ?></h1>



    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            [
              'attribute' => 'shop',
                'value' => function($data){
                    return $data->getShopName();
                }
            ],
            [
                'attribute' => 'product_count',
                'value' => function($data){
                    return $data->getProductCount();
                }
            ],

            [
                'attribute' => 'progress_status',
                'format' => 'raw',
                'value' => function ($data) {
                    switch (true) {
                        case (Yii::$app->cache->exists(sprintf('__%d__', $data->id))):
//                                        $html = equal(sprintf('<span class="status__tag status__tag_final">%s %s</span>', Yii::$app->cache->get(sprintf('__%d__', $data->id)), '%'));
                            $html = sprintf('<progress class="progress-column" max="100" value="%d" data-label="%d%s"></progress>', Yii::$app->cache->get(sprintf('__%d__', $data->id)), !empty(Yii::$app->cache->get(sprintf('__%d__', $data->id))["percent"])?Yii::$app->cache->get(sprintf('__%d__', $data->id))["percent"]:null, '%');
                            break;
                        case ($data->attempt > 0):
                            $html = '<span class="status__tag status__tag_final">running</span>';
                            break;
                        default:
                            $html = '<span class="status__tag status__tag_ful">waiting</span>';
                            break;
                    }

                    return $html;
                }


            ],

            'channel',
//            'job',
            'pushed_at',
            'ttr',
            'delay',
            'priority',
            'reserved_at',
            'attempt',
            'done_at',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Queue $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
