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

class Statistics extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $datetime = \Altum\Date::get_start_end_dates_new();

        /* Query for the statistics of the notification */
        $logs_chart = [];
        $logs_total = [
            'impression'        => 0,
            'hover'             => 0,
            'click'             => 0,
            'ctr'               => 0,
            'form_submission'   => 0,
            'conversions'       => 0,
        ];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        /* Logs for the charts */
        $logs_result = database()->query("
            SELECT
                 `type`,
                 COUNT(`id`) AS `total`,
                 DATE_FORMAT({$convert_tz_sql}, '{$datetime['query_date_format']}') AS `formatted_date`
            FROM
                 `track_notifications`
            WHERE
                `user_id` = {$this->user->user_id}
                AND ({$convert_tz_sql} BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
            GROUP BY
                `formatted_date`,
                `type`
            ORDER BY
                `formatted_date`
        ");

        $has_data = $logs_result->num_rows;

        /* Generate the raw chart data and save logs for later usage */
        while($row = $logs_result->fetch_object()) {
            $row->formatted_date = $datetime['process']($row->formatted_date);

            /* Handle if the date key is not already set */
            if(!array_key_exists($row->formatted_date, $logs_chart)) {
                $logs_chart[$row->formatted_date] = [
                    'impression'        => 0,
                    'hover'             => 0,
                    'click'             => 0,
                    'form_submission'   => 0,
                ];
            }

            $logs_chart[$row->formatted_date][$row->type] = $row->total;

            /* Count totals */
            if(in_array($row->type, ['impression', 'hover', 'click', 'form_submission'])) {
                $logs_total[$row->type] += $row->total;
            }
        }

        /* CTR on mouse clicks */
        $logs_total['ctr'] = $logs_total['impression'] && $logs_total['click'] ? ($logs_total['click'] / $logs_total['impression']) * 100 : 0;

        /* Calculate form submissions conversions */
        $logs_total['conversions'] = $logs_total['impression'] && $logs_total['form_submission'] ? ($logs_total['form_submission'] / $logs_total['impression']) * 100 : 0;

        $logs_chart = get_chart_data($logs_chart);

        /* Get page performance data */
        $top_pages_result = database()->query("
            SELECT 
                `track_notifications`.`path`,
                `campaigns`.`domain`,
                SUM(CASE WHEN `track_notifications`.`type` = 'impression' THEN 1 ELSE 0 END) AS `impressions`,
                SUM(CASE WHEN `track_notifications`.`type` = 'hover' THEN 1 ELSE 0 END) AS `hovers`,
                SUM(CASE WHEN `track_notifications`.`type` = 'click' THEN 1 ELSE 0 END) AS `clicks`,
                SUM(CASE WHEN `track_notifications`.`type` = 'form_submission' THEN 1 ELSE 0 END) AS `form_submissions`,
                COUNT(`id`) AS `total`
            FROM 
                `track_notifications`
            LEFT JOIN
                `campaigns` ON `track_notifications`.`campaign_id` = `campaigns`.`campaign_id`
            WHERE
                `track_notifications`.`user_id` = {$this->user->user_id}
                AND (`track_notifications`.`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
            GROUP BY 
                `track_notifications`.`path`, 
                `campaigns`.`domain`
            ORDER BY 
                `total` DESC 
            LIMIT 25
        ");

        /* Prepare the view */
        $data = [
            'top_pages_result'  => $top_pages_result,
            'has_data'          => $has_data,
            'logs_chart'        => $logs_chart,
            'logs_total'        => $logs_total,
            'datetime'          => $datetime,
        ];

        $view = new \Altum\View('statistics/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function reset() {

        \Altum\Authentication::guard();

        if (empty($_POST)) {
            throw_404();
        }

        $datetime = \Altum\Date::get_start_end_dates_new($_POST['start_date'], $_POST['end_date']);

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.campaigns')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('statistics');
        }

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('statistics');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Clear statistics data */
            database()->query("DELETE FROM `track_notifications` WHERE `user_id` = {$this->user->user_id} AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')");

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.update2'));

            redirect('statistics');

        }

        redirect('statistics');

    }

}
