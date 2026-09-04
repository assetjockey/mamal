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

<div class="d-flex justify-content-between">
    <h3 class="h5"><?= l('notification.settings.engagement_links_categories') ?></h3>

    <div>
        <button type="button" id="category_create" class="btn btn-success btn-sm"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('global.create') ?></button>
    </div>
</div>
<div id="categories"></div>

<template id="template_category">
    <div class="category">
        <div class="form-group">
            <label><i class="fas fa-fw fa-sm fa-heading text-muted mr-1"></i> <?= l('notification.settings.title') ?></label>
            <input type="text" name="categories[category_index][title]" class="form-control" value="" maxlength="256" required="required" />
            <small class="form-text text-muted" data-toggle="tooltip" title="<?= l('notification.settings.html_info_tooltip') ?>"><?= l('notification.settings.html_info') ?></small>
        </div>

        <div class="form-group">
            <label><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('notification.settings.description') ?></label>
            <input type="text" name="categories[category_index][description]" class="form-control" value="" maxlength="512" />
            <small class="form-text text-muted" data-toggle="tooltip" title="<?= l('notification.settings.html_info_tooltip') ?>"><?= l('notification.settings.html_info') ?></small>
        </div>

        <div class="d-flex justify-content-between">
            <h3 class="h5"><?= l('notification.settings.engagement_links_categories_links') ?></h3>

            <div>
                <button type="button" id="category_link_create_category_index" class="btn btn-outline-success btn-sm"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('global.create') ?></button>
            </div>
        </div>

        <div class="category_links bg-gray-100 p-5 my-3 rounded"></div>

        <button type="button" class="category_delete btn btn-outline-danger btn-sm" title="<?= l('global.delete') ?>"><i class="fas fa-fw fa-times"></i> <?= l('global.delete') ?></button>

        <div class="my-4"></div>
    </div>
</template>

<template id="template_category_links">
    <div class="category_link">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label><i class="fas fa-fw fa-sm fa-heading text-muted mr-1"></i> <?= l('notification.settings.title') ?></label>
                    <input type="text" name="categories[category_index][links][category_link_index][title]" class="form-control" value="" maxlength="256" required="required" />
                    <small class="form-text text-muted" data-toggle="tooltip" title="<?= l('notification.settings.html_info_tooltip') ?>"><?= l('notification.settings.html_info') ?></small>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('notification.settings.description') ?></label>
                    <input type="text" name="categories[category_index][links][category_link_index][description]" class="form-control" value="" maxlength="512" />
                    <small class="form-text text-muted" data-toggle="tooltip" title="<?= l('notification.settings.html_info_tooltip') ?>"><?= l('notification.settings.html_info') ?></small>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('notification.settings.image') ?></label>
                    <input type="text" name="categories[category_index][links][category_link_index][image]" class="form-control" value="" maxlength="2048" placeholder="<?= l('global.url_placeholder') ?>" />
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('notification.settings.url') ?></label>
                    <input type="text" name="categories[category_index][links][category_link_index][url]" class="form-control" value="" maxlength="2048" placeholder="<?= l('global.url_placeholder') ?>" required="required" />
                </div>
            </div>
        </div>

        <?php /* Hidden link colors */ ?>
        <input type="hidden" name="categories[category_index][links][category_link_index][background_color]" value="" />
        <input type="hidden" name="categories[category_index][links][category_link_index][border_color]" value="" />

        <button type="button" class="category_link_delete btn btn-outline-danger btn-sm" title="<?= l('global.delete') ?>"><i class="fas fa-fw fa-times"></i> <?= l('global.delete') ?></button>

        <div class="my-4"></div>
    </div>
</template>
<?php $html['basic'] = ob_get_clean() ?>


<?php /* Customize Tab */ ?>
<?php ob_start() ?>
<div class="form-group">
    <label for="settings_title_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.title_color') ?></label>
    <input type="hidden" id="settings_title_color" name="title_color" class="form-control" value="<?= $data->notification->settings->title_color ?>" />
    <div id="settings_title_color_pickr"></div>
</div>

