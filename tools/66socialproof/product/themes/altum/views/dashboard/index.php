<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <div class="d-flex justify-content-between">
        <h1 class="h4 text-truncate"><i class="fas fa-fw fa-xs fa-table-cells mr-1"></i> <?= l('dashboard.header') ?></h1>
    </div>

    <div class="mt-3">
        <div class="row">
            <!-- Campaigns -->
            <div class="col-12 col-lg-4 mb-4 position-relative">
                <div id="total_campaigns_wrapper" class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('campaigns') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-pager text-primary-600"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body text-truncate">
                        <div id="total_campaigns" class="text-truncate">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </div>
                        <div id="total_campaigns_progress" class="progress" style="height: .25rem;">
                            <div class="progress-bar <?= $this->user->plan_settings->campaigns_limit == -1 ? 'bg-success' : null ?>" role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            <div class="col-12 col-lg-4 mb-4 position-relative">
                <div id="total_notifications_wrapper" class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <i class="fas fa-fw fa-sm fa-window-maximize text-primary-600"></i>
                        </div>
                    </div>
                    <div class="card-body text-truncate">
                        <div id="total_notifications" class="text-truncate">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </div>
                        <div id="total_notifications_progress" class="progress" style="height: .25rem;">
                            <div class="progress-bar <?= $this->user->plan_settings->notifications_limit == -1 ? 'bg-success' : null ?>" role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Impressions (this month) -->
            <div class="col-12 col-lg-4 mb-4 position-relative">
                <div id="total_notifications_impressions_wrapper" class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('global.date.this_month') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('statistics') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-eye text-primary-600"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body text-truncate">
                        <div id="total_notifications_impressions" class="text-truncate">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </div>
                        <div id="total_notifications_impressions_progress" class="progress" style="height: .25rem;">
                            <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-2">
        <div class="card-body">
            <div class="chart-container d-none" id="notifications_chart_container">
                <canvas id="notifications_chart"></canvas>
            </div>

            <div id="notifications_chart_no_data" class="d-none">
                <?= include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
            </div>

            <div id="notifications_chart_loading" class="chart-container d-flex align-items-center justify-content-center">
                <span class="spinner-border spinner-border-lg" role="status"></span>
            </div>

            <?php if(settings()->main->chart_cache): ?>
                <small class="text-muted d-none" id="notifications_chart_help">
                    <span data-toggle="tooltip" title="<?= sprintf(l('global.chart_help'), settings()->main->chart_cache ?? 12, settings()->main->chart_days ?? 30) ?>"><i class="fas fa-fw fa-sm fa-info-circle mr-1"></i></span>
                    <span class="d-lg-none"><?= sprintf(l('global.chart_help'), settings()->main->chart_cache ?? 12, settings()->main->chart_days ?? 30) ?></span>
                </small>
            <?php endif ?>
        </div>
    </div>

    <?= \Altum\Alerts::output_alerts() ?>

    <div class="row mb-4 mt-5">
        <div class="col-12 col-lg d-flex align-items-end mb-3 mb-lg-0 text-truncate">

            <h2 class="h5 m-0 text-truncate"><?= l('dashboard.campaigns_header') ?></h2>
        </div>

        <div class="col-12 col-lg-auto d-flex flex-wrap gap-3 d-print-none">
            <div>
                <?php if($this->user->plan_settings->campaigns_limit != -1 && $data->total_campaigns >= $this->user->plan_settings->campaigns_limit): ?>
                    <button type="button" class="btn btn-primary disabled" <?= get_plan_feature_limit_reached_info() ?>>
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('campaigns.create') ?>
                    </button>
                <?php else: ?>
                    <button type="button" data-toggle="modal" data-target="#campaign_create_modal" class="btn btn-primary" data-tooltip data-toggle="tooltip" data-html="true" title="<?= get_plan_feature_limit_info($data->total_campaigns, $this->user->plan_settings->campaigns_limit, isset($data->filters) ? !$data->filters->has_applied_filters : true) ?>"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('campaigns.create') ?></button>
                <?php endif ?>
            </div>

            <div>
                <a href="<?= url('campaigns-import') ?>" class="btn btn-outline-primary" data-toggle="tooltip" data-html="true" title="<?= l('campaigns_import.menu') ?>">
                    <i class="fas fa-fw fa-upload fa-sm"></i>
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($data->campaigns)): ?>
        <div class="table-responsive table-custom-container mt-3">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th><?= l('campaigns.campaign') ?></th>
                    <th></th>
                    <th><?= l('global.status') ?></th>
                    <th class="d-none d-md-table-cell"></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                <?php foreach($data->campaigns as $row): ?>
                    <?php $row->branding = json_decode($row->branding ?? ''); ?>
                    <tr>
                        <td class="text-nowrap">
                            <a href="<?= url('campaign/' . $row->campaign_id) ?>"><?= $row->name ?></a>

                            <div class="small d-flex align-items-center text-muted">
                                <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($row->domain) ?>" class="img-fluid icon-favicon-small mr-1" />

                                <?= $row->domain ?>

                                <a href="<?= 'https://' . $row->domain ?>" target="_blank" rel="noreferrer"><i class="fas fa-fw fa-xs fa-external-link-alt text-muted ml-1"></i></a>
                            </div>
                        </td>

                        <td class="text-nowrap">
                            <a href="<?= url('campaign/' . $row->campaign_id) ?>" class="badge badge-light">
                                <i class="fas fa-fw fa-sm fa-window-maximize mr-1"></i> <?= sprintf(l('campaigns.x_notifications'), nr($row->notifications_count)) ?>
                            </a>
                        </td>

                        <td class="text-nowrap">
                            <div class="d-flex">
                                <div class="custom-control custom-switch" data-toggle="tooltip" title="<?= l('campaigns.table.is_enabled_tooltip') ?>">
                                    <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="campaign_is_enabled_<?= $row->campaign_id ?>"
                                            data-row-id="<?= $row->campaign_id ?>"
                                            onchange="ajax_call_helper(event, 'campaigns-ajax', 'is_enabled_toggle')"
                                            <?= $row->is_enabled ? 'checked="checked"' : null ?>
                                    >
                                    <label class="custom-control-label" for="campaign_is_enabled_<?= $row->campaign_id ?>"></label>
                                </div>
                            </div>
                        </td>

                        <td class="text-nowrap d-none d-md-table-cell">
                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                <i class="fas fa-fw fa-calendar text-muted"></i>
                            </span>

                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                <i class="fas fa-fw fa-history text-muted"></i>
                            </span>
                        </td>

                        <td>
                            <div class="d-flex justify-content-end">
                                <div class="dropdown">
                                    <button type="button" class="btn btn-link text-secondary dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
                                        <i class="fas fa-fw fa-ellipsis-v"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="<?= url('campaign/' . $row->campaign_id) ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-pager mr-2"></i> <?= l('global.view') ?></a>
                                        <a href="<?= url('campaign/' . $row->campaign_id . '/statistics') ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-chart-bar mr-2"></i> <?= l('statistics.link') ?></a>
                                        <a href="#" data-toggle="modal" data-target="#campaign_update_modal" data-campaign-id="<?= $row->campaign_id ?>" data-name="<?= $row->name ?>" data-domain="<?= $row->domain ?>" data-domain-id="<?= $row->domain_id ?>" data-email-reports="<?= $row->email_reports ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>

                                        <a
                                                href="#"
                                                data-toggle="modal"
                                                data-target="#campaign_pixel_key_modal"
                                                data-pixel-key="<?= $row->pixel_key ?>"
                                                data-campaign-id="<?= $row->campaign_id ?>"
                                                data-base-url="<?= $row->domain_id ? $data->domains[$row->domain_id]->scheme . $data->domains[$row->domain_id]->host . '/' : SITE_URL ?>"
                                                class="dropdown-item"
                                        ><i class="fas fa-fw fa-sm fa-code mr-2"></i> <?= l('campaign.pixel_key') ?></a>

                                        <div <?= $this->user->plan_settings->custom_branding ? null : get_plan_feature_disabled_info() ?>>
                                            <a
                                                    href="#"
                                                    <?php if($this->user->plan_settings->custom_branding): ?>
                                                        data-toggle="modal"
                                                        data-target="#campaign_custom_branding_modal"
                                                        data-campaign-id="<?= $row->campaign_id ?>"
                                                        data-branding-name="<?= e($row->branding->name ?? '') ?>"
                                                        data-branding-url="<?= $row->branding->url ?? '' ?>"
                                                        class="dropdown-item"
                                                    <?php else: ?>
                                                        class="dropdown-item container-disabled"
                                                    <?php endif ?>
                                            >
                                                <i class="fas fa-fw fa-sm fa-random mr-2"></i> <?= l('campaign.custom_branding') ?>
                                            </a>
                                        </div>

                                        <a href="#" data-toggle="modal" data-target="#campaign_duplicate_modal" data-campaign-id="<?= $row->campaign_id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-clone mr-2"></i> <?= l('global.duplicate') ?></a>

                                        <a href="#" data-toggle="modal" data-target="#campaign_delete_modal" data-campaign-id="<?= $row->campaign_id ?>" data-resource-name="<?= $row->name ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>

                <tr>
                    <td class="py-3" colspan="5">
                        <a href="<?= url('campaigns') ?>" class="text-muted small text-decoration-none">
                            <i class="fas fa-angle-double-right fa-sm fa-fw mr-1"></i> <?= l('global.view_all') ?>
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

    <?php else: ?>

        <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                'filters_get' => $data->filters->get ?? [],
                'name' => 'campaigns',
                'has_secondary_text' => true,
        ]); ?>

    <?php endif ?>


    <?php if (!empty($data->notifications)): ?>
        <div class="mt-5 d-flex justify-content-between">
            <h2 class="h5"><?= l('dashboard.notifications_header') ?></h2>
        </div>

        <div class="table-responsive table-custom-container mt-3">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th><?= l('notifications.notification') ?></th>
                    <th><?= l('notification.statistics.link') ?></th>
                    <th class="d-none d-md-table-cell"><?= l('notifications.table.display_trigger') ?></th>
                    <th class="d-none d-md-table-cell"><?= l('notifications.table.display_duration') ?></th>
                    <th><?= l('global.status') ?></th>
                    <th class="d-none d-md-table-cell"></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($data->notifications as $row): ?>
                    <?php $row->settings = json_decode($row->settings) ?>

                    <tr>
                        <td class="text-nowrap">
                            <div class="d-flex">
                                <div class="notification-avatar rounded-circle mr-3" style="background-color: <?= $data->notifications_config[$row->type]['notification_background_color'] ?>; color: <?= $data->notifications_config[$row->type]['notification_color'] ?>">
                                    <i class="<?= l('notification.' . mb_strtolower($row->type) . '.icon') ?>"></i>
                                </div>

                                <div class="d-flex flex-column">
                                    <a href="<?= url('notification/' . $row->notification_id) ?>"><?= $row->name ?></a>

                                    <div class="text-muted">
                                        <?= l('notification.' . mb_strtolower($row->type) . '.name') ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="text-nowrap">
                            <?php ob_start() ?>
                            <div class='d-flex flex-column text-left'>
                                <div class='d-flex flex-column my-1'>
                                    <div><?= l('notification.statistics.impressions_chart') ?></div>
                                    <strong>
                                        <?= nr($row->impressions) ?>
                                    </strong>
                                </div>

                                <div class='d-flex flex-column my-1'>
                                    <div><?= l('notification.statistics.hovers_chart') ?></div>
                                    <strong>
                                        <?= nr($row->hovers) . '/' . nr($row->impressions) ?>

                                        <span class='text-muted'>
                                            <?= ' (' . nr(get_percentage_between_two_numbers($row->hovers, $row->impressions)) . '%' . ')' ?>
                                        </span>
                                    </strong>
                                </div>

                                <div class='d-flex flex-column my-1'>
                                    <div><?= l('notification.statistics.clicks_chart') ?></div>
                                    <strong>
                                        <?= nr($row->clicks) . '/' . nr($row->impressions) ?>

                                        <span class='text-muted'>
                                            <?= ' (' . nr(get_percentage_between_two_numbers($row->clicks, $row->impressions)) . '%' . ')' ?>
                                        </span>
                                    </strong>
                                </div>

                                <?php if(in_array($row->type, ['collector_modal', 'collector_two_modal', 'countdown_collector', 'email_collector', 'request_collector', 'text_feedback', 'score_feedback', 'emoji_feedback'])): ?>
                                    <div class='d-flex flex-column my-1'>
                                        <div><?= l('notification.statistics.form_submissions_chart') ?></div>
                                        <strong>
                                            <?= nr($row->form_submissions) . '/' . nr($row->impressions) ?>

                                            <span class='text-muted'>
                                                <?= ' (' . nr(get_percentage_between_two_numbers($row->form_submissions, $row->impressions)) . '%' . ')' ?>
                                            </span>
                                        </strong>
                                    </div>
                                <?php endif ?>
                            </div>
                            <?php $tooltip = ob_get_clean(); ?>

                            <a href="<?= url('notification/' . $row->notification_id . '/statistics') ?>" class="badge badge-primary" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                <i class="fas fa-fw fa-sm fa-eye mr-1"></i> <?= nr($row->impressions) ?>
                            </a>
                        </td>

                        <td class="text-nowrap d-none d-md-table-cell">
                            <div class="text-muted d-flex flex-column">

                                <?php
                                switch($row->settings->display_trigger) {
                                    case 'delay':
                                    case 'time_on_site':
                                    case 'inactivity':
                                    case 'target_visible':

                                        echo '<span>' . $row->settings->display_trigger_value . ' <small>' . l('global.date.seconds') . '</small></span>';
                                        echo '<small>' . l('notification.settings.display_trigger_' . $row->settings->display_trigger) . '</small>';

                                        break;

                                    case 'scroll':

                                        echo $row->settings->display_trigger_value . '%';
                                        echo '<small>' . l('notification.settings.display_trigger_' . $row->settings->display_trigger)  . '</small>';

                                        break;

                                    case 'exit_intent':

                                        echo l('notification.settings.display_trigger_' . $row->settings->display_trigger);

                                        break;

                                    case 'pageviews':

                                        echo nr($row->settings->display_trigger_value) . ' ';
                                        echo l('notification.settings.display_trigger_' . $row->settings->display_trigger);

                                        break;

                                    case 'click':
                                    case 'hover':

                                        echo '<span>' . $row->settings->display_trigger_value . '</span>';
                                        echo '<small>' . l('notification.settings.display_trigger_' . $row->settings->display_trigger) . '</small>';

                                        break;
                                }
                                ?>

                            </div>
                        </td>

                        <td class="text-nowrap d-none d-md-table-cell">
                            <span><?= $row->settings->display_duration == -1 ? l('notifications.table.display_duration_unlimited') : $row->settings->display_duration . ' <small>' . l('global.date.seconds') . '</small>' ?></span>
                        </td>

                        <td class="text-nowrap">
                            <div class="d-flex">
                                <div class="custom-control custom-switch" data-toggle="tooltip" title="<?= l('notifications.table.is_enabled_tooltip') ?>">
                                    <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="notification_is_enabled_<?= $row->notification_id ?>"
                                            data-row-id="<?= $row->notification_id ?>"
                                            onchange="ajax_call_helper(event, 'notifications-ajax', 'is_enabled_toggle')"
                                            <?= $row->is_enabled ? 'checked="checked"' : null ?>
                                    >
                                    <label class="custom-control-label" for="notification_is_enabled_<?= $row->notification_id ?>"></label>
                                </div>
                            </div>
                        </td>

                        <td class="text-nowrap d-none d-md-table-cell">
                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                <i class="fas fa-fw fa-calendar text-muted"></i>
                            </span>

                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                <i class="fas fa-fw fa-history text-muted"></i>
                            </span>
                        </td>

                        <td>
                            <div class="d-flex justify-content-end">
                                <div class="dropdown">
                                    <button type="button" class="btn btn-link text-secondary dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
                                        <i class="fas fa-fw fa-ellipsis-v"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="<?= url('notification/' . $row->notification_id) ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
                                        <a href="<?= url('notification/' . $row->notification_id . '/statistics') ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-chart-bar mr-2"></i> <?= l('notification.statistics.link') ?></a>
                                        <a href="#" data-toggle="modal" data-target="#notification_duplicate_modal" data-notification-id="<?= $row->notification_id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-clone mr-2"></i> <?= l('global.duplicate') ?></a>
                                        <a href="#" data-toggle="modal" data-target="#notification_reset_modal" data-notification-id="<?= $row->notification_id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-redo mr-2"></i> <?= l('global.reset') ?></a>
                                        <a href="#" data-toggle="modal" data-target="#notification_delete_modal" data-notification-id="<?= $row->notification_id ?>" data-resource-name="<?= $row->name ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>

