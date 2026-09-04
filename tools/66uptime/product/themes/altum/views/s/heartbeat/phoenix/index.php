<?php defined('ALTUMCODE') || die() ?>

<?= $this->views['header'] ?>

<div class="container mt-4">

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= $data->status_page->full_url ?>"><?= l('s_status_page.breadcrumb') ?></a> <div class="svg-sm text-muted d-inline-block"><?= include_view(ASSETS_PATH . '/images/icons/chevron-right.svg') ?></div>
                </li>
                <li class="active" aria-current="page"><?= sprintf(l('s_heartbeat.breadcrumb'), $data->heartbeat->name) ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="card bg-blue-900 border-0 rounded-2x">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <?php if($data->heartbeat->is_ok): ?>
                    <div class="svg-head-status text-primary-400 d-inline-bloc kpulse-animation pulse-animation-success">
                        <span class="pulse-circle"></span>
                        <?= include_view(ASSETS_PATH . '/images/icons/check-circle.svg') ?>
                    </div>

                    <div class="ml-3">
                        <span class="text-white h3"><?= sprintf(l('s_heartbeat.heartbeat_status_ok'), $data->heartbeat->name) ?></span>
                        <div>
                            <span class="text-gray-400"><?= sprintf(l('s_heartbeat.last_run_datetime'), \Altum\Date::get_timeago($data->heartbeat->last_run_datetime)) ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="svg-head-status text-danger d-inline-block pulse-animation pulse-animation-success">
                        <span class="pulse-circle"></span>
                        <?= include_view(ASSETS_PATH . '/images/icons/x-circle.svg') ?>
                    </div>

                    <div class="ml-3">
                        <span class="text-white h3"><?= sprintf(l('s_heartbeat.heartbeat_status_not_ok'), $data->heartbeat->name) ?></span>
                        <div>
                            <span class="text-gray-400"><?= sprintf(l('s_heartbeat.last_run_datetime'), \Altum\Date::get_timeago($data->heartbeat->last_run_datetime)) ?></span>
                        </div>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>

    <div class="row justify-content-between my-4">
        <div class="col-12 col-xl mb-3 mb-xl-0">
            <div class="card h-100">
                <div class="card-body d-flex p-3">

                    <div>
                        <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                            <div class="p-3 d-flex align-items-center justify-content-between">
                                <div class="svg-card-icon d-inline-block"><?= include_view(ASSETS_PATH . '/images/icons/globe-alt.svg') ?></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <span class="text-muted font-size-small"><?= l('heartbeat.uptime') ?></span>
                        <div class="d-flex align-items-center">
                            <div class="card-title h6 m-0"><?= $data->total_heartbeat_logs ? nr($data->heartbeat_logs_data['uptime'], settings()->monitors_heartbeats->decimals) . '%' : '?' ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl mb-3 mb-xl-0">
            <div class="card h-100">
                <div class="card-body d-flex p-3">

                    <div>
                        <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                            <div class="p-3 d-flex align-items-center justify-content-between">
                                <div class="svg-card-icon d-inline-block"><?= include_view(ASSETS_PATH . '/images/icons/x-circle.svg') ?></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <span class="text-muted font-size-small"><?= l('heartbeat.total_incidents') ?></span>
                        <div class="d-flex align-items-center">
                            <div class="card-title h6 m-0"><?= $data->total_heartbeat_logs ? nr(count($data->heartbeat_incidents)) : '?' ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="my-4 d-flex justify-content-between align-items-center">
        <div></div>
        <div
                id="daterangepicker"
                role="button"
                class="btn btn-sm btn-light bg-white border-gray-100 text-muted"
                data-min-date="<?= \Altum\Date::get($data->heartbeat->datetime, 4) ?>"
                data-max-date="<?= \Altum\Date::get('', 4) ?>"
        >
            <div class="svg-sm text-muted d-inline-block mr-1">
                <?= include_view(ASSETS_PATH . '/images/icons/calendar.svg') ?>
            </div>
            <span class="">
                <?php if($data->date->start_date == $data->date->end_date): ?>
                    <?= \Altum\Date::get($data->date->start_date, 2, \Altum\Date::$default_timezone) ?>
                <?php else: ?>
                    <?= \Altum\Date::get($data->date->start_date, 2, \Altum\Date::$default_timezone) . ' - ' . \Altum\Date::get($data->date->end_date, 2, \Altum\Date::$default_timezone) ?>
                <?php endif ?>
            </span>
            <i class="fas fa-fw fa-caret-down d-none d-lg-inline-block ml-lg-1"></i>
        </div>
    </div>

    <?php if($data->total_heartbeat_logs): ?>
        <div class="card my-4">
            <div class="card-body">
                <div class="d-flex flex-wrap">
                    <?php foreach($data->heartbeat_logs as $log): ?>

                        <?php ob_start() ?>
                        <div class='d-flex flex-column text-left'>
                            <div class='d-flex flex-column my-1'>
                                <div><?= \Altum\Date::get($log->datetime, 1) ?></div>
                                <strong><?= \Altum\Date::get_timeago($log->datetime) ?></strong>
                            </div>

                            <div class='d-flex flex-column my-1'>
                                <div><?= l('heartbeat.is_ok') ?></div>
                                <strong><?= ($log->is_ok ? l('global.yes') : l('global.no')) ?></strong>
                            </div>
                        </div>
                        <?php $tooltip = ob_get_clean() ?>

                        <div
                                class="status-badge <?= $log->is_ok ? 'bg-success' : 'bg-danger' ?> m-1"
                                data-toggle="tooltip"
                                data-html="true"
                                title="<?= $tooltip ?>"
                        ></div>
                    <?php endforeach ?>
                </div>
            </div>
        </div>

        <?php if (!empty($data->heartbeat_incidents)): ?>
            <div class="my-4">
                <div class="table-responsive table-custom-container">
                    <table class="table table-custom">
                        <thead>
                        <tr>
                            <th colspan="4"><?= l('incidents.header') ?></th>
                        </tr>
                        <tr>
                            <th><?= l('incidents.start_datetime') ?></th>
                            <th><?= l('incidents.end_datetime') ?></th>
                            <th><?= l('incidents.length') ?></th>
                            <th><?= l('incidents.comment') ?></th>
                        </tr>
                        <tbody>
                        <?php foreach($data->heartbeat_incidents as $heartbeat_incident): ?>
                            <tr>
                                <td class="text-truncate text-muted">
                                <span data-toggle="tooltip" title="<?= \Altum\Date::get($heartbeat_incident->start_datetime, 1) ?>">
                                    <?= \Altum\Date::get_timeago($heartbeat_incident->start_datetime) ?>
                                </span>
                                </td>

                                <td class="text-truncate">
                                    <?php if($heartbeat_incident->end_datetime): ?>
                                        <span class="badge badge-success" data-toggle="tooltip" title="<?= \Altum\Date::get($heartbeat_incident->end_datetime, 1) ?>">
                                            <div class="svg-sm align-middle d-inline-block mr-1"><?= include_view(ASSETS_PATH . '/images/icons/check-circle.svg') ?></div>

                                            <span class="align-middle"><?= \Altum\Date::get_timeago($heartbeat_incident->end_datetime) ?></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">
                                            <div class="svg-sm align-middle d-inline-block mr-1"><?= include_view(ASSETS_PATH . '/images/icons/x-circle.svg') ?></div>

                                            <span class="align-middle"><?= l('incidents.end_datetime_null') ?></span>
                                        </span>
                                    <?php endif ?>
                                </td>

                                <td class="text-truncate">
                                    <?= \Altum\Date::get_elapsed_time($heartbeat_incident->start_datetime, $heartbeat_incident->end_datetime) ?>
                                </td>

                                <td class="text-truncate">
                                    <span title="<?= $heartbeat_incident->comment ?>">
                                        <?= $heartbeat_incident->comment ? string_truncate($heartbeat_incident->comment, 30) : l('global.none') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif ?>

    <?php else: ?>

        <div class="card my-4">
            <div class="card-body">
                <span class="text-muted"><?= l('s_heartbeat.heartbeat_logs_no_data') ?></span>
            </div>
        </div>

    <?php endif ?>

    <div class="mt-5">
        <div><small class="text-muted"><?= sprintf(l('s_status_page.timezone'), $data->status_page->timezone) ?></small></div>

        <?php if($data->total_heartbeat_logs): ?>
            <div><small class="text-muted"><?= sprintf(l('s_heartbeat.total_heartbeat_logs'), nr($data->total_heartbeat_logs)) ?></small></div>
        <?php endif ?>

        <?php if($data->status_page->settings->auto_refresh): ?>
            <div><small class="text-muted"><?= sprintf(l('s_status_page.auto_refresh'), $data->status_page->settings->auto_refresh) ?></small></div>

        <?php ob_start() ?>
            <script>
                'use strict';

                setInterval(() => {
                    location.reload();
                }, <?= (int) $data->status_page->settings->auto_refresh * 60000 ?>);
            </script>
            <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
        <?php endif ?>
    </div>
</div>

<?php if($data->status_page->settings->display_share_buttons): ?>
    <?= include_view(THEME_PATH . 'views/s/partials/share.php', ['external_url' => $data->status_page->full_url . 'heartbeat/' . $data->heartbeat->heartbeat_id]) ?>
<?php endif ?>

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

    moment.tz.setDefault(<?= json_encode($data->status_page->timezone) ?>);

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

        /* Redirect */
        redirect(`<?= $data->status_page->full_url . 'heartbeat/' . $data->heartbeat->heartbeat_id ?>?start_date=${start.format('YYYY-MM-DD')}&end_date=${end.format('YYYY-MM-DD')}`, true);

    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
