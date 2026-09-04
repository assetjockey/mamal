<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?= $this->views['account_header_menu'] ?>

    <div class="d-flex align-items-center mb-3">
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
                        <label for="transfers_default_order_by"><i class="fas fa-fw fa-sm fa-paper-plane text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('transfers.title')) ?></label>
                        <select id="transfers_default_order_by" name="transfers_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('transfers_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="transfer_id" <?= $this->user->preferences->transfers_default_order_by == 'transfer_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->transfers_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->transfers_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="expiration_datetime" <?= $this->user->preferences->transfers_default_order_by == 'expiration_datetime' ? 'selected="selected"' : null ?>><?= l('transfers.expiration_datetime') ?></option>
                            <option value="pageviews" <?= $this->user->preferences->transfers_default_order_by == 'pageviews' ? 'selected="selected"' : null ?>><?= l('transfer.pageviews') ?></option>
                            <option value="downloads" <?= $this->user->preferences->transfers_default_order_by == 'downloads' ? 'selected="selected"' : null ?>><?= l('transfer.downloads') ?></option>
                            <option value="url" <?= $this->user->preferences->transfers_default_order_by == 'url' ? 'selected="selected"' : null ?>><?= l('transfer.url') ?></option>
                            <option value="name" <?= $this->user->preferences->transfers_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="downloads_limit" <?= $this->user->preferences->transfers_default_order_by == 'downloads_limit' ? 'selected="selected"' : null ?>><?= l('transfer.downloads_limit') ?></option>
                            <option value="total_files" <?= $this->user->preferences->transfers_default_order_by == 'total_files' ? 'selected="selected"' : null ?>><?= l('transfer.total_files') ?></option>
                            <option value="total_size" <?= $this->user->preferences->transfers_default_order_by == 'total_size' ? 'selected="selected"' : null ?>><?= l('transfer.total_size') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('transfers_default_order_by') ?>
                    </div>

                    <?php if(settings()->transfers->transfer_requests_is_enabled): ?>
                        <div class="form-group">
                            <label for="transfer_requests_default_order_by"><i class="fas fa-fw fa-sm fa-inbox text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('transfer_requests.title')) ?></label>
                            <select id="transfer_requests_default_order_by" name="transfer_requests_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('transfer_requests_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="transfer_request_id" <?= $this->user->preferences->transfer_requests_default_order_by == 'transfer_request_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->transfer_requests_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $this->user->preferences->transfer_requests_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="expiration_datetime" <?= $this->user->preferences->transfer_requests_default_order_by == 'expiration_datetime' ? 'selected="selected"' : null ?>><?= l('transfer_requests.expiration_datetime') ?></option>
                                <option value="last_submitted_datetime" <?= $this->user->preferences->transfer_requests_default_order_by == 'last_submitted_datetime' ? 'selected="selected"' : null ?>><?= l('transfer_requests.last_submitted_datetime') ?></option>
                                <option value="pageviews" <?= $this->user->preferences->transfer_requests_default_order_by == 'pageviews' ? 'selected="selected"' : null ?>><?= l('transfer.pageviews') ?></option>
                                <option value="url" <?= $this->user->preferences->transfer_requests_default_order_by == 'url' ? 'selected="selected"' : null ?>><?= l('transfer.url') ?></option>
                                <option value="name" <?= $this->user->preferences->transfer_requests_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                <option value="total_submissions" <?= $this->user->preferences->transfer_requests_default_order_by == 'total_submissions' ? 'selected="selected"' : null ?>><?= l('transfer_requests.total_submissions') ?></option>
                                <option value="total_files" <?= $this->user->preferences->transfer_requests_default_order_by == 'total_files' ? 'selected="selected"' : null ?>><?= l('transfer.total_files') ?></option>
                                <option value="total_size" <?= $this->user->preferences->transfer_requests_default_order_by == 'total_size' ? 'selected="selected"' : null ?>><?= l('transfer.total_size') ?></option>
                            </select>
                            <?= \Altum\Alerts::output_field_error('transfer_requests_default_order_by') ?>
                        </div>
                    <?php endif ?>

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

                    <?php if(settings()->transfers->domains_is_enabled): ?>
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

                    <?php if(settings()->transfers->projects_is_enabled): ?>
                        <div class="form-group">
                            <label for="projects_default_order_by"><i class="fas fa-fw fa-sm fa-project-diagram text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('projects.title')) ?></label>
                            <select id="projects_default_order_by" name="projects_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('projects_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="project_id" <?= $this->user->preferences->projects_default_order_by == 'project_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->projects_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $this->user->preferences->projects_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="name" <?= $this->user->preferences->projects_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            </select>
                            <?= \Altum\Alerts::output_field_error('projects_default_order_by') ?>
                        </div>
                    <?php endif ?>
                </div>

                <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#default_transfer_settings_container" aria-expanded="false" aria-controls="default_transfer_settings_container">
                    <i class="fas fa-fw fa-paper-plane fa-sm mr-1"></i> <?= l('account_preferences.default_transfer_settings') ?>
                </button>

                <div class="collapse" data-parent="#account_preferences" id="default_transfer_settings_container">
                    <div class="form-group">
                        <label for="transfers_default_type"><i class="fas fa-fw fa-sm fa-paper-plane text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfers.title'), l('global.type')) ?></label>
                        <select id="transfers_default_type" name="transfers_default_type" class="custom-select <?= \Altum\Alerts::has_field_errors('transfers_default_type') ? 'is-invalid' : null ?>">
                            <option value="link" <?= $this->user->preferences->transfers_default_type == 'link' ? 'selected="selected"' : null ?>><?= l('transfer.type.link') ?></option>
                            <?php if(settings()->transfers->email_transfer_is_enabled): ?>
                                <option value="email" <?= $this->user->preferences->transfers_default_type == 'email' ? 'selected="selected"' : null ?>><?= l('transfer.type.email') ?></option>
                            <?php endif ?>
                        </select>
                        <?= \Altum\Alerts::output_field_error('transfers_default_type') ?>
                    </div>

                    <div class="form-group">
                        <label for="transfers_default_downloads_limit"><i class="fas fa-fw fa-sm fa-download text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfers.title'), l('transfer.downloads_limit')) ?></label>
                        <input type="number" min="0" id="transfers_default_downloads_limit" name="transfers_default_downloads_limit" class="form-control <?= \Altum\Alerts::has_field_errors('transfers_default_downloads_limit') ? 'is-invalid' : null ?>" value="<?= $this->user->preferences->transfers_default_downloads_limit ?>" />
                        <?= \Altum\Alerts::output_field_error('transfers_default_downloads_limit') ?>
                        <?php if($this->user->plan_settings->downloads_per_transfer_limit == -1): ?>
                            <small class="form-text text-muted"><?= l('transfer.downloads_limit_help') ?></small>
                        <?php endif ?>
                    </div>

                    <div class="form-group">
                        <label for="transfers_default_expiration_datetime"><i class="fas fa-fw fa-sm fa-hourglass-half text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfers.title'), l('account_preferences.expiration_datetime')) ?></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">+</span>
                            </div>

                            <input type="number" min="0" id="transfers_default_expiration_datetime" name="transfers_default_expiration_datetime" class="form-control <?= \Altum\Alerts::has_field_errors('transfers_default_expiration_datetime') ? 'is-invalid' : null ?>" value="<?= $this->user->preferences->transfers_default_expiration_datetime ?>" />

                            <div class="input-group-append">
                                <span class="input-group-text"><?= l('global.date.days') ?></span>
                            </div>
                        </div>
                        <?= \Altum\Alerts::output_field_error('transfers_default_expiration_datetime') ?>
                        <?php if($this->user->plan_settings->transfers_retention == -1): ?>
                            <small class="form-text text-muted"><?= l('transfer.expiration_datetime_help') ?></small>
                        <?php endif ?>
                    </div>

                    <div class="form-group custom-control custom-switch">
                        <input id="transfers_default_file_preview_is_enabled" name="transfers_default_file_preview_is_enabled" type="checkbox" class="custom-control-input" <?= $this->user->preferences->transfers_default_file_preview_is_enabled ? 'checked="checked"' : null ?>>
                        <label class="custom-control-label" for="transfers_default_file_preview_is_enabled"><?= sprintf(l('account_preferences.x_default_y'), l('transfers.title'), l('transfer.file_preview')) ?></label>
                        <?= \Altum\Alerts::output_field_error('transfers_default_file_preview_is_enabled') ?>
                    </div>

                    <div class="form-group custom-control custom-switch">
                        <input id="transfers_default_gallery_file_preview_is_enabled" name="transfers_default_gallery_file_preview_is_enabled" type="checkbox" class="custom-control-input" <?= $this->user->preferences->transfers_default_gallery_file_preview_is_enabled ? 'checked="checked"' : null ?>>
                        <label class="custom-control-label" for="transfers_default_gallery_file_preview_is_enabled"><?= sprintf(l('account_preferences.x_default_y'), l('transfers.title'), l('transfer.gallery_file_preview')) ?></label>
                        <?= \Altum\Alerts::output_field_error('transfers_default_gallery_file_preview_is_enabled') ?>
                    </div>

                    <?php if(settings()->transfers->pixels_is_enabled): ?>
                        <div class="form-group">
                            <label for="transfers_default_pixels_ids"><i class="fas fa-fw fa-sm fa-adjust text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfers.title'), l('transfer.pixels')) ?></label>
                            <select id="transfers_default_pixels_ids" name="transfers_default_pixels_ids[]" class="custom-select <?= \Altum\Alerts::has_field_errors('transfers_default_pixels_ids') ? 'is-invalid' : null ?>" multiple="multiple">
                                <?php foreach($data->pixels as $pixel): ?>
                                    <option value="<?= $pixel->pixel_id ?>" <?= in_array($pixel->pixel_id, $this->user->preferences->transfers_default_pixels_ids ?? []) ? 'selected="selected"' : null ?>><?= $pixel->name ?></option>
                                <?php endforeach ?>
                            </select>
                            <?= \Altum\Alerts::output_field_error('transfers_default_pixels_ids') ?>
                            <?php if(get_this_device_type() == 'desktop'): ?><small class="form-text text-muted"><?= l('global.select_multiple_help') ?></small><?php endif ?>
                        </div>
                    <?php endif ?>

                    <div class="form-group">
                        <label for="transfers_default_project_id"><i class="fas fa-fw fa-sm fa-project-diagram text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfers.title'), l('projects.project_id')) ?></label>
                        <select id="transfers_default_project_id" name="transfers_default_project_id" class="custom-select <?= \Altum\Alerts::has_field_errors('transfers_default_project_id') ? 'is-invalid' : null ?>">
                            <option value=" " <?= !$this->user->preferences->transfers_default_project_id ? 'selected="selected"' : null ?>><?= l('global.none') ?></option>
                            <?php foreach($data->projects as $project): ?>
                                <option value="<?= $project->project_id ?>" <?= $project->project_id == $this->user->preferences->transfers_default_project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                            <?php endforeach ?>
                        </select>
                        <?= \Altum\Alerts::output_field_error('transfers_default_project_id') ?>
                    </div>

                    <div class="form-group custom-control custom-switch">
                        <input id="transfers_default_is_removed_branding" name="transfers_default_is_removed_branding" type="checkbox" class="custom-control-input" <?= $this->user->preferences->transfers_default_is_removed_branding ? 'checked="checked"' : null ?>>
                        <label class="custom-control-label" for="transfers_default_is_removed_branding"><?= sprintf(l('account_preferences.x_default_y'), l('transfers.title'), l('transfer.is_removed_branding')) ?></label>
                        <?= \Altum\Alerts::output_field_error('transfers_default_is_removed_branding') ?>
                    </div>

                    <div class="form-group" data-character-counter="textarea">
                        <label for="transfers_default_custom_css" class="d-flex justify-content-between align-items-center">
                            <span><i class="fab fa-fw fa-sm fa-css3 text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfers.title'), l('global.custom_css')) ?></span>
                            <small class="text-muted" data-character-counter-wrapper></small>
                        </label>
                        <textarea id="transfers_default_custom_css" name="transfers_default_custom_css" class="form-control <?= \Altum\Alerts::has_field_errors('transfers_default_custom_css') ? 'is-invalid' : null ?>" maxlength="10000"><?= $this->user->preferences->transfers_default_custom_css ?></textarea>
                        <?= \Altum\Alerts::output_field_error('transfers_default_custom_css') ?>
                    </div>

                    <div class="form-group" data-character-counter="textarea">
                        <label for="transfers_default_custom_js" class="d-flex justify-content-between align-items-center">
                            <span><i class="fab fa-fw fa-sm fa-js text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfers.title'), l('global.custom_js')) ?></span>
                            <small class="text-muted" data-character-counter-wrapper></small>
                        </label>
                        <textarea id="transfers_default_custom_js" name="transfers_default_custom_js" class="form-control <?= \Altum\Alerts::has_field_errors('transfers_default_custom_js') ? 'is-invalid' : null ?>" maxlength="10000"><?= $this->user->preferences->transfers_default_custom_js ?></textarea>
                        <?= \Altum\Alerts::output_field_error('transfers_default_custom_js') ?>
                    </div>

                    <?php if(settings()->notification_handlers->is_enabled): ?>
                        <div class="form-group">
                            <label for="transfers_default_download_notification_handlers_ids"><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfers.title'), l('transfer.download_notification_handlers')) ?></label>
                            <select id="transfers_default_download_notification_handlers_ids" name="transfers_default_download_notification_handlers_ids[]" class="custom-select <?= \Altum\Alerts::has_field_errors('transfers_default_download_notification_handlers_ids') ? 'is-invalid' : null ?>" multiple="multiple">
                                <?php foreach($data->notification_handlers as $notification_handler): ?>
                                    <option value="<?= $notification_handler->notification_handler_id ?>" <?= in_array($notification_handler->notification_handler_id, $this->user->preferences->transfers_default_download_notification_handlers_ids ?? []) ? 'selected="selected"' : null ?>><?= $notification_handler->name ?></option>
                                <?php endforeach ?>
                            </select>
                            <?= \Altum\Alerts::output_field_error('transfers_default_download_notification_handlers_ids') ?>
                            <?php if(get_this_device_type() == 'desktop'): ?><small class="form-text text-muted"><?= l('global.select_multiple_help') ?></small><?php endif ?>
                        </div>

                        <div class="form-group">
                            <label for="transfers_default_pageview_notification_handlers_ids"><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfers.title'), l('transfer.pageview_notification_handlers')) ?></label>
                            <select id="transfers_default_pageview_notification_handlers_ids" name="transfers_default_pageview_notification_handlers_ids[]" class="custom-select <?= \Altum\Alerts::has_field_errors('transfers_default_pageview_notification_handlers_ids') ? 'is-invalid' : null ?>" multiple="multiple">
                                <?php foreach($data->notification_handlers as $notification_handler): ?>
                                    <option value="<?= $notification_handler->notification_handler_id ?>" <?= in_array($notification_handler->notification_handler_id, $this->user->preferences->transfers_default_pageview_notification_handlers_ids ?? []) ? 'selected="selected"' : null ?>><?= $notification_handler->name ?></option>
                                <?php endforeach ?>
                            </select>
                            <?= \Altum\Alerts::output_field_error('transfers_default_pageview_notification_handlers_ids') ?>
                            <?php if(get_this_device_type() == 'desktop'): ?><small class="form-text text-muted"><?= l('global.select_multiple_help') ?></small><?php endif ?>
                        </div>
                    <?php endif ?>

                    <div class="form-group custom-control custom-switch">
                        <input id="transfers_auto_file_upload" name="transfers_auto_file_upload" type="checkbox" class="custom-control-input" <?= $this->user->preferences->transfers_auto_file_upload ? 'checked="checked"' : null ?>>
                        <label class="custom-control-label" for="transfers_auto_file_upload"><?= l('account_preferences.transfers_auto_file_upload') ?></label>
                        <?= \Altum\Alerts::output_field_error('transfers_auto_file_upload') ?>
                    </div>

                    <div class="form-group custom-control custom-switch">
                        <input id="transfers_auto_transfer_create" name="transfers_auto_transfer_create" type="checkbox" class="custom-control-input" <?= $this->user->preferences->transfers_auto_transfer_create ? 'checked="checked"' : null ?>>
                        <label class="custom-control-label" for="transfers_auto_transfer_create"><?= l('account_preferences.transfers_auto_transfer_create') ?></label>
                        <?= \Altum\Alerts::output_field_error('transfers_auto_transfer_create') ?>
                    </div>

                    <div class="form-group custom-control custom-switch">
                        <input id="transfers_auto_copy_link" name="transfers_auto_copy_link" type="checkbox" class="custom-control-input" <?= $this->user->preferences->transfers_auto_copy_link ? 'checked="checked"' : null ?>>
                        <label class="custom-control-label" for="transfers_auto_copy_link"><?= l('account_preferences.transfers_auto_copy_link') ?></label>
                        <?= \Altum\Alerts::output_field_error('transfers_auto_copy_link') ?>
                    </div>
                </div>

                <?php if(settings()->transfers->transfer_requests_is_enabled): ?>
                    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#default_transfer_request_settings_container" aria-expanded="false" aria-controls="default_transfer_request_settings_container">
                        <i class="fas fa-fw fa-inbox fa-sm mr-1"></i> <?= l('account_preferences.default_transfer_request_settings') ?>
                    </button>

                    <div class="collapse" data-parent="#account_preferences" id="default_transfer_request_settings_container">
                        <div class="form-group">
                            <label for="transfer_requests_default_expiration_datetime"><i class="fas fa-fw fa-sm fa-hourglass-half text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfer_requests.title'), l('account_preferences.expiration_datetime')) ?></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">+</span>
                                </div>

                                <input type="number" min="0" id="transfer_requests_default_expiration_datetime" name="transfer_requests_default_expiration_datetime" class="form-control <?= \Altum\Alerts::has_field_errors('transfer_requests_default_expiration_datetime') ? 'is-invalid' : null ?>" value="<?= $this->user->preferences->transfer_requests_default_expiration_datetime ?>" />

                                <div class="input-group-append">
                                    <span class="input-group-text"><?= l('global.date.days') ?></span>
                                </div>
                            </div>
                            <?= \Altum\Alerts::output_field_error('transfer_requests_default_expiration_datetime') ?>
                            <?php if($this->user->plan_settings->transfers_retention == -1): ?>
                                <small class="form-text text-muted"><?= l('transfer.expiration_datetime_help') ?></small>
                            <?php endif ?>
                        </div>

                        <?php if(settings()->transfers->pixels_is_enabled): ?>
                            <div class="form-group">
                                <label for="transfer_requests_default_pixels_ids"><i class="fas fa-fw fa-sm fa-adjust text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfer_requests.title'), l('transfer.pixels')) ?></label>
                                <select id="transfer_requests_default_pixels_ids" name="transfer_requests_default_pixels_ids[]" class="custom-select <?= \Altum\Alerts::has_field_errors('transfer_requests_default_pixels_ids') ? 'is-invalid' : null ?>" multiple="multiple">
                                    <?php foreach($data->pixels as $pixel): ?>
                                        <option value="<?= $pixel->pixel_id ?>" <?= in_array($pixel->pixel_id, $this->user->preferences->transfer_requests_default_pixels_ids ?? []) ? 'selected="selected"' : null ?>><?= $pixel->name ?></option>
                                    <?php endforeach ?>
                                </select>
                                <?= \Altum\Alerts::output_field_error('transfer_requests_default_pixels_ids') ?>
                                <?php if(get_this_device_type() == 'desktop'): ?><small class="form-text text-muted"><?= l('global.select_multiple_help') ?></small><?php endif ?>
                            </div>
                        <?php endif ?>

                        <div class="form-group">
                            <label for="transfer_requests_default_project_id"><i class="fas fa-fw fa-sm fa-project-diagram text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfer_requests.title'), l('projects.project_id')) ?></label>
                            <select id="transfer_requests_default_project_id" name="transfer_requests_default_project_id" class="custom-select <?= \Altum\Alerts::has_field_errors('transfer_requests_default_project_id') ? 'is-invalid' : null ?>">
                                <option value=" " <?= !$this->user->preferences->transfer_requests_default_project_id ? 'selected="selected"' : null ?>><?= l('global.none') ?></option>
                                <?php foreach($data->projects as $project): ?>
                                    <option value="<?= $project->project_id ?>" <?= $project->project_id == $this->user->preferences->transfer_requests_default_project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                                <?php endforeach ?>
                            </select>
                            <?= \Altum\Alerts::output_field_error('transfer_requests_default_project_id') ?>
                        </div>

                        <div class="form-group custom-control custom-switch">
                            <input id="transfer_requests_default_is_removed_branding" name="transfer_requests_default_is_removed_branding" type="checkbox" class="custom-control-input" <?= $this->user->preferences->transfer_requests_default_is_removed_branding ? 'checked="checked"' : null ?>>
                            <label class="custom-control-label" for="transfer_requests_default_is_removed_branding"><?= sprintf(l('account_preferences.x_default_y'), l('transfer_requests.title'), l('transfer.is_removed_branding')) ?></label>
                            <?= \Altum\Alerts::output_field_error('transfer_requests_default_is_removed_branding') ?>
                        </div>

                        <div class="form-group" data-character-counter="textarea">
                            <label for="transfer_requests_default_custom_css" class="d-flex justify-content-between align-items-center">
                                <span><i class="fab fa-fw fa-sm fa-css3 text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfer_requests.title'), l('global.custom_css')) ?></span>
                                <small class="text-muted" data-character-counter-wrapper></small>
                            </label>
                            <textarea id="transfer_requests_default_custom_css" name="transfer_requests_default_custom_css" class="form-control <?= \Altum\Alerts::has_field_errors('transfer_requests_default_custom_css') ? 'is-invalid' : null ?>" maxlength="10000"><?= $this->user->preferences->transfer_requests_default_custom_css ?></textarea>
                            <?= \Altum\Alerts::output_field_error('transfer_requests_default_custom_css') ?>
                        </div>

                        <div class="form-group" data-character-counter="textarea">
                            <label for="transfer_requests_default_custom_js" class="d-flex justify-content-between align-items-center">
                                <span><i class="fab fa-fw fa-sm fa-js text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfer_requests.title'), l('global.custom_js')) ?></span>
                                <small class="text-muted" data-character-counter-wrapper></small>
                            </label>
                            <textarea id="transfer_requests_default_custom_js" name="transfer_requests_default_custom_js" class="form-control <?= \Altum\Alerts::has_field_errors('transfer_requests_default_custom_js') ? 'is-invalid' : null ?>" maxlength="10000"><?= $this->user->preferences->transfer_requests_default_custom_js ?></textarea>
                            <?= \Altum\Alerts::output_field_error('transfer_requests_default_custom_js') ?>
                        </div>

                        <?php if(settings()->notification_handlers->is_enabled): ?>
                            <div class="form-group">
                                <label for="transfer_requests_default_submission_notification_handlers_ids"><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfer_requests.title'), l('transfer.submission_notification_handlers')) ?></label>
                                <select id="transfer_requests_default_submission_notification_handlers_ids" name="transfer_requests_default_submission_notification_handlers_ids[]" class="custom-select <?= \Altum\Alerts::has_field_errors('transfer_requests_default_submission_notification_handlers_ids') ? 'is-invalid' : null ?>" multiple="multiple">
                                    <?php foreach($data->notification_handlers as $notification_handler): ?>
                                        <option value="<?= $notification_handler->notification_handler_id ?>" <?= in_array($notification_handler->notification_handler_id, $this->user->preferences->transfer_requests_default_submission_notification_handlers_ids ?? []) ? 'selected="selected"' : null ?>><?= $notification_handler->name ?></option>
                                    <?php endforeach ?>
                                </select>
                                <?= \Altum\Alerts::output_field_error('transfer_requests_default_submission_notification_handlers_ids') ?>
                                <?php if(get_this_device_type() == 'desktop'): ?><small class="form-text text-muted"><?= l('global.select_multiple_help') ?></small><?php endif ?>
                            </div>

                            <div class="form-group">
                                <label for="transfer_requests_default_pageview_notification_handlers_ids"><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= sprintf(l('account_preferences.x_default_y'), l('transfer_requests.title'), l('transfer.pageview_notification_handlers')) ?></label>
                                <select id="transfer_requests_default_pageview_notification_handlers_ids" name="transfer_requests_default_pageview_notification_handlers_ids[]" class="custom-select <?= \Altum\Alerts::has_field_errors('transfer_requests_default_pageview_notification_handlers_ids') ? 'is-invalid' : null ?>" multiple="multiple">
                                    <?php foreach($data->notification_handlers as $notification_handler): ?>
                                        <option value="<?= $notification_handler->notification_handler_id ?>" <?= in_array($notification_handler->notification_handler_id, $this->user->preferences->transfer_requests_default_pageview_notification_handlers_ids ?? []) ? 'selected="selected"' : null ?>><?= $notification_handler->name ?></option>
                                    <?php endforeach ?>
                                </select>
                                <?= \Altum\Alerts::output_field_error('transfer_requests_default_pageview_notification_handlers_ids') ?>
                                <?php if(get_this_device_type() == 'desktop'): ?><small class="form-text text-muted"><?= l('global.select_multiple_help') ?></small><?php endif ?>
                            </div>
                        <?php endif ?>

                        <div class="form-group custom-control custom-switch">
                            <input id="transfer_requests_auto_file_upload" name="transfer_requests_auto_file_upload" type="checkbox" class="custom-control-input" <?= $this->user->preferences->transfer_requests_auto_file_upload ? 'checked="checked"' : null ?>>
                            <label class="custom-control-label" for="transfer_requests_auto_file_upload"><?= l('account_preferences.transfer_requests_auto_file_upload') ?></label>
                            <?= \Altum\Alerts::output_field_error('transfer_requests_auto_file_upload') ?>
                        </div>

                        <div class="form-group custom-control custom-switch">
                            <input id="transfer_requests_auto_submission_create" name="transfer_requests_auto_submission_create" type="checkbox" class="custom-control-input" <?= $this->user->preferences->transfer_requests_auto_submission_create ? 'checked="checked"' : null ?>>
                            <label class="custom-control-label" for="transfer_requests_auto_submission_create"><?= l('account_preferences.transfer_requests_auto_submission_create') ?></label>
                            <?= \Altum\Alerts::output_field_error('transfer_requests_auto_submission_create') ?>
                        </div>

                        <div class="form-group custom-control custom-switch">
                            <input id="transfer_requests_auto_copy_link" name="transfer_requests_auto_copy_link" type="checkbox" class="custom-control-input" <?= $this->user->preferences->transfer_requests_auto_copy_link ? 'checked="checked"' : null ?>>
                            <label class="custom-control-label" for="transfer_requests_auto_copy_link"><?= l('account_preferences.transfer_requests_auto_copy_link') ?></label>
                            <?= \Altum\Alerts::output_field_error('transfer_requests_auto_copy_link') ?>
                        </div>
                    </div>
                <?php endif ?>

                <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#dashboard_settings_container" aria-expanded="false" aria-controls="dashboard_settings_container">
                    <i class="fas fa-fw fa-table-cells fa-sm mr-1"></i> <?= l('account_preferences.dashboard_features') ?>
                </button>

                <div class="collapse" data-parent="#account_preferences" id="dashboard_settings_container">
                    <div class="form-group">
                        <label><i class="fas fa-fw fa-sm fa-table-cells text-muted mr-1"></i> <?= l('account_preferences.dashboard_features') ?></label>
                    </div>

                    <div id="dashboard_features">
                        <?php $dashboard_features = ((array) $this->user->preferences->dashboard) + array_fill_keys(require APP_PATH . 'includes/available_dashboard_features.php', true) ?>
                        <?php $index = 0; ?>
                        <?php foreach($dashboard_features as $feature => $is_enabled): ?>
                            <div class="d-flex">
                                <span class="cursor-grab drag mr-3" data-toggle="tooltip" title="<?= l('global.drag_and_drop') ?>">
                                    <i class="fas fa-fw fa-sm fa-bars text-muted"></i>
                                </span>

                                <div class="form-group custom-control custom-checkbox" data-dashboard-feature>
                                    <input id="<?= 'dashboard_' . $feature ?>" name="dashboard[<?= $index++ ?>]" value="<?= $feature ?>" type="checkbox" class="custom-control-input" <?= $is_enabled ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label" for="<?= 'dashboard_' . $feature ?>"><?= l('dashboard.' . $feature . '_header') ?></label>
                                </div>
                            </div>
                        <?php endforeach ?>
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

