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

class ApiServerMonitors extends Controller {
    use Apiable;

    public function index() {

        if(!settings()->monitors_heartbeats->server_monitors_is_enabled) {
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

            case 'DELETE':
                $this->delete();
                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'project_id'], ['name', 'target'], ['server_monitor_id', 'last_datetime', 'datetime', 'last_log_datetime', 'name', 'total_logs', 'cpu_usage', 'ram_usage', 'disk_usage', 'uptime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_log_datetime']));
        $filters->set_default_order_by($this->user->preferences->server_monitors_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `server_monitors` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/server_monitors?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `server_monitors`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->server_monitor_id,
                'user_id' => (int) $row->user_id,
                'project_id' => (int) $row->project_id,
                'name' => $row->name,
                'target' => $row->target,
                'notifications' => json_decode($row->notifications ?? ''),
                'settings' => json_decode($row->settings ?? ''),
                'uptime' => (int) $row->uptime,
                'network_total_download' => (int) $row->network_total_download,
                'network_download' => (int) $row->network_download,
                'network_total_upload' => (int) $row->network_total_upload,
                'network_upload' => (int) $row->network_upload,
                'os_name' => $row->os_name,
                'os_version' => $row->os_version,
                'kernel_name' => $row->kernel_name,
                'kernel_version' => $row->kernel_version,
                'kernel_release' => $row->kernel_release,
                'cpu_architecture' => $row->cpu_architecture,
                'cpu_model' => $row->cpu_model,
                'cpu_cores' => (int) $row->cpu_cores,
                'cpu_frequency' => (int) $row->cpu_frequency,
                'cpu_usage' => (float) $row->cpu_usage,
                'ram_usage' => (float) $row->ram_usage,
                'ram_used' => (float) $row->ram_used,
                'ram_total' => (float) $row->ram_total,
                'disk_usage' => (float) $row->disk_usage,
                'disk_used' => (float) $row->disk_used,
                'disk_total' => (float) $row->disk_total,
                'cpu_load_1' => (float) $row->cpu_load_1,
                'cpu_load_5' => (float) $row->cpu_load_5,
                'cpu_load_15' => (float) $row->cpu_load_15,
                'is_enabled' => (bool) $row->is_enabled,
                'total_logs' => (int) $row->total_logs,
                'last_log_datetime' => $row->last_log_datetime,
                'datetime' => $row->datetime,
                'last_datetime' => $row->last_datetime,
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

        $server_monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $server_monitor = db()->where('server_monitor_id', $server_monitor_id)->where('user_id', $this->user->user_id)->getOne('server_monitors');

        /* We haven't found the resource */
        if(!$server_monitor) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $server_monitor->server_monitor_id,
            'user_id' => (int) $server_monitor->user_id,
            'project_id' => (int) $server_monitor->project_id,
            'name' => $server_monitor->name,
            'target' => $server_monitor->target,
            'notifications' => json_decode($server_monitor->notifications ?? ''),
            'settings' => json_decode($server_monitor->settings ?? ''),
            'uptime' => (int) $server_monitor->uptime,
            'network_total_download' => (int) $server_monitor->network_total_download,
            'network_download' => (int) $server_monitor->network_download,
            'network_total_upload' => (int) $server_monitor->network_total_upload,
            'network_upload' => (int) $server_monitor->network_upload,
            'os_name' => $server_monitor->os_name,
            'os_version' => $server_monitor->os_version,
            'kernel_name' => $server_monitor->kernel_name,
            'kernel_version' => $server_monitor->kernel_version,
            'kernel_release' => $server_monitor->kernel_release,
            'cpu_architecture' => $server_monitor->cpu_architecture,
            'cpu_model' => $server_monitor->cpu_model,
            'cpu_cores' => (int) $server_monitor->cpu_cores,
            'cpu_frequency' => (int) $server_monitor->cpu_frequency,
            'cpu_usage' => (float) $server_monitor->cpu_usage,
            'ram_usage' => (float) $server_monitor->ram_usage,
            'ram_used' => (float) $server_monitor->ram_used,
            'ram_total' => (float) $server_monitor->ram_total,
            'disk_usage' => (float) $server_monitor->disk_usage,
            'disk_used' => (float) $server_monitor->disk_used,
            'disk_total' => (float) $server_monitor->disk_total,
            'cpu_load_1' => (float) $server_monitor->cpu_load_1,
            'cpu_load_5' => (float) $server_monitor->cpu_load_5,
            'cpu_load_15' => (float) $server_monitor->cpu_load_15,
            'is_enabled' => (bool) $server_monitor->is_enabled,
            'total_logs' => (int) $server_monitor->total_logs,
            'last_log_datetime' => $server_monitor->last_log_datetime,
            'datetime' => $server_monitor->datetime,
            'last_datetime' => $server_monitor->last_datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('server_monitors', 'count(`server_monitor_id`)');

        if($this->user->plan_settings->server_monitors_limit != -1 && $total_rows >= $this->user->plan_settings->server_monitors_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Monitors vars */
        $server_monitor_check_intervals = require APP_PATH . 'includes/server_monitor_check_intervals.php';

        /* Check for any errors */
        $required_fields = ['name', 'target'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['name'] = input_clean($_POST['name'], 64);
        $_POST['target'] = query_clean($_POST['target']);
        $_POST['server_check_interval_seconds'] = isset($_POST['server_check_interval_seconds']) && in_array($_POST['server_check_interval_seconds'], $this->user->plan_settings->server_monitors_check_intervals ?? []) ? (int) $_POST['server_check_interval_seconds'] : reset($this->user->plan_settings->server_monitors_check_intervals);
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
        $_POST['notifications'] = array_map(
            'intval',
            array_filter($_POST['notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        $_POST['is_enabled'] = (int) (bool) ($_POST['is_enabled'] ?? 1);

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

        $settings = json_encode([
            'alerts' => $alerts,
            'server_check_interval_seconds' => $_POST['server_check_interval_seconds'],
        ]);

        $notifications = json_encode($_POST['notifications'] ?? '');

        /* Database query */
        $server_monitor_id = db()->insert('server_monitors', [
            'user_id' => $this->user->user_id,
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'target' => $_POST['target'],
            'settings' => $settings,
            'notifications' => $notifications,
            'is_enabled' => $_POST['is_enabled'],
            'datetime' => get_date(),
        ]);

        /* Prepare the data */
        $data = [
            'id' => $server_monitor_id
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('server_monitors', 'count(`server_monitor_id`)');

        if($this->user->plan_settings->server_monitors_limit != -1 && $total_rows > $this->user->plan_settings->server_monitors_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->server_monitors_limit, mb_strtolower(l('server_monitors.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $server_monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $server_monitor = db()->where('server_monitor_id', $server_monitor_id)->where('user_id', $this->user->user_id)->getOne('server_monitors');

        /* We haven't found the resource */
        if(!$server_monitor) {
            $this->return_404();
        }

        $server_monitor->settings = json_decode($server_monitor->settings);
        $server_monitor->notifications = json_decode($server_monitor->notifications);

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Monitors vars */
        $server_monitor_check_intervals = require APP_PATH . 'includes/server_monitor_check_intervals.php';

        $_POST['name'] = input_clean($_POST['name'] ?? $server_monitor->name, 64);
        $_POST['target'] = query_clean($_POST['target']?? $server_monitor->target);
        $_POST['server_check_interval_seconds'] = isset($_POST['server_check_interval_seconds']) && in_array($_POST['server_check_interval_seconds'], $this->user->plan_settings->server_monitors_check_intervals ?? []) ? (int) $_POST['server_check_interval_seconds'] : $server_monitor->settings->server_check_interval_seconds;
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : $server_monitor->project_id;
        $_POST['notifications'] = array_map(
            'intval',
            array_filter($_POST['notifications'] ?? $server_monitor->notifications, function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        $_POST['is_enabled'] = (int) (bool) ($_POST['is_enabled'] ?? $server_monitor->is_enabled);

        /* Alerts */
        if(!isset($_POST['alert_metric'])) {
            $_POST['alert_metric'] = [];
            $_POST['alert_rule'] = [];
            $_POST['alert_value'] = [];
            $_POST['alert_trigger'] = [];
        }

        $alerts = $server_monitor->settings->alerts;

        foreach($_POST['alert_metric'] as $key => $value) {
            if(empty(trim($value))) {
                unset($alerts[$key]);
            } else {
                $alerts[$key] = [
                    'metric' => in_array($value, ['cpu_usage', 'disk_usage', 'ram_usage']) ? $value : 'cpu_usage',
                    'rule' => in_array($_POST['alert_rule'][$key], ['is_higher', 'is_lower']) ? $_POST['alert_rule'][$key] : 'is_higher',
                    'value' => in_array($_POST['alert_value'][$key], range(1, 99)) ? $_POST['alert_value'][$key] : 50,
                    'trigger' => in_array($_POST['alert_trigger'][$key], range(1, 10)) ? $_POST['alert_trigger'][$key] : 5,
                    'is_triggered' => 0,
                ];
            }
        }

        $settings = json_encode([
            'alerts' => $alerts,
            'server_check_interval_seconds' => $_POST['server_check_interval_seconds'],
        ]);

        $notifications = json_encode($_POST['notifications'] ?? '');

        /* Database query */
        db()->where('server_monitor_id', $server_monitor->server_monitor_id)->update('server_monitors', [
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'target' => $_POST['target'],
            'settings' => $settings,
            'notifications' => $notifications,
            'is_enabled' => $_POST['is_enabled'],
            'last_datetime' => get_date(),
        ]);

        /* Prepare the data */
        $data = [
            'id' => $server_monitor->server_monitor_id
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $server_monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $server_monitor = db()->where('server_monitor_id', $server_monitor_id)->where('user_id', $this->user->user_id)->getOne('server_monitors');

        /* We haven't found the resource */
        if(!$server_monitor) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('server_monitor_id', $server_monitor_id)->delete('server_monitors');

        /* Clear the cache */
        cache()->deleteItemsByTag('server_monitor_id=' . $server_monitor->server_monitor_id);
        cache()->deleteItem('server_monitor?server_monitor_id=' . $server_monitor->server_monitor_id);

        http_response_code(200);
        die();

    }

}
