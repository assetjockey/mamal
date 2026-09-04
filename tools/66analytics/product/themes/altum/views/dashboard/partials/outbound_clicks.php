<?php defined('ALTUMCODE') || die() ?>

<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0">
            <div class="card-body">
                <div id="outbound_clicks_chart_loading"></div>

                <div id="logs_chart_container" class="chart-container d-none">
                    <canvas id="logs_chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 m-0"><?= l('dashboard.outbound_clicks.header') ?></h2>
                    </div>
                    <span class="round-circle-sm bg-gray-200 text-primary-700 p-3">
                        <i class="fas fa-fw fa-sm fa-external-link-alt"></i>
                    </span>
                </div>

                <div class="mt-4" id="outbound_clicks_result" data-limit="-1"></div>
            </div>
        </div>
    </div>
</div>

<?php require THEME_PATH . 'views/partials/js_chart_defaults.php' ?>
<?php ob_start() ?>
<script>
    'use strict';
    
    let css = window.getComputedStyle(document.body);
    let color = css.getPropertyValue('--primary');
    let color_gradient = null;

    /* Chart */
    Chart.defaults.elements.line.borderWidth = 4;
    Chart.defaults.elements.point.radius = 2;
    let chart = document.getElementById('logs_chart').getContext('2d');

    /* Colors */
    color_gradient = chart.createLinearGradient(0, 0, 0, 250);
    color_gradient.addColorStop(0, set_hex_opacity(color, 0.6));
    color_gradient.addColorStop(1, set_hex_opacity(color, 0.1));

    let clicks_chart = new Chart(chart, {
        type: 'line',
        data: {
            labels: null,
            datasets: [{
                data: null,
                backgroundColor: color_gradient,
                borderColor: color,
                fill: true,
                label: <?= json_encode(l('analytics.clicks')) ?>
            }]
        },
        options: chart_options
    });

    let load = () => {
        /* Default loading state */
        let outbound_clicks_chart_loading = document.querySelector('#outbound_clicks_chart_loading');
        outbound_clicks_chart_loading.innerHTML = document.querySelector('#loading').innerHTML;

        /* Basic data to use for fetching extra data */
        let tracking_type = <?= json_encode($this->website->tracking_type) ?>;
        let website_id = $('input[name="website_id"]').val();
        let pixel_key = $('input[name="pixel_key"]').val();
        let start_date = $('input[name="start_date"]').val();
        let end_date = $('input[name="end_date"]').val();
        let request_type = 'outbound_clicks_chart';
        let source = <?= json_encode(\Altum\Router::$controller_key) ?>;

        let url_query = build_url_query({
            website_id,
            pixel_key,
            start_date,
            end_date,
            global_token,
            request_type,
            source,
            limit: -1,
            bounce_rate: null,
        });

        /* Send the request */
        $.ajax({
            type: 'GET',
            url: `${url}statistics-ajax-${tracking_type}?${url_query}`,
            success: (data) => {

                /* Update the chart */
                let labels = JSON.parse(data.details.logs_chart_labels);
                let clicks_dataset_data = JSON.parse(data.details.logs_chart_clicks);

                clicks_chart.data.labels = labels;
                clicks_chart.data.datasets[0].data = clicks_dataset_data;

                clicks_chart.update();

                /* Show chart & hide loading */
                outbound_clicks_chart_loading.innerHTML = '';
                document.querySelector('#logs_chart_container').classList.remove('d-none');


            },
            dataType: 'json'
        });
    }

    load();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
