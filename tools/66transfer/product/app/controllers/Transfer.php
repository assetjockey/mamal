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
use Altum\Captcha;
use Altum\Csrf;
use Altum\Date;
use Altum\Response;
use Altum\Title;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class Transfer extends Controller {
    use Apiable;

    public function index() {
        $transfer_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Get transfer details */
        if(!$transfer = db()->where('transfer_id', $transfer_id)->getOne('transfers')) {
            throw_404();
        }

        /* Make sure the current user has access */
        if(($transfer->uploader_id != md5(get_ip())) && (!$transfer->user_id || $transfer->user_id != $this->user->user_id)) {
            throw_404();
        }

        /* Generate the transfer full URL base */
        $transfer->full_url = (new \Altum\Models\Transfers())->get_transfer_full_url($transfer, $this->user);

        $transfer->settings = json_decode($transfer->settings ?? '');

        /* Get the files */
        $files = (new \Altum\Models\Files())->get_files_by_transfer_id($transfer->transfer_id);

        /* File stats */
        $files_stats = [
            'total_size' => 0,
            'total_files' => 0,
        ];

        foreach($files as $file) {
            $files_stats['total_size'] += $file->size;
            $files_stats['total_files']++;
        }

        $statistics = [];
        if($transfer->pageviews) {
            $statistics = db()->orderBy('transfer_id', 'DESC')->where('transfer_id', $transfer->transfer_id)->get('statistics', 5);
        }

        $downloads = [];
        if($transfer->downloads) {
            $downloads = db()->orderBy('transfer_id', 'DESC')->where('transfer_id', $transfer->transfer_id)->get('downloads', 5);
        }

        /* Set a custom title */
        Title::set(sprintf(l('transfer.title'), $transfer->name));

        /* Set auto-delete if there are no files */
        if(!count($files) && !$transfer->expiration_datetime) {
            $new_expiration_datetime = (new \DateTime())->modify('+6 hours')->format('Y-m-d H:i:s');
            db()->where('transfer_id', $transfer_id)->update('transfers', [
                'expiration_datetime' => $new_expiration_datetime,
            ]);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('transfer.info_message.new_expiration_datetime'), '<strong>' . Date::get_time_until($new_expiration_datetime) . '</strong>'));
        }

        /* Initiate captcha */
        $captcha = new Captcha();

        /* Main View */
        $data = [
            'transfer' => $transfer,
            'files' => $files,
            'files_stats' => $files_stats,
            'statistics' => $statistics,
            'downloads' => $downloads,
            'captcha' => $captcha,
        ];

        $view = new \Altum\View('transfer/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }

    public function create_api() {

        set_time_limit(0);

        if(empty($_POST)) {
            throw_404();
        }

        /* Define the return content to be treated as JSON */
        header('Content-Type: application/json');

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.transfers')) {
            $this->response_error(l('global.info_message.team_no_access'), 401);
        }

        /* Get potential API key */
        $api_key = \Altum\Authentication::get_authorization_bearer();

        /* Check for the plan limit */
        if(is_logged_in()) {
            $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `transfers` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

            if($this->user->plan_settings->transfers_limit != -1 && $total_rows >= $this->user->plan_settings->transfers_limit) {
                $this->response_error(l('global.info_message.plan_feature_limit'), 401);
            }
        }

        /* API */
        elseif($api_key) {
            $this->user = db()->where('api_key', $api_key)->where('status', 1)->getOne('users');

            if(!$this->user) {
                $this->response_error(l('api.error_message.no_access'), 401);
            }

            $this->user->plan_settings = json_decode($this->user->plan_settings);
            $this->user->preferences = json_decode($this->user->preferences);

            if(!$this->user->plan_settings->api_is_enabled) {
                $this->response_error(l('api.error_message.no_access'), 401);
            }
        }

        /* Guest */
        else {
            if($this->user->plan_settings->transfers_limit == 0) {
                $this->response_error(l('global.info_message.plan_feature_limit'), 401);
            }
        }

        /* Check for required fields */
        $required_fields = [];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field])) {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        if(!$api_key) {
            if(!Csrf::check('global_token')) {
                $this->response_error(l('global.error_message.invalid_csrf_token'), 401);
            }

            /* Initiate captcha */
            $captcha = new Captcha();

            if(!is_logged_in() && settings()->captcha->transfer_upload_is_enabled && !$captcha->is_valid(false)) {
                $this->response_error(l('global.error_message.invalid_captcha'), 401);
            }
        }

        /* Filter some of the variables */
        $_POST['uploaded_files'] = array_query_clean($_POST['uploaded_files']);
        $_POST['uploaded_files'] = array_map(function($uuid) {
            return preg_replace('/[^a-zA-Z0-9]/', '', $uuid ?? '');
        }, $_POST['uploaded_files']);

        $files = [];
        $total_size = 0;

        /* Make sure the uploaded files exist and are ready */
        foreach($_POST['uploaded_files'] as $file_uuid) {
            $file_uuid = hex2bin($file_uuid);
            $file = db()->where('file_uuid', $file_uuid)->getOne('files');

            if(!$file) {
                continue;
            }

            if($file->status == 'uploading') {
                continue;
            }

            if($file->uploader_id != md5(get_ip())) {
                continue;
            }

            if($file->transfer_id) {
                continue;
            }

            $files[] = $file;
            $total_size += $file->size;
        }

        if(!count($files)) {
            $this->response_error(l('transfer.error_message.empty_files'), 401);
        }

        if($this->user->plan_settings->files_per_transfer_limit != -1 && count($files) > $this->user->plan_settings->files_per_transfer_limit) {
            $this->response_error(l('transfer.error_message.files_per_transfer_limit'), 401);
        }

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

        /* Generate dynamic name */
        if(empty($_POST['name'])) {
            $files_count = count($files);

            switch($files_count) {
                case 1:
                    $_POST['name'] = sprintf(l('transfer.name.default_one'), string_truncate($files[0]->original_name, 64));
                    break;

                case 2:
                    $_POST['name'] = sprintf(l('transfer.name.default_two'), string_truncate($files[0]->original_name, 16), string_truncate($files[1]->original_name, 16));
                    break;

                default:
                    $_POST['name'] = sprintf(l('transfer.name.default_many'), string_truncate($files[0]->original_name, 32), $files_count - 1);
                    break;
            }
        }

        /* :) */
        $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['link', 'email']) ? input_clean($_POST['type']) : ($this->user->preferences->transfers_default_type ?: 'link');
        $_POST['email_to'] = isset($_POST['email_to']) ? filter_var($_POST['email_to'], FILTER_SANITIZE_EMAIL) : null;
        $_POST['name'] = input_clean($_POST['name'], 64);
        $_POST['description'] = input_clean($_POST['description'] ?? '');
        $_POST['url'] = !empty($_POST['url']) && $this->user->plan_settings->custom_url_is_enabled ? get_slug(input_clean($_POST['url'])) : false;
        $_POST['domain_id'] = isset($_POST['domain_id']) && isset($domains[$_POST['domain_id']]) ? (!empty($_POST['domain_id']) ? (int) $_POST['domain_id'] : null) : null;
        $_POST['is_removed_branding'] = $this->user->plan_settings->removable_branding_is_enabled ? (int) isset($_POST['is_removed_branding']) : 0;
        $_POST['custom_css'] = $this->user->plan_settings->custom_css_is_enabled ? mb_substr(trim($_POST['custom_css'] ?? $this->user->preferences->transfers_default_custom_css), 0, 10000) : null;
        $_POST['custom_js'] = $this->user->plan_settings->custom_js_is_enabled ? mb_substr(trim($_POST['custom_js'] ?? $this->user->preferences->transfers_default_custom_js), 0, 10000) : null;

        /* Check for duplicate url if needed */
        if($_POST['url']) {
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
                $this->response_error(sprintf(l('transfer.error_message.url_maximum_characters'), ($this->user->plan_settings->url_maximum_characters ?? 64)), 401);
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
        if (!array_key_exists('downloads_limit', $_POST)) {
            $_POST['downloads_limit'] = $this->user->preferences->transfers_default_downloads_limit;
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

        /* Projects & Pixels */
        $projects = [];
        $pixels = [];
        $notification_handlers = [];

        if(is_logged_in() || $api_key) {
            $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

            /* Get available pixels */
            $pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);

            /* Get available notification handlers */
            $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);
        }

        /* Projects */
        if(!isset($_POST['global_token']) && !isset($_POST['project_id'])) {
            $_POST['project_id'] = $this->user->preferences->transfers_default_project_id ?? null;
        }
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;

        /* Pixels */
        if(!isset($_POST['global_token']) && !isset($_POST['pixels_ids'])) {
            $_POST['pixels_ids'] = $this->user->preferences->transfers_default_pixels_ids ?? [];
        }

        $_POST['pixels_ids'] = isset($_POST['pixels_ids']) ? array_map(
            'intval',
            array_filter($_POST['pixels_ids'], function($pixel_id) use($pixels) {
                return array_key_exists($pixel_id, $pixels);
            })
        ) : [];

        $_POST['pixels_ids'] = json_encode($_POST['pixels_ids']);

        /* Notification handlers */
        if(!isset($_POST['global_token']) && !isset($_POST['download_notification_handlers_ids'])) {
            $_POST['download_notification_handlers_ids'] = $this->user->preferences->transfers_default_download_notification_handlers_ids ?? [];
        }

        if(!isset($_POST['global_token']) && !isset($_POST['pageview_notification_handlers_ids'])) {
            $_POST['pageview_notification_handlers_ids'] = $this->user->preferences->transfers_default_pageview_notification_handlers_ids ?? [];
        }

        $_POST['download_notification_handlers_ids'] = array_map('intval', array_filter($_POST['download_notification_handlers_ids'] ?? [], fn($notification_handler_id) => isset($notification_handlers[$notification_handler_id])));
        $_POST['pageview_notification_handlers_ids'] = array_map('intval', array_filter($_POST['pageview_notification_handlers_ids'] ?? [], fn($notification_handler_id) => isset($notification_handlers[$notification_handler_id])));

        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
            $_POST['download_notification_handlers_ids'] = array_slice($_POST['download_notification_handlers_ids'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
            $_POST['pageview_notification_handlers_ids'] = array_slice($_POST['pageview_notification_handlers_ids'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
        }

        /* Password */
        $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
        $_POST['password'] = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

        /* File encryption */
        $_POST['file_encryption_is_enabled'] = $this->user->plan_settings->file_encryption_is_enabled ? (bool) ($_POST['file_encryption_is_enabled'] ?? false) : false;

        if($_POST['file_encryption_is_enabled']) {
            $_POST['file_preview_is_enabled'] = false;
            $_POST['gallery_file_preview_is_enabled'] = false;
        } else {
            /* File preview */
            if(!isset($_POST['global_token']) && !isset($_POST['file_preview_is_enabled'])) {
                $_POST['file_preview_is_enabled'] = $this->user->preferences->transfers_default_file_preview_is_enabled;
            } else {
                $_POST['file_preview_is_enabled'] = (int) isset($_POST['file_preview_is_enabled']);
            }

            if(!isset($_POST['global_token']) && !isset($_POST['gallery_file_preview_is_enabled'])) {
                $_POST['gallery_file_preview_is_enabled'] = $this->user->preferences->transfers_default_gallery_file_preview_is_enabled;
            } else {
                $_POST['gallery_file_preview_is_enabled'] = (int) isset($_POST['gallery_file_preview_is_enabled']);
            }
        }

        /* Prepare settings */
        $settings = json_encode([
            'password' => $_POST['password'],
            'file_encryption_is_enabled' => $_POST['file_encryption_is_enabled'],
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
        $transfer_id = db()->insert('transfers', [
            'user_id' => $this->user->user_id ?? null,
            'uploader_id' => md5(get_ip()),
            'domain_id' => $_POST['domain_id'],
            'project_id' => $_POST['project_id'],
            'pixels_ids' => $_POST['pixels_ids'],
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'type' => $_POST['type'],
            'email_to' => $_POST['email_to'],
            'url' => $_POST['url'],
            'settings' => $settings,
            'notifications' => $notifications,
            'total_files' => count($files),
            'total_size' => $total_size,
            'downloads_limit' => $downloads_limit,
            'expiration_datetime' => $expiration_datetime,
            'datetime' => get_date(),
        ]);

        foreach($files as $file) {
            /* Database query */
            db()->where('file_id', $file->file_id)->update('files', [
                'user_id' => $this->user->user_id ?? null,
                'transfer_id' => $transfer_id
            ]);
        }

        /* Update the user */
        (new \Altum\Models\Files())->calculate_and_update_file_usage($this->user->user_id);

        /* Clear the cache */
        cache()->deleteItem('transfers_total?user_id=' . $this->user->user_id);

        /* Full transfer URL */
        $transfer_full_url = $_POST['domain_id'] ? $domains[$_POST['domain_id']]->scheme . $domains[$_POST['domain_id']]->host . '/' . $_POST['url'] . '/' : SITE_URL . $_POST['url'] . '/';

        /* Send email if needed */
        if(settings()->transfers->email_transfer_is_enabled && $_POST['email_to']) {
            /* Prepare the email */
            $email_template = get_email_template(
                [
                    '{{TRANSFER_NAME}}' => $_POST['name'],
                    '{{SENDER_NAME}}' => $this->user->name ?? l('global.emails.transfer_someone')
                ],
                l('global.emails.transfer_send.subject', $this->user->language ?? null),
                [
                    '{{TRANSFER_LINK}}' => $transfer_full_url,
                    '{{TRANSFER_TOTAL_FILES}}' => count($files),
                    '{{TRANSFER_TOTAL_SIZE}}' => get_formatted_bytes($total_size),
                    '{{TRANSFER_NAME}}' => $_POST['name'],
                    '{{TRANSFER_DESCRIPTION}}' => $_POST['description'],
                    '{{SENDER_NAME}}' => $this->user->name ?? l('global.emails.transfer_someone')
                ],
                l('global.emails.transfer_send.body', $this->user->language ?? null)
            );

            /* Send the email */
            send_mail($_POST['email_to'], $email_template->subject, $email_template->body);
        }

        Response::jsonapi_success([
            'id' => $transfer_id,
            'download_url' => $transfer_full_url,
            'view_url' => url('transfer/' . $transfer_id . ($this->user->preferences->transfers_auto_copy_link ? '?auto_copy_link=true' : '')),
        ]);
    }

    public function update_api() {

        set_time_limit(0);

        if(empty($_POST)) {
            throw_404();
        }

        /* Define the return content to be treated as JSON */
        header('Content-Type: application/json');

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.transfers')) {
            $this->response_error(l('global.info_message.team_no_access'), 401);
        }

        /* Get potential API key */
        $api_key = \Altum\Authentication::get_authorization_bearer();

        /* API */
        if($api_key) {
            $this->user = db()->where('api_key', $api_key)->where('status', 1)->getOne('users');

            if(!$this->user) {
                $this->response_error(l('api.error_message.no_access'), 401);
            }

            $this->user->plan_settings = json_decode($this->user->plan_settings);
            $this->user->preferences = json_decode($this->user->preferences);

            if(!$this->user->plan_settings->api_is_enabled) {
                $this->response_error(l('api.error_message.no_access'), 401);
            }
        }

        /* Guest */
        elseif(!is_logged_in()) {
            if($this->user->plan_settings->transfers_limit == 0) {
                $this->response_error(l('global.info_message.plan_feature_limit'), 401);
            }
        }

        /* Check for required fields */
        $required_fields = ['transfer_id', 'uploaded_files'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field])) {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        if(!$api_key) {
            if(!Csrf::check('global_token')) {
                $this->response_error(l('global.error_message.invalid_csrf_token'), 401);
            }

            /* Initiate captcha */
            $captcha = new Captcha();

            if(!is_logged_in() && settings()->captcha->transfer_upload_is_enabled && !$captcha->is_valid(false)) {
                $this->response_error(l('global.error_message.invalid_captcha'), 401);
            }
        }

        /* Filter variables */
        $_POST['transfer_id'] = (int) $_POST['transfer_id'];
        $_POST['uploaded_files'] = array_query_clean($_POST['uploaded_files']);
        $_POST['uploaded_files'] = array_map(function($uuid) {
            return preg_replace('/[^a-zA-Z0-9]/', '', $uuid ?? '');
        }, $_POST['uploaded_files']);

        /* Get the transfer */
        $transfer = db()->where('transfer_id', $_POST['transfer_id'])->getOne('transfers');

        if(!$transfer) {
            $this->response_error(l('global.error_message.basic'), 404);
        }

        /* Ownership checks */
        if($api_key || is_logged_in()) {
            if($transfer->user_id != $this->user->user_id) {
                $this->response_error(l('api.error_message.no_access'), 401);
            }
        } else {
            if($transfer->uploader_id != md5(get_ip())) {
                $this->response_error(l('api.error_message.no_access'), 401);
            }
        }

        /* Do not allow updates to expired transfers */
        if($transfer->expiration_datetime && (new \DateTime($transfer->expiration_datetime)) < (new \DateTime())) {
            $this->response_error(l('global.error_message.basic'), 401);
        }

        $files = [];
        $total_size = 0;

        /* Make sure the uploaded files exist and are ready */
        foreach($_POST['uploaded_files'] as $file_uuid) {
            $file_uuid = hex2bin($file_uuid);
            $file = db()->where('file_uuid', $file_uuid)->getOne('files');

            if(!$file) {
                continue;
            }

            if($file->status == 'uploading') {
                continue;
            }

            if($file->uploader_id != md5(get_ip())) {
                continue;
            }

            if($file->transfer_id) {
                continue;
            }

            $files[] = $file;
            $total_size += $file->size;
        }

        if(!count($files)) {
            $this->response_error(l('transfer.error_message.empty_files'), 401);
        }

        /* Existing + new files limit */
        $new_total_files = $transfer->total_files + count($files);

        if($this->user->plan_settings->files_per_transfer_limit != -1 && $new_total_files > $this->user->plan_settings->files_per_transfer_limit) {
            $this->response_error(l('transfer.error_message.files_per_transfer_limit'), 401);
        }

        $new_total_size = $transfer->total_size + $total_size;

        /* Attach files */
        foreach($files as $file) {
            db()->where('file_id', $file->file_id)->update('files', [
                'user_id' => $transfer->user_id,
                'transfer_id' => $transfer->transfer_id
            ]);
        }

        /* Update transfer totals */
        db()->where('transfer_id', $transfer->transfer_id)->update('transfers', [
            'total_files' => $new_total_files,
            'total_size' => $new_total_size,
            'last_datetime' => get_date(),
        ]);

        /* Update the user file usage */
        (new \Altum\Models\Files())->calculate_and_update_file_usage($this->user->user_id);

        /* Clear the cache */
		cache()->deleteItem('transfers_total?user_id=' . $this->user->user_id);
		cache()->deleteItem('files?transfer_id=' . $transfer->transfer_id);

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

        /* Full transfer URL */
        $transfer_full_url = $transfer->domain_id && isset($domains[$transfer->domain_id]) ? $domains[$transfer->domain_id]->scheme . $domains[$transfer->domain_id]->host . '/' . $transfer->url . '/' : SITE_URL . $transfer->url . '/';

        Response::jsonapi_success([
            'id' => $transfer->transfer_id,
            'download_url' => $transfer_full_url,
            'view_url' => url('transfer/' . $transfer->transfer_id . ($this->user->preferences->transfers_auto_copy_link ? '?auto_copy_link=true' : '')),
        ]);
    }
}
