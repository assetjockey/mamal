<?php defined('ALTUMCODE') || die() ?>

<?php if(!settings()->notifications->analytics_is_enabled): ?>
    <div class="alert alert-warning" role="alert">
        <?= l('statistics.disabled') ?>
    </div>
<?php endif ?>

<div class="mt-5 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 text-truncate mb-0">
			<?= l('statistics.link') ?>
        </h2>

        <div class="d-flex align-items-center col-auto p-0">
            <div data-toggle="tooltip" title="<?= l('statistics_reset_modal.header') ?>">
                <button
                        type="button"
                        class="btn btn-link text-secondary"
                        data-toggle="modal"
                        data-target="#campaign_statistics_reset_modal"
                        aria-label="<?= l('statistics_reset_modal.header') ?>"
                        data-campaign-id="<?= $data->campaign->campaign_id ?>"
                        data-start-date="<?= $data->datetime['start_date'] ?>"
                        data-end-date="<?= $data->datetime['end_date'] ?>"
                >
                    <i class="fas fa-fw fa-sm fa-eraser"></i>
                </button>
            </div>

            <div>
                <div
                        id="daterangepicker"
                        role="button"
                        class="btn btn-sm btn-light"
                        data-min-date="<?= \Altum\Date::get($data->campaign->datetime, 4) ?>"
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
        </div>
    </div>
</div>

<?php if(!$data->has_data): ?>

    <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
        'filters_get' => $data->filters->get ?? [],
        'name' => 'statistics',
        'has_secondary_text' => true,
        'has_wrapper' => true,
    ]); ?>

