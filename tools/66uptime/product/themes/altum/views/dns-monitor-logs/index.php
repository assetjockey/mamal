<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('dns_monitors') ?>"><?= l('dns_monitors.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?= url('dns-monitor/' . $data->dns_monitor->dns_monitor_id) ?>"><?= l('dns_monitor.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('dns_monitor_logs.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="card bg-blue-900 border-0 rounded-2x">
        <div class="card-body">
            <div class="row">
                <div class="col-auto d-flex align-items-center">
                    <?php if($data->dns_monitor->is_enabled): ?>
                        <?php if(!$data->dns_monitor->total_checks): ?>
                            <div data-toggle="tooltip" title="<?= l('dns_monitor.pending_check') ?>">
                                <i class="fas fa-fw fa-clock fa-3x text-gray-400"></i>
                            </div>
                        <?php else: ?>
                            <div class="pulse-animation pulse-animation-success">
                                <span class="pulse-circle"></span>
                                <i class="fas fa-fw fa-check-circle fa-3x text-primary-400"></i>
                            </div>
                        <?php endif ?>
                    <?php else: ?>
                        <div data-toggle="tooltip" title="<?= l('dns_monitor.is_enabled_paused') ?>">
                            <i class="fas fa-fw fa-pause-circle fa-3x text-warning"></i>
                        </div>
                    <?php endif ?>
                </div>

                <div class="col text-truncate">
                    <h1 class="h3 text-truncate text-white mb-0 mr-2"><?= sprintf(l('dns_monitor.header'), $data->dns_monitor->name) ?></h1>

                    <div class="text-gray-400">
                        <span><?= $data->dns_monitor->target ?></span>
                    </div>
                </div>

                <div class="col-auto">
                    <?= include_view(THEME_PATH . 'views/dns-monitor/dns_monitor_dropdown_button.php', ['id' => $data->dns_monitor->dns_monitor_id, 'resource_name' => $data->dns_monitor->name, 'button_text_class' => 'text-white']) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if(!$data->dns_monitor->total_checks): ?>
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex flex-column align-items-center justify-content-center py-4">
                    <img src="<?= ASSETS_FULL_URL . 'images/processing.svg' ?>" class="col-10 col-md-7 col-lg-5 mb-3" alt="<?= l('dns_monitor.no_data') ?>" />
                    <h2 class="h4 text-muted"><?= l('dns_monitor.no_data') ?></h2>
                    <p class="text-muted"><?= sprintf(l('dns_monitor.no_data_help'), $data->dns_monitor->name) ?></p>
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if($data->dns_monitor->total_checks): ?>

        <div class="d-flex justify-content-end mt-4">
            <div class="d-flex">
                <div
                        id="daterangepicker"
                        role="button"
                        class="btn btn-sm btn-light"
                        data-min-date="<?= \Altum\Date::get($data->dns_monitor->datetime, 4) ?>"
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
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle-simple <?= count($data->dns_monitor_logs) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                            <i class="fas fa-fw fa-sm fa-download"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right d-print-none">
                            <a href="<?= url('dns-monitor-logs/' . $data->dns_monitor->dns_monitor_id . '?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date'] . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                                <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                            </a>
                            <a href="<?= url('dns-monitor-logs/' . $data->dns_monitor->dns_monitor_id . '?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date'] . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
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
                        <button type="button" class="btn btn-sm <?= $data->filters->has_applied_filters ? 'btn-primary' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= !empty($data->dns_monitor_logs) || $data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip data-html="true" title="<?= l('global.filters.tooltip') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-filter"></i></button>

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
                                    <label for="filters_search" class="small"><?= l('global.filters.search') ?></label>
                                    <input type="search" name="search" id="filters_search" class="form-control form-control-sm" value="<?= $data->filters->search ?>" />
                                </div>

                                <div class="form-group px-4">
                                    <label for="filters_search_by" class="small"><?= l('global.filters.search_by') ?></label>
                                    <select name="search_by" id="filters_search_by" class="custom-select custom-select-sm">
                                        <option value="response_status_code" <?= $data->filters->search_by == 'response_status_code' ? 'selected="selected"' : null ?>><?= l('dns_monitor.checks.response_status_code') ?></option>
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
                                        <option value="dns_monitor_log_id" <?= $data->filters->order_by == 'dns_monitor_log_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                        <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
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
                        <th colspan="5">
                            <?= l('dns_monitor.logs.last_checks') ?>
                        </th>
                    </tr>
                    <tr>
                        <th><?= l('global.type') ?></th>
                        <th></th>
                        <th><?= l('dns_monitor.old') ?></th>
                        <th><?= l('dns_monitor.new') ?></th>
                        <th><?= l('dns_monitor.logs.datetime') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(!count($data->dns_monitor_logs)): ?>
                        <tr>
                            <td colspan="5" class="text-muted"><?= l('dns_monitor.logs.no_data') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($data->dns_monitor_logs as $dns_monitor_log): ?>
                            <?php foreach($dns_monitor_log->dns_changes as $dns_change): ?>
                                <?php
                                unset($dns_change->old->host);
                                unset($dns_change->new->host);
                                ?>
                                <tr>
                                    <td class="text-nowrap">
                                        <?php if($dns_change->type == 'changed'): ?>
                                            <i class="fas fa-fw fa-sm fa-exchange-alt text-info mr-1"></i>
                                        <?php elseif($dns_change->type == 'added'): ?>
                                            <i class="fas fa-fw fa-sm fa-plus-circle text-success mr-1"></i>
                                        <?php else: ?>
                                            <i class="fas fa-fw fa-sm fa-minus-circle text-danger mr-1"></i>
                                        <?php endif ?>

                                        <?= l('dns_monitor.' . $dns_change->type) ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <span class="badge badge-success"><?= strtoupper($dns_change->dns_type) ?> </span>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php if($dns_change->type == 'changed'): ?>
                                            <?= implode(' ', array_values((array) $dns_change->old)) ?>
                                        <?php elseif($dns_change->type == 'added'): ?>
                                            -
                                        <?php else: ?>
                                            <?= implode(' ', array_values((array) $dns_change->old)) ?>
                                        <?php endif ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php if($dns_change->type == 'changed'): ?>
                                            <?= implode(' ', array_values((array) $dns_change->new)) ?>
                                        <?php elseif($dns_change->type == 'added'): ?>
                                            <?= implode(' ', array_values((array) $dns_change->new)) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif ?>
                                    </td>

                                    <td class="text-nowrap">
                                    <span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($dns_monitor_log->datetime, 1) ?>">
                                        <?= \Altum\Date::get_timeago($dns_monitor_log->datetime) ?>
                                    </span>
                                    </td>
                                </tr>
                            <?php endforeach ?>
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

</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
