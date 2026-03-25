<?php

namespace app\modules\qrcode\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use app\modules\qrcode\models\QrCode;

class DefaultController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'import-csv' => ['POST'],
                    'process-csv-row' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => QrCode::find()->where(['user_id' => Yii::$app->user->id]),
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new QrCode();

        if ($model->load(Yii::$app->request->post())) {
            $model->user_id = Yii::$app->user->id;
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionDownload($id)
    {
        $model = $this->findModel($id);

        $filePath = Yii::getAlias('@webroot') . $model->qr_image_path;
        if (!$model->qr_image_path || !file_exists($filePath)) {
            throw new NotFoundHttpException('QR code image not found.');
        }

        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $model->product) . '_qr.png';

        return Yii::$app->response->sendFile($filePath, $filename);
    }

    public function actionImportCsv()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $file = UploadedFile::getInstanceByName('csv_file');
        if (!$file) {
            return ['success' => false, 'message' => 'No file uploaded.'];
        }

        if (!in_array($file->extension, ['csv', 'txt'])) {
            return ['success' => false, 'message' => 'Only CSV files are allowed.'];
        }

        $handle = fopen($file->tempName, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'Could not read the file.'];
        }

        $rows = [];
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return ['success' => false, 'message' => 'CSV file is empty.'];
        }

        // Normalise header names to lowercase/trimmed
        $header = array_map(function ($col) {
            return strtolower(trim($col));
        }, $header);

        $requiredCols = ['sku', 'artist', 'album', 'url'];
        foreach ($requiredCols as $col) {
            if (!in_array($col, $header)) {
                fclose($handle);
                return ['success' => false, 'message' => "Missing required column: $col"];
            }
        }

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($header)) {
                continue;
            }
            $assoc = array_combine($header, $row);
            $rows[] = [
                'sku' => trim($assoc['sku']),
                'creator' => trim($assoc['artist']),
                'product' => trim($assoc['album']),
                'url' => trim($assoc['url']),
            ];
        }
        fclose($handle);

        if (empty($rows)) {
            return ['success' => false, 'message' => 'No data rows found in the CSV.'];
        }

        // Store rows in session for row-by-row processing
        Yii::$app->session->set('csv_import_rows', $rows);

        return ['success' => true, 'total' => count($rows)];
    }

    public function actionProcessCsvRow()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $index = (int) Yii::$app->request->post('index', 0);
        $rows = Yii::$app->session->get('csv_import_rows', []);

        if (!isset($rows[$index])) {
            return ['success' => false, 'message' => 'Row not found.'];
        }

        $row = $rows[$index];
        $model = new QrCode();
        $model->user_id = Yii::$app->user->id;
        $model->sku = $row['sku'];
        $model->creator = $row['creator'];
        $model->product = $row['product'];
        $model->url = $row['url'];

        if (!$model->save()) {
            $errors = implode('; ', array_map(function ($e) {
                return implode(', ', $e);
            }, $model->getErrors()));
            return [
                'success' => false,
                'message' => "Row " . ($index + 1) . ": $errors",
                'current' => $index + 1,
            ];
        }

        // Clean up session on last row
        if ($index >= count($rows) - 1) {
            Yii::$app->session->remove('csv_import_rows');
        }

        return ['success' => true, 'current' => $index + 1];
    }

    protected function findModel($id)
    {
        $model = QrCode::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);
        if ($model === null) {
            throw new NotFoundHttpException('The requested QR code does not exist.');
        }
        return $model;
    }
}