<h3 class="h5"><?= l('notification.settings.engagement_links_categories') ?></h3>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_categories_title_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.title_color') ?></label>
            <input type="hidden" id="settings_categories_title_color" name="categories_title_color" class="form-control" value="<?= $data->notification->settings->categories_title_color ?>" />
            <div id="settings_categories_title_color_pickr"></div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_categories_description_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.description_color') ?></label>
            <input type="hidden" id="settings_categories_description_color" name="categories_description_color" class="form-control" value="<?= $data->notification->settings->categories_description_color ?>" />
            <div id="settings_categories_description_color_pickr"></div>
        </div>
    </div>
</div>

<h3 class="h5"><?= l('notification.settings.engagement_links_categories_links') ?></h3>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_categories_links_title_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.title_color') ?></label>
            <input type="hidden" id="settings_categories_links_title_color" name="categories_links_title_color" class="form-control" value="<?= $data->notification->settings->categories_links_title_color ?>" />
            <div id="settings_categories_links_title_color_pickr"></div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_categories_links_description_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.description_color') ?></label>
            <input type="hidden" id="settings_categories_links_description_color" name="categories_links_description_color" class="form-control" value="<?= $data->notification->settings->categories_links_description_color ?>" />
            <div id="settings_categories_links_description_color_pickr"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_categories_links_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.background_color') ?></label>
            <input type="hidden" id="settings_categories_links_background_color" name="categories_links_background_color" class="form-control" value="<?= $data->notification->settings->categories_links_background_color ?>" />
            <div id="settings_categories_links_background_color_pickr"></div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="settings_categories_links_border_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.border_color') ?></label>
            <input type="hidden" id="settings_categories_links_border_color" name="categories_links_border_color" class="form-control" value="<?= $data->notification->settings->categories_links_border_color ?>" />
            <div id="settings_categories_links_border_color_pickr"></div>
        </div>
    </div>
</div>

