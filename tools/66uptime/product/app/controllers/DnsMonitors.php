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

class DnsMonitors extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->dns_monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'project_id'], ['name', 'target'], ['dns_monitor_id', 'total_checks', 'total_changes', 'last_datetime', 'datetime', 'last_check_datetime', 'name', 'last_change_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_check_datetime', 'last_change_datetime']));
        $filters->set_default_order_by($this->user->preferences->dns_monitors_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `dns_monitors` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('dns-monitors?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the dns_monitors */
        $dns_monitors = [];
        $dns_monitors_result = database()->query("
            SELECT
                *
            FROM
                `dns_monitors`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}

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
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Prepare the view */
        $data = [
            'projects' => $projects,
            'dns_monitors' => $dns_monitors,
            'total_dns_monitors' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('dns-monitors/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function duplicate() {
        \Altum\Authentication::guard();

        if(!settings()->monitors_heartbeats->dns_monitors_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.dns_monitors')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('dns-monitors');
        }

        if (empty($_POST)) {
            throw_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('dns_monitors', 'COUNT(*)') ?? 0;
        if($this->user->plan_settings->dns_monitors_limit != -1 && $total_rows >= $this->user->plan_settings->dns_monitors_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('dns-monitors');
        }

        $dns_monitor_id = (int) $_POST['dns_monitor_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');
        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('dns-monitors');
        }

        /* Verify the main resource */
        if(!$dns_monitor = db()->where('dns_monitor_id', $dns_monitor_id)->where('user_id', $this->user->user_id)->getOne('dns_monitors')) {
            redirect('dns-monitors');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Insert to database */
            $dns_monitor_id = db()->insert('dns_monitors', [
                'user_id' => $this->user->user_id,
                'project_id' => $dns_monitor->project_id,
                'name' => string_truncate($dns_monitor->name . ' - ' . l('global.duplicated'), 64, null),
                'target' => $dns_monitor->target,
                'settings' => $dns_monitor->settings,
                'notifications' => $dns_monitor->notifications,
                'is_enabled' => 0,
                'next_check_datetime' => $dns_monitor->next_check_datetime,
                'datetime' => get_date(),
            ]);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($dns_monitor->name) . '</strong>'));

            /* Redirect */
            redirect('dns-monitor-update/' . $dns_monitor_id);

        }

        redirect('dns-monitors');
    }

    public function bulk() {

        if(!settings()->monitors_heartbeats->dns_monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('dns-monitors');
        }

        if(!isset($_POST['type'])) {
            redirect('dns-monitors');
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
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.dns_monitors')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('dns-monitors');
                    }

                    foreach($_POST['selected'] as $dns_monitor_id) {
                        if($dns_monitor = db()->where('dns_monitor_id', $dns_monitor_id)->where('user_id', $this->user->user_id)->getOne('dns_monitors', ['dns_monitor_id'])) {
                            /* Delete the resource */
                            db()->where('dns_monitor_id', $dns_monitor->dns_monitor_id)->delete('dns_monitors');

                            /* Clear the cache */
                            cache()->deleteItemsByTag('dns_monitor_id=' . $dns_monitor->dns_monitor_id);
                        }
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('dns-monitors');
    }

    public function delete() {

        if(!settings()->monitors_heartbeats->dns_monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.dns_monitors')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('dns-monitors');
        }

        if (empty($_POST)) {
            throw_404();
        }

        $dns_monitor_id = (int) $_POST['dns_monitor_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('dns-monitors');
        }

        /* Make sure the dns_monitor id is created by the logged in user */
        if(!$dns_monitor = db()->where('dns_monitor_id', $dns_monitor_id)->where('user_id', $this->user->user_id)->getOne('dns_monitors', ['dns_monitor_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the dns_monitor */
            db()->where('dns_monitor_id', $dns_monitor->dns_monitor_id)->delete('dns_monitors');

            /* Clear the cache */
            cache()->deleteItemsByTag('dns_monitor_id=' . $dns_monitor->dns_monitor_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $dns_monitor->name . '</strong>'));

            redirect('dns-monitors');

        }

        redirect('dns-monitors');
    }
}
