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

class DomainNames extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->domain_names_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'project_id'], ['name', 'target'], ['domain_name_id', 'last_datetime', 'datetime', 'name', 'target', 'whois_start_datetime', 'whois_updated_datetime', 'whois_end_datetime', 'ssl_start_datetime', 'ssl_end_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'whois_start_datetime', 'whois_updated_datetime', 'whois_end_datetime', 'ssl_start_datetime', 'ssl_end_datetime']));
        $filters->set_default_order_by($this->user->preferences->domain_names_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `domain_names` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('domain-names?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the domain names */
        $domain_names = [];
        $domain_names_result = database()->query("
            SELECT
                *,
                JSON_EXTRACT(`whois`, '$.whois_start_datetime') as `whois_start_datetime`,
                JSON_EXTRACT(`whois`, '$.whois_updated_datetime') as `whois_updated_datetime`,
                JSON_EXTRACT(`whois`, '$.whois_end_datetime') as `whois_end_datetime`,
                JSON_EXTRACT(`ssl`, '$.ssl_start_datetime') as `ssl_start_datetime`,
                JSON_EXTRACT(`ssl`, '$.ssl_end_datetime') as `ssl_end_datetime`
            FROM
                `domain_names`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}

            {$paginator->get_sql_limit()}
        ");
        while($row = $domain_names_result->fetch_object()) {
            $row->whois = json_decode($row->whois ?? '');
            $row->whois_notifications = json_decode($row->whois_notifications ?? []);
            $row->ssl = json_decode($row->ssl ?? '');
            $row->ssl_notifications = json_decode($row->ssl_notifications ?? []);
            $domain_names[] = $row;
        }

        /* Export handler */
        process_export_csv_new($domain_names, ['domain_name_id', 'project_id', 'name', 'target', 'whois', 'whois_notifications', 'ssl', 'ssl_notifications', 'total_checks', 'last_check_datetime', 'next_check_datetime', 'is_enabled', 'datetime', 'last_datetime'], ['whois', 'whois_notifications', 'ssl', 'ssl_notifications',], sprintf(l('domain_names.title')));
        process_export_json($domain_names, ['domain_name_id', 'project_id', 'name', 'target', 'whois', 'whois_notifications', 'ssl', 'ssl_notifications', 'total_checks', 'last_check_datetime', 'next_check_datetime', 'is_enabled', 'datetime', 'last_datetime'], sprintf(l('domain_names.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Prepare the view */
        $data = [
            'projects' => $projects,
            'domain_names' => $domain_names,
            'total_domain_names' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('domain-names/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function duplicate() {
        \Altum\Authentication::guard();

        if(!settings()->monitors_heartbeats->domain_names_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.domain_names')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('domain-names');
        }

        if (empty($_POST)) {
            throw_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('domain_names', 'COUNT(*)') ?? 0;
        if($this->user->plan_settings->domain_names_limit != -1 && $total_rows >= $this->user->plan_settings->domain_names_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('domain-names');
        }

        $domain_name_id = (int) $_POST['domain_name_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');
        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('domain-names');
        }

        /* Verify the main resource */
        if(!$domain_name = db()->where('domain_name_id', $domain_name_id)->where('user_id', $this->user->user_id)->getOne('domain_names')) {
            redirect('domain-names');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Insert to database */
            $domain_name_id = db()->insert('domain_names', [
                'user_id' => $this->user->user_id,
                'project_id' => $domain_name->project_id,
                'name' => string_truncate($domain_name->name . ' - ' . l('global.duplicated'), 64, null),
                'target' => $domain_name->target,
                'ssl_port' => $domain_name->ssl_port,
                'whois_notifications' => $domain_name->whois_notifications,
                'ssl_notifications' => $domain_name->ssl_notifications,
                'is_enabled' => 0,
                'datetime' => get_date(),
            ]);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($domain_name->name) . '</strong>'));

            /* Redirect */
            redirect('domain-name-update/' . $domain_name_id);

        }

        redirect('domain-names');
    }

    public function bulk() {

        if(!settings()->monitors_heartbeats->domain_names_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('domain-names');
        }

        if(!isset($_POST['type'])) {
            redirect('domain-names');
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
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.domain_names')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('domain-names');
                    }

                    foreach($_POST['selected'] as $domain_name_id) {
                        if($domain_name = db()->where('domain_name_id', $domain_name_id)->where('user_id', $this->user->user_id)->getOne('domain_names', ['domain_name_id'])) {
                            /* Delete the resource */
                            db()->where('domain_name_id', $domain_name->domain_name_id)->delete('domain_names');

                            /* Clear the cache */
                            cache()->deleteItemsByTag('domain_name_id=' . $domain_name->domain_name_id);
                        }
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('domain-names');
    }

    public function delete() {

        if(!settings()->monitors_heartbeats->domain_names_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.domain_names')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('domain-names');
        }

        if (empty($_POST)) {
            throw_404();
        }

        $domain_name_id = (int) $_POST['domain_name_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        /* Make sure the domain name id is created by the logged in user */
        if(!$domain_name = db()->where('domain_name_id', $domain_name_id)->where('user_id', $this->user->user_id)->getOne('domain_names')) {
            redirect('domain-names');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the domain_name */
            db()->where('domain_name_id', $domain_name->domain_name_id)->delete('domain_names');

            /* Clear the cache */
            cache()->deleteItemsByTag('domain_name_id=' . $domain_name->domain_name_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $domain_name->name . '</strong>'));

            redirect('domain-names');

        }

        redirect('domain-names');
    }

}
