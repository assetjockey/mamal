<?php defined('ALTUMCODE') || die() ?>

<div class="row mb-4">
    <div class="col d-flex align-items-center">
        <h1 class="h3 m-0"><i class="fas fa-fw fa-xs fa-chart-bar text-primary-900 mr-2"></i> <?= sprintf(l('admin_statistics.header')) ?></h1>

        <div class="ml-2">
            <span data-toggle="tooltip" title="<?= l('admin_statistics.subheader') ?>">
                <i class="fas fa-fw fa-info-circle text-muted"></i>
            </span>
        </div>
    </div>

    <?php
    /* Load the proper type view */
    $partial = require THEME_PATH . 'views/admin/statistics/partials/' . $data->type . '.php';
    ?>

    <?php if($partial->has_datepicker ?? true): ?>
        <div class="col-auto d-flex align-items-center">
            <div
                    id="daterangepicker"
                    role="button"
                    class="btn btn-sm btn-light"
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
    <?php endif ?>
</div>

<?= \Altum\Alerts::output_alerts() ?>

<div class="row">
    <div class="mb-3 mb-xl-0 col-12 col-xl-4 order-xl-1">
        <div class="d-xl-none">
            <select class="custom-select" onchange="if(this.value) window.location.href=this.value">

                <optgroup label="<?= l('admin_statistics.general') ?>">
                    <option value="<?= url('admin/statistics/growth?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'growth' ? 'selected' : null ?>>🌱 <?= l('admin_statistics.growth.menu') ?></option>
                    <option value="<?= url('admin/statistics/users?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'users' ? 'selected' : null ?>>👥 <?= l('admin_statistics.users.menu') ?></option>
                    <option value="<?= url('admin/statistics/users_map?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'users_map' ? 'selected' : null ?>>🗺️ <?= l('admin_statistics.users_map.menu') ?></option>
                    <?php if(\Altum\Plugin::is_active('teams')): ?>
                        <option value="<?= url('admin/statistics/teams?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'teams' ? 'selected' : null ?>>🛡️ <?= l('admin_teams.menu') ?></option>
                        <option value="<?= url('admin/statistics/teams_members?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'teams_members' ? 'selected' : null ?>>🏷️ <?= l('admin_statistics.teams_members.menu') ?></option>
                    <?php endif ?>
                </optgroup>

                <optgroup label="<?= l('admin_statistics.storage') ?>">
                    <option value="<?= url('admin/statistics/database?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'database' ? 'selected="selected"' : null ?>>🗄️ <?= l('admin_statistics.database.menu') ?></option>
                    <option value="<?= url('admin/statistics/local_files?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'local_files' ? 'selected="selected"' : null ?>>📂 <?= l('admin_statistics.local_files.menu') ?></option>
                </optgroup>

                <?php if(in_array(settings()->license->type, ['SPECIAL', 'Extended License', 'extended'])): ?>
                    <optgroup label="<?= l('admin_statistics.monetization') ?>">
                        <option value="<?= url('admin/statistics/payments?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'payments' ? 'selected' : null ?>>💳 <?= l('admin_statistics.payments.menu') ?></option>
                        <option value="<?= url('admin/statistics/redeemed_codes?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'redeemed_codes' ? 'selected' : null ?>>🏷️ <?= l('admin_statistics.redeemed_codes.menu') ?></option>
                        <?php if(\Altum\Plugin::is_active('affiliate')): ?>
                            <option value="<?= url('admin/statistics/affiliates_commissions?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'affiliates_commissions' ? 'selected' : null ?>>👛 <?= l('admin_statistics.affiliates_commissions.menu') ?></option>
                            <option value="<?= url('admin/statistics/affiliates_withdrawals?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'affiliates_withdrawals' ? 'selected' : null ?>>👛 <?= l('admin_statistics.affiliates_withdrawals.menu') ?></option>
                        <?php endif ?>
                    </optgroup>
                <?php endif ?>

                <optgroup label="<?= l('admin_statistics.engagement') ?>">
                    <option value="<?= url('admin/statistics/broadcasts?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'broadcasts' ? 'selected' : null ?>>📣 <?= l('admin_statistics.broadcasts.menu') ?></option>
                    <option value="<?= url('admin/statistics/internal_notifications?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'internal_notifications' ? 'selected' : null ?>>🔔 <?= l('admin_internal_notifications.menu') ?></option>
                    <option value="<?= url('admin/statistics/notification_handlers?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'notification_handlers' ? 'selected' : null ?>>🔔 <?= l('admin_notification_handlers.menu') ?></option>
                </optgroup>

                <optgroup label="<?= l('admin_statistics.content') ?>">
                    <option value="<?= url('admin/statistics/transfers?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'transfers' ? 'selected' : null ?>>📤 <?= l('admin_transfers.menu') ?></option>
                    <option value="<?= url('admin/statistics/transfer_requests?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'transfer_requests' ? 'selected' : null ?>>📨 <?= l('admin_transfer_requests.menu') ?></option>
                    <option value="<?= url('admin/statistics/files?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'files' ? 'selected' : null ?>>📄 <?= l('admin_files.menu') ?></option>
                    <option value="<?= url('admin/statistics/statistics?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'statistics' ? 'selected' : null ?>>📊 <?= l('admin_statistics.statistics.menu') ?></option>
                    <option value="<?= url('admin/statistics/downloads?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'downloads' ? 'selected' : null ?>>⬇️ <?= l('admin_statistics.downloads.menu') ?></option>
                    <option value="<?= url('admin/statistics/requests_submissions?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'requests_submissions' ? 'selected' : null ?>>⬆️ <?= l('admin_statistics.requests_submissions.menu') ?></option>
                    <option value="<?= url('admin/statistics/projects?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'projects' ? 'selected' : null ?>>📁 <?= l('admin_projects.menu') ?></option>
                    <option value="<?= url('admin/statistics/pixels?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'pixels' ? 'selected' : null ?>>🎯 <?= l('admin_pixels.menu') ?></option>
                    <option value="<?= url('admin/statistics/domains?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'domains' ? 'selected' : null ?>>🌐 <?= l('admin_domains.menu') ?></option>
                </optgroup>

                <?php if(
                        \Altum\Plugin::is_active('push-notifications')
                        || \Altum\Plugin::is_active('email-shield')
                        || \Altum\Plugin::is_active('image-optimizer')
                ): ?>
                    <optgroup label="<?= l('admin_statistics.plugins') ?>">
                        <?php if(\Altum\Plugin::is_active('push-notifications')): ?>
                            <option value="<?= url('admin/statistics/push_notifications?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'push_notifications' ? 'selected' : null ?>>⚡ <?= l('admin_push_notifications.menu') ?></option>
                            <option value="<?= url('admin/statistics/push_subscribers?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'push_subscribers' ? 'selected' : null ?>>✅ <?= l('admin_push_subscribers.menu') ?></option>
                        <?php endif ?>
                        <?php if(\Altum\Plugin::is_active('email-shield')): ?>
                            <option value="<?= url('admin/statistics/email_shield?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'email_shield' ? 'selected="selected"' : null ?>>🛡️️ <?= l('admin_statistics.email_shield.menu') ?></option>
                        <?php endif ?>
                        <?php if(\Altum\Plugin::is_active('image-optimizer')): ?>
                            <option value="<?= url('admin/statistics/image_optimizer?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'image_optimizer' ? 'selected="selected"' : null ?>>🖼️ <?= l('admin_statistics.image_optimizer.menu') ?></option>
                        <?php endif ?>
                    </optgroup>
                <?php endif ?>

            </select>
        </div>

        <div class="card d-none d-xl-flex">
            <div class="card-body">
                <div class="nav flex-column nav-pills" role="tablist" aria-orientation="vertical">

                    <div class="mb-2 font-weight-bold small text-uppercase text-muted">
                        <?= l('admin_statistics.general') ?>
                    </div>
                    <a class="nav-link <?= $data->type == 'growth' ? 'active' : null ?>" href="<?= url('admin/statistics/growth?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-seedling mr-1"></i> <?= l('admin_statistics.growth.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'users' ? 'active' : null ?>" href="<?= url('admin/statistics/users?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-users mr-1"></i> <?= l('admin_statistics.users.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'users_map' ? 'active' : null ?>" href="<?= url('admin/statistics/users_map?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-map mr-1"></i> <?= l('admin_statistics.users_map.menu') ?></a>
                    <?php if(\Altum\Plugin::is_active('teams')): ?>
                        <a class="nav-link <?= $data->type == 'teams' ? 'active' : null ?>" href="<?= url('admin/statistics/teams?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-user-shield mr-1"></i> <?= l('admin_teams.menu') ?></a>
                        <a class="nav-link <?= $data->type == 'teams_members' ? 'active' : null ?>" href="<?= url('admin/statistics/teams_members?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-user-tag mr-1"></i> <?= l('admin_statistics.teams_members.menu') ?></a>
                    <?php endif ?>

                    <div class="mt-4 mb-2 font-weight-bold small text-uppercase text-muted">
                        <?= l('admin_statistics.storage') ?>
                    </div>
                    <a class="nav-link <?= $data->type == 'database' ? 'active' : null ?>" href="<?= url('admin/statistics/database?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-database mr-1"></i> <?= l('admin_statistics.database.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'local_files' ? 'active' : null ?>" href="<?= url('admin/statistics/local_files?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-copy mr-1"></i> <?= l('admin_statistics.local_files.menu') ?></a>

                    <?php if(in_array(settings()->license->type, ['SPECIAL', 'Extended License', 'extended'])): ?>
                        <div class="mt-4 mb-2 font-weight-bold small text-uppercase text-muted">
                            <?= l('admin_statistics.monetization') ?>
                        </div>
                        <a class="nav-link <?= $data->type == 'payments' ? 'active' : null ?>" href="<?= url('admin/statistics/payments?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-credit-card mr-1"></i> <?= l('admin_statistics.payments.menu') ?></a>
                        <a class="nav-link <?= $data->type == 'redeemed_codes' ? 'active' : null ?>" href="<?= url('admin/statistics/redeemed_codes?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-tags mr-1"></i> <?= l('admin_statistics.redeemed_codes.menu') ?></a>
                        <?php if(\Altum\Plugin::is_active('affiliate')): ?>
                            <a class="nav-link <?= $data->type == 'affiliates_commissions' ? 'active' : null ?>" href="<?= url('admin/statistics/affiliates_commissions?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-wallet mr-1"></i> <?= l('admin_statistics.affiliates_commissions.menu') ?></a>
                            <a class="nav-link <?= $data->type == 'affiliates_withdrawals' ? 'active' : null ?>" href="<?= url('admin/statistics/affiliates_withdrawals?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-wallet mr-1"></i> <?= l('admin_statistics.affiliates_withdrawals.menu') ?></a>
                        <?php endif ?>
                    <?php endif ?>

                    <div class="mt-4 mb-2 font-weight-bold small text-uppercase text-muted">
                        <?= l('admin_statistics.engagement') ?>
                    </div>
                    <a class="nav-link <?= $data->type == 'broadcasts' ? 'active' : null ?>" href="<?= url('admin/statistics/broadcasts?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-mail-bulk mr-1"></i> <?= l('admin_statistics.broadcasts.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'internal_notifications' ? 'active' : null ?>" href="<?= url('admin/statistics/internal_notifications?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-bell mr-1"></i> <?= l('admin_internal_notifications.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'notification_handlers' ? 'active' : null ?>" href="<?= url('admin/statistics/notification_handlers?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-bell mr-1"></i> <?= l('admin_notification_handlers.menu') ?></a>

                    <div class="mt-4 mb-2 font-weight-bold small text-uppercase text-muted">
                        <?= l('admin_statistics.content') ?>
                    </div>
                    <a class="nav-link <?= $data->type == 'transfers' ? 'active' : null ?>" href="<?= url('admin/statistics/transfers?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-paper-plane mr-1"></i> <?= l('admin_transfers.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'transfer_requests' ? 'active' : null ?>" href="<?= url('admin/statistics/transfer_requests?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-inbox mr-1"></i> <?= l('admin_transfer_requests.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'files' ? 'active' : null ?>" href="<?= url('admin/statistics/files?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-copy mr-1"></i> <?= l('admin_files.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'statistics' ? 'active' : null ?>" href="<?= url('admin/statistics/statistics?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-chart-bar mr-1"></i> <?= l('admin_statistics.statistics.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'downloads' ? 'active' : null ?>" href="<?= url('admin/statistics/downloads?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-download mr-1"></i> <?= l('admin_statistics.downloads.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'requests_submissions' ? 'active' : null ?>" href="<?= url('admin/statistics/requests_submissions?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-upload mr-1"></i> <?= l('admin_statistics.requests_submissions.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'projects' ? 'active' : null ?>" href="<?= url('admin/statistics/projects?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-project-diagram mr-1"></i> <?= l('admin_projects.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'pixels' ? 'active' : null ?>" href="<?= url('admin/statistics/pixels?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-adjust mr-1"></i> <?= l('admin_pixels.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'domains' ? 'active' : null ?>" href="<?= url('admin/statistics/domains?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-globe mr-1"></i> <?= l('admin_domains.menu') ?></a>

                    <?php if(
                        \Altum\Plugin::is_active('push-notifications')
                        || \Altum\Plugin::is_active('email-shield')
                        || \Altum\Plugin::is_active('image-optimizer')
                    ): ?>
                    <div class="mt-4 mb-2 font-weight-bold small text-uppercase text-muted">
                        <?= l('admin_statistics.plugins') ?>
                    </div>
                    <?php endif ?>

                    <?php if(\Altum\Plugin::is_active('push-notifications')): ?>
                        <a class="nav-link <?= $data->type == 'push_notifications' ? 'active' : null ?>" href="<?= url('admin/statistics/push_notifications?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-bolt-lightning mr-1"></i> <?= l('admin_push_notifications.menu') ?></a>
                        <a class="nav-link <?= $data->type == 'push_subscribers' ? 'active' : null ?>" href="<?= url('admin/statistics/push_subscribers?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-user-check mr-1"></i> <?= l('admin_push_subscribers.menu') ?></a>
                    <?php endif ?>
                    <?php if(\Altum\Plugin::is_active('email-shield')): ?>
                        <a class="nav-link <?= $data->type == 'email_shield' ? 'active' : null ?>" href="<?= url('admin/statistics/email_shield?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-shield-alt mr-1"></i> <?= l('admin_statistics.email_shield.menu') ?></a>
                    <?php endif ?>
                    <?php if(\Altum\Plugin::is_active('image-optimizer')): ?>
                        <a class="nav-link <?= $data->type == 'image_optimizer' ? 'active' : null ?>" href="<?= url('admin/statistics/image_optimizer?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-image mr-1"></i> <?= l('admin_statistics.image_optimizer.menu') ?></a>
                    <?php endif ?>

                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8 order-xl-0">

        <?= $partial->html ?? null ?>

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
            <?= json_encode(l('global.date.all_time')) ?>: [moment('2015-01-01'), moment()]
        },
        alwaysShowCalendars: true,
        linkedCalendars: false,
        singleCalendar: true,
        locale: <?= json_encode(require APP_PATH . 'includes/daterangepicker_translations.php') ?>,
    }, (start, end, label) => {

        /* Redirect */
        redirect(`<?= url('admin/statistics/' . $data->type) ?>?start_date=${start.format('YYYY-MM-DD')}&end_date=${end.format('YYYY-MM-DD')}`, true);

    });

    let css = window.getComputedStyle(document.body)
</script>

<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php ob_start() ?>
<?= $partial->javascript ?? null ?>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
