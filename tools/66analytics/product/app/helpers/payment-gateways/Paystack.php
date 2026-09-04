<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
 *
 * This software is proprietary software owned and licensed by AltumCode.
 * A valid license is required to use, modify, or distribute this software.
 * Unauthorized use, reproduction, modification, or distribution is prohibited.
 *
 * 🌍 Explore all AltumCode projects: https://altumcode.com/
 * 📧 Support & general inquiries: https://altumcode.com/contact
 * 📤 Download the latest version: https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 */
namespace Altum\PaymentGateways;

/* Helper class for Paystack v2 */
defined('ALTUMCODE') || die();

class Paystack {
    static public $api_url = 'https://api.paystack.co/';
    static public $secret_key = null;

    public static function get_headers() {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . self::$secret_key
        ];
    }

}
