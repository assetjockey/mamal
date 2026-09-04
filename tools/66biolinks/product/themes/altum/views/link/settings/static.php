<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>

<?php
/* AI improvement availability */
$static_ai_improve_is_available = settings()->links->static_ai_is_enabled;
$static_ai_prompt_characters_limit = $this->user->plan_settings->ai_static_prompts_characters_limit;
$static_ai_restore_type = isset($data->link->additional->static_ai_restore_type) && $data->link->additional->static_ai_restore_type == 'redo' ? 'redo' : 'undo';
$static_ai_restore_submit = $static_ai_restore_type == 'redo' ? l('link.static.ai_redo_submit') : l('link.static.ai_restore_submit');
$static_ai_restore_help = $static_ai_restore_type == 'redo' ? l('link.static.ai_redo_help') : l('link.static.ai_restore_help');
$static_ai_restore_icon = $static_ai_restore_type == 'redo' ? 'fa-redo' : 'fa-undo';
?>

<div class="row">
    <div class="col-12 col-xl-6">
        <?php if($static_ai_improve_is_available): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450" type="button" data-toggle="collapse" data-target="#static_ai_improve_container" aria-expanded="false" aria-controls="static_ai_improve_container">
                        <i class="fas fa-fw fa-wand-magic fa-sm mr-1"></i> <?= l('link.static.ai_improve') ?>
                    </button>

                    <div class="collapse mt-4" id="static_ai_improve_container">
                        <form id="static_ai_improve" name="static_ai_improve" action="" method="post" role="form">
                            <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
                            <input type="hidden" name="request_type" value="static_ai_improve" />
                            <input type="hidden" name="link_id" value="<?= $data->link->link_id ?>" />

                            <div class="notification-container"></div>

                            <div class="form-group" <?= $static_ai_prompt_characters_limit == -1 ? null : 'data-character-counter="textarea"' ?>>
                                <label for="static_ai_improve_input" class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-fw fa-sm fa-wand-magic text-muted mr-1"></i> <?= l('link.static.ai_improve') ?></span>
                                    <?php if($static_ai_prompt_characters_limit != -1): ?>
                                        <small class="text-muted" data-character-counter-wrapper></small>
                                    <?php endif ?>
                                </label>
                                <textarea id="static_ai_improve_input" name="input" class="form-control" <?= $static_ai_prompt_characters_limit == -1 ? null : 'maxlength="' . $static_ai_prompt_characters_limit . '"' ?> rows="4" placeholder="<?= l('link.static.ai_improve_placeholder') ?>" required="required"></textarea>
                            </div>

                            <button type="submit" name="submit" class="btn btn-block btn-primary" data-is-ajax>
                                <?= l('link.static.ai_improve_submit') ?>
                            </button>
                        </form>

                        <form id="static_ai_restore" name="static_ai_restore" action="" method="post" role="form" class="<?= isset($data->link->additional->static_ai_backup_folder) ? 'mt-4' : 'd-none mt-4' ?>">
                            <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
                            <input type="hidden" name="request_type" value="static_ai_restore" />
                            <input type="hidden" name="link_id" value="<?= $data->link->link_id ?>" />

                            <div class="notification-container"></div>

                            <small class="form-text text-muted mb-2" data-static-ai-restore-help><?= $static_ai_restore_help ?></small>

                            <button type="submit" name="submit" class="btn btn-block btn-gray-200" data-is-ajax>
                                <i class="fas fa-fw fa-sm <?= $static_ai_restore_icon ?> mr-1" data-static-ai-restore-icon></i> <span data-static-ai-restore-submit><?= $static_ai_restore_submit ?></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <div class="card">
            <div class="card-body">

                <form id="update_static" name="update_static" action="" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
                    <input type="hidden" name="request_type" value="update" />
                    <input type="hidden" name="type" value="static" />
                    <input type="hidden" name="link_id" value="<?= $data->link->link_id ?>" />

                    <div class="notification-container"></div>

                    <?php //if($data->link->additional->mode == 'file'): ?>
                    <div class="form-group" data-file-input-wrapper-size-limit="<?= settings()->links->static_size_limit ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), settings()->links->static_size_limit) ?>">
                        <label for="file"><i class="fas fa-fw fa-sm fa-file-zipper text-muted mr-1"></i> <?= l('create_static_modal.file') ?></label>
                        <input id="file" type="file" name="file" accept="<?= \Altum\Uploads::get_whitelisted_file_extensions_accept('static') ?>" class="form-control-file altum-file-input" />
                        <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('static')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->links->static_size_limit) ?></small>
                        <small class="form-text text-muted"><?= sprintf(l('create_static_modal.file.inside_zip_whitelisted_file_extensions'), \Altum\Uploads::array_to_list_format(\Altum\Uploads::$uploads['static']['inside_zip_whitelisted_file_extensions'])) ?></small>
                        <small class="form-text text-muted"><?= l('create_static_modal.file.help1') ?></small>
                        <small class="form-text text-muted"><?= l('create_static_modal.file.help2') ?></small>
                    </div>
                    <?php //endif ?>

                    <div class="form-group">
                        <label for="url"><i class="fas fa-fw fa-bolt fa-sm text-muted mr-1"></i> <?= l('link.settings.url') ?></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <?php if (!empty($data->domains)): ?>
                                    <select name="domain_id" class="appearance-none custom-select form-control input-group-text">
                                        <?php if(settings()->links->main_domain_is_enabled || \Altum\Authentication::is_admin()): ?>
                                            <option value=" " <?= $data->link->domain ? 'selected="selected"' : null ?> data-full-url="<?= SITE_URL ?>"><?= remove_url_protocol_from_url(SITE_URL) ?></option>
                                        <?php endif ?>

                                        <?php foreach($data->domains as $row): ?>
                                            <option value="<?= $row->domain_id ?>" <?= $data->link->domain && $row->domain_id == $data->link->domain->domain_id ? 'selected="selected"' : null ?>  data-full-url="<?= $row->url ?>" data-type="<?= $row->type ?>"><?= remove_url_protocol_from_url($row->url) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                <?php else: ?>
                                    <span class="input-group-text"><?= remove_url_protocol_from_url(SITE_URL) ?></span>
                                <?php endif ?>
                            </div>

                            <input
                                    id="url"
                                    type="text"
                                    class="form-control"
                                    name="url"
                                    placeholder="<?= l('global.url_slug_placeholder') ?>"
                                    value="<?= $data->link->url ?>"
                                    maxlength="<?= $this->user->plan_settings->url_maximum_characters ?? 64 ?>"
                                    onchange="update_this_value(this, get_slug)"
                                    onkeyup="update_this_value(this, get_slug)"
                                <?= !$this->user->plan_settings->custom_url ? 'readonly="readonly"' : null ?>
                                <?= $this->user->plan_settings->custom_url ? null : get_plan_feature_disabled_info() ?>
                            />
                        </div>
                        <small class="form-text text-muted"><?= l('link.settings.url_help') ?></small>
                    </div>

                    <?php if (!empty($data->domains)): ?>
                        <div id="is_main_link_wrapper" class="form-group custom-control custom-switch <?= $data->link->domain_id && $data->domains[$data->link->domain_id]->type == '0' ? null : 'd-none' ?>">
                            <input id="is_main_link" name="is_main_link" type="checkbox" class="custom-control-input" <?= $data->link->domain_id && $data->domains[$data->link->domain_id]->link_id == $data->link->link_id ? 'checked="checked"' : null ?>>
                            <label class="custom-control-label" for="is_main_link"><?= l('link.settings.is_main_link') ?></label>
                            <small class="form-text text-muted"><?= l('link.settings.is_main_link_help') ?></small>
                        </div>
                    <?php endif ?>

                    <?php if(settings()->links->pixels_is_enabled): ?>
                        <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#pixels_container" aria-expanded="false" aria-controls="pixels_container">
                            <i class="fas fa-fw fa-adjust fa-sm mr-1"></i> <?= l('link.settings.pixels_header') ?>
                        </button>

                        <div class="collapse" data-parent="#update_static" id="pixels_container">
                            <div class="form-group">
                                <div class="d-flex flex-wrap flex-row justify-content-between">
                                    <label><i class="fas fa-fw fa-sm fa-adjust text-muted mr-1"></i> <?= l('link.settings.pixels_ids') ?></label>
                                    <a href="<?= url('pixel-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('pixels.create') ?></a>
                                </div>

                                <div class="row">
                                    <?php $available_pixels = require APP_PATH . 'includes/pixels.php'; ?>
                                    <?php foreach($data->pixels as $pixel): ?>
                                        <div class="col-12 col-lg-6">
                                            <div class="custom-control custom-checkbox my-2">
                                                <input id="pixel_id_<?= $pixel->pixel_id ?>" name="pixels_ids[]" value="<?= $pixel->pixel_id ?>" type="checkbox" class="custom-control-input" <?= in_array($pixel->pixel_id, $data->link->pixels_ids) ? 'checked="checked"' : null ?>>
                                                <label class="custom-control-label d-flex align-items-center" for="pixel_id_<?= $pixel->pixel_id ?>">
                                                    <span class="text-truncate" title="<?= $pixel->name ?>"><?= $pixel->name ?></span>
                                                    <small class="badge badge-light ml-1" data-toggle="tooltip" title="<?= $available_pixels[$pixel->type]['name'] ?>">
                                                        <i class="<?= $available_pixels[$pixel->type]['icon'] ?> fa-fw fa-sm" style="color: <?= $available_pixels[$pixel->type]['color'] ?>"></i>
                                                    </small>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>

                    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#temporary_url_container" aria-expanded="false" aria-controls="temporary_url_container">
                        <i class="fas fa-fw fa-clock fa-sm mr-1"></i> <?= l('link.settings.temporary_url_header') ?>
                    </button>

                    <div class="collapse" data-parent="#update_static" id="temporary_url_container">
                        <div <?= $this->user->plan_settings->temporary_url_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                            <div class="<?= $this->user->plan_settings->temporary_url_is_enabled ? null : 'container-disabled' ?>">
                                <div class="form-group custom-control custom-switch">
                                    <input
                                            id="schedule"
                                            name="schedule"
                                            type="checkbox"
                                            class="custom-control-input"
                                        <?= $data->link->settings->schedule && !empty($data->link->start_date) && !empty($data->link->end_date) ? 'checked="checked"' : null ?>
                                        <?= $this->user->plan_settings->temporary_url_is_enabled ? null : 'disabled="disabled"' ?>
                                    >
                                    <label class="custom-control-label" for="schedule"><?= l('link.settings.schedule') ?></label>
                                    <small class="form-text text-muted"><?= l('link.settings.schedule_help') ?></small>
                                </div>
                            </div>
                        </div>

                        <div id="schedule_container" style="display: none;">
                            <div <?= $this->user->plan_settings->temporary_url_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                                <div class="<?= $this->user->plan_settings->temporary_url_is_enabled ? null : 'container-disabled' ?>">
                                    <div class="row">
                                        <div class="col">
                                            <div class="form-group">
                                                <label><i class="fas fa-fw fa-hourglass-start fa-sm text-muted mr-1"></i> <?= l('link.settings.start_date') ?></label>
                                                <input
                                                        type="text"
                                                        class="form-control"
                                                        name="start_date"
                                                        value="<?= \Altum\Date::get($data->link->start_date, 1) ?>"
                                                        placeholder="<?= l('link.settings.start_date') ?>"
                                                        autocomplete="off"
                                                        data-daterangepicker
                                                >
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="form-group">
                                                <label><i class="fas fa-fw fa-hourglass-end fa-sm text-muted mr-1"></i> <?= l('link.settings.end_date') ?></label>
                                                <input
                                                        type="text"
                                                        class="form-control"
                                                        name="end_date"
                                                        value="<?= \Altum\Date::get($data->link->end_date, 1) ?>"
                                                        placeholder="<?= l('link.settings.end_date') ?>"
                                                        autocomplete="off"
                                                        data-daterangepicker
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div <?= $this->user->plan_settings->temporary_url_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                            <div class="form-group <?= $this->user->plan_settings->temporary_url_is_enabled ? null : 'container-disabled' ?>">
                                <label for="clicks_limit"><i class="fas fa-fw fa-mouse fa-sm text-muted mr-1"></i> <?= l('link.settings.clicks_limit') ?></label>
                                <input id="clicks_limit" type="number" class="form-control" name="clicks_limit" value="<?= $data->link->settings->clicks_limit ?>" />
                                <small class="form-text text-muted"><?= l('link.settings.clicks_limit_help') ?></small>
                            </div>
                        </div>

                        <div <?= $this->user->plan_settings->temporary_url_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                            <div class="form-group <?= $this->user->plan_settings->temporary_url_is_enabled ? null : 'container-disabled' ?>">
                                <label for="expiration_url"><i class="fas fa-fw fa-hourglass-end fa-sm text-muted mr-1"></i> <?= l('link.settings.expiration_url') ?></label>
                                <input id="expiration_url" type="url" class="form-control" name="expiration_url" value="<?= $data->link->settings->expiration_url ?>" maxlength="2048" />
                                <small class="form-text text-muted"><?= l('link.settings.expiration_url_help') ?></small>
                            </div>
                        </div>

                    </div>

                    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#protection_container" aria-expanded="false" aria-controls="protection_container">
                        <i class="fas fa-fw fa-user-shield fa-sm mr-1"></i> <?= l('link.settings.protection_header') ?>
                    </button>

                    <div class="collapse" data-parent="#update_static" id="protection_container">
                        <div <?= $this->user->plan_settings->password ? null : get_plan_feature_disabled_info() ?>>
                            <div class="<?= $this->user->plan_settings->password ? null : 'container-disabled' ?>">
                                <div class="form-group" data-password-toggle-view data-password-toggle-view-show="<?= l('global.show') ?>" data-password-toggle-view-hide="<?= l('global.hide') ?>">
                                    <label for="qweasdzxc"><i class="fas fa-fw fa-key fa-sm text-muted mr-1"></i> <?= l('global.password') ?></label>
                                    <input id="qweasdzxc" type="password" class="form-control" name="qweasdzxc" maxlength="64" value="<?= $data->link->settings->password ?>" autocomplete="new-password" <?= !$this->user->plan_settings->password ? 'disabled="disabled"': null ?> />
                                    <small class="form-text text-muted"><?= l('link.settings.password_help') ?></small>
                                </div>
                            </div>
                        </div>

                        <div <?= $this->user->plan_settings->sensitive_content ? null : get_plan_feature_disabled_info() ?>>
                            <div class="<?= $this->user->plan_settings->sensitive_content ? null : 'container-disabled' ?>">
                                <div class="form-group custom-control custom-switch">
                                    <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="sensitive_content"
                                            name="sensitive_content"
                                        <?= !$this->user->plan_settings->sensitive_content ? 'disabled="disabled"': null ?>
                                        <?= $data->link->settings->sensitive_content ? 'checked="checked"' : null ?>
                                    >
                                    <label class="custom-control-label" for="sensitive_content"><?= l('link.settings.sensitive_content') ?></label>
                                    <small class="form-text text-muted"><?= l('link.settings.sensitive_content_help') ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#seo_container" aria-expanded="false" aria-controls="seo_container">
                        <i class="fas fa-fw fa-search-plus fa-sm mr-1"></i> <?= l('link.settings.seo_header') ?>
                    </button>

                    <div class="collapse" data-parent="#update_static" id="seo_container">
                        <div <?= $this->user->plan_settings->seo ? null : get_plan_feature_disabled_info() ?>>
                            <div class="<?= $this->user->plan_settings->seo ? null : 'container-disabled' ?>">
                                <div class="form-group custom-control custom-switch">
                                    <input id="seo_block" name="seo_block" type="checkbox" class="custom-control-input" <?= $data->link->settings->seo->block ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label" for="seo_block"><?= l('link.settings.seo_block') ?></label>
                                    <small class="form-text text-muted"><?= l('link.settings.seo_block_help') ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                        <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('link.settings.advanced_header') ?>
                    </button>

                    <div class="collapse" data-parent="#update_static" id="advanced_container">
                            <?php if(settings()->links->email_reports_is_enabled && settings()->notification_handlers->is_enabled): ?>
                                <div <?= $this->user->plan_settings->email_reports_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                                    <div class="form-group <?= $this->user->plan_settings->email_reports_is_enabled ? null : 'container-disabled' ?>">
                                        <div class="d-flex flex-wrap flex-row justify-content-between">
                                            <label><i class="fas fa-fw fa-sm fa-envelope-open-text text-muted mr-1"></i> <?= l('global.plan_settings.email_reports_is_enabled_' . settings()->links->email_reports_is_enabled) ?></label>
                                            <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                                        </div>
                                        <div class="mb-2"><small class="text-muted"><?= l('link.settings.email_reports_is_enabled_help') ?></small></div>

                                        <div class="row">
                                            <?php foreach($data->notification_handlers as $notification_handler): ?>
                                                <?php if($notification_handler->type != 'email') continue ?>
                                                <div class="col-12 col-lg-6">
                                                    <div class="custom-control custom-checkbox my-2">
                                                        <input id="<?= 'email_reports_' . $notification_handler->notification_handler_id ?>" name="email_reports[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->link->email_reports) ? 'checked="checked"' : null ?>>
                                                        <label class="custom-control-label" for="<?= 'email_reports_' . $notification_handler->notification_handler_id ?>">
                                                            <span class="mr-1"><?= $notification_handler->name ?></span>
                                                            <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif ?>

                        <?php if(settings()->links->projects_is_enabled): ?>
                        <div class="form-group">
                            <div class="d-flex flex-wrap flex-row justify-content-between">
                                <label for="project_id"><i class="fas fa-fw fa-sm fa-project-diagram text-muted mr-1"></i> <?= l('projects.project_id') ?></label>
                                <a href="<?= url('project-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('projects.create') ?></a>
                            </div>
                            <select id="project_id" name="project_id" class="custom-select">
                                <option value=" "><?= l('global.none') ?></option>
                                <?php foreach($data->projects as $row): ?>
                                    <option value="<?= $row->project_id ?>" <?= $data->link->project_id == $row->project_id ? 'selected="selected"' : null?>><?= $row->name ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <?php endif ?>

                        <?php if(settings()->links->splash_page_is_enabled): ?>
                            <div <?= $this->user->plan_settings->splash_pages_limit ? null : get_plan_feature_disabled_info() ?>>
                                <div class="<?= $this->user->plan_settings->splash_pages_limit ? null : 'container-disabled' ?>">
                                    <div class="form-group">
                                        <div class="d-flex flex-wrap flex-row justify-content-between">
                                            <label for="splash_page_id"><i class="fas fa-fw fa-sm fa-droplet text-muted mr-1"></i> <?= l('splash_pages.splash_page_id') ?></label>
                                            <a href="<?= url('splash-pages') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('splash_pages.create') ?></a>
                                        </div>
                                        <select id="splash_page_id" name="splash_page_id" class="custom-select">
                                            <option value=" "><?= l('global.none') ?></option>
                                            <?php foreach($data->splash_pages as $row): ?>
                                                <option value="<?= $row->splash_page_id ?>" <?= $data->link->splash_page_id == $row->splash_page_id ? 'selected="selected"' : null?>><?= $row->name ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>
                    </div>

                    <div class="mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-primary" data-is-ajax><?= l('global.update') ?></button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6 mt-5 mt-xl-0 d-flex justify-content-center justify-content-xl-end">
        <div class="biolink-preview-container">
            <div class="biolink-preview sticky">
                <div class="biolink-preview-iframe-container">
                    <div id="biolink_preview_iframe_loading" class="biolink-preview-iframe-loading d-none"><div class="spinner-border bg-primary" role="status"></div></div>
                    <iframe id="biolink_preview_iframe" class="biolink-preview-iframe" src="<?= $data->link->full_url . '&preview=' . $data->link->token ?>"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>


