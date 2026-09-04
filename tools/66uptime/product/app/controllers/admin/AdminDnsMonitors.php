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

class AdminDnsMonitors extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'project_id', 'user_id'], ['name', 'target'], ['dns_monitor_id', 'last_datetime', 'datetime', 'last_check_datetime', 'name', 'last_change_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_check_datetime', 'last_change_datetime']));
        $filters->set_default_order_by($this->user->preferences->dns_monitors_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `dns_monitors` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/dns-monitors?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $dns_monitors = [];
        $dns_monitors_result = database()->query("
            SELECT
                `dns_monitors`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `dns_monitors`
            LEFT JOIN
                `users` ON `dns_monitors`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('dns_monitors')}
                {$filters->get_sql_order_by('dns_monitors')}

            {$paginator->get_sql_limit()}
        ");
        while($row = $dns_monitors_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');
            $row->notifications = json_decode($row->notifications ?? '');
            $row->dns = json_decode($row->dns ?? '');
            $dns_monitors[] = $row;
        }

        /* Export handler */
        process_export_csv_new($dns_monitors, ['dns_monitor_id', 'project_id', 'name', 'target', 'settings', 'notifications', 'dns', 'total_checks', 'total_changes', 'total_dns_types_found', 'total_dns_records_found', 'last_check_datetime', 'next_check_datetime', 'last_change_datetime', 'is_enabled', 'last_datetime', 'datetime'], ['settings', 'notifications', 'dns',], sprintf(l('dns_monitors.title')));
        process_export_json($dns_monitors, ['dns_monitor_id', 'project_id', 'name', 'target', 'settings', 'notifications', 'dns', 'total_checks', 'total_changes', 'total_dns_types_found', 'total_dns_records_found', 'last_check_datetime', 'next_check_datetime', 'last_change_datetime', 'is_enabled', 'last_datetime', 'datetime'], sprintf(l('dns_monitors.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'dns_monitors' => $dns_monitors,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/dns-monitors/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/dns-monitors');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/dns-monitors');
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

                    foreach($_POST['selected'] as $dns_monitor_id) {

                        /* Delete the dns_monitor */
                        db()->where('dns_monitor_id', $dns_monitor_id)->delete('dns_monitors');

                        /* Clear the cache */
                        cache()->deleteItemsByTag('dns_monitor_id=' . $dns_monitor_id);

                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/dns-monitors');
    }

    public function delete() {

        $dns_monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$dns_monitor = db()->where('dns_monitor_id', $dns_monitor_id)->getOne('dns_monitors', ['dns_monitor_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the monitor */
            db()->where('dns_monitor_id', $dns_monitor_id)->delete('dns_monitors');

            /* Clear the cache */
            cache()->deleteItemsByTag('dns_monitor_id=' . $dns_monitor_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $dns_monitor->name . '</strong>'));

        }

        redirect('admin/dns-monitors');
    }

}
