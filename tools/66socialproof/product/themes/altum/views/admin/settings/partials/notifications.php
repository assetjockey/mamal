<?php defined('ALTUMCODE') || die() ?>

<div id="notifications">
    <div class="form-group">
        <label for="branding"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('admin_settings.notifications.branding') ?></label>
        <textarea id="branding" name="branding" class="form-control"><?= settings()->notifications->branding ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.notifications.branding_help') ?></small>
        <small class="form-text text-muted"><?= sprintf(l('global.variables'), '<code data-copy>' . implode('</code> , <code data-copy>',  ['{{WEBSITE_TITLE}}', '{{URL}}', '{{AFFILIATE_URL_TAG}}']) . '</code>') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="analytics_is_enabled" name="analytics_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->notifications->analytics_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="analytics_is_enabled"><?= l('admin_settings.notifications.analytics_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.notifications.analytics_is_enabled_help') ?></small>
    </div>

    <div class="form-group">
        <label for="pixel_cache"><i class="fas fa-fw fa-sm fa-clock text-muted mr-1"></i> <?= l('admin_settings.notifications.pixel_cache') ?></label>
        <div class="input-group">
            <input id="pixel_cache" type="number" min="0" name="pixel_cache" class="form-control" value="<?= settings()->notifications->pixel_cache ?>" />
            <div class="input-group-append">
                <span class="input-group-text"><?= l('global.date.seconds') ?></span>
            </div>
        </div>
        <small class="form-text text-muted"><?= l('admin_settings.notifications.pixel_cache_help') ?></small>
    </div>

    <div class="form-group">
        <label for="email_reports_is_enabled"><i class="fas fa-fw fa-sm fa-fire text-muted mr-1"></i> <?= l('admin_settings.notifications.email_reports_is_enabled') ?></label>
        <select id="email_reports_is_enabled" name="email_reports_is_enabled" class="custom-select">
            <option value="0" <?= !settings()->notifications->email_reports_is_enabled ? 'selected="selected"' : null ?>><?= l('global.disabled') ?></option>
            <option value="weekly" <?= settings()->notifications->email_reports_is_enabled == 'weekly' ? 'selected="selected"' : null ?>><?= l('admin_settings.notifications.email_reports_is_enabled_weekly') ?></option>
            <option value="monthly" <?= settings()->notifications->email_reports_is_enabled == 'monthly' ? 'selected="selected"' : null ?>><?= l('admin_settings.notifications.email_reports_is_enabled_monthly') ?></option>
        </select>
        <small class="form-text text-muted"><?= l('admin_settings.notifications.email_reports_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="email_notices_is_enabled" name="email_notices_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->notifications->email_notices_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="email_notices_is_enabled"><?= l('admin_settings.notifications.email_notices_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.notifications.email_notices_is_enabled_help') ?></small>
    </div>

    <div class="form-group">
        <label for="blacklisted_domains"><i class="fas fa-fw fa-sm fa-ban text-muted mr-1"></i> <?= l('admin_settings.notifications.blacklisted_domains') ?></label>
        <textarea id="blacklisted_domains" class="form-control" name="blacklisted_domains"><?= implode(',', settings()->notifications->blacklisted_domains) ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.notifications.blacklisted_domains_help') ?></small>
    </div>

    <div class="form-group mt-5">
        <?php $notifications = ((array) (settings()->notifications->available_notifications ?? [])) + array_fill_keys(array_keys(require APP_PATH . 'includes/notifications.php'), true) ?>

        <div class="d-flex justify-content-between align-items-end mb-4">
            <h3 class="h5"><?= l('admin_settings.notifications.available_notifications') . ' (' . count($notifications) . ')' ?></h3>

            <div>
                <button type="button" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.select_all') ?>" data-tooltip-hide-on-click onclick="document.querySelectorAll(`[name^='available_notifications[']`).forEach(element => element.checked ? null : element.checked = true)"><i class="fas fa-fw fa-check-square"></i></button>
                <button type="button" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.deselect_all') ?>" data-tooltip-hide-on-click onclick="document.querySelectorAll(`[name^='available_notifications[']`).forEach(element => element.checked ? element.checked = false : null)"><i class="fas fa-fw fa-minus-square"></i></button>
            </div>
        </div>

        <div id="notifications_list">
            <?php $index = 0; ?>
            <?php /* Show all registered notifications */ ?>
            <?php foreach($notifications as $notification => $is_enabled): ?>
                <div class="d-flex">
                    <span class="cursor-grab drag mr-3" data-toggle="tooltip" title="<?= l('global.drag_and_drop') ?>">
                        <i class="fas fa-fw fa-sm fa-bars text-muted"></i>
                    </span>

                    <div class="form-group custom-control custom-checkbox" data-plan-feature>
                        <input id="<?= 'available_notifications_' . $notification ?>" name="available_notifications[<?= $index++ ?>]" value="<?= $notification ?>" type="checkbox" class="custom-control-input" <?= $is_enabled ? 'checked="checked"' : null ?>>
                        <label class="custom-control-label" for="<?= 'available_notifications_' . $notification ?>"><?= l('notification.' . mb_strtolower($notification) . '.name') ?></label>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>

    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#domains_container" aria-expanded="false" aria-controls="domains_container">
        <i class="fas fa-fw fa-globe fa-sm mr-1"></i> <?= l('admin_settings.notifications.domains') ?>
    </button>

    <div class="collapse" data-parent="#notifications" id="domains_container">
        <div class="form-group custom-control custom-switch">
            <input id="domains_is_enabled" name="domains_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->notifications->domains_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="domains_is_enabled"><?= l('admin_settings.notifications.domains_is_enabled') ?></label>
            <small class="form-text text-muted"><?= l('admin_settings.notifications.domains_is_enabled_help') ?></small>
        </div>

        <div class="form-group">
            <label for="domains_custom_main_ip"><i class="fas fa-fw fa-sm fa-server text-muted mr-1"></i> <?= l('admin_settings.notifications.domains_custom_main_ip') ?></label>
            <input id="domains_custom_main_ip" name="domains_custom_main_ip" type="text" class="form-control" value="<?= settings()->notifications->domains_custom_main_ip ?>" placeholder="<?= $_SERVER['SERVER_ADDR'] ?>">
            <small class="form-text text-muted"><?= l('admin_settings.notifications.domains_custom_main_ip_help') ?></small>
        </div>
    </div>

    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#file_size_limits_container" aria-expanded="false" aria-controls="file_size_limits_container">
        <i class="fas fa-fw fa-file fa-sm mr-1"></i> <?= l('admin_settings.notifications.file_size_limits') ?>
    </button>

    <div class="collapse" data-parent="#notifications" id="file_size_limits_container">
        <?php foreach(['image', 'audio'] as $key): ?>
            <div class="form-group">
                <label for="<?= $key . '_size_limit' ?>"><i class="fas fa-fw fa-sm fa-file text-muted mr-1"></i> <?= l('admin_settings.notifications.' . $key . '_size_limit') ?></label>
                <div class="input-group">
                    <input id="<?= $key . '_size_limit' ?>" type="number" min="0" max="<?= get_max_upload() ?>" step="any" name="<?= $key . '_size_limit' ?>" class="form-control" value="<?= settings()->notifications->{$key . '_size_limit'} ?>" />
                    <div class="input-group-append">
                        <span class="input-group-text"><?= l('global.mb') ?></span>
                    </div>
                </div>
                <small class="form-text text-muted"><?= l('global.accessibility.admin_file_size_limit_help') ?></small>
            </div>
        <?php endforeach ?>
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/sortable.js?v=' . PRODUCT_CODE ?>"></script>
<script>
    'use strict';

    let sortable = Sortable.create(document.getElementById('notifications_list'), {
        animation: 150,
        handle: '.drag',
        onUpdate: event => {

            /* Refresh tooltips */
            tooltips_initiate();

            document.querySelectorAll('#notifications_list > div').forEach((elm, i) => {
                let input = elm.querySelector('input[type="checkbox"]');
                if(input) {
                    input.setAttribute('name', `available_notifications[${i}]`);
                }
            });

        }
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
