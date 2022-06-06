<?php


namespace common\models;


use yii\base\Model;
use yii\web\UploadedFile;

class CSVUploader extends Model
{
    /**@var UploadedFile $csvFile */
    public $csvFile;
    public $store;


    public static function productImportFields()
    {
        $fields = [
            'Required' => ['upc' => 'upc'],
        ];

        $optional = [
            'ib' => 'ID',
            'price' => 'Price',
            'title' => 'Title',
        ];


        $fields['Optional'] = $optional;

        return $fields;
    }


    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['store'],'integer'],
            [['csvFile'], 'file', 'skipOnEmpty' => false, 'checkExtensionByMimeType' => false, 'extensions' => 'csv'],
            [['csvFile'], 'file', 'on' => 'manual_upload', 'maxSize' => (5 * 1024 * 1024), 'tooBig' => 'File is too big. Max uploading file size is 5MB.']
        ];
    }
}