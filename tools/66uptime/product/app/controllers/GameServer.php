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

class GameServer extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->game_servers_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        $game_server_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$game_server = db()->where('game_server_id', $game_server_id)->where('user_id', $this->user->user_id)->getOne('game_servers')) {
            redirect('game-servers');
        }
        $game_server->details = json_decode($game_server->details ?? '');
        $game_server->settings = json_decode($game_server->settings ?? '');

        $start_date = isset($_GET['start_date']) ? query_clean($_GET['start_date']) : Date::get('', 4);
        $end_date = isset($_GET['end_date']) ? query_clean($_GET['end_date']) : Date::get('', 4);
        $date = \Altum\Date::get_start_end_dates($start_date, $end_date);

        /* Get the required statistics */
        $game_server_logs = [];
        $game_server_logs_chart = [];

        $game_server_logs_result = database()->query("
            SELECT
                *
            FROM
                 `game_servers_logs`
            WHERE
                `game_server_id` = {$game_server->game_server_id}
                AND (`datetime` BETWEEN '{$date->start_date_query}' AND '{$date->end_date_query}')
        ");

        $total_ok_checks = 0;
        $total_not_ok_checks = 0;
        $total_response_time = 0;
        $total_online_players = 0;
        $ping_servers_checks = [];

        /* Get monitor logs to calculate data and display charts */
        while($game_server_log = $game_server_logs_result->fetch_object()) {

            /* Only store the last 5 logs if no export is needed */
            $game_server_logs[] = $game_server_log;

            if(!isset($_GET['export']) && count($game_server_logs) > 5) {
                array_shift($game_server_logs);
            }

            /* Data for the chart */
            $label = $start_date == $end_date ? \Altum\Date::get($game_server_log->datetime, 3) : \Altum\Date::get($game_server_log->datetime, 1);

            $game_server_log->error = json_decode($game_server_log->error ?? '');
            $game_server_log_error = $game_server_log->is_ok ? l('global.none') : l('global.unknown');

            if(isset($game_server_log_error->type)) {
                if($game_server_log_error->type == 'exception') {
                    $game_server_log_error = $game_server_log_error->message;
                }
            }

            $game_server_logs_chart[$label] = [
                'error' => $game_server_log_error,
                'online_players' => $game_server_log->online_players,
                'maximum_online_players' => $game_server_log->maximum_online_players,
                'is_ok' => $game_server_log->is_ok,
                'response_time' => $game_server_log->is_ok ? $game_server_log->response_time : $game_server->average_response_time ?? 0,
                'hour_minute_second_label' => \Altum\Date::get($game_server_log->datetime, 3)
            ];

            $total_ok_checks = $game_server_log->is_ok ? $total_ok_checks + 1 : $total_ok_checks;
            $total_not_ok_checks = !$game_server_log->is_ok ? $total_not_ok_checks + 1 : $total_not_ok_checks;
            $total_response_time += $game_server_log->is_ok ? $game_server_log->response_time : 0;
            $total_online_players += $game_server_log->is_ok ? $game_server_log->online_players : 0;
        }

        /* Set a custom title */
        Title::set(sprintf(l('game_server.title'), $game_server->name));

        /* Export handler */
        process_export_csv_new($game_server_logs, ['game_server_log_id', 'game_server_id', 'online_players', 'maximum_online_players', 'is_ok', 'response_time', 'error', 'datetime'], ['error'], sprintf(l('monitor_logs.title'), $game_server->name));
        process_export_json($game_server_logs, ['game_server_log_id', 'game_server_id', 'online_players', 'maximum_online_players', 'is_ok', 'response_time', 'error', 'datetime'], sprintf(l('monitor_logs.title'), $game_server->name));

        /* Total logs */
        $total_game_server_logs = count($game_server_logs_chart);

        /* Chart data generation */
        $game_server_logs_chart = get_chart_data($game_server_logs_chart);

        /* calculate some data */
        $uptime = $total_ok_checks > 0 ? $total_ok_checks / ($total_ok_checks + $total_not_ok_checks) * 100 : 0;
        $downtime = 100 - $uptime;
        $average_response_time = $total_ok_checks > 0 ? $total_response_time / $total_ok_checks : 0;
        $average_online_players = $total_ok_checks > 0 ? $total_online_players / $total_ok_checks : 0;

        /* Prepare the view */
        $data = [
            'game_server' => $game_server,
            'game_server_logs_chart' => $game_server_logs_chart,
            'game_server_logs' => $game_server_logs,
            'total_game_server_logs' => $total_game_server_logs,
            'game_server_logs_data' => [
                'uptime' => $uptime,
                'downtime' => $downtime,
                'average_response_time' => $average_response_time,
                'total_ok_checks' => $total_ok_checks,
                'total_not_ok_checks' => $total_not_ok_checks,
                'average_online_players' => $average_online_players,
            ],
            'date' => $date,
            'game_server_types' => require APP_PATH . 'includes/game_server_types.php'
        ];

        $view = new \Altum\View('game-server/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }

}
