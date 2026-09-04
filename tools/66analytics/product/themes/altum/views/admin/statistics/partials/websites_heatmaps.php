<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>
<div class="card mb-5">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="h5 text-truncate mb-0"><i class="fas fa-fw fa-fire fa-xs text-primary-900 mr-2"></i> <?= l('admin_statistics.websites_heatmaps.header') ?></h2>

            <div>
                <span class="badge <?= $data->total['websites_heatmaps'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['websites_heatmaps'] > 0 ? '+' : null) . nr($data->total['websites_heatmaps']) ?></span>
            </div>
        </div>

        <div class="chart-container <?= $data->total['websites_heatmaps'] ? null : 'd-none' ?>">
            <canvas id="websites_heatmaps"></canvas>
        </div>
        <?= $data->total['websites_heatmaps'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
    </div>
</div>

<div class="card mb-5">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="h5 text-truncate mb-0"><i class="fas fa-fw fa-hdd fa-xs text-primary-900 mr-2"></i> <?= l('admin_statistics.websites_heatmaps.chart_total_size') ?></h2>

            <div>
                <span class="badge <?= $data->total['websites_heatmaps_total_size'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['websites_heatmaps_total_size'] > 0 ? '+' : null) . get_formatted_bytes($data->total['websites_heatmaps_total_size']) ?></span>
            </div>
        </div>

        <div class="chart-container <?= $data->total['websites_heatmaps_total_size'] ? null : 'd-none' ?>">
            <canvas id="websites_heatmaps_total_size"></canvas>
        </div>
        <?= $data->total['websites_heatmaps_total_size'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
    </div>
</div>

<?php $html = ob_get_clean() ?>

<?php ob_start() ?>
<script>
    'use strict';
    
let get_formatted_bytes = (bytes) => {
        let selected_size = 0;
        let selected_unit = 'B';

        if(bytes > 0) {
            let units = ['TB', 'GB', 'MB', 'KB', 'B'];

            for (let i = 0; i < units.length; i++) {
                let unit = units[i];
                let cutoff = Math.pow(1000, 4 - i) / 10;

                if(bytes >= cutoff) {
                    selected_size = bytes / Math.pow(1000, 4 - i);
                    selected_unit = unit;
                    break;
                }
            }

            selected_size = Math.round(10 * selected_size) / 10;
        }

        return `${selected_size} ${selected_unit}`;
    }

    let color = css.getPropertyValue('--primary');
    let color_gradient = null;

    /* Display chart */
    let websites_heatmaps_chart = document.getElementById('websites_heatmaps').getContext('2d');
    color_gradient = websites_heatmaps_chart.createLinearGradient(0, 0, 0, 250);
    color_gradient.addColorStop(0, set_hex_opacity(color, 0.1));
    color_gradient.addColorStop(1, set_hex_opacity(color, 0.025));

    new Chart(websites_heatmaps_chart, {
        type: 'line',
        data: {
            labels: <?= $data->websites_heatmaps_chart['labels'] ?>,
            datasets: [
                {
                    label: <?= json_encode(l('admin_statistics.websites_heatmaps.chart')) ?>,
                    data: <?= $data->websites_heatmaps_chart['websites_heatmaps'] ?? '[]' ?>,
                    backgroundColor: color_gradient,
                    borderColor: color,
                    fill: true
                }
            ]
        },
        options: chart_options
    });

    /* Display chart */
    let websites_heatmaps_total_size = document.getElementById('websites_heatmaps_total_size').getContext('2d');
    color_gradient = websites_heatmaps_total_size.createLinearGradient(0, 0, 0, 250);
    color_gradient.addColorStop(0, set_hex_opacity(color, 0.1));
    color_gradient.addColorStop(1, set_hex_opacity(color, 0.025));

    new Chart(websites_heatmaps_total_size, {
        type: 'bar',
        data: {
            labels: <?= $data->websites_heatmaps_chart['labels'] ?>,
            datasets: [
                {
                    label: <?= json_encode(l('admin_statistics.websites_heatmaps.chart_total_size')) ?>,
                    data: <?= $data->websites_heatmaps_chart['websites_heatmaps_total_size'] ?? '[]' ?>,
                    backgroundColor: color,
                    borderColor: color,
                    fill: true
                }
            ]
        },
        options: {
            ...chart_options,
            plugins: {
                ...chart_options.plugins,
                tooltip: {
                    ...chart_options.plugins.tooltip,
                    callbacks: {
                        ...chart_options.plugins.tooltip.callbacks,
                        label: (context) => {
                            return `${context.dataset.label}: ${get_formatted_bytes(context.raw)}`;
                        }
                    }
                }
            },

            scales: {
                ...chart_options.scales,
                y: {
                    ...chart_options.scales.y,
                    ticks: {
                        callback: (value, index, ticks) => {
                            return `${get_formatted_bytes(value)}`;
                        }
                    }
                }
            }
        }
    });
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
