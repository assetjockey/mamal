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
                        <label for="websites_default_order_by"><i class="fas fa-fw fa-sm fa-pager text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('websites.title')) ?></label>
                        <select id="websites_default_order_by" name="websites_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('websites_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="website_id" <?= $this->user->preferences->websites_default_order_by == 'website_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->websites_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->websites_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->websites_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="host" <?= $this->user->preferences->websites_default_order_by == 'host' ? 'selected="selected"' : null ?>><?= l('websites.host') ?></option>
                            <option value="current_month_sessions_events" <?= $this->user->preferences->websites_default_order_by == 'current_month_sessions_events' ? 'selected="selected"' : null ?>><?= l('websites.sessions_events') ?></option>
                            <option value="last_24_hours_pageviews" <?= $this->user->preferences->websites_default_order_by == 'last_24_hours_pageviews' ? 'selected="selected"' : null ?>><?= l('websites.last_24_hours') ?></option>
                            <option value="last_7_days_pageviews" <?= $this->user->preferences->websites_default_order_by == 'last_7_days_pageviews' ? 'selected="selected"' : null ?>><?= l('websites.last_7_days') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('heatmaps_default_order_by') ?>
                    </div>

                    <div class="form-group">
                        <label for="websites_data_period"><i class="fas fa-fw fa-sm fa-chart-line text-muted mr-1"></i> <?= l('account_preferences.websites_data_period') ?></label>
                        <select id="websites_data_period" name="websites_data_period" class="custom-select <?= \Altum\Alerts::has_field_errors('websites_data_period') ? 'is-invalid' : null ?>">
                            <option value="current_month" <?= ($this->user->preferences->websites_data_period ?? 'current_month') == 'current_month' ? 'selected="selected"' : null ?>><?= l('websites.this_month') ?></option>
                            <option value="last_7_days" <?= ($this->user->preferences->websites_data_period ?? 'current_month') == 'last_7_days' ? 'selected="selected"' : null ?>><?= l('websites.last_7_days') ?></option>
                            <option value="last_24_hours" <?= ($this->user->preferences->websites_data_period ?? 'current_month') == 'last_24_hours' ? 'selected="selected"' : null ?>><?= l('websites.last_24_hours') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('websites_data_period') ?>
                    </div>

                    <?php if(settings()->analytics->websites_heatmaps_is_enabled): ?>
                        <div class="form-group">
                            <label for="heatmaps_default_order_by"><i class="fas fa-fw fa-sm fa-fire text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('heatmaps.title')) ?></label>
                            <select id="heatmaps_default_order_by" name="heatmaps_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('heatmaps_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="heatmap_id" <?= $this->user->preferences->heatmaps_default_order_by == 'heatmap_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->heatmaps_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $this->user->preferences->heatmaps_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="name" <?= $this->user->preferences->heatmaps_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                <option value="path" <?= $this->user->preferences->heatmaps_default_order_by == 'path' ? 'selected="selected"' : null ?>><?= l('heatmap_create_modal.path') ?></option>
                            </select>
                            <?= \Altum\Alerts::output_field_error('heatmaps_default_order_by') ?>
                        </div>
                    <?php endif ?>

                    <?php if(settings()->analytics->annotations_is_enabled): ?>
                        <div class="form-group">
                            <label for="annotations_default_order_by"><i class="fas fa-fw fa-sm fa-comments text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('annotations.title')) ?></label>
                            <select id="annotations_default_order_by" name="annotations_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('annotations_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="annotation_id" <?= $this->user->preferences->annotations_default_order_by == 'annotation_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->annotations_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $this->user->preferences->annotations_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="chart_datetime" <?= $this->user->preferences->annotations_default_order_by == 'chart_datetime' ? 'selected="selected"' : null ?>><?= l('annotations.chart_datetime') ?></option>
                                <option value="name" <?= $this->user->preferences->annotations_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            </select>
                            <?= \Altum\Alerts::output_field_error('annotations_default_order_by') ?>
                        </div>
                    <?php endif ?>

                    <?php if(1==2): ?>
                    <div class="form-group">
                        <label for="goals_default_order_by"><i class="fas fa-fw fa-sm fa-comments text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('goals.title')) ?></label>
                        <select id="goals_default_order_by" name="goals_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('goals_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="goal_id" <?= $this->user->preferences->goals_default_order_by == 'goal_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->goals_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->goals_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="path" <?= $this->user->preferences->goals_default_order_by == 'path' ? 'selected="selected"' : null ?>><?= l('goal_create_modal.path') ?></option>
                            <option value="key" <?= $this->user->preferences->goals_default_order_by == 'key' ? 'selected="selected"' : null ?>><?= l('goal_create_modal.key') ?></option>
                            <option value="name" <?= $this->user->preferences->goals_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('goals_default_order_by') ?>
                    </div>
                    <?php endif ?>

                    <?php if(settings()->analytics->domains_is_enabled): ?>
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
                    <?php endif ?>

                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('global.update') ?></button>
            </form>
        </div>
    </div>
</div>
