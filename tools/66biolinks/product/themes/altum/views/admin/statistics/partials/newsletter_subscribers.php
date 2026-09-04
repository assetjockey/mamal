<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>
<div class="card mb-5">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="h5 text-truncate mb-0"><i class="fas fa-fw fa-users fa-xs text-primary-900 mr-2"></i> <?= l('admin_statistics.newsletter_subscribers.header') ?></h2>

            <div>
                <span class="badge <?= $data->total['newsletter_subscribers'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['newsletter_subscribers'] > 0 ? '+' : null) . nr($data->total['newsletter_subscribers']) ?></span>
            </div>
        </div>

        <div class="chart-container <?= $data->total['newsletter_subscribers'] ? null : 'd-none' ?>">
            <canvas id="newsletter_subscribers"></canvas>
        </div>
        <?= $data->total['newsletter_subscribers'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
    </div>
</div>
<?php $html = ob_get_clean() ?>

<?php ob_start() ?>
<script>
    'use strict';
    
    let color = css.getPropertyValue('--primary');
    let color_gradient = null;

    /* Prepare chart */
    let newsletter_subscribers_chart = document.getElementById('newsletter_subscribers').getContext('2d');
    color_gradient = newsletter_subscribers_chart.createLinearGradient(0, 0, 0, 250);
    color_gradient.addColorStop(0, set_hex_opacity(color, 0.1));
    color_gradient.addColorStop(1, set_hex_opacity(color, 0.025));

    /* Display chart */
    new Chart(newsletter_subscribers_chart, {
        type: 'line',
        data: {
            labels: <?= $data->newsletter_subscribers_chart['labels'] ?>,
            datasets: [{
                label: <?= json_encode(l('admin_statistics.newsletter_subscribers.chart')) ?>,
                data: <?= $data->newsletter_subscribers_chart['newsletter_subscribers'] ?? '[]' ?>,
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
