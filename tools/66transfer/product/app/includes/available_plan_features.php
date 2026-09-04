<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 *  View all other existing AltumCode projects via https://altumcode.com/
 *  Get in touch for support or general queries via https://altumcode.com/contact
 *  Download the latest version via https://altumcode.com/downloads
 *
 *  X/Twitter: https://x.com/AltumCode
 *  Facebook: https://facebook.com/altumcode
 *  Instagram: https://instagram.com/altumcode
 */

defined('ALTUMCODE') || die();

$features = [];

/* Transfer-related limits */
$features = array_merge($features, [
    'storage_size_limit',
    'transfers_limit',
    'transfer_size_limit',
    'files_per_transfer_limit',
    'downloads_per_transfer_limit',
    'transfers_retention',
    'download_unlocking_time',
]);

/* Projects */
if(settings()->transfers->projects_is_enabled) {
    $features[] = 'projects_limit';
}

/* Pixels */
if(settings()->transfers->pixels_is_enabled) {
    $features[] = 'pixels_limit';
}

/* Domains */
if(settings()->transfers->domains_is_enabled) {
    $features[] = 'domains_limit';
}

/* Additional Domains */
if(settings()->transfers->additional_domains_is_enabled) {
    $features[] = 'additional_domains';
}

/* Plugin: Teams */
if(\Altum\Plugin::is_active('teams')) {
    $features[] = 'teams_limit';
}

/* Plugin: Affiliate */
if(\Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled) {
    $features[] = 'affiliate_commission_percentage';
}

/* Retention & Handlers */
$features[] = 'statistics_retention';
if(settings()->notification_handlers->is_enabled) {
    $features[] = 'notification_handlers_limit';
}

/* Transfer features */
$features[] = 'analytics_is_enabled';
$features[] = 'password_protection_is_enabled';
$features[] = 'file_encryption_is_enabled';
$features[] = 'custom_url_is_enabled';
$features[] = 'removable_branding_is_enabled';
$features[] = 'custom_css_is_enabled';
$features[] = 'custom_js_is_enabled';

/* Global settings */
if(settings()->transfers->transfer_requests_is_enabled) {
    $features[] = 'transfer_requests_is_enabled';
}

if(settings()->main->api_is_enabled) {
    $features[] = 'api_is_enabled';
}

if(settings()->main->white_labeling_is_enabled) {
    $features[] = 'white_labeling_is_enabled';
}

/* Export feature */
$features[] = sprintf(l('global.plan_settings.export'), '');

/* Ads */
$features[] = 'no_ads';

return $features;
