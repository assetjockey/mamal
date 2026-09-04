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
use Altum\Meta;
use Altum\Models\User;
use Altum\Response;
use Altum\Title;

defined('ALTUMCODE') || die();

class Audit extends Controller {
    public $audit;
    public $audit_user;

    public function index() {
        /* Make sure there are no extra URL additions */
        if(isset($this->params[1])) {
            throw_404();
        }

        $audit_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$audit = db()->where('audit_id', $audit_id)->getOne('audits')) {
            throw_404();
        }
        foreach(['data', 'issues', 'settings'] as $key) $audit->{$key} = json_decode($audit->{$key} ?? '');

        /* Public audit */
        if(!$audit->settings->is_public) {

            /* Make sure the current user has access */
            if(($audit->uploader_id != md5(get_ip())) && (!$audit->user_id || ($audit->user_id != $this->user->user_id))) {
                throw_404();
            }

        }

        /* Audit */
        $audit->full_url = (isset(\Altum\Router::$data['domain']) ? \Altum\Router::$data['domain']->url : url()) . 'audit/' . $audit_id;

        /* User of the audit */
        $this->audit_user = (new User())->get_user_by_user_id($audit->user_id);

        /* Meta */
        Meta::set_canonical_url($audit->full_url);

        /* Check if the user has access to the page */
        $has_access = !$audit->settings->password || ($audit->settings->password && isset($_COOKIE['password_' . $audit->audit_id]) && $_COOKIE['password_' . $audit->audit_id] == $audit->settings->password);

        /* Do not let the user have password protection if the plan doesn't allow it */
        if(!$this->audit_user->plan_settings->password_protection_is_enabled) {
            $has_access = true;
        }

        /* White label */
        if(settings()->main->white_labeling_is_enabled && $this->audit_user->plan_settings->white_labeling_is_enabled && \Altum\Router::$controller_key != 'invoice' && \Altum\Router::$path != 'admin') {
            if($this->audit_user->preferences->white_label_title) {
                settings()->main->title = $this->audit_user->preferences->white_label_title;
                Title::initialize(settings()->main->title);
            }

            if($this->audit_user->preferences->white_label_logo_light) {
                settings()->main->logo_light = $this->audit_user->preferences->white_label_logo_light;
                settings()->main->logo_light_full_url = \Altum\Uploads::get_full_url('users') . settings()->main->logo_light;
            }

            if($this->audit_user->preferences->white_label_logo_dark) {
                settings()->main->logo_dark = $this->audit_user->preferences->white_label_logo_dark;
                settings()->main->logo_dark_full_url = \Altum\Uploads::get_full_url('users') . settings()->main->logo_dark;
            }

            if($this->audit_user->preferences->white_label_favicon) {
                settings()->main->favicon = $this->audit_user->preferences->white_label_favicon;
                settings()->main->favicon_full_url = \Altum\Uploads::get_full_url('users') . settings()->main->favicon;
            }
        }

        /* Check if the password form is submitted */
        if(!$has_access && !empty($_POST)) {
            $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);

            /* Check for any errors */
            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!password_verify($_POST['password'], $audit->settings->password)) {
                Alerts::add_field_error('password', l('audits.password.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Set a cookie */
                setcookie('password_' . $audit->audit_id, $audit->settings->password, time() + 60 * 60 * 24 * 30);

                header('Location: ' . $_SERVER['REQUEST_URI']);
                die();

            }

        }

        /* Display the password form */
        if(!$has_access) {

            /* Set a custom title */
            Title::set(l('audits.password.title'));

            /* Main View */
            $data = [];
            $view = new \Altum\View('audit/password', (array)$this);
            $this->add_view_content('content', $view->run($data));

        }

        /* Show audit */
        else {

            /* Get archived audits data */
            $archived_audits = $audit->total_refreshes > 0 ?
                db()->where('audit_id', $audit_id)->orderBy('`archived_audit_id`', 'DESC')->get('archived_audits', 30, ['archived_audit_id', 'score', 'datetime', 'is_external_redirected', 'refresh_error'])
                : [];

            $archived_audits = array_reverse($archived_audits);

            /* Set a custom title */
            Title::set(sprintf(l('audit.title'), string_truncate(remove_url_protocol_from_url($audit->url), 32)));

            /* Global websites stats */
            $audits_stats = (new \Altum\Models\Audit())->get_stats();

            /* Export handler */
            process_export_csv_new([$audit], ['audit_id', 'user_id', 'domain_id', 'uploader_id', 'host', 'url', 'ttfb', 'response_time', 'average_download_speed', 'page_size', 'http_requests', 'is_https', 'is_ssl_valid', 'http_protocol', 'title', 'meta_description', 'meta_keywords', 'ai_summary', 'score', 'total_issues', 'major_issues', 'moderate_issues', 'minor_issues', 'refresh_error', 'total_refreshes', 'next_refresh_datetime', 'last_refresh_datetime', 'expiration_datetime', 'last_datetime', 'datetime'], [], sprintf(l('audits.title')));
            process_export_json([$audit], ['audit_id', 'user_id', 'domain_id', 'uploader_id', 'host', 'url', 'ttfb', 'response_time', 'average_download_speed', 'page_size', 'http_requests', 'is_https', 'is_ssl_valid', 'http_protocol', 'title', 'meta_description', 'meta_keywords', 'ai_summary', 'data', 'issues', 'settings', 'notifications', 'score', 'total_issues', 'major_issues', 'moderate_issues', 'minor_issues', 'refresh_error', 'total_refreshes', 'next_refresh_datetime', 'last_refresh_datetime', 'expiration_datetime', 'last_datetime', 'datetime'], sprintf(l('audits.title')));

            $data = [
                'audit' => $audit,
                'archived_audits' => $archived_audits,
                'audits_stats' => $audits_stats,
            ];

            $view = new \Altum\View('audit/index', (array)$this);
            $this->add_view_content('content', $view->run($data));

        }
    }

    public function get_ai_summary() {

        \Altum\Authentication::guard();

        if(!settings()->audits->ai_is_enabled || !settings()->audits->openai_api_key) {
            throw_404();
        }

        $audit_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$audit = db()->where('audit_id', $audit_id)->where('user_id', $this->user->user_id)->getOne('audits')) {
            throw_404();
        }

        Response::json($audit->ai_summary);

    }
}
