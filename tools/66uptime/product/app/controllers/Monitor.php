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

use Altum\Date;
use Altum\Title;

defined('ALTUMCODE') || die();

class Monitor extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        $monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$monitor = db()->where('monitor_id', $monitor_id)->where('user_id', $this->user->user_id)->getOne('monitors')) {
            redirect('monitors');
        }
        $monitor->details = json_decode($monitor->details);
        $monitor->settings = json_decode($monitor->settings ?? '');

        $start_date = isset($_GET['start_date']) ? query_clean($_GET['start_date']) : Date::get('', 4);
        $end_date = isset($_GET['end_date']) ? query_clean($_GET['end_date']) : Date::get('', 4);
        $date = \Altum\Date::get_start_end_dates($start_date, $end_date);

        /* Get available ping servers */
        $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();

        /* Get the required statistics */
        $monitor_logs = [];
        $monitor_logs_chart = [];

        $monitor_logs_result = database()->query("
            SELECT
                `monitor_id`,
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
                AND (`datetime` BETWEEN '{$date->start_date_query}' AND '{$date->end_date_query}')
        ");

        $total_ok_checks = 0;
        $total_not_ok_checks = 0;
        $total_response_time = 0;
        $ping_servers_checks = [];

        /* Get monitor logs to calculate data and display charts */
        while($monitor_log = $monitor_logs_result->fetch_object()) {

            /* Process for the ping server average */
            if(isset($ping_servers[$monitor_log->ping_server_id])) {
                $ping_server = $ping_servers[$monitor_log->ping_server_id];

                if(!isset($ping_servers_checks[$ping_server->ping_server_id])) {
                    $ping_servers_checks[$ping_server->ping_server_id] = [
                        'total_response_time' => $monitor_log->response_time,
                        'total_ok_checks' => $monitor_log->is_ok ? 1 : 0,
                        'total_not_ok_checks' => !$monitor_log->is_ok ? 1: 0,
                        'lowest_response_time' => $monitor_log->response_time,
                        'highest_response_time' => $monitor_log->response_time,
                    ];
                } else {
                    if($monitor_log->is_ok) $ping_servers_checks[$ping_server->ping_server_id]['total_ok_checks']++;
                    if(!$monitor_log->is_ok) $ping_servers_checks[$ping_server->ping_server_id]['total_not_ok_checks']++;
                    $ping_servers_checks[$ping_server->ping_server_id]['total_response_time'] += $monitor_log->response_time;

                    if($monitor_log->response_time < $ping_servers_checks[$ping_server->ping_server_id]['lowest_response_time']) {
                        $ping_servers_checks[$ping_server->ping_server_id]['lowest_response_time'] = $monitor_log->response_time;
                    }

                    if($monitor_log->response_time > $ping_servers_checks[$ping_server->ping_server_id]['highest_response_time']) {
                        $ping_servers_checks[$ping_server->ping_server_id]['highest_response_time'] = $monitor_log->response_time;
                    }

                }
            }

            /* Only store the last 5 logs if no export is needed */
            $monitor_logs[] = $monitor_log;

            if(!isset($_GET['export']) && count($monitor_logs) > 5) {
                array_shift($monitor_logs);
            }

            /* Data for the chart */
            $label = $start_date == $end_date ? \Altum\Date::get($monitor_log->datetime, 3) : \Altum\Date::get($monitor_log->datetime, 1);

            $monitor_log->error = json_decode($monitor_log->error ?? '');
            $monitor_log_error = $monitor_log->is_ok ? l('global.none') : l('global.unknown');

            if(isset($monitor_log_error->type)) {
                if($monitor_log->type == 'exception') {
                    $monitor_log_error = $monitor_log_error->message;
                } elseif(in_array($monitor_log->type, ['response_status_code', 'response_body', 'response_header', 'ping_failed', 'parse_error', 'socket_connect_failed', 'socket_no_response', 'socket_create_failed'])) {
                    $monitor_log_error = l('monitor.checks.error.' . $monitor_log->type);
                } elseif(in_array($monitor_log->type, ['connection_failed'])) {
                    $monitor_log_error = sprintf(l('monitor.checks.error.connection_failed'), $monitor_log_error->error->message);
                }
            }

            $monitor_logs_chart[$label] = [
                'ping_server' => isset($ping_servers[$monitor_log->ping_server_id]) ? get_countries_array()[$ping_servers[$monitor_log->ping_server_id]->country_code] . ', ' . $ping_servers[$monitor_log->ping_server_id]->city_name : null,
                'error' => $monitor_log_error,
                'response_status_code' => $monitor_log->response_status_code,
                'is_ok' => $monitor_log->is_ok,
                'response_time' => $monitor_log->is_ok ? $monitor_log->response_time : $monitor->average_response_time ?? 0,
                'hour_minute_second_label' => \Altum\Date::get($monitor_log->datetime, 3)
            ];

            $total_ok_checks = $monitor_log->is_ok ? $total_ok_checks + 1 : $total_ok_checks;
            $total_not_ok_checks = !$monitor_log->is_ok ? $total_not_ok_checks + 1 : $total_not_ok_checks;
            $total_response_time += $monitor_log->is_ok ? $monitor_log->response_time : 0;
        }

        /* Set a custom title */
        Title::set(sprintf(l('monitor.title'), $monitor->name));

        /* Export handler */
        process_export_csv_new($monitor_logs, ['monitor_log_id', 'monitor_id', 'ping_server_id', 'is_ok', 'response_time', 'response_status_code', 'error', 'datetime'], ['error'], sprintf(l('monitor_logs.title'), $monitor->name));
        process_export_json($monitor_logs, ['monitor_log_id', 'monitor_id', 'ping_server_id', 'is_ok', 'response_time', 'response_status_code', 'error', 'datetime'], sprintf(l('monitor_logs.title'), $monitor->name));

        /* Total logs */
        $total_monitor_logs = count($monitor_logs_chart);

        /* Chart data generation */
        $monitor_logs_chart = get_chart_data($monitor_logs_chart);

        /* Get the available incidents */
        $monitor_incidents = [];

        $monitor_incidents_result = database()->query("
            SELECT
                `start_datetime`,
                `end_datetime`,
                `comment`,
                `error`,
                `incident_id`,
                `failed_checks`
            FROM
                 `incidents`
            WHERE
                `monitor_id` = {$monitor->monitor_id}
                AND `start_datetime` >= '{$date->start_date_query}' 
                AND (`end_datetime` <= '{$date->end_date_query}' OR `end_datetime` IS NULL)
            ORDER BY
                `incident_id` DESC
        ");

        while($row = $monitor_incidents_result->fetch_object()) {
            $row->error = json_decode($row->error ?? '');
            $monitor_incidents[] = $row;
        }

        /* calculate some data */
        $uptime = $total_ok_checks > 0 ? $total_ok_checks / ($total_ok_checks + $total_not_ok_checks) * 100 : 0;
        $downtime = 100 - $uptime;
        $average_response_time = $total_ok_checks > 0 ? $total_response_time / $total_ok_checks : 0;

        /* Prepare the view */
        $data = [
            'monitor' => $monitor,
            'monitor_logs_chart' => $monitor_logs_chart,
            'monitor_logs' => $monitor_logs,
            'total_monitor_logs' => $total_monitor_logs,
            'monitor_logs_data' => [
                'uptime' => $uptime,
                'downtime' => $downtime,
                'average_response_time' => $average_response_time,
                'total_ok_checks' => $total_ok_checks,
                'total_not_ok_checks' => $total_not_ok_checks
            ],
            'date' => $date,
            'monitor_incidents' => $monitor_incidents,
            'ping_servers_checks' => $ping_servers_checks,
            'ping_servers' => $ping_servers,
        ];

        $view = new \Altum\View('monitor/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }

}
