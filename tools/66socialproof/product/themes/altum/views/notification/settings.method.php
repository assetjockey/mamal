<?php defined('ALTUMCODE') || die() ?>

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

$notification_settings_default_html = [];

$notification_themes = require APP_PATH . 'includes/notification_themes.php';
$available_notification_themes = [];

/* Prepare the available themes */
foreach($notification_themes as $key => $theme) {
    if(isset($theme['notification_types']) && !in_array($data->notification->type, $theme['notification_types'])) {
        continue;
    }

    $available_notification_themes[$key] = [
        'name' => $theme['name'],
        'preview_background_color' => $theme['preview_background_color'],
        'settings' => array_merge($theme['settings'], $theme['notifications'][$data->notification->type] ?? []),
    ];
}

/* Include the extra content of the notification */
$settings = require THEME_PATH . 'views/notification/settings/settings.' . mb_strtolower($data->notification->type) . '.method.php';
?>


<?php /* Default Triggers Tab */ ?>
<?php ob_start() ?>
<div class="form-group custom-control custom-switch">
    <input
            type="checkbox"
            class="custom-control-input"
            id="trigger_all_pages"
            name="trigger_all_pages"
            <?= $data->notification->settings->trigger_all_pages ? 'checked="checked"' : null ?>
    >
    <label class="custom-control-label" for="trigger_all_pages"><?= l('notification.settings.trigger_all_pages') ?></label>

    <div>
        <small class="form-text text-muted"><?= l('notification.settings.trigger_all_pages_help') ?></small>
    </div>
</div>

<div id="triggers" class="container-disabled">
    <?php if (!empty($data->notification->settings->triggers)): ?>
        <?php foreach($data->notification->settings->triggers as $trigger): ?>
            <div class="form-row">
                <div class="form-group col-lg-4">
                    <select class="form-control" name="trigger_type[]" data-is-not-custom-select>
                        <option value="exact" data-placeholder="<?= l('notification.settings.trigger_type_exact_placeholder') ?>" <?= $trigger->type == 'exact' ? 'selected="selected"' : null ?>><?= l('notification.settings.trigger_type_exact') ?></option>
                        <option value="not_exact" data-placeholder="<?= l('notification.settings.trigger_type_not_exact_placeholder') ?>" <?= $trigger->type == 'not_exact' ? 'selected="selected"' : null ?>><?= l('notification.settings.trigger_type_not_exact') ?></option>
                        <option value="contains" data-placeholder="<?= l('notification.settings.trigger_type_contains_placeholder') ?>" <?= $trigger->type == 'contains' ? 'selected="selected"' : null ?>><?= l('notification.settings.trigger_type_contains') ?></option>
                        <option value="not_contains" data-placeholder="<?= l('notification.settings.trigger_type_not_contains_placeholder') ?>" <?= $trigger->type == 'not_contains' ? 'selected="selected"' : null ?>><?= l('notification.settings.trigger_type_not_contains') ?></option>
                        <option value="starts_with" data-placeholder="<?= l('notification.settings.trigger_type_starts_with_placeholder') ?>" <?= $trigger->type == 'starts_with' ? 'selected="selected"' : null ?>><?= l('notification.settings.trigger_type_starts_with') ?></option>
                        <option value="not_starts_with" data-placeholder="<?= l('notification.settings.trigger_type_not_starts_with_placeholder') ?>" <?= $trigger->type == 'not_starts_with' ? 'selected="selected"' : null ?>><?= l('notification.settings.trigger_type_not_starts_with') ?></option>
                        <option value="ends_with" data-placeholder="<?= l('notification.settings.trigger_type_ends_with_placeholder') ?>" <?= $trigger->type == 'ends_with' ? 'selected="selected"' : null ?>><?= l('notification.settings.trigger_type_ends_with') ?></option>
                        <option value="not_ends_with" data-placeholder="<?= l('notification.settings.trigger_type_not_ends_with_placeholder') ?>" <?= $trigger->type == 'not_ends_with' ? 'selected="selected"' : null ?>><?= l('notification.settings.trigger_type_not_ends_with') ?></option>
                        <option value="page_contains" data-placeholder="<?= l('notification.settings.trigger_type_page_contains_placeholder') ?>" <?= $trigger->type == 'page_contains' ? 'selected="selected"' : null ?>><?= l('notification.settings.trigger_type_page_contains') ?></option>
                    </select>
                </div>

                <div class="form-group col-lg-6">
                    <input type="text" name="trigger_value[]" class="form-control" value="<?= $trigger->value ?>">
                </div>

                <div class="form-group col-lg-2">
                    <button type="button" class="trigger-delete btn btn-block btn-outline-danger" title="<?= l('global.delete') ?>"><i class="fas fa-fw fa-times"></i></button>
                </div>
            </div>
        <?php endforeach ?>
    <?php endif ?>
</div>

<button type="button" id="trigger_add" class="btn btn-outline-success btn-sm mb-4"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('notification.settings.trigger_add') ?></button>

<div class="form-group">
    <label for="settings_display_trigger"><i class="fas fa-fw fa-bolt fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_trigger') ?></label>
    <div class="row btn-group-toggle m-n2" data-toggle="buttons">
        <div class="col-12 col-lg-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_trigger == 'delay' ? 'active"' : null?>">
                <input type="radio" name="display_trigger" value="delay" class="custom-control-input" <?= $data->notification->settings->display_trigger == 'delay' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-hourglass-start fa-sm mr-1"></i> <?= l('notification.settings.display_trigger_delay') ?>
            </label>
        </div>

        <div class="col-12 col-lg-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_trigger == 'time_on_site' ? 'active"' : null?>">
                <input type="radio" name="display_trigger" value="time_on_site" class="custom-control-input" <?= $data->notification->settings->display_trigger == 'time_on_site' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-user-clock fa-sm mr-1"></i> <?= l('notification.settings.display_trigger_time_on_site') ?>
            </label>
        </div>

        <div class="col-12 col-lg-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_trigger == 'inactivity' ? 'active"' : null?>">
                <input type="radio" name="display_trigger" value="inactivity" class="custom-control-input" <?= $data->notification->settings->display_trigger == 'inactivity' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-pause-circle fa-sm mr-1"></i> <?= l('notification.settings.display_trigger_inactivity') ?>
            </label>
        </div>

        <div class="col-12 col-lg-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_trigger == 'pageviews' ? 'active"' : null?>">
                <input type="radio" name="display_trigger" value="pageviews" class="custom-control-input" <?= $data->notification->settings->display_trigger == 'pageviews' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-eye fa-sm mr-1"></i> <?= l('notification.settings.display_trigger_pageviews') ?>
            </label>
        </div>

        <div class="col-12 col-lg-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_trigger == 'exit_intent' ? 'active"' : null?>">
                <input type="radio" name="display_trigger" value="exit_intent" class="custom-control-input" <?= $data->notification->settings->display_trigger == 'exit_intent' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-door-open fa-sm mr-1"></i> <?= l('notification.settings.display_trigger_exit_intent') ?>
            </label>
        </div>

        <div class="col-12 col-lg-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_trigger == 'scroll' ? 'active"' : null?>">
                <input type="radio" name="display_trigger" value="scroll" class="custom-control-input" <?= $data->notification->settings->display_trigger == 'scroll' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-scroll fa-sm mr-1"></i> <?= l('notification.settings.display_trigger_scroll') ?>
            </label>
        </div>

        <div class="col-12 col-lg-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_trigger == 'target_visible' ? 'active"' : null?>">
                <input type="radio" name="display_trigger" value="target_visible" class="custom-control-input" <?= $data->notification->settings->display_trigger == 'target_visible' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-crosshairs fa-sm mr-1"></i> <?= l('notification.settings.display_trigger_target_visible') ?>
            </label>
        </div>

        <div class="col-12 col-lg-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_trigger == 'click' ? 'active"' : null?>">
                <input type="radio" name="display_trigger" value="click" class="custom-control-input" <?= $data->notification->settings->display_trigger == 'click' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-mouse fa-sm mr-1"></i> <?= l('notification.settings.display_trigger_click') ?>
            </label>
        </div>

        <div class="col-12 col-lg-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_trigger == 'hover' ? 'active"' : null?>">
                <input type="radio" name="display_trigger" value="hover" class="custom-control-input" <?= $data->notification->settings->display_trigger == 'hover' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-mouse-pointer fa-sm mr-1"></i> <?= l('notification.settings.display_trigger_hover') ?>
            </label>
        </div>
    </div>
