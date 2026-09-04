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

class ServerMonitors extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->server_monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'project_id'], ['name', 'target'], ['server_monitor_id', 'last_datetime', 'datetime', 'last_log_datetime', 'name', 'total_logs', 'cpu_usage', 'ram_usage', 'disk_usage', 'uptime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_log_datetime']));
        $filters->set_default_order_by($this->user->preferences->server_monitors_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `server_monitors` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('server-monitors?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the server_monitors */
        $server_monitors = [];
        $server_monitors_result = database()->query("
            SELECT
                *
            FROM
                `server_monitors`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}

            {$paginator->get_sql_limit()}
        ");
        while($row = $server_monitors_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');
            $row->notifications = json_decode($row->notifications ?? '');
            $server_monitors[] = $row;
        }

        /* Export handler */
        process_export_csv_new($server_monitors, ['server_monitor_id', 'project_id', 'name', 'target', 'uptime', 'network_total_download', 'network_download', 'network_total_upload', 'network_upload', 'os_name', 'os_version', 'kernel_name', 'kernel_version', 'kernel_release', 'cpu_architecture', 'cpu_usage', 'cpu_model', 'cpu_cores', 'cpu_frequency', 'ram_usage', 'ram_used', 'ram_total', 'disk_usage', 'disk_used', 'disk_total', 'cpu_load_1', 'cpu_load_5', 'cpu_load_15', 'settings', 'notifications', 'total_logs', 'last_log_datetime', 'is_enabled', 'last_datetime', 'datetime'], ['settings', 'notifications'], sprintf(l('server_monitors.title')));
        process_export_json($server_monitors, ['server_monitor_id', 'project_id', 'name', 'target', 'uptime', 'network_total_download', 'network_download', 'network_total_upload', 'network_upload', 'os_name', 'os_version', 'kernel_name', 'kernel_version', 'kernel_release', 'cpu_architecture', 'cpu_usage', 'cpu_model', 'cpu_cores', 'cpu_frequency', 'ram_usage', 'ram_used', 'ram_total', 'disk_usage', 'disk_used', 'disk_total', 'cpu_load_1', 'cpu_load_5', 'cpu_load_15', 'settings', 'notifications', 'total_logs', 'last_log_datetime', 'is_enabled', 'last_datetime', 'datetime'], sprintf(l('server_monitors.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Prepare the view */
        $data = [
            'projects' => $projects,
            'server_monitors' => $server_monitors,
            'total_server_monitors' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('server-monitors/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function duplicate() {
        \Altum\Authentication::guard();

        if(!settings()->monitors_heartbeats->server_monitors_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.server_monitors')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('server-monitors');
        }

        if (empty($_POST)) {
            throw_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('server_monitors', 'COUNT(*)') ?? 0;
        if($this->user->plan_settings->server_monitors_limit != -1 && $total_rows >= $this->user->plan_settings->server_monitors_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('server-monitors');
        }

        $server_monitor_id = (int) $_POST['server_monitor_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');
        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('server-monitors');
        }

        /* Verify the main resource */
        if(!$server_monitor = db()->where('server_monitor_id', $server_monitor_id)->where('user_id', $this->user->user_id)->getOne('server_monitors')) {
            redirect('server-monitors');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Insert to database */
            $server_monitor_id = db()->insert('server_monitors', [
                'user_id' => $this->user->user_id,
                'project_id' => $server_monitor->project_id,
                'name' => string_truncate($server_monitor->name . ' - ' . l('global.duplicated'), 64, null),
                'target' => $server_monitor->target,
                'settings' => $server_monitor->settings,
                'notifications' => $server_monitor->notifications,
                'is_enabled' => 0,
                'datetime' => get_date(),
            ]);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($server_monitor->name) . '</strong>'));

            /* Redirect */
            redirect('server-monitor-update/' . $server_monitor_id);

        }

        redirect('server-monitors');
    }

    public function bulk() {

        if(!settings()->monitors_heartbeats->server_monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('server-monitors');
        }

        if(!isset($_POST['type'])) {
            redirect('server-monitors');
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
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.server_monitors')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('server-monitors');
                    }

                    foreach($_POST['selected'] as $server_monitor_id) {
                        if($server_monitor = db()->where('server_monitor_id', $server_monitor_id)->where('user_id', $this->user->user_id)->getOne('server_monitors', ['server_monitor_id'])) {
                            /* Delete the resource */
                            db()->where('server_monitor_id', $server_monitor->server_monitor_id)->delete('server_monitors');

                            /* Clear the cache */
                            cache()->deleteItemsByTag('server_monitor_id=' . $server_monitor->server_monitor_id);
                            cache()->deleteItem('server_monitor?server_monitor_id=' . $server_monitor->server_monitor_id);
                        }
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('server-monitors');
    }

    public function delete() {

        if(!settings()->monitors_heartbeats->server_monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.server_monitors')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('server-monitors');
        }

        if (empty($_POST)) {
            throw_404();
        }

        $server_monitor_id = (int) $_POST['server_monitor_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('server-monitors');
        }

        /* Make sure the server_monitor id is created by the logged in user */
        if(!$server_monitor = db()->where('server_monitor_id', $server_monitor_id)->where('user_id', $this->user->user_id)->getOne('server_monitors', ['server_monitor_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the server_monitor */
            db()->where('server_monitor_id', $server_monitor->server_monitor_id)->delete('server_monitors');

            /* Clear the cache */
            cache()->deleteItemsByTag('server_monitor_id=' . $server_monitor->server_monitor_id);
            cache()->deleteItem('server_monitor?server_monitor_id=' . $server_monitor->server_monitor_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $server_monitor->name . '</strong>'));

            redirect('server-monitors');

        }

        redirect('server-monitors');
    }

}
