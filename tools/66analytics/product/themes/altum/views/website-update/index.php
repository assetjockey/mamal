<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('websites') ?>"><?= l('websites.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('website_update.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="d-flex justify-content-between align-items-end mb-4">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-pager mr-1"></i> <?= l('website_update.header') ?></h1>

        <?= include_view(THEME_PATH . 'views/websites/website_dropdown_button.php', ['id' => $data->website->website_id, 'resource_name' => $data->website->name, ]) ?>
    </div>

    <div class="card">
        <div class="card-body">

            <form id="website_update" action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= $data->website->name ?>" required="required" />
                </div>

                <div class="form-group">
                    <label for="host"><i class="fas fa-fw fa-sm fa-pager text-muted mr-1"></i> <?= l('websites.host') ?></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <select name="scheme" class="appearance-none custom-select custom-select-lg form-control input-group-text">
                                <option value="https://" <?= $data->website->scheme == 'https://' ? 'selected="selected"' : null ?>>https://</option>
                                <option value="http://" <?= $data->website->scheme == 'http://' ? 'selected="selected"' : null ?>>http://</option>
                            </select>
                        </div>

                        <input id="host" type="text" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" name="host" value="<?= $data->website->host . $data->website->path ?>" placeholder="<?= l('global.host_placeholder') ?>" required="required" />
                    </div>
                    <?= \Altum\Alerts::output_field_error('host') ?>
                    <small class="form-text text-muted"><?= l('websites.host_help') ?></small>
                </div>

                <?php if(count($data->domains) && (settings()->analytics->domains_is_enabled || settings()->analytics->additional_domains_is_enabled)): ?>
                    <div class="form-group">
                        <label for="domain_id"><i class="fas fa-fw fa-sm fa-globe text-muted mr-1"></i> <?= l('websites.domain_id') ?></label>
                        <select id="domain_id" name="domain_id" class="custom-select">
                            <?php if(settings()->analytics->main_domain_is_enabled || \Altum\Authentication::is_admin()): ?>
                                <option value=" " <?= $data->website->domain_id ? null : 'selected="selected"' ?>><?= parse_url(SITE_URL, PHP_URL_HOST) ?></option>
                            <?php endif ?>

                            <?php foreach($data->domains as $row): ?>
                                <option value="<?= $row->domain_id ?>" <?= $data->website->domain_id && $data->website->domain_id == $row->domain_id ? 'selected="selected"' : null ?>><?= $row->host ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="form-text text-muted"><?= l('websites.domain_id_help') ?></small>
                    </div>
                <?php endif ?>

                <div class="form-group">
                    <label for="tracking_type"><i class="fas fa-fw fa-sm fa-chart-bar text-muted mr-1"></i> <?= l('websites.tracking_type') ?></label>
                    <select id="tracking_type" name="tracking_type" class="custom-select form-control-lg" disabled="disabled">
                        <option value="lightweight" <?= $data->website->tracking_type == 'lightweight' ? 'selected="selected"' : null ?>>🪶 <?= l('websites.tracking_type_lightweight') ?></option>
                        <option value="advanced" <?= $data->website->tracking_type == 'advanced' ? 'selected="selected"' : null ?>>🧠 <?= l('websites.tracking_type_advanced') ?></option>
                    </select>

                    <?php if($data->website->tracking_type == 'lightweight'): ?>
                        <small class="form-text text-muted"><?= l('websites.tracking_type_lightweight_help') ?></small>
                    <?php endif ?>

                    <?php if($data->website->tracking_type == 'advanced'): ?>
                        <small class="form-text text-muted"><?= l('websites.tracking_type_advanced_help') ?></small>
                    <?php endif ?>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input
                            type="checkbox"
                            class="custom-control-input"
                            name="is_enabled"
                            id="is_enabled"
                            <?= $data->website->is_enabled ? 'checked="checked"' : null ?>
                    >
                    <label class="custom-control-label" for="is_enabled"><?= l('websites.is_enabled') ?></label>
                    <small class="form-text text-muted"><?= l('websites.is_enabled_help') ?></small>
                </div>

                <div <?= $this->user->plan_settings->sessions_events_limit ? null : get_plan_feature_disabled_info() ?>>
                    <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->sessions_events_limit ? null : 'container-disabled' ?>">
                        <input
                                type="checkbox"
                                class="custom-control-input"
                                name="outbound_clicks_is_enabled"
                                id="outbound_clicks_is_enabled"
                                <?= $data->website->outbound_clicks_is_enabled ? 'checked="checked"' : null ?>
                                <?= $this->user->plan_settings->sessions_events_limit ? null : 'disabled="disabled"' ?>
                        >
                        <label class="custom-control-label" for="outbound_clicks_is_enabled"><?= l('websites.outbound_clicks_is_enabled') ?></label>
                        <small class="form-text text-muted"><?= l('websites.outbound_clicks_is_enabled_help') ?></small>
                    </div>
                </div>

                <?php if($data->website->tracking_type == 'advanced'): ?>
                    <div <?= $this->user->plan_settings->events_children_limit ? null : get_plan_feature_disabled_info() ?>>
                        <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->events_children_limit ? null : 'container-disabled' ?>">
                            <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    name="events_children_is_enabled"
                                    id="events_children_is_enabled"
                                    <?= $data->website->events_children_is_enabled ? 'checked="checked"' : null ?>
                                    <?= $this->user->plan_settings->events_children_limit ? null : 'disabled="disabled"' ?>
                            >
                            <label class="custom-control-label" for="events_children_is_enabled"><?= l('websites.events_children_is_enabled') ?></label>
                            <small class="form-text text-muted"><?= l('websites.events_children_is_enabled_help') ?></small>
                        </div>
                    </div>

                    <?php if(settings()->analytics->sessions_replays_is_enabled): ?>
                        <div <?= $this->user->plan_settings->sessions_replays_limit ? null : get_plan_feature_disabled_info() ?>>
                            <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->sessions_replays_limit ? null : 'container-disabled' ?>">
                                <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        name="sessions_replays_is_enabled"
                                        id="sessions_replays_is_enabled"
                                        <?= $data->website->sessions_replays_is_enabled ? 'checked="checked"' : null ?>
                                        <?= $this->user->plan_settings->sessions_replays_limit ? null : 'disabled="disabled"' ?>
                                />
                                <label class="custom-control-label" for="sessions_replays_is_enabled"><?= l('websites.sessions_replays_is_enabled') ?></label>
                                <small class="form-text text-muted"><?= l('websites.sessions_replays_is_enabled_help') ?></small>
                            </div>
                        </div>
                    <?php endif ?>
                <?php endif ?>

                <?php if(settings()->analytics->email_reports_is_enabled): ?>
                    <div <?= $this->user->plan_settings->email_reports_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                        <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->email_reports_is_enabled ? null : 'container-disabled' ?>">
                            <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    name="email_reports_is_enabled"
                                    id="email_reports_is_enabled"
                                    <?= $data->website->email_reports_is_enabled ? 'checked="checked"' : null ?>
                                    <?= $this->user->plan_settings->email_reports_is_enabled ? null : 'disabled="disabled"' ?>
                            >
                            <label class="custom-control-label" for="email_reports_is_enabled"><?= l('global.plan_settings.email_reports_is_enabled_' . settings()->analytics->email_reports_is_enabled) ?></label>
                            <small class="form-text text-muted"><?= l('websites.email_reports_is_enabled_help') ?></small>
                        </div>
                    </div>
                <?php endif ?>


                <button class="btn btn-sm btn-block btn-gray-200 my-3" type="button" data-toggle="collapse" data-target="#public_statistics_container" aria-expanded="false" aria-controls="public_statistics_container">
                    <i class="fas fa-fw fa-paper-plane fa-sm mr-1"></i> <?= l('websites.public_statistics_is_enabled') ?>
                </button>

                <div class="collapse" data-parent="#website_update" id="public_statistics_container">
                    <div class="form-group custom-control custom-switch">
                        <input
                                type="checkbox"
                                class="custom-control-input"
                                name="public_statistics_is_enabled"
                                id="public_statistics_is_enabled"
                                <?= $data->website->public_statistics_is_enabled ? 'checked="checked"' : null ?>
                        >
                        <label class="custom-control-label" for="public_statistics_is_enabled"><?= l('websites.public_statistics_is_enabled') ?></label>
                        <small class="form-text text-muted"><?= l('websites.public_statistics_is_enabled_help') ?></small>
                    </div>

                    <div class="form-group" data-password-toggle-view data-password-toggle-view-show="<?= l('global.show') ?>" data-password-toggle-view-hide="<?= l('global.hide') ?>" data-public-statistics-is-enabled-type="on">
                        <label for="public_statistics_password"><i class="fas fa-fw fa-sm fa-lock text-muted mr-1"></i> <?= l('websites.public_statistics_password') ?></label>
                        <input id="public_statistics_password" type="password" class="form-control" name="public_statistics_password" value="<?= $data->website->public_statistics_password ?>" maxlength="64" />
                        <small class="form-text text-muted"><?= l('websites.public_statistics_password_help') ?></small>
                    </div>
                </div>

                <button class="btn btn-sm btn-block btn-gray-200 my-3" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('websites.advanced') ?>
                </button>

                <div class="collapse" data-parent="#website_update" id="advanced_container">
                    <div class="form-group">
                        <label for="pixel_key"><i class="fas fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('websites.custom_pixel_key') ?></label>
                        <input id="pixel_key" type="text" class="form-control <?= \Altum\Alerts::has_field_errors('pixel_key') ? 'is-invalid' : null ?>" name="pixel_key" value="<?= $data->website->pixel_key ?>" maxlength="16" required="required" />
                        <?= \Altum\Alerts::output_field_error('pixel_key') ?>
                        <small class="form-text text-muted"><?= l('websites.custom_pixel_key_help') ?></small>
                    </div>

                    <div class="form-group custom-control custom-switch">
                        <input
                                type="checkbox"
                                class="custom-control-input"
                                name="bot_exclusion_is_enabled"
                                id="bot_exclusion_is_enabled"
                                <?= $data->website->bot_exclusion_is_enabled ? 'checked="checked"' : null ?>
                        >
                        <label class="custom-control-label" for="bot_exclusion_is_enabled"><?= l('websites.bot_exclusion_is_enabled') ?></label>
                        <small class="form-text text-muted"><?= l('websites.bot_exclusion_is_enabled_help') ?></small>
                    </div>

                    <div class="form-group custom-control custom-switch">
                        <input
                                type="checkbox"
                                class="custom-control-input"
                                name="query_parameters_tracking_is_enabled"
                                id="query_parameters_tracking_is_enabled"
                                <?= $data->website->query_parameters_tracking_is_enabled ? 'checked="checked"' : null ?>
                        >
                        <label class="custom-control-label" for="query_parameters_tracking_is_enabled"><?= l('websites.query_parameters_tracking_is_enabled') ?></label>
                        <small class="form-text text-muted"><?= l('websites.query_parameters_tracking_is_enabled_help') ?></small>
                    </div>

                    <?php if(settings()->analytics->ip_storage_is_enabled): ?>
                        <div class="form-group custom-control custom-switch" data-tracking-type="advanced">
                            <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    name="ip_storage_is_enabled"
                                    id="ip_storage_is_enabled"
                                    <?= $data->website->ip_storage_is_enabled ? 'checked="checked"' : null ?>
                            >
                            <label class="custom-control-label" for="ip_storage_is_enabled"><?= l('websites.ip_storage_is_enabled') ?></label>
                            <small class="form-text text-muted"><?= l('websites.ip_storage_is_enabled_help') ?></small>
                        </div>
                    <?php endif ?>

                    <div class="form-group">
                        <label for="excluded_ips"><i class="fas fa-fw fa-sm fa-eye-slash text-muted mr-1"></i> <?= l('websites.excluded_ips') ?></label>
                        <textarea id="excluded_ips" class="form-control" name="excluded_ips"><?= $data->website->excluded_ips ?></textarea>
                        <small class="form-text text-muted"><?= l('websites.excluded_ips_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="sessions_replays_hide_text_selector"><i class="fas fa-fw fa-sm fa-video-slash text-muted mr-1"></i> <?= l('websites.sessions_replays_hide_text_selector') ?></label>
                        <input id="sessions_replays_hide_text_selector" type="text" class="form-control" name="sessions_replays_hide_text_selector" value="<?= $data->website->sessions_replays_hide_text_selector ?>" maxlength="1024" placeholder="<?= l('websites.sessions_replays_hide_text_selector_placeholder') ?>" />
                        <small class="form-text text-muted"><?= l('websites.sessions_replays_hide_text_selector_help') ?></small>
                    </div>
                </div>

                <?php if(settings()->analytics->pixel_cache): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-fw fa-info-circle mr-1"></i>
                        <?= sprintf(l('website_update.info_message.cache'), nr(settings()->analytics->pixel_cache / 60)) ?>
                    </div>
                <?php endif ?>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.update') ?></button>
            </form>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    type_handler('[name="public_statistics_is_enabled"]', 'data-public-statistics-is-enabled-type');
    document.querySelector('[name="public_statistics_is_enabled"]') && document.querySelectorAll('[name="public_statistics_is_enabled"]').forEach(element => element.addEventListener('change', () => { type_handler('[name="public_statistics_is_enabled"]', 'data-public-statistics-is-enabled-type'); }));
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
