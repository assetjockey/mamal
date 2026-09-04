<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
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

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Title;

defined('ALTUMCODE') || die();

class HeartbeatUpdate extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->heartbeats_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.heartbeats')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('heartbeats');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `heartbeats` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;
        if($this->user->plan_settings->heartbeats_limit != -1 && $total_rows > $this->user->plan_settings->heartbeats_limit) {
            redirect('heartbeats');
        }

        $heartbeat_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$heartbeat = db()->where('heartbeat_id', $heartbeat_id)->where('user_id', $this->user->user_id)->getOne('heartbeats')) {
            redirect('heartbeats');
        }
        $heartbeat->settings = json_decode($heartbeat->settings ?? '');
        $heartbeat->notifications = json_decode($heartbeat->notifications ?? '');

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        if(!empty($_POST)) {
            $_POST['name'] = input_clean($_POST['name'], 64);
            $_POST['run_interval'] = (int) $_POST['run_interval'];
            $_POST['run_interval_type'] = in_array($_POST['run_interval_type'], ['seconds', 'minutes', 'hours', 'days']) ? $_POST['run_interval_type'] : 'seconds';
            $_POST['run_interval_grace'] = (int) $_POST['run_interval_grace'];
            $_POST['run_interval_grace_type'] = in_array($_POST['run_interval_grace_type'], ['seconds', 'minutes', 'hours', 'days']) ? $_POST['run_interval_grace_type'] : 'seconds';
            $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
            $_POST['is_ok_notifications'] = array_map(
                'intval',
                array_filter($_POST['is_ok_notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                    return array_key_exists($notification_handler_id, $notification_handlers);
                })
            );
            $_POST['email_reports_is_enabled'] = (int) isset($_POST['email_reports_is_enabled']);
            $_POST['is_enabled'] = (int) isset($_POST['is_enabled']);

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $settings = json_encode([
                    'run_interval' => $_POST['run_interval'],
                    'run_interval_type' => $_POST['run_interval_type'],
                    'run_interval_grace' => $_POST['run_interval_grace'],
                    'run_interval_grace_type' => $_POST['run_interval_grace_type'],
                ]);

                $notifications = json_encode([
                    'is_ok' => $_POST['is_ok_notifications'],
                ]);

                /* Database query */
                db()->where('heartbeat_id', $heartbeat->heartbeat_id)->update('heartbeats', [
                    'project_id' => $_POST['project_id'],
                    'name' => $_POST['name'],
                    'settings' => $settings,
                    'notifications' => $notifications,
                    'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
                    'is_enabled' => $_POST['is_enabled'],
                    'last_datetime' => get_date(),
                ]);

                /* Clear the cache */
                cache()->deleteItemsByTag('heartbeat_id=' . $heartbeat_id);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('heartbeat-update/' . $heartbeat_id);
            }

        }

        /* Set a custom title */
        Title::set(sprintf(l('heartbeat_update.title'), $heartbeat->name));

        /* Prepare the view */
        $data = [
            'projects' => $projects,
            'notification_handlers' => $notification_handlers,
            'heartbeat' => $heartbeat
        ];

        $view = new \Altum\View('heartbeat-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
