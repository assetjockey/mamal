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

class DomainNamesImport extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->domain_names_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.domain_names')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('domain-names');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `domain_names` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->domain_names_limit != -1 && $total_rows >= $this->user->plan_settings->domain_names_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('domain-names');
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

                /* Domain name timings */
                $domain_name_timings = require APP_PATH . 'includes/domain_name_timings.php';

                /* Count imported */
                $imported_count = 0;

                foreach($csv_array as $csv_row) {
                    if(count($headers_array) != count($csv_row)) continue;

                    $data = array_combine($headers_array, $csv_row);

                    /* Clean input */
                    $name = input_clean($data['name'] ?? '', 256);
                    $target = input_clean($data['target'] ?? '', 512);
                    $ssl_port = isset($data['ssl_port']) ? (int) $data['ssl_port'] : 443;
                    $project_id = !empty($data['project_id']) && array_key_exists($data['project_id'], $projects) ? (int) $data['project_id'] : null;

                    if(!$name || !$target) continue;

                    /* Validate and clean target */
                    if(filter_var($target, FILTER_VALIDATE_URL)) {
                        $target = get_domain_from_url($target);
                    }

                    if(in_array(get_domain_from_url($target), settings()->status_pages->blacklisted_domains)) {
                        continue;
                    }

                    /* Whois notifications */
                    $whois_notifications = [];
                    if(!empty($data['whois_notifications'])) {
                        $whois_notifications_raw = explode(',', $data['whois_notifications']);
                        $whois_notifications_raw = array_map('trim', $whois_notifications_raw);

                        $whois_notifications = array_map(
                            'intval',
                            array_filter($whois_notifications_raw, function($notification_handler_id) use ($notification_handlers) {
                                return array_key_exists($notification_handler_id, $notification_handlers);
                            })
                        );

                        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
                            $whois_notifications = array_slice($whois_notifications, 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
                        }
                    }

                    $whois_notifications_timing = array_key_exists($data['whois_notifications_timing'] ?? '', $domain_name_timings) ? $data['whois_notifications_timing'] : array_key_first($domain_name_timings);

                    $whois_notifications_json = json_encode([
                        'whois_notifications' => $whois_notifications,
                        'whois_notifications_timing' => $whois_notifications_timing,
                    ]);

                    /* SSL notifications */
                    $ssl_notifications = [];
                    if(!empty($data['ssl_notifications'])) {
                        $ssl_notifications_raw = explode(',', $data['ssl_notifications']);
                        $ssl_notifications_raw = array_map('trim', $ssl_notifications_raw);

                        $ssl_notifications = array_map(
                            'intval',
                            array_filter($ssl_notifications_raw, function($notification_handler_id) use ($notification_handlers) {
                                return array_key_exists($notification_handler_id, $notification_handlers);
                            })
                        );

                        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
                            $ssl_notifications = array_slice($ssl_notifications, 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
                        }
                    }

                    $ssl_notifications_timing = array_key_exists($data['ssl_notifications_timing'] ?? '', $domain_name_timings) ? $data['ssl_notifications_timing'] : array_key_first($domain_name_timings);

                    $ssl_notifications_json = json_encode([
                        'ssl_notifications' => $ssl_notifications,
                        'ssl_notifications_timing' => $ssl_notifications_timing,
                    ]);

                    /* Is is_enabled */
                    $is_enabled = !empty($data['is_enabled']) ? (int) isset($data['is_enabled']) : 1;

                    /* Database insert */
                    db()->insert('domain_names', [
                        'project_id' => $project_id,
                        'user_id' => $this->user->user_id,
                        'name' => $name,
                        'target' => $target,
                        'ssl_port' => $ssl_port,
                        'whois_notifications' => $whois_notifications_json,
                        'ssl_notifications' => $ssl_notifications_json,
                        'next_check_datetime' => get_date(),
                        'is_enabled' => $is_enabled,
                        'datetime' => get_date(),
                    ]);

                    $imported_count++;

                    /* Check against limit */
                    if($this->user->plan_settings->domain_names_limit != -1 && $total_rows + $imported_count >= $this->user->plan_settings->domain_names_limit) {
                        break;
                    }
                }

                Alerts::add_success(sprintf(l('global.success_message.csv_imported'), '<strong>' . $imported_count . '</strong>', l('domain_names.title')));

                redirect('domain-names');
            }
        }

        $values = [];

        /* Prepare the view */
        $data = [
            'values' => $values
        ];

        $view = new \Altum\View('domain-names-import/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
