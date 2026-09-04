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
    <small class="form-text text-muted"><?= l('notification.settings.conversion_title_help') ?></small>
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
    <small class="form-text text-muted"><?= l('notification.settings.conversion_active_help') ?></small>
</div>

<div class="collapse" id="description_translate_container" data-translation-container="description">
    <?= htmlspecialchars(json_encode($data->notification->settings->translations->description ?? ''), ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= settings()->notifications->image_size_limit ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), settings()->notifications->image_size_limit) ?>">
    <label for="image"><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('notification.settings.image') ?></label>
    <?= include_view(THEME_PATH . 'views/partials/custom_file_image_input.php', ['uploads_file_key' => 'notifications', 'file_key' => 'image', 'already_existing_image' => $data->notification->settings->image, 'input_data' => 'data-crop data-aspect-ratio="1"']) ?>
    <?= \Altum\Alerts::output_field_error('image') ?>
    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('notifications')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->notifications->image_size_limit) ?></small>
</div>

<div class="form-group">
    <label for="settings_image_alt"><i class="fas fa-fw fa-sm fa-comment text-muted mr-1"></i> <?= l('notification.settings.image_alt') ?></label>
    <input type="text" id="settings_image_alt" name="image_alt" class="form-control" value="<?= $data->notification->settings->image_alt ?>" maxlength="100" />
    <small class="form-text text-muted"><?= l('notification.settings.image_alt_help') ?></small>
    <small class="form-text text-muted"><?= l('notification.settings.conversion_active_help') ?></small>
</div>

<div class="form-group">
    <label for="settings_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('notification.settings.url') ?></label>
    <input type="text" id="settings_url" name="url" class="form-control" value="<?= $data->notification->settings->url ?>" placeholder="<?= l('global.url_placeholder') ?>" maxlength="2048" />
    <small class="form-text text-muted"><?= l('notification.settings.url_help') ?></small>
    <small class="form-text text-muted"><?= l('notification.settings.conversion_active_help') ?></small>
</div>

<div class="form-group custom-control custom-switch">
    <input
            type="checkbox"
            class="custom-control-input"
            id="settings_display_time"
            name="display_time"
            <?= $data->notification->settings->display_time ? 'checked="checked"' : null ?>
    >

    <label class="custom-control-label" for="settings_display_time"><?= l('notification.settings.display_time') ?></label>
</div>

<div class="form-group custom-control custom-switch">
    <input
            type="checkbox"
            class="custom-control-input"
            id="settings_url_new_tab"
            name="url_new_tab"
            <?= $data->notification->settings->url_new_tab ? 'checked="checked"' : null ?>
    >

    <label class="custom-control-label" for="settings_url_new_tab"><?= l('notification.settings.url_new_tab') ?></label>
    <div>
        <small class="form-text text-muted"><?= l('notification.settings.url_new_tab_help') ?></small>
    </div>
</div>

<div class="form-group">
    <label for="settings_order"><i class="fas fa-fw fa-sm fa-sort text-muted mr-1"></i> <?= l('notification.settings.order') ?></label>
    <div class="row btn-group-toggle m-n2" data-toggle="buttons">
        <div class="col-12 col-lg-6 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->order == 'descending' ? 'active"' : null?>">
                <input type="radio" name="order" value="descending" class="custom-control-input" <?= $data->notification->settings->order == 'descending' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-caret-down fa-sm mr-1"></i> <?= l('notification.settings.order_descending') ?>
            </label>
        </div>

        <div class="col-12 col-lg-6 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->order == 'random' ? 'active"' : null?>">
                <input type="radio" name="order" value="random" class="custom-control-input" <?= $data->notification->settings->order == 'random' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-random fa-sm mr-1"></i> <?= l('notification.settings.order_random') ?>
            </label>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="settings_conversions_count"><i class="fas fa-fw fa-sm fa-users text-muted mr-1"></i> <?= l('notification.settings.conversions_count') ?></label>
    <input type="number" min="1" id="settings_conversions_count" name="conversions_count" class="form-control" value="<?= $data->notification->settings->conversions_count ?>" />
</div>

