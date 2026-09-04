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

use Altum\Date;
use Altum\Response;

defined('ALTUMCODE') || die();

class WebsitesAjax extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Do not use sessions anymore to not lockout the user from doing anything else on the site */
        session_write_close();

        if(
            \Altum\Csrf::check('global_token') &&
            isset($_GET['request_type']) &&
            in_array($_GET['request_type'], ['pageviews'])
        ) {
            $this->{$_GET['request_type']}();
        }

        die();
    }

    private function pageviews() {

        $website_ids = isset($_GET['website_ids']) ? explode(',', $_GET['website_ids']) : [];
        $website_ids = array_filter(array_unique(array_map('intval', $website_ids)));
        $website_ids = array_intersect($website_ids, array_keys($this->websites));
        $website_ids = array_slice($website_ids, 0, 100);

        if(empty($website_ids)) {
            Response::json('', 'success', ['websites' => []]);
        }

        $period = isset($_GET['period']) && in_array($_GET['period'], ['current_month', 'last_7_days', 'last_24_hours']) ? $_GET['period'] : 'current_month';
        $website_ids_query = implode(',', $website_ids);

        $websites = [];
        $websites_result = database()->query("
            SELECT
                `website_id`,
                `tracking_type`,
                `current_month_sessions_events`,
                `last_24_hours_pageviews`,
                `last_7_days_pageviews`,
                `pageviews_stats_last_datetime`
            FROM
                `websites`
            WHERE
                `website_id` IN ({$website_ids_query})
        ");

        while($row = $websites_result->fetch_object()) {
            $websites[$row->website_id] = $row;
        }

        if($period != 'current_month') {
            $this->refresh_pageviews_stats($websites);
        }

        $charts = $this->get_pageviews_charts($websites, $period);

        $data = [];

        foreach($websites as $website_id => $website) {
            $data[$website_id] = [
                'pageviews' => match($period) {
                    'last_24_hours' => (int) $website->last_24_hours_pageviews,
                    'last_7_days' => (int) $website->last_7_days_pageviews,
                    default => (int) $website->current_month_sessions_events,
                },
                'chart' => $charts[$website_id],
            ];
        }

        Response::json('', 'success', ['websites' => $data, 'period' => $period]);

    }

    private function refresh_pageviews_stats(&$websites) {

        $refresh_datetime = (new \DateTime())->modify('-10 minutes')->format('Y-m-d H:i:s');
        $last_24_hours_datetime = (new \DateTime())->modify('-24 hours')->format('Y-m-d H:i:s');
        $last_7_days_datetime = (new \DateTime())->modify('-7 days')->format('Y-m-d H:i:s');
        $now = get_date();

        $website_ids_by_tracking_type = [
            'advanced' => [],
            'lightweight' => [],
        ];

        foreach($websites as $website) {
            if($website->pageviews_stats_last_datetime && $website->pageviews_stats_last_datetime >= $refresh_datetime) {
                continue;
            }

            $website_ids_by_tracking_type[$website->tracking_type][] = (int) $website->website_id;
        }

        $stats = [];
        $stats += $this->get_pageviews_totals('sessions_events', $website_ids_by_tracking_type['advanced'], $last_24_hours_datetime, $last_7_days_datetime);
        $stats += $this->get_pageviews_totals('lightweight_events', $website_ids_by_tracking_type['lightweight'], $last_24_hours_datetime, $last_7_days_datetime);

        foreach($website_ids_by_tracking_type as $website_ids) {
            foreach($website_ids as $website_id) {
                $last_24_hours_pageviews = $stats[$website_id]['last_24_hours_pageviews'] ?? 0;
                $last_7_days_pageviews = $stats[$website_id]['last_7_days_pageviews'] ?? 0;

                db()->where('website_id', $website_id)->update('websites', [
                    'last_24_hours_pageviews' => $last_24_hours_pageviews,
                    'last_7_days_pageviews' => $last_7_days_pageviews,
                    'pageviews_stats_last_datetime' => $now,
                ]);

                if(isset($websites[$website_id])) {
                    $websites[$website_id]->last_24_hours_pageviews = $last_24_hours_pageviews;
                    $websites[$website_id]->last_7_days_pageviews = $last_7_days_pageviews;
                    $websites[$website_id]->pageviews_stats_last_datetime = $now;
                }
            }
        }

    }

    private function get_pageviews_totals($table, $website_ids, $last_24_hours_datetime, $last_7_days_datetime) {

        if(empty($website_ids)) {
            return [];
        }

        $website_ids_query = implode(',', array_map('intval', $website_ids));
        $data = [];

        $result = database()->query("
            SELECT
                `website_id`,
                SUM(CASE WHEN `date` >= '{$last_24_hours_datetime}' THEN 1 ELSE 0 END) AS `last_24_hours_pageviews`,
                COUNT(*) AS `last_7_days_pageviews`
            FROM
                `{$table}`
            WHERE
                `website_id` IN ({$website_ids_query})
                AND `date` >= '{$last_7_days_datetime}'
                AND `type` IN ('landing_page', 'pageview')
            GROUP BY
                `website_id`
        ");

        while($row = $result->fetch_object()) {
            $data[$row->website_id] = [
                'last_24_hours_pageviews' => (int) $row->last_24_hours_pageviews,
                'last_7_days_pageviews' => (int) $row->last_7_days_pageviews,
            ];
        }

        return $data;

    }

    private function get_pageviews_charts($websites, $period) {

        $timezone = $this->user->timezone ?? Date::$default_timezone;
        $timezone = in_array($timezone, \DateTimeZone::listIdentifiers()) ? $timezone : Date::$default_timezone;
        $timezone_object = new \DateTimeZone($timezone);
        $default_timezone_object = new \DateTimeZone(Date::$default_timezone);

        $end_datetime = new \DateTime('now', $timezone_object);

        if($period == 'last_24_hours') {
            $start_datetime = (clone $end_datetime)->modify('-24 hours');
            $intervals = 25;
            $interval_modifier = 'hours';
            $date_key_format = 'Y-m-d H:00:00';
            $query_date_format = '%Y-%m-%d %H:00:00';
        } else {
            $start_datetime = $period == 'current_month' ? (clone $end_datetime)->modify('first day of this month')->setTime(0, 0) : (clone $end_datetime)->modify('-6 days')->setTime(0, 0);
            $intervals = (int) $start_datetime->diff($end_datetime)->format('%a') + 1;
            $interval_modifier = 'days';
            $date_key_format = 'Y-m-d';
            $query_date_format = '%Y-%m-%d';
        }

        $query_start_date = (clone $start_datetime)->setTimezone($default_timezone_object)->format('Y-m-d H:i:s');
        $query_end_date = (clone $end_datetime)->setTimezone($default_timezone_object)->format('Y-m-d H:i:s');

        $labels = [];
        $base_chart = [];

        for($i = 0; $i < $intervals; $i++) {
            $date = (clone $start_datetime)->modify('+' . $i . ' ' . $interval_modifier);
            $date_key = $date->format($date_key_format);

            $labels[$date_key] = $period == 'last_24_hours' ? Date::get(clone $date, 5, $timezone) . ' ' . Date::get(clone $date, 'H:00', $timezone) : Date::get(clone $date, 5, $timezone);
            $base_chart[$date_key] = 0;
        }

        $charts = [];
        $website_ids_by_tracking_type = [
            'advanced' => [],
            'lightweight' => [],
        ];

        foreach($websites as $website) {
            $charts[$website->website_id] = $base_chart;
            $website_ids_by_tracking_type[$website->tracking_type][] = (int) $website->website_id;
        }

        $this->add_chart_data('sessions_events', $website_ids_by_tracking_type['advanced'], $charts, $query_start_date, $query_end_date, $timezone, $query_date_format);
        $this->add_chart_data('lightweight_events', $website_ids_by_tracking_type['lightweight'], $charts, $query_start_date, $query_end_date, $timezone, $query_date_format);

        foreach($charts as $website_id => $chart) {
            $charts[$website_id] = [
                'labels' => array_values($labels),
                'pageviews' => array_values($chart),
            ];
        }

        return $charts;

    }

    private function add_chart_data($table, $website_ids, &$charts, $query_start_date, $query_end_date, $timezone, $query_date_format) {

        if(empty($website_ids)) {
            return;
        }

        $website_ids_query = implode(',', array_map('intval', $website_ids));
        $convert_tz_sql = get_convert_tz_sql('`date`', $timezone);

        $result = database()->query("
            SELECT
                `website_id`,
                DATE_FORMAT({$convert_tz_sql}, '{$query_date_format}') AS `formatted_date`,
                COUNT(*) AS `pageviews`
            FROM
                `{$table}`
            WHERE
                `website_id` IN ({$website_ids_query})
                AND `date` >= '{$query_start_date}' AND `date` < '{$query_end_date}'
                AND `type` IN ('landing_page', 'pageview')
            GROUP BY
                `website_id`,
                `formatted_date`
        ");

        while($row = $result->fetch_object()) {
            if(isset($charts[$row->website_id][$row->formatted_date])) {
                $charts[$row->website_id][$row->formatted_date] = (int) $row->pageviews;
            }
        }

    }

}
