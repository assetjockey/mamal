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

namespace Altum\models;

defined('ALTUMCODE') || die();

class HeartbeatsIncidents extends Model {

    public function get_heartbeat_incidents_by_heartbeat_id_and_start_datetime_and_end_datetime($heartbeat_id, $start_datetime, $end_datetime) {

        /* Get all available heartbeat logs */
        $heartbeat_incidents = [];

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = cache()->getItem('s_heartbeat_incidents?heartbeat_id=' . $heartbeat_id . '&start_datetime=' . md5($start_datetime) . '&end_datetime=' . md5($end_datetime));

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $heartbeat_incidents_result = database()->query("SELECT * FROM `incidents` WHERE `heartbeat_id` = {$heartbeat_id} AND `start_datetime` >= '{$start_datetime}' AND (`end_datetime` <= '{$end_datetime}' OR `end_datetime` IS NULL) ORDER BY `incident_id` DESC");

            while($row = $heartbeat_incidents_result->fetch_object()) $heartbeat_incidents[] = $row;

            cache()->save(
                $cache_instance->set($heartbeat_incidents)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('heartbeat_id=' . $heartbeat_id)
            );

        } else {

            /* Get cache */
            $heartbeat_incidents = $cache_instance->get();

        }

        return $heartbeat_incidents;

    }

}