<div class="form-group">
    <label for="settings_in_between_delay"><i class="fas fa-fw fa-sm fa-stopwatch text-muted mr-1"></i> <?= l('notification.settings.in_between_delay') ?></label>
    <div class="input-group">
        <input type="text" id="settings_in_between_delay" name="in_between_delay" class="form-control" value="<?= $data->notification->settings->in_between_delay ?>" />
        <div class="input-group-append">
            <span class="input-group-text"><?= l('global.date.seconds') ?></span>
        </div>
    </div>
</div>

<div class="form-group custom-control custom-switch">
    <input
            type="checkbox"
            class="custom-control-input"
            id="settings_infinite_rotation"
            name="infinite_rotation"
        <?= ($data->notification->settings->infinite_rotation ?? false) ? 'checked="checked"' : null ?>
    >

    <label class="custom-control-label" for="settings_infinite_rotation"><?= l('notification.settings.infinite_rotation') ?></label>

    <div>
        <small class="form-text text-muted"><?= l('notification.settings.infinite_rotation_help') ?></small>
    </div>
</div>
<?php $html['basic'] = ob_get_clean() ?>


<?php /* Display Tab */ ?>
<?php ob_start() ?>
<div class="form-group">
    <label for="settings_direction"><i class="fas fa-fw fa-map-signs fa-sm text-muted mr-1"></i> <?= l('notification.settings.direction') ?></label>
    <div class="row btn-group-toggle m-n2" data-toggle="buttons">
        <div class="col-6 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= ($data->notification->settings->direction ?? null) == 'ltr' ? 'active"' : null?>">
                <input type="radio" name="direction" value="ltr" class="custom-control-input" <?= ($data->notification->settings->direction ?? null) == 'ltr' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-long-arrow-alt-right fa-sm mr-1"></i> <?= l('notification.settings.direction_ltr') ?>
            </label>
        </div>

        <div class="col-6 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= ($data->notification->settings->direction ?? null) == 'rtl' ? 'active"' : null?>">
                <input type="radio" name="direction" value="rtl" class="custom-control-input" <?= ($data->notification->settings->direction ?? null) == 'rtl' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-long-arrow-alt-left fa-sm mr-1"></i> <?= l('notification.settings.direction_rtl') ?>
            </label>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="settings_selector"><i class="fas fa-fw fa-crosshairs fa-sm text-muted mr-1"></i> <?= l('notification.inline_conversions.selector') ?></label>
    <input type="text" id="settings_selector" name="selector" class="form-control" value="<?= e($data->notification->settings->selector) ?>" placeholder="#pricing, .checkout-summary" maxlength="256" required="required" />
    <small class="form-text text-muted"><?= l('notification.inline_conversions.selector_help') ?></small>
</div>

<div class="form-group">
    <label for="settings_inline_placement"><i class="fas fa-fw fa-arrows-alt-v fa-sm text-muted mr-1"></i> <?= l('notification.inline_conversions.placement') ?></label>
    <select id="settings_inline_placement" name="inline_placement" class="custom-select">
        <option value="append" <?= $data->notification->settings->inline_placement == 'append' ? 'selected="selected"' : null ?>><?= l('notification.inline_conversions.placement_append') ?></option>
        <option value="prepend" <?= $data->notification->settings->inline_placement == 'prepend' ? 'selected="selected"' : null ?>><?= l('notification.inline_conversions.placement_prepend') ?></option>
        <option value="before" <?= $data->notification->settings->inline_placement == 'before' ? 'selected="selected"' : null ?>><?= l('notification.inline_conversions.placement_before') ?></option>
        <option value="after" <?= $data->notification->settings->inline_placement == 'after' ? 'selected="selected"' : null ?>><?= l('notification.inline_conversions.placement_after') ?></option>
    </select>
</div>

<input type="hidden" name="display_position" value="inline" />

<div class="form-group">
    <label for="settings_display_duration"><i class="fas fa-fw fa-hourglass-start fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_duration') ?></label>
    <div class="input-group">
        <input type="number" min="-1" id="settings_display_duration" name="display_duration" class="form-control" value="<?= $data->notification->settings->display_duration ?>" required="required" />
        <div class="input-group-append">
            <span class="input-group-text"><?= l('global.date.seconds') ?></span>
        </div>
    </div>
    <small class="form-text text-muted"><?= l('notification.settings.display_duration_help') ?></small>
