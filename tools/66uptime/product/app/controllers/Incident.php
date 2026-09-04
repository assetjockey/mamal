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

namespace Altum\controllers;

use Altum\Title;

defined('ALTUMCODE') || die();

class Incident extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->monitors_is_enabled && !settings()->monitors_heartbeats->heartbeats_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        $incident_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$incident = db()->where('incident_id', $incident_id)->where('user_id', $this->user->user_id)->getOne('incidents')) {
            redirect('dashboard');
        }

        if($incident->monitor_id) {
            if(!$monitor = db()->where('monitor_id', $incident->monitor_id)->where('user_id', $this->user->user_id)->getOne('monitors')) {
                redirect('monitors');
            }

            /* Get available ping servers */
            $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();

            /* Failed logs datetime check */
            $monitor_failed_logs_datetime_query = $incident->end_datetime ? "AND `datetime` BETWEEN '{$incident->start_datetime}' AND '{$incident->end_datetime}'" : "AND `datetime` >= '{$incident->start_datetime}'";

            /* Get the failed logs of the incident */
            $monitor_logs = [];

            $monitor_logs_result = database()->query("
                SELECT
                    `monitor_log_id`,
                    `ping_server_id`,
                    `is_ok`,
                    `response_time`,
                    `response_status_code`,
                    `error`,
                    `datetime`
                FROM
                     `monitors_logs`
                WHERE
                    `monitor_id` = {$monitor->monitor_id}
                    {$monitor_failed_logs_datetime_query}
                ORDER BY `monitor_log_id` DESC
                LIMIT 25
            ");

            /* Get monitor logs to calculate data and display charts */
            while($monitor_log = $monitor_logs_result->fetch_object()) {
                $monitor_log->error = json_decode($monitor_log->error ?? '');
                $monitor_logs[] = $monitor_log;
            }

            $total_monitor_logs = count($monitor_logs);
        }

        else if($incident->heartbeat_id) {
            if(!$heartbeat = db()->where('heartbeat_id', $incident->heartbeat_id)->where('user_id', $this->user->user_id)->getOne('heartbeats')) {
                redirect('heartbeats');
            }
        }

        $incident->notification_handlers_ids = json_decode($incident->notification_handlers_ids ?? '[]');
        $incident->error = json_decode($incident->error ?? '');

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Set a custom title */
        Title::set(l('incident.title'));

        /* Prepare the view */
        $data = [
            'monitor' => $monitor ?? null,
            'ping_servers' => $ping_servers ?? null,
            'monitor_logs' => $monitor_logs ?? null,
            'total_monitor_logs' => $total_monitor_logs ?? null,

            'heartbeat' => $heartbeat ?? null,
            'incident' => $incident,
            'notification_handlers' => $notification_handlers,
        ];

        $view = new \Altum\View('incident/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }
}
