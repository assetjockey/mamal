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

use Altum\Response;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiStatistics extends Controller {
    use Apiable;
    public $datetime;

    public function index() {

        $this->verify_request();

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                /* Detect if we only need an object, or the whole list */
                if(!isset($this->params[0])) {
                    $this->get_all();
                }

                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['notification_id', 'campaign_id'], [], [], [], []));
        $filters->set_default_order_by('id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* :) */
        $datetime = \Altum\Date::get_start_end_dates_new();

        $data = [];
        $data_totals = [
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
                {$filters->get_sql_where()}
                AND ({$convert_tz_sql} BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
            GROUP BY
                `formatted_date`,
                `type`
            ORDER BY
                `formatted_date`
        ");

        /* Generate the raw chart data and save logs for later usage */
        while($row = $logs_result->fetch_object()) {
            $row->formatted_date = $datetime['process']($row->formatted_date);

            /* Handle if the date key is not already set */
            if(!array_key_exists($row->formatted_date, $data)) {
                $data[$row->formatted_date] = [
                    'impression'        => 0,
                    'hover'             => 0,
                    'click'             => 0,
                    'form_submission'   => 0,
                    'formatted_date' => $row->formatted_date,
                ];
            }

            $data[$row->formatted_date][$row->type] = (int) $row->total;

            /* Count totals */
            if(in_array($row->type, ['impression', 'hover', 'click', 'form_submission'])) {
                $data_totals[$row->type] += $row->total;
            }
        }

        /* CTR on mouse clicks */
        $data_totals['ctr'] = $data_totals['impression'] && $data_totals['click'] ? ($data_totals['click'] / $data_totals['impression']) * 100 : 0;
        $data_totals['ctr'] = number_format($data_totals['ctr'], 2, '.', '');

        /* Calculate form submissions conversions */
        $data_totals['conversions'] = $data_totals['impression'] && $data_totals['form_submission'] ? ($data_totals['form_submission'] / $data_totals['impression']) * 100 : 0;

        /* :) */
        $data = [
            'statistics' => array_values($data),
            'totals' => $data_totals,
        ];

        Response::jsonapi_success($data);

    }

}
