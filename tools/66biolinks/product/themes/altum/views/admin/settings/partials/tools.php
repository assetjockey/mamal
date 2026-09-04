<?php defined('ALTUMCODE') || die() ?>

<div>
    <div class="form-group custom-control custom-switch">
        <input id="is_enabled" name="is_enabled" type="checkbox" class="custom-control-input" <?= settings()->tools->is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="is_enabled"><i class="fas fa-fw fa-sm fa-screwdriver-wrench text-muted mr-1"></i> <?= l('admin_settings.tools.is_enabled') ?></label>
    </div>

    <div class="form-group">
        <label for="access"><i class="fas fa-fw fa-sm fa-users text-muted mr-1"></i> <?= l('admin_settings.tools.access') ?></label>
        <select id="access" name="access" class="custom-select">
            <option value="everyone" <?= settings()->tools->access == 'everyone' ? 'selected="selected"' : null ?>><?= l('admin_settings.tools.access_everyone') ?></option>
            <option value="users" <?= settings()->tools->access == 'users' ? 'selected="selected"' : null ?>><?= l('admin_settings.tools.access_users') ?></option>
        </select>
    </div>

    <div class="form-group">
        <label for="style"><i class="fas fa-fw fa-sm fa-sliders-h text-muted mr-1"></i> <?= l('admin_settings.tools.style') ?></label>
        <select id="style" name="style" class="custom-select">
            <option value="frankfurt" <?= settings()->tools->style == 'frankfurt' ? 'selected="selected"' : null ?>><?= l('admin_settings.tools.style_frankfurt') ?></option>
            <option value="munich" <?= settings()->tools->style == 'munich' ? 'selected="selected"' : null ?>><?= l('admin_settings.tools.style_munich') ?></option>
        </select>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="categories_expanded_is_enabled" name="categories_expanded_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->tools->categories_expanded_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="categories_expanded_is_enabled"><i class="fas fa-fw fa-sm fa-paragraph text-muted mr-1"></i> <?= l('admin_settings.tools.categories_expanded_is_enabled') ?></label>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="extra_content_is_enabled" name="extra_content_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->tools->extra_content_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="extra_content_is_enabled"><i class="fas fa-fw fa-sm fa-paragraph text-muted mr-1"></i> <?= l('admin_settings.tools.extra_content_is_enabled') ?></label>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="share_is_enabled" name="share_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->tools->share_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="share_is_enabled"><i class="fas fa-fw fa-sm fa-share-alt text-muted mr-1"></i> <?= l('admin_settings.tools.share_is_enabled') ?></label>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="popular_widget_is_enabled" name="popular_widget_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->tools->popular_widget_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="popular_widget_is_enabled"><i class="fas fa-fw fa-sm fa-fire text-muted mr-1"></i> <?= l('admin_settings.tools.popular_widget_is_enabled') ?></label>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="similar_widget_is_enabled" name="similar_widget_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->tools->similar_widget_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="similar_widget_is_enabled"><i class="fas fa-fw fa-sm fa-clone text-muted mr-1"></i> <?= l('admin_settings.tools.similar_widget_is_enabled') ?></label>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="views_is_enabled" name="views_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->tools->views_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="views_is_enabled"><i class="fas fa-fw fa-sm fa-eye text-muted mr-1"></i> <?= l('admin_settings.tools.views_is_enabled') ?></label>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="submissions_is_enabled" name="submissions_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->tools->submissions_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="submissions_is_enabled"><i class="fas fa-fw fa-sm fa-check text-muted mr-1"></i> <?= l('admin_settings.tools.submissions_is_enabled') ?></label>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="ratings_is_enabled" name="ratings_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->tools->ratings_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="ratings_is_enabled"><i class="fas fa-fw fa-sm fa-star text-muted mr-1"></i> <?= l('admin_settings.tools.ratings_is_enabled') ?></label>
    </div>

    <div class="form-group mt-5">
		<?php $tools = require APP_PATH . 'includes/tools/tools.php' ?>

        <div class="d-flex justify-content-between align-items-end mb-4">
            <h3 class="h5"><?= l('admin_settings.tools.available_tools') . ' (' . count($tools) . ')' ?></h3>

            <div>
                <button type="button" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.select_all') ?>" data-tooltip-hide-on-click onclick="document.querySelectorAll(`[name='available_tools[]']`).forEach(element => element.checked ? null : element.checked = true)"><i class="fas fa-fw fa-check-square"></i></button>
                <button type="button" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.deselect_all') ?>" data-tooltip-hide-on-click onclick="document.querySelectorAll(`[name='available_tools[]']`).forEach(element => element.checked ? element.checked = false : null)"><i class="fas fa-fw fa-minus-square"></i></button>
            </div>
        </div>

        <div id="available_tools">
			<?php $index = 0; ?>
			<?php foreach(require APP_PATH . 'includes/tools/categories.php' as $tool_category => $tool_category_properties): ?>

				<?php /* Load only this category tools */ ?>
				<?php $category_tools = require APP_PATH . 'includes/tools/' . $tool_category . '.php' ?>
				<?php $container_id = 'available_tools_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $tool_category) . '_container' ?>

                <div data-tool-category>
                    <input type="hidden" name="categories_order[<?= $index++ ?>]" value="<?= $tool_category ?>" />

                    <div class="d-flex align-items-center">
                        <span class="cursor-grab drag mr-3 mb-4" data-toggle="tooltip" title="<?= l('global.drag_and_drop') ?>">
                            <i class="fas fa-fw fa-sm fa-bars text-muted"></i>
                        </span>

                        <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#<?= $container_id ?>" aria-expanded="false" aria-controls="<?= $container_id ?>">
                            <i class="<?= $tool_category_properties['icon'] ?> fa-fw fa-sm mr-1"></i> <?= l('tools.' . $tool_category) . ' (' . count($category_tools) . ')' ?>
                        </button>
                    </div>

                    <div class="collapse" data-parent="#available_tools" id="<?= $container_id ?>">
                        <div class="mb-4">
                            <div class="d-flex justify-content-end align-items-center mb-2">
                                <button type="button" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.select_all') ?>" data-tooltip-hide-on-click onclick="document.querySelectorAll(`[data-tool-category='<?= $tool_category ?>']`).forEach(element => element.checked ? null : element.checked = true)"><i class="fas fa-fw fa-check-square"></i></button>
                                <button type="button" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.deselect_all') ?>" data-tooltip-hide-on-click onclick="document.querySelectorAll(`[data-tool-category='<?= $tool_category ?>']`).forEach(element => element.checked ? element.checked = false : null)"><i class="fas fa-fw fa-minus-square"></i></button>
                            </div>

                            <div class="row">
								<?php foreach($category_tools as $key => $value): ?>
									<?php
									/* Determine the tool name / description */
									if(isset($value['category']) && in_array($value['category'],['color_converter_tools', 'data_converter_tools', 'length_converter_tools', 'image_manipulation_tools', 'weight_converter_tools', 'volume_converter_tools', 'time_converter_tools', 'area_converter_tools', 'force_converter_tools', 'pressure_converter_tools', 'energy_converter_tools', 'power_converter_tools', 'speed_converter_tools', 'frequency_converter_tools', 'angle_converter_tools', 'torque_converter_tools', 'fuel_economy_converter_tools',])) {
										/* Process the tool */
										$exploded = explode('_to_', $key);
										$from = $exploded[0];
										$to = $exploded[1];

										$name = sprintf(l('tools.' . $value['category'] . '.name'), l('tools.' . $from), l('tools.' . $to));
									} else {
										$name = l('tools.' . $key . '.name');
									}
									?>

                                    <div class="col-12 col-lg-6">
                                        <div class="custom-control custom-checkbox my-2">
                                            <input id="<?= 'tool_' . $key ?>" name="available_tools[]" value="<?= $key ?>" type="checkbox" class="custom-control-input" <?= settings()->tools->available_tools->{$key} ? 'checked="checked"' : null ?> data-tool-category="<?= $tool_category ?>">
                                            <label class="custom-control-label d-flex align-items-center" for="<?= 'tool_' . $key ?>">
												<?= $name ?>
                                            </label>
                                        </div>
                                    </div>
								<?php endforeach ?>
                            </div>
                        </div>
                    </div>
                </div>
			<?php endforeach ?>
        </div>
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/sortable.js?v=' . PRODUCT_CODE ?>"></script>
<script>
    'use strict';

    let available_tools_sortable = Sortable.create(document.getElementById('available_tools'), {
        animation: 150,
        handle: '.drag',
        onUpdate: event => {

            /* Refresh tooltips */
            tooltips_initiate();

            document.querySelectorAll('#available_tools > [data-tool-category]').forEach((elm, i) => {
                let input = elm.querySelector('input[type="hidden"]');

                if(input) {
                    input.setAttribute('name', `categories_order[${i}]`);
                }
            });

        }
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
