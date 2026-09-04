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
                    class="btn btn-sm btn-light text-nowrap"
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
                    <option value="<?= url('admin/statistics/teams?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'teams' ? 'selected' : null ?>>🛡️ <?= l('admin_teams.menu') ?></option>
                </optgroup>

                <optgroup label="<?= l('admin_statistics.storage') ?>">
                    <option value="<?= url('admin/statistics/database?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'database' ? 'selected="selected"' : null ?>>🗄️ <?= l('admin_statistics.database.menu') ?></option>
                    <option value="<?= url('admin/statistics/local_files?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'local_files' ? 'selected="selected"' : null ?>>📂 <?= l('admin_statistics.local_files.menu') ?></option>
                </optgroup>

                <?php if(in_array(settings()->license->type, ['Extended License','extended'])): ?>
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
                    <option value="<?= url('admin/statistics/broadcasts?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'broadcasts' ? 'selected="selected"' : null ?>>📣 <?= l('admin_statistics.broadcasts.menu') ?></option>
                    <option value="<?= url('admin/statistics/broadcast_subscribers?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'broadcast_subscribers' ? 'selected="selected"' : null ?>>👥 <?= l('admin_statistics.broadcast_subscribers.menu') ?></option>
                    <option value="<?= url('admin/statistics/internal_notifications?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'internal_notifications' ? 'selected' : null ?>>🔔 <?= l('admin_internal_notifications.menu') ?></option>
                    <option value="<?= url('admin/statistics/email_reports?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'email_reports' ? 'selected' : null ?>>✉️ <?= l('admin_statistics.email_reports.menu') ?></option>
                </optgroup>

                <optgroup label="<?= l('admin_statistics.content') ?>">
                    <option value="<?= url('admin/statistics/websites?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'websites' ? 'selected' : null ?>>📄 <?= l('admin_statistics.websites.menu') ?></option>
                    <option value="<?= url('admin/statistics/domains?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'domains' ? 'selected' : null ?>>🌐 <?= l('admin_domains.menu') ?></option>
                    <option value="<?= url('admin/statistics/lightweight_events?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'lightweight_events' ? 'selected' : null ?>>🌱 <?= l('admin_statistics.lightweight_events.menu') ?></option>
                    <option value="<?= url('admin/statistics/sessions_events?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'sessions_events' ? 'selected' : null ?>>👁️ <?= l('admin_statistics.sessions_events.menu') ?></option>
                    <option value="<?= url('admin/statistics/events_children?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'events_children' ? 'selected' : null ?>>🔔 <?= l('admin_statistics.events_children.menu') ?></option>
                    <option value="<?= url('admin/statistics/sessions_replays?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'sessions_replays' ? 'selected' : null ?>>📹 <?= l('admin_statistics.sessions_replays.menu') ?></option>
                    <option value="<?= url('admin/statistics/websites_heatmaps?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'websites_heatmaps' ? 'selected' : null ?>>🔥 <?= l('admin_statistics.websites_heatmaps.menu') ?></option>
                    <option value="<?= url('admin/statistics/websites_goals?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'websites_goals' ? 'selected' : null ?>>🎯 <?= l('admin_statistics.websites_goals.menu') ?></option>
                    <option value="<?= url('admin/statistics/goals_conversions?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'goals_conversions' ? 'selected' : null ?>>🎯 <?= l('admin_statistics.goals_conversions.menu') ?></option>
                    <option value="<?= url('admin/statistics/outbound_clicks?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'outbound_clicks' ? 'selected' : null ?>>🔗 <?= l('admin_statistics.outbound_clicks.menu') ?></option>
                    <option value="<?= url('admin/statistics/annotations?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" <?= $data->type == 'annotations' ? 'selected' : null ?>>🎈 <?= l('admin_annotations.menu') ?></option>
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
                <div class="nav flex-column nav-pills">

                    <div class="mb-2 font-weight-bold small text-uppercase text-muted">
                        <?= l('admin_statistics.general') ?>
                    </div>
                    <a class="nav-link <?= $data->type == 'growth' ? 'active' : null ?>" href="<?= url('admin/statistics/growth?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-seedling mr-1"></i> <?= l('admin_statistics.growth.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'users' ? 'active' : null ?>" href="<?= url('admin/statistics/users?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-users mr-1"></i> <?= l('admin_statistics.users.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'users_map' ? 'active' : null ?>" href="<?= url('admin/statistics/users_map?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-map mr-1"></i> <?= l('admin_statistics.users_map.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'teams' ? 'active' : null ?>" href="<?= url('admin/statistics/teams?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-user-shield mr-1"></i> <?= l('admin_teams.menu') ?></a>

                    <div class="mt-4 mb-2 font-weight-bold small text-uppercase text-muted">
                        <?= l('admin_statistics.storage') ?>
                    </div>
                    <a class="nav-link <?= $data->type == 'database' ? 'active' : null ?>" href="<?= url('admin/statistics/database?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-database mr-1"></i> <?= l('admin_statistics.database.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'local_files' ? 'active' : null ?>" href="<?= url('admin/statistics/local_files?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-copy mr-1"></i> <?= l('admin_statistics.local_files.menu') ?></a>

                    <?php if(in_array(settings()->license->type, ['Extended License','extended'])): ?>
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
                    <a class="nav-link <?= $data->type == 'broadcasts' ? 'active' : null ?>" href="<?= url('admin/statistics/broadcasts?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-mail-bulk mr-2"></i> <?= l('admin_statistics.broadcasts.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'broadcast_subscribers' ? 'active' : null ?>" href="<?= url('admin/statistics/broadcast_subscribers?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-users mr-2"></i> <?= l('admin_statistics.broadcast_subscribers.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'internal_notifications' ? 'active' : null ?>" href="<?= url('admin/statistics/internal_notifications?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-bell mr-1"></i> <?= l('admin_internal_notifications.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'email_reports' ? 'active' : null ?>" href="<?= url('admin/statistics/email_reports?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-envelope mr-1"></i> <?= l('admin_statistics.email_reports.menu') ?></a>

                    <div class="mt-4 mb-2 font-weight-bold small text-uppercase text-muted">
                        <?= l('admin_statistics.content') ?>
                    </div>
                    <a class="nav-link <?= $data->type == 'websites' ? 'active' : null ?>" href="<?= url('admin/statistics/websites?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-pager mr-1"></i> <?= l('admin_statistics.websites.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'domains' ? 'active' : null ?>" href="<?= url('admin/statistics/domains?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-globe mr-1"></i> <?= l('admin_domains.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'lightweight_events' ? 'active' : null ?>" href="<?= url('admin/statistics/lightweight_events?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-seedling mr-1"></i> <?= l('admin_statistics.lightweight_events.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'sessions_events' ? 'active' : null ?>" href="<?= url('admin/statistics/sessions_events?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-eye mr-1"></i> <?= l('admin_statistics.sessions_events.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'events_children' ? 'active' : null ?>" href="<?= url('admin/statistics/events_children?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-bell mr-1"></i> <?= l('admin_statistics.events_children.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'sessions_replays' ? 'active' : null ?>" href="<?= url('admin/statistics/sessions_replays?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-video mr-1"></i> <?= l('admin_statistics.sessions_replays.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'websites_heatmaps' ? 'active' : null ?>" href="<?= url('admin/statistics/websites_heatmaps?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-fire mr-1"></i> <?= l('admin_statistics.websites_heatmaps.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'websites_goals' ? 'active' : null ?>" href="<?= url('admin/statistics/websites_goals?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-bullseye mr-1"></i> <?= l('admin_statistics.websites_goals.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'goals_conversions' ? 'active' : null ?>" href="<?= url('admin/statistics/goals_conversions?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-bullseye mr-1"></i> <?= l('admin_statistics.goals_conversions.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'outbound_clicks' ? 'active' : null ?>" href="<?= url('admin/statistics/outbound_clicks?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-external-link-alt mr-1"></i> <?= l('admin_statistics.outbound_clicks.menu') ?></a>
                    <a class="nav-link <?= $data->type == 'annotations' ? 'active' : null ?>" href="<?= url('admin/statistics/annotations?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>"><i class="fas fa-fw fa-sm fa-comments mr-1"></i> <?= l('admin_annotations.menu') ?></a>

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
