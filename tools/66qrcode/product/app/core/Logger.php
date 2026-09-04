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

class Logger {

    public static function users($user_id, $type) {

        $ip = get_ip();

        /* Detect the location */
        try {
            $maxmind = (get_maxmind_reader_city())->get($ip);
        } catch(\Exception $exception) {
            /* :) */
        }
        $continent_code = isset($maxmind) && isset($maxmind['continent']) ? $maxmind['continent']['code'] : null;
        $country_code = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;
        $city_name = isset($maxmind) && isset($maxmind['city']) ? $maxmind['city']['names']['en'] : null;
        $device_type = get_this_device_type();

        /* Detect extra details about the user */
        $whichbrowser = get_whichbrowser();

        /* Detect extra details about the user */
        $os_name = $whichbrowser->os->name ?? null;

        db()->insert('users_logs', [
            'user_id'        => $user_id,
            'type'           => $type,
            'ip'             => $ip,
            'device_type'    => $device_type,
            'os_name'        => $os_name,
            'continent_code' => $continent_code,
            'country_code'   => $country_code,
            'city_name'      => $city_name,
            'datetime'       => get_date(),
        ]);

    }

}
