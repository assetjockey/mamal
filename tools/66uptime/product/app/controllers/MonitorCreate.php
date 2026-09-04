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

class MonitorCreate extends Controller {

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

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Get available ping servers */
        $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();

        /* Monitors vars */
        $monitor_check_intervals = require APP_PATH . 'includes/monitor_check_intervals.php';
        $monitor_timeouts = require APP_PATH . 'includes/monitor_timeouts.php';

        if(!empty($_POST)) {
            $_POST['name'] = input_clean($_POST['name'], 64);
            $_POST['type'] = in_array($_POST['type'], ['website', 'ping', 'port']) ? query_clean($_POST['type']) : 'website';
            $_POST['target'] = query_clean($_POST['target']);
            $_POST['port'] = isset($_POST['port']) ? (int) $_POST['port'] : 0;

            $_POST['ping_ipv'] = isset($_POST['ping_ipv']) && in_array($_POST['ping_ipv'], ['ipv4', 'ipv6']) ? $_POST['ping_ipv'] : 'ipv4';
            $_POST['check_interval_seconds'] = in_array($_POST['check_interval_seconds'], $this->user->plan_settings->monitors_check_intervals ?? []) ? (int) $_POST['check_interval_seconds'] : reset($this->user->plan_settings->monitors_check_intervals);
            $_POST['timeout_seconds'] = array_key_exists($_POST['timeout_seconds'], $monitor_timeouts) ? (int) $_POST['timeout_seconds'] : 5;

            $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
            $_POST['ping_servers_ids'] = array_map(
                'intval',
                array_filter($_POST['ping_servers_ids'] ?? [], function($ping_server_id) use($ping_servers) {
                    return array_key_exists($ping_server_id, $ping_servers) && in_array($ping_server_id, $this->user->plan_settings->monitors_ping_servers);
                })
            );
            $_POST['is_ok_notifications'] = array_map(
                'intval',
                array_filter($_POST['is_ok_notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                    return array_key_exists($notification_handler_id, $notification_handlers);
                })
            );
            if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
                $_POST['is_ok_notifications'] = array_slice($_POST['is_ok_notifications'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
            }

            $_POST['email_reports_is_enabled'] = (int) isset($_POST['email_reports_is_enabled']);
            $_POST['cache_buster_is_enabled'] = (int) isset($_POST['cache_buster_is_enabled']);
            $_POST['verify_ssl_is_enabled'] = (int) isset($_POST['verify_ssl_is_enabled']);

            /* Request */
            $_POST['follow_redirects'] = (int) isset($_POST['follow_redirects']);
            $_POST['request_method'] = in_array($_POST['request_method'], ['HEAD', 'GET', 'POST', 'PUT', 'PATCH']) ? query_clean($_POST['request_method']) : settings()->monitors_heartbeats->monitors_default_request_method;
            $_POST['request_body'] = mb_substr(query_clean($_POST['request_body']), 0, 10000);
            $_POST['request_basic_auth_username'] = mb_substr(query_clean($_POST['request_basic_auth_username']), 0, 256);
            $_POST['request_basic_auth_password'] = mb_substr(query_clean($_POST['request_basic_auth_password']), 0, 256);

            if(!isset($_POST['request_header_name'])) {
                $_POST['request_header_name'] = [];
                $_POST['request_header_value'] = [];
            }

            $request_headers = [];
            foreach($_POST['request_header_name'] as $key => $value) {
                if(empty(trim($value))) continue;

                $request_headers[] = [
                    'name' => mb_substr(query_clean($value), 0, 128),
                    'value' => mb_substr(trim(query_clean($_POST['request_header_value'][$key])), 0, 256),
                ];
            }

            /* Response */
            $_POST['response_status_code'] = trim($_POST['response_status_code'] ?? 200);
            $_POST['response_status_code'] = explode(',', $_POST['response_status_code']);
            if(count($_POST['response_status_code'])) {
                $_POST['response_status_code'] = array_map(function ($response_status_code) {
                    return $response_status_code < 0 || $response_status_code > 1000 ? 200 : (int) $response_status_code;
                }, $_POST['response_status_code']);
                $_POST['response_status_code'] = array_unique($_POST['response_status_code']);
            }
            $_POST['response_body'] = input_clean($_POST['response_body'], 10000);

            if(!isset($_POST['response_header_name'])) {
                $_POST['response_header_name'] = [];
                $_POST['response_header_value'] = [];
            }

            $response_headers = [];
            foreach($_POST['response_header_name'] as $key => $value) {
                if(empty(trim($value))) continue;

                $response_headers[] = [
                    'name' => input_clean($value, 128),
                    'value' => input_clean($_POST['response_header_value'][$key], 256),
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

            switch($_POST['type']) {
                case 'website':
                    $ip = '';

                    if(!filter_var($_POST['target'], FILTER_VALIDATE_URL)) {
                        Alerts::add_field_error('target', l('monitor.error_message.invalid_target_url'));
                    } else {
                        $host = parse_url($_POST['target'])['host'];
                        $ip = gethostbyname($host);
                    }

                    if(in_array(get_domain_from_url($_POST['target']), settings()->status_pages->blacklisted_domains)) {
                        Alerts::add_field_error('target', l('status_page.error_message.blacklisted_domain'));
                    }
                    break;

                case 'ping':
                case 'port':

                    $ip = $_POST['target'];

                    if(filter_var($_POST['target'], FILTER_VALIDATE_DOMAIN)) {
                        $ip = gethostbyname($_POST['target']);
                    }
                    break;
            }

            /* Detect the location */
            try {
                $maxmind = (get_maxmind_reader_city())->get($ip);
            } catch(\Exception $exception) {
                if(in_array($_POST['type'], ['ping', 'port']) && $_POST['ping_ipv'] == 'ipv4') {
                    Alerts::add_error($exception->getMessage());
                }
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $ping_servers_ids = json_encode($_POST['ping_servers_ids']);
                $settings = json_encode([
                    'cache_buster_is_enabled' => $_POST['cache_buster_is_enabled'],
                    'verify_ssl_is_enabled' => $_POST['verify_ssl_is_enabled'],
                    'ping_ipv' => $_POST['ping_ipv'],
                    'check_interval_seconds' => $_POST['check_interval_seconds'],
                    'timeout_seconds' => $_POST['timeout_seconds'],
                    'follow_redirects' => $_POST['follow_redirects'],
                    'request_method' => $_POST['request_method'],
                    'request_body' => $_POST['request_body'],
                    'request_basic_auth_username' => $_POST['request_basic_auth_username'],
                    'request_basic_auth_password' => $_POST['request_basic_auth_password'],
                    'request_headers' => $request_headers,
                    'response_status_code' => $_POST['response_status_code'],
                    'response_body' => $_POST['response_body'],
                    'response_headers' => $response_headers,
                ]);

                /* Detect the location */
                $country_code = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;
                $city_name = isset($maxmind) && isset($maxmind['city']) ? $maxmind['city']['names']['en'] : null;
                $continent_name = isset($maxmind) && isset($maxmind['continent']) ? $maxmind['continent']['names']['en'] : null;

                $details = json_encode([
                    'country_code' => $country_code,
                    'city_name' => $city_name,
                    'continent_name' => $continent_name
                ]);

                $notifications = json_encode([
                    'is_ok' => $_POST['is_ok_notifications'],
                ]);

                /* Database query */
                $monitor_id = db()->insert('monitors', [
                    'project_id' => $_POST['project_id'],
                    'user_id' => $this->user->user_id,
                    'name' => $_POST['name'],
                    'type' => $_POST['type'],
                    'target' => $_POST['target'],
                    'port' => $_POST['port'],
                    'ping_servers_ids' => $ping_servers_ids,
                    'settings' => $settings,
                    'details' => $details,
                    'notifications' => $notifications,
                    'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
                    'email_reports_last_datetime' => get_date(),
                    'next_check_datetime' => get_date(),
                    'datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(l('monitor_create.success_message'));

                /* Clear the cache */
                cache()->deleteItem('s_monitors?user_id=' . $this->user->user_id);

                redirect('monitor/' . $monitor_id);
            }

        }

        /* Set default values */
        if(!isset($request_headers)) {
            $request_headers = [];
            $request_headers[] = [
                'name' => '',
                'value' => ''
            ];
        }

        if(!isset($response_headers)) {
            $response_headers = [];
            $response_headers[] = [
                'name' => '',
                'value' => ''
            ];
        }

        $values = [
            'name' => $_POST['name'] ?? '',
            'type' => $_POST['type'] ?? 'website',
            'target' => $_POST['target'] ?? '',
            'port' => $_POST['port'] ?? 80,
            'ping_ipv' => $_POST['ping_ipv'] ?? 'ipv4',
            'check_interval_seconds' => $_POST['check_interval_seconds'] ?? reset($this->user->plan_settings->monitors_check_intervals),
            'timeout_seconds' => $_POST['timeout_seconds'] ?? 5,
            'project_id' => $_POST['project_id'] ?? '',
            'ping_servers_ids' => $_POST['ping_servers_ids'] ?? $this->user->plan_settings->monitors_ping_servers ?? [],
            'is_ok_notifications' => $_POST['is_ok_notifications'] ?? [],
            'email_reports_is_enabled' => $_POST['email_reports_is_enabled'] ?? 0,
            'cache_buster_is_enabled' => $_POST['cache_buster_is_enabled'] ?? 0,
            'verify_ssl_is_enabled' => $_POST['verify_ssl_is_enabled'] ?? 1,
            'request_method' => $_POST['request_method'] ?? settings()->monitors_heartbeats->monitors_default_request_method,
            'follow_redirects' => $_POST['follow_redirects'] ?? true,
            'request_body' => $_POST['request_body'] ?? '',
            'request_basic_auth_username' => $_POST['request_basic_auth_username'] ?? '',
            'request_basic_auth_password' => $_POST['request_basic_auth_password'] ?? '',
            'request_headers' => json_decode(json_encode($request_headers)),
            'response_status_code' => $_POST['response_status_code'] ?? 200,
            'response_body' => $_POST['response_body'] ?? '',
            'response_headers' => json_decode(json_encode($response_headers)),
        ];

        /* Prepare the view */
        $data = [
            'ping_servers' => $ping_servers,
            'projects' => $projects,
            'notification_handlers' => $notification_handlers,
            'monitor_check_intervals' => $monitor_check_intervals,
            'monitor_timeouts' => $monitor_timeouts,
            'values' => $values
        ];

        $view = new \Altum\View('monitor-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
