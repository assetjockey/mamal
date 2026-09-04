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

class AuditRefresh extends Controller {

    public function index() {

        set_time_limit(0);

        if(empty($_POST)) {
            throw_404();
        }

        $error_redirect = is_logged_in() ? 'archived-audits' : 'seo';

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.audits')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect($error_redirect);
        }

        /* Check for the plan limit */
        $audits_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`audit_audits_current_month`');

        if($this->user->plan_settings->audits_per_month_limit != -1 && $audits_current_month >= $this->user->plan_settings->audits_per_month_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect($error_redirect);
        }

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        /* Check for any errors */
        $required_fields = ['audit_id'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) ||(isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                Alerts::add_field_error($field, l('global.error_message.empty_field'));
            }
        }

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$audit = db()->where('audit_id', $_POST['audit_id'])->getOne('audits')) {
            redirect($error_redirect);
        }
        foreach(['settings'] as $key) $audit->{$key} = json_decode($audit->{$key} ?? '');


        if($audit->user_id != $this->user->user_id && $audit->uploader_id != md5(get_ip())) {
            redirect($error_redirect);
        }

        /* Send the main request */
        try {
            $response = \Altum\Helpers\Audit::process_request($audit->url);
        } catch(\Exception $exception) {
            Alerts::add_error($exception->getMessage());
            redirect($error_redirect);
        }

        /* Single URL processing */
        $data = \Altum\Helpers\Audit::process_request_response($audit->url, $response);
        if(!$data['is_external_redirected']) {
            $data_not_found = \Altum\Helpers\Audit::process_not_found($data['parsed_url']);
            $data_robots = \Altum\Helpers\Audit::process_robots($data['parsed_url']);
        }

        /* Merge data */
        $data = $data + ($data_not_found ?? []) + ($data_robots ?? []);

        /* Clean data */
        $data = convert_array_to_utf8($data);

        /* Prepare full data to be inserted */
        $data_json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

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
                'datetime' => $audit->last_refresh_datetime ?: $audit->datetime,
            ]);

            /* Prepare expiration date */
            $expiration_datetime = (new \DateTime())->modify('+' . ($this->user->plan_settings->audits_retention ?? 90) . ' days')->format('Y-m-d H:i:s');

            /* Next refresh date */
            $next_refresh_datetime = $audit->settings->audit_check_interval ? (new \DateTime())->modify('+' . $audit->settings->audit_check_interval . ' seconds')->format('Y-m-d H:i:s') : null;

            /* Check if we should continue the checking process */
            if($data['is_external_redirected']) {
                db()->where('audit_id', $audit->audit_id)->update('audits', [
                    'host' => $data['parsed_url']['host'],
                    'resolved_url' => $data['resolved_url'],
                    'total_refreshes' => db()->inc(),
                    'refresh_error' => '',
                    'is_external_redirected' => $data['is_external_redirected'],
                    'total_redirects' => $data['total_redirects'],
                    'next_refresh_datetime' => $next_refresh_datetime,
                    'last_refresh_datetime' => get_date(),
                    'expiration_datetime' => $expiration_datetime,
                ]);
            }

            /* Normal process */
            else {
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

                if(settings()->audits->ai_is_enabled && settings()->audits->openai_api_key) {
                    $in_parallel = (bool)settings()->audits->openai_webhook_secret_key;

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

                /* Update the main audit */
                db()->where('audit_id', $audit->audit_id)->update('audits', [
                    'host' => $data['parsed_url']['host'],
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
            }

            (new \Altum\Models\Website())->refresh_stats($audit->website_id);

            /* Database query */
            db()->where('user_id', $this->user->user_id)->update('users', [
                'audit_audits_current_month' => db()->inc()
            ]);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('audits.success_message.processed_refresh'), '<strong>' . remove_url_protocol_from_url($audit->url) . '</strong>'));
            redirect('audit/' . $audit->audit_id);
        }

        redirect($error_redirect);
    }

}
