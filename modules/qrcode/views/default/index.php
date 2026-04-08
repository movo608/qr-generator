<?php

/** @var yii\web\View $this */
/** @var app\modules\qrcode\models\QrCodeSearch $searchModel */
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
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-striped table-hover'],
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
            [
                'class' => 'yii\grid\CheckboxColumn',
                'checkboxOptions' => function ($model) {
                    return [
                        'class' => 'qr-select-checkbox',
                        'data-id' => $model->id,
                        'data-creator' => $model->creator,
                        'data-product' => $model->product,
                        'data-sku' => $model->sku,
                    ];
                },
                'headerOptions' => ['class' => 'checkbox-column'],
                'contentOptions' => ['class' => 'checkbox-column'],
            ],
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
                'filter' => false,
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

    <!-- Sticky toggle button (right edge) -->
    <button type="button" class="qr-sidebar-toggle" id="qr-sidebar-toggle" style="display:none;" title="Selected QR Codes" aria-label="Selected QR Codes">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
        </svg>
        <span class="qr-sidebar-toggle-badge" id="qr-toggle-badge">0</span>
    </button>

    <!-- Slide-out sidebar panel + backdrop -->
    <div class="qr-sidebar-backdrop" id="qr-sidebar-backdrop"></div>
    <div class="qr-sidebar" id="qr-sidebar">
        <div class="qr-sidebar-header">
            <h6>Selected QR Codes</h6>
            <button type="button" class="btn-close" id="qr-sidebar-close" aria-label="Close"></button>
        </div>
        <div class="qr-sidebar-body" id="qr-selected-list">
            <p class="text-muted small" id="qr-sidebar-empty">No items selected. Use the checkboxes to select QR codes for bulk download.</p>
        </div>
        <div class="qr-sidebar-footer" id="qr-sidebar-footer" style="display:none;">
            <button type="button" class="btn btn-sm btn-outline-secondary w-100 mb-2" id="qr-clear-selection">Clear All</button>
            <button type="button" class="btn btn-primary w-100" id="qr-download-zip">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download me-1" viewBox="0 0 16 16">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                </svg>
                Download ZIP
            </button>
        </div>
    </div>
</div>

