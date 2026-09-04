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

class Monitors extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'type', 'project_id', 'ping_servers_ids'], ['name', 'target'], ['monitor_id', 'last_datetime', 'datetime', 'last_check_datetime', 'name', 'uptime', 'average_response_time'], [], ['ping_servers_ids' => 'json_contains'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_check_datetime']));
        $filters->set_default_order_by($this->user->preferences->monitors_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `monitors` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('monitors?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the monitors */
        $monitors = [];
        $monitors_result = database()->query("
            SELECT
                *
            FROM
                `monitors`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}

            {$paginator->get_sql_limit()}
        ");
        while($row = $monitors_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');
            $row->notifications = json_decode($row->notifications ?? '');
            $row->details = json_decode($row->details ?? '');
            $row->last_logs = json_decode($row->last_logs ?? '');
            if(is_null($row->last_logs)) $row->last_logs = [[], [], [], [], [], [], []];
            $monitors[] = $row;
        }

        /* Export handler */
        process_export_csv_new($monitors, ['monitor_id', 'project_id', 'incident_id', 'name', 'type', 'target', 'port', 'settings', 'details', 'ping_servers_ids', 'is_ok', 'uptime', 'uptime_seconds', 'downtime', 'downtime_seconds', 'average_response_time', 'total_checks', 'total_ok_checks', 'total_not_ok_checks', 'last_check_datetime', 'next_check_datetime', 'main_ok_datetime', 'last_ok_datetime', 'main_not_ok_datetime', 'last_not_ok_datetime', 'email_reports_is_enabled', 'email_reports_last_datetime', 'notifications', 'is_enabled', 'datetime', 'last_datetime'], ['settings', 'details', 'notifications'], sprintf(l('monitors.title')));
        process_export_json($monitors, ['monitor_id', 'project_id', 'incident_id', 'name', 'type', 'target', 'port', 'settings', 'details', 'ping_servers_ids', 'is_ok', 'uptime', 'uptime_seconds', 'downtime', 'downtime_seconds', 'average_response_time', 'total_checks', 'total_ok_checks', 'total_not_ok_checks', 'last_check_datetime', 'next_check_datetime', 'main_ok_datetime', 'last_ok_datetime', 'main_not_ok_datetime', 'last_not_ok_datetime', 'email_reports_is_enabled', 'email_reports_last_datetime', 'notifications', 'is_enabled', 'datetime', 'last_datetime'], sprintf(l('monitors.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();

        /* Prepare the view */
        $data = [
            'projects' => $projects,
            'ping_servers' => $ping_servers,
            'monitors' => $monitors,
            'total_monitors' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('monitors/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function duplicate() {
        \Altum\Authentication::guard();

        if(!settings()->monitors_heartbeats->monitors_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.monitors')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('monitors');
        }

        if (empty($_POST)) {
            throw_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('monitors', 'COUNT(*)') ?? 0;
        if($this->user->plan_settings->monitors_limit != -1 && $total_rows >= $this->user->plan_settings->monitors_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('monitors');
        }

        $monitor_id = (int) $_POST['monitor_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');
        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('monitors');
        }

        /* Verify the main resource */
        if(!$monitor = db()->where('monitor_id', $monitor_id)->where('user_id', $this->user->user_id)->getOne('monitors')) {
            redirect('monitors');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Insert to database */
            $monitor_id = db()->insert('monitors', [
                'user_id' => $this->user->user_id,
                'project_id' => $monitor->project_id,
                'name' => string_truncate($monitor->name . ' - ' . l('global.duplicated'), 64, null),
                'type' => $monitor->type,
                'target' => $monitor->target,
                'port' => $monitor->port,
                'ping_servers_ids' => $monitor->ping_servers_ids,
                'settings' => $monitor->settings,
                'details' => $monitor->details,
                'notifications' => $monitor->notifications,
                'email_reports_is_enabled' => $monitor->email_reports_is_enabled,
                'is_enabled' => 0,
                'next_check_datetime' => $monitor->next_check_datetime,
                'datetime' => get_date(),
            ]);

            /* Clear the cache */
            cache()->deleteItem('s_monitors?user_id=' . $this->user->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($monitor->name) . '</strong>'));

            /* Redirect */
            redirect('monitor-update/' . $monitor_id);

        }

        redirect('monitors');
    }

    public function bulk() {

        if(!settings()->monitors_heartbeats->monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('monitors');
        }

        if(!isset($_POST['type'])) {
            redirect('monitors');
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
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.monitors')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('monitors');
                    }

                    foreach($_POST['selected'] as $monitor_id) {
                        if($monitor = db()->where('monitor_id', $monitor_id)->where('user_id', $this->user->user_id)->getOne('monitors', ['monitor_id'])) {
                            /* Delete the monitor */
                            db()->where('monitor_id', $monitor->monitor_id)->delete('monitors');

                            /* Clear the cache */
                            cache()->deleteItemsByTag('monitor_id=' . $monitor->monitor_id);
                        }
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('monitors');
    }

    public function delete() {

        if(!settings()->monitors_heartbeats->monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.monitors')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('monitors');
        }

        if (empty($_POST)) {
            throw_404();
        }

        $monitor_id = (int) $_POST['monitor_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('monitors');
        }

        /* Make sure the monitor id is created by the logged in user */
        if(!$monitor = db()->where('monitor_id', $monitor_id)->where('user_id', $this->user->user_id)->getOne('monitors', ['monitor_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the monitor */
            db()->where('monitor_id', $monitor->monitor_id)->delete('monitors');

            /* Clear the cache */
            cache()->deleteItemsByTag('monitor_id=' . $monitor->monitor_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $monitor->name . '</strong>'));

            redirect('monitors');

        }

        redirect('monitors');
    }

}
