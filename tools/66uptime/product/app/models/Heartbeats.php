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

namespace Altum\Models;

defined('ALTUMCODE') || die();

class Heartbeats extends Model {

    public function get_heartbeat_by_heartbeat_id($heartbeat_id) {

        /* Get the heartbeat */
        $heartbeat = null;

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = cache()->getItem('s_heartbeat?heartbeat_id=' . $heartbeat_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $heartbeat = database()->query("SELECT * FROM `heartbeats` WHERE `heartbeat_id` = {$heartbeat_id}")->fetch_object() ?? null;

            cache()->save(
                $cache_instance->set($heartbeat)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('heartbeat_id=' . $heartbeat_id)
            );

        } else {

            /* Get cache */
            $heartbeat = $cache_instance->get();

        }

        return $heartbeat;

    }

    public function get_heartbeat_by_code($code) {

        /* Get the heartbeat */
        $heartbeat = null;

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = cache()->getItem('heartbeat?code=' . $code);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $heartbeat = database()->query("SELECT * FROM `heartbeats` WHERE `code` = '{$code}'")->fetch_object() ?? null;

            if($heartbeat) {
                cache()->save(
                    $cache_instance->set($heartbeat)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('heartbeat_id=' . $heartbeat->heartbeat_id)
                );
            }

        } else {

            /* Get cache */
            $heartbeat = $cache_instance->get();

        }

        return $heartbeat;

    }

    public function get_heartbeats_by_user_id($user_id) {

        /* Get the status_page posts */
        $heartbeats = [];

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = cache()->getItem('s_heartbeats?user_id=' . $user_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $heartbeats_result = database()->query("
                SELECT 
                    *
                FROM 
                    `heartbeats` 
                WHERE 
                    `user_id` = {$user_id}
                    AND `is_enabled` = 1
            ");
            while($row = $heartbeats_result->fetch_object()) $heartbeats[$row->heartbeat_id] = $row;

            /* Properly tag the cache */
            $cache_instance->set($heartbeats)->expiresAfter(CACHE_DEFAULT_SECONDS);

            foreach($heartbeats as $heartbeat) {
                $cache_instance->addTag('heartbeat_id=' . $heartbeat->heartbeat_id);
            }

            if(count($heartbeats)) {
                cache()->save($cache_instance);
            }

        } else {

            /* Get cache */
            $heartbeats = $cache_instance->get();

        }

        return $heartbeats;

    }

    public function get_heartbeats_by_heartbeats_ids($heartbeats_ids) {

        if(empty($heartbeats_ids)) return [];

        $heartbeats_ids_plain = implode(',', $heartbeats_ids);

        /* Get the status_page posts */
        $heartbeats = [];

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = cache()->getItem('s_heartbeats?heartbeats_ids=' . $heartbeats_ids_plain);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $heartbeats_result = database()->query("
                SELECT 
                    *
                FROM 
                    `heartbeats` 
                WHERE 
                    `heartbeat_id` IN ({$heartbeats_ids_plain})
                    AND `is_enabled` = 1
            ");
            while($row = $heartbeats_result->fetch_object()) $heartbeats[$row->heartbeat_id] = $row;

            /* Properly tag the cache */
            $cache_instance->set($heartbeats)->expiresAfter(CACHE_DEFAULT_SECONDS);

            foreach($heartbeats_ids as $heartbeat_id) {
                $cache_instance->addTag('heartbeat_id=' . $heartbeat_id);
            }

            if(count($heartbeats)) {
                cache()->save($cache_instance);
            }

        } else {

            /* Get cache */
            $heartbeats = $cache_instance->get();

        }

        return $heartbeats;

    }

}
