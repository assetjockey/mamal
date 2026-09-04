<?php defined('ALTUMCODE') || die() ?>

<?php if(settings()->main->breadcrumbs_is_enabled): ?>
<nav aria-label="breadcrumb">
    <ol class="custom-breadcrumbs small">
        <li>
            <a href="<?= url('admin/broadcasts') ?>"><?= l('admin_broadcasts.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
        </li>
        <li class="active" aria-current="page"><?= l('admin_broadcast_view.breadcrumb') ?></li>
    </ol>
</nav>
<?php endif ?>

<div class="d-flex align-items-end mb-4">
    <h1 class="h3 mb-0 mr-3 text-truncate flex-fill min-width-0"><i class="fas fa-fw fa-xs fa-mail-bulk text-primary-900 mr-2"></i> <?= $data->broadcast->name ?></h1>

    <div class="d-flex align-items-center">
        <div>
            <div
                    id="daterangepicker"
                    role="button"
                    class="btn btn-sm btn-light text-nowrap"
                    data-min-date="<?= \Altum\Date::get($data->broadcast->datetime, 4) ?>"
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

        <div class="ml-3">
            <?= include_view(THEME_PATH . 'views/admin/broadcasts/admin_broadcast_dropdown_button.php', ['id' => $data->broadcast->broadcast_id, 'resource_name' => $data->broadcast->name]) ?>
        </div>
    </div>
</div>

<?= \Altum\Alerts::output_alerts() ?>


<div class="mb-4 row justify-content-between">
    <?php
    $status_icon = match($data->broadcast->status) {
        'draft' => '<i class="fas fa-fw fa-sm fa-save text-primary"></i>',
        'processing' => '<i class="fas fa-fw fa-sm fa-spinner fa-spin text-primary"></i>',
        'sent' => '<i class="fas fa-fw fa-sm fa-check-circle text-primary"></i>',
        default => null,
    };

    $status_text = match($data->broadcast->status) {
        'draft' => l('admin_broadcasts.status.draft'),
        'processing' => l('admin_broadcasts.status.processing'),
        'sent' => l('admin_broadcasts.status.sent'),
        default => null,
    };
    ?>

    <div class="col-12 col-sm-6 col-xl p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('global.status') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <?= $status_icon ?>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <span class="h4"><?= $status_text ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= nr(get_percentage_between_two_numbers($data->broadcast->sent_emails, $data->broadcast->total_emails)) . '%' ?>">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('admin_broadcasts.sent_emails') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-envelope text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <span class="h4"><?= nr($data->broadcast->sent_emails) . '/' . nr($data->broadcast->total_emails) ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php if(settings()->content->broadcasts_statistics_is_enabled): ?>
        <div class="col-12 col-sm-6 col-xl p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= nr(get_percentage_between_two_numbers($data->broadcast->views, $data->broadcast->total_emails)) . '%' ?>">
                <div class="card-body">
                    <div class="row">
                        <div class="col text-truncate">
                            <small class="text-muted font-weight-bold"><?= l('admin_broadcasts.views') ?></small>
                        </div>

                        <div class="col-auto">
                            <span class="p-2 bg-primary-100 rounded">
                                <i class="fas fa-fw fa-sm fa-eye text-primary"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mt-2 text-break">
                        <span class="h4"><?= nr($data->broadcast->views) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= nr(get_percentage_between_two_numbers($data->broadcast->clicks, $data->broadcast->total_emails)) . '%' ?>">
                <div class="card-body">
                    <div class="row">
                        <div class="col text-truncate">
                            <small class="text-muted font-weight-bold"><?= l('admin_broadcasts.clicks') ?></small>
                        </div>

                        <div class="col-auto">
                            <span class="p-2 bg-primary-100 rounded">
                                <i class="fas fa-fw fa-sm fa-mouse text-primary"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mt-2 text-break">
                        <span class="h4"><?= nr($data->broadcast->clicks) ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>
</div>

<div class="card mb-5">
    <div class="card-body">
        <div class="chart-container <?= !$data->statistics_chart['is_empty'] ? null : 'd-none' ?>">
            <canvas id="statistics"></canvas>
        </div>
        <?= !$data->statistics_chart['is_empty'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-5">
        <div class="d-flex align-items-center mb-3">
            <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-circle-info mr-1"></i> <?= l('global.details') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>
        </div>

        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <tbody>
                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i>
                        <?= l('global.name') ?>
                    </td>
                    <td class="text-truncate"><?= $data->broadcast->name ?></td>
                </tr>

                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-heading fa-sm text-muted mr-1"></i>
                        <?= l('admin_broadcasts.subject') ?>
                    </td>
                    <td class="text-truncate"><?= $data->broadcast->subject ?></td>
                </tr>

                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-users fa-sm text-muted mr-1"></i>
                        <?= l('admin_broadcasts.audience') ?>
                    </td>
                    <td class="text-truncate"><?= l('admin_broadcasts.audience.' . ($data->is_system_email ? 'system' : 'subscribers')) ?></td>
                </tr>

                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-layer-group fa-sm text-muted mr-1"></i>
                        <?= l('admin_broadcasts.segment') ?>
                    </td>
                    <td class="text-truncate"><?= l('admin_broadcasts.segment.' . $data->broadcast->segment) ?></td>
                </tr>

                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-paper-plane fa-sm text-muted mr-1"></i>
                        <?= sprintf(l('admin_broadcasts.last_sent_email_datetime'), '') ?>
                    </td>
                    <td class="text-nowrap">
                        <?= $data->broadcast->last_sent_email_datetime ? \Altum\Date::get($data->broadcast->last_sent_email_datetime, 2) . ' - <small>' . \Altum\Date::get($data->broadcast->last_sent_email_datetime, 3) . '</small>' : l('global.na') ?>
                    </td>
                </tr>

                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-clock fa-sm text-muted mr-1"></i>
                        <?= sprintf(l('global.datetime_tooltip'), '') ?>
                    </td>
                    <td class="text-nowrap">
                        <?= $data->broadcast->datetime ? \Altum\Date::get($data->broadcast->datetime, 2) . ' - <small>' . \Altum\Date::get($data->broadcast->datetime, 3) . '</small>' : l('global.na') ?>
                    </td>
                </tr>

                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-history fa-sm text-muted mr-1"></i>
                        <?= sprintf(l('global.last_datetime_tooltip'), '') ?>
                    </td>
                    <td class="text-nowrap">
                        <?= $data->broadcast->last_datetime ? \Altum\Date::get($data->broadcast->last_datetime, 2) . ' - <small>' . \Altum\Date::get($data->broadcast->last_datetime, 3) . '</small>' : l('global.na') ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <?php if(settings()->content->broadcasts_statistics_is_enabled): ?>
        <div class="col-12 mb-5">
            <div class="d-flex align-items-center mb-3">
                <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-eye mr-1"></i> <?= l('admin_broadcasts.latest_views') ?></h2>

                <div class="flex-fill">
                    <hr class="border-gray-100" />
                </div>
            </div>

            <?php if(!empty($data->users)): ?>
                <div class="table-responsive table-custom-container">
                    <table class="table table-custom">
                        <thead>
                        <tr>
                            <th><?= l('global.user') ?></th>
                            <th><?= l('global.datetime') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($data->users as $user): ?>
                            <tr>
                                <td class="text-nowrap">
                                    <div class="d-flex align-items-center">
                                        <?php if($user->user_id): ?>
                                            <a href="<?= url('admin/user-view/' . $user->user_id) ?>">
                                                <img src="<?= get_user_avatar($user->avatar, $user->email) ?>" class="table-avatar mr-3" alt="" />
                                            </a>
                                        <?php else: ?>
                                            <img src="<?= get_user_avatar($user->avatar, $user->email) ?>" class="table-avatar mr-3" alt="" />
                                        <?php endif ?>

                                        <div class="d-flex flex-column">
                                            <div>
                                                <?php if($user->user_id): ?>
                                                    <a href="<?= url('admin/user-view/' . $user->user_id) ?>"><?= $user->name ?></a>
                                                <?php else: ?>
                                                    <span><?= $user->name ?></span>
                                                <?php endif ?>
                                            </div>

                                            <span class="small text-muted"><?= $user->email ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-nowrap">
                                    <span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($user->datetime, 1) ?>"><?= \Altum\Date::get_timeago($user->datetime) ?></span>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                    'filters_get' => $data->filters->get ?? [],
                    'name' => 'global',
                    'has_secondary_text' => false,
                    'has_wrapper' => false,
                ]); ?>
            <?php endif ?>
        </div>
    <?php endif ?>
