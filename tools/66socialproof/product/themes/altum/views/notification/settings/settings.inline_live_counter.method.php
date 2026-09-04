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

/* Load shared live counter settings */
$settings = require THEME_PATH . 'views/notification/settings/settings.live_counter.method.php';
?>

<?php /* Inline Display Tab */ ?>
<?php ob_start() ?>
<div class="form-group">
    <label for="settings_direction"><i class="fas fa-fw fa-map-signs fa-sm text-muted mr-1"></i> <?= l('notification.settings.direction') ?></label>
    <div class="row btn-group-toggle m-n2" data-toggle="buttons">
        <div class="col-6 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->direction == 'ltr' ? 'active"' : null?>">
                <input type="radio" name="direction" value="ltr" class="custom-control-input" <?= $data->notification->settings->direction == 'ltr' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-long-arrow-alt-right fa-sm mr-1"></i> <?= l('notification.settings.direction_ltr') ?>
            </label>
        </div>

        <div class="col-6 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->direction == 'rtl' ? 'active"' : null?>">
                <input type="radio" name="direction" value="rtl" class="custom-control-input" <?= $data->notification->settings->direction == 'rtl' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-long-arrow-alt-left fa-sm mr-1"></i> <?= l('notification.settings.direction_rtl') ?>
            </label>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="settings_selector"><i class="fas fa-fw fa-crosshairs fa-sm text-muted mr-1"></i> <?= l('notification.inline_live_counter.selector') ?></label>
    <input type="text" id="settings_selector" name="selector" class="form-control" value="<?= $data->notification->settings->selector ?>" placeholder="#pricing, .checkout-summary" maxlength="256" required="required" />
    <small class="form-text text-muted"><?= l('notification.inline_live_counter.selector_help') ?></small>
</div>

<div class="form-group">
    <label for="settings_inline_placement"><i class="fas fa-fw fa-arrows-alt-v fa-sm text-muted mr-1"></i> <?= l('notification.inline_live_counter.placement') ?></label>
    <select id="settings_inline_placement" name="inline_placement" class="custom-select">
        <option value="append" <?= $data->notification->settings->inline_placement == 'append' ? 'selected="selected"' : null ?>><?= l('notification.inline_live_counter.placement_append') ?></option>
        <option value="prepend" <?= $data->notification->settings->inline_placement == 'prepend' ? 'selected="selected"' : null ?>><?= l('notification.inline_live_counter.placement_prepend') ?></option>
        <option value="before" <?= $data->notification->settings->inline_placement == 'before' ? 'selected="selected"' : null ?>><?= l('notification.inline_live_counter.placement_before') ?></option>
        <option value="after" <?= $data->notification->settings->inline_placement == 'after' ? 'selected="selected"' : null ?>><?= l('notification.inline_live_counter.placement_after') ?></option>
    </select>
</div>

<input type="hidden" name="display_duration" value="-1" />
<input type="hidden" name="display_position" value="inline" />

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
<?php $settings->html['display'] = ob_get_clean() ?>

<?php /* Inline Margins */ ?>
<?php ob_start() ?>
<button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#margins_container" aria-expanded="false" aria-controls="margins_container">
    <i class="fas fa-fw fa-arrows-alt fa-sm mr-1"></i> <?= l('notification.settings.margins') ?>
</button>

<div class="collapse" data-parent="#tab_customize" id="margins_container">
    <div class="row">
        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="settings_margin_top"><?= l('notification.settings.margin_top') ?></label>
                <input type="text" id="settings_margin_top" name="margin_top" class="form-control" value="<?= $data->notification->settings->margin_top ?>" placeholder="0" maxlength="32" pattern="(?:auto|0|-?(?:\d+(?:\.\d+)?|\.\d+)(?:px|rem|em|%|vh|vw|vmin|vmax|ch|ex|cm|mm|in|pt|pc|q))" data-margin="margin-top" />
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="settings_margin_right"><?= l('notification.settings.margin_right') ?></label>
                <input type="text" id="settings_margin_right" name="margin_right" class="form-control" value="<?= $data->notification->settings->margin_right ?>" placeholder="0" maxlength="32" pattern="(?:auto|0|-?(?:\d+(?:\.\d+)?|\.\d+)(?:px|rem|em|%|vh|vw|vmin|vmax|ch|ex|cm|mm|in|pt|pc|q))" data-margin="margin-right" />
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="settings_margin_bottom"><?= l('notification.settings.margin_bottom') ?></label>
                <input type="text" id="settings_margin_bottom" name="margin_bottom" class="form-control" value="<?= $data->notification->settings->margin_bottom ?>" placeholder="0" maxlength="32" pattern="(?:auto|0|-?(?:\d+(?:\.\d+)?|\.\d+)(?:px|rem|em|%|vh|vw|vmin|vmax|ch|ex|cm|mm|in|pt|pc|q))" data-margin="margin-bottom" />
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="settings_margin_left"><?= l('notification.settings.margin_left') ?></label>
                <input type="text" id="settings_margin_left" name="margin_left" class="form-control" value="<?= $data->notification->settings->margin_left ?>" placeholder="0" maxlength="32" pattern="(?:auto|0|-?(?:\d+(?:\.\d+)?|\.\d+)(?:px|rem|em|%|vh|vw|vmin|vmax|ch|ex|cm|mm|in|pt|pc|q))" data-margin="margin-left" />
            </div>
        </div>
    </div>
</div>
<?php $settings->html['customize'] .= ob_get_clean() ?>

<?php return $settings ?>
