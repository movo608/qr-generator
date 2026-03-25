<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Html;

$this->title = Yii::$app->name;

// Guest landing page
if (Yii::$app->user->isGuest): ?>
<div class="site-index">
    <div class="jumbotron text-center bg-transparent mt-5 mb-5">
        <h1 class="display-4">Welcome to <?= Html::encode(Yii::$app->name) ?></h1>
        <p class="lead">Generate and manage QR codes for your music catalogue.</p>
        <p>
            <?= Html::a('Sign Up', ['/site/signup'], ['class' => 'btn btn-lg btn-success me-2']) ?>
            <?= Html::a('Log In', ['/site/login'], ['class' => 'btn btn-lg btn-primary']) ?>
        </p>
    </div>
</div>
<?php return; endif;

/**
 * @var int $totalQrCodes
 * @var int $uniqueArtists
 * @var int $uniqueAlbums
 * @var int $thisMonth
 * @var array $dailyData
 * @var array $topArtists
 * @var array $topAlbums
 * @var \app\modules\qrcode\models\QrCode[] $latestCodes
 */
?>
<div class="site-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Dashboard</h1>
        <div>
            <?= Html::a('My QR Codes', ['/qrcode/default/index'], ['class' => 'btn btn-primary me-2']) ?>
            <?= Html::a('Create QR Code', ['/qrcode/default/create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="text-muted small">Total QR Codes</div>
                    <div class="display-5 fw-bold" style="color: var(--theme-primary);"><?= $totalQrCodes ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="text-muted small">Unique Artists</div>
                    <div class="display-5 fw-bold" style="color: var(--theme-mid);"><?= $uniqueArtists ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="text-muted small">Unique Albums</div>
                    <div class="display-5 fw-bold" style="color: var(--theme-dark);"><?= $uniqueAlbums ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="text-muted small">Added This Month</div>
                    <div class="display-5 fw-bold" style="color: var(--theme-darker);"><?= $thisMonth ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Timeline chart -->
        <div class="col-lg-8 mb-3">
            <div class="card h-100">
                <div class="card-header">QR Codes Created (Last 30 Days)</div>
                <div class="card-body">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Doughnut chart -->
        <div class="col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-header">Top Albums</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="albumsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Bar chart -->
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header">Top Artists</div>
                <div class="card-body">
                    <canvas id="artistsChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Latest QR codes table -->
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header">Recently Added</div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Artist / Band</th>
                                <th>Album</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($latestCodes)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">No QR codes yet. <?= Html::a('Create one', ['/qrcode/default/create']) ?></td></tr>
                            <?php else: ?>
                                <?php foreach ($latestCodes as $qr): ?>
                                    <tr>
                                        <td><?= Html::a(Html::encode($qr->sku), ['/qrcode/default/view', 'id' => $qr->id]) ?></td>
                                        <td><?= Html::encode($qr->creator) ?></td>
                                        <td><?= Html::encode($qr->product) ?></td>
                                        <td><?= Yii::$app->formatter->asDate($qr->created_at) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Prepare chart data
$dailyLabels = array_column($dailyData, 'date');
$dailyCounts = array_map('intval', array_column($dailyData, 'count'));

$artistLabels = array_column($topArtists, 'creator');
$artistCounts = array_map('intval', array_column($topArtists, 'count'));

$albumLabels = array_column($topAlbums, 'product');
$albumCounts = array_map('intval', array_column($topAlbums, 'count'));

$dailyLabelsJson = json_encode($dailyLabels);
$dailyCountsJson = json_encode($dailyCounts);
$artistLabelsJson = json_encode($artistLabels);
$artistCountsJson = json_encode($artistCounts);
$albumLabelsJson = json_encode($albumLabels);
$albumCountsJson = json_encode($albumCounts);

$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js', [
    'position' => \yii\web\View::POS_HEAD,
]);

$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    var purple = getComputedStyle(document.documentElement);
    var palette = [
        '#6a3fbf', '#7e57c2', '#9b7bd4', '#c4a8e8', '#ede4f7',
        '#4a3080', '#3b2667', '#2d1b4e', '#b794d6', '#d4bfe8'
    ];

    // Daily line chart
    new Chart(document.getElementById('dailyChart'), {
        type: 'line',
        data: {
            labels: {$dailyLabelsJson},
            datasets: [{
                label: 'QR Codes',
                data: {$dailyCountsJson},
                borderColor: '#6a3fbf',
                backgroundColor: 'rgba(106, 63, 191, 0.1)',
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#6a3fbf',
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // Artists bar chart
    new Chart(document.getElementById('artistsChart'), {
        type: 'bar',
        data: {
            labels: {$artistLabelsJson},
            datasets: [{
                label: 'QR Codes',
                data: {$artistCountsJson},
                backgroundColor: palette,
                borderColor: '#6a3fbf',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // Albums doughnut chart
    new Chart(document.getElementById('albumsChart'), {
        type: 'doughnut',
        data: {
            labels: {$albumLabelsJson},
            datasets: [{
                data: {$albumCountsJson},
                backgroundColor: palette,
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 8, font: { size: 11 } }
                }
            }
        }
    });
});
JS;

$this->registerJs($js, \yii\web\View::POS_END);

$this->registerCss(<<<CSS
#dailyChart { min-height: 250px; }
#artistsChart { min-height: 280px; }
#albumsChart { min-height: 280px; }
CSS);
?>