</div>

<div class="form-group" data-display-trigger="delay">
    <label for="display_trigger_value_delay"><i class="fas fa-fw fa-hourglass-start fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_trigger_delay') ?></label>
    <div class="input-group">
        <input id="display_trigger_value_delay" type="number" min="0" name="display_trigger_value" class="form-control" value="<?= $data->notification->settings->display_trigger_value ?>" />
        <div class="input-group-append">
            <span class="input-group-text"><?= l('global.date.seconds') ?></span>
        </div>
    </div>
</div>

<div class="form-group" data-display-trigger="time_on_site">
    <label for="display_trigger_value_time_on_site"><i class="fas fa-fw fa-user-clock fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_trigger_time_on_site') ?></label>
    <div class="input-group">
        <input id="display_trigger_value_time_on_site" type="number" min="0" name="display_trigger_value" class="form-control" value="<?= $data->notification->settings->display_trigger_value ?>" />
        <div class="input-group-append">
            <span class="input-group-text"><?= l('global.date.seconds') ?></span>
        </div>
    </div>
</div>

<div class="form-group" data-display-trigger="inactivity">
    <label for="display_trigger_value_inactivity"><i class="fas fa-fw fa-pause-circle fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_trigger_inactivity') ?></label>
    <div class="input-group">
        <input id="display_trigger_value_inactivity" type="number" min="0" name="display_trigger_value" class="form-control" value="<?= $data->notification->settings->display_trigger_value ?>" />
        <div class="input-group-append">
            <span class="input-group-text"><?= l('global.date.seconds') ?></span>
        </div>
    </div>
</div>

<div class="form-group" data-display-trigger="pageviews">
    <label for="display_trigger_value_pageviews"><i class="fas fa-fw fa-eye fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_trigger_pageviews') ?></label>
    <div class="input-group">
        <input id="display_trigger_value_pageviews" type="number" min="1" name="display_trigger_value" class="form-control" value="<?= $data->notification->settings->display_trigger_value ?>" />
    </div>
</div>

<div class="form-group" data-display-trigger="scroll">
    <label for="display_trigger_value_scroll"><i class="fas fa-fw fa-scroll fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_trigger_scroll') ?></label>
    <div class="input-group">
        <input id="display_trigger_value_scroll" type="number" min="0" max="100" name="display_trigger_value" class="form-control" value="<?= $data->notification->settings->display_trigger_value ?>" />
        <div class="input-group-append">
            <span class="input-group-text">%</span>
        </div>
    </div>
</div>

<div class="form-group" data-display-trigger="target_visible">
    <label for="display_trigger_selector"><i class="fas fa-fw fa-crosshairs fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_trigger_target_visible_selector') ?></label>
    <input id="display_trigger_selector" type="text" name="display_trigger_selector" class="form-control" value="<?= e($data->notification->settings->display_trigger_selector) ?>" required="required" />
    <small class="form-text text-muted"><?= l('notification.settings.display_trigger_target_visible_help') ?></small>
</div>

<div class="form-group" data-display-trigger="target_visible">
    <label for="display_trigger_value_target_visible"><i class="fas fa-fw fa-hourglass-start fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_trigger_target_visible_delay') ?></label>
    <div class="input-group">
        <input id="display_trigger_value_target_visible" type="number" min="0" name="display_trigger_value" class="form-control" value="<?= $data->notification->settings->display_trigger_value ?>" />
        <div class="input-group-append">
            <span class="input-group-text"><?= l('global.date.seconds') ?></span>
        </div>
    </div>
</div>

<div class="form-group" data-display-trigger="click">
    <label for="display_trigger_value_click"><i class="fas fa-fw fa-mouse fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_trigger_click') ?></label>
    <input id="display_trigger_value_click" type="text" name="display_trigger_value" class="form-control" value="<?= $data->notification->settings->display_trigger_value ?>" />
    <small class="form-text text-muted"><?= l('notification.settings.display_trigger_click_help') ?></small>
</div>

<div class="form-group" data-display-trigger="hover">
    <label for="display_trigger_value_hover"><i class="fas fa-fw fa-mouse-pointer fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_trigger_hover') ?></label>
    <input id="display_trigger_value_hover" type="text" name="display_trigger_value" class="form-control" value="<?= $data->notification->settings->display_trigger_value ?>" />
    <small class="form-text text-muted"><?= l('notification.settings.display_trigger_hover_help') ?></small>
</div>

<div class="form-group">
    <label for="settings_display_frequency"><i class="fas fa-fw fa-th fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_frequency') ?></label>
    <div class="row btn-group-toggle m-n2" data-toggle="buttons">
        <div class="col-12 col-lg-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_frequency == 'all_time' ? 'active"' : null?>">
                <input type="radio" name="display_frequency" value="all_time" class="custom-control-input" <?= $data->notification->settings->display_frequency == 'all_time' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-history fa-sm mr-1"></i> <?= l('notification.settings.display_frequency_all_time') ?>
            </label>
        </div>

        <div class="col-12 col-lg-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_frequency == 'once_per_session' ? 'active"' : null?>">
                <input type="radio" name="display_frequency" value="once_per_session" class="custom-control-input" <?= $data->notification->settings->display_frequency == 'once_per_session' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-stopwatch fa-sm mr-1"></i> <?= l('notification.settings.display_frequency_once_per_session') ?>
            </label>
        </div>

        <div class="col-12 col-lg-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_frequency == 'once_per_browser' ? 'active"' : null?>">
                <input type="radio" name="display_frequency" value="once_per_browser" class="custom-control-input" <?= $data->notification->settings->display_frequency == 'once_per_browser' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-window-maximize fa-sm mr-1"></i> <?= l('notification.settings.display_frequency_once_per_browser') ?>
            </label>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="settings_display_delay_type_after_close"><i class="fas fa-fw fa-calendar-check fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_delay_type_after_close') ?></label>
    <div class="row btn-group-toggle m-n2" data-toggle="buttons">
        <div class="col-12 col-lg-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_delay_type_after_close == 'time_on_site' ? 'active"' : null?>">
                <input type="radio" name="display_delay_type_after_close" value="time_on_site" class="custom-control-input" <?= $data->notification->settings->display_delay_type_after_close == 'time_on_site' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-user-clock fa-sm mr-1"></i> <?= l('notification.settings.display_delay_type_after_close_time_on_site') ?>
            </label>
        </div>

        <div class="col-12 col-lg-4 p-2">
            <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->display_delay_type_after_close == 'pageviews' ? 'active"' : null?>">
                <input type="radio" name="display_delay_type_after_close" value="pageviews" class="custom-control-input" <?= $data->notification->settings->display_delay_type_after_close == 'pageviews' ? 'checked="checked"' : null?> />
                <i class="fas fa-fw fa-eye fa-sm mr-1"></i> <?= l('notification.settings.display_delay_type_after_close_pageviews') ?>
            </label>
        </div>
    </div>
</div>

<div class="form-group" data-display-delay-type-after-close="time_on_site">
    <label for="display_delay_value_after_close_time_on_site"><i class="fas fa-fw fa-user-clock fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_delay_type_after_close_time_on_site') ?></label>
    <div class="input-group">
        <input id="display_delay_value_after_close_time_on_site" type="number" min="0" name="display_delay_value_after_close" class="form-control" value="<?= $data->notification->settings->display_delay_value_after_close ?>" />
        <div class="input-group-append">
            <span class="input-group-text"><?= l('global.date.seconds') ?></span>
        </div>
    </div>
    <small class="form-text text-muted"><?= l('notification.settings.display_delay_type_after_close_help') ?></small>
</div>

