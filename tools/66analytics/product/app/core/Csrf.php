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
namespace Altum;

defined('ALTUMCODE') || die();

class Csrf {

    public static function set($name = 'token', $regenerate = false) {
        $existing_token = session_get($name, null);
        $new_token = bin2hex(random_bytes(32));

        if (is_null($existing_token) || $regenerate) {
            session_set($name, $new_token);
			return $new_token;
        }

		return $existing_token;
    }

    public static function get($name = 'token') {
        return session_get($name, null) ?? self::set($name);
    }

    public static function check($name = 'token') {
        $token = self::get($name);

        return (
            (isset($_GET[$name]) && hash_equals($token, $_GET[$name])) ||
            (isset($_POST[$name]) && hash_equals($token, $_POST[$name]))
        );
    }

}
