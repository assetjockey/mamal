<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

defined('ALTUMCODE') || die();

/* Create the content for each tab */
$html = [];

/* Extra Javascript needed */
$javascript = '';
?>

<?php /* Basic Tab */ ?>
<?php ob_start() ?>
<div class="form-group">
    <label for="settings_name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('notification.settings.name') ?></label>
    <input type="text" id="settings_name" name="name" class="form-control" value="<?= $data->notification->name ?>" maxlength="256" required="required" />
</div>

<div class="form-group">
    <label for="settings_title"><i class="fas fa-fw fa-sm fa-heading text-muted mr-1"></i> <?= l('notification.settings.title') ?></label>
    <div class="input-group">
        <input type="text" id="settings_title" name="title" class="form-control" value="<?= e($data->notification->settings->title) ?>" maxlength="256" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#title_translate_container" aria-expanded="false" aria-controls="title_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
    <small class="form-text text-muted" data-toggle="tooltip" title="<?= l('notification.settings.html_info_tooltip') ?>"><?= l('notification.settings.html_info') ?></small>
    <small class="form-text text-muted"><?= l('global.spintax_help') ?></small>
</div>

<div class="collapse" id="title_translate_container" data-translation-container="title">
    <?= htmlspecialchars(json_encode($data->notification->settings->translations->title ?? ''), ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="form-group">
    <label for="settings_description"><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('notification.settings.description') ?></label>
    <div class="input-group">
        <input type="text" id="settings_description" name="description" class="form-control" value="<?= e($data->notification->settings->description) ?>" maxlength="512" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#description_translate_container" aria-expanded="false" aria-controls="description_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
    <small class="form-text text-muted" data-toggle="tooltip" title="<?= l('notification.settings.html_info_tooltip') ?>"><?= l('notification.settings.html_info') ?></small>
    <small class="form-text text-muted"><?= l('global.spintax_help') ?></small>
</div>

<div class="collapse" id="description_translate_container" data-translation-container="description">
    <?= htmlspecialchars(json_encode($data->notification->settings->translations->description ?? ''), ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= settings()->notifications->image_size_limit ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), settings()->notifications->image_size_limit) ?>">
    <label for="image"><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('notification.settings.image') ?></label>
    <?= include_view(THEME_PATH . 'views/partials/custom_file_image_input.php', ['uploads_file_key' => 'notifications', 'file_key' => 'image', 'already_existing_image' => $data->notification->settings->image]) ?>
    <?= \Altum\Alerts::output_field_error('image') ?>
    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('notifications')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->notifications->image_size_limit) ?></small>
</div>

<div class="form-group">
    <label for="settings_image_alt"><i class="fas fa-fw fa-sm fa-comment text-muted mr-1"></i> <?= l('notification.settings.image_alt') ?></label>
    <input type="text" id="settings_image_alt" name="image_alt" class="form-control" value="<?= $data->notification->settings->image_alt ?>" maxlength="100" />
    <small class="form-text text-muted"><?= l('notification.settings.image_alt_help') ?></small>
</div>


<div class="form-group">
    <label for="settings_input_placeholder"><i class="fas fa-fw fa-sm fa-i-cursor text-muted mr-1"></i> <?= l('notification.settings.input_placeholder') ?></label>
    <div class="input-group">
        <input type="text" id="settings_input_placeholder" name="input_placeholder" class="form-control" value="<?= e($data->notification->settings->input_placeholder) ?>" maxlength="128" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#input_placeholder_translate_container" aria-expanded="false" aria-controls="input_placeholder_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
</div>

<div class="collapse" id="input_placeholder_translate_container" data-translation-container="input_placeholder">
    <?= htmlspecialchars(json_encode($data->notification->settings->translations->input_placeholder ?? ''), ENT_QUOTES, 'UTF-8') ?>
</div>


<div class="form-group">
    <label for="settings_button_text"><i class="fas fa-fw fa-sm fa-quote-left text-muted mr-1"></i> <?= l('notification.settings.button_text') ?></label>
    <div class="input-group">
        <input type="text" id="settings_button_text" name="button_text" class="form-control" value="<?= e($data->notification->settings->button_text) ?>" maxlength="128" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#button_text_translate_container" aria-expanded="false" aria-controls="button_text_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
    <small class="form-text text-muted" data-toggle="tooltip" title="<?= l('notification.settings.html_info_tooltip') ?>"><?= l('notification.settings.html_info') ?></small>
    <small class="form-text text-muted"><?= l('global.spintax_help') ?></small>
</div>

