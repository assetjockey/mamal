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
use Altum\Title;

defined('ALTUMCODE') || die();

class ArchivedAudit extends Controller {
    public $audit;
    public $audit_user;

    public function index()
    {

        $archived_audit_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$archived_audit = db()->where('archived_audit_id', $archived_audit_id)->getOne('archived_audits')) {
            throw_404();
        }
        foreach(['data', 'issues'] as $key) $archived_audit->{$key} = json_decode($archived_audit->{$key} ?? '');

        /* Get main audit */
        if(!$audit = db()->where('audit_id', $archived_audit->audit_id)->getOne('audits')) {
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
        $audit->full_url = (isset(\Altum\Router::$data['domain']) ? \Altum\Router::$data['domain']->url : url()) . 'archived-audit/' . $archived_audit_id;

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
            $archived_audits_left = db()->where('audit_id', $audit->audit_id)->where('archived_audit_id', $archived_audit->archived_audit_id, '<')->orderBy('`archived_audit_id`', 'DESC')->get('archived_audits', 15, ['archived_audit_id', 'score', 'datetime', 'is_external_redirected', 'refresh_error']);
            $archived_audits_left = array_reverse($archived_audits_left);

            $archived_audits_right = db()->where('audit_id', $audit->audit_id)->where('archived_audit_id', $archived_audit->archived_audit_id, '>')->orderBy('`archived_audit_id`', 'DESC')->get('archived_audits', 15, ['archived_audit_id', 'score', 'datetime', 'is_external_redirected', 'refresh_error']);
            $archived_audits_right = array_reverse($archived_audits_right);

            /* Set a custom title */
            Title::set(sprintf(l('archived_audit.title'), string_truncate(remove_url_protocol_from_url($archived_audit->url), 32)));

            /* Global websites stats */
            $audits_stats = (new \Altum\Models\Audit())->get_stats();

            /* Export handler */
            process_export_csv_new([$archived_audit], ['audit_id', 'user_id', 'domain_id', 'uploader_id', 'host', 'url', 'ttfb', 'response_time', 'average_download_speed', 'page_size', 'http_requests', 'is_https', 'is_ssl_valid', 'http_protocol', 'title', 'meta_description', 'meta_keywords', 'ai_summary', 'score', 'total_issues', 'major_issues', 'moderate_issues', 'minor_issues', 'refresh_error', 'total_refreshes', 'next_refresh_datetime', 'last_refresh_datetime', 'expiration_datetime', 'last_datetime', 'datetime'], [], sprintf(l('audits.title')));
            process_export_json([$archived_audit], ['audit_id', 'user_id', 'domain_id', 'uploader_id', 'host', 'url', 'ttfb', 'response_time', 'average_download_speed', 'page_size', 'http_requests', 'is_https', 'is_ssl_valid', 'http_protocol', 'title', 'meta_description', 'meta_keywords', 'ai_summary', 'data', 'issues', 'settings', 'notifications', 'score', 'total_issues', 'major_issues', 'moderate_issues', 'minor_issues', 'refresh_error', 'total_refreshes', 'next_refresh_datetime', 'last_refresh_datetime', 'expiration_datetime', 'last_datetime', 'datetime'], sprintf(l('audits.title')));

            $data = [
                'archived_audit' => $archived_audit,
                'archived_audits_left' => $archived_audits_left,
                'archived_audits_right' => $archived_audits_right,
                'audit' => $audit,
                'audits_stats' => $audits_stats,
            ];

            $view = new \Altum\View('archived-audit/index', (array)$this);
            $this->add_view_content('content', $view->run($data));

        }
    }

}
