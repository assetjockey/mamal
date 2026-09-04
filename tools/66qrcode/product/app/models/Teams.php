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

class Teams extends Model {

    public function get_team_by_team_id($team_id) {

        /* Get the team */
        $team = null;

        /* Try to check if the resource exists via the cache */
        $cache_instance = cache()->getItem('team?team_id=' . $team_id);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $team = db()->where('team_id', $team_id)->getOne('teams');

            if($team) {
                cache()->save(
                    $cache_instance->set($team)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $team->user_id)
                );
            }

        } else {

            /* Get cache */
            $team = $cache_instance->get();

        }

        return $team;

    }

}
