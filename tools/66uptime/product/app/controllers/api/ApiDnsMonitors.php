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

class ApiDnsMonitors extends Controller {
    use Apiable;

    public function index() {

        if(!settings()->monitors_heartbeats->dns_monitors_is_enabled) {
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
        $filters = (new \Altum\Filters(['is_enabled', 'project_id'], ['name', 'target'], ['dns_monitor_id', 'total_checks', 'total_changes', 'last_datetime', 'datetime', 'last_check_datetime', 'name', 'last_change_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_check_datetime', 'last_change_datetime']));
        $filters->set_default_order_by($this->user->preferences->dns_monitors_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `dns_monitors` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/dns_monitors?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `dns_monitors`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->dns_monitor_id,
                'user_id' => (int) $row->user_id,
                'project_id' => (int) $row->project_id,
                'name' => $row->name,
                'target' => $row->target,
                'dns' => json_decode($row->dns ?? ''),
                'notifications' => json_decode($row->notifications),
                'settings' => json_decode($row->settings),
                'total_checks' => (int) $row->total_checks,
                'total_changes' => (int) $row->total_changes,
                'last_check_datetime' => $row->last_check_datetime,
                'next_check_datetime' => $row->next_check_datetime,
                'last_change_datetime' => $row->last_change_datetime,
                'is_enabled' => (bool) $row->is_enabled,
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

        $dns_monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $dns_monitor = db()->where('dns_monitor_id', $dns_monitor_id)->where('user_id', $this->user->user_id)->getOne('dns_monitors');

        /* We haven't found the resource */
        if(!$dns_monitor) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $dns_monitor->dns_monitor_id,
            'user_id' => (int) $dns_monitor->user_id,
            'project_id' => (int) $dns_monitor->project_id,
            'name' => $dns_monitor->name,
            'target' => $dns_monitor->target,
            'dns' => json_decode($dns_monitor->dns ?? ''),
            'notifications' => json_decode($dns_monitor->notifications),
            'settings' => json_decode($dns_monitor->settings),
            'total_checks' => (int) $dns_monitor->total_checks,
            'total_changes' => (int) $dns_monitor->total_changes,
            'last_check_datetime' => $dns_monitor->last_check_datetime,
            'next_check_datetime' => $dns_monitor->next_check_datetime,
            'last_change_datetime' => $dns_monitor->last_change_datetime,
            'is_enabled' => (bool) $dns_monitor->is_enabled,
            'datetime' => $dns_monitor->datetime,
            'last_datetime' => $dns_monitor->last_datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('dns_monitors', 'count(`dns_monitor_id`)');

        if($this->user->plan_settings->dns_monitors_limit != -1 && $total_rows >= $this->user->plan_settings->dns_monitors_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Monitors vars */
        $dns_monitor_check_intervals = require APP_PATH . 'includes/dns_monitor_check_intervals.php';
        $dns_types = require APP_PATH . 'includes/dns_monitor_types.php';

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
        $_POST['dns_check_interval_seconds'] = isset($_POST['dns_check_interval_seconds']) && in_array($_POST['dns_check_interval_seconds'], $this->user->plan_settings->dns_monitors_check_intervals ?? []) ? (int) $_POST['dns_check_interval_seconds'] : reset($this->user->plan_settings->dns_monitors_check_intervals);
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
        $_POST['notifications'] = array_map(
            'intval',
            array_filter($_POST['notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        $_POST['dns_types'] = array_filter($_POST['dns_types'] ?? array_keys($dns_types), function($dns_type) use($dns_types) {
            return array_key_exists($dns_type, $dns_types);
        });
        $_POST['is_enabled'] = (int) (bool) ($_POST['is_enabled'] ?? 1);

        if(filter_var($_POST['target'], FILTER_VALIDATE_URL)) {
            $_POST['target'] = get_domain_from_url($_POST['target']);
        }

        if(in_array(get_domain_from_url($_POST['target']), settings()->status_pages->blacklisted_domains)) {
            $this->response_error(l('status_page.error_message.blacklisted_domain'));
        }

        $settings = json_encode([
            'dns_check_interval_seconds' => $_POST['dns_check_interval_seconds'],
            'dns_types' => $_POST['dns_types'],
        ]);

        $notifications = json_encode($_POST['notifications'] ?? '');

        /* Database query */
        $dns_monitor_id = db()->insert('dns_monitors', [
            'user_id' => $this->user->user_id,
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'target' => $_POST['target'],
            'settings' => $settings,
            'notifications' => $notifications,
            'is_enabled' => $_POST['is_enabled'],
            'next_check_datetime' => get_date(),
            'datetime' => get_date(),
        ]);

        /* Prepare the data */
        $data = [
            'id' => $dns_monitor_id
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('dns_monitors', 'count(`dns_monitor_id`)');

        if($this->user->plan_settings->dns_monitors_limit != -1 && $total_rows > $this->user->plan_settings->dns_monitors_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->dns_monitors_limit, mb_strtolower(l('dns_monitors.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $dns_monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $dns_monitor = db()->where('dns_monitor_id', $dns_monitor_id)->where('user_id', $this->user->user_id)->getOne('dns_monitors');

        /* We haven't found the resource */
        if(!$dns_monitor) {
            $this->return_404();
        }

        $dns_monitor->settings = json_decode($dns_monitor->settings);
        $dns_monitor->notifications = json_decode($dns_monitor->notifications);

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Monitors vars */
        $dns_monitor_check_intervals = require APP_PATH . 'includes/dns_monitor_check_intervals.php';
        $dns_types = require APP_PATH . 'includes/dns_monitor_types.php';

        $_POST['name'] = input_clean($_POST['name'] ?? $dns_monitor->name, 64);
        $_POST['target'] = query_clean($_POST['target']?? $dns_monitor->target);
        $_POST['dns_check_interval_seconds'] = isset($_POST['dns_check_interval_seconds']) && in_array($_POST['dns_check_interval_seconds'], $this->user->plan_settings->dns_monitors_check_intervals ?? []) ? (int) $_POST['dns_check_interval_seconds'] : $dns_monitor->settings->dns_check_interval_seconds;
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : $dns_monitor->project_id;
        $_POST['notifications'] = array_map(
            'intval',
            array_filter($_POST['notifications'] ?? $dns_monitor->notifications, function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        $_POST['dns_types'] = array_filter($_POST['dns_types'] ?? $dns_monitor->settings->dns_types, function($dns_type) use($dns_types) {
            return array_key_exists($dns_type, $dns_types);
        });
        $_POST['is_enabled'] = (int) (bool) ($_POST['is_enabled'] ?? $dns_monitor->is_enabled);

        if(in_array(get_domain_from_url($_POST['target']), settings()->status_pages->blacklisted_domains)) {
            $this->response_error(l('status_page.error_message.blacklisted_domain'));
        }

        $settings = json_encode([
            'dns_check_interval_seconds' => $_POST['dns_check_interval_seconds'],
            'dns_types' => $_POST['dns_types'],
        ]);

        $notifications = json_encode($_POST['notifications'] ?? '');

        /* Next check recalculation */
        $next_check_datetime = $dns_monitor->next_check_datetime;
        if((new \DateTime($dns_monitor->next_check_datetime)) > (new \DateTime())) {
            $next_check_datetime = (new \DateTime())->modify('+' . $_POST['dns_check_interval_seconds'] . ' seconds')->format('Y-m-d H:i:s');
        }

        /* Database query */
        db()->where('dns_monitor_id', $dns_monitor->dns_monitor_id)->update('dns_monitors', [
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'target' => $_POST['target'],
            'settings' => $settings,
            'notifications' => $notifications,
            'is_enabled' => $_POST['is_enabled'],
            'next_check_datetime' => $next_check_datetime,
            'last_datetime' => get_date(),
        ]);

        /* Prepare the data */
        $data = [
            'id' => $dns_monitor->dns_monitor_id
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $dns_monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $dns_monitor = db()->where('dns_monitor_id', $dns_monitor_id)->where('user_id', $this->user->user_id)->getOne('dns_monitors');

        /* We haven't found the resource */
        if(!$dns_monitor) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('dns_monitor_id', $dns_monitor_id)->delete('dns_monitors');

        /* Clear the cache */
        cache()->deleteItemsByTag('dns_monitor_id=' . $dns_monitor->dns_monitor_id);

        http_response_code(200);
        die();

    }

}
