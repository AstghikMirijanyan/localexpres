<?php

use common\models\Store;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\validators\FileValidator;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/**
 * @var $delimiter
 * @var $file_name
 * @var $is_overwrite
 * @var $selects
 * @var $model
 * @var $store
 */

$this->title = Yii::t('app', 'Create Store Product');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Store Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


Pjax::begin();
$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data', 'data-pjax' => true]]);

?>
<div class="content">
    <?php
    if (!empty($file_loaded)) {
        ?>
        <div style="margin: 10px 0;position: relative;">

            <input type="hidden" name="fcsv" value="<?= !empty($file_name) ? $file_name : '' ?>">
            <input type="hidden" name="stock_action" value="<?= !empty($is_overwrite) ? $is_overwrite : '' ?>">
            <input type="hidden" name="delimiter" value="<?= !empty($delimiter) ? $delimiter : ','; ?>">
            <input type="submit" name="import_confirm" id="import-confirm" class="btn btn-success" value="IMPORT">
            <?php

            if (!empty($errors) && is_array($errors)) {
                foreach ($errors as $error) {
                    ?>
                    <div>
                        <?= $error; ?>
                    </div>
                    <?php
                }
            } elseif (!empty($errors) && is_string($errors)) {
                ?>
                <div>
                    <?= $errors; ?>
                </div>
                <?php
            }

            ?>
        </div>
        <?php
    }
    ?>

    <div class="store-product-form">
        <?php
        if (!empty($file_loaded) && !empty($view_data)) {
            ?>

            <table style="table-layout: fixed;"
                   class="mt-3 table table-fixed table-bordered responsive-table display">
                <thead>
                <tr class="import_grid-header">
                    <th style="width: 40px;">Row #</th>
                    <?php
                    foreach ($view_data['headers'] as $header) {
                        ?>
                        <th><span><?= $header; ?></span></th>
                        <?php
                    }
                    ?>
                </tr>
                <tr class="import_fields">
                    <th></th>
                    <?php
                    foreach ($view_data['headers'] as $header_key => $header) {
                        ?>
                        <th>
                            <select name="select-res-<?= $header_key; ?>" class="select2-theme browser-default">
                                <option style="color:red" value="0">Don't Import</option>
                                <?php
                                $group_id = 1;
                                foreach ($selects as $group => $select_item) {
                                    ?>
                                    <optgroup label="<?= $group; ?>" data-i="<?= $group_id ?>">
                                        <?php
                                        foreach ($select_item as $item_field => $item) {
                                            //                                            sort($select);
                                            ?>
                                            <option <?= ((!empty($post_data) && ($post_data['select-res-' . $header_key] == $item_field)) || (strcasecmp($item, $header) == 0) || (strcasecmp(str_replace('_', ' ', $header), $item) == 0)) ? 'selected' : ''; ?>
                                                    value="<?= $item_field; ?>"><?= $item; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </optgroup>
                                    <?php
                                    $group_id++;
                                }
                                ?>
                            </select>
                        </th>
                        <?php
                    }
                    ?>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($view_data['data'] as $key => $data) {
                    ?>
                    <tr class="<?= ($key % 2) ? '' : 'odd'; ?>">
                        <td><?= $key; ?></td>
                        <?php
                        foreach ($data as $datum) {
                            ?>
                            <td><?= $datum; ?></td>
                            <?php
                        } ?>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <?php
        } else {
            ?>
            <div class="row">
                <div class="form-group no-absolute">
                    <?= $form->field($model, 'csvFile', ['options' => ['id' => 'import-products']])->fileInput(['accept' => '.csv'])->label(false) ?>
                    <label for="import-products" class="form-label black-text">
                        <strong style=""><?= Yii::t('app', 'Select CSV File'); ?></strong>
                        <?php
                        $val = new FileValidator();
                        $fileSize = round($val->getSizeLimit() * 2.5 / 1024 / 1024, 5) . 'MB';
                        ?>
                    </label>
                    <div>Max uploading size is <?= $fileSize; ?></div>
                </div>
                <div>

                    <?= Html::activeDropDownList($model, 'store', ArrayHelper::map(Store::find()->all(), 'id', 'title')) ?>

                </div>
            </div>

            <?php
        }
        ?>

    </div>

</div>
<?php
if (empty($file_loaded)) {
    ?>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Upload File'), ['class' => 'btn btn-success']) ?>
    </div>
    <?php
}
?>
<?php ActiveForm::end();
Pjax::end();

?>


