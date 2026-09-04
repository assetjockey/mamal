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
namespace Altum\Controllers;


defined('ALTUMCODE') || die();

class Logout extends Controller {

    public function index() {

        /* Exit admin impersonation */
        if(isset($_GET['admin_impersonate_user'])) {
            $admin_user_id = session_get('admin_user_id');

            /* Logout of the current users */
            \Altum\Authentication::logout(false);

            $admin_user = db()->where('user_id', $admin_user_id)->getOne('users', ['user_id', 'password']);

            if($admin_user) {
                /* Login as the admin */
                session_start();
                session_regenerate_id(true);
                session_set('user_id', $admin_user_id);
                session_set('user_password_hash', md5($admin_user->password));
            }

            redirect('admin/users');
        }

        /* Exit team delegated access */
        else if(isset($_GET['team'])) {
            session_unset_key('team_id');
            redirect('teams-member');
        }

        /* Normal logout */
        else {
            \Altum\Authentication::logout();
        }

    }

}
