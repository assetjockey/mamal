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

class Pixel extends Model {

    public function get_pixels($user_id) {
        if(!settings()->links->pixels_is_enabled) return [];

        /* Get the user pixels */
        $pixels = [];

        /* Try to check if the user posts exists via the cache */
        $cache_instance = cache()->getItem('pixels?user_id=' . $user_id);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $pixels_result = database()->query("SELECT * FROM `pixels` WHERE `user_id` = {$user_id}");
            while($row = $pixels_result->fetch_object()) $pixels[$row->pixel_id] = $row;

            cache()->save(
                $cache_instance->set($pixels)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $user_id)->addTag('pixels?user_id=' . $user_id)
            );

        } else {

            /* Get cache */
            $pixels = $cache_instance->get();

        }

        return $pixels;

    }

    public function get_pixels_by_pixels_ids_and_user_id($pixels_ids, $user_id) {
        if(!settings()->links->pixels_is_enabled) return [];

        if(empty($pixels_ids)) return [];

        $pixels_ids_plain = implode(',', $pixels_ids);

        /* Get the user pixels */
        $pixels = [];

        /* Try to check if the user posts exists via the cache */
        $cache_instance = cache()->getItem('pixels?pixels_ids_plain=' . $pixels_ids_plain);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $pixels_result = database()->query("SELECT * FROM `pixels` WHERE `pixel_id` IN({$pixels_ids_plain})");
            while($row = $pixels_result->fetch_object()) $pixels[$row->pixel_id] = $row;

            cache()->save(
                $cache_instance->set($pixels)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $user_id)->addTag('pixels?user_id=' . $user_id)
            );

        } else {

            /* Get cache */
            $pixels = $cache_instance->get();

        }

        return $pixels;

    }
}
