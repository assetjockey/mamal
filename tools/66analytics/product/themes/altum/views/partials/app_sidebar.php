<?php defined('ALTUMCODE') || die() ?>

<section class="app-sidebar d-print-none">
    <div class="app-sidebar-title text-truncate" data-toggle="tooltip" data-placement="right" title="<?= settings()->main->title ?>">
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
                <?= mb_substr(settings()->main->title, 0, 1) ?>
            <?php endif ?>
        </a>
    </div>


    <div class="overflow-auto flex-grow-1">
        <ul class="app-sidebar-links">
            <li class="<?= \Altum\Router::$controller == 'Dashboard' && !string_ends_with('dashboard/goals', $_GET['altum']) && !string_ends_with('dashboard/outbound-clicks', $_GET['altum']) && !string_ends_with('dashboard/realtime', $_GET['altum']) ? 'active' : null ?>">
                <a href="<?= url('dashboard') ?>" data-toggle="tooltip" data-boundary="viewport" data-placement="right" title="<?= l('dashboard.menu') ?>"><i class="fas fa-fw fa-th"></i></a>
            </li>

            <li class="<?= \Altum\Router::$controller == 'Dashboard' && string_ends_with('dashboard/goals', $_GET['altum']) ? 'active' : null ?>">
                <a href="<?= url('dashboard/goals') ?>" data-toggle="tooltip" data-boundary="viewport" data-placement="right" title="<?= l('analytics.goals') ?>"><i class="fas fa-fw fa-bullseye"></i></a>
            </li>

            <li class="<?= \Altum\Router::$controller == 'Dashboard' && string_ends_with('dashboard/outbound-clicks', $_GET['altum']) ? 'active' : null ?>">
                <a href="<?= url('dashboard/outbound-clicks') ?>" data-toggle="tooltip" data-boundary="viewport" data-placement="right" title="<?= l('analytics.outbound_clicks') ?>"><i class="fas fa-fw fa-external-link-alt"></i></a>
            </li>

            <li class="<?= \Altum\Router::$controller == 'Dashboard' && string_ends_with('dashboard/realtime', $_GET['altum']) ? 'active' : null ?>">
                <a href="<?= url('dashboard/realtime') ?>" data-toggle="tooltip" data-boundary="viewport" data-placement="right" title="<?= l('realtime.menu') ?>"><i class="fas fa-fw fa-signal"></i></a>
            </li>

            <li class="<?= in_array(\Altum\Router::$controller, ['PageviewsAdvanced', 'PageviewsLightweight']) ? 'active' : null ?>">
                <a href="<?= url('pageviews') ?>" data-toggle="tooltip" data-boundary="viewport" data-placement="right" title="<?= l('pageviews.menu') ?>"><i class="fas fa-fw fa-eye"></i></a>
            </li>

            <?php if(!$this->website || ($this->website && $this->website->tracking_type == 'advanced')): ?>
                <li class="<?= in_array(\Altum\Router::$controller, ['Visitors', 'Visitor']) ? 'active' : null ?>">
                    <a href="<?= url('visitors') ?>" data-toggle="tooltip" data-boundary="viewport" data-placement="right" title="<?= l('visitors.menu') ?>"><i class="fas fa-fw fa-user-friends"></i></a>
                </li>

                <?php if(settings()->analytics->websites_heatmaps_is_enabled): ?>
                    <li class="<?= in_array(\Altum\Router::$controller, ['Heatmaps', 'Heatmap']) ? 'active' : null ?>">
                        <a href="<?= url('heatmaps') ?>" data-toggle="tooltip" data-boundary="viewport" data-placement="right" title="<?= l('heatmaps.menu') ?>"><i class="fas fa-fw fa-fire"></i></a>
                    </li>
                <?php endif ?>

                <?php if(settings()->analytics->sessions_replays_is_enabled): ?>
                    <li class="<?= in_array(\Altum\Router::$controller, ['Replays', 'Replay']) ? 'active' : null ?>">
                        <a href="<?= url('replays') ?>" data-toggle="tooltip" data-boundary="viewport" data-placement="right" title="<?= l('replays.menu') ?>"><i class="fas fa-fw fa-video"></i></a>
                    </li>
                <?php endif ?>
            <?php endif ?>

            <li class="<?= in_array(\Altum\Router::$controller, ['Websites', 'WebsitesImport', 'WebsiteUpdate', 'WebsiteCreate']) ? 'active' : null ?>">
                <a href="<?= url('websites') ?>" data-toggle="tooltip" data-boundary="viewport" data-placement="right" title="<?= l('websites.menu') ?>"><i class="fas fa-fw fa-pager"></i></a>
            </li>

            <li class="<?= in_array(\Altum\Router::$controller, ['Teams', 'Team']) ? 'active' : null ?>">
                <a href="<?= url('teams') ?>" data-toggle="tooltip" data-boundary="viewport" data-placement="right" title="<?= l('teams.menu') ?>"><i class="fas fa-fw fa-user-shield"></i></a>
            </li>

            <?php if(settings()->analytics->domains_is_enabled): ?>
                <li class="<?= in_array(\Altum\Router::$controller, ['Domains', 'DomainUpdate', 'DomainCreate']) ? 'active' : null ?>">
                    <a href="<?= url('domains') ?>" data-toggle="tooltip" data-boundary="viewport" data-placement="right" title="<?= l('domains.menu') ?>"><i class="fas fa-fw fa-globe"></i></a>
                </li>
            <?php endif ?>

            <li class="<?= in_array(\Altum\Router::$controller, ['Help']) ? 'active' : null ?>">
                <a href="<?= url('help') ?>" data-toggle="tooltip" data-boundary="viewport" data-placement="right" title="<?= l('help.menu') ?>"><i class="fas fa-fw fa-question"></i></a>
            </li>

            <?php if(settings()->internal_notifications->users_is_enabled): ?>
                <li id="internal_notifications" class="dropdown">
                    <a id="internal_notifications_link" href="#" class="nav-link dropdown-toggle dropdown-toggle-simple" data-internal-notifications="user" data-tooltip data-tooltip-hide-on-click data-placement="right" title="<?= l('internal_notifications.menu') ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-boundary="window">
                        <span class="fa-layers fa-fw">
                            <i class="fas fa-fw fa-bell"></i>
                            <?php if($this->user->has_pending_internal_notifications): ?>
                                <span class="fa-layers-counter text-danger internal-notification-icon">&nbsp;</span>
                            <?php endif ?>
                        </span>
                    </a>

                    <div id="internal_notifications_content" class="dropdown-menu dropdown-menu-right px-4 py-2" style="width: 550px;max-width: 550px;"></div>
                </li>

                <?php include_view(THEME_PATH . 'views/partials/internal_notifications_js.php', ['has_pending_internal_notifications' => $this->user->has_pending_internal_notifications]) ?>
            <?php endif ?>
        </ul>
    </div>
</section>
