<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
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

/* Codes - AI QR Codes */
if(settings()->codes->ai_qr_codes_is_enabled) {
    $features[] = 'ai_qr_codes_per_month_limit';
}

/* Codes - QR Codes */
if(settings()->codes->qr_codes_is_enabled) {
    $features[] = 'enabled_qr_codes';
    $features[] = 'qr_codes_limit';
    $features[] = 'qr_codes_bulk_limit';
}

/* Codes - Barcodes */
if(settings()->codes->barcodes_is_enabled) {
    $features[] = 'enabled_barcodes';
    $features[] = 'barcodes_limit';
    $features[] = 'barcodes_bulk_limit';
}

/* Links */
$features[] = 'links_limit';
$features[] = 'links_bulk_limit';

/* Projects */
if(settings()->links->projects_is_enabled) {
    $features[] = 'projects_limit';
}

/* Pixels */
if(settings()->links->pixels_is_enabled) {
    $features[] = 'pixels_limit';
}

/* Domains */
if(settings()->links->domains_is_enabled) {
    $features[] = 'domains_limit';
}

/* Additional Domains */
if(settings()->links->additional_domains_is_enabled) {
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

/* Statistics */
$features[] = 'statistics_retention';

/* Link Features */
$features[] = 'analytics_is_enabled';
if(settings()->links->email_reports_is_enabled) {
    $features[] = 'email_reports_is_enabled';
}
$features[] = 'password_protection_is_enabled';
$features[] = 'sensitive_content_is_enabled';
$features[] = 'cloaking_is_enabled';
$features[] = 'app_linking_is_enabled';
$features[] = 'targeting_is_enabled';
$features[] = 'custom_url_is_enabled';
$features[] = 'utm_parameters_is_enabled';
$features[] = 'search_engine_visibility_is_enabled';

/* Codes Readers */
if(settings()->codes->qr_reader_is_enabled) {
    $features[] = 'qr_reader_is_enabled';
}

if(settings()->codes->barcode_reader_is_enabled) {
    $features[] = 'barcode_reader_is_enabled';
}

/* Global Settings */
if(settings()->main->api_is_enabled) {
    $features[] = 'api_is_enabled';
}

if(settings()->main->white_labeling_is_enabled) {
    $features[] = 'white_labeling_is_enabled';
}

/* Export */
$features[] = sprintf(l('global.plan_settings.export'), '');

/* Additional Simple User Plan Settings */
$features[] = 'no_ads';
$features[] = 'removable_branding';

return $features;
