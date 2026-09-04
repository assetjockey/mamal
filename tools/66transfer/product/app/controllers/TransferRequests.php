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

class TransferRequests extends Controller {

    public function index() {
        \Altum\Authentication::guard();

        if(!settings()->transfers->transfer_requests_is_enabled) {
            throw_404();
        }

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user, false);

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['domain_id', 'project_id', 'pixels_ids'], ['name', 'description', 'url'], ['transfer_request_id', 'last_submitted_datetime', 'expiration_datetime', 'last_datetime', 'datetime', 'name', 'url', 'pageviews', 'total_submissions', 'total_files', 'total_size'], [], ['pixels_ids' => 'json_contains'], allowed_datetime_fields: ['datetime', 'last_datetime', 'expiration_datetime', 'last_submitted_datetime']));
        $filters->set_default_order_by($this->user->preferences->transfer_requests_default_order_by ?? 'transfer_request_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `transfer_requests` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('transfer-requests?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the transfer requests */
        $transfer_requests = [];
        $transfer_requests_result = database()->query("
            SELECT
                *
            FROM
                `transfer_requests`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
            {$filters->get_sql_order_by()}
            {$paginator->get_sql_limit()}
        ");

        while($row = $transfer_requests_result->fetch_object()) {
            $row->full_url = (new \Altum\Models\TransferRequests())->get_transfer_request_full_url($row, $this->user, $domains);
            $row->settings = json_decode($row->settings ?? '');
            $transfer_requests[] = $row;
        }

        /* Export handler */
        process_export_csv_new($transfer_requests, ['transfer_request_id', 'domain_id', 'project_id', 'user_id', 'pixels_ids', 'name', 'description', 'url', 'settings', 'total_submissions', 'total_files', 'total_size', 'pageviews', 'expiration_datetime', 'last_submitted_datetime', 'datetime', 'last_datetime'], ['settings'], sprintf(l('transfer_requests.title')));
        process_export_json($transfer_requests, ['transfer_request_id', 'domain_id', 'project_id', 'user_id', 'pixels_ids', 'name', 'description', 'url', 'settings', 'total_submissions', 'total_files', 'total_size', 'pageviews', 'expiration_datetime', 'last_submitted_datetime', 'datetime', 'last_datetime'], sprintf(l('transfer_requests.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get statistics */
        if(count($transfer_requests) && !$filters->has_applied_filters) {
            $start_date_query = (new \DateTime())->modify('-' . (settings()->main->chart_days ?? 30) . ' day')->format('Y-m-d');
            $end_date_query = (new \DateTime('tomorrow'))->modify('+1 day')->format('Y-m-d');

            $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

            $statistics_result_query = "
                SELECT
                    COUNT(`id`) AS `pageviews`,
                    SUM(`is_unique`) AS `visitors`,
                    DATE_FORMAT({$convert_tz_sql}, '%Y-%m-%d') AS `formatted_date`
                FROM
                    `statistics`
                WHERE   
                    `user_id` = {$this->user->user_id} 
                    AND `transfer_request_id` IS NOT NULL
                    AND ({$convert_tz_sql} BETWEEN '{$start_date_query}' AND '{$end_date_query}')
                GROUP BY
                    `formatted_date`
                ORDER BY
                    `formatted_date`
            ";

            $transfer_requests_chart = \Altum\Cache::cache_function_result('statistics?user_id=' . $this->user->user_id, null, function() use ($statistics_result_query) {
                $transfer_requests_chart = [];

                $statistics_result = database()->query($statistics_result_query);

                while($row = $statistics_result->fetch_object()) {
                    $label = \Altum\Date::get($row->formatted_date, 5, \Altum\Date::$default_timezone);

                    $transfer_requests_chart[$label] = [
                        'pageviews' => $row->pageviews,
                        'visitors' => $row->visitors
                    ];
                }

                return $transfer_requests_chart;
            }, 60 * 60 * (settings()->main->chart_cache ?? 12));

            $transfer_requests_chart = get_chart_data($transfer_requests_chart);
        }

        /* Prepare the view */
        $data = [
            'transfer_requests_chart' => $transfer_requests_chart ?? null,
            'projects' => $projects,
            'domains' => $domains,
            'transfer_requests' => $transfer_requests,
            'total_transfer_requests' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('transfer-requests/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }

    public function bulk() {

        \Altum\Authentication::guard();

        if(!settings()->transfers->transfer_requests_is_enabled) {
            throw_404();
        }

        /* Check for any errors */
        if(empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('transfer-requests');
        }

        if(!isset($_POST['type'])) {
            redirect('transfer-requests');
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

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.transfer_requests')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('transfer-requests');
                    }

                    /* Delete all valid transfer_requests */
                    (new \Altum\Models\TransferRequests())->bulk_delete($_POST['selected'], $this->user->user_id);

                    break;
            }

            session_start();

            Alerts::add_success(l('bulk_delete_modal.success_message'));
        }

        redirect('transfer-requests');
    }

    public function delete() {

        if(!settings()->transfers->transfer_requests_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.transfer_requests')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('transfer-requests');
        }

        if(empty($_POST)) {
            throw_404();
        }

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('transfer-requests');
        }

        $transfer_request_id = (int) $_POST['transfer_request_id'];

        /* Get transfer request details */
        if(!$transfer_request = db()->where('transfer_request_id', $transfer_request_id)->getOne('transfer_requests')) {
            throw_404();
        }

        /* Make sure the current user has access */
        if(($transfer_request->uploader_id != md5(get_ip())) && (!$transfer_request->user_id || $transfer_request->user_id != $this->user->user_id)) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new \Altum\Models\TransferRequests())->delete($transfer_request->transfer_request_id, $this->user->user_id);

            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $transfer_request->name . '</strong>'));

            redirect('transfer-requests');
        }

        redirect('transfer-requests');
    }

}
