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

class ApiWebsites extends Controller {
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

        /* Prepare the filtering system */
        $allowed_order_by = ['website_id', 'last_datetime', 'datetime', 'name', 'host', 'current_month_sessions_events', 'last_24_hours_pageviews', 'last_7_days_pageviews'];

        $filters = (new \Altum\Filters(['is_enabled', 'tracking_type', 'domain_id'], ['name', 'host'], $allowed_order_by, allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by('website_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `websites` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/websites?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `websites`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->website_id,
                'user_id' => (int) $row->user_id,
                'pixel_key' => $row->pixel_key,
                'name' => $row->name,
                'scheme' => $row->scheme,
                'host' => $row->host,
                'path' => $row->path,
                'tracking_type' => $row->tracking_type,
                'excluded_ips' => $row->excluded_ips,
                'outbound_clicks_is_enabled' => (bool) $row->outbound_clicks_is_enabled,
                'events_children_is_enabled' => (bool) $row->events_children_is_enabled,
                'sessions_replays_is_enabled' => (bool) $row->sessions_replays_is_enabled,
                'sessions_replays_hide_text_selector' => $row->sessions_replays_hide_text_selector,
                'email_reports_is_enabled' => (bool) $row->email_reports_is_enabled,
                'email_reports_last_date' => $row->email_reports_last_date,
                'bot_exclusion_is_enabled' => (bool) $row->bot_exclusion_is_enabled,
                'query_parameters_tracking_is_enabled' => (bool) $row->query_parameters_tracking_is_enabled,
                'ip_storage_is_enabled' => (bool) $row->ip_storage_is_enabled,
                'public_statistics_is_enabled' => (bool) $row->public_statistics_is_enabled,
                'public_statistics_password' => !empty($row->public_statistics_password),
                'current_month_sessions_events' => (int) $row->current_month_sessions_events,
                'last_24_hours_pageviews' => (int) $row->last_24_hours_pageviews,
                'last_7_days_pageviews' => (int) $row->last_7_days_pageviews,
                'pageviews_stats_last_datetime' => $row->pageviews_stats_last_datetime,
                'current_month_sessions_replays' => (int) $row->current_month_sessions_replays,
                'is_enabled' => (bool) $row->is_enabled,
                'last_datetime' => $row->last_datetime,
                'datetime' => $row->datetime,
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

        $website_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $website = db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->getOne('websites');

        /* We haven't found the resource */
        if(!$website) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $website->website_id,
            'user_id' => (int) $website->user_id,
            'pixel_key' => $website->pixel_key,
            'name' => $website->name,
            'scheme' => $website->scheme,
            'host' => $website->host,
            'path' => $website->path,
            'tracking_type' => $website->tracking_type,
            'excluded_ips' => $website->excluded_ips,
            'outbound_clicks_is_enabled' => (bool) $website->outbound_clicks_is_enabled,
            'events_children_is_enabled' => (bool) $website->events_children_is_enabled,
            'sessions_replays_is_enabled' => (bool) $website->sessions_replays_is_enabled,
            'sessions_replays_hide_text_selector' => $website->sessions_replays_hide_text_selector,
            'email_reports_is_enabled' => (bool) $website->email_reports_is_enabled,
            'email_reports_last_date' => $website->email_reports_last_date,
            'bot_exclusion_is_enabled' => (bool) $website->bot_exclusion_is_enabled,
            'query_parameters_tracking_is_enabled' => (bool) $website->query_parameters_tracking_is_enabled,
            'ip_storage_is_enabled' => (bool) $website->ip_storage_is_enabled,
            'public_statistics_is_enabled' => (bool) $website->public_statistics_is_enabled,
            'public_statistics_password' => !empty($website->public_statistics_password),
            'current_month_sessions_events' => (int) $website->current_month_sessions_events,
            'last_24_hours_pageviews' => (int) $website->last_24_hours_pageviews,
            'last_7_days_pageviews' => (int) $website->last_7_days_pageviews,
            'pageviews_stats_last_datetime' => $website->pageviews_stats_last_datetime,
            'current_month_sessions_replays' => (int) $website->current_month_sessions_replays,
            'is_enabled' => (bool) $website->is_enabled,
            'last_datetime' => $website->last_datetime,
            'datetime' => $website->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('websites', 'count(`website_id`)');

        if($this->user->plan_settings->websites_limit != -1 && $total_rows >= $this->user->plan_settings->websites_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Check for any errors */
        $required_fields = ['name', 'host'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['name'] = input_clean($_POST['name'], 256);
        $_POST['scheme'] = isset($_POST['scheme']) && in_array($_POST['scheme'], ['https://', 'http://']) ? $_POST['scheme'] : 'https://';
        $_POST['host'] = str_replace(' ', '', mb_strtolower(input_clean($_POST['host'], 128)));
        $_POST['host'] = string_starts_with('http://', $_POST['host']) || string_starts_with('https://', $_POST['host']) ? parse_url($_POST['host'], PHP_URL_HOST) : $_POST['host'];
        $_POST['tracking_type'] = isset($_POST['tracking_type']) && in_array($_POST['tracking_type'], ['lightweight', 'advanced']) ? query_clean($_POST['tracking_type']) : 'lightweight';
        $_POST['outbound_clicks_is_enabled'] = (int) isset($_POST['outbound_clicks_is_enabled']);
        $_POST['events_children_is_enabled'] = (int) isset($_POST['events_children_is_enabled']);
        $_POST['sessions_replays_is_enabled'] = (int) isset($_POST['sessions_replays_is_enabled']);
        $_POST['email_reports_is_enabled'] = (int) isset($_POST['email_reports_is_enabled']);
        $_POST['bot_exclusion_is_enabled'] = (int) isset($_POST['bot_exclusion_is_enabled']);
        $_POST['query_parameters_tracking_is_enabled'] = (int) isset($_POST['query_parameters_tracking_is_enabled']);
        $_POST['ip_storage_is_enabled'] = (int) isset($_POST['ip_storage_is_enabled']);
        $_POST['pixel_key'] = isset($_POST['pixel_key']) ? mb_substr(get_slug($_POST['pixel_key']), 0, 16) : '';

        /* Check the pixel key */
        if($_POST['pixel_key'] && db()->where('pixel_key', $_POST['pixel_key'])->has('websites')) {
            $this->response_error(l('websites.error_message.pixel_key_exists'), 401);
        }

        $_POST['public_statistics_is_enabled'] = (int) isset($_POST['public_statistics_is_enabled']);
        $_POST['public_statistics_password'] = mb_substr($_POST['public_statistics_password'] ?? '', 0, 64);
        $_POST['public_statistics_password'] = !empty($_POST['public_statistics_password']) ? password_hash($_POST['public_statistics_password'], PASSWORD_DEFAULT) : null;

        $_POST['excluded_ips'] = implode(',', array_map(function($value) {
            return query_clean(trim($value));
        }, explode(',', $_POST['excluded_ips'] ?? null)));
        $is_enabled = 1;

        /* Domain checking */
        $path = null;
        if(function_exists('idn_to_utf8')) {
            $path = parse_url($_POST['scheme'] . idn_to_utf8($_POST['host']), PHP_URL_PATH);
            $_POST['host'] = parse_url($_POST['scheme'] . idn_to_utf8($_POST['host']), PHP_URL_HOST);
        }

        if(function_exists('idn_to_ascii')) {
            $_POST['host'] = idn_to_ascii($_POST['host']);
        }

        /* Check for blacklisted domain */
        if(in_array($_POST['host'], settings()->analytics->blacklisted_domains)) {
            $this->response_error(l('websites.error_message.blacklisted_domain'));
        }

        /* Generate a pixel key if needed */
        $pixel_key = $_POST['pixel_key'];
        if(!$pixel_key) {
            $pixel_key = string_generate(16);
            while(db()->where('pixel_key', $pixel_key)->getOne('websites', ['pixel_key'])) {
                $pixel_key = string_generate(16);
            }
        }

		/* Get available custom domains */
		$domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

		$_POST['domain_id'] = isset($_POST['domain_id']) ? (isset($domains[$_POST['domain_id']]) ? (int) $_POST['domain_id'] : null) : null;

		if(!$_POST['domain_id'] && !settings()->links->main_domain_is_enabled && $this->user->type != 1) {
			$this->response_error(l('global.info_message.plan_feature_limit'), 401);
		}

        /* Database query */
        $website_id = db()->insert('websites', [
			'user_id' => $this->user->user_id,
			'domain_id' => $_POST['domain_id'],
            'pixel_key' => $pixel_key,
            'name' => $_POST['name'],
            'scheme' => $_POST['scheme'],
            'host' => $_POST['host'],
            'path' => $path,
            'excluded_ips' => $_POST['excluded_ips'],
            'tracking_type' => $_POST['tracking_type'],
            'outbound_clicks_is_enabled' => $_POST['outbound_clicks_is_enabled'],
            'events_children_is_enabled' => $_POST['events_children_is_enabled'],
            'sessions_replays_is_enabled' => $_POST['sessions_replays_is_enabled'],
            'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
            'bot_exclusion_is_enabled' => $_POST['bot_exclusion_is_enabled'],
            'query_parameters_tracking_is_enabled' => $_POST['query_parameters_tracking_is_enabled'],
            'ip_storage_is_enabled' => $_POST['ip_storage_is_enabled'],
            'public_statistics_is_enabled' => $_POST['public_statistics_is_enabled'],
            'public_statistics_password' => $_POST['public_statistics_password'],
            'is_enabled' => $is_enabled,
            'email_reports_last_date' => get_date(),
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('websites_' . $this->user->user_id);
        cache()->deleteItemsByTag('website_id=' . $website_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $website_id,
            'user_id' => (int) $this->user->user_id,
            'pixel_key' => $pixel_key,
            'name' => $_POST['name'],
            'scheme' => $_POST['scheme'],
            'host' => $_POST['host'],
            'path' => $path,
            'tracking_type' => $_POST['tracking_type'],
            'excluded_ips' => $_POST['excluded_ips'],
            'outbound_clicks_is_enabled' => (bool) $_POST['outbound_clicks_is_enabled'],
            'events_children_is_enabled' => (bool) $_POST['events_children_is_enabled'],
            'sessions_replays_is_enabled' => (bool) $_POST['sessions_replays_is_enabled'],
            'sessions_replays_hide_text_selector' => null,
            'email_reports_is_enabled' => (bool) $_POST['email_reports_is_enabled'],
            'email_reports_last_date' => get_date(),
            'bot_exclusion_is_enabled' => (bool) $_POST['bot_exclusion_is_enabled'],
            'query_parameters_tracking_is_enabled' => (bool) $_POST['query_parameters_tracking_is_enabled'],
            'ip_storage_is_enabled' => (bool) $_POST['ip_storage_is_enabled'],
            'public_statistics_is_enabled' => (bool) $_POST['public_statistics_is_enabled'],
            'public_statistics_password' => !empty($_POST['public_statistics_password']),
            'current_month_sessions_events' => 0,
            'last_24_hours_pageviews' => 0,
            'last_7_days_pageviews' => 0,
            'pageviews_stats_last_datetime' => null,
            'current_month_sessions_replays' => 0,
            'is_enabled' => (bool) $is_enabled,
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('websites', 'count(`website_id`)');

        if($this->user->plan_settings->websites_limit != -1 && $total_rows > $this->user->plan_settings->websites_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->websites_limit, mb_strtolower(l('websites.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $website_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $website = db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->getOne('websites');

        /* We haven't found the resource */
        if(!$website) {
            $this->return_404();
        }

        $_POST['name'] = trim($_POST['name'] ?? $website->name);
        $_POST['scheme'] = isset($_POST['scheme']) && in_array($_POST['scheme'], ['https://', 'http://']) ? $_POST['scheme'] : $website->scheme;
        $_POST['host'] = mb_strtolower(trim($_POST['host'] ?? $website->host));
        $_POST['pixel_key'] = isset($_POST['pixel_key']) ? mb_substr(get_slug($_POST['pixel_key']), 0, 16) : $website->pixel_key;
        $_POST['outbound_clicks_is_enabled'] = isset($_POST['outbound_clicks_is_enabled']) ? (int) $_POST['outbound_clicks_is_enabled'] : $website->outbound_clicks_is_enabled;
        $_POST['events_children_is_enabled'] = isset($_POST['events_children_is_enabled']) ? (int) $_POST['events_children_is_enabled'] : $website->events_children_is_enabled;
        $_POST['sessions_replays_is_enabled'] = isset($_POST['sessions_replays_is_enabled']) ? (int) $_POST['sessions_replays_is_enabled'] : $website->sessions_replays_is_enabled;
        $_POST['sessions_replays_hide_text_selector'] = isset($_POST['sessions_replays_hide_text_selector']) ? input_clean($_POST['sessions_replays_hide_text_selector'], 1024) : $website->sessions_replays_hide_text_selector;
        $_POST['email_reports_is_enabled'] = isset($_POST['email_reports_is_enabled']) ? (int) $_POST['email_reports_is_enabled'] : $website->email_reports_is_enabled;
        $_POST['is_enabled'] = isset($_POST['is_enabled']) ? (int) $_POST['is_enabled'] : $website->is_enabled;
        $_POST['bot_exclusion_is_enabled'] = isset($_POST['bot_exclusion_is_enabled']) ? (int) $_POST['bot_exclusion_is_enabled'] : $website->bot_exclusion_is_enabled;
        $_POST['query_parameters_tracking_is_enabled'] = isset($_POST['query_parameters_tracking_is_enabled']) ? (int) $_POST['query_parameters_tracking_is_enabled'] : $website->query_parameters_tracking_is_enabled;
        $_POST['ip_storage_is_enabled'] = isset($_POST['ip_storage_is_enabled']) ? (int) $_POST['ip_storage_is_enabled'] : $website->ip_storage_is_enabled;

        /* Check the pixel key */
        if($website->pixel_key != $_POST['pixel_key'] && db()->where('website_id', $website->website_id, '<>')->where('pixel_key', $_POST['pixel_key'])->has('websites')) {
            $this->response_error(l('websites.error_message.pixel_key_exists'), 401);
        }

        $_POST['public_statistics_is_enabled'] = isset($_POST['public_statistics_is_enabled']) ? (int) $_POST['public_statistics_is_enabled'] : $website->public_statistics_is_enabled;
        $_POST['public_statistics_password'] = mb_substr($_POST['public_statistics_password'] ?? '', 0, 64);
        $_POST['public_statistics_password'] = !empty($_POST['public_statistics_password']) ?
            ($_POST['public_statistics_password'] != $website->public_statistics_password ? password_hash($_POST['public_statistics_password'], PASSWORD_DEFAULT) : $website->public_statistics_password)
            : null;

        $_POST['excluded_ips'] = implode(',', array_map(function($value) {
            return query_clean(trim($value));
        }, explode(',', $_POST['excluded_ips'] ?? $website->excluded_ips)));

        /* Domain checking */
        $path = $website->path;

        if($_POST['host'] != $website->host) {
            if(function_exists('idn_to_utf8')) {
                $path = parse_url($_POST['scheme'] . idn_to_utf8($_POST['host']), PHP_URL_PATH);
                $_POST['host'] = parse_url($_POST['scheme'] . idn_to_utf8($_POST['host']), PHP_URL_HOST);
            }

            if(function_exists('idn_to_ascii')) {
                $_POST['host'] = idn_to_ascii($_POST['host']);
            }
        }

        /* Check for blacklisted domain */
        if(in_array($_POST['host'], settings()->analytics->blacklisted_domains)) {
            $this->response_error(l('websites.error_message.blacklisted_domain'));
        }

		/* Get available custom domains */
		$domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

		if(isset($_POST['domain_id']) && $_POST['domain_id'] == 0 && !settings()->links->main_domain_is_enabled && $this->user->type != 1) {
			$this->response_error(l('create_link_modal.error_message.main_domain_is_disabled'), 401);
		}

		$_POST['domain_id'] = isset($_POST['domain_id']) ? (isset($domains[$_POST['domain_id']]) ? (int) $_POST['domain_id'] : null) : $website->domain_id;

        /* Database query */
        db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->update('websites', [
			'domain_id' => $_POST['domain_id'],
            'pixel_key' => $_POST['pixel_key'],
			'name' => $_POST['name'],
            'scheme' => $_POST['scheme'],
            'host' => $_POST['host'],
            'path' => $path,
            'excluded_ips' => $_POST['excluded_ips'],
            'outbound_clicks_is_enabled' => $_POST['outbound_clicks_is_enabled'],
            'events_children_is_enabled' => $_POST['events_children_is_enabled'],
            'sessions_replays_is_enabled' => $_POST['sessions_replays_is_enabled'],
            'sessions_replays_hide_text_selector' => $_POST['sessions_replays_hide_text_selector'],
            'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
            'bot_exclusion_is_enabled' => $_POST['bot_exclusion_is_enabled'],
            'query_parameters_tracking_is_enabled' => $_POST['query_parameters_tracking_is_enabled'],
            'ip_storage_is_enabled' => $_POST['ip_storage_is_enabled'],
            'public_statistics_is_enabled' => $_POST['public_statistics_is_enabled'],
            'public_statistics_password' => $_POST['public_statistics_password'],
            'is_enabled' => $_POST['is_enabled'],
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('websites_' . $this->user->user_id);
        cache()->deleteItemsByTag('website_id=' . $website_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $website->website_id,
            'user_id' => (int) $website->user_id,
            'pixel_key' => $_POST['pixel_key'],
            'name' => $_POST['name'],
            'scheme' => $_POST['scheme'],
            'host' => $_POST['host'],
            'path' => $path,
            'tracking_type' => $website->tracking_type,
            'excluded_ips' => $_POST['excluded_ips'],
            'outbound_clicks_is_enabled' => (bool) $_POST['outbound_clicks_is_enabled'],
            'events_children_is_enabled' => (bool) $_POST['events_children_is_enabled'],
            'sessions_replays_is_enabled' => (bool) $_POST['sessions_replays_is_enabled'],
            'sessions_replays_hide_text_selector' => $_POST['sessions_replays_hide_text_selector'],
            'email_reports_is_enabled' => (bool) $_POST['email_reports_is_enabled'],
            'email_reports_last_date' => $website->email_reports_last_date,
            'bot_exclusion_is_enabled' => (bool) $_POST['bot_exclusion_is_enabled'],
            'query_parameters_tracking_is_enabled' => (bool) $_POST['query_parameters_tracking_is_enabled'],
            'ip_storage_is_enabled' => (bool) $_POST['ip_storage_is_enabled'],
            'public_statistics_is_enabled' => (bool) $_POST['public_statistics_is_enabled'],
            'public_statistics_password' => !empty($_POST['public_statistics_password']),
            'current_month_sessions_events' => (int) $website->current_month_sessions_events,
            'last_24_hours_pageviews' => (int) $website->last_24_hours_pageviews,
            'last_7_days_pageviews' => (int) $website->last_7_days_pageviews,
            'pageviews_stats_last_datetime' => $website->pageviews_stats_last_datetime,
            'current_month_sessions_replays' => (int) $website->current_month_sessions_replays,
            'is_enabled' => (bool) $_POST['is_enabled'],
            'last_datetime' => get_date(),
            'datetime' => $website->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $website_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $website = db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->getOne('websites');

        /* We haven't found the resource */
        if(!$website) {
            $this->return_404();
        }

        /* Database query */
        db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->delete('websites');

        /* Clear the cache */
        \Altum\Cache::store_initialize();
        cache('store_adapter')->deleteItemsByTag('session_replay_website_' . $website_id);
        cache()->deleteItem('websites_' . $this->user->user_id);
        cache()->deleteItemsByTag('website_id=' . $website_id);

        http_response_code(200);
        die();

    }

}
