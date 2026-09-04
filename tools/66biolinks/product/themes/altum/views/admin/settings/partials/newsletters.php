<?php defined('ALTUMCODE') || die() ?>

<div>
    <div <?= !\Altum\Plugin::is_active('newsletters') ? 'data-toggle="tooltip" title="' . sprintf(l('admin_plugins.no_access'), \Altum\Plugin::get('newsletters')->name ?? 'newsletters') . '"' : null ?>>
        <div class="<?= !\Altum\Plugin::is_active('newsletters') ? 'container-disabled' : null ?>">

            <div class="form-group custom-control custom-switch">
                <input id="is_enabled" name="is_enabled" type="checkbox" class="custom-control-input" <?= settings()->newsletters->is_enabled ? 'checked="checked"' : null?>>
                <label class="custom-control-label" for="is_enabled"><?= l('admin_settings.newsletters.is_enabled') ?></label>
                <small class="form-text text-muted"><?= l('admin_settings.newsletters.is_enabled_help') ?></small>
            </div>

            <div class="form-group custom-control custom-switch">
                <input id="statistics_is_enabled" name="statistics_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->newsletters->statistics_is_enabled ? 'checked="checked"' : null?>>
                <label class="custom-control-label" for="statistics_is_enabled"><?= l('admin_settings.newsletters.statistics_is_enabled') ?></label>
                <small class="form-text text-muted"><?= l('admin_settings.newsletters.statistics_is_enabled_help') ?></small>
            </div>

            <div class="form-group">
                <label for="emails_per_cron"><i class="fas fa-fw fa-sm fa-envelope text-muted mr-1"></i> <?= l('admin_settings.newsletters.emails_per_cron') ?></label>
                <input id="emails_per_cron" type="number" min="1" step="1" name="emails_per_cron" class="form-control" value="<?= settings()->newsletters->emails_per_cron ?? 40 ?>" />
                <small class="form-text text-muted"><?= l('admin_settings.newsletters.emails_per_cron_help') ?></small>
            </div>

            <div class="form-group custom-control custom-switch">
                <input id="custom_smtp_is_enabled" name="custom_smtp_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->newsletters->custom_smtp_is_enabled ? 'checked="checked"' : null?>>
                <label class="custom-control-label" for="custom_smtp_is_enabled"><?= l('admin_settings.newsletters.custom_smtp_is_enabled') ?></label>
                <small class="form-text text-muted"><?= l('admin_settings.newsletters.custom_smtp_is_enabled_help') ?></small>
            </div>
        </div>
    </div>
</div>

<?php if(\Altum\Plugin::is_active('newsletters')): ?>
    <button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
<?php endif ?>
