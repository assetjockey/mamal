<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('monitors') ?>"><?= l('monitors.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('game_server.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="card bg-blue-900 border-0 rounded-2x position-relative" style="--background-image: url(<?= ASSETS_FULL_URL . 'images/games/' . $data->game_server->type . '-cover.webp' ?>)">
        <div class="game-server-card"></div>

        <div class="card-body">
            <div class="row">
                <div class="col-auto d-flex align-items-center">
                    <?php if($data->game_server->is_enabled): ?>
                        <?php if(!$data->game_server->total_checks): ?>
                            <div data-toggle="tooltip" title="<?= l('game_servers.pending_check') ?>">
                                <i class="fas fa-fw fa-clock fa-3x text-gray-400"></i>
                            </div>
                        <?php elseif($data->game_server->is_ok): ?>
                            <div data-toggle="tooltip" title="<?= l('game_servers.is_ok') ?>" class="pulse-animation pulse-animation-success">
                                <span class="pulse-circle"></span>
                                <i class="fas fa-fw fa-check-circle fa-3x text-primary-400"></i>
                            </div>
                        <?php else: ?>
                            <div data-toggle="tooltip" title="<?= l('game_servers.is_not_ok') ?>" class="pulse-animation pulse-animation-danger">
                                <span class="pulse-circle"></span>
                                <i class="fas fa-fw fa-times-circle fa-3x text-danger"></i>
                            </div>
                        <?php endif ?>
                    <?php else: ?>
                        <div data-toggle="tooltip" title="<?= l('game_servers.is_enabled_paused') ?>">
                            <i class="fas fa-fw fa-pause-circle fa-3x text-warning"></i>
                        </div>
                    <?php endif ?>
                </div>

                <div class="col text-truncate">
                    <h1 class="h3 text-truncate mb-0 mr-2" style="color: white;"><?= sprintf(l('game_server.header'), $data->game_server->name) ?></h1>

                    <div>
                        <img src="<?= ASSETS_FULL_URL . 'images/games/' . $data->game_server_types[$data->game_server->type]['icon'] ?>" class="img-fluid icon-favicon-small mr-1" data-toggle="tooltip" title="<?= $data->game_server_types[$data->game_server->type]['name'] ?>" />
                        <span style="color: white;"><?= $data->game_server->target ?><?= $data->game_server->port ? ':' . $data->game_server->port : null ?></span>
                    </div>
                </div>

                <div class="col-auto">
                    <?= include_view(THEME_PATH . 'views/game-server/game_server_dropdown_button.php', ['id' => $data->game_server->game_server_id, 'resource_name' => $data->game_server->name, 'button_text_class' => 'text-white']) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if(!$data->game_server->total_checks): ?>
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex flex-column align-items-center justify-content-center py-4">
                    <img src="<?= ASSETS_FULL_URL . 'images/processing.svg' ?>" class="col-10 col-md-7 col-lg-5 mb-3" alt="<?= l('game_server.no_data') ?>" />
                    <h2 class="h4 text-muted"><?= l('game_server.no_data') ?></h2>
                    <p class="text-muted"><?= sprintf(l('game_server.no_data_help'), $data->game_server->name) ?></p>
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if($data->game_server->total_checks): ?>

        <div class="row justify-content-between mt-3">
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
                            <span class="font-size-small text-muted"><?= l('game_servers.uptime') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0"><?= $data->total_game_server_logs ? nr($data->game_server_logs_data['uptime'], settings()->monitors_heartbeats->decimals) . '%' : '?' ?></div>
                                <div class="ml-2">
                                    <span data-toggle="tooltip" title="<?= sprintf(l('game_server.total_checks_tooltip'), nr($data->total_game_server_logs)) ?>">
                                        <i class="fas fa-fw fa-sm fa-info-circle text-muted"></i>
                                    </span>
                                </div>
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
                                    <i class="fas fa-fw fa-bolt fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="font-size-small text-muted"><?= l('game_servers.average_response_time') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0"><?= $data->total_game_server_logs ? display_response_time($data->game_server_logs_data['average_response_time']) : '?' ?></div>
                                <div class="ml-2">
                                    <span data-toggle="tooltip" title="<?= sprintf(l('game_server.total_ok_checks_tooltip'), nr($data->game_server_logs_data['total_ok_checks'])) ?>">
                                        <i class="fas fa-fw fa-sm fa-info-circle text-muted"></i>
                                    </span>
                                </div>
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
                                    <i class="fas fa-fw fa-users fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="font-size-small text-muted"><?= l('game_servers.players') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0"><?= nr($data->game_server->online_players) . '/' . nr($data->game_server->maximum_online_players) ?></div>

                                <div class="ml-2">
                                    <span data-toggle="tooltip" title="<?= sprintf(l('game_server.downtime_tooltip'), nr($data->game_server_logs_data['downtime'], settings()->monitors_heartbeats->decimals) . '%') ?>">
                                        <i class="fas fa-fw fa-sm fa-info-circle text-muted"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if(($data->date->start_date != $data->date->end_date && $data->date->end_date == \Altum\Date::get('', 4)) || ($data->date->start_date == $data->date->end_date && $data->date->start_date == \Altum\Date::get('', 4))): ?>
                <?php if($data->game_server->is_enabled): ?>
                    <div class="col-12 col-lg-6 p-3">
                        <?php if($data->game_server->is_ok): ?>
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
                                        <span class="font-size-small text-muted"><?= l('game_server.currently_up_for') ?></span>
                                        <div class="d-flex align-items-center">
                                            <div class="card-title h6 m-0"><?= \Altum\Date::get_elapsed_time($data->game_server->main_ok_datetime, null, 2) ?></div>
                                            <div class="ml-2">
                                                <span data-toggle="tooltip" title="<?= sprintf(l('game_server.last_not_ok_datetime_tooltip'), \Altum\Date::get($data->game_server->last_not_ok_datetime, 1)) ?>">
                                                    <i class="fas fa-fw fa-sm fa-info-circle text-muted"></i>
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
                                        <span class="font-size-small text-muted"><?= l('game_server.currently_down_for') ?></span>
                                        <div class="d-flex align-items-center">
                                            <div class="card-title h6 m-0"><?= \Altum\Date::get_elapsed_time($data->game_server->main_not_ok_datetime, null, 2) ?></div>
                                            <div class="ml-2">
                                                <span data-toggle="tooltip" title="<?= sprintf(l('game_server.last_ok_datetime_tooltip'), \Altum\Date::get($data->game_server->last_ok_datetime, 1)) ?>">
                                                    <i class="fas fa-fw fa-sm fa-info-circle text-muted"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>
                    </div>
                <?php endif ?>

                <div class="col-12 col-lg-6 p-3">
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
                                <span class="font-size-small text-muted"><?= l('game_server.last_check_datetime') ?></span>
                                <div class="d-flex align-items-center">
                                    <div class="card-title h6 m-0"><?= $data->game_server->last_check_datetime ? \Altum\Date::get_timeago($data->game_server->last_check_datetime) : l('global.na') ?></div>
                                    <div class="ml-2">
                                        <span data-toggle="tooltip" title="<?= sprintf(l('game_server.check_interval_seconds_tooltip'), $data->game_server->settings->check_interval_seconds) ?>">
                                            <i class="fas fa-fw fa-sm fa-info-circle text-muted"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <div class="d-flex">
                <div
                        id="daterangepicker"
                        role="button"
                        class="btn btn-sm btn-light"
                        data-min-date="<?= \Altum\Date::get($data->game_server->datetime, 4) ?>"
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
                            <a href="<?= url('monitor/' . $data->game_server->game_server_id . '?' . \Altum\Router::$original_request_query . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                                <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                            </a>
                            <a href="<?= url('monitor/' . $data->game_server->game_server_id . '?' . \Altum\Router::$original_request_query . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
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

        <?php if($data->total_game_server_logs): ?>

            <div class="card mt-4">
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="game_server_logs_chart"></canvas>
                    </div>
                </div>
            </div>

        <?php endif ?>

        <div class="mt-5">
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th colspan="4">
                            <?= l('game_server.last_checks') ?>
                            <span class="ml-3 small">
                                <a href="<?= url('game-server-logs/' . $data->game_server->game_server_id) ?>"><?= l('global.view_all') ?></a>
                            </span>
                        </th>
                    </tr>
                    <tr>
                        <th><?= l('global.status') ?></th>
                        <th><?= l('game_servers.players') ?></th>
                        <th><?= l('game_servers.response_time') ?></th>
                        <th><?= l('game_server.datetime') ?></th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if(!$data->total_game_server_logs): ?>
                        <tr>
                            <td colspan="4" class="text-muted"><?= l('game_server.last_checks_no_data') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php for($i = count($data->game_server_logs) - 1; $i >= count($data->game_server_logs) - 5; $i--): ?>

                            <?php
                            if(!isset($data->game_server_logs[$i])) {
                                continue;
                            }
                            ?>

                            <tr>
                                <td class="text-nowrap">
                                    <?php if($data->game_server_logs[$i]->is_ok): ?>
                                        <i class="fas fa-fw fa-sm fa-check-circle text-success"></i>
                                    <?php else: ?>
                                        <i class="fas fa-fw fa-sm fa-times-circle text-danger"></i>
                                    <?php endif ?>

                                    <?php if(!$data->game_server_logs[$i]->is_ok): ?>
                                        <?php
                                        $error = l('global.unknown');

                                        if($data->game_server_logs[$i]->error->type == 'exception') {
                                            $error = $data->game_server_logs[$i]->error->message;
                                        }
                                        ?>

                                        <span class="ml-3" data-toggle="tooltip" title="<?= $error ?>">
                                            <i class="fas fa-fw fa-sm fa-envelope-open-text text-muted"></i>
                                        </span>
                                    <?php endif ?>
                                </td>

                                <td class="text-nowrap">
                                    <?= nr($data->game_server_logs[$i]->online_players) . '/' . nr($data->game_server_logs[$i]->maximum_online_players) ?>

                                    <?php if($data->game_server->average_online_players && $data->game_server_logs[$i]->online_players && $data->game_server->average_online_players != $data->game_server_logs[$i]->online_players): ?>
                                        <?php if($data->game_server_logs[$i]->online_players > $data->game_server->average_online_players): ?>
                                            <span class="badge badge-pill badge-success ml-1" data-toggle="tooltip" title="<?= sprintf(l('game_server.higher_than_average'), nr(abs($data->game_server->average_online_players - $data->game_server_logs[$i]->online_players)), nr($data->game_server->average_online_players)) ?>">
                                                <i class="fas fa-fw fa-arrow-up fa-sm"></i>
                                                <?= nr(get_percentage_change($data->game_server->average_online_players, $data->game_server_logs[$i]->online_players), 2) . '%'; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-pill badge-danger ml-1" data-toggle="tooltip" title="<?= sprintf(l('game_server.lower_than_average'), nr(abs($data->game_server->average_online_players - $data->game_server_logs[$i]->online_players)), nr($data->game_server->average_online_players)) ?>">
                                                <i class="fas fa-fw fa-arrow-down fa-sm"></i>
                                                <?= nr(get_percentage_change($data->game_server->average_online_players, $data->game_server_logs[$i]->online_players), 2) . '%'; ?>
                                            </span>
                                        <?php endif ?>
                                    <?php endif ?>
                                </td>

                                <td class="text-nowrap">
                                    <?= display_response_time($data->game_server_logs[$i]->response_time) ?>

                                    <?php if($data->game_server->average_response_time && $data->game_server_logs[$i]->response_time && $data->game_server->average_response_time != $data->game_server_logs[$i]->response_time): ?>
                                        <?php if($data->game_server_logs[$i]->response_time > $data->game_server->average_response_time): ?>
                                            <span class="badge badge-pill badge-danger ml-1" data-toggle="tooltip" title="<?= sprintf(l('game_server.higher_than_average'), display_response_time(abs($data->game_server->average_response_time - $data->game_server_logs[$i]->response_time)), display_response_time($data->game_server->average_response_time)) ?>">
                                                <i class="fas fa-fw fa-arrow-up fa-sm"></i>
                                                <?= nr(get_percentage_change($data->game_server->average_response_time, $data->game_server_logs[$i]->response_time), 2) . '%'; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-pill badge-success ml-1" data-toggle="tooltip" title="<?= sprintf(l('game_server.lower_than_average'), display_response_time(abs($data->game_server->average_response_time - $data->game_server_logs[$i]->response_time)), display_response_time($data->game_server->average_response_time)) ?>">
                                                <i class="fas fa-fw fa-arrow-down fa-sm"></i>
                                                <?= nr(get_percentage_change($data->game_server->average_response_time, $data->game_server_logs[$i]->response_time), 2) . '%'; ?>
                                            </span>
                                        <?php endif ?>
                                    <?php endif ?>
                                </td>

                                <td class="text-nowrap">
                                    <span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($data->game_server_logs[$i]->datetime, 1) ?>">
                                        <?= \Altum\Date::get_timeago($data->game_server_logs[$i]->datetime) ?>
                                    </span>
                                </td>
                            </tr>

                        <?php endfor ?>
                    <?php endif ?>

                    </tbody>
                </table>
            </div>
        </div>

        <?php
        $table_content_type = match($data->game_server_types[$data->game_server->type]['protocol']) {
            'gold_source', 'source' => 'gold_source_and_source',
            default => $data->game_server_types[$data->game_server->type]['protocol'],
        }
        ?>
        <?= include_view(THEME_PATH . 'views/game-server/partials/' . $table_content_type . '_content.php', ['game_server' => $data->game_server]) ?>

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

    <?php if($data->total_game_server_logs): ?>
    let css = window.getComputedStyle(document.body)

    /* Response Time chart */
    let game_server_logs_chart = document.getElementById('game_server_logs_chart').getContext('2d');

    let online_players_color = css.getPropertyValue('--primary');
    let online_players_gradient = game_server_logs_chart.createLinearGradient(0, 0, 0, 250);
    online_players_gradient.addColorStop(0, set_hex_opacity(online_players_color, 0.1));
    online_players_gradient.addColorStop(1, set_hex_opacity(online_players_color, 0.025));

    let is_not_ok_color = css.getPropertyValue('--danger');
    let is_not_ok_gradient = game_server_logs_chart.createLinearGradient(0, 0, 0, 250);
    is_not_ok_gradient.addColorStop(0, set_hex_opacity(is_not_ok_color, 0.1));
    is_not_ok_gradient.addColorStop(1, set_hex_opacity(is_not_ok_color, 0.025));

    let is_ok_color = css.getPropertyValue('--gray-300');
    let is_ok_gradient = game_server_logs_chart.createLinearGradient(0, 0, 0, 250);
    is_not_ok_gradient.addColorStop(0, set_hex_opacity(is_ok_color, 0.1));
    is_not_ok_gradient.addColorStop(1, set_hex_opacity(is_ok_color, 0.025));

    /* Generate colors based on if monitor is ok */
    let online_players_colors = [];
    <?= $data->game_server_logs_chart['is_ok'] ?? '[]' ?>.forEach(is_ok => {
        online_players_colors.push(parseInt(is_ok) ? online_players_color : is_not_ok_color);
    })

    /* Data for chart */
    let data = {
        labels: <?= $data->game_server_logs_chart['hour_minute_second_label'] ?>,
        datasets: [
            {
                label: <?= json_encode(l('game_servers.online_players')) ?>,
                data: <?= $data->game_server_logs_chart['online_players'] ?? '[]' ?>,
                backgroundColor: online_players_gradient,
                borderColor: online_players_colors,
                fill: true
            },
            {
                label: <?= json_encode(l('game_servers.is_ok')) ?>,
                data: <?= $data->game_server_logs_chart['is_ok'] ?? '[]' ?>,
                backgroundColor: 'rgba(0,0,0,0)',
                borderColor: 'rgba(0,0,0,0)',
                fill: false,
                showLine: false,
                borderWidth: 0,
                pointBorderWidth: 0,
                pointBorderRadius: 0,
                hidden: true,
            },
            {
                label: <?= json_encode(l('game_servers.response_time')) ?>,
                data: <?= $data->game_server_logs_chart['response_time'] ?? '[]' ?>,
                backgroundColor: 'rgba(0,0,0,0)',
                borderColor: 'rgba(0,0,0,0)',
                fill: false,
                showLine: false,
                borderWidth: 0,
                pointBorderWidth: 0,
                pointBorderRadius: 0,
                hidden: true,
            },
            {
                label: <?= json_encode(l('game_servers.checks.error')) ?>,
                data: <?= $data->game_server_logs_chart['error'] ?? '[]' ?>,
                backgroundColor: 'rgba(0,0,0,0)',
                borderColor: 'rgba(0,0,0,0)',
                fill: false,
                showLine: false,
                borderWidth: 0,
                pointBorderWidth: 0,
                pointBorderRadius: 0,
                hidden: true,
            }
        ]
    };

    /* Tooltip titles */
    let tooltip_titles = <?= $data->game_server_logs_chart['labels'] ?>;
    chart_options.plugins.tooltip.callbacks.title = (context) => {
        return tooltip_titles[context[0].dataIndex];
    }

    chart_options.plugins.tooltip.callbacks.label = (context, chart) => {
        return data.datasets.map((dataset, index) => {
            switch(index) {
                case 0:
                    return `${dataset.label}: ${dataset.backgroundColor[context.index] == is_not_ok_color ? 0 : nr(dataset.data[context.dataIndex])}` + "\n";
                case 1:
                    return `${dataset.label}: ${dataset.data[context.dataIndex] == 0 ? <?= json_encode(l('global.no')) ?> : <?= json_encode(l('global.yes')) ?>}` + "\n";
                case 2:
                    return `${dataset.label}: ${nr(dataset.data[context.dataIndex])} <?= l('global.date.short_milliseconds') ?>` + "\n";
                case 3:
                    return `${dataset.label}: ${dataset.backgroundColor[context.index] == is_not_ok_color ? 0 : dataset.data[context.dataIndex]}` + "\n";
            }
        });
    }

    /* Display chart */
    new Chart(game_server_logs_chart, {
        type: 'line',
        data: data,
        options: chart_options
    });
    <?php endif ?>
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

