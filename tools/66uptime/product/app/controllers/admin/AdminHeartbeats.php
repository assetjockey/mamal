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

class AdminHeartbeats extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'type', 'user_id', 'project_id'], ['name', 'target'], ['heartbeat_id', 'last_datetime', 'datetime', 'name', 'uptime', 'total_checks', 'last_run_datetime', 'average_response_time'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_run_datetime']));
        $filters->set_default_order_by($this->user->preferences->heartbeats_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `heartbeats` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/heartbeats?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $heartbeats = [];
        $heartbeats_result = database()->query("
            SELECT
                `heartbeats`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `heartbeats`
            LEFT JOIN
                `users` ON `heartbeats`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('heartbeats')}
                {$filters->get_sql_order_by('heartbeats')}

            {$paginator->get_sql_limit()}
        ");
        while($row = $heartbeats_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');
            $row->last_logs = json_decode($row->last_logs ?? '');
            if(is_null($row->last_logs)) $row->last_logs = [[], [], [], [], [], [], []];
            $heartbeats[] = $row;
        }

        /* Export handler */
        process_export_csv_new($heartbeats, ['heartbeat_id', 'project_id', 'incident_id', 'name', 'code', 'settings', 'notifications', 'is_ok', 'uptime', 'uptime_seconds', 'downtime', 'downtime_seconds', 'total_runs', 'total_missed_runs', 'main_run_datetime', 'main_missed_datetime', 'last_missed_datetime', 'last_run_datetime', 'next_run_datetime', 'is_enabled', 'datetime', 'last_datetime'], ['settings', 'notifications',], sprintf(l('heartbeats.title')));
        process_export_json($heartbeats, ['heartbeat_id', 'project_id', 'incident_id', 'name', 'code', 'settings', 'notifications', 'is_ok', 'uptime', 'uptime_seconds', 'downtime', 'downtime_seconds', 'total_runs', 'total_missed_runs', 'main_run_datetime', 'main_missed_datetime', 'last_missed_datetime', 'last_run_datetime', 'next_run_datetime', 'is_enabled', 'datetime', 'last_datetime'], sprintf(l('heartbeats.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'heartbeats' => $heartbeats,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/heartbeats/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/heartbeats');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/heartbeats');
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

                    foreach($_POST['selected'] as $heartbeat_id) {

                        /* Delete the heartbeat */
                        db()->where('heartbeat_id', $heartbeat_id)->delete('heartbeats');

                        /* Clear the cache */
                        cache()->deleteItemsByTag('heartbeat_id=' . $heartbeat_id);

                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/heartbeats');
    }

    public function delete() {

        $heartbeat_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$heartbeat = db()->where('heartbeat_id', $heartbeat_id)->getOne('heartbeats', ['heartbeat_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the heartbeat */
            db()->where('heartbeat_id', $heartbeat->heartbeat_id)->delete('heartbeats');

            /* Clear the cache */
            cache()->deleteItemsByTag('heartbeat_id=' . $heartbeat_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $heartbeat->name . '</strong>'));

        }

        redirect('admin/heartbeats');
    }

}
