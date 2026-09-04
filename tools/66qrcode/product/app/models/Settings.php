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

namespace Altum\Models;

defined('ALTUMCODE') || die();

class Settings extends Model {

    public function get() {

        $cache_instance = cache()->getItem('settings');

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            $result = database()->query("SELECT * FROM `settings`");
            $data = new \StdClass();

            while($row = $result->fetch_object()) {

                /* Put the value in a variable so we can check if its json or not */
                $value = json_decode($row->value);

                $data->{$row->key} = is_null($value) ? $row->value : $value;

            }

            cache()->save($cache_instance->set($data)->expiresAfter(CACHE_DEFAULT_SECONDS));

        } else {

            /* Get cache */
            $data = $cache_instance->get('settings');

        }

        /* Define some stuff from the database */
        if(!defined('PRODUCT_VERSION')) define('PRODUCT_VERSION', $data->product_info->version);
        if(!defined('PRODUCT_CODE')) define('PRODUCT_CODE', $data->product_info->code);

        /* Set the full url for assets */
        $assets_url = SITE_URL . ASSETS_URL_PATH;
        $uploads_url = SITE_URL . UPLOADS_URL_PATH;

        if(\Altum\Plugin::is_active('offload')) {
            if(!empty($data->offload->assets_url)) {
                $assets_url = $data->offload->assets_url;
            }

            if(!empty($data->offload->uploads_url)) {
                $uploads_url = $data->offload->uploads_url;
            }

            /* CDN */
            if(!empty($data->offload->cdn_assets_url)) {
                $assets_url = $data->offload->cdn_assets_url;
            }

            if(!empty($data->offload->cdn_uploads_url)) {
                $uploads_url = $data->offload->cdn_uploads_url;
            }
        }

        define('ASSETS_FULL_URL', $assets_url);
        define('UPLOADS_FULL_URL', $uploads_url);

        /* Fallbacks for new releases */
        if(isset($data->notification_handlers) && !isset($data->notification_handlers->is_enabled)) {
            $data->notification_handlers->is_enabled = true;
        }

        if(!isset($data->main->openai_api_url)) {
            $data->main->openai_api_url = 'https://api.openai.com/';
        }

        /* Popular posts limit */
        if(!isset($data->content->blog_popular_widget_posts_limit)) {
            $data->content->blog_popular_widget_posts_limit = 5;
        }

        return $data;
    }

}