<?php ob_start() ?>
<script>
    'use strict';

    let process_auto_fields = () => {
        let transfers_auto_file_upload = document.getElementById('transfers_auto_file_upload');
        let transfers_auto_transfer_create = document.getElementById('transfers_auto_transfer_create');

        if(transfers_auto_file_upload.checked) {
            transfers_auto_transfer_create.removeAttribute('disabled');
        } else {
            transfers_auto_transfer_create.setAttribute('disabled', 'disabled');
            transfers_auto_transfer_create.checked = false;
        }
    };

    document.querySelectorAll('#transfers_auto_file_upload,#transfers_auto_transfer_create').forEach(element => element.addEventListener('change', process_auto_fields));

    process_auto_fields();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php ob_start() ?>
    <script src="<?= ASSETS_FULL_URL . 'js/libraries/sortable.js?v=' . PRODUCT_CODE ?>"></script>
    <script>
        'use strict';

        let sortable = Sortable.create(document.getElementById('dashboard_features'), {
            animation: 150,
            handle: '.drag',
            onUpdate: event => {

                /* Refresh tooltips */
                tooltips_initiate();

                document.querySelectorAll('#dashboard_features > div').forEach((elm, i) => {
                    let input = elm.querySelector('input[type="checkbox"]');
                    if(input) {
                        input.setAttribute('name', `dashboard[${i}]`);
                    }
                });

            }
        });
    </script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
