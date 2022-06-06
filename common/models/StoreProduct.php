<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "store_product".
 *
 * @property int $id
 * @property int $store_id
 * @property string|null $title
 * @property string $upc
 * @property float|null $price
 *
 * @property Store $store
 */
class StoreProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'store_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['store_id', 'upc'], 'required'],
            [['store_id'], 'integer'],
            [['price'], 'number'],
            [['title', 'upc'], 'string', 'max' => 255],
            [['store_id'], 'exist', 'skipOnError' => true, 'targetClass' => Store::className(), 'targetAttribute' => ['store_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'store_id' => 'Store ID',
            'title' => 'Title',
            'upc' => 'Upc',
            'price' => 'Price',
        ];
    }

    /**
     * @param $filePath
     * @param $post_data
     * @param $is_test
     * @param $delimiter
     * @return array
     */
    public static function readCsvImportFile($filePath, $post_data, $is_test = false, $delimiter = ',')
    {
        $file_data = [
            'headers' => [],
            'data' => []
        ];
        $row = 1;
        $products = $fields = [];
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
            $row_count = count($fp);
            $limit = (!empty($row_count) && $row_count > 50) ? 50 : $row_count;
            while (($data = fgetcsv($handle, 10000, $delimiter)) !== FALSE) {
                $data = str_replace('"','',$data);

                if (empty($fields)) {
                    $fields = $data;
                    continue;
                }

                if (str_replace(array($delimiter, ' '), '', $data) == '') {
                    continue;
                }

                foreach ($data as $k => $value) {
                    if(isset($fields[$k])) {
                        $file_data['data'][$row][$fields[$k]] = $value;
                        $file_data['headers'][$k] = $fields[$k];
                        if (!$is_test) {

                            $datum = 'select-res-' . $k;
                            if (!empty($post_data[$datum]) && in_array($datum, array_keys($post_data))) {
                                if($post_data[$datum] == 'custom'){
                                    $products[$row][$post_data[$datum]][$fields[$k]] = $value;
                                }else{
                                    $products[$row][$post_data[$datum]] = $value;

                                }
                                if (!empty($post_data['import_channels'])){
                                    $products[$row]['import_channels'] = $post_data['import_channels'];
                                }else{
                                    $products[$row]['localExpress'] = 'localExpress';
                                }
                            }
                        }
                    }
                }

                $row++;
                if ($row == $limit) {
                    break;
                }
            }

            fclose($handle);

        }

        return ['file_data' => $file_data, 'products' => $products];

    }

    /**
     * @param $product
     * @param $row
     * @param $errors
     * @return void
     */
    public static function validateImportProduct($product, $row, &$errors)
    {
        if (empty($product['upc'])) {
            $errors[] = Yii::t('app', 'Product UPC can not be empty.') . '<br> Row ' . $row;
        }

        if (!empty($product['price'])) {
            $price = !empty($product['price']) ? trim($product['price']) : 0;
            $price = str_replace('-', '', $price);
            $price = str_replace('$', '', $price);
            if (!is_numeric($price)) {
                $errors[] = Yii::t('app', 'Product  Price Must be numeric.') . '<br> Row ' . $row;
            }
        }
    }

    /**
     * Gets query for [[Store]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'store_id']);
    }
}
