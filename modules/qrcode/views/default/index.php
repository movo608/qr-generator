<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\bootstrap5\Html;
use yii\grid\GridView;

$this->title = 'My QR Codes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qrcode-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create QR Code', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::button('Import CSV', ['class' => 'btn btn-primary', 'data-bs-toggle' => 'modal', 'data-bs-target' => '#csvImportModal']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pager' => [
            'class' => \yii\widgets\LinkPager::class,
            'options' => ['class' => 'pagination'],
            'linkOptions' => ['class' => 'page-link btn btn-sm btn-outline-primary me-1'],
            'pageCssClass' => 'page-item',
            'prevPageCssClass' => 'page-item',
            'nextPageCssClass' => 'page-item',
            'firstPageCssClass' => 'page-item',
            'lastPageCssClass' => 'page-item',
            'activePageCssClass' => 'active',
            'disabledPageCssClass' => 'disabled',
            'disabledListItemSubTagOptions' => ['class' => 'page-link btn btn-sm btn-outline-secondary me-1 disabled'],
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'sku',
            'creator',
            'product',
            [
                'attribute' => 'url',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a(Html::encode($model->url), $model->url, ['target' => '_blank', 'rel' => 'noopener']);
                },
            ],
            [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    return Yii::$app->formatter->asDatetime($model->created_at);
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
            ],
        ],
    ]); ?>
</div>

<!-- CSV Import Modal -->
<div class="modal fade" id="csvImportModal" tabindex="-1" aria-labelledby="csvImportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="csvImportModalLabel">Import QR Codes from CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Step 1: File upload -->
                <div id="csv-upload-step">
                    <p class="text-muted mb-3">Upload a CSV file with columns: <strong>SKU, Artist, Album, URL</strong></p>
                    <div class="mb-3">
                        <input type="file" class="form-control" id="csv-file-input" accept=".csv,.txt">
                    </div>
                    <div id="csv-file-info" class="text-muted small" style="display:none;"></div>
                </div>

                <!-- Step 2: Progress -->
                <div id="csv-progress-step" style="display:none;">
                    <div class="mb-2">
                        <strong id="csv-progress-label">0 / 0</strong>
                    </div>
                    <div class="progress" style="height: 24px;">
                        <div id="csv-progress-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div id="csv-error-log" class="mt-3 small text-danger" style="display:none;"></div>
                </div>

                <!-- Step 3: Done -->
                <div id="csv-done-step" style="display:none;">
                    <div class="alert alert-success mb-0" id="csv-done-message"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="csv-close-btn">Close</button>
                <button type="button" class="btn btn-success" id="csv-start-btn" disabled>Start Generation</button>
            </div>
        </div>
    </div>
</div>

<?php
$csrfToken = Yii::$app->request->csrfToken;
$importUrl = \yii\helpers\Url::to(['import-csv']);
$processUrl = \yii\helpers\Url::to(['process-csv-row']);

$js = <<<JS
(function() {
    var total = 0;
    var errors = [];
    var processing = false;

    var fileInput = document.getElementById('csv-file-input');
    var fileInfo = document.getElementById('csv-file-info');
    var startBtn = document.getElementById('csv-start-btn');
    var closeBtn = document.getElementById('csv-close-btn');
    var uploadStep = document.getElementById('csv-upload-step');
    var progressStep = document.getElementById('csv-progress-step');
    var doneStep = document.getElementById('csv-done-step');
    var progressBar = document.getElementById('csv-progress-bar');
    var progressLabel = document.getElementById('csv-progress-label');
    var errorLog = document.getElementById('csv-error-log');
    var doneMessage = document.getElementById('csv-done-message');

    fileInput.addEventListener('change', function() {
        if (!this.files.length) {
            startBtn.disabled = true;
            fileInfo.style.display = 'none';
            return;
        }
        var file = this.files[0];
        fileInfo.textContent = 'Selected: ' + file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
        fileInfo.style.display = 'block';
        startBtn.disabled = false;
    });

    startBtn.addEventListener('click', function() {
        if (processing) return;
        processing = true;
        startBtn.disabled = true;
        fileInput.disabled = true;
        errors = [];

        var formData = new FormData();
        formData.append('csv_file', fileInput.files[0]);
        formData.append('_csrf', '{$csrfToken}');

        fetch('{$importUrl}', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) {
                    alert(data.message);
                    processing = false;
                    startBtn.disabled = false;
                    fileInput.disabled = false;
                    return;
                }
                total = data.total;
                uploadStep.style.display = 'none';
                startBtn.style.display = 'none';
                progressStep.style.display = 'block';
                progressLabel.textContent = '0 / ' + total;
                processRow(0);
            })
            .catch(function(err) {
                alert('Upload failed: ' + err.message);
                processing = false;
                startBtn.disabled = false;
                fileInput.disabled = false;
            });
    });

    function processRow(index) {
        if (index >= total) {
            finishImport();
            return;
        }

        var formData = new FormData();
        formData.append('index', index);
        formData.append('_csrf', '{$csrfToken}');

        fetch('{$processUrl}', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var current = index + 1;
                var pct = Math.round((current / total) * 100);
                progressBar.style.width = pct + '%';
                progressBar.setAttribute('aria-valuenow', pct);
                progressLabel.textContent = current + ' / ' + total;

                if (!data.success) {
                    errors.push(data.message);
                    errorLog.style.display = 'block';
                    errorLog.innerHTML += data.message + '<br>';
                }

                processRow(index + 1);
            })
            .catch(function(err) {
                errors.push('Row ' + (index + 1) + ': Network error');
                errorLog.style.display = 'block';
                errorLog.innerHTML += 'Row ' + (index + 1) + ': Network error<br>';
                processRow(index + 1);
            });
    }

    function finishImport() {
        processing = false;
        progressStep.style.display = 'none';
        doneStep.style.display = 'block';

        var succeeded = total - errors.length;
        var msg = succeeded + ' of ' + total + ' QR codes created successfully.';
        if (errors.length > 0) {
            msg += ' ' + errors.length + ' row(s) had errors.';
        }
        doneMessage.textContent = msg;

        closeBtn.textContent = 'Close & Refresh';
        closeBtn.onclick = function() {
            location.reload();
        };
    }

    // Reset modal state when closed
    document.getElementById('csvImportModal').addEventListener('hidden.bs.modal', function() {
        if (processing) return;
        uploadStep.style.display = 'block';
        progressStep.style.display = 'none';
        doneStep.style.display = 'none';
        errorLog.style.display = 'none';
        errorLog.innerHTML = '';
        fileInput.value = '';
        fileInput.disabled = false;
        fileInfo.style.display = 'none';
        startBtn.disabled = true;
        startBtn.style.display = 'inline-block';
        closeBtn.textContent = 'Close';
        closeBtn.onclick = null;
        progressBar.style.width = '0%';
        progressLabel.textContent = '0 / 0';
    });
})();
JS;

$this->registerJs($js);
?>
