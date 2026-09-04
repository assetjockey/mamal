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

class Heartbeats extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->heartbeats_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'project_id'], ['name'], ['heartbeat_id', 'datetime', 'last_datetime', 'last_run_datetime', 'name', 'uptime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_run_datetime']));
        $filters->set_default_order_by($this->user->preferences->heartbeats_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `heartbeats` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('heartbeats?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the heartbeats */
        $heartbeats = [];
        $heartbeats_result = database()->query("
            SELECT
                *
            FROM
                `heartbeats`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}

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
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Prepare the view */
        $data = [
            'projects' => $projects,
            'heartbeats' => $heartbeats,
            'total_heartbeats' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('heartbeats/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function duplicate() {
        \Altum\Authentication::guard();

        if(!settings()->monitors_heartbeats->heartbeats_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.heartbeats')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('heartbeats');
        }

        if (empty($_POST)) {
            throw_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('heartbeats', 'COUNT(*)') ?? 0;
        if($this->user->plan_settings->heartbeats_limit != -1 && $total_rows >= $this->user->plan_settings->heartbeats_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('heartbeats');
        }

        $heartbeat_id = (int) $_POST['heartbeat_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');
        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('heartbeats');
        }

        /* Verify the main resource */
        if(!$heartbeat = db()->where('heartbeat_id', $heartbeat_id)->where('user_id', $this->user->user_id)->getOne('heartbeats')) {
            redirect('heartbeats');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
            $code = md5(time() . $_POST['name'] . $this->user->user_id . microtime());

            /* Insert to database */
            $heartbeat_id = db()->insert('heartbeats', [
                'user_id' => $this->user->user_id,
                'project_id' => $heartbeat->project_id,
                'name' => string_truncate($heartbeat->name . ' - ' . l('global.duplicated'), 64, null),
                'code' => $code,
                'settings' => $heartbeat->settings,
                'notifications' => $heartbeat->notifications,
                'email_reports_is_enabled' => $heartbeat->email_reports_is_enabled,
                'email_reports_last_datetime' => get_date(),
                'next_run_datetime' => $heartbeat->next_run_datetime,
                'is_enabled' => 0,
                'datetime' => get_date(),
            ]);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($heartbeat->name) . '</strong>'));

            /* Redirect */
            redirect('heartbeat-update/' . $heartbeat_id);

        }

        redirect('heartbeats');
    }

    public function bulk() {

        if(!settings()->monitors_heartbeats->heartbeats_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('heartbeats');
        }

        if(!isset($_POST['type'])) {
            redirect('heartbeats');
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
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.heartbeats')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('heartbeats');
                    }

                    foreach($_POST['selected'] as $heartbeat_id) {
                        if($heartbeat = db()->where('heartbeat_id', $heartbeat_id)->where('user_id', $this->user->user_id)->getOne('heartbeats', ['heartbeat_id'])) {
                            /* Delete the heartbeat */
                            db()->where('heartbeat_id', $heartbeat->heartbeat_id)->delete('heartbeats');

                            /* Clear the cache */
                            cache()->deleteItemsByTag('heartbeat_id=' . $heartbeat->heartbeat_id);
                        }
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('heartbeats');
    }

    public function delete() {

        if(!settings()->monitors_heartbeats->heartbeats_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.heartbeats')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('heartbeats');
        }

        if (empty($_POST)) {
            throw_404();
        }

        $heartbeat_id = (int) $_POST['heartbeat_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('heartbeats');
        }

        /* Make sure the heartbeat id is created by the logged in user */
        if(!$heartbeat = db()->where('heartbeat_id', $heartbeat_id)->where('user_id', $this->user->user_id)->getOne('heartbeats', ['heartbeat_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the heartbeat */
            db()->where('heartbeat_id', $heartbeat->heartbeat_id)->delete('heartbeats');

            /* Clear the cache */
            cache()->deleteItemsByTag('heartbeat_id=' . $heartbeat->heartbeat_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $heartbeat->name . '</strong>'));

            redirect('heartbeats');

        }

        redirect('heartbeats');
    }

}
