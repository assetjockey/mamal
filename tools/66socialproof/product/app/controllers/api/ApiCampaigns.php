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

class ApiCampaigns extends Controller {
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
        $filters = (new \Altum\Filters(['is_enabled'], ['name', 'domain', 'domain_id'], ['campaign_id', 'last_datetime', 'datetime', 'name', 'domain'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->campaigns_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `campaigns` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/campaigns?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `campaigns`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->campaign_id,
                'user_id' => (int) $row->user_id,
                'pixel_key' => $row->pixel_key,
                'name' => $row->name,
                'domain' => $row->domain,
                'branding' => json_decode($row->branding ?? ''),
                'email_reports_is_enabled' => (bool) $row->email_reports_is_enabled,
                'email_reports_last_datetime' => $row->email_reports_last_datetime,
                'is_enabled' => (bool) $row->is_enabled,
                'last_datetime' => $row->last_datetime,
                'datetime' => $row->datetime
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

        /* Prepare the pagination campaigns */
        $others = ['campaigns' => [
            'first' => $paginator->getPageUrl(1),
            'last' => $paginator->getNumPages() ? $paginator->getPageUrl($paginator->getNumPages()) : null,
            'next' => $paginator->getNextUrl(),
            'prev' => $paginator->getPrevUrl(),
            'self' => $paginator->getPageUrl($_GET['page'] ?? 1)
        ]];

        Response::jsonapi_success($data, $meta, 200, $others);
    }

    private function get() {

        $campaign_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $campaign = db()->where('campaign_id', $campaign_id)->where('user_id', $this->user->user_id)->getOne('campaigns');

        /* We haven't found the resource */
        if(!$campaign) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $campaign->campaign_id,
            'user_id' => (int) $campaign->user_id,
            'pixel_key' => $campaign->pixel_key,
            'name' => $campaign->name,
            'domain' => $campaign->domain,
            'branding' => json_decode($campaign->branding ?? ''),
            'email_reports_is_enabled' => (bool) $campaign->email_reports_is_enabled,
            'email_reports_last_datetime' => $campaign->email_reports_last_datetime,
            'is_enabled' => (bool) $campaign->is_enabled,
            'last_datetime' => $campaign->last_datetime,
            'datetime' => $campaign->datetime
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for any errors */
        $required_fields = ['name', 'domain'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('campaigns', 'count(`campaign_id`)');
        if($this->user->plan_settings->campaigns_limit != -1 && $total_rows >= $this->user->plan_settings->campaigns_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        $_POST['name'] = input_clean($_POST['name'], 256);
        $_POST['branding_name'] = input_clean($_POST['branding_name'] ?? '', 128);
        $_POST['branding_url'] = get_url($_POST['branding_url'] ?? '');
        $_POST['is_enabled'] = isset($_POST['is_enabled']) ? (int) (bool) $_POST['is_enabled'] : 1;

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        $_POST['email_reports'] = array_map(
            'intval',
            array_filter($_POST['email_reports'] ?? [], function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
            $_POST['email_reports'] = array_slice($_POST['email_reports'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
        }
        if(!$this->user->plan_settings->email_reports_is_enabled) {
            $_POST['email_reports'] = [];
        }

        /* Get available custom domains */
        $domain_id = null;
        if(isset($_POST['domain_id'])) {
            $domain = (new \Altum\Models\Domain())->get_domain_by_domain_id($_POST['domain_id']);

            if($domain && $domain->user_id == $this->user->user_id) {
                $domain_id = $domain->domain_id;
            }
        }

        /* Domain checking */
        $_POST['domain'] = mb_strtolower(input_clean($_POST['domain']));

        if(string_starts_with('http://', $_POST['domain']) || string_starts_with('https://', $_POST['domain'])) {
            if(function_exists('idn_to_utf8')) {
                $_POST['domain'] = parse_url(idn_to_utf8($_POST['domain']), PHP_URL_HOST);
            }
        } else {
            if(function_exists('idn_to_utf8')) {
                $_POST['domain'] = parse_url(idn_to_utf8('https://' . $_POST['domain']), PHP_URL_HOST);
            }
        }

        if(function_exists('idn_to_ascii')) {
            $_POST['domain'] = idn_to_ascii($_POST['domain']);
        }

        if(in_array($_POST['domain'], settings()->notifications->blacklisted_domains)) {
            $this->response_error(l('campaigns.error_message.blacklisted_domain'));
        }

        /* Generate a unique pixel key for the website */
        $pixel_key = string_generate(32);
        while(db()->where('pixel_key', $pixel_key)->getValue('campaigns', 'pixel_key')) {
            $pixel_key = string_generate(32);
        }

        /* Branding */
        $branding = json_encode([
            'name' => $_POST['branding_name'],
            'url'   => $_POST['branding_url']
        ]);

        /* Database query */
        $campaign_id = db()->insert('campaigns', [
            'user_id' => $this->user->user_id,
            'domain_id' => $domain_id,
            'pixel_key' => $pixel_key,
            'name' => $_POST['name'],
            'domain' => $_POST['domain'],
            'branding' => $branding,
            'is_enabled' => $_POST['is_enabled'],
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('campaign_id=' . $campaign_id);
        cache()->deleteItem('campaigns_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('notifications_total?user_id=' . $this->user->user_id);

        /* Send webhook notification if needed */
        if(settings()->webhooks->campaign_new) {
            fire_and_forget('post', settings()->webhooks->campaign_new, [
                'user_id' => $this->user->user_id,
                'campaign_id' => $campaign_id,
                'domain_id' => $domain_id,
                'domain' => $_POST['domain'],
                'datetime' => get_date(),
            ], signature: true);
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $campaign_id,
            'user_id' => (int) $this->user->user_id,
            'pixel_key' => $pixel_key,
            'name' => $_POST['name'],
            'domain' => $_POST['domain'],
            'branding' => json_decode($branding ?? ''),
            'email_reports_is_enabled' => false,
            'email_reports_last_datetime' => null,
            'is_enabled' => (bool) $_POST['is_enabled'],
            'last_datetime' => null,
            'datetime' => get_date()
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $campaign_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $campaign = db()->where('campaign_id', $campaign_id)->where('user_id', $this->user->user_id)->getOne('campaigns');

        /* We haven't found the resource */
        if(!$campaign) {
            $this->return_404();
        }

        $campaign->branding = json_decode($campaign->branding ?? '');
        $campaign->email_reports = json_decode($campaign->email_reports ?? '');

        $_POST['name'] = input_clean($_POST['name'] ?? $campaign->name, 256);
        $_POST['branding_name'] = input_clean($_POST['branding_name'] ?? $campaign->branding->name, 128);
        $_POST['branding_url'] = get_url($_POST['branding_url'] ?? $campaign->branding->url);
        $_POST['is_enabled'] = isset($_POST['is_enabled']) ? (int) $_POST['is_enabled'] : $campaign->is_enabled;

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        $_POST['email_reports'] = array_map(
            'intval',
            array_filter($_POST['email_reports'] ?? $campaign->email_reports, function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
            $_POST['email_reports'] = array_slice($_POST['email_reports'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
        }
        if(!$this->user->plan_settings->email_reports_is_enabled) {
            $_POST['email_reports'] = [];
        }

        /* Get available custom domains */
        $domain_id = $campaign->domain_id;
        if(isset($_POST['domain_id'])) {
            $domain = (new \Altum\Models\Domain())->get_domain_by_domain_id($_POST['domain_id']);

            if($domain && $domain->user_id == $this->user->user_id) {
                $domain_id = $domain->domain_id;
            }
        }

        /* Domain checking */
        $_POST['domain'] = mb_strtolower(input_clean($_POST['domain'] ?? $campaign->domain));

        if(string_starts_with('http://', $_POST['domain']) || string_starts_with('https://', $_POST['domain'])) {
            if(function_exists('idn_to_utf8')) {
                $_POST['domain'] = parse_url(idn_to_utf8($_POST['domain']), PHP_URL_HOST);
            }
        } else {
            if(function_exists('idn_to_utf8')) {
                $_POST['domain'] = parse_url(idn_to_utf8('https://' . $_POST['domain']), PHP_URL_HOST);
            }
        }

        if(function_exists('idn_to_ascii')) {
            $_POST['domain'] = idn_to_ascii($_POST['domain']);
        }

        if(in_array($_POST['domain'], settings()->notifications->blacklisted_domains)) {
            $this->response_error(l('campaigns.error_message.blacklisted_domain'));
        }

        /* Branding */
        $branding = json_encode([
            'name' => $_POST['branding_name'],
            'url'   => $_POST['branding_url']
        ]);

        /* Database query */
        db()->where('campaign_id', $campaign->campaign_id)->update('campaigns', [
            'domain_id' => $domain_id,
            'name' => $_POST['name'],
            'domain' => $_POST['domain'],
            'branding' => $branding,
            'is_enabled' => $_POST['is_enabled'],
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('campaign_id=' . $campaign_id);
        cache()->deleteItem('campaigns_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('notifications_total?user_id=' . $this->user->user_id);

        /* Send webhook notification if needed */
        if(settings()->webhooks->campaign_update) {
            fire_and_forget('post', settings()->webhooks->campaign_update, [
                'user_id' => $this->user->user_id,
                'campaign_id' => $campaign_id,
                'domain_id' => $domain_id,
                'domain' => $_POST['domain'],
                'datetime' => get_date(),
            ], signature: true);
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $campaign->campaign_id,
            'user_id' => (int) $campaign->user_id,
            'pixel_key' => $campaign->pixel_key,
            'name' => $_POST['name'],
            'domain' => $_POST['domain'],
            'branding' => json_decode($branding ?? ''),
            'email_reports_is_enabled' => (bool) $campaign->email_reports_is_enabled,
            'email_reports_last_datetime' => $campaign->email_reports_last_datetime,
            'is_enabled' => (bool) $_POST['is_enabled'],
            'last_datetime' => get_date(),
            'datetime' => $campaign->datetime
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $campaign_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $campaign = db()->where('campaign_id', $campaign_id)->where('user_id', $this->user->user_id)->getOne('campaigns');

        /* We haven't found the resource */
        if(!$campaign) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('campaign_id', $campaign_id)->delete('campaigns');

        /* Clear the cache */
        cache()->deleteItemsByTag('campaign_id=' . $campaign_id);
        cache()->deleteItem('campaigns_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('notifications_total?user_id=' . $this->user->user_id);

        http_response_code(200);
        die();

    }
}