</div>

<div <?= $this->user->plan_settings->removable_branding ? null : get_plan_feature_disabled_info() ?>>
    <div class="form-group custom-control custom-switch <?= !$this->user->plan_settings->removable_branding ? 'container-disabled': null ?>">
        <input
                type="checkbox"
                class="custom-control-input"
                id="display_branding"
                name="display_branding"
                <?= $data->notification->settings->display_branding ? 'checked="checked"' : null ?>
                <?= !$this->user->plan_settings->removable_branding ? 'disabled="disabled"' : null ?>
        >
        <label class="custom-control-label" for="display_branding"><?= l('notification.settings.display_branding') ?></label>
    </div>
</div>
<?php $html['display'] = ob_get_clean() ?>


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
    <label for="settings_date_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.date_color') ?></label>
    <input type="hidden" id="settings_date_color" name="date_color" class="form-control" value="<?= $data->notification->settings->date_color ?>" />
    <div id="settings_date_color_pickr"></div>
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

<button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#margins_container" aria-expanded="false" aria-controls="margins_container">
    <i class="fas fa-fw fa-arrows-alt fa-sm mr-1"></i> <?= l('notification.settings.margins') ?>
</button>

<div class="collapse" data-parent="#tab_customize" id="margins_container">
    <div class="row">
        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="settings_margin_top"><?= l('notification.settings.margin_top') ?></label>
                <input type="text" id="settings_margin_top" name="margin_top" class="form-control" value="<?= e($data->notification->settings->margin_top) ?>" placeholder="0" maxlength="32" pattern="(?:auto|0|-?(?:\d+(?:\.\d+)?|\.\d+)(?:px|rem|em|%|vh|vw|vmin|vmax|ch|ex|cm|mm|in|pt|pc|q))" data-margin="margin-top" />
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="settings_margin_right"><?= l('notification.settings.margin_right') ?></label>
                <input type="text" id="settings_margin_right" name="margin_right" class="form-control" value="<?= e($data->notification->settings->margin_right) ?>" placeholder="0" maxlength="32" pattern="(?:auto|0|-?(?:\d+(?:\.\d+)?|\.\d+)(?:px|rem|em|%|vh|vw|vmin|vmax|ch|ex|cm|mm|in|pt|pc|q))" data-margin="margin-right" />
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="settings_margin_bottom"><?= l('notification.settings.margin_bottom') ?></label>
                <input type="text" id="settings_margin_bottom" name="margin_bottom" class="form-control" value="<?= e($data->notification->settings->margin_bottom) ?>" placeholder="0" maxlength="32" pattern="(?:auto|0|-?(?:\d+(?:\.\d+)?|\.\d+)(?:px|rem|em|%|vh|vw|vmin|vmax|ch|ex|cm|mm|in|pt|pc|q))" data-margin="margin-bottom" />
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="settings_margin_left"><?= l('notification.settings.margin_left') ?></label>
                <input type="text" id="settings_margin_left" name="margin_left" class="form-control" value="<?= e($data->notification->settings->margin_left) ?>" placeholder="0" maxlength="32" pattern="(?:auto|0|-?(?:\d+(?:\.\d+)?|\.\d+)(?:px|rem|em|%|vh|vw|vmin|vmax|ch|ex|cm|mm|in|pt|pc|q))" data-margin="margin-left" />
            </div>
        </div>
    </div>
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
        <label for="dark_mode_date_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.date_color') ?></label>
        <input type="hidden" id="dark_mode_date_color" name="dark_mode_date_color" class="form-control" value="<?= $data->notification->settings->dark_mode_date_color ?>" />
        <div id="dark_mode_date_color_pickr"></div>
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


<?php /* Data Tab */ ?>
<?php ob_start() ?>
<div class="form-group">
    <label for="settings_data_trigger_input_webhook"><?= l('notification.settings.data_trigger_webhook') ?></label>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text"><?= l('notification.settings.data_trigger_type_webhook') ?></span>
        </div>

        <input type="text" id="settings_data_trigger_input_webhook" name="data_trigger_input_webhook" class="form-control" value="<?= url('pixel-webhook/' . $data->notification->notification_key) ?>" placeholder="<?= l('notification.settings.data_trigger_input_webhook') ?>" aria-label="<?= l('notification.settings.data_trigger_input_webhook') ?>" readonly="readonly">
    </div>

    <small class="form-text text-muted"><?= l('notification.settings.data_trigger_webhook_help') ?></small>
