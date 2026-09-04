<?php defined('ALTUMCODE') || die() ?>

<?php if(\Altum\Router::$controller_key == 'index'): ?>
    <div class="index-background-container d-none d-lg-block">
        <svg class="index-background-image" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2000 1500" aria-hidden="true">
            <defs>
                <radialGradient id="index-gradient-a" gradientUnits="objectBoundingBox">
                    <stop offset="0" stop-color="var(--primary)" />
                    <stop offset="1" stop-color="var(--primary-700)" />
                </radialGradient>

                <linearGradient id="index-gradient-b" gradientUnits="userSpaceOnUse" x1="0" y1="750" x2="1550" y2="750">
                    <stop offset="0" stop-color="var(--primary-600)" />
                    <stop offset="1" stop-color="var(--primary-700)" />
                </linearGradient>

                <path id="index-shape" fill="url(#index-gradient-b)" d="M1549.2 51.6c-5.4 99.1-20.2 197.6-44.2 293.6c-24.1 96-57.4 189.4-99.3 278.6c-41.9 89.2-92.4 174.1-150.3 253.3c-58 79.2-123.4 152.6-195.1 219c-71.7 66.4-149.6 125.8-232.2 177.2c-82.7 51.4-170.1 94.7-260.7 129.1c-90.6 34.4-184.4 60-279.5 76.3C192.6 1495 96.1 1502 0 1500c96.1-2.1 191.8-13.3 285.4-33.6c93.6-20.2 185-49.5 272.5-87.2c87.6-37.7 171.3-83.8 249.6-137.3c78.4-53.5 151.5-114.5 217.9-181.7c66.5-67.2 126.4-140.7 178.6-218.9c52.3-78.3 96.9-161.4 133-247.9c36.1-86.5 63.8-176.2 82.6-267.6c18.8-91.4 28.6-184.4 29.6-277.4c.3-27.6 23.2-48.7 50.8-48.4s49.5 21.8 49.2 49.5c0 .7 0 1.3-.1 2l-.1.1z" />

                <g id="index-shapes">
                    <use href="#index-shape" transform="scale(.12) rotate(60)" />
                    <use href="#index-shape" transform="scale(.2) rotate(10)" />
                    <use href="#index-shape" transform="scale(.25) rotate(40)" />
                    <use href="#index-shape" transform="scale(.3) rotate(-20)" />
                    <use href="#index-shape" transform="scale(.4) rotate(-30)" />
                    <use href="#index-shape" transform="scale(.5) rotate(20)" />
                    <use href="#index-shape" transform="scale(.6) rotate(60)" />
                    <use href="#index-shape" transform="scale(.7) rotate(10)" />
                    <use href="#index-shape" transform="scale(.835) rotate(-40)" />
                    <use href="#index-shape" transform="scale(.9) rotate(40)" />
                    <use href="#index-shape" transform="scale(1.05) rotate(25)" />
                    <use href="#index-shape" transform="scale(1.2) rotate(8)" />
                    <use href="#index-shape" transform="scale(1.333) rotate(-60)" />
                    <use href="#index-shape" transform="scale(1.45) rotate(-30)" />
                    <use href="#index-shape" transform="scale(1.6) rotate(10)" />
                </g>
            </defs>

            <g transform="translate(80 0)">
                <circle fill="url(#index-gradient-a)" r="3000" />

                <g opacity=".5">
                    <circle fill="url(#index-gradient-a)" r="2000" />
                    <circle fill="url(#index-gradient-a)" r="1800" />
                    <circle fill="url(#index-gradient-a)" r="1700" />
                    <circle fill="url(#index-gradient-a)" r="1651" />
                    <circle fill="url(#index-gradient-a)" r="1450" />
                    <circle fill="url(#index-gradient-a)" r="1250" />
                    <circle fill="url(#index-gradient-a)" r="1175" />
                    <circle fill="url(#index-gradient-a)" r="900" />
                    <circle fill="url(#index-gradient-a)" r="750" />
                    <circle fill="url(#index-gradient-a)" r="500" />
                    <circle fill="url(#index-gradient-a)" r="380" />
                    <circle fill="url(#index-gradient-a)" r="250" />
                </g>

                <g transform="rotate(-25.2)">
                    <use href="#index-shapes" transform="rotate(10)" />
                    <use href="#index-shapes" transform="rotate(120)" />
                    <use href="#index-shapes" transform="rotate(240)" />
                </g>
            </g>
        </svg>
    </div>

    <style>
        .navbar-main .navbar-nav > li > a {
            color: black !important;
        }

        [data-theme-style="dark"] .navbar-main .navbar-nav > li > a {
            color: white !important;
        }
    </style>
