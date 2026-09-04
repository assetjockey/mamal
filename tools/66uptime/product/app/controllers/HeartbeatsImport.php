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

class HeartbeatsImport extends Controller {

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

        if(!empty($_POST)) {
            if(!isset($_FILES['file'])) {
                Alerts::add_error(l('global.error_message.empty_field'));
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            \Altum\Uploads::validate_upload('resources_csv', 'file', get_max_upload());

            $csv_array = array_map(function($line) {
                return str_getcsv($line, ',', '"', '\\');
            }, file($_FILES['file']['tmp_name']));

            if(!$csv_array || !is_array($csv_array)) {
                Alerts::add_error(l('global.error_message.invalid_file_type'));
            }

            $headers_array = $csv_array[0];
            unset($csv_array[0]);
            reset($csv_array);

            if(!Alerts::has_errors()) {
                /* Get available projects */
                $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

                /* Get available notification handlers */
                $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

                /* Count the successful inserts */
                $imported_count = 0;

                foreach($csv_array as $csv_row) {
                    /* Skip wrong lines */
                    if(count($headers_array) != count($csv_row)) continue;

                    /* Extract data */
                    $data = array_combine($headers_array, $csv_row);

                    /* Clean input */
                    $name = input_clean($data['name'] ?? '', 256);
                    $run_interval = isset($data['run_interval']) ? (int) $data['run_interval'] : 60;
                    $run_interval_type = in_array($data['run_interval_type'] ?? '', ['seconds', 'minutes', 'hours', 'days']) ? $data['run_interval_type'] : 'seconds';
                    $run_interval_grace = isset($data['run_interval_grace']) ? (int) $data['run_interval_grace'] : 0;
                    $run_interval_grace_type = in_array($data['run_interval_grace_type'] ?? '', ['seconds', 'minutes', 'hours', 'days']) ? $data['run_interval_grace_type'] : 'seconds';
                    $project_id = !empty($data['project_id']) && array_key_exists($data['project_id'], $projects) ? (int) $data['project_id'] : null;
                    $email_reports_is_enabled = isset($data['email_reports_is_enabled']) ? (int) $data['email_reports_is_enabled'] : 0;

                    /* Required field */
                    if(!$name) continue;

                    /* Notifications validation */
                    $is_ok_notifications = [];
                    if(!empty($data['is_ok_notifications'])) {
                        $is_ok_notifications_raw = explode(',', $data['is_ok_notifications']);
                        $is_ok_notifications_raw = array_map('trim', $is_ok_notifications_raw);

                        $is_ok_notifications = array_map(
                            'intval',
                            array_filter($is_ok_notifications_raw, function($notification_handler_id) use ($notification_handlers) {
                                return array_key_exists($notification_handler_id, $notification_handlers);
                            })
                        );

                        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
                            $is_ok_notifications = array_slice($is_ok_notifications, 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
                        }
                    }

                    /* Heartbeat setup */
                    $code = md5(time() . $name . $this->user->user_id . microtime());
                    $next_run_datetime = (new \DateTime())->modify('+5 years')->format('Y-m-d H:i:s');

                    $settings = json_encode([
                        'run_interval' => $run_interval,
                        'run_interval_type' => $run_interval_type,
                        'run_interval_grace' => $run_interval_grace,
                        'run_interval_grace_type' => $run_interval_grace_type,
                    ]);

                    $notifications = json_encode([
                        'is_ok' => $is_ok_notifications,
                    ]);

                    /* Is is_enabled */
                    $is_enabled = !empty($data['is_enabled']) ? (int) isset($data['is_enabled']) : 1;

                    /* Database insert */
                    db()->insert('heartbeats', [
                        'project_id' => $project_id,
                        'user_id' => $this->user->user_id,
                        'name' => $name,
                        'code' => $code,
                        'settings' => $settings,
                        'notifications' => $notifications,
                        'email_reports_is_enabled' => $email_reports_is_enabled,
                        'email_reports_last_datetime' => get_date(),
                        'next_run_datetime' => $next_run_datetime,
                        'is_enabled' => $is_enabled,
                        'datetime' => get_date(),
                    ]);

                    $imported_count++;

                    /* Check against limit */
                    if($this->user->plan_settings->heartbeats_limit != -1 && $total_rows + $imported_count >= $this->user->plan_settings->heartbeats_limit) {
                        break;
                    }
                }

                Alerts::add_success(sprintf(l('global.success_message.csv_imported'), '<strong>' . $imported_count . '</strong>', l('heartbeats.title')));

                redirect('heartbeats');
            }
        }

        $values = [];

        /* Prepare the view */
        $data = [
            'values' => $values
        ];

        $view = new \Altum\View('heartbeats-import/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