</div>


<?php if(settings()->content->broadcasts_statistics_is_enabled): ?>
    <div class="mb-5">
        <div class="d-flex align-items-center mb-3">
            <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-mouse mr-1"></i> <?= l('admin_broadcasts.clicks') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>
        </div>

        <?php if(!empty($data->clicks)): ?>
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th><?= l('global.url') ?></th>
                        <th><?= l('admin_broadcasts.clicks') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data->clicks as $click): ?>
                        <tr>
                            <td class="text-nowrap">
                                <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($click->target) ?>" class="img-fluid icon-favicon mr-1" loading="lazy" />

                                <a href="<?= $click->target ?>" target="_blank" rel="noreferrer">
                                    <?= remove_url_protocol_from_url($click->target) ?>
                                </a>
                            </td>
                            <td class="text-nowrap"><?= nr($click->clicks) ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                'filters_get' => $data->filters->get ?? [],
                'name' => 'global',
                'has_secondary_text' => false,
                'has_wrapper' => false,
            ]); ?>
        <?php endif ?>
    </div>
<?php endif ?>

<div class="mb-5">
    <div class="d-flex align-items-center mb-3">
        <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-envelope-open-text mr-1"></i> <?= l('admin_broadcasts.content') ?></h2>

        <div class="flex-fill">
            <hr class="border-gray-100" />
        </div>

        <div class="ml-3">
            <button type="button" class="btn btn-sm btn-primary-100" data-toggle="collapse" data-target="#content_preview" aria-expanded="true" aria-controls="content_preview" data-tooltip title="<?= l('global.preview') ?>" data-tooltip-hide-on-click>
                <i class="fas fa-fw fa-sm fa-eye"></i>
            </button>
        </div>
    </div>

    <div class="collapse show" id="content_preview">
        <div class="card">
            <div class="card-body">
                <div class="row justify-content-center">
                    <div class="col-lg-8 text-break">
                        <?= $data->content ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    'use strict'

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
            <?= json_encode(l('global.date.all_time')) ?>: [moment('2015-01-01'), moment()]
        },
        alwaysShowCalendars: true,
        linkedCalendars: false,
        singleCalendar: true,
        locale: <?= json_encode(require APP_PATH . 'includes/daterangepicker_translations.php') ?>,
    }, (start, end, label) => {

        /* Redirect */
        redirect(`<?= url('admin/broadcast-view/' . $data->broadcast->broadcast_id) ?>?start_date=${start.format('YYYY-MM-DD')}&end_date=${end.format('YYYY-MM-DD')}`, true);

    });

    /* Chart */
    let css = window.getComputedStyle(document.body)
    let views_color = css.getPropertyValue('--primary');
    let clicks_color = css.getPropertyValue('--gray-500');
    let views_color_gradient = null;
    let clicks_color_gradient = null;

    /* Display chart */
    let statistics_chart = document.getElementById('statistics').getContext('2d');

    views_color_gradient = statistics_chart.createLinearGradient(0, 0, 0, 250);
    views_color_gradient.addColorStop(0, set_hex_opacity(views_color, 0.1));
    views_color_gradient.addColorStop(1, set_hex_opacity(views_color, 0.025));

    clicks_color_gradient = statistics_chart.createLinearGradient(0, 0, 0, 250);
    clicks_color_gradient.addColorStop(0, set_hex_opacity(clicks_color, 0.1));
    clicks_color_gradient.addColorStop(1, set_hex_opacity(clicks_color, 0.025));

    new Chart(statistics_chart, {
        type: 'line',
        data: {
            labels: <?= $data->statistics_chart['labels'] ?>,
            datasets: [
                {
                    label: <?= json_encode(l('admin_broadcasts.views')) ?>,
                    data: <?= $data->statistics_chart['views'] ?? '[]' ?>,
                    backgroundColor: views_color_gradient,
                    borderColor: views_color,
                    fill: true
                },

                {
                    label: <?= json_encode(l('admin_broadcasts.clicks')) ?>,
                    data: <?= $data->statistics_chart['clicks'] ?? '[]' ?>,
                    backgroundColor: clicks_color_gradient,
                    borderColor: clicks_color,
                    fill: true
                }
            ]
        },
        options: chart_options
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
