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

use Altum\Response;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiMonitors extends Controller {
    use Apiable;

    public function index() {

        if(!settings()->monitors_heartbeats->monitors_is_enabled) {
            throw_404();
        }

        $this->verify_request();

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                /* Detect if we only need an object, or the whole list */
                if(isset($this->params[0])) {
                    $this->get();
                } else {
                    $this->get_all();
                }

                break;

            case 'POST':

                /* Detect what method to use */
                if(isset($this->params[0])) {
                    $this->patch();
                } else {
                    $this->post();
                }

                break;

            case 'DELETE':
                $this->delete();
                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'type', 'project_id', 'ping_servers_ids'], ['name', 'target'], ['monitor_id', 'last_datetime', 'datetime', 'last_check_datetime', 'name', 'uptime', 'average_response_time'], [], ['ping_servers_ids' => 'json_contains'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_check_datetime']));
        $filters->set_default_order_by($this->user->preferences->monitors_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `monitors` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/monitors?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `monitors`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->monitor_id,
                'user_id' => (int) $row->user_id,
                'project_id' => (int) $row->project_id,
                'name' => $row->name,
                'type' => $row->type,
                'target' => $row->target,
                'port' => (int) $row->port,
                'settings' => json_decode($row->settings),
                'ping_servers_ids' => json_decode($row->ping_servers_ids),
                'is_ok' => (int) $row->is_ok,
                'uptime' => (float) $row->uptime,
                'downtime' => (float) $row->downtime,
                'average_response_time' => (float) $row->average_response_time,
                'total_checks' => (int) $row->total_checks,
                'total_ok_checks' => (int) $row->total_ok_checks,
                'total_not_ok_checks' => (int) $row->total_not_ok_checks,
                'last_check_datetime' => $row->last_check_datetime,
                'notifications' => json_decode($row->notifications),
                'is_enabled' => (bool) $row->is_enabled,
                'datetime' => $row->datetime,
                'last_datetime' => $row->datetime,
            ];

            $data[] = $row;
        }

        /* Prepare the data */
        $meta = [
            'page' => $_GET['page'] ?? 1,
            'total_pages' => $paginator->getNumPages(),
            'results_per_page' => $filters->get_results_per_page(),
            'total_results' => (int) $total_rows,
        ];

        /* Prepare the pagination links */
        $others = ['links' => [
            'first' => $paginator->getPageUrl(1),
            'last' => $paginator->getNumPages() ? $paginator->getPageUrl($paginator->getNumPages()) : null,
            'next' => $paginator->getNextUrl(),
            'prev' => $paginator->getPrevUrl(),
            'self' => $paginator->getPageUrl($_GET['page'] ?? 1)
        ]];

        Response::jsonapi_success($data, $meta, 200, $others);
    }

    private function get() {

        $monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $monitor = db()->where('monitor_id', $monitor_id)->where('user_id', $this->user->user_id)->getOne('monitors');

        /* We haven't found the resource */
        if(!$monitor) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $monitor->monitor_id,
            'user_id' => (int) $monitor->user_id,
            'project_id' => (int) $monitor->project_id,
            'name' => $monitor->name,
            'type' => $monitor->type,
            'target' => $monitor->target,
            'port' => (int) $monitor->port,
            'settings' => json_decode($monitor->settings),
            'ping_servers_ids' => json_decode($monitor->ping_servers_ids),
            'is_ok' => (int) $monitor->is_ok,
            'uptime' => (float) $monitor->uptime,
            'downtime' => (float) $monitor->downtime,
            'average_response_time' => (float) $monitor->average_response_time,
            'total_checks' => (int) $monitor->total_checks,
            'total_ok_checks' => (int) $monitor->total_ok_checks,
            'total_not_ok_checks' => (int) $monitor->total_not_ok_checks,
            'last_check_datetime' => $monitor->last_check_datetime,
            'notifications' => json_decode($monitor->notifications),
            'is_enabled' => (bool) $monitor->is_enabled,
            'datetime' => $monitor->datetime,
            'last_datetime' => $monitor->last_datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('monitors', 'count(`monitor_id`)');

        if($this->user->plan_settings->monitors_limit != -1 && $total_rows >= $this->user->plan_settings->monitors_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
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

        /* Check for any errors */
        $required_fields = ['name', 'target'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['name'] = input_clean($_POST['name'], 64);
        $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['website', 'ping', 'port']) ? query_clean($_POST['type']) : 'website';
        $_POST['target'] = query_clean($_POST['target']);
        $_POST['port'] = isset($_POST['port']) ? (int) $_POST['port'] : 0;

        $_POST['ping_ipv'] = isset($_POST['ping_ipv']) && in_array($_POST['ping_ipv'], ['ipv4', 'ipv6']) ? $_POST['ping_ipv'] : 'ipv4';
        $_POST['check_interval_seconds'] = isset($_POST['check_interval_seconds']) && in_array($_POST['check_interval_seconds'], $this->user->plan_settings->monitors_check_intervals ?? []) ? (int) $_POST['check_interval_seconds'] : reset($this->user->plan_settings->monitors_check_intervals);
        $_POST['timeout_seconds'] = isset($_POST['timeout_seconds']) && array_key_exists($_POST['timeout_seconds'], $monitor_timeouts) ? (int) $_POST['timeout_seconds'] : 5;

        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
        $_POST['ping_servers_ids'] = array_map(
            'intval',
            array_filter($_POST['ping_servers_ids'] ?? [], function($ping_server_id) use($ping_servers) {
                return array_key_exists($ping_server_id, $ping_servers);
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
        $_POST['email_reports_is_enabled'] = (int) (bool) ($_POST['email_reports_is_enabled'] ?? 0);
        $_POST['cache_buster_is_enabled'] = (int) (bool) ($_POST['cache_buster_is_enabled'] ?? 0);
        $_POST['verify_ssl_is_enabled'] = (int) (bool) ($_POST['verify_ssl_is_enabled'] ?? 0);
        $_POST['is_enabled'] = (int) (bool) ($_POST['is_enabled'] ?? 1);

        /* Request */
        $_POST['follow_redirects'] = (int) ($_POST['follow_redirects'] ?? 1);
        $_POST['request_method'] = isset($_POST['request_method']) && in_array($_POST['request_method'], ['HEAD', 'GET', 'POST', 'PUT', 'PATCH']) ? query_clean($_POST['request_method']) : 'HEAD';
        $_POST['request_body'] = mb_substr(query_clean($_POST['request_body'] ?? null), 0, 10000);
        $_POST['request_basic_auth_username'] = mb_substr(query_clean($_POST['request_basic_auth_username'] ?? null), 0, 256);
        $_POST['request_basic_auth_password'] = mb_substr(query_clean($_POST['request_basic_auth_password'] ?? null), 0, 256);

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
        $_POST['response_body'] = input_clean($_POST['response_body'] ?? '', 10000);

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

        switch($_POST['type']) {
            case 'website':
                $ip = '';

                if(!filter_var($_POST['target'], FILTER_VALIDATE_URL)) {
                    $this->response_error(l('monitor.error_message.invalid_target_url'), 401);
                } else {
                    $host = parse_url($_POST['target'])['host'];
                    $ip = gethostbyname($host);
                }

                if(in_array(get_domain_from_url($_POST['target']), settings()->status_pages->blacklisted_domains)) {
                    $this->response_error(l('status_page.error_message.blacklisted_domain'));
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
                $this->response_error($exception->getMessage(), 401);
            }
        }

        /* Prepare */
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
            'user_id' => $this->user->user_id,
            'project_id' => $_POST['project_id'],
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
            'is_enabled' => $_POST['is_enabled'],
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('s_monitors?user_id=' . $this->user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $monitor_id
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('monitors', 'count(`monitor_id`)');

        if($this->user->plan_settings->monitors_limit != -1 && $total_rows > $this->user->plan_settings->monitors_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->monitors_limit, mb_strtolower(l('monitors.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $monitor = db()->where('monitor_id', $monitor_id)->where('user_id', $this->user->user_id)->getOne('monitors');

        /* We haven't found the resource */
        if(!$monitor) {
            $this->return_404();
        }
        $monitor->settings = json_decode($monitor->settings ?? '');
        $monitor->notifications = json_decode($monitor->notifications ?? '');
        $monitor->ping_servers_ids = json_decode($monitor->ping_servers_ids);

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Get available ping servers */
        $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();

        /* Monitors vars */
        $monitor_check_intervals = require APP_PATH . 'includes/monitor_check_intervals.php';
        $monitor_timeouts = require APP_PATH . 'includes/monitor_timeouts.php';

        $_POST['name'] = input_clean($_POST['name'] ?? $monitor->name, 64);
        $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['website', 'ping', 'port']) ? query_clean($_POST['type']) : $monitor->type;
        $_POST['target'] = query_clean($_POST['target'] ?? $monitor->target);
        $_POST['port'] = isset($_POST['port']) ? (int) $_POST['port'] : $monitor->port;

        $_POST['ping_ipv'] = isset($_POST['ping_ipv']) && in_array($_POST['ping_ipv'], ['ipv4', 'ipv6']) ? $_POST['ping_ipv'] : $monitor->settings->ping_ipv;
        $_POST['check_interval_seconds'] = isset($_POST['check_interval_seconds']) && in_array($_POST['check_interval_seconds'], $this->user->plan_settings->monitors_check_intervals ?? []) ? (int) $_POST['check_interval_seconds'] : $monitor->settings->check_interval_seconds;
        $_POST['timeout_seconds'] = isset($_POST['timeout_seconds']) && array_key_exists($_POST['timeout_seconds'], $monitor_timeouts) ? (int) $_POST['timeout_seconds'] : $monitor->settings->timeout_seconds;

        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
        $_POST['ping_servers_ids'] = array_map(
            'intval',
            array_filter($_POST['ping_servers_ids'] ?? $monitor->ping_servers_ids, function($ping_server_id) use($ping_servers) {
                return array_key_exists($ping_server_id, $ping_servers);
            })
        );
        $_POST['is_ok_notifications'] = array_map(
            'intval',
            array_filter($_POST['is_ok_notifications'] ?? $monitor->notifications->is_ok, function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
            $_POST['is_ok_notifications'] = array_slice($_POST['is_ok_notifications'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
        }
        $_POST['email_reports_is_enabled'] = (int) (bool) ($_POST['email_reports_is_enabled'] ?? $monitor->email_reports_is_enabled);
        $_POST['cache_buster_is_enabled'] = (int) (bool) ($_POST['cache_buster_is_enabled'] ?? $monitor->settings->cache_buster_is_enabled);
        $_POST['verify_ssl_is_enabled'] = (int) (bool) ($_POST['verify_ssl_is_enabled'] ?? $monitor->settings->verify_ssl_is_enabled);

        $_POST['is_enabled'] = (int) (bool) ($_POST['is_enabled'] ?? $monitor->is_enabled);

        /* Request */
        $_POST['follow_redirects'] = (int) ($_POST['follow_redirects'] ?? $monitor->settings->follow_redirects);
        $_POST['request_method'] = isset($_POST['request_method']) && in_array($_POST['request_method'], ['HEAD', 'GET', 'POST', 'PUT', 'PATCH']) ? query_clean($_POST['request_method']) : $monitor->settings->request_method;
        $_POST['request_body'] = mb_substr(query_clean($_POST['request_body'] ?? $monitor->settings->request_body), 0, 10000);
        $_POST['request_basic_auth_username'] = mb_substr(query_clean($_POST['request_basic_auth_username'] ?? $monitor->settings->request_basic_auth_username), 0, 256);
        $_POST['request_basic_auth_password'] = mb_substr(query_clean($_POST['request_basic_auth_password'] ?? $monitor->settings->request_basic_auth_password), 0, 256);

        $request_headers = $monitor->settings->request_headers;

        if(isset($_POST['request_header_name'])) {
            $request_headers = [];
            foreach($_POST['request_header_name'] as $key => $value) {
                if(empty(trim($value))) continue;

                $request_headers[] = [
                    'name' => mb_substr(query_clean($value), 0, 128),
                    'value' => mb_substr(trim(query_clean($_POST['request_header_value'][$key])), 0, 256),
                ];
            }
        }

        /* Response */
        $_POST['response_status_code'] = trim($_POST['response_status_code'] ?? implode(',', $monitor->settings->response_status_code));
        $_POST['response_status_code'] = explode(',', $_POST['response_status_code']);
        if(count($_POST['response_status_code'])) {
            $_POST['response_status_code'] = array_map(function ($response_status_code) {
                return $response_status_code < 0 || $response_status_code > 1000 ? 200 : (int) $response_status_code;
            }, $_POST['response_status_code']);
            $_POST['response_status_code'] = array_unique($_POST['response_status_code']);
        }
        $_POST['response_body'] = input_clean($_POST['response_body'] ?? $monitor->settings->response_body, 10000);

        if(!isset($_POST['response_header_name'])) {
            $_POST['response_header_name'] = [];
            $_POST['response_header_value'] = [];
        }

        $response_headers = $monitor->settings->response_headers;

        if(isset($_POST['response_header_name'])) {
            $response_headers = [];
            foreach($_POST['response_header_name'] as $key => $value) {
                if(empty(trim($value))) continue;

                $response_headers[] = [
                    'name' => input_clean($value, 128),
                    'value' => input_clean($_POST['response_header_value'][$key], 256),
                ];
            }
        }

        switch($_POST['type']) {
            case 'website':
                $ip = '';

                if(!filter_var($_POST['target'], FILTER_VALIDATE_URL)) {
                    $this->response_error(l('monitor.error_message.invalid_target_url'), 401);
                } else {
                    $host = parse_url($_POST['target'])['host'];
                    $ip = gethostbyname($host);
                }

                if(in_array(get_domain_from_url($_POST['target']), settings()->status_pages->blacklisted_domains)) {
                    $this->response_error(l('status_page.error_message.blacklisted_domain'));
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
                $this->response_error($exception->getMessage(), 401);
            }
        }

        /* Prepare */
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

        /* Next check recalculation */
        $next_check_datetime = $monitor->next_check_datetime;
        if(!$next_check_datetime || (new \DateTime($monitor->next_check_datetime)) > (new \DateTime())) {
            $next_check_datetime = (new \DateTime())->modify('+' . $_POST['check_interval_seconds'] . ' seconds')->format('Y-m-d H:i:s');
        }

        /* Database query */
        db()->where('monitor_id', $monitor->monitor_id)->update('monitors', [
            'user_id' => $this->user->user_id,
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'target' => $_POST['target'],
            'port' => $_POST['port'],
            'ping_servers_ids' => $ping_servers_ids,
            'settings' => $settings,
            'details' => $details,
            'notifications' => $notifications,
            'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
            'cache_buster_is_enabled' => $_POST['cache_buster_is_enabled'],
            'verify_ssl_is_enabled' => $_POST['verify_ssl_is_enabled'],
            'is_enabled' => $_POST['is_enabled'],
            'next_check_datetime' => $next_check_datetime,
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('monitor_id=' . $monitor_id);
        cache()->deleteItem('s_monitors?user_id=' . $this->user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $monitor->monitor_id
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $monitor = db()->where('monitor_id', $monitor_id)->where('user_id', $this->user->user_id)->getOne('monitors');

        /* We haven't found the resource */
        if(!$monitor) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('monitor_id', $monitor_id)->delete('monitors');

        /* Clear the cache */
        cache()->deleteItemsByTag('monitor_id=' . $monitor->monitor_id);

        http_response_code(200);
        die();

    }

}
