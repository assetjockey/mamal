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
namespace Altum;

defined('ALTUMCODE') || die();

class AnalyticsFilters {

    /* Visitor filters */
    public static $websites_visitors = [
        'ip',
        'continent_code',
		'country_code',
		'region_name',
        'city_name',
        'screen_resolution',
        'browser_language',
        'browser_timezone',
        'os_name',
        'device_type',
        'browser_name',
        'theme',
        'custom_parameters_key',
        'custom_parameters_value'
    ];

    /* Pageview filters */
    public static $sessions_events = [
        'type',
        'path',
        'title',
        'referrer_host',
        'referrer_path',
        'utm_source',
        'utm_medium',
        'utm_campaign'
    ];

    /* Lightweight filters */
    public static $lightweight_events = [
        'continent_code',
		'country_code',
		'region_name',
        'city_name',
        'screen_resolution',
        'browser_language',
        'browser_timezone',
        'os_name',
        'device_type',
        'browser_name',
        'theme',
        'type',
        'path',
        'referrer_host',
        'referrer_path',
        'utm_source',
        'utm_medium',
        'utm_campaign'
    ];

    public static function get_date() {

        /* Establish the start and end date for the statistics */
        if(isset($_GET['start_date'], $_GET['end_date'])) {
            $start_date = query_clean($_GET['start_date']);
            $end_date = query_clean($_GET['end_date']);

            /* Set it to the session */
            session_set('analytics_start_date', $start_date);
            session_set('analytics_end_date', $end_date);
        }

        /* Try to get start / end date from sessions if any */
        else if(session_has('analytics_start_date') && session_get('analytics_end_date')) {
            $start_date = query_clean(session_get('analytics_start_date'));
            $end_date = query_clean(session_get('analytics_end_date'));
        }

        /* Default start / end dates */
        else {
            $start_date = (new \DateTime())->modify('-30 day')->format('Y-m-d');
            $end_date = (new \DateTime())->format('Y-m-d');
        }

        return [
            $start_date,
            $end_date
        ];
    }

    public static function get_filters($available_filters = null) {

        /* Determine which type of filters to retrieve */
        switch($available_filters) {
            case 'websites_visitors':
                $available_filters = self::$websites_visitors;

                break;

            case 'sessions_events':
                $available_filters = self::$sessions_events;

                break;

            case 'lightweight_events':
                $available_filters = self::$lightweight_events;

                break;

            default:
                $available_filters = array_merge(self::$websites_visitors, self::$sessions_events);
                break;
        }

        $filters = isset($_COOKIE['filters']) ? json_decode($_COOKIE['filters']) : null;
        $processed_filters = [];

        if($filters) {

            foreach($filters as $filter) {

                if(!in_array($filter->by, $available_filters)) {
                    continue;
                }

                if(!in_array($filter->rule, [
                    'is',
                    'is_not',
                    'contains',
                    'starts_with',
                    'ends_with'
                ])) {
                    continue;
                }

                $filter->value = query_clean($filter->value);

                $processed_filters[] = $filter;
            }

        }

        return $processed_filters;
    }

    public static function get_filters_sql($filters_keys = []) {

        $websites_visitors = self::$websites_visitors;
        $sessions_events = self::$sessions_events;
        $lightweight_events = self::$lightweight_events;

        if(!count($filters_keys)) {
            return null;
        }

        $available_filters = [];
        foreach($filters_keys as $filter) {
            $available_filters = array_merge($available_filters, ${$filter});
        }

        $filters = isset($_COOKIE['filters']) ? json_decode($_COOKIE['filters']) : null;
        $wheres = [];

        if($filters) {
            foreach($filters as $filter) {

                if(!in_array($filter->by, $available_filters)) {
                    continue;
                }

                if(!in_array($filter->rule, [
                    'is',
                    'is_not',
                    'contains',
                    'starts_with',
                    'ends_with'
                ])) {
                    continue;
                }

                $filter->value = query_clean($filter->value);

                /* Custom parameter filters */
                if(in_array('websites_visitors', $filters_keys) && in_array($filter->by, ['custom_parameters_key', 'custom_parameters_value'])) {
                    switch($filter->rule) {
                        case 'is':
                            $json_search = $filter->value;
                            $json_condition = "IS NOT NULL";
                            break;

                        case 'is_not':
                            $json_search = $filter->value;
                            $json_condition = "IS NULL";
                            break;

                        case 'contains':
                            $json_search = "%{$filter->value}%";
                            $json_condition = "IS NOT NULL";
                            break;

                        case 'starts_with':
                            $json_search = "{$filter->value}%";
                            $json_condition = "IS NOT NULL";
                            break;

                        case 'ends_with':
                            $json_search = "%{$filter->value}";
                            $json_condition = "IS NOT NULL";
                            break;
                    }

                    $custom_parameters = "COALESCE(NULLIF(`websites_visitors`.`custom_parameters`, ''), '{}')";

                    switch($filter->by) {
                        case 'custom_parameters_key':
                            $wheres[] = "JSON_SEARCH(JSON_KEYS({$custom_parameters}), 'one', '{$json_search}') {$json_condition}";
                            break;

                        case 'custom_parameters_value':
                            $wheres[] = "JSON_SEARCH({$custom_parameters}, 'one', '{$json_search}') {$json_condition}";
                            break;
                    }

                    continue;
                }

                switch($filter->rule) {
                    case 'is':
                        $condition = "= '{$filter->value}'";
                        break;

                    case 'is_not':
                        $condition = "<> '{$filter->value}'";
                        break;

                    case 'contains':
                        $condition = "LIKE '%{$filter->value}%'";
                        break;

                    case 'starts_with':
                        $condition = "LIKE '{$filter->value}%'";
                        break;

                    case 'ends_with':
                        $condition = "LIKE '%{$filter->value}'";
                        break;
                }

                if(in_array('websites_visitors', $filters_keys) && in_array($filter->by, $websites_visitors)) {
                    $table = 'websites_visitors';
                }

                if(in_array('sessions_events', $filters_keys) && in_array($filter->by, $sessions_events)) {
                    $table = 'sessions_events';
                }

                if(in_array('lightweight_events', $filters_keys) && in_array($filter->by, $lightweight_events)) {
                    $table = 'lightweight_events';
                }

                $wheres[] = "`{$table}`.`{$filter->by}` $condition";

            }
        }

        return count($wheres) ? ' AND ' . implode(' AND ', $wheres) : null;

    }

}
