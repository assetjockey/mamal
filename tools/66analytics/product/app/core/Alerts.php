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

class Alerts {
    public static $types = ['success', 'error', 'info', 'warning'];

    /* Field errors */
    public static function add_field_error($key, $message) {
        $field_errors = session_get('field_errors', []);

        if (!isset($field_errors[$key])) {
            $field_errors[$key] = [$message];
        } else {
            $field_errors[$key][] = $message;
        }

        session_set('field_errors', $field_errors);
    }

    public static function has_field_errors($key = null) {
        $field_errors = session_get('field_errors', []);

        if (is_null($key)) {
            return !empty($field_errors);
        }

        if (is_array($key)) {
            foreach ($key as $field_name) {
                if (strpos($field_name, '*') !== false) {
                    foreach ($field_errors as $session_field_error_key => $session_field_error_value) {
                        if (mb_ereg($field_name, $session_field_error_key) && !empty($session_field_error_value)) {
                            return true;
                        }
                    }
                } else {
                    if (!empty($field_errors[$field_name])) {
                        return true;
                    }
                }
            }

            return false;
        }

        return !empty($field_errors[$key]);
    }

    public static function get_first_field_error($key) {
        $field_errors = session_get('field_errors', []);
        return isset($field_errors[$key]) ? reset($field_errors[$key]) : null;
    }

    public static function output_field_error($key) {
        $output = null;

        if (self::has_field_errors($key)) {
            $output = '<div class="invalid-feedback d-inline-block">' . self::get_first_field_error($key) . '</div>';

            $field_errors = session_get('field_errors', []);
            unset($field_errors[$key]);

            if (empty($field_errors)) {
                session_unset_key('field_errors');
            } else {
                session_set('field_errors', $field_errors);
            }
        }

        return $output;
    }

    public static function clear_field_errors($key = null) {
        $field_errors = session_get('field_errors', []);

        if ($key) {
            unset($field_errors[$key]);
            session_set('field_errors', $field_errors);
        } else {
            session_unset_key('field_errors');
        }
    }

    /* Session alerts */
    public static function add($type, $key, $message) {
        $alerts = session_get($type, []);

        if (!isset($alerts[$key])) {
            $alerts[$key] = [$message];
        } else {
            $alerts[$key][] = $message;
        }

        session_set($type, $alerts);
    }

    public static function has($type, $key = null) {
        $alerts = session_get($type, []);

        if (is_null($key)) {
            return !empty($alerts);
        }

        return isset($alerts[$key]);
    }

    public static function get($type, $key) {
        $alerts = session_get($type, []);
        return $alerts[$key] ?? null;
    }

    public static function output_alerts($type = null) {
        $types = is_null($type) ? self::$types : [$type];
        $output = null;

        foreach ($types as $type_name) {
            $alerts = session_get($type_name, []);

            if (empty($alerts)) {
                continue;
            }

            foreach ($alerts as $key => $messages) {
                foreach ($messages as $message) {
                    $output .= output_alert($type_name, $message);
                }
                unset($alerts[$key]);
            }

            if (empty($alerts)) {
                session_unset_key($type_name);
            } else {
                session_set($type_name, $alerts);
            }
        }

        return $output;
    }

    /* Shortcuts */
    public static function add_warning($message, $key = 'warning') {
        self::add('warning', $key, $message);
    }

    public static function has_warnings($key = null) {
        return self::has('warning', $key);
    }

    public static function add_error($message, $key = 'error') {
        self::add('error', $key, $message);
    }

    public static function has_errors($key = null) {
        return self::has('error', $key);
    }

    public static function add_info($message, $key = 'info') {
        self::add('info', $key, $message);
    }

    public static function has_infos($key = null) {
        return self::has('info', $key);
    }

    public static function add_success($message, $key = 'success') {
        self::add('success', $key, $message);
    }

    public static function has_successes($key = null) {
        return self::has('success', $key);
    }
}
