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

class AdminTransferRequests extends Controller {

    public function index() {

        if(!settings()->transfers->transfer_requests_is_enabled) {
            throw_404();
        }

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['user_id', 'domain_id', 'project_id', 'pixels_ids'], ['name', 'description', 'url'], ['transfer_request_id', 'last_submitted_datetime', 'expiration_datetime', 'last_datetime', 'datetime', 'name', 'url', 'pageviews', 'total_submissions', 'total_files', 'total_size'], [], ['pixels_ids' => 'json_contains'], allowed_datetime_fields: ['datetime', 'last_datetime', 'expiration_datetime', 'last_submitted_datetime']));
        $filters->set_default_order_by($this->user->preferences->transfer_requests_default_order_by ?? 'transfer_request_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `transfer_requests` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/transfer-requests?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $transfer_requests = [];
        $transfer_requests_result = database()->query("
            SELECT
                `transfer_requests`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `transfer_requests`
            LEFT JOIN
                `users` ON `transfer_requests`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('transfer_requests')}
                {$filters->get_sql_order_by('transfer_requests')}

            {$paginator->get_sql_limit()}
        ");
        while($row = $transfer_requests_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');
            $transfer_requests[] = $row;
        }

        /* Export handler */
        process_export_csv_new($transfer_requests, ['transfer_request_id', 'domain_id', 'project_id', 'user_id', 'pixels_ids', 'name', 'description', 'url', 'settings', 'total_submissions', 'total_files', 'total_size', 'pageviews', 'expiration_datetime', 'last_submitted_datetime', 'datetime', 'last_datetime'], ['settings'], sprintf(l('transfer_requests.title')));
        process_export_json($transfer_requests, ['transfer_request_id', 'domain_id', 'project_id', 'user_id', 'pixels_ids', 'name', 'description', 'url', 'settings', 'total_submissions', 'total_files', 'total_size', 'pageviews', 'expiration_datetime', 'last_submitted_datetime', 'datetime', 'last_datetime'], sprintf(l('transfer_requests.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'transfer_requests' => $transfer_requests,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/transfer-requests/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        if(!settings()->transfers->transfer_requests_is_enabled) {
            throw_404();
        }

        /* Check for any errors */
        if(empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/transfer-requests');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/transfer-requests');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            set_time_limit(0);

            session_write_close();

            $_POST['selected'] = is_array($_POST['selected']) ? array_filter(array_unique(array_map('intval', $_POST['selected']))) : [];

            switch($_POST['type']) {
                case 'delete':

                    (new \Altum\Models\TransferRequests())->bulk_delete($_POST['selected']);

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/transfer-requests');
    }

    public function delete() {

        if(!settings()->transfers->transfer_requests_is_enabled) {
            throw_404();
        }

        $transfer_request_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$transfer_request = db()->where('transfer_request_id', $transfer_request_id)->getOne('transfer_requests', ['transfer_request_id', 'user_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new \Altum\Models\TransferRequests())->delete($transfer_request->transfer_request_id, $transfer_request->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $transfer_request->name . '</strong>'));

        }

        redirect('admin/transfer-requests');
    }

}
