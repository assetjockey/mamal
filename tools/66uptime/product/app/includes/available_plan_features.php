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

/* Monitors & Heartbeats */
if(settings()->monitors_heartbeats->monitors_is_enabled) {
    $features[] = 'monitors_limit';
    $features[] = 'monitors_ping_servers';
    $features[] = 'monitors_check_intervals';
}

if(settings()->monitors_heartbeats->heartbeats_is_enabled) {
    $features[] = 'heartbeats_limit';
}

if(settings()->monitors_heartbeats->domain_names_is_enabled) {
    $features[] = 'domain_names_limit';
}

if(settings()->monitors_heartbeats->dns_monitors_is_enabled) {
    $features[] = 'dns_monitors_limit';
    $features[] = 'dns_monitors_check_intervals';
}

if(settings()->monitors_heartbeats->game_servers_is_enabled) {
	$features[] = 'game_servers_limit';
	$features[] = 'game_servers_check_intervals';
}

if(settings()->monitors_heartbeats->server_monitors_is_enabled) {
    $features[] = 'server_monitors_limit';
    $features[] = 'server_monitors_check_intervals';
}

/* Status Pages */
if(settings()->status_pages->status_pages_is_enabled) {
    $features[] = 'status_pages_limit';
    $features[] = 'analytics_is_enabled';
    $features[] = 'qr_is_enabled';
    $features[] = 'password_protection_is_enabled';
    $features[] = 'removable_branding_is_enabled';
    $features[] = 'custom_url_is_enabled';
    $features[] = 'search_engine_visibility_is_enabled';
    $features[] = 'custom_css_is_enabled';
    $features[] = 'custom_js_is_enabled';
}

/* Plugin: PWA */
if(\Altum\Plugin::is_active('pwa') && settings()->pwa->is_enabled) {
    $features[] = 'custom_pwa_is_enabled';
}

/* Links & Projects */
if(settings()->monitors_heartbeats->projects_is_enabled) {
    $features[] = 'projects_limit';
}

/* Domains */
if(settings()->status_pages->domains_is_enabled) {
    $features[] = 'domains_limit';
}

if(settings()->status_pages->additional_domains_is_enabled) {
    $features[] = 'additional_domains';
}

/* Plugin: Teams */
if(\Altum\Plugin::is_active('teams')) {
    $features[] = 'teams_limit';
}

/* Plugin: Affiliate */
if(settings()->affiliate->is_enabled) {
    $features[] = 'affiliate_commission_percentage';
}

/* Retention */
$features[] = 'logs_retention';
$features[] = 'statistics_retention';

/* Notification Handlers */
if(settings()->notification_handlers->is_enabled) {
    $features[] = 'notification_handlers_limit';
}

/* Email Reports */
if(settings()->monitors_heartbeats->email_reports_is_enabled) {
    $features[] = 'email_reports_is_enabled';
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

/* Miscellaneous */
$features[] = 'no_ads';

return $features;
