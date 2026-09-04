<?php defined('ALTUMCODE') || die() ?>

<?php $available_plan_features = require APP_PATH . 'includes/available_plan_features.php' ?>
<?php $features = ((array) (settings()->payment->plan_features ?? [])) + array_fill_keys($available_plan_features, true) ?>
<?php $features_in_front = ((array) (settings()->payment->plan_features_in_front ?? [])) + array_fill_keys($available_plan_features, true) ?>

<?php $not_in_front_html = ''; ?>

<ul class="list-style-none m-0">
    <?php foreach($features as $feature => $is_enabled): ?>
        <?php if(!$is_enabled) continue ?>

        <?php ob_start() ?>

        <?php if($feature == 'monitors_limit' && settings()->monitors_heartbeats->monitors_is_enabled): ?>
            <?php $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers(); ?>
            <?php $monitor_check_intervals = require APP_PATH . 'includes/monitor_check_intervals.php'; ?>

            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->monitors_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->monitors_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.monitors_limit'), '<strong>' . ($data->plan_settings->monitors_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->monitors_limit)) . '</strong>') ?>
                    <span class="mr-1" data-toggle="tooltip" title="<?= sprintf(l('global.plan_settings.monitors_check_intervals'), implode(', ', array_values(array_intersect_key($monitor_check_intervals, array_flip($data->plan_settings->monitors_check_intervals ?? []))))) ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>

            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= count($data->plan_settings->monitors_ping_servers ?? []) ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= count($data->plan_settings->monitors_ping_servers ?? []) ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.monitors_ping_servers'), '<strong>' . nr(count($data->plan_settings->monitors_ping_servers ?? [])) . '</strong>') ?>
                    <span class="mr-1" data-toggle="tooltip" title="<?= sprintf(l('global.plan_settings.monitors_ping_servers_help'), implode(', ', array_map(function($ping_server_id) use($ping_servers) { return get_countries_array()[$ping_servers[$ping_server_id]->country_code] . ' (' . $ping_servers[$ping_server_id]->city_name . ')'; }, $data->plan_settings->monitors_ping_servers ?? []))) ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'heartbeats_limit' && settings()->monitors_heartbeats->heartbeats_is_enabled): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->heartbeats_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->heartbeats_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.heartbeats_limit'), '<strong>' . ($data->plan_settings->heartbeats_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->heartbeats_limit)) . '</strong>') ?>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'domain_names_limit' && settings()->monitors_heartbeats->domain_names_is_enabled): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->domain_names_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->domain_names_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.domain_names_limit'), '<strong>' . ($data->plan_settings->domain_names_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->domain_names_limit)) . '</strong>') ?>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'dns_monitors_limit' && settings()->monitors_heartbeats->dns_monitors_is_enabled): ?>
            <?php $dns_monitor_check_intervals = require APP_PATH . 'includes/dns_monitor_check_intervals.php'; ?>

            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->dns_monitors_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->dns_monitors_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.dns_monitors_limit'), '<strong>' . ($data->plan_settings->dns_monitors_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->dns_monitors_limit)) . '</strong>') ?>
                    <span class="mr-1" data-toggle="tooltip" title="<?= sprintf(l('global.plan_settings.dns_monitors_check_intervals'), implode(', ', array_values(array_intersect_key($dns_monitor_check_intervals, array_flip($data->plan_settings->dns_monitors_check_intervals ?? []))))) ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'server_monitors_limit' && settings()->monitors_heartbeats->server_monitors_is_enabled): ?>
            <?php $server_monitor_check_intervals = require APP_PATH . 'includes/server_monitor_check_intervals.php'; ?>

            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->server_monitors_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->server_monitors_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.server_monitors_limit'), '<strong>' . ($data->plan_settings->server_monitors_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->server_monitors_limit)) . '</strong>') ?>
                    <span class="mr-1" data-toggle="tooltip" title="<?= sprintf(l('global.plan_settings.server_monitors_check_intervals'), implode(', ', array_values(array_intersect_key($server_monitor_check_intervals, array_flip($data->plan_settings->dns_monitors_check_intervals ?? []))))) ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

		<?php if($feature == 'game_servers_limit' && settings()->monitors_heartbeats->game_servers_is_enabled): ?>
			<?php $game_server_check_intervals = require APP_PATH . 'includes/game_server_check_intervals.php'; ?>

            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->game_servers_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->game_servers_limit ? null : 'text-muted' ?>">
					<?= sprintf(l('global.plan_settings.game_servers_limit'), '<strong>' . ($data->plan_settings->game_servers_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->game_servers_limit)) . '</strong>') ?>
                    <span class="mr-1" data-toggle="tooltip" title="<?= sprintf(l('global.plan_settings.game_servers_check_intervals'), implode(', ', array_values(array_intersect_key($game_server_check_intervals, array_flip($data->plan_settings->dns_monitors_check_intervals ?? []))))) ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
		<?php endif ?>

        <?php if($feature == 'status_pages_limit' && settings()->status_pages->status_pages_is_enabled): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->status_pages_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->status_pages_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.status_pages_limit'), '<strong>' . ($data->plan_settings->status_pages_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->status_pages_limit)) . '</strong>') ?>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'projects_limit' && settings()->monitors_heartbeats->projects_is_enabled): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->projects_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->projects_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.projects_limit'), '<strong>' . ($data->plan_settings->projects_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->projects_limit)) . '</strong>') ?>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'domains_limit' && settings()->status_pages->domains_is_enabled): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->domains_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->domains_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.domains_limit'), '<strong>' . ($data->plan_settings->domains_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->domains_limit)) . '</strong>') ?>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'additional_domains' && settings()->status_pages->additional_domains_is_enabled): ?>
            <?php $additional_domains = (new \Altum\Models\Domain())->get_available_additional_domains(); ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= count($data->plan_settings->additional_domains ?? []) ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= count($data->plan_settings->additional_domains ?? []) ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.additional_domains'), '<strong>' . nr(count($data->plan_settings->additional_domains ?? [])) . '</strong>') ?>
                    <span class="mr-1" data-toggle="tooltip" title="<?= sprintf(l('global.plan_settings.additional_domains_help'), implode(', ', array_map(function($domain_id) use($additional_domains) { return $additional_domains[$domain_id]->host ?? null; }, $data->plan_settings->additional_domains ?? []))) ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'teams_limit' && \Altum\Plugin::is_active('teams')): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->teams_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->teams_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.teams_limit'), '<strong>' . ($data->plan_settings->teams_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->teams_limit)) . '</strong>') ?>
                    <span class="ml-1" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.plan_settings.team_members_limit'), '<strong>' . ($data->plan_settings->team_members_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->team_members_limit)) . '</strong>') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'affiliate_commission_percentage' && settings()->affiliate->is_enabled): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->affiliate_commission_percentage ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->affiliate_commission_percentage ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.affiliate_commission_percentage'), '<strong>' . nr($data->plan_settings->affiliate_commission_percentage) . '%</strong>') ?>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'logs_retention'): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->logs_retention ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->logs_retention ? null : 'text-muted' ?>" data-toggle="tooltip" title="<?= ($data->plan_settings->logs_retention == -1 ? '' : $data->plan_settings->logs_retention . ' ' . l('global.date.days')) ?>">
                    <?= sprintf(l('global.plan_settings.logs_retention'), '<strong>' . ($data->plan_settings->logs_retention == -1 ? l('global.unlimited') : \Altum\Date::days_format($data->plan_settings->logs_retention)) . '</strong>') ?>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'statistics_retention'): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->statistics_retention ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->statistics_retention ? null : 'text-muted' ?>" data-toggle="tooltip" title="<?= ($data->plan_settings->statistics_retention == -1 ? '' : $data->plan_settings->statistics_retention . ' ' . l('global.date.days')) ?>">
                    <?= sprintf(l('global.plan_settings.statistics_retention'), '<strong>' . ($data->plan_settings->statistics_retention == -1 ? l('global.unlimited') : \Altum\Date::days_format($data->plan_settings->statistics_retention)) . '</strong>') ?>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'notification_handlers_limit'): ?>
            <?php ob_start() ?>
            <?php $notification_handlers_icon = 'fa-times text-muted'; ?>
            <div class='d-flex flex-column'>
                <?php foreach(array_keys(require APP_PATH . 'includes/notification_handlers.php') as $notification_handler): ?>
                    <span class='my-1'><?= sprintf(l('global.plan_settings.notification_handlers_' . $notification_handler . '_limit'), '<strong>' . ($data->plan_settings->{'notification_handlers_' . $notification_handler . '_limit'} == -1 ? l('global.unlimited') : nr($data->plan_settings->{'notification_handlers_' . $notification_handler . '_limit'})) . '</strong>') ?></span>
                    <?php if($data->plan_settings->{'notification_handlers_' . $notification_handler . '_limit'}) $notification_handlers_icon = 'fa-check text-success'; ?>
                <?php endforeach ?>
            </div>
            <?php $html = ob_get_clean() ?>

            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $notification_handlers_icon ?>"></i>
                <div>
                    <?= l('global.plan_settings.notification_handlers_limit') ?>
                    <span class="ml-1" data-toggle="tooltip" data-html="true" title="<?= $html ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'analytics_is_enabled' && settings()->status_pages->status_pages_is_enabled): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->analytics_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->analytics_is_enabled ? null : 'text-muted' ?>">
                    <?= l('global.plan_settings.analytics_is_enabled') ?>
                    <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.analytics_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'custom_pwa_is_enabled' && \Altum\Plugin::is_active('pwa') && settings()->pwa->is_enabled): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->custom_pwa_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->custom_pwa_is_enabled ? null : 'text-muted' ?>">
                    <?= l('global.plan_settings.custom_pwa_is_enabled') ?>
                    <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.custom_pwa_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'qr_is_enabled'): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->qr_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->qr_is_enabled ? null : 'text-muted' ?>">
                    <?= l('global.plan_settings.qr_is_enabled') ?>
                    <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.qr_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'password_protection_is_enabled'): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->password_protection_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->password_protection_is_enabled ? null : 'text-muted' ?>">
                    <?= l('global.plan_settings.password_protection_is_enabled') ?>
                    <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.password_protection_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'removable_branding_is_enabled'): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->removable_branding_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->removable_branding_is_enabled ? null : 'text-muted' ?>">
                    <?= l('global.plan_settings.removable_branding_is_enabled') ?>
                    <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.removable_branding_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'custom_url_is_enabled'): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->custom_url_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->custom_url_is_enabled ? null : 'text-muted' ?>">
                    <?= l('global.plan_settings.custom_url_is_enabled') ?>
                    <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.custom_url_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'search_engine_visibility_is_enabled'): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->search_engine_visibility_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->search_engine_visibility_is_enabled ? null : 'text-muted' ?>">
                    <?= l('global.plan_settings.search_engine_visibility_is_enabled') ?>
                    <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.search_engine_visibility_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'custom_css_is_enabled'): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->custom_css_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->custom_css_is_enabled ? null : 'text-muted' ?>">
                    <?= l('global.plan_settings.custom_css_is_enabled') ?>
                    <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.custom_css_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'custom_js_is_enabled'): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->custom_js_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->custom_js_is_enabled ? null : 'text-muted' ?>">
                    <?= l('global.plan_settings.custom_js_is_enabled') ?>
                    <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.custom_js_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'email_reports_is_enabled' && settings()->monitors_heartbeats->email_reports_is_enabled): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->email_reports_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->email_reports_is_enabled ? null : 'text-muted' ?>">
                    <?= l('global.plan_settings.email_reports_is_enabled') ?>
                    <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.email_reports_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'api_is_enabled' && settings()->main->api_is_enabled): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->api_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->api_is_enabled ? null : 'text-muted' ?>">
                    <?= l('global.plan_settings.api_is_enabled') ?>
                    <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.api_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'white_labeling_is_enabled' && settings()->main->white_labeling_is_enabled): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->white_labeling_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->white_labeling_is_enabled ? null : 'text-muted' ?>">
                    <?= l('global.plan_settings.white_labeling_is_enabled') ?>
                    <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.white_labeling_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == sprintf(l('global.plan_settings.export'), '')): ?>
            <?php $enabled_exports_count = count(array_filter((array) $data->plan_settings->export)); ?>

            <?php ob_start() ?>
            <div class='d-flex flex-column'>
                <?php foreach(['csv', 'json', 'pdf'] as $key): ?>
                    <?php if($data->plan_settings->export->{$key}): ?>
                        <span class='my-1'><?= sprintf(l('global.export_to'), mb_strtoupper($key)) ?></span>
                    <?php else: ?>
                        <s class='my-1'><?= sprintf(l('global.export_to'), mb_strtoupper($key)) ?></s>
                    <?php endif ?>
                <?php endforeach ?>
            </div>
            <?php $html = ob_get_clean() ?>

            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $enabled_exports_count ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $enabled_exports_count ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.export'), $enabled_exports_count) ?>
                    <span class="mr-1" data-html="true" data-toggle="tooltip" title="<?= $html ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'no_ads'): ?>
            <li class="d-flex align-items-baseline mb-2">
                <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->no_ads ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                <div class="<?= $data->plan_settings->no_ads ? null : 'text-muted' ?>">
                    <?= l('global.plan_settings.no_ads') ?>
                    <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.no_ads_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php
        if($features_in_front[$feature]) {
            echo ob_get_clean();
        } else {
            $not_in_front_html .= trim(ob_get_clean());
        }
        ?>

    <?php endforeach ?>

    <?php if(!empty($not_in_front_html)): ?>
        <div class="d-flex justify-content-between align-items-center my-3">
            <button type="button" class="btn btn-sm btn-outline-light btn-block text-reset text-decoration-none font-weight-bold px-5" data-toggle="collapse" data-target=".view_all_container">
                <i class="fas fa-fw fa-sm fa-plus-circle mr-1"></i> <?= l('global.view_all') ?>
            </button>
        </div>

        <div class="collapse view_all_container">
            <?= $not_in_front_html ?>
        </div>
    <?php endif ?>
</ul>
