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

class ApiTransferRequestSubmissionsStatistics extends Controller {
    use Apiable;
    public $transfer_request;
    public $datetime;

    public function index() {

        $this->verify_request();

		if(!settings()->transfers->transfer_requests_is_enabled) {
			$this->return_404();
		}

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                /* Detect if we only need an object, or the whole list */
                if(isset($this->params[0])) {
                    $this->get();
                }

            break;
        }

        $this->return_404();
    }

    private function get() {

        $transfer_request_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $this->transfer_request = $transfer_request = db()->where('transfer_request_id', $transfer_request_id)->where('user_id', $this->user->user_id)->getOne('transfer_requests');

        /* We haven't found the resource */
        if(!$transfer_request) {
            $this->return_404();
        }

        /* :) */
        $this->datetime = \Altum\Date::get_start_end_dates_new();

        $type = isset($_GET['type']) && in_array($_GET['type'], [
            'overview',
            'referrer_host',
            'referrer_path',
            'continent_code',
            'country_code',
            'city_name',
            'os_name',
            'browser_name',
            'device_type',
            'browser_language',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'hour'
        ]) ? query_clean($_GET['type']) : 'overview';

        /* :) */
        $data = [];

        switch($type) {
            case 'overview':

                $convert_tz_sql = get_convert_tz_sql('`datetime`', \Altum\Date::$default_timezone);

                $result = database()->query("
                    SELECT
                        COUNT(`request_submission_id`) AS `submissions`,
                        SUM(`is_unique`) AS `unique_submissions`,
                        DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$this->transfer_request->transfer_request_id}
                        AND ({$convert_tz_sql} BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}')
                    GROUP BY
                        `formatted_date`
                    ORDER BY
                        `formatted_date`
                ");

                while($row = $result->fetch_object()) {
                    $data[] = [
                        'submissions' => (int) $row->submissions,
                        'unique_submissions' => (int) $row->unique_submissions,
                        'formatted_date' => $this->datetime['process']($row->formatted_date, true),
                    ];
                }

                break;

            case 'referrer_host':
            case 'continent_code':
            case 'country_code':
            case 'os_name':
            case 'browser_name':
            case 'device_type':
            case 'browser_language':

                $result = database()->query("
                    SELECT
                        `{$type}`,
                        COUNT(*) AS `submissions`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$this->transfer_request->transfer_request_id}
                        AND (`datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}')
                    GROUP BY
                        `{$type}`
                    ORDER BY
                        `requests_submissions` DESC
                    
                ");

                while($row = $result->fetch_object()) {
                    $data[] = [
                        $type => $row->{$type},
                        'submissions' => (int) $row->submissions
                    ];
                }

                break;

            case 'referrer_path':

                $referrer_host = trim(query_clean($_GET['referrer_host']));

                $result = database()->query("
                    SELECT
                        `referrer_host`,
                        `referrer_path`,
                        COUNT(*) AS `submissions`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$this->transfer_request->transfer_request_id}
                        AND `referrer_host` = '{$referrer_host}'
                        AND (`datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}')
                    GROUP BY
                        `referrer_host`,
                        `referrer_path`
                    ORDER BY
                        `requests_submissions` DESC
                    
                ");

                while($row = $result->fetch_object()) {
                    $data[] = [
                        'referrer_host' => $row->referrer_host,
                        $type => $row->{$type},
                        'submissions' => (int) $row->submissions
                    ];
                }

                break;

            case 'city_name':

                $country_code = isset($_GET['country_code']) ? trim(query_clean($_GET['country_code'])) : null;

                $result = database()->query("
                    SELECT
                        `country_code`,
                        `city_name`,
                        COUNT(*) AS `submissions`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$this->transfer_request->transfer_request_id}
                        " . ($country_code ? "AND `country_code` = '{$country_code}'" : null) . "
                        AND (`datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}')
                    GROUP BY
                        `country_code`,
                        `city_name`
                    ORDER BY
                        `requests_submissions` DESC
                    
                ");

                while($row = $result->fetch_object()) {
                    $data[] = [
                        'country_code' => $row->country_code,
                        'country_name' => $row->country_code ? get_country_from_country_code($row->country_code) : null,
                        $type => $row->{$type},
                        'submissions' => (int) $row->submissions
                    ];
                }

                break;

            case 'utm_source':

                $result = database()->query("
                    SELECT
                        `utm_source`,
                        COUNT(*) AS `submissions`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$this->transfer_request->transfer_request_id}
                        AND (`datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}')
                        AND `utm_source` IS NOT NULL
                    GROUP BY
                        `utm_source`
                    ORDER BY
                        `requests_submissions` DESC
                    
                ");

                while($row = $result->fetch_object()) {
                    $data[] = [
                        $type => $row->{$type},
                        'submissions' => (int) $row->submissions
                    ];
                }

                break;

            case 'utm_medium':

                $utm_source = trim(query_clean($_GET['utm_source']));

                $result = database()->query("
                    SELECT
                        `utm_source`,
                        `utm_medium`,
                        COUNT(*) AS `submissions`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$this->transfer_request->transfer_request_id}
                        AND `utm_source` = '{$utm_source}'
                        AND (`datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}')
                    GROUP BY
                        `utm_source`,
                        `utm_medium`
                    ORDER BY
                        `requests_submissions` DESC
                    
                ");

                while($row = $result->fetch_object()) {
                    $data[] = [
                        'utm_source' => $row->utm_source,
                        $type => $row->{$type},
                        'submissions' => (int) $row->submissions
                    ];
                }

                break;

            case 'utm_campaign':

                $utm_source = trim(query_clean($_GET['utm_source']));
                $utm_medium = trim(query_clean($_GET['utm_medium']));

                $result = database()->query("
                    SELECT
                        `utm_source`,
                        `utm_medium`,
                        `utm_campaign`,
                        COUNT(*) AS `submissions`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$this->transfer_request->transfer_request_id}
                        AND `utm_source` = '{$utm_source}'
                        AND `utm_medium` = '{$utm_medium}'
                        AND (`datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}')
                    GROUP BY
                        `utm_source`,
                        `utm_campaign`,
                        `utm_campaign`
                    ORDER BY
                        `requests_submissions` DESC
                    
                ");

                while($row = $result->fetch_object()) {
                    $data[] = [
                        'utm_source' => $row->utm_source,
                        'utm_medium' => $row->utm_medium,
                        $type => $row->{$type},
                        'submissions' => (int) $row->submissions
                    ];
                }

                break;

            case 'hour':

                /* Group by HOUR after timezone adjustment */
                $result = database()->query("
                    SELECT 
                        HOUR(`datetime`) AS `hour`,
                        COUNT(*) AS `submissions`
                    FROM
                        `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$this->transfer_request->transfer_request_id}
                        AND (`datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}')
                    GROUP BY
                        `hour`
                    ORDER BY
                        `hour`
                ");

                while($row = $result->fetch_object()) {
                    $hour_start = sprintf('%02d:00', $row->hour);
                    $hour_end = sprintf('%02d:00', ($row->hour + 1) % 24);
                    $label = $hour_start . ' - ' . $hour_end;

                    $data[] = [
                        $type => $label,
                        'submissions' => (int) $row->submissions
                    ];
                }

                break;
        }

        Response::jsonapi_success($data);

    }

}
