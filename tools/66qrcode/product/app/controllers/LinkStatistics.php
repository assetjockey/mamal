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
use Altum\Title;

defined('ALTUMCODE') || die();

class LinkStatistics extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!$this->user->plan_settings->analytics_is_enabled) {
            Alerts::add_error(l('global.info_message.plan_feature_no_access'));
            redirect('links');
        }

        $link_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$link = db()->where('link_id', $link_id)->where('user_id', $this->user->user_id)->getOne('links')) {
            redirect('links');
        }

        /* Generate the link full URL base */
        $link->full_url = (new \Altum\Models\Link())->get_link_full_url($link, $this->user);
        $link->qr_code_id = $link->type == 'file' ? db()->where('link_id', $link->link_id)->getValue('qr_codes', 'qr_code_id') : null;

        /* Statistics related variables */
        $type = isset($_GET['type']) && in_array($_GET['type'], ['overview', 'entries', 'referrer_host', 'referrer_path', 'crawler', 'continent_code', 'country', 'region_name', 'city_name', 'timezone', 'os', 'browser', 'device', 'language', 'utm_source', 'utm_medium', 'utm_campaign', 'hour', 'weekday', 'visitor_type', 'acquisition_channel']) ? input_clean($_GET['type']) : 'overview';

        $datetime = \Altum\Date::get_start_end_dates_new();
        $referrer_type = isset($_GET['referrer_type']) && in_array($_GET['referrer_type'], ['all', 'ai', 'social_media', 'search_engines', 'other_websites']) ? input_clean($_GET['referrer_type']) : 'all';
        $crawler_type = isset($_GET['crawler_type']) && in_array($_GET['crawler_type'], ['all', 'ai', 'search_engines']) ? input_clean($_GET['crawler_type']) : 'all';

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
                        COUNT(`id`) AS `pageviews`,
                        SUM(`is_unique`) AS `visitors`,
                        DATE_FORMAT({$convert_tz_sql}, '{$datetime['query_date_format']}') AS `formatted_date`
                    FROM
                         `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        AND (`datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}')
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
                        `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'
                    ORDER BY
                        `datetime` DESC
                    LIMIT {$limit}
                ");

                break;

            case 'entries':

                /* Prepare the filtering system */
                $filters = (new \Altum\Filters([], [], ['datetime'], allowed_datetime_fields: ['datetime']));
                $filters->set_default_order_by('id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
                $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

                /* Prepare the paginator */
                $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `statistics` WHERE `link_id` = {$link->link_id} AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}' {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
                $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('link-statistics/' . $link->link_id . '?type=' . $type . '&start_date=' . $datetime['start_date'] . '&end_date=' . $datetime['end_date'] . $filters->get_get() . '&page={{PAGE}}')));

                $result = database()->query("
                    SELECT
                        *
                    FROM
                        `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'
                    {$filters->get_sql_where()}
                    {$filters->get_sql_order_by()}
                    {$paginator->get_sql_limit()}
                ");

                break;

            case 'referrer_host':

                /* Group referrers */
                $referrer_host_sql = "
                    CASE
                        WHEN `referrer_host` = 'l.threads.com' THEN 'threads.com'
                        WHEN `referrer_host` IN ('l.facebook.com', 'lm.facebook.com', 'm.facebook.com', 'www.facebook.com', 'staticxx.facebook.com') THEN 'facebook.com'
                        WHEN `referrer_host` IN ('l.instagram.com', 'www.instagram.com') THEN 'instagram.com'
                        WHEN `referrer_host` LIKE '%.pinterest.com' OR `referrer_host` = 'www.pinterest.com' THEN 'pinterest.com'
                        WHEN `referrer_host` IN ('t.co', 'twitter.com') THEN 'x.com'
                        WHEN `referrer_host` IN ('www.youtube.com', 'm.youtube.com', 'youtube.com') THEN 'youtube.com'
                        WHEN `referrer_host` IN ('www.tiktok.com', 'm.tiktok.com') THEN 'tiktok.com'
                        WHEN `referrer_host` IN ('www.reddit.com', 'reddit.com') THEN 'reddit.com'
                        WHEN `referrer_host` IN ('www.linkedin.com', 'linkedin.com') THEN 'linkedin.com'
                        WHEN `referrer_host` IN ('story.snapchat.com', 'www.snapchat.com') THEN 'snapchat.com'
                        WHEN `referrer_host` IN ('t.me', 'telegram.me', 'web.telegram.org') THEN 'telegram.org'
                        WHEN `referrer_host` IN ('www.bing.com', 'bing.com') THEN 'bing.com'
                        WHEN `referrer_host` IN ('www.baidu.com', 'baidu.com') THEN 'baidu.com'
                        WHEN `referrer_host` LIKE 'www.google.%' OR `referrer_host` LIKE 'google.%' THEN 'google.com'
                        WHEN `referrer_host` LIKE 'search.yahoo.com' OR `referrer_host` LIKE 'www.yahoo.com' OR `referrer_host` LIKE '%.yahoo.com' THEN 'yahoo.com'
                        WHEN `referrer_host` IN ('yandex.com', 'www.yandex.com') THEN 'yandex.com'
                        WHEN `referrer_host` IN ('duckduckgo.com', 'www.duckduckgo.com') THEN 'duckduckgo.com'
                        WHEN `referrer_host` IN ('ecosia.org', 'www.ecosia.org') THEN 'ecosia.org'
                        WHEN `referrer_host` IN ('startpage.com', 'www.startpage.com') THEN 'startpage.com'
                        WHEN `referrer_host` IN ('search.aol.com') THEN 'aol.com'
                        WHEN `referrer_host` LIKE 'search.brave.com' THEN 'brave.com'
                        WHEN `referrer_host` IN ('chat.openai.com', 'openai.com') THEN 'openai.com'
                        WHEN `referrer_host` = 'claude.ai' THEN 'claude.ai'
                        WHEN `referrer_host` IN ('perplexity.ai', 'www.perplexity.ai') THEN 'perplexity.ai'
                        WHEN `referrer_host` = 'copilot.microsoft.com' THEN 'copilot.microsoft.com'
                        ELSE `referrer_host`
                    END
                ";
                $referrer_type_sql_where = null;

                switch($referrer_type) {
                    case 'ai':
                        $referrer_type_sql_where = "AND {$referrer_host_sql} IN ('openai.com', 'claude.ai', 'perplexity.ai', 'copilot.microsoft.com')";
                        break;

                    case 'social_media':
                        $referrer_type_sql_where = "AND {$referrer_host_sql} IN ('threads.com', 'facebook.com', 'instagram.com', 'pinterest.com', 'x.com', 'youtube.com', 'tiktok.com', 'reddit.com', 'linkedin.com', 'snapchat.com', 'telegram.org')";
                        break;

                    case 'search_engines':
                        $referrer_type_sql_where = "AND {$referrer_host_sql} IN ('bing.com', 'baidu.com', 'google.com', 'yahoo.com', 'yandex.com', 'duckduckgo.com', 'ecosia.org', 'startpage.com', 'aol.com', 'brave.com')";
                        break;

                    case 'other_websites':
                        $referrer_type_sql_where = "AND `referrer_host` IS NOT NULL AND {$referrer_host_sql} NOT IN ('openai.com', 'claude.ai', 'perplexity.ai', 'copilot.microsoft.com', 'threads.com', 'facebook.com', 'instagram.com', 'pinterest.com', 'x.com', 'youtube.com', 'tiktok.com', 'reddit.com', 'linkedin.com', 'snapchat.com', 'telegram.org', 'bing.com', 'baidu.com', 'google.com', 'yahoo.com', 'yandex.com', 'duckduckgo.com', 'ecosia.org', 'startpage.com', 'aol.com', 'brave.com')";
                        break;
                }

                $result = database()->query("
                    SELECT
                        {$referrer_host_sql} AS `referrer_host`,
                        COUNT(*) AS `total`
                    FROM
                         `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        {$referrer_type_sql_where}
                        AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'
                    GROUP BY
                        {$referrer_host_sql}
                    ORDER BY
                        `total` DESC

                ");

                break;

            case 'crawler':

                /* Group crawlers */
                $crawler_type_sql_where = null;

                switch($crawler_type) {
                    case 'ai':
                        $crawler_type_sql_where = "AND `crawler_category` IN ('ai_search', 'ai_training', 'ai_agent')";
                        break;

                    case 'search_engines':
                        $crawler_type_sql_where = "AND `crawler_category` = 'search_engine'";
                        break;
                }

                $result = database()->query("
                    SELECT
                        `crawler_name`,
                        `crawler_category`,
                        COUNT(*) AS `total`
                    FROM
                         `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        {$crawler_type_sql_where}
                        AND `is_crawler` = 1
                        AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'
                    GROUP BY
                        `crawler_name`,
                        `crawler_category`
                    ORDER BY
                        `total` DESC

                ");

                break;

            case 'continent_code':
            case 'timezone':
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
                    'timezone' => 'timezone',
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
                         `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'
                    GROUP BY
                        `{$columns[$type]}`
                    ORDER BY
                        `total` DESC

                ");

                break;

            case 'referrer_path':

                $referrer_host = input_clean($_GET['referrer_host']);
                $referrer_host = database()->real_escape_string($referrer_host);
                $referrer_host_sql = "
                    CASE
                        WHEN `referrer_host` = 'l.threads.com' THEN 'threads.com'
                        WHEN `referrer_host` IN ('l.facebook.com', 'lm.facebook.com', 'm.facebook.com', 'www.facebook.com', 'staticxx.facebook.com') THEN 'facebook.com'
                        WHEN `referrer_host` IN ('l.instagram.com', 'www.instagram.com') THEN 'instagram.com'
                        WHEN `referrer_host` LIKE '%.pinterest.com' OR `referrer_host` = 'www.pinterest.com' THEN 'pinterest.com'
                        WHEN `referrer_host` IN ('t.co', 'twitter.com') THEN 'x.com'
                        WHEN `referrer_host` IN ('www.youtube.com', 'm.youtube.com', 'youtube.com') THEN 'youtube.com'
                        WHEN `referrer_host` IN ('www.tiktok.com', 'm.tiktok.com') THEN 'tiktok.com'
                        WHEN `referrer_host` IN ('www.reddit.com', 'reddit.com') THEN 'reddit.com'
                        WHEN `referrer_host` IN ('www.linkedin.com', 'linkedin.com') THEN 'linkedin.com'
                        WHEN `referrer_host` IN ('story.snapchat.com', 'www.snapchat.com') THEN 'snapchat.com'
                        WHEN `referrer_host` IN ('t.me', 'telegram.me', 'web.telegram.org') THEN 'telegram.org'
                        WHEN `referrer_host` IN ('www.bing.com', 'bing.com') THEN 'bing.com'
                        WHEN `referrer_host` IN ('www.baidu.com', 'baidu.com') THEN 'baidu.com'
                        WHEN `referrer_host` LIKE 'www.google.%' OR `referrer_host` LIKE 'google.%' THEN 'google.com'
                        WHEN `referrer_host` LIKE 'search.yahoo.com' OR `referrer_host` LIKE 'www.yahoo.com' OR `referrer_host` LIKE '%.yahoo.com' THEN 'yahoo.com'
                        WHEN `referrer_host` IN ('yandex.com', 'www.yandex.com') THEN 'yandex.com'
                        WHEN `referrer_host` IN ('duckduckgo.com', 'www.duckduckgo.com') THEN 'duckduckgo.com'
                        WHEN `referrer_host` IN ('ecosia.org', 'www.ecosia.org') THEN 'ecosia.org'
                        WHEN `referrer_host` IN ('startpage.com', 'www.startpage.com') THEN 'startpage.com'
                        WHEN `referrer_host` IN ('search.aol.com') THEN 'aol.com'
                        WHEN `referrer_host` LIKE 'search.brave.com' THEN 'brave.com'
                        WHEN `referrer_host` IN ('chat.openai.com', 'openai.com') THEN 'openai.com'
                        WHEN `referrer_host` = 'claude.ai' THEN 'claude.ai'
                        WHEN `referrer_host` IN ('perplexity.ai', 'www.perplexity.ai') THEN 'perplexity.ai'
                        WHEN `referrer_host` = 'copilot.microsoft.com' THEN 'copilot.microsoft.com'
                        ELSE `referrer_host`
                    END
                ";
                $referrer_host_sql_where = "AND {$referrer_host_sql} = '{$referrer_host}'";

                $result = database()->query("
                    SELECT
                        `referrer_path`,
                        COUNT(*) AS `total`
                    FROM
                         `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        {$referrer_host_sql_where}
                        AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'
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
                         `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        " . ($continent_code ? "AND `continent_code` = '{$continent_code}'" : null) . "
                        AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'
                    GROUP BY
                        " . ($continent_code ? "`continent_code`," : null) . "
                        `country_code`
                    ORDER BY
                        `total` DESC
                ");

                break;

            case 'region_name':

                /* Filter by country */
                $country_code = isset($_GET['country_code']) ? input_clean($_GET['country_code']) : null;
                $country_code = $country_code ? database()->real_escape_string($country_code) : null;

                /* Group by region */
                $result = database()->query("
                    SELECT
                        `country_code`,
                        `region_name`,
                        COUNT(*) AS `total`
                    FROM
                         `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        " . ($country_code ? "AND `country_code` = '{$country_code}'" : null) . "
                        AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'
                    GROUP BY
                        `country_code`,
                        `region_name`
                    ORDER BY
                        `total` DESC

                ");

                break;

            case 'city_name':

                /* Filter by location */
                $country_code = isset($_GET['country_code']) ? input_clean($_GET['country_code']) : null;
                $country_code = $country_code ? database()->real_escape_string($country_code) : null;
                $region_name = isset($_GET['region_name']) ? input_clean($_GET['region_name']) : null;
                $region_name = $region_name ? database()->real_escape_string($region_name) : null;

                $result = database()->query("
                    SELECT
                        `country_code`,
                        `region_name`,
                        `city_name`,
                        COUNT(*) AS `total`
                    FROM
                         `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        " . ($country_code ? "AND `country_code` = '{$country_code}'" : null) . "
                        " . ($region_name ? "AND `region_name` = '{$region_name}'" : null) . "
                        AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'
                    GROUP BY
                        `country_code`,
                        `region_name`,
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
                         `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'
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
                         `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        AND `utm_source` = '{$utm_source}'
                        AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'
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
                         `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        AND `utm_source` = '{$utm_source}'
                        AND `utm_medium` = '{$utm_medium}'
                        AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'
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
                        `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        AND (`datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}')
                    GROUP BY
                        `hour`
                    ORDER BY
                        `total` DESC
                ");

                break;

            case 'weekday':

                /* Get the timezone conversion SQL */
                $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

                /* Group by weekday after timezone adjustment */
                $result = database()->query("
                    SELECT
                        WEEKDAY({$convert_tz_sql}) + 1 AS `weekday`,
                        COUNT(*) AS `total`
                    FROM
                        `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        AND (`datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}')
                    GROUP BY
                        `weekday`
                    ORDER BY
                        `total` DESC
                ");

                break;

            case 'visitor_type':

                /* Group by visitor type */
                $result = database()->query("
                    SELECT
                        IF(`is_crawler` = 1, 'crawler', 'human') AS `visitor_type`,
                        `is_crawler`,
                        COUNT(*) AS `total`
                    FROM
                         `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'
                    GROUP BY
                        `is_crawler`
                    ORDER BY
                        `total` DESC
                ");

                break;

            case 'acquisition_channel':

                /* Group referrers */
                $referrer_host_sql = "
                    CASE
                        WHEN `referrer_host` = 'l.threads.com' THEN 'threads.com'
                        WHEN `referrer_host` IN ('l.facebook.com', 'lm.facebook.com', 'm.facebook.com', 'www.facebook.com', 'staticxx.facebook.com') THEN 'facebook.com'
                        WHEN `referrer_host` IN ('l.instagram.com', 'www.instagram.com') THEN 'instagram.com'
                        WHEN `referrer_host` LIKE '%.pinterest.com' OR `referrer_host` = 'www.pinterest.com' THEN 'pinterest.com'
                        WHEN `referrer_host` IN ('t.co', 'twitter.com') THEN 'x.com'
                        WHEN `referrer_host` IN ('www.youtube.com', 'm.youtube.com', 'youtube.com') THEN 'youtube.com'
                        WHEN `referrer_host` IN ('www.tiktok.com', 'm.tiktok.com') THEN 'tiktok.com'
                        WHEN `referrer_host` IN ('www.reddit.com', 'reddit.com') THEN 'reddit.com'
                        WHEN `referrer_host` IN ('www.linkedin.com', 'linkedin.com') THEN 'linkedin.com'
                        WHEN `referrer_host` IN ('story.snapchat.com', 'www.snapchat.com') THEN 'snapchat.com'
                        WHEN `referrer_host` IN ('t.me', 'telegram.me', 'web.telegram.org') THEN 'telegram.org'
                        WHEN `referrer_host` IN ('www.bing.com', 'bing.com') THEN 'bing.com'
                        WHEN `referrer_host` IN ('www.baidu.com', 'baidu.com') THEN 'baidu.com'
                        WHEN `referrer_host` LIKE 'www.google.%' OR `referrer_host` LIKE 'google.%' THEN 'google.com'
                        WHEN `referrer_host` LIKE 'search.yahoo.com' OR `referrer_host` LIKE 'www.yahoo.com' OR `referrer_host` LIKE '%.yahoo.com' THEN 'yahoo.com'
                        WHEN `referrer_host` IN ('yandex.com', 'www.yandex.com') THEN 'yandex.com'
                        WHEN `referrer_host` IN ('duckduckgo.com', 'www.duckduckgo.com') THEN 'duckduckgo.com'
                        WHEN `referrer_host` IN ('ecosia.org', 'www.ecosia.org') THEN 'ecosia.org'
                        WHEN `referrer_host` IN ('startpage.com', 'www.startpage.com') THEN 'startpage.com'
                        WHEN `referrer_host` IN ('search.aol.com') THEN 'aol.com'
                        WHEN `referrer_host` LIKE 'search.brave.com' THEN 'brave.com'
                        WHEN `referrer_host` IN ('chat.openai.com', 'openai.com') THEN 'openai.com'
                        WHEN `referrer_host` = 'claude.ai' THEN 'claude.ai'
                        WHEN `referrer_host` IN ('perplexity.ai', 'www.perplexity.ai') THEN 'perplexity.ai'
                        WHEN `referrer_host` = 'copilot.microsoft.com' THEN 'copilot.microsoft.com'
                        ELSE `referrer_host`
                    END
                ";

                /* Group by acquisition channel */
                $acquisition_channel_sql = "
                    CASE
                        WHEN `referrer_host` IS NULL OR `referrer_host` = '' THEN 'direct'
                        WHEN {$referrer_host_sql} IN ('openai.com', 'claude.ai', 'perplexity.ai', 'copilot.microsoft.com') THEN 'ai'
                        WHEN {$referrer_host_sql} IN ('threads.com', 'facebook.com', 'instagram.com', 'pinterest.com', 'x.com', 'youtube.com', 'tiktok.com', 'reddit.com', 'linkedin.com', 'snapchat.com', 'telegram.org') THEN 'social_media'
                        WHEN {$referrer_host_sql} IN ('bing.com', 'baidu.com', 'google.com', 'yahoo.com', 'yandex.com', 'duckduckgo.com', 'ecosia.org', 'startpage.com', 'aol.com', 'brave.com') THEN 'search_engines'
                        ELSE 'referring_websites'
                    END
                ";

                $result = database()->query("
                    SELECT
                        {$acquisition_channel_sql} AS `acquisition_channel`,
                        COUNT(*) AS `total`
                    FROM
                         `statistics`
                    WHERE
                        `link_id` = {$link->link_id}
                        AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'
                    GROUP BY
                        `acquisition_channel`
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
                    'link' => $link,
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
                    'link' => $link,
                    'datetime' => $datetime,
                    'pagination' => $pagination,
                    'filters' => $filters,
                ];

                $has_data = count($statistics);

                break;

            case 'referrer_host':
            case 'crawler':
            case 'continent_code':
            case 'country':
            case 'region_name':
            case 'city_name':
            case 'timezone':
            case 'os':
            case 'browser':
            case 'device':
            case 'language':
            case 'referrer_path':
            case 'utm_source':
            case 'utm_medium':
            case 'utm_campaign':
            case 'hour':
            case 'weekday':
            case 'visitor_type':
            case 'acquisition_channel':

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
                    'link' => $link,
                    'datetime' => $datetime,

                    'referrer_host' => $referrer_host ?? null,
                    'continent_code' => $continent_code ?? null,
                    'country_code' => $country_code ?? null,
                    'region_name' => isset($region_name) ? $region_name : null,
                    'utm_source' => $utm_source ?? null,
                    'utm_medium' => $utm_medium ?? null,
                    'referrer_type' => $referrer_type,
                    'crawler_type' => $crawler_type,
                ];

                $has_data = count($statistics);

                break;
        }

        /* Set a custom title */
        Title::set(sprintf(l('link_statistics.title'), $link->url));

        /* Export handler */
        process_export_csv($statistics);
        process_export_json($statistics);

        $data['type'] = $type;
        $data['referrer_type'] = $referrer_type;
        $data['crawler_type'] = $crawler_type;
        $view = new \Altum\View('link-statistics/statistics_' . $type, (array) $this);
        $this->add_view_content('statistics', $view->run($data));

        /* Delete Modal */
        $view = new \Altum\View('links/link_delete_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Prepare the view */
        $data = [
            'link' => $link,
            'type' => $type,
            'datetime' => $datetime,
            'has_data' => $has_data,
            'referrer_type' => $referrer_type,
            'crawler_type' => $crawler_type,
        ];

        $view = new \Altum\View('link-statistics/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function reset() {

        \Altum\Authentication::guard();

        if(!$this->user->plan_settings->analytics_is_enabled) {
            Alerts::add_error(l('global.info_message.plan_feature_no_access'));
            redirect('links');
        }

        if (empty($_POST)) {
            throw_404();
        }

        $link_id = (int) $_POST['link_id'];
        $datetime = \Altum\Date::get_start_end_dates_new($_POST['start_date'], $_POST['end_date']);

        /* Make sure the link id is created by the logged in user */
        if(!$link = db()->where('link_id', $link_id)->where('user_id', $this->user->user_id)->getOne('links', ['link_id'])) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.links')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('link-statistics/' . $link->link_id);
        }

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('link-statistics/' . $link->link_id);
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Clear statistics data */
            database()->query("DELETE FROM `statistics` WHERE `link_id` = {$link->link_id} AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'");

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.update2'));

            redirect('link-statistics/' . $link->link_id);

        }

        redirect('link-statistics/' . $link->link_id);

    }

}
