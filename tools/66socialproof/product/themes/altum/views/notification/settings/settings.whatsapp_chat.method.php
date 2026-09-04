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

<div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= settings()->notifications->image_size_limit ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), settings()->notifications->image_size_limit) ?>">
    <label for="settings_agent_image"><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('notification.settings.agent_image') ?></label>
    <?= include_view(THEME_PATH . 'views/partials/custom_file_image_input.php', ['uploads_file_key' => 'notifications', 'file_key' => 'agent_image', 'already_existing_image' => $data->notification->settings->agent_image, 'input_data' => 'data-crop data-aspect-ratio="1"']) ?>
    <?= \Altum\Alerts::output_field_error('agent_image') ?>
    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('notifications')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->notifications->image_size_limit) ?></small>
</div>

<div class="form-group">
    <label for="settings_agent_image_alt"><i class="fas fa-fw fa-sm fa-comment text-muted mr-1"></i> <?= l('notification.settings.image_alt') ?></label>
    <input type="text" id="settings_agent_image_alt" name="agent_image_alt" class="form-control" value="<?= $data->notification->settings->agent_image_alt ?>" maxlength="100" />
    <small class="form-text text-muted"><?= l('notification.settings.image_alt_help') ?></small>
</div>

<div class="form-group">
    <label for="settings_agent_name"><i class="fas fa-fw fa-sm fa-user text-muted mr-1"></i> <?= l('notification.settings.agent_name') ?></label>
    <div class="input-group">
        <input type="text" id="settings_agent_name" name="agent_name" class="form-control" value="<?= e($data->notification->settings->agent_name) ?>" maxlength="64" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#agent_name_translate_container" aria-expanded="false" aria-controls="agent_name_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
</div>