<!-- Hidden form for zip download -->
<?= Html::beginForm(['download-zip'], 'POST', ['id' => 'qr-zip-form']) ?>
<div id="qr-zip-inputs"></div>
<?= Html::endForm() ?>

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
    // ===== Selection Sidebar Logic =====
    var STORAGE_KEY = 'qr_selected_items';

    function saveSelection() {
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(selected));
    }
    function loadSelection() {
        try {
            return JSON.parse(sessionStorage.getItem(STORAGE_KEY)) || {};
        } catch (e) {
            return {};
        }
    }

    var selected = loadSelection();

    var sidebar = document.getElementById('qr-sidebar');
    var backdrop = document.getElementById('qr-sidebar-backdrop');
    var toggleBtn = document.getElementById('qr-sidebar-toggle');
    var toggleBadge = document.getElementById('qr-toggle-badge');
    var closeBtn = document.getElementById('qr-sidebar-close');
    var selectedList = document.getElementById('qr-selected-list');
    var emptyMsg = document.getElementById('qr-sidebar-empty');
    var sidebarFooter = document.getElementById('qr-sidebar-footer');
    var clearAllBtn = document.getElementById('qr-clear-selection');
    var downloadBtn = document.getElementById('qr-download-zip');
    var zipForm = document.getElementById('qr-zip-form');
    var zipInputs = document.getElementById('qr-zip-inputs');

    function openSidebar() {
        sidebar.classList.add('open');
        backdrop.classList.add('open');
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        backdrop.classList.remove('open');
    }

    toggleBtn.addEventListener('click', openSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    backdrop.addEventListener('click', closeSidebar);

    // Re-check checkboxes that were previously selected
    function restoreCheckboxes() {
        document.querySelectorAll('.qr-select-checkbox').forEach(function(cb) {
            var id = cb.getAttribute('data-id');
            if (selected[id]) {
                cb.checked = true;
            }
        });
    }

    function updateSidebar() {
        saveSelection();

        var keys = Object.keys(selected);
        var count = keys.length;
        toggleBadge.textContent = count;

        // Show/hide the sticky toggle button
        toggleBtn.style.display = count > 0 ? 'flex' : 'none';

        if (count === 0) {
            emptyMsg.style.display = 'block';
            sidebarFooter.style.display = 'none';
            var items = selectedList.querySelectorAll('.qr-sidebar-item');
            items.forEach(function(el) { el.remove(); });
            closeSidebar();
            return;
        }

        emptyMsg.style.display = 'none';
        sidebarFooter.style.display = 'block';

        // Rebuild the item list
        var items = selectedList.querySelectorAll('.qr-sidebar-item');
        items.forEach(function(el) { el.remove(); });

        keys.forEach(function(id) {
            var item = selected[id];
            var div = document.createElement('div');
            div.className = 'qr-sidebar-item';
            div.setAttribute('data-id', id);

            var info = document.createElement('div');
            info.className = 'qr-sidebar-item-info';
            info.innerHTML = '<strong>' + escapeHtml(item.creator) + '</strong><br><small>' + escapeHtml(item.product) + '</small><br><span class="text-muted" style="font-size:0.75rem;">' + escapeHtml(item.sku) + '</span>';

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-close qr-sidebar-remove';
            removeBtn.setAttribute('aria-label', 'Remove');
            removeBtn.setAttribute('data-id', id);

            div.appendChild(info);
            div.appendChild(removeBtn);
            selectedList.appendChild(div);
        });
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    var batchUpdate = false;

    // Listen for checkbox changes (using event delegation)
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('qr-select-checkbox')) {
            var cb = e.target;
            var id = cb.getAttribute('data-id');
            if (cb.checked) {
                selected[id] = {
                    creator: cb.getAttribute('data-creator'),
                    product: cb.getAttribute('data-product'),
                    sku: cb.getAttribute('data-sku'),
                };
            } else {
                delete selected[id];
            }
            if (!batchUpdate) {
                updateSidebar();
            }
        }

        // Handle "select all" checkbox
        if (e.target.closest('.select-on-check-all')) {
            batchUpdate = true;
            var checkboxes = document.querySelectorAll('.qr-select-checkbox');
            checkboxes.forEach(function(cb) {
                var id = cb.getAttribute('data-id');
                if (cb.checked) {
                    selected[id] = {
                        creator: cb.getAttribute('data-creator'),
                        product: cb.getAttribute('data-product'),
                        sku: cb.getAttribute('data-sku'),
                    };
                } else {
                    delete selected[id];
                }
            });
            batchUpdate = false;
            updateSidebar();
        }
    });

    // Remove individual items from sidebar
    selectedList.addEventListener('click', function(e) {
        var removeBtn = e.target.closest('.qr-sidebar-remove');
        if (!removeBtn) return;
        var id = removeBtn.getAttribute('data-id');
        delete selected[id];
        // Uncheck the corresponding checkbox if visible
        var cb = document.querySelector('.qr-select-checkbox[data-id="' + id + '"]');
        if (cb) cb.checked = false;
        // Also uncheck "select all" if any item removed
        var selectAll = document.querySelector('.select-on-check-all');
        if (selectAll) selectAll.checked = false;
        updateSidebar();
    });

    // Clear all
    clearAllBtn.addEventListener('click', function() {
        selected = {};
        document.querySelectorAll('.qr-select-checkbox').forEach(function(cb) {
            cb.checked = false;
        });
        var selectAll = document.querySelector('.select-on-check-all');
        if (selectAll) selectAll.checked = false;
        updateSidebar();
    });

    // On page load: restore checkboxes and sidebar from sessionStorage
    restoreCheckboxes();
    updateSidebar();

    // Download ZIP
    downloadBtn.addEventListener('click', function() {
        var keys = Object.keys(selected);
        if (keys.length === 0) return;

        zipInputs.innerHTML = '';
        keys.forEach(function(id) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            zipInputs.appendChild(input);
        });
        zipForm.submit();
        selected = {};
        saveSelection();
        updateSidebar();
    });

    // ===== CSV Import Logic =====
    var total = 0;
    var errors = [];
    var processing = false;

    var fileInput = document.getElementById('csv-file-input');
    var fileInfo = document.getElementById('csv-file-info');
    var startBtn = document.getElementById('csv-start-btn');
    var csvCloseBtn = document.getElementById('csv-close-btn');
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
                    errorLog.innerHTML += escapeHtml(data.message) + '<br>';
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

        csvCloseBtn.textContent = 'Close & Refresh';
        csvCloseBtn.onclick = function() {
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
        csvCloseBtn.textContent = 'Close';
        csvCloseBtn.onclick = null;
        progressBar.style.width = '0%';
        progressLabel.textContent = '0 / 0';
    });
})();
JS;

$this->registerJs($js);
?>
