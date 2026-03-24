<?php

/** @var yii\web\View $this */
/** @var app\modules\qrcode\models\QrCode $model */

use yii\bootstrap5\Html;
use yii\widgets\DetailView;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'My QR Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qrcode-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Download PNG', ['download', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this QR code?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Back to List', ['index'], ['class' => 'btn btn-secondary']) ?>
    </p>

    <div class="row">
        <div class="col-md-6">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'name',
                    [
                        'attribute' => 'url',
                        'format' => 'raw',
                        'value' => Html::a(Html::encode($model->url), $model->url, ['target' => '_blank', 'rel' => 'noopener']),
                    ],
                    [
                        'attribute' => 'created_at',
                        'value' => Yii::$app->formatter->asDatetime($model->created_at),
                    ],
                    [
                        'attribute' => 'updated_at',
                        'value' => Yii::$app->formatter->asDatetime($model->updated_at),
                    ],
                ],
            ]) ?>
        </div>
        <div class="col-md-6 text-center">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">QR Code Preview</h5>
                    <div class="my-3">
                        <?= Html::img($model->qrImageUrl, [
                            'alt' => 'QR Code for ' . Html::encode($model->name),
                            'style' => 'max-width: 300px;',
                        ]) ?>
                    </div>
                    <p class="text-muted">Scan this code to open: <?= Html::encode($model->url) ?></p>
                    <?= Html::a('Download as PNG', ['download', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
    </div>
</div>
