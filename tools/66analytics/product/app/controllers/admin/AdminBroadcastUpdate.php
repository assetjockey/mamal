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

class AdminBroadcastUpdate extends Controller {

    public function index() {

        $broadcast_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$broadcast = db()->where('broadcast_id', $broadcast_id)->getOne('broadcasts')) {
            redirect('admin/broadcasts');
        }

        if($broadcast->status == 'processing') {
            Alerts::add_error(l('admin_broadcast_update.error_message.processing'));
            redirect('admin/broadcasts');
        }

        $broadcast->settings = json_decode($broadcast->settings ?? '');
        $broadcast->users_ids = implode(',', json_decode($broadcast->users_ids));

        $plans = (new \Altum\Models\Plan())->get_plans();

		if(!empty($_POST)) {
			/* Filter some of the variables */
			$_POST['name'] = input_clean($_POST['name'], 64);
			$_POST['subject'] = input_clean($_POST['subject'], 128);

			/* Sent broadcasts have these fields disabled in the form, so use the existing values */
			if($broadcast->status == 'sent') {
				$_POST['segment'] = $broadcast->segment;
				$_POST['is_system_email'] = (int) ($broadcast->settings->is_system_email ?? 0);
			} else {
				$_POST['segment'] = isset($_POST['segment']) && in_array($_POST['segment'], ['all', 'custom', 'filter']) ? input_clean($_POST['segment']) : 'all';
				$_POST['is_system_email'] = (int) isset($_POST['is_system_email']);
			}

			/* Users ids */
			$_POST['users_ids'] = trim($_POST['users_ids'] ?? '');
			$_POST['users_ids'] = array_filter(array_map('intval', explode(',', $_POST['users_ids'])));
			$_POST['users_ids'] = array_values(array_unique($_POST['users_ids']));
			$_POST['users_ids'] = $_POST['users_ids'] ?: [0];

			/* Broadcast subscribers ids */
			$_POST['broadcast_subscribers_ids'] = trim($_POST['broadcast_subscribers_ids'] ?? '');
			$_POST['broadcast_subscribers_ids'] = array_filter(array_map('intval', explode(',', $_POST['broadcast_subscribers_ids'])));
			$_POST['broadcast_subscribers_ids'] = array_values(array_unique($_POST['broadcast_subscribers_ids']));
			$_POST['broadcast_subscribers_ids'] = $_POST['broadcast_subscribers_ids'] ?: [0];

			//ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

			if(!\Altum\Csrf::check()) {
				Alerts::add_error(l('global.error_message.invalid_csrf_token'));
			}

			/* Preview email */
			if(isset($_POST['preview'])) {
				$_POST['preview_email'] = mb_substr(filter_var($_POST['preview_email'], FILTER_SANITIZE_EMAIL), 0, 320);

				$required_fields = ['subject', 'content', 'preview_email'];
				foreach($required_fields as $field) {
					if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
						Alerts::add_field_error($field, l('global.error_message.empty_field'));
					}
				}

				if(filter_var($_POST['preview_email'], FILTER_VALIDATE_EMAIL) == false) {
					Alerts::add_field_error('preview_email', l('global.error_message.invalid_email'));
				}
			}

