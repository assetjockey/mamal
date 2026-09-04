<?php defined('ALTUMCODE') || die() ?>

<div>
    <div class="form-group">
        <label for="random_url_length"><?= l('admin_settings.transfers.random_url_length') ?></label>
        <input id="random_url_length" type="number" min="4" step="1" name="random_url_length" class="form-control" value="<?= settings()->transfers->random_url_length ?? 7 ?>" />
        <small class="form-text text-muted"><?= l('admin_settings.transfers.random_url_length_help') ?></small>
    </div>

    <div class="form-group">
        <label for="branding"><?= l('admin_settings.transfers.branding') ?></label>
        <textarea id="branding" name="branding" class="form-control"><?= settings()->transfers->branding ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.transfers.branding_help') ?></small>
        <small class="form-text text-muted"><?= sprintf(l('global.variables'), '<code data-copy>' . implode('</code> , <code data-copy>',  ['{{WEBSITE_TITLE}}', '{{URL}}', '{{AFFILIATE_URL_TAG}}']) . '</code>') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="transfer_requests_is_enabled" name="transfer_requests_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->transfers->transfer_requests_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="transfer_requests_is_enabled"><?= l('admin_settings.transfers.transfer_requests_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.transfers.transfer_requests_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="email_transfer_is_enabled" name="email_transfer_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->transfers->email_transfer_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="email_transfer_is_enabled"><?= l('admin_settings.transfers.email_transfer_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.transfers.email_transfer_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="report_is_enabled" name="report_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->transfers->report_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="report_is_enabled"><?= l('admin_settings.transfers.report_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.transfers.report_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="parallel_file_uploading" name="parallel_file_uploading" type="checkbox" class="custom-control-input" <?= settings()->transfers->parallel_file_uploading ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="parallel_file_uploading"><?= l('admin_settings.transfers.parallel_file_uploading') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.transfers.parallel_file_uploading_help') ?></small>
    </div>

    <div <?= !\Altum\Plugin::is_active('offload') ? 'data-toggle="tooltip" title="' . sprintf(l('admin_plugins.no_access'), \Altum\Plugin::get('offload')->name ?? 'offload') . '"' : null ?>>
        <div class="<?= !\Altum\Plugin::is_active('offload') ? 'container-disabled' : null ?>">
            <div class="form-group custom-control custom-switch">
                <input id="is_direct_offload_upload" name="is_direct_offload_upload" type="checkbox" class="custom-control-input" <?= settings()->transfers->is_direct_offload_upload ? 'checked="checked"' : null?> <?= !\Altum\Plugin::is_active('offload') ? 'disabled="disabled"' : null ?>>
                <label class="custom-control-label" for="is_direct_offload_upload"><?= l('admin_settings.transfers.is_direct_offload_upload') ?></label>
                <small class="form-text text-muted"><?= l('admin_settings.transfers.is_direct_offload_upload_help') ?></small>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="blacklisted_file_extensions"><?= l('admin_settings.transfers.blacklisted_file_extensions') ?></label>
        <textarea id="blacklisted_file_extensions" class="form-control" name="blacklisted_file_extensions"><?= settings()->transfers->blacklisted_file_extensions ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.transfers.blacklisted_file_extensions_help') ?></small>
    </div>

    <div class="form-group">
        <label for="preview_file_extensions"><?= l('admin_settings.transfers.preview_file_extensions') ?></label>
        <textarea id="preview_file_extensions" class="form-control" name="preview_file_extensions"><?= settings()->transfers->preview_file_extensions ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.transfers.preview_file_extensions_help') ?></small>
    </div>

    <div class="form-group">
        <label for="chunk_size_limit"><?= l('admin_settings.transfers.chunk_size_limit') ?></label>
        <div class="input-group">
            <input id="chunk_size_limit" type="number" min="0" max="<?= get_max_upload() ?>" step="any" name="chunk_size_limit" class="form-control" value="<?= settings()->transfers->chunk_size_limit ?>" />
            <div class="input-group-append">
                <span class="input-group-text"><?= l('global.mb') ?></span>
            </div>
        </div>
        <small class="form-text text-muted"><?= l('admin_settings.transfers.chunk_size_limit_help') ?></small>
    </div>

    <div <?= !\Altum\Plugin::is_active('affiliate') ? 'data-toggle="tooltip" title="' . sprintf(l('admin_plugins.no_access'), \Altum\Plugin::get('affiliate')->name ?? 'affiliate') . '"' : null ?>>
        <div class="<?= !in_array(settings()->license->type, ['Extended License', 'extended']) || !\Altum\Plugin::is_active('affiliate') ? 'container-disabled' : null ?>">
            <div class="form-group">
                <label for="offload_chunk_size_limit"><?= l('admin_settings.transfers.offload_chunk_size_limit') ?></label>
                <div class="input-group">
                    <input id="offload_chunk_size_limit" type="number" min="5" max="<?= 5000 ?>" step="1" name="offload_chunk_size_limit" class="form-control" value="<?= settings()->transfers->offload_chunk_size_limit ?? 10 ?>" />
                    <div class="input-group-append">
                        <span class="input-group-text"><?= l('global.mb') ?></span>
                    </div>
                </div>
                <small class="form-text text-muted"><?= l('admin_settings.transfers.offload_chunk_size_limit_help') ?></small>
            </div>
        </div>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="projects_is_enabled" name="projects_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->transfers->projects_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="projects_is_enabled"><?= l('admin_settings.transfers.projects_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.transfers.projects_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="pixels_is_enabled" name="pixels_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->transfers->pixels_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="pixels_is_enabled"><?= l('admin_settings.transfers.pixels_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.transfers.pixels_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="domains_is_enabled" name="domains_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->transfers->domains_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="domains_is_enabled"><?= l('admin_settings.transfers.domains_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.transfers.domains_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="additional_domains_is_enabled" name="additional_domains_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->transfers->additional_domains_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="additional_domains_is_enabled"><?= l('admin_settings.transfers.additional_domains_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.transfers.additional_domains_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="main_domain_is_enabled" name="main_domain_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->transfers->main_domain_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="main_domain_is_enabled"><?= l('admin_settings.transfers.main_domain_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.transfers.main_domain_is_enabled_help') ?></small>
    </div>

    <div class="form-group">
        <label for="domains_custom_main_ip"><?= l('admin_settings.transfers.domains_custom_main_ip') ?></label>
        <input id="domains_custom_main_ip" name="domains_custom_main_ip" type="text" class="form-control" value="<?= settings()->transfers->domains_custom_main_ip ?>" placeholder="<?= $_SERVER['SERVER_ADDR'] ?>">
        <small class="form-text text-muted"><?= l('admin_settings.transfers.domains_custom_main_ip_help') ?></small>
    </div>

    <div class="form-group">
        <label for="blacklisted_domains"><?= l('admin_settings.transfers.blacklisted_domains') ?></label>
        <textarea id="blacklisted_domains" class="form-control" name="blacklisted_domains"><?= implode(',', settings()->transfers->blacklisted_domains) ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.transfers.blacklisted_domains_help') ?></small>
    </div>

    <div class="form-group">
        <label for="blacklisted_keywords"><?= l('admin_settings.transfers.blacklisted_keywords') ?></label>
        <textarea id="blacklisted_keywords" class="form-control" name="blacklisted_keywords"><?= implode(',', settings()->transfers->blacklisted_keywords) ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.transfers.blacklisted_keywords_help') ?></small>
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>
