<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>
<div class="card mb-5">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-4">
            <h2 class="h5 text-truncate mb-0"><i class="fas fa-fw fa-chart-bar fa-xs text-primary-900 mr-2"></i> <?= l('admin_statistics.heartbeats_logs.header') ?></h2>

            <div>
                <span class="badge <?= $data->total['heartbeats_logs'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['heartbeats_logs'] > 0 ? '+' : null) . nr($data->total['heartbeats_logs']) ?></span>
            </div>
        </div>

        <div class="chart-container <?= $data->total['heartbeats_logs'] ? null : 'd-none' ?>">
            <canvas id="heartbeats_logs"></canvas>
        </div>
        <?= $data->total['heartbeats_logs'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
    </div>
</div>

<?php $html = ob_get_clean() ?>

<?php ob_start() ?>
<script>
    'use strict';
    
let color = css.getPropertyValue('--primary');
    let color_gradient = null;

    /* Display chart */
    let heartbeats_logs_chart = document.getElementById('heartbeats_logs').getContext('2d');
    color_gradient = heartbeats_logs_chart.createLinearGradient(0, 0, 0, 250);
    color_gradient.addColorStop(0, set_hex_opacity(color, 0.1));
    color_gradient.addColorStop(1, set_hex_opacity(color, 0.025));

    new Chart(heartbeats_logs_chart, {
        type: 'line',
        data: {
            labels: <?= $data->heartbeats_logs_chart['labels'] ?>,
            datasets: [
                {
                    label: <?= json_encode(l('admin_statistics.heartbeats_logs.chart')) ?>,
                    data: <?= $data->heartbeats_logs_chart['heartbeats_logs'] ?? '[]' ?>,
                    backgroundColor: color_gradient,
                    borderColor: color,
                    fill: true
                }
            ]
        },
        options: chart_options
    });
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
