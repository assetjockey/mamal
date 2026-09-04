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

/* Plan-related features */
$features = array_merge($features, [
    'websites_limit',
    'sessions_events_limit',
    'sessions_events_retention',
    'events_children_limit',
    'events_children_retention',
    'websites_goals_limit',
]);

/* Chart annotations */
if(settings()->analytics->annotations_is_enabled) {
    $features[] = 'annotations_limit';
}

/* Dashboard views */
if(settings()->analytics->dashboard_views_is_enabled) {
    $features[] = 'dashboard_views_limit';
}

/* sessions replays */
if(settings()->analytics->sessions_replays_is_enabled) {
    $features[] = 'sessions_replays_limit';
}

$features[] = 'sessions_replays_retention';

/* Heatmaps */
if(settings()->analytics->websites_heatmaps_is_enabled) {
    $features[] = 'websites_heatmaps_limit';
}

/* Custom Domains */
if(settings()->analytics->domains_is_enabled) {
    $features[] = 'domains_limit';
}

/* Additional Domains */
if(settings()->analytics->additional_domains_is_enabled) {
	$features[] = 'additional_domains';
}

/* Plugin: Affiliate */
if(\Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled) {
    $features[] = 'affiliate_commission_percentage';
}

/* Email reports */
if(settings()->analytics->email_reports_is_enabled) {
    $features[] = 'email_reports_is_enabled';
}

/* Teams */
$features[] = 'teams_is_enabled';

/* Additional simple user plan settings */
$features[] = 'no_ads';

/* Global settings */
if(settings()->main->api_is_enabled) {
    $features[] = 'api_is_enabled';
}

if(settings()->main->white_labeling_is_enabled) {
    $features[] = 'white_labeling_is_enabled';
}

/* Export features */
$features[] = sprintf(l('global.plan_settings.export'), '');

return $features;
