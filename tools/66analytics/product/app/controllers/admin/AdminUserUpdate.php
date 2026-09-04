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

class AdminUserUpdate extends Controller {

    public function index() {

        $user_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Check if user exists */
        if(!$user = db()->where('user_id', $user_id)->getOne('users')) {
            redirect('admin/users');
        }

        $user->plan_settings = json_decode($user->plan_settings);

		$additional_domains = db()->where('is_enabled', 1)->where('type', 1)->get('domains');

        if(!empty($_POST)) {
            /* Filter some of the variables */
            $_POST['name'] = input_clean($_POST['name']);
            $_POST['status'] = (int) $_POST['status'];
            $_POST['type'] = (int) $_POST['type'];
            $_POST['plan_trial_done'] = (int)isset($_POST['plan_trial_done']);

            if(\Altum\Plugin::is_active('affiliate')) {
                $_POST['referred_by'] = !empty($_POST['referred_by']) ? (int) $_POST['referred_by'] : null;
            }

            switch ($_POST['plan_id']) {
                case 'free':

                    $plan_settings = json_encode(settings()->plan_free->settings ?? '');

                    break;

                case 'custom':

                    $plan_settings = json_encode([
                        'no_ads' => isset($_POST['no_ads']),
                        'white_labeling_is_enabled' => isset($_POST['white_labeling_is_enabled']),
                'export' => [
                            'pdf'                           => isset($_POST['export']) && in_array('pdf', $_POST['export']),
                            'csv'                           => isset($_POST['export']) && in_array('csv', $_POST['export']),
                            'json'                          => isset($_POST['export']) && in_array('json', $_POST['export']),
                        ],
                        'email_reports_is_enabled' => isset($_POST['email_reports_is_enabled']),
                        'teams_is_enabled' => isset($_POST['teams_is_enabled']),
                        'websites_limit' => (int) $_POST['websites_limit'],
                        'sessions_events_limit' => (int) $_POST['sessions_events_limit'],
                        'sessions_events_retention'   => $_POST['sessions_events_retention'] > 0 ? (int) $_POST['sessions_events_retention'] : 365,
                        'events_children_limit' => (int) $_POST['events_children_limit'],
                        'events_children_retention' => $_POST['events_children_retention'] > 0 ? (int) $_POST['events_children_retention'] : 30,
                        'sessions_replays_limit' => (int) $_POST['sessions_replays_limit'],
                        'sessions_replays_retention' => $_POST['sessions_replays_retention'] > 0 ? (int) $_POST['sessions_replays_retention'] : 30,
                        'sessions_replays_time_limit' => $_POST['sessions_replays_time_limit'] >= 1 ? (int) $_POST['sessions_replays_time_limit'] : 10,
                        'websites_heatmaps_limit' => (int) $_POST['websites_heatmaps_limit'],
                        'websites_goals_limit' => (int) $_POST['websites_goals_limit'],
                        'annotations_limit' => (int) $_POST['annotations_limit'],
                        'dashboard_views_limit' => (int) $_POST['dashboard_views_limit'],
                        'domains_limit' => (int) $_POST['domains_limit'],
                        'api_is_enabled' => isset($_POST['api_is_enabled']),
                        'affiliate_commission_percentage' => (int) $_POST['affiliate_commission_percentage'],
                        'additional_domains'                => $_POST['additional_domains'] ?? [],
                    ]);

                    break;

                default:

                    $_POST['plan_id'] = (int) $_POST['plan_id'];

                    /* Make sure this plan exists */
                    if(!$plan_settings = db()->where('plan_id', $_POST['plan_id'])->getValue('plans', 'settings')) {
                        redirect('admin/user-update/' . $user->user_id);
                    }

                    break;
            }

            $_POST['plan_expiration_date'] = \Altum\Date::validate($_POST['plan_expiration_date'], 'Y-m-d') || \Altum\Date::validate($_POST['plan_expiration_date'], 'Y-m-d H:i:s') ? $_POST['plan_expiration_date'] : '';
            $_POST['plan_expiration_date'] = (new \DateTime($_POST['plan_expiration_date']))->format('Y-m-d H:i:s');

            //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

            /* Check for any errors */
            $required_fields = ['name', 'email'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }
            if(mb_strlen($_POST['name']) < 1 || mb_strlen($_POST['name']) > 64) {
                Alerts::add_field_error('name', l('admin_users.error_message.name_length'));
            }
            if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) == false) {
                //ALTUMCODE:DEMO if(DEMO) {
                Alerts::add_field_error('email', l('global.error_message.invalid_email'));
                //ALTUMCODE:DEMO }
            }