<?php $html = ob_get_clean() ?>


<?php ob_start() ?>
<script>
    'use strict';

    /* Settings Tab */
    let schedule_handler = () => {
        if($('#schedule').is(':checked')) {
            $('#schedule_container').show();
        } else {
            $('#schedule_container').hide();
        }
    };

    $('#schedule').on('change', schedule_handler);

    schedule_handler();

    /* Daterangepicker */
    let locale = <?= json_encode(require APP_PATH . 'includes/daterangepicker_translations.php') ?>;
    $('[data-daterangepicker]').daterangepicker({
        minDate: "<?= (new \DateTime('', new \DateTimeZone(\Altum\Date::$default_timezone)))->setTimezone(new \DateTimeZone($this->user->timezone))->format('Y-m-d H:i:s'); ?>",
        alwaysShowCalendars: true,
        singleCalendar: true,
        singleDatePicker: true,
        locale: {...locale, format: 'YYYY-MM-DD HH:mm:ss'},
        timePicker: true,
        timePicker24Hour: true,
        timePickerSeconds: true,
    }, (start, end, label) => {
    });

    /* Form handling */
    $('form[name="update_static"]').on('submit', event => {
        let form = $(event.currentTarget)[0];
        let data = new FormData(form);

        let notification_container = event.currentTarget.querySelector('.notification-container');
        notification_container.innerHTML = '';
        pause_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

        $.ajax({
            type: 'POST',
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            cache: false,
            url: `${url}link-ajax`,
            data: data,
            dataType: 'json',
            success: (data) => {
                display_notifications(data.message, data.status, notification_container);
                notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

                if(data.status == 'success') {
                    update_main_url(data.details.url);
                    event.currentTarget.querySelector('input[type="file"]').value = '';
                }

                refresh_preview();
            },
            error: () => {
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));
                display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            },
        });

        event.preventDefault();
    });

    <?php if($static_ai_improve_is_available): ?>
    let update_static_ai_restore_button = (static_ai_restore, details) => {
        if(!details) {
            return;
        }

        if(details.static_ai_restore_help) {
            static_ai_restore.querySelector('[data-static-ai-restore-help]').innerHTML = details.static_ai_restore_help;
        }

        if(details.static_ai_restore_submit && details.static_ai_restore_icon) {
            let static_ai_restore_button = static_ai_restore.querySelector('[type="submit"][name="submit"]');
            static_ai_restore_button.innerHTML = '<i class="fas fa-fw fa-sm ' + details.static_ai_restore_icon + ' mr-1" data-static-ai-restore-icon></i> <span data-static-ai-restore-submit>' + details.static_ai_restore_submit + '</span>';

            if(typeof FontAwesome !== 'undefined' && FontAwesome.dom && FontAwesome.dom.i2svg) {
                FontAwesome.dom.i2svg({ node: static_ai_restore_button });
            }
        }
    }

    $('form[name="static_ai_improve"]').on('submit', event => {
        let form = $(event.currentTarget)[0];
        let data = new FormData(form);
        let notification_container = event.currentTarget.querySelector('.notification-container');

        notification_container.innerHTML = '';
        pause_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

        $.ajax({
            type: 'POST',
            processData: false,
            contentType: false,
            cache: false,
            url: `${url}link-ajax`,
            data: data,
            dataType: 'json',
            success: data => {
                display_notifications(data.message, data.status, notification_container);
                notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

                if(data.status == 'success') {
                    let static_ai_restore = document.querySelector('form[name="static_ai_restore"]');
                    static_ai_restore.classList.remove('d-none');
                    update_static_ai_restore_button(static_ai_restore, data.details);

                    refresh_preview();
                }
            },
            error: () => {
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));
                display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            },
        });

        event.preventDefault();
    });

    $('form[name="static_ai_restore"]').on('submit', event => {
        let form = $(event.currentTarget)[0];
        let data = new FormData(form);
        let notification_container = event.currentTarget.querySelector('.notification-container');

        notification_container.innerHTML = '';
        pause_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

        $.ajax({
            type: 'POST',
            processData: false,
            contentType: false,
            cache: false,
            url: `${url}link-ajax`,
            data: data,
            dataType: 'json',
            success: data => {
                display_notifications(data.message, data.status, notification_container);
                notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

                if(data.status == 'success') {
                    update_static_ai_restore_button(event.currentTarget, data.details);

                    refresh_preview();
                }
            },
            error: () => {
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));
                display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            },
        });

        event.preventDefault();
    });
    <?php endif ?>

    let refresh_preview = () => {
        if(!document.querySelector('#biolink_preview_iframe')) {
            return;
        }

        /* Add loader */
        document.querySelector('#biolink_preview_iframe_loading').classList.remove('d-none');

        /* Refresh iframe */
        let biolink_preview_iframe = document.querySelector('#biolink_preview_iframe');

        setTimeout(() => {
            biolink_preview_iframe.setAttribute('src', biolink_preview_iframe.getAttribute('src'));
        }, 750)

        biolink_preview_iframe.onload = () => {
            document.querySelector('#biolink_preview_iframe').dispatchEvent(new Event('refreshed'));
            document.querySelector('#biolink_preview_iframe_loading').classList.add('d-none');
        }
    }
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
