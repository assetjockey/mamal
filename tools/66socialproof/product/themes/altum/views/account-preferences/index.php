<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?= $this->views['account_header_menu'] ?>

    <div class="d-flex align-items-center mb-4">
        <h1 class="h4 m-0"><?= l('account_preferences.header') ?></h1>

        <div class="ml-2">
            <span data-toggle="tooltip" title="<?= l('account_preferences.subheader') ?>">
                <i class="fas fa-fw fa-info-circle text-muted"></i>
            </span>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <form id="account_preferences" action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <?php if(settings()->main->white_labeling_is_enabled): ?>
                    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#white_labeling_container" aria-expanded="false" aria-controls="white_labeling_container">
                        <i class="fas fa-fw fa-cube fa-sm mr-1"></i> <?= l('account_preferences.white_labeling') ?>
                    </button>

                    <div class="collapse" data-parent="#account_preferences" id="white_labeling_container">
                        <div <?= $this->user->plan_settings->white_labeling_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                            <div class="<?= $this->user->plan_settings->white_labeling_is_enabled ? null : 'container-disabled' ?>">
                                <div class="form-group">
                                    <label for="white_label_title"><i class="fas fa-fw fa-sm fa-heading text-muted mr-1"></i> <?= l('account_preferences.white_label_title') ?></label>
                                    <input type="text" id="white_label_title" name="white_label_title" class="form-control <?= \Altum\Alerts::has_field_errors('white_label_title') ? 'is-invalid' : null ?>" value="<?= $this->user->preferences->white_label_title ?>" maxlength="32" />
                                    <?= \Altum\Alerts::output_field_error('white_label_title') ?>
                                </div>

                                <div class="form-group">
                                    <label for="white_label_footer_description"><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('account_preferences.white_label_footer_description') ?></label>
                                    <input type="text" id="white_label_footer_description" name="white_label_footer_description" class="form-control <?= \Altum\Alerts::has_field_errors('white_label_footer_description') ? 'is-invalid' : null ?>" value="<?= $this->user->preferences->white_label_footer_description ?>" maxlength="256" />
                                    <?= \Altum\Alerts::output_field_error('white_label_footer_description') ?>
                                </div>

                                <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= get_max_upload() ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), get_max_upload()) ?>">
                                    <label for="white_label_logo_light"><i class="fas fa-fw fa-sm fa-sun text-muted mr-1"></i> <?= l('account_preferences.white_label_logo_light') ?></label>
                                    <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'users', 'file_key' => 'white_label_logo_light', 'already_existing_image' => $this->user->preferences->white_label_logo_light]) ?>
                                    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('users')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), get_max_upload()) ?></small>
                                </div>

                                <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= get_max_upload() ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), get_max_upload()) ?>">
                                    <label for="white_label_logo_dark"><i class="fas fa-fw fa-sm fa-moon text-muted mr-1"></i> <?= l('account_preferences.white_label_logo_dark') ?></label>
                                    <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'users', 'file_key' => 'white_label_logo_dark', 'already_existing_image' => $this->user->preferences->white_label_logo_dark]) ?>
                                    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('users')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), get_max_upload()) ?></small>
                                </div>

                                <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= get_max_upload() ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), get_max_upload()) ?>">
                                    <label for="white_label_favicon"><i class="fas fa-fw fa-sm fa-icons text-muted mr-1"></i> <?= l('account_preferences.white_label_favicon') ?></label>
                                    <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'users', 'file_key' => 'white_label_favicon', 'already_existing_image' => $this->user->preferences->white_label_favicon]) ?>
                                    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('users')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), get_max_upload()) ?></small>
                                </div>

                                <div class="form-group custom-control custom-switch">
                                    <input id="white_label_remove_socials" name="white_label_remove_socials" type="checkbox" class="custom-control-input" <?= $this->user->preferences->white_label_remove_socials ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label" for="white_label_remove_socials"><?= l('account_preferences.white_label_remove_socials') ?></label>
                                    <?= \Altum\Alerts::output_field_error('white_label_remove_socials') ?>
                                </div>

                                <div class="form-group custom-control custom-switch">
                                    <input id="white_label_remove_footer_links" name="white_label_remove_footer_links" type="checkbox" class="custom-control-input" <?= $this->user->preferences->white_label_remove_footer_links ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label" for="white_label_remove_footer_links"><?= l('account_preferences.white_label_remove_footer_links') ?></label>
                                    <?= \Altum\Alerts::output_field_error('white_label_remove_footer_links') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif ?>

                <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#default_settings_container" aria-expanded="false" aria-controls="default_settings_container">
                    <i class="fas fa-fw fa-wrench fa-sm mr-1"></i> <?= l('account_preferences.default_settings') ?>
                </button>

                <div class="collapse" data-parent="#account_preferences" id="default_settings_container">
                    <div class="form-group">
                        <label for="default_results_per_page"><i class="fas fa-fw fa-sm fa-list-ol text-muted mr-1"></i> <?= l('account_preferences.default_results_per_page') ?></label>
                        <select id="default_results_per_page" name="default_results_per_page" class="custom-select <?= \Altum\Alerts::has_field_errors('default_results_per_page') ? 'is-invalid' : null ?>">
                            <?php foreach([10, 25, 50, 100, 250, 500, 1000] as $key): ?>
                                <option value="<?= $key ?>" <?= ($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page) == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                            <?php endforeach ?>
                        </select>
                        <?= \Altum\Alerts::output_field_error('default_results_per_page') ?>
                    </div>

                    <div class="form-group">
                        <label for="default_order_type"><i class="fas fa-fw fa-sm fa-sort text-muted mr-1"></i> <?= l('account_preferences.default_order_type') ?></label>
                        <select id="default_order_type" name="default_order_type" class="custom-select <?= \Altum\Alerts::has_field_errors('default_order_type') ? 'is-invalid' : null ?>">
                            <option value="ASC" <?= ($this->user->preferences->default_order_type ?? settings()->main->default_order_type) == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                            <option value="DESC" <?= ($this->user->preferences->default_order_type ?? settings()->main->default_order_type) == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('default_order_type') ?>
                    </div>

                    <div class="form-group">
                        <label for="campaigns_default_order_by"><i class="fas fa-fw fa-sm fa-server text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('campaigns.title')) ?></label>
                        <select id="campaigns_default_order_by" name="campaigns_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('campaigns_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="campaign_id" <?= $this->user->preferences->campaigns_default_order_by == 'campaign_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->campaigns_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->campaigns_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->campaigns_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="domain" <?= $this->user->preferences->campaigns_default_order_by == 'domain' ? 'selected="selected"' : null ?>><?= l('campaigns.domain') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('campaigns_default_order_by') ?>
                    </div>

                    <div class="form-group">
                        <label for="notifications_default_order_by"><i class="fas fa-fw fa-sm fa-window-maximize text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('notifications.title')) ?></label>
                        <select id="notifications_default_order_by" name="notifications_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('notifications_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="notification_id" <?= $this->user->preferences->notifications_default_order_by == 'notification_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->notifications_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->notifications_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->notifications_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="impressions" <?= $this->user->preferences->notifications_default_order_by == 'impressions' ? 'selected="selected"' : null ?>><?= l('notification.statistics.impressions_chart') ?></option>
                            <option value="hovers" <?= $this->user->preferences->notifications_default_order_by == 'hovers' ? 'selected="selected"' : null ?>><?= l('notification.statistics.hovers_chart') ?></option>
                            <option value="clicks" <?= $this->user->preferences->notifications_default_order_by == 'clicks' ? 'selected="selected"' : null ?>><?= l('notification.statistics.clicks_chart') ?></option>
                            <option value="form_submissions" <?= $this->user->preferences->notifications_default_order_by == 'form_submissions' ? 'selected="selected"' : null ?>><?= l('notification.statistics.form_submissions_chart') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('notifications_default_order_by') ?>
                    </div>

                    <div class="form-group">
                        <label for="notification_handlers_default_order_by"><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('notification_handlers.title')) ?></label>
                        <select id="notification_handlers_default_order_by" name="notification_handlers_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('notification_handlers_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="notification_handler_id" <?= $this->user->preferences->notification_handlers_default_order_by == 'notification_handler_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->notification_handlers_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->notification_handlers_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->notification_handlers_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('notification_handlers_default_order_by') ?>
                    </div>

                    <div class="form-group">
                        <label for="domains_default_order_by"><i class="fas fa-fw fa-sm fa-globe text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('domains.title')) ?></label>
                        <select id="domains_default_order_by" name="domains_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('domains_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="domain_id" <?= $this->user->preferences->domains_default_order_by == 'domain_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->domains_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->domains_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="host" <?= $this->user->preferences->domains_default_order_by == 'host' ? 'selected="selected"' : null ?>><?= l('domains.table.host') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('domains_default_order_by') ?>
                    </div>
                </div>

                <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#tracking_settings_container" aria-expanded="false" aria-controls="tracking_settings_container">
                    <i class="fas fa-fw fa-eye fa-sm mr-1"></i> <?= l('account_preferences.tracking_settings') ?>
                </button>

                <div class="collapse" data-parent="#account_preferences" id="tracking_settings_container">
                    <div class="form-group" data-character-counter="textarea">
                        <label for="excluded_ips" class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-fw fa-sm fa-eye-slash text-muted mr-1"></i> <?= l('account_preferences.excluded_ips') ?></span>
                            <small class="text-muted" data-character-counter-wrapper></small>
                        </label>
                        <textarea id="excluded_ips" class="form-control" name="excluded_ips" maxlength="500"><?= implode(',', $this->user->preferences->excluded_ips ?? []) ?></textarea>
                        <small class="form-text text-muted"><?= l('account_preferences.excluded_ips_help') ?></small>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('global.update') ?></button>
            </form>
        </div>
    </div>
</div>

