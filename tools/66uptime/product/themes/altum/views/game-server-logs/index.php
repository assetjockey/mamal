<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('game-servers') ?>"><?= l('monitors.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?= url('game-server/' . $data->game_server->game_server_id) ?>"><?= l('monitor.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('monitor_logs.breadcrumb') ?></li>
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
                    <h1 class="h3 text-truncate mb-0 mr-2" style="color: white;"><?= sprintf(l('game_server_logs.header'), $data->game_server->name) ?></h1>

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
                    <img src="<?= ASSETS_FULL_URL . 'images/processing.svg' ?>" class="col-10 col-md-7 col-lg-5 mb-3" alt="<?= l('monitor.no_data') ?>" />
                    <h2 class="h4 text-muted"><?= l('game_server.no_data') ?></h2>
                    <p class="text-muted"><?= sprintf(l('game_server.no_data_help'), $data->game_server->name) ?></p>
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if($data->game_server->total_checks): ?>

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
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle-simple <?= count($data->game_server_logs) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                            <i class="fas fa-fw fa-sm fa-download"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right d-print-none">
                            <a href="<?= url('game-server-logs/' . $data->game_server->game_server_id . '?' . \Altum\Router::$original_request_query . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                                <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                            </a>
                            <a href="<?= url('game-server-logs/' . $data->game_server->game_server_id . '?' . \Altum\Router::$original_request_query . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
                                <i class="fas fa-fw fa-sm fa-file-code mr-2"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                            </a>
                            <a href="#" class="dropdown-item <?= $this->user->plan_settings->export->pdf ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->pdf ? 'onclick="event.preventDefault(); window.print();"' : get_plan_feature_disabled_info() ?>>
                                <i class="fas fa-fw fa-sm fa-file-pdf mr-2"></i> <?= sprintf(l('global.export_to'), 'PDF') ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="ml-2">
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm <?= $data->filters->has_applied_filters ? 'btn-primary' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= !empty($data->game_server_logs) || $data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip data-html="true" title="<?= l('global.filters.tooltip') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-filter"></i></button>

                        <div class="dropdown-menu dropdown-menu-right filters-dropdown">
                            <div class="dropdown-header d-flex justify-content-between">
                                <span class="h6 m-0"><?= l('global.filters.header') ?></span>

                                <?php if($data->filters->has_applied_filters): ?>
                                    <a href="<?= url(\Altum\Router::$original_request) ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
                                <?php endif ?>
                            </div>

                            <div class="dropdown-divider"></div>

                            <form action="" method="get" role="form">
                                <div class="form-group px-4">
                                    <label for="filters_is_ok" class="small"><?= l('global.status') ?></label>
                                    <select name="is_ok" id="filters_is_ok" class="custom-select custom-select-sm">
                                        <option value=""><?= l('global.all') ?></option>
                                        <option value="1" <?= isset($data->filters->filters['is_ok']) && $data->filters->filters['is_ok'] == '1' ? 'selected="selected"' : null ?>><?= l('game_servers.is_ok') ?></option>
                                        <option value="0" <?= isset($data->filters->filters['is_ok']) && $data->filters->filters['is_ok'] == '0' ? 'selected="selected"' : null ?>><?= l('game_servers.is_not_ok') ?></option>
                                    </select>
                                </div>

                                <div class="form-group px-4">
                                    <label for="filters_datetime_field" class="small"><?= l('global.filters.datetime_field') ?></label>
                                    <select name="datetime_field" id="filters_datetime_field" class="custom-select custom-select-sm" data-toggle-datetime-filters="#filters_datetime">
                                        <option value=""><?= l('global.none') ?></option>
                                        <option value="datetime" <?= $data->filters->datetime_field == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                    </select>
                                </div>

                                <div id="filters_datetime">
                                    <div class="form-group px-4">
                                        <label for="filters_datetime_start" class="small"><?= l('global.filters.datetime_start') ?></label>
                                        <input type="datetime-local" name="datetime_start" id="filters_datetime_start" class="form-control form-control-sm" value="<?= $data->filters->datetime_start ?? null ?>" />
                                    </div>

                                    <div class="form-group px-4">
                                        <label for="filters_datetime_end" class="small"><?= l('global.filters.datetime_end') ?></label>
                                        <input type="datetime-local" name="datetime_end" id="filters_datetime_end" class="form-control form-control-sm" value="<?= $data->filters->datetime_end ?? null ?>" />
                                    </div>
                                </div>

                                <div class="form-group px-4">
                                    <label for="filters_order_by" class="small"><?= l('global.filters.order_by') ?></label>
                                    <select name="order_by" id="filters_order_by" class="custom-select custom-select-sm">
                                        <option value="monitor_log_id" <?= $data->filters->order_by == 'monitor_log_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                        <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                        <option value="response_time" <?= $data->filters->order_by == 'response_time' ? 'selected="selected"' : null ?>><?= l('game_server.response_time') ?></option>
                                        <option value="online_players" <?= $data->filters->order_by == 'online_players' ? 'selected="selected"' : null ?>><?= l('game_servers.online_players') ?></option>
                                    </select>
                                </div>

                                <div class="form-group px-4">
                                    <label for="filters_order_type" class="small"><?= l('global.filters.order_type') ?></label>
                                    <select name="order_type" id="filters_order_type" class="custom-select custom-select-sm">
                                        <option value="ASC" <?= $data->filters->order_type == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                                        <option value="DESC" <?= $data->filters->order_type == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                                    </select>
                                </div>

                                <div class="form-group px-4">
                                    <label for="filters_results_per_page" class="small"><?= l('global.filters.results_per_page') ?></label>
                                    <select name="results_per_page" id="filters_results_per_page" class="custom-select custom-select-sm">
                                        <?php foreach($data->filters->allowed_results_per_page as $key): ?>
                                            <option value="<?= $key ?>" <?= $data->filters->results_per_page == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                                <div class="form-group px-4 mt-4">
                                    <button type="submit" name="submit" class="btn btn-sm btn-primary btn-block"><?= l('global.submit') ?></button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th><?= l('global.status') ?></th>
                        <th><?= l('game_servers.players') ?></th>
                        <th><?= l('monitor.checks.response_time') ?></th>
                        <th><?= l('monitor.checks.datetime') ?></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(!count($data->game_server_logs)): ?>
                        <tr>
                            <td colspan="4" class="text-muted"><?= l('monitor.checks.no_data') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($data->game_server_logs as $game_server_log): ?>

                            <tr>
                                <td class="text-nowrap">
                                    <?php if($game_server_log->is_ok): ?>
                                        <i class="fas fa-fw fa-sm fa-check-circle text-success"></i>
                                    <?php else: ?>
                                        <i class="fas fa-fw fa-sm fa-times-circle text-danger"></i>
                                    <?php endif ?>

                                    <?php if(!$game_server_log->is_ok): ?>
                                        <?php
                                        $error = l('global.unknown');

                                        if($game_server_log->error->type == 'exception') {
                                            $error = $game_server_log->error->message;
                                        }
                                        ?>

                                        <span class="ml-3" data-toggle="tooltip" title="<?= $error ?>">
                                            <i class="fas fa-fw fa-sm fa-envelope-open-text text-muted"></i>
                                        </span>
                                    <?php endif ?>
                                </td>

                                <td class="text-nowrap">
                                    <?= nr($game_server_log->online_players) . '/' . nr($game_server_log->maximum_online_players) ?>

                                    <?php if($data->game_server->average_online_players && $game_server_log->online_players && $data->game_server->average_online_players != $game_server_log->online_players): ?>
                                        <?php if($game_server_log->online_players > $data->game_server->average_online_players): ?>
                                            <span class="badge badge-pill badge-success ml-1" data-toggle="tooltip" title="<?= sprintf(l('game_server.higher_than_average'), nr(abs($data->game_server->average_online_players - $game_server_log->online_players)), nr($data->game_server->average_online_players)) ?>">
                                                <i class="fas fa-fw fa-arrow-up fa-sm"></i>
                                                <?= nr(get_percentage_change($data->game_server->average_online_players, $game_server_log->online_players), 2) . '%'; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-pill badge-danger ml-1" data-toggle="tooltip" title="<?= sprintf(l('game_server.lower_than_average'), nr(abs($data->game_server->average_online_players - $game_server_log->online_players)), nr($data->game_server->average_online_players)) ?>">
                                                <i class="fas fa-fw fa-arrow-down fa-sm"></i>
                                                <?= nr(get_percentage_change($data->game_server->average_online_players, $game_server_log->online_players), 2) . '%'; ?>
                                            </span>
                                        <?php endif ?>
                                    <?php endif ?>
                                </td>

                                <td class="text-nowrap">
                                    <?= display_response_time($game_server_log->response_time) ?>

                                    <?php if($data->game_server->average_response_time && $game_server_log->response_time && $data->game_server->average_response_time != $game_server_log->response_time): ?>
                                        <?php if($game_server_log->response_time > $data->game_server->average_response_time): ?>
                                            <span class="badge badge-pill badge-danger ml-1" data-toggle="tooltip" title="<?= sprintf(l('monitor.checks.higher_than_average'), display_response_time(abs($data->game_server->average_response_time - $game_server_log->response_time)), display_response_time($data->game_server->average_response_time)) ?>">
                                                <i class="fas fa-fw fa-arrow-up fa-sm"></i>
                                                <?= nr(get_percentage_change($data->game_server->average_response_time, $game_server_log->response_time), 2) . '%'; ?>
                                            </span>
                                        <?php else: ?>

                                            <span class="badge badge-pill badge-success ml-1" data-toggle="tooltip" title="<?= sprintf(l('monitor.checks.lower_than_average'), display_response_time(abs($data->game_server->average_response_time - $game_server_log->response_time)), display_response_time($data->game_server->average_response_time)) ?>">
                                                <i class="fas fa-fw fa-arrow-down fa-sm"></i>
                                                <?= nr(get_percentage_change($data->game_server->average_response_time, $game_server_log->response_time), 2) . '%'; ?>
                                            </span>
                                        <?php endif ?>
                                    <?php endif ?>
                                </td>

                                <td class="text-nowrap">
                                    <span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($game_server_log->datetime, 1) ?>">
                                        <?= \Altum\Date::get_timeago($game_server_log->datetime) ?>
                                    </span>
                                </td>
                            </tr>

                        <?php endforeach ?>
                    <?php endif ?>

                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3"><?= $data->pagination ?></div>

    <?php endif ?>

</div>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/libraries/daterangepicker.min.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

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

</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
