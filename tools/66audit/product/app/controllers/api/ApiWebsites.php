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
        $filters = (new \Altum\Filters(['user_id', 'domain_id'], ['host',], ['website_id', 'last_datetime', 'datetime', 'last_audit_datetime', 'host', 'total_audits', 'total_archived_audits', 'total_issues', 'score'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_audit_datetime']));
        $filters->set_default_order_by($this->user->preferences->websites_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
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
                'domain_id' => (int) $row->domain_id,
                'scheme' => $row->scheme,
                'host' => $row->host,
                'score' => (int) $row->score,
                'total_audits' => (int) $row->total_audits,
                'total_archived_audits' => (int) $row->total_archived_audits,
                'total_issues' => (int) $row->total_issues,
                'major_issues' => (int) $row->major_issues,
                'moderate_issues' => (int) $row->moderate_issues,
                'minor_issues' => (int) $row->minor_issues,
                'directory_is_enabled_and_verified' => (int) $row->directory_is_enabled_and_verified,
                'last_audit_datetime' => $row->last_audit_datetime,
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
            'domain_id' => (int) $website->domain_id,
            'scheme' => $website->scheme,
            'host' => $website->host,
            'score' => (int) $website->score,
            'total_audits' => (int) $website->total_audits,
            'total_archived_audits' => (int) $website->total_archived_audits,
            'total_issues' => (int) $website->total_issues,
            'major_issues' => (int) $website->major_issues,
            'moderate_issues' => (int) $website->moderate_issues,
            'minor_issues' => (int) $website->minor_issues,
            'directory_is_enabled_and_verified' => (int) $website->directory_is_enabled_and_verified,
            'last_audit_datetime' => $website->last_audit_datetime,
            'last_datetime' => $website->last_datetime,
            'datetime' => $website->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function patch() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('websites', 'count(`domain_id`)');

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

        foreach(['notifications', 'settings'] as $key) $website->{$key} = json_decode($website->{$key} ?? '');

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
        $_POST['domain_id'] = isset($_POST['domain_id']) && array_key_exists($_POST['domain_id'], $domains) ? (int) $_POST['domain_id'] : $website->domain_id   ;
        $is_public = (int) (bool) ($_POST['is_public'] ?? $website->is_public);

        $_POST['audit_check_interval'] = isset($_POST['audit_check_interval']) && in_array($_POST['audit_check_interval'], array_merge([''], $this->user->plan_settings->audits_check_intervals ?? [])) ? (int) $_POST['audit_check_interval'] : $website->settings->audit_check_interval;
        $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
        $password = !empty($_POST['password']) ?
            ($_POST['password'] != $website->settings->password ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $website->settings->password)
            : $website->settings->password;

        /* Notification handlers */
        $_POST['notifications'] = array_map(
            'intval',
            array_filter($_POST['notifications'] ?? $website->notifications, function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
            $_POST['notifications'] = array_slice($_POST['notifications'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
        }

        /* Directory */
        $directory_is_enabled_and_verified = $website->directory_is_enabled_and_verified ?? 0;

        if(settings()->audits->directory_is_enabled) {
            $directory_is_enabled_and_verified =  $this->user->plan_settings->directory_is_enabled ? (int) (bool) ($_POST['directory_is_enabled_and_verified'] ?? $directory_is_enabled_and_verified) : 0;

            if($this->user->plan_settings->directory_is_enabled) {
                /* Verify domain */
                $dns_host = substr(get_slug(settings()->main->title, '_'), 0, 30) . '_verify';
                $dns_value = substr(get_slug(settings()->main->title, '_'), 0, 30) . '_token=' . md5($website->host . SITE_URL . $this->user->user_id);

                /* Get DNS records */
                $dns_fqdn = $dns_host . '.' . rtrim(strtolower($website->host), '.');
                $dns_records = dns_get_record($dns_fqdn, DNS_TXT) ?: [];

                $dns_is_verified = false;

                foreach ($dns_records as $dns_record) {
                    $txt_value = $dns_record['txt'] ?? (isset($dns_record['entries']) ? implode('', $dns_record['entries']) : null);
                    $txt_value = $txt_value !== null ? trim($txt_value, " \t\n\r\0\x0B\"") : null;

                    if ($txt_value === $dns_value) {
                        $dns_is_verified = true;
                        break;
                    }
                }

                /* Require verification to enable listing */
                $directory_is_enabled_and_verified = (int)($directory_is_enabled_and_verified && $dns_is_verified);

                /* Error throwing if needed */
                if (isset($_POST['directory_is_enabled_and_verified']) && !$dns_is_verified) {
                    $this->response_error(l('websites.directory_is_enabled.directory.error_message'), 400);
                }
            }
        }

        /* Settings */
        $settings = [
            'is_public' => $is_public,
            'password' => $password,
            'audit_check_interval' => $_POST['audit_check_interval'],
        ];

        /* Notification handlers */
        $notifications = json_encode($_POST['notifications']);

        /* Database query */
        db()->where('website_id', $website->website_id)->update('websites', [
            'domain_id' => $_POST['domain_id'],
            'settings' => json_encode($settings),
            'notifications' => $notifications,
            'directory_is_enabled_and_verified' => $directory_is_enabled_and_verified,
            'last_datetime' => get_date(),
        ]);

        /* Update all audits within the website */
        db()->where('website_id', $website->website_id)->update('audits', [
            'domain_id' => $_POST['domain_id'],
            'settings' => json_encode($settings),
            'notifications' => $notifications,
            'last_datetime' => get_date(),
        ]);

        /* Prepare the data */
        $data = [
            'id' => (int) $website->website_id,
            'user_id' => (int) $website->user_id,
            'domain_id' => (int) $_POST['domain_id'],
            'scheme' => $website->scheme,
            'host' => $website->host,
            'score' => (int) $website->score,
            'total_audits' => (int) $website->total_audits,
            'total_archived_audits' => (int) $website->total_archived_audits,
            'total_issues' => (int) $website->total_issues,
            'major_issues' => (int) $website->major_issues,
            'moderate_issues' => (int) $website->moderate_issues,
            'minor_issues' => (int) $website->minor_issues,
            'directory_is_enabled_and_verified' => (int) $directory_is_enabled_and_verified,
            'last_audit_datetime' => $website->last_audit_datetime,
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

        /* Delete the resource */
        db()->where('website_id', $website_id)->delete('websites');

        /* Clear the cache */
        cache()->deleteItem('audits_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('audits_dashboard?user_id=' . $this->user->user_id);
        cache()->deleteItem('websites_dashboard?user_id=' . $this->user->user_id);

        http_response_code(200);
        die();

    }
}
