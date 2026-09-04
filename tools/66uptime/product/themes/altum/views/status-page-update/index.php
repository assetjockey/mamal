<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('status-pages') ?>"><?= l('status_pages.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li>
                    <?= l('status_page.breadcrumb') ?><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('status_page_update.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-wifi mr-1"></i> <?= sprintf(l('global.update_x'), $data->status_page->name) ?></h1>

        <div class="d-flex align-items-center col-auto p-0">
            <div>
                <button
                        id="url_copy"
                        type="button"
                        class="btn btn-link text-secondary"
                        data-toggle="tooltip"
                        title="<?= l('global.clipboard_copy') ?>"
                        aria-label="<?= l('global.clipboard_copy') ?>"
                        data-copy="<?= l('global.clipboard_copy') ?>"
                        data-copied="<?= l('global.clipboard_copied') ?>"
                        data-clipboard-text="<?= $data->status_page->full_url ?>"
                >
                    <i class="fas fa-fw fa-sm fa-copy"></i>
                </button>
            </div>

            <?= include_view(THEME_PATH . 'views/status-pages/status_page_dropdown_button.php', ['id' => $data->status_page->status_page_id, 'resource_name' => $data->status_page->name]) ?>
        </div>
    </div>

    <p class="text-truncate">
        <a href="<?= $data->status_page->full_url ?>" target="_blank">
            <i class="fas fa-fw fa-sm fa-external-link-alt text-muted mr-1"></i> <?= remove_url_protocol_from_url($data->status_page->full_url) ?>
        </a>
    </p>

    <div class="card">
        <div class="card-body">

            <form id="status_page_update" action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <?php if(count($data->domains) && (settings()->status_pages->domains_is_enabled || settings()->status_pages->additional_domains_is_enabled)): ?>
                    <div class="form-group">
                        <label for="domain_id"><i class="fas fa-fw fa-sm fa-globe text-muted mr-1"></i> <?= l('status_page.domain_id') ?></label>
                        <select id="domain_id" name="domain_id" class="custom-select">
                            <?php if(settings()->status_pages->main_domain_is_enabled || \Altum\Authentication::is_admin()): ?>
                                <option value=" " <?= $data->status_page->domain_id ? null : 'selected="selected"' ?>><?= remove_url_protocol_from_url(SITE_URL) ?></option>
                            <?php endif ?>

                            <?php foreach($data->domains as $row): ?>
                                <option value="<?= $row->domain_id ?>" data-type="<?= $row->type ?>" <?= $data->status_page->domain_id && $data->status_page->domain_id == $row->domain_id ? 'selected="selected"' : null ?>><?= remove_url_protocol_from_url($row->url) ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="form-text text-muted"><?= l('status_page.domain_id_help') ?></small>
                    </div>

                    <div id="is_main_status_page_wrapper" class="form-group custom-control custom-switch">
                        <input id="is_main_status_page" name="is_main_status_page" type="checkbox" class="custom-control-input" <?= $data->status_page->domain_id && $data->domains[$data->status_page->domain_id]->status_page_id == $data->status_page->status_page_id ? 'checked="checked"' : null ?>>
                        <label class="custom-control-label" for="is_main_status_page"><?= l('status_page.is_main_status_page') ?></label>
                        <small class="form-text text-muted"><?= l('status_page.is_main_status_page_help') ?></small>
                    </div>

                    <div <?= $this->user->plan_settings->custom_url_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                        <div class="<?= $this->user->plan_settings->custom_url_is_enabled ? null : 'container-disabled' ?>">
                            <div class="form-group">
                                <label for="url"><i class="fas fa-fw fa-sm fa-bolt text-muted mr-1"></i> <?= l('status_page.url') ?></label>
                                <input type="text" id="url" name="url" class="form-control <?= \Altum\Alerts::has_field_errors('url') ? 'is-invalid' : null ?>" value="<?= $data->status_page->url ?>" maxlength="<?= ($this->user->plan_settings->url_maximum_characters ?? 64) ?>" onchange="update_this_value(this, get_slug)" onkeyup="update_this_value(this, get_slug)" placeholder="<?= l('global.url_slug_placeholder') ?>" />
                                <?= \Altum\Alerts::output_field_error('url') ?>
                                <small class="form-text text-muted"><?= l('status_page.url_help') ?></small>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div <?= $this->user->plan_settings->custom_url_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                        <div class="<?= $this->user->plan_settings->custom_url_is_enabled ? null : 'container-disabled' ?>">
                            <label for="url"><i class="fas fa-fw fa-sm fa-bolt text-muted mr-1"></i> <?= l('status_page.url') ?></label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><?= remove_url_protocol_from_url(SITE_URL) ?></span>
                                    </div>
                                    <input type="text" id="url" name="url" class="form-control <?= \Altum\Alerts::has_field_errors('url') ? 'is-invalid' : null ?>" value="<?= $data->status_page->url ?>" maxlength="<?= ($this->user->plan_settings->url_maximum_characters ?? 64) ?>" onchange="update_this_value(this, get_slug)" onkeyup="update_this_value(this, get_slug)" placeholder="<?= l('global.url_slug_placeholder') ?>" />
                                    <?= \Altum\Alerts::output_field_error('url') ?>
                                </div>
                                <small class="form-text text-muted"><?= l('status_page.url_help') ?></small>
                            </div>
                        </div>
                    </div>
                <?php endif ?>

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->status_page->name ?>" maxlength="256" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('global.description') ?></label>
                    <input type="text" id="description" name="description" class="form-control" value="<?= $data->status_page->description ?>" maxlength="256" />
                    <small class="form-text text-muted"><?= l('status_page.description_help') ?></small>
                </div>

                <div class="form-group">
                    <div class="d-flex flex-wrap flex-row justify-content-between">
                        <label for="monitors_ids"><i class="fas fa-fw fa-sm fa-server text-muted mr-1"></i> <?= l('status_page.monitors_ids') ?></label>
                        <a href="<?= url('monitor-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('global.create') ?></a>
                    </div>
                    <select id="monitors_ids" name="monitors_ids[]" class="custom-select" multiple="multiple">
                        <?php foreach($data->monitors as $monitor): ?>
                            <option value="<?= $monitor->monitor_id ?>" <?= in_array($monitor->monitor_id, $data->status_page->monitors_ids)  ? 'selected="selected"' : null ?>>
                                <?= $monitor->name . ' - ' . $monitor->target ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                    <small class="form-text text-muted"><?= l('status_page.monitors_ids_help') ?></small>
                </div>

                <div class="form-group">
                    <div class="d-flex flex-wrap flex-row justify-content-between">
                        <label for="heartbeats_ids"><i class="fas fa-fw fa-sm fa-heartbeat text-muted mr-1"></i> <?= l('status_page.heartbeats_ids') ?></label>
                        <a href="<?= url('heartbeat-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('global.create') ?></a>
                    </div>
                    <select id="heartbeats_ids" name="heartbeats_ids[]" class="custom-select" multiple="multiple">
                        <?php foreach($data->heartbeats as $heartbeat): ?>
                            <option value="<?= $heartbeat->heartbeat_id ?>" <?= in_array($heartbeat->heartbeat_id, $data->status_page->heartbeats_ids)  ? 'selected="selected"' : null ?>>
                                <?= $heartbeat->name ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                    <small class="form-text text-muted"><?= l('status_page.heartbeats_ids_help') ?></small>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="is_enabled" name="is_enabled" type="checkbox" class="custom-control-input" <?= $data->status_page->is_enabled ? 'checked="checked"' : null?>>
                    <label class="custom-control-label" for="is_enabled"><?= l('status_page.is_enabled') ?></label>
                </div>

                <button class="btn btn-sm btn-block <?= \Altum\Alerts::has_field_errors(['logo', 'favicon']) ? 'btn-outline-danger' : 'btn-outline-blue-500 bg-blue-50' ?> my-3" type="button" data-toggle="collapse" data-target="#customizations_container" aria-expanded="false" aria-controls="customizations_container">
                    <i class="fas fa-fw fa-paint-brush fa-sm mr-1"></i> <?= l('status_page.customizations') ?>
                </button>

                <div class="collapse" data-parent="#status_page_update" id="customizations_container">
                    <?php $themes = require APP_PATH . 'includes/s/themes.php'; ?>
                    <div class="form-group">
                        <label for="theme"><i class="fas fa-fw fa-paint-roller fa-sm mr-1"></i> <?= l('status_page.theme') ?></label>
                        <div class="row btn-group-toggle m-n2" data-toggle="buttons">
                            <?php foreach($themes as $key => $value): ?>
                                <div class="p-2 col-4">
                                    <label class="btn btn-light btn-block font-size-small mb-0 text-truncate <?= $data->status_page->theme == $key ? 'selected="selected""' : null?>">
                                        <input type="radio" name="theme" value="<?= $key ?>" class="custom-control-input" <?= $data->status_page->theme == $key ? 'checked="checked"' : null ?> />
                                        <i class="<?= $value['icon'] ?> fa-fw fa-sm mr-1"></i> <?= $value['name'] ?>
                                    </label>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>

                    <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= settings()->status_pages->logo_size_limit ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), settings()->status_pages->logo_size_limit) ?>">
                        <label for="logo"><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('status_page.logo') ?></label>
                        <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'status_pages_logos', 'file_key' => 'logo', 'already_existing_image' => $data->status_page->logo]) ?>
                        <?= \Altum\Alerts::output_field_error('logo') ?>
                        <small class="form-text text-muted"><?= l('status_page.logo_help') ?> <?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('status_pages_logos')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->status_pages->logo_size_limit) ?></small>
                    </div>

                    <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= settings()->status_pages->favicon_size_limit ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), settings()->status_pages->favicon_size_limit) ?>">
                        <label for="favicon"><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('status_page.favicon') ?></label>
                        <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'status_pages_favicons', 'file_key' => 'favicon', 'already_existing_image' => $data->status_page->favicon, 'input_data' => 'data-crop data-aspect-ratio="1"']) ?>
                        <?= \Altum\Alerts::output_field_error('favicon') ?>
                        <small class="form-text text-muted"><?= l('status_page.favicon_help') ?> <?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('status_pages_favicons')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->status_pages->logo_size_limit) ?></small>
                    </div>

                    <?php $available_fonts = require APP_PATH . 'includes/s/fonts.php'; ?>
                    <?php foreach($available_fonts as $font_key => $font): ?>
                        <?php if($font['font_css_url']): ?>
                            <?php ob_start() ?>
                            <link href="<?= $font['font_css_url'] ?>" rel="stylesheet">
                            <?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>
                        <?php endif ?>
                    <?php endforeach ?>

                    <div class="form-group">
                        <label for="font_family"><i class="fas fa-fw fa-pen-nib fa-sm mr-1"></i> <?= l('status_page.font_family') ?></label>
                        <div class="row btn-group-toggle m-n2" data-toggle="buttons">
                            <?php foreach($available_fonts as $font_key => $font): ?>
                                <div class="col-6 col-lg-4 p-2 h-100">
                                    <label class="btn btn-light btn-block font-size-small mb-0 text-truncate mb-0 <?= ($data->status_page->settings->font_family ?? 'default') == $font_key ? 'active"' : null?>" style="font-family: <?= $font['font-family'] ?> !important;">
                                        <input type="radio" name="font_family" value="<?= $font_key ?>" class="custom-control-input" <?= ($data->status_page->settings->font_family ?? 'default') == $font_key ? 'checked="checked"' : null?> required="required" />
                                        <?= $font['name'] ?>
                                    </label>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="font_size"><i class="fas fa-fw fa-font fa-sm mr-1"></i> <?= l('status_page.font_size') ?></label>
                        <div class="input-group">
                            <input id="font_size" type="number" min="14" max="22" name="font_size" class="form-control" value="<?= $data->status_page->settings->font_size ?>" />
                            <div class="input-group-append">
                                <span class="input-group-text">px</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group custom-control custom-switch">
                        <input id="display_share_buttons" name="display_share_buttons" type="checkbox" class="custom-control-input" <?= $data->status_page->settings->display_share_buttons ? 'checked="checked"' : null?>>
                        <label class="custom-control-label" for="display_share_buttons"><?= l('status_page.display_share_buttons') ?></label>
                    </div>

                    <div class="form-group custom-control custom-switch">
                        <input id="display_header_text" name="display_header_text" type="checkbox" class="custom-control-input" <?= $data->status_page->settings->display_header_text ? 'checked="checked"' : null?>>
                        <label class="custom-control-label" for="display_header_text"><?= l('status_page.display_header_text') ?></label>
                    </div>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 bg-blue-50 my-3" type="button" data-toggle="collapse" data-target="#socials_container" aria-expanded="false" aria-controls="socials_container">
                    <i class="fas fa-fw fa-share-alt fa-sm mr-1"></i> <?= l('status_page.socials') ?>
                </button>

                <div class="collapse" data-parent="#status_page_update" id="socials_container">
                    <div>
                        <?php foreach(require APP_PATH . 'includes/s/socials.php' as $key => $value): ?>
                            <div class="form-group">
                                <label for="socials_<?= $key ?>"><i class="<?= $value['icon'] ?> fa-fw fa-sm text-muted mr-1"></i> <?= l('status_page.' . $key) ?></label>
                                <div class="input-group">
                                    <?php if($value['input_display_format']): ?>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><?= str_replace('%s', '', $value['format']) ?></span>
                                        </div>
                                    <?php endif ?>
                                    <input id="socials_<?= $key ?>" type="text" class="form-control" name="socials[<?= $key ?>]" placeholder="<?= l('status_page.' . $key . '_placeholder') ?>" value="<?= $data->status_page->socials->{$key} ?? '' ?>" maxlength="<?= $value['max_length'] ?>" />
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>

                <button class="btn btn-sm btn-block <?= \Altum\Alerts::has_field_errors(['opengraph']) ? 'btn-outline-danger' : 'btn-outline-blue-500 bg-blue-50' ?> my-3" type="button" data-toggle="collapse" data-target="#seo_container" aria-expanded="false" aria-controls="seo_container">
                    <i class="fas fa-fw fa-search-plus fa-sm mr-1"></i> <?= l('status_page.seo') ?>
                </button>

                <div class="collapse" data-parent="#status_page_update" id="seo_container">
                    <div <?= $this->user->plan_settings->search_engine_visibility_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                        <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->search_engine_visibility_is_enabled ? null : 'container-disabled' ?>">
                            <input id="is_se_visible" name="is_se_visible" type="checkbox" class="custom-control-input" <?= $data->status_page->is_se_visible ? 'checked="checked"' : null?> <?= $this->user->plan_settings->search_engine_visibility_is_enabled ? null : 'disabled="disabled"' ?>>
                            <label class="custom-control-label" for="is_se_visible"><?= l('status_page.is_se_visible') ?></label>
                            <small class="form-text text-muted"><?= l('status_page.is_se_visible_help') ?></small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="title"><i class="fas fa-fw fa-heading fa-sm text-muted mr-1"></i> <?= l('status_page.title') ?></label>
                        <input id="title" type="text" class="form-control" name="title" value="<?= $data->status_page->settings->title ?? '' ?>" maxlength="70" />
                        <small class="form-text text-muted"><?= l('status_page.title_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="meta_description"><i class="fas fa-fw fa-paragraph fa-sm text-muted mr-1"></i> <?= l('status_page.meta_description') ?></label>
                        <input id="meta_description" type="text" class="form-control" name="meta_description" value="<?= $data->status_page->settings->meta_description ?? '' ?>" maxlength="160" />
                        <small class="form-text text-muted"><?= l('status_page.meta_description_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="meta_keywords"><i class="fas fa-fw fa-file-word fa-sm text-muted mr-1"></i> <?= l('status_page.meta_keywords') ?></label>
                        <input id="meta_keywords" type="text" class="form-control" name="meta_keywords" value="<?= $data->status_page->settings->meta_keywords ?? '' ?>" maxlength="160" />
                    </div>

                    <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= settings()->status_pages->opengraph_size_limit ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), settings()->status_pages->opengraph_size_limit) ?>">
                        <label for="opengraph"><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('status_page.opengraph') ?></label>
                        <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'status_pages_opengraph', 'file_key' => 'opengraph', 'already_existing_image' => $data->status_page->opengraph, 'input_data' => 'data-crop data-aspect-ratio="1.91"']) ?>
                        <?= \Altum\Alerts::output_field_error('opengraph') ?>
                        <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('status_pages_opengraph')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->status_pages->logo_size_limit) ?></small>
                    </div>

                    <div class="form-group">
                        <label for="language_code"><i class="fas fa-fw fa-sm fa-language text-muted mr-1"></i> <?= l('status_page.language_code') ?></label>
                        <select id="language_code" name="language_code" class="custom-select">
                            <?php foreach(get_locale_languages_array() as $locale => $language): ?>
                                <option value="<?= $locale ?>" <?= ($data->status_page->settings->language_code ?? 'en') == $locale ? 'selected="selected"' : null?>><?= $language ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="form-text text-muted"><?= l('status_page.language_code_help') ?></small>
                    </div>
                </div>

                <?php if(\Altum\Plugin::is_active('pwa') && settings()->pwa->is_enabled): ?>
                    <button class="btn btn-sm btn-block btn-outline-blue-500 bg-blue-50 my-3" type="button" data-toggle="collapse" data-target="#pwa_container" aria-expanded="false" aria-controls="pwa_container">
                        <i class="fas fa-fw fa-mobile-alt fa-sm mr-1"></i> <?= l('status_page.pwa_header') ?>
                    </button>

                    <div class="collapse" data-parent="#status_page_update" id="pwa_container">
                        <div class="alert alert-info">
                            <i class="fas fa-fw fa-info-circle mr-1"></i> <?= l('status_page.pwa_help') ?>
                        </div>

                        <div <?= !$this->user->plan_settings->custom_pwa_is_enabled ? get_plan_feature_disabled_info() : null ?>>
                            <div class="<?= !$this->user->plan_settings->custom_pwa_is_enabled ? 'container-disabled' : null ?>">

                                <div class="form-group custom-control custom-switch">
                                    <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="pwa_is_enabled"
                                            name="pwa_is_enabled"
                                        <?= $data->status_page->settings->pwa_is_enabled ? 'checked="checked"' : null ?>
                                        <?= !$this->user->plan_settings->custom_pwa_is_enabled ? 'disabled="disabled"' : null ?>
                                    >
                                    <label class="custom-control-label" for="pwa_is_enabled"><?= l('status_page.pwa_is_enabled') ?></label>
                                </div>

                                <div class="form-group custom-control custom-switch">
                                    <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="pwa_display_install_bar"
                                            name="pwa_display_install_bar"
                                        <?= $data->status_page->settings->pwa_display_install_bar ? 'checked="checked"' : null ?>
                                        <?= !$this->user->plan_settings->custom_pwa_is_enabled ? 'disabled="disabled"' : null ?>
                                    >
                                    <label class="custom-control-label" for="pwa_display_install_bar"><?= l('status_page.pwa_display_install_bar') ?></label>
                                </div>

                                <div class="form-group">
                                    <label for="pwa_display_install_bar_delay"><i class="fas fa-fw fa-bars fa-sm text-muted mr-1"></i> <?= l('status_page.pwa_display_install_bar_delay') ?></label>
                                    <div class="input-group">
                                        <input id="pwa_display_install_bar_delay" type="number" min="0" class="form-control" name="pwa_display_install_bar_delay" value="<?= $data->status_page->settings->pwa_display_install_bar_delay ?? 3 ?>" />
                                        <div class="input-group-append">
                                            <span class="input-group-text"><?= l('global.date.seconds') ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= settings()->status_pages->pwa_icon_size_limit ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), settings()->status_pages->pwa_icon_size_limit) ?>">
                                    <label for="pwa_icon"><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('status_page.pwa_icon') ?></label>
                                    <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'status_pages_pwa_icon', 'file_key' => 'pwa_icon', 'already_existing_image' => $data->status_page->settings->pwa_icon]) ?>
                                    <?= \Altum\Alerts::output_field_error('pwa_icon') ?>
                                    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('status_pages_pwa_icon')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->status_pages->pwa_icon_size_limit) ?></small>
                                </div>

                                <div class="form-group">
                                    <label for="pwa_theme_color"><i class="fas fa-fw fa-paint-brush fa-sm text-muted mr-1"></i> <?= l('status_page.pwa_theme_color') ?></label>
                                    <input type="hidden" id="pwa_theme_color" name="pwa_theme_color" class="form-control" value="<?= $data->status_page->settings->pwa_theme_color ?? '#000000' ?>" required="required" data-color-picker />
                                    <div id="settings_pwa_theme_color_pickr"></div>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endif ?>

                <button class="btn btn-sm btn-block btn-outline-blue-500 bg-blue-50 my-3" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('status_page.advanced') ?>
                </button>

                <div class="collapse" data-parent="#status_page_update" id="advanced_container">
                    <?php if(settings()->monitors_heartbeats->projects_is_enabled): ?>
                    <div class="form-group">
                        <div class="d-flex flex-wrap flex-row justify-content-between">
                            <label for="project_id"><i class="fas fa-fw fa-sm fa-project-diagram text-muted mr-1"></i> <?= l('projects.project_id') ?></label>
                            <a href="<?= url('project-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('projects.create') ?></a>
                        </div>
                        <select id="project_id" name="project_id" class="custom-select">
                            <option value=" "><?= l('global.none') ?></option>
                            <?php foreach($data->projects as $project_id => $project): ?>
                                <option value="<?= $project_id ?>" <?= $data->status_page->project_id == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="form-text text-muted"><?= l('projects.project_id_help') ?></small>
                    </div>
                    <?php endif ?>

                    <div class="form-group">
                        <label for="timezone"><i class="fas fa-fw fa-sm fa-clock text-muted mr-1"></i> <?= l('status_page.timezone') ?></label>
                        <select id="timezone" name="timezone" class="custom-select">
                            <?php foreach(DateTimeZone::listIdentifiers() as $timezone) echo '<option value="' . $timezone . '" ' . ($data->status_page->timezone == $timezone ? 'selected="selected"' : null) . '>' . $timezone . '</option>' ?>
                        </select>
                        <small class="form-text text-muted"><?= l('status_page.timezone_help') ?></small>
                    </div>

                    <div <?= $this->user->plan_settings->password_protection_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                        <div class="form-group <?= $this->user->plan_settings->password_protection_is_enabled ? null : 'container-disabled' ?>" data-password-toggle-view data-password-toggle-view-show="<?= l('global.show') ?>" data-password-toggle-view-hide="<?= l('global.hide') ?>">
                            <label for="password"><i class="fas fa-fw fa-sm fa-lock text-muted mr-1"></i> <?= l('global.password') ?></label>
                            <input type="password" id="password" name="password" class="form-control" value="<?= $data->status_page->password ?>" maxlength="64" autocomplete="new-password" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="auto_refresh"><i class="fas fa-fw fa-sync fa-sm mr-1"></i> <?= l('status_page.auto_refresh') ?></label>
                        <div class="input-group">
                            <input id="auto_refresh" type="number" min="0" max="60" name="auto_refresh" class="form-control" value="<?= $data->status_page->settings->auto_refresh ?>" />
                            <div class="input-group-append">
                                <span class="input-group-text"><?= l('global.date.minutes') ?></span>
                            </div>
                        </div>
                        <small class="form-text text-muted"><?= l('status_page.auto_refresh_help') ?></small>
                    </div>

                    <div <?= $this->user->plan_settings->removable_branding_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                        <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->removable_branding_is_enabled ? null : 'container-disabled' ?>">
                            <input id="is_removed_branding" name="is_removed_branding" type="checkbox" class="custom-control-input" <?= $data->status_page->is_removed_branding ? 'checked="checked"' : null?> <?= $this->user->plan_settings->removable_branding_is_enabled ? null : 'disabled="disabled"' ?>>
                            <label class="custom-control-label" for="is_removed_branding"><?= l('status_page.is_removed_branding') ?></label>
                            <small class="form-text text-muted"><?= l('status_page.is_removed_branding_help') ?></small>
                        </div>
                    </div>

                    <div <?= $this->user->plan_settings->custom_css_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                        <div class="form-group <?= $this->user->plan_settings->custom_css_is_enabled ? null : 'container-disabled' ?>" data-character-counter="textarea">
                            <label for="custom_css" class="d-flex justify-content-between align-items-center">
                                <span><i class="fab fa-fw fa-sm fa-css3 text-muted mr-1"></i> <?= l('global.custom_css') ?></span>
                                <small class="text-muted" data-character-counter-wrapper></small>
                            </label>
                            <textarea id="custom_css" class="form-control" name="custom_css" maxlength="10000" placeholder="<?= l('global.custom_css_placeholder') ?>"><?= $data->status_page->custom_css ?></textarea>
                            <small class="form-text text-muted"><?= l('global.custom_css_help') ?></small>
                        </div>
                    </div>

                    <div <?= $this->user->plan_settings->custom_js_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                        <div class="form-group <?= $this->user->plan_settings->custom_js_is_enabled ? null : 'container-disabled' ?>" data-character-counter="textarea">
                            <label for="custom_js" class="d-flex justify-content-between align-items-center">
                                <span><i class="fab fa-fw fa-sm fa-js-square text-muted mr-1"></i> <?= l('global.custom_js') ?></span>
                                <small class="text-muted" data-character-counter-wrapper></small>
                            </label>
                            <textarea id="custom_js" class="form-control" name="custom_js" maxlength="10000" placeholder="<?= l('global.custom_js_placeholder') ?>"><?= $data->status_page->custom_js ?></textarea>
                            <small class="form-text text-muted"><?= l('global.custom_js_help') ?></small>
                        </div>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.update') ?></button>
            </form>

        </div>
    </div>
