<?php defined('ALTUMCODE') || die() ?>

<div>
    <div class="form-group custom-control custom-switch">
        <input id="ip_storage_is_enabled" name="ip_storage_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->analytics->ip_storage_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="ip_storage_is_enabled"><?= l('admin_settings.analytics.ip_storage_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.analytics.ip_storage_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="annotations_is_enabled" name="annotations_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->analytics->annotations_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="annotations_is_enabled"><?= l('admin_settings.analytics.annotations_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.analytics.annotations_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="dashboard_views_is_enabled" name="dashboard_views_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->analytics->dashboard_views_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="dashboard_views_is_enabled"><?= l('admin_settings.analytics.dashboard_views_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.analytics.dashboard_views_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="sessions_replays_is_enabled" name="sessions_replays_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->analytics->sessions_replays_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="sessions_replays_is_enabled"><?= l('admin_settings.analytics.sessions_replays_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.analytics.sessions_replays_is_enabled_help') ?></small>
        <small class="form-text text-muted"><?= l('admin_settings.analytics.sessions_replays_is_enabled_help2') ?></small>
        <small class="form-text text-muted"><?= sprintf(l('admin_settings.analytics.sessions_replays_is_enabled_help3'), ini_get('post_max_size')) ?></small>
    </div>

    <div class="form-group">
        <label for="sessions_replays_minimum_duration"><i class="fas fa-fw fa-sm fa-clock text-muted mr-1"></i> <?= l('admin_settings.analytics.sessions_replays_minimum_duration') ?></label>
        <input id="sessions_replays_minimum_duration" type="number" min="1" name="sessions_replays_minimum_duration" class="form-control" value="<?= settings()->analytics->sessions_replays_minimum_duration ?>" />
        <small class="form-text text-muted"><?= l('admin_settings.analytics.sessions_replays_minimum_duration_help') ?></small>
        <small class="form-text text-muted"><?= l('admin_settings.analytics.sessions_replays_minimum_duration_help2') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="websites_heatmaps_is_enabled" name="websites_heatmaps_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->analytics->websites_heatmaps_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="websites_heatmaps_is_enabled"><?= l('admin_settings.analytics.websites_heatmaps_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.analytics.websites_heatmaps_is_enabled_help') ?></small>
    </div>

    <div class="form-group">
        <label for="pixel_cache"><i class="fas fa-fw fa-sm fa-clock text-muted mr-1"></i> <?= l('admin_settings.analytics.pixel_cache') ?></label>
        <div class="input-group">
            <input id="pixel_cache" type="number" min="0" name="pixel_cache" class="form-control" value="<?= settings()->analytics->pixel_cache ?>" />
            <div class="input-group-append">
                <span class="input-group-text"><?= l('global.date.seconds') ?></span>
            </div>
        </div>
        <small class="form-text text-muted"><?= l('admin_settings.analytics.pixel_cache_help') ?></small>
    </div>

    <div class="form-group">
        <label for="pixel_exposed_identifier"><i class="fas fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('admin_settings.analytics.pixel_exposed_identifier') ?></label>
        <input id="pixel_exposed_identifier" type="text" name="pixel_exposed_identifier" class="form-control" value="<?= settings()->analytics->pixel_exposed_identifier ?>" />
        <small class="form-text text-muted"><?= l('admin_settings.analytics.pixel_exposed_identifier_help') ?></small>
    </div>

    <div class="form-group">
        <label for="email_reports_is_enabled"><i class="fas fa-fw fa-sm fa-fire text-muted mr-1"></i> <?= l('admin_settings.analytics.email_reports_is_enabled') ?></label>
        <select id="email_reports_is_enabled" name="email_reports_is_enabled" class="custom-select">
            <option value="0" <?= !settings()->analytics->email_reports_is_enabled ? 'selected="selected"' : null ?>><?= l('global.disabled') ?></option>
            <option value="weekly" <?= settings()->analytics->email_reports_is_enabled == 'weekly' ? 'selected="selected"' : null ?>><?= l('admin_settings.analytics.email_reports_is_enabled_weekly') ?></option>
            <option value="monthly" <?= settings()->analytics->email_reports_is_enabled == 'monthly' ? 'selected="selected"' : null ?>><?= l('admin_settings.analytics.email_reports_is_enabled_monthly') ?></option>
        </select>
        <small class="form-text text-muted"><?= l('admin_settings.analytics.email_reports_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="email_notices_is_enabled" name="email_notices_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->analytics->email_notices_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="email_notices_is_enabled"><?= l('admin_settings.analytics.email_notices_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.analytics.email_notices_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="domains_is_enabled" name="domains_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->analytics->domains_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="domains_is_enabled"><?= l('admin_settings.analytics.domains_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.analytics.domains_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="additional_domains_is_enabled" name="additional_domains_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->analytics->additional_domains_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="additional_domains_is_enabled"><?= l('admin_settings.analytics.additional_domains_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.analytics.additional_domains_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="main_domain_is_enabled" name="main_domain_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->analytics->main_domain_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="main_domain_is_enabled"><?= l('admin_settings.analytics.main_domain_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.analytics.main_domain_is_enabled_help') ?></small>
    </div>

    <div class="form-group">
        <label for="domains_custom_main_ip"><i class="fas fa-fw fa-sm fa-server text-muted mr-1"></i> <?= l('admin_settings.analytics.domains_custom_main_ip') ?></label>
        <input id="domains_custom_main_ip" name="domains_custom_main_ip" type="text" class="form-control" value="<?= settings()->analytics->domains_custom_main_ip ?>" placeholder="<?= $_SERVER['SERVER_ADDR'] ?>">
        <small class="form-text text-muted"><?= l('admin_settings.analytics.domains_custom_main_ip_help') ?></small>
    </div>

    <div class="form-group">
        <label for="blacklisted_domains"><i class="fas fa-fw fa-sm fa-ban text-muted mr-1"></i> <?= l('admin_settings.analytics.blacklisted_domains') ?></label>
        <textarea id="blacklisted_domains" class="form-control" name="blacklisted_domains"><?= implode(',', settings()->analytics->blacklisted_domains) ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.analytics.blacklisted_domains_help') ?></small>
    </div>

    <div class="form-group">
        <label for="example_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('admin_settings.analytics.example_url') ?></label>
        <input id="example_url" type="url" name="example_url" class="form-control" placeholder="<?= l('global.url_placeholder') ?>" value="<?= settings()->analytics->example_url ?>" />
        <small class="form-text text-muted"><?= l('admin_settings.analytics.example_url_help') ?></small>
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
