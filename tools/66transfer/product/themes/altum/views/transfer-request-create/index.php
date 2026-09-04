<?php defined('ALTUMCODE') || die() ?>

<div class="container">
	<?= \Altum\Alerts::output_alerts() ?>

	<?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('transfer-requests') ?>"><?= l('transfer_requests.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('transfer_request_create.breadcrumb') ?></li>
            </ol>
        </nav>
	<?php endif ?>

    <h1 class="h4 text-truncate mb-4"><i class="fas fa-fw fa-xs fa-inbox mr-1"></i> <?= l('transfer_request_create.header') ?></h1>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-fw fa-pen fa-sm text-muted mr-1"></i> <?= l('transfer.description') ?></label>
                    <input type="text" id="description" name="description" class="form-control <?= \Altum\Alerts::has_field_errors('description') ? 'is-invalid' : null ?>" value="<?= $data->values['description'] ?>" maxlength="256" />
                    <?= \Altum\Alerts::output_field_error('description') ?>
                </div>

                <?php if(count($data->domains) && (settings()->transfers->domains_is_enabled || settings()->transfers->additional_domains_is_enabled)): ?>
                    <div class="form-group">
                        <label for="domain_id"><i class="fas fa-fw fa-globe fa-sm text-muted mr-1"></i> <?= l('transfer.domain_id') ?></label>
                        <select id="domain_id" name="domain_id" class="custom-select">
                            <?php if(settings()->transfers->main_domain_is_enabled || \Altum\Authentication::is_admin()): ?>
                                <option value=" " <?= $data->values['domain_id'] ? null : 'selected="selected"' ?>><?= remove_url_protocol_from_url(SITE_URL) ?></option>
                            <?php endif ?>

                            <?php foreach($data->domains as $row): ?>
                                <option value="<?= $row->domain_id ?>" <?= $data->values['domain_id'] && $data->values['domain_id'] == $row->domain_id ? 'selected="selected"' : null ?>><?= remove_url_protocol_from_url($row->url) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div <?= $this->user->plan_settings->custom_url_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                        <div class="<?= $this->user->plan_settings->custom_url_is_enabled ? null : 'container-disabled' ?>">
                            <div class="form-group">
                                <label for="url"><i class="fas fa-fw fa-link fa-sm text-muted mr-1"></i> <?= l('transfer.url') ?></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><?= 'r/' ?></span>
                                    </div>
                                    <input type="text" id="url" name="url" class="form-control <?= \Altum\Alerts::has_field_errors('url') ? 'is-invalid' : null ?>" value="<?= $data->values['url'] ?>" maxlength="<?= ($this->user->plan_settings->url_maximum_characters ?? 64) ?>" placeholder="<?= l('global.url_slug_placeholder') ?>" onchange="update_this_value(this, get_slug)" onkeyup="update_this_value(this, get_slug)" />
                                </div>
                                <?= \Altum\Alerts::output_field_error('url') ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div <?= $this->user->plan_settings->custom_url_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                        <div class="<?= $this->user->plan_settings->custom_url_is_enabled ? null : 'container-disabled' ?>">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><?= remove_url_protocol_from_url(SITE_URL) . 'r/' ?></span>
                                    </div>
                                    <input type="text" id="url" name="url" class="form-control <?= \Altum\Alerts::has_field_errors('url') ? 'is-invalid' : null ?>" value="<?= $data->values['url'] ?>" maxlength="<?= ($this->user->plan_settings->url_maximum_characters ?? 64) ?>" placeholder="<?= l('global.url_slug_placeholder') ?>" onchange="update_this_value(this, get_slug)" onkeyup="update_this_value(this, get_slug)" aria-label="<?= l('transfer.url') ?>" />
                                    <?= \Altum\Alerts::output_field_error('url') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif ?>

                <?php if(!is_logged_in() && settings()->captcha->transfer_request_create_is_enabled): ?>
                    <div class="form-group">
                        <?php $data->captcha->display() ?>
                    </div>
                <?php endif ?>

                <ul class="nav nav-pills d-flex flex-fill flex-row mb-3" role="tablist">
                    <li class="nav-item flex-fill text-center" role="presentation" data-toggle="tooltip" title="<?= l('transfer.expiration_tab') ?>" data-tooltip-hide-on-click>
                        <a class="nav-link" id="expiration-tab" data-toggle="pill" href="#pills-expiration" role="tab" aria-controls="pills-home" aria-selected="true">
                            <i class="fas fa-fw fa-clock"></i>
                        </a>
                    </li>
                    <li class="nav-item flex-fill text-center" role="presentation" data-toggle="tooltip" title="<?= l('transfer.protection_tab') ?>" data-tooltip-hide-on-click>
                        <a class="nav-link" id="protection-tab" data-toggle="pill" href="#pills-protection" role="tab" aria-controls="pills-protection" aria-selected="false">
                            <i class="fas fa-fw fa-lock"></i>
                        </a>
                    </li>

                    <?php if(settings()->transfers->pixels_is_enabled): ?>
                        <li class="nav-item flex-fill text-center" role="presentation" data-toggle="tooltip" title="<?= l('transfer.pixels_tab') ?>" data-tooltip-hide-on-click>
                            <a class="nav-link" id="pixels-tab" data-toggle="pill" href="#pills-pixels" role="tab" aria-controls="pills-pixels" aria-selected="false">
                                <i class="fas fa-fw fa-adjust"></i>
                            </a>
                        </li>
                    <?php endif ?>

                    <li class="nav-item flex-fill text-center" role="presentation" data-toggle="tooltip" title="<?= l('transfer.advanced_tab') ?>" data-tooltip-hide-on-click>
                        <a class="nav-link" id="advanced-tab" data-toggle="pill" href="#pills-advanced" role="tab" aria-controls="pills-advanced" aria-selected="false">
                            <i class="fas fa-fw fa-user-tie"></i>
                        </a>
                    </li>

                    <?php if(settings()->notification_handlers->is_enabled): ?>
                        <li class="nav-item flex-fill text-center" role="presentation" data-toggle="tooltip" title="<?= l('transfer.notification_handlers_tab') ?>" data-tooltip-hide-on-click>
                            <a class="nav-link" id="notification-handlers-tab" data-toggle="pill" href="#pills-notification-handlers" role="tab" aria-controls="pills-notification-handlers" aria-selected="false">
                                <i class="fas fa-fw fa-bell"></i>
                            </a>
                        </li>
                    <?php endif ?>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade" id="pills-expiration" role="tabpanel" aria-labelledby="expiration-tab">

                        <?php
                        $potential_max_expiration_time = (new \DateTime())->modify('+' . $this->user->plan_settings->transfers_retention . ' days')->setTimezone(new \DateTimeZone($this->user->timezone))->format('Y-m-d H:i:s');
                        $potential_min_expiration_time_object = (new \DateTime())->setTimezone(new \DateTimeZone($this->user->timezone));
                        $potential_min_expiration_time = $potential_min_expiration_time_object->format('Y-m-d H:i:s');
                        $current_expiration_datetime = null;
                        if($data->values['expiration_datetime']) {
                            $current_expiration_datetime_object = (new \DateTime($data->values['expiration_datetime']))->setTimezone(new \DateTimeZone($this->user->timezone));
                            $current_expiration_datetime = $potential_min_expiration_time > $current_expiration_datetime_object ? $potential_min_expiration_time : $current_expiration_datetime_object->format('Y-m-d H:i:s');
                        }
                        ?>

                        <div class="form-group">
                            <label for="expiration_datetime"><i class="fas fa-fw fa-clock fa-sm text-muted mr-1"></i> <?= l('transfer.expiration_datetime') ?></label>
                            <input
                                    type="text"
                                    id="expiration_datetime"
                                    name="expiration_datetime"
                                    class="form-control"
                                    value="<?= $current_expiration_datetime ?>"
                                    autocomplete="off"
                                    data-min-date="<?= $potential_min_expiration_time ?>"
                                    data-max-date="<?= $this->user->plan_settings->transfers_retention == -1 ? null : $potential_max_expiration_time ?>"
                            />
                            <?php if($this->user->plan_settings->transfers_retention == -1): ?>
                                <small class="form-text text-muted"><?= l('transfer.expiration_datetime_help') ?></small>
                            <?php endif ?>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pills-protection" role="tabpanel" aria-labelledby="protection-tab">
                        <div <?= $this->user->plan_settings->password_protection_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                            <div class="form-group">
                                <label for="password"><i class="fas fa-fw fa-lock fa-sm text-muted mr-1"></i> <?= l('global.password') ?></label>
                                <input type="password" id="password" name="password" class="form-control" value="<?= $data->values['password'] ?>" autocomplete="new-password" maxlength="64" />
                                <small class="form-text text-muted"><?= l('transfer.password_help') ?></small>
                            </div>
                        </div>
                    </div>

                    <?php if(settings()->transfers->pixels_is_enabled): ?>
                        <div class="tab-pane fade" id="pills-pixels" role="tabpanel" aria-labelledby="pixels-tab">
                            <div <?= $this->user->plan_settings->pixels_limit != 0 ? null : get_plan_feature_disabled_info() ?>>
                                <div class="form-group <?= $this->user->plan_settings->pixels_limit != 0 ? null : 'container-disabled' ?>">
                                    <div class="d-flex flex-wrap flex-row justify-content-between">
                                        <label><i class="fas fa-fw fa-adjust fa-sm text-muted mr-1"></i> <?= l('transfer.pixels') ?></label>
                                        <a href="<?= url('pixel-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('pixels.create') ?></a>
                                    </div>
                                    <div class="row">
                                        <?php $available_pixels = require APP_PATH . 'includes/t/pixels.php'; ?>
                                        <?php foreach($data->pixels as $pixel): ?>
                                            <div class="col-12 col-lg-6">
                                                <div class="custom-control custom-checkbox my-2">
                                                    <input id="pixel_id_<?= $pixel->pixel_id ?>" name="pixels_ids[]" value="<?= $pixel->pixel_id ?>" type="checkbox" class="custom-control-input" <?= in_array($pixel->pixel_id, $data->values['pixels_ids']) ? 'checked="checked"' : null ?>>
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
                        </div>
                    <?php endif ?>

                    <?php if(settings()->notification_handlers->is_enabled): ?>
                        <div class="tab-pane fade" id="pills-notification-handlers" role="tabpanel" aria-labelledby="notification-handlers-tab">
                            <div <?= $this->user->plan_id != 'guest' ? null : get_plan_feature_disabled_info() ?>>

                                <div class="form-group <?= $this->user->plan_settings != 'guest' ? null : 'container-disabled' ?>">
                                    <div class="d-flex flex-wrap flex-row justify-content-between">
                                        <label><i class="fas fa-fw fa-bell fa-sm text-muted mr-1"></i> <?= l('transfer.submission_notification_handlers') ?></label>
                                        <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                                    </div>

                                    <div class="row">
                                        <?php foreach($data->notification_handlers as $notification_handler): ?>
                                            <div class="col-12 col-lg-6">
                                                <div class="custom-control custom-checkbox my-2">
                                                    <input id="submission_notification_handlers_ids_<?= $notification_handler->notification_handler_id ?>" name="submission_notification_handlers_ids[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->values['submission_notification_handlers_ids'] ?? []) ? 'checked="checked"' : null ?>>
                                                    <label class="custom-control-label" for="submission_notification_handlers_ids_<?= $notification_handler->notification_handler_id ?>">
                                                        <span class="mr-1"><?= $notification_handler->name ?></span>
                                                        <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach ?>
                                    </div>
                                </div>

                                <div class="form-group <?= $this->user->plan_settings != 'guest' ? null : 'container-disabled' ?>">
                                    <div class="d-flex flex-wrap flex-row justify-content-between">
                                        <label><i class="fas fa-fw fa-bell fa-sm text-muted mr-1"></i> <?= l('transfer.pageview_notification_handlers') ?></label>
                                        <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                                    </div>

                                    <div class="row">
                                        <?php foreach($data->notification_handlers as $notification_handler): ?>
                                            <div class="col-12 col-lg-6">
                                                <div class="custom-control custom-checkbox my-2">
                                                    <input id="pageview_notification_handlers_ids_<?= $notification_handler->notification_handler_id ?>" name="pageview_notification_handlers_ids[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->values['pageview_notification_handlers_ids'] ?? []) ? 'checked="checked"' : null ?>>
                                                    <label class="custom-control-label" for="pageview_notification_handlers_ids_<?= $notification_handler->notification_handler_id ?>">
                                                        <span class="mr-1"><?= $notification_handler->name ?></span>
                                                        <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    <?php endif ?>

                    <div class="tab-pane fade" id="pills-advanced" role="tabpanel" aria-labelledby="advanced-tab">
                        <?php if(settings()->transfers->projects_is_enabled): ?>
                            <div <?= $this->user->plan_settings->projects_limit != 0 ? null : get_plan_feature_disabled_info() ?>>
                                <div class="form-group <?= $this->user->plan_settings->projects_limit != 0 ? null : 'container-disabled' ?>">
                                    <div class="d-flex flex-wrap flex-row justify-content-between">
                                        <label for="project_id"><?= l('projects.project_id') ?></label>
                                        <a href="<?= url('project-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('projects.create') ?></a>
                                    </div>
                                    <select id="project_id" name="project_id" class="custom-select">
                                        <option value=" "><?= l('global.none') ?></option>
                                        <?php foreach($data->projects as $project_id => $project): ?>
                                            <option value="<?= $project_id ?>" <?= $data->values['project_id'] == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                                        <?php endforeach ?>
                                    </select>
                                    <small class="form-text text-muted"><?= l('projects.project_id_help') ?></small>
                                </div>
                            </div>
                        <?php endif ?>

                        <div <?= $this->user->plan_settings->removable_branding_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                            <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->removable_branding_is_enabled ? null : 'container-disabled' ?>">
                                <input id="is_removed_branding" name="is_removed_branding" type="checkbox" class="custom-control-input" <?= $data->values['is_removed_branding'] ? 'checked="checked"' : null?> <?= $this->user->plan_settings->removable_branding_is_enabled ? null : 'disabled="disabled"' ?>>
                                <label class="custom-control-label" for="is_removed_branding"><?= l('transfer.is_removed_branding') ?></label>
                                <small class="form-text text-muted"><?= l('transfer.is_removed_branding_help') ?></small>
                            </div>
                        </div>

                        <div <?= $this->user->plan_settings->custom_css_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                            <div class="form-group <?= $this->user->plan_settings->custom_css_is_enabled ? null : 'container-disabled' ?>" data-character-counter="textarea">
                                <label for="custom_css" class="d-flex justify-content-between align-items-center">
                                    <span><i class="fab fa-fw fa-sm fa-css3 text-muted mr-1"></i> <?= l('global.custom_css') ?></span>
                                    <small class="text-muted" data-character-counter-wrapper></small>
                                </label>
                                <textarea id="custom_css" class="form-control" name="custom_css" maxlength="10000" placeholder="<?= l('global.custom_css_placeholder') ?>"><?= $data->values['custom_css'] ?></textarea>
                                <small class="form-text text-muted"><?= l('global.custom_css_help') ?></small>
                            </div>
                        </div>

                        <div <?= $this->user->plan_settings->custom_js_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                            <div class="form-group <?= $this->user->plan_settings->custom_js_is_enabled ? null : 'container-disabled' ?>" data-character-counter="textarea">
                                <label for="custom_js" class="d-flex justify-content-between align-items-center">
                                    <span><i class="fab fa-fw fa-sm fa-js text-muted mr-1"></i> <?= l('global.custom_js') ?></span>
                                    <small class="text-muted" data-character-counter-wrapper></small>
                                </label>
                                <textarea id="custom_js" class="form-control" name="custom_js" maxlength="10000" placeholder="<?= l('global.custom_js_placeholder') ?>"><?= $data->values['custom_js'] ?></textarea>
                                <small class="form-text text-muted"><?= l('global.custom_js_help') ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.create') ?></button>
            </form>

        </div>
    </div>
