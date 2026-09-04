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

class ServerMonitorCreate extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->server_monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.server_monitors')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('server-monitors');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `server_monitors` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->server_monitors_limit != -1 && $total_rows >= $this->user->plan_settings->server_monitors_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('server-monitors');
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Monitors vars */
        $server_monitor_check_intervals = require APP_PATH . 'includes/server_monitor_check_intervals.php';

        if(!empty($_POST)) {
            $_POST['name'] = input_clean($_POST['name'], 64);
            $_POST['target'] = query_clean($_POST['target']);
            $_POST['server_check_interval_seconds'] = in_array($_POST['server_check_interval_seconds'], $this->user->plan_settings->server_monitors_check_intervals ?? []) ? (int) $_POST['server_check_interval_seconds'] : reset($this->user->plan_settings->server_monitors_check_intervals);
            $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;

            $_POST['notifications'] = array_map(
                'intval',
                array_filter($_POST['notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                    return array_key_exists($notification_handler_id, $notification_handlers);
                })
            );

            /* Alerts */
            if(!isset($_POST['alert_metric'])) {
                $_POST['alert_metric'] = [];
                $_POST['alert_rule'] = [];
                $_POST['alert_value'] = [];
                $_POST['alert_trigger'] = [];
            }

            $alerts = [];
            foreach($_POST['alert_metric'] as $key => $value) {
                if(empty(trim($value))) continue;

                $alerts[] = [
                    'metric' => in_array($value, ['cpu_usage', 'disk_usage', 'ram_usage']) ? $value : 'cpu_usage',
                    'rule' => in_array($_POST['alert_rule'][$key], ['is_higher', 'is_lower']) ? $_POST['alert_rule'][$key] : 'is_higher',
                    'value' => in_array($_POST['alert_value'][$key], range(1, 99)) ? $_POST['alert_value'][$key] : 50,
                    'trigger' => in_array($_POST['alert_trigger'][$key], range(1, 10)) ? $_POST['alert_trigger'][$key] : 5,
                    'is_triggered' => 0,
                ];
            }

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name', 'target'];
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
                    'alerts' => $alerts,
                    'server_check_interval_seconds' => $_POST['server_check_interval_seconds'],
                ]);

                $notifications = json_encode($_POST['notifications'] ?? '');

                /* Database query */
                $server_monitor_id = db()->insert('server_monitors', [
                    'project_id' => $_POST['project_id'],
                    'user_id' => $this->user->user_id,
                    'name' => $_POST['name'],
                    'target' => $_POST['target'],
                    'settings' => $settings,
                    'notifications' => $notifications,
                    'datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(l('server_monitor_create.success_message'));

                redirect('server-monitor/' . $server_monitor_id);
            }

        }

        /* Set default values */
        $values = [
            'name' => $_POST['name'] ?? '',
            'target' => $_POST['target'] ?? '',
            'server_check_interval_seconds' => $_POST['server_check_interval_seconds'] ?? reset($this->user->plan_settings->server_monitors_check_intervals),
            'project_id' => $_POST['project_id'] ?? '',
            'notifications' => $_POST['notifications'] ?? [],
            'alerts' => $alerts ?? [],
        ];

        /* Prepare the view */
        $data = [
            'projects' => $projects,
            'notification_handlers' => $notification_handlers,
            'server_monitor_check_intervals' => $server_monitor_check_intervals,
            'values' => $values
        ];

        $view = new \Altum\View('server-monitor-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