</div>

<div class="form-group">
    <div class="custom-control custom-switch">
        <input
                type="checkbox"
                class="custom-control-input"
                id="data_trigger_auto"
                name="data_trigger_auto"
                <?= $data->notification->settings->data_trigger_auto ? 'checked="checked"' : null ?>
        >
        <label class="custom-control-label" for="data_trigger_auto"><?= l('notification.settings.data_trigger_auto') ?></label>

        <div><small class="form-text text-muted"><?= l('notification.settings.data_trigger_auto_help') ?></small></div>
    </div>
</div>

<div id="data_triggers_auto" class="container-disabled">
    <?php if (!empty($data->notification->settings->data_triggers_auto)): ?>
        <?php foreach($data->notification->settings->data_triggers_auto as $trigger): ?>
            <div class="form-row">
                <div class="form-group col-lg-4">
                    <select class="form-control" name="data_trigger_auto_type[]" data-is-not-custom-select>
                        <option value="exact" data-placeholder="<?= l('notification.settings.trigger_type_exact_placeholder') ?>" <?= $trigger->type == 'exact' ? 'selected="selected"' : null ?>><?= l('notification.settings.trigger_type_exact') ?></option>
                        <option value="contains" data-placeholder="<?= l('notification.settings.trigger_type_contains_placeholder') ?>" <?= $trigger->type == 'contains' ? 'selected="selected"' : null ?>><?= l('notification.settings.trigger_type_contains') ?></option>
                        <option value="starts_with" data-placeholder="<?= l('notification.settings.trigger_type_starts_with_placeholder') ?>" <?= $trigger->type == 'starts_with' ? 'selected="selected"' : null ?>><?= l('notification.settings.trigger_type_starts_with') ?></option>
                        <option value="ends_with" data-placeholder="<?= l('notification.settings.trigger_type_ends_with_placeholder') ?>" <?= $trigger->type == 'ends_with' ? 'selected="selected"' : null ?>><?= l('notification.settings.trigger_type_ends_with') ?></option>
                        <option value="page_contains" data-placeholder="<?= l('notification.settings.trigger_type_page_contains_placeholder') ?>" <?= $trigger->type == 'page_contains' ? 'selected="selected"' : null ?>><?= l('notification.settings.trigger_type_page_contains') ?></option>
                    </select>
                </div>

                <div class="form-group col-lg-6">
                    <input type="text" name="data_trigger_auto_value[]" class="form-control" value="<?= $trigger->value ?>" placeholder="<?= l('notification.settings.trigger_type_exact_placeholder') ?>" aria-label="<?= l('notification.settings.trigger_type_exact_placeholder') ?>">
                </div>

                <div class="form-group col-lg-2">
                    <button type="button" class="data-trigger-auto-delete btn btn-block btn-outline-danger" title="<?= l('global.delete') ?>"><i class="fas fa-fw fa-times"></i></button>
                </div>
            </div>
        <?php endforeach ?>
    <?php endif ?>
</div>

<div>
    <button type="button" id="data_trigger_auto_add" class="btn btn-outline-success btn-sm"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('notification.settings.data_trigger_auto_add') ?></button>
</div>
<?php $html['data'] = ob_get_clean() ?>