</div>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

<?php ob_start() ?>
<script>
    'use strict';

/* Is main status_page handler */
    let is_main_status_page_handler = () => {
        if(document.querySelector('#is_main_status_page').checked) {
            document.querySelector('#url').setAttribute('disabled', 'disabled');
        } else {
            document.querySelector('#url').removeAttribute('disabled');
        }
    }

    document.querySelector('#is_main_status_page') && document.querySelector('#is_main_status_page').addEventListener('change', is_main_status_page_handler);

    /* Domain Id Handler */
    let domain_id_handler = () => {
        let domain_id = document.querySelector('select[name="domain_id"]').value;

        if(document.querySelector(`select[name="domain_id"] option[value="${domain_id}"]`).getAttribute('data-type') == '0') {
            document.querySelector('#is_main_status_page_wrapper').classList.remove('d-none');
        } else {
            document.querySelector('#is_main_status_page_wrapper').classList.add('d-none');
            document.querySelector('#is_main_status_page').checked = false;
        }

        is_main_status_page_handler();
    }

    domain_id_handler();

    document.querySelector('select[name="domain_id"]') && document.querySelector('select[name="domain_id"]').addEventListener('change', domain_id_handler);
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php include_view(THEME_PATH . 'views/partials/js_cropper.php') ?>
<?php include_view(THEME_PATH . 'views/partials/color_picker_js.php') ?>
