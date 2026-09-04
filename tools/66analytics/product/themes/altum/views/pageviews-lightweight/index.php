<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-end mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-eye mr-1"></i> <?= l('pageviews.header') ?></h1>
        </div>

        <div class="col-12 col-lg-auto d-flex flex-wrap gap-3 d-print-none">
            <div>
                <div
                        id="daterangepicker"
                        role="button"
                        class="btn btn-sm btn-light text-nowrap"
                        data-min-date="<?= \Altum\Date::get($this->website->datetime, 4) ?>"
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
            </div>

            <div>
                <div class="dropdown">
                    <button type="button" class="btn btn-sm btn-light dropdown-toggle-simple <?= count($data->pageviews) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('pageviews-lightweight?export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('pageviews-lightweight?export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-code mr-2"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                        </a>
                        <a href="#" class="dropdown-item <?= $this->user->plan_settings->export->pdf ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->pdf ? 'onclick="event.preventDefault(); window.print();"' : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-pdf mr-2"></i> <?= sprintf(l('global.export_to'), 'PDF') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div>
                <button type="button" class="btn btn-sm btn-light d-print-none" onclick="$('#filters').toggle();" data-toggle="tooltip" title="<?= l('analytics.filters.toggle') ?>">
                    <i class="fas fa-fw fa-filter"></i>
                </button>
            </div>

            <div>
                <button id="bulk_enable" type="button" class="btn btn-sm btn-light <?= !empty($data->pageviews) ? null : 'disabled' ?>" data-toggle="tooltip" title="<?= l('global.bulk_actions') ?>"><i class="fas fa-fw fa-sm fa-list"></i></button>

                <div id="bulk_group" class="btn-group btn-group-sm d-none" role="group">
                    <div class="btn-group dropdown" role="group">
                        <button id="bulk_actions" type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                            <?= l('global.bulk_actions') ?> <span id="bulk_counter" class="d-none"></span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="bulk_actions">
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#bulk_delete_modal"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
                        </div>
                    </div>

                    <button id="bulk_disable" type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" title="<?= l('global.close') ?>"><i class="fas fa-fw fa-times"></i></button>
                </div>
            </div>
        </div>
    </div>

    <?= (new \Altum\View('partials/analytics/filters_wrapper', (array) $this))->run(['available_filters' => 'lightweight_events', 'tracking_type' => $this->website->tracking_type]) ?>

    <?php if(!$data->total_rows): ?>

        <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
            'filters_get' => $data->filters->get ?? [],
            'name' => 'global',
            'has_secondary_text' => false,
        ]); ?>

    <?php else: ?>

        <form id="table" action="<?= SITE_URL . 'pageviews-lightweight/bulk' ?>" method="post" role="form">
            <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
            <input type="hidden" name="type" value="" data-bulk-type />
            <input type="hidden" name="original_request" value="<?= base64_encode(\Altum\Router::$original_request) ?>" />
            <input type="hidden" name="original_request_query" value="<?= base64_encode(\Altum\Router::$original_request_query) ?>" />

            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th data-bulk-table class="d-none">
                            <div class="custom-control custom-checkbox">
                                <input id="bulk_select_all" type="checkbox" class="custom-control-input" />
                                <label class="custom-control-label" for="bulk_select_all"></label>
                            </div>
                        </th>
                        <th><?= l('analytics.pageview') ?></th>
                        <th></th>
                        <th><?= l('global.details') ?></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data->pageviews as $row): ?>
                        <tr>
                            <td data-bulk-table class="d-none">
                                <div class="custom-control custom-checkbox">
                                    <input id="selected_pageview_id_<?= $row->event_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->event_id ?>" />
                                    <label class="custom-control-label" for="selected_pageview_id_<?= $row->event_id ?>"></label>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <span data-toggle="tooltip" title="<?= $row->path ?>"><?= string_truncate($row->path, 50) ?></span>
                            </td>

                            <td class="text-nowrap">
                                <span data-toggle="tooltip" title="<?= \Altum\Date::get($row->date, 1) ?>" class="badge badge-light">
                                   <i class="fas fa-fw fa-calendar fa-sm mr-1"></i> <?= \Altum\Date::get_timeago($row->date) ?>
                                </span>
                            </td>

                            <td class="text-nowrap">
                                <div class="d-flex align-items-center">
                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->date, 2) . '<br /><small>' . \Altum\Date::get($row->date, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->date) . ')</small>') ?>">
                                        <i class="fas fa-fw fa-calendar text-muted"></i>
                                    </span>

                                    <?php ob_start() ?>
                                    <div class='d-flex flex-column text-left'>
                                        <?php foreach(['referrer_host', 'referrer_path'] as $key): ?>
                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('analytics.filters.by.' . $key) ?></div>
                                                <strong><?= $row->{$key} ?: l('global.none') ?></strong>
                                            </div>
                                        <?php endforeach ?>
                                    </div>
                                    <?php $tooltip = ob_get_clean() ?>
                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                        <i class="fas fa-fw fa-random text-muted"></i>
                                    </span>

                                    <?php ob_start() ?>
                                    <div class='d-flex flex-column text-left'>
                                        <?php foreach(['utm_source', 'utm_medium', 'utm_campaign',] as $key): ?>
                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('analytics.filters.by.' . $key) ?></div>
                                                <strong><?= $row->{$key} ?: l('global.none') ?></strong>
                                            </div>
                                        <?php endforeach ?>
                                    </div>
                                    <?php $tooltip = ob_get_clean() ?>
                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                        <i class="fas fa-fw fa-link text-muted"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" title="<?= get_continent_from_continent_code($row->continent_code ?? l('global.unknown')) ?>">
                                        <i class="fas fa-fw fa-globe-europe text-muted"></i>
                                    </span>

                                    <?php if($row->country_code): ?>
                                        <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($row->country_code) . '.svg' ?>" class="icon-favicon mr-2" data-toggle="tooltip" title="<?= get_country_from_country_code($row->country_code) ?>" />
                                    <?php else: ?>
                                        <span class="mr-2" data-toggle="tooltip" title="<?= l('global.unknown') ?>">
                                            <i class="fas fa-fw fa-flag text-muted"></i>
                                        </span>
                                    <?php endif ?>

                                    <span class="mr-2" data-toggle="tooltip" title="<?= $row->city_name ?? l('global.unknown') ?>">
                                        <i class="fas fa-fw fa-city text-muted"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" title="<?= l('global.device.' . $row->device_type) ?>">
                                        <i class="fas fa-fw fa-sm fa-<?= $row->device_type ?> text-muted"></i>
                                    </span>

                                    <img src="<?= ASSETS_FULL_URL . 'images/os/' . os_name_to_os_key($row->os_name) . '.svg' ?>" class="img-fluid icon-favicon mr-2" data-toggle="tooltip" title="<?= $row->os_name ?: l('global.unknown') ?>" />

                                    <img src="<?= ASSETS_FULL_URL . 'images/browsers/' . browser_name_to_browser_key($row->browser_name) . '.svg' ?>" class="img-fluid icon-favicon mr-2" data-toggle="tooltip" title="<?= $row->browser_name ?: l('global.unknown') ?>" />

                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('pageviews.expiration_date') . '<br />' . \Altum\Date::get($row->expiration_date, 2) . '<br /><small>' . \Altum\Date::get($row->expiration_date, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_time_until($row->expiration_date) . ')</small>' ?>">
                                        <i class="fas fa-fw fa-hourglass-half text-muted"></i>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/pageviews-lightweight/pageview_dropdown_button.php', ['id' => $row->event_id]) ?>
                                </div>
                            </td>
                        </tr>

                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </form>

        <div class="mt-3"><?= $data->pagination ?></div>

    <?php endif ?>

</div>

<input type="hidden" name="start_date" value="<?= \Altum\Date::get($data->datetime['start_date'], 1) ?>" />
<input type="hidden" name="end_date" value="<?= \Altum\Date::get($data->datetime['end_date'], 1) ?>" />
<input type="hidden" name="website_id" value="<?= $this->website->website_id ?>" />

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
                <?= json_encode(l('global.date.this_year')) ?>: [moment().startOf('year'), moment()],
                <?= json_encode(l('global.date.last_year')) ?>: [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
            <?= json_encode(l('global.date.all_time')) ?>: [moment($('#daterangepicker').data('min-date')), moment()]
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

<?php require THEME_PATH . 'views/partials/js_bulk.php' ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/bulk_delete_modal.php'), 'modals'); ?>
