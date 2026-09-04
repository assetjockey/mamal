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

class AdminDomainNames extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'project_id', 'user_id',], ['name', 'target'], ['domain_name_id', 'last_datetime', 'last_check_datetime', 'datetime', 'name', 'target', 'whois_start_datetime', 'whois_updated_datetime', 'whois_end_datetime', 'ssl_start_datetime', 'ssl_end_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_check_datetime', 'whois_start_datetime', 'whois_updated_datetime', 'whois_end_datetime', 'ssl_start_datetime', 'ssl_end_datetime']));
        $filters->set_default_order_by($this->user->preferences->domain_names_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `domain_names` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/domain-names?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $domain_names = [];
        $domain_names_result = database()->query("
            SELECT
                `domain_names`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`,
                JSON_EXTRACT(`domain_names`.`whois`, '$.whois_start_datetime') as `whois_start_datetime`,
                JSON_EXTRACT(`domain_names`.`whois`, '$.whois_updated_datetime') as `whois_updated_datetime`,
                JSON_EXTRACT(`domain_names`.`whois`, '$.whois_end_datetime') as `whois_end_datetime`,
                JSON_EXTRACT(`domain_names`.`ssl`, '$.ssl_start_datetime') as `ssl_start_datetime`,
                JSON_EXTRACT(`domain_names`.`ssl`, '$.ssl_end_datetime') as `ssl_end_datetime`
            FROM
                `domain_names`
            LEFT JOIN
                `users` ON `domain_names`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
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
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'domain_names' => $domain_names,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/domain-names/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/domain-names');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/domain-names');
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

                    foreach($_POST['selected'] as $domain_name_id) {

                        /* Delete the domain_name */
                        db()->where('domain_name_id', $domain_name_id)->delete('domain_names');

                        /* Clear the cache */
                        cache()->deleteItemsByTag('domain_name_id=' . $domain_name_id);

                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/domain-names');
    }

    public function delete() {

        $domain_name_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$domain_name = db()->where('domain_name_id', $domain_name_id)->getOne('domain_names', ['domain_name_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the domain_name */
            db()->where('domain_name_id', $domain_name->domain_name_id)->delete('domain_names');

            /* Clear the cache */
            cache()->deleteItemsByTag('domain_name_id=' . $domain_name_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $domain_name->name . '</strong>'));

        }

        redirect('admin/domain-names');
    }

}
