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

/* Helper class for Paddle */
defined('ALTUMCODE') || die();

class Paddle {
    static public $sandbox_api_url = 'https://sandbox-vendors.paddle.com/api/';
    static public $live_api_url = 'https://vendors.paddle.com/api/';

    public static function get_api_url() {
        return settings()->paddle->mode == 'live' ? self::$live_api_url : self::$sandbox_api_url;
    }

}
