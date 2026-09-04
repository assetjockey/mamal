<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
 *
 * This software is proprietary software owned and licensed by AltumCode.
 * A valid license is required to use, modify, or distribute this software.
 * Unauthorized use, reproduction, modification, or distribution is prohibited.
 *
 * 🌍 Explore all AltumCode projects: https://altumcode.com/
 * 📧 Support & general inquiries: https://altumcode.com/contact
 * 📤 Download the latest version: https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
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
            $websites_allowed_order_by = ['website_id', 'last_datetime', 'datetime', 'name', 'host', 'current_month_sessions_events', 'last_24_hours_pageviews', 'last_7_days_pageviews'];

            $_POST['websites_default_order_by'] = isset($_POST['websites_default_order_by']) && in_array($_POST['websites_default_order_by'], $websites_allowed_order_by) ? $_POST['websites_default_order_by'] : 'website_id';
            $_POST['websites_data_period'] = isset($_POST['websites_data_period']) && in_array($_POST['websites_data_period'], ['current_month', 'last_7_days', 'last_24_hours']) ? $_POST['websites_data_period'] : 'current_month';
            $_POST['heatmaps_default_order_by'] = isset($_POST['heatmaps_default_order_by']) && in_array($_POST['heatmaps_default_order_by'], ['heatmap_id', 'name', 'last_datetime', 'datetime']) ? $_POST['heatmaps_default_order_by'] : 'heatmap_id';
            $_POST['domains_default_order_by'] = isset($_POST['domains_default_order_by']) && in_array($_POST['domains_default_order_by'], ['domain_id', 'last_datetime', 'host', 'datetime']) ? $_POST['domains_default_order_by'] : 'domain_id';
            $_POST['annotations_default_order_by'] = isset($_POST['annotations_default_order_by']) && in_array($_POST['annotations_default_order_by'], ['annotation_id', 'last_datetime', 'name', 'datetime', 'chart_datetime']) ? $_POST['annotations_default_order_by'] : 'annotation_id';
            $_POST['goals_default_order_by'] = isset($_POST['goals_default_order_by']) && in_array($_POST['goals_default_order_by'], ['website_id', 'last_datetime', 'datetime', 'name', 'path', 'key']) ? $_POST['goals_default_order_by'] : 'goal_id';

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
                    'websites_data_period' => $_POST['websites_data_period'],
                    'heatmaps_default_order_by' => $_POST['heatmaps_default_order_by'],
                    'domains_default_order_by' => $_POST['domains_default_order_by'],
                    'annotations_default_order_by' => $_POST['annotations_default_order_by'],
                    'goals_default_order_by' => $_POST['goals_default_order_by'],
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
