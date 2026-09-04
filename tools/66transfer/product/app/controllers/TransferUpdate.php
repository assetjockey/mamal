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
use Altum\Date;
use Altum\Title;

defined('ALTUMCODE') || die();

class TransferUpdate extends Controller {

    public function index() {

        $transfer_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.transfers')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('transfer/' . $transfer_id);
        }

        /* Get transfer details */
        if(!$transfer = db()->where('transfer_id', $transfer_id)->getOne('transfers')) {
            throw_404();
        }

        /* Make sure the current user has access */
        if(($transfer->uploader_id != md5(get_ip())) && (!$transfer->user_id || $transfer->user_id != $this->user->user_id)) {
            throw_404();
        }

        /* Check for the plan limit */
        if(is_logged_in()) {
            $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `transfers` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;
            if ($this->user->plan_settings->transfers_limit != -1 && $total_rows > $this->user->plan_settings->transfers_limit) {
                redirect('transfers');
            }
        }

        /* Generate the transfer full URL base */
        $transfer->full_url = (new \Altum\Models\Transfers())->get_transfer_full_url($transfer, $this->user);

        $transfer->settings = json_decode($transfer->settings ?? '');
        $transfer->pixels_ids = json_decode($transfer->pixels_ids);
        $transfer->notifications = json_decode($transfer->notifications);

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

        if(is_logged_in()) {
            /* Get available projects */
            $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

            /* Get available pixels */
            $pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);

            /* Get available notification handlers */
            $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);
        }

        if(!empty($_POST)) {
            $_POST['name'] = input_clean($_POST['name']);
            $_POST['description'] = input_clean($_POST['description']);
            $_POST['url'] = !empty($_POST['url']) ? get_slug(input_clean($_POST['url'])) : false;
            $_POST['domain_id'] = isset($_POST['domain_id']) && isset($domains[$_POST['domain_id']]) ? (!empty($_POST['domain_id']) ? (int) $_POST['domain_id'] : null) : null;
            $_POST['is_removed_branding'] = $this->user->plan_settings->removable_branding_is_enabled ? (int) isset($_POST['is_removed_branding']) : 0;
            $_POST['custom_css'] = $this->user->plan_settings->custom_css_is_enabled ? mb_substr(trim($_POST['custom_css']), 0, 10000) : null;
            $_POST['custom_js'] = $this->user->plan_settings->custom_js_is_enabled ? mb_substr(trim($_POST['custom_js']), 0, 10000) : null;

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
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
                ($_POST['url'] && $this->user->plan_settings->custom_url_is_enabled && $_POST['url'] != $transfer->url)
                || ($transfer->domain_id != $_POST['domain_id'])
            ) {
                $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
                $is_existing_link = database()->query("SELECT `transfer_id` FROM `transfers` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;

                if($is_existing_link) {
                    Alerts::add_error(l('transfer.error_message.url_exists'), 'url');
                }

                if(array_key_exists($_POST['url'], \Altum\Router::$routes['']) || in_array($_POST['url'], \Altum\Language::$active_languages) || file_exists(ROOT_PATH . $_POST['url'])) {
                    Alerts::add_error(l('transfer.error_message.blacklisted_url'), 'url');
                }

                if(in_array($_POST['url'], settings()->transfers->blacklisted_keywords)) {
                    Alerts::add_error(l('transfer.error_message.blacklisted_keyword'), 'url');
                }

                /* Make sure the custom url meets the requirements */
                if(mb_strlen($_POST['url']) < ($this->user->plan_settings->url_minimum_characters ?? 1)) {
                    Alerts::add_error(sprintf(l('transfer.error_message.url_minimum_characters'), ($this->user->plan_settings->url_minimum_characters ?? 1)));
                }

                if(mb_strlen($_POST['url']) > ($this->user->plan_settings->url_maximum_characters ?? 64)) {
                    Alerts::add_error(sprintf(l('transfer.error_message.url_maximum_characters'), ($this->user->plan_settings->url_maximum_characters ?? 64)));
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

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

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

                /* Projects */
                $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;

                /* Pixels */
                $_POST['pixels_ids'] = isset($_POST['pixels_ids']) ? array_map(
                    'intval',
                    array_filter($_POST['pixels_ids'], function($pixel_id) use($pixels) {
                        return array_key_exists($pixel_id, $pixels);
                    })
                ) : [];
                $_POST['pixels_ids'] = json_encode($_POST['pixels_ids']);

                /* Notification handlers */
                $_POST['download_notification_handlers_ids'] = array_map('intval', array_filter($_POST['download_notification_handlers_ids'] ?? [], fn($notification_handler_id) => isset($notification_handlers[$notification_handler_id])));
                $_POST['pageview_notification_handlers_ids'] = array_map('intval', array_filter($_POST['pageview_notification_handlers_ids'] ?? [], fn($notification_handler_id) => isset($notification_handlers[$notification_handler_id])));

                if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
                    $_POST['download_notification_handlers_ids'] = array_slice($_POST['download_notification_handlers_ids'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
                    $_POST['pageview_notification_handlers_ids'] = array_slice($_POST['pageview_notification_handlers_ids'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
                }

                /* File encryption */
                $_POST['file_encryption_is_enabled'] = $this->user->plan_settings->file_encryption_is_enabled ? (bool) ($_POST['file_encryption_is_enabled'] ?? false) : false;

                /* File preview */
                $_POST['file_preview_is_enabled'] = (int) isset($_POST['file_preview_is_enabled']);
                $_POST['gallery_file_preview_is_enabled'] = (int) isset($_POST['gallery_file_preview_is_enabled']);

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

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItemsByTag('transfer_id=' . $transfer->transfer_id);

                redirect('transfer-update/' . $transfer->transfer_id);
            }
        }

        /* Set a custom title */
        Title::set(sprintf(l('transfer_update.title'), $transfer->name));

        /* Main View */
        $data = [
            'transfer' => $transfer,
            'domains' => $domains,
            'projects' => $projects ?? [],
            'pixels' => $pixels ?? [],
            'notification_handlers' => $notification_handlers ?? [],
        ];

        $view = new \Altum\View('transfer-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }

}