</div>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/libraries/daterangepicker.min.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/daterangepicker.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    'use strict';

    let active_notification_handlers_per_resource_limit = <?= (int) $this->user->plan_settings->active_notification_handlers_per_resource_limit ?>;

    if(active_notification_handlers_per_resource_limit != -1) {
        let process_notification_handlers = () => {
            let submission_notifications = document.querySelectorAll('[name="submission_notification_handlers_ids[]"]:checked').length;
            let pageview_notifications = document.querySelectorAll('[name="pageview_notification_handlers_ids[]"]:checked').length;

            if(submission_notifications >= active_notification_handlers_per_resource_limit) {
                document.querySelectorAll('[name="submission_notification_handlers_ids[]"]:not(:checked)').forEach(element => element.setAttribute('disabled', 'disabled'));
            } else {
                document.querySelectorAll('[name="submission_notification_handlers_ids[]"]:not(:checked)').forEach(element => element.removeAttribute('disabled'));
            }

            if(pageview_notifications >= active_notification_handlers_per_resource_limit) {
                document.querySelectorAll('[name="pageview_notification_handlers_ids[]"]:not(:checked)').forEach(element => element.setAttribute('disabled', 'disabled'));
            } else {
                document.querySelectorAll('[name="pageview_notification_handlers_ids[]"]:not(:checked)').forEach(element => element.removeAttribute('disabled'));
            }
        }

        document.querySelectorAll('[name="submission_notification_handlers_ids[]"]').forEach(element => element.addEventListener('change', process_notification_handlers));
        document.querySelectorAll('[name="pageview_notification_handlers_ids[]"]').forEach(element => element.addEventListener('change', process_notification_handlers));

        process_notification_handlers();
    }

    /* Daterangepicker */
    let locale = <?= json_encode(require APP_PATH . 'includes/daterangepicker_translations.php') ?>;
    $('#expiration_datetime').daterangepicker({
        minDate: document.querySelector('#expiration_datetime').getAttribute('data-min-date'),
        maxDate: document.querySelector('#expiration_datetime').getAttribute('data-max-date'),
        alwaysShowCalendars: true,
        singleCalendar: true,
        singleDatePicker: true,
        locale: {
            ...locale,
            format: 'YYYY-MM-DD HH:mm:ss',
            cancelLabel: <?= json_encode(l('transfer.expiration_datetime_clear')) ?>
        },
        timePicker: true,
        timePicker24Hour: true,
        timePickerSeconds: true,
        autoUpdateInput: false,
    }, (start, end, label) => {
    });

    $('#expiration_datetime').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss'));
    });

    $('#expiration_datetime').on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>


<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