<div class="form-group" data-display-delay-type-after-close="pageviews">
    <label for="display_delay_value_after_close_pageviews"><i class="fas fa-fw fa-eye fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_delay_type_after_close_pageviews') ?></label>
    <div class="input-group">
        <input id="display_delay_value_after_close_pageviews" type="number" min="1" name="display_delay_value_after_close" class="form-control" value="<?= $data->notification->settings->display_delay_value_after_close ?>" />
    </div>
    <small class="form-text text-muted"><?= l('notification.settings.display_delay_type_after_close_help') ?></small>
</div>

<div class="form-group custom-control custom-switch">
    <input
            id="schedule"
            name="schedule"
            type="checkbox"
            class="custom-control-input"
            <?= $data->notification->settings->schedule && !empty($data->notification->settings->start_date) && !empty($data->notification->settings->end_date) ? 'checked="checked"' : null ?>
    >
    <label class="custom-control-label" for="schedule"><?= l('notification.settings.schedule') ?></label>
    <small class="form-text text-muted"><?= l('notification.settings.schedule_help') ?></small>
</div>

<div id="schedule_container" style="display: none;">
    <div class="row">
        <div class="col">
            <div class="form-group">
                <label><i class="fas fa-fw fa-hourglass-start fa-sm text-muted mr-1"></i> <?= l('notification.settings.start_date') ?></label>
                <input
                        type="text"
                        class="form-control"
                        name="start_date"
                        value="<?= \Altum\Date::get($data->notification->settings->start_date, 1) ?>"
                        placeholder="<?= l('notification.settings.start_date') ?>"
                        autocomplete="off"
                        data-daterangepicker
                />
            </div>
        </div>

        <div class="col">
            <div class="form-group">
                <label><i class="fas fa-fw fa-hourglass-end fa-sm text-muted mr-1"></i> <?= l('notification.settings.end_date') ?></label>
                <input
                        type="text"
                        class="form-control"
                        name="end_date"
                        value="<?= \Altum\Date::get($data->notification->settings->end_date, 1) ?>"
                        placeholder="<?= l('notification.settings.end_date') ?>"
                        autocomplete="off"
                        data-daterangepicker
                />
            </div>
        </div>
    </div>
</div>

<button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#display_settings_container" aria-expanded="false" aria-controls="<?= 'display_settings_container' ?>">
    <i class="fas fa-fw fa-bullseye fa-sm mr-1"></i> <?= l('notification.settings.targeting') ?>
</button>