<?php else: ?>

    <div class="row justify-content-between mb-5">
        <div class="col-12 col-md-6 col-lg-3 p-3">
            <div class="card h-100">
                <div class="card-body d-flex">

                    <div>
                        <div class="card bg-gray-200 text-gray-700 mr-3">
                            <div class="p-3 d-flex align-items-center justify-content-between">
                                <i class="fas fa-fw fa-eye fa-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="card-title h4 m-0"><?= nr($data->logs_total['impression']) ?></div>
                        <small class="form-text text-muted"><?= l('statistics.impressions_chart') ?></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3 p-3">
            <div class="card h-100">
                <div class="card-body d-flex">

                    <div>
                        <div class="card bg-gray-200 text-gray-700 mr-3">
                            <div class="p-3 d-flex align-items-center justify-content-between">
                                <i class="fas fa-fw fa-mouse-pointer fa-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="card-title h4 m-0"><?= nr($data->logs_total['hover']) ?></div>
                        <small class="form-text text-muted"><?= l('statistics.hovers_chart') ?></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3 p-3">
            <div class="card h-100">
                <div class="card-body d-flex">

                    <div>
                        <div class="card bg-gray-200 text-gray-700 mr-3">
                            <div class="p-3 d-flex align-items-center justify-content-between">
                                <i class="fas fa-fw fa-mouse fa-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="card-title h4 m-0"><?= nr($data->logs_total['click']) ?></div>
                        <small class="form-text text-muted"><?= l('statistics.clicks_chart') ?></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3 p-3">
            <div class="card h-100">
                <div class="card-body d-flex">

                    <div>
                        <div class="card bg-gray-200 text-gray-700 mr-3">
                            <div class="p-3 d-flex align-items-center justify-content-between">
                                <i class="fas fa-fw fa-bolt fa-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="card-title h4 m-0"><?= nr($data->logs_total['ctr'], 2) . '%' ?></div>
                        <small class="text-muted"><?= l('notification.statistics.ctr_chart') ?></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-6 p-3">
            <div class="card h-100">
                <div class="card-body d-flex">
                    <div>
                        <div class="card bg-gray-200 text-gray-700 mr-3">
                            <div class="p-3 d-flex align-items-center justify-content-between">
                                <i class="fas fa-fw fa-database fa-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="card-title h4 m-0"><?= nr($data->logs_total['form_submission']) ?></div>
                        <small class="form-text text-muted"><?= l('statistics.form_submissions_chart') ?></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-6 p-3">
            <div class="card h-100">
                <div class="card-body d-flex">
                    <div>
                        <div class="card bg-gray-200 text-gray-700 mr-3">
                            <div class="p-3 d-flex align-items-center justify-content-between">
                                <i class="fas fa-fw fa-align-center fa-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="card-title h4 m-0"><?= nr($data->logs_total['conversions'], 2) . '%' ?></div>
                        <small class="form-text text-muted"><?= l('notification.statistics.conversions_chart') ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <div class="chart-container">
                <canvas id="impressions_chart"></canvas>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <div class="chart-container">
                <canvas id="hovers_chart"></canvas>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <div class="chart-container">
                <canvas id="clicks_chart"></canvas>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <div class="chart-container">
                <canvas id="form_submissions_chart"></canvas>
            </div>
        </div>
    </div>

    <?php if($data->notifications_performance_result->num_rows): ?>

        <div class="d-flex align-items-center mb-3 text-truncate">
            <h2 class="h5 m-0 text-truncate"><?= l('notification.statistics.header_notifications_performance') ?></h2>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('notification.statistics.subheader_notifications_performance') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="table-responsive table-custom-container mb-5">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th><?= l('notifications.notification') ?></th>
                    <th><?= l('global.type') ?></th>
                    <th><?= l('notification.statistics.impressions_chart') ?></th>
                    <th><?= l('notification.statistics.clicks_chart') ?></th>
                    <th><?= l('notification.statistics.ctr_chart') ?></th>
                    <th><?= l('notification.statistics.form_submissions_chart') ?></th>
                    <th><?= l('notification.statistics.conversions_chart') ?></th>
                </tr>
                </thead>
                <tbody>

                <?php while($row = $data->notifications_performance_result->fetch_object()): ?>
                    <?php
                    $ctr = get_percentage_between_two_numbers($row->clicks, $row->impressions);
                    $conversions = get_percentage_between_two_numbers($row->form_submissions, $row->impressions);
                    ?>

                    <tr>
                        <td class="text-nowrap">
                            <a href="<?= url('notification/' . $row->notification_id . '/statistics') ?>"><?= $row->name ?></a>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge badge-secondary">
                                <i class="<?= l('notification.' . mb_strtolower($row->type) . '.icon') ?> fa-sm mr-1"></i>
                                <?= l('notification.' . mb_strtolower($row->type) . '.name') ?>
                            </span>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge badge-light"><i class="fas fa-fw fa-sm fa-eye mr-1"></i> <?= nr($row->impressions) ?></span>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge badge-light"><i class="fas fa-fw fa-sm fa-computer-mouse mr-1"></i> <?= nr($row->clicks) ?></span>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge badge-light"><i class="fas fa-fw fa-sm fa-bolt mr-1"></i> <?= nr($ctr, 2) ?>%</span>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge badge-light"><i class="fas fa-fw fa-sm fa-database mr-1"></i> <?= nr($row->form_submissions) ?></span>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge badge-light"><i class="fas fa-fw fa-sm fa-align-center mr-1"></i> <?= nr($conversions, 2) ?>%</span>
                        </td>
                    </tr>

                <?php endwhile ?>

                </tbody>
            </table>
        </div>

    <?php endif ?>

    <?php if($data->top_pages_result->num_rows): ?>

        <div class="d-flex align-items-center mb-3 text-truncate">
            <h2 class="h5 m-0 text-truncate"><?= l('notification.statistics.header_top_pages') ?></h2>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('notification.statistics.subheader_top_pages') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th><?= l('notification.statistics.path') ?></th>
                    <th><?= l('notification.statistics.impressions_chart') ?></th>
                    <th><?= l('notification.statistics.hovers_chart') ?></th>
                    <th><?= l('notification.statistics.clicks_chart') ?></th>
                    <th><?= l('notification.statistics.ctr_chart') ?></th>
                    <th><?= l('notification.statistics.form_submissions_chart') ?></th>
                    <th><?= l('notification.statistics.conversions_chart') ?></th>
                </tr>
                </thead>
                <tbody>

                <?php while($row = $data->top_pages_result->fetch_object()): ?>
                    <?php
                    $ctr = get_percentage_between_two_numbers($row->clicks, $row->impressions);
                    $conversions = get_percentage_between_two_numbers($row->form_submissions, $row->impressions);
                    ?>

                    <tr>
                        <td class="text-nowrap">
                            <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($data->campaign->domain) ?>" class="img-fluid icon-favicon-small mr-2" loading="lazy" />

                            <span title="<?= $row->path ?>"><?= string_truncate($row->path, 64) ?></span>

                            <a href="<?= 'https://' . $data->campaign->domain . $row->path ?>" target="_blank" rel="nofollow noopener" class="text-muted ml-2"><i class="fas fa-fw fa-xs fa-external-link-alt"></i></a>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge badge-light"><i class="fas fa-fw fa-sm fa-eye mr-1"></i> <?= nr($row->impressions) ?></span>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge badge-light"><i class="fas fa-fw fa-sm fa-mouse-pointer mr-1"></i> <?= nr($row->hovers) ?></span>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge badge-light"><i class="fas fa-fw fa-sm fa-computer-mouse mr-1"></i> <?= nr($row->clicks) ?></span>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge badge-light"><i class="fas fa-fw fa-sm fa-bolt mr-1"></i> <?= nr($ctr, 2) ?>%</span>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge badge-light"><i class="fas fa-fw fa-sm fa-database mr-1"></i> <?= nr($row->form_submissions) ?></span>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge badge-light"><i class="fas fa-fw fa-sm fa-align-center mr-1"></i> <?= nr($conversions, 2) ?>%</span>
                        </td>
                    </tr>

                <?php endwhile ?>

                </tbody>
            </table>
        </div>

    <?php endif ?>

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

    <?php if ($data->has_data): ?>
    let impressions_chart = document.getElementById('impressions_chart').getContext('2d');

    let gradient = impressions_chart.createLinearGradient(0, 0, 0, 250);
    gradient.addColorStop(0, 'rgba(96, 122, 226, 0.6)');
    gradient.addColorStop(1, 'rgba(96, 122, 226, 0.05)');

    new Chart(impressions_chart, {
        type: 'line',
        data: {
            labels: <?= $data->logs_chart['labels'] ?>,
            datasets: [{
                label: <?= json_encode(l('statistics.impressions_chart')) ?>,
                data: <?= $data->logs_chart['impression'] ?? '[]' ?>,
                backgroundColor: gradient,
                borderColor: '#607ae2',
                fill: true
            }]
        },
        options: chart_options
    });


    let hovers_chart = document.getElementById('hovers_chart').getContext('2d');
    gradient = hovers_chart.createLinearGradient(0, 0, 0, 250);
    gradient.addColorStop(0, 'rgba(213, 96, 226, 0.6)');
    gradient.addColorStop(1, 'rgba(213, 96, 226, 0.05)');

    new Chart(hovers_chart, {
        type: 'line',
        data: {
            labels: <?= $data->logs_chart['labels'] ?>,
            datasets: [{
                label: <?= json_encode(l('statistics.hovers_chart')) ?>,
                data: <?= $data->logs_chart['hover'] ?? '[]' ?>,
                backgroundColor: gradient,
                borderColor: '#d560e2',
                fill: true
            }]
        },
        options: chart_options
    });

    let clicks_chart = document.getElementById('clicks_chart').getContext('2d');

    gradient = clicks_chart.createLinearGradient(0, 0, 0, 250);
    gradient.addColorStop(0, 'rgba(96, 187, 226, 0.4)');
    gradient.addColorStop(1, 'rgba(96, 187, 226, 0.05)');

    new Chart(clicks_chart, {
        type: 'line',
        data: {
            labels: <?= $data->logs_chart['labels'] ?>,
            datasets: [{
                label: <?= json_encode(l('statistics.clicks_chart')) ?>,
                data: <?= $data->logs_chart['click'] ?? '[]' ?>,
                backgroundColor: gradient,
                borderColor: '#60BBE2',
                fill: true
            }]
        },
        options: chart_options
    });

    let form_submissions_chart = document.getElementById('form_submissions_chart').getContext('2d');

    gradient = form_submissions_chart.createLinearGradient(0, 0, 0, 250);
    gradient.addColorStop(0, 'rgba(226, 96, 174, 0.4)');
    gradient.addColorStop(1, 'rgba(226, 96, 174, 0.05)');

    new Chart(form_submissions_chart, {
        type: 'line',
        data: {
            labels: <?= $data->logs_chart['labels'] ?>,
            datasets: [{
                label: <?= json_encode(l('statistics.form_submissions_chart')) ?>,
                data: <?= $data->logs_chart['form_submission'] ?? '[]' ?>,
                backgroundColor: gradient,
                borderColor: '#E260AE',
                fill: true
            }]
        },
        options: chart_options
    });
    <?php endif ?>
</script>

<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/statistics_reset_modal.php', ['modal_id' => 'campaign_statistics_reset_modal', 'resource_id' => 'campaign_id', 'path' => 'campaign/' . $data->campaign->campaign_id . '/statistics/reset']), 'modals'); ?>
