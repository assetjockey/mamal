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

class Website extends Model {

    public function get_website_by_pixel_key($pixel_key) {

        /* Try to check if the store posts exists via the cache */
        $cache_instance = cache()->getItem('website?pixel_key=' . md5($pixel_key));

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $data = db()->where('pixel_key', $pixel_key)->getOne('websites');

            if($data) {
                /* Save to cache */
                cache()->save(
                    $cache_instance->set($data)->expiresAfter(43200)->addTag('user_id=' . $data->user_id)->addTag('website_id=' . $data->website_id)
                );
            }

        } else {

            /* Get cache */
            $data = $cache_instance->get();

        }

        return $data;
    }

    public function get_websites_by_user_id($user_id) {

        $cache_instance = cache()->getItem('websites_' . $user_id);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            $result = database()->query("SELECT * FROM `websites` WHERE `user_id` = {$user_id}");
            $data = [];

            while($row = $result->fetch_object()) {

                $data[$row->website_id] = $row;

            }

            cache()->save($cache_instance->set($data)->expiresAfter(CACHE_DEFAULT_SECONDS));

        } else {

            /* Get cache */
            $data = $cache_instance->get();

        }

        return $data;
    }

    public function get_websites_by_websites_ids(array $websites_ids = []) {

        $websites_ids_query = implode(',', $websites_ids);
        $result = database()->query("SELECT * FROM `websites` WHERE `website_id` IN ({$websites_ids_query}) ");
        $data = [];

        while($row = $result->fetch_object()) {

            $data[$row->website_id] = $row;

        }

        return $data;
    }
}
