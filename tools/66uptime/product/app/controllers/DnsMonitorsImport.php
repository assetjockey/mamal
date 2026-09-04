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

class DnsMonitorsImport extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->dns_monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.monitors')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('dns-monitors');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `dns_monitors` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->dns_monitors_limit != -1 && $total_rows >= $this->user->plan_settings->dns_monitors_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('dns-monitors');
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
                /* Count the successful inserts */
                $imported_count = 0;

                /* Get available projects */
                $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

                /* Get available notification handlers */
                $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

                /* Monitors vars */
                $dns_types = require APP_PATH . 'includes/dns_monitor_types.php';

                foreach($csv_array as $csv_row) {
                    /* Skip wrong lines */
                    if(count($headers_array) != count($csv_row)) continue;

                    /* Extract data */
                    $data = array_combine($headers_array, $csv_row);

                    /* Start to work the data */
                    $name = query_clean($data['name'] ?? '');
                    $target = query_clean($data['target'] ?? '');

                    /* Required */
                    if(!$name || !$target) continue;

                    if(filter_var($target, FILTER_VALIDATE_URL)) {
                        $target = get_domain_from_url($target);
                    }
                    if(in_array(get_domain_from_url($target), settings()->status_pages->blacklisted_domains)) {
                        continue;
                    }

                    $project_id = !empty($data['project_id']) && array_key_exists($data['project_id'], $projects) ? (int) $data['project_id'] : null;

                    $dns_monitors_check_intervals = $this->user->plan_settings->dns_monitors_check_intervals ?? [];
                    $dns_check_interval_seconds = isset($data['dns_check_interval_seconds']) && in_array((int) $data['dns_check_interval_seconds'], $dns_monitors_check_intervals) ? (int) $data['dns_check_interval_seconds'] : reset($dns_monitors_check_intervals);

                    $dns_types_row = isset($data['dns_types']) && $data['dns_types'] !== '' ? explode(',', $data['dns_types']) : [];
                    $dns_types_row = array_filter($dns_types_row, function($dns_type) use($dns_types) {
                        return array_key_exists($dns_type, $dns_types);
                    });

                    /* Notifications validation */
                    $notifications = [];
                    if(!empty($data['notifications'])) {
                        $notifications_raw = explode(',', $data['notifications']);
                        $notifications_raw = array_map('trim', $notifications_raw);

                        $notifications = array_map(
                            'intval',
                            array_filter($notifications_raw, function($notification_handler_id) use ($notification_handlers) {
                                return array_key_exists($notification_handler_id, $notification_handlers);
                            })
                        );

                        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
                            $notifications = array_slice($notifications, 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
                        }
                    }

                    /* Is is_enabled */
                    $is_enabled = !empty($data['is_enabled']) ? (int) isset($data['is_enabled']) : 1;

                    $settings = json_encode([
                        'dns_check_interval_seconds' => $dns_check_interval_seconds,
                        'dns_types' => $dns_types_row,
                    ]);

                    $notifications = json_encode($notifications);

                    db()->insert('dns_monitors', [
                        'project_id' => $project_id,
                        'user_id' => $this->user->user_id,
                        'name' => $name,
                        'target' => $target,
                        'settings' => $settings,
                        'notifications' => $notifications,
                        'is_enabled' => $is_enabled,
                        'next_check_datetime' => get_date(),
                        'datetime' => get_date(),
                    ]);

                    $imported_count++;

                    /* Check against limit */
                    if($this->user->plan_settings->dns_monitors_limit != -1 && $total_rows + $imported_count >= $this->user->plan_settings->dns_monitors_limit) {
                        break;
                    }
                }

                Alerts::add_success(sprintf(l('global.success_message.csv_imported'), '<strong>' . $imported_count . '</strong>', l('dns_monitors.title')));

                cache()->deleteItem('s_dns_monitors?user_id=' . $this->user->user_id);

                redirect('dns-monitors');
            }
        }

        $values = [];

        /* Prepare the view */
        $data = [
            'values' => $values
        ];

        $view = new \Altum\View('dns-monitors-import/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
