<?php

/** @var yii\web\View $this */
/** @var app\modules\qrcode\models\QrCode $model */

use yii\bootstrap5\Html;

$this->title = 'Update: ' . $model->product;
$this->params['breadcrumbs'][] = ['label' => 'My QR Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->product, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="qrcode-update">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