<div class="collapse" id="button_text_translate_container" data-translation-container="button_text">
    <?= htmlspecialchars(json_encode($data->notification->settings->translations->button_text ?? ''), ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="form-group">
    <div class="custom-control custom-switch">
        <input id="settings_show_agreement" name="show_agreement" type="checkbox" class="custom-control-input" <?= $data->notification->settings->show_agreement ? 'checked="checked"' : null ?>>
        <label class="custom-control-label" for="settings_show_agreement"><?= l('notification.settings.show_agreement') ?></label>
        <div><small class="form-text text-muted"><?= l('notification.settings.show_agreement_help') ?></small></div>
    </div>
</div>

<div id="agreement">
    <div class="form-group">
    <label for="settings_agreement_text"><i class="fas fa-fw fa-sm fa-file-signature text-muted mr-1"></i> <?= l('notification.settings.agreement_text') ?></label>
    <div class="input-group">
        <input type="text" id="settings_agreement_text" name="agreement_text" class="form-control" value="<?= e($data->notification->settings->agreement_text) ?>" maxlength="256" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#agreement_text_translate_container" aria-expanded="false" aria-controls="agreement_text_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
</div>

<div class="collapse" id="agreement_text_translate_container" data-translation-container="agreement_text">
    <?= htmlspecialchars(json_encode($data->notification->settings->translations->agreement_text ?? ''), ENT_QUOTES, 'UTF-8') ?>
</div>

    <div class="form-group">
        <label for="settings_agreement_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('notification.settings.agreement_url') ?></label>
        <input type="url" id="settings_agreement_url" name="agreement_url" class="form-control" value="<?= $data->notification->settings->agreement_url ?>" placeholder="<?= l('global.url_placeholder') ?>" maxlength="2048" />
    </div>
</div>

<div class="form-group">
    <label for="settings_thank_you_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('notification.settings.thank_you_url') ?></label>
    <input type="url" id="settings_thank_you_url" name="thank_you_url" class="form-control" value="<?= $data->notification->settings->thank_you_url ?>" placeholder="<?= l('global.url_placeholder') ?>" />
    <small class="form-text text-muted"><?= l('notification.settings.thank_you_url_help') ?></small>
</div>
<?php $html['basic'] = ob_get_clean() ?>


<?php /* Customize Tab */ ?>
<?php ob_start() ?>
<div class="form-group">
    <label for="settings_title_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.title_color') ?></label>
    <input type="hidden" id="settings_title_color" name="title_color" class="form-control" value="<?= $data->notification->settings->title_color ?>" />
    <div id="settings_title_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_description_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.description_color') ?></label>
    <input type="hidden" id="settings_description_color" name="description_color" class="form-control" value="<?= $data->notification->settings->description_color ?>" />
    <div id="settings_description_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.background_color') ?></label>
    <input type="hidden" id="settings_background_color" name="background_color" class="form-control" value="<?= $data->notification->settings->background_color ?>" />
    <div id="settings_background_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_button_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.button_background_color') ?></label>
    <input type="hidden" id="settings_button_background_color" name="button_background_color" class="form-control" value="<?= $data->notification->settings->button_background_color ?>" />
    <div id="settings_button_background_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_button_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.button_color') ?></label>
    <input type="hidden" id="settings_button_color" name="button_color" class="form-control" value="<?= $data->notification->settings->button_color ?>" />
    <div id="settings_button_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_close_button_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.close_button_color') ?></label>
    <input type="hidden" id="settings_close_button_color" name="close_button_color" class="form-control" value="<?= $data->notification->settings->close_button_color ?>" />
    <div id="settings_close_button_color_pickr"></div>
</div>

<div class="form-group" data-range-counter data-range-counter-suffix="px">
    <label for="settings_internal_padding"><i class="fas fa-fw fa-expand-arrows-alt fa-sm text-muted mr-1"></i> <?= l('notification.settings.internal_padding') ?></label>
    <input type="range" min="5" max="25" id="settings_internal_padding" name="internal_padding" class="form-control-range" value="<?= $data->notification->settings->internal_padding ?>" />
</div>

<div class="form-group" data-range-counter data-range-counter-suffix="px">
    <label for="background_blur"><i class="fas fa-fw fa-low-vision fa-sm text-muted mr-1"></i> <?= l('notification.settings.background_blur') ?></label>
    <input id="background_blur" type="range"  min="0" max="30" class="form-control-range" name="background_blur" value="<?= $data->notification->settings->background_blur ?? 0 ?>" />
    <small class="form-text text-muted"><?= l('notification.settings.background_blur_help') ?></small>
</div>

<button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#dark_mode_container" aria-expanded="false" aria-controls="dark_mode_container">
    <i class="fas fa-fw fa-moon fa-sm mr-1"></i> <?= l('notification.settings.dark_mode') ?>
</button>

<div class="collapse" data-parent="#tab_customize" id="dark_mode_container">
    <div class="form-group custom-control custom-switch">
        <input
                type="checkbox"
                class="custom-control-input"
                id="dark_mode_is_enabled"
                name="dark_mode_is_enabled"
                <?= $data->notification->settings->dark_mode_is_enabled ? 'checked="checked"' : null ?>
        >
        <label class="custom-control-label" for="dark_mode_is_enabled"><?= l('notification.settings.dark_mode_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('notification.settings.dark_mode_is_enabled_help') ?></small>
    </div>

    <div class="form-group">
        <label for="dark_mode_title_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.title_color') ?></label>
        <input type="hidden" id="dark_mode_title_color" name="dark_mode_title_color" class="form-control" value="<?= $data->notification->settings->dark_mode_title_color ?>" />
        <div id="dark_mode_title_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_description_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.description_color') ?></label>
        <input type="hidden" id="dark_mode_description_color" name="dark_mode_description_color" class="form-control" value="<?= $data->notification->settings->dark_mode_description_color ?>" />
        <div id="dark_mode_description_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.background_color') ?></label>
        <input type="hidden" id="dark_mode_background_color" name="dark_mode_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_background_color ?>" />
        <div id="dark_mode_background_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_button_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.button_background_color') ?></label>
        <input type="hidden" id="dark_mode_button_background_color" name="dark_mode_button_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_button_background_color ?>" />
        <div id="dark_mode_button_background_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_button_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.button_color') ?></label>
        <input type="hidden" id="dark_mode_button_color" name="dark_mode_button_color" class="form-control" value="<?= $data->notification->settings->dark_mode_button_color ?>" />
        <div id="dark_mode_button_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_close_button_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.close_button_color') ?></label>
        <input type="hidden" id="dark_mode_close_button_color" name="dark_mode_close_button_color" class="form-control" value="<?= $data->notification->settings->dark_mode_close_button_color ?>" />
        <div id="dark_mode_close_button_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_shadow_color"><i class="fas fa-fw fa-cloud-sun fa-sm text-muted mr-1"></i> <?= l('notification.settings.shadow_color') ?></label>
        <input type="hidden" id="dark_mode_shadow_color" name="dark_mode_shadow_color" class="form-control border-left-0" value="<?= $data->notification->settings->dark_mode_shadow_color ?>" />
        <div id="dark_mode_shadow_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_border_color"><i class="fas fa-fw fa-fill fa-sm text-muted mr-1"></i> <?= l('notification.settings.border_color') ?></label>
        <input type="hidden" id="dark_mode_border_color" name="dark_mode_border_color" class="form-control border-left-0" value="<?= $data->notification->settings->dark_mode_border_color ?>" />
        <div id="dark_mode_border_color_pickr"></div>
    </div>
</div>

<button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#borders_container" aria-expanded="false" aria-controls="borders_container">
    <i class="fas fa-fw fa-border-style fa-sm mr-1"></i> <?= l('notification.settings.borders') ?>
</button>

<div class="collapse" data-parent="#tab_customize" id="borders_container">
    <div class="form-group">
        <label for="settings_shadow"><i class="fas fa-fw fa-cloud fa-sm text-muted mr-1"></i> <?= l('notification.settings.shadow') ?></label>
        <div class="row mx-n2 btn-group-toggle" data-toggle="buttons">
            <div class="col-4 p-2">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= ($data->notification->settings->shadow  ?? null) == '' ? 'active"' : null?>">
                    <input type="radio" name="shadow" value="" class="custom-control-input" <?= ($data->notification->settings->shadow  ?? null) == '' ? 'checked="checked"' : null?> />
                    <?= l('global.none') ?>
                </label>
            </div>
            <div class="col-4 p-2">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= ($data->notification->settings->shadow  ?? null) == 'subtle' ? 'active' : null?>">
                    <input type="radio" name="shadow" value="subtle" class="custom-control-input" <?= ($data->notification->settings->shadow  ?? null) == 'subtle' ? 'checked="checked"' : null?> />
                    <?= l('notification.settings.shadow.subtle') ?>
                </label>
            </div>
            <div class="col-4 p-2">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= ($data->notification->settings->shadow  ?? null) == 'feather' ? 'active' : null?>">
                    <input type="radio" name="shadow" value="feather" class="custom-control-input" <?= ($data->notification->settings->shadow  ?? null) == 'feather' ? 'checked="checked"' : null?> />
                    <?= l('notification.settings.shadow.feather') ?>
                </label>
            </div>
            <div class="col-4 p-2">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= ($data->notification->settings->shadow  ?? null) == '3d' ? 'active' : null?>">
                    <input type="radio" name="shadow" value="3d" class="custom-control-input" <?= ($data->notification->settings->shadow  ?? null) == '3d' ? 'checked="checked"' : null?> />
                    <?= l('notification.settings.shadow.3d') ?>
                </label>
            </div>
            <div class="col-4 p-2">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= ($data->notification->settings->shadow  ?? null) == 'layered' ? 'active' : null?>">
                    <input type="radio" name="shadow" value="layered" class="custom-control-input" <?= ($data->notification->settings->shadow  ?? null) == 'layered' ? 'checked="checked"' : null?> />
                    <?= l('notification.settings.shadow.layered') ?>
                </label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="settings_shadow_color"><i class="fas fa-fw fa-cloud-sun fa-sm text-muted mr-1"></i> <?= l('notification.settings.shadow_color') ?></label>
        <input type="hidden" id="settings_shadow_color" name="shadow_color" class="form-control border-left-0" value="<?= $data->notification->settings->shadow_color ?>" />
        <div id="settings_shadow_color_pickr"></div>
    </div>

    <div class="form-group" data-range-counter data-range-counter-suffix="px">
        <label for="settings_border_width"><i class="fas fa-fw fa-border-top-left fa-sm text-muted mr-1"></i> <?= l('notification.settings.border_width') ?></label>
        <input type="range" min="0" max="5" id="settings_border_width" name="border_width" class="form-control-range" value="<?= $data->notification->settings->border_width ?>" />
    </div>

    <div class="form-group">
        <label for="settings_border_color"><i class="fas fa-fw fa-fill fa-sm text-muted mr-1"></i> <?= l('notification.settings.border_color') ?></label>
        <input type="hidden" id="settings_border_color" name="border_color" class="form-control border-left-0" value="<?= $data->notification->settings->border_color ?>" />
        <div id="settings_border_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="settings_border_radius"><i class="fas fa-fw fa-border-all fa-sm text-muted mr-1"></i> <?= l('notification.settings.border_radius') ?></label>
        <div class="row mx-n2 btn-group-toggle" data-toggle="buttons">
            <div class="col-4 p-2">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= ($data->notification->settings->border_radius  ?? null) == 'straight' ? 'active"' : null?>">
                    <input type="radio" name="border_radius" value="straight" class="custom-control-input" <?= ($data->notification->settings->border_radius  ?? null) == 'straight' ? 'checked="checked"' : null?> />
                    <i class="fas fa-fw fa-square-full fa-sm mr-1"></i> <?= l('notification.settings.border_radius_straight') ?>
                </label>
            </div>
            <div class="col-4 p-2">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= ($data->notification->settings->border_radius  ?? null) == 'rounded' ? 'active' : null?>">
                    <input type="radio" name="border_radius" value="rounded" class="custom-control-input" <?= ($data->notification->settings->border_radius  ?? null) == 'rounded' ? 'checked="checked"' : null?> />
                    <i class="fas fa-fw fa-square fa-sm mr-1"></i> <?= l('notification.settings.border_radius_rounded') ?>
                </label>
            </div>
            <div class="col-4 p-2">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= ($data->notification->settings->border_radius  ?? null) == 'highly_rounded' ? 'active' : null?>">
                    <input type="radio" name="border_radius" value="highly_rounded" class="custom-control-input" <?= ($data->notification->settings->border_radius  ?? null) == 'highly_rounded' ? 'checked="checked"' : null?> />
                    <i class="fas fa-fw fa-square fa-sm mr-1"></i> <?= l('notification.settings.border_radius_highly_rounded') ?>
                </label>
            </div>
        </div>
    </div>
</div>
<?php $html['customize'] = ob_get_clean() ?>


<?php /* Data Tab */ ?>
<?php ob_start() ?>
<div class="alert alert-info"><i class="fas fa-fw fa-info-circle fa-sm mr-1"></i> <?= l('notification.settings.data_info') ?></div>

<?php if(settings()->notification_handlers->is_enabled): ?>
<div class="form-group">
    <div class="d-flex flex-wrap flex-row justify-content-between">
        <label><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= l('notification.settings.notifications') ?></label>
        <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
    </div>
    <div class="mb-2"><small class="text-muted"><?= l('notification.settings.notifications_help') ?></small></div>

    <div class="row">
        <?php foreach($data->notification_handlers as $notification_handler): ?>
            <div class="col-12 col-lg-6">
                <div class="custom-control custom-checkbox my-2">
                    <input id="notifications_<?= $notification_handler->notification_handler_id ?>" name="notifications[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->notification->notifications ?? []) ? 'checked="checked"' : null ?>>
                    <label class="custom-control-label" for="notifications_<?= $notification_handler->notification_handler_id ?>">
                        <span class="mr-1"><?= $notification_handler->name ?></span>
                        <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                    </label>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>
<?php endif ?>

<?php $html['data'] = ob_get_clean() ?>


<?php ob_start() ?>
<script>
    'use strict';

    /* Dont show the agreement fields if unchecked */
    let show_agreement_check = () => {
        if($('#settings_show_agreement').is(':checked')) {
            $('#agreement').show();
        } else {
            $('#agreement').hide();
        }
    };
    show_agreement_check();
    $('#settings_show_agreement').on('change', show_agreement_check);

    /* Cancel the submit button form of the email collector */
    $('#altumcode-collector-modal-form').on('submit', event => event.preventDefault());

    /* Notification Preview Handlers */
    $('#settings_title').on('change paste keyup', event => {
        $('#notification_preview .altumcode-collector-modal-title').text($(event.currentTarget).val());
    });

    $('#settings_description').on('change paste keyup', event => {
        $('#notification_preview .altumcode-collector-modal-description').text($(event.currentTarget).val());
    });

    $('#settings_input_placeholder').on('change paste keyup', event => {
        $('#notification_preview [name="input"]').attr('placeholder', $(event.currentTarget).val());
    });

    $('#settings_button_text').on('change paste keyup', event => {
        $('#notification_preview [name="button"]').text($(event.currentTarget).val());
    });

    $('#settings_image').on('change paste keyup', event => {
        $('#notification_preview .altumcode-collector-modal-image-holder').css('background-image', `url('${$(event.currentTarget).val()}')`);
    });

    /* light mode */
    init_color_pickr('#settings_title_color', '#settings_title_color_pickr', '.altumcode-collector-modal-title', 'color', 'light', 'title');
    init_color_pickr('#settings_description_color', '#settings_description_color_pickr', '.altumcode-collector-modal-description', 'color', 'light', 'description');
    init_color_pickr('#settings_background_color', '#settings_background_color_pickr', '.altumcode-wrapper', 'background-color', 'light', 'background');
    init_color_pickr('#settings_button_background_color', '#settings_button_background_color_pickr', '.altumcode-collector-modal-row button', 'background-color', 'light', 'button_background');
    init_color_pickr('#settings_button_color', '#settings_button_color_pickr', '.altumcode-collector-modal-row button', 'color', 'light', 'button');

    /* dark mode */
    init_color_pickr('#dark_mode_title_color', '#dark_mode_title_color_pickr', '.altumcode-collector-modal-title', 'color', 'dark', 'title');
    init_color_pickr('#dark_mode_description_color', '#dark_mode_description_color_pickr', '.altumcode-collector-modal-description', 'color', 'dark', 'description');
    init_color_pickr('#dark_mode_background_color', '#dark_mode_background_color_pickr', '.altumcode-wrapper', 'background-color', 'dark', 'background');
    init_color_pickr('#dark_mode_button_background_color', '#dark_mode_button_background_color_pickr', '.altumcode-collector-modal-row button', 'background-color', 'dark', 'button_background');
    init_color_pickr('#dark_mode_button_color', '#dark_mode_button_color_pickr', '.altumcode-collector-modal-row button', 'color', 'dark', 'button');
</script>

<script>
    'use strict';

let active_notification_handlers_per_resource_limit = <?= (int) $this->user->plan_settings->active_notification_handlers_per_resource_limit ?>;

    if(active_notification_handlers_per_resource_limit != -1) {
        let process_notification_handlers = () => {
            let selected = document.querySelectorAll('[name="notifications[]"]:checked').length;

            if(selected >= active_notification_handlers_per_resource_limit) {
                document.querySelectorAll('[name="notifications[]"]:not(:checked)').forEach(element => element.setAttribute('disabled', 'disabled'));
            } else {
                document.querySelectorAll('[name="notifications[]"]:not(:checked)').forEach(element => element.removeAttribute('disabled'));
            }
        }

        document.querySelectorAll('[name="notifications[]"]').forEach(element => element.addEventListener('change', process_notification_handlers));

        process_notification_handlers();
    }
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
