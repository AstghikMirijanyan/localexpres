<?php

namespace frontend\controllers;

use common\jobs\ProductsImportJob;
use common\models\CSVUploader;
use common\models\StoreProduct;
use common\models\StoreProductSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * StoreProductController implements the CRUD actions for StoreProduct model.
 */
class StoreProductController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all StoreProduct models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StoreProductSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single StoreProduct model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new StoreProduct model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new StoreProduct();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing StoreProduct model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing StoreProduct model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionImportDialog()
    {
        $model = new CSVUploader();
        $model->scenario = 'manual_upload';
        $selects = CSVUploader::productImportFields();


        if (Yii::$app->request->isAjax || Yii::$app->request->isPost) {
            $csv_file = Yii::$app->request->post('fcsv');
            $delimiter = Yii::$app->request->post('delimiter') ?: ',';
            $store = Yii::$app->request->post('store') ? Yii::$app->request->post('store') : '1';
            $post_data = Yii::$app->request->post();


            if (!empty($csv_file) && !empty($post_data)) {

                $path = \Yii::getAlias('@backend/web/temp/products/import/');
                $filePath = $path . $csv_file . '.csv';
                if (file_exists($filePath)) {

                    if (!empty($is_cancel)) {
                        unlink($filePath);
                        return $this->redirect(['index']);
                    }

                    $file_data = ['headers' => [], 'data' => []];

                    $read_file = StoreProduct::readCsvImportFile($filePath, $post_data, false, $delimiter);
                    if (!empty($read_file['file_data'])) {
                        $file_data = $read_file['file_data'];
                        $products = $read_file['products'];
                    }

                    $errors = [];

                    if (!empty($products)) {

                        foreach ($products as $row => $product) {
                            if (!empty($product)) {
                                StoreProduct::validateImportProduct($product, $row, $errors);
                            }
                        }
                    } else {
                        $errors[] = Yii::t('app', 'No Products to import.');
                    }

                    if (!empty($post_data['import_confirm']) && empty($errors)) {


                        Yii::$app->queue->push(new ProductsImportJob([

                            'filePath' => $filePath,
                            'extension' => 'CSV',
                            'job' => 'import_products',
                            'store' => $store,
                            'post_data' => $post_data,
                            'product_count' => !empty($products) ? count($products) : 0,
                            'shop_name' => $store,
                            'delimiter' => $delimiter,

                        ]));

                        return $this->redirect(['index']);
                    }

                    if (empty($errors)) {
                        $errors = Yii::t('app', 'Seams everything is ok');
                    }
                    return $this->render('import_dialog', [
                        'model' => $model,
                        'file_loaded' => true,
                        'selects' => $selects,
                        'store' => $store,
                        'errors' => $errors,
                        'file_name' => $csv_file,
                        'delimiter' => $delimiter,
                        'post_data' => $post_data,
                        'view_data' => $file_data
                    ]);
                }
            } else {

                $model->csvFile = UploadedFile::getInstance($model, 'csvFile');
                if ($model->validate() && $model->csvFile instanceof UploadedFile) {

                    //open file and display in view
                    $file = $model->csvFile;
                    $csv_name = (uniqid() . md5(sha1($file->baseName) . date('H:i:s')));
                    $import = $csv_name . '.' . $file->extension;

                    $path = \Yii::getAlias('@backend/web/temp/products/import/');
                    if (!is_dir($path)) {
                        mkdir($path, 0755, true);
                    }
                    $filePath = $path . $import;
                    $file_data = ['headers' => [], 'data' => []];

                    if ($file->saveAs($filePath)) {
                        $read_file = StoreProduct::readCsvImportFile($filePath, [], true, $delimiter);
                        if (!empty($read_file['file_data'])) {
                            $file_data = $read_file['file_data'];
                        }
                    }

                    return $this->render('import_dialog', [
                        'model' => $model,
                        'file_loaded' => true,
                        'selects' => $selects,
                        'store' => $store,
                        'delimiter' => $delimiter,
                        'file_name' => $csv_name,
                        'view_data' => $file_data
                    ]);

                }
            }
        }

        return $this->render('/store-product/import_dialog', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the StoreProduct model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return StoreProduct the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StoreProduct::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
