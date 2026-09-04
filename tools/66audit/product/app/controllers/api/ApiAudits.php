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
use Altum\Response;
use Altum\Traits\Apiable;
use vipnytt\SitemapParser;

defined('ALTUMCODE') || die();

class ApiAudits extends Controller {
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

            case 'DELETE':
                $this->delete();
                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['user_id', 'website_id', 'domain_id',], ['url', 'host', 'title'], ['audit_id', 'last_datetime', 'datetime', 'last_refresh_datetime', 'next_refresh_datetime', 'url', 'host', 'score', 'total_issues', 'title'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_refresh_datetime', 'next_refresh_datetime']));
        $filters->set_default_order_by($this->user->preferences->audits_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `audits` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/audits?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `audits`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");


        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->audit_id,
                'website_id' => (int) $row->website_id,
                'domain_id' => (int) $row->domain_id,
                'user_id' => (int) $row->user_id,
                'uploader_id' => $row->uploader_id,
                'host' => $row->host,
                'url' => $row->url,
                'resolved_url' => $row->resolved_url,
                'ttfb' => (float) $row->ttfb,
                'response_time' => (float) $row->response_time,
                'average_download_speed' => (float) $row->average_download_speed,
                'page_size' => (float) $row->page_size,
                'http_requests' => (int) $row->http_requests,
                'is_https' => (bool) $row->is_https,
                'is_ssl_valid' => (bool) $row->is_ssl_valid,
                'http_protocol' => (int) $row->http_protocol,
                'title' => $row->title,
                'meta_description' => $row->meta_description,
                'meta_keywords' => $row->meta_keywords,
                'data' => json_decode($row->data),
                'issues' => json_decode($row->issues),
                'settings' => json_decode($row->settings),
                'notifications' => json_decode($row->notifications),
                'score' => (int) $row->score,
                'total_tests' => (int) $row->total_tests,
                'passed_tests' => (int) $row->passed_tests,
                'total_issues' => (int) $row->total_issues,
                'major_issues' => (int) $row->major_issues,
                'moderate_issues' => (int) $row->moderate_issues,
                'minor_issues' => (int) $row->minor_issues,
                'total_refreshes' => (int) $row->total_refreshes,
                'is_queued' => (bool) $row->is_queued,
                'refresh_error' => $row->refresh_error,
                'is_external_redirected' => (bool) $row->is_external_redirected,
                'total_redirects' => (int) $row->total_redirects,
                'next_refresh_datetime' => $row->next_refresh_datetime,
                'last_refresh_datetime' => $row->last_refresh_datetime,
                'expiration_datetime' => $row->expiration_datetime,
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

        $audit_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $audit = db()->where('audit_id', $audit_id)->where('user_id', $this->user->user_id)->getOne('audits');

        /* We haven't found the resource */
        if(!$audit) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $audit->audit_id,
            'website_id' => (int) $audit->website_id,
            'domain_id' => (int) $audit->domain_id,
            'user_id' => (int) $audit->user_id,
            'uploader_id' => $audit->uploader_id,
            'host' => $audit->host,
            'url' => $audit->url,
            'resolved_url' => $audit->resolved_url,
            'ttfb' => (float) $audit->ttfb,
            'response_time' => (float) $audit->response_time,
            'average_download_speed' => (float) $audit->average_download_speed,
            'page_size' => (float) $audit->page_size,
            'http_requests' => (int) $audit->http_requests,
            'is_https' => (bool) $audit->is_https,
            'is_ssl_valid' => (bool) $audit->is_ssl_valid,
            'http_protocol' => (int) $audit->http_protocol,
            'title' => $audit->title,
            'meta_description' => $audit->meta_description,
            'meta_keywords' => $audit->meta_keywords,
            'data' => json_decode($audit->data),
            'issues' => json_decode($audit->issues),
            'settings' => json_decode($audit->settings),
            'notifications' => json_decode($audit->notifications),
            'score' => (int) $audit->score,
            'total_tests' => (int) $audit->total_tests,
            'passed_tests' => (int) $audit->passed_tests,
            'total_issues' => (int) $audit->total_issues,
            'major_issues' => (int) $audit->major_issues,
            'moderate_issues' => (int) $audit->moderate_issues,
            'minor_issues' => (int) $audit->minor_issues,
            'total_refreshes' => (int) $audit->total_refreshes,
            'is_queued' => (bool) $audit->is_queued,
            'refresh_error' => $audit->refresh_error,
            'is_external_redirected' => (bool) $audit->is_external_redirected,
            'total_redirects' => (int) $audit->total_redirects,
            'next_refresh_datetime' => $audit->next_refresh_datetime,
            'last_refresh_datetime' => $audit->last_refresh_datetime,
            'expiration_datetime' => $audit->expiration_datetime,
            'last_datetime' => $audit->last_datetime,
            'datetime' => $audit->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        $_POST['url'] = get_url($_POST['url'] ?? '', 256, false);
        $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['single', 'sitemap', 'bulk', 'html']) ? input_clean($_POST['type']) : 'single';
        $is_public = (int) isset($_POST['is_public']);
        $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        $_POST['audit_check_interval'] = isset($_POST['audit_check_interval']) && in_array($_POST['audit_check_interval'], $this->user->plan_settings->audits_check_intervals ?? []) ? (int) $_POST['audit_check_interval'] : null;

        /* Check for any errors */
        switch($_POST['type']) {
            case 'single':
            case 'sitemap':
                $required_fields = ['url'];
                break;

            case 'bulk':
                $required_fields = ['urls'];
                break;

            case 'html':
                $required_fields = ['url', 'html'];
                break;
        }

        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Check for the plan limit */
        $total_websites = db()->where('user_id', $this->user->user_id)->getValue('websites', 'count(`website_id`)');
        $audits_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`audit_audits_current_month`');
        if($this->user->plan_settings->audits_per_month_limit != -1 && $audits_current_month >= $this->user->plan_settings->audits_per_month_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Data array of processed URLs */
        $data_array = [];

        /* Sitemap processing */
        if($_POST['type'] == 'sitemap') {
            /* Get URL data */
            $parsed_url = parse_url($_POST['url']);

            if(in_array(get_domain_from_url($_POST['url']), settings()->audits->blacklisted_domains)) {
                $this->response_error(l('audits.error_message.blacklisted_domain'));
            }

            /* Check for the plan limit */
            if(is_logged_in() && $this->user->plan_settings->websites_limit != -1 && $total_websites >= $this->user->plan_settings->websites_limit) {
                $website_exists = db()->where('user_id', $this->user->user_id)->where('host', $parsed_url['host'])->getOne('websites');

                /* If its a new website and limit is reached, trigger alert */
                if(!$website_exists) {
                    $this->response_error(l('global.info_message.plan_feature_limit'), 401);
                }
            }

            try {
                $parser = new SitemapParser(settings()->audits->user_agent);
                $parser->parseRecursive($_POST['url']);

                $urls = array_keys($parser->getURLs());
            } catch (SitemapParser\Exceptions\SitemapParserException $exception) {
                $this->response_error($exception->getMessage());
            }

            $data_not_found = \Altum\Helpers\Audit::process_not_found($parsed_url);
            $data_robots = \Altum\Helpers\Audit::process_robots($parsed_url);

            if($this->user->plan_settings->audits_bulk_limit != -1) {
                $urls = array_slice($urls, 0, $this->user->plan_settings->audits_bulk_limit);
            }

            /* Sitemap processing */
            $audits_created = 0;

            /* Go through each sitemap URLs */
            foreach($urls as $url) {
                $url = get_url($url, 256, false);

                if(empty($url)) continue;
                if($parsed_url['host'] != parse_url($url, PHP_URL_HOST)) continue;
                if($this->user->plan_settings->audits_per_month_limit != -1 && ($audits_current_month + $audits_created) >= $this->user->plan_settings->audits_per_month_limit) break;

                if(count($data_array) >= (settings()->audits->bulk_processing_before_queue ?? 3)){
                    $data_array[] = [
                        'url' => $url,
                        'parsed_url' => parse_url($url),
                        'queued' => 1,
                    ];
                } else {
                    /* Send the main request */
                    try {
                        $response = \Altum\Helpers\Audit::process_request($url);
                    } catch (\Exception $exception) {
                        /* Alerts::add_field_error('url', $exception->getMessage()); */
                        continue;
                    }

                    /* Single URL processing */
                    $data = \Altum\Helpers\Audit::process_request_response($url, $response);

                    /* Merge data */
                    $data_array[] = $data + $data_not_found + $data_robots;

                    /* Delay between requests */
                    usleep(rand(500000, 1000000));
                }

                $audits_created++;
            }
        }

        /* Bulk processing */
        else if($_POST['type'] == 'bulk') {
            $urls = preg_split('/\r\n|\r|\n/', $_POST['urls'] ?? '');
            $urls = array_filter(array_unique($urls));

            if($this->user->plan_settings->audits_bulk_limit != -1) {
                $urls = array_slice($urls, 0, $this->user->plan_settings->audits_bulk_limit);
            }

            /* Sitemap processing */
            $audits_created = 0;

            /* Check for the plan limit */
            if(is_logged_in() && $this->user->plan_settings->websites_limit != -1 && $total_websites >= $this->user->plan_settings->websites_limit) {
                /* Go through each sitemap URLs */
                foreach($urls as $url) {
                    /* Get URL data */
                    $parsed_url = parse_url($url);

                    /* Check if its a new entry */
                    $website_exists = db()->where('user_id', $this->user->user_id)->where('host', $parsed_url['host'])->getOne('websites');

                    /* If its a new website and limit is reached, trigger alert */
                    if(!$website_exists) {
                        $this->response_error(l('global.info_message.plan_feature_limit'), 401);
                    }

                    if(in_array(get_domain_from_url($url), settings()->audits->blacklisted_domains)) {
                        $this->response_error(l('audits.error_message.blacklisted_domain'));
                    }
                }
            }


            /* Go through each sitemap URLs */
            foreach($urls as $url) {
                $url = get_url($url, 256, false);

                if(empty($url)) continue;
                if($this->user->plan_settings->audits_per_month_limit != -1 && ($audits_current_month + $audits_created) >= $this->user->plan_settings->audits_per_month_limit) break;

                if(count($data_array) >= (settings()->audits->bulk_processing_before_queue ?? 3)){
                    $data_array[] = [
                        'url' => $url,
                        'parsed_url' => parse_url($url),
                        'queued' => 1,
                    ];
                } else {
                    /* Send the main request */
                    try {
                        $response = \Altum\Helpers\Audit::process_request($url);
                    } catch (\Exception $exception) {
                        /* Alerts::add_field_error('url', $exception->getMessage()); */
                        continue;
                    }

                    /* Single URL processing */
                    $data = \Altum\Helpers\Audit::process_request_response($url, $response);

                    $data_not_found = [];
                    $data_robots = [];

                    if(!$data['is_external_redirected']) {
                        $data_not_found = \Altum\Helpers\Audit::process_not_found($data['parsed_url']);
                        $data_robots = \Altum\Helpers\Audit::process_robots($data['parsed_url']);
                    }

                    /* Merge data */
                    $data_array[] = $data + $data_not_found + $data_robots;

                    /* Delay between requests */
                    usleep(rand(500000, 1000000));
                }

                $audits_created++;
            }
        }

        /* HTML processing */
        else if($_POST['type'] == 'html') {
            $_POST['html'] = trim($_POST['html'] ?? '');

            /* Get URL data */
            $parsed_url = parse_url($_POST['url']);
            $parsed_url['host'] = str_replace('www.', '', $parsed_url['host']);

            if(in_array(get_domain_from_url($_POST['url']), settings()->audits->blacklisted_domains)) {
                $this->response_error(l('audits.error_message.blacklisted_domain'));
            }

            /* Check for the plan limit */
            if(is_logged_in() && $this->user->plan_settings->websites_limit != -1 && $total_websites >= $this->user->plan_settings->websites_limit) {
                $website_exists = db()->where('user_id', $this->user->user_id)->where('host', $parsed_url['host'])->getOne('websites');

                /* If its a new website and limit is reached, trigger alert */
                if(!$website_exists) {
                    $this->response_error(l('global.info_message.plan_feature_limit'), 401);
                }
            }

            /* Send the main request */
            try {
                $response = \Altum\Helpers\Audit::process_request($_POST['url']);
            } catch(\Exception $exception) {
                \Altum\Response::jsonapi_error([[
                    'title' => $exception->getMessage(),
                    'status' => '401'
                ]]);
            }

            /* Single URL processing */
            $data = \Altum\Helpers\Audit::process_request_response($_POST['url'], $response, $_POST['html']);

            $data_not_found = [];
            $data_robots = [];

            if(!$data['is_external_redirected']) {
                $data_not_found = \Altum\Helpers\Audit::process_not_found($data['parsed_url']);
                $data_robots = \Altum\Helpers\Audit::process_robots($data['parsed_url']);
            }

            /* Merge data */
            $data_array[] = $data + $data_not_found + $data_robots;

            $audits_created = 1;
        }

        /* Single URL processing */
        else {
            /* Get URL data */
            $parsed_url = parse_url($_POST['url']);
            $parsed_url['host'] = str_replace('www.', '', $parsed_url['host']);

            if(in_array(get_domain_from_url($_POST['url']), settings()->audits->blacklisted_domains)) {
                $this->response_error(l('audits.error_message.blacklisted_domain'));
            }

            /* Check for the plan limit */
            if(is_logged_in() && $this->user->plan_settings->websites_limit != -1 && $total_websites >= $this->user->plan_settings->websites_limit) {
                $website_exists = db()->where('user_id', $this->user->user_id)->where('host', $parsed_url['host'])->getOne('websites');

                /* If its a new website and limit is reached, trigger alert */
                if(!$website_exists) {
                    $this->response_error(l('global.info_message.plan_feature_limit'), 401);
                }
            }

            /* Send the main request */
            try {
                $response = \Altum\Helpers\Audit::process_request($_POST['url']);
            } catch(\Exception $exception) {
                \Altum\Response::jsonapi_error([[
                    'title' => $exception->getMessage(),
                    'status' => '401'
                ]]);
            }

            /* Single URL processing */
            $data = \Altum\Helpers\Audit::process_request_response($_POST['url'], $response);

            $data_not_found = [];
            $data_robots = [];

            if(!$data['is_external_redirected']) {
                $data_not_found = \Altum\Helpers\Audit::process_not_found($data['parsed_url']);
                $data_robots = \Altum\Helpers\Audit::process_robots($data['parsed_url']);
            }

            /* Merge data */
            $data_array[] = $data + $data_not_found + $data_robots;

            $audits_created = 1;
        }

        /* Get available custom domains */
        $domain_id = null;
        if(isset($_POST['domain_id'])) {
            $domain = (new \Altum\Models\Domain())->get_domain_by_domain_id($_POST['domain_id']);

            if($domain && $domain->user_id == $this->user->user_id) {
                $domain_id = $domain->domain_id;
            }
        }

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Notification handlers */
        $_POST['notifications'] = array_map(
            'intval',
            array_filter($_POST['notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );

        /* Database query */
        db()->where('user_id', $this->user->user_id)->update('users', [
            'audit_audits_current_month' => db()->inc($audits_created)
        ]);

        /* Settings */
        $settings = [
            'is_public' => $is_public,
            'password' => $password,
            'audit_check_interval' => $_POST['audit_check_interval'],
        ];

        $settings_json = json_encode($settings);
        $notifications_json = json_encode($_POST['notifications']);

        /* Prepare expiration date */
        $expiration_datetime = (new \DateTime())->modify('+' . ($this->user->plan_settings->audits_retention ?? 90) . ' days')->format('Y-m-d H:i:s');

        /* Next refresh date */
        $next_refresh_datetime = $_POST['audit_check_interval'] ? (new \DateTime())->modify('+' . $_POST['audit_check_interval'] . ' seconds')->format('Y-m-d H:i:s') : null;

        /* Cache the tested websites */
        $websites_cache = [];
        $response_data = [];

        /* Database query */
        foreach($data_array as $data) {
            $data = convert_array_to_utf8($data);

            /* Parse URL */
            $parsed_url = parse_url($data['url']);

            /* Website processing */
            $website_id = $websites_cache[$parsed_url['host']] ?? null;
            $parsed_url['host'] = str_replace('www.', '', $parsed_url['host']);
            $url_hash = md5($parsed_url['scheme'] . $parsed_url['host'] . $parsed_url['path']);
            $current_datetime = get_date();

            if(!$website_id) {
                /* Check to see if website is already added or not */
                $website_id = db()->where('user_id', $this->user->user_id)->where('host', $parsed_url['host'])->getValue('websites', 'website_id');
            }

            if(!$website_id) {
                /* Database query */
                $website_id = db()->insert('websites', [
                    'user_id' => $this->user->user_id,
                    'domain_id' => $domain_id,
                    'scheme' => $parsed_url['scheme'],
                    'host' => $parsed_url['host'],
                    'settings' => $settings_json,
                    'notifications' => $notifications_json,
                    'datetime' => $current_datetime,
                ]);

                $websites_cache[$parsed_url['host']] = $website_id;
            } else {
                $websites_cache[$parsed_url['host']] = $website_id;

				db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->update('websites', [
                    'scheme' => $parsed_url['scheme'],
                    'host' => $parsed_url['host'],
                    'settings' => $settings_json,
                    'last_audit_datetime' => $current_datetime,
                ]);
            }

            /* Prepare full data to be inserted */
            $data_json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            /* Pending / Processing */
            $should_be_queued = $data['queued'] ?? false;

            if($should_be_queued) {
                /* Check to see if URL audit exists already for this user */
                $audit = db()->where('user_id', $this->user->user_id)->where('url_hash', $url_hash)->getOne('audits');

                if($audit) {
                    /* Mark existing audit to be refreshed */
                    db()->where('audit_id', $audit->audit_id)->update('audits', [
                        'is_queued' => 1,
                        'refresh_error' => '',
                        'next_refresh_datetime' => $next_refresh_datetime,
                    ]);

                    $response_data[] = [
                        'id' => (int) $audit->audit_id,
                        'website_id' => (int) $audit->website_id,
                        'domain_id' => (int) $audit->domain_id,
                        'user_id' => (int) $audit->user_id,
                        'uploader_id' => $audit->uploader_id,
                        'host' => $audit->host,
                        'url' => $audit->url,
                        'resolved_url' => $audit->resolved_url,
                        'ttfb' => (float) $audit->ttfb,
                        'response_time' => (float) $audit->response_time,
                        'average_download_speed' => (float) $audit->average_download_speed,
                        'page_size' => (float) $audit->page_size,
                        'http_requests' => (int) $audit->http_requests,
                        'is_https' => (bool) $audit->is_https,
                        'is_ssl_valid' => (bool) $audit->is_ssl_valid,
                        'http_protocol' => (int) $audit->http_protocol,
                        'title' => $audit->title,
                        'meta_description' => $audit->meta_description,
                        'meta_keywords' => $audit->meta_keywords,
                        'data' => json_decode($audit->data),
                        'issues' => json_decode($audit->issues),
                        'settings' => json_decode($audit->settings),
                        'notifications' => json_decode($audit->notifications),
                        'score' => (int) $audit->score,
                        'total_tests' => (int) $audit->total_tests,
                        'passed_tests' => (int) $audit->passed_tests,
                        'total_issues' => (int) $audit->total_issues,
                        'major_issues' => (int) $audit->major_issues,
                        'moderate_issues' => (int) $audit->moderate_issues,
                        'minor_issues' => (int) $audit->minor_issues,
                        'total_refreshes' => (int) $audit->total_refreshes,
                        'is_queued' => true,
                        'refresh_error' => '',
                        'is_external_redirected' => (bool) $audit->is_external_redirected,
                        'total_redirects' => (int) $audit->total_redirects,
                        'next_refresh_datetime' => $next_refresh_datetime,
                        'last_refresh_datetime' => $audit->last_refresh_datetime,
                        'expiration_datetime' => $audit->expiration_datetime,
                        'last_datetime' => $audit->last_datetime,
                        'datetime' => $audit->datetime,
                    ];
                } else {
                    /* Create a new queued audit */
                    $uploader_id = md5(get_ip());

                    $audit_id = db()->insert('audits', [
                        'user_id' => $this->user->user_id,
                        'domain_id' => $domain_id,
                        'uploader_id' => $uploader_id,
                        'website_id' => $website_id,
                        'host' => $parsed_url['host'],
                        'url' => $data['url'],
                        'url_hash' => $url_hash,
                        'data' => $data_json,
                        'settings' => $settings_json,
                        'notifications' => $notifications_json,
                        'is_queued' => 1,
                        'next_refresh_datetime' => get_date(),
                        'expiration_datetime' => $expiration_datetime,
                        'datetime' => $current_datetime,
                    ]);

                    $response_data[] = [
                        'id' => (int) $audit_id,
                        'website_id' => (int) $website_id,
                        'domain_id' => (int) $domain_id,
                        'user_id' => (int) $this->user->user_id,
                        'uploader_id' => $uploader_id,
                        'host' => $parsed_url['host'],
                        'url' => $data['url'],
                        'resolved_url' => null,
                        'ttfb' => 0.0,
                        'response_time' => 0.0,
                        'average_download_speed' => 0.0,
                        'page_size' => 0.0,
                        'http_requests' => 0,
                        'is_https' => false,
                        'is_ssl_valid' => false,
                        'http_protocol' => 0,
                        'title' => null,
                        'meta_description' => null,
                        'meta_keywords' => null,
                        'data' => json_decode($data_json),
                        'issues' => null,
                        'settings' => json_decode($settings_json),
                        'notifications' => json_decode($notifications_json),
                        'score' => 0,
                        'total_tests' => 0,
                        'passed_tests' => 0,
                        'total_issues' => 0,
                        'major_issues' => 0,
                        'moderate_issues' => 0,
                        'minor_issues' => 0,
                        'total_refreshes' => 0,
                        'is_queued' => true,
                        'refresh_error' => '',
                        'is_external_redirected' => false,
                        'total_redirects' => 0,
                        'next_refresh_datetime' => $current_datetime,
                        'last_refresh_datetime' => null,
                        'expiration_datetime' => $expiration_datetime,
                        'last_datetime' => null,
                        'datetime' => $current_datetime,
                    ];
                }
            } /* Instant processing */
            else {

                /* Check to see if URL audit exists already for this user */
                $audit = db()->where('user_id', $this->user->user_id)->where('url_hash', $url_hash)->getOne('audits');
                $audit_id = null;

                /* Check if we should continue the checking process */
                if($data['is_external_redirected']) {
                    /* Update already existing audit */
                    if($audit) {
                        $audit_id = $audit->audit_id;

                        db()->where('audit_id', $audit->audit_id)->update('audits', [
                            'host' => $parsed_url['host'],
                            'resolved_url' => $data['resolved_url'],
                            'total_refreshes' => db()->inc(),
                            'is_external_redirected' => $data['is_external_redirected'],
                            'total_redirects' => $data['total_redirects'],
                            'refresh_error' => '',
                            'next_refresh_datetime' => $next_refresh_datetime,
                            'last_refresh_datetime' => $current_datetime,
                            'expiration_datetime' => $expiration_datetime,
                        ]);

                        /* Insert a log of the current update as old */
                        db()->insert('archived_audits', [
                            'audit_id' => $audit->audit_id,
                            'user_id' => $audit->user_id,
                            'domain_id' => $audit->domain_id,
                            'uploader_id' => $audit->uploader_id,
                            'website_id' => $audit->website_id,
                            'host' => $audit->host,
                            'url' => $audit->url,
                            'resolved_url' => $audit->resolved_url,
                            'ttfb' => $audit->ttfb,
                            'response_time' => $audit->response_time,
                            'average_download_speed' => $audit->average_download_speed,
                            'page_size' => $audit->page_size,
                            'http_requests' => $audit->http_requests,
                            'is_https' => $audit->is_https,
                            'is_ssl_valid' => $audit->is_ssl_valid,
                            'http_protocol' => $audit->http_protocol,
                            'title' => $audit->title,
                            'meta_description' => $audit->meta_description,
                            'meta_keywords' => $audit->meta_keywords,
                            'data' => $audit->data,
                            'ai_summary' => $audit->ai_summary,
                            'issues' => $audit->issues,
                            'score' => $audit->score,
                            'total_tests' => $audit->total_tests,
                            'passed_tests' => $audit->passed_tests,
                            'total_issues' => $audit->total_issues,
                            'major_issues' => $audit->major_issues,
                            'moderate_issues' => $audit->moderate_issues,
                            'minor_issues' => $audit->minor_issues,
                            'refresh_error' => $audit->refresh_error,
                            'is_external_redirected' => $audit->is_external_redirected,
                            'total_redirects' => $audit->total_redirects,
                            'expiration_datetime' => $audit->expiration_datetime,
                            'datetime' => $audit->datetime,
                        ]);

                        $response_data[] = [
                            'id' => (int) $audit->audit_id,
                            'website_id' => (int) $audit->website_id,
                            'domain_id' => (int) $audit->domain_id,
                            'user_id' => (int) $audit->user_id,
                            'uploader_id' => $audit->uploader_id,
                            'host' => $parsed_url['host'],
                            'url' => $audit->url,
                            'resolved_url' => $data['resolved_url'],
                            'ttfb' => (float) $audit->ttfb,
                            'response_time' => (float) $audit->response_time,
                            'average_download_speed' => (float) $audit->average_download_speed,
                            'page_size' => (float) $audit->page_size,
                            'http_requests' => (int) $audit->http_requests,
                            'is_https' => (bool) $audit->is_https,
                            'is_ssl_valid' => (bool) $audit->is_ssl_valid,
                            'http_protocol' => (int) $audit->http_protocol,
                            'title' => $audit->title,
                            'meta_description' => $audit->meta_description,
                            'meta_keywords' => $audit->meta_keywords,
                            'data' => json_decode($audit->data),
                            'issues' => json_decode($audit->issues),
                            'settings' => json_decode($audit->settings),
                            'notifications' => json_decode($audit->notifications),
                            'score' => (int) $audit->score,
                            'total_tests' => (int) $audit->total_tests,
                            'passed_tests' => (int) $audit->passed_tests,
                            'total_issues' => (int) $audit->total_issues,
                            'major_issues' => (int) $audit->major_issues,
                            'moderate_issues' => (int) $audit->moderate_issues,
                            'minor_issues' => (int) $audit->minor_issues,
                            'total_refreshes' => (int) $audit->total_refreshes + 1,
                            'is_queued' => (bool) $audit->is_queued,
                            'refresh_error' => '',
                            'is_external_redirected' => (bool) $data['is_external_redirected'],
                            'total_redirects' => (int) $data['total_redirects'],
                            'next_refresh_datetime' => $next_refresh_datetime,
                            'last_refresh_datetime' => $current_datetime,
                            'expiration_datetime' => $expiration_datetime,
                            'last_datetime' => $audit->last_datetime,
                            'datetime' => $audit->datetime,
                        ];
                    }

                    /* Insert new audit */
                    else {
                        $uploader_id = md5(get_ip());

                        $audit_id = db()->insert('audits', [
                            'user_id' => $this->user->user_id,
                            'domain_id' => $domain_id,
                            'uploader_id' => $uploader_id,
                            'website_id' => $website_id,
                            'host' => $parsed_url['host'],
                            'url' => $data['url'],
                            'url_hash' => $url_hash,
                            'resolved_url' => $data['resolved_url'],
                            'settings' => $settings_json,
                            'notifications' => $notifications_json,
                            'is_external_redirected' => $data['is_external_redirected'],
                            'total_redirects' => $data['total_redirects'],
                            'next_refresh_datetime' => $next_refresh_datetime,
                            'last_refresh_datetime' => $current_datetime,
                            'expiration_datetime' => $expiration_datetime,
                            'datetime' => $current_datetime,
                        ]);

                        $response_data[] = [
                            'id' => (int) $audit_id,
                            'website_id' => (int) $website_id,
                            'domain_id' => (int) $domain_id,
                            'user_id' => (int) $this->user->user_id,
                            'uploader_id' => $uploader_id,
                            'host' => $parsed_url['host'],
                            'url' => $data['url'],
                            'resolved_url' => $data['resolved_url'],
                            'ttfb' => 0.0,
                            'response_time' => 0.0,
                            'average_download_speed' => 0.0,
                            'page_size' => 0.0,
                            'http_requests' => 0,
                            'is_https' => false,
                            'is_ssl_valid' => false,
                            'http_protocol' => 0,
                            'title' => null,
                            'meta_description' => null,
                            'meta_keywords' => null,
                            'data' => null,
                            'issues' => null,
                            'settings' => json_decode($settings_json),
                            'notifications' => json_decode($notifications_json),
                            'score' => 0,
                            'total_tests' => 0,
                            'passed_tests' => 0,
                            'total_issues' => 0,
                            'major_issues' => 0,
                            'moderate_issues' => 0,
                            'minor_issues' => 0,
                            'total_refreshes' => 0,
                            'is_queued' => false,
                            'refresh_error' => '',
                            'is_external_redirected' => (bool) $data['is_external_redirected'],
                            'total_redirects' => (int) $data['total_redirects'],
                            'next_refresh_datetime' => $next_refresh_datetime,
                            'last_refresh_datetime' => $current_datetime,
                            'expiration_datetime' => $expiration_datetime,
                            'last_datetime' => null,
                            'datetime' => $current_datetime,
                        ];
                    }

                    continue;
                }

                /* Process data */
                $audit_data = \Altum\Helpers\Audit::process_audit_data($data);
                $issues = [
                    'major' => $audit_data['major_issues'],
                    'moderate' => $audit_data['moderate_issues'],
                    'minor' => $audit_data['minor_issues'],
                    'potential_major_issues' => $audit_data['potential_major_issues'],
                    'potential_moderate_issues' => $audit_data['potential_moderate_issues'],
                    'potential_minor_issues' => $audit_data['potential_minor_issues'],
                    'total_tests' => $audit_data['total_tests'],
                    'passed_tests' => $audit_data['passed_tests'],
                ];
                $issues_json = json_encode($issues);

                if(settings()->audits->ai_is_enabled && settings()->audits->openai_api_key) {
                    $in_parallel = (bool) settings()->audits->openai_webhook_secret_key;

                    $ai_summary = null;
                    $ai_summary_id = null;

                    $ai_result = \Altum\Helpers\Audit::process_ai_summary($data, $issues, $in_parallel);

                    if($in_parallel) {
                        $ai_summary_id = $ai_result;
                    } else {
                        $ai_summary = $ai_result;
                    }
                }

                /* Score */
                $score = $audit_data['score'];

                /* Update already existing audit */
                if($audit) {
                    $audit_id = $audit->audit_id;

                    db()->where('audit_id', $audit->audit_id)->update('audits', [
                        'url' => $data['url'],
                        'resolved_url' => $data['resolved_url'],
                        'ttfb' => $data['ttfb'],
                        'response_time' => $data['response_time'],
                        'average_download_speed' => $data['average_download_speed'],
                        'page_size' => $data['page_size'],
                        'http_requests' => $data['http_requests'],
                        'is_https' => $data['is_https'],
                        'is_ssl_valid' => $data['is_ssl_valid'],
                        'http_protocol' => $data['http_protocol'],
                        'title' => $data['title'],
                        'meta_description' => $data['meta_description'],
                        'meta_keywords' => $data['meta_keywords'],
                        'data' => $data_json,
                        'ai_summary' => $ai_summary ?? null,
                        'ai_summary_id' => $ai_summary_id ?? null,
                        'issues' => $issues_json,
                        'score' => $score,
                        'total_tests' => $audit_data['total_tests'],
                        'passed_tests' => $audit_data['passed_tests'],
                        'total_issues' => $audit_data['total_issues'],
                        'major_issues' => $audit_data['found_major_issues'],
                        'moderate_issues' => $audit_data['found_moderate_issues'],
                        'minor_issues' => $audit_data['found_minor_issues'],
                        'total_refreshes' => db()->inc(),
                        'total_redirects' => $data['total_redirects'],
                        'refresh_error' => '',
                        'is_external_redirected' => $data['is_external_redirected'],
                        'next_refresh_datetime' => $next_refresh_datetime,
                        'last_refresh_datetime' => $current_datetime,
                        'expiration_datetime' => $expiration_datetime,
                    ]);

                    /* Insert a log of the current update as old */
                    db()->insert('archived_audits', [
                        'audit_id' => $audit->audit_id,
                        'user_id' => $audit->user_id,
                        'domain_id' => $audit->domain_id,
                        'uploader_id' => $audit->uploader_id,
                        'website_id' => $audit->website_id,
                        'host' => $audit->host,
                        'url' => $audit->url,
                        'resolved_url' => $audit->resolved_url,
                        'ttfb' => $audit->ttfb,
                        'response_time' => $audit->response_time,
                        'average_download_speed' => $audit->average_download_speed,
                        'page_size' => $audit->page_size,
                        'http_requests' => $audit->http_requests,
                        'is_https' => $audit->is_https,
                        'is_ssl_valid' => $audit->is_ssl_valid,
                        'http_protocol' => $audit->http_protocol,
                        'title' => $audit->title,
                        'meta_description' => $audit->meta_description,
                        'meta_keywords' => $audit->meta_keywords,
                        'data' => $audit->data,
                        'ai_summary' => $audit->ai_summary,
                        'issues' => $audit->issues,
                        'score' => $audit->score,
                        'total_tests' => $audit->total_tests,
                        'passed_tests' => $audit->passed_tests,
                        'total_issues' => $audit->total_issues,
                        'major_issues' => $audit->major_issues,
                        'moderate_issues' => $audit->moderate_issues,
                        'minor_issues' => $audit->minor_issues,
                        'refresh_error' => $audit->refresh_error,
                        'is_external_redirected' => $audit->is_external_redirected,
                        'total_redirects' => $audit->total_redirects,
                        'expiration_datetime' => $audit->expiration_datetime,
                        'datetime' => $audit->datetime,
                    ]);

                    $response_data[] = [
                        'id' => (int) $audit->audit_id,
                        'website_id' => (int) $audit->website_id,
                        'domain_id' => (int) $audit->domain_id,
                        'user_id' => (int) $audit->user_id,
                        'uploader_id' => $audit->uploader_id,
                        'host' => $audit->host,
                        'url' => $data['url'],
                        'resolved_url' => $data['resolved_url'],
                        'ttfb' => (float) $data['ttfb'],
                        'response_time' => (float) $data['response_time'],
                        'average_download_speed' => (float) $data['average_download_speed'],
                        'page_size' => (float) $data['page_size'],
                        'http_requests' => (int) $data['http_requests'],
                        'is_https' => (bool) $data['is_https'],
                        'is_ssl_valid' => (bool) $data['is_ssl_valid'],
                        'http_protocol' => (int) $data['http_protocol'],
                        'title' => $data['title'],
                        'meta_description' => $data['meta_description'],
                        'meta_keywords' => $data['meta_keywords'],
                        'data' => json_decode($data_json),
                        'issues' => json_decode($issues_json),
                        'settings' => json_decode($audit->settings),
                        'notifications' => json_decode($audit->notifications),
                        'score' => (int) $score,
                        'total_tests' => (int) $audit_data['total_tests'],
                        'passed_tests' => (int) $audit_data['passed_tests'],
                        'total_issues' => (int) $audit_data['total_issues'],
                        'major_issues' => (int) $audit_data['found_major_issues'],
                        'moderate_issues' => (int) $audit_data['found_moderate_issues'],
                        'minor_issues' => (int) $audit_data['found_minor_issues'],
                        'total_refreshes' => (int) $audit->total_refreshes + 1,
                        'is_queued' => false,
                        'refresh_error' => '',
                        'is_external_redirected' => (bool) $data['is_external_redirected'],
                        'total_redirects' => (int) $data['total_redirects'],
                        'next_refresh_datetime' => $next_refresh_datetime,
                        'last_refresh_datetime' => $current_datetime,
                        'expiration_datetime' => $expiration_datetime,
                        'last_datetime' => $audit->last_datetime,
                        'datetime' => $audit->datetime,
                    ];
                } /* Insert new audit */
                else {
                    $uploader_id = md5(get_ip());

                    $audit_id = db()->insert('audits', [
                        'user_id' => $this->user->user_id,
                        'domain_id' => $domain_id,
                        'uploader_id' => $uploader_id,
                        'website_id' => $website_id,
                        'host' => $data['parsed_url']['host'],
                        'url' => $data['url'],
                        'url_hash' => $url_hash,
                        'resolved_url' => $data['resolved_url'],
                        'ttfb' => $data['ttfb'],
                        'response_time' => $data['response_time'],
                        'average_download_speed' => $data['average_download_speed'],
                        'page_size' => $data['page_size'],
                        'http_requests' => $data['http_requests'],
                        'is_https' => $data['is_https'],
                        'is_ssl_valid' => $data['is_ssl_valid'],
                        'http_protocol' => $data['http_protocol'],
                        'title' => $data['title'],
                        'meta_description' => $data['meta_description'],
                        'meta_keywords' => $data['meta_keywords'],
                        'data' => $data_json,
                        'issues' => $issues_json,
                        'score' => $score,
                        'total_tests' => $audit_data['total_tests'],
                        'passed_tests' => $audit_data['passed_tests'],
                        'total_issues' => $audit_data['total_issues'],
                        'major_issues' => $audit_data['found_major_issues'],
                        'moderate_issues' => $audit_data['found_moderate_issues'],
                        'minor_issues' => $audit_data['found_minor_issues'],
                        'settings' => $settings_json,
                        'notifications' => $notifications_json,
                        'next_refresh_datetime' => $next_refresh_datetime,
                        'last_refresh_datetime' => $current_datetime,
                        'expiration_datetime' => $expiration_datetime,
                        'datetime' => $current_datetime,
                    ]);

                    $response_data[] = [
                        'id' => (int) $audit_id,
                        'website_id' => (int) $website_id,
                        'domain_id' => (int) $domain_id,
                        'user_id' => (int) $this->user->user_id,
                        'uploader_id' => $uploader_id,
                        'host' => $data['parsed_url']['host'],
                        'url' => $data['url'],
                        'resolved_url' => $data['resolved_url'],
                        'ttfb' => (float) $data['ttfb'],
                        'response_time' => (float) $data['response_time'],
                        'average_download_speed' => (float) $data['average_download_speed'],
                        'page_size' => (float) $data['page_size'],
                        'http_requests' => (int) $data['http_requests'],
                        'is_https' => (bool) $data['is_https'],
                        'is_ssl_valid' => (bool) $data['is_ssl_valid'],
                        'http_protocol' => (int) $data['http_protocol'],
                        'title' => $data['title'],
                        'meta_description' => $data['meta_description'],
                        'meta_keywords' => $data['meta_keywords'],
                        'data' => json_decode($data_json),
                        'issues' => json_decode($issues_json),
                        'settings' => json_decode($settings_json),
                        'notifications' => json_decode($notifications_json),
                        'score' => (int) $score,
                        'total_tests' => (int) $audit_data['total_tests'],
                        'passed_tests' => (int) $audit_data['passed_tests'],
                        'total_issues' => (int) $audit_data['total_issues'],
                        'major_issues' => (int) $audit_data['found_major_issues'],
                        'moderate_issues' => (int) $audit_data['found_moderate_issues'],
                        'minor_issues' => (int) $audit_data['found_minor_issues'],
                        'total_refreshes' => 0,
                        'is_queued' => false,
                        'refresh_error' => '',
                        'is_external_redirected' => (bool) $data['is_external_redirected'],
                        'total_redirects' => (int) $data['total_redirects'],
                        'next_refresh_datetime' => $next_refresh_datetime,
                        'last_refresh_datetime' => $current_datetime,
                        'expiration_datetime' => $expiration_datetime,
                        'last_datetime' => null,
                        'datetime' => $current_datetime,
                    ];
                }
            }
        }

        /* Stats calculation for each website */
        foreach($websites_cache as $website_id) {
            (new \Altum\Models\Website())->refresh_stats($website_id);
        }

        /* Clear the cache */
        cache()->deleteItem('audits_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('audits_dashboard?user_id=' . $this->user->user_id);
        cache()->deleteItem('websites_dashboard?user_id=' . $this->user->user_id);

        Response::jsonapi_success(count($response_data) == 1 ? $response_data[0] : $response_data, null, 201);

    }

    private function patch() {

        $audit_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $audit = db()->where('audit_id', $audit_id)->where('user_id', $this->user->user_id)->getOne('audits');

        /* We haven't found the resource */
        if(!$audit) {
            $this->return_404();
        }
        foreach(['notifications', 'settings'] as $key) $audit->{$key} = json_decode($audit->{$key} ?? '');

        /* Check for any errors */
        $required_fields = [];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user_id($this->user->user_id);

        /* Filter some of the variables */
        $_POST['domain_id'] = isset($_POST['domain_id']) && array_key_exists($_POST['domain_id'], $domains) ? (int) $_POST['domain_id'] : $audit->domain_id;
        $is_public = (int) (bool) ($_POST['is_public'] ?? $audit->is_public);
        $_POST['audit_check_interval'] = isset($_POST['audit_check_interval']) && in_array($_POST['audit_check_interval'], $this->user->plan_settings->audits_check_intervals ?? []) ? (int) $_POST['audit_check_interval'] : $audit->settings->audit_check_interval;
        $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
        $password = !empty($_POST['password']) ?
            ($_POST['password'] != $audit->settings->password ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $audit->settings->password)
            : $audit->settings->password;

        /* Notification handlers */
        $_POST['notifications'] = array_map(
            'intval',
            array_filter($_POST['notifications'] ?? $audit->notifications, function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
            $_POST['notifications'] = array_slice($_POST['notifications'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
        }

        /* Settings */
        $settings = [
            'is_public' => $is_public,
            'password' => $password,
            'audit_check_interval' => $_POST['audit_check_interval'],
        ];

        /* Next refresh date */
        $next_refresh_datetime = $_POST['audit_check_interval'] && $audit->settings->audit_check_interval != $_POST['audit_check_interval'] ? (new \DateTime())->modify('+' . $_POST['audit_check_interval'] . ' seconds')->format('Y-m-d H:i:s') : $audit->next_refresh_datetime;

        /* Notification handlers */
        $notifications = json_encode($_POST['notifications']);

        /* Database query */
        db()->where('audit_id', $audit->audit_id)->update('audits', [
            'domain_id' => $_POST['domain_id'],
            'settings' => json_encode($settings),
            'notifications' => $notifications,
            'next_refresh_datetime' => $next_refresh_datetime,
            'last_datetime' => get_date(),
        ]);

        /* Prepare the data */
        $data = [
            'id' => (int) $audit->audit_id,
            'website_id' => (int) $audit->website_id,
            'domain_id' => (int) $_POST['domain_id'],
            'user_id' => (int) $audit->user_id,
            'uploader_id' => $audit->uploader_id,
            'host' => $audit->host,
            'url' => $audit->url,
            'resolved_url' => $audit->resolved_url,
            'ttfb' => (float) $audit->ttfb,
            'response_time' => (float) $audit->response_time,
            'average_download_speed' => (float) $audit->average_download_speed,
            'page_size' => (float) $audit->page_size,
            'is_https' => (bool) $audit->is_https,
            'is_ssl_valid' => (bool) $audit->is_ssl_valid,
            'http_protocol' => (int) $audit->http_protocol,
            'title' => $audit->title,
            'meta_description' => $audit->meta_description,
            'meta_keywords' => $audit->meta_keywords,
            'data' => json_decode($audit->data),
            'issues' => json_decode($audit->issues),
            'settings' => $settings,
            'notifications' => $_POST['notifications'],
            'score' => (int) $audit->score,
            'total_tests' => (int) $audit->total_tests,
            'passed_tests' => (int) $audit->passed_tests,
            'total_issues' => (int) $audit->total_issues,
            'major_issues' => (int) $audit->major_issues,
            'moderate_issues' => (int) $audit->moderate_issues,
            'minor_issues' => (int) $audit->minor_issues,
            'total_refreshes' => (int) $audit->total_refreshes,
            'is_queued' => (bool) $audit->is_queued,
            'refresh_error' => $audit->refresh_error,
            'is_external_redirected' => (bool) $audit->is_external_redirected,
            'total_redirects' => (int) $audit->total_redirects,
            'next_refresh_datetime' => $next_refresh_datetime,
            'last_refresh_datetime' => $audit->last_refresh_datetime,
            'expiration_datetime' => $audit->expiration_datetime,
            'last_datetime' => get_date(),
            'datetime' => $audit->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $audit_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $audit = db()->where('audit_id', $audit_id)->where('user_id', $this->user->user_id)->getOne('audits');

        /* We haven't found the resource */
        if(!$audit) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('audit_id', $audit_id)->delete('audits');

        (new \Altum\Models\Website())->refresh_stats($audit->website_id);

        /* Clear the cache */
        cache()->deleteItem('audits_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('audits_dashboard?user_id=' . $this->user->user_id);


        http_response_code(200);
        die();

    }
}