<div class="collapse" id="display_settings_container">
    <div class="form-group">
        <label for="display_continents"><i class="fas fa-fw fa-sm fa-globe-europe text-muted mr-1"></i> <?= l('global.continents') ?></label>
        <select id="display_continents" name="display_continents[]" class="custom-select" multiple="multiple">
            <?php foreach(get_continents_array() as $continent_code => $continent_name): ?>
                <option value="<?= $continent_code ?>" <?= in_array($continent_code, $data->notification->settings->display_continents ?? []) ? 'selected="selected"' : null ?>><?= $continent_name ?></option>
            <?php endforeach ?>
        </select>
        <small class="form-text text-muted"><?= l('notification.settings.display_targeting_help') ?></small>
        <?php if(get_this_device_type() == 'desktop'): ?><small class="form-text text-muted"><?= l('global.select_multiple_help') ?></small><?php endif ?>
    </div>

    <div class="form-group">
        <label for="display_countries"><i class="fas fa-fw fa-globe fa-sm text-muted mr-1"></i> <?= l('global.countries') ?></label>
        <select id="display_countries" name="display_countries[]" class="custom-select" multiple="multiple">
            <?php foreach(get_countries_array() as $country => $country_name): ?>
                <option value="<?= $country ?>" <?= in_array($country, $data->notification->settings->display_countries ?? []) ? 'selected="selected"' : null ?>><?= $country_name ?></option>
            <?php endforeach ?>
        </select>
        <small class="form-text text-muted"><?= l('notification.settings.display_targeting_help') ?></small>
        <?php if(get_this_device_type() == 'desktop'): ?><small class="form-text text-muted"><?= l('global.select_multiple_help') ?></small><?php endif ?>
    </div>

    <div class="form-group">
        <label for="display_cities"><i class="fas fa-fw fa-sm fa-city text-muted mr-1"></i> <?= l('global.cities') ?></label>
        <input type="text" id="display_cities" name="display_cities" value="<?= implode(',', $data->notification->settings->display_cities ?? []) ?>" class="form-control" placeholder="<?= l('notification.settings.display_cities_placeholder') ?>" />
        <small class="form-text text-muted"><?= l('notification.settings.display_cities_help') ?></small>
    </div>

    <div class="form-group">
        <label for="display_operating_systems"><i class="fas fa-fw fa-server fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_operating_systems') ?></label>
        <select id="display_operating_systems" name="display_operating_systems[]" class="custom-select" multiple="multiple">
            <?php foreach(['iOS', 'Android', 'Windows', 'OS X', 'Linux', 'Ubuntu', 'Chrome OS'] as $os_name): ?>
                <option value="<?= $os_name ?>" <?= in_array($os_name, $data->notification->settings->display_operating_systems ?? []) ? 'selected="selected"' : null ?>><?= $os_name ?></option>
            <?php endforeach ?>
        </select>
        <small class="form-text text-muted"><?= l('notification.settings.display_targeting_help') ?></small>
        <?php if(get_this_device_type() == 'desktop'): ?><small class="form-text text-muted"><?= l('global.select_multiple_help') ?></small><?php endif ?>
    </div>

    <div class="form-group">
        <label for="display_browsers"><i class="fas fa-fw fa-window-restore fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_browsers') ?></label>
        <select id="display_browsers" name="display_browsers[]" class="custom-select" multiple="multiple">
            <?php foreach(['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera', 'Samsung Internet'] as $browser_name): ?>
                <option value="<?= $browser_name ?>" <?= in_array($browser_name, $data->notification->settings->display_browsers ?? []) ? 'selected="selected"' : null ?>><?= $browser_name ?></option>
            <?php endforeach ?>
        </select>
        <small class="form-text text-muted"><?= l('notification.settings.display_targeting_help') ?></small>
        <?php if(get_this_device_type() == 'desktop'): ?><small class="form-text text-muted"><?= l('global.select_multiple_help') ?></small><?php endif ?>
    </div>

    <div class="form-group">
        <label for="display_languages"><i class="fas fa-fw fa-language fa-sm text-muted mr-1"></i> <?= l('notification.settings.display_languages') ?></label>
        <select id="display_languages" name="display_languages[]" class="custom-select" multiple="multiple">
            <?php foreach(get_locale_languages_array() as $locale => $language): ?>
                <option value="<?= $locale ?>" <?= in_array($locale, $data->notification->settings->display_languages ?? []) ? 'selected="selected"' : null ?>><?= $language ?></option>
            <?php endforeach ?>
        </select>
        <small class="form-text text-muted"><?= l('notification.settings.display_targeting_help') ?></small>
        <?php if(get_this_device_type() == 'desktop'): ?><small class="form-text text-muted"><?= l('global.select_multiple_help') ?></small><?php endif ?>
    </div>

    <div class="form-group custom-control custom-switch">
        <input type="checkbox" class="custom-control-input" id="display_mobile" name="display_mobile" <?= $data->notification->settings->display_mobile ? 'checked="checked"' : null ?>>
        <label class="custom-control-label" for="display_mobile"><i class="fas fa-fw fa-sm fa-mobile text-muted mr-1"></i> <?= l('notification.settings.display_mobile') ?></label>
        <small class="form-text text-muted"><?= l('notification.settings.display_mobile_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input type="checkbox" class="custom-control-input" id="display_desktop" name="display_desktop" <?= $data->notification->settings->display_desktop ? 'checked="checked"' : null ?>>
        <label class="custom-control-label" for="display_desktop"><i class="fas fa-fw fa-sm fa-desktop text-muted mr-1"></i> <?= l('notification.settings.display_desktop') ?></label>
        <small class="form-text text-muted"><?= l('notification.settings.display_desktop_help') ?></small>
    </div>
</div>
<?php $notification_settings_default_html['triggers'] = ob_get_clean() ?>


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
<?php $notification_settings_default_html['display'] = ob_get_clean() ?>


<?php /* Standard Customize Tab */ ?>
<?php ob_start() ?>

<button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#animations_container" aria-expanded="false" aria-controls="animations_container">
    <i class="fas fa-fw fa-running fa-sm mr-1"></i> <?= l('notification.settings.animations') ?>
</button>

<div class="collapse" data-parent="#tab_customize" id="animations_container">
    <div class="form-group">
        <label for="settings_hover_animation"><i class="fas fa-fw fa-mouse-pointer fa-sm text-muted mr-1"></i> <?= l('notification.settings.hover_animation') ?></label>
        <select id="settings_hover_animation" class="custom-select" name="hover_animation">
            <option value=" " <?= $data->notification->settings->hover_animation == '' ? 'selected="selected"' : null ?>><?= l('global.none') ?></option>
            <option value="fast_scale_up" <?= $data->notification->settings->hover_animation == 'fast_scale_up' ? 'selected="selected"' : null ?>><?= l('notification.settings.hover_animation_fast_scale_up') ?></option>
            <option value="slow_scale_up" <?= $data->notification->settings->hover_animation == 'slow_scale_up' ? 'selected="selected"' : null ?>><?= l('notification.settings.hover_animation_slow_scale_up') ?></option>
            <option value="fast_scale_down" <?= $data->notification->settings->hover_animation == 'fast_scale_down' ? 'selected="selected"' : null ?>><?= l('notification.settings.hover_animation_fast_scale_down') ?></option>
            <option value="slow_scale_down" <?= $data->notification->settings->hover_animation == 'slow_scale_down' ? 'selected="selected"' : null ?>><?= l('notification.settings.hover_animation_slow_scale_down') ?></option>
        </select>
    </div>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="settings_on_animation"><i class="fas fa-fw fa-sign-in-alt fa-sm text-muted mr-1"></i> <?= l('notification.settings.on_animation') ?></label>
                <select id="settings_on_animation" class="custom-select" name="on_animation">
                    <option value="fadeIn" <?= $data->notification->settings->on_animation == 'fadeIn' ? 'selected="selected"' : null ?>><?= l('notification.settings.on_animation_fadeIn') ?></option>
                    <option value="slideInUp" <?= $data->notification->settings->on_animation == 'slideInUp' ? 'selected="selected"' : null ?>><?= l('notification.settings.on_animation_slideInUp') ?></option>
                    <option value="slideInDown" <?= $data->notification->settings->on_animation == 'slideInDown' ? 'selected="selected"' : null ?>><?= l('notification.settings.on_animation_slideInDown') ?></option>
                    <option value="zoomIn" <?= $data->notification->settings->on_animation == 'zoomIn' ? 'selected="selected"' : null ?>><?= l('notification.settings.on_animation_zoomIn') ?></option>
                    <option value="bounceIn" <?= $data->notification->settings->on_animation == 'bounceIn' ? 'selected="selected"' : null ?>><?= l('notification.settings.on_animation_bounceIn') ?></option>
                </select>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="settings_off_animation"><i class="fas fa-fw fa-sign-out-alt fa-sm text-muted mr-1"></i> <?= l('notification.settings.off_animation') ?></label>
                <select id="settings_off_animation" class="custom-select" name="off_animation">
                    <option value="fadeOut" <?= $data->notification->settings->off_animation == 'fadeOut' ? 'selected="selected"' : null ?>><?= l('notification.settings.off_animation_fadeOut') ?></option>
                    <option value="slideOutUp" <?= $data->notification->settings->off_animation == 'slideOutUp' ? 'selected="selected"' : null ?>><?= l('notification.settings.off_animation_slideOutUp') ?></option>
                    <option value="slideOutDown" <?= $data->notification->settings->off_animation == 'slideOutDown' ? 'selected="selected"' : null ?>><?= l('notification.settings.off_animation_slideOutDown') ?></option>
                    <option value="zoomOut" <?= $data->notification->settings->off_animation == 'zoomOut' ? 'selected="selected"' : null ?>><?= l('notification.settings.off_animation_zoomOut') ?></option>
                    <option value="bounceOut" <?= $data->notification->settings->off_animation == 'bounceOut' ? 'selected="selected"' : null ?>><?= l('notification.settings.off_animation_bounceOut') ?></option>
                </select>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="settings_animation"><i class="fas fa-fw fa-film fa-sm text-muted mr-1"></i> <?= l('notification.settings.animation') ?></label>
        <select id="settings_animation" class="custom-select" name="animation">
            <option value=" " <?= $data->notification->settings->animation == '' ? 'selected="selected"' : null ?>><?= l('global.none') ?></option>
            <option value="heartbeat" <?= $data->notification->settings->animation == 'heartbeat' ? 'selected="selected"' : null ?>><?= l('notification.settings.animation_heartbeat') ?></option>
            <option value="bounce" <?= $data->notification->settings->animation == 'bounce' ? 'selected="selected"' : null ?>><?= l('notification.settings.animation_bounce') ?></option>
            <option value="flash" <?= $data->notification->settings->animation == 'flash' ? 'selected="selected"' : null ?>><?= l('notification.settings.animation_flash') ?></option>
            <option value="pulse" <?= $data->notification->settings->animation == 'pulse' ? 'selected="selected"' : null ?>><?= l('notification.settings.animation_pulse') ?></option>
        </select>
    </div>

    <div class="form-group">
        <label for="settings_animation_interval"><i class="fas fa-fw fa-history fa-sm text-muted mr-1"></i> <?= l('notification.settings.animation_interval') ?></label>
        <div class="input-group">
            <input type="number" min="3" id="settings_animation_interval" name="animation_interval" class="form-control" value="<?= $data->notification->settings->animation_interval ?? 3 ?>" />
            <div class="input-group-append">
                <span class="input-group-text"><?= l('global.date.seconds') ?></span>
            </div>
        </div>
    </div>
</div>

<button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#font_container" aria-expanded="false" aria-controls="font_container">
    <i class="fas fa-fw fa-pen-nib fa-sm mr-1"></i> <?= l('notification.settings.font') ?>
</button>

<div class="collapse" data-parent="#tab_customize" id="font_container">
    <div class="form-group">
        <label for="settings_font"><i class="fas fa-fw fa-pen-nib fa-sm text-muted mr-1"></i> <?= l('notification.settings.font') ?></label>
        <div class="row btn-group-toggle mx-n2" data-toggle="buttons">
            <div class="col-6 col-lg-4 p-2 h-100">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= ($data->notification->settings->font ?? 'inherit') == 'inherit' ? 'active"' : null?>">
                    <input type="radio" name="font" value="inherit" class="custom-control-input" <?= ($data->notification->settings->font ?? 'inherit') == 'inherit' ? 'checked="checked"' : null?> required="required" />
                    <?= l('notification.settings.font_inherit') ?>
                </label>
            </div>

            <?php foreach(['Arial', 'Verdana', 'Helvetica', 'Tahoma', 'Trebuchet MS', 'Times New Roman', 'Georgia', 'Courier New', 'Monaco', 'Comic Sans MS', 'Courier', 'Impact', 'Futura', 'Luminari', 'Baskerville', 'Papyrus',] as $font): ?>
                <div class="col-6 col-lg-4 p-2 h-100">
                    <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= ($data->notification->settings->font ?? 'inherit') == $font ? 'active"' : null?>" style="font-family: <?= $font ?> !important;">
                        <input type="radio" name="font" value="<?= $font ?>" class="custom-control-input" <?= ($data->notification->settings->font ?? 'inherit') == $font ? 'checked="checked"' : null?> required="required" />
                        <?= $font ?>
                    </label>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>

<button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
    <i class="fas fa-fw fa-running fa-sm mr-1"></i> <?= l('notification.settings.advanced') ?>
</button>

<div class="collapse" data-parent="#tab_customize" id="advanced_container">
    <div <?= $this->user->plan_settings->custom_css_is_enabled ? null : get_plan_feature_disabled_info() ?>>
        <div class="form-group <?= $this->user->plan_settings->custom_css_is_enabled ? null : 'container-disabled' ?>" data-character-counter="textarea">
            <label for="custom_css" class="d-flex justify-content-between align-items-center">
                <span><i class="fab fa-fw fa-sm fa-css3 text-muted mr-1"></i> <?= l('global.custom_css') ?></span>
                <small class="text-muted" data-character-counter-wrapper></small>
            </label>
            <textarea id="custom_css" class="form-control" name="custom_css" maxlength="10000" placeholder="<?= sprintf(l('global.custom_css_placeholder'), $data->notification->notification_id) ?>"><?= $data->notification->settings->custom_css ?></textarea>
            <small class="form-text text-muted"><?= l('global.custom_css_help') ?></small>
        </div>
    </div>
</div>
<?php $notification_settings_default_html['customize'] = ob_get_clean() ?>

<div class="mt-5 mb-3 row">
    <?php if(str_contains($data->notification->type, '_bar')): ?>
        <div class="col-12 col-lg-12 mb-5">
            <div id="notification_preview" class="notification-preview notification-preview-<?= mb_strtolower($data->notification->type) ?>">
                <?= \Altum\Notification::get($data->notification->type, $data->notification, $this->user, is_preview: true)->html ?>
            </div>
        </div>
    <?php endif ?>

    <div class="col-12 col-lg-7 order-1 order-lg-0">
        <ul class="nav nav-pills nav-custom2 flex-column flex-lg-row justify-content-between mb-4 gap-3" id="pills-tab" role="tablist">
            <?php if(in_array('basic', $data->notification->settings->enabled_settings_tabs)): ?>
                <li class="nav-item">
                    <a class="nav-link mb-0 active" id="tab_basic_link" data-toggle="pill" href="#tab_basic" role="tab" aria-controls="tab_basic" aria-selected="true">
                        <i class="fas fa-fw fa-sm fa-cog mr-1"></i> <?= l('notification.settings.tab_basic') ?>
                    </a>
                </li>
            <?php endif ?>

            <?php if(in_array('triggers', $data->notification->settings->enabled_settings_tabs)): ?>
                <li class="nav-item">
                    <a class="nav-link mb-0" id="tab_triggers_link" data-toggle="pill" href="#tab_triggers" role="tab" aria-controls="tab_triggers" aria-selected="false">
                        <i class="fas fa-fw fa-sm fa-bolt mr-1"></i> <?= l('notification.settings.tab_triggers') ?>
                    </a>
                </li>
            <?php endif ?>

            <?php if(in_array('display', $data->notification->settings->enabled_settings_tabs)): ?>
                <li class="nav-item">
                    <a class="nav-link mb-0" id="tab_display_link" data-toggle="pill" href="#tab_display" role="tab" aria-controls="tab_display" aria-selected="false">
                        <i class="fas fa-fw fa-sm fa-window-maximize mr-1"></i> <?= l('notification.settings.tab_display') ?>
                    </a>
                </li>
            <?php endif ?>

            <?php if(in_array('customize', $data->notification->settings->enabled_settings_tabs)): ?>
                <li class="nav-item">
                    <a class="nav-link mb-0" id="tab_customize_link" data-toggle="pill" href="#tab_customize" role="tab" aria-controls="tab_customize" aria-selected="false">
                        <i class="fas fa-fw fa-sm fa-paint-brush mr-1"></i> <?= l('notification.settings.tab_customize') ?>
                    </a>
                </li>
            <?php endif ?>

            <?php if(in_array('customize', $data->notification->settings->enabled_settings_tabs)): ?>
                <li class="nav-item">
                    <a class="nav-link mb-0" id="tab_themes_link" data-toggle="pill" href="#tab_themes" role="tab" aria-controls="tab_themes" aria-selected="false">
                        <i class="fas fa-fw fa-sm fa-palette mr-1"></i> <?= l('notification.settings.tab_themes') ?>
                    </a>
                </li>
            <?php endif ?>

            <?php if(in_array('data', $data->notification->settings->enabled_settings_tabs)): ?>
                <li class="nav-item">
                    <a class="nav-link mb-0" id="tab_data_link" data-toggle="pill" href="#tab_data" role="tab" aria-controls="tab_data" aria-selected="false">
                        <i class="fas fa-fw fa-sm fa-database mr-1"></i> <?= l('notification.settings.tab_data') ?>
                    </a>
                </li>
            <?php endif ?>
        </ul>

        <div class="card rounded-2x">
            <div class="card-body">

                <form id="notification_update" action="" method="post" role="form" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab_basic" role="tabpanel" aria-labelledby="tab_basic_link">
                            <?= $settings->html['basic'] ?>
                        </div>


                        <div class="tab-pane fade" id="tab_triggers" role="tabpanel" aria-labelledby="tab_triggers_link">
                            <?= $notification_settings_default_html['triggers'] ?>

                            <?= isset($settings->html['triggers']) ? $settings->html['triggers'] : null ?>
                        </div>

                        <div class="tab-pane fade" id="tab_display" role="tabpanel" aria-labelledby="tab_display_link">
                            <?= isset($settings->html['display']) ? $settings->html['display'] : $notification_settings_default_html['display'] ?>
                        </div>

                        <div class="tab-pane fade" id="tab_customize" role="tabpanel" aria-labelledby="tab_customize_link">
                            <?= $settings->html['customize'] ?>

                            <?= $notification_settings_default_html['customize'] ?>
                        </div>

                        <?php if(in_array('customize', $data->notification->settings->enabled_settings_tabs)): ?>
                        <div class="tab-pane fade" id="tab_themes" role="tabpanel" aria-labelledby="tab_themes_link">
                                <div class="row mx-n2 mb-3 mt-4">
                                    <?php foreach($available_notification_themes as $key => $theme): ?>
                                        <?php
                                        $theme_notification = clone $data->notification;
                                        $theme_notification->settings = clone $data->notification->settings;
                                        $theme_notification->notification_id = sprintf('%u', crc32($data->notification->notification_id . $key));

                                        foreach($theme['settings'] as $theme_key => $theme_value) {
                                            if(property_exists($theme_notification->settings, $theme_key)) {
                                                $theme_notification->settings->{$theme_key} = $theme_value;
                                            }
                                        }

                                        $theme_notification->settings->custom_css = '';
                                        $theme_notification->settings->hover_animation = '';
                                        $theme_notification->settings->display_branding = false;
                                        $theme_notification->settings->dark_mode_is_enabled = false;

                                        if($data->notification->type == 'custom_html') {
                                            $theme_notification->settings->html = l('notification.custom_html.html_default');
                                        }

                                        if($data->notification->type == 'video') {
                                            $theme_notification->settings->video = '';
                                            $theme_notification->settings->video_autoplay = false;
                                        }

                                        if($data->notification->type == 'audio') {
                                            $theme_notification->settings->audio_autoplay = false;
                                        }

                                        $theme_preview_html = \Altum\Notification::get($data->notification->type, $theme_notification, $this->user, force_branding: false, force_branding_data: false, is_preview: true)->html;
                                        $theme_preview_html = preg_replace('/<\/?form\b[^>]*>/i', '', $theme_preview_html);
                                        $theme_preview_html = preg_replace('/<(input|button|select|textarea)\b(?![^>]*\bdisabled\b)/i', '<$1 disabled', $theme_preview_html);
                                        $theme_preview_html = str_replace('<audio ', '<audio preload="none" ', $theme_preview_html);
                                        ?>

                                        <label class="col-12 col-xl-6 p-2 custom-radio-box m-0">
                                            <input type="radio" name="notification_theme" value="<?= $key ?>" class="custom-control-input" data-notification-theme />

                                            <div class="card h-100" role="button" data-toggle="tooltip" title="<?= l('notification.settings.theme_apply') ?>" data-tooltip-hide-on-click>
                                                <div class="card-body d-flex flex-column">
                                                    <h3 class="h6 mb-3"><?= $theme['name'] ?></h3>

                                                    <div class="notification-theme-preview notification-theme-preview-<?= $data->notification->type ?> <?= str_contains($data->notification->type, '_bar') ? 'notification-theme-preview-bar' : null ?> rounded mb-3 flex-grow-1" style="background-color: <?= $theme['preview_background_color'] ?>;" inert aria-hidden="true">
                                                        <?= $theme_preview_html ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    <?php endforeach ?>
                                </div>
                        </div>
                        <?php endif ?>

                        <div class="tab-pane fade" id="tab_data" role="tabpanel" aria-labelledby="tab_data_link">
                            <?= $settings->html['data'] ?? null ?>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" name="submit" class="btn btn-lg btn-block btn-primary"><?= l('global.update') ?></button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <?php if(!str_contains($data->notification->type, '_bar')): ?>
        <div class="col-12 d-flex justify-content-center col-lg-5 order-0 order-lg-1 mb-5 mb-lg-0">
            <div id="notification_preview" class="notification-preview notification-preview-<?= mb_strtolower($data->notification->type) ?>">
                <?= \Altum\Notification::get($data->notification->type, $data->notification, $this->user, is_preview: true)->html ?>
            </div>
        </div>
    <?php endif ?>
</div>

<?php ob_start() ?>
<style>
    .notification-theme-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 14rem;
        padding: 1.5rem;
        overflow: hidden;
        pointer-events: none;
    }

    .notification-theme-preview .altumcode-wrapper {
        flex-shrink: 0;
        transform: scale(.7) !important;
        transform-origin: center;
    }

    .notification-theme-preview-bar .altumcode-wrapper {
        width: 175% !important;
        max-width: 175% !important;
    }

    .notification-theme-preview-engagement_links,
    .notification-theme-preview-help_center,
    .notification-theme-preview-whatsapp_chat,
    .notification-theme-preview-telegram_chat,
    .notification-theme-preview-facebook_messenger_chat {
        min-height: 24rem;
    }

    @media (max-width: 575.98px) {
        .notification-theme-preview {
            min-height: 15rem;
            padding: 1rem;
        }

        .notification-theme-preview .altumcode-wrapper {
            transform: scale(.5) !important;
        }

        .notification-theme-preview-engagement_links,
        .notification-theme-preview-help_center,
        .notification-theme-preview-whatsapp_chat,
        .notification-theme-preview-telegram_chat,
        .notification-theme-preview-facebook_messenger_chat {
            min-height: 21rem;
        }
    }
