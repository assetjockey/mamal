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
    <label for="settings_coupon_code"><i class="fas fa-fw fa-sm fa-tag text-muted mr-1"></i> <?= l('notification.settings.coupon_code') ?></label>
    <div class="input-group">
        <input type="text" id="settings_coupon_code" name="coupon_code" class="form-control" value="<?= e($data->notification->settings->coupon_code) ?>" maxlength="64" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#coupon_code_translate_container" aria-expanded="false" aria-controls="coupon_code_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
    <small class="form-text text-muted"><?= l('global.spintax_help') ?></small>
</div>

<div class="collapse" id="coupon_code_translate_container" data-translation-container="coupon_code">
    <?= htmlspecialchars(json_encode($data->notification->settings->translations->coupon_code ?? ''), ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="form-group">
    <label for="settings_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('notification.settings.url') ?></label>
    <input type="url" id="settings_url" name="url" class="form-control" value="<?= $data->notification->settings->url ?>" placeholder="<?= l('global.url_placeholder') ?>" maxlength="2048" />
    <small class="form-text text-muted"><?= l('notification.settings.url_help') ?></small>
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
<?php $html['basic'] = ob_get_clean() ?>

<?php /* Default Display Tab */ ?>
<?php ob_start() ?>
<div class="form-group">
    <label for="settings_direction"><i class="fas fa-fw fa-map-signs fa-sm text-muted mr-1"></i> <?= l('notification.settings.direction') ?></label>
    <div class="row btn-group-toggle m-n2" data-toggle="buttons">
        <div class="col-6 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= ($data->notification->settings->direction  ?? null) == 'ltr' ? 'active"' : null?>">
                <input type="radio" name="direction" value="ltr" class="custom-control-input" <?= ($data->notification->settings->direction  ?? null) == 'ltr' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-long-arrow-alt-right fa-sm mr-1"></i> <?= l('notification.settings.direction_ltr') ?>
            </label>
        </div>
        <div class="col-6 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= ($data->notification->settings->direction  ?? null) == 'rtl' ? 'active' : null?>">
                <input type="radio" name="direction" value="rtl" class="custom-control-input" <?= ($data->notification->settings->direction  ?? null) == 'rtl' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-long-arrow-alt-left fa-sm mr-1"></i> <?= l('notification.settings.direction_rtl') ?>
            </label>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="settings_display_position"><i class="fas fa-fw fa-th fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_position') ?></label>
    <div class="row btn-group-toggle m-n2" data-toggle="buttons">
        <div class="col-6 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_position == 'top' ? 'active"' : null?>">
                <input type="radio" name="display_position" value="top" class="custom-control-input" <?= $data->notification->settings->display_position == 'top' ? 'checked="checked"' : null?> />
                <?= l('notification.settings.display_position_top') ?>
            </label>
        </div>

        <div class="col-6 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_position == 'top_floating' ? 'active"' : null?>">
                <input type="radio" name="display_position" value="top_floating" class="custom-control-input" <?= $data->notification->settings->display_position == 'top_floating' ? 'checked="checked"' : null?> />
                <?= l('notification.settings.display_position_top_floating') ?>
            </label>
        </div>

        <div class="col-6 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_position == 'bottom' ? 'active"' : null?>">
                <input type="radio" name="display_position" value="bottom" class="custom-control-input" <?= $data->notification->settings->display_position == 'bottom' ? 'checked="checked"' : null?> />
                <?= l('notification.settings.display_position_bottom') ?>
            </label>
        </div>

        <div class="col-6 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_position == 'bottom_floating' ? 'active"' : null?>">
                <input type="radio" name="display_position" value="bottom_floating" class="custom-control-input" <?= $data->notification->settings->display_position == 'bottom_floating' ? 'checked="checked"' : null?> />
                <?= l('notification.settings.display_position_bottom_floating') ?>
            </label>
        </div>
    </div>
</div>

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

<div class="form-group custom-control custom-switch">
    <input
            type="checkbox"
            class="custom-control-input"
            id="display_close_button"
            name="display_close_button"
        <?= $data->notification->settings->display_close_button ? 'checked="checked"' : null ?>
    >
    <label class="custom-control-label" for="display_close_button"><?= l('notification.settings.display_close_button') ?></label>
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
    <label for="settings_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.background_color') ?></label>
    <input type="hidden" id="settings_background_color" name="background_color" class="form-control" value="<?= $data->notification->settings->background_color ?>" />
    <div id="settings_background_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_coupon_code_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.coupon_code_color') ?></label>
    <input type="hidden" id="settings_coupon_code_color" name="coupon_code_color" class="form-control" value="<?= $data->notification->settings->coupon_code_color ?>" />
    <div id="settings_coupon_code_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_coupon_code_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.coupon_code_background_color') ?></label>
    <input type="hidden" id="settings_coupon_code_background_color" name="coupon_code_background_color" class="form-control" value="<?= $data->notification->settings->coupon_code_background_color ?>" />
    <div id="settings_coupon_code_background_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_coupon_code_border_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.coupon_code_border_color') ?></label>
    <input type="hidden" id="settings_coupon_code_border_color" name="coupon_code_border_color" class="form-control" value="<?= $data->notification->settings->coupon_code_border_color ?>" />
    <div id="settings_coupon_code_border_color_pickr"></div>
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
        <label for="dark_mode_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.background_color') ?></label>
        <input type="hidden" id="dark_mode_background_color" name="dark_mode_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_background_color ?>" />
        <div id="dark_mode_background_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_coupon_code_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.coupon_code_color') ?></label>
        <input type="hidden" id="dark_mode_coupon_code_color" name="dark_mode_coupon_code_color" class="form-control" value="<?= $data->notification->settings->dark_mode_coupon_code_color ?>" />
        <div id="dark_mode_coupon_code_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_coupon_code_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.coupon_code_background_color') ?></label>
        <input type="hidden" id="dark_mode_coupon_code_background_color" name="dark_mode_coupon_code_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_coupon_code_background_color ?>" />
        <div id="dark_mode_coupon_code_background_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_coupon_code_border_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.coupon_code_border_color') ?></label>
        <input type="hidden" id="dark_mode_coupon_code_border_color" name="dark_mode_coupon_code_border_color" class="form-control" value="<?= $data->notification->settings->dark_mode_coupon_code_border_color ?>" />
        <div id="dark_mode_coupon_code_border_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_close_button_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.close_button_color') ?></label>
        <input type="hidden" id="dark_mode_close_button_color" name="dark_mode_close_button_color" class="form-control" value="<?= $data->notification->settings->dark_mode_close_button_color ?>" />
        <div id="dark_mode_close_button_color_pickr"></div>
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
    <div class="form-group" data-range-counter data-range-counter-suffix="px">
        <label for="settings_border_width"><i class="fas fa-fw fa-border-top-left fa-sm text-muted mr-1"></i> <?= l('notification.settings.border_width') ?></label>
        <input type="range" min="0" max="5" id="settings_border_width" name="border_width" class="form-control-range" value="<?= $data->notification->settings->border_width ?>" />
    </div>

    <div class="form-group">
        <label for="settings_border_color"><i class="fas fa-fw fa-fill fa-sm text-muted mr-1"></i> <?= l('notification.settings.border_color') ?></label>
        <input type="hidden" id="settings_border_color" name="border_color" class="form-control border-left-0" value="<?= $data->notification->settings->border_color ?>" />
        <div id="settings_border_color_pickr"></div>
    </div>
</div>
<?php $html['customize'] = ob_get_clean() ?>


<?php ob_start() ?>
<script>
    'use strict';

    /* Notification Preview Handlers */
    $('#settings_title').on('change paste keyup', event => {
        $('#notification_preview .altumcode-coupon-bar-title').text($(event.currentTarget).val());
    });

    $('#settings_coupon_code').on('change paste keyup', event => {
        $('#notification_preview .altumcode-coupon-bar-coupon-code').text($(event.currentTarget).val());
    });

    /* light mode */
init_color_pickr('#settings_title_color', '#settings_title_color_pickr', '.altumcode-coupon-bar-title', 'color', 'light', 'title');
init_color_pickr('#settings_background_color', '#settings_background_color_pickr', '.altumcode-wrapper', 'background-color', 'light', 'background');
init_color_pickr('#settings_coupon_code_color', '#settings_coupon_code_color_pickr', '.altumcode-coupon-bar-coupon-code', 'color', 'light', 'coupon_code');
init_color_pickr('#settings_coupon_code_background_color', '#settings_coupon_code_background_color_pickr', '.altumcode-coupon-bar-coupon-code', 'background-color', 'light', 'coupon_code_background');
init_color_pickr('#settings_coupon_code_border_color', '#settings_coupon_code_border_color_pickr', '.altumcode-coupon-bar-coupon-code', 'border-color', 'light', 'coupon_code_border');

/* dark mode */
init_color_pickr('#dark_mode_title_color', '#dark_mode_title_color_pickr', '.altumcode-coupon-bar-title', 'color', 'dark', 'title');
init_color_pickr('#dark_mode_background_color', '#dark_mode_background_color_pickr', '.altumcode-wrapper', 'background-color', 'dark', 'background');
init_color_pickr('#dark_mode_coupon_code_color', '#dark_mode_coupon_code_color_pickr', '.altumcode-coupon-bar-coupon-code', 'color', 'dark', 'coupon_code');
init_color_pickr('#dark_mode_coupon_code_background_color', '#dark_mode_coupon_code_background_color_pickr', '.altumcode-coupon-bar-coupon-code', 'background-color', 'dark', 'coupon_code_background');
init_color_pickr('#dark_mode_coupon_code_border_color', '#dark_mode_coupon_code_border_color_pickr', '.altumcode-coupon-bar-coupon-code', 'border-color', 'dark', 'coupon_code_border');

</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
