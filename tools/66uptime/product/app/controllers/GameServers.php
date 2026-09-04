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


use Altum\Alerts;

defined('ALTUMCODE') || die();

class GameServers extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->game_servers_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'type', 'project_id'], ['name', 'target', 'port'], ['game_server_id', 'last_datetime', 'datetime', 'last_check_datetime', 'total_checks', 'name', 'uptime', 'average_response_time', 'average_online_players', 'online_players', 'maximum_online_players'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_check_datetime']));
        $filters->set_default_order_by($this->user->preferences->game_servers_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `game_servers` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('game_servers?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the game_servers */
        $game_servers = [];
        $game_servers_result = database()->query("
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
        while($row = $game_servers_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');
            $row->notifications = json_decode($row->notifications ?? '');
            $row->details = json_decode($row->details ?? '');
            $row->last_logs = json_decode($row->last_logs ?? '');
            if(is_null($row->last_logs)) $row->last_logs = [[], [], [], [], [], [], []];
            $game_servers[] = $row;
        }

        /* Export handler */
        process_export_csv_new($game_servers, ['game_server_id', 'project_id', 'incident_id', 'name', 'type', 'target', 'port', 'query_port', 'settings', 'details', 'is_ok', 'online_players', 'maximum_online_players', 'average_online_players', 'uptime', 'uptime_seconds', 'downtime', 'downtime_seconds', 'average_response_time', 'total_checks', 'total_ok_checks', 'total_not_ok_checks', 'last_check_datetime', 'next_check_datetime', 'main_ok_datetime', 'last_ok_datetime', 'main_not_ok_datetime', 'last_not_ok_datetime', 'notifications', 'is_enabled', 'datetime', 'last_datetime'], ['settings', 'details', 'notifications'], sprintf(l('game_servers.title')));
        process_export_json($game_servers, ['game_server_id', 'project_id', 'incident_id', 'name', 'type', 'target', 'port', 'query_port', 'settings', 'details', 'is_ok', 'online_players', 'maximum_online_players', 'average_online_players', 'uptime', 'uptime_seconds', 'downtime', 'downtime_seconds', 'average_response_time', 'total_checks', 'total_ok_checks', 'total_not_ok_checks', 'last_check_datetime', 'next_check_datetime', 'main_ok_datetime', 'last_ok_datetime', 'main_not_ok_datetime', 'last_not_ok_datetime', 'notifications', 'is_enabled', 'datetime', 'last_datetime'], sprintf(l('game_servers.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Prepare the view */
        $data = [
            'projects' => $projects,
            'game_servers' => $game_servers,
            'total_game_servers' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
            'game_server_types' => require APP_PATH . 'includes/game_server_types.php'
        ];

        $view = new \Altum\View('game-servers/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function duplicate() {
        \Altum\Authentication::guard();

        if(!settings()->monitors_heartbeats->game_servers_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.game_servers')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('game-servers');
        }

        if (empty($_POST)) {
            throw_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('game_servers', 'COUNT(*)') ?? 0;
        if($this->user->plan_settings->game_servers_limit != -1 && $total_rows >= $this->user->plan_settings->game_servers_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('game-servers');
        }

        $game_server_id = (int) $_POST['game_server_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');
        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('game-servers');
        }

        /* Verify the main resource */
        if(!$game_server = db()->where('game_server_id', $game_server_id)->where('user_id', $this->user->user_id)->getOne('game_servers')) {
            redirect('game-servers');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Insert to database */
            $game_server_id = db()->insert('game_servers', [
                'user_id' => $this->user->user_id,
                'project_id' => $game_server->project_id,
                'name' => string_truncate($game_server->name . ' - ' . l('global.duplicated'), 64, null),
                'type' => $game_server->type,
                'target' => $game_server->target,
                'port' => $game_server->port,
                'settings' => $game_server->settings,
                'details' => $game_server->details,
                'is_enabled' => 0,
                'next_check_datetime' => $game_server->next_check_datetime,
                'datetime' => get_date(),
            ]);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($game_server->name) . '</strong>'));

            /* Redirect */
            redirect('game-server-update/' . $game_server_id);

        }

        redirect('game-servers');
    }

    public function bulk() {

        if(!settings()->monitors_heartbeats->game_servers_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('game-servers');
        }

        if(!isset($_POST['type'])) {
            redirect('game-servers');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            set_time_limit(0);

            session_write_close();

            $_POST['selected'] = is_array($_POST['selected']) ? array_unique(array_map('intval', $_POST['selected'])) : [];

            switch($_POST['type']) {
                case 'delete':

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.game_servers')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('game-servers');
                    }

                    foreach($_POST['selected'] as $game_server_id) {
                        if($game_server = db()->where('game_server_id', $game_server_id)->where('user_id', $this->user->user_id)->getOne('game_servers', ['game_server_id'])) {
                            /* Delete the monitor */
                            db()->where('game_server_id', $game_server->game_server_id)->delete('game_servers');

                            /* Clear the cache */
                            cache()->deleteItemsByTag('game_server_id=' . $game_server->game_server_id);
                        }
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('game-servers');
    }

    public function delete() {

        if(!settings()->monitors_heartbeats->game_servers_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.game_servers')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('game-servers');
        }

        if (empty($_POST)) {
            throw_404();
        }

        $game_server_id = (int) $_POST['game_server_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('game-servers');
        }

        /* Make sure the monitor id is created by the logged in user */
        if(!$game_server = db()->where('game_server_id', $game_server_id)->where('user_id', $this->user->user_id)->getOne('game_servers', ['game_server_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the monitor */
            db()->where('game_server_id', $game_server->game_server_id)->delete('game_servers');

            /* Clear the cache */
            cache()->deleteItemsByTag('game_server_id=' . $game_server->game_server_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $game_server->name . '</strong>'));

            redirect('game-servers');

        }

        redirect('game-servers');
    }

}
