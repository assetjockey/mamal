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

use Altum\Models\User;

defined('ALTUMCODE') || die();

class Teams {
    public static $team = null;
    public static $team_member = null;
    public static $team_user = null;

    public static function initialize() {
        if(\Altum\Plugin::is_active('teams') && session_has('team_id')) {
            /* Get requested team */
            self::$team = (new \Altum\Models\Teams())->get_team_by_team_id(session_get('team_id'));

            if(self::$team) {
                /* Get team member */
                self::$team_member = (new \Altum\Models\TeamsMembers())->get_team_member_by_team_id_and_user_id(self::$team->team_id, \Altum\Authentication::$user_id);

                if(self::$team_member) {
                    self::$team_member->access = json_decode(self::$team_member->access);
                }
            }
        }
    }

    public static function delegate_access() {
        if(!self::$team || !self::$team_member) {
            return false;
        }

        /* Get team owner user */
        self::$team_user = (new User())->get_user_by_user_id(self::$team->user_id);

        return self::$team_user;
    }

    public static function is_delegated() {
        return self::$team && self::$team_member;
    }

    public static function has_access($access_level = null) {
        if(!self::$team || !self::$team_member) {
            /* Return true as there is no team or team member set */
            return true;
        }

        return self::$team_member->access->{$access_level};
    }
}
