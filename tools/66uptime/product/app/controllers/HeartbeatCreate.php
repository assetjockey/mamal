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

defined('ALTUMCODE') || die();

class HeartbeatCreate extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->heartbeats_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.heartbeats')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('heartbeats');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `heartbeats` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->heartbeats_limit != -1 && $total_rows >= $this->user->plan_settings->heartbeats_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('heartbeats');
        }

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
                $code = md5(time() . $_POST['name'] . $this->user->user_id . microtime());
                $next_run_datetime = (new \DateTime())->modify('+5 years')->format('Y-m-d H:i:s');
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
                $heartbeat_id = db()->insert('heartbeats', [
                    'project_id' => $_POST['project_id'],
                    'user_id' => $this->user->user_id,
                    'name' => $_POST['name'],
                    'code' => $code,
                    'settings' => $settings,
                    'notifications' => $notifications,
                    'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
                    'email_reports_last_datetime' => get_date(),
                    'next_run_datetime' => $next_run_datetime,
                    'datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(l('heartbeat_create.success_message'));

                redirect('heartbeat/' . $heartbeat_id);
            }

        }

        /* Set default values */
        $values = [
            'name' => $_POST['name'] ?? '',
            'run_interval' => $_POST['run_interval'] ?? 1,
            'run_interval_type' => $_POST['run_interval_type'] ?? 'hours',
            'run_interval_grace' => $_POST['run_interval_grace'] ?? 5,
            'run_interval_grace_type' => $_POST['run_interval_grace_type'] ?? 'minutes',
            'email_reports_is_enabled' => $_POST['email_reports_is_enabled'] ?? 0,
            'project_id' => $_POST['project_id'] ?? '',
        ];

        /* Prepare the view */
        $data = [
            'projects' => $projects,
            'notification_handlers' => $notification_handlers,
            'values' => $values
        ];

        $view = new \Altum\View('heartbeat-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
