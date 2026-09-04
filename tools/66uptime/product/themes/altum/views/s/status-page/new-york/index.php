<?php defined('ALTUMCODE') || die() ?>

<?= $this->views['header'] ?>

<div class="container mt-4">
    <?php if(count($data->monitors) || count($data->heartbeats)): ?>
        <div class="card bg-blue-900 border-0 rounded-2x">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <?php if($data->resources_status): ?>
                        <div class="svg-head-status text-primary-400 d-inline-block pulse-animation pulse-animation-success">
                            <span class="pulse-circle"></span>
                            <?= include_view(ASSETS_PATH . '/images/icons/check-circle.svg') ?>
                        </div>

                        <div class="ml-3">
                            <span class="text-white h3"><?= l('s_status_page.resources_status_ok') ?></span>
                        </div>
                    <?php else: ?>
                        <div class="svg-head-status text-danger d-inline-block pulse-animation pulse-animation-danger">
                            <span class="pulse-circle"></span>
                            <?= include_view(ASSETS_PATH . '/images/icons/x-circle.svg') ?>
                        </div>

                        <div class="ml-3">
                            <span class="text-white h3"><?= l('s_status_page.resources_status_not_ok') ?></span>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </div>

        <div class="my-4 d-flex justify-content-end">
            <div
                    id="daterangepicker"
                    role="button"
                    class="btn btn-sm btn-light"
                    data-min-date="<?= \Altum\Date::get($data->status_page_earliest_datetime_available, 4) ?>"
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

        <div class="mt-4">
            <?php foreach($data->monitors as $monitor): ?>
                <div class="card my-4">
                    <div class="card-body">

                        <?php if ($monitor->monitor_logs_data['total_monitor_logs']): ?>
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

                                <div class="d-flex flex-column">
                                    <div>
                                        <span class="text-muted mr-3" data-toggle="tooltip" title="<?= l('monitor.uptime') ?>">
                                            <?= nr($monitor->monitor_logs_data['uptime'], settings()->monitors_heartbeats->decimals) . '%' ?>
                                        </span>

                                        <span class="text-muted mr-3" data-toggle="tooltip" title="<?= l('monitor.average_response_time') ?>">
                                            <?= display_response_time($monitor->monitor_logs_data['average_response_time']) ?>
                                        </span>

                                        <span class="text-muted">
                                            <?= sprintf(l('s_status_page.total_not_ok_checks'), nr($monitor->monitor_logs_data['total_not_ok_checks'])) ?>
                                        </span>
                                    </div>
                                    <div>
                                        <small class="text-muted"><?= sprintf(l('s_monitor.total_monitor_logs'), nr($monitor->monitor_logs_data['total_monitor_logs'])) ?></small>
                                    </div>
                                </div>
                            </div>

                            <div class="chart-container mt-2" style="height: 175px;">
                                <canvas id="monitor_logs_chart_<?= $monitor->monitor_id ?>"></canvas>
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
                <div class="card my-4">
                    <div class="card-body">

                        <?php if ($heartbeat->heartbeat_logs_data['total_heartbeat_logs']): ?>
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

                                <div class="d-flex flex-column">
                                    <div>
                                        <span class="text-muted mr-3" data-toggle="tooltip" title="<?= l('monitor.uptime') ?>">
                                            <?= nr($heartbeat->heartbeat_logs_data['uptime'], settings()->monitors_heartbeats->decimals) . '%' ?>
                                        </span>

                                        <span class="text-muted"><?= sprintf(l('s_status_page.total_not_ok_checks'), nr($heartbeat->heartbeat_logs_data['total_not_ok_checks'])) ?></span>
                                    </div>
                                    <div>
                                        <small class="text-muted"><?= sprintf(l('s_heartbeat.total_heartbeat_logs'), nr($heartbeat->heartbeat_logs_data['total_heartbeat_logs'])) ?></small>
                                    </div>
                                </div>
                            </div>

                            <div class="chart-container mt-2" style="height: 175px;">
                                <canvas id="heartbeat_logs_chart_<?= $heartbeat->heartbeat_id ?>"></canvas>
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
        </div>
    <?php endif ?>
</div>

<?php if($data->status_page->settings->display_share_buttons): ?>
    <?= include_view(THEME_PATH . 'views/s/partials/share.php', ['external_url' => $data->status_page->full_url]) ?>
<?php endif ?>


