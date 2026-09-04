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
            $_POST['websites_default_order_by'] = isset($_POST['websites_default_order_by']) && in_array($_POST['websites_default_order_by'], ['website_id', 'datetime', 'last_datetime', 'last_audit_datetime', 'host', 'score', 'total_audits', 'total_archived_audits', 'total_issues',]) ? $_POST['websites_default_order_by'] : 'website_id';
            $_POST['audits_default_order_by'] = isset($_POST['audits_default_order_by']) && in_array($_POST['audits_default_order_by'], ['audit_id', 'datetime', 'last_datetime', 'next_refresh_datetime', 'last_refresh_datetime', 'url', 'host', 'title', 'score', 'total_issues', 'total_refreshes',]) ? $_POST['audits_default_order_by'] : 'audit_id';
            $_POST['archived_audits_default_order_by'] = isset($_POST['archived_audits_default_order_by']) && in_array($_POST['archived_audits_default_order_by'], ['archived_audit_id', 'datetime', 'expiration_datetime', 'url', 'host', 'title', 'score', 'total_issues',]) ? $_POST['archived_audits_default_order_by'] : 'archived_audit_id';
            $_POST['notification_handlers_default_order_by'] = isset($_POST['notification_handlers_default_order_by']) && in_array($_POST['notification_handlers_default_order_by'], ['notification_handler_id', 'datetime', 'last_datetime', 'name']) ? $_POST['notification_handlers_default_order_by'] : 'notification_handler_id';
            $_POST['domains_default_order_by'] = isset($_POST['domains_default_order_by']) && in_array($_POST['domains_default_order_by'], ['domain_id', 'last_datetime', 'host', 'datetime']) ? $_POST['domains_default_order_by'] : 'domain_id';

            /* Allowed dashboard features */
            $allowed_dashboard_features = ['websites', 'audits'];

            /* Sanitize input - keep only valid features */
            $_POST['dashboard'] = array_values(array_filter($_POST['dashboard'] ?? [], fn($item) => in_array($item, $allowed_dashboard_features)));

            /* Preserve the order of $_POST['dashboard'] */
            $dashboard = array_fill_keys($_POST['dashboard'], true);

            /* Append missing features at the end with false */
            foreach ($allowed_dashboard_features as $feature) {
                if(!array_key_exists($feature, $dashboard)) {
                    $dashboard[$feature] = false;
                }
            }

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

                    'websites_default_order_by' => $_POST['websites_default_order_by'],
                    'audits_default_order_by' => $_POST['audits_default_order_by'],
                    'archived_audits_default_order_by' => $_POST['archived_audits_default_order_by'],
                    'notification_handlers_default_order_by' => $_POST['notification_handlers_default_order_by'],
                    'domains_default_order_by' => $_POST['domains_default_order_by'],

                    'dashboard' => $dashboard,
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
        $data = [];

        $view = new \Altum\View('account-preferences/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
