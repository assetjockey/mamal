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

use Altum\Response;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiPageviewsLightweight extends Controller {
    use Apiable;

    public function index() {

        $this->verify_request();

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                /* Detect if we only need an object, or the whole list */
                if(isset($this->params[0])) {
                    $this->get();
                } else {
                    $this->get_all();
                }

                break;

            case 'POST':

                /* Detect what method to use */
                if(isset($this->params[0])) {
                    $this->patch();
                } else {
                    $this->post();
                }

                break;

            case 'DELETE':
                $this->delete();
                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Website */
        $website_id = isset($_GET['website_id']) ? (int) $_GET['website_id'] : null;

        /* Verify ownership */
        $website = \Altum\Cache::cache_function_result('website?website_id=' . $website_id, ['website_id=' . $website_id], function() use ($website_id) {
            return db()
                ->where('website_id', $website_id)
                ->where('user_id', $this->user->user_id)
                ->getOne('websites', ['website_id']);
        });

        if(!$website) {
            $this->return_404();
        }

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['website_id', 'type', 'continent_code', 'country_code', 'device_type', 'theme', 'browser_language', 'browser_timezone'], ['path', 'referrer_host', 'referrer_path', 'utm_source', 'utm_medium', 'utm_campaign', 'region_name', 'city_name', 'os_name', 'browser_name'], ['event_id', 'datetime', 'expiration_date'], allowed_datetime_fields: ['datetime']));
        $filters->set_default_order_by('event_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `lightweight_events` WHERE `website_id` = {$website_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/pageviews-lightweight?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `lightweight_events`
            WHERE
                `website_id` = {$website_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");

        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->event_id,
                'website_id' => (int) $row->website_id,
                'type' => $row->type,
                'path' => $row->path,
                'referrer_host' => $row->referrer_host,
                'referrer_path' => $row->referrer_path,
                'utm_source' => $row->utm_source,
                'utm_medium' => $row->utm_medium,
                'utm_campaign' => $row->utm_campaign,
                'continent_code' => $row->continent_code,
                'country_code' => $row->country_code,
                'region_name' => $row->region_name,
                'city_name' => $row->city_name,
                'os_name' => $row->os_name,
                'browser_name' => $row->browser_name,
                'browser_language' => $row->browser_language,
                'browser_timezone' => $row->browser_timezone,
                'screen_resolution' => $row->screen_resolution,
                'device_type' => $row->device_type,
                'theme' => $row->theme,
                'expiration_date' => $row->expiration_date,
                'datetime' => $row->date,
            ];

            $data[] = $row;
        }

        /* Prepare the data */
        $meta = [
            'page' => $_GET['page'] ?? 1,
            'total_pages' => $paginator->getNumPages(),
            'results_per_page' => $filters->get_results_per_page(),
            'total_results' => (int) $total_rows,
        ];

        /* Prepare the pagination links */
        $others = ['links' => [
            'first' => $paginator->getPageUrl(1),
            'last' => $paginator->getNumPages() ? $paginator->getPageUrl($paginator->getNumPages()) : null,
            'next' => $paginator->getNextUrl(),
            'prev' => $paginator->getPrevUrl(),
            'self' => $paginator->getPageUrl($_GET['page'] ?? 1)
        ]];

        Response::jsonapi_success($data, $meta, 200, $others);
    }

    private function get() {

        /* Website */
        $website_id = isset($_GET['website_id']) ? (int) $_GET['website_id'] : null;

        /* Verify ownership */
        $website = \Altum\Cache::cache_function_result('website?website_id=' . $website_id, ['website_id=' . $website_id], function() use ($website_id) {
            return db()
                ->where('website_id', $website_id)
                ->where('user_id', $this->user->user_id)
                ->getOne('websites', ['website_id']);
        });

        if(!$website) {
            $this->return_404();
        }

        $event_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $event = db()->where('event_id', $event_id)->where('website_id', $website_id)->getOne('lightweight_events');

        /* We haven't found the resource */
        if(!$event) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $event->event_id,
            'website_id' => (int) $event->website_id,
            'type' => $event->type,
            'path' => $event->path,
            'referrer_host' => $event->referrer_host,
            'referrer_path' => $event->referrer_path,
            'utm_source' => $event->utm_source,
            'utm_medium' => $event->utm_medium,
            'utm_campaign' => $event->utm_campaign,
            'continent_code' => $event->continent_code,
            'country_code' => $event->country_code,
            'region_name' => $event->region_name,
            'city_name' => $event->city_name,
            'os_name' => $event->os_name,
            'browser_name' => $event->browser_name,
            'browser_language' => $event->browser_language,
            'browser_timezone' => $event->browser_timezone,
            'screen_resolution' => $event->screen_resolution,
            'device_type' => $event->device_type,
            'theme' => $event->theme,
            'expiration_date' => $event->expiration_date,
            'datetime' => $event->date,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for any errors */
        $required_fields = ['website_id', 'type', 'path', 'device_type', 'theme'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['website_id'] = (int) $_POST['website_id'];

        if(!$website_id = db()->where('website_id', $_POST['website_id'])->where('user_id', $this->user->user_id)->getValue('websites', 'website_id')) {
            $this->return_404();
        }

        $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['pageview', 'custom']) ? input_clean($_POST['type']) : 'pageview';
        $_POST['path'] = '/' . trim(input_clean($_POST['path'], 1024), '/');
        $_POST['device_type'] = isset($_POST['device_type']) && in_array($_POST['device_type'], ['tablet', 'desktop', 'mobile']) ? input_clean($_POST['device_type']) : 'desktop';
        $_POST['theme'] = isset($_POST['theme']) && in_array($_POST['theme'], ['light', 'dark']) ? input_clean($_POST['theme']) : 'light';
        $_POST['screen_resolution'] = isset($_POST['screen_resolution']) && preg_match('/^([1-9]\d{1,4})x([1-9]\d{1,4})$/', $_POST['screen_resolution']) ? input_clean($_POST['screen_resolution']) : null;
        $_POST['browser_timezone'] = isset($_POST['browser_timezone']) && in_array($_POST['browser_timezone'], timezone_identifiers_list()) ? input_clean($_POST['browser_timezone']) : null;
        $_POST['browser_language'] = isset($_POST['browser_language']) && preg_match('/^[a-z]{2,3}(-[A-Z]{2})?$/', $_POST['browser_language']) ? input_clean($_POST['browser_language']) : null;
        $_POST['browser_name'] = isset($_POST['browser_name']) ? input_clean($_POST['browser_name'], 32) : null;
        $_POST['os_name'] = isset($_POST['os_name']) ? input_clean($_POST['os_name'], 32) : null;
        $_POST['region_name'] = isset($_POST['region_name']) ? input_clean($_POST['region_name'], 128) : null;
        $_POST['city_name'] = isset($_POST['city_name']) ? input_clean($_POST['city_name'], 128) : null;
        $_POST['country_code'] = isset($_POST['country_code']) && in_array($_POST['country_code'], array_keys(get_countries_no_emoji_array())) ? input_clean($_POST['country_code']) : null;
        $_POST['continent_code'] = isset($_POST['continent_code']) && in_array($_POST['continent_code'], array_keys(get_continents_no_emoji_array())) ? input_clean($_POST['continent_code']) : null;
        $_POST['utm_source'] = isset($_POST['utm_source']) ? input_clean($_POST['utm_source'], 128) : null;
        $_POST['utm_medium'] = isset($_POST['utm_medium']) ? input_clean($_POST['utm_medium'], 128) : null;
        $_POST['utm_campaign'] = isset($_POST['utm_campaign']) ? input_clean($_POST['utm_campaign'], 128) : null;
        $_POST['referrer_host'] = isset($_POST['referrer_host']) ? input_clean($_POST['referrer_host'], 256) : null;
        $_POST['referrer_path'] = isset($_POST['referrer_path']) ? input_clean($_POST['referrer_path'], 1024) : null;

        /* Check for the plan limit */
        $total_rows = db()->where('website_id', $website_id)->getValue('lightweight_events', 'count(*)');

        if($this->user->plan_settings->sessions_events_limit != -1 && $total_rows >= $this->user->plan_settings->sessions_events_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        $expiration_date = (new \DateTime())->modify('+' . $this->user->plan_settings->sessions_events_retention . ' days')->format('Y-m-d');

        /* Database query */
        $event_id = db()->insert('lightweight_events', [
			'website_id' => $_POST['website_id'],
            'type' => $_POST['type'],
            'path' => $_POST['path'],
            'referrer_host' => $_POST['referrer_host'],
            'referrer_path' => $_POST['referrer_path'],
            'utm_source' => $_POST['utm_source'],
            'utm_medium' => $_POST['utm_medium'],
            'utm_campaign' => $_POST['utm_campaign'],
            'continent_code' => $_POST['continent_code'],
            'country_code' => $_POST['country_code'],
            'region_name' => $_POST['region_name'],
            'city_name' => $_POST['city_name'],
            'os_name' => $_POST['os_name'],
            'browser_name' => $_POST['browser_name'],
            'browser_language' => $_POST['browser_language'],
            'browser_timezone' => $_POST['browser_timezone'],
            'screen_resolution' => $_POST['screen_resolution'],
            'device_type' => $_POST['device_type'],
            'theme' => $_POST['theme'],
            'expiration_date' => $expiration_date,
            'date' => get_date(),
        ]);

        /* Update the website usage */
        $website_usage = ['current_month_sessions_events' => db()->inc()];

        if(in_array($_POST['type'], ['landing_page', 'pageview'])) {
            $website_usage['last_24_hours_pageviews'] = db()->inc();
            $website_usage['last_7_days_pageviews'] = db()->inc();
        }

        db()->where('website_id', $_POST['website_id'])->update('websites', $website_usage);

        /* Prepare the data */
        $data = [
            'id' => (int) $event_id,
            'website_id' => (int) $_POST['website_id'],
            'type' => $_POST['type'],
            'path' => $_POST['path'],
            'referrer_host' => $_POST['referrer_host'],
            'referrer_path' => $_POST['referrer_path'],
            'utm_source' => $_POST['utm_source'],
            'utm_medium' => $_POST['utm_medium'],
            'utm_campaign' => $_POST['utm_campaign'],
            'continent_code' => $_POST['continent_code'],
            'country_code' => $_POST['country_code'],
            'region_name' => $_POST['region_name'],
            'city_name' => $_POST['city_name'],
            'os_name' => $_POST['os_name'],
            'browser_name' => $_POST['browser_name'],
            'browser_language' => $_POST['browser_language'],
            'browser_timezone' => $_POST['browser_timezone'],
            'screen_resolution' => $_POST['screen_resolution'],
            'device_type' => $_POST['device_type'],
            'theme' => $_POST['theme'],
            'expiration_date' => $expiration_date,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $event_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $event = db()->where('event_id', $event_id)->getOne('lightweight_events');

        /* We haven't found the resource */
        if(!$event) {
            $this->return_404();
        }

        if(!$website_id = db()->where('website_id', $event->website_id)->where('user_id', $this->user->user_id)->getValue('websites', 'website_id')) {
            $this->return_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('website_id', $event->website_id)->getValue('lightweight_events', 'count(*)');

        if($this->user->plan_settings->sessions_events_limit != -1 && $total_rows > $this->user->plan_settings->sessions_events_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->sessions_events_limit, mb_strtolower(l('pageviews.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $_POST['website_id'] = isset($_POST['website_id']) ? (int) $_POST['website_id'] : $event->website_id;
        if($_POST['website_id'] != $event->website_id && !db()->where('website_id', $_POST['website_id'])->where('user_id', $this->user->user_id)->has('websites')) {
            $this->return_404();
        }

        $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['pageview', 'custom']) ? input_clean($_POST['type']) : $event->type;
        $_POST['path'] = isset($_POST['path']) ? '/' . trim(input_clean($_POST['path'], 1024), '/') : $event->path;
        $_POST['device_type'] = isset($_POST['device_type']) && in_array($_POST['device_type'], ['tablet', 'desktop', 'mobile']) ? input_clean($_POST['device_type']) : $event->device_type;
        $_POST['theme'] = isset($_POST['theme']) && in_array($_POST['theme'], ['light', 'dark']) ? input_clean($_POST['theme']) : $event->theme;
        $_POST['screen_resolution'] = isset($_POST['screen_resolution']) && preg_match('/^([1-9]\d{1,4})x([1-9]\d{1,4})$/', $_POST['screen_resolution']) ? input_clean($_POST['screen_resolution']) : $event->screen_resolution;
        $_POST['browser_timezone'] = isset($_POST['browser_timezone']) && in_array($_POST['browser_timezone'], timezone_identifiers_list()) ? input_clean($_POST['browser_timezone']) : $event->browser_timezone;
        $_POST['browser_language'] = isset($_POST['browser_language']) && preg_match('/^[a-z]{2,3}(-[A-Z]{2})?$/', $_POST['browser_language']) ? input_clean($_POST['browser_language']) : $event->browser_language;
        $_POST['browser_name'] = isset($_POST['browser_name']) ? input_clean($_POST['browser_name'], 32) : $event->browser_name;
        $_POST['os_name'] = isset($_POST['os_name']) ? input_clean($_POST['os_name'], 32) : $event->os_name;
        $_POST['region_name'] = isset($_POST['region_name']) ? input_clean($_POST['region_name'], 128) : $event->region_name;
        $_POST['city_name'] = isset($_POST['city_name']) ? input_clean($_POST['city_name'], 128) : $event->city_name;
        $_POST['country_code'] = isset($_POST['country_code']) && in_array($_POST['country_code'], array_keys(get_countries_no_emoji_array())) ? input_clean($_POST['country_code']) : $event->country_code;
        $_POST['continent_code'] = isset($_POST['continent_code']) && in_array($_POST['continent_code'], array_keys(get_continents_no_emoji_array())) ? input_clean($_POST['continent_code']) : $event->continent_code;
        $_POST['utm_source'] = isset($_POST['utm_source']) ? input_clean($_POST['utm_source'], 128) : $event->utm_source;
        $_POST['utm_medium'] = isset($_POST['utm_medium']) ? input_clean($_POST['utm_medium'], 128) : $event->utm_medium;
        $_POST['utm_campaign'] = isset($_POST['utm_campaign']) ? input_clean($_POST['utm_campaign'], 128) : $event->utm_campaign;
        $_POST['referrer_host'] = isset($_POST['referrer_host']) ? input_clean($_POST['referrer_host'], 256) : $event->referrer_host;
        $_POST['referrer_path'] = isset($_POST['referrer_path']) ? input_clean($_POST['referrer_path'], 1024) : $event->referrer_path;

        /* Database query */
        db()->where('event_id', $event_id)->where('website_id', $event->website_id)->update('lightweight_events', [
            'website_id' => $_POST['website_id'],
            'type' => $_POST['type'],
            'path' => $_POST['path'],
            'referrer_host' => $_POST['referrer_host'],
            'referrer_path' => $_POST['referrer_path'],
            'utm_source' => $_POST['utm_source'],
            'utm_medium' => $_POST['utm_medium'],
            'utm_campaign' => $_POST['utm_campaign'],
            'continent_code' => $_POST['continent_code'],
            'country_code' => $_POST['country_code'],
            'region_name' => $_POST['region_name'],
            'city_name' => $_POST['city_name'],
            'os_name' => $_POST['os_name'],
            'browser_name' => $_POST['browser_name'],
            'browser_language' => $_POST['browser_language'],
            'browser_timezone' => $_POST['browser_timezone'],
            'screen_resolution' => $_POST['screen_resolution'],
            'device_type' => $_POST['device_type'],
            'theme' => $_POST['theme'],
        ]);

        $website_ids = array_unique([(int) $event->website_id, (int) $_POST['website_id']]);
        db()->where('website_id', $website_ids, 'IN')->update('websites', [
            'pageviews_stats_last_datetime' => null,
        ]);

        /* Prepare the data */
        $data = [
            'id' => (int) $event->event_id,
            'website_id' => (int) $_POST['website_id'],
            'type' => $_POST['type'],
            'path' => $_POST['path'],
            'referrer_host' => $_POST['referrer_host'],
            'referrer_path' => $_POST['referrer_path'],
            'utm_source' => $_POST['utm_source'],
            'utm_medium' => $_POST['utm_medium'],
            'utm_campaign' => $_POST['utm_campaign'],
            'continent_code' => $_POST['continent_code'],
            'country_code' => $_POST['country_code'],
            'region_name' => $_POST['region_name'],
            'city_name' => $_POST['city_name'],
            'os_name' => $_POST['os_name'],
            'browser_name' => $_POST['browser_name'],
            'browser_language' => $_POST['browser_language'],
            'browser_timezone' => $_POST['browser_timezone'],
            'screen_resolution' => $_POST['screen_resolution'],
            'device_type' => $_POST['device_type'],
            'theme' => $_POST['theme'],
            'expiration_date' => $event->expiration_date,
            'datetime' => $event->date,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $event_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $event = db()->where('event_id', $event_id)->getOne('lightweight_events');

        /* We haven't found the resource */
        if(!$event) {
            $this->return_404();
        }

        /* Verify ownership */
        $website = \Altum\Cache::cache_function_result('website?website_id=' . $event->website_id, ['website_id=' . $event->website_id], function() use ($event) {
            return db()
                ->where('website_id', $event->website_id)
                ->where('user_id', $this->user->user_id)
                ->getOne('websites', ['website_id']);
        });

        if(!$website) {
            $this->return_404();
        }

        /* Database query */
        db()->where('event_id', $event_id)->delete('lightweight_events');
        db()->where('website_id', $event->website_id)->update('websites', [
            'pageviews_stats_last_datetime' => null,
        ]);

        http_response_code(200);
        die();

    }

}
