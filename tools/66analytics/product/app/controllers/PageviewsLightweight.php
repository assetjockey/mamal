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
use Altum\Title;

defined('ALTUMCODE') || die();

class PageviewsLightweight extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!$this->website) {
            redirect('pageviews');
        }

        if($this->website->tracking_type == 'advanced') {
            redirect('pageviews-advanced');
        }

        /* Establish the start and end date for the statistics */
        list($start_date, $end_date) = AnalyticsFilters::get_date();

        $datetime = \Altum\Date::get_start_end_dates_new($start_date, $end_date);

        /* Filters */
        $filters = AnalyticsFilters::get_filters_sql(['lightweight_events']);

        /* Prepare the paginator */
        $total_rows = database()->query("
            SELECT
                COUNT(*) AS `total`
            FROM
                `lightweight_events`
            WHERE
                `lightweight_events`.`website_id` = {$this->website->website_id}
                AND `lightweight_events`.`date` >= '{$datetime['query_start_date']}' AND `lightweight_events`.`date` < '{$datetime['query_end_date']}'
                {$filters}
        ")->fetch_object()->total ?? 0;

        $paginator = (new \Altum\Paginator($total_rows, settings()->main->default_results_per_page, $_GET['page'] ?? 1, url('pageviews-lightweight?page={{PAGE}}')));

        /* Get the websites list for the user */
        $pageviews = [];
        $pageviews_result = database()->query("
            SELECT
                `lightweight_events`.*
            FROM
            	`lightweight_events`
			WHERE
			     `lightweight_events`.`website_id` = {$this->website->website_id}
                AND `lightweight_events`.`date` >= '{$datetime['query_start_date']}' AND `lightweight_events`.`date` < '{$datetime['query_end_date']}'
                {$filters}
            ORDER BY
                `lightweight_events`.`event_id` DESC
            {$paginator->get_sql_limit()}
        ");
        while($row = $pageviews_result->fetch_object()) {
            $pageviews[] = $row;
        }

        /* Set a custom title */
        Title::set(l('pageviews.title'));

        /* Export handler */
        process_export_csv($pageviews, ['event_id', 'website_id', 'type', 'path', 'referrer_host', 'referrer_path', 'utm_source', 'utm_medium', 'utm_campaign', 'continent_code', 'country_code', 'city_name', 'os_name', 'browser_name', 'browser_language', 'browser_timezone', 'screen_resolution', 'device_type', 'theme', 'date'], sprintf(l('pageviews.title')));
        process_export_json($pageviews, ['event_id', 'website_id', 'type', 'path', 'referrer_host', 'referrer_path', 'utm_source', 'utm_medium', 'utm_campaign', 'continent_code', 'country_code', 'city_name', 'os_name', 'browser_name', 'browser_language', 'browser_timezone', 'screen_resolution', 'device_type', 'theme', 'date'], sprintf(l('pageviews.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'datetime' => $datetime,
            'total_rows' => $total_rows,
            'pagination' => $pagination,
            'pageviews' => $pageviews
        ];

        $view = new \Altum\View('pageviews-lightweight/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        if(!$this->website) {
            redirect('pageviews');
        }

        if($this->team) {
            redirect('pageviews');
        }

        \Altum\Authentication::guard();

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('pageviews');
        }

        if(!isset($_POST['type'])) {
            redirect('pageviews');
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

                    foreach($_POST['selected'] as $event_id) {
                        db()->where('event_id', $event_id)->where('website_id', $this->website->website_id)->delete('lightweight_events');
                    }

                    db()->where('website_id', $this->website->website_id)->update('websites', [
                        'pageviews_stats_last_datetime' => null,
                    ]);

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('pageviews');
    }

    public function delete() {

        if(!$this->website) {
            redirect('pageviews');
        }

        if($this->team) {
            redirect('pageviews');
        }

        \Altum\Authentication::guard();

        if (empty($_POST)) {
            throw_404();
        }

        $event_id = (int) $_POST['event_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Database query */
            db()->where('event_id', $event_id)->where('website_id', $this->website->website_id)->delete('lightweight_events');
            db()->where('website_id', $this->website->website_id)->update('websites', [
                'pageviews_stats_last_datetime' => null,
            ]);

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.delete2'));

            redirect('pageviews');
        }

        redirect('pageviews');
    }

}
