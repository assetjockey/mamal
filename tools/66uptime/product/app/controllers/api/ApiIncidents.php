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

class ApiIncidents extends Controller {
    use Apiable;

    public function index() {

        if(!settings()->monitors_heartbeats->monitors_is_enabled && !settings()->monitors_heartbeats->heartbeats_is_enabled) {
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
        $filters = (new \Altum\Filters(['monitor_id', 'heartbeat_id', 'notification_handlers_ids'], ['comment'], ['incident_id', 'start_datetime', 'end_datetime', 'last_failed_check_datetime', 'failed_checks'], [], ['notification_handlers_ids' => 'json_contains'], allowed_datetime_fields: ['start_datetime', 'end_datetime', 'last_failed_check_datetime']));
        $filters->set_default_order_by($this->user->preferences->incidents_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `incidents` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/incidents?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `incidents`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->incident_id,
                'user_id' => (int) $row->user_id,
                'monitor_id' => (int) $row->monitor_id,
                'start_monitor_log_id' => (int) $row->start_monitor_log_id,
                'end_monitor_log_id' => (int) $row->end_monitor_log_id,
                'heartbeat_id' => (int) $row->heartbeat_id,
                'start_heartbeat_log_id' => (int) $row->start_heartbeat_log_id,
                'end_heartbeat_log_id' => (int) $row->end_heartbeat_log_id,
                'start_datetime' => $row->start_datetime,
                'end_datetime' => $row->end_datetime,
                'last_failed_check_datetime' => $row->last_failed_check_datetime,
                'failed_checks' => (int) $row->failed_checks,
                'notification_handlers_ids' => json_decode($row->notification_handlers_ids ?? '[]'),
                'comment' => $row->comment,
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

        $incident_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $incident = db()->where('incident_id', $incident_id)->where('user_id', $this->user->user_id)->getOne('incidents');

        /* We haven't found the resource */
        if(!$incident) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $incident->incident_id,
            'user_id' => (int) $incident->user_id,
            'monitor_id' => (int) $incident->monitor_id,
            'start_monitor_log_id' => (int) $incident->start_monitor_log_id,
            'end_monitor_log_id' => (int) $incident->end_monitor_log_id,
            'heartbeat_id' => (int) $incident->heartbeat_id,
            'start_heartbeat_log_id' => (int) $incident->start_heartbeat_log_id,
            'end_heartbeat_log_id' => (int) $incident->end_heartbeat_log_id,
            'start_datetime' => $incident->start_datetime,
            'end_datetime' => $incident->end_datetime,
            'last_failed_check_datetime' => $incident->last_failed_check_datetime,
            'failed_checks' => (int) $incident->failed_checks,
            'notification_handlers_ids' => json_decode($incident->notification_handlers_ids ?? '[]'),
            'comment' => $incident->comment,
        ];

        Response::jsonapi_success($data);

    }

    private function patch() {

        $incident_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $incident = db()->where('incident_id', $incident_id)->where('user_id', $this->user->user_id)->getOne('incidents');

        /* We haven't found the resource */
        if(!$incident) {
            $this->return_404();
        }

        $_POST['comment'] = input_clean($_POST['comment'] ?? $incident->comment, 300);

        /* Database query */
        db()->where('incident_id', $incident->incident_id)->update('incidents', [
            'comment' => $_POST['comment'],
        ]);

        /* Clear the cache */
        if($incident->monitor_id) {
            cache()->deleteItemsByTag('monitor_id=' . $incident->monitor_id);
        } else if($incident->heartbeat_id) {
            cache()->deleteItemsByTag('heartbeat_id=' . $incident->heartbeat_id);
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $incident->incident_id,
            'user_id' => (int) $incident->user_id,
            'monitor_id' => (int) $incident->monitor_id,
            'start_monitor_log_id' => (int) $incident->start_monitor_log_id,
            'end_monitor_log_id' => (int) $incident->end_monitor_log_id,
            'heartbeat_id' => (int) $incident->heartbeat_id,
            'start_heartbeat_log_id' => (int) $incident->start_heartbeat_log_id,
            'end_heartbeat_log_id' => (int) $incident->end_heartbeat_log_id,
            'start_datetime' => $incident->start_datetime,
            'end_datetime' => $incident->end_datetime,
            'last_failed_check_datetime' => $incident->last_failed_check_datetime,
            'failed_checks' => (int) $incident->failed_checks,
            'notification_handlers_ids' => json_decode($incident->notification_handlers_ids ?? '[]'),
            'comment' => $_POST['comment'],
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $incident_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $incident = db()->where('incident_id', $incident_id)->where('user_id', $this->user->user_id)->getOne('incidents');

        /* We haven't found the resource */
        if(!$incident) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('incident_id', $incident_id)->delete('incidents');

        /* Clear the cache */
        if($incident->heartbeat_id) {
            cache()->deleteItemsByTag('heartbeat_id=' . $incident->heartbeat_id);
        }
        if($incident->monitor_id) {
            cache()->deleteItemsByTag('monitor_id=' . $incident->monitor_id);
        }

        http_response_code(200);
        die();

    }

}
