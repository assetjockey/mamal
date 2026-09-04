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

class Projects extends Model {

    public function get_projects_by_user_id($user_id) {
        if(!settings()->links->projects_is_enabled) return [];

        /* Get the user projects */
        $projects = [];

        /* Try to check if the user posts exists via the cache */
        $cache_instance = cache()->getItem('projects?user_id=' . $user_id);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $projects_result = database()->query("SELECT * FROM `projects` WHERE `user_id` = {$user_id}");
            while($row = $projects_result->fetch_object()) $projects[$row->project_id] = $row;

            cache()->save(
                $cache_instance->set($projects)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $user_id)
            );

        } else {

            /* Get cache */
            $projects = $cache_instance->get();

        }

        return $projects;

    }

}
