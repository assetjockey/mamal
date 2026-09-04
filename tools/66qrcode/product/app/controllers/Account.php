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

class Account extends Controller {

	public function index() {

		\Altum\Authentication::guard();

		/* Prepare the TwoFA codes just in case we need them */
		$twofa = new \RobThree\Auth\TwoFactorAuth(new \RobThree\Auth\Providers\Qr\BaconQrCodeProvider(format: 'svg'), settings()->main->title, 6, 30);
		$twofa_secret = $twofa->createSecret();
		$twofa_image = $twofa->getQRCodeImageAsDataUri($this->user->email . ' - ' . $this->user->name, $twofa_secret, 400);

		if(!empty($_POST)) {

			/* Clean some posted variables */
			$this->user->avatar = \Altum\Uploads::process_upload($this->user->avatar, 'users', 'avatar', 'avatar_remove', settings()->main->avatar_size_limit);
			$_POST['email'] = input_clean_email($_POST['email'] ?? '');
			$_POST['name'] = input_clean_name($_POST['name'], 64);
			$_POST['timezone'] = in_array($_POST['timezone'], \DateTimeZone::listIdentifiers()) ? query_clean($_POST['timezone']) : settings()->main->default_timezone;
			$_POST['anti_phishing_code'] = input_clean($_POST['anti_phishing_code'], 8);
			$_POST['twofa_is_enabled'] = (bool) $_POST['twofa_is_enabled'];
			$_POST['twofa_token'] = input_clean(str_replace(' ', '', $_POST['twofa_token'] ?? ''));
			$_POST['is_broadcast_subscribed'] = (int) isset($_POST['is_broadcast_subscribed']);
			$twofa_secret = $_POST['twofa_is_enabled'] ? $this->user->twofa_secret : null;

			if(\Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled) {
				$_POST['referral_key'] = input_clean($_POST['referral_key'], 32);
			} else {
				$_POST['referral_key'] = $this->user->referral_key;
			}

			/* Billing */
			if(empty($this->user->payment_subscription_id)) {
				$_POST['billing_type'] = in_array($_POST['billing_type'], ['personal', 'business']) ? query_clean($_POST['billing_type']) : 'personal';
				$_POST['billing_name'] = input_clean($_POST['billing_name'], 128);
				$_POST['billing_address'] = input_clean($_POST['billing_address'], 128);
				$_POST['billing_city'] = input_clean($_POST['billing_city'], 64);
				$_POST['billing_state'] = input_clean($_POST['billing_state'], 64);
				$_POST['billing_county'] = input_clean($_POST['billing_county'], 64);
				$_POST['billing_zip'] = input_clean($_POST['billing_zip'], 32);
				$_POST['billing_country'] = array_key_exists($_POST['billing_country'], get_countries_array()) ? query_clean($_POST['billing_country']) : 'US';
				$_POST['billing_phone'] = input_clean($_POST['billing_phone'], 32);
				$_POST['billing_tax_id'] = $_POST['billing_type'] == 'business' ? input_clean($_POST['billing_tax_id'], 64) : '';
				$_POST['billing_notes'] = input_clean($_POST['billing_notes'], 512);
				$_POST['billing'] = json_encode([
					'type' => $_POST['billing_type'],
					'name' => $_POST['billing_name'],
					'address' => $_POST['billing_address'],
					'city' => $_POST['billing_city'],
					'state' => $_POST['billing_state'],
					'county' => $_POST['billing_county'],
					'zip' => $_POST['billing_zip'],
					'country' => $_POST['billing_country'],
					'phone' => $_POST['billing_phone'],
					'tax_id' => $_POST['billing_tax_id'],
					'notes' => $_POST['billing_notes'],
				]);
			}

			//ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

			/* Check for any errors */
			if(!\Altum\Csrf::check()) {
				Alerts::add_error(l('global.error_message.invalid_csrf_token'));
			}

			if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) == false) {
				Alerts::add_field_error('email', l('global.error_message.invalid_email'));
			}

			if(db()->where('email', $_POST['email'])->has('users') && $_POST['email'] !== $this->user->email) {
				Alerts::add_field_error('email', l('register.error_message.email_exists'));
			}

			if(!settings()->users->email_aliases_is_enabled && str_contains($_POST['email'], '+')) {
				Alerts::add_field_error('email', l('register.error_message.email_aliases_not_allowed'));
			}

