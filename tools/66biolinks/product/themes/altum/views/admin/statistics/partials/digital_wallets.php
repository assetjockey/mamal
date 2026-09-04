<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>
<div class="card mb-5">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="h5 text-truncate mb-0"><i class="fas fa-fw fa-wifi fa-xs text-primary-900 mr-2"></i> <?= l('admin_statistics.digital_wallets.header') ?></h2>

            <div>
                <span class="badge <?= $data->total['digital_wallets'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['digital_wallets'] > 0 ? '+' : null) . nr($data->total['digital_wallets']) ?></span>
            </div>
        </div>

        <div class="chart-container <?= $data->total['digital_wallets'] ? null : 'd-none' ?>">
            <canvas id="digital_wallets"></canvas>
        </div>
        <?= $data->total['digital_wallets'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
    </div>
</div>
<?php $html = ob_get_clean() ?>

<?php ob_start() ?>
<script>
    'use strict';

    let color = css.getPropertyValue('--primary');
    let color_gradient = null;

    /* Prepare chart */
    let digital_wallets_chart = document.getElementById('digital_wallets').getContext('2d');
    color_gradient = digital_wallets_chart.createLinearGradient(0, 0, 0, 250);
    color_gradient.addColorStop(0, set_hex_opacity(color, 0.1));
    color_gradient.addColorStop(1, set_hex_opacity(color, 0.025));

    /* Display chart */
    new Chart(digital_wallets_chart, {
        type: 'line',
        data: {
            labels: <?= $data->digital_wallets_chart['labels'] ?>,
            datasets: [{
                label: <?= json_encode(l('admin_statistics.digital_wallets.chart')) ?>,
                data: <?= $data->digital_wallets_chart['digital_wallets'] ?? '[]' ?>,
                backgroundColor: color_gradient,
                borderColor: color,
                fill: true
            }]
        },
        options: chart_options
    });
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