			/* Save draft or send */
			else {
				$required_fields = ['name', 'subject', 'content'];
				foreach($required_fields as $field) {
					if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
						Alerts::add_field_error($field, l('global.error_message.empty_field'));
					}
				}
			}

			if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

				/* Preview email */
				if(isset($_POST['preview'])) {
					$vars = [
						'{{USER:NAME}}' => $this->user->name,
						'{{USER:EMAIL}}' => $this->user->email,
						'{{USER:CONTINENT_NAME}}' => get_continent_from_continent_code($this->user->continent_code),
						'{{USER:COUNTRY_NAME}}' => get_country_from_country_code($this->user->country),
						'{{USER:CITY_NAME}}' => $this->user->city_name,
						'{{USER:DEVICE_TYPE}}' => l('global.device.' . $this->user->device_type),
						'{{USER:OS_NAME}}' => $this->user->os_name,
						'{{USER:BROWSER_NAME}}' => $this->user->browser_name,
						'{{USER:BROWSER_LANGUAGE}}' => get_language_from_locale($this->user->browser_language),
					];

					$email_template = get_email_template(
						$vars,
						htmlspecialchars_decode($_POST['subject']),
						$vars,
						convert_editorjs_json_to_html($_POST['content'])
					);

					send_mail($_POST['preview_email'], $email_template->subject, $email_template->body, ['is_broadcast' => true, 'is_system_email' => $_POST['is_system_email'], 'anti_phishing_code' => $this->user->anti_phishing_code, 'language' => $this->user->language], $_POST['preview_email']);

					/* Set a nice success message */
					Alerts::add_success(sprintf(l('admin_broadcast_create.success_message.preview'), '<strong>' . $_POST['preview_email'] . '</strong>'));
				}

				if(isset($_POST['save']) || isset($_POST['send'])) {

					/*
					 * Sent broadcasts can only have their name changed.
					 * Do not recalculate recipients or overwrite settings/content.
					 */
					if($broadcast->status == 'sent') {
						db()->where('broadcast_id', $broadcast->broadcast_id)->update('broadcasts', [
							'name' => $_POST['name'],
							'last_datetime' => get_date(),
						]);
					}

					else {
						$settings = [
							'is_system_email' => $_POST['is_system_email'],
						];

						$table = $_POST['is_system_email'] ? 'users' : 'broadcast_subscribers';
						$id_column = $_POST['is_system_email'] ? 'user_id' : 'broadcast_subscriber_id';

						/* Get all the recipients needed */
						switch($_POST['segment']) {
							case 'all':
								$query = db();

								if(!$_POST['is_system_email']) {
									$query->where('status', 1);
								}

								$recipients = $query->get($table, null, [$id_column]);
								break;

							case 'custom':
								$query = db();

								if(!$_POST['is_system_email']) {
									$query->where('status', 1);
								}

								$ids = $_POST['is_system_email'] ? $_POST['users_ids'] : $_POST['broadcast_subscribers_ids'];
								$recipients = $query->where($id_column, $ids, 'IN')->get($table, null, [$id_column]);
								break;

							case 'filter':
								$query = db();
								$has_filters = false;

								/* Newsletter subscriber filters */
								if(!$_POST['is_system_email']) {
									/* Only active subscribers can receive newsletter broadcasts */
									$query->where('status', 1);

									/* Subscriber type */
									if(isset($_POST['filters_subscriber_type'])) {
										$_POST['filters_subscriber_type'] = array_values(array_intersect($_POST['filters_subscriber_type'], ['registered', 'guest']));

										if(count($_POST['filters_subscriber_type'])) {
											$has_filters = true;
											$settings['filters_subscriber_type'] = $_POST['filters_subscriber_type'];

											if(count($_POST['filters_subscriber_type']) == 1) {
												if(in_array('registered', $_POST['filters_subscriber_type'])) {
													$query->where('user_id', null, 'IS NOT');
												} else {
													$query->where('user_id', null, 'IS');
												}
											}
										}
									}

									/* Subscription source */
									if(isset($_POST['filters_broadcast_subscribers_source'])) {
										$_POST['filters_broadcast_subscribers_source'] = array_values(array_intersect($_POST['filters_broadcast_subscribers_source'], ['index', 'register', 'account']));

										if(count($_POST['filters_broadcast_subscribers_source'])) {
											$has_filters = true;
											$query->where('source', $_POST['filters_broadcast_subscribers_source'], 'IN');
											$settings['filters_broadcast_subscribers_source'] = $_POST['filters_broadcast_subscribers_source'];
										}
									}
								}

								/* System email filters */
								else {

									/* Plans */
									if(isset($_POST['filters_plans'])) {
										$has_filters = true;
										$query->where('plan_id', $_POST['filters_plans'], 'IN');
										$settings['filters_plans'] = $_POST['filters_plans'];
									}

									/* Status */
									if(isset($_POST['filters_status'])) {
										$has_filters = true;
										$query->where('status', $_POST['filters_status'], 'IN');
										$settings['filters_status'] = $_POST['filters_status'];
									}

									/* Registration source */
									if(isset($_POST['filters_source'])) {
										$has_filters = true;
										$query->where('source', $_POST['filters_source'], 'IN');
										$settings['filters_source'] = $_POST['filters_source'];
									}
								}

								/* Cities */
								if(!empty($_POST['filters_cities'])) {
									$_POST['filters_cities'] = explode(',', $_POST['filters_cities']);
									$_POST['filters_cities'] = array_filter(array_unique($_POST['filters_cities']));

									if(count($_POST['filters_cities'])) {
										$_POST['filters_cities'] = array_map(function($city) {
											return query_clean(trim($city));
										}, $_POST['filters_cities']);

										$has_filters = true;
										$query->where('city_name', $_POST['filters_cities'], 'IN');
										$settings['filters_cities'] = $_POST['filters_cities'];
									}
								}

								/* Countries */
								if(isset($_POST['filters_countries'])) {
									$has_filters = true;
									$query->where($_POST['is_system_email'] ? 'country' : 'country_code', $_POST['filters_countries'], 'IN');
									$settings['filters_countries'] = $_POST['filters_countries'];
								}

								/* Continents */
								if(isset($_POST['filters_continents'])) {
									$has_filters = true;
									$query->where('continent_code', $_POST['filters_continents'], 'IN');
									$settings['filters_continents'] = $_POST['filters_continents'];
								}

								/* Device type */
								if(isset($_POST['filters_device_type'])) {
									$has_filters = true;
									$query->where('device_type', $_POST['filters_device_type'], 'IN');
									$settings['filters_device_type'] = $_POST['filters_device_type'];
								}

								/* Languages */
								if(isset($_POST['filters_languages'])) {
									$has_filters = true;
									$query->where('language', $_POST['filters_languages'], 'IN');
									$settings['filters_languages'] = $_POST['filters_languages'];
								}

								/* Browser languages */
								if(isset($_POST['filters_browser_languages'])) {
									$_POST['filters_browser_languages'] = array_filter($_POST['filters_browser_languages'], function($locale) {
										return array_key_exists($locale, get_locale_languages_array());
									});

									if(count($_POST['filters_browser_languages'])) {
										$has_filters = true;
										$query->where('browser_language', $_POST['filters_browser_languages'], 'IN');
										$settings['filters_browser_languages'] = $_POST['filters_browser_languages'];
									}
								}

								/* Filters operating systems */
								if(isset($_POST['filters_operating_systems'])) {
									$_POST['filters_operating_systems'] = array_filter($_POST['filters_operating_systems'], function($os_name) {
										return in_array($os_name, ['iOS', 'Android', 'Windows', 'OS X', 'Linux', 'Ubuntu', 'Chrome OS']);
									});

									if(count($_POST['filters_operating_systems'])) {
										$has_filters = true;
										$query->where('os_name', $_POST['filters_operating_systems'], 'IN');
										$settings['filters_operating_systems'] = $_POST['filters_operating_systems'];
									}
								}

								/* Filters browsers */
								if(isset($_POST['filters_browsers'])) {
									$_POST['filters_browsers'] = array_filter($_POST['filters_browsers'], function($browser_name) {
										return in_array($browser_name, ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera', 'Samsung Internet']);
									});

									if(count($_POST['filters_browsers'])) {
										$has_filters = true;
										$query->where('browser_name', $_POST['filters_browsers'], 'IN');
										$settings['filters_browsers'] = $_POST['filters_browsers'];
									}
								}

								$recipients = $has_filters ? $query->get($table, null, [$id_column]) : [];

								break;
						}

						/* Get all recipient ids */
						$recipients_ids = array_column($recipients, $id_column);

						/* Free memory */
						unset($recipients);

						/* Database query */
						db()->where('broadcast_id', $broadcast->broadcast_id)->update('broadcasts', [
							'name' => $_POST['name'],
							'subject' => $_POST['subject'],
							'content' => $_POST['content'],
							'segment' => $_POST['segment'],
							'settings' => json_encode($settings),
							'users_ids' => json_encode($recipients_ids),
							'sent_users_ids' => '[]',
							'total_emails' => count($recipients_ids),
							'status' => isset($_POST['save']) ? 'draft' : 'processing',
							'last_datetime' => get_date(),
						]);
					}

					if(isset($_POST['save'])) {
						/* Set a nice success message */
						Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));
					} else {
						/* Set a nice success message */
						Alerts::add_success(sprintf(l('admin_broadcast_create.success_message.send'), '<strong>' . $_POST['name'] . '</strong>'));

						redirect('admin/broadcasts');
					}
				}

				/* Refresh the page */
				redirect('admin/broadcast-update/' . $broadcast_id);
			}
		}

        /* Main View */
        $data = [
            'broadcast_id' => $broadcast_id,
            'broadcast' => $broadcast,
            'plans' => $plans,
        ];

        $view = new \Altum\View('admin/broadcast-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