			/* Make sure the domain is not blacklisted */
			$email_domain = get_domain_from_email($_POST['email']);
			if(settings()->users->blacklisted_domains && in_array($email_domain, settings()->users->blacklisted_domains)) {
				Alerts::add_field_error('email', l('register.error_message.blacklisted_domain'));
			}

			/* Email shield plugin */
			if(
				\Altum\Plugin::is_active('email-shield')
				&& settings()->email_shield->is_enabled
				&& !in_array($email_domain, settings()->email_shield->whitelisted_domains ?? [])
				&& !\Altum\Plugin\EmailShield::validate($email_domain)
			) {
				Alerts::add_field_error('email', l('register.error_message.blacklisted_domain'));
			}

			if(db()->where('referral_key', $_POST['referral_key'])->has('users') && $_POST['referral_key'] !== $this->user->referral_key) {
				Alerts::add_field_error('referral_key', l('account.error_message.referral_key_exists'));
			}

			if(mb_strlen($_POST['name']) < 1 || mb_strlen($_POST['name']) > 64) {
				Alerts::add_field_error('name', l('register.error_message.name_length'));
			}

			if(!empty($_POST['old_password']) && !empty($_POST['new_password'])) {
				$_POST['old_password'] = mb_substr($_POST['old_password'], 0, 64);
				$_POST['new_password'] = mb_substr($_POST['new_password'], 0, 64);

				if(!password_verify($_POST['old_password'], $this->user->password)) {
					Alerts::add_field_error('old_password', l('account.error_message.invalid_current_password'));
				}
				if(mb_strlen($_POST['new_password']) < 6) {
					Alerts::add_field_error('new_password', l('global.error_message.password_length'));
				}
				if($_POST['new_password'] !== $_POST['repeat_password']) {
					Alerts::add_field_error('repeat_password', l('global.error_message.passwords_not_matching'));
				}
			}

			if($_POST['twofa_is_enabled'] && $_POST['twofa_token']) {
				$twofa_check = $twofa->verifyCode(session_get('twofa_potential_secret'), $_POST['twofa_token']);

				if(!$twofa_check) {
					Alerts::add_field_error('twofa_token', l('account.error_message.twofa_check'));

					/* Regenerate */
					$twofa_secret = $twofa->createSecret();
					$twofa_image = $twofa->getQRCodeImageAsDataUri($this->user->email . ' - ' . $this->user->name, $twofa_secret, 400);

				} else {
					$twofa_secret = session_get('twofa_potential_secret');
				}

			}

			if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

				/* Only update the billing if no active subscriptions are found */
				if(!empty($this->user->payment_subscription_id)) {
					$_POST['billing'] = json_encode($this->user->billing);
				}

				/* Database query */
				db()->where('user_id', $this->user->user_id)->update('users', [
					'avatar' => $this->user->avatar,
					'name' => $_POST['name'],
					'billing' => $_POST['billing'],
					'timezone' => $_POST['timezone'],
					'twofa_secret' => $twofa_secret,
					'anti_phishing_code' => $_POST['anti_phishing_code'],
					'is_broadcast_subscribed' => $_POST['is_broadcast_subscribed'],
					'referral_key' => $_POST['referral_key'],
				]);

				/* Keep broadcasts on confirmed email */
				$broadcast_subscriber_email = $this->user->email;

