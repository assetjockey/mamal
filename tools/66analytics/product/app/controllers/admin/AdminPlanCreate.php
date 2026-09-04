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

class AdminPlanCreate extends Controller {

    public function index() {

        if(in_array(settings()->license->type, ['Extended License', 'extended'])) {
            /* Get the available taxes from the system */
            $taxes = db()->get('taxes');

            /* Get available codes */
            $codes = db()->where('type', 'discount')->get('codes');
        }

		$additional_domains = db()->where('is_enabled', 1)->where('type', 1)->get('domains');

        /* Get all available plans */
        $plans = (new \Altum\Models\Plan())->get_plans();

        /* Initiate purifier */
        $purifier_config = \HTMLPurifier_Config::createDefault();
        $purifier_config->set('Cache.SerializerPath', UPLOADS_PATH . 'cache');
        $purifier_config->set('HTML.Allowed', 'span[style]');
        $purifier_config->set('CSS.AllowedProperties', 'color,font-weight,font-style,text-decoration,font-family,background-color,text-transform,margin,padding,text-align');
        $purifier = new \HTMLPurifier($purifier_config);

        if(!empty($_POST)) {
            /* Filter some of the variables */
            $_POST['name'] = input_clean($_POST['name'], 64);
            $_POST['description'] = $purifier->purify(mb_substr($_POST['description'], 0, 512));

            /* Prices */
            $prices = [
                'monthly' => [],
                'quarterly' => [],
                'biannual' => [],
                'annual' => [],
                'lifetime' => [],
            ];

            foreach((array) settings()->payment->currencies as $currency => $currency_data) {
                $prices['monthly'][$currency] = (float) $_POST['monthly_price'][$currency];
                $prices['quarterly'][$currency] = (float) $_POST['quarterly_price'][$currency];
                $prices['biannual'][$currency] = (float) $_POST['biannual_price'][$currency];
                $prices['annual'][$currency] = (float) $_POST['annual_price'][$currency];
                $prices['lifetime'][$currency] = (float) $_POST['lifetime_price'][$currency];
            }

            $prices = json_encode($prices);

            $_POST['settings'] = [
                'no_ads'                     => isset($_POST['no_ads']),
                'white_labeling_is_enabled' => isset($_POST['white_labeling_is_enabled']),
                'export' => [
                    'pdf'                           => isset($_POST['export']) && in_array('pdf', $_POST['export']),
                    'csv'                           => isset($_POST['export']) && in_array('csv', $_POST['export']),
                    'json'                          => isset($_POST['export']) && in_array('json', $_POST['export']),
                ],
                'email_reports_is_enabled'   => isset($_POST['email_reports_is_enabled']),
                'teams_is_enabled'           => isset($_POST['teams_is_enabled']),
                'websites_limit'             => (int) $_POST['websites_limit'],
                'sessions_events_limit'      => (int) $_POST['sessions_events_limit'],
                'sessions_events_retention'  => $_POST['sessions_events_retention'] > 0 ? (int) $_POST['sessions_events_retention'] : 365,
                'events_children_limit'      => (int) $_POST['events_children_limit'],
                'events_children_retention'  => $_POST['events_children_retention'] > 0 ? (int) $_POST['events_children_retention'] : 30,
                'sessions_replays_limit'     => (int) $_POST['sessions_replays_limit'],
                'sessions_replays_retention' => $_POST['sessions_replays_retention'] > 0 ? (int) $_POST['sessions_replays_retention'] : 30,
                'sessions_replays_time_limit' => $_POST['sessions_replays_time_limit'] >= 1 ? (int) $_POST['sessions_replays_time_limit'] : 10,
                'websites_heatmaps_limit'     => (int) $_POST['websites_heatmaps_limit'],
                'websites_goals_limit'        => (int) $_POST['websites_goals_limit'],
                'annotations_limit'           => (int) $_POST['annotations_limit'],
                'dashboard_views_limit'       => (int) $_POST['dashboard_views_limit'],
                'domains_limit'               => (int) $_POST['domains_limit'],
                'api_is_enabled' => isset($_POST['api_is_enabled']),
                'affiliate_commission_percentage'   => (int) $_POST['affiliate_commission_percentage'],
                'additional_domains'                => $_POST['additional_domains'] ?? [],
            ];

            $_POST['settings']['custom_redirect_url'] = get_url($_POST['custom_redirect_url']);
            $_POST['settings']['tag'] = input_clean($_POST['tag'], 64);

            $_POST['settings'] = json_encode($_POST['settings']);


            $_POST['color'] = !verify_hex_color($_POST['color']) ? null : $_POST['color'];
            $_POST['suggested_plan_id'] = !empty($_POST['suggested_plan_id']) && array_key_exists($_POST['suggested_plan_id'], $plans) ? (int) $_POST['suggested_plan_id'] : null;
            $_POST['suggested_plan_code_id'] = !empty($_POST['suggested_plan_code_id']) ? (int) $_POST['suggested_plan_code_id'] : null;
            $_POST['status'] = (int) $_POST['status'];
            $_POST['order'] = (int) $_POST['order'];
            $_POST['trial_days'] = (int) $_POST['trial_days'];
            $_POST['taxes_ids'] = json_encode($_POST['taxes_ids'] ?? []);

            /* Initiate purifier */
            $purifier_config = \HTMLPurifier_Config::createDefault();
        $purifier_config->set('Cache.SerializerPath', UPLOADS_PATH . 'cache');
            $purifier_config->set('HTML.Allowed', 'span[style]');
            $purifier_config->set('CSS.AllowedProperties', 'color,font-weight,font-style,text-decoration,font-family,background-color,text-transform,margin,padding,text-align');
            $purifier = new \HTMLPurifier($purifier_config);

            /* Translations */
            foreach($_POST['translations'] as $language_name => $array) {
                foreach($array as $key => $value) {
                    if($key == 'description') {
                        $_POST['translations'][$language_name][$key] = $purifier->purify(mb_substr($value, 0, 512));
                    } else {
                        $_POST['translations'][$language_name][$key] = input_clean($value);
                    }
                }
                if(!array_key_exists($language_name, \Altum\Language::$active_languages)) {
                    unset($_POST['translations'][$language_name]);
                }
            }

            $_POST['translations'] = json_encode($_POST['translations']);

            /* Prepare more settings */
            $_POST['tag_background_color'] = !verify_hex_color($_POST['tag_background_color']) ? null : $_POST['tag_background_color'];
            $_POST['tag_text_color'] = !verify_hex_color($_POST['tag_text_color']) ? null : $_POST['tag_text_color'];

            /* Additional settings */
            $additional_settings = [
                'tag_background_color' => $_POST['tag_background_color'],
                'tag_text_color' => $_POST['tag_text_color'],
                'suggested_plan_id' => $_POST['suggested_plan_id'],
                'suggested_plan_code_id' => $_POST['suggested_plan_code_id'],
            ];

            //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

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

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Database query */
                db()->insert('plans', [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'prices' => $prices,
                    'settings' => $_POST['settings'],
                    'additional_settings' => json_encode($additional_settings),
                    'translations' => $_POST['translations'],
                    'taxes_ids' => $_POST['taxes_ids'],
                    'color' => $_POST['color'],
                    'status' => $_POST['status'],
                    'order' => $_POST['order'],
                    'datetime' => get_date(),
                ]);

                /* Clear the cache */
                cache()->deleteItem('plans');

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('admin/plans');
            }
        }


        $suggested_next_order_number = db()->orderBy('`order`', 'DESC')->getValue('plans', '`order`', 1);
        $suggested_next_order_number = $suggested_next_order_number ? $suggested_next_order_number + 1 : 1;

        /* Set default values */
        $values = [
            'order' => $_POST['order'] ?? $suggested_next_order_number,
        ];

        /* Main View */
        $data = [
            'values' => $values,
            'taxes' => $taxes ?? null,
            'plans' => $plans,
            'codes' => $codes ?? null,
			'additional_domains' => $additional_domains,
        ];

        $view = new \Altum\View('admin/plan-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
