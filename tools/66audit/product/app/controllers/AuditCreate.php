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
use vipnytt\SitemapParser;

defined('ALTUMCODE') || die();

class AuditCreate extends Controller {

    public function index() {

        set_time_limit(0);

        if(empty($_POST)) {
            throw_404();
        }

        /* Set used variables */
        session_set('audit_form', [
            'url' => $_POST['url'] ?? null,
            'urls' => $_POST['urls'] ?? null,
            'type' => $_POST['type'] ?? 'single',
            'domain_id' => $_POST['domain_id'] ?? null,
            'is_public' => $_POST['is_public'] ?? true,
            'password' => $_POST['password'] ?? null,
            'audit_check_interval' => $_POST['audit_check_interval'] ?? null,
            'notifications' => $_POST['notifications'] ?? [],
        ]);

        $error_redirect = is_logged_in() ? 'dashboard' : 'seo';

        if(!is_logged_in() && !$this->user->plan_settings->audits_per_month_limit) {
            redirect($error_redirect);
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.audits')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('audits');
        }

        /* Check for the plan limit */
        if(is_logged_in()) {
            $total_websites = db()->where('user_id', $this->user->user_id)->getValue('websites', 'count(`website_id`)');
            $audits_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`audit_audits_current_month`');
        } else {
            $audits_current_month = db()
                ->where('uploader_id', md5(get_ip()))
                ->where('MONTH(datetime)', date('m'))
                ->where('YEAR(datetime)', date('Y'))
                ->getValue('audits', 'count(*)');
        }

        if($this->user->plan_settings->audits_per_month_limit != -1 && $audits_current_month >= $this->user->plan_settings->audits_per_month_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect($error_redirect);
        }

        $_POST['url'] = get_url($_POST['url'] ?? '', 256, false);
        $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['single', 'sitemap', 'bulk', 'html']) ? input_clean($_POST['type']) : 'single';
        $is_public = (int) isset($_POST['is_public']);
        $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        $_POST['audit_check_interval'] = isset($_POST['audit_check_interval']) && in_array($_POST['audit_check_interval'], $this->user->plan_settings->audits_check_intervals ?? []) ? (int) $_POST['audit_check_interval'] : null;

        /* Do not allow guests to use custom settings */
        if(!is_logged_in()) {
            $_POST['type'] = 'single';
            $is_public = 1;
            $password = null;
            $_POST['audit_check_interval'] = null;
        }

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

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
            if(!isset($_POST[$field]) ||(isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                Alerts::add_field_error($field, l('global.error_message.empty_field'));
            }
        }

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        /* Data array of processed URLs */
        $data_array = [];

        /* Sitemap processing */
        if($_POST['type'] == 'sitemap') {
            /* Get URL data */
            $parsed_url = parse_url($_POST['url']);
            $parsed_url['host'] = str_replace('www.', '', $parsed_url['host']);

            if(in_array(get_domain_from_url($_POST['url']), settings()->audits->blacklisted_domains)) {
                Alerts::add_field_error('url', l('audits.error_message.blacklisted_domain'));
                redirect($error_redirect);
            }

            /* Check for the plan limit */
            if(is_logged_in() && $this->user->plan_settings->websites_limit != -1 && $total_websites >= $this->user->plan_settings->websites_limit) {
                $website_exists = db()->where('user_id', $this->user->user_id)->where('host', $parsed_url['host'])->getOne('websites');

                /* If its a new website and limit is reached, trigger alert */
                if(!$website_exists) {
                    Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
                    redirect($error_redirect);
                }
            }

            try {
                $parser = new SitemapParser(settings()->audits->user_agent);
                $parser->parseRecursive($_POST['url']);

                $urls = array_keys($parser->getURLs());
            } catch (SitemapParser\Exceptions\SitemapParserException $exception) {
                Alerts::add_field_error('url', $exception->getMessage());
                redirect($error_redirect);
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
                        error_log('[Audit Create][URL processing]: ' . $exception->getMessage());
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
                    $parsed_url['host'] = str_replace('www.', '', $parsed_url['host']);

                    /* Check if its a new entry */
                    $website_exists = db()->where('user_id', $this->user->user_id)->where('host', $parsed_url['host'])->getOne('websites');

                    /* If its a new website and limit is reached, trigger alert */
                    if(!$website_exists) {
                        Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
                        redirect($error_redirect);
                    }

                    if(in_array(get_domain_from_url($url), settings()->audits->blacklisted_domains)) {
                        Alerts::add_field_error('url', l('audits.error_message.blacklisted_domain'));
                        redirect($error_redirect);
                    }
                }
            }


