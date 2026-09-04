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

$enabled_notifications = [];

$notifications = require APP_PATH . 'includes/notifications.php';

/* Enable newly registered notifications */
$available_notifications = (array) settings()->notifications->available_notifications + array_fill_keys(array_keys($notifications), true);

foreach($available_notifications as $notification_type => $is_enabled) {
    if($is_enabled) {
        $enabled_notifications[$notification_type] = $notifications[$notification_type];
    }
}

return $enabled_notifications;
