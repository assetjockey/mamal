<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

defined('ALTUMCODE') || die();

$features = [
    'no_ads',
    'removable_branding',
    'custom_branding',
    'custom_css_is_enabled',
];


if(settings()->main->api_is_enabled) {
    $features = array_merge($features, [
        'api_is_enabled',
    ]);
}

if(settings()->main->white_labeling_is_enabled) {
    $features = array_merge($features, [
        'white_labeling_is_enabled',
    ]);
}

return $features;