</style>
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>


<div style="display:none" id="trigger_rule_sample">
    <div class="form-row">
        <div class="form-group col-lg-4">
            <select class="form-control" name="trigger_type[]" data-is-not-custom-select>
                <option value="exact" data-placeholder="<?= l('notification.settings.trigger_type_exact_placeholder') ?>"><?= l('notification.settings.trigger_type_exact') ?></option>
                <option value="not_exact" data-placeholder="<?= l('notification.settings.trigger_type_not_exact_placeholder') ?>"><?= l('notification.settings.trigger_type_not_exact') ?></option>
                <option value="contains" data-placeholder="<?= l('notification.settings.trigger_type_contains_placeholder') ?>"><?= l('notification.settings.trigger_type_contains') ?></option>
                <option value="not_contains" data-placeholder="<?= l('notification.settings.trigger_type_not_contains_placeholder') ?>"><?= l('notification.settings.trigger_type_not_contains') ?></option>
                <option value="starts_with" data-placeholder="<?= l('notification.settings.trigger_type_starts_with_placeholder') ?>"><?= l('notification.settings.trigger_type_starts_with') ?></option>
                <option value="not_starts_with" data-placeholder="<?= l('notification.settings.trigger_type_not_starts_with_placeholder') ?>"><?= l('notification.settings.trigger_type_not_starts_with') ?></option>
                <option value="ends_with" data-placeholder="<?= l('notification.settings.trigger_type_ends_with_placeholder') ?>"><?= l('notification.settings.trigger_type_ends_with') ?></option>
                <option value="not_ends_with" data-placeholder="<?= l('notification.settings.trigger_type_not_ends_with_placeholder') ?>"><?= l('notification.settings.trigger_type_not_ends_with') ?></option>
                <option value="page_contains" data-placeholder="<?= l('notification.settings.trigger_type_page_contains_placeholder') ?>"><?= l('notification.settings.trigger_type_page_contains') ?></option>
            </select>
        </div>

        <div class="form-group col-lg-6">
            <input type="text" name="trigger_value[]" class="form-control" value="">
        </div>

        <div class="form-group col-lg-2">
            <button type="button" class="trigger-delete btn btn-block btn-outline-danger" title="<?= l('global.delete') ?>"><i class="fas fa-fw fa-times"></i></button>
        </div>
    </div>
