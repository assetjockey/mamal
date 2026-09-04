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

class ApiTransfers extends Controller {
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
        $filters = (new \Altum\Filters(['domain_id', 'project_id', 'pixels_ids'], ['name', 'url'], ['transfer_id', 'expiration_datetime', 'last_datetime', 'datetime', 'name', 'url', 'pageviews', 'downloads', 'downloads_limit', 'total_files', 'total_size'], [], ['pixels_ids' => 'json_contains'], allowed_datetime_fields: ['datetime', 'last_datetime', 'expiration_datetime']));
        $filters->set_default_order_by('transfer_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `transfers` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/transfers?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `transfers`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Get all files */
            $files = db()->where('transfer_id', $row->transfer_id)->get('files', null, ['file_id']);
            $files_ids = array_map(function($file) { return $file->file_id; }, $files);

            /* Prepare the data */
            $row = [
                'id' => (int) $row->transfer_id,
                'user_id' => (int) $row->user_id,
                'project_id' => (int) $row->project_id,
                'uploader_id' => $row->uploader_id,
                'pixels_ids' => json_decode($row->pixels_ids ?? ''),
                'files_ids' => $files_ids,
                'name' => $row->name,
                'description' => $row->description,
                'type' => $row->type,
                'email_to' => $row->email_to,
                'url' => $row->url,
                'settings' => json_decode($row->settings ?? ''),
                'notifications' => json_decode($row->notifications ?? ''),
                'total_files' => (int) $row->total_files,
                'total_size' => (int) $row->total_size,
                'pageviews' => (int) $row->pageviews,
                'downloads' => (int) $row->downloads,
                'downloads_limit' => (int) $row->downloads_limit,
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

        $transfer_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $transfer = db()->where('transfer_id', $transfer_id)->where('user_id', $this->user->user_id)->getOne('transfers');

        /* We haven't found the resource */
        if(!$transfer) {
            $this->return_404();
        }

        /* Get all files */
        $files = db()->where('transfer_id', $transfer_id)->get('files', null, ['file_id']);
        $files_ids = array_map(function($file) { return $file->file_id; }, $files);

        /* Prepare the data */
        $data = [
            'id' => (int) $transfer->transfer_id,
            'user_id' => (int) $transfer->user_id,
            'project_id' => (int) $transfer->project_id,
            'uploader_id' => $transfer->uploader_id,
            'pixels_ids' => json_decode($transfer->pixels_ids ?? ''),
            'files_ids' => $files_ids,
            'name' => $transfer->name,
            'description' => $transfer->description,
            'type' => $transfer->type,
            'email_to' => $transfer->email_to,
            'url' => $transfer->url,
            'settings' => json_decode($transfer->settings ?? ''),
            'notifications' => json_decode($transfer->notifications ?? ''),
            'total_files' => (int) $transfer->total_files,
            'total_size' => (int) $transfer->total_size,
            'pageviews' => (int) $transfer->pageviews,
            'downloads' => (int) $transfer->downloads,
            'downloads_limit' => (int) $transfer->downloads_limit,
            'expiration_datetime' => $transfer->expiration_datetime,
            'last_datetime' => $transfer->last_datetime,
            'datetime' => $transfer->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function patch() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('transfers', 'count(`transfer_id`)');

        if($this->user->plan_settings->transfers_limit != -1 && $total_rows > $this->user->plan_settings->transfers_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->transfers_limit, mb_strtolower(l('transfers.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $transfer_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $transfer = db()->where('transfer_id', $transfer_id)->where('user_id', $this->user->user_id)->getOne('transfers');

        /* We haven't found the resource */
        if(!$transfer) {
            $this->return_404();
        }

        $transfer->settings = json_decode($transfer->settings ?? '');
        $transfer->pixels_ids = json_decode($transfer->pixels_ids);
        $transfer->notifications = json_decode($transfer->notifications);

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available pixels */
        $pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        $_POST['name'] = input_clean($_POST['name'] ?? $transfer->name);
        $_POST['description'] = input_clean($_POST['description'] ?? $transfer->description);
        $_POST['url'] = !empty($_POST['url']) ? get_slug(input_clean($_POST['url'])) : $transfer->url;
        $_POST['domain_id'] = isset($_POST['domain_id']) && isset($domains[$_POST['domain_id']]) ? (!empty($_POST['domain_id']) ? (int) $_POST['domain_id'] : null) : $transfer->domain_id;
        $_POST['is_removed_branding'] = isset($_POST['is_removed_branding']) && $this->user->plan_settings->removable_branding_is_enabled ? (int) isset($_POST['is_removed_branding']) : $transfer->settings->is_removed_branding;
        $_POST['custom_css'] = isset($_POST['custom_css']) && $this->user->plan_settings->custom_css_is_enabled ? mb_substr(trim($_POST['custom_css']), 0, 10000) : $transfer->settings->custom_css;
        $_POST['custom_js'] = isset($_POST['custom_js']) && $this->user->plan_settings->custom_js_is_enabled ? mb_substr(trim($_POST['custom_js']), 0, 10000) : $transfer->settings->custom_js;

        /* Check for duplicate url if needed */
        if(
            ($_POST['url'] && $this->user->plan_settings->custom_url_is_enabled && $_POST['url'] != $transfer->url)
            || ($transfer->domain_id != $_POST['domain_id'])
        ) {
            $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
            $is_existing_link = database()->query("SELECT `transfer_id` FROM `transfers` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;

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
                $this->response_error(sprintf(l('transfer.error_message.url_maximum_characters'), ($this->user->plan_settings->url_maximum_characters ?? 1)), 401);
            }
        }

        /* Generate a random URL */
        if(!$_POST['url']) {
            $is_existing_link = true;

            while($is_existing_link) {
                $_POST['url'] = mb_strtolower(string_generate(settings()->transfers->random_url_length ?? 7));

                $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
                $is_existing_link = database()->query("SELECT `transfer_id` FROM `transfers` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;
            }
        }

        /* Downloads limit */
        if(!array_key_exists('downloads_limit', $_POST)) {
            $_POST['downloads_limit'] = $transfer->downloads_limit;
        }

        $_POST['downloads_limit'] = $_POST['downloads_limit'] === '' ? null : (int) $_POST['downloads_limit'];

        $downloads_limit = $this->user->plan_settings->downloads_per_transfer_limit == -1 ? null : $this->user->plan_settings->downloads_per_transfer_limit;

        if(is_numeric($_POST['downloads_limit']) && (!$downloads_limit || $_POST['downloads_limit'] < $downloads_limit)) {
            $downloads_limit = $_POST['downloads_limit'];
        }

        /* Expiration datetime */
        if(!isset($_POST['expiration_datetime'])) {
            $_POST['expiration_datetime'] = $this->user->preferences->transfers_default_expiration_datetime ? (new \DateTime())->modify('+' . $this->user->preferences->transfers_default_expiration_datetime . ' days')->format('Y-m-d H:i:s') : null;
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
            $posted_expiration_datetime_object = (new \DateTime($_POST['expiration_datetime'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(Date::$default_timezone));
            if(
                $posted_expiration_datetime_object > (new \DateTime())
                && (is_null($expiration_datetime_object) || $posted_expiration_datetime_object < $expiration_datetime_object)
            ) {
                $expiration_datetime = $posted_expiration_datetime_object->format('Y-m-d H:i:s');
            }
        }

        /* Projects */
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : $transfer->project_id;

        /* Pixels */
        $_POST['pixels_ids'] = isset($_POST['pixels_ids']) ? array_map(
            'intval',
            array_filter($_POST['pixels_ids'], function($pixel_id) use($pixels) {
                return array_key_exists($pixel_id, $pixels);
            })
        ) : $transfer->pixels_ids;
        $_POST['pixels_ids'] = json_encode($_POST['pixels_ids']);

        /* Notification handlers */
		$_POST['download_notification_handlers_ids'] = array_map('intval', array_filter($_POST['download_notification_handlers_ids'] ?? $transfer->notifications->download, fn($notification_handler_id) => isset($notification_handlers[$notification_handler_id])));
		$_POST['pageview_notification_handlers_ids'] = array_map('intval', array_filter($_POST['pageview_notification_handlers_ids'] ?? $transfer->notifications->pageview, fn($notification_handler_id) => isset($notification_handlers[$notification_handler_id])));

        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
			$_POST['download_notification_handlers_ids'] = array_slice($_POST['download_notification_handlers_ids'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
			$_POST['pageview_notification_handlers_ids'] = array_slice($_POST['pageview_notification_handlers_ids'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
        }

        /* File preview */
        $_POST['file_preview_is_enabled'] = isset($_POST['file_preview_is_enabled']) ? (int) isset($_POST['file_preview_is_enabled']) : $transfer->settings->file_preview_is_enabled;
        $_POST['gallery_file_preview_is_enabled'] = isset($_POST['gallery_file_preview_is_enabled']) ? (int) isset($_POST['gallery_file_preview_is_enabled']) : $transfer->settings->gallery_file_preview_is_enabled;

        /* Prepare settings */
        $settings = json_encode([
            'password' => $transfer->settings->password,
            'file_encryption_is_enabled' => $transfer->settings->file_encryption_is_enabled,
            'file_preview_is_enabled' => $_POST['file_preview_is_enabled'],
            'gallery_file_preview_is_enabled' => $_POST['gallery_file_preview_is_enabled'],
            'is_removed_branding' => $_POST['is_removed_branding'],
            'custom_css' => $_POST['custom_css'],
            'custom_js' => $_POST['custom_js'],
        ]);

        $notifications = json_encode([
            'download' => $_POST['download_notification_handlers_ids'],
			'pageview' => $_POST['pageview_notification_handlers_ids'],
        ]);

        /* Database query */
        db()->where('transfer_id', $transfer->transfer_id)->update('transfers', [
            'domain_id' => $_POST['domain_id'],
            'project_id' => $_POST['project_id'],
            'pixels_ids' => $_POST['pixels_ids'],
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'url' => $_POST['url'],
            'settings' => $settings,
            'notifications' => $notifications,
            'downloads_limit' => $downloads_limit,
            'expiration_datetime' => $expiration_datetime,
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('transfer_id=' . $transfer->transfer_id);

        /* Prepare the data */
        $data = [
            'id' => $transfer->transfer_id
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $transfer_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $transfer = db()->where('transfer_id', $transfer_id)->where('user_id', $this->user->user_id)->getOne('transfers');

        /* We haven't found the resource */
        if(!$transfer) {
            $this->return_404();
        }

        /* Delete uploaded transfer */
        (new \Altum\Models\Transfers())->delete($transfer->transfer_id, $this->user->user_id);

        http_response_code(200);
        die();

    }

}
