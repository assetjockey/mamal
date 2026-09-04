<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
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

namespace Altum\Models;

defined('ALTUMCODE') || die();

class MonitorsLogs extends Model {

    public function get_monitor_logs_chart_data_by_monitor_id($monitor_id, $start_datetime, $end_datetime) {

        $data = [
            'total_logs' => 0,
            'total_ok_checks' => 0,
            'total_not_ok_checks' => 0,
            'total_response_time' => 0,
        ];

        /* Get all available monitor logs */
        $monitor_logs_chart = [];

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = cache()->getItem('s_monitor_logs_chart_data?monitor_id=' . $monitor_id . '&start_datetime=' . md5($start_datetime) . '&end_datetime=' . md5($end_datetime));

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $monitor_logs_result = database()->query("SELECT * FROM `monitors_logs` WHERE `monitor_id` = {$monitor_id} AND (`datetime` BETWEEN '{$start_datetime}' AND '{$end_datetime}')");

            while($row = $monitor_logs_result->fetch_object()) {
                /* Stats for chart */
                $label = $start_datetime == $end_datetime ? \Altum\Date::get($row->datetime, 3) : \Altum\Date::get($row->datetime, 1);

                $monitor_logs_chart[$label] = [
                    'is_ok' => $row->is_ok,
                    'response_time' => $row->is_ok ? $row->response_time : 0,
                    'hour_minute_second_label' => \Altum\Date::get($row->datetime, 3)
                ];

                /* Complete stats */
                $data['total_logs']++;
                $data['total_ok_checks'] += (int) $row->is_ok;
                $data['total_not_ok_checks'] += (int) !$row->is_ok;
                $data['total_response_time'] += $row->response_time;
            }

            $data['chart'] = get_chart_data($monitor_logs_chart);

            cache()->save(
                $cache_instance->set($data)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('monitor_id=' . $monitor_id)
            );

        } else {

            /* Get cache */
            $data = $cache_instance->get();

        }

        return $data;

    }

    public function get_monitor_logs_data_by_monitor_id($monitor_id, $start_datetime, $end_datetime) {

        $data = [
            'logs' => [],
            'total_logs' => 0,
            'total_ok_checks' => 0,
            'total_not_ok_checks' => 0,
            'total_response_time' => 0,
        ];

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = cache()->getItem('s_monitor_logs_data?monitor_id=' . $monitor_id . '&start_datetime=' . md5($start_datetime) . '&end_datetime=' . md5($end_datetime));

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $monitor_logs_result = database()->query("SELECT * FROM `monitors_logs` WHERE `monitor_id` = {$monitor_id} AND (`datetime` BETWEEN '{$start_datetime}' AND '{$end_datetime}')");

            while($row = $monitor_logs_result->fetch_object()) {
                $data['logs'][] = $row;

                /* Complete stats */
                $data['total_logs']++;
                $data['total_ok_checks'] += (int) $row->is_ok;
                $data['total_not_ok_checks'] += (int) !$row->is_ok;
                $data['total_response_time'] += $row->response_time;
            }

            cache()->save(
                $cache_instance->set($data)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('monitor_id=' . $monitor_id)
            );

        } else {

            /* Get cache */
            $data = $cache_instance->get();

        }

        return $data;

    }

}
