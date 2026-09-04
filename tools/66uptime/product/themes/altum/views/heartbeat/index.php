<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('heartbeats') ?>"><?= l('heartbeats.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('heartbeat.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="card bg-blue-900 border-0 rounded-2x">
        <div class="card-body">
            <div class="row">
                <div class="col-auto d-flex align-items-center">
                    <?php if($data->heartbeat->is_enabled): ?>
                        <?php if($data->heartbeat->is_ok): ?>
                            <div data-toggle="tooltip" title="<?= l('heartbeat.is_ok') ?>" class="pulse-animation pulse-animation-success">
                                <span class="pulse-circle"></span>
                                <i class="fas fa-fw fa-check-circle fa-3x text-primary-400"></i>
                            </div>
                        <?php else: ?>
                            <a href="<?= url('incident/' . $data->heartbeat->incident_id) ?>" data-toggle="tooltip" title="<?= l('heartbeat.is_not_ok') ?>" class="pulse-animation pulse-animation-danger">
                                <span class="pulse-circle"></span>
                                <i class="fas fa-fw fa-times-circle fa-3x text-danger"></i>
                            </a>
                        <?php endif ?>
                    <?php else: ?>
                        <div data-toggle="tooltip" title="<?= l('heartbeat.is_enabled_paused') ?>">
                            <i class="fas fa-fw fa-pause-circle fa-3x text-warning"></i>
                        </div>
                    <?php endif ?>
                </div>

                <div class="col text-truncate">
                    <h1 class="h3 text-truncate text-white mb-0 mr-2"><?= sprintf(l('heartbeat.header'), $data->heartbeat->name) ?></h1>

                    <div class="text-truncate text-gray-400">
                        <span><?= l('heartbeat.code') ?></span>
                    </div>
                </div>

                <div class="col-auto">
                    <?= include_view(THEME_PATH . 'views/heartbeat/heartbeat_dropdown_button.php', ['id' => $data->heartbeat->heartbeat_id, 'resource_name' => $data->heartbeat->name, 'button_text_class' => 'text-white']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <code class="w-100 p-2 d-flex justify-content-center align-items-center">
            <?= SITE_URL . 'webhook-heartbeat/' . $data->heartbeat->code ?>

            <button
                    id="url_copy"
                    type="button"
                    class="btn btn-sm btn-link"
                    data-toggle="tooltip"
                    title="<?= l('global.clipboard_copy') ?>"
                    aria-label="<?= l('global.clipboard_copy') ?>"
                    data-copy="<?= l('global.clipboard_copy') ?>"
                    data-copied="<?= l('global.clipboard_copied') ?>"
                    data-clipboard-text="<?= SITE_URL . 'webhook-heartbeat/' . $data->heartbeat->code ?>"
            >
                <i class="fas fa-fw fa-sm fa-copy"></i>
            </button>

            <a href="<?= SITE_URL . 'webhook-heartbeat/' . $data->heartbeat->code ?>" target="_blank" class="btn btn-sm btn-link" data-toggle="tooltip" title="<?= l('heartbeat.code_execute') ?>">
                <i class="fas fa-fw fa-sm fa-external-link-alt"></i>
            </a>
        </code>
    </div>

    <?php if(!$data->heartbeat->total_runs): ?>
        <div class="card  mt-4">
            <div class="card-body">
                <div class="d-flex flex-column align-items-center justify-content-center py-4">
                    <img src="<?= ASSETS_FULL_URL . 'images/processing.svg' ?>" class="col-10 col-md-7 col-lg-5 mb-3" alt="<?= l('heartbeat.no_data') ?>" />
                    <h2 class="h4 text-muted"><?= l('heartbeat.no_data') ?></h2>
                    <p class="text-muted"><?= sprintf(l('heartbeat.no_data_help'), $data->heartbeat->name) ?></p>
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if($data->heartbeat->total_runs): ?>

        <div class="row justify-content-between mt-3">
            <div class="col-12 col-xl p-3">
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
                            <span class="text-muted font-size-small"><?= l('heartbeat.uptime') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0"><?= $data->total_heartbeat_logs ? nr($data->heartbeat_logs_data['uptime'], settings()->monitors_heartbeats->decimals) . '%' : '?' ?></div>
                                <div class="ml-2">
                                    <span data-toggle="tooltip" title="<?= sprintf(l('heartbeat.total_runs_tooltip'), nr($data->total_heartbeat_logs)) ?>">
                                        <i class="fas fa-sm fa-fw fa-info-circle text-muted"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl p-3">
                <div class="card h-100 position-relative">
                    <div class="card-body d-flex p-3">

                        <div>
                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-times-circle fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted font-size-small"><?= l('heartbeat.total_incidents') ?></span>
                            <div class="d-flex align-items-center">
                                <a href="<?= url('incidents?heartbeat=' . $data->heartbeat->heartbeat_id) ?>" class="card-title text-reset stretched-link text-decoration-none h6 m-0" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><?= $data->total_heartbeat_logs ? nr(count($data->heartbeat_incidents)) : '?' ?></a>
                                <div class="ml-2">
                                    <span data-toggle="tooltip" title="<?= sprintf(l('heartbeat.downtime_tooltip'), nr($data->heartbeat_logs_data['downtime'], settings()->monitors_heartbeats->decimals) . '%') ?>">
                                        <i class="fas fa-sm fa-fw fa-info-circle text-muted"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if(($data->date->start_date != $data->date->end_date && $data->date->end_date == \Altum\Date::get('', 4)) || ($data->date->start_date == $data->date->end_date && $data->date->start_date == \Altum\Date::get('', 4))): ?>
            <div class="row justify-content-between">
                <?php if($data->heartbeat->is_enabled): ?>
                    <div class="col-12 col-xl p-3">
                        <?php if($data->heartbeat->is_ok): ?>
                            <div class="card h-100">
                                <div class="card-body d-flex p-3">
                                    <div>
                                        <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                            <div class="p-3 d-flex align-items-center justify-content-between">
                                                <i class="fas fa-fw fa-check fa-lg"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <span class="text-muted font-size-small"><?= l('heartbeat.currently_up_for') ?></span>
                                        <div class="d-flex align-items-center">
                                            <div class="card-title h6 m-0"><?= \Altum\Date::get_elapsed_time($data->heartbeat->main_run_datetime, null, 2) ?></div>
                                            <div class="ml-2">
                                                <span data-toggle="tooltip" title="<?= sprintf(l('heartbeat.last_missed_datetime_tooltip'), \Altum\Date::get($data->heartbeat->last_missed_datetime, 1)) ?>">
                                                    <i class="fas fa-sm fa-fw fa-info-circle text-muted"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card h-100">
                                <div class="card-body d-flex p-3">
                                    <div>
                                        <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                            <div class="p-3 d-flex align-items-center justify-content-between">
                                                <i class="fas fa-fw fa-times fa-lg"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <span class="text-muted font-size-small"><?= l('heartbeat.currently_down_for') ?></span>
                                        <div class="d-flex align-items-center">
                                            <div class="card-title h6 m-0"><?= \Altum\Date::get_elapsed_time($data->heartbeat->main_missed_datetime, null, 2) ?></div>
                                            <div class="ml-2">
                                                <span data-toggle="tooltip" title="<?= sprintf(l('heartbeat.last_run_datetime_tooltip'), \Altum\Date::get($data->heartbeat->last_run_datetime, 1)) ?>">
                                                    <i class="fas fa-sm fa-fw fa-info-circle text-muted"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>
                    </div>
                <?php endif ?>

                <div class="col-12 col-xl p-3">
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
                                <span class="text-muted font-size-small"><?= l('heartbeat.last_run_datetime') ?></span>
                                <div class="d-flex align-items-center">
                                    <div class="card-title h6 m-0"><?= \Altum\Date::get_timeago($data->heartbeat->last_run_datetime) ?></div>
                                    <div class="ml-2">
                                <span data-toggle="tooltip" title="<?= sprintf(l('heartbeat.run_interval_seconds_tooltip'), $data->heartbeat->settings->run_interval, l('global.date.' . $data->heartbeat->settings->run_interval_type)) ?>">
                                    <i class="fas fa-sm fa-fw fa-info-circle text-muted"></i>
                                </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <div class="d-flex justify-content-end mt-4">
            <div class="d-flex">
                <div
                        id="daterangepicker"
                        role="button"
                        class="btn btn-sm btn-light"
                        data-min-date="<?= \Altum\Date::get($data->heartbeat->datetime, 4) ?>"
                        data-max-date="<?= \Altum\Date::get('', 4) ?>"
                >
                    <i class="fas fa-fw fa-calendar mr-lg-1"></i>
                    <span class="d-none d-lg-inline-block">
                        <?php if($data->date->start_date == $data->date->end_date): ?>
                            <?= \Altum\Date::get($data->date->start_date, 2, \Altum\Date::$default_timezone) ?>
                        <?php else: ?>
                            <?= \Altum\Date::get($data->date->start_date, 2, \Altum\Date::$default_timezone) . ' - ' . \Altum\Date::get($data->date->end_date, 2, \Altum\Date::$default_timezone) ?>
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
                            <a href="<?= url('heartbeat/' . $data->heartbeat->heartbeat_id . '?' . \Altum\Router::$original_request_query . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                                <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                            </a>
                            <a href="<?= url('heartbeat/' . $data->heartbeat->heartbeat_id . '?' . \Altum\Router::$original_request_query . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
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

        <?php if($data->total_heartbeat_logs): ?>

            <div class="card mt-4">
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="heartbeat_logs_chart"></canvas>
                    </div>
                </div>
            </div>

        <?php endif ?>

        <?php if($data->total_heartbeat_logs): ?>
            <div class="mt-5">
                <div class="table-responsive table-custom-container">
                    <table class="table table-custom">
                        <thead>
                        <tr>
                            <th colspan="6"><?= l('incidents.header') ?></th>
                        </tr>
                        <tr>
                            <th><?= l('global.status') ?></th>
                            <th><?= l('incidents.start_datetime') ?></th>
                            <th><?= l('incidents.end_datetime') ?></th>
                            <th><?= l('incidents.length') ?></th>
                            <th><?= l('incidents.comment') ?></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!count($data->heartbeat_incidents)): ?>
                            <tr>
                                <td colspan="6" class="text-muted">
                                    <i class="fas fa-fw fa-sm fa-check-circle text-success mr-1"></i> <?= l('incidents.no_data_date_range') ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($data->heartbeat_incidents as $incident): ?>
                                <tr>
                                    <td class="text-truncate text-muted">
                                        <?php if($incident->end_datetime): ?>
                                            <span class="" data-toggle="tooltip" title="<?= l('incidents.solved') ?>">
                                                <i class="fas fa-fw fa-sm fa-check-circle text-success"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="" data-toggle="tooltip" title="<?= l('incidents.unsolved') ?>">
                                                <i class="fas fa-fw fa-sm fa-times-circle text-danger"></i>
                                            </span>
                                        <?php endif ?>

                                        <span class="ml-3" data-toggle="tooltip" title="<?= l('heartbeat.checks.error.missed') ?>">
                                            <i class="fas fa-fw fa-sm fa-envelope-open-text text-muted"></i>
                                        </span>

                                        <span class="ml-3" data-toggle="tooltip" title="<?= sprintf(l('incidents.x_failed_checks'), nr($incident->failed_checks)) ?>">
                                            <i class="fas fa-fw fa-sm fa-undo text-muted"></i>
                                        </span>
                                    </td>

                                    <td class="text-truncate text-muted">
                                        <div>
                                            <a href="<?= url('incident/' . $incident->incident_id) ?>"><?= $data->heartbeat->name ?></a>
                                        </div>

                                        <span class="small" data-toggle="tooltip" title="<?= \Altum\Date::get($incident->start_datetime, 1) ?>">
                                            <?= \Altum\Date::get_timeago($incident->start_datetime) ?>
                                        </span>
                                    </td>

                                    <td class="text-truncate">
                                        <?php if($incident->end_datetime): ?>
                                            <span class="badge badge-success" data-toggle="tooltip" title="<?= \Altum\Date::get($incident->end_datetime, 1) ?>">
                                                <i class="fas fa-fw fa-sm fa-check-circle mr-1"></i>
                                                <?= \Altum\Date::get_timeago($incident->end_datetime) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">
                                                <i class="fas fa-fw fa-sm fa-exclamation-circle mr-1"></i>
                                                <?= l('incidents.end_datetime_null') ?>
                                            </span>
                                        <?php endif ?>
                                    </td>

                                    <td class="text-truncate">
                                        <?= \Altum\Date::get_elapsed_time($incident->start_datetime, $incident->end_datetime, 2) ?>
                                    </td>

                                    <td class="text-truncate">
                                        <span id="incident_id_<?= $incident->incident_id ?>" title="<?= $incident->comment ?: l('global.none') ?>" data-toggle="tooltip">
                                            <i class="fas fa-fw fa-sm fa-comment"></i>
                                        </span>

                                        <button type="button" class="btn btn-sm btn-light ml-1" data-tooltip title="<?= l('global.update') ?>" data-toggle="modal" data-target="#incident_comment_modal" data-incident-id="<?= $incident->incident_id ?>" data-comment="<?= $incident->comment ?>">
                                            <i class="fas fa-fw fa-sm fa-pen"></i>
                                        </button>
                                    </td>

                                    <td class="text-truncate">
                                        <div class="d-flex justify-content-end">
                                            <?= include_view(THEME_PATH . 'views/incidents/incident_dropdown_button.php', ['id' => $incident->incident_id]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif ?>

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
        startDate: <?= json_encode($data->date->start_date) ?>,
        endDate: <?= json_encode($data->date->end_date) ?>,
        minDate: $('#daterangepicker').data('min-date'),
        maxDate: $('#daterangepicker').data('max-date'),
        ranges: {
            <?= json_encode(l('global.date.today')) ?>: [moment(), moment()],
            <?= json_encode(l('global.date.yesterday')) ?>: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            <?= json_encode(l('global.date.this_week')) ?>: [moment().startOf('week'), moment().endOf('week')],

            <?= json_encode(l('global.date.last_30_days')) ?>: [moment().subtract(29, 'days'), moment()],
                <?= json_encode(l('global.date.this_month')) ?>: [moment().startOf('month'), moment().endOf('month')],
            <?= json_encode(l('global.date.last_month')) ?>: [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
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

    <?php if($data->total_heartbeat_logs): ?>
    let css = window.getComputedStyle(document.body)

    /* Response Time chart */
    let heartbeat_logs_chart = document.getElementById('heartbeat_logs_chart').getContext('2d');

    /* Colors */
    let is_ok_color = css.getPropertyValue('--primary');
    let is_ok_gradient = heartbeat_logs_chart.createLinearGradient(0, 0, 0, 250);
    is_ok_gradient.addColorStop(0, set_hex_opacity(is_ok_color, 0.25));
    is_ok_gradient.addColorStop(1, set_hex_opacity(is_ok_color, 0.025));

    let is_not_ok_color = css.getPropertyValue('--danger');
    let is_not_ok_gradient = heartbeat_logs_chart.createLinearGradient(0, 0, 0, 250);
    is_not_ok_gradient.addColorStop(0, set_hex_opacity(is_not_ok_color, 0.25));
    is_not_ok_gradient.addColorStop(1, set_hex_opacity(is_not_ok_color, 0.025));

    /* Generate colors based on if heartbeat is ok */
    let is_ok_colors = [];
    <?= $data->heartbeat_logs_chart['is_ok'] ?? '[]' ?>.forEach(is_ok => {
        is_ok_colors.push(parseInt(is_ok) ? is_ok_color : is_not_ok_color);
    })

    /* Tooltip titles */
    let tooltip_titles = <?= $data->heartbeat_logs_chart['labels'] ?>;
    chart_options.plugins.tooltip.callbacks.title = (context) => {
        return tooltip_titles[context[0].dataIndex];
    }

    chart_options.plugins.tooltip.callbacks.label = (context) => {
        return `${context.dataset.label}: ${context.raw == 0.999 ? <?= json_encode(l('global.no')) ?> : <?= json_encode(l('global.yes')) ?>}`;
    }

    /* Display chart */
    new Chart(heartbeat_logs_chart, {
        type: 'bar',
        data: {
            labels: <?= $data->heartbeat_logs_chart['hour_minute_second_label'] ?>,
            datasets: [
                {
                    label: <?= json_encode(l('heartbeat.is_ok')) ?>,
                    data: <?= $data->heartbeat_logs_chart['is_ok_chart'] ?? '[]' ?>,
                    backgroundColor: is_ok_colors,
                    borderColor: is_ok_colors,
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

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/incidents/incident_comment_modal.php'), 'modals'); ?>
