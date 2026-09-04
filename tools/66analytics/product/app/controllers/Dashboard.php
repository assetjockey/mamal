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
use Altum\Title;

defined('ALTUMCODE') || die();

class Dashboard extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!$this->website) {
            redirect('websites');
        }

        $this->website->annotations = json_decode($this->website->annotations ?? '[]');

        $type = isset($this->params[0]) && in_array($this->params[0], ['paths', 'referrers', 'screen-resolutions', 'utms', 'operating-systems', 'device-types', 'continents', 'countries', 'cities', 'browser-names', 'browser-languages', 'browser-timezones', 'goals', 'realtime', 'themes', 'outbound-clicks', 'hours', 'weekdays']) ? query_clean(str_replace('-', '_', $this->params[0])) : 'default';

        /* Check to see if we need to switch the selected website */
        if(isset($_GET['website_id']) && array_key_exists($_GET['website_id'], $this->websites)) {
            $redirect = $_GET['redirect'] ?? 'dashboard';

            $_COOKIE['selected_website_id'] = (int) $_GET['website_id'];

            setcookie('selected_website_id', (int) $_GET['website_id'], time() + (86400 * 30), COOKIE_PATH);

            redirect($redirect);
        }

        $base_url_path = 'dashboard/';

        /* Custom realtime page */
        if($type == 'realtime') {

            Title::set(sprintf(l('dashboard.title_dynamic'), l('realtime.header'), $this->website->name));

            /* Prepare the view */
            $data = [
                'base_url_path' => $base_url_path,
            ];

            $view = new \Altum\View('realtime/index', (array)$this);

            $this->add_view_content('content', $view->run($data));
        } else {

            /* Load data based on the website type */
            $dashboard = $this->{$this->website->tracking_type}();
            $has_logs = count($dashboard['logs']);

            $dashboard_views = [];

            /* Get dashboard views if needed */
            if(settings()->analytics->dashboard_views_is_enabled) {
                $dashboard_views = \Altum\Cache::cache_function_result('dashboard_views?website_id=' . $this->website->website_id . '&user_id=' . $this->user->user_id, ['dashboard_views?website_id=' . $this->website->website_id, 'website_id=' . $this->website->website_id, 'user_id=' . $this->user->user_id], function() {
                    return db()
                        ->where('website_id', $this->website->website_id)
                        ->where('user_id', $this->user->user_id)
                        ->orderBy('dashboard_view_id', 'DESC')
                        ->get('dashboard_views');
                });
            }

            /* Prepare annotations */
            $total_annotations = 0;
            $dashboard_annotation_datetimes = [];
            $grouped_annotations = [];
            $can_create_annotations = false;

            if($type == 'default' && $has_logs && settings()->analytics->annotations_is_enabled) {
                $cache_key = md5($this->website->website_id . $dashboard['datetime']['query_start_date'] . $dashboard['datetime']['query_end_date']);
                $total_annotations = (int) db()->where('website_id', $this->website->website_id)->where('user_id', $this->user->user_id)->getValue('annotations', 'count(*)');
                $can_create_annotations = !$this->team && ($this->user->plan_settings->annotations_limit == -1 || $total_annotations < $this->user->plan_settings->annotations_limit);

                $annotations = \Altum\Cache::cache_function_result('annotations?hash=' . $cache_key, ['annotations?website_id=' . $this->website->website_id, 'website_id' => $this->website->website_id, 'user_id=' . $this->user->user_id], function() use ($dashboard) {
                    return db()
                        ->where('website_id', $this->website->website_id)
                        ->where('user_id', $this->user->user_id)
                        ->where('chart_datetime', [$dashboard['datetime']['query_start_date'], $dashboard['datetime']['query_end_date']], 'BETWEEN')
                        ->get('annotations');
                });

                $user_timezone = new \DateTimeZone($this->user->timezone);

                foreach($dashboard['logs'] as $row) {
                    if(!isset($row->formatted_date)) {
                        continue;
                    }

                    $chart_datetime = null;

                    switch($dashboard['datetime']['query_date_format']) {
                        case '%Y-%m-%d %H':
                            $chart_datetime = \DateTime::createFromFormat('!Y-m-d H', $row->formatted_date, $user_timezone);
                            break;

                        case '%Y-%m-%d':
                            $chart_datetime = \DateTime::createFromFormat('!Y-m-d', $row->formatted_date, $user_timezone);
                            break;

                        case '%Y-%m':
                            $chart_datetime = \DateTime::createFromFormat('!Y-m', $row->formatted_date, $user_timezone);
                            break;

                        case '%Y':
                            $chart_datetime = \DateTime::createFromFormat('!Y', $row->formatted_date, $user_timezone);
                            break;
                    }

                    if($chart_datetime) {
                        $dashboard_annotation_datetimes[$dashboard['datetime']['process']($row->formatted_date, true)] = $chart_datetime->format('Y-m-d H:i:s');
                    }
                }

                foreach($annotations as $annotation) {
                    $annotation_datetime = new \DateTime($annotation->chart_datetime);
                    $start_datetime = new \DateTime($dashboard['datetime']['query_start_date']);
                    $end_datetime = new \DateTime($dashboard['datetime']['query_end_date']);

                    if($annotation_datetime < $start_datetime || $annotation_datetime > $end_datetime) {
                        continue;
                    }

                    $x_value = (new \DateTime($annotation->chart_datetime))->setTimezone(new \DateTimeZone(Date::$timezone))->format($dashboard['datetime']['response_date_format']);

                    $grouped_annotations[$x_value][] = [
                        'annotation_id' => (int) $annotation->annotation_id,
                        'name' => $annotation->name,
                        'chart_datetime' => Date::get($annotation->chart_datetime, 1),
                    ];
                }
            }

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

            /* Annotation modals */
            if($type == 'default' && $has_logs && settings()->analytics->annotations_is_enabled && !$this->team) {
                $view = new \Altum\View('dashboard/annotation_create_modal', (array)$this);
                \Altum\Event::add_content($view->run(), 'modals');

                $view = new \Altum\View('dashboard/annotation_update_modal', (array)$this);
                \Altum\Event::add_content($view->run(), 'modals');
            }

            /* Set a custom title */
            if($type == 'default') {
                Title::set(sprintf(l('dashboard.title'), $this->website->name));
            } else {
                Title::set(sprintf(l('dashboard.title_dynamic'), l('dashboard.' . $type . '.header'), $this->website->name));
            }


            /* Prepare the inside content View */
            $data = [
                'total_annotations' => $total_annotations,
                'annotation_datetimes' => $dashboard_annotation_datetimes,
                'grouped_annotations' => $grouped_annotations,
                'can_create_annotations' => $can_create_annotations,
                'start_date' => $dashboard['start_date'],
                'end_date' => $dashboard['end_date'],
                'datetime' => $dashboard['datetime'],
                'logs' => $dashboard['logs'],
                'basic_totals' => $dashboard['basic_totals'],
                'logs_chart' => $dashboard['logs_chart'],
                'has_logs' => $has_logs,
                'base_url_path' => $base_url_path,
            ];

            $view = new \Altum\View('dashboard/partials/' . $type, (array)$this);
            $this->add_view_content('dashboard_content', $view->run($data));


            /* Prepare the view */
            $data = [
                'datetime' => $dashboard['datetime'],
                'logs' => $dashboard['logs'],
                'basic_totals' => $dashboard['basic_totals'],
                'logs_chart' => $dashboard['logs_chart'],
                'has_logs' => $has_logs,
                'type' => $type,
                'dashboard_views' => $dashboard_views,
                'base_url_path' => $base_url_path,
            ];

            $view = new \Altum\View('dashboard/index', (array)$this);
            $this->add_view_content('content', $view->run($data));

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

        $convert_tz_sql = get_convert_tz_sql('`sessions_events`.`date`', $this->user->timezone);

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
            'start_date' => $start_date,
            'end_date' => $end_date,
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

        $convert_tz_sql = get_convert_tz_sql('`date`', $this->user->timezone);

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
            'start_date' => $start_date,
            'end_date' => $end_date,
            'datetime' => $datetime,
            'logs' => $logs,
            'basic_totals' => $basic_totals,
            'logs_chart' => $logs_chart
        ];
    }

    public function export_advanced() {

        \Altum\Authentication::guard();

        if(!$this->website) {
            redirect('websites');
        }

        $type = isset($this->params[0]) && in_array($this->params[0], ['csv', 'json']) ? $this->params[0] : 'csv';

        /* Establish the start and end date for the statistics */
        list($start_date, $end_date) = AnalyticsFilters::get_date();

        $datetime = \Altum\Date::get_start_end_dates_new($start_date, $end_date, Date::$default_timezone);

        /* Filters */
        $filters = AnalyticsFilters::get_filters_sql(['websites_visitors', 'sessions_events']);

        /* Get the data from the database */
        $rows = [];

        $convert_tz_sql = get_convert_tz_sql('`sessions_events`.`date`', $this->user->timezone);

        $result = database()->query("
            SELECT
                `websites_visitors`.`continent_code`,
                `websites_visitors`.`country_code`,
                `websites_visitors`.`os_name`,
                `websites_visitors`.`os_version`,
                `websites_visitors`.`browser_name`,
                `websites_visitors`.`browser_version`,
                `websites_visitors`.`browser_language`,
                `websites_visitors`.`browser_timezone`,
                `websites_visitors`.`screen_resolution`,
                `websites_visitors`.`device_type`,
                `sessions_events`.`type`,
                `sessions_events`.`path`,
                `sessions_events`.`title`,
                `sessions_events`.`referrer_host`,
                `sessions_events`.`referrer_path`,
                `sessions_events`.`utm_source`,
                `sessions_events`.`utm_medium`,
                `sessions_events`.`utm_campaign`,
                `sessions_events`.`viewport_width`,
                `sessions_events`.`viewport_height`,
                DATE_FORMAT({$convert_tz_sql}, '%Y-%m-%d') AS `formatted_date`
            FROM
                `sessions_events`
            LEFT JOIN
                `websites_visitors` ON `sessions_events`.`visitor_id` = `websites_visitors`.`visitor_id`
            WHERE
                `sessions_events`.`website_id` = {$this->website->website_id}
                AND (`sessions_events`.`date` >= '{$datetime['query_start_date']}' AND `sessions_events`.`date` < '{$datetime['query_end_date']}')
                {$filters}
        ");

        while($row = $result->fetch_object()) {
            $rows[] = $row;
        }

        switch($type) {
            case 'csv':
                header('Content-Disposition: attachment; filename="' . get_slug($this->website->name) . '.csv";');
                header('Content-Type: application/csv; charset=UTF-8');

                $data = csv_exporter($rows);
                break;

            case 'json':
                header('Content-Disposition: attachment; filename="' . get_slug($this->website->name) . '.json";');
                header('Content-Type: application/json; charset=UTF-8');

                $data = json_exporter($rows);
                break;
        }

        die($data);
    }

    public function export_lightweight() {

        \Altum\Authentication::guard();

        if(!$this->website) {
            redirect('websites');
        }

        $type = isset($this->params[0]) && in_array($this->params[0], ['csv', 'json']) ? $this->params[0] : 'csv';

        /* Establish the start and end date for the statistics */
        list($start_date, $end_date) = AnalyticsFilters::get_date();

        $datetime = \Altum\Date::get_start_end_dates_new($start_date, $end_date, Date::$default_timezone);

        /* Get the data from the database */
        $rows = [];

        $convert_tz_sql = get_convert_tz_sql('`date`', $this->user->timezone);

        $result = database()->query("
            SELECT
                *
            FROM
                `lightweight_events`
            WHERE
                `website_id` = {$this->website->website_id}
                AND (`date` >= '{$datetime['query_start_date']}' AND `date` < '{$datetime['query_end_date']}')
            ");

        while($row = $result->fetch_object()) {

            unset($row->event_id);
            unset($row->website_id);

            $rows[] = $row;
        }

        switch($type) {
            case 'csv':
                header('Content-Disposition: attachment; filename="' . get_slug($this->website->name) . '.csv";');
                header('Content-Type: application/csv; charset=UTF-8');

                $data = csv_exporter($rows);
                break;

            case 'json':
                header('Content-Disposition: attachment; filename="' . get_slug($this->website->name) . '.json";');
                header('Content-Type: application/json; charset=UTF-8');

                $data = json_exporter($rows);
                break;
        }

        die($data);
    }

    public function export_goals() {

        \Altum\Authentication::guard();

        if(!$this->website) {
            redirect('websites');
        }

        $type = isset($this->params[0]) && in_array($this->params[0], ['csv', 'json']) ? $this->params[0] : 'csv';

        /* Establish the start and end date for the statistics */
        list($start_date, $end_date) = AnalyticsFilters::get_date();

        $datetime = \Altum\Date::get_start_end_dates_new($start_date, $end_date, Date::$default_timezone);

        /* Filters */
        $filters = AnalyticsFilters::get_filters_sql($this->website->tracking_type == 'advanced' ? ['websites_visitors', 'sessions_events'] : ['lightweight_events']);

        /* Get the data from the database */
        $rows = [];

        if($this->website->tracking_type == 'advanced') {
            $result = database()->query("
                SELECT
                    `goals_conversions`.`conversion_id`,
                    `goals_conversions`.`user_id`,
                    `goals_conversions`.`event_id`,
                    `goals_conversions`.`session_id`,
                    `goals_conversions`.`visitor_id`,
                    `goals_conversions`.`website_id`,
                    `goals_conversions`.`goal_id`,
                    `websites_goals`.`key`,
                    `websites_goals`.`type` AS `goal_type`,
                    `websites_goals`.`path` AS `goal_path`,
                    `websites_goals`.`scroll_percentage` AS `goal_scroll_percentage`,
                    `websites_goals`.`name` AS `goal_name`,
                    `goals_conversions`.`expiration_date`,
                    `goals_conversions`.`datetime`
                FROM
                    `goals_conversions`
                LEFT JOIN
                    `websites_goals` ON `websites_goals`.`goal_id` = `goals_conversions`.`goal_id`
                LEFT JOIN
                    `sessions_events` ON `sessions_events`.`event_id` = `goals_conversions`.`event_id`
                LEFT JOIN
                    `websites_visitors` ON `websites_visitors`.`visitor_id` = `goals_conversions`.`visitor_id`
                WHERE
                    `goals_conversions`.`website_id` = {$this->website->website_id}
                    AND `goals_conversions`.`datetime` >= '{$datetime['query_start_date']}' AND `goals_conversions`.`datetime` < '{$datetime['query_end_date']}'
                    {$filters}
                ORDER BY
                    `goals_conversions`.`conversion_id` DESC;
            ");
        } else {
            $result = database()->query("
                SELECT
                    `goals_conversions`.`conversion_id`,
                    `goals_conversions`.`user_id`,
                    `goals_conversions`.`event_id`,
                    `goals_conversions`.`session_id`,
                    `goals_conversions`.`visitor_id`,
                    `goals_conversions`.`website_id`,
                    `goals_conversions`.`goal_id`,
                    `websites_goals`.`key`,
                    `websites_goals`.`type` AS `goal_type`,
                    `websites_goals`.`path` AS `goal_path`,
                    `websites_goals`.`scroll_percentage` AS `goal_scroll_percentage`,
                    `websites_goals`.`name` AS `goal_name`,
                    `goals_conversions`.`expiration_date`,
                    `goals_conversions`.`datetime`
                FROM
                    `goals_conversions`
                LEFT JOIN
                    `websites_goals` ON `websites_goals`.`goal_id` = `goals_conversions`.`goal_id`
                LEFT JOIN
                    `lightweight_events` ON `lightweight_events`.`event_id` = `goals_conversions`.`event_id`
                WHERE
                    `goals_conversions`.`website_id` = {$this->website->website_id}
                    AND `goals_conversions`.`datetime` >= '{$datetime['query_start_date']}' AND `goals_conversions`.`datetime` < '{$datetime['query_end_date']}'
                    {$filters}
                ORDER BY
                    `goals_conversions`.`conversion_id` DESC;
            ");
        }

        while($row = $result->fetch_object()) {
            $rows[] = $row;
        }

        switch($type) {
            case 'csv':
                header('Content-Disposition: attachment; filename="' . get_slug($this->website->name . '-goals') . '.csv";');
                header('Content-Type: application/csv; charset=UTF-8');

                $data = csv_exporter_new($rows, ['conversion_id', 'user_id', 'event_id', 'session_id', 'visitor_id', 'website_id', 'goal_id', 'key', 'goal_type', 'goal_path', 'goal_scroll_percentage', 'goal_name', 'expiration_date', 'datetime']);
                break;

            case 'json':
                header('Content-Disposition: attachment; filename="' . get_slug($this->website->name . '-goals') . '.json";');
                header('Content-Type: application/json; charset=UTF-8');

                $data = json_exporter($rows);
                break;
        }

        die($data);
    }

    public function export_outbound_clicks() {

        \Altum\Authentication::guard();

        if(!$this->website) {
            redirect('websites');
        }

        $type = isset($this->params[0]) && in_array($this->params[0], ['csv', 'json']) ? $this->params[0] : 'csv';

        /* Establish the start and end date for the statistics */
        list($start_date, $end_date) = AnalyticsFilters::get_date();

        $datetime = \Altum\Date::get_start_end_dates_new($start_date, $end_date, Date::$default_timezone);

        /* Filters */
        $filters = AnalyticsFilters::get_filters_sql($this->website->tracking_type == 'advanced' ? ['websites_visitors', 'sessions_events'] : ['lightweight_events']);

        /* Get the data from the database */
        $rows = [];

        if($this->website->tracking_type == 'advanced') {
            $result = database()->query("
                SELECT
                    `outbound_clicks`.`outbound_click_id`,
                    `outbound_clicks`.`event_id`,
                    `outbound_clicks`.`session_id`,
                    `outbound_clicks`.`visitor_id`,
                    `outbound_clicks`.`website_id`,
                    `outbound_clicks`.`host`,
                    `outbound_clicks`.`path`,
                    `outbound_clicks`.`title`,
                    `outbound_clicks`.`datetime`,
                    `outbound_clicks`.`expiration_date`
                FROM
                    `outbound_clicks`
                LEFT JOIN
                    `sessions_events` ON `sessions_events`.`event_id` = `outbound_clicks`.`event_id`
                LEFT JOIN
                    `websites_visitors` ON `websites_visitors`.`visitor_id` = `outbound_clicks`.`visitor_id`
                WHERE
                    `outbound_clicks`.`website_id` = {$this->website->website_id}
                    AND `outbound_clicks`.`datetime` >= '{$datetime['query_start_date']}' AND `outbound_clicks`.`datetime` < '{$datetime['query_end_date']}'
                    {$filters}
                ORDER BY
                    `outbound_clicks`.`outbound_click_id` DESC;
            ");
        } else {
            $result = database()->query("
                SELECT
                    `outbound_clicks`.`outbound_click_id`,
                    `outbound_clicks`.`event_id`,
                    `outbound_clicks`.`session_id`,
                    `outbound_clicks`.`visitor_id`,
                    `outbound_clicks`.`website_id`,
                    `outbound_clicks`.`host`,
                    `outbound_clicks`.`path`,
                    `outbound_clicks`.`title`,
                    `outbound_clicks`.`datetime`,
                    `outbound_clicks`.`expiration_date`
                FROM
                    `outbound_clicks`
                LEFT JOIN
                    `lightweight_events` ON `lightweight_events`.`event_id` = `outbound_clicks`.`event_id`
                WHERE
                    `outbound_clicks`.`website_id` = {$this->website->website_id}
                    AND `outbound_clicks`.`datetime` >= '{$datetime['query_start_date']}' AND `outbound_clicks`.`datetime` < '{$datetime['query_end_date']}'
                    {$filters}
                ORDER BY
                    `outbound_clicks`.`outbound_click_id` DESC;
            ");
        }

        while($row = $result->fetch_object()) {
            $rows[] = $row;
        }

        switch($type) {
            case 'csv':
                header('Content-Disposition: attachment; filename="' . get_slug($this->website->name . '-outbound-clicks') . '.csv";');
                header('Content-Type: application/csv; charset=UTF-8');

                $data = csv_exporter_new($rows, ['outbound_click_id', 'event_id', 'session_id', 'visitor_id', 'website_id', 'host', 'path', 'title', 'datetime', 'expiration_date']);
                break;

            case 'json':
                header('Content-Disposition: attachment; filename="' . get_slug($this->website->name . '-outbound-clicks') . '.json";');
                header('Content-Type: application/json; charset=UTF-8');

                $data = json_exporter($rows);
                break;
        }

        die($data);
    }

    public function reset() {

        \Altum\Authentication::guard();

        if (empty($_POST)) {
            throw_404();
        }

        $website_id = (int) $_POST['website_id'];
        $datetime = \Altum\Date::get_start_end_dates_new($_POST['start_date'], $_POST['end_date']);

        /* Team */
        if($this->team) {
            die();
        }

        /* Make sure the resource is created by the logged in user */
        if(!array_key_exists($_POST['website_id'], $this->websites)) {
            redirect('dashboard');
        }

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('dashboard');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Clear statistics data */
            database()->query("DELETE FROM `websites_visitors` WHERE `website_id` = {$website_id} AND `date` >= '{$datetime['query_start_date']}' AND `date` < '{$datetime['query_end_date']}'");
            database()->query("DELETE FROM `visitors_sessions` WHERE `website_id` = {$website_id} AND `date` >= '{$datetime['query_start_date']}' AND `date` < '{$datetime['query_end_date']}'");
            database()->query("DELETE FROM `sessions_events` WHERE `website_id` = {$website_id} AND `date` >= '{$datetime['query_start_date']}' AND `date` < '{$datetime['query_end_date']}'");
            database()->query("DELETE FROM `events_children` WHERE `website_id` = {$website_id} AND `date` >= '{$datetime['query_start_date']}' AND `date` < '{$datetime['query_end_date']}'");
            database()->query("DELETE FROM `lightweight_events` WHERE `website_id` = {$website_id} AND `date` >= '{$datetime['query_start_date']}' AND `date` < '{$datetime['query_end_date']}'");
            db()->where('website_id', $website_id)->update('websites', [
                'pageviews_stats_last_datetime' => null,
            ]);

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.update2'));

            redirect('dashboard');

        }

        redirect('dashboard');

    }

}