				/* Manage broadcast subscription */
				if(
					settings()->content->broadcasts_is_enabled
					&& $this->user->is_broadcast_subscribed != $_POST['is_broadcast_subscribed']
				) {

					/* Subscribe */
					if($_POST['is_broadcast_subscribed']) {
						$broadcast_subscriber = db()->where('email', $broadcast_subscriber_email)->getOne('broadcast_subscribers');
						$is_new_subscription = !$broadcast_subscriber || $broadcast_subscriber->status != 1;

						if($broadcast_subscriber) {
							$broadcast_subscriber_id = $broadcast_subscriber->broadcast_subscriber_id;

							/* Update subscriber status */
							$broadcast_subscriber_update_data = [
								'user_id' => $this->user->user_id,
								'name' => $_POST['name'],
								'status' => 1,
								'email_activation_code' => null,
								'unsubscribed_datetime' => null,
								'last_datetime' => get_date(),
							];

							if(!$broadcast_subscriber->unsubscribe_code) {
								$broadcast_subscriber_update_data['unsubscribe_code'] = md5(uniqid('', true) . random_bytes(16));
							}

							db()->where('broadcast_subscriber_id', $broadcast_subscriber->broadcast_subscriber_id)->update('broadcast_subscribers', $broadcast_subscriber_update_data);
						} else {
							$broadcast_subscriber_id = db()->insert('broadcast_subscribers', [
								'user_id' => $this->user->user_id,
								'email' => $broadcast_subscriber_email,
								'name' => $_POST['name'],
								'source' => 'account',
								'language' => $this->user->language,
								'ip' => $this->user->ip,
								'continent_code' => $this->user->continent_code,
								'country_code' => $this->user->country,
								'city_name' => $this->user->city_name,
								'device_type' => $this->user->device_type,
								'browser_language' => $this->user->browser_language,
								'browser_name' => $this->user->browser_name,
								'os_name' => $this->user->os_name,
								'status' => 1,
								'email_activation_code' => null,
								'unsubscribe_code' => md5(uniqid('', true) . random_bytes(16)),
								'unsubscribed_datetime' => null,
								'last_datetime' => get_date(),
								'datetime' => get_date(),
							]);
						}

						/* Send webhook notification if needed */
						if($is_new_subscription && settings()->webhooks->broadcast_subscriber_new) {
							$broadcast_subscriber = db()->where('broadcast_subscriber_id', $broadcast_subscriber_id)->getOne('broadcast_subscribers');

							if($broadcast_subscriber) {
								fire_and_forget('post', settings()->webhooks->broadcast_subscriber_new, [
									'broadcast_subscriber_id' => $broadcast_subscriber->broadcast_subscriber_id,
									'user_id' => $broadcast_subscriber->user_id,
									'email' => $broadcast_subscriber->email,
									'name' => $broadcast_subscriber->name,
									'source' => $broadcast_subscriber->source,
									'status' => $broadcast_subscriber->status,
									'language' => $broadcast_subscriber->language,
									'ip' => $broadcast_subscriber->ip,
									'country_code' => $broadcast_subscriber->country_code,
									'city_name' => $broadcast_subscriber->city_name,
									'unsubscribed_datetime' => $broadcast_subscriber->unsubscribed_datetime,
									'last_datetime' => $broadcast_subscriber->last_datetime,
									'datetime' => $broadcast_subscriber->datetime,
								], signature: true);
							}
						}
					}

					/* Unsubscribe */
					else {
						$broadcast_subscriber = db()->where('user_id', $this->user->user_id)->getOne('broadcast_subscribers');
						$datetime = get_date();

						db()->where('user_id', $this->user->user_id)->update('broadcast_subscribers', [
							'status' => 2,
							'unsubscribed_datetime' => $datetime,
							'last_datetime' => $datetime,
						]);

						/* Send webhook notification if needed */
						if($broadcast_subscriber && $broadcast_subscriber->status == 1 && settings()->webhooks->broadcast_subscriber_unsubscribe) {
							fire_and_forget('post', settings()->webhooks->broadcast_subscriber_unsubscribe, [
								'broadcast_subscriber_id' => $broadcast_subscriber->broadcast_subscriber_id,
								'user_id' => $broadcast_subscriber->user_id,
								'email' => $broadcast_subscriber->email,
								'name' => $broadcast_subscriber->name,
								'source' => $broadcast_subscriber->source,
								'status' => 2,
								'language' => $broadcast_subscriber->language,
								'ip' => $broadcast_subscriber->ip,
								'country_code' => $broadcast_subscriber->country_code,
								'city_name' => $broadcast_subscriber->city_name,
								'unsubscribed_datetime' => $datetime,
								'last_datetime' => $datetime,
								'datetime' => $broadcast_subscriber->datetime,
							], signature: true);
						}
					}
				}

				/* Log the action */
				\Altum\Logger::users($this->user->user_id, 'account.updated');

				/* Set a nice success message */
				Alerts::add_success(l('account.success_message.account_updated'));

				/* Update all websites if any */
				if(settings()->sso->is_enabled && count((array) settings()->sso->websites)) {
					foreach(settings()->sso->websites as $website) {
						$response = \Unirest\Request::post(
							$website->url . 'admin-api/sso/update',
							['Authorization' => 'Bearer ' . $website->api_key],
							\Unirest\Request\Body::form([
								'email' => $this->user->email,
								'name' => $_POST['name'],
							])
						);
					}
				}