<?php require THEME_PATH . 'views/partials/js_chart_defaults.php' ?>

<?php ob_start() ?>
<script>
    'use strict';

    (async function fetch_statistics() {
        /* Send request to server */
        let response = await fetch(`${url}dashboard/get_stats_ajax`, {
            method: 'get',
        });

        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            /* :)  */
        }

        if(!response.ok) {
            /* :)  */
        }

        if(data.status == 'error') {
            /* :)  */
        } else if(data.status == 'success') {

            /* update total_campaigns */
            const total_campaigns_element = document.querySelector('#total_campaigns');
            if (total_campaigns_element) {
                let total_campaigns_translation = <?= json_encode(l('dashboard.total_campaigns')) ?>;
                let total_campaigns = data.details.total_campaigns ? data.details.total_campaigns : 0;
                let total_campaigns_html = total_campaigns_translation.replace('%s', `<span class='h6' id='total_campaigns'>${nr(total_campaigns)}</span>`);

                let campaigns_plan_limit = <?= (int) $this->user->plan_settings->campaigns_limit ?>;

                /* calculate progress */
                let progress = 0;
                if (campaigns_plan_limit > 0) {
                    progress = Math.min((total_campaigns / campaigns_plan_limit) * 100, 100);
                }

                document.querySelector('#total_campaigns_progress .progress-bar').style.width = `${progress}%`;

                document.querySelector('#total_campaigns_wrapper').setAttribute('title', get_plan_feature_limit_info(total_campaigns, campaigns_plan_limit, true, <?= json_encode(l('global.info_message.plan_feature_limit_info')) ?>, <?= json_encode(l('global.unlimited')) ?>));
                total_campaigns_element.innerHTML = total_campaigns_html;
            }

            /* update total_notifications */
            const total_notifications_element = document.querySelector('#total_notifications');
            if (total_notifications_element) {
                let total_notifications_translation = <?= json_encode(l('dashboard.total_notifications')) ?>;
                let total_notifications = data.details.total_notifications ? data.details.total_notifications : 0;
                let total_notifications_html = total_notifications_translation.replace('%s', `<span class='h6' id='total_notifications'>${nr(total_notifications)}</span>`);

                let notifications_plan_limit = <?= (int) $this->user->plan_settings->notifications_limit ?>;

                /* calculate progress */
                let progress = 0;
                if (notifications_plan_limit > 0) {
                    progress = Math.min((total_notifications / notifications_plan_limit) * 100, 100);
                }

                document.querySelector('#total_notifications_progress .progress-bar').style.width = `${progress}%`;

                document.querySelector('#total_notifications_wrapper').setAttribute('title', get_plan_feature_limit_info(total_notifications, notifications_plan_limit, true, <?= json_encode(l('global.info_message.plan_feature_limit_info')) ?>, <?= json_encode(l('global.unlimited')) ?>));
                total_notifications_element.innerHTML = total_notifications_html;
            }

            /* update total_notifications_impressions */
            const total_notifications_impressions_element = document.querySelector('#total_notifications_impressions');
            if (total_notifications_impressions_element) {
                let total_notifications_impressions_translation = <?= json_encode(l('dashboard.total_notifications_impressions')) ?>;
                let total_notifications_impressions = data.details.current_month_notifications_impressions ? data.details.current_month_notifications_impressions : 0;
                let notifications_impressions_limit = <?= (int) $this->user->plan_settings->notifications_impressions_limit ?>;

                let total_notifications_impressions_html = total_notifications_impressions_translation
                    .replace('%1$s', `<span class='h6' id='total_notifications_impressions'>${nr(total_notifications_impressions)}`)
                    .replace('%2$s', notifications_impressions_limit != -1 ? nr(notifications_impressions_limit) : '∞' + '</span>');

                /* calculate progress and class */
                let progress = 0;
                let progress_class = 'bg-success';
                if (notifications_impressions_limit > 0) {
                    progress = Math.min((total_notifications_impressions / notifications_impressions_limit) * 100, 100);
                    if (progress > 85) {
                        progress_class = 'bg-danger';
                    } else if (progress > 60) {
                        progress_class = 'bg-warning';
                    }
                } else if (notifications_impressions_limit == 0) {
                    progress = 100;
                }

                let progress_bar = document.querySelector('#total_notifications_impressions_progress .progress-bar');
                progress_bar.style.width = `${progress}%`;
                progress_bar.className = `progress-bar ${progress_class}`;

                total_notifications_impressions_element.innerHTML = total_notifications_impressions_html;
            }

            tooltips_initiate();

            /* Remove loading */
            document.querySelector('#notifications_chart_loading').classList.add('d-none');
            document.querySelector('#notifications_chart_loading').classList.remove('d-flex');

            /* Chart */
            if(data.details.notifications_chart.is_empty) {
                document.querySelector('#notifications_chart_no_data').classList.remove('d-none');
            } else {
                /* Display chart data */
                document.querySelector('#notifications_chart_container').classList.remove('d-none');
                document.querySelector('#notifications_chart_help') && document.querySelector('#notifications_chart_help').classList.remove('d-none');

                let css = window.getComputedStyle(document.body);
                let impressions_color = css.getPropertyValue('--primary');
                let impressions_color_gradient = null;

                /* Chart */
                let notifications_chart = document.getElementById('notifications_chart').getContext('2d');

                /* Colors */
                impressions_color_gradient = notifications_chart.createLinearGradient(0, 0, 0, 250);
                impressions_color_gradient.addColorStop(0, set_hex_opacity(impressions_color, 0.6));
                impressions_color_gradient.addColorStop(1, set_hex_opacity(impressions_color, 0.1));

                new Chart(notifications_chart, {
                    type: 'line',
                    data: {
                        labels: JSON.parse(data.details.notifications_chart.labels ?? '[]'),
                        datasets: [
                            {
                                label: <?= json_encode(l('statistics.impressions_chart')) ?>,
                                data: JSON.parse(data.details.notifications_chart.impressions ?? '[]'),
                                backgroundColor: impressions_color_gradient,
                                borderColor: impressions_color,
                                fill: true
                            }
                        ]
                    },
                    options: chart_options
                });
            }
        }
    })();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/campaign/campaign_create_modal.php', ['domains' => $data->domains, 'user' => $this->user, 'notification_handlers' => $data->notification_handlers]), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/campaign/campaign_pixel_key_modal.php', ['domains' => $data->domains]), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/campaign/campaign_update_modal.php', ['domains' => $data->domains, 'user' => $this->user, 'notification_handlers' => $data->notification_handlers]), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/campaign/campaign_custom_branding_modal.php'), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/duplicate_modal.php', ['modal_id' => 'campaign_duplicate_modal', 'resource_id' => 'campaign_id', 'path' => 'campaign/duplicate']), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/duplicate_modal.php', ['modal_id' => 'notification_duplicate_modal', 'resource_id' => 'notification_id', 'path' => 'notification/duplicate']), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
        'name' => 'campaign',
        'resource_id' => 'campaign_id',
        'has_dynamic_resource_name' => true,
        'path' => 'campaign/delete'
]), 'modals'); ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
        'name' => 'notification',
        'resource_id' => 'notification_id',
        'has_dynamic_resource_name' => true,
        'path' => 'notification/delete'
]), 'modals'); ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/x_reset_modal.php', ['modal_id' => 'notification_reset_modal', 'resource_id' => 'notification_id', 'path' => 'notification/reset']), 'modals', 'notification_reset_modal'); ?>

