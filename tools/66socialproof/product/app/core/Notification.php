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

namespace Altum;

defined('ALTUMCODE') || die();

class Notification {
    public static $notifications;
    public static $enabled_notifications = null;
    public static $available_notifications = null;

    public static function get($notification_type, $notification = null, $user = null, $force_branding = null, $force_branding_data = null, $is_preview = false) {

        if(!self::$available_notifications) {
            self::$available_notifications = require APP_PATH . 'includes/notifications.php';
        }

        /* When no actual notification data is present, use the defaults */
        if(!$notification) {
            $notification = new \StdClass();
            $notification->notification_id = $notification_type;
            $notification->settings = (object) self::$available_notifications[$notification_type];
        }

        /* Determine the notification branding settings */
        if($user && !$user->plan_settings->removable_branding && !$notification->settings->display_branding) {
            $notification->settings->display_branding = true;
        }

        if($user && $user->plan_settings->removable_branding && !$notification->settings->display_branding) {
            $notification->settings->display_branding = false;
        }

        if(!is_null($force_branding)) {
            $notification->settings->display_branding = $force_branding;
            $notification->branding = $force_branding_data;
        } else {
            /* Check if we can show the custom branding if available */
            if(isset($notification->branding, $notification->branding->name, $notification->branding->url) && !$user->plan_settings->custom_branding) {
                $notification->branding = false;
            }
        }

        /* Branding processing */
        $replacers = [
            '{{URL}}' => url(),
            '{{WEBSITE_TITLE}}' => settings()->main->title,
            '{{AFFILIATE_URL_TAG}}' => \Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled && $user ? '?ref=' . $user->referral_key : null,
        ];

        settings()->notifications->branding = str_replace(
            array_keys($replacers),
            array_values($replacers),
            settings()->notifications->branding
        );

        if(self::$available_notifications[$notification_type]['type'] == 'default') {
            $data = require THEME_PATH . 'views/partials/notifications/' . mb_strtolower($notification_type) .'.php';
        } elseif(self::$available_notifications[$notification_type]['type'] == 'pro') {
            $data = require \Altum\Plugin::get('pro-notifications')->path . 'views/partials/notifications/' . mb_strtolower($notification_type) .'.php';
        } elseif(self::$available_notifications[$notification_type]['type'] == 'ultimate') {
            /* Load ultimate notifications */
            $data = require \Altum\Plugin::get('ultimate-notifications')->path . 'views/partials/notifications/' . mb_strtolower($notification_type) .'.php';
        }

        /* remove only the first <script> */
        $data->javascript = preg_replace('/<script>/', '', $data->javascript, 1);

        /* remove only the last </script> */
        $data->javascript = preg_replace('/<\/script>(?!.*<\/script>)/s', '', $data->javascript, 1);

        return $data;
    }
}