				/* Check for an email address change */
				if($_POST['email'] != $this->user->email) {

					if(settings()->users->email_confirmation) {
						$email_activation_code = md5(uniqid('', true) . random_bytes(16));

						/* Prepare the email */
						$email_template = get_email_template(
							[],
							l('global.emails.user_pending_email.subject'),
							[
								'{{ACTIVATION_LINK}}' => url('activate-user?email=' . md5($_POST['email']) . '&email_activation_code=' . $email_activation_code . '&type=user_pending_email'),
								'{{NAME}}' => $this->user->name,
								'{{CURRENT_EMAIL}}' => $this->user->email,
								'{{NEW_EMAIL}}' => $_POST['email'],
							],
							l('global.emails.user_pending_email.body')
						);

						send_mail($_POST['email'], $email_template->subject, $email_template->body, ['anti_phishing_code' => $this->user->anti_phishing_code, 'language' => $this->user->language]);

						/* Save the potential new email as pending */
						db()->where('user_id', $this->user->user_id)->update('users', [
							'pending_email' => $_POST['email'],
							'email_activation_code' => $email_activation_code,
						]);

						Alerts::add_info(l('account.info_message.user_pending_email'));

					} else {

						/* Save the new email without verification */
						db()->where('user_id', $this->user->user_id)->update('users', ['email' => $_POST['email']]);

						/* Use new confirmed email */
						$broadcast_subscriber_email = $_POST['email'];

						/* Sync broadcast subscriber email */
						if(settings()->content->broadcasts_is_enabled) {
							db()->where('user_id', $this->user->user_id)->update('broadcast_subscribers', [
								'email' => $_POST['email'],
								'last_datetime' => get_date(),
							]);
						}

						/* Update all websites if any */
						if(settings()->sso->is_enabled && count((array) settings()->sso->websites)) {
							foreach(settings()->sso->websites as $website) {
								$response = \Unirest\Request::post(
									$website->url . 'admin-api/sso/update',
									['Authorization' => 'Bearer ' . $website->api_key],
									\Unirest\Request\Body::form([
										'email' => $this->user->email,
										'new_email' => $_POST['email'],
									])
								);
							}
						}

					}

				}

				if(!empty($_POST['old_password']) && !empty($_POST['new_password'])) {
					$new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

					db()->where('user_id', $this->user->user_id)->update('users', ['password' => $new_password]);

					/* Logout of the user */
					\Altum\Authentication::logout(false);

					/* Start a new session to set a success message */
					session_start();

					/* Clear the cache */
					cache()->deleteItemsByTag('user_id=' . $this->user->user_id);

					/* Set a nice success message */
					Alerts::add_success(l('account.success_message.password_updated'));

					redirect('login');
				}

				/* Send internal notification if needed */
				if(settings()->internal_notifications->admins_is_enabled && settings()->internal_notifications->new_newsletter_subscriber && $_POST['is_broadcast_subscribed'] && !$this->user->is_broadcast_subscribed) {
					db()->insert('internal_notifications', [
						'for_who' => 'admin',
						'from_who' => 'system',
						'icon' => 'fas fa-newspaper',
						'title' => l('global.notifications.new_newsletter_subscriber.title'),
						'description' => sprintf(l('global.notifications.new_newsletter_subscriber.description'), $_POST['name'], $broadcast_subscriber_email),
						'url' => 'admin/user-view/' . $this->user->user_id,
						'datetime' => get_date(),
					]);
				}

				/* Send webhook notification if needed */
				if(settings()->webhooks->user_update) {
					fire_and_forget('post', settings()->webhooks->user_update, [
						'user_id' => $this->user->user_id,
						'email' => $_POST['email'],
						'name' => $_POST['name'],
						'source' => 'account',
						'datetime' => get_date(),
					], signature: true);
				}

				/* Clear the cache */
				cache()->deleteItemsByTag('user_id=' . $this->user->user_id);

				redirect('account');
			}

		}

		/* Store the potential secret */
		session_set('twofa_potential_secret', $twofa_secret);

		/* Get the account header menu */
		$menu = new \Altum\View('partials/account_header_menu', (array) $this);
		$this->add_view_content('account_header_menu', $menu->run());

		/* Prepare the view */
		$data = [
			'twofa_secret'  => $twofa_secret,
			'twofa_image'   => $twofa_image
		];

		$view = new \Altum\View('account/index', (array) $this);

		$this->add_view_content('content', $view->run($data));

	}

}
