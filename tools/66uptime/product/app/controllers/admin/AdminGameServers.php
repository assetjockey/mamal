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

class AdminGameServers extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'type', 'project_id'], ['name', 'target', 'port'], ['game_server_id', 'last_datetime', 'datetime', 'last_check_datetime', 'total_checks', 'name', 'uptime', 'average_response_time', 'average_online_players', 'online_players', 'maximum_online_players'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_check_datetime']));
		$filters->set_default_order_by($this->user->preferences->game_servers_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `game_servers` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/game_servers?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $game_servers = [];
        $game_servers_result = database()->query("
            SELECT
                `game_servers`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `game_servers`
            LEFT JOIN
                `users` ON `game_servers`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('game_servers')}
                {$filters->get_sql_order_by('game_servers')}

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
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'game_servers' => $game_servers,
            'filters' => $filters,
            'pagination' => $pagination,
            'game_server_types' => require APP_PATH . 'includes/game_server_types.php',
        ];

        $view = new \Altum\View('admin/game-servers/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/game-servers');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/game-servers');
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

                    foreach($_POST['selected'] as $game_server_id) {

                        /* Delete the game_server */
                        db()->where('game_server_id', $game_server_id)->delete('game_servers');

                        /* Clear the cache */
                        cache()->deleteItemsByTag('game_server_id=' . $game_server_id);

                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/game-servers');
    }

    public function delete() {

        $game_server_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$game_server = db()->where('game_server_id', $game_server_id)->getOne('game_servers', ['game_server_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the game_server */
            db()->where('game_server_id', $game_server_id)->delete('game_servers');

            /* Clear the cache */
            cache()->deleteItemsByTag('game_server_id=' . $game_server_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $game_server->name . '</strong>'));

        }

        redirect('admin/game-servers');
    }

}
