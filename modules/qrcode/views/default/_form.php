<?php

/** @var yii\web\View $this */
/** @var app\modules\qrcode\models\QrCode $model */
/** @var yii\bootstrap5\ActiveForm $form */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

?>
<div class="qrcode-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'sku')->textInput([
        'maxlength' => true,
        'placeholder' => 'e.g. ABC-12345',
    ]) ?>

    <?= $form->field($model, 'creator')->textInput([
        'maxlength' => true,
        'placeholder' => 'e.g. The Beatles',
    ]) ?>

    <?= $form->field($model, 'product')->textInput([
        'maxlength' => true,
        'placeholder' => 'e.g. Abbey Road',
    ]) ?>

    <?= $form->field($model, 'url')->textInput([
        'placeholder' => 'https://example.com',
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
