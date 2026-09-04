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

class AdminServerMonitors extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'project_id', 'user_id'], ['name', 'target'], ['server_monitor_id', 'last_datetime', 'datetime', 'last_log_datetime', 'name', 'total_logs', 'cpu_usage', 'ram_usage', 'disk_usage', 'uptime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_log_datetime']));
        $filters->set_default_order_by($this->user->preferences->server_monitors_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `server_monitors` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/server-monitors?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $server_monitors = [];
        $server_monitors_result = database()->query("
            SELECT
                `server_monitors`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `server_monitors`
            LEFT JOIN
                `users` ON `server_monitors`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('server_monitors')}
                {$filters->get_sql_order_by('server_monitors')}

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
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'server_monitors' => $server_monitors,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/server-monitors/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/server-monitors');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/server-monitors');
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

                    foreach($_POST['selected'] as $server_monitor_id) {

                        /* Delete the server_monitor */
                        db()->where('server_monitor_id', $server_monitor_id)->delete('server_monitors');

                        /* Clear the cache */
                        cache()->deleteItemsByTag('server_monitor_id=' . $server_monitor_id);
                        cache()->deleteItem('server_monitor?server_monitor_id=' . $server_monitor_id);


                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/server-monitors');
    }

    public function delete() {

        $server_monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$server_monitor = db()->where('server_monitor_id', $server_monitor_id)->getOne('server_monitors', ['server_monitor_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the monitor */
            db()->where('server_monitor_id', $server_monitor_id)->delete('server_monitors');

            /* Clear the cache */
            cache()->deleteItemsByTag('server_monitor_id=' . $server_monitor->server_monitor_id);
            cache()->deleteItem('server_monitor?server_monitor_id=' . $server_monitor->server_monitor_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $server_monitor->name . '</strong>'));

        }

        redirect('admin/server-monitors');
    }

}
