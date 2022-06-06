<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "queue".
 *
 * @property int $id
 * @property string $channel
 * @property resource $job
 * @property int $pushed_at
 * @property int $ttr
 * @property int $delay
 * @property int $priority
 * @property int|null $reserved_at
 * @property int|null $attempt
 * @property int|null $done_at
 */
class Queue extends \yii\db\ActiveRecord
{
    public $progress_status;
    public $shop;
    public $product_count;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'queue';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['channel', 'job', 'pushed_at', 'ttr'], 'required'],
            [['job'], 'string'],
            [['pushed_at', 'ttr', 'delay', 'priority', 'reserved_at', 'attempt', 'done_at'], 'integer'],
            [['channel'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'channel' => 'Channel',
            'job' => 'Job',
            'pushed_at' => 'Pushed At',
            'ttr' => 'Ttr',
            'delay' => 'Delay',
            'priority' => 'Priority',
            'reserved_at' => 'Reserved At',
            'attempt' => 'Attempt',
            'done_at' => 'Done At',
        ];
    }

    /**
     * @return $this
     */
    public function getProgressStatus(){
        $job = unserialize($this->job);
        $this->progress_status = $job->hasProperty('progress') ? $job->progress : null;
        return $this;
    }

    /**
     * @return $this
     */
    public function getShopName(){
        $job = unserialize($this->job);
       $store =  $this->shop = $job->hasProperty('shop_name') ? $job->shop_name : null;
       if (!empty($store)){
           $st = Store::find()->where(['id'=>$store])->asArray()->one();
           return $st['title'];
       }

    }

    /**
     * @return $this
     */
    public function getProductCount(){

        $job = unserialize($this->job);
       return $this->product_count = $job->hasProperty('product_count') ? $job->product_count : null;

    }


}
