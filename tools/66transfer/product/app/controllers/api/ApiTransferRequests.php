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

use Altum\Date;
use Altum\Response;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiTransferRequests extends Controller {
    use Apiable;

    public function index() {

        $this->verify_request();

        if(!settings()->transfers->transfer_requests_is_enabled) {
            $this->return_404();
        }

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
                    $this->create();
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
        $filters = (new \Altum\Filters(['domain_id', 'project_id', 'pixels_ids'], ['name', 'url'], ['transfer_request_id', 'expiration_datetime', 'last_datetime', 'last_submission_datetime', 'datetime', 'name', 'url', 'total_submissions', 'total_files', 'total_size'], [], ['pixels_ids' => 'json_contains'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_submission_datetime', 'expiration_datetime']));
        $filters->set_default_order_by('transfer_request_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `transfer_requests` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/transfer-requests?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `transfer_requests`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}

            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->transfer_request_id,
                'user_id' => (int) $row->user_id,
                'project_id' => (int) $row->project_id,
                'uploader_id' => $row->uploader_id,
                'pixels_ids' => json_decode($row->pixels_ids ?? ''),
                'name' => $row->name,
                'description' => $row->description,
                'url' => $row->url,
                'settings' => json_decode($row->settings ?? ''),
                'notifications' => json_decode($row->notifications ?? ''),
                'total_submissions' => (int) $row->total_submissions,
                'total_files' => (int) $row->total_files,
                'total_size' => (int) $row->total_size,
                'expiration_datetime' => $row->expiration_datetime,
                'last_submission_datetime' => $row->last_submission_datetime,
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

        $transfer_request_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $transfer_request = db()->where('transfer_request_id', $transfer_request_id)->where('user_id', $this->user->user_id)->getOne('transfer_requests');

        /* We haven't found the resource */
        if(!$transfer_request) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $transfer_request->transfer_request_id,
            'user_id' => (int) $transfer_request->user_id,
            'project_id' => (int) $transfer_request->project_id,
            'uploader_id' => $transfer_request->uploader_id,
            'pixels_ids' => json_decode($transfer_request->pixels_ids ?? ''),
            'name' => $transfer_request->name,
            'description' => $transfer_request->description,
            'url' => $transfer_request->url,
            'settings' => json_decode($transfer_request->settings ?? ''),
            'notifications' => json_decode($transfer_request->notifications ?? ''),
            'total_submissions' => (int) $transfer_request->total_submissions,
            'total_files' => (int) $transfer_request->total_files,
            'total_size' => (int) $transfer_request->total_size,
            'expiration_datetime' => $transfer_request->expiration_datetime,
            'last_submission_datetime' => $transfer_request->last_submission_datetime,
            'last_datetime' => $transfer_request->last_datetime,
            'datetime' => $transfer_request->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function create() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('transfer_requests', 'count(`transfer_request_id`)');

        if($this->user->plan_settings->transfer_requests_limit != -1 && $total_rows >= $this->user->plan_settings->transfer_requests_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->transfer_requests_limit + 1, mb_strtolower(l('transfer_requests.title')), l('global.info_message.plan_upgrade')), 401);
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user, false);

        /* Get available pixels */
        $pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        $_POST['name'] = input_clean($_POST['name'] ?? '', 64);
        $_POST['description'] = input_clean($_POST['description'] ?? '');
        $_POST['url'] = !empty($_POST['url']) && $this->user->plan_settings->custom_url_is_enabled ? get_slug(input_clean($_POST['url'])) : false;
        $_POST['domain_id'] = isset($_POST['domain_id']) && isset($domains[$_POST['domain_id']]) ? (!empty($_POST['domain_id']) ? (int) $_POST['domain_id'] : null) : null;
        $_POST['is_removed_branding'] = isset($_POST['is_removed_branding']) && $this->user->plan_settings->removable_branding_is_enabled ? (int) isset($_POST['is_removed_branding']) : 0;
        $_POST['custom_css'] = $this->user->plan_settings->custom_css_is_enabled ? mb_substr(trim($_POST['custom_css'] ?? $this->user->preferences->transfer_requests_default_custom_css ?? ''), 0, 10000) : null;
        $_POST['custom_js'] = $this->user->plan_settings->custom_js_is_enabled ? mb_substr(trim($_POST['custom_js'] ?? $this->user->preferences->transfer_requests_default_custom_js ?? ''), 0, 10000) : null;

        /* Check for required fields */
        $required_fields = ['name'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Check for duplicate url if needed */
        if($_POST['url']) {
            $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
            $is_existing_link = database()->query("SELECT `transfer_request_id` FROM `transfer_requests` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;

            if($is_existing_link) {
                $this->response_error(l('transfer.error_message.url_exists'), 401);
            }

            if(array_key_exists($_POST['url'], \Altum\Router::$routes['']) || in_array($_POST['url'], \Altum\Language::$active_languages) || file_exists(ROOT_PATH . $_POST['url'])) {
                $this->response_error(l('transfer.error_message.blacklisted_url'), 401);
            }

            if(in_array($_POST['url'], settings()->transfers->blacklisted_keywords)) {
                $this->response_error(l('transfer.error_message.blacklisted_keyword'), 401);
            }

            /* Make sure the custom url meets the requirements */
            if(mb_strlen($_POST['url']) < ($this->user->plan_settings->url_minimum_characters ?? 1)) {
                $this->response_error(sprintf(l('transfer.error_message.url_minimum_characters'), ($this->user->plan_settings->url_minimum_characters ?? 1)), 401);
            }

            if(mb_strlen($_POST['url']) > ($this->user->plan_settings->url_maximum_characters ?? 64)) {
                $this->response_error(sprintf(l('transfer.error_message.url_maximum_characters'), ($this->user->plan_settings->url_maximum_characters ?? 64)), 401);
            }
        }

        /* Generate a random URL */
        if(!$_POST['url']) {
            $is_existing_link = true;

            while($is_existing_link) {
                $_POST['url'] = mb_strtolower(string_generate(settings()->transfers->random_url_length ?? 7));

                $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
                $is_existing_link = database()->query("SELECT `transfer_request_id` FROM `transfer_requests` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;
            }
        }

        /* Expiration datetime */
        if(!isset($_POST['expiration_datetime'])) {
            $_POST['expiration_datetime'] = $this->user->preferences->transfer_requests_default_expiration_datetime ? (new \DateTime())->modify('+' . $this->user->preferences->transfer_requests_default_expiration_datetime . ' days')->format('Y-m-d H:i:s') : null;
        }
        $_POST['expiration_datetime'] = empty($_POST['expiration_datetime']) ? null : $_POST['expiration_datetime'];

        if($this->user->plan_settings->transfers_retention == -1) {
            $expiration_datetime_object = null;
            $expiration_datetime = null;
        } else {
            $expiration_datetime_object = (new \DateTime())->modify('+' . $this->user->plan_settings->transfers_retention . ' days');
            $expiration_datetime = $expiration_datetime_object->format('Y-m-d H:i:s');
        }

        /* Make sure posted expiration datetime is between allowed dates */
        if(!is_null($_POST['expiration_datetime'])) {
            $posted_expiration_datetime_object = (new \DateTime($_POST['expiration_datetime'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone));

            if(
                $posted_expiration_datetime_object > (new \DateTime())
                && (is_null($expiration_datetime_object) || $posted_expiration_datetime_object < $expiration_datetime_object)
            ) {
                $expiration_datetime = $posted_expiration_datetime_object->format('Y-m-d H:i:s');
            }
        }

        /* Projects */
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : ($this->user->preferences->transfer_requests_default_project_id ?: null);

        /* Pixels */
        $_POST['pixels_ids'] = isset($_POST['pixels_ids']) ? array_map('intval', array_filter($_POST['pixels_ids'], function($pixel_id) use($pixels) {
            return array_key_exists($pixel_id, $pixels);
        })) : ($this->user->preferences->transfer_requests_default_pixels_ids ?? []);
        $_POST['pixels_ids'] = json_encode($_POST['pixels_ids']);

        /* Notification handlers */
        $_POST['submission_notification_handlers_ids'] = array_map('intval', array_filter($_POST['submission_notification_handlers_ids'] ?? ($this->user->preferences->transfer_requests_default_submission_notification_handlers_ids ?? []), fn($notification_handler_id) => isset($notification_handlers[$notification_handler_id])));
        $_POST['pageview_notification_handlers_ids'] = array_map('intval', array_filter($_POST['pageview_notification_handlers_ids'] ?? ($this->user->preferences->transfer_requests_default_pageview_notification_handlers_ids ?? []), fn($notification_handler_id) => isset($notification_handlers[$notification_handler_id])));

        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
            $_POST['submission_notification_handlers_ids'] = array_slice($_POST['submission_notification_handlers_ids'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
            $_POST['pageview_notification_handlers_ids'] = array_slice($_POST['pageview_notification_handlers_ids'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
        }

        /* Password */
        $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
        $_POST['password'] = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

        /* Prepare settings */
        $settings = json_encode([
            'password' => $_POST['password'],
            'is_removed_branding' => $_POST['is_removed_branding'],
            'custom_css' => $_POST['custom_css'],
            'custom_js' => $_POST['custom_js'],
        ]);

        $notifications = json_encode([
            'submission' => $_POST['submission_notification_handlers_ids'],
            'pageview' => $_POST['pageview_notification_handlers_ids'],
        ]);

        /* Database query */
        $transfer_request_id = db()->insert('transfer_requests', [
            'user_id' => $this->user->user_id,
            'uploader_id' => md5(get_ip()),
            'domain_id' => $_POST['domain_id'],
            'project_id' => $_POST['project_id'],
            'pixels_ids' => $_POST['pixels_ids'],
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'url' => $_POST['url'],
            'settings' => $settings,
            'notifications' => $notifications,
            'total_submissions' => 0,
            'total_files' => 0,
            'total_size' => 0,
            'expiration_datetime' => $expiration_datetime,
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('transfer_requests_total?user_id=' . $this->user->user_id);
        cache()->deleteItemsByTag('transfer_requests?user_id=' . $this->user->user_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $transfer_request_id,
            'user_id' => (int) $this->user->user_id,
            'project_id' => (int) $_POST['project_id'],
            'uploader_id' => md5(get_ip()),
            'pixels_ids' => json_decode($_POST['pixels_ids'] ?? ''),
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'url' => $_POST['url'],
            'settings' => json_decode($settings ?? ''),
            'notifications' => json_decode($notifications ?? ''),
            'total_submissions' => 0,
            'total_files' => 0,
            'total_size' => 0,
            'expiration_datetime' => $expiration_datetime,
            'last_submission_datetime' => null,
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data);

    }

    private function patch() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('transfer_requests', 'count(`transfer_request_id`)');

        if($this->user->plan_settings->transfer_requests_limit != -1 && $total_rows > $this->user->plan_settings->transfer_requests_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->transfer_requests_limit, mb_strtolower(l('transfer_requests.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $transfer_request_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $transfer_request = db()->where('transfer_request_id', $transfer_request_id)->where('user_id', $this->user->user_id)->getOne('transfer_requests');

        /* We haven't found the resource */
        if(!$transfer_request) {
            $this->return_404();
        }

        $transfer_request->settings = json_decode($transfer_request->settings ?? '');
        $transfer_request->pixels_ids = json_decode($transfer_request->pixels_ids);
        $transfer_request->notifications = json_decode($transfer_request->notifications);

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available pixels */
        $pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user, false);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        $_POST['name'] = input_clean($_POST['name'] ?? $transfer_request->name, 64);
        $_POST['description'] = input_clean($_POST['description'] ?? $transfer_request->description);
        $_POST['url'] = !empty($_POST['url']) && $this->user->plan_settings->custom_url_is_enabled ? get_slug(input_clean($_POST['url'])) : $transfer_request->url;
        $_POST['domain_id'] = isset($_POST['domain_id']) && isset($domains[$_POST['domain_id']]) ? (!empty($_POST['domain_id']) ? (int) $_POST['domain_id'] : null) : $transfer_request->domain_id;
        $_POST['is_removed_branding'] = isset($_POST['is_removed_branding']) && $this->user->plan_settings->removable_branding_is_enabled ? (int) isset($_POST['is_removed_branding']) : $transfer_request->settings->is_removed_branding;
        $_POST['custom_css'] = isset($_POST['custom_css']) && $this->user->plan_settings->custom_css_is_enabled ? mb_substr(trim($_POST['custom_css']), 0, 10000) : $transfer_request->settings->custom_css;
        $_POST['custom_js'] = isset($_POST['custom_js']) && $this->user->plan_settings->custom_js_is_enabled ? mb_substr(trim($_POST['custom_js']), 0, 10000) : $transfer_request->settings->custom_js;

        /* Check for required fields */
        $required_fields = ['name'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Check for duplicate url if needed */
        if(
            ($_POST['url'] && $this->user->plan_settings->custom_url_is_enabled && $_POST['url'] != $transfer_request->url)
            || ($transfer_request->domain_id != $_POST['domain_id'])
        ) {
            $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
            $is_existing_link = database()->query("SELECT `transfer_request_id` FROM `transfer_requests` WHERE `url` = '{$_POST['url']}' {$domain_id_where} AND `transfer_request_id` != {$transfer_request->transfer_request_id}")->num_rows;

            if($is_existing_link) {
                $this->response_error(l('transfer.error_message.url_exists'), 401);
            }

            if(array_key_exists($_POST['url'], \Altum\Router::$routes['']) || in_array($_POST['url'], \Altum\Language::$active_languages) || file_exists(ROOT_PATH . $_POST['url'])) {
                $this->response_error(l('transfer.error_message.blacklisted_url'), 401);
            }

            if(in_array($_POST['url'], settings()->transfers->blacklisted_keywords)) {
                $this->response_error(l('transfer.error_message.blacklisted_keyword'), 401);
            }

            /* Make sure the custom url meets the requirements */
            if(mb_strlen($_POST['url']) < ($this->user->plan_settings->url_minimum_characters ?? 1)) {
                $this->response_error(sprintf(l('transfer.error_message.url_minimum_characters'), ($this->user->plan_settings->url_minimum_characters ?? 1)), 401);
            }

            if(mb_strlen($_POST['url']) > ($this->user->plan_settings->url_maximum_characters ?? 64)) {
                $this->response_error(sprintf(l('transfer.error_message.url_maximum_characters'), ($this->user->plan_settings->url_maximum_characters ?? 64)), 401);
            }
        }

        /* Generate a random URL */
        if(!$_POST['url']) {
            $is_existing_link = true;

            while($is_existing_link) {
                $_POST['url'] = mb_strtolower(string_generate(settings()->transfers->random_url_length ?? 7));

                $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
                $is_existing_link = database()->query("SELECT `transfer_request_id` FROM `transfer_requests` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;
            }
        }

        /* Expiration datetime */
        if(!isset($_POST['expiration_datetime'])) {
            $_POST['expiration_datetime'] = $transfer_request->expiration_datetime;
        }
        $_POST['expiration_datetime'] = empty($_POST['expiration_datetime']) ? null : $_POST['expiration_datetime'];

        if($this->user->plan_settings->transfers_retention == -1) {
            $expiration_datetime_object = null;
            $expiration_datetime = null;
        } else {
            $expiration_datetime_object = (new \DateTime())->modify('+' . $this->user->plan_settings->transfers_retention . ' days');
            $expiration_datetime = $expiration_datetime_object->format('Y-m-d H:i:s');
        }

        /* Make sure posted expiration datetime is between allowed dates */
        if(!is_null($_POST['expiration_datetime'])) {
            $posted_expiration_datetime_object = (new \DateTime($_POST['expiration_datetime'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone));

            if(
                $posted_expiration_datetime_object > (new \DateTime())
                && (is_null($expiration_datetime_object) || $posted_expiration_datetime_object < $expiration_datetime_object)
            ) {
                $expiration_datetime = $posted_expiration_datetime_object->format('Y-m-d H:i:s');
            }
        }

        /* Projects */
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : $transfer_request->project_id;

        /* Pixels */
        $_POST['pixels_ids'] = isset($_POST['pixels_ids']) ? array_map('intval', array_filter($_POST['pixels_ids'], function($pixel_id) use($pixels) {
            return array_key_exists($pixel_id, $pixels);
        })) : $transfer_request->pixels_ids;
        $_POST['pixels_ids'] = json_encode($_POST['pixels_ids']);

        /* Notification handlers */
        $_POST['submission_notification_handlers_ids'] = array_map('intval', array_filter($_POST['submission_notification_handlers_ids'] ?? $transfer_request->notifications->submission ?? [], fn($notification_handler_id) => isset($notification_handlers[$notification_handler_id])));
        $_POST['pageview_notification_handlers_ids'] = array_map('intval', array_filter($_POST['pageview_notification_handlers_ids'] ?? $transfer_request->notifications->pageview ?? [], fn($notification_handler_id) => isset($notification_handlers[$notification_handler_id])));

        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
            $_POST['submission_notification_handlers_ids'] = array_slice($_POST['submission_notification_handlers_ids'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
            $_POST['pageview_notification_handlers_ids'] = array_slice($_POST['pageview_notification_handlers_ids'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
        }

        /* Password */
        if(array_key_exists('password', $_POST)) {
            $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
            $_POST['password'] = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        } else {
            $_POST['password'] = $transfer_request->settings->password;
        }

        /* Prepare settings */
        $settings = json_encode([
            'password' => $_POST['password'],
            'is_removed_branding' => $_POST['is_removed_branding'],
            'custom_css' => $_POST['custom_css'],
            'custom_js' => $_POST['custom_js'],
        ]);

        $notifications = json_encode([
            'submission' => $_POST['submission_notification_handlers_ids'],
            'pageview' => $_POST['pageview_notification_handlers_ids'],
        ]);

        /* Database query */
        db()->where('transfer_request_id', $transfer_request->transfer_request_id)->update('transfer_requests', [
            'domain_id' => $_POST['domain_id'],
            'project_id' => $_POST['project_id'],
            'pixels_ids' => $_POST['pixels_ids'],
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'url' => $_POST['url'],
            'settings' => $settings,
            'notifications' => $notifications,
            'expiration_datetime' => $expiration_datetime,
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('transfer_requests_total?user_id=' . $this->user->user_id);
        cache()->deleteItemsByTag('transfer_requests?user_id=' . $this->user->user_id);
        cache()->deleteItemsByTag('transfer_request_id=' . $transfer_request->transfer_request_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $transfer_request->transfer_request_id,
            'user_id' => (int) $transfer_request->user_id,
            'project_id' => (int) $_POST['project_id'],
            'uploader_id' => $transfer_request->uploader_id,
            'pixels_ids' => json_decode($_POST['pixels_ids'] ?? ''),
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'url' => $_POST['url'],
            'settings' => json_decode($settings ?? ''),
            'notifications' => json_decode($notifications ?? ''),
            'total_submissions' => (int) $transfer_request->total_submissions,
            'total_files' => (int) $transfer_request->total_files,
            'total_size' => (int) $transfer_request->total_size,
            'expiration_datetime' => $expiration_datetime,
            'last_submission_datetime' => $transfer_request->last_submission_datetime,
            'last_datetime' => get_date(),
            'datetime' => $transfer_request->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function delete() {

        $transfer_request_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $transfer_request = db()->where('transfer_request_id', $transfer_request_id)->where('user_id', $this->user->user_id)->getOne('transfer_requests');

        /* We haven't found the resource */
        if(!$transfer_request) {
            $this->return_404();
        }

        /* Delete transfer request */
        (new \Altum\Models\TransferRequests())->delete($transfer_request->transfer_request_id, $this->user->user_id);

        http_response_code(200);
        die();

    }

}