<?php ob_start() ?>
<script>
    'use strict';

    /* Margins preview */
    $('[data-margin]').on('change paste keyup', event => {
        let margin = event.currentTarget.getAttribute('data-margin');
        document.querySelector('#notification_preview .altumcode-wrapper').style.setProperty(margin, $(event.currentTarget).val(), 'important');
    });

    /* Notification Preview Handlers */
    $('#settings_title').on('change paste keyup', event => {
        $('#notification_preview .altumcode-inline-conversions-title').text($(event.currentTarget).val());
    });

    $('#settings_description').on('change paste keyup', event => {
        $('#notification_preview .altumcode-inline-conversions-description').text($(event.currentTarget).val());
    });

    $('#settings_image').on('change paste keyup', event => {
        $('#notification_preview .altumcode-inline-conversions-image').attr('src', $(event.currentTarget).val());
    });

    /* init_color_pickr – light mode */
    init_color_pickr('#settings_title_color', '#settings_title_color_pickr', '.altumcode-inline-conversions-title, #notification_preview .altumcode-inline-conversions-arrow', 'color', 'light', 'title');
    init_color_pickr('#settings_description_color', '#settings_description_color_pickr', '.altumcode-inline-conversions-description', 'color', 'light', 'description');
    init_color_pickr('#settings_date_color', '#settings_date_color_pickr', '.altumcode-inline-conversions-time', 'color', 'light', 'date');
    init_color_pickr('#settings_background_color', '#settings_background_color_pickr', '.altumcode-wrapper', 'background-color', 'light', 'background');

    /* init_color_pickr – dark mode */
    init_color_pickr('#dark_mode_title_color', '#dark_mode_title_color_pickr', '.altumcode-inline-conversions-title, #notification_preview .altumcode-inline-conversions-arrow', 'color', 'dark', 'title');
    init_color_pickr('#dark_mode_description_color', '#dark_mode_description_color_pickr', '.altumcode-inline-conversions-description', 'color', 'dark', 'description');
    init_color_pickr('#dark_mode_date_color', '#dark_mode_date_color_pickr', '.altumcode-inline-conversions-time', 'color', 'dark', 'date');
    init_color_pickr('#dark_mode_background_color', '#dark_mode_background_color_pickr', '.altumcode-wrapper', 'background-color', 'dark', 'background');

    /* Data Triggers Auto Handler */
    let data_trigger_auto_status_handler = () => {

        if(!$('#data_trigger_auto:checked').length) {

            /* Disable the container visually */
            $('#data_triggers_auto').addClass('container-disabled');

            /* Remove the new trigger add button */
            $('#data_trigger_auto_add').hide();

        } else {

            /* Remove disabled container if depending on the status of the trigger checkbox */
            $('#data_triggers_auto').removeClass('container-disabled');

            /* Bring back the new trigger add button */
            $('#data_trigger_auto_add').show();

        }

        $('select[name="data_trigger_auto_type[]"]').off().on('change', event => {

            let input = $(event.currentTarget).closest('.form-row').find('input');
            let placeholder = $(event.currentTarget).find(':checked').data('placeholder');

            /* Add the proper placeholder */
            input.attr('placeholder', placeholder);

        }).trigger('change');

    };

    /* Trigger on status change live of the checkbox */
    $('#data_trigger_auto').on('change', data_trigger_auto_status_handler);

    /* Delete trigger handler */
    let data_triggers_auto_delete_handler = () => {

        /* Delete button handler */
        $('.data-trigger-auto-delete').off().on('click', event => {

            let element = $(event.currentTarget).closest('.form-row');

            element.remove();

            data_triggers_auto_count_handler();

        });

    };

    let data_trigger_auto_add_sample = () => {
        let rule_sample = $('#data_trigger_auto_rule_sample').html();

        $('#data_triggers_auto').append(rule_sample);
    };

    let data_triggers_auto_count_handler = () => {

        let total_triggers = $('#data_triggers_auto > .form-row').length;

        /* Make sure we at least have two input groups to show the delete button */
        if(total_triggers > 1) {
            $('#data_triggers_auto .data-trigger-auto-delete').removeAttr('disabled');

            /* Make sure to set a limit to these triggers */
            if(total_triggers > 10) {
                $('#data_trigger_auto_add').hide();
            } else {
                $('#data_trigger_auto_add').show();
            }

        } else {

            if(total_triggers == 0) {
                data_trigger_auto_add_sample();
            }

            $('#data_triggers_auto .data-trigger-auto-delete').attr('disabled', 'disabled');
        }
    };

    /* Add new trigger rule handler */
    $('#data_trigger_auto_add').on('click', () => {
        data_trigger_auto_add_sample();
        data_triggers_auto_delete_handler();
        data_triggers_auto_count_handler();
        data_trigger_auto_status_handler();
    });

    /* Trigger functions for the first initial load */
    data_trigger_auto_status_handler();
    data_triggers_auto_delete_handler();
    data_triggers_auto_count_handler();
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
