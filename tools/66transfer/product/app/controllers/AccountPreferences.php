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

class AccountPreferences extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(is_null($this->user->preferences)) {
            $this->user->preferences = new \StdClass();
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available pixels */
        $pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        if(!empty($_POST)) {

            /* White labeling */
            $_POST['white_label_title'] = isset($_POST['white_label_title']) ? input_clean($_POST['white_label_title'], 32) : '';
            $_POST['white_label_footer_description'] = isset($_POST['white_label_footer_description']) ? input_clean($_POST['white_label_footer_description'], 256) : '';
            $_POST['white_label_remove_socials'] = isset($_POST['white_label_remove_socials']);
            $_POST['white_label_remove_footer_links'] = isset($_POST['white_label_remove_footer_links']);

            /* Uploads processing */
            foreach(['logo_light', 'logo_dark', 'favicon'] as $image_key) {
                $this->user->preferences->{'white_label_' . $image_key} = \Altum\Uploads::process_upload($this->user->preferences->{'white_label_' . $image_key}, 'users', 'white_label_' . $image_key, 'white_label_' . $image_key . '_remove', null);
            }

            /* Clean some posted variables */
            $_POST['default_results_per_page'] = isset($_POST['default_results_per_page']) && in_array($_POST['default_results_per_page'], [10, 25, 50, 100, 250, 500, 1000]) ? (int) $_POST['default_results_per_page'] : settings()->main->default_results_per_page;
            $_POST['default_order_type'] = isset($_POST['default_order_type']) && in_array($_POST['default_order_type'], ['ASC', 'DESC']) ? $_POST['default_order_type'] : settings()->main->default_order_type;

            /* Custom */
            $_POST['transfers_default_order_by'] = isset($_POST['transfers_default_order_by']) && in_array($_POST['transfers_default_order_by'], ['transfer_id', 'datetime', 'last_datetime', 'expiration_datetime', 'pageviews', 'downloads', 'url', 'name', 'downloads_limit', 'total_files', 'total_size',]) ? $_POST['transfers_default_order_by'] : 'transfer_id';
            $_POST['transfer_requests_default_order_by'] = isset($_POST['transfer_requests_default_order_by']) && in_array($_POST['transfer_requests_default_order_by'], ['transfer_request_id', 'datetime', 'last_datetime', 'expiration_datetime', 'last_submitted_datetime', 'pageviews', 'url', 'name', 'total_submissions', 'total_files', 'total_size',]) ? $_POST['transfer_requests_default_order_by'] : 'transfer_request_id';
            $_POST['notification_handlers_default_order_by'] = isset($_POST['notification_handlers_default_order_by']) && in_array($_POST['notification_handlers_default_order_by'], ['notification_handler_id', 'datetime', 'last_datetime', 'name']) ? $_POST['notification_handlers_default_order_by'] : 'notification_handler_id';
            $_POST['domains_default_order_by'] = isset($_POST['domains_default_order_by']) && in_array($_POST['domains_default_order_by'], ['domain_id', 'last_datetime', 'host', 'datetime']) ? $_POST['domains_default_order_by'] : 'domain_id';
            $_POST['projects_default_order_by'] = isset($_POST['projects_default_order_by']) && in_array($_POST['projects_default_order_by'], ['project_id', 'last_datetime', 'name', 'datetime']) ? $_POST['projects_default_order_by'] : 'project_id';

            /* Transfer custom */
            $_POST['transfers_default_type'] = isset($_POST['transfers_default_type']) && in_array($_POST['transfers_default_type'], ['link', 'email',]) ? $_POST['transfers_default_type'] : 'link';
            $_POST['transfers_default_downloads_limit'] = empty($_POST['transfers_default_downloads_limit']) ? null : (int) $_POST['transfers_default_downloads_limit'];
            $_POST['transfers_default_expiration_datetime'] = empty($_POST['transfers_default_expiration_datetime']) ? null : (int) $_POST['transfers_default_expiration_datetime'];
            $_POST['transfers_default_file_preview_is_enabled'] = isset($_POST['transfers_default_file_preview_is_enabled']);
            $_POST['transfers_default_gallery_file_preview_is_enabled'] = isset($_POST['transfers_default_gallery_file_preview_is_enabled']);
            $_POST['transfers_default_pixels_ids'] = array_map('intval', array_filter($_POST['transfers_default_pixels_ids'] ?? [], fn($pixel_id) => array_key_exists($pixel_id, $pixels)));
            $_POST['transfers_default_project_id'] = isset($_POST['transfers_default_project_id']) && array_key_exists($_POST['transfers_default_project_id'], $projects) ? $_POST['transfers_default_project_id'] : null;
            $_POST['transfers_default_is_removed_branding'] = isset($_POST['transfers_default_is_removed_branding']);
            $_POST['transfers_default_custom_css'] = mb_substr(trim($_POST['transfers_default_custom_css']), 0, 10000);
            $_POST['transfers_default_custom_js'] = mb_substr(trim($_POST['transfers_default_custom_js']), 0, 10000);
            $_POST['transfers_default_download_notification_handlers_ids'] = array_map('intval', array_filter($_POST['transfers_default_download_notification_handlers_ids'] ?? [], fn($notification_handler_id) => array_key_exists($notification_handler_id, $notification_handlers)));
            $_POST['transfers_default_pageview_notification_handlers_ids'] = array_map('intval', array_filter($_POST['transfers_default_pageview_notification_handlers_ids'] ?? [], fn($notification_handler_id) => array_key_exists($notification_handler_id, $notification_handlers)));
            $_POST['transfers_auto_file_upload'] = isset($_POST['transfers_auto_file_upload']);
            $_POST['transfers_auto_transfer_create'] = isset($_POST['transfers_auto_transfer_create']);
            $_POST['transfers_auto_copy_link'] = isset($_POST['transfers_auto_copy_link']);

            /* Transfer requests */
            $_POST['transfer_requests_default_expiration_datetime'] = empty($_POST['transfer_requests_default_expiration_datetime']) ? null : (int) $_POST['transfer_requests_default_expiration_datetime'];
            $_POST['transfer_requests_default_pixels_ids'] = array_map('intval', array_filter($_POST['transfer_requests_default_pixels_ids'] ?? [], fn($pixel_id) => array_key_exists($pixel_id, $pixels)));
            $_POST['transfer_requests_default_project_id'] = isset($_POST['transfer_requests_default_project_id']) && array_key_exists($_POST['transfer_requests_default_project_id'], $projects) ? $_POST['transfer_requests_default_project_id'] : null;
            $_POST['transfer_requests_default_is_removed_branding'] = isset($_POST['transfer_requests_default_is_removed_branding']);
            $_POST['transfer_requests_default_custom_css'] = mb_substr(trim($_POST['transfer_requests_default_custom_css']), 0, 10000);
            $_POST['transfer_requests_default_custom_js'] = mb_substr(trim($_POST['transfer_requests_default_custom_js']), 0, 10000);
            $_POST['transfer_requests_default_submission_notification_handlers_ids'] = array_map('intval', array_filter($_POST['transfer_requests_default_submission_notification_handlers_ids'] ?? [], fn($notification_handler_id) => array_key_exists($notification_handler_id, $notification_handlers)));
            $_POST['transfer_requests_default_pageview_notification_handlers_ids'] = array_map('intval', array_filter($_POST['transfer_requests_default_pageview_notification_handlers_ids'] ?? [], fn($notification_handler_id) => array_key_exists($notification_handler_id, $notification_handlers)));
            $_POST['transfer_requests_auto_file_upload'] = isset($_POST['transfer_requests_auto_file_upload']);
            $_POST['transfer_requests_auto_submission_create'] = isset($_POST['transfer_requests_auto_submission_create']);
            $_POST['transfer_requests_auto_copy_link'] = isset($_POST['transfer_requests_auto_copy_link']);

            /* Allowed dashboard features */
            $allowed_dashboard_features = require APP_PATH . 'includes/available_dashboard_features.php';

            /* Keep only valid features */
            $_POST['dashboard'] = array_values(array_filter($_POST['dashboard'] ?? [], fn($item) => in_array($item, $allowed_dashboard_features)));

            /* Preserve the order of $_POST['dashboard'] */
            $dashboard = array_fill_keys($_POST['dashboard'], true);

            /* Append missing features at the end with false */
            foreach ($allowed_dashboard_features as $feature) {
                if(!array_key_exists($feature, $dashboard)) {
                    $dashboard[$feature] = false;
                }
            }

            /* Tracking */
            $_POST['excluded_ips'] = array_filter(array_map('trim', explode(',', input_clean($_POST['excluded_ips'], 500))));

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $preferences = json_encode([
                    'white_label_title' => $_POST['white_label_title'],
                    'white_label_footer_description' => $_POST['white_label_footer_description'],
                    'white_label_remove_socials' => $_POST['white_label_remove_socials'],
                    'white_label_remove_footer_links' => $_POST['white_label_remove_footer_links'],
                    'white_label_logo_light' => $this->user->preferences->white_label_logo_light,
                    'white_label_logo_dark' => $this->user->preferences->white_label_logo_dark,
                    'white_label_favicon' => $this->user->preferences->white_label_favicon,
                    'default_results_per_page' => $_POST['default_results_per_page'],
                    'default_order_type' => $_POST['default_order_type'],
                    'transfers_default_order_by' => $_POST['transfers_default_order_by'],
                    'notification_handlers_default_order_by' => $_POST['notification_handlers_default_order_by'],
                    'domains_default_order_by' => $_POST['domains_default_order_by'],
                    'projects_default_order_by' => $_POST['projects_default_order_by'],
                    'transfers_default_type' => $_POST['transfers_default_type'],
                    'transfers_default_downloads_limit' => $_POST['transfers_default_downloads_limit'],
                    'transfers_default_expiration_datetime' => $_POST['transfers_default_expiration_datetime'],
                    'transfers_default_file_preview_is_enabled' => $_POST['transfers_default_file_preview_is_enabled'],
                    'transfers_default_gallery_file_preview_is_enabled' => $_POST['transfers_default_gallery_file_preview_is_enabled'],
                    'transfers_default_pixels_ids' => $_POST['transfers_default_pixels_ids'],
                    'transfers_default_project_id' => $_POST['transfers_default_project_id'],
                    'transfers_default_is_removed_branding' => $_POST['transfers_default_is_removed_branding'],
                    'transfers_default_custom_css' => $_POST['transfers_default_custom_css'],
                    'transfers_default_custom_js' => $_POST['transfers_default_custom_js'],
					'transfers_default_download_notification_handlers_ids' => $_POST['transfers_default_download_notification_handlers_ids'],
					'transfers_default_pageview_notification_handlers_ids' => $_POST['transfers_default_pageview_notification_handlers_ids'],
                    'transfers_auto_file_upload' => $_POST['transfers_auto_file_upload'],
                    'transfers_auto_transfer_create' => $_POST['transfers_auto_transfer_create'],
                    'transfers_auto_copy_link' => $_POST['transfers_auto_copy_link'],

                    /* transfer requests */
                    'transfer_requests_default_expiration_datetime' => $_POST['transfer_requests_default_expiration_datetime'],
                    'transfer_requests_default_pixels_ids' => $_POST['transfer_requests_default_pixels_ids'],
                    'transfer_requests_default_project_id' => $_POST['transfer_requests_default_project_id'],
                    'transfer_requests_default_is_removed_branding' => $_POST['transfer_requests_default_is_removed_branding'],
                    'transfer_requests_default_custom_css' => $_POST['transfer_requests_default_custom_css'],
                    'transfer_requests_default_custom_js' => $_POST['transfer_requests_default_custom_js'],
                    'transfer_requests_default_submission_notification_handlers_ids' => $_POST['transfer_requests_default_submission_notification_handlers_ids'],
                    'transfer_requests_default_pageview_notification_handlers_ids' => $_POST['transfer_requests_default_pageview_notification_handlers_ids'],
                    'transfer_requests_auto_file_upload' => $_POST['transfer_requests_auto_file_upload'],
                    'transfer_requests_auto_submission_create' => $_POST['transfer_requests_auto_submission_create'],
                    'transfer_requests_auto_copy_link' => $_POST['transfer_requests_auto_copy_link'],

                    'dashboard' => $dashboard,

                    'excluded_ips' => $_POST['excluded_ips'],
                ]);

                /* Database query */
                db()->where('user_id', $this->user->user_id)->update('users', [
                    'preferences' => $preferences,
                ]);

                /* Log the action */
                \Altum\Logger::users($this->user->user_id, 'account_preferences.updated');

                /* Set a nice success message */
                Alerts::add_success(l('account_preferences.success_message'));

                /* Clear the cache */
                cache()->deleteItemsByTag('user_id=' . $this->user->user_id);

                /* Send webhook notification if needed */
                if(settings()->webhooks->user_update) {
                    fire_and_forget('post', settings()->webhooks->user_update, [
                        'user_id' => $this->user->user_id,
                        'email' => $this->user->email,
                        'name' => $this->user->name,
                        'source' => 'account_preferences',
                        'datetime' => get_date(),
                    ], signature: true);
                }

                redirect('account-preferences');
            }

        }

        /* Get the account header menu */
        $menu = new \Altum\View('partials/account_header_menu', (array) $this);
        $this->add_view_content('account_header_menu', $menu->run());

        /* Prepare the view */
        $data = [
            'projects' => $projects,
            'pixels' => $pixels,
            'notification_handlers' => $notification_handlers,
        ];

        $view = new \Altum\View('account-preferences/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
