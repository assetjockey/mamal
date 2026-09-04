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
use Altum\Date;
use Altum\Meta;
use Altum\Title;

defined('ALTUMCODE') || die();

class Statistics extends Controller {
    public $website;

    public function index()
    {
        /* Make sure there are no extra URL additions */
        if(isset($this->params[2])) {
            throw_404();
        }

        $pixel_key = isset($this->params[0]) ? input_clean($this->params[0]) : null;

        if(!$website = (new \Altum\Models\Website())->get_website_by_pixel_key($pixel_key)) {
            throw_404();
        }

        if(!$website->public_statistics_is_enabled) {
            throw_404();
        }

        $website->annotations = json_decode($website->annotations ?? '[]');

        $this->website = $website;

        /* Check if the user has access to the page */
        $has_access = !$website->public_statistics_password || ($website->public_statistics_password && isset($_COOKIE['website_public_statistics_password_' . $website->website_id]) && $_COOKIE['website_public_statistics_password_' . $website->website_id] == $website->public_statistics_password);

        /* Meta */
        $this->website->full_url = (isset(\Altum\Router::$data['domain']) ? \Altum\Router::$data['domain']->url : url()) . 'statistics/' . $pixel_key;
        Meta::set_canonical_url($this->website->full_url);

        /* Check if the password form is submitted */
        if(!$has_access && !empty($_POST)) {

            /* Check for any errors */
            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!password_verify($_POST['password'], $website->public_statistics_password)) {
                Alerts::add_field_error('password', l('statistics.password.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Set a cookie */
                setcookie('website_public_statistics_password_' . $website->website_id, $website->public_statistics_password, time() + 60 * 60 * 24 * 30);

                header('Location: ' . $_SERVER['REQUEST_URI']);
                die();

            }

        }

        /* Display the password form */
        if(!$has_access) {

            /* Set a custom title */
            Title::set(l('statistics.password.title'));

            /* Main View */
            $data = [
                'website' => $website,
            ];

            $view = new \Altum\View('dashboard/password', (array)$this);
            $this->add_view_content('content', $view->run($data));

        }

        /* Show statistics */
        else {
            /* Subpage */
            $type = isset($this->params[1]) && in_array($this->params[1], ['paths', 'referrers', 'screen-resolutions', 'utms', 'operating-systems', 'device-types', 'continents', 'countries', 'cities', 'browser-names', 'browser-languages', 'browser-timezones', 'goals', 'realtime', 'themes', 'outbound-clicks']) ? query_clean(str_replace('-', '_', $this->params[1])) : 'default';

            $base_url_path = 'statistics/' . $website->pixel_key . '/';

            /* Custom realtime page */
            if($type == 'realtime') {
                /* Prepare the view */
                $data = [
                    'base_url_path' => $base_url_path,
                ];

                $view = new \Altum\View('realtime/index', (array)$this);

                $this->add_view_content('content', $view->run($data));
            } else {
                /* Load data based on the website type */
                $dashboard = $this->{$this->website->tracking_type}();

                /* Outbound Clicks Modal */
                $view = new \Altum\View('dashboard/outbound_clicks_paths_modal', (array)$this);
                \Altum\Event::add_content($view->run(), 'modals');

                /* Referrer Paths Modal */
                $view = new \Altum\View('dashboard/referrer_paths_modal', (array)$this);
                \Altum\Event::add_content($view->run(), 'modals');

                /* UTMs medium campaign Modal */
                $view = new \Altum\View('dashboard/utms_medium_campaign_modal', (array)$this);
                \Altum\Event::add_content($view->run(), 'modals');

                /* Cities Modal */
                $view = new \Altum\View('dashboard/cities_modal', (array)$this);
                \Altum\Event::add_content($view->run(), 'modals');

                /* Create Goal Modal */
                $view = new \Altum\View('dashboard/goal_create_modal', (array)$this);
                \Altum\Event::add_content($view->run(), 'modals');

                /* Update Goal Modal */
                $view = new \Altum\View('dashboard/goal_update_modal', (array)$this);
                \Altum\Event::add_content($view->run(), 'modals');

                /* Set a custom title */
                if($type == 'default') {
                    Title::set(sprintf(l('statistics.title'), $this->website->name));
                } else {
                    Title::set(sprintf(l('statistics.title_dynamic'), l('dashboard.' . $type . '.header'), $this->website->name));
                }

                /* Prepare the inside content View */
                $data = [
                    'logs' => $dashboard['logs'],
                    'has_logs' => count($dashboard['logs']),
                    'basic_totals' => $dashboard['basic_totals'],
                    'logs_chart' => $dashboard['logs_chart'],
                    'base_url_path' => $base_url_path,
                ];

                $view = new \Altum\View('dashboard/partials/' . $type, (array)$this);
                $this->add_view_content('dashboard_content', $view->run($data));

                /* Prepare the view */
                $data = [
                    'datetime' => $dashboard['datetime'],
                    'logs' => $dashboard['logs'],
                    'has_logs' => count($dashboard['logs']),
                    'basic_totals' => $dashboard['basic_totals'],
                    'logs_chart' => $dashboard['logs_chart'],
                    'type' => $type,
                    'base_url_path' => $base_url_path,
                ];

                $view = new \Altum\View('dashboard/index', (array)$this);
                $this->add_view_content('content', $view->run($data));
            }
        }
    }

    private function advanced() {
        /* Establish the start and end date for the statistics */
        list($start_date, $end_date) = AnalyticsFilters::get_date();

        $datetime = \Altum\Date::get_start_end_dates_new($start_date, $end_date, Date::$default_timezone);

        /* Get basic overall data */
        $logs = [];
        $logs_chart = [];
        $basic_totals = [
            'pageviews' => 0,
            'sessions'  => 0,
            'visitors'  => 0
        ];

        $filters = AnalyticsFilters::get_filters_sql(['websites_visitors', 'sessions_events']);

        $convert_tz_sql = get_convert_tz_sql('`sessions_events`.`date`', $this->user->timezone ?? \Altum\Date::$default_timezone);

        /* Apply different query when filters are applied */
        if($filters) {
            $result = database()->query("
                SELECT
                    COUNT(*) AS `pageviews`,
                    COUNT(DISTINCT `sessions_events`.`session_id`) AS `sessions`,
                    COUNT(DISTINCT `sessions_events`.`visitor_id`) AS `visitors`,
                    DATE_FORMAT({$convert_tz_sql}, '{$datetime['query_date_format']}') AS `formatted_date`
                FROM
                    `sessions_events`
                LEFT JOIN
                    `websites_visitors` ON `sessions_events`.`visitor_id` = `websites_visitors`.`visitor_id`
                WHERE
                    `sessions_events`.`website_id` = {$this->website->website_id}
                    AND (`sessions_events`.`date` >= '{$datetime['query_start_date']}' AND `sessions_events`.`date` < '{$datetime['query_end_date']}')
                    {$filters}
                GROUP BY
                    `formatted_date`
            ");
        } else {
            $result = database()->query("
                SELECT
                    COUNT(*) AS `pageviews`,
                    COUNT(DISTINCT `sessions_events`.`session_id`) AS `sessions`,
                    COUNT(DISTINCT `sessions_events`.`visitor_id`) AS `visitors`,
                    DATE_FORMAT({$convert_tz_sql}, '{$datetime['query_date_format']}') AS `formatted_date`
                FROM
                    `sessions_events`
                WHERE
                    `sessions_events`.`website_id` = {$this->website->website_id}
                    AND (`sessions_events`.`date` >= '{$datetime['query_start_date']}' AND `sessions_events`.`date` < '{$datetime['query_end_date']}')
                GROUP BY
                    `formatted_date`
            ");
        }

        /* Generate the raw chart data and save logs for later usage */
        while($row = $result->fetch_object()) {
            $logs[] = $row;

            $formatted_date = $datetime['process']($row->formatted_date, true);

            /* Insert data for the chart */
            $logs_chart[$formatted_date] = [
                'pageviews' => $row->pageviews,
                'sessions'  => $row->sessions,
                'visitors'  => $row->visitors,
            ];

            /* Sum for basic totals */
            $basic_totals['pageviews'] += $row->pageviews;
            $basic_totals['sessions'] += $row->sessions;
        }

        $logs_chart = get_chart_data($logs_chart);

        /* Apply different query when filters are applied */
        if($filters) {
            $basic_totals['visitors'] = database()->query("
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
        } else {
            $basic_totals['visitors'] = database()->query("
                SELECT
                    COUNT(DISTINCT `visitors_sessions`.`visitor_id`) AS `total`
                FROM
                    `visitors_sessions`
                WHERE
                    `visitors_sessions`.`website_id` = {$this->website->website_id}
                    AND `visitors_sessions`.`date` >= '{$datetime['query_start_date']}' AND `visitors_sessions`.`date` < '{$datetime['query_end_date']}'
            ")->fetch_object()->total ?? 0;
        }

        return [
            'datetime' => $datetime,
            'logs' => $logs,
            'basic_totals' => $basic_totals,
            'logs_chart' => $logs_chart
        ];
    }

    private function lightweight() {
        /* Establish the start and end date for the statistics */
        list($start_date, $end_date) = AnalyticsFilters::get_date();

        $datetime = \Altum\Date::get_start_end_dates_new($start_date, $end_date, Date::$default_timezone);

        /* Get basic overall data */
        $logs = [];
        $logs_chart = [];
        $basic_totals = [
            'pageviews' => 0,
            'visitors'  => 0
        ];

        $filters = AnalyticsFilters::get_filters_sql(['lightweight_events']);

        $convert_tz_sql = get_convert_tz_sql('`date`', $this->user->timezone ?? \Altum\Date::$default_timezone);

        $result = database()->query("
            SELECT
                COUNT(*) AS `pageviews`,
                SUM(CASE WHEN `type` = 'landing_page' THEN 1 ELSE 0 END) AS `visitors`,
                DATE_FORMAT({$convert_tz_sql}, '{$datetime['query_date_format']}') AS `formatted_date`
            FROM
                `lightweight_events`
            WHERE
                `website_id` = {$this->website->website_id}
                AND (`date` >= '{$datetime['query_start_date']}' AND `date` < '{$datetime['query_end_date']}')
                {$filters}
            GROUP BY
                `formatted_date`
        ");

        /* Generate the raw chart data and save logs for later usage */
        while($row = $result->fetch_object()) {
            $logs[] = $row;

            $formatted_date = $datetime['process']($row->formatted_date, true);

            /* Insert data for the chart */
            $logs_chart[$formatted_date] = [
                'pageviews' => $row->pageviews,
                'visitors'  => $row->visitors,
                'labels_alt' => $formatted_date
            ];

            /* Sum for basic totals */
            $basic_totals['pageviews'] += $row->pageviews;
            $basic_totals['visitors'] += $row->visitors;
        }

        $logs_chart = get_chart_data($logs_chart);

        return [
            'datetime' => $datetime,
            'logs' => $logs,
            'basic_totals' => $basic_totals,
            'logs_chart' => $logs_chart
        ];
    }

}
