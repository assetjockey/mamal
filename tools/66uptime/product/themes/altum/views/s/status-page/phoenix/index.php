<?php defined('ALTUMCODE') || die() ?>

<?= $this->views['header'] ?>

<div class="container mt-4">
    <?php if(count($data->monitors) || count($data->heartbeats)): ?>
    <div class="card border-0 phoenix-shadow-header rounded-2x">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <?php if($data->resources_status): ?>
                    <div class="svg-head-status text-primary-400 d-inline-block pulse-animation pulse-animation-success">
                        <span class="pulse-circle"></span>
                        <?= include_view(ASSETS_PATH . '/images/icons/check-circle.svg') ?>
                    </div>

                    <div class="ml-3">
                        <span class="h3"><?= l('s_status_page.resources_status_ok') ?></span>
                    </div>
                <?php else: ?>
                    <div class="svg-head-status text-danger d-inline-block pulse-animation pulse-animation-danger">
                        <span class="pulse-circle"></span>
                        <?= include_view(ASSETS_PATH . '/images/icons/x-circle.svg') ?>
                    </div>

                    <div class="ml-3">
                        <span class="h3"><?= l('s_status_page.resources_status_not_ok') ?></span>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>

    <div class="my-4 d-flex justify-content-end">
        <div
                id="daterangepicker"
                role="button"
                class="btn btn-sm btn-light bg-white border-gray-100 text-muted"
                data-min-date="<?= \Altum\Date::get($data->status_page_earliest_datetime_available, 4) ?>"
                data-max-date="<?= \Altum\Date::get('', 4) ?>"
                style="z-index: 1;"
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

    <div class="mt-4">
        <?php foreach($data->monitors as $monitor): ?>
            <div class="card rounded-2x my-4 phoenix-shadow">
                <div class="card-body">

                    <?php if (!empty($monitor->monitor_logs)): ?>
                        <div class="d-flex flex-column flex-lg-row justify-content-between">
                            <div class="mb-2 mb-lg-0">
                                <?php if($monitor->is_ok): ?>
                                    <div class="svg-lg text-primary d-inline-block mr-2" data-toggle="tooltip" title="<?= sprintf(l('s_monitor.monitor_status_ok'), $monitor->name) ?>">
                                        <?= include_view(ASSETS_PATH . '/images/icons/check-circle.svg') ?>
                                    </div>
                                <?php else: ?>
                                    <div class="svg-lg text-danger d-inline-block mr-2" data-toggle="tooltip" title="<?= sprintf(l('s_monitor.monitor_status_not_ok'), $monitor->name) ?>">
                                        <?= include_view(ASSETS_PATH . '/images/icons/x-circle.svg') ?>
                                    </div>
                                <?php endif ?>

                                <a href="<?= $data->status_page->full_url . 'monitor/' . $monitor->monitor_id . (isset($_GET['start_date'], $_GET['end_date']) ? '?start_date=' . $data->date->start_date . '&end_date=' . $data->date->end_date : null) ?>" class="font-weight-bold text-dark"><?= $monitor->name ?></a>
                            </div>

                            <?php
                            /* Determine the border color based on the status */
                            $uptime_class_name = match (true) {
                                $monitor->monitor_logs_data['uptime'] >= 90 => 'success',
                                $monitor->monitor_logs_data['uptime'] >= 50 => 'warning',
                                $monitor->monitor_logs_data['uptime'] >= 0 => 'danger',
                            };
                            ?>

                            <div class="d-flex flex-column mb-2">
                                <div>
                                    <span class="badge badge-pill badge-<?= $uptime_class_name ?> mr-2" data-toggle="tooltip" title="<?= l('monitor.uptime') ?>">
                                        <?= nr($monitor->monitor_logs_data['uptime'], settings()->monitors_heartbeats->decimals) . '%' ?>
                                    </span>

                                    <span class="badge badge-pill badge-light mr-2" data-toggle="tooltip" title="<?= l('monitor.average_response_time') ?>">
                                        <?= display_response_time($monitor->monitor_logs_data['average_response_time']) ?>
                                    </span>

                                    <span class="badge badge-pill badge-light bg-gray-100 mr-2" data-toggle="tooltip" title="<?= sprintf(l('s_status_page.total_not_ok_checks'), nr($monitor->monitor_logs_data['total_not_ok_checks'])) ?>">❌</span>

                                    <span class="badge badge-pill badge-light bg-gray-100" data-toggle="tooltip" title="<?= sprintf(l('s_monitor.total_monitor_logs'), nr($monitor->monitor_logs_data['total_monitor_logs'])) ?>">📶</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap">
                                <?php foreach($monitor->monitor_logs as $log): ?>

                                    <?php ob_start() ?>
                                    <div class='d-flex flex-column text-left'>
                                        <div class='d-flex flex-column my-1'>
                                            <div><?= \Altum\Date::get($log->datetime, 1) ?></div>
                                            <strong><?= \Altum\Date::get_timeago($log->datetime) ?></strong>
                                        </div>

                                        <div class='d-flex flex-column my-1'>
                                            <div><?= l('monitor.is_ok_label') ?></div>
                                            <strong><?= ($log->is_ok ? l('global.yes') : l('global.no')) ?></strong>
                                        </div>

                                        <div class='d-flex flex-column my-1'>
                                            <div><?= l('monitor.response_time_label') ?></div>
                                            <strong><?= display_response_time($log->response_time) ?></strong>
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

                    <?php else: ?>
                        <div class="d-flex flex-column flex-lg-row justify-content-between">
                            <div class="d-flex align-items-center mb-2 mb-lg-0">
                                <?php if($monitor->is_ok): ?>
                                    <div class="svg-lg text-primary d-inline-block mr-2"><?= include_view(ASSETS_PATH . '/images/icons/check-circle.svg') ?></div>
                                <?php else: ?>
                                    <div class="svg-lg text-danger d-inline-block mr-2"><?= include_view(ASSETS_PATH . '/images/icons/x-circle.svg') ?></div>
                                <?php endif ?>

                                <a href="<?= $data->status_page->full_url . 'monitor/' . $monitor->monitor_id . (isset($_GET['start_date'], $_GET['end_date']) ? '?start_date=' . $data->date->start_date . '&end_date=' . $data->date->end_date : null) ?>" class="font-weight-bold text-dark"><?= $monitor->name ?></a>
                            </div>
                        </div>

                        <div class="small text-muted mt-2"><?= l('s_status_page.no_logs') ?></div>
                    <?php endif ?>

                </div>
            </div>
        <?php endforeach ?>

        <?php foreach($data->heartbeats as $heartbeat): ?>
            <div class="card rounded-2x my-4 phoenix-shadow">
                <div class="card-body">

                    <?php if (!empty($heartbeat->heartbeat_logs)): ?>
                        <div class="d-flex flex-column flex-lg-row justify-content-between">
                            <div class="mb-2 mb-lg-0">
                                <?php if($heartbeat->is_ok): ?>
                                    <div class="svg-lg text-primary d-inline-block mr-2" data-toggle="tooltip" title="<?= sprintf(l('s_heartbeat.heartbeat_status_ok'), $heartbeat->name) ?>">
                                        <?= include_view(ASSETS_PATH . '/images/icons/check-circle.svg') ?>
                                    </div>
                                <?php else: ?>
                                    <div class="svg-lg text-danger d-inline-block mr-2" data-toggle="tooltip" title="<?= sprintf(l('s_heartbeat.heartbeat_status_not_ok'), $heartbeat->name) ?>">
                                        <?= include_view(ASSETS_PATH . '/images/icons/x-circle.svg') ?>
                                    </div>
                                <?php endif ?>

                                <a href="<?= $data->status_page->full_url . 'heartbeat/' . $heartbeat->heartbeat_id . (isset($_GET['start_date'], $_GET['end_date']) ? '?start_date=' . $data->date->start_date . '&end_date=' . $data->date->end_date : null) ?>" class="font-weight-bold text-dark"><?= $heartbeat->name ?></a>
                            </div>

                            <?php
                            /* Determine the border color based on the status */
                            $uptime_class_name = match (true) {
                                $heartbeat->heartbeat_logs_data['uptime'] >= 90 => 'success',
                                $heartbeat->heartbeat_logs_data['uptime'] >= 50 => 'warning',
                                $heartbeat->heartbeat_logs_data['uptime'] >= 0 => 'danger',
                            };
                            ?>

                            <div class="d-flex flex-column mb-2">
                                <div>
                                    <span class="badge badge-pill badge-<?= $uptime_class_name ?> mr-2" data-toggle="tooltip" title="<?= l('monitor.uptime') ?>">
                                        <?= nr($heartbeat->heartbeat_logs_data['uptime'], settings()->monitors_heartbeats->decimals) . '%' ?>
                                    </span>

                                    <span class="badge badge-pill badge-light bg-gray-100 mr-2" data-toggle="tooltip" title="<?= sprintf(l('s_status_page.total_not_ok_checks'), nr($monitor->monitor_logs_data['total_not_ok_checks'])) ?>">❌</span>

                                    <span class="badge badge-pill badge-light bg-gray-100" data-toggle="tooltip" title="<?= sprintf(l('s_heartbeat.total_heartbeat_logs'), nr($heartbeat->heartbeat_logs_data['total_heartbeat_logs'])) ?>">📶</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap">
                            <?php foreach($heartbeat->heartbeat_logs as $log): ?>

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

                    <?php else: ?>
                        <div class="d-flex flex-column flex-lg-row justify-content-between">
                            <div class="d-flex align-items-center mb-2 mb-lg-0">
                                <?php if($heartbeat->is_ok): ?>
                                    <div class="svg-lg text-primary d-inline-block mr-2"><?= include_view(ASSETS_PATH . '/images/icons/check-circle.svg') ?></div>
                                <?php else: ?>
                                    <div class="svg-lg text-danger d-inline-block mr-2"><?= include_view(ASSETS_PATH . '/images/icons/x-circle.svg') ?></div>
                                <?php endif ?>

                                <a href="<?= $data->status_page->full_url . 'heartbeat/' . $heartbeat->heartbeat_id . (isset($_GET['start_date'], $_GET['end_date']) ? '?start_date=' . $data->date->start_date . '&end_date=' . $data->date->end_date : null) ?>" class="font-weight-bold text-dark"><?= $heartbeat->name ?></a>
                            </div>
                        </div>

                        <div class="small text-muted mt-2"><?= l('s_status_page.no_logs') ?></div>
                    <?php endif ?>

                </div>
            </div>
        <?php endforeach ?>
    </div>

    <div class="mt-5">
        <div><small class="text-muted"><?= sprintf(l('s_status_page.timezone'), $data->status_page->timezone) ?></small></div>
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
        <?php endif ?>
    </div>
</div>

<?php if($data->status_page->settings->display_share_buttons): ?>
    <?= include_view(THEME_PATH . 'views/s/partials/share.php', ['external_url' => $data->status_page->full_url]) ?>
<?php endif ?>


<?php if(count($data->monitors) || count($data->heartbeats)): ?>

    <?php ob_start() ?>
    <link href="<?= ASSETS_FULL_URL . 'css/libraries/daterangepicker.min.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
    <?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

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
            redirect(`<?= $data->status_page->full_url ?>?start_date=${start.format('YYYY-MM-DD')}&end_date=${end.format('YYYY-MM-DD')}`, true);

        });
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php endif ?>