</div>

<div style="display:none" id="data_trigger_auto_rule_sample">
    <div class="form-row">
        <div class="form-group col-lg-4">
            <select class="form-control" name="data_trigger_auto_type[]" data-is-not-custom-select>
                <option value="exact" data-placeholder="<?= l('notification.settings.trigger_type_exact_placeholder') ?>"><?= l('notification.settings.trigger_type_exact') ?></option>
                <option value="contains" data-placeholder="<?= l('notification.settings.trigger_type_contains_placeholder') ?>"><?= l('notification.settings.trigger_type_contains') ?></option>
                <option value="starts_with" data-placeholder="<?= l('notification.settings.trigger_type_starts_with_placeholder') ?>"><?= l('notification.settings.trigger_type_starts_with') ?></option>
                <option value="ends_with" data-placeholder="<?= l('notification.settings.trigger_type_ends_with_placeholder') ?>"><?= l('notification.settings.trigger_type_ends_with') ?></option>
                <option value="page_contains" data-placeholder="<?= l('notification.settings.trigger_type_page_contains_placeholder') ?>"><?= l('notification.settings.trigger_type_page_contains') ?></option>
            </select>
        </div>

        <div class="form-group col-lg-6">
            <input type="text" name="data_trigger_auto_value[]" class="form-control" placeholder="<?= l('notification.settings.trigger_type_exact_placeholder') ?>" aria-label="<?= l('notification.settings.trigger_type_exact_placeholder') ?>">
        </div>

        <div class="form-group col-lg-2">
            <button type="button" class="data-trigger-auto-delete btn btn-block btn-outline-danger" title="<?= l('global.delete') ?>"><i class="fas fa-fw fa-times"></i></button>
        </div>
    </div>
</div>

<template id="template_translation_container">
    <div class="p-3 bg-gray-50 rounded mb-4">
        <div id="translations">

        </div>

        <div class="mb-4">
            <button data-add-translation="" type="button" class="btn btn-block btn-outline-success"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('global.create') ?></button>
        </div>
    </div>
</template>

<template id="template_translation_input">
    <div class="translation">
        <div class="row">
            <div class="form-group col-3">
                <select name="translations[language][index][key]" class="custom-select" data-is-not-custom-select>
                    <?php foreach(get_locale_languages_array() as $locale => $language): ?>
                        <option value="<?= $locale ?>"><?= $language ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="form-group col-7">
                <input type="text" name="translations[language][index][value]" value="" class="form-control" placeholder="<?= l('notification.settings.translation') ?>" />
            </div>

            <div class="col-2">
                <button type="button" data-remove-translation="type" class="mb-3 btn font-size-small btn-block btn-outline-danger" title="<?= l('global.delete') ?>">
                    <i class="fas fa-fw fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<?php ob_start() ?>
