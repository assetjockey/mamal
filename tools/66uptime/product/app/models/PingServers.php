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

class PingServers extends Model {

    public function get_ping_servers() {

        /* Get all available ping servers */
        $ping_servers = [];

        /* Try to check if the user posts exists via the cache */
        $cache_instance = cache()->getItem('ping_servers');

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            $where = \Altum\Plugin::is_active('ping-servers') ? '`is_enabled` = 1' : '`ping_server_id` = 1';

            /* Get data from the database */
            $ping_servers_result = database()->query("SELECT * FROM `ping_servers` WHERE {$where}");
            while($row = $ping_servers_result->fetch_object()) $ping_servers[$row->ping_server_id] = $row;

            cache()->save(
                $cache_instance->set($ping_servers)->expiresAfter(86400 * 30)
            );

        } else {

            /* Get cache */
            $ping_servers = $cache_instance->get();

        }

        return $ping_servers;

    }

}
