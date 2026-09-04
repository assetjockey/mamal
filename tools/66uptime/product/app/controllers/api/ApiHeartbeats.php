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

class ApiHeartbeats extends Controller {
    use Apiable;

    public function index() {

        if(!settings()->monitors_heartbeats->heartbeats_is_enabled) {
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
        $filters = (new \Altum\Filters(['is_enabled', 'project_id'], ['name'], ['heartbeat_id', 'datetime', 'last_datetime', 'last_run_datetime', 'name', 'uptime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_run_datetime']));
        $filters->set_default_order_by($this->user->preferences->heartbeats_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `heartbeats` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/heartbeats?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `heartbeats`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->heartbeat_id,
                'user_id' => (int) $row->user_id,
                'project_id' => (int) $row->project_id,
                'name' => $row->name,
                'code' => $row->code,
                'settings' => json_decode($row->settings),
                'is_ok' => (int) $row->is_ok,
                'uptime' => (float) $row->uptime,
                'downtime' => (float) $row->downtime,
                'total_runs' => (int) $row->total_runs,
                'total_missed_runs' => (int) $row->total_missed_runs,
                'last_run_datetime' => $row->last_run_datetime,
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

        $heartbeat_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $heartbeat = db()->where('heartbeat_id', $heartbeat_id)->where('user_id', $this->user->user_id)->getOne('heartbeats');

        /* We haven't found the resource */
        if(!$heartbeat) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $heartbeat->heartbeat_id,
            'user_id' => (int) $heartbeat->user_id,
            'project_id' => (int) $heartbeat->project_id,
            'name' => $heartbeat->name,
            'code' => $heartbeat->code,
            'settings' => json_decode($heartbeat->settings),
            'is_ok' => (int) $heartbeat->is_ok,
            'uptime' => (float) $heartbeat->uptime,
            'downtime' => (float) $heartbeat->downtime,
            'total_runs' => (int) $heartbeat->total_runs,
            'total_missed_runs' => (int) $heartbeat->total_missed_runs,
            'last_run_datetime' => $heartbeat->last_run_datetime,
            'notifications' => json_decode($heartbeat->notifications),
            'is_enabled' => (bool) $heartbeat->is_enabled,
            'datetime' => $heartbeat->datetime,
            'last_datetime' => $heartbeat->last_datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('heartbeats', 'count(`heartbeat_id`)');

        if($this->user->plan_settings->heartbeats_limit != -1 && $total_rows >= $this->user->plan_settings->heartbeats_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Check for any errors */
        $required_fields = ['name'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['name'] = input_clean($_POST['name'], 64);
        $_POST['run_interval'] = (int) ($_POST['run_interval'] ?? 1);
        $_POST['run_interval_type'] = isset($_POST['run_interval_type']) && in_array($_POST['run_interval_type'], ['seconds', 'minutes', 'hours', 'days']) ? $_POST['run_interval_type'] : 'hours';
        $_POST['run_interval_grace'] = (int) ($_POST['run_interval_grace'] ?? 5);
        $_POST['run_interval_grace_type'] = isset($_POST['run_interval_grace_type']) && in_array($_POST['run_interval_grace_type'], ['seconds', 'minutes', 'hours', 'days']) ? $_POST['run_interval_grace_type'] : 'minutes';
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
        $_POST['is_ok_notifications'] = array_map(
            'intval',
            array_filter($_POST['is_ok_notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        $_POST['email_reports_is_enabled'] = (int) (bool) ($_POST['email_reports_is_enabled'] ?? 0);
        $_POST['is_enabled'] = (int) (bool) ($_POST['is_enabled'] ?? 1);

        /* Prepare */
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
            'user_id' => $this->user->user_id,
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'code' => $code,
            'settings' => $settings,
            'notifications' => $notifications,
            'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
            'email_reports_last_datetime' => get_date(),
            'next_run_datetime' => $next_run_datetime,
            'is_enabled' => $_POST['is_enabled'],
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('s_heartbeats?user_id=' . $this->user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $heartbeat_id
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('heartbeats', 'count(`heartbeat_id`)');

        if($this->user->plan_settings->heartbeats_limit != -1 && $total_rows > $this->user->plan_settings->heartbeats_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->heartbeats_limit, mb_strtolower(l('heartbeats.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $heartbeat_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $heartbeat = db()->where('heartbeat_id', $heartbeat_id)->where('user_id', $this->user->user_id)->getOne('heartbeats');

        /* We haven't found the resource */
        if(!$heartbeat) {
            $this->return_404();
        }
        $heartbeat->settings = json_decode($heartbeat->settings ?? '');
        $heartbeat->notifications = json_decode($heartbeat->notifications ?? '');

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        $_POST['name'] = input_clean($_POST['name'] ?? $heartbeat->name, 64);
        $_POST['run_interval'] = (int) ($_POST['run_interval'] ?? $heartbeat->settings->run_interval);
        $_POST['run_interval_type'] = isset($_POST['run_interval_type']) && in_array($_POST['run_interval_type'], ['seconds', 'minutes', 'hours', 'days']) ? $_POST['run_interval_type'] : $heartbeat->settings->run_interval_type;
        $_POST['run_interval_grace'] = (int) ($_POST['run_interval_grace'] ?? $heartbeat->settings->run_interval_grace);
        $_POST['run_interval_grace_type'] = isset($_POST['run_interval_grace_type']) && in_array($_POST['run_interval_grace_type'], ['seconds', 'minutes', 'hours', 'days']) ? $_POST['run_interval_grace_type'] : $heartbeat->settings->run_interval_grace_type;
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : $heartbeat->project_id;
        $_POST['is_ok_notifications'] = array_map(
            'intval',
            array_filter($_POST['is_ok_notifications'] ?? $heartbeat->notifications->is_ok, function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        $_POST['email_reports_is_enabled'] = (int) (bool) ($_POST['email_reports_is_enabled'] ?? $heartbeat->email_reports_is_enabled);
        $_POST['is_enabled'] = (int) (bool) ($_POST['is_enabled'] ?? $heartbeat->is_enabled);

        /* Prepare */
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
        db()->where('heartbeat_id', $heartbeat->heartbeat_id)->update('heartbeats', [
            'user_id' => $this->user->user_id,
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'settings' => $settings,
            'notifications' => $notifications,
            'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
            'is_enabled' => $_POST['is_enabled'],
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('s_heartbeats?user_id=' . $this->user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $heartbeat->heartbeat_id
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $heartbeat_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $heartbeat = db()->where('heartbeat_id', $heartbeat_id)->where('user_id', $this->user->user_id)->getOne('heartbeats');

        /* We haven't found the resource */
        if(!$heartbeat) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('heartbeat_id', $heartbeat_id)->delete('heartbeats');

        /* Clear the cache */
        cache()->deleteItemsByTag('heartbeat_id=' . $heartbeat->heartbeat_id);

        http_response_code(200);
        die();

    }

}
