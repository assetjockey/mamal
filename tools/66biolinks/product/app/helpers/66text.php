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

function get_phone_number($raw_phone_number, $default_country_code = '51') {
    /* remove surrounding spaces */
    $raw_phone_number = trim($raw_phone_number);

    /* keep only digits and plus signs */
    $raw_phone_number = preg_replace('/[^\d\+]/', '', $raw_phone_number);

    /* format international number */
    if (substr($raw_phone_number, 0, 1) === '+') {
        $sanitized_phone_number = '+' . preg_replace('/[^\d]/', '', substr($raw_phone_number, 1));
    } else {
        $sanitized_phone_number = '+' . $default_country_code . preg_replace('/[^\d]/', '', $raw_phone_number);
    }

    /* reject invalid E.164 length */
    if (!preg_match('/^\+\d{6,15}$/', $sanitized_phone_number)) {
        return null;
    }

    return $sanitized_phone_number;
}
