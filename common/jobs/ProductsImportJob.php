<?php

namespace common\jobs;

use common\models\StoreProduct;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ProductsImportJob extends BaseObject implements JobInterface

{


    public $file;
    public $channel;
    public $offset = 0;
    public $limit = 1000;
    public $filePath;
    public $store;
    public $job;

    public $type;
    public $product_count;
    public $shop_name;
    public $extension;
    public $post_data;
    public $delimiter;
    public $redirect;


    /**
     * @param Queue $queue which pushed and is handling the job
     */
    public function execute($queue)
    {
        $row = 1;
        $products = $fields = [];

        $filePath = $this->filePath;
        $delimiter = $this->delimiter ? $this->delimiter : ',';
        if (($handle = fopen($filePath, "r")) !== false) {

            while (($data = fgetcsv($handle, 10000, $delimiter)) !== false) {


                if (empty($fields)) {
                    $fields = $data;
                    continue;
                }

                if (!empty($this->post_data)) {
                    $val1 = '';
                    $val2 = '';
                    $specification = !empty($data[11]) ? $data[11] : '';
                    $post_data = $this->post_data;

                    if (!(strstr($specification, 'tebook 15-g049ca') || strstr($specification,
                            '00-420qe (CTO)') || strstr($specification, 'oenix Desktop ENVY h9-1330') || strstr($specification,
                            'hSmart') || strstr($specification, 'ENVY h9-1387'))) {

                        foreach ($data as $k => $value) {
                            $datum = 'select-res-' . $k;
                            if (!empty($post_data[$datum]) && in_array($datum, array_keys($post_data))) {
                                if ($post_data[$datum] == 'product_desc') {
                                    $val1 .= $value . ' ';
                                    $value = $val1;
                                } elseif ($post_data[$datum] == 'product_type') {
                                    $val2 .= !empty($value) ? $value . ':' : '';
                                    $value = rtrim($val2, ':');
                                }

                                if ($post_data[$datum] == 'custom') {
                                    $products[$row][$post_data[$datum]][$fields[$k]] = $value;
                                } else {
                                    $products[$row][$post_data[$datum]] = $value;
                                }



                                $products[$row]['import_channels'] = $this->store;

                            }
                        }
                    }
                }

                $row++;

            }

            fclose($handle);

            foreach ($products as  $prod){
                $store_products = new StoreProduct();
                $store_products->upc = $prod['upc'];
                $store_products->title = !empty($prod['title']) ? $prod['title'] : '';
                $store_products->store_id = !empty($prod['import_channel']) ? (int)$prod['import_channels'] : 1;
                $store_products->price = !empty($prod['price']) ? (int)$prod['price'] : 0;
                $store_products->save('false');
                echo "inserted";
            }

            unlink($filePath);

        }
    }




}