<div class="collapse" id="agent_name_translate_container" data-translation-container="agent_name">
    <?= htmlspecialchars(json_encode($data->notification->settings->translations->agent_name ?? ''), ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="form-group">
    <label for="settings_agent_description"><i class="fas fa-fw fa-sm fa-info-circle text-muted mr-1"></i> <?= l('notification.settings.agent_description') ?></label>
    <div class="input-group">
        <input type="text" id="settings_agent_description" name="agent_description" class="form-control" value="<?= e($data->notification->settings->agent_description) ?>" maxlength="512" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#agent_description_translate_container" aria-expanded="false" aria-controls="agent_description_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
</div>

<div class="collapse" id="agent_description_translate_container" data-translation-container="agent_description">
    <?= htmlspecialchars(json_encode($data->notification->settings->translations->agent_description ?? ''), ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="form-group">
    <label for="settings_agent_message"><i class="fas fa-fw fa-sm fa-comment-dots text-muted mr-1"></i> <?= l('notification.settings.agent_message') ?></label>
    <div class="input-group">
        <input type="text" id="settings_agent_message" name="agent_message" class="form-control" value="<?= e($data->notification->settings->agent_message) ?>" maxlength="1024" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#agent_message_translate_container" aria-expanded="false" aria-controls="agent_message_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
</div>

<div class="collapse" id="agent_message_translate_container" data-translation-container="agent_message">
    <?= htmlspecialchars(json_encode($data->notification->settings->translations->agent_message ?? ''), ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="form-group">
    <label for="settings_agent_phone_number"><i class="fas fa-fw fa-sm fa-phone text-muted mr-1"></i> <?= l('notification.settings.agent_phone_number') ?></label>
    <div class="input-group">
        <input type="number" id="settings_agent_phone_number" name="agent_phone_number" class="form-control" value="<?= e($data->notification->settings->agent_phone_number) ?>" maxlength="32" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#agent_phone_number_translate_container" aria-expanded="false" aria-controls="agent_phone_number_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
</div>

<div class="collapse" id="agent_phone_number_translate_container" data-translation-container="agent_phone_number">
    <?= htmlspecialchars(json_encode($data->notification->settings->translations->agent_phone_number ?? ''), ENT_QUOTES, 'UTF-8') ?>
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
<?php $html['basic'] = ob_get_clean() ?>


<?php /* Customize Tab */ ?>
<?php ob_start() ?>

<div class="form-group">
    <label for="settings_header_agent_name_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.header_agent_name_color') ?></label>
    <input type="hidden" id="settings_header_agent_name_color" name="header_agent_name_color" class="form-control" value="<?= $data->notification->settings->header_agent_name_color ?>" />
    <div id="settings_header_agent_name_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_header_agent_description_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.header_agent_description_color') ?></label>
    <input type="hidden" id="settings_header_agent_description_color" name="header_agent_description_color" class="form-control" value="<?= $data->notification->settings->header_agent_description_color ?>" />
    <div id="settings_header_agent_description_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_header_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.header_background_color') ?></label>
    <input type="hidden" id="settings_header_background_color" name="header_background_color" class="form-control" value="<?= $data->notification->settings->header_background_color ?>" />
    <div id="settings_header_background_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_content_agent_name_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.content_agent_name_color') ?></label>
    <input type="hidden" id="settings_content_agent_name_color" name="content_agent_name_color" class="form-control" value="<?= $data->notification->settings->content_agent_name_color ?>" />
    <div id="settings_content_agent_name_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_content_agent_message_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.content_agent_message_color') ?></label>
    <input type="hidden" id="settings_content_agent_message_color" name="content_agent_message_color" class="form-control" value="<?= $data->notification->settings->content_agent_message_color ?>" />
    <div id="settings_content_agent_message_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_content_agent_message_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.content_agent_message_background_color') ?></label>
    <input type="hidden" id="settings_content_agent_message_background_color" name="content_agent_message_background_color" class="form-control" value="<?= $data->notification->settings->content_agent_message_background_color ?>" />
    <div id="settings_content_agent_message_background_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_content_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.content_background_color') ?></label>
    <input type="hidden" id="settings_content_background_color" name="content_background_color" class="form-control" value="<?= $data->notification->settings->content_background_color ?>" />
    <div id="settings_content_background_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_footer_button_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.footer_button_color') ?></label>
    <input type="hidden" id="settings_footer_button_color" name="footer_button_color" class="form-control" value="<?= $data->notification->settings->footer_button_color ?>" />
    <div id="settings_footer_button_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_footer_button_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.footer_button_background_color') ?></label>
    <input type="hidden" id="settings_footer_button_background_color" name="footer_button_background_color" class="form-control" value="<?= $data->notification->settings->footer_button_background_color ?>" />
    <div id="settings_footer_button_background_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_footer_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.footer_background_color') ?></label>
    <input type="hidden" id="settings_footer_background_color" name="footer_background_color" class="form-control" value="<?= $data->notification->settings->footer_background_color ?>" />
    <div id="settings_footer_background_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_title_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.title_color') ?></label>
    <input type="hidden" id="settings_title_color" name="title_color" class="form-control" value="<?= $data->notification->settings->title_color ?>" />
    <div id="settings_title_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.background_color') ?></label>
    <input type="hidden" id="settings_background_color" name="background_color" class="form-control" value="<?= $data->notification->settings->background_color ?>" />
    <div id="settings_background_color_pickr"></div>
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
        <label for="dark_mode_header_agent_name_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.header_agent_name_color') ?></label>
        <input type="hidden" id="dark_mode_header_agent_name_color" name="dark_mode_header_agent_name_color" class="form-control" value="<?= $data->notification->settings->dark_mode_header_agent_name_color ?>" />
        <div id="dark_mode_header_agent_name_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_header_agent_description_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.header_agent_description_color') ?></label>
        <input type="hidden" id="dark_mode_header_agent_description_color" name="dark_mode_header_agent_description_color" class="form-control" value="<?= $data->notification->settings->dark_mode_header_agent_description_color ?>" />
        <div id="dark_mode_header_agent_description_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_header_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.header_background_color') ?></label>
        <input type="hidden" id="dark_mode_header_background_color" name="dark_mode_header_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_header_background_color ?>" />
        <div id="dark_mode_header_background_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_content_agent_name_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.content_agent_name_color') ?></label>
        <input type="hidden" id="dark_mode_content_agent_name_color" name="dark_mode_content_agent_name_color" class="form-control" value="<?= $data->notification->settings->dark_mode_content_agent_name_color ?>" />
        <div id="dark_mode_content_agent_name_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_content_agent_message_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.content_agent_message_color') ?></label>
        <input type="hidden" id="dark_mode_content_agent_message_color" name="dark_mode_content_agent_message_color" class="form-control" value="<?= $data->notification->settings->dark_mode_content_agent_message_color ?>" />
        <div id="dark_mode_content_agent_message_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_content_agent_message_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.content_agent_message_background_color') ?></label>
        <input type="hidden" id="dark_mode_content_agent_message_background_color" name="dark_mode_content_agent_message_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_content_agent_message_background_color ?>" />
        <div id="dark_mode_content_agent_message_background_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_content_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.content_background_color') ?></label>
        <input type="hidden" id="dark_mode_content_background_color" name="dark_mode_content_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_content_background_color ?>" />
        <div id="dark_mode_content_background_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_footer_button_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.footer_button_color') ?></label>
        <input type="hidden" id="dark_mode_footer_button_color" name="dark_mode_footer_button_color" class="form-control" value="<?= $data->notification->settings->dark_mode_footer_button_color ?>" />
        <div id="dark_mode_footer_button_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_footer_button_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.footer_button_background_color') ?></label>
        <input type="hidden" id="dark_mode_footer_button_background_color" name="dark_mode_footer_button_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_footer_button_background_color ?>" />
        <div id="dark_mode_footer_button_background_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_footer_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.footer_background_color') ?></label>
        <input type="hidden" id="dark_mode_footer_background_color" name="dark_mode_footer_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_footer_background_color ?>" />
        <div id="dark_mode_footer_background_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_title_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.title_color') ?></label>
        <input type="hidden" id="dark_mode_title_color" name="dark_mode_title_color" class="form-control" value="<?= $data->notification->settings->dark_mode_title_color ?>" />
        <div id="dark_mode_title_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.background_color') ?></label>
        <input type="hidden" id="dark_mode_background_color" name="dark_mode_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_background_color ?>" />
        <div id="dark_mode_background_color_pickr"></div>
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
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= ($data->notification->settings->border_radius  ?? null) == 'round' ? 'active' : null?>">
                    <input type="radio" name="border_radius" value="round" class="custom-control-input" <?= ($data->notification->settings->border_radius  ?? null) == 'round' ? 'checked="checked"' : null?> />
                    <i class="fas fa-fw fa-circle fa-sm mr-1"></i> <?= l('notification.settings.border_radius_round') ?>
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


<?php ob_start() ?>
<script>
    'use strict';

    $('#notification_preview .altumcode-hidden').removeClass('altumcode-hidden').addClass('altumcode-shown');

    /* Notification Preview Handlers */
    $('#settings_title').on('change paste keyup', event => {
        $('#notification_preview .altumcode-whatsapp-chat-title').text($(event.currentTarget).val());
    });

    $('#settings_agent_image').on('change paste keyup', event => {
        $('#notification_preview .altumcode-whatsapp-chat-window-header-image').attr('src', $(event.currentTarget).val());
    });

    $('#settings_agent_name').on('change paste keyup', event => {
        $('#notification_preview .altumcode-whatsapp-chat-window-header-title').text($(event.currentTarget).val());
        $('#notification_preview .altumcode-whatsapp-chat-window-content-reply-author').text($(event.currentTarget).val());
    });

    $('#settings_agent_description').on('change paste keyup', event => {
        $('#notification_preview .altumcode-whatsapp-chat-window-header-description').text($(event.currentTarget).val());
    });

    $('#settings_agent_message').on('change paste keyup', event => {
        $('#notification_preview .altumcode-whatsapp-chat-window-content-reply-text').text($(event.currentTarget).val());
    });

    $('#settings_button_text').on('change paste keyup', event => {
        $('#notification_preview .altumcode-whatsapp-chat-window-footer-button').text($(event.currentTarget).val());
    });

    /* Light mode */
    init_color_pickr('#settings_header_agent_name_color', '#settings_header_agent_name_color_pickr', '.altumcode-whatsapp-chat-window-header-title', 'color', 'light', 'header_agent_name');
    init_color_pickr('#settings_header_agent_description_color', '#settings_header_agent_description_color_pickr', '.altumcode-whatsapp-chat-window-header-description', 'color', 'light', 'header_agent_description');
    init_color_pickr('#settings_header_background_color', '#settings_header_background_color_pickr', '.altumcode-whatsapp-chat-window-header', 'background-color', 'light', 'header_background');

    init_color_pickr('#settings_content_agent_name_color', '#settings_content_agent_name_color_pickr', '.altumcode-whatsapp-chat-window-content-reply-author', 'color', 'light', 'content_agent_name');
    init_color_pickr('#settings_content_agent_message_color', '#settings_content_agent_message_color_pickr', '.altumcode-whatsapp-chat-window-content-reply-text', 'color', 'light', 'content_agent_message');
    init_color_pickr('#settings_content_agent_message_background_color', '#settings_content_agent_message_background_color_pickr', '.altumcode-whatsapp-chat-window-content-reply', 'background-color', 'light', 'content_agent_message_background');
    init_color_pickr('#settings_content_background_color', '#settings_content_background_color_pickr', '.altumcode-whatsapp-chat-window-content', 'background-color', 'light', 'content_background');

    init_color_pickr('#settings_footer_button_color', '#settings_footer_button_color_pickr', '.altumcode-whatsapp-chat-window-footer-button', 'color', 'light', 'footer_button');
    init_color_pickr('#settings_footer_button_background_color', '#settings_footer_button_background_color_pickr', '.altumcode-whatsapp-chat-window-footer-button', 'background-color', 'light', 'footer_button_background');
    init_color_pickr('#settings_footer_background_color', '#settings_footer_background_color_pickr', '.altumcode-whatsapp-chat-window-footer', 'background-color', 'light', 'footer_background');

    init_color_pickr('#settings_title_color', '#settings_title_color_pickr', '.altumcode-whatsapp-chat-title', 'color', 'light', 'title');
    init_color_pickr('#settings_background_color', '#settings_background_color_pickr', '.altumcode-wrapper', 'background-color', 'light', 'background');

    /* Dark mode  */
    init_color_pickr('#dark_mode_header_agent_name_color', '#dark_mode_header_agent_name_color_pickr', '.altumcode-whatsapp-chat-window-header-title', 'color', 'dark', 'header_agent_name');
    init_color_pickr('#dark_mode_header_agent_description_color', '#dark_mode_header_agent_description_color_pickr', '.altumcode-whatsapp-chat-window-header-description', 'color', 'dark', 'header_agent_description');
    init_color_pickr('#dark_mode_header_background_color', '#dark_mode_header_background_color_pickr', '.altumcode-whatsapp-chat-window-header', 'background-color', 'dark', 'header_background');

    init_color_pickr('#dark_mode_content_agent_name_color', '#dark_mode_content_agent_name_color_pickr', '.altumcode-whatsapp-chat-window-content-reply-author', 'color', 'dark', 'content_agent_name');
    init_color_pickr('#dark_mode_content_agent_message_color', '#dark_mode_content_agent_message_color_pickr', '.altumcode-whatsapp-chat-window-content-reply-text', 'color', 'dark', 'content_agent_message');
    init_color_pickr('#dark_mode_content_agent_message_background_color', '#dark_mode_content_agent_message_background_color_pickr', '.altumcode-whatsapp-chat-window-content-reply', 'background-color', 'dark', 'content_agent_message_background');
    init_color_pickr('#dark_mode_content_background_color', '#dark_mode_content_background_color_pickr', '.altumcode-whatsapp-chat-window-content', 'background-color', 'dark', 'content_background');

    init_color_pickr('#dark_mode_footer_button_color', '#dark_mode_footer_button_color_pickr', '.altumcode-whatsapp-chat-window-footer-button', 'color', 'dark', 'footer_button');
    init_color_pickr('#dark_mode_footer_button_background_color', '#dark_mode_footer_button_background_color_pickr', '.altumcode-whatsapp-chat-window-footer-button', 'background-color', 'dark', 'footer_button_background');
    init_color_pickr('#dark_mode_footer_background_color', '#dark_mode_footer_background_color_pickr', '.altumcode-whatsapp-chat-window-footer', 'background-color', 'dark', 'footer_background');

    init_color_pickr('#dark_mode_title_color', '#dark_mode_title_color_pickr', '.altumcode-whatsapp-chat-title', 'color', 'dark', 'title');
    init_color_pickr('#dark_mode_background_color', '#dark_mode_background_color_pickr', '.altumcode-wrapper', 'background-color', 'dark', 'background');
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
