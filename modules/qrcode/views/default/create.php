<?php

/** @var yii\web\View $this */
/** @var app\modules\qrcode\models\QrCode $model */

use yii\bootstrap5\Html;

$this->title = 'Create QR Code';
$this->params['breadcrumbs'][] = ['label' => 'My QR Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qrcode-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
