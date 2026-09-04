<?php defined('ALTUMCODE') || die() ?>

<div>
    <div class="form-group custom-control custom-switch">
        <input id="status_pages_is_enabled" name="status_pages_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->status_pages->status_pages_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="status_pages_is_enabled"><?= l('admin_settings.status_pages.status_pages_is_enabled') ?></label>
    </div>

    <div class="form-group">
        <label for="random_url_length"><?= l('admin_settings.status_pages.random_url_length') ?></label>
        <input id="random_url_length" type="number" min="4" step="1" name="random_url_length" class="form-control" value="<?= settings()->status_pages->random_url_length ?? 7 ?>" />
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.random_url_length_help') ?></small>
    </div>

    <div class="form-group">
        <label for="example_url"><?= l('admin_settings.status_pages.example_url') ?></label>
        <input id="example_url" type="url" name="example_url" class="form-control" placeholder="<?= l('global.url_placeholder') ?>" value="<?= settings()->status_pages->example_url ?>" />
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.example_url_help') ?></small>
    </div>

    <div class="form-group">
        <label for="branding"><?= l('admin_settings.status_pages.branding') ?></label>
        <textarea id="branding" name="branding" class="form-control"><?= settings()->status_pages->branding ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.branding_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="report_is_enabled" name="report_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->status_pages->report_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="report_is_enabled"><?= l('admin_settings.status_pages.report_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.report_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="domains_is_enabled" name="domains_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->status_pages->domains_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="domains_is_enabled"><?= l('admin_settings.status_pages.domains_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.domains_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="additional_domains_is_enabled" name="additional_domains_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->status_pages->additional_domains_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="additional_domains_is_enabled"><?= l('admin_settings.status_pages.additional_domains_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.additional_domains_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="main_domain_is_enabled" name="main_domain_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->status_pages->main_domain_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="main_domain_is_enabled"><?= l('admin_settings.status_pages.main_domain_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.main_domain_is_enabled_help') ?></small>
    </div>

    <div class="form-group">
        <label for="domains_custom_main_ip"><?= l('admin_settings.status_pages.domains_custom_main_ip') ?></label>
        <input id="domains_custom_main_ip" name="domains_custom_main_ip" type="text" class="form-control" value="<?= settings()->status_pages->domains_custom_main_ip ?>" placeholder="<?= $_SERVER['SERVER_ADDR'] ?>">
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.domains_custom_main_ip_help') ?></small>
    </div>

    <div class="form-group">
        <label for="blacklisted_domains"><?= l('admin_settings.status_pages.blacklisted_domains') ?></label>
        <textarea id="blacklisted_domains" class="form-control" name="blacklisted_domains"><?= implode(',', settings()->status_pages->blacklisted_domains) ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.blacklisted_domains_help') ?></small>
    </div>

    <div class="form-group">
        <label for="blacklisted_keywords"><?= l('admin_settings.status_pages.blacklisted_keywords') ?></label>
        <textarea id="blacklisted_keywords" class="form-control" name="blacklisted_keywords"><?= implode(',', settings()->status_pages->blacklisted_keywords) ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.blacklisted_keywords_help') ?></small>
    </div>

    <?php foreach(['logo', 'favicon', 'opengraph', 'pwa_icon'] as $key): ?>
        <div class="form-group">
            <label for="<?= $key . '_size_limit' ?>"><?= l('admin_settings.status_pages.' . $key . '_size_limit') ?></label>
            <div class="input-group">
                <input id="<?= $key . '_size_limit' ?>" type="number" min="0" max="<?= get_max_upload() ?>" step="any" name="<?= $key . '_size_limit' ?>" class="form-control" value="<?= settings()->status_pages->{$key . '_size_limit'} ?>" />
                <div class="input-group-append">
                    <span class="input-group-text"><?= l('global.mb') ?></span>
                </div>
            </div>
            <small class="form-text text-muted"><?= l('global.accessibility.admin_file_size_limit_help') ?></small>
        </div>
    <?php endforeach ?>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

