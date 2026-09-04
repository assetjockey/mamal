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

/* Helper class for Plisio */
defined('ALTUMCODE') || die();

class Plisio {
    static public $api_url = 'https://api.plisio.net/';

    public static function get_api_url() {
        return self::$api_url;
    }

    public static function validate_hash($secret_key) {
        if (!isset($_POST['verify_hash'])) {
            return false;
        }

        $post = $_POST;
        $verifyHash = $post['verify_hash'];
        unset($post['verify_hash']);
        ksort($post);

        if (isset($post['expire_utc'])){
            $post['expire_utc'] = (string)$post['expire_utc'];
        }
        if (isset($post['tx_urls'])){
            $post['tx_urls'] = html_entity_decode($post['tx_urls']);
        }

        $postString = serialize($post);
        $checkKey = hash_hmac('sha1', $postString, $secret_key);

        if ($checkKey != $verifyHash) {
            return false;
        }

        return true;
    }
}
