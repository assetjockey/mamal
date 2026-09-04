<?php defined('ALTUMCODE') || die() ?>

<div class="app-sidebar">
    <div class="app-sidebar-title text-truncate">
        <a
                href="<?= url() ?>"
                class="navbar-brand text-truncate mr-0"
                data-logo
                data-light-value="<?= settings()->main->logo_dark != '' ? settings()->main->logo_dark_full_url : settings()->main->title ?>"
                data-light-class="<?= settings()->main->logo_dark != '' ? 'img-fluid navbar-logo' : '' ?>"
                data-light-tag="<?= settings()->main->logo_dark != '' ? 'img' : 'span' ?>"
                data-dark-value="<?= settings()->main->logo_dark != '' ? settings()->main->logo_dark_full_url : settings()->main->title ?>"
                data-dark-class="<?= settings()->main->logo_dark != '' ? 'img-fluid navbar-logo' : '' ?>"
                data-dark-tag="<?= settings()->main->logo_dark != '' ? 'img' : 'span' ?>"
        >
            <?php if(settings()->main->logo_dark != ''): ?>
                <img src="<?= settings()->main->{'logo_dark_full_url'} ?>" class="img-fluid navbar-logo" alt="<?= l('global.accessibility.logo_alt') ?>" />
            <?php else: ?>
                <?= settings()->main->title ?>
            <?php endif ?>
        </a>
    </div>

    <div class="overflow-auto flex-grow-1">
        <ul class="app-sidebar-links">
            <?php if(is_logged_in()): ?>
                <li class="<?= \Altum\Router::$controller == 'Dashboard' ? 'active' : null ?> d-flex dropdown" id="internal_notifications">
                    <a href="<?= url('dashboard') ?>"><i class="fas fa-fw fa-sm fa-th mr-2"></i> <?= l('dashboard.menu') ?></a>

                    <?php if(settings()->internal_notifications->users_is_enabled): ?>
                        <a id="internal_notifications_link" href="#" class="default w-auto dropdown-toggle dropdown-toggle-simple ml-1" data-internal-notifications="user" data-tooltip data-tooltip-hide-on-click title="<?= l('internal_notifications.menu') ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-boundary="window">
                            <span id="internal_notifications_icon_wrapper" class="fa-layers fa-fw">
                                <i class="fas fa-fw fa-bell"></i>
                                <?php if($this->user->has_pending_internal_notifications): ?>
                                    <span class="fa-layers-counter text-danger internal-notification-icon">&nbsp;</span>
                                <?php endif ?>
                            </span>
                        </a>

                        <div id="internal_notifications_content" class="dropdown-menu dropdown-menu-right px-4 py-2" style="width: 550px;max-width: 550px;"></div>

                        <?php include_view(THEME_PATH . 'views/partials/internal_notifications_js.php', ['has_pending_internal_notifications' => $this->user->has_pending_internal_notifications]) ?>
                    <?php endif ?>
                </li>

                <?php if(settings()->monitors_heartbeats->monitors_is_enabled): ?>
                    <li class="<?= in_array(\Altum\Router::$controller, ['Monitor', 'Monitors', 'MonitorsImport', 'MonitorUpdate', 'MonitorCreate']) ? 'active' : null ?>">
                        <a href="<?= url('monitors') ?>"><i class="fas fa-fw fa-sm fa-server mr-2"></i> <?= l('monitors.menu') ?></a>
                    </li>
                <?php endif ?>

                <?php if(settings()->monitors_heartbeats->dns_monitors_is_enabled): ?>
                    <li class="<?= in_array(\Altum\Router::$controller, ['DnsMonitor', 'DnsMonitors', 'DnsMonitorsImport', 'DnsMonitorUpdate', 'DnsMonitorCreate']) ? 'active' : null ?>">
                        <a href="<?= url('dns-monitors') ?>"><i class="fas fa-fw fa-sm fa-plug mr-2"></i> <?= l('dns_monitors.menu') ?></a>
                    </li>
                <?php endif ?>

                <?php if(settings()->monitors_heartbeats->server_monitors_is_enabled): ?>
                    <li class="<?= in_array(\Altum\Router::$controller, ['ServerMonitor', 'ServerMonitors', 'ServerMonitorsImport', 'ServerMonitorUpdate', 'ServerMonitorCreate']) ? 'active' : null ?>">
                        <a href="<?= url('server-monitors') ?>"><i class="fas fa-fw fa-sm fa-microchip mr-2"></i> <?= l('server_monitors.menu') ?></a>
                    </li>
                <?php endif ?>

                <?php if(settings()->monitors_heartbeats->heartbeats_is_enabled): ?>
                    <li class="<?= in_array(\Altum\Router::$controller, ['Heartbeat', 'Heartbeats', 'HeartbeatsImport', 'HeartbeatUpdate', 'HeartbeatCreate']) ? 'active' : null ?>">
                        <a href="<?= url('heartbeats') ?>"><i class="fas fa-fw fa-sm fa-heartbeat mr-2"></i> <?= l('heartbeats.menu') ?></a>
                    </li>
                <?php endif ?>

                <?php if(settings()->monitors_heartbeats->domain_names_is_enabled): ?>
                    <li class="<?= in_array(\Altum\Router::$controller, ['DomainName', 'DomainNames', 'DomainNamesImport', 'DomainNameUpdate', 'DomainNameCreate']) ? 'active' : null ?>">
                        <a href="<?= url('domain-names') ?>"><i class="fas fa-fw fa-sm fa-network-wired mr-2"></i> <?= l('domain_names.menu') ?></a>
                    </li>
                <?php endif ?>

                <?php if(settings()->monitors_heartbeats->game_servers_is_enabled): ?>
                    <li class="<?= in_array(\Altum\Router::$controller, ['GameServer', 'GameServers', 'GameServersImport', 'GameServerUpdate', 'GameServerCreate', 'GameServerLogs']) ? 'active' : null ?>">
                        <a href="<?= url('game-servers') ?>"><i class="fas fa-fw fa-sm fa-gamepad mr-2"></i> <?= l('game_servers.menu') ?></a>
                    </li>
                <?php endif ?>

                <?php if(settings()->status_pages->status_pages_is_enabled): ?>
                    <li class="<?= in_array(\Altum\Router::$controller, ['StatusPages', 'StatusPageUpdate', 'StatusPageCreate', 'StatusPageQr', 'StatusPageStatistics']) ? 'active' : null ?>">
                        <a href="<?= url('status-pages') ?>"><i class="fas fa-fw fa-sm fa-wifi mr-2"></i> <?= l('status_pages.menu') ?></a>
                    </li>
                <?php endif ?>

                <?php if(settings()->monitors_heartbeats->monitors_is_enabled || settings()->monitors_heartbeats->heartbeats_is_enabled): ?>
                    <li class="<?= in_array(\Altum\Router::$controller, ['Incidents', 'Incident', ]) ? 'active' : null ?>">
                        <a href="<?= url('incidents') ?>"><i class="fas fa-fw fa-sm fa-exclamation-circle mr-2"></i> <?= l('incidents.menu') ?></a>
                    </li>
                <?php endif ?>

                <?php if(settings()->monitors_heartbeats->projects_is_enabled): ?>
                    <li class="<?= in_array(\Altum\Router::$controller, ['Projects', 'ProjectUpdate', 'ProjectCreate']) ? 'active' : null ?>">
                        <a href="<?= url('projects') ?>"><i class="fas fa-fw fa-sm fa-project-diagram mr-2"></i> <?= l('projects.menu') ?></a>
                    </li>
                <?php endif ?>

                <?php if(settings()->status_pages->domains_is_enabled): ?>
                    <li class="<?= in_array(\Altum\Router::$controller, ['Domains', 'DomainUpdate', 'DomainCreate']) ? 'active' : null ?>">
                        <a href="<?= url('domains') ?>"><i class="fas fa-fw fa-sm fa-globe mr-2"></i> <?= l('domains.menu') ?></a>
                    </li>
                <?php endif ?>

                <li class="<?= in_array(\Altum\Router::$controller, ['NotificationHandlers', 'NotificationHandlerUpdate', 'NotificationHandlerCreate']) ? 'active' : null ?>">
                    <a href="<?= url('notification-handlers') ?>"><i class="fas fa-fw fa-sm fa-bell mr-2"></i> <?= l('notification_handlers.menu') ?></a>
                </li>
            <?php endif ?>

            <?php if(settings()->tools->is_enabled && (settings()->tools->access == 'everyone' || (settings()->tools->access == 'users' && is_logged_in()))): ?>
                <li class="<?= \Altum\Router::$controller == 'Tools' ? 'active' : null ?>">
                    <a href="<?= url('tools') ?>"><i class="fas fa-fw fa-sm fa-tools mr-2"></i> <?= l('tools.menu') ?></a>
                </li>
            <?php endif ?>

            <?php foreach($data->pages as $row): ?>
                <?php if($row->type == 'feature') continue ?>
                <?php $page = process_dynamic_page_link($row) ?>
                <?php if(!$page) continue ?>

                <li>
                    <a
                            class="nav-link"
                            href="<?= $page['url'] ?>"
                            target="<?= $page['target'] ?>"
                    <?php foreach($page['attributes'] as $attribute_key => $attribute_value): ?><?= $attribute_key ?>="<?= $attribute_value ?>" <?php endforeach ?>
                    >
                    <?php if($page['icon']): ?>
                        <i class="<?= $page['icon'] ?> fa-fw fa-sm mr-2"></i>
                    <?php endif ?>

                    <?= $page['title'] ?>
                    </a>
                </li>
            <?php endforeach ?>
        </ul>
    </div>

    <?php if(is_logged_in()): ?>

        <ul class="app-sidebar-links">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="d-flex align-items-center app-sidebar-footer-block">
                        <img src="<?= get_user_avatar($this->user->avatar, $this->user->email) ?>" class="app-sidebar-avatar mr-3" loading="lazy" />

                        <div class="app-sidebar-footer-text d-flex flex-column text-truncate">
                            <span class="text-truncate"><?= $this->user->name ?></span>
                            <small class="text-truncate"><?= $this->user->email ?></small>
                        </div>
                    </div>
                </a>

                <div class="dropdown-menu dropdown-menu-right">
                    <?php if(!\Altum\Teams::is_delegated()): ?>
                        <?php if(\Altum\Authentication::is_admin()): ?>
                            <a class="dropdown-item" href="<?= url('admin') ?>"><i class="fas fa-fw fa-sm fa-fingerprint text-primary mr-2"></i> <?= l('global.menu.admin') ?></a>
                            <div class="dropdown-divider"></div>
                        <?php endif ?>

                        <a class="dropdown-item <?= in_array(\Altum\Router::$controller, ['Account']) ? 'active' : null ?>" href="<?= url('account') ?>"><i class="fas fa-fw fa-sm fa-user-cog mr-2"></i> <?= l('account.menu') ?></a>

                        <a class="dropdown-item <?= in_array(\Altum\Router::$controller, ['AccountPreferences']) ? 'active' : null ?>" href="<?= url('account-preferences') ?>"><i class="fas fa-fw fa-sm fa-sliders-h mr-2"></i> <?= l('account_preferences.menu') ?></a>

                        <a class="dropdown-item <?= in_array(\Altum\Router::$controller, ['AccountPlan']) ? 'active' : null ?>" href="<?= url('account-plan') ?>"><i class="fas fa-fw fa-sm fa-box-open mr-2"></i> <?= l('account_plan.menu') ?></a>

                        <?php if(settings()->payment->is_enabled): ?>
                            <a class="dropdown-item <?= in_array(\Altum\Router::$controller, ['AccountPayments']) ? 'active' : null ?>" href="<?= url('account-payments') ?>"><i class="fas fa-fw fa-sm fa-credit-card mr-2"></i> <?= l('account_payments.menu') ?></a>

                            <?php if(\Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled): ?>
                                <a class="dropdown-item <?= in_array(\Altum\Router::$controller, ['Referrals']) ? 'active' : null ?>" href="<?= url('referrals') ?>"><i class="fas fa-fw fa-sm fa-wallet mr-2"></i> <?= l('referrals.menu') ?></a>
                            <?php endif ?>
                        <?php endif ?>

                        <?php if(settings()->main->api_is_enabled): ?>
                            <a class="dropdown-item <?= in_array(\Altum\Router::$controller, ['AccountApi']) ? 'active' : null ?>" href="<?= url('account-api') ?>"><i class="fas fa-fw fa-sm fa-code mr-2"></i> <?= l('account_api.menu') ?></a>
                        <?php endif ?>

                        <?php if(\Altum\Plugin::is_active('teams')): ?>
                            <a class="dropdown-item <?= in_array(\Altum\Router::$controller, ['TeamsSystem', 'Teams', 'Team', 'TeamCreate', 'TeamUpdate', 'TeamsMember', 'TeamsMembers', 'TeamsMemberCreate', 'TeamsMemberUpdate']) ? 'active' : null ?>" href="<?= url('teams-system') ?>"><i class="fas fa-fw fa-sm fa-user-shield mr-2"></i> <?= l('teams_system.menu') ?></a>
                        <?php endif ?>

                        <?php if(settings()->sso->is_enabled && settings()->sso->display_menu_items && count((array) settings()->sso->websites)): ?>
                            <div class="dropdown-divider"></div>

                            <?php foreach(settings()->sso->websites as $website): ?>
                                <a class="dropdown-item" href="<?= url('sso/switch?to=' . $website->id) ?>"><i class="<?= $website->icon ?> fa-fw fa-sm mr-2"></i> <?= sprintf(l('sso.menu'), $website->name) ?></a>
                            <?php endforeach ?>
                        <?php endif ?>

                        <div class="dropdown-divider"></div>
                    <?php endif ?>

                    <a class="dropdown-item" href="<?= url('logout') ?>"><i class="fas fa-fw fa-sm fa-sign-out-alt mr-2"></i> <?= l('global.menu.logout') ?></a>
                </div>
            </li>
        </ul>

    <?php else: ?>

        <ul class="app-sidebar-links">
            <li>
                <a class="nav-link" href="<?= url('login') ?>"><i class="fas fa-fw fa-sm fa-sign-in-alt mr-2"></i> <?= l('login.menu') ?></a>
            </li>

            <?php if(settings()->users->register_is_enabled): ?>
                <li><a class="nav-link" href="<?= url('register') ?>"><i class="fas fa-fw fa-sm fa-user-plus mr-2"></i> <?= l('register.menu') ?></a></li>
            <?php endif ?>
        </ul>

    <?php endif ?>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    document.querySelector('ul[class="app-sidebar-links"] li.active') && document.querySelector('ul[class="app-sidebar-links"] li.active').scrollIntoView({ behavior: 'smooth', block: 'center' });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
