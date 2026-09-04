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

/* Plan-related features */
$features = array_merge($features, [
    'campaigns_limit',
    'notifications_limit',
    'notifications_impressions_limit',
]);

/* Notifications - Domains */
if(settings()->notifications->domains_is_enabled) {
    $features[] = 'domains_limit';
}

/* Plugin: Teams */
if(\Altum\Plugin::is_active('teams')) {
    $features[] = 'teams_limit';
}

/* Plugin: Affiliate */
if(\Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled) {
    $features[] = 'affiliate_commission_percentage';
}

/* Notification handlers */
if(settings()->notification_handlers->is_enabled) {
    $features[] = 'notification_handlers_limit';
}

/* Tracking retention */
$features[] = 'track_notifications_retention';
$features[] = 'track_conversions_retention';

/* Enabled notifications */
$features[] = 'enabled_notifications';

/* Email reports */
if(settings()->notifications->email_reports_is_enabled) {
    $features[] = 'email_reports_is_enabled';
}

/* Additional simple user plan settings */
$features[] = 'no_ads';
$features[] = 'removable_branding';
$features[] = 'custom_branding';
$features[] = 'custom_css_is_enabled';

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
