<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
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
    <label for="settings_description"><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('notification.settings.description') ?></label>
    <div class="input-group">
        <input type="text" id="settings_description" name="description" class="form-control" value="<?= e($data->notification->settings->description) ?>" maxlength="512" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#description_translate_container" aria-expanded="false" aria-controls="description_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
    <small class="form-text text-muted"><?= l('notification.settings.rating_summary_description_help') ?></small>
    <small class="form-text text-muted" data-toggle="tooltip" title="<?= l('notification.settings.html_info_tooltip') ?>"><?= l('notification.settings.html_info') ?></small>
    <small class="form-text text-muted"><?= l('global.spintax_help') ?></small>
</div>

<div class="collapse" id="description_translate_container" data-translation-container="description">
    <?= htmlspecialchars(json_encode($data->notification->settings->translations->description ?? ''), ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="form-group">
    <label for="settings_average_rating"><i class="fas fa-fw fa-sm fa-star-half-alt text-muted mr-1"></i> <?= l('notification.settings.fallback_average_rating') ?></label>
    <input type="number" min="1" max="5" step="0.1" id="settings_average_rating" name="average_rating" class="form-control" value="<?= $data->notification->settings->average_rating ?>" required="required" />
</div>

<div class="form-group">
    <label for="settings_reviews_count"><i class="fas fa-fw fa-sm fa-hashtag text-muted mr-1"></i> <?= l('notification.settings.fallback_reviews_count') ?></label>
    <input type="number" min="0" id="settings_reviews_count" name="reviews_count" class="form-control" value="<?= $data->notification->settings->reviews_count ?>" required="required" />
</div>

<div class="form-group">
    <label for="settings_display_minimum_reviews"><i class="fas fa-fw fa-sm fa-users text-muted mr-1"></i> <?= l('notification.settings.display_minimum_reviews') ?></label>
    <input type="number" min="0" id="settings_display_minimum_reviews" name="display_minimum_reviews" class="form-control" value="<?= $data->notification->settings->display_minimum_reviews ?>" required="required" />
    <small class="form-text text-muted"><?= l('notification.settings.display_minimum_reviews_help') ?></small>
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

<?php /* Customize Tab */ ?>
<?php ob_start() ?>
<div class="form-group">
    <label for="settings_rating_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.rating_color') ?></label>
    <input type="hidden" id="settings_rating_color" name="rating_color" class="form-control" value="<?= $data->notification->settings->rating_color ?? '#FFFFFF' ?>" />
    <div id="settings_rating_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_rating_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.rating_background_color') ?></label>
    <input type="hidden" id="settings_rating_background_color" name="rating_background_color" class="form-control" value="<?= $data->notification->settings->rating_background_color ?? '#5b83ff' ?>" />
    <div id="settings_rating_background_color_pickr"></div>
</div>

<div class="form-group">
    <label for="settings_stars_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.stars_color') ?></label>
    <input type="hidden" id="settings_stars_color" name="stars_color" class="form-control" value="<?= $data->notification->settings->stars_color ?>" />
    <div id="settings_stars_color_pickr"></div>
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
        <label for="dark_mode_rating_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.rating_color') ?></label>
        <input type="hidden" id="dark_mode_rating_color" name="dark_mode_rating_color" class="form-control" value="<?= $data->notification->settings->dark_mode_rating_color ?? '#FFFFFF' ?>" />
        <div id="dark_mode_rating_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_rating_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.rating_background_color') ?></label>
        <input type="hidden" id="dark_mode_rating_background_color" name="dark_mode_rating_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_rating_background_color ?? '#002c73' ?>" />
        <div id="dark_mode_rating_background_color_pickr"></div>
    </div>

    <div class="form-group">
        <label for="dark_mode_stars_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.stars_color') ?></label>
        <input type="hidden" id="dark_mode_stars_color" name="dark_mode_stars_color" class="form-control" value="<?= $data->notification->settings->dark_mode_stars_color ?>" />
        <div id="dark_mode_stars_color_pickr"></div>
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
    <small class="form-text text-muted"><?= sprintf(l('notification.settings.data_trigger_webhook_help_reviews'), '<code>' . implode('</code>, <code>', ['title', 'description', 'image', 'image_alt', 'stars']) . '</code>') ?></small>
</div>
<?php $html['data'] = ob_get_clean() ?>

<?php ob_start() ?>
<script>
    'use strict';

    let update_rating_summary_preview = () => {
        let average_rating = $('#settings_average_rating').val();
        let reviews_count = $('#settings_reviews_count').val();
        let description = $('#settings_description').val().replaceAll('{average_rating}', average_rating).replaceAll('{count}', reviews_count);

        $('#notification_preview .altumcode-rating-summary-rating').text(average_rating);
        $('#notification_preview .altumcode-rating-summary-description').text(description);
        $('#notification_preview .altumcode-rating-summary-stars-foreground').css('width', `${Math.min(100, Math.max(0, average_rating / 5 * 100))}%`);
    };

    $('#settings_description, #settings_average_rating, #settings_reviews_count').on('change paste keyup', update_rating_summary_preview);

    /* light mode */
    init_color_pickr('#settings_rating_color', '#settings_rating_color_pickr', '.altumcode-rating-summary-rating', 'color', 'light', 'rating');
    init_color_pickr('#settings_rating_background_color', '#settings_rating_background_color_pickr', '.altumcode-rating-summary-rating', 'background-color', 'light', 'rating_background');
    init_color_pickr('#settings_stars_color', '#settings_stars_color_pickr', '.altumcode-rating-summary-stars-foreground', 'color', 'light', 'stars');
    init_color_pickr('#settings_description_color', '#settings_description_color_pickr', '.altumcode-rating-summary-description', 'color', 'light', 'description');
    init_color_pickr('#settings_background_color', '#settings_background_color_pickr', '.altumcode-wrapper', 'background-color', 'light', 'background');

    /* dark mode */
    init_color_pickr('#dark_mode_rating_color', '#dark_mode_rating_color_pickr', '.altumcode-rating-summary-rating', 'color', 'dark', 'rating');
    init_color_pickr('#dark_mode_rating_background_color', '#dark_mode_rating_background_color_pickr', '.altumcode-rating-summary-rating', 'background-color', 'dark', 'rating_background');
    init_color_pickr('#dark_mode_stars_color', '#dark_mode_stars_color_pickr', '.altumcode-rating-summary-stars-foreground', 'color', 'dark', 'stars');
    init_color_pickr('#dark_mode_description_color', '#dark_mode_description_color_pickr', '.altumcode-rating-summary-description', 'color', 'dark', 'description');
    init_color_pickr('#dark_mode_background_color', '#dark_mode_background_color_pickr', '.altumcode-wrapper', 'background-color', 'dark', 'background');
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
