<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>
<div class="card mb-5">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="h5 text-truncate mb-0"><i class="fas fa-fw fa-external-link-alt fa-xs text-primary-900 mr-2"></i> <?= l('admin_statistics.outbound_clicks.header') ?></h2>

            <div>
                <span class="badge <?= $data->total['outbound_clicks'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['outbound_clicks'] > 0 ? '+' : null) . nr($data->total['outbound_clicks']) ?></span>
            </div>
        </div>

        <div class="chart-container <?= $data->total['outbound_clicks'] ? null : 'd-none' ?>">
            <canvas id="outbound_clicks"></canvas>
        </div>
        <?= $data->total['outbound_clicks'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
    </div>
</div>

<?php $html = ob_get_clean() ?>

<?php ob_start() ?>
<script>
    'use strict';
    
let color = css.getPropertyValue('--primary');
    let color_gradient = null;

    /* Display chart */
    let outbound_clicks_chart = document.getElementById('outbound_clicks').getContext('2d');
    color_gradient = outbound_clicks_chart.createLinearGradient(0, 0, 0, 250);
    color_gradient.addColorStop(0, set_hex_opacity(color, 0.1));
    color_gradient.addColorStop(1, set_hex_opacity(color, 0.025));

    new Chart(outbound_clicks_chart, {
        type: 'line',
        data: {
            labels: <?= $data->outbound_clicks_chart['labels'] ?>,
            datasets: [
                {
                    label: <?= json_encode(l('admin_statistics.outbound_clicks.chart')) ?>,
                    data: <?= $data->outbound_clicks_chart['outbound_clicks'] ?? '[]' ?>,
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
