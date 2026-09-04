<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?= $this->views['account_header_menu'] ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-end mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><?= l('account_api.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('account_api.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-lg-auto d-flex flex-wrap gap-3 d-print-none">
            <a href="<?= url('api-documentation') ?>" class="btn btn-primary"><i class="fas fa-fw fa-book fa-sm mr-1"></i> <?= l('api_documentation.menu') ?></a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div <?= $this->user->plan_settings->api_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                    <div class="form-group <?= $this->user->plan_settings->api_is_enabled ? null : 'container-disabled' ?>">
                        <label for="api_key"><i class="fas fa-fw fa-sm fa-code text-muted mr-1"></i> <?= l('account_api.api_key') ?></label>
                        <div class="input-group">
                            <?php
                            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) $this->user->api_key = 'hidden on demo';
                            ?>

                            <input type="text" id="api_key" name="api_key" value="<?= $this->user->api_key ?>" class="form-control" onclick="this.select();" readonly="readonly" />

                            <div class="input-group-append">
                                <button
                                        id="url_copy"
                                        type="button"
                                        class="btn btn-light border border-left-0"
                                        data-toggle="tooltip"
                                        title="<?= l('global.clipboard_copy') ?>"
                                        aria-label="<?= l('global.clipboard_copy') ?>"
                                        data-copy="<?= l('global.clipboard_copy') ?>"
                                        data-copied="<?= l('global.clipboard_copied') ?>"
                                        data-clipboard-text="<?= $this->user->api_key ?>"
                                >
                                    <i class="fas fa-fw fa-sm fa-copy"></i>
                                </button>
                            </div>

                            <div class="input-group-append">
                                <button
                                        type="button"
                                        class="btn btn-light border"
                                        data-tooltip
                                        title="<?= l('account_api.button') ?>"
                                        <?= $this->user->plan_settings->api_is_enabled ? 'data-target="#api_key_regenerate_modal" data-toggle="modal"' : get_plan_feature_disabled_info() ?>
                                >
                                    <i class="fas fa-fw fa-sm fa-refresh"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/account-api/api_key_regenerate_modal.php'), 'modals', 'api_key_regenerate_modal'); ?>
            </form>

        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/user') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-user text-primary-600"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body d-flex align-items-center font-weight-450">
                    <?= l('api_documentation.user') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/websites') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-pager text-primary-600"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body d-flex align-items-center font-weight-450">
                    <?= l('api_documentation.websites') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/pageviews-lightweight') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-eye text-primary-600"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body d-flex flex-column justify-content-center align-items-start font-weight-450">
                    <div><?= l('pageviews.title') ?></div>
                    <small class="text-muted"><?= l('websites.tracking_type_lightweight') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/pageviews-advanced') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-eye text-primary-600"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body d-flex flex-column justify-content-center align-items-start font-weight-450">
                    <div><?= l('pageviews.title') ?></div>
                    <small class="text-muted"><?= l('websites.tracking_type_advanced') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/sessions') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-stream text-primary-600"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body d-flex flex-column justify-content-center align-items-start font-weight-450">
                    <div><?= l('analytics.sessions') ?></div>
                    <small class="text-muted"><?= l('websites.tracking_type_advanced') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/events-children') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-mouse-pointer text-primary-600"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body d-flex flex-column justify-content-center align-items-start font-weight-450">
                    <div><?= l('api_documentation.events_children') ?></div>
                    <small class="text-muted"><?= l('websites.tracking_type_advanced') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/outbound-clicks') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-external-link-alt text-primary-600"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body d-flex flex-column justify-content-center align-items-start font-weight-450">
                    <div><?= l('outbound_clicks.title') ?></div>
                    <small class="text-muted"><?= l('websites.tracking_type_advanced') ?> / <?= l('websites.tracking_type_lightweight') ?></small>
                </div>
            </div>
        </div>

        <?php if(settings()->analytics->sessions_replays_is_enabled): ?>
            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/replays') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-video text-primary-600"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body d-flex flex-column justify-content-center align-items-start font-weight-450">
                        <div><?= l('replays.title') ?></div>
                        <small class="text-muted"><?= l('websites.tracking_type_advanced') ?></small>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/visitors') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-users text-primary-600"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body d-flex flex-column justify-content-center align-items-start font-weight-450">
                    <div><?= l('visitors.title') ?></div>
                    <small class="text-muted"><?= l('websites.tracking_type_advanced') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/goals') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-bullseye text-primary-600"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body d-flex flex-column justify-content-center align-items-start font-weight-450">
                    <div><?= l('goals.title') ?></div>
                    <small class="text-muted"><?= l('websites.tracking_type_advanced') ?> / <?= l('websites.tracking_type_lightweight') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/goals-conversions') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-bullseye text-primary-600"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body d-flex flex-column justify-content-center align-items-start font-weight-450">
                    <div><?= l('goals_conversions.title') ?></div>
                    <small class="text-muted"><?= l('websites.tracking_type_advanced') ?> / <?= l('websites.tracking_type_lightweight') ?></small>
                </div>
            </div>
        </div>

        <?php if(settings()->analytics->websites_heatmaps_is_enabled): ?>
            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/heatmaps') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-fire text-primary-600"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body d-flex flex-column justify-content-center align-items-start font-weight-450">
                        <div><?= l('heatmaps.title') ?></div>
                        <small class="text-muted"><?= l('websites.tracking_type_advanced') ?></small>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <?php if(settings()->analytics->annotations_is_enabled): ?>
            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/annotations') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-comments text-primary-600"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body d-flex flex-column justify-content-center align-items-start font-weight-450">
                        <div><?= l('annotations.title') ?></div>
                        <small class="text-muted"><?= l('websites.tracking_type_advanced') ?> / <?= l('websites.tracking_type_lightweight') ?></small>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/statistics') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-chart-bar text-primary-600"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body d-flex flex-column justify-content-center align-items-start font-weight-450">
                    <div><?= l('api_documentation.statistics') ?></div>
                    <small class="text-muted"><?= l('websites.tracking_type_advanced') ?> / <?= l('websites.tracking_type_lightweight') ?></small>
                </div>
            </div>
        </div>

        <?php if(settings()->analytics->domains_is_enabled): ?>
            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/domains') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-globe text-primary-600"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body d-flex align-items-center font-weight-450">
                        <?= l('domains.title') ?>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <?php if(\Altum\Plugin::is_active('teams')): ?>
            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/teams') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-user-cog text-primary-600"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body d-flex align-items-center font-weight-450">
                        <?= l('teams.title') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/team-members') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-users-cog text-primary-600"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body d-flex align-items-center font-weight-450">
                        <?= l('api_documentation.team_members') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/teams-member') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-user-tag text-primary-600"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body d-flex align-items-center font-weight-450">
                        <?= l('api_documentation.teams_member') ?>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <?php if(settings()->payment->is_enabled): ?>
            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/payments') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-credit-card text-primary-600"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body d-flex align-items-center font-weight-450">
                        <?= l('account_payments.title') ?>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <?php if(\Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled): ?>
            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/referrals-users') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-user-friends text-primary-600"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body d-flex align-items-center font-weight-450">
                        <?= l('referrals_users.title') ?>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/users-logs') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-scroll text-primary-600"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body d-flex align-items-center font-weight-450">
                    <?= l('account_logs.title') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>
