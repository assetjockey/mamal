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

class MonitorsImport extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.monitors')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('monitors');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `monitors` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->monitors_limit != -1 && $total_rows >= $this->user->plan_settings->monitors_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('monitors');
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

                /* Get available ping servers */
                $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();

                /* Monitors vars */
                $monitor_timeouts = require APP_PATH . 'includes/monitor_timeouts.php';

                /* Count the successful inserts */
                $imported_count = 0;

                foreach($csv_array as $csv_row) {
                    /* Skip wrong lines */
                    if(count($headers_array) != count($csv_row)) continue;

                    /* Extract data */
                    $data = array_combine($headers_array, $csv_row);

                    /* Start to work the data */
                    $name = input_clean($data['name'] ?? '', 256);
                    $type = in_array($data['type'] ?? '', ['website', 'ping', 'port']) ? $data['type'] : 'website';
                    $target = input_clean($data['target'] ?? '', 512);

                    /* Required */
                    if(!$name || !$target) continue;

                    $port = isset($data['port']) ? (int) $data['port'] : 0;
                    $project_id = !empty($data['project_id']) && array_key_exists($data['project_id'], $projects) ? (int) $data['project_id'] : null;

                    $ping_ipv = in_array($data['ping_ipv'] ?? '', ['ipv4', 'ipv6']) ? $data['ping_ipv'] : 'ipv4';
                    $check_interval_seconds = isset($data['check_interval_seconds']) ? (int) $data['check_interval_seconds'] : reset($this->user->plan_settings->monitors_check_intervals);
                    $timeout_seconds = isset($data['timeout_seconds']) && array_key_exists($data['timeout_seconds'], $monitor_timeouts) ? (int) $data['timeout_seconds'] : 5;

                    $cache_buster_is_enabled = isset($data['cache_buster_is_enabled']) ? (int) $data['cache_buster_is_enabled'] : 0;
                    $verify_ssl_is_enabled = isset($data['verify_ssl_is_enabled']) ? (int) $data['verify_ssl_is_enabled'] : 0;
                    $follow_redirects = isset($data['follow_redirects']) ? (int) $data['follow_redirects'] : 0;
                    $request_method = in_array($data['request_method'] ?? '', ['HEAD','GET','POST','PUT','PATCH']) ? $data['request_method'] : 'GET';
                    $request_body = mb_substr(input_clean($data['request_body'] ?? '', 10000), 0, 10000);

                    /* Basic auth */
                    $request_basic_auth_username = mb_substr(input_clean($data['request_basic_auth_username'] ?? '', 256), 0, 256);
                    $request_basic_auth_password = mb_substr(input_clean($data['request_basic_auth_password'] ?? '', 256), 0, 256);

                    /* Request Headers */
                    $request_headers = [];
                    $response_headers = [];
                    foreach($data as $key => $value) {
                        if(preg_match('/^request_header_name\[(.*?)\]$/', $key, $m) && !empty(trim($value))) {
                            $index = $m[1];
                            $header_value = $data["request_header_value[$index]"] ?? '';
                            $request_headers[] = [
                                'name' => mb_substr(input_clean($value), 0, 128),
                                'value' => mb_substr(input_clean($header_value), 0, 256)
                            ];
                        }
                        if(preg_match('/^response_header_name\[(.*?)\]$/', $key, $m) && !empty(trim($value))) {
                            $index = $m[1];
                            $header_value = $data["response_header_value[$index]"] ?? '';
                            $response_headers[] = [
                                'name' => mb_substr(input_clean($value), 0, 128),
                                'value' => mb_substr(input_clean($header_value), 0, 256)
                            ];
                        }
                    }

                    $response_status_code = isset($data['response_status_code']) ? explode(',', $data['response_status_code']) : [200];
                    $response_status_code = array_map(function($c){ return (int)trim($c); }, $response_status_code);
                    $response_status_code = array_filter($response_status_code, fn($c) => $c > 0 && $c < 1000);
                    if(empty($response_status_code)) $response_status_code = [200];
                    $response_body = input_clean($data['response_body'] ?? '', 10000);

                    $email_reports_is_enabled = isset($data['email_reports_is_enabled']) ? (int) $data['email_reports_is_enabled'] : 0;

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

                    /* Ping servers validation */
                    $ping_servers_ids = [];
                    if(!empty($data['ping_servers_ids'])) {
                        $ping_servers_ids_raw = explode(',', $data['ping_servers_ids']);
                        $ping_servers_ids_raw = array_map('trim', $ping_servers_ids_raw);

                        $ping_servers_ids = array_map(
                            'intval',
                            array_filter($ping_servers_ids_raw, function($ping_server_id) use ($ping_servers) {
                                return array_key_exists($ping_server_id, $ping_servers) && in_array($ping_server_id, $this->user->plan_settings->monitors_ping_servers);
                            })
                        );
                    }

                    /* IP detection */
                    $ip = '';
                    switch($type) {
                        case 'website':
                            if(!filter_var($target, FILTER_VALIDATE_URL)) {
                                continue 2;
                            }

                            $host = parse_url($target)['host'];
                            $ip = gethostbyname($host);

                            if(in_array(get_domain_from_url($target), settings()->status_pages->blacklisted_domains)) {
                                continue 2;
                            }

                            break;
                        case 'ping':
                        case 'port':
                            $ip = filter_var($target, FILTER_VALIDATE_DOMAIN) ? gethostbyname($target) : $target;
                            break;
                    }


                    /* Detect location from IP  */
                    try {
                        $maxmind = (get_maxmind_reader_city())->get($ip);
                    } catch(\Exception $exception) {
                        $maxmind = null;

                        if(in_array($type, ['ping', 'port']) && $ping_ipv == 'ipv4') {
                            continue;
                        }
                    }

                    $country_code = $maxmind['country']['iso_code'] ?? null;
                    $city_name = $maxmind['city']['names']['en'] ?? null;
                    $continent_name = $maxmind['continent']['names']['en'] ?? null;

                    /* Is is_enabled */
                    $is_enabled = !empty($data['is_enabled']) ? (int) isset($data['is_enabled']) : 1;

                    $settings = json_encode([
                        'cache_buster_is_enabled' => $cache_buster_is_enabled,
                        'verify_ssl_is_enabled' => $verify_ssl_is_enabled,
                        'ping_ipv' => $ping_ipv,
                        'check_interval_seconds' => $check_interval_seconds,
                        'timeout_seconds' => $timeout_seconds,
                        'follow_redirects' => $follow_redirects,
                        'request_method' => $request_method,
                        'request_body' => $request_body,
                        'request_basic_auth_username' => $request_basic_auth_username,
                        'request_basic_auth_password' => $request_basic_auth_password,
                        'request_headers' => $request_headers,
                        'response_status_code' => $response_status_code,
                        'response_body' => $response_body,
                        'response_headers' => $response_headers,
                    ]);

                    $details = json_encode([
                        'country_code' => $country_code,
                        'city_name' => $city_name,
                        'continent_name' => $continent_name
                    ]);

                    $notifications = json_encode(['is_ok' => $is_ok_notifications]);

                    db()->insert('monitors', [
                        'project_id' => $project_id,
                        'user_id' => $this->user->user_id,
                        'name' => $name,
                        'type' => $type,
                        'target' => $target,
                        'port' => $port,
                        'ping_servers_ids' => json_encode($ping_servers_ids),
                        'settings' => $settings,
                        'details' => $details,
                        'notifications' => $notifications,
                        'email_reports_is_enabled' => $email_reports_is_enabled,
                        'email_reports_last_datetime' => get_date(),
                        'is_enabled' => $is_enabled,
                        'next_check_datetime' => get_date(),
                        'datetime' => get_date(),
                    ]);

                    $imported_count++;

                    /* Check against limit */
                    if($this->user->plan_settings->monitors_limit != -1 && $total_rows + $imported_count >= $this->user->plan_settings->monitors_limit) {
                        break;
                    }
                }

                Alerts::add_success(sprintf(l('global.success_message.csv_imported'), '<strong>' . $imported_count . '</strong>', l('monitors.title')));

                cache()->deleteItem('s_monitors?user_id=' . $this->user->user_id);

                redirect('monitors');
            }
        }

        $values = [];

        /* Prepare the view */
        $data = [
            'values' => $values
        ];

        $view = new \Altum\View('monitors-import/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
