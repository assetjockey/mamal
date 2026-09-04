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
use Altum\Title;

defined('ALTUMCODE') || die();

class TransferRequestSubmissionsStatistics extends Controller {

    public function index() {

		if(!settings()->transfers->transfer_requests_is_enabled) {
			throw_404();
		}

        //\Altum\Authentication::guard();

        $transfer_request_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Get transfer_request details */
        if(!$transfer_request = db()->where('transfer_request_id', $transfer_request_id)->getOne('transfer_requests')) {
            throw_404();
        }

        /* Make sure the current user has access */
        if(($transfer_request->uploader_id != md5(get_ip())) && (!$transfer_request->user_id || $transfer_request->user_id != $this->user->user_id)) {
            throw_404();
        }

        /* Check for plan access */
        if(!$this->user->plan_settings->analytics_is_enabled) {
            Alerts::add_error(l('global.info_message.plan_feature_no_access'));
            redirect('transfer-request-submissions-statistics/' . $transfer_request->transfer_request_id);
        }

        /* Generate the transfer full URL base */
		$transfer_request->full_url = (new \Altum\Models\TransferRequests())->get_transfer_request_full_url($transfer_request, $this->user);

        /* Statistics related variables */
        $type = isset($_GET['type']) && in_array($_GET['type'], ['overview', 'entries', 'referrer_host', 'referrer_path', 'continent_code', 'country', 'city_name', 'os', 'browser', 'device', 'language', 'utm_source', 'utm_medium', 'utm_campaign', 'hour']) ? input_clean($_GET['type']) : 'overview';

        $datetime = \Altum\Date::get_start_end_dates_new();

        /* Get data based on what statistics are needed */
        switch($type) {
            case 'overview':

                /* Get the required statistics */
                $pageviews = [];
                $pageviews_chart = [];
                $totals = [
                    'pageviews' => 0,
                    'visitors' => 0,
                ];

                $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

                $pageviews_result = database()->query("
                    SELECT
                        COUNT(*) AS `pageviews`,
                        SUM(`is_unique`) AS `visitors`,
                        DATE_FORMAT({$convert_tz_sql}, '{$datetime['query_date_format']}') AS `formatted_date`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$transfer_request->transfer_request_id}
                        AND ({$convert_tz_sql} BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    GROUP BY
                        `formatted_date`
                    ORDER BY
                        `formatted_date`
                ");

                /* Generate the raw chart data and save pageviews for later usage */
                while($row = $pageviews_result->fetch_object()) {
                    $pageviews[] = $row;

                    $row->formatted_date = $datetime['process']($row->formatted_date, true);

                    $pageviews_chart[$row->formatted_date] = [
                        'pageviews' => $row->pageviews,
                        'visitors' => $row->visitors
                    ];

                    $totals['pageviews'] += $row->pageviews;
                    $totals['visitors'] += $row->visitors;
                }

                $pageviews_chart = get_chart_data($pageviews_chart);

                $limit = $this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page;
                $result = database()->query("
                    SELECT
                        *
                    FROM
                        `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$transfer_request->transfer_request_id}
                        AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    ORDER BY
                        `datetime` DESC
                    LIMIT {$limit}
                ");

                break;

            case 'entries':

                /* Prepare the filtering system */
                $filters = (new \Altum\Filters([], [], ['datetime'], allowed_datetime_fields: ['datetime']));
                $filters->set_default_order_by('request_submission_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
                $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

                /* Prepare the paginator */
                $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `requests_submissions` WHERE `transfer_request_id` = {$transfer_request->transfer_request_id} AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}') {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
                $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('transfer-request-submissions-statistics/' . $transfer_request->transfer_request_id . '?type=' . $type . '&start_date=' . $datetime['start_date'] . '&end_date=' . $datetime['end_date'] . $filters->get_get() . '&page={{PAGE}}')));

                $result = database()->query("
                    SELECT
                        *
                    FROM
                        `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$transfer_request->transfer_request_id}
                        AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    {$filters->get_sql_where()}
                    {$filters->get_sql_order_by()}
                    {$paginator->get_sql_limit()}
                ");

                break;

            case 'referrer_host':
            case 'continent_code':
            case 'os':
            case 'browser':
            case 'device':
            case 'language':

                $columns = [
                    'referrer_host' => 'referrer_host',
                    'referrer_path' => 'referrer_path',
                    'continent_code' => 'continent_code',
                    'country' => 'country_code',
                    'city_name' => 'city_name',
                    'os' => 'os_name',
                    'browser' => 'browser_name',
                    'device' => 'device_type',
                    'language' => 'browser_language'
                ];

                $result = database()->query("
                    SELECT
                        `{$columns[$type]}`,
                        COUNT(*) AS `total`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$transfer_request->transfer_request_id}
                        AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    GROUP BY
                        `{$columns[$type]}`
                    ORDER BY
                        `total` DESC
                    
                ");

                break;

            case 'referrer_path':

                $referrer_host = input_clean($_GET['referrer_host']);

                $result = database()->query("
                    SELECT
                        `referrer_path`,
                        COUNT(*) AS `total`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$transfer_request->transfer_request_id}
                        AND `referrer_host` = '{$referrer_host}'
                        AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    GROUP BY
                        `referrer_path`
                    ORDER BY
                        `total` DESC
                    
                ");

                break;

            case 'country':

                $continent_code = isset($_GET['continent_code']) ? input_clean($_GET['continent_code']) : null;

                $result = database()->query("
                    SELECT
                        `country_code`,
                        " . ($continent_code ? "`continent_code`," : null) . "
                        COUNT(*) AS `total`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$transfer_request->transfer_request_id}
                        " . ($continent_code ? "AND `continent_code` = '{$continent_code}'" : null) . "
                        AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    GROUP BY
                        " . ($continent_code ? "`continent_code`," : null) . "
                        `country_code`
                    ORDER BY
                        `total` DESC
                    
                ");

                break;

            case 'city_name':

                $country_code = isset($_GET['country_code']) ? input_clean($_GET['country_code']) : null;

                $result = database()->query("
                    SELECT
                        `country_code`,
                        `city_name`,
                        COUNT(*) AS `total`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$transfer_request->transfer_request_id}
                        " . ($country_code ? "AND `country_code` = '{$country_code}'" : null) . "
                        AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    GROUP BY
                        `country_code`,
                        `city_name`
                    ORDER BY
                        `total` DESC
                    
                ");


                break;

            case 'utm_source':

                $result = database()->query("
                    SELECT
                        `utm_source`,
                        COUNT(*) AS `total`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$transfer_request->transfer_request_id}
                        AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                        AND `utm_source` IS NOT NULL
                    GROUP BY
                        `utm_source`
                    ORDER BY
                        `total` DESC
                    
                ");

                break;

            case 'utm_medium':

                $utm_source = input_clean($_GET['utm_source']);

                $result = database()->query("
                    SELECT
                        `utm_medium`,
                        COUNT(*) AS `total`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$transfer_request->transfer_request_id}
                        AND `utm_source` = '{$utm_source}'
                        AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    GROUP BY
                        `utm_medium`
                    ORDER BY
                        `total` DESC
                    
                ");

                break;

            case 'utm_campaign':

                $utm_source = input_clean($_GET['utm_source']);
                $utm_medium = input_clean($_GET['utm_medium']);

                $result = database()->query("
                    SELECT
                        `utm_campaign`,
                        COUNT(*) AS `total`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$transfer_request->transfer_request_id}
                        AND `utm_source` = '{$utm_source}'
                        AND `utm_medium` = '{$utm_medium}'
                        AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    GROUP BY
                        `utm_campaign`
                    ORDER BY
                        `total` DESC
                    
                ");

                break;

            case 'hour':

                /* Get the timezone conversion SQL */
                $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

                /* Group by HOUR after timezone adjustment */
                $result = database()->query("
                    SELECT 
                        HOUR({$convert_tz_sql}) AS `hour`,
                        COUNT(*) AS `total`
                    FROM
                         `requests_submissions`
                    WHERE
                        `transfer_request_id` = {$transfer_request->transfer_request_id}
                        AND ({$convert_tz_sql} BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    GROUP BY
                        `hour`
                    ORDER BY
                        `total` DESC
                ");

                break;
        }

        switch($type) {
            case 'overview':

                $statistics_keys = [
                    'continent_code',
                    'country_code',
                    'city_name',
                    'referrer_host',
                    'device_type',
                    'os_name',
                    'browser_name',
                    'browser_language'
                ];

                $latest = [];
                $statistics = [];
                foreach($statistics_keys as $key) {
                    $statistics[$key] = [];
                    $statistics[$key . '_total_sum'] = 0;
                }

                $has_data = $result->num_rows;

                /* Start processing the rows from the database */
                while($row = $result->fetch_object()) {
                    foreach($statistics_keys as $key) {

                        $row->{$key} = $row->{$key} ?? '';

                        $statistics[$key][$row->{$key}] = isset($statistics[$key][$row->{$key}]) ? $statistics[$key][$row->{$key}] + 1 : 1;

                        $statistics[$key . '_total_sum']++;

                    }

                    $latest[] = $row;
                }

                foreach($statistics_keys as $key) {
                    arsort($statistics[$key]);
                }

                /* Prepare the statistics method View */
                $data = [
                    'statistics' => $statistics,
                    'transfer_request' => $transfer_request,
                    'datetime' => $datetime,
                    'latest' => $latest,
                    'pageviews' => $pageviews,
                    'pageviews_chart' => $pageviews_chart,
                    'totals' => $totals,
                ];

                break;

            case 'entries':

                /* Store all the results from the database */
                $statistics = [];

                while($row = $result->fetch_object()) {
                    $statistics[] = $row;
                }

                /* Prepare the pagination view */
                $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

                /* Prepare the statistics method View */
                $data = [
                    'rows' => $statistics,
                    'transfer_request' => $transfer_request,
                    'datetime' => $datetime,
                    'pagination' => $pagination,
                    'filters' => $filters,
                ];

                $has_data = count($statistics);

                break;

            case 'referrer_host':
            case 'continent_code':
            case 'country':
            case 'city_name':
            case 'os':
            case 'browser':
            case 'device':
            case 'language':
            case 'referrer_path':
            case 'utm_source':
            case 'utm_medium':
            case 'utm_campaign':
            case 'hour':

                /* Store all the results from the database */
                $statistics = [];
                $statistics_total_sum = 0;

                while($row = $result->fetch_object()) {
                    $statistics[] = $row;

                    $statistics_total_sum += $row->total;
                }

                /* Prepare the statistics method View */
                $data = [
                    'rows' => $statistics,
                    'total_sum' => $statistics_total_sum,
                    'transfer_request' => $transfer_request,
                    'datetime' => $datetime,

                    'referrer_host' => $referrer_host ?? null,
                    'continent_code' => $continent_code ?? null,
                    'country_code' => $country_code ?? null,
                    'utm_source' => $utm_source ?? null,
                    'utm_medium' => $utm_medium ?? null,
                ];

                $has_data = count($statistics);

                break;
        }

        /* Set a custom title */
        Title::set(sprintf(l('transfer_statistics.title'), $transfer_request->url));

        /* Export handler */
        process_export_csv($statistics);
        process_export_json($statistics);

        $data['type'] = $type;
        $view = new \Altum\View('transfer-request-submissions-statistics/statistics_' . $type, (array) $this);
        $this->add_view_content('statistics', $view->run($data));

        /* Prepare the view */
        $data = [
            'transfer_request' => $transfer_request,
            'type' => $type,
            'datetime' => $datetime,
            'has_data' => $has_data,
        ];

        $view = new \Altum\View('transfer-request-submissions-statistics/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
