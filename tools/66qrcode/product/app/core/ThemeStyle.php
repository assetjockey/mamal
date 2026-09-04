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

class ThemeStyle {
    public static $themes = [
        'light' => [
            'ltr' => 'bootstrap.min.css',
            'rtl' => 'bootstrap-rtl.min.css'
        ],
        'dark' => [
            'ltr' => 'bootstrap-dark.min.css',
            'rtl' => 'bootstrap-dark-rtl.min.css'
        ],
    ];
    public static $theme = 'light';

    public static function get() {
        if(isset($_COOKIE['theme_style']) && array_key_exists($_COOKIE['theme_style'], self::$themes)) {
            self::$theme = input_clean($_COOKIE['theme_style']);
        }

        return self::$theme;
    }

    public static function get_file() {
        return (\Altum\Router::$path != 'admin' && settings()->theme->{self::get() . '_is_enabled'} ? 'custom-bootstrap/' : null ) . self::$themes[self::get()][l('direction')];
    }

    public static function set_default($theme) {
        self::$theme = $theme;
    }

}