<hr class="my-3">

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
        <label for="dark_mode_title_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.title_color') ?></label>
        <input type="hidden" id="dark_mode_title_color" name="dark_mode_title_color" class="form-control" value="<?= $data->notification->settings->dark_mode_title_color ?>" />
        <div id="dark_mode_title_color_pickr"></div>
    </div>

    <h3 class="h5"><?= l('notification.settings.engagement_links_categories') ?></h3>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_categories_title_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.title_color') ?></label>
                <input type="hidden" id="dark_mode_categories_title_color" name="dark_mode_categories_title_color" class="form-control" value="<?= $data->notification->settings->dark_mode_categories_title_color ?>" />
                <div id="dark_mode_categories_title_color_pickr"></div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_categories_description_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.description_color') ?></label>
                <input type="hidden" id="dark_mode_categories_description_color" name="dark_mode_categories_description_color" class="form-control" value="<?= $data->notification->settings->dark_mode_categories_description_color ?>" />
                <div id="dark_mode_categories_description_color_pickr"></div>
            </div>
        </div>
    </div>

    <h3 class="h5"><?= l('notification.settings.engagement_links_categories_links') ?></h3>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_categories_links_title_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.title_color') ?></label>
                <input type="hidden" id="dark_mode_categories_links_title_color" name="dark_mode_categories_links_title_color" class="form-control" value="<?= $data->notification->settings->dark_mode_categories_links_title_color ?>" />
                <div id="dark_mode_categories_links_title_color_pickr"></div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_categories_links_description_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.description_color') ?></label>
                <input type="hidden" id="dark_mode_categories_links_description_color" name="dark_mode_categories_links_description_color" class="form-control" value="<?= $data->notification->settings->dark_mode_categories_links_description_color ?>" />
                <div id="dark_mode_categories_links_description_color_pickr"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_categories_links_background_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.background_color') ?></label>
                <input type="hidden" id="dark_mode_categories_links_background_color" name="dark_mode_categories_links_background_color" class="form-control" value="<?= $data->notification->settings->dark_mode_categories_links_background_color ?>" />
                <div id="dark_mode_categories_links_background_color_pickr"></div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="dark_mode_categories_links_border_color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('notification.settings.border_color') ?></label>
                <input type="hidden" id="dark_mode_categories_links_border_color" name="dark_mode_categories_links_border_color" class="form-control" value="<?= $data->notification->settings->dark_mode_categories_links_border_color ?>" />
                <div id="dark_mode_categories_links_border_color_pickr"></div>
            </div>
        </div>
    </div>

    <hr class="my-3">

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

    /* Categories and categories links handlers */
    let categories = <?= json_encode($data->notification->settings->categories) ?>;
    let template_category = document.querySelector('#template_category');
    let template_category_links = document.querySelector('#template_category_links');

    /* Create new link */
    let category_link_create = (category_index = null, category_link_index = null, title = null, description = null, image = null, url = null, background_color = null, border_color = null) => {
        /* Prepare template */
        let category_link_template_clone = template_category_links.content.cloneNode(true);

        $(category_link_template_clone).find('.category_link').attr('data-category-link-index', category_link_index);
        $(category_link_template_clone).find('input[name="categories[category_index][links][category_link_index][title]"]').attr('name', `categories[${category_index}][links][${category_link_index}][title]`).val(title);
        $(category_link_template_clone).find('input[name="categories[category_index][links][category_link_index][description]"]').attr('name', `categories[${category_index}][links][${category_link_index}][description]`).val(description);
        $(category_link_template_clone).find('input[name="categories[category_index][links][category_link_index][image]"]').attr('name', `categories[${category_index}][links][${category_link_index}][image]`).val(image);
        $(category_link_template_clone).find('input[name="categories[category_index][links][category_link_index][url]"]').attr('name', `categories[${category_index}][links][${category_link_index}][url]`).val(url);
        $(category_link_template_clone).find('input[name="categories[category_index][links][category_link_index][background_color]"]').attr('name', `categories[${category_index}][links][${category_link_index}][background_color]`).val(background_color);
        $(category_link_template_clone).find('input[name="categories[category_index][links][category_link_index][border_color]"]').attr('name', `categories[${category_index}][links][${category_link_index}][border_color]`).val(border_color);

        return category_link_template_clone;
    };

    /* Create new category */
    let category_create = (category_index = null, title = null, description = null) => {
        /* Prepare template */
        let category_template_clone = template_category.content.cloneNode(true);

        $(category_template_clone).find('.category').attr('data-category-index', category_index);
        $(category_template_clone).find('input[name="categories[category_index][title]"]').attr('name', `categories[${category_index}][title]`).val(title);
        $(category_template_clone).find('input[name="categories[category_index][description]"]').attr('name', `categories[${category_index}][description]`).val(description);
        $(category_template_clone).find('button[id="category_link_create_category_index"]').attr('id', `category_link_create_${category_index}`);

        return category_template_clone;
    };

    if(categories) {
        for (let category_index = 0; category_index < categories.length; category_index++) {

            let category_template_clone = category_create(category_index, categories[category_index].title, categories[category_index].description);

            /* Go over the category links */
            for (let category_link_index = 0; category_link_index < categories[category_index]['links'].length; category_link_index++) {

                let category_link_template_clone = category_link_create(category_index, category_link_index, categories[category_index]['links'][category_link_index].title, categories[category_index]['links'][category_link_index].description, categories[category_index]['links'][category_link_index].image, categories[category_index]['links'][category_link_index].url, categories[category_index]['links'][category_link_index].background_color, categories[category_index]['links'][category_link_index].border_color);

                /* Append the link to the category links section */
                $(category_template_clone).find('.category_links').append(category_link_template_clone);

            }

            /* Append the category */
            $('#categories').append(category_template_clone);
        }
    }

    let initiate_handlers = () => {
        $('#category_create').off().on('click', () => {

            let category_index = $('#categories > .category').length;

            let new_category = category_create(category_index);
            let new_category_link = category_link_create(category_index, 0);

            /* Append category link to category */
            $(new_category).find('.category_links').append(new_category_link);

            /* Append the category */
            $('#categories').append(new_category);

            initiate_handlers();
        });

        $('button[id^="category_link_create_"]').off().on('click', event => {

            let category = $(event.currentTarget).closest('.category');
            let category_index = category.data('category-index');
            let category_link_index = category.find('.category_link').length;

            let new_category_link = category_link_create(category_index, category_link_index);

            /* Append category link to category */
            $(category).find('.category_links').append(new_category_link);

            initiate_handlers();
        });

        $('#categories > .category > .category_delete').off().on('click', event => {
            $(event.currentTarget).closest('.category').remove();

            initiate_handlers();
        });

        $('#categories > .category > .category_links > .category_link > .category_link_delete').off().on('click', event => {
            $(event.currentTarget).closest('.category_link').remove();

            initiate_handlers();
        });
    };

    initiate_handlers();

    $('#notification_preview .altumcode-hidden').removeClass('altumcode-hidden').addClass('altumcode-shown');

    /* Notification Preview Handlers */
    $('#settings_title').on('change paste keyup', event => {
        $('#notification_preview .altumcode-engagement-links-title').text($(event.currentTarget).val());
    });

    /* Light mode */
    init_color_pickr('#settings_title_color', '#settings_title_color_pickr', '.altumcode-engagement-links-title', 'color', 'light', 'title');
    init_color_pickr('#settings_categories_title_color', '#settings_categories_title_color_pickr', '.altumcode-engagement-links-category-title', 'color', 'light', 'categories_title');
    init_color_pickr('#settings_categories_description_color', '#settings_categories_description_color_pickr', '.altumcode-engagement-links-category-description', 'color', 'light', 'categories_description');
    init_color_pickr('#settings_categories_links_title_color', '#settings_categories_links_title_color_pickr', '.altumcode-engagement-links-category-link-title', 'color', 'light', 'categories_links_title');
    init_color_pickr('#settings_categories_links_description_color', '#settings_categories_links_description_color_pickr', '.altumcode-engagement-links-category-link-description', 'color', 'light', 'categories_links_description');
    init_color_pickr('#settings_categories_links_background_color', '#settings_categories_links_background_color_pickr', '.altumcode-engagement-links-category-link', 'background-color', 'light', 'categories_links_background');
    init_color_pickr('#settings_categories_links_border_color', '#settings_categories_links_border_color_pickr', '.altumcode-engagement-links-category-link', 'border-color', 'light', 'categories_links_border');
    init_color_pickr('#settings_background_color', '#settings_background_color_pickr', '.altumcode-wrapper', 'background-color', 'light', 'background');

    /* Dark mode */
    init_color_pickr('#dark_mode_title_color', '#dark_mode_title_color_pickr', '.altumcode-engagement-links-title', 'color', 'dark', 'title');
    init_color_pickr('#dark_mode_categories_title_color', '#dark_mode_categories_title_color_pickr', '.altumcode-engagement-links-category-title', 'color', 'dark', 'categories_title');
    init_color_pickr('#dark_mode_categories_description_color', '#dark_mode_categories_description_color_pickr', '.altumcode-engagement-links-category-description', 'color', 'dark', 'categories_description');
    init_color_pickr('#dark_mode_categories_links_title_color', '#dark_mode_categories_links_title_color_pickr', '.altumcode-engagement-links-category-link-title', 'color', 'dark', 'categories_links_title');
    init_color_pickr('#dark_mode_categories_links_description_color', '#dark_mode_categories_links_description_color_pickr', '.altumcode-engagement-links-category-link-description', 'color', 'dark', 'categories_links_description');
    init_color_pickr('#dark_mode_categories_links_background_color', '#dark_mode_categories_links_background_color_pickr', '.altumcode-engagement-links-category-link', 'background-color', 'dark', 'categories_links_background');
    init_color_pickr('#dark_mode_categories_links_border_color', '#dark_mode_categories_links_border_color_pickr', '.altumcode-engagement-links-category-link', 'border-color', 'dark', 'categories_links_border');
    init_color_pickr('#dark_mode_background_color', '#dark_mode_background_color_pickr', '.altumcode-wrapper', 'background-color', 'dark', 'background');

</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
