<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>
<div class="card mb-5">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="h5 text-truncate mb-0"><i class="fas fa-fw fa-chart-bar fa-xs text-primary-900 mr-2"></i> <?= l('admin_statistics.track_conversions.header') ?></h2>

            <div>
                <span data-toggle="tooltip" title="<?= l('admin_statistics.track_conversions.chart_webhook') ?>" class="badge <?= $data->total['webhook'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['webhook'] > 0 ? '+' : null) . nr($data->total['webhook']) ?></span>
                <span data-toggle="tooltip" title="<?= l('admin_statistics.track_conversions.chart_auto_capture') ?>" class="badge <?= $data->total['auto_capture'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['auto_capture'] > 0 ? '+' : null) . nr($data->total['auto_capture']) ?></span>
                <span data-toggle="tooltip" title="<?= l('admin_statistics.track_conversions.chart_collector') ?>" class="badge <?= $data->total['collector'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['collector'] > 0 ? '+' : null) . nr($data->total['collector']) ?></span>
                <span data-toggle="tooltip" title="<?= l('admin_statistics.track_conversions.chart_imported') ?>" class="badge <?= $data->total['imported'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['imported'] > 0 ? '+' : null) . nr($data->total['imported']) ?></span>
            </div>
        </div>

        <div class="chart-container <?= $data->total['webhook'] + $data->total['auto_capture'] + $data->total['collector'] + $data->total['imported'] ? null : 'd-none' ?>">
            <canvas id="track_conversions"></canvas>
        </div>
        <?= $data->total['webhook'] + $data->total['auto_capture'] + $data->total['collector'] + $data->total['imported'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
    </div>
</div>
<?php $html = ob_get_clean() ?>

<?php ob_start() ?>
<script>
    'use strict';
    
    let webhook_color = css.getPropertyValue('--teal');
    let auto_capture_color = css.getPropertyValue('--indigo');
    let collector_color = css.getPropertyValue('--cyan');
    let imported_color = css.getPropertyValue('--blue');

    /* Display chart */
    new Chart(document.getElementById('track_conversions').getContext('2d'), {
        type: 'line',
        data: {
            labels: <?= $data->track_conversions_chart['labels'] ?>,
            datasets: [
                {
                    label: <?= json_encode(l('admin_statistics.track_conversions.chart_webhook')) ?>,
                    data: <?= $data->track_conversions_chart['webhook'] ?? '[]' ?>,
                    backgroundColor: webhook_color,
                    borderColor: webhook_color,
                    fill: false
                },
                {
                    label: <?= json_encode(l('admin_statistics.track_conversions.chart_auto_capture')) ?>,
                    data: <?= $data->track_conversions_chart['auto_capture'] ?? '[]' ?>,
                    backgroundColor: auto_capture_color,
                    borderColor: auto_capture_color,
                    fill: false
                },
                {
                    label: <?= json_encode(l('admin_statistics.track_conversions.chart_collector')) ?>,
                    data: <?= $data->track_conversions_chart['collector'] ?? '[]' ?>,
                    backgroundColor: collector_color,
                    borderColor: collector_color,
                    fill: false
                },
                {
                    label: <?= json_encode(l('admin_statistics.track_conversions.chart_imported')) ?>,
                    data: <?= $data->track_conversions_chart['imported'] ?? '[]' ?>,
                    backgroundColor: imported_color,
                    borderColor: imported_color,
                    fill: false
                }
            ]
        },
        options: chart_options
    });
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
