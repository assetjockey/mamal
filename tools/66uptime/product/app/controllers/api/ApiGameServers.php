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

class ApiGameServers extends Controller {
    use Apiable;

    public function index() {

        if(!settings()->monitors_heartbeats->game_servers_is_enabled) {
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
        $filters = (new \Altum\Filters(['is_enabled', 'type', 'project_id', 'ping_servers_ids'], ['name', 'target'], ['game_server_id', 'last_datetime', 'datetime', 'last_check_datetime', 'name', 'uptime', 'average_response_time'], [], ['ping_servers_ids' => 'json_contains'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_check_datetime']));
        $filters->set_default_order_by($this->user->preferences->game_servers_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `game_servers` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/game_servers?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `game_servers`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->game_server_id,
                'user_id' => (int) $row->user_id,
                'project_id' => (int) $row->project_id,
                'name' => $row->name,
                'type' => $row->type,
                'target' => $row->target,
                'port' => (int) $row->port,
                'query_port' => (int) $row->query_port,
                'settings' => json_decode($row->settings),
                'online_players' => (int) $row->online_players,
                'maximum_online_players' => (int) $row->maximum_online_players,
                'details' => json_decode($row->details),
                'uptime' => (float) $row->uptime,
                'downtime' => (float) $row->downtime,
                'average_response_time' => (float) $row->average_response_time,
                'average_online_players' => (float) $row->average_online_players,
                'total_checks' => (int) $row->total_checks,
                'total_ok_checks' => (int) $row->total_ok_checks,
                'total_not_ok_checks' => (int) $row->total_not_ok_checks,
                'last_check_datetime' => $row->last_check_datetime,
                'next_check_datetime' => $row->next_check_datetime,
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

        $game_server_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $game_server = db()->where('game_server_id', $game_server_id)->where('user_id', $this->user->user_id)->getOne('game_servers');

        /* We haven't found the resource */
        if(!$game_server) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $game_server->game_server_id,
            'user_id' => (int) $game_server->user_id,
            'project_id' => (int) $game_server->project_id,
            'name' => $game_server->name,
            'type' => $game_server->type,
            'target' => $game_server->target,
            'port' => (int) $game_server->port,
            'query_port' => (int) $game_server->query_port,
            'settings' => json_decode($game_server->settings),
            'online_players' => (int) $game_server->online_players,
            'maximum_online_players' => (int) $game_server->maximum_online_players,
            'details' => json_decode($game_server->details),
            'uptime' => (float) $game_server->uptime,
            'downtime' => (float) $game_server->downtime,
            'average_response_time' => (float) $game_server->average_response_time,
            'average_online_players' => (float) $game_server->average_online_players,
            'total_checks' => (int) $game_server->total_checks,
            'total_ok_checks' => (int) $game_server->total_ok_checks,
            'total_not_ok_checks' => (int) $game_server->total_not_ok_checks,
            'last_check_datetime' => $game_server->last_check_datetime,
            'next_check_datetime' => $game_server->next_check_datetime,
            'is_enabled' => (bool) $game_server->is_enabled,
            'datetime' => $game_server->datetime,
            'last_datetime' => $game_server->last_datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('game_servers', 'count(`game_server_id`)');

        if($this->user->plan_settings->game_servers_limit != -1 && $total_rows >= $this->user->plan_settings->game_servers_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Monitors vars */
        $game_server_check_intervals = require APP_PATH . 'includes/game_server_check_intervals.php';
        $game_server_types = require APP_PATH . 'includes/game_server_types.php';
        $monitor_timeouts = require APP_PATH . 'includes/monitor_timeouts.php';

        /* Check for any errors */
        $required_fields = ['name', 'target', 'port'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['name'] = input_clean($_POST['name'], 64);
        $_POST['target'] = input_clean($_POST['target']);
        $_POST['port'] = isset($_POST['port']) ? (int) $_POST['port'] : 0;
        $_POST['query_port'] = isset($_POST['query_port']) ? (int) $_POST['query_port'] : $_POST['port'];
        $_POST['type'] = isset($_POST['type']) && array_key_exists($_POST['type'], $game_server_types) ? $_POST['type'] : array_key_first($game_server_types);
        $_POST['check_interval_seconds'] = isset($_POST['check_interval_seconds']) && in_array($_POST['check_interval_seconds'], $this->user->plan_settings->game_servers_check_intervals ?? []) ? (int) $_POST['check_interval_seconds'] : reset($this->user->plan_settings->game_servers_check_intervals);
        $_POST['timeout_seconds'] = isset($_POST['timeout_seconds']) && array_key_exists($_POST['timeout_seconds'], $monitor_timeouts) ? (int) $_POST['timeout_seconds'] : 5;
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
        $_POST['is_enabled'] = (int) (bool) ($_POST['is_enabled'] ?? 1);

        $settings = [
            'check_interval_seconds' => $_POST['check_interval_seconds'],
            'timeout_seconds' => $_POST['timeout_seconds'],
        ];

        /* Database query */
        $game_server_id = db()->insert('game_servers', [
            'user_id' => $this->user->user_id,
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'target' => $_POST['target'],
            'port' => $_POST['port'],
            'query_port' => $_POST['query_port'],
            'settings' => json_encode($settings),
            'is_enabled' => $_POST['is_enabled'],
            'next_check_datetime' => get_date(),
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('s_game_servers?user_id=' . $this->user->user_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $game_server_id,
            'user_id' => (int) $this->user->user_id,
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'target' => $_POST['target'],
            'port' => $_POST['port'],
            'query_port' => $_POST['query_port'],
            'settings' => $settings,
            'online_players' => (int) 0,
            'maximum_online_players' => (int) 0,
            'details' => [],
            'uptime' => (float) 100,
            'downtime' => (float) 0,
            'average_response_time' => (float) 0,
            'average_online_players' => (float) 0,
            'total_checks' => (int) 0,
            'total_ok_checks' => (int) 0,
            'total_not_ok_checks' => (int) 0,
            'last_check_datetime' => null,
            'next_check_datetime' => get_date(),
            'is_enabled' => (bool) $_POST['is_enabled'],
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('game_servers', 'count(`game_server_id`)');

        if($this->user->plan_settings->game_servers_limit != -1 && $total_rows > $this->user->plan_settings->game_servers_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->game_servers_limit, mb_strtolower(l('game_servers.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $game_server_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $game_server = db()->where('game_server_id', $game_server_id)->where('user_id', $this->user->user_id)->getOne('game_servers');

        /* We haven't found the resource */
        if(!$game_server) {
            $this->return_404();
        }
        $game_server->settings = json_decode($game_server->settings ?? '');
        $game_server->details = json_decode($game_server->details ?? '');

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Monitors vars */
        $game_server_check_intervals = require APP_PATH . 'includes/game_server_check_intervals.php';
        $game_server_types = require APP_PATH . 'includes/game_server_types.php';
        $monitor_timeouts = require APP_PATH . 'includes/monitor_timeouts.php';

        $_POST['name'] = input_clean($_POST['name'] ?? $game_server->name, 64);
        $_POST['type'] = isset($_POST['type']) && array_key_exists($_POST['type'], $game_server_types) ? $_POST['type'] : $game_server->type;
        $_POST['target'] = query_clean($_POST['target'] ?? $game_server->target);
        $_POST['port'] = isset($_POST['port']) ? (int) $_POST['port'] : $game_server->port;
        $_POST['query_port'] = isset($_POST['query_port']) ? (int) $_POST['query_port'] : $game_server->query_port;

        $_POST['check_interval_seconds'] = isset($_POST['check_interval_seconds']) && in_array($_POST['check_interval_seconds'], $this->user->plan_settings->game_servers_check_intervals ?? []) ? (int) $_POST['check_interval_seconds'] : $game_server->settings->check_interval_seconds;
        $_POST['timeout_seconds'] = isset($_POST['timeout_seconds']) && array_key_exists($_POST['timeout_seconds'], $monitor_timeouts) ? (int) $_POST['timeout_seconds'] : $game_server->settings->timeout_seconds;

        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
        $_POST['is_enabled'] = (int) (bool) ($_POST['is_enabled'] ?? $game_server->is_enabled);

        $settings = [
            'check_interval_seconds' => $_POST['check_interval_seconds'],
            'timeout_seconds' => $_POST['timeout_seconds'],
        ];

        /* Next check recalculation */
        $next_check_datetime = $game_server->next_check_datetime;
        if(!$next_check_datetime || (new \DateTime($game_server->next_check_datetime)) > (new \DateTime())) {
            $next_check_datetime = (new \DateTime())->modify('+' . $_POST['check_interval_seconds'] . ' seconds')->format('Y-m-d H:i:s');
        }

        /* Database query */
        db()->where('game_server_id', $game_server->game_server_id)->update('game_servers', [
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'target' => $_POST['target'],
            'port' => $_POST['port'],
            'query_port' => $_POST['query_port'],
            'settings' => json_encode($settings),
            'is_enabled' => $_POST['is_enabled'],
            'next_check_datetime' => $next_check_datetime,
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('game_server_id=' . $game_server_id);

        /* Prepare the data */
        $data = [
            'id' => $game_server->game_server_id,
            'user_id' => (int) $game_server->user_id,
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'target' => $_POST['target'],
            'port' => $_POST['port'],
            'query_port' => $_POST['query_port'],
            'settings' => $settings,
            'online_players' => (int) $game_server->online_players,
            'maximum_online_players' => (int) $game_server->maximum_online_players,
            'details' => $game_server->details,
            'uptime' => (float) $game_server->uptime,
            'downtime' => (float) $game_server->downtime,
            'average_response_time' => (float) $game_server->average_response_time,
            'average_online_players' => (float) $game_server->average_online_players,
            'total_checks' => (int) $game_server->total_checks,
            'total_ok_checks' => (int) $game_server->total_ok_checks,
            'total_not_ok_checks' => (int) $game_server->total_not_ok_checks,
            'last_check_datetime' => $game_server->last_check_datetime,
            'next_check_datetime' => $next_check_datetime,
            'is_enabled' => (bool) $_POST['is_enabled'],
            'datetime' => $game_server->datetime,
            'last_datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $game_server_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $game_server = db()->where('game_server_id', $game_server_id)->where('user_id', $this->user->user_id)->getOne('game_servers');

        /* We haven't found the resource */
        if(!$game_server) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('game_server_id', $game_server_id)->delete('game_servers');

        /* Clear the cache */
        cache()->deleteItemsByTag('game_server_id=' . $game_server->game_server_id);

        http_response_code(200);
        die();

    }

}
