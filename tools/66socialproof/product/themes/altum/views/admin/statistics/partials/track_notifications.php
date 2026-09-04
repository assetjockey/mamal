<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>
<div class="card mb-5">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="h5 text-truncate mb-0"><i class="fas fa-fw fa-chart-bar fa-xs text-primary-900 mr-2"></i> <?= l('admin_statistics.track_notifications.header') ?></h2>

            <div>
                <span data-toggle="tooltip" title="<?= l('admin_statistics.track_notifications.chart_impression') ?>" class="badge <?= $data->total['impression'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['impression'] > 0 ? '+' : null) . nr($data->total['impression']) ?></span>
                <span data-toggle="tooltip" title="<?= l('admin_statistics.track_notifications.chart_hover') ?>" class="badge <?= $data->total['hover'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['hover'] > 0 ? '+' : null) . nr($data->total['hover']) ?></span>
                <span data-toggle="tooltip" title="<?= l('admin_statistics.track_notifications.chart_click') ?>" class="badge <?= $data->total['click'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['click'] > 0 ? '+' : null) . nr($data->total['click']) ?></span>
                <span data-toggle="tooltip" title="<?= l('admin_statistics.track_notifications.chart_form_submission') ?>" class="badge <?= $data->total['form_submission'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['form_submission'] > 0 ? '+' : null) . nr($data->total['form_submission']) ?></span>
            </div>
        </div>

        <div class="chart-container <?= $data->total['impression'] + $data->total['hover'] + $data->total['click'] + $data->total['form_submission'] ? null : 'd-none' ?>">
            <canvas id="track_notifications"></canvas>
        </div>
        <?= $data->total['impression'] + $data->total['hover'] + $data->total['click'] + $data->total['form_submission'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
    </div>
</div>
<?php $html = ob_get_clean() ?>

<?php ob_start() ?>
<script>
    'use strict';
    
    let impression_color = css.getPropertyValue('--teal');
    let hover_color = css.getPropertyValue('--indigo');
    let click_color = css.getPropertyValue('--cyan');
    let form_submission_color = css.getPropertyValue('--blue');

    /* Display chart */
    new Chart(document.getElementById('track_notifications').getContext('2d'), {
        type: 'line',
        data: {
            labels: <?= $data->track_notifications_chart['labels'] ?>,
            datasets: [
                {
                    label: <?= json_encode(l('admin_statistics.track_notifications.chart_impression')) ?>,
                    data: <?= $data->track_notifications_chart['impression'] ?? '[]' ?>,
                    backgroundColor: impression_color,
                    borderColor: impression_color,
                    fill: false
                },
                {
                    label: <?= json_encode(l('admin_statistics.track_notifications.chart_hover')) ?>,
                    data: <?= $data->track_notifications_chart['hover'] ?? '[]' ?>,
                    backgroundColor: hover_color,
                    borderColor: hover_color,
                    fill: false
                },
                {
                    label: <?= json_encode(l('admin_statistics.track_notifications.chart_click')) ?>,
                    data: <?= $data->track_notifications_chart['click'] ?? '[]' ?>,
                    backgroundColor: click_color,
                    borderColor: click_color,
                    fill: false
                },
                {
                    label: <?= json_encode(l('admin_statistics.track_notifications.chart_form_submission')) ?>,
                    data: <?= $data->track_notifications_chart['form_submission'] ?? '[]' ?>,
                    backgroundColor: form_submission_color,
                    borderColor: form_submission_color,
                    fill: false
                }
            ]
        },
        options: chart_options
    });
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
