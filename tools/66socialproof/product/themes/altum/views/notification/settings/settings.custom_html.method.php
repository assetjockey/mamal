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
    <label for="settings_html"><i class="fas fa-fw fa-sm fa-code text-muted mr-1"></i> <?= l('notification.settings.html') ?></label>
    <textarea id="settings_html" name="html" class="form-control" maxlength="16000" required="required"><?= e($data->notification->settings->html) ?></textarea>
    <small class="form-text text-muted"><?= l('notification.settings.html_help') ?></small>
    <small class="form-text text-muted"><?= l('notification.settings.html_help2') ?></small>
</div>
<?php $html['basic'] = ob_get_clean() ?>

<?php /* Default Display Tab */ ?>
<?php ob_start() ?>
<div class="form-group">
    <label for="settings_display_position"><i class="fas fa-fw fa-th fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_position') ?></label>
    <div class="row btn-group-toggle m-n2" data-toggle="buttons">
        <div class="col-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_position == 'top_left' ? 'active"' : null?>">
                <input type="radio" name="display_position" value="top_left" class="custom-control-input" <?= $data->notification->settings->display_position == 'top_left' ? 'checked="checked"' : null?> />
                <?= l('notification.settings.display_position_top_left') ?>
            </label>
        </div>

        <div class="col-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_position == 'top_center' ? 'active"' : null?>">
                <input type="radio" name="display_position" value="top_center" class="custom-control-input" <?= $data->notification->settings->display_position == 'top_center' ? 'checked="checked"' : null?> />
                <?= l('notification.settings.display_position_top_center') ?>
            </label>
        </div>

        <div class="col-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_position == 'top_right' ? 'active"' : null?>">
                <input type="radio" name="display_position" value="top_right" class="custom-control-input" <?= $data->notification->settings->display_position == 'top_right' ? 'checked="checked"' : null?> />
                <?= l('notification.settings.display_position_top_right') ?>
            </label>
        </div>

        <div class="col-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_position == 'middle_left' ? 'active"' : null?>">
                <input type="radio" name="display_position" value="middle_left" class="custom-control-input" <?= $data->notification->settings->display_position == 'middle_left' ? 'checked="checked"' : null?> />
                <?= l('notification.settings.display_position_middle_left') ?>
            </label>
        </div>

        <div class="col-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_position == 'middle_center' ? 'active"' : null?>">
                <input type="radio" name="display_position" value="middle_center" class="custom-control-input" <?= $data->notification->settings->display_position == 'middle_center' ? 'checked="checked"' : null?> />
                <?= l('notification.settings.display_position_middle_center') ?>
            </label>
        </div>

        <div class="col-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_position == 'middle_right' ? 'active"' : null?>">
                <input type="radio" name="display_position" value="middle_right" class="custom-control-input" <?= $data->notification->settings->display_position == 'middle_right' ? 'checked="checked"' : null?> />
                <?= l('notification.settings.display_position_middle_right') ?>
            </label>
        </div>

        <div class="col-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_position == 'bottom_left' ? 'active"' : null?>">
                <input type="radio" name="display_position" value="bottom_left" class="custom-control-input" <?= $data->notification->settings->display_position == 'bottom_left' ? 'checked="checked"' : null?> />
                <?= l('notification.settings.display_position_bottom_left') ?>
            </label>
        </div>

        <div class="col-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_position == 'bottom_center' ? 'active"' : null?>">
                <input type="radio" name="display_position" value="bottom_center" class="custom-control-input" <?= $data->notification->settings->display_position == 'bottom_center' ? 'checked="checked"' : null?> />
                <?= l('notification.settings.display_position_bottom_center') ?>
            </label>
        </div>

        <div class="col-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_position == 'bottom_right' ? 'active"' : null?>">
                <input type="radio" name="display_position" value="bottom_right" class="custom-control-input" <?= $data->notification->settings->display_position == 'bottom_right' ? 'checked="checked"' : null?> />
                <?= l('notification.settings.display_position_bottom_right') ?>
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

    /* Notification Preview Handlers */
    $('#settings_html').on('change paste keyup', event => {
        $('#notification_preview .altumcode-custom-html-html').text($(event.currentTarget).val());
    });

    /* Light mode inits */
    init_color_pickr('#settings_background_color', '#settings_background_color_pickr', '.altumcode-wrapper', 'background-color', 'light', 'background');

    /* Dark mode inits */
    init_color_pickr('#dark_mode_background_color', '#dark_mode_background_color_pickr', '.altumcode-wrapper', 'background-color', 'dark', 'background');
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
