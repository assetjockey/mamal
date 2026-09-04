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

class Event {
    /* For events */
    public static $callbacks = [];

    /* For extra content, such as javascript */
    public static $content = [];

    public static function bind($event, Callable $function) {
        if(empty(self::$callbacks[$event]) || !is_array(self::$callbacks[$event])){
            self::$callbacks[$event] = [];
        }

        self::$callbacks[$event][] = $function;
    }

    public static function trigger() {
        $args = func_get_args();
        $event = $args[0];
        unset($args[0]);

        if(isset(self::$callbacks[$event])) {
            foreach(self::$callbacks[$event] as $func) {
                call_user_func_array($func, $args);
            }
        }
    }

    public static function exists_content_type($type) {
        return isset(self::$content[$type]);
    }

    public static function exists_content_type_key($type, $key) {
        return self::exists_content_type($type) && isset(self::$content[$type][$key]);
    }

    public static function add_content($content, $type, $key = null) {

        if(!isset(self::$content[$type])) {
            self::$content[$type] = [];
        }

        /* Key already exists → do nothing */
        if($key !== null && isset(self::$content[$type][$key])) {
            return;
        }

        /* Lazy execution support */
        if(is_callable($content)) {
            $content = $content();
        }

        if($key !== null) {
            self::$content[$type][$key] = $content;
        } else {
            self::$content[$type][] = $content;
        }
    }

    public static function get_content($type) {

        $full_content = '';

        if(isset(self::$content[$type])) {
            foreach(self::$content[$type] as $key => $value) {

                $full_content .= $value;

            }
        }

        return $full_content;

    }
}
