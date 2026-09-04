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

class Heartbeat extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->heartbeats_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        $heartbeat_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$heartbeat = db()->where('heartbeat_id', $heartbeat_id)->where('user_id', $this->user->user_id)->getOne('heartbeats')) {
            redirect('heartbeats');
        }
        $heartbeat->settings = json_decode($heartbeat->settings ?? '');

        $start_date = isset($_GET['start_date']) ? query_clean($_GET['start_date']) : Date::get('', 4);
        $end_date = isset($_GET['end_date']) ? query_clean($_GET['end_date']) : Date::get('', 4);
        $date = \Altum\Date::get_start_end_dates($start_date, $end_date);

        /* Get the required statistics */
        $heartbeat_logs = [];
        $heartbeat_logs_chart = [];

        $heartbeat_logs_result = database()->query("
            SELECT
                `heartbeat_log_id`,
                `heartbeat_id`,
                `is_ok`,
                `datetime`
            FROM
                `heartbeats_logs`
            WHERE
                `heartbeat_id` = {$heartbeat->heartbeat_id}
                AND (`datetime` BETWEEN '{$date->start_date_query}' AND '{$date->end_date_query}')
        ");

        $total_runs = 0;
        $total_missed_runs = 0;

        /* Get heartbeat logs to calculate data and display charts */
        while($heartbeat_log = $heartbeat_logs_result->fetch_object()) {

            /* Only store the logs if export is needed */
            if(isset($_GET['export'])) {
                $heartbeat_logs[] = $heartbeat_log;
            }

            $label = $start_date == $end_date ? \Altum\Date::get($heartbeat_log->datetime, 3) : \Altum\Date::get($heartbeat_log->datetime, 1);

            $heartbeat_logs_chart[$label] = [
                'is_ok' => $heartbeat_log->is_ok,
                'is_ok_chart' => $heartbeat_log->is_ok ? 1 : 0.999,
                'hour_minute_second_label' => \Altum\Date::get($heartbeat_log->datetime, 3)
            ];

            $total_runs = $heartbeat_log->is_ok ? $total_runs + 1 : $total_runs;
            $total_missed_runs = !$heartbeat_log->is_ok ? $total_missed_runs + 1 : $total_missed_runs;
        }

        /* Set a custom title */
        Title::set(sprintf(l('heartbeat.title'), $heartbeat->name));

        /* Export handler */
        process_export_csv($heartbeat_logs, ['heartbeat_log_id', 'heartbeat_id', 'is_ok', 'datetime'], sprintf(l('heartbeat.title'), $heartbeat->name));
        process_export_json($heartbeat_logs, ['heartbeat_log_id', 'heartbeat_id', 'is_ok', 'datetime'], sprintf(l('heartbeat.title'), $heartbeat->name));

        /* Total logs */
        $total_heartbeat_logs = count($heartbeat_logs_chart);

        /* Chart data generation */
        $heartbeat_logs_chart = get_chart_data($heartbeat_logs_chart);

        /* Get the available incidents */
        $heartbeat_incidents = [];

        $heartbeat_incidents_result = database()->query("
            SELECT
                `start_datetime`,
                `end_datetime`,
                `comment`,
                `incident_id`,
                `failed_checks`
            FROM
                 `incidents`
            WHERE
                `heartbeat_id` = {$heartbeat->heartbeat_id}
                AND `start_datetime` >= '{$date->start_date_query}' 
                AND (`end_datetime` <= '{$date->end_date_query}' OR `end_datetime` IS NULL)
            ORDER BY
                `incident_id` DESC
        ");

        while($row = $heartbeat_incidents_result->fetch_object()) {
            $heartbeat_incidents[] = $row;
        }

        /* calculate some data */
        $uptime = $total_runs > 0 ? $total_runs / ($total_runs + $total_missed_runs) * 100 : 0;
        $downtime = 100 - $uptime;

        /* Prepare the view */
        $data = [
            'heartbeat' => $heartbeat,
            'heartbeat_logs_chart' => $heartbeat_logs_chart,
            'heartbeat_logs' => $heartbeat_logs,
            'total_heartbeat_logs' => $total_heartbeat_logs,
            'heartbeat_logs_data' => [
                'uptime' => $uptime,
                'downtime' => $downtime,
                'total_runs' => $total_runs,
                'total_missed_runs' => $total_missed_runs
            ],
            'date' => $date,
            'heartbeat_incidents' => $heartbeat_incidents,
        ];

        $view = new \Altum\View('heartbeat/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