<?php if(count($data->monitors) || count($data->heartbeats)): ?>

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
            redirect(`<?= $data->status_page->full_url ?>?start_date=${start.format('YYYY-MM-DD')}&end_date=${end.format('YYYY-MM-DD')}`, true);

        });

        let css = window.getComputedStyle(document.body);
        let is_ok_color = css.getPropertyValue('--primary');
        let is_not_ok_color = css.getPropertyValue('--danger');
        let is_ok_colors = [];

        let monitor_logs_charts = {};
        let heartbeat_logs_charts = {};
        let tooltip_titles = null;

        /* Monitors */
        <?php foreach($data->monitors as $monitor): ?>

        <?php if(!$monitor->monitor_logs_data['total_monitor_logs']) continue ?>

        /* Response Time chart */
        monitor_logs_charts[<?= json_encode($monitor->monitor_id) ?>] = document.getElementById('monitor_logs_chart_<?= $monitor->monitor_id ?>').getContext('2d');

        /* Generate colors based on if monitor is ok */
        is_ok_colors = [];
        <?= $monitor->monitor_logs_chart['is_ok'] ?? '[]' ?>.forEach(is_ok => {
            is_ok_colors.push(parseInt(is_ok) ? is_ok_color : is_not_ok_color);
        })

        /* Tooltip titles */
        tooltip_titles = <?= $monitor->monitor_logs_chart['labels'] ?>;
        chart_options.plugins.tooltip.callbacks.title = (context) => {
            return tooltip_titles[context[0].dataIndex];
        }

        /* Display chart */
        new Chart(monitor_logs_charts[<?= json_encode($monitor->monitor_id) ?>], {
            type: 'bar',
            data: {
                labels: <?= $monitor->monitor_logs_chart['hour_minute_second_label'] ?>,
                datasets: [
                    {
                        label: <?= json_encode(l('monitor.response_time_label')) ?>,
                        data: <?= $monitor->monitor_logs_chart['response_time'] ?? '[]' ?>,
                        backgroundColor: is_ok_colors,
                        borderColor: is_ok_colors,
                        fill: true,
                    }
                ]
            },
            options: {
                ...chart_options,
                plugins: {
                    ...chart_options.plugins,
                    tooltip: {
                        ...chart_options.plugins.tooltip,
                        callbacks: {
                            ...chart_options.plugins.tooltip.callbacks,
                            label: (context) => {
                                return context.datasetIndex == 0 ?
                                    `${context.dataset.label}: ${context.dataset.backgroundColor[context.dataIndex] == is_not_ok_color ? 0 : nr(context.raw)}` :
                                    `${context.dataset.label}: ${context.raw == 0 ? <?= json_encode(l('global.no')) ?> : <?= json_encode(l('global.yes')) ?>}`;
                            }
                        }
                    }
                }
            }
        });

        <?php endforeach ?>

        /* Heartbeats */
        <?php foreach($data->heartbeats as $heartbeat): ?>

        <?php if(!$heartbeat->heartbeat_logs_data['total_heartbeat_logs']) continue ?>

        /* Response Time chart */
        heartbeat_logs_charts[<?= json_encode($heartbeat->heartbeat_id) ?>] = document.getElementById('heartbeat_logs_chart_<?= $heartbeat->heartbeat_id ?>').getContext('2d');

        /* Generate colors based on if heartbeat is ok */
        is_ok_colors = [];
        <?= $heartbeat->heartbeat_logs_chart['is_ok'] ?? '[]' ?>.forEach(is_ok => {
            is_ok_colors.push(parseInt(is_ok) ? is_ok_color : is_not_ok_color);
        })

        /* Tooltip titles */
        tooltip_titles = <?= $heartbeat->heartbeat_logs_chart['labels'] ?>;
        chart_options.plugins.tooltip.callbacks.title = (context) => {
            return tooltip_titles[context[0].dataIndex];
        }

        /* Display chart */
        new Chart(heartbeat_logs_charts[<?= json_encode($heartbeat->heartbeat_id) ?>], {
            type: 'bar',
            data: {
                labels: <?= $heartbeat->heartbeat_logs_chart['hour_minute_second_label'] ?>,
                datasets: [
                    {
                        label: <?= json_encode(l('heartbeat.is_ok')) ?>,
                        data: <?= $heartbeat->heartbeat_logs_chart['is_ok_chart'] ?? '[]' ?>,
                        backgroundColor: is_ok_colors,
                        borderColor: is_ok_colors,
                        fill: true,
                    }
                ]
            },
            options: {
                ...chart_options,
                plugins: {
                    ...chart_options.plugins,
                    tooltip: {
                        ...chart_options.plugins.tooltip,
                        callbacks: {
                            ...chart_options.plugins.tooltip.callbacks,
                            label: (context) => {
                                return `${context.dataset.label}: ${context.raw == 0.999 ? <?= json_encode(l('global.no')) ?> : <?= json_encode(l('global.yes')) ?>}`;
                            }
                        }
                    }
                }
            }
        });
        <?php endforeach ?>

    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php endif ?>