            /* Go through each bulk submitted URLs */
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
                        error_log('[Audit Create][URL processing]: ' . $exception->getMessage());
                        continue;
                    }

                    /* Single URL processing */
                    $data = \Altum\Helpers\Audit::process_request_response($url, $response);

                    if(!$data['is_external_redirected']) {
                        $data_not_found = \Altum\Helpers\Audit::process_not_found($data['parsed_url']);
                        $data_robots = \Altum\Helpers\Audit::process_robots($data['parsed_url']);
                    }

                    /* Merge data */
                    $data_array[] = $data + ($data_not_found ?? []) + ($data_robots ?? []);

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
                Alerts::add_field_error('url', l('audits.error_message.blacklisted_domain'));
                redirect($error_redirect);
            }

            /* Check for the plan limit */
            if(is_logged_in() && $this->user->plan_settings->websites_limit != -1 && $total_websites >= $this->user->plan_settings->websites_limit) {
                $website_exists = db()->where('user_id', $this->user->user_id)->where('host', $parsed_url['host'])->getOne('websites');

                /* If its a new website and limit is reached, trigger alert */
                if(!$website_exists) {
                    Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
                    redirect($error_redirect);
                }
            }

            /* Send the main request */
            try {
                $response = \Altum\Helpers\Audit::process_request($_POST['url']);
            } catch(\Exception $exception) {
                Alerts::add_field_error('url', $exception->getMessage());
                redirect($error_redirect);
            }

            /* Single URL processing */
            $data = \Altum\Helpers\Audit::process_request_response($_POST['url'], $response, $_POST['html']);

            if(!$data['is_external_redirected']) {
                $data_not_found = \Altum\Helpers\Audit::process_not_found($data['parsed_url']);
                $data_robots = \Altum\Helpers\Audit::process_robots($data['parsed_url']);
            }

            /* Merge data */
            $data_array[] = $data + ($data_not_found ?? []) + ($data_robots ?? []);

            $audits_created = 1;
        }

        /* Single URL processing */
        else {
            /* Get URL data */
            $parsed_url = parse_url($_POST['url']);
            $parsed_url['host'] = str_replace('www.', '', $parsed_url['host']);

            if(in_array($parsed_url['host'], settings()->audits->blacklisted_domains)) {
                Alerts::add_field_error('url', l('audits.error_message.blacklisted_domain'));
                redirect($error_redirect);
            }

            /* Check for the plan limit */
            if(is_logged_in() && $this->user->plan_settings->websites_limit != -1 && $total_websites >= $this->user->plan_settings->websites_limit) {
                $website_exists = db()->where('user_id', $this->user->user_id)->where('host', $parsed_url['host'])->getOne('websites');

                /* If its a new website and limit is reached, trigger alert */
                if(!$website_exists) {
                    Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
                    redirect($error_redirect);
                }
            }

            /* Send the main request */
            try {
                $response = \Altum\Helpers\Audit::process_request($_POST['url']);
            } catch(\Exception $exception) {
                Alerts::add_field_error('url', $exception->getMessage());
                redirect($error_redirect);
            }

            /* Single URL processing */
            $data = \Altum\Helpers\Audit::process_request_response($_POST['url'], $response);

            if(!$data['is_external_redirected']) {
                $data_not_found = \Altum\Helpers\Audit::process_not_found($data['parsed_url']);
                $data_robots = \Altum\Helpers\Audit::process_robots($data['parsed_url']);
            }

            /* Merge data */
            $data_array[] = $data + ($data_not_found ?? []) + ($data_robots ?? []);

            $audits_created = 1;
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            set_time_limit(0);

            session_write_close();

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
            if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
                $_POST['notifications'] = array_slice($_POST['notifications'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
            }

            /* Processing based on user status for websites */
            if(is_logged_in()) {
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
            }

            else {
                /* Settings */
                $settings = [
                    'is_public' => 1,
                    'password' => null,
                    'audit_check_interval' => null,
                ];
            }

            /* Prepare expiration date */
            $expiration_datetime = (new \DateTime())->modify('+' . ($this->user->plan_settings->audits_retention ?? 90) . ' days')->format('Y-m-d H:i:s');

            /* Next refresh date */
            $next_refresh_datetime = $_POST['audit_check_interval'] ? (new \DateTime())->modify('+' . $_POST['audit_check_interval'] . ' seconds')->format('Y-m-d H:i:s') : null;

            /* Cache the tested websites */
            $websites_cache = [];

            /* Database query */
            foreach($data_array as $data) {
                $data = convert_array_to_utf8($data);

                /* Parse URL */
                $parsed_url = parse_url($data['url']);
                $parsed_url['host'] = str_replace('www.', '', $parsed_url['host']);
                $url_hash = md5($parsed_url['scheme'] . $parsed_url['host'] . $parsed_url['path']);

                /* Website processing */
                $website_id = null;

                if(is_logged_in()) {
                    $website_id = $websites_cache[$parsed_url['host']] ?? null;

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
                            'settings' => json_encode($settings),
                            'notifications' => json_encode($_POST['notifications']),
                            'datetime' => get_date(),
                        ]);

                        $websites_cache[$parsed_url['host']] = $website_id;
                    } else {
                        $websites_cache[$parsed_url['host']] = $website_id;

						db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->update('websites', [
                            'scheme' => $parsed_url['scheme'],
                            'host' => $parsed_url['host'],
                            'settings' => json_encode($settings),
                            'last_audit_datetime' => get_date(),
                        ]);
                    }
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
                            'next_refresh_datetime' => get_date(),
                        ]);
                    } else {
                        /* Create a new queued audit */
                        $audit_id = db()->insert('audits', [
                            'user_id' => $this->user->user_id,
                            'domain_id' => $domain_id,
                            'uploader_id' => md5(get_ip()),
                            'website_id' => $website_id,
                            'host' => $parsed_url['host'],
                            'url' => $data['url'],
                            'url_hash' => $url_hash,
                            'data' => $data_json,
                            'settings' => json_encode($settings),
                            'notifications' => json_encode($_POST['notifications']),
                            'is_queued' => 1,
                            'next_refresh_datetime' => get_date(),
                            'expiration_datetime' => $expiration_datetime,
                            'datetime' => get_date(),
                        ]);
                    }
                }

                /* Instant processing */
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
                                'last_refresh_datetime' => get_date(),
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
                        }

                        /* Insert new audit */
                        else {
                            $audit_id = db()->insert('audits', [
                                'user_id' => $this->user->user_id,
                                'domain_id' => $domain_id,
                                'uploader_id' => md5(get_ip()),
                                'website_id' => $website_id,
                                'host' => $parsed_url['host'],
                                'url' => $data['url'],
                                'url_hash' => $url_hash,
                                'resolved_url' => $data['resolved_url'],
                                'settings' => json_encode($settings),
                                'notifications' => json_encode($_POST['notifications']),
                                'is_external_redirected' => $data['is_external_redirected'],
                                'total_redirects' => $data['total_redirects'],
                                'next_refresh_datetime' => $next_refresh_datetime,
                                'last_refresh_datetime' => get_date(),
                                'expiration_datetime' => $expiration_datetime,
                                'datetime' => get_date(),
                            ]);
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

                    if(settings()->audits->ai_is_enabled && settings()->audits->openai_api_key && $this->user->plan_settings->ai_is_enabled) {
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
                            'issues' => json_encode($issues),
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
                            'last_refresh_datetime' => get_date(),
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
                    }

                    /* Insert new audit */
                    else {
                        $audit_id = db()->insert('audits', [
                            'user_id' => $this->user->user_id,
                            'domain_id' => $domain_id,
                            'uploader_id' => md5(get_ip()),
                            'website_id' => $website_id,
                            'host' => $parsed_url['host'],
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
                            'ai_summary' => $ai_summary ?? null,
                            'ai_summary_id' => $ai_summary_id ?? null,
                            'issues' => json_encode($issues),
                            'score' => $score,
                            'total_tests' => $audit_data['total_tests'],
                            'passed_tests' => $audit_data['passed_tests'],
                            'total_issues' => $audit_data['total_issues'],
                            'major_issues' => $audit_data['found_major_issues'],
                            'moderate_issues' => $audit_data['found_moderate_issues'],
                            'minor_issues' => $audit_data['found_minor_issues'],
                            'settings' => json_encode($settings),
                            'notifications' => json_encode($_POST['notifications']),
                            'next_refresh_datetime' => $next_refresh_datetime,
                            'last_refresh_datetime' => get_date(),
                            'expiration_datetime' => $expiration_datetime,
                            'datetime' => get_date(),
                        ]);
                    }
                }
            }

            /* Stats calculation for each website */
            foreach($websites_cache as $website_id) {
                (new \Altum\Models\Website())->refresh_stats($website_id);
            }

            /* Start a new session to set a success message */
            session_start();

            /* Clear form */
            session_set('audit_form', []);

            if(is_logged_in()) {
                /* Clear the cache */
                cache()->deleteItem('audits_total?user_id=' . $this->user->user_id);
                cache()->deleteItem('audits_dashboard?user_id=' . $this->user->user_id);
                cache()->deleteItem('websites_dashboard?user_id=' . $this->user->user_id);
            }

            /* Set a nice success message */
            if($_POST['type'] == 'sitemap') {
                Alerts::add_success(sprintf(l('audits.success_message.processed_sitemap'), '<strong>' . remove_url_protocol_from_url($_POST['url']) . '</strong>', '<strong>' . $audits_created . '</strong>'));
                redirect('audits');
            }

            elseif($_POST['type'] == 'bulk') {
                Alerts::add_success(sprintf(l('audits.success_message.processed_bulk'), '<strong>' . $audits_created . '</strong>'));
                redirect('audits');
            }

            else {
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . remove_url_protocol_from_url($_POST['url']) . '</strong>'));
                redirect('audit/' . $audit_id);
            }
        }

        redirect($error_redirect);

    }

}
