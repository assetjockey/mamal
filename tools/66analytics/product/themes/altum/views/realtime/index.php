<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-end mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-signal mr-1"></i> <?= l('realtime.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= sprintf(l('realtime.info'), 5) ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="card border-0">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-4 d-flex justify-content-center align-items-center">
                    <div class="text-center">
                        <div id="visitors_total_result" class="h1"></div>

                        <?php if($this->website->tracking_type == 'advanced'): ?>
                            <span class="text-muted"><?= l('realtime.visitors') ?></span>
                        <?php endif ?>

                        <?php if($this->website->tracking_type == 'lightweight'): ?>
                            <span class="text-muted"><?= l('realtime.pageviews') ?></span>
                        <?php endif ?>
                    </div>
                </div>

                <div class="col-12 col-md-8">
                    <div class="chart-container">
                        <canvas id="logs_chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h5 m-0"><?= l('global.countries') ?></h2>
                        </div>
                        <span class="round-circle-sm bg-gray-200 text-primary-700 p-3">
                            <i class="fas fa-fw fa-sm fa-flag"></i>
                        </span>
                    </div>

                    <div class="mt-4" id="countries_html_result"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h5 m-0"><?= l('dashboard.device_types.header') ?></h2>
                        </div>
                        <span class="round-circle-sm bg-gray-200 text-primary-700 p-3">
                            <i class="fas fa-fw fa-sm fa-laptop"></i>
                        </span>
                    </div>

                    <div class="mt-4" id="device_types_html_result"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h5 m-0"><?= l('dashboard.paths.header') ?></h2>
                        </div>
                        <span class="round-circle-sm bg-gray-200 text-primary-700 p-3">
                            <i class="fas fa-fw fa-sm fa-copy"></i>
                        </span>
                    </div>

                    <div class="mt-4" id="paths_html_result"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<input type="hidden" name="website_id" value="<?= $this->website->website_id ?>" />
<input type="hidden" name="pixel_key" value="<?= $this->website->pixel_key ?>" />

<?php require THEME_PATH . 'views/partials/js_chart_defaults.php' ?>

<?php ob_start() ?>

<script>
    'use strict';

    /* Put the loading placeholders */
    let loading_html = $('#loading').html();
    for (let request_type of ['visitors_total', 'paths_html', 'countries_html', 'device_types_html']) {
        $(`#${request_type}_result`).html(loading_html);
    }

    let css = window.getComputedStyle(document.body);
    let color = css.getPropertyValue('--primary');
    let color_gradient = null;

    /* Chart */
    Chart.defaults.elements.line.borderWidth = 4;
    Chart.defaults.elements.point.radius = 2;
    let chart = document.getElementById('logs_chart').getContext('2d');

    /* Colors */
    color_gradient = chart.createLinearGradient(0, 0, 0, 250);
    color_gradient.addColorStop(0, set_hex_opacity(color, 0.9));
    color_gradient.addColorStop(1, set_hex_opacity(color, 0.6));

    let pageviews_chart = new Chart(chart, {
        type: 'bar',
        data: {
            labels: null,
            datasets: [{
                data: null,
                backgroundColor: color_gradient,
                borderColor: color,
                fill: true,
                label: <?= json_encode(l('dashboard.basic.chart.pageviews')) ?>
            }]
        },
        options: chart_options
    });

    /* Basic data to use for fetching extra data */
    let website_id = $('input[name="website_id"]').val();
    let pixel_key = $('input[name="pixel_key"]').val();
    let tracking_type = <?= json_encode($this->website->tracking_type) ?>;
    let start_date = 'now';
    let end_date = 'now';
    let request_subtype = 'realtime';
    let dashboard_ajax_url = `${url}statistics-ajax-${tracking_type}?website_id=${website_id}&request_subtype=${request_subtype}&start_date=${start_date}&end_date=${end_date}&global_token=${global_token}`;
    let source = <?= json_encode(\Altum\Router::$controller_key) ?>;

    let load = () => {

        /* Send the request */
        $.ajax({
            type: 'GET',
            url: `${dashboard_ajax_url}&request_type=realtime&limit=10&source=${source}&pixel_key=${pixel_key}`,
            success: (data) => {

                for (let request_type of ['visitors_total', 'paths_html', 'countries_html', 'device_types_html']) {
                    $(`#${request_type}_result`).html(data.details[request_type]);
                }

                /* Update the title */
                let title = <?= json_encode(l('realtime.title') . ' - ' . \Altum\Title::$site_title) ?>;
                let title_dynamic = <?= json_encode(l('realtime.title_dynamic')) ?>.replace('%s', data.details.visitors_total);

                document.title = `${title_dynamic} - ${title}`;

                /* Update the chart */
                let labels = JSON.parse(data.details.logs_chart_labels);
                let pageviews_dataset_data = JSON.parse(data.details.logs_chart_pageviews);

                pageviews_chart.data.labels = labels;
                pageviews_chart.data.datasets[0].data = pageviews_dataset_data;

                pageviews_chart.update();

            },
            dataType: 'json'
        });
    };

    load();

    setInterval(load, 5000);
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