<script>
    'use strict';

    function decode_htmlspecialchars(encoded_string) {
        const textarea = document.createElement('textarea');
        textarea.innerHTML = encoded_string;
        return textarea.value;
    }

    let locales_count = <?= json_encode(count(get_locale_languages_array())) ?>;

    /* Process translations */
    document.querySelectorAll(`[data-translation-container]`).forEach(element => {
        let type = element.getAttribute('data-translation-container');
        let translations = JSON.parse(element.innerHTML.trim());
        element.innerHTML = '';

        let translation_container_clone = document.querySelector(`#template_translation_container`).content.cloneNode(true);

        for (const translation of Object.entries(translations)) {
            let [language_code, translation_value] = translation;

            let count = translation_container_clone.querySelectorAll(`.translation`).length;
            if(count >= locales_count) {
                return;
            }

            let translation_input_clone = document.querySelector(`#template_translation_input`).content.cloneNode(true);
            translation_input_clone.querySelector(`select`).setAttribute('name', `translations[${type}][${count}][key]`);
            translation_input_clone.querySelector(`select`).value = language_code;
            translation_input_clone.querySelector(`input`).setAttribute('name', `translations[${type}][${count}][value]`);
            translation_input_clone.querySelector(`input`).setAttribute('value', decode_htmlspecialchars(translation_value));
            translation_input_clone.querySelector(`[data-remove-translation]`).setAttribute('data-remove-translation', type);

            translation_container_clone.querySelector('#translations').appendChild(translation_input_clone);
        }

        translation_container_clone.querySelector('#translations').setAttribute('id', `${type}_translations`);
        translation_container_clone.querySelector('button[data-add-translation]').setAttribute('data-add-translation', type);

        element.appendChild(translation_container_clone);
    })

    /* Enable / disable already existing translations */
    let get_existing_translations_for_type = type => {
        let existing_translations = [];

        let languages_select = document.querySelectorAll(`#${type}_translations select`);

        languages_select.forEach(select => existing_translations.push(select.value))

        return existing_translations;
    }

    /* add new */
    let add_translation = event => {
        let type = event.currentTarget.getAttribute('data-add-translation') || event.currentTarget.getAttribute('data-translation-container');
        let clone = document.querySelector(`#template_translation_input`).content.cloneNode(true);
        let count = document.querySelectorAll(`#${type}_translations .translation`).length;

        if(count >= locales_count) {
            return;
        }

        clone.querySelector(`select`).setAttribute('name', `translations[${type}][${count}][key]`);
        clone.querySelector(`input`).setAttribute('name', `translations[${type}][${count}][value]`);
        clone.querySelector('[data-remove-translation]').setAttribute('data-remove-translation', type);

        /* Detect already translated fields and disable them */
        let existing_translations = get_existing_translations_for_type(type);
        Array.from(clone.querySelector(`select`).options).forEach(option => {
            if(existing_translations.includes(option.value)) {
                option.disabled = true;
            }
        })

        /* Pre select user language if not already translated */
        if(!existing_translations.includes(navigator.language) && clone.querySelector(`select option[value="${navigator.language}"]`)){
            clone.querySelector(`select`).value = navigator.language;
        } else {
            clone.querySelector(`select`).value = clone.querySelector(`select option:not([disabled])`).value;
        }

        /* Append */
        document.querySelector(`#${type}_translations`).appendChild(clone);

        remove_translation_initiator();
    };

    document.querySelectorAll('[data-add-translation]').forEach(element => {
        element.addEventListener('click', add_translation);
    })

    /* remove  */
    let remove_translation = event => {
        let type = event.currentTarget.getAttribute('data-remove-translation');
        let translation_container = event.currentTarget.closest('.translation');
        let selected_translation_language = translation_container.querySelector('select').value;

        /* Remove the translation */
        translation_container.remove();

        /* Make sure to enable the option if possible */
        document.querySelectorAll(`#${type}_translations select`).forEach(element => {
            Array.from(element.options).forEach(option => {
                if(option.value == selected_translation_language) {
                    option.disabled = false;
                }
            })
        })
    };

    let remove_translation_initiator = () => {
        document.querySelectorAll('[data-remove-translation]').forEach(element => {
            element.removeEventListener('click', remove_translation);
            element.addEventListener('click', remove_translation)
        })
    };

    remove_translation_initiator();

    /* Add one translatable input if none existing */
    $(`[data-translation-container]`).on('show.bs.collapse', event => {
        let type = event.currentTarget.getAttribute('data-translation-container');
        let count = event.currentTarget.querySelectorAll(`.translation`).length;

        if(count == 0) {
            add_translation(event);
        }
    })
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/libraries/daterangepicker.min.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/daterangepicker.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment-timezone-with-data-10-year-range.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/pickr.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    'use strict';

    /* Initiate the color picker */
    let pickr_options = {
        comparison: false,

        components: {
            preview: true,
            opacity: true,
            hue: true,
            comparison: false,
            interaction: {
                hex: true,
                rgba: false,
                hsla: false,
                hsva: false,
                cmyk: false,
                input: true,
                clear: false,
                save: false,
            }
        }
    };

    /* Display Trigger Handler */
    type_handler('input[name="display_trigger"]', 'data-display-trigger');
    document.querySelector('input[name="display_trigger"]') && document.querySelectorAll('input[name="display_trigger"]').forEach(element => element.addEventListener('change', () => { type_handler('input[name="display_trigger"]', 'data-display-trigger'); }));

    /* Display Trigger Handler */
    type_handler('input[name="display_delay_type_after_close"]', 'data-display-delay-type-after-close');
    document.querySelector('input[name="display_delay_type_after_close"]') && document.querySelectorAll('input[name="display_delay_type_after_close"]').forEach(element => element.addEventListener('change', () => { type_handler('input[name="display_delay_type_after_close"]', 'data-display-delay-type-after-close'); }));

    /* Triggers Handler */
    let triggers_status_handler = () => {

        if($('#trigger_all_pages').is(':checked')) {

            /* Disable the container visually */
            $('#triggers').addClass('container-disabled');

            /* Remove the new trigger add button */
            $('#trigger_add').hide();

        } else {

            /* Remove disabled container if depending on the status of the trigger checkbox */
            $('#triggers').removeClass('container-disabled');

            /* Bring back the new trigger add button */
            $('#trigger_add').show();

        }

        $('select[name="trigger_type[]"]').off().on('change', event => {

            let input = $(event.currentTarget).closest('.form-row').find('input');
            let placeholder = $(event.currentTarget).find(':checked').data('placeholder');

            /* Add the proper placeholder */
            input.attr('placeholder', placeholder);

        }).trigger('change');

    };

    /* Trigger on status change live of the checkbox */
    $('#trigger_all_pages').on('change', triggers_status_handler);

    /* Delete trigger handler */
    let triggers_delete_handler = () => {

        /* Delete button handler */
        $('.trigger-delete').off().on('click', event => {

            let trigger = $(event.currentTarget).closest('.form-row');

            trigger.remove();

            triggers_count_handler();
        });

    };

    let triggers_add_sample = () => {
        let trigger_rule_sample = $('#trigger_rule_sample').html();

        $('#triggers').append(trigger_rule_sample);
    };

    let triggers_count_handler = () => {
        let total_triggers = $('#triggers > .form-row').length;

        /* Make sure we at least have two input groups to show the delete button */
        if(total_triggers > 1) {
            $('#triggers .trigger-delete').removeAttr('disabled');

            /* Make sure to set a limit to these triggers */
            if(total_triggers > 10) {
                $('#trigger_add').hide();
            } else {
                $('#trigger_add').show();
            }

        } else {

            if(total_triggers == 0) {
                triggers_add_sample();
            }

            $('#triggers .trigger-delete').attr('disabled', 'disabled');
        }
    };

    /* Add new trigger rule handler */
    $('#trigger_add').on('click', () => {
        triggers_add_sample();
        triggers_delete_handler();
        triggers_count_handler();
        triggers_status_handler();
    });

    /* Trigger functions for the first initial load */
    triggers_status_handler();
    triggers_delete_handler();
    triggers_count_handler();

    $('#dark_mode_container').on('shown.bs.collapse', () => {
        $('#notification_preview').addClass('dark-preview');
        apply_preview('dark');
    });

    $('#dark_mode_container').on('hidden.bs.collapse', () => {
        $('#notification_preview').removeClass('dark-preview');
        apply_preview('light');
    });

    /* Preview fields */
    let preview_fields = {
        light: [],
        dark: []
    };

    /* Reusable color pickr initializer */
    function init_color_pickr(input_selector, pickr_selector, preview_selector, css_property, mode = 'light', key) {
        if(!document.querySelector(input_selector)) {
            return;
        }

        let options = { ...pickr_options };

        /* Disable opacity for shadow color */
        if(key === 'shadow') {
            options.components = { ...pickr_options.components, opacity: false };
        }

        let color_pickr = Pickr.create({
            el: pickr_selector,
            default: $(input_selector).val(),
            ...options
        });

        preview_fields[mode].push({ input_selector, preview_selector, css_property, key, color_pickr });

        color_pickr.on('change', hsva => {
            let color_value = hsva.toHEXA().toString();
            $(input_selector).val(color_value);

            if(key === 'shadow') {
                let rgba = hsva.toRGBA();
                if(mode === 'light' && !$('#dark_mode_container').hasClass('show')) {
                    $('#notification_preview ' + preview_selector).css('--shadow-r', rgba[0]);
                    $('#notification_preview ' + preview_selector).css('--shadow-g', rgba[1]);
                    $('#notification_preview ' + preview_selector).css('--shadow-b', rgba[2]);
                }
                if(mode === 'dark' && $('#dark_mode_container').hasClass('show')) {
                    $('#notification_preview ' + preview_selector).css('--shadow-r', rgba[0]);
                    $('#notification_preview ' + preview_selector).css('--shadow-g', rgba[1]);
                    $('#notification_preview ' + preview_selector).css('--shadow-b', rgba[2]);
                }
            } else {
                if(mode === 'light' && !$('#dark_mode_container').hasClass('show')) {
                    $('#notification_preview ' + preview_selector).css(css_property, color_value);
                }
                if(mode === 'dark' && $('#dark_mode_container').hasClass('show')) {
                    $('#notification_preview ' + preview_selector).css(css_property, color_value);
                }
            }
        });
    }

    function hex_to_rgb(hex) {
        hex = hex.replace(/^#/, '');
        if(hex.length === 3) {
            hex = hex.split('').map(c => c + c).join('');
        }
        let num = parseInt(hex, 16);
        return [(num >> 16) & 255, (num >> 8) & 255, num & 255];
    }

    /* Reapply preview */
    let apply_preview = mode => {
        preview_fields[mode].forEach(field => {
            let value = $(field.input_selector).val();

            if(field.key === 'shadow') {
                let rgb = hex_to_rgb(value);
                $('#notification_preview ' + field.preview_selector).css('--shadow-r', rgb[0]);
                $('#notification_preview ' + field.preview_selector).css('--shadow-g', rgb[1]);
                $('#notification_preview ' + field.preview_selector).css('--shadow-b', rgb[2]);
            } else {
                $('#notification_preview ' + field.preview_selector).css(field.css_property, value);
            }
        });
    };

    /* Themes */
    let notification_themes = <?= json_encode($available_notification_themes) ?>;

    document.querySelectorAll('[data-notification-theme]').forEach(element => element.addEventListener('click', event => {
        let theme = notification_themes[event.currentTarget.value].settings;

        Object.entries(theme).forEach(([name, value]) => {
            let elements = document.querySelectorAll(`#notification_update [name="${name}"]`);

            if(!elements.length) {
                return;
            }

            let element = elements[0];

            if(element.type === 'radio') {
                elements.forEach(element => {
                    element.checked = element.value == value;
                    element.closest('label').classList.toggle('active', element.checked);
                });

                let checked_element = Array.from(elements).find(element => element.checked);
                checked_element && checked_element.dispatchEvent(new Event('change'));

                return;
            }

            if(element.type === 'checkbox') {
                element.checked = value;
                element.dispatchEvent(new Event('change'));

                return;
            }

            elements.forEach(element => {
                element.value = value;
                element.dispatchEvent(new Event('input'));
                element.dispatchEvent(new Event('change'));
            });

            let preview_field = [...preview_fields.light, ...preview_fields.dark].find(field => field.input_selector === `#${element.id}`);
            preview_field && preview_field.color_pickr.setColor(value);
        });

        apply_preview($('#dark_mode_container').hasClass('show') ? 'dark' : 'light');
    }));

    /* Light mode */
    init_color_pickr('#settings_close_button_color', '#settings_close_button_color_pickr', '.altumcode-close', 'color', 'light', 'close_button');

    /* Dark mode */
    init_color_pickr('#dark_mode_close_button_color', '#dark_mode_close_button_color_pickr', '.altumcode-close', 'color', 'dark', 'close_button');

    /* Border radius preview */
    $('input[name="border_radius"]').on('change', event => {
        let border_radius = event.currentTarget.value;
        let notification_preview_wrapper = $('#notification_preview .altumcode-wrapper');
        notification_preview_wrapper.removeClass('altumcode-wrapper-round altumcode-wrapper-straight altumcode-wrapper-rounded altumcode-wrapper-highly_rounded').addClass(`altumcode-wrapper-${border_radius}`);
    });

    /* Font preview */
    $('input[name="font"]').on('change', event => {
        let font = event.currentTarget.value;
        document.querySelector('#notification_preview .altumcode-wrapper').style.fontFamily = font;
    });

    /* Shadow preview */
    $('input[name="shadow"]').on('change', event => {
        let shadow = event.currentTarget.value;
        let notification_preview_wrapper = document.querySelector('#notification_preview .altumcode-wrapper');

        let prefix = 'altumcode-wrapper-shadow-';
        let classes = notification_preview_wrapper.className.split(" ").filter(c => !c.startsWith(prefix));
        notification_preview_wrapper.className = classes.join(" ").trim();

        notification_preview_wrapper.classList.add(`altumcode-wrapper-shadow-${shadow}`);
    });

    /* Light mode */
    init_color_pickr('#settings_shadow_color', '#settings_shadow_color_pickr', '.altumcode-wrapper', null, 'light', 'shadow');
    init_color_pickr('#settings_border_color', '#settings_border_color_pickr', '.altumcode-wrapper', 'border-color', 'light', 'border');

    /* Dark mode */
    init_color_pickr('#dark_mode_shadow_color', '#dark_mode_shadow_color_pickr', '.altumcode-wrapper', null, 'dark', 'shadow');
    init_color_pickr('#dark_mode_border_color', '#dark_mode_border_color_pickr', '.altumcode-wrapper', 'border-color', 'dark', 'border');

    /* Internal Padding Handler */
    $('#settings_internal_padding').on('change', event => {

        /* Notification Preview Handler */
        document.querySelector('#notification_preview .altumcode-wrapper').style.setProperty('padding', $(event.currentTarget).val() + 'px', 'important');

    });

    /* Background blur */
    document.querySelector('#background_blur').addEventListener('change', event => {
        let blur = document.querySelector('#background_blur').value;
        $('#notification_preview .altumcode-wrapper').css('backdrop-filter',  `blur(${blur}px)`);
        $('#notification_preview .altumcode-wrapper').css('-webkit-backdrop-filter',  `blur(${blur}px)`);
    });

    /* Border Width Handler */
    $('#settings_border_width').on('change', event => {

        /* Notification Preview Handler */
        $('#notification_preview .altumcode-wrapper').css('border-width', $(event.currentTarget).val());

    });

    /* Shadow handler */
    $('#settings_shadow').on('change', event => {

        /* Notification Preview Handler */
        if($(event.currentTarget).is(':checked')) {
            $('#notification_preview .altumcode-wrapper').addClass('altumcode-wrapper-shadow');
        } else {
            $('#notification_preview .altumcode-wrapper').removeClass('altumcode-wrapper-shadow');
        }

    });

    /* Close button handler */
    $('#display_close_button').on('change', event => {

        /* Notification Preview Handler */
        if($(event.currentTarget).is(':checked')) {
            $('#notification_preview .altumcode-close').css('display', 'inherit');
        } else {
            $('#notification_preview .altumcode-close').css('display', 'none');
        }
    });

    /* Branding handler */
    $('#display_branding').on('change', event => {

        /* Notification Preview Handler */
        if($(event.currentTarget).is(':checked')) {
            $('#notification_preview .altumcode-site').css('display', 'inherit');
        } else {
            $('#notification_preview .altumcode-site').attr('style', 'display: none !important;');
        }
    });

    /* Failsafe on _color fields being empty */
    $('input[name$="_color"]').on('change paste keyup', event => {
        if($(event.currentTarget).val().trim() == '') {
            $(event.currentTarget).val('#000000');
        }
    });

    /* Schedule */
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
    }, (start, end, label) => {});
</script>

<?= $settings->javascript ?>

<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php include_view(THEME_PATH . 'views/partials/js_cropper.php') ?>
