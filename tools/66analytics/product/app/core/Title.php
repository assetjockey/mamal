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

class Title {
    public static $full_title;
    public static $site_title;
    public static $page_title;
    public static $title_separator = '-';

    public static function initialize($site_title, $title_separator = '-') {

        self::$site_title = $site_title;
        self::$title_separator = $title_separator;

        /* Add the prefix if needed */
        $language_key = preg_replace('/-/', '_', \Altum\Router::$controller_key);

        if(\Altum\Router::$path != '') {
            $language_key = \Altum\Router::$path . '_' . $language_key;
        }

        /* Check if the default is viable and use it */
        $page_title = (l($language_key . '.title')) ? l($language_key . '.title') : \Altum\Router::$controller;

        self::set($page_title);
    }

    public static function set($page_title, $full = false) {

        self::$page_title = self::$title_separator == '-' ? $page_title : str_replace(' - ', ' ' . self::$title_separator . ' ', $page_title);

        self::$full_title = self::$page_title . ($full ? null : ' ' . self::$title_separator . ' ' . self::$site_title);

        /* Set title */
        Meta::set_social_title(self::$full_title);
    }


    public static function get() {

        return self::$full_title;

    }

}
