<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('server-monitors') ?>"><?= l('server_monitors.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('server_monitor.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="card bg-blue-900 border-0 rounded-2x">
        <div class="card-body">
            <div class="row">
                <div class="col-auto d-flex align-items-center">
                    <?php if($data->server_monitor->is_enabled): ?>
                        <div data-toggle="tooltip" title="<?= l('server_monitor.is_enabled') ?>" class="pulse-animation pulse-animation-success">
                            <span class="pulse-circle"></span>
                            <i class="fas fa-fw fa-check-circle fa-3x text-primary-400"></i>
                        </div>
                    <?php else: ?>
                        <div data-toggle="tooltip" title="<?= l('server_monitor.is_enabled_paused') ?>">
                            <i class="fas fa-fw fa-pause-circle fa-3x text-warning"></i>
                        </div>
                    <?php endif ?>
                </div>

                <div class="col text-truncate">
                    <h1 class="h3 text-truncate text-white mb-0 mr-2"><?= sprintf(l('server_monitor.header'), $data->server_monitor->name) ?></h1>

                    <div class="text-truncate text-gray-400">
                        <span><?= $data->server_monitor->target ?></span>
                    </div>
                </div>

                <div class="col-auto">
                    <?= include_view(THEME_PATH . 'views/server-monitor/server_monitor_dropdown_button.php', ['id' => $data->server_monitor->server_monitor_id, 'resource_name' => $data->server_monitor->name, 'button_text_class' => 'text-white']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3 ">
        <div class="col">
            <button class="btn btn-outline-success btn-block" data-toggle="modal" data-target="#server_monitor_install_modal" data-server-monitor-id="<?= $data->server_monitor->server_monitor_id ?>" data-api-key="<?= $this->user->api_key ?>" data-name="<?= $data->server_monitor->name ?>" data-server-check-interval-seconds="<?= $data->server_monitor->settings->server_check_interval_seconds ?>">
                <i class="fas fa-fw fa-sm fa-code mr-1"></i> <?= l('server_monitor_install_modal.header') ?>
            </button>
        </div>

        <div class="col">
            <button class="btn btn-outline-secondary btn-block" data-toggle="modal" data-target="#server_monitor_uninstall_modal" data-user-id="<?= $this->user->user_id ?>" data-api-key="<?= $this->user->api_key ?>" data-name="<?= $data->server_monitor->name ?>" data-server-check-interval-seconds="<?= $data->server_monitor->settings->server_check_interval_seconds ?>">
                <i class="fas fa-fw fa-sm fa-times mr-1"></i> <?= l('server_monitor_uninstall_modal.header') ?>
            </button>
        </div>
    </div>


    <?php if(!$data->server_monitor->total_logs): ?>
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex flex-column align-items-center justify-content-center py-4">
                    <img src="<?= ASSETS_FULL_URL . 'images/processing.svg' ?>" class="col-10 col-md-7 col-lg-5 mb-3" alt="<?= l('server_monitor.no_data') ?>" />
                    <h2 class="h4 text-muted"><?= l('server_monitor.no_data') ?></h2>
                    <p class="text-muted"><?= sprintf(l('server_monitor.no_data_help'), $data->server_monitor->name) ?></p>
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if($data->server_monitor->total_logs): ?>
        <div class="row justify-content-between my-3">
            <div class="col-12 col-lg-6 col-xl-4 p-3">
                <div class="card h-100">
                    <div class="card-body d-flex p-3">
                        <div>
                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-globe fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted font-size-small"><?= l('server_monitor.total_logs') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0"><?= nr($data->server_monitor->total_logs) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6 col-xl-4 p-3">
                <div class="card h-100">
                    <div class="card-body d-flex p-3">
                        <div>
                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-calendar-check fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted font-size-small"><?= l('server_monitor.last_log_datetime') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0"><?= $data->server_monitor->last_log_datetime ? \Altum\Date::get_timeago($data->server_monitor->last_log_datetime) : l('global.na') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4 p-3">
                <div class="card h-100">
                    <div class="card-body d-flex p-3">
                        <div>
                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-microchip fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted font-size-small"><?= l('server_monitor.cpu_usage') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0"><?= nr($data->server_monitor->cpu_usage, settings()->monitors_heartbeats->decimals) . '%' ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4 p-3">
                <div class="card h-100">
                    <div class="card-body d-flex p-3">
                        <div>
                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-memory fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted font-size-small"><?= l('server_monitor.ram_usage') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0"><?= nr($data->server_monitor->ram_usage, settings()->monitors_heartbeats->decimals) . '%' ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4 p-3">
                <div class="card h-100">
                    <div class="card-body d-flex p-3">
                        <div>
                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-save fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted font-size-small"><?= l('server_monitor.disk_usage') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0"><?= nr($data->server_monitor->disk_usage, settings()->monitors_heartbeats->decimals) . '%' ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <div class="d-flex">
                <div
                        id="daterangepicker"
                        role="button"
                        class="btn btn-sm btn-light"
                        data-min-date="<?= \Altum\Date::get($data->server_monitor->datetime, 4) ?>"
                        data-max-date="<?= \Altum\Date::get('', 4) ?>"
                >
                    <i class="fas fa-fw fa-calendar mr-lg-1"></i>
                    <span class="d-none d-lg-inline-block">
                        <?php if($data->datetime['start_date'] == $data->datetime['end_date']): ?>
                            <?= \Altum\Date::get($data->datetime['start_date'], 6, \Altum\Date::$default_timezone) ?>
                        <?php else: ?>
                            <?= \Altum\Date::get($data->datetime['start_date'], 6, \Altum\Date::$default_timezone) . ' - ' . \Altum\Date::get($data->datetime['end_date'], 6, \Altum\Date::$default_timezone) ?>
                        <?php endif ?>
                    </span>
                    <i class="fas fa-fw fa-caret-down d-none d-lg-inline-block ml-lg-1"></i>
                </div>

                <div class="ml-2">
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                            <i class="fas fa-fw fa-sm fa-download"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right d-print-none">
                            <a href="<?= url('server-monitor/' . $data->server_monitor->server_monitor_id . '?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date'] . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                                <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                            </a>
                            <a href="<?= url('server-monitor/' . $data->server_monitor->server_monitor_id . '?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date'] . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
                                <i class="fas fa-fw fa-sm fa-file-code mr-2"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                            </a>
                            <a href="#" class="dropdown-item <?= $this->user->plan_settings->export->pdf ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->pdf ? 'onclick="event.preventDefault(); window.print();"' : get_plan_feature_disabled_info() ?>>
                                <i class="fas fa-fw fa-sm fa-file-pdf mr-2"></i> <?= sprintf(l('global.export_to'), 'PDF') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if($data->total_server_monitor_logs): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h6">
                            <i class="fas fa-fw fa-microchip mr-1"></i> <?= l('server_monitor.cpu_usage') ?>
                        </h2>

                        <div class="text-muted">
                            <div>
                                <small><?= $data->server_monitor->cpu_model ?></small>
                            </div>
                            <div>
                                <small><?= sprintf(l('server_monitor.cpu_cores_x'), nr($data->server_monitor->cpu_cores)) ?> @ <?= nr($data->server_monitor->cpu_frequency) . ' MHz' ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="chart-container">
                        <canvas id="server_monitor_cpu_usage_chart"></canvas>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h2 class="h6">
                            <i class="fas fa-fw fa-memory mr-1"></i> <?= l('server_monitor.ram_usage') ?>
                        </h2>

                        <div class="text-muted">
                            <span>
                                <?= nr(mb_to_gb($data->server_monitor->ram_used), 2) . ' GB' ?>
                            </span>
                            /
                            <span data-toggle="tooltip" title="<?= l('server_monitor.ram_total') ?>">
                                <?= nr(mb_to_gb($data->server_monitor->ram_total), 2) . ' GB' ?>
                            </span>
                        </div>
                    </div>

                    <div class="chart-container">
                        <canvas id="server_monitor_ram_usage_chart"></canvas>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h2 class="h6">
                            <i class="fas fa-fw fa-save mr-1"></i> <?= l('server_monitor.disk_usage') ?>
                        </h2>

                        <div class="text-muted">
                            <span>
                                <?= nr(mb_to_gb($data->server_monitor->disk_used), 2) . ' GB' ?>
                            </span>
                            /
                            <span data-toggle="tooltip" title="<?= l('server_monitor.disk_total') ?>">
                                <?= nr(mb_to_gb($data->server_monitor->disk_total), 2) . ' GB' ?>
                            </span>
                        </div>
                    </div>

                    <div class="chart-container">
                        <canvas id="server_monitor_disk_usage_chart"></canvas>
                    </div>
                </div>
            </div>

            <?php if($data->server_monitor->cpu_load_1): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h2 class="h6">
                            <i class="fas fa-fw fa-server mr-1"></i> <?= l('server_monitor.cpu_load') ?>
                        </h2>

                        <div class="chart-container">
                            <canvas id="server_monitor_cpu_load_chart"></canvas>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <div class="card mt-4">
                <div class="card-body">
                    <h2 class="h6">
                        <i class="fas fa-fw fa-network-wired mr-1"></i> <?= l('server_monitor.network') ?>
                    </h2>

                    <div class="chart-container">
                        <canvas id="server_monitor_network_chart"></canvas>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center justify-content-center py-4">
                        <img src="<?= ASSETS_FULL_URL . 'images/processing.svg' ?>" class="col-10 col-md-7 col-lg-5 mb-3" alt="<?= l('server_monitor.no_logs') ?>" />
                        <h2 class="h4 text-muted"><?= l('server_monitor.no_logs') ?></h2>
                        <p class="text-muted"><?= sprintf(l('server_monitor.no_logs_help'), $data->server_monitor->name) ?></p>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <div class="mt-5">
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <tbody>
                    <?php if($data->server_monitor->uptime): ?>
                        <tr>
                            <td class="font-weight-500 font-size-little-small text-muted text-truncate">
                                <i class="fas fa-fw fa-clock fa-xs text-muted mr-1"></i>
                                <?= l('server_monitor.uptime') ?>
                            </td>
                            <td class="text-truncate">
                                <?php $date = (new \DateTime())->modify('-' . $data->server_monitor->uptime . ' seconds')->format('Y-m-d H:i:s'); ?>
                                <?= \Altum\Date::get_elapsed_time($date) ?>
                            </td>
                        </tr>
                    <?php endif ?>

                    <?php if($data->server_monitor->network_total_download): ?>
                        <tr>
                            <td class="font-weight-500 font-size-little-small text-muted text-truncate">
                                <i class="fas fa-fw fa-download fa-xs text-muted mr-1"></i>
                                <?= l('server_monitor.network_total_download') ?>
                            </td>
                            <td class="text-truncate">
                                <?= get_formatted_bytes($data->server_monitor->network_total_download) ?>

                                <span data-toggle="tooltip" title="<?= l('server_monitor.network_total_help') ?>">
                                        <i class="fas fa-fw fa-sm fa-circle-info text-muted ml-1"></i>
                                    </span>
                            </td>
                        </tr>
                    <?php endif ?>

                    <?php if($data->server_monitor->network_total_upload): ?>
                        <tr>
                            <td class="font-weight-500 font-size-little-small text-muted text-truncate">
                                <i class="fas fa-fw fa-upload fa-xs text-muted mr-1"></i>
                                <?= l('server_monitor.network_total_upload') ?>
                            </td>
                            <td class="text-truncate">
                                <?= get_formatted_bytes($data->server_monitor->network_total_upload) ?>

                                <span data-toggle="tooltip" title="<?= l('server_monitor.network_total_help') ?>">
                                        <i class="fas fa-fw fa-sm fa-circle-info text-muted ml-1"></i>
                                    </span>
                            </td>
                        </tr>
                    <?php endif ?>

                    <?php if($data->server_monitor->os_name): ?>
                        <tr>
                            <td class="font-weight-500 font-size-little-small text-muted text-truncate">
                                <i class="fas fa-fw fa-desktop fa-xs text-muted mr-1"></i>
                                <?= l('server_monitor.os_name') ?>
                            </td>
                            <td class="text-truncate">
                                <?= $data->server_monitor->os_name ?>
                            </td>
                        </tr>
                    <?php endif ?>

                    <?php if($data->server_monitor->os_version): ?>
                        <tr>
                            <td class="font-weight-500 font-size-little-small text-muted text-truncate">
                                <i class="fas fa-fw fa-history fa-xs text-muted mr-1"></i>
                                <?= l('server_monitor.os_version') ?>
                            </td>
                            <td class="text-truncate">
                                <?= $data->server_monitor->os_version ?>
                            </td>
                        </tr>
                    <?php endif ?>

                    <?php if($data->server_monitor->kernel_name): ?>
                        <tr>
                            <td class="font-weight-500 font-size-little-small text-muted text-truncate">
                                <i class="fas fa-fw fa-brain fa-xs text-muted mr-1"></i>
                                <?= l('server_monitor.kernel_name') ?>
                            </td>
                            <td class="text-truncate">
                                <?= $data->server_monitor->kernel_name ?>
                            </td>
                        </tr>
                    <?php endif ?>

                    <?php if($data->server_monitor->kernel_version): ?>
                        <tr>
                            <td class="font-weight-500 font-size-little-small text-muted text-truncate">
                                <i class="fas fa-fw fa-code-branch fa-xs text-muted mr-1"></i>
                                <?= l('server_monitor.kernel_version') ?>
                            </td>
                            <td class="text-truncate">
                                <?= $data->server_monitor->kernel_version ?>
                            </td>
                        </tr>
                    <?php endif ?>

                    <?php if($data->server_monitor->kernel_release): ?>
                        <tr>
                            <td class="font-weight-500 font-size-little-small text-muted text-truncate">
                                <i class="fas fa-fw fa-thermometer-half fa-xs text-muted mr-1"></i>
                                <?= l('server_monitor.kernel_release') ?>
                            </td>
                            <td class="text-truncate">
                                <?= $data->server_monitor->kernel_release ?>
                            </td>
                        </tr>
                    <?php endif ?>

                    <?php if($data->server_monitor->cpu_architecture): ?>
                        <tr>
                            <td class="font-weight-500 font-size-little-small text-muted text-truncate">
                                <i class="fas fa-fw fa-cogs fa-xs text-muted mr-1"></i>
                                <?= l('server_monitor.cpu_architecture') ?>
                            </td>
                            <td class="text-truncate">
                                <?= $data->server_monitor->cpu_architecture ?>
                            </td>
                        </tr>
                    <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif ?>
</div>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/libraries/daterangepicker.min.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php require THEME_PATH . 'views/partials/js_chart_defaults.php' ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/daterangepicker.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment-timezone-with-data-10-year-range.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    'use strict';

moment.tz.setDefault(<?= json_encode($this->user->timezone) ?>);

    /* Daterangepicker */
    $('#daterangepicker').daterangepicker({
        maxSpan: {
            days: 30
        },
        startDate: <?= json_encode($data->datetime['start_date']) ?>,
        endDate: <?= json_encode($data->datetime['end_date']) ?>,
        minDate: $('#daterangepicker').data('min-date'),
        maxDate: $('#daterangepicker').data('max-date'),
        ranges: {
            <?= json_encode(l('global.date.today')) ?>: [moment(), moment()],
            <?= json_encode(l('global.date.yesterday')) ?>: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            <?= json_encode(l('global.date.this_week')) ?>: [moment().startOf('week'), moment().endOf('week')],

            <?= json_encode(l('global.date.last_30_days')) ?>: [moment().subtract(29, 'days'), moment()],
                <?= json_encode(l('global.date.this_month')) ?>: [moment().startOf('month'), moment().endOf('month')],
            <?= json_encode(l('global.date.last_month')) ?>: [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        },
        alwaysShowCalendars: true,
        linkedCalendars: false,
        singleCalendar: true,
        locale: <?= json_encode(require APP_PATH . 'includes/daterangepicker_translations.php') ?>,
    }, (start, end, label) => {

        <?php
        parse_str(\Altum\Router::$original_request_query, $original_request_query_array);
        $modified_request_query_array = array_diff_key($original_request_query_array, ['start_date' => '', 'end_date' => '']);
        ?>

        /* Redirect */
        redirect(`<?= url(\Altum\Router::$original_request . '?' . http_build_query($modified_request_query_array)) ?>&start_date=${start.format('YYYY-MM-DD')}&end_date=${end.format('YYYY-MM-DD')}`, true);

    });

    <?php if($data->total_server_monitor_logs): ?>
    let css = window.getComputedStyle(document.body)

    /* Change chart settings */
    Chart.defaults.elements.line.borderWidth = 2;
    Chart.defaults.elements.point.radius = 0;
    Chart.defaults.elements.point.hoverRadius = 2;
    Chart.defaults.elements.point.borderWidth = 0;
    Chart.defaults.elements.point.hoverBorderWidth = 2;

    /* Tooltip titles */
    let tooltip_titles = <?= $data->server_monitor_logs_chart['labels'] ?>;
    chart_options.plugins.tooltip.callbacks.title = (context) => {
        return tooltip_titles[context[0].dataIndex];
    }

    /* CPU usage chart */
    let server_monitor_cpu_usage_chart = document.getElementById('server_monitor_cpu_usage_chart').getContext('2d');

    let cpu_usage_color = css.getPropertyValue('--success');
    let cpu_usage_gradient = server_monitor_cpu_usage_chart.createLinearGradient(0, 0, 0, 250);
    cpu_usage_gradient.addColorStop(0, set_hex_opacity(cpu_usage_color, 0.25));
    cpu_usage_gradient.addColorStop(1, set_hex_opacity(cpu_usage_color, 0.025));

    /* Display chart */
    new Chart(server_monitor_cpu_usage_chart, {
        type: 'line',
        data: {
            labels: <?= $data->server_monitor_logs_chart['hour_minute_second_label'] ?>,
            datasets: [
                {
                    label: <?= json_encode(l('server_monitor.cpu_usage')) ?>,
                    data: <?= $data->server_monitor_logs_chart['cpu_usage'] ?? '[]' ?>,
                    backgroundColor: cpu_usage_gradient,
                    borderColor: cpu_usage_color,
                    fill: true
                }
            ]
        },
        options: {
            ...chart_options,
            ...{
                plugins: {
                    ...chart_options.plugins,
                    tooltip: {
                        ...chart_options.plugins.tooltip,
                        callbacks: {
                            label: context => {
                                return `${context.dataset.label}: ${nr(context.raw, 2)}%`;
                            },
                            title: (context) => {
                                return tooltip_titles[context[0].dataIndex];
                            }
                        }
                    },
                }
            }
        }
    });

    /* RAM usage chart */
    let server_monitor_ram_usage_chart = document.getElementById('server_monitor_ram_usage_chart').getContext('2d');

    let ram_usage_color = css.getPropertyValue('--blue');
    let ram_usage_gradient = server_monitor_ram_usage_chart.createLinearGradient(0, 0, 0, 250);
    ram_usage_gradient.addColorStop(0, set_hex_opacity(ram_usage_color, 0.25));
    ram_usage_gradient.addColorStop(1, set_hex_opacity(ram_usage_color, 0.025));

    /* Display chart */
    new Chart(server_monitor_ram_usage_chart, {
        type: 'line',
        data: {
            labels: <?= $data->server_monitor_logs_chart['hour_minute_second_label'] ?>,
            datasets: [
                {
                    label: <?= json_encode(l('server_monitor.ram_usage')) ?>,
                    data: <?= $data->server_monitor_logs_chart['ram_usage'] ?? '[]' ?>,
                    backgroundColor: ram_usage_gradient,
                    borderColor: ram_usage_color,
                    fill: true
                }
            ]
        },
        options: {
            ...chart_options,
            ...{
                plugins: {
                    ...chart_options.plugins,
                    tooltip: {
                        ...chart_options.plugins.tooltip,
                        callbacks: {
                            label: context => {
                                return `${context.dataset.label}: ${nr(context.raw, 2)}%`;
                            },
                            title: (context) => {
                                return tooltip_titles[context[0].dataIndex];
                            }
                        }
                    },
                }
            }
        }
    });

    /* DISK usage chart */
    let server_monitor_disk_usage_chart = document.getElementById('server_monitor_disk_usage_chart').getContext('2d');

    let disk_usage_color = css.getPropertyValue('--purple');
    let disk_usage_gradient = server_monitor_disk_usage_chart.createLinearGradient(0, 0, 0, 250);
    disk_usage_gradient.addColorStop(0, set_hex_opacity(disk_usage_color, 0.25));
    disk_usage_gradient.addColorStop(1, set_hex_opacity(disk_usage_color, 0.025));

    /* Display chart */
    new Chart(server_monitor_disk_usage_chart, {
        type: 'line',
        data: {
            labels: <?= $data->server_monitor_logs_chart['hour_minute_second_label'] ?>,
            datasets: [
                {
                    label: <?= json_encode(l('server_monitor.disk_usage')) ?>,
                    data: <?= $data->server_monitor_logs_chart['disk_usage'] ?? '[]' ?>,
                    backgroundColor: disk_usage_gradient,
                    borderColor: disk_usage_color,
                    fill: true
                }
            ]
        },
        options: {
            ...chart_options,
            ...{
                plugins: {
                    ...chart_options.plugins,
                    tooltip: {
                        ...chart_options.plugins.tooltip,
                        callbacks: {
                            label: context => {
                                return `${context.dataset.label}: ${nr(context.raw, 2)}%`;
                            },
                            title: (context) => {
                                return tooltip_titles[context[0].dataIndex];
                            }
                        }
                    },
                }
            }
        }
    });

    /* CPU load chart */
    <?php if($data->server_monitor->cpu_load_1): ?>
    let server_monitor_cpu_load_chart = document.getElementById('server_monitor_cpu_load_chart').getContext('2d');

    let cpu_load_1_color = css.getPropertyValue('--pink');
    let cpu_load_5_color = css.getPropertyValue('--warning');
    let cpu_load_15_color = css.getPropertyValue('--orange');
    let cpu_load_gradient = server_monitor_cpu_load_chart.createLinearGradient(0, 0, 0, 250);
    cpu_load_gradient.addColorStop(0, set_hex_opacity(cpu_load_1_color, 0.25));
    cpu_load_gradient.addColorStop(1, set_hex_opacity(cpu_load_1_color, 0.025));

    /* Display chart */
    new Chart(server_monitor_cpu_load_chart, {
        type: 'line',
        data: {
            labels: <?= $data->server_monitor_logs_chart['hour_minute_second_label'] ?>,
            datasets: [
                {
                    label: <?= json_encode(l('server_monitor.cpu_load_1')) ?>,
                    data: <?= $data->server_monitor_logs_chart['cpu_load_1'] ?? '[]' ?>,
                    backgroundColor: cpu_load_gradient,
                    borderColor: cpu_load_1_color,
                    fill: true
                },
                {
                    label: <?= json_encode(l('server_monitor.cpu_load_5')) ?>,
                    data: <?= $data->server_monitor_logs_chart['cpu_load_5'] ?? '[]' ?>,
                    backgroundColor: cpu_load_gradient,
                    borderColor: cpu_load_5_color,
                    fill: true
                },
                {
                    label: <?= json_encode(l('server_monitor.cpu_load_15')) ?>,
                    data: <?= $data->server_monitor_logs_chart['cpu_load_15'] ?? '[]' ?>,
                    backgroundColor: cpu_load_gradient,
                    borderColor: cpu_load_15_color,
                    fill: true
                },
            ]
        },
        options: {
            ...chart_options,
            ...{
                plugins: {
                    ...chart_options.plugins,
                    tooltip: {
                        ...chart_options.plugins.tooltip,
                        callbacks: {
                            label: context => {
                                return `${context.dataset.label}: ${nr(context.raw, 2)}`;
                            },
                            title: (context) => {
                                return tooltip_titles[context[0].dataIndex];
                            }
                        }
                    },
                }
            }
        }
    });
    <?php endif ?>

    /* Network chart */
    function get_formatted_bytes(bytes, precision = 2) {
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];

        bytes = Math.max(bytes, 0);
        let pow = bytes ? Math.floor(Math.log(bytes) / Math.log(1000)) : 0;
        pow = Math.min(pow, units.length - 1);
        bytes /= Math.pow(1000, pow);

        return bytes.toFixed(precision) + ' ' + units[pow];
    }

    let server_monitor_network_chart = document.getElementById('server_monitor_network_chart').getContext('2d');

    let network_download = css.getPropertyValue('--success');
    let network_upload = css.getPropertyValue('--warning');
    let network_gradient = server_monitor_network_chart.createLinearGradient(0, 0, 0, 250);
    network_gradient.addColorStop(0, set_hex_opacity(network_download, 0.25));
    network_gradient.addColorStop(1, set_hex_opacity(network_download, 0.025));

    chart_options.plugins.tooltip.callbacks.label = (context) => {
        return `${context.dataset.label}: ${get_formatted_bytes(context.raw)}/s`;
    }

    /* Display chart */
    new Chart(server_monitor_network_chart, {
        type: 'line',
        data: {
            labels: <?= $data->server_monitor_logs_chart['hour_minute_second_label'] ?>,
            datasets: [
                {
                    label: <?= json_encode(l('server_monitor.network_download')) ?>,
                    data: <?= $data->server_monitor_logs_chart['network_download'] ?? '[]' ?>,
                    backgroundColor: network_gradient,
                    borderColor: network_download,
                    fill: true
                },
                {
                    label: <?= json_encode(l('server_monitor.network_upload')) ?>,
                    data: <?= $data->server_monitor_logs_chart['network_upload'] ?? '[]' ?>,
                    backgroundColor: network_gradient,
                    borderColor: network_upload,
                    fill: true
                }
            ]
        },
        options: chart_options
    });
    <?php endif ?>
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/server-monitor/server_monitor_install_modal.php'), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/server-monitor/server_monitor_uninstall_modal.php'), 'modals'); ?>
