<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
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

namespace Altum\PaymentGateways;

/* Helper class for 1pay */
defined('ALTUMCODE') || die();

class Onepay {
    static public $api_url = 'https://api.1pay.ch/v1.16/';

    public static function get_headers() {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-API-KEY' => settings()->onepay->api_key,
        ];
    }
}
