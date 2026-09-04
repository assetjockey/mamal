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

class TeamsMembers extends Model {

    public function get_team_member_by_team_id_and_user_id($team_id, $user_id) {

        /* Get the team member */
        $team_member = null;

        /* Try to check if the resource exists via the cache */
        $cache_instance = cache()->getItem('team_member?team_id=' . $team_id . '&user_id=' . $user_id);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $team_member = db()->where('team_id', $team_id)->where('user_id', $user_id)->getOne('teams_members');

            if($team_member) {
                cache()->save(
                    $cache_instance->set($team_member)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $team_member->user_id)->addTag('team_id=' . $team_member->team_id)
                );
            }

        } else {

            /* Get cache */
            $team_member = $cache_instance->get();

        }

        return $team_member;

    }

}
