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

class GameServerLogs extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->game_servers_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        $game_server_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$game_server = db()->where('game_server_id', $game_server_id)->where('user_id', $this->user->user_id)->getOne('game_servers')) {
            redirect('game_servers');
        }
        $game_server->details = json_decode($game_server->details ?? '');
        $game_server->settings = json_decode($game_server->settings ?? '');

        $start_date = isset($_GET['start_date']) ? query_clean($_GET['start_date']) : Date::get('', 4);
        $end_date = isset($_GET['end_date']) ? query_clean($_GET['end_date']) : Date::get('', 4);
        $has_utc_timezone = isset($_GET['timezone']) && $_GET['timezone'] == 'UTC';

        if($has_utc_timezone) {
            $date = \Altum\Date::get_start_end_dates($start_date, $end_date, 'UTC', 'UTC', true);
        } else {
            $date = \Altum\Date::get_start_end_dates($start_date, $end_date);
        }

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_ok'], [], ['game_server_log_id', 'online_players', 'response_time', 'datetime'], allowed_datetime_fields: ['datetime']));
        $filters->set_default_order_by('game_server_log_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `game_servers_logs` WHERE `game_server_id` = {$game_server->game_server_id} AND (`datetime` BETWEEN '{$date->start_date_query}' AND '{$date->end_date_query}') {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('game-server-logs/' . $game_server->game_server_id . '?' . $filters->get_get() . '&start_date=' . $start_date . '&end_date=' . $end_date . '&page={{PAGE}}')));

        /* Get the required logs */
        $game_server_logs = [];
        $game_server_logs_result = database()->query("
            SELECT
                *
            FROM
                 `game_servers_logs`
            WHERE
                `game_server_id` = {$game_server->game_server_id}
                AND (`datetime` BETWEEN '{$date->start_date_query}' AND '{$date->end_date_query}')
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");

        /* Get game_server logs to calculate data and display charts */
        while($game_server_log = $game_server_logs_result->fetch_object()) {
            $game_server_log->error = json_decode($game_server_log->error ?? '');
            $game_server_logs[] = $game_server_log;
        }

        /* Set a custom title */
        Title::set(sprintf(l('game_server_logs.title'), $game_server->name));

        /* Export handler */
        process_export_csv_new($game_server_logs, ['game_server_log_id', 'game_server_id', 'online_players', 'maximum_online_players', 'is_ok', 'response_time', 'error', 'datetime'], ['error'], sprintf(l('monitor_logs.title'), $game_server->name));
        process_export_json($game_server_logs, ['game_server_log_id', 'game_server_id', 'online_players', 'maximum_online_players', 'is_ok', 'response_time', 'error', 'datetime'], sprintf(l('monitor_logs.title'), $game_server->name));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'game_server' => $game_server,
            'game_server_logs' => $game_server_logs,
            'date' => $date,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('game-server-logs/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }
}
