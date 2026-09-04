<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
 *
 * This software is proprietary software owned and licensed by AltumCode.
 * A valid license is required to use, modify, or distribute this software.
 * Unauthorized use, reproduction, modification, or distribution is prohibited.
 *
 * 🌍 Explore all AltumCode projects: https://altumcode.com/
 * 📧 Support & general inquiries: https://altumcode.com/contact
 * 📤 Download the latest version: https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 */
namespace Altum\Controllers;

use Altum\Alerts;
use Altum\AnalyticsFilters;

defined('ALTUMCODE') || die();

class Visitors extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!$this->website || ($this->website && $this->website->tracking_type == 'lightweight')) {
            redirect('websites');
        }

        /* Establish the start and end date for the statistics */
        list($start_date, $end_date) = AnalyticsFilters::get_date();

        $datetime = \Altum\Date::get_start_end_dates_new($start_date, $end_date);

        /* Filters */
        $filters = AnalyticsFilters::get_filters_sql(['websites_visitors']);

        /* Average time per session */
        $average_time_per_session = database()->query("
            SELECT
                AVG(`seconds`) AS `average`
            FROM
                (
                    SELECT
                        TIMESTAMPDIFF(SECOND, MIN(`sessions_events`.`date`), MAX(`sessions_events`.`date`)) AS `seconds`
                    FROM
                        `sessions_events`
                    LEFT JOIN
                        `websites_visitors` ON `sessions_events`.`visitor_id` = `websites_visitors`.`visitor_id`
                    WHERE
                        `sessions_events`.`website_id` = {$this->website->website_id}
                        AND `sessions_events`.`date` >= '{$datetime['query_start_date']}' AND `sessions_events`.`date` < '{$datetime['query_end_date']}'
                        {$filters}
                    GROUP BY `sessions_events`.`session_id`
                ) AS `seconds`
        ")->fetch_object()->average ?? 0;

        /* Prepare the paginator */
        $total_rows = database()->query("
            SELECT
                COUNT(DISTINCT `visitors_sessions`.`visitor_id`) AS `total`
            FROM
                `visitors_sessions`
            LEFT JOIN
                `sessions_events` ON `visitors_sessions`.`visitor_id` = `sessions_events`.`visitor_id`
            LEFT JOIN
                `websites_visitors` ON `visitors_sessions`.`visitor_id` = `websites_visitors`.`visitor_id`
            WHERE
                `visitors_sessions`.`website_id` = {$this->website->website_id}
                AND `visitors_sessions`.`date` >= '{$datetime['query_start_date']}' AND `visitors_sessions`.`date` < '{$datetime['query_end_date']}'
                {$filters}
        ")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, settings()->main->default_results_per_page, $_GET['page'] ?? 1, url('visitors?page={{PAGE}}')));

        /* Determine the average sessions per user */
        $total_sessions = 0;

        /* Get the websites list for the user */
        $visitors = [];
        $visitors_result = database()->query("
            SELECT
                `websites_visitors`.*
            FROM
            	`visitors_sessions`
            LEFT JOIN
            	`websites_visitors` ON `visitors_sessions`.`visitor_id` = `websites_visitors`.`visitor_id`
			WHERE
			     `visitors_sessions`.`website_id` = {$this->website->website_id}
                AND `visitors_sessions`.`date` >= '{$datetime['query_start_date']}' AND `visitors_sessions`.`date` < '{$datetime['query_end_date']}'
                {$filters}
			GROUP BY
				`visitor_id`
            ORDER BY
                `websites_visitors`.`last_date` DESC
            {$paginator->get_sql_limit()}
        ");
        while($row = $visitors_result->fetch_object()) {
            $row->goals_conversions_ids = json_decode($row->goals_conversions_ids ?? '[]');
            $row->total_goals_conversions = count($row->goals_conversions_ids);
            $visitors[] = $row;
            $total_sessions += $row->total_sessions;
        }

        /* Average sessions per visitor */
        $average_sessions_per_visitor = $total_sessions && count($visitors) ? $total_sessions / count($visitors) : 0;

        /* Export handler */
        process_export_csv($visitors, ['visitor_id', 'visitor_uuid_binary', 'website_id', 'ip', 'continent_code', 'country_code', 'city_name', 'os_name', 'os_version', 'browser_name', 'browser_version', 'browser_language', 'browser_timezone', 'screen_resolution', 'device_type', 'total_sessions', 'total_goals_conversions', 'date', 'last_date'], sprintf(l('visitors.title')));
        process_export_json($visitors, ['visitor_id', 'visitor_uuid_binary', 'website_id', 'ip', 'custom_parameters', 'continent_code', 'country_code', 'city_name', 'os_name', 'os_version', 'browser_name', 'browser_version', 'browser_language', 'browser_timezone', 'screen_resolution', 'device_type', 'total_sessions', 'total_goals_conversions', 'date', 'last_date'], sprintf(l('visitors.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'datetime' => $datetime,
            'total_rows' => $total_rows,
            'average_time_per_session' => $average_time_per_session,
            'average_sessions_per_visitor' => $average_sessions_per_visitor,
            'pagination' => $pagination,
            'visitors' => $visitors
        ];

        $view = new \Altum\View('visitors/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        if(!$this->website || ($this->website && $this->website->tracking_type == 'lightweight')) {
            redirect('websites');
        }

        if($this->team) {
            redirect('websites');
        }

        \Altum\Authentication::guard();

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('visitors');
        }

        if(!isset($_POST['type'])) {
            redirect('visitors');
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

					db()->where('visitor_id', $_POST['selected'], 'IN')->where('website_id', $this->website->website_id)->delete('websites_visitors');

                    break;
            }

            /* Clear the cache */
            cache()->deleteItemsByTag('website_id=' . $this->website->website_id);

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('visitors');
    }

    public function delete() {

        if(!$this->website || ($this->website && $this->website->tracking_type == 'lightweight')) {
            redirect('websites');
        }

        if($this->team) {
            redirect('websites');
        }

        \Altum\Authentication::guard();

        if (empty($_POST)) {
            throw_404();
        }

        $visitor_id = (int) $_POST['visitor_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$visitor = db()->where('visitor_id', $visitor_id)->where('website_id', $this->website->website_id)->getOne('websites_visitors', ['visitor_id', 'visitor_uuid_binary'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Database query */
            db()->where('visitor_id', $visitor_id)->delete('websites_visitors');

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.delete2'));

            /* Clear the cache */
            cache()->deleteItem('visitor?visitor_uuid=' . md5($visitor->visitor_uuid_binary));

            redirect('visitors');
        }

        redirect('visitors');
    }

}
