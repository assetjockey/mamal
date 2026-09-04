<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>
<div class="card mb-5">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="h5 text-truncate mb-0"><i class="fas fa-fw fa-comments fa-xs text-primary-900 mr-2"></i> <?= l('admin_annotations.header') ?></h2>

            <div>
                <span class="badge <?= $data->total['annotations'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['annotations'] > 0 ? '+' : null) . nr($data->total['annotations']) ?></span>
            </div>
        </div>

        <div class="chart-container <?= $data->total['annotations'] ? null : 'd-none' ?>">
            <canvas id="annotations"></canvas>
        </div>
        <?= $data->total['annotations'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
    </div>
</div>
<?php $html = ob_get_clean() ?>

<?php ob_start() ?>
<script>
    'use strict';

    let color = css.getPropertyValue('--primary');
    let color_gradient = null;

    /* Prepare chart */
    let annotations_chart = document.getElementById('annotations').getContext('2d');
    color_gradient = annotations_chart.createLinearGradient(0, 0, 0, 250);
    color_gradient.addColorStop(0, set_hex_opacity(color, 0.1));
    color_gradient.addColorStop(1, set_hex_opacity(color, 0.025));

    /* Display chart */
    new Chart(annotations_chart, {
        type: 'line',
        data: {
            labels: <?= $data->annotations_chart['labels'] ?>,
            datasets: [{
                label: <?= json_encode(l('admin_annotations.title')) ?>,
                data: <?= $data->annotations_chart['annotations'] ?? '[]' ?>,
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