<?php endif ?>

<div class="container pt-4">
    <nav class="navbar navbar-main mb-5 mb-lg-6 navbar-expand-lg navbar-light border border-gray-200 rounded-2x">
        <div class="container">
            <a
                    href="<?= url() ?>"
                    class="navbar-brand d-flex"
                    data-logo
                    data-light-value="<?= settings()->main->logo_light != '' ? settings()->main->logo_light_full_url : settings()->main->title ?>"
                    data-light-class="<?= settings()->main->logo_light != '' ? 'img-fluid navbar-logo' : '' ?>"
                    data-light-tag="<?= settings()->main->logo_light != '' ? 'img' : 'span' ?>"
                    data-dark-value="<?= settings()->main->logo_dark != '' ? settings()->main->logo_dark_full_url : settings()->main->title ?>"
                    data-dark-class="<?= settings()->main->logo_dark != '' ? 'img-fluid navbar-logo' : '' ?>"
                    data-dark-tag="<?= settings()->main->logo_dark != '' ? 'img' : 'span' ?>"
            >
                <?php if(settings()->main->{'logo_' . \Altum\ThemeStyle::get()} != ''): ?>
                    <img src="<?= settings()->main->{'logo_' . \Altum\ThemeStyle::get() . '_full_url'} ?>" class="img-fluid navbar-logo" alt="<?= l('global.accessibility.logo_alt') ?>" />
                <?php else: ?>
                    <?= settings()->main->title ?>
                <?php endif ?>
            </a>

            <button class="btn navbar-custom-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#main_navbar" aria-controls="main_navbar" aria-expanded="false" aria-label="<?= l('global.accessibility.toggle_navigation') ?>">
                <i class="fas fa-fw fa-bars"></i>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="main_navbar">
                <ul class="navbar-nav">

                    <?php foreach($data->pages as $row): ?>
                    <?php $page = process_dynamic_page_link($row) ?>
                    <?php if(!$page) continue ?>

                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="<?= $page['url'] ?>"
                            target="<?= $page['target'] ?>"
                            <?php foreach($page['attributes'] as $attribute_key => $attribute_value): ?><?= $attribute_key ?>="<?= $attribute_value ?>" <?php endforeach ?>
                        >
                            <?php if($page['icon']): ?>
                                <i class="<?= $page['icon'] ?> fa-fw fa-sm mr-1"></i>
                            <?php endif ?>

                            <?= $page['title'] ?>
                        </a>
                    </li>
                <?php endforeach ?>

                    <?php if(is_logged_in()): ?>

                        <li class="nav-item"><a class="nav-link" href="<?= url('dashboard') ?>"> <?= l('dashboard.menu') ?></a></li>

                        <?php if(settings()->internal_notifications->users_is_enabled): ?>
                            <li class="nav-item dropdown" id="internal_notifications">
                                <a id="internal_notifications_link" href="#" class="nav-link dropdown-toggle dropdown-toggle-simple" data-internal-notifications="user" data-tooltip data-tooltip-hide-on-click title="<?= l('internal_notifications.menu') ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-boundary="window">
                                    <span class="fa-layers fa-fw">
                                        <i class="fas fa-fw fa-bell"></i>
                                        <?php if($this->user->has_pending_internal_notifications): ?>
                                            <span class="fa-layers-counter text-danger internal-notification-icon">&nbsp;</span>
                                        <?php endif ?>
                                    </span>
                                    <span class="d-lg-none ml-1"><?= l('internal_notifications.menu') ?></span>
                                </a>

                                <div id="internal_notifications_content" class="dropdown-menu dropdown-menu-right px-4 py-2" style="width: 550px;max-width: 550px;"></div>
                            </li>

                            <?php include_view(THEME_PATH . 'views/partials/internal_notifications_js.php', ['has_pending_internal_notifications' => $this->user->has_pending_internal_notifications]) ?>
                        <?php endif ?>

                        <li class="dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown" data-boundary="viewport" href="#" aria-haspopup="true" aria-expanded="false">
                                <img src="<?= get_user_avatar($this->user->avatar, $this->user->email) ?>" class="navbar-avatar mr-2" loading="lazy" />
                                <?= $this->user->name ?>
                                <span class="ml-2 caret"></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right">
                                <div class="d-flex flex-column flex-lg-row">
                                    <div class="pr-lg-3">
                                        <div
                                                class="px-3 py-2 font-weight-bold"
                                                data-logo
                                                data-light-value="<?= settings()->main->logo_light != '' ? settings()->main->logo_light_full_url : settings()->main->title ?>"
                                                data-light-class="<?= settings()->main->logo_light != '' ? 'img-fluid navbar-logo-mini' : '' ?>"
                                                data-light-tag="<?= settings()->main->logo_light != '' ? 'img' : 'span' ?>"
                                                data-dark-value="<?= settings()->main->logo_dark != '' ? settings()->main->logo_dark_full_url : settings()->main->title ?>"
                                                data-dark-class="<?= settings()->main->logo_dark != '' ? 'img-fluid navbar-logo-mini' : '' ?>"
                                                data-dark-tag="<?= settings()->main->logo_dark != '' ? 'img' : 'span' ?>"
                                        >
                                            <?php if(settings()->main->{'logo_' . \Altum\ThemeStyle::get()} != ''): ?>
                                                <img src="<?= settings()->main->{'logo_' . \Altum\ThemeStyle::get() . '_full_url'} ?>" class="img-fluid navbar-logo-mini" alt="<?= l('global.accessibility.logo_alt') ?>" data-toggle="tooltip" title="<?= settings()->main->title ?>" />
                                            <?php else: ?>
                                                <?= settings()->main->title ?>
                                            <?php endif ?>
                                        </div>

                                        <div class="dropdown-divider"></div>

                                        <a class="dropdown-item <?= in_array(\Altum\Router::$controller, ['Campaigns', 'Campaign']) ? 'active' : null ?>" href="<?= url('campaigns') ?>"><i class="fas fa-fw fa-sm fa-pager mr-2"></i> <?= l('campaigns.menu') ?></a>

                                        <a class="dropdown-item <?= in_array(\Altum\Router::$controller, ['Statistics']) ? 'active' : null ?>" href="<?= url('statistics') ?>"><i class="fas fa-fw fa-sm fa-chart-bar mr-2"></i> <?= l('statistics.menu') ?></a>

                                        <a class="dropdown-item <?= in_array(\Altum\Router::$controller, ['NotificationHandlers', 'NotificationHandlerUpdate', 'NotificationHandlerCreate']) ? 'active' : null ?>" href="<?= url('notification-handlers') ?>"><i class="fas fa-fw fa-sm fa-bell mr-2"></i> <?= l('notification_handlers.menu') ?></a>

                                        <?php if(settings()->notifications->domains_is_enabled): ?>
                                            <a href="<?= url('domains') ?>" class="dropdown-item <?= in_array(\Altum\Router::$controller, ['Domains', 'DomainUpdate', 'DomainCreate']) ? 'active' : null ?>"><i class="fas fa-fw fa-globe fa-sm mr-2"></i> <?= l('domains.menu') ?></a>
                                        <?php endif ?>
                                    </div>

                                    <div>
                                        <?php if(!\Altum\Teams::is_delegated()): ?>
                                            <?php if(\Altum\Authentication::is_admin()): ?>
                                                <a class="dropdown-item" href="<?= url('admin') ?>"><i class="fas fa-fw fa-sm fa-fingerprint text-primary mr-2"></i> <?= l('global.menu.admin') ?></a>
                                                <div class="dropdown-divider"></div>
                                            <?php else: ?>
                                                <div class="px-3 py-2 font-weight-bold  d-flex align-items-center">
                                                    <img src="<?= get_user_avatar($this->user->avatar, $this->user->email) ?>" class="navbar-logo-mini rounded mr-2" loading="lazy" />
                                                    <div class="text-truncate d-inline-block"><?= $this->user->email ?></div>
                                                </div>

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
                                        <?php endif ?>

                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="<?= url('logout') ?>"><i class="fas fa-fw fa-sm fa-sign-out-alt mr-2"></i> <?= l('global.menu.logout') ?></a>
                                    </div>
                                </div>
                            </div>
                        </li>

                    <?php else: ?>

                        <li class="nav-item">
                            <a class="nav-link text-primary" href="<?= url('login') ?>">
                                <i class="fas fa-sign-in-alt fa-fw fa-sm mr-2"></i>
                                <?= l('login.menu') ?>
                            </a>
                        </li>

                        <?php if(settings()->users->register_is_enabled): ?>
                            <li class="nav-item">
                                <a class="nav-link text-primary" href="<?= url('register') ?>">
                                    <i class="fas fa-plus fa-fw fa-sm mr-2"></i>
                                    <?= l('register.menu') ?>
                                </a>
                            </li>
                        <?php endif ?>

                    <?php endif ?>

                </ul>
            </div>
        </div>
    </nav>
</div>

