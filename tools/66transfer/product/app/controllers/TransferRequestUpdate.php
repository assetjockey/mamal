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
use Altum\Title;

defined('ALTUMCODE') || die();

class TransferRequestUpdate extends Controller {

    public function index() {

        if(!settings()->transfers->transfer_requests_is_enabled) {
            throw_404();
        }

        $transfer_request_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.transfer_requests')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('transfer-request/' . $transfer_request_id);
        }

        /* Get transfer request details */
        if(!$transfer_request = db()->where('transfer_request_id', $transfer_request_id)->getOne('transfer_requests')) {
            throw_404();
        }

        /* Make sure the current user has access */
        if(($transfer_request->uploader_id != md5(get_ip())) && (!$transfer_request->user_id || $transfer_request->user_id != $this->user->user_id)) {
            throw_404();
        }

        /* Check for the plan limit */
        if(is_logged_in()) {
            $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `transfer_requests` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

            if($this->user->plan_settings->transfer_requests_limit != -1 && $total_rows > $this->user->plan_settings->transfer_requests_limit) {
                redirect('transfer-requests');
            }
        }

        $transfer_request->settings = json_decode($transfer_request->settings ?? '');
        $transfer_request->pixels_ids = json_decode($transfer_request->pixels_ids);
        $transfer_request->notifications = json_decode($transfer_request->notifications);

		/* Get available custom domains */
		$domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user, false);


		if(is_logged_in()) {
			/* Get available projects */
			$projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

			/* Get available pixels */
			$pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);

			/* Get available notification handlers */
			$notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);
		} else {
			$projects = $pixels = $notification_handlers = [];
		}

        if(!empty($_POST)) {
            $_POST['name'] = input_clean($_POST['name'], 64);
            $_POST['description'] = input_clean($_POST['description'] ?? '');
            $_POST['url'] = !empty($_POST['url']) && $this->user->plan_settings->custom_url_is_enabled ? get_slug(input_clean($_POST['url'])) : false;
            $_POST['domain_id'] = isset($_POST['domain_id']) && isset($domains[$_POST['domain_id']]) ? (!empty($_POST['domain_id']) ? (int) $_POST['domain_id'] : null) : null;
            $_POST['is_removed_branding'] = $this->user->plan_settings->removable_branding_is_enabled ? (int) isset($_POST['is_removed_branding']) : 0;
            $_POST['custom_css'] = $this->user->plan_settings->custom_css_is_enabled ? mb_substr(trim($_POST['custom_css'] ?? ''), 0, 10000) : null;
            $_POST['custom_js'] = $this->user->plan_settings->custom_js_is_enabled ? mb_substr(trim($_POST['custom_js'] ?? ''), 0, 10000) : null;

            $required_fields = ['name'];

            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Check for duplicate url if needed */
            if(
                ($_POST['url'] && $this->user->plan_settings->custom_url_is_enabled && $_POST['url'] != $transfer_request->url)
                || ($transfer_request->domain_id != $_POST['domain_id'])
            ) {
                $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
                $is_existing_link = database()->query("SELECT `transfer_request_id` FROM `transfer_requests` WHERE `url` = '{$_POST['url']}' {$domain_id_where} AND `transfer_request_id` != {$transfer_request->transfer_request_id}")->num_rows;

                if($is_existing_link) {
                    Alerts::add_field_error('url', l('transfer.error_message.url_exists'));
                }

                if(array_key_exists($_POST['url'], \Altum\Router::$routes['']) || in_array($_POST['url'], \Altum\Language::$active_languages) || file_exists(ROOT_PATH . $_POST['url'])) {
                    Alerts::add_field_error('url', l('transfer.error_message.blacklisted_url'));
                }

                if(in_array($_POST['url'], settings()->transfers->blacklisted_keywords)) {
                    Alerts::add_field_error('url', l('transfer.error_message.blacklisted_keyword'));
                }

                if(mb_strlen($_POST['url']) < ($this->user->plan_settings->url_minimum_characters ?? 1)) {
                    Alerts::add_field_error('url', sprintf(l('transfer.error_message.url_minimum_characters'), ($this->user->plan_settings->url_minimum_characters ?? 1)));
                }

                if(mb_strlen($_POST['url']) > ($this->user->plan_settings->url_maximum_characters ?? 64)) {
                    Alerts::add_field_error('url', sprintf(l('transfer.error_message.url_maximum_characters'), ($this->user->plan_settings->url_maximum_characters ?? 64)));
                }
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                if(!$_POST['url']) {
                    $is_existing_link = true;

                    while($is_existing_link) {
                        $_POST['url'] = mb_strtolower(string_generate(settings()->transfers->random_url_length ?? 7));

                        $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
                        $is_existing_link = database()->query("SELECT `transfer_request_id` FROM `transfer_requests` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;
                    }
                }

                /* Expiration datetime */
                $_POST['expiration_datetime'] = empty($_POST['expiration_datetime']) ? null : $_POST['expiration_datetime'];

                if($this->user->plan_settings->transfers_retention == -1) {
                    $expiration_datetime_object = null;
                    $expiration_datetime = null;
                } else {
                    $expiration_datetime_object = (new \DateTime())->modify('+' . $this->user->plan_settings->transfers_retention . ' days');
                    $expiration_datetime = $expiration_datetime_object->format('Y-m-d H:i:s');
                }

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
                $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;

                /* Pixels */
                $_POST['pixels_ids'] = isset($_POST['pixels_ids']) ? array_map('intval', array_filter($_POST['pixels_ids'], function($pixel_id) use($pixels) {
                    return array_key_exists($pixel_id, $pixels);
                })) : [];

                $_POST['pixels_ids'] = json_encode($_POST['pixels_ids']);

                /* Notification handlers */
                $_POST['submission_notification_handlers_ids'] = array_map('intval', array_filter($_POST['submission_notification_handlers_ids'] ?? [], fn($notification_handler_id) => isset($notification_handlers[$notification_handler_id])));
                $_POST['pageview_notification_handlers_ids'] = array_map('intval', array_filter($_POST['pageview_notification_handlers_ids'] ?? [], fn($notification_handler_id) => isset($notification_handlers[$notification_handler_id])));

                if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
                    $_POST['submission_notification_handlers_ids'] = array_slice($_POST['submission_notification_handlers_ids'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
                    $_POST['pageview_notification_handlers_ids'] = array_slice($_POST['pageview_notification_handlers_ids'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
                }

                /* Password */
                $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
				$_POST['password'] = !empty($_POST['password'])
					? ($_POST['password'] != $transfer_request->settings->password ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $transfer_request->settings->password)
					: null;

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

                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                cache()->deleteItem('transfer_requests_total?user_id=' . $this->user->user_id);
                cache()->deleteItemsByTag('transfer_requests?user_id=' . $this->user->user_id);
                cache()->deleteItemsByTag('transfer_request_id=' . $transfer_request->transfer_request_id);

                redirect('transfer-request-update/' . $transfer_request->transfer_request_id);
            }
        }

        /* Set a custom title */
        Title::set(sprintf(l('transfer_request_update.title'), $transfer_request->name));

        /* Main View */
        $data = [
            'transfer_request' => $transfer_request,
            'domains' => $domains,
            'projects' => $projects,
            'pixels' => $pixels,
            'notification_handlers' => $notification_handlers,
        ];

        $view = new \Altum\View('transfer-request-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