            if(db()->where('email', $_POST['email'])->has('users') && $_POST['email'] !== $user->email) {
                Alerts::add_field_error('email', l('admin_users.error_message.email_exists'));
            }

            if(!empty($_POST['new_password']) && !empty($_POST['repeat_password'])) {
                if(mb_strlen($_POST['new_password']) < 6 || mb_strlen($_POST['new_password']) > 64) {
                    Alerts::add_field_error('new_password', l('global.error_message.password_length'));
                }
                if($_POST['new_password'] !== $_POST['repeat_password']) {
                    Alerts::add_field_error('repeat_password', l('global.error_message.passwords_not_matching'));
                }
            }

            /* If there are no errors, continue */
            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Update the basic user settings */
                db()->where('user_id', $user->user_id)->update('users', [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'status' => $_POST['status'],
                    'type' => $_POST['type'],
                    'plan_id' => $_POST['plan_id'],
                    'plan_expiration_date' => $_POST['plan_expiration_date'],
                    'plan_expiry_reminder' => $user->plan_expiration_date != $_POST['plan_expiration_date'] ? 0 : 1,
                    'plan_renewal_reminder' => $user->plan_expiration_date != $_POST['plan_expiration_date'] ? 0 : $user->plan_renewal_reminder,
                    'plan_settings' => $plan_settings,
                    'plan_trial_done' => $_POST['plan_trial_done'],
                    'referred_by' => $user->referred_by != $_POST['referred_by'] ? $_POST['referred_by'] : $user->referred_by,
                ]);

                /* Update the password if set */
				if(!empty($_POST['new_password']) && !empty($_POST['repeat_password'])) {
					$new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

					/* Database query */
					db()->where('user_id', $user->user_id)->update('users', ['password' => $new_password]);
				}

				/* Manage broadcast subscription */
				if(settings()->content->broadcasts_is_enabled) {
					$broadcast_subscriber = db()->where('email', $user->email)->getOne('broadcast_subscribers');

					if($broadcast_subscriber) {
						/* Keep subscriber details fresh */
						$broadcast_subscriber_update_data = [
							'user_id' => $user->user_id,
							'name' => $_POST['name'],
							'email' => $_POST['email'],
							'last_datetime' => get_date(),
						];

						if(!$broadcast_subscriber->unsubscribe_code) {
							$broadcast_subscriber_update_data['unsubscribe_code'] = md5(uniqid('', true) . random_bytes(16));
						}

						db()->where('broadcast_subscriber_id', $broadcast_subscriber->broadcast_subscriber_id)->update('broadcast_subscribers', $broadcast_subscriber_update_data);
					}
				}

                /* Update all websites if any */
                if(settings()->sso->is_enabled && count((array) settings()->sso->websites)) {
                    foreach(settings()->sso->websites as $website) {
                        $response = \Unirest\Request::post(
                            $website->url . 'admin-api/sso/update',
                            ['Authorization' => 'Bearer ' . $website->api_key],
                            \Unirest\Request\Body::form([
                                'name' => $_POST['name'],
                                'email' => $user->email,
                                'new_email' => $_POST['email'],
                            ])
                        );
                    }
                }

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItemsByTag('user_id=' . $user->user_id);

                redirect('admin/user-update/' . $user->user_id);

            }
        }

        /* Get all the plans available */
        $plans = db()->where('status', 0, '<>')->get('plans');

        /* Main View */
        $data = [
            'user' => $user,
            'plans' => $plans,
			'additional_domains' => $additional_domains,
        ];

        $view = new \Altum\View('admin/user-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
