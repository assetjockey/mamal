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
        <input type="text" id="settings_title" name="title" class="form-control" value="<?= htmlspecialchars($data->notification->settings->title, ENT_QUOTES, 'UTF-8') ?>" maxlength="256" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#title_translate_container" aria-expanded="false" aria-controls="title_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
    <small class="form-text text-muted" data-toggle="tooltip" title="<?= l('notification.settings.html_info_tooltip') ?>"><?= l('notification.settings.html_info') ?></small>
    <small class="form-text text-muted"><?= l('global.spintax_help') ?></small>
</div>

<div class="collapse" id="title_translate_container" data-translation-container="title">
    <?= htmlspecialchars(json_encode(isset($data->notification->settings->translations->title) ? $data->notification->settings->translations->title : ''), ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="form-group">
    <label for="settings_description"><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('notification.settings.description') ?></label>
    <div class="input-group">
        <input type="text" id="settings_description" name="description" class="form-control" value="<?= htmlspecialchars($data->notification->settings->description, ENT_QUOTES, 'UTF-8') ?>" maxlength="512" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#description_translate_container" aria-expanded="false" aria-controls="description_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
    <small class="form-text text-muted" data-toggle="tooltip" title="<?= l('notification.settings.html_info_tooltip') ?>"><?= l('notification.settings.html_info') ?></small>
    <small class="form-text text-muted"><?= l('global.spintax_help') ?></small>
</div>

<div class="collapse" id="description_translate_container" data-translation-container="description">
    <?= htmlspecialchars(json_encode(isset($data->notification->settings->translations->description) ? $data->notification->settings->translations->description : ''), ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="form-group">
    <label for="settings_badge_text"><i class="fas fa-fw fa-sm fa-certificate text-muted mr-1"></i> <?= l('notification.settings.badge_text') ?></label>
    <div class="input-group">
        <input type="text" id="settings_badge_text" name="badge_text" class="form-control" value="<?= htmlspecialchars($data->notification->settings->badge_text, ENT_QUOTES, 'UTF-8') ?>" maxlength="64" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#badge_text_translate_container" aria-expanded="false" aria-controls="badge_text_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
    <small class="form-text text-muted"><?= l('global.spintax_help') ?></small>
</div>

<div class="collapse" id="badge_text_translate_container" data-translation-container="badge_text">
    <?= htmlspecialchars(json_encode(isset($data->notification->settings->translations->badge_text) ? $data->notification->settings->translations->badge_text : ''), ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="form-group">
    <label for="settings_hint_text"><i class="fas fa-fw fa-sm fa-comment-alt text-muted mr-1"></i> <?= l('notification.settings.hint_text') ?></label>
    <div class="input-group">
        <input type="text" id="settings_hint_text" name="hint_text" class="form-control" value="<?= htmlspecialchars($data->notification->settings->hint_text, ENT_QUOTES, 'UTF-8') ?>" maxlength="128" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#hint_text_translate_container" aria-expanded="false" aria-controls="hint_text_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
    <small class="form-text text-muted"><?= l('global.spintax_help') ?></small>
</div>

<div class="collapse" id="hint_text_translate_container" data-translation-container="hint_text">
    <?= htmlspecialchars(json_encode(isset($data->notification->settings->translations->hint_text) ? $data->notification->settings->translations->hint_text : ''), ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="form-group">
    <label for="settings_button_text"><i class="fas fa-fw fa-sm fa-quote-left text-muted mr-1"></i> <?= l('notification.settings.button_text') ?></label>
    <div class="input-group">
        <input type="text" id="settings_button_text" name="button_text" class="form-control" value="<?= htmlspecialchars($data->notification->settings->button_text, ENT_QUOTES, 'UTF-8') ?>" maxlength="128" />
        <div class="input-group-append">
            <button class="btn btn-dark font-size-small" type="button" data-toggle="collapse" data-target="#button_text_translate_container" aria-expanded="false" aria-controls="button_text_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
        </div>
    </div>
    <small class="form-text text-muted" data-toggle="tooltip" title="<?= l('notification.settings.html_info_tooltip') ?>"><?= l('notification.settings.html_info') ?></small>
    <small class="form-text text-muted"><?= l('global.spintax_help') ?></small>
</div>

<div class="collapse" id="button_text_translate_container" data-translation-container="button_text">
    <?= htmlspecialchars(json_encode(isset($data->notification->settings->translations->button_text) ? $data->notification->settings->translations->button_text : ''), ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="form-group">
    <label for="settings_button_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('notification.settings.url') ?></label>
    <input type="url" id="settings_button_url" name="button_url" class="form-control" value="<?= htmlspecialchars($data->notification->settings->button_url, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= l('global.url_placeholder') ?>" maxlength="2048" />
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

<div class="d-flex justify-content-between">
    <h3 class="h5"><?= l('notification.settings.help_center_items') ?></h3>

    <div>
        <button type="button" id="item_create" class="btn btn-success btn-sm"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('global.create') ?></button>
    </div>
</div>
<div id="items"></div>

<template id="template_item">
    <div class="item bg-gray-100 p-5 my-3 rounded">
        <div class="form-group">
            <label><i class="fas fa-fw fa-sm fa-question-circle text-muted mr-1"></i> <?= l('notification.settings.question') ?></label>
            <input type="text" name="items[item_index][question]" class="form-control" value="" maxlength="256" required="required" />
            <small class="form-text text-muted"><?= l('global.spintax_help') ?></small>
        </div>

        <div class="form-group">
            <label><i class="fas fa-fw fa-sm fa-align-left text-muted mr-1"></i> <?= l('notification.settings.answer') ?></label>
            <textarea name="items[item_index][answer]" class="form-control" maxlength="1024" required="required"></textarea>
            <small class="form-text text-muted"><?= l('global.spintax_help') ?></small>
        </div>

        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('notification.settings.url') ?></label>
                    <input type="url" name="items[item_index][url]" class="form-control" value="" maxlength="2048" placeholder="<?= l('global.url_placeholder') ?>" />
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label><i class="fas fa-fw fa-sm fa-quote-left text-muted mr-1"></i> <?= l('notification.settings.url_text') ?></label>
                    <input type="text" name="items[item_index][url_text]" class="form-control" value="" maxlength="128" />
                </div>
            </div>
        </div>

        <button type="button" class="item_delete btn btn-outline-danger btn-sm" title="<?= l('global.delete') ?>"><i class="fas fa-fw fa-times"></i> <?= l('global.delete') ?></button>
    </div>
</template>
<?php $html['basic'] = ob_get_clean() ?>


<?php /* Customize Tab */ ?>
<?php ob_start() ?>
<h3 class="h5"><?= l('notification.settings.badge_text') ?></h3>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_badge_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.badge_background_color') ?></label>
            <input type="hidden" id="settings_badge_background_color" name="badge_background_color" class="form-control" value="<?= $data->notification->settings->badge_background_color ?>" />
            <div id="settings_badge_background_color_pickr"></div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_badge_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.badge_color') ?></label>
            <input type="hidden" id="settings_badge_color" name="badge_color" class="form-control" value="<?= $data->notification->settings->badge_color ?>" />
            <div id="settings_badge_color_pickr"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_hint_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.hint_background_color') ?></label>
            <input type="hidden" id="settings_hint_background_color" name="hint_background_color" class="form-control" value="<?= $data->notification->settings->hint_background_color ?>" />
            <div id="settings_hint_background_color_pickr"></div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_hint_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.hint_color') ?></label>
            <input type="hidden" id="settings_hint_color" name="hint_color" class="form-control" value="<?= $data->notification->settings->hint_color ?>" />
            <div id="settings_hint_color_pickr"></div>
        </div>
    </div>
</div>

<hr class="my-3">

<h3 class="h5"><?= l('notification.settings.help_center_items') ?></h3>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_title_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.title_color') ?></label>
            <input type="hidden" id="settings_title_color" name="title_color" class="form-control" value="<?= $data->notification->settings->title_color ?>" />
            <div id="settings_title_color_pickr"></div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_description_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.description_color') ?></label>
            <input type="hidden" id="settings_description_color" name="description_color" class="form-control" value="<?= $data->notification->settings->description_color ?>" />
            <div id="settings_description_color_pickr"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_item_question_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.item_question_color') ?></label>
            <input type="hidden" id="settings_item_question_color" name="item_question_color" class="form-control" value="<?= $data->notification->settings->item_question_color ?>" />
            <div id="settings_item_question_color_pickr"></div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_item_answer_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.item_answer_color') ?></label>
            <input type="hidden" id="settings_item_answer_color" name="item_answer_color" class="form-control" value="<?= $data->notification->settings->item_answer_color ?>" />
            <div id="settings_item_answer_color_pickr"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_item_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.item_background_color') ?></label>
            <input type="hidden" id="settings_item_background_color" name="item_background_color" class="form-control" value="<?= $data->notification->settings->item_background_color ?>" />
            <div id="settings_item_background_color_pickr"></div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_item_border_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.item_border_color') ?></label>
            <input type="hidden" id="settings_item_border_color" name="item_border_color" class="form-control" value="<?= $data->notification->settings->item_border_color ?>" />
            <div id="settings_item_border_color_pickr"></div>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="settings_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.background_color') ?></label>
    <input type="hidden" id="settings_background_color" name="background_color" class="form-control" value="<?= $data->notification->settings->background_color ?>" />
    <div id="settings_background_color_pickr"></div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_button_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.button_background_color') ?></label>
            <input type="hidden" id="settings_button_background_color" name="button_background_color" class="form-control" value="<?= $data->notification->settings->button_background_color ?>" />
            <div id="settings_button_background_color_pickr"></div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_button_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.button_color') ?></label>
            <input type="hidden" id="settings_button_color" name="button_color" class="form-control" value="<?= $data->notification->settings->button_color ?>" />
            <div id="settings_button_color_pickr"></div>
        </div>
    </div>
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
    <input id="background_blur" type="range" min="0" max="30" class="form-control-range" name="background_blur" value="<?= $data->notification->settings->background_blur ?>" />
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

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_badge_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.badge_background_color') ?></label>
                <input type="hidden" id="dark_mode_badge_background_color" name="dark_mode_badge_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_badge_background_color ?>" />
                <div id="dark_mode_badge_background_color_pickr"></div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_badge_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.badge_color') ?></label>
                <input type="hidden" id="dark_mode_badge_color" name="dark_mode_badge_color" class="form-control" value="<?= $data->notification->settings->dark_mode_badge_color ?>" />
                <div id="dark_mode_badge_color_pickr"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_hint_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.hint_background_color') ?></label>
                <input type="hidden" id="dark_mode_hint_background_color" name="dark_mode_hint_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_hint_background_color ?>" />
                <div id="dark_mode_hint_background_color_pickr"></div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_hint_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.hint_color') ?></label>
                <input type="hidden" id="dark_mode_hint_color" name="dark_mode_hint_color" class="form-control" value="<?= $data->notification->settings->dark_mode_hint_color ?>" />
                <div id="dark_mode_hint_color_pickr"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_title_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.title_color') ?></label>
                <input type="hidden" id="dark_mode_title_color" name="dark_mode_title_color" class="form-control" value="<?= $data->notification->settings->dark_mode_title_color ?>" />
                <div id="dark_mode_title_color_pickr"></div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_description_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.description_color') ?></label>
                <input type="hidden" id="dark_mode_description_color" name="dark_mode_description_color" class="form-control" value="<?= $data->notification->settings->dark_mode_description_color ?>" />
                <div id="dark_mode_description_color_pickr"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_item_question_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.item_question_color') ?></label>
                <input type="hidden" id="dark_mode_item_question_color" name="dark_mode_item_question_color" class="form-control" value="<?= $data->notification->settings->dark_mode_item_question_color ?>" />
                <div id="dark_mode_item_question_color_pickr"></div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_item_answer_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.item_answer_color') ?></label>
                <input type="hidden" id="dark_mode_item_answer_color" name="dark_mode_item_answer_color" class="form-control" value="<?= $data->notification->settings->dark_mode_item_answer_color ?>" />
                <div id="dark_mode_item_answer_color_pickr"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_item_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.item_background_color') ?></label>
                <input type="hidden" id="dark_mode_item_background_color" name="dark_mode_item_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_item_background_color ?>" />
                <div id="dark_mode_item_background_color_pickr"></div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_item_border_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.item_border_color') ?></label>
                <input type="hidden" id="dark_mode_item_border_color" name="dark_mode_item_border_color" class="form-control" value="<?= $data->notification->settings->dark_mode_item_border_color ?>" />
                <div id="dark_mode_item_border_color_pickr"></div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="dark_mode_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.background_color') ?></label>
        <input type="hidden" id="dark_mode_background_color" name="dark_mode_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_background_color ?>" />
        <div id="dark_mode_background_color_pickr"></div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_button_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.button_background_color') ?></label>
                <input type="hidden" id="dark_mode_button_background_color" name="dark_mode_button_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_button_background_color ?>" />
                <div id="dark_mode_button_background_color_pickr"></div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_button_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.button_color') ?></label>
                <input type="hidden" id="dark_mode_button_color" name="dark_mode_button_color" class="form-control" value="<?= $data->notification->settings->dark_mode_button_color ?>" />
                <div id="dark_mode_button_color_pickr"></div>
            </div>
        </div>
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
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->shadow == '' ? 'active' : null ?>">
                    <input type="radio" name="shadow" value="" class="custom-control-input" <?= $data->notification->settings->shadow == '' ? 'checked="checked"' : null ?> />
                    <?= l('global.none') ?>
                </label>
            </div>
            <div class="col-4 p-2">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->shadow == 'subtle' ? 'active' : null ?>">
                    <input type="radio" name="shadow" value="subtle" class="custom-control-input" <?= $data->notification->settings->shadow == 'subtle' ? 'checked="checked"' : null ?> />
                    <?= l('notification.settings.shadow.subtle') ?>
                </label>
            </div>
            <div class="col-4 p-2">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->shadow == 'feather' ? 'active' : null ?>">
                    <input type="radio" name="shadow" value="feather" class="custom-control-input" <?= $data->notification->settings->shadow == 'feather' ? 'checked="checked"' : null ?> />
                    <?= l('notification.settings.shadow.feather') ?>
                </label>
            </div>
            <div class="col-4 p-2">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->shadow == '3d' ? 'active' : null ?>">
                    <input type="radio" name="shadow" value="3d" class="custom-control-input" <?= $data->notification->settings->shadow == '3d' ? 'checked="checked"' : null ?> />
                    <?= l('notification.settings.shadow.3d') ?>
                </label>
            </div>
            <div class="col-4 p-2">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->shadow == 'layered' ? 'active' : null ?>">
                    <input type="radio" name="shadow" value="layered" class="custom-control-input" <?= $data->notification->settings->shadow == 'layered' ? 'checked="checked"' : null ?> />
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
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->border_radius == 'straight' ? 'active' : null ?>">
                    <input type="radio" name="border_radius" value="straight" class="custom-control-input" <?= $data->notification->settings->border_radius == 'straight' ? 'checked="checked"' : null ?> />
                    <i class="fas fa-fw fa-square-full fa-sm mr-1"></i> <?= l('notification.settings.border_radius_straight') ?>
                </label>
            </div>
            <div class="col-4 p-2">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->border_radius == 'round' ? 'active' : null ?>">
                    <input type="radio" name="border_radius" value="round" class="custom-control-input" <?= $data->notification->settings->border_radius == 'round' ? 'checked="checked"' : null ?> />
                    <i class="fas fa-fw fa-circle fa-sm mr-1"></i> <?= l('notification.settings.border_radius_round') ?>
                </label>
            </div>
            <div class="col-4 p-2">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->border_radius == 'rounded' ? 'active' : null ?>">
                    <input type="radio" name="border_radius" value="rounded" class="custom-control-input" <?= $data->notification->settings->border_radius == 'rounded' ? 'checked="checked"' : null ?> />
                    <i class="fas fa-fw fa-square fa-sm mr-1"></i> <?= l('notification.settings.border_radius_rounded') ?>
                </label>
            </div>
            <div class="col-4 p-2">
                <label class="btn btn-light btn-block font-size-small text-truncate mb-0 <?= $data->notification->settings->border_radius == 'highly_rounded' ? 'active' : null ?>">
                    <input type="radio" name="border_radius" value="highly_rounded" class="custom-control-input" <?= $data->notification->settings->border_radius == 'highly_rounded' ? 'checked="checked"' : null ?> />
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

    /* Help center item handlers */
    let items = <?= json_encode($data->notification->settings->items) ?>;
    let template_item = document.querySelector('#template_item');

    /* Create new item */
    let item_create = (item_index = null, question = null, answer = null, url = null, url_text = null) => {
        let item_template_clone = template_item.content.cloneNode(true);

        $(item_template_clone).find('.item').attr('data-item-index', item_index);
        $(item_template_clone).find('input[name="items[item_index][question]"]').attr('name', `items[${item_index}][question]`).val(question);
        $(item_template_clone).find('textarea[name="items[item_index][answer]"]').attr('name', `items[${item_index}][answer]`).val(answer);
        $(item_template_clone).find('input[name="items[item_index][url]"]').attr('name', `items[${item_index}][url]`).val(url);
        $(item_template_clone).find('input[name="items[item_index][url_text]"]').attr('name', `items[${item_index}][url_text]`).val(url_text);

        return item_template_clone;
    };

    if(items) {
        for (let item_index = 0; item_index < items.length; item_index++) {
            let item_template_clone = item_create(item_index, items[item_index].question, items[item_index].answer, items[item_index].url, items[item_index].url_text);

            $('#items').append(item_template_clone);
        }
    }

    let initiate_handlers = () => {
        $('#item_create').off().on('click', () => {
            let item_index = $('#items > .item').length;
            let new_item = item_create(item_index);

            $('#items').append(new_item);

            initiate_handlers();
        });

        $('#items > .item > .item_delete').off().on('click', event => {
            $(event.currentTarget).closest('.item').remove();

            initiate_handlers();
        });
    };

    initiate_handlers();

    $('#notification_preview .altumcode-help-center-window-holder').removeClass('altumcode-hidden').addClass('altumcode-shown');
    $('#notification_preview .altumcode-help-center-hint').addClass('altumcode-help-center-hint-hidden');

    /* Notification Preview Handlers */
    $('#settings_title').on('change paste keyup', event => {
        $('#notification_preview .altumcode-help-center-window-title').text($(event.currentTarget).val());
    });

    $('#settings_description').on('change paste keyup', event => {
        $('#notification_preview .altumcode-help-center-window-description').text($(event.currentTarget).val());
    });

    $('#settings_badge_text').on('change paste keyup', event => {
        $('#notification_preview .altumcode-help-center-badge-text').text($(event.currentTarget).val());
    });

    $('#settings_hint_text').on('change paste keyup', event => {
        $('#notification_preview .altumcode-help-center-hint').text($(event.currentTarget).val());
    });

    $('#settings_button_text').on('change paste keyup', event => {
        $('#notification_preview .altumcode-help-center-button').text($(event.currentTarget).val());
    });

    /* Light mode */
    init_color_pickr('#settings_badge_background_color', '#settings_badge_background_color_pickr', '.altumcode-help-center-wrapper', 'background-color', 'light', 'badge_background');
    init_color_pickr('#settings_badge_color', '#settings_badge_color_pickr', '.altumcode-help-center-badge-icon, .altumcode-help-center-badge-text', 'color', 'light', 'badge');
    init_color_pickr('#settings_hint_background_color', '#settings_hint_background_color_pickr', '.altumcode-help-center-hint', 'background-color', 'light', 'hint_background');
    init_color_pickr('#settings_hint_color', '#settings_hint_color_pickr', '.altumcode-help-center-hint', 'color', 'light', 'hint');
    init_color_pickr('#settings_title_color', '#settings_title_color_pickr', '.altumcode-help-center-window-title', 'color', 'light', 'title');
    init_color_pickr('#settings_description_color', '#settings_description_color_pickr', '.altumcode-help-center-window-description', 'color', 'light', 'description');
    init_color_pickr('#settings_item_question_color', '#settings_item_question_color_pickr', '.altumcode-help-center-question', 'color', 'light', 'item_question');
    init_color_pickr('#settings_item_answer_color', '#settings_item_answer_color_pickr', '.altumcode-help-center-answer', 'color', 'light', 'item_answer');
    init_color_pickr('#settings_item_background_color', '#settings_item_background_color_pickr', '.altumcode-help-center-item', 'background-color', 'light', 'item_background');
    init_color_pickr('#settings_item_border_color', '#settings_item_border_color_pickr', '.altumcode-help-center-item', 'border-color', 'light', 'item_border');
    init_color_pickr('#settings_background_color', '#settings_background_color_pickr', '.altumcode-help-center-window', 'background-color', 'light', 'background');
    init_color_pickr('#settings_button_background_color', '#settings_button_background_color_pickr', '.altumcode-help-center-button', 'background-color', 'light', 'button_background');
    init_color_pickr('#settings_button_color', '#settings_button_color_pickr', '.altumcode-help-center-button', 'color', 'light', 'button');

    /* Dark mode */
    init_color_pickr('#dark_mode_badge_background_color', '#dark_mode_badge_background_color_pickr', '.altumcode-help-center-wrapper', 'background-color', 'dark', 'badge_background');
    init_color_pickr('#dark_mode_badge_color', '#dark_mode_badge_color_pickr', '.altumcode-help-center-badge-icon, .altumcode-help-center-badge-text', 'color', 'dark', 'badge');
    init_color_pickr('#dark_mode_hint_background_color', '#dark_mode_hint_background_color_pickr', '.altumcode-help-center-hint', 'background-color', 'dark', 'hint_background');
    init_color_pickr('#dark_mode_hint_color', '#dark_mode_hint_color_pickr', '.altumcode-help-center-hint', 'color', 'dark', 'hint');
    init_color_pickr('#dark_mode_title_color', '#dark_mode_title_color_pickr', '.altumcode-help-center-window-title', 'color', 'dark', 'title');
    init_color_pickr('#dark_mode_description_color', '#dark_mode_description_color_pickr', '.altumcode-help-center-window-description', 'color', 'dark', 'description');
    init_color_pickr('#dark_mode_item_question_color', '#dark_mode_item_question_color_pickr', '.altumcode-help-center-question', 'color', 'dark', 'item_question');
    init_color_pickr('#dark_mode_item_answer_color', '#dark_mode_item_answer_color_pickr', '.altumcode-help-center-answer', 'color', 'dark', 'item_answer');
    init_color_pickr('#dark_mode_item_background_color', '#dark_mode_item_background_color_pickr', '.altumcode-help-center-item', 'background-color', 'dark', 'item_background');
    init_color_pickr('#dark_mode_item_border_color', '#dark_mode_item_border_color_pickr', '.altumcode-help-center-item', 'border-color', 'dark', 'item_border');
    init_color_pickr('#dark_mode_background_color', '#dark_mode_background_color_pickr', '.altumcode-help-center-window', 'background-color', 'dark', 'background');
    init_color_pickr('#dark_mode_button_background_color', '#dark_mode_button_background_color_pickr', '.altumcode-help-center-button', 'background-color', 'dark', 'button_background');
    init_color_pickr('#dark_mode_button_color', '#dark_mode_button_color_pickr', '.altumcode-help-center-button', 'color', 'dark', 'button');
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
