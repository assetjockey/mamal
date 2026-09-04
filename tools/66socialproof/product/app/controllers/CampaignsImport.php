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

class CampaignsImport extends Controller {

	public function index() {

		\Altum\Authentication::guard();

		/* Team checks */
		if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.campaigns')) {
			Alerts::add_error(l('global.info_message.team_no_access'));
			redirect('campaigns');
		}


		/* Check for the plan limit */
		$total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `campaigns` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

		if($this->user->plan_settings->campaigns_limit != -1 && $total_rows >= $this->user->plan_settings->campaigns_limit) {
			Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
			redirect('campaigns');
		}

		if(!empty($_POST)) {
			if(!isset($_FILES['file'])) {
				Alerts::add_error(l('global.error_message.empty_field'));
			}

			if(!\Altum\Csrf::check()) {
				Alerts::add_error(l('global.error_message.invalid_csrf_token'));
			}

			\Altum\Uploads::validate_upload('resources_csv', 'file', get_max_upload());

			$csv_array = array_map(function($line) {
				return str_getcsv($line, ',', '"', '\\');
			}, file($_FILES['file']['tmp_name']));

			if(!$csv_array || !is_array($csv_array)) {
				Alerts::add_error(l('global.error_message.invalid_file_type'));
			}

			$headers_array = $csv_array[0];
			unset($csv_array[0]);
			reset($csv_array);

			if(!Alerts::has_errors()) {

				set_time_limit(0);

				session_write_close();

				/* Get available domains */
				$domains = (new \Altum\Models\Domain())->get_available_domains_by_user_id($this->user->user_id);

				/* Get available notification handlers */
				$notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

				/* Count the successful inserts */
				$imported_count = 0;

				foreach($csv_array as $csv_row) {
					/* Skip wrong lines */
					if(count($headers_array) != count($csv_row)) continue;

					/* Extract data */
					$data = array_combine($headers_array, $csv_row);

					/* Clean and validate input */
					$name = input_clean($data['name'] ?? '', 256);
					$domain = mb_strtolower(input_clean($data['domain'] ?? '', 256));
					$domain_id = !empty($data['domain_id']) && array_key_exists($data['domain_id'], $domains) ? (int) $data['domain_id'] : null;
					$is_enabled = isset($data['is_enabled']) ? (int) $data['is_enabled'] : 1;

					if(!$name || !$domain) continue;

					/* Normalize domain */
					if(string_starts_with('http://', $domain) || string_starts_with('https://', $domain)) {
						if(function_exists('idn_to_utf8')) {
							$domain = parse_url(idn_to_utf8($domain), PHP_URL_HOST);
						}
					} else {
						if(function_exists('idn_to_utf8')) {
							$domain = parse_url(idn_to_utf8('https://' . $domain), PHP_URL_HOST);
						}
					}

					if(function_exists('idn_to_ascii')) {
						$domain = idn_to_ascii($domain);
					}

					/* Skip blacklisted domains */
					if(in_array($domain, settings()->notifications->blacklisted_domains)) continue;

					/* Notification handlers */
					$email_reports = [];
					if(!empty($data['email_reports'])) {
						$email_reports_raw = explode(',', $data['email_reports']);
						$email_reports_raw = array_map('trim', $email_reports_raw);

						$email_reports = array_map(
							'intval',
							array_filter($email_reports_raw, function($notification_handler_id) use ($notification_handlers) {
								return array_key_exists($notification_handler_id, $notification_handlers);
							})
						);

						if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
							$email_reports = array_slice($email_reports, 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
						}
					}

					/* Generate a unique pixel key */
					$pixel_key = string_generate(32);
					while(db()->where('pixel_key', $pixel_key)->getValue('campaigns', 'pixel_key')) {
						$pixel_key = string_generate(32);
					}

					/* Branding */
					$branding_name = '';
					$branding_url = '';

					if($this->user->plan_settings->custom_branding) {
						if(!empty($data['branding_name']) || !empty($data['branding_url'])) {

							$branding_url = get_url($data['branding_url'] ?? '');

							/* Initiate purifier */
							$purifier_config = \HTMLPurifier_Config::createDefault();
							$purifier_config->set('Cache.SerializerPath', UPLOADS_PATH . 'cache');
							$purifier_config->set('HTML.Allowed', 'span[style]');
							$purifier_config->set('CSS.AllowedProperties', 'border-radius,color,font-weight,font-style,text-decoration,font-family,background-color,text-transform,margin,padding,text-align');
							$purifier_config->set('CSS.AllowImportant', true);
							$purifier_config->set('CSS.Proprietary', true);
							$purifier = new \HTMLPurifier($purifier_config);

							/* Clean name */
							$branding_name = $purifier->purify(mb_substr($data['branding_name'] ?? '', 0, 512));
						}
					}

					$campaign_id = db()->insert('campaigns', [
						'user_id' => $this->user->user_id,
						'domain_id' => $domain_id,
						'pixel_key' => $pixel_key,
						'name' => $name,
						'domain' => $domain,
						'branding' => json_encode([
							'name' => $branding_name,
							'url' => $branding_url,
						]),
						'email_reports' => json_encode($email_reports),
						'email_reports_last_datetime' => get_date(),
						'is_enabled' => $is_enabled,
						'datetime' => get_date(),
					]);

					/* Send webhook notification if needed */
					if(settings()->webhooks->campaign_new) {
						fire_and_forget('post', settings()->webhooks->campaign_new, [
							'user_id' => $this->user->user_id,
							'campaign_id' => $campaign_id,
							'domain_id' => $domain_id,
							'domain' => $domain,
							'datetime' => get_date(),
						], signature: true);
					}

					$imported_count++;

					/* Check against limit */
					if($this->user->plan_settings->campaigns_limit != -1 && $total_rows + $imported_count >= $this->user->plan_settings->campaigns_limit) {
						break;
					}
				}

				session_start();

				Alerts::add_success(sprintf(l('global.success_message.csv_imported'), '<strong>' . $imported_count . '</strong>', l('campaigns.title')));

				redirect('campaigns');
			}
		}

		$values = [];

		/* Prepare the view */
		$data = [
			'values' => $values
		];

		$view = new \Altum\View('campaigns-import/index', (array) $this);

		$this->add_view_content('content', $view->run($data));

	}

}
