<?php

/** @var yii\web\View $this */
/** @var app\modules\qrcode\models\QrCode $model */
/** @var yii\bootstrap5\ActiveForm $form */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

?>
<div class="qrcode-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput([
        'maxlength' => true,
        'placeholder' => 'e.g. Product Brochure, Business Card',
    ]) ?>

    <?= $form->field($model, 'url')->textInput([
        'placeholder' => 'https://example.com',
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
