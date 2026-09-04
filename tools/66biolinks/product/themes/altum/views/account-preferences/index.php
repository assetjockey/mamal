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

                <?php if(\Altum\Plugin::is_active('aix')): ?>
                    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#aix_container" aria-expanded="false" aria-controls="aix_container">
                        <i class="fas fa-fw fa-robot fa-sm mr-1"></i> <?= l('account_preferences.aix') ?>
                    </button>

                    <div class="collapse" data-parent="#account_preferences" id="aix_container">
                        <div class="form-group">
                            <label for="openai_api_key"><?= l('account_preferences.aix.openai_api_key') ?></label>
                            <textarea id="openai_api_key" name="openai_api_key" class="form-control"><?= $this->user->preferences->openai_api_key ?></textarea>
                            <small class="form-text text-muted"><?= l('account_preferences.aix.openai_api_key_help') ?></small>
                            <?php if($this->user->plan_settings->exclusive_personal_api_keys): ?>
                                <small class="form-text text-muted"><?= l('account_preferences.aix.required_help') ?></small>
                            <?php else: ?>
                                <small class="form-text text-muted"><?= l('account_preferences.aix.optional_help') ?></small>
                            <?php endif ?>
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
                        <label for="links_default_order_by"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('links.title')) ?></label>
                        <select id="links_default_order_by" name="links_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('links_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="link_id" <?= $this->user->preferences->links_default_order_by == 'link_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->links_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->links_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="clicks" <?= $this->user->preferences->links_default_order_by == 'clicks' ? 'selected="selected"' : null ?>><?= l('links.filters.order_by_clicks') ?></option>
                            <option value="url" <?= $this->user->preferences->links_default_order_by == 'url' ? 'selected="selected"' : null ?>><?= l('links.filters.url') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('links_default_order_by') ?>
                    </div>

                    <?php if(settings()->codes->qr_codes_is_enabled): ?>
                        <div class="form-group">
                            <label for="qr_codes_default_order_by"><i class="fas fa-fw fa-sm fa-qrcode text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('qr_codes.title')) ?></label>
                            <select id="qr_codes_default_order_by" name="qr_codes_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('qr_codes_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="qr_code_id" <?= $this->user->preferences->qr_codes_default_order_by == 'qr_code_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->qr_codes_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $this->user->preferences->qr_codes_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="name" <?= $this->user->preferences->qr_codes_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                <option value="type" <?= $this->user->preferences->qr_codes_default_order_by == 'type' ? 'selected="selected"' : null ?>><?= l('global.type') ?></option>
                            </select>
                            <?= \Altum\Alerts::output_field_error('qr_codes_default_order_by') ?>
                        </div>
                    <?php endif ?>

                    <?php if(settings()->links->projects_is_enabled): ?>
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

                    <?php if(settings()->links->pixels_is_enabled): ?>
                        <div class="form-group">
                            <label for="pixels_default_order_by"><i class="fas fa-fw fa-sm fa-adjust text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('pixels.title')) ?></label>
                            <select id="pixels_default_order_by" name="pixels_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('pixels_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="pixel_id" <?= $this->user->preferences->pixels_default_order_by == 'pixel_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->pixels_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $this->user->preferences->pixels_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="name" <?= $this->user->preferences->pixels_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            </select>
                            <?= \Altum\Alerts::output_field_error('pixels_default_order_by') ?>
                        </div>
                    <?php endif ?>

                    <?php if(settings()->links->domains_is_enabled): ?>
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

                    <?php if(settings()->links->splash_page_is_enabled): ?>
                    <div class="form-group">
                        <label for="splash_pages_default_order_by"><i class="fas fa-fw fa-sm fa-droplet text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('splash_pages.title')) ?></label>
                        <select id="splash_pages_default_order_by" name="splash_pages_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('splash_pages_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="splash_page_id" <?= $this->user->preferences->splash_pages_default_order_by == 'splash_page_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->splash_pages_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->splash_pages_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->splash_pages_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('splash_pages_default_order_by') ?>
                    </div>
                    <?php endif ?>

                    <?php if(settings()->links->biolinks_is_enabled): ?>
                    <div class="form-group">
                        <label for="data_default_order_by"><i class="fas fa-fw fa-sm fa-database text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('data.title')) ?></label>
                        <select id="data_default_order_by" name="data_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('data_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="datum_id" <?= $this->user->preferences->data_default_order_by == 'datum_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->data_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('data_default_order_by') ?>
                    </div>

                    <?php if(\Altum\Plugin::is_active('newsletters') && settings()->links->biolinks_is_enabled && settings()->newsletters->is_enabled): ?>
                    <div class="form-group">
                        <label for="newsletters_default_order_by"><i class="fas fa-fw fa-sm fa-envelope-open-text text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('newsletters.title')) ?></label>
                        <select id="newsletters_default_order_by" name="newsletters_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('newsletters_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="newsletter_id" <?= ($this->user->preferences->newsletters_default_order_by ?? 'newsletter_id') == 'newsletter_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= ($this->user->preferences->newsletters_default_order_by ?? 'newsletter_id') == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= ($this->user->preferences->newsletters_default_order_by ?? 'newsletter_id') == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="name" <?= ($this->user->preferences->newsletters_default_order_by ?? 'newsletter_id') == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="sent_emails" <?= ($this->user->preferences->newsletters_default_order_by ?? 'newsletter_id') == 'sent_emails' ? 'selected="selected"' : null ?>><?= l('newsletters.sent_emails') ?></option>
                            <option value="total_emails" <?= ($this->user->preferences->newsletters_default_order_by ?? 'newsletter_id') == 'total_emails' ? 'selected="selected"' : null ?>><?= l('newsletters.total_emails') ?></option>
                            <option value="views" <?= ($this->user->preferences->newsletters_default_order_by ?? 'newsletter_id') == 'views' ? 'selected="selected"' : null ?>><?= l('newsletters.views') ?></option>
                            <option value="clicks" <?= ($this->user->preferences->newsletters_default_order_by ?? 'newsletter_id') == 'clicks' ? 'selected="selected"' : null ?>><?= l('newsletters.clicks') ?></option>
                            <option value="last_sent_email_datetime" <?= ($this->user->preferences->newsletters_default_order_by ?? 'newsletter_id') == 'last_sent_email_datetime' ? 'selected="selected"' : null ?>><?= sprintf(l('newsletters.last_sent_email_datetime'), '') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('newsletters_default_order_by') ?>
                    </div>

                    <div class="form-group">
                        <label for="newsletter_subscribers_default_order_by"><i class="fas fa-fw fa-sm fa-users text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('newsletter_subscribers.title')) ?></label>
                        <select id="newsletter_subscribers_default_order_by" name="newsletter_subscribers_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('newsletter_subscribers_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="newsletter_subscriber_id" <?= ($this->user->preferences->newsletter_subscribers_default_order_by ?? 'newsletter_subscriber_id') == 'newsletter_subscriber_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="email" <?= ($this->user->preferences->newsletter_subscribers_default_order_by ?? 'newsletter_subscriber_id') == 'email' ? 'selected="selected"' : null ?>><?= l('global.email') ?></option>
                            <option value="name" <?= ($this->user->preferences->newsletter_subscribers_default_order_by ?? 'newsletter_subscriber_id') == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="status" <?= ($this->user->preferences->newsletter_subscribers_default_order_by ?? 'newsletter_subscriber_id') == 'status' ? 'selected="selected"' : null ?>><?= l('global.status') ?></option>
                            <option value="source" <?= ($this->user->preferences->newsletter_subscribers_default_order_by ?? 'newsletter_subscriber_id') == 'source' ? 'selected="selected"' : null ?>><?= l('newsletter_subscribers.source') ?></option>
                            <option value="datetime" <?= ($this->user->preferences->newsletter_subscribers_default_order_by ?? 'newsletter_subscriber_id') == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= ($this->user->preferences->newsletter_subscribers_default_order_by ?? 'newsletter_subscriber_id') == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="unsubscribed_datetime" <?= ($this->user->preferences->newsletter_subscribers_default_order_by ?? 'newsletter_subscriber_id') == 'unsubscribed_datetime' ? 'selected="selected"' : null ?>><?= l('newsletter_subscribers.unsubscribed_datetime') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('newsletter_subscribers_default_order_by') ?>
                    </div>
                    <?php endif ?>
                    <?php endif ?>

                    <?php if(\Altum\Plugin::is_active('payment-blocks')): ?>
                        <div class="form-group">
                            <label for="payment_processors_default_order_by"><i class="fas fa-fw fa-sm fa-credit-card text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('payment_processors.title')) ?></label>
                            <select id="payment_processors_default_order_by" name="payment_processors_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('payment_processors_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="payment_processor_id" <?= $this->user->preferences->payment_processors_default_order_by == 'payment_processor_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->payment_processors_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $this->user->preferences->payment_processors_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="name" <?= $this->user->preferences->payment_processors_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            </select>
                            <?= \Altum\Alerts::output_field_error('payment_processors_default_order_by') ?>
                        </div>

                        <div class="form-group">
                            <label for="guests_payments_default_order_by"><i class="fas fa-fw fa-sm fa-coins text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('guests_payments.title')) ?></label>
                            <select id="guests_payments_default_order_by" name="guests_payments_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('guests_payments_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="guest_payment_id" <?= $this->user->preferences->guests_payments_default_order_by == 'guest_payment_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->guests_payments_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="total_amount" <?= $this->user->preferences->guests_payments_default_order_by == 'total_amount' ? 'selected="selected"' : null ?>><?= l('guests_payments.total_amount') ?></option>
                            </select>
                            <?= \Altum\Alerts::output_field_error('guests_payments_default_order_by') ?>
                        </div>
                    <?php endif ?>

                    <?php if(\Altum\Plugin::is_active('email-signatures') && settings()->signatures->is_enabled): ?>
                        <div class="form-group">
                            <label for="signatures_default_order_by"><i class="fas fa-fw fa-sm fa-file-signature text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('signatures.title')) ?></label>
                            <select id="signatures_default_order_by" name="signatures_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('signatures_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="signature_id" <?= $this->user->preferences->signatures_default_order_by == 'signature_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->signatures_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $this->user->preferences->signatures_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="host" <?= $this->user->preferences->signatures_default_order_by == 'host' ? 'selected="selected"' : null ?>><?= l('signatures.table.host') ?></option>
                            </select>
                            <?= \Altum\Alerts::output_field_error('signatures_default_order_by') ?>
                        </div>
                    <?php endif ?>

					<?php if(\Altum\Plugin::is_active('digital-wallets') && settings()->digital_wallets->is_enabled): ?>
                        <div class="form-group">
                            <label for="digital_wallets_default_order_by"><i class="fas fa-fw fa-sm fa-wifi text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('digital_wallets.title')) ?></label>
                            <select id="digital_wallets_default_order_by" name="digital_wallets_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('digital_wallets_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="digital_wallet_id" <?= $this->user->preferences->digital_wallets_default_order_by == 'digital_wallet_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->digital_wallets_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $this->user->preferences->digital_wallets_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="pageviews" <?= $this->user->preferences->digital_wallets_default_order_by == 'pageviews' ? 'selected="selected"' : null ?>><?= l('link.statistics.pageviews') ?></option>
                                <option value="name" <?= $this->user->preferences->digital_wallets_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            </select>
							<?= \Altum\Alerts::output_field_error('digital_wallets_default_order_by') ?>
                        </div>
					<?php endif ?>

                    <?php if(\Altum\Plugin::is_active('aix') && settings()->aix->images_is_enabled): ?>
                        <div class="form-group">
                            <label for="images_default_order_by"><i class="fas fa-fw fa-sm fa-icons text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('images.title')) ?></label>
                            <select id="images_default_order_by" name="images_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('images_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="image_id" <?= $this->user->preferences->images_default_order_by == 'image_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->images_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $this->user->preferences->images_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="name" <?= $this->user->preferences->images_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            </select>
                            <?= \Altum\Alerts::output_field_error('images_default_order_by') ?>
                        </div>
                    <?php endif ?>

                    <?php if(\Altum\Plugin::is_active('aix') && settings()->aix->transcriptions_is_enabled): ?>
                        <div class="form-group">
                            <label for="transcriptions_default_order_by"><i class="fas fa-fw fa-sm fa-microphone-alt text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('transcriptions.title')) ?></label>
                            <select id="transcriptions_default_order_by" name="transcriptions_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('transcriptions_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="transcription_id" <?= $this->user->preferences->transcriptions_default_order_by == 'transcription_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->transcriptions_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $this->user->preferences->transcriptions_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="name" <?= $this->user->preferences->transcriptions_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                <option value="words" <?= $this->user->preferences->transcriptions_default_order_by == 'words' ? 'selected="selected"' : null ?>><?= l('transcriptions.words') ?></option>
                            </select>
                            <?= \Altum\Alerts::output_field_error('transcriptions_default_order_by') ?>
                        </div>
                    <?php endif ?>

                    <?php if(\Altum\Plugin::is_active('aix') && settings()->aix->chats_is_enabled): ?>
                        <div class="form-group">
                            <label for="chats_default_order_by"><i class="fas fa-fw fa-sm fa-comments text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('chats.title')) ?></label>
                            <select id="chats_default_order_by" name="chats_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('chats_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="chat_id" <?= $this->user->preferences->chats_default_order_by == 'chat_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->chats_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $this->user->preferences->chats_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="name" <?= $this->user->preferences->chats_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                <option value="total_messages" <?= $this->user->preferences->chats_default_order_by == 'total_messages' ? 'selected="selected"' : null ?>><?= l('chats.total_messages') ?></option>
                            </select>
                            <?= \Altum\Alerts::output_field_error('chats_default_order_by') ?>
                        </div>
                    <?php endif ?>

                    <div class="form-group custom-control custom-switch">
                        <input id="links_auto_copy_link" name="links_auto_copy_link" type="checkbox" class="custom-control-input" <?= $this->user->preferences->links_auto_copy_link ? 'checked="checked"' : null ?>>
                        <label class="custom-control-label" for="links_auto_copy_link"><?= l('account_preferences.links_auto_copy_link') ?></label>
                        <?= \Altum\Alerts::output_field_error('links_auto_copy_link') ?>
                    </div>

                    <div class="form-group custom-control custom-switch">
                        <input id="links_autosave_settings" name="links_autosave_settings" type="checkbox" class="custom-control-input" <?= $this->user->preferences->links_autosave_settings ? 'checked="checked"' : null ?>>
                        <label class="custom-control-label" for="links_autosave_settings"><?= l('account_preferences.links_autosave_settings') ?></label>
                        <?= \Altum\Alerts::output_field_error('links_autosave_settings') ?>
                    </div>
                </div>

                <?php $custom_smtp_is_available = \Altum\Plugin::is_active('newsletters') && settings()->links->biolinks_is_enabled && settings()->newsletters->is_enabled && (settings()->newsletters->custom_smtp_is_enabled || !empty($this->user->plan_settings->force_newsletters_custom_smtp_is_enabled)) ?>
                <?php if($custom_smtp_is_available): ?>
                    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#newsletters_smtp_container" aria-expanded="false" aria-controls="newsletters_smtp_container">
                        <i class="fas fa-fw fa-envelope-open-text fa-sm mr-1"></i> <?= l('account_preferences.newsletters_smtp') ?>
                    </button>

                    <div class="collapse" data-parent="#account_preferences" id="newsletters_smtp_container">
                        <div class="form-group custom-control custom-switch">
                            <input id="newsletters_smtp_is_enabled" name="newsletters_smtp_is_enabled" type="checkbox" class="custom-control-input" <?= ($this->user->preferences->newsletters_smtp_is_enabled ?? false) ? 'checked="checked"' : null ?>>
                            <label class="custom-control-label" for="newsletters_smtp_is_enabled"><?= l('account_preferences.newsletters_smtp_is_enabled') ?></label>
                            <small class="form-text text-muted"><?= l('account_preferences.newsletters_smtp_is_enabled_help') ?></small>
                        </div>

                        <div class="form-group">
                            <label for="newsletters_smtp_host"><i class="fas fa-fw fa-sm fa-server text-muted mr-1"></i> <?= l('account_preferences.newsletters_smtp_host') ?></label>
                            <input id="newsletters_smtp_host" type="text" name="newsletters_smtp_host" class="form-control <?= \Altum\Alerts::has_field_errors('newsletters_smtp_host') ? 'is-invalid' : null ?>" value="<?= e($this->user->preferences->newsletters_smtp_host ?? '') ?>" maxlength="128" />
                            <?= \Altum\Alerts::output_field_error('newsletters_smtp_host') ?>
                        </div>

                        <div class="form-group">
                            <label for="newsletters_smtp_port"><i class="fas fa-fw fa-sm fa-plug text-muted mr-1"></i> <?= l('account_preferences.newsletters_smtp_port') ?></label>
                            <input id="newsletters_smtp_port" type="text" name="newsletters_smtp_port" class="form-control <?= \Altum\Alerts::has_field_errors('newsletters_smtp_port') ? 'is-invalid' : null ?>" value="<?= e($this->user->preferences->newsletters_smtp_port ?? '') ?>" maxlength="16" />
                            <?= \Altum\Alerts::output_field_error('newsletters_smtp_port') ?>
                        </div>

                        <div class="form-group">
                            <label for="newsletters_smtp_encryption"><i class="fas fa-fw fa-sm fa-lock text-muted mr-1"></i> <?= l('account_preferences.newsletters_smtp_encryption') ?></label>
                            <select id="newsletters_smtp_encryption" name="newsletters_smtp_encryption" class="custom-select">
                                <option value="tls" <?= ($this->user->preferences->newsletters_smtp_encryption ?? 'tls') == 'tls' ? 'selected="selected"' : null ?>>TLS</option>
                                <option value="ssl" <?= ($this->user->preferences->newsletters_smtp_encryption ?? 'tls') == 'ssl' ? 'selected="selected"' : null ?>>SSL</option>
                                <option value="0" <?= ($this->user->preferences->newsletters_smtp_encryption ?? 'tls') == '0' ? 'selected="selected"' : null ?>><?= l('global.none') ?></option>
                            </select>
                        </div>

                        <div class="form-group custom-control custom-switch">
                            <input id="newsletters_smtp_auth" name="newsletters_smtp_auth" type="checkbox" class="custom-control-input" <?= ($this->user->preferences->newsletters_smtp_auth ?? false) ? 'checked="checked"' : null ?>>
                            <label class="custom-control-label" for="newsletters_smtp_auth"><?= l('account_preferences.newsletters_smtp_auth') ?></label>
                        </div>

                        <div class="form-group">
                            <label for="newsletters_smtp_username"><i class="fas fa-fw fa-sm fa-user text-muted mr-1"></i> <?= l('account_preferences.newsletters_smtp_username') ?></label>
                            <input id="newsletters_smtp_username" type="text" name="newsletters_smtp_username" class="form-control <?= \Altum\Alerts::has_field_errors('newsletters_smtp_username') ? 'is-invalid' : null ?>" value="<?= e($this->user->preferences->newsletters_smtp_username ?? '') ?>" maxlength="128" />
                            <?= \Altum\Alerts::output_field_error('newsletters_smtp_username') ?>
                        </div>

                        <div class="form-group">
                            <label for="newsletters_smtp_password"><i class="fas fa-fw fa-sm fa-key text-muted mr-1"></i> <?= l('account_preferences.newsletters_smtp_password') ?></label>
                            <input id="newsletters_smtp_password" type="password" name="newsletters_smtp_password" class="form-control <?= \Altum\Alerts::has_field_errors('newsletters_smtp_password') ? 'is-invalid' : null ?>" value="<?= e($this->user->preferences->newsletters_smtp_password ?? '') ?>" maxlength="256" autocomplete="new-password" />
                            <?= \Altum\Alerts::output_field_error('newsletters_smtp_password') ?>
                        </div>

                        <div class="form-group">
                            <label for="newsletters_smtp_from"><i class="fas fa-fw fa-sm fa-at text-muted mr-1"></i> <?= l('account_preferences.newsletters_smtp_from') ?></label>
                            <input id="newsletters_smtp_from" type="email" name="newsletters_smtp_from" class="form-control <?= \Altum\Alerts::has_field_errors('newsletters_smtp_from') ? 'is-invalid' : null ?>" value="<?= e($this->user->preferences->newsletters_smtp_from ?? '') ?>" maxlength="320" />
                            <?= \Altum\Alerts::output_field_error('newsletters_smtp_from') ?>
                        </div>

                        <div class="form-group">
                            <label for="newsletters_smtp_from_name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('account_preferences.newsletters_smtp_from_name') ?></label>
                            <input id="newsletters_smtp_from_name" type="text" name="newsletters_smtp_from_name" class="form-control" value="<?= e($this->user->preferences->newsletters_smtp_from_name ?? '') ?>" maxlength="64" />
                        </div>

                        <div class="form-group">
                            <label for="newsletters_smtp_reply_to"><i class="fas fa-fw fa-sm fa-reply text-muted mr-1"></i> <?= l('account_preferences.newsletters_smtp_reply_to') ?></label>
                            <input id="newsletters_smtp_reply_to" type="email" name="newsletters_smtp_reply_to" class="form-control <?= \Altum\Alerts::has_field_errors('newsletters_smtp_reply_to') ? 'is-invalid' : null ?>" value="<?= e($this->user->preferences->newsletters_smtp_reply_to ?? '') ?>" maxlength="320" />
                            <?= \Altum\Alerts::output_field_error('newsletters_smtp_reply_to') ?>
                        </div>

                        <div class="form-group">
                            <label for="newsletters_smtp_reply_to_name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('account_preferences.newsletters_smtp_reply_to_name') ?></label>
                            <input id="newsletters_smtp_reply_to_name" type="text" name="newsletters_smtp_reply_to_name" class="form-control" value="<?= e($this->user->preferences->newsletters_smtp_reply_to_name ?? '') ?>" maxlength="64" />
                        </div>
                    </div>
                <?php endif ?>

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
