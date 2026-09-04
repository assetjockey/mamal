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

use Altum\Alerts;

defined('ALTUMCODE') || die();

class TeamsMembers extends Controller {

    public function delete() {

        if(!\Altum\Plugin::is_active('teams')) {
            throw_404();
        }

        \Altum\Authentication::guard();

        if (empty($_POST)) {
            throw_404();
        }

        $team_member_id = (int) $_POST['team_member_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('teams-system');
        }

        if(!$team_member = db()->where('team_member_id', $team_member_id)->getOne('teams_members')) {
            redirect('teams-system');
        }

        if(!$team = db()->where('team_id', $team_member->team_id)->getOne('teams')) {
            redirect('teams-system');
        }

        /* Detect if it's a team owner deletion or team member deletion */
        $team_member_deletion_as = null;

        if($team->user_id == $this->user->user_id) {
            $team_member_deletion_as = 'owner';
        }

        elseif($team_member->user_id == $this->user->user_id || $team_member->user_email == $this->user->email) {
            $team_member_deletion_as = 'member';
        }

        else {
            redirect('teams-system');
        }


        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the team member */
            db()->where('team_member_id', $team_member->team_member_id)->delete('teams_members');

            /* Clear the cache */
            cache()->deleteItem('team_member?team_id=' . $team_member->team_id . '&user_id=' . $team_member->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $team_member->user_email . '</strong>'));

            if($team_member_deletion_as == 'owner') {
                redirect('team/' . $team_member->team_id);
            } else {
                redirect('teams-member');
            }

        }

        redirect('teams-system');
    }

    public function join() {

        if(!\Altum\Plugin::is_active('teams')) {
            throw_404();
        }

        \Altum\Authentication::guard();

        if (empty($_POST)) {
            throw_404();
        }

        $team_member_id = (int) $_POST['team_member_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('teams-member');
        }

        if(!$team_member = db()->where('team_member_id', $team_member_id)->where('user_email', $this->user->email)->where('status', 0)->getOne('teams_members')) {
            redirect('teams-member');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Update the team member */
            db()->where('team_member_id', $team_member->team_member_id)->update('teams_members', [
                'token' => '',
                'user_id' => $this->user->user_id,
                'status' => 1,
                'last_datetime' => get_date(),
            ]);

            /* Clear the cache */
            cache()->deleteItem('team_member?team_id=' . $team_member->team_id . '&user_id=' . $team_member->user_id);

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.update2'));

            redirect('teams-member');
        }

        redirect('teams-member');
    }

    public function login() {

        if(!\Altum\Plugin::is_active('teams')) {
            throw_404();
        }

        \Altum\Authentication::guard();

        if (empty($_POST)) {
            throw_404();
        }

        $team_member_id = (int) $_POST['team_member_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('teams-member');
        }

        if(!$team_member = db()->where('team_member_id', $team_member_id)->where('user_id', $this->user->user_id)->where('status', 1)->getOne('teams_members')) {
            redirect('teams-member');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Login the team member */
            session_set('team_id', $team_member->team_id);

            /* Clear the cache */
            cache()->deleteItem('team_member?team_id=' . $team_member->team_id . '&user_id=' . $team_member->user_id);

            redirect('dashboard');
        }

        redirect('teams-member');
    }

}
