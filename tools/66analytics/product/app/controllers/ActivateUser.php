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
use Altum\Logger;

defined('ALTUMCODE') || die();

class ActivateUser extends Controller {

	public function index() {

		$md5email = isset($_GET['email']) ? $_GET['email'] : null;
		$email_activation_code = isset($_GET['email_activation_code']) ? $_GET['email_activation_code'] : null;
		$type = isset($_GET['type']) && in_array($_GET['type'], ['user_activation', 'user_pending_email']) ? $_GET['type'] : 'user_activation';
		$redirect = process_and_get_redirect_params() ?? 'dashboard';

		if(!$md5email || !$email_activation_code) throw_404();

		/* Check if the activation code is correct */
		switch($type) {
			case 'user_activation':

				if(!$user = db()->where('email_activation_code', $email_activation_code)->getOne('users')) {
					throw_404();
				}

				if(md5($user->email) != $md5email) {
					throw_404();
				}

				/* Activate the account and reset the email_activation_code */
				db()->where('user_id', $user->user_id)->update('users', [
					'status' => 1,
					'email_activation_code' => null,
					'total_logins' => db()->inc()
				]);

				/* Activate pending broadcast subscription */
				if(settings()->content->broadcasts_is_enabled) {
					$broadcast_subscriber = db()->where('email', $user->email)->where('user_id', $user->user_id)->getOne('broadcast_subscribers');

					if($broadcast_subscriber) {
						$datetime = get_date();

						if($broadcast_subscriber->status == 0) {
							$broadcast_subscriber_update_data = [
								'status' => 1,
								'email_activation_code' => null,
								'unsubscribed_datetime' => null,
								'last_datetime' => $datetime,
							];

							if(!$broadcast_subscriber->unsubscribe_code) {
								$broadcast_subscriber_update_data['unsubscribe_code'] = md5(uniqid('', true) . random_bytes(16));
							}

							db()->where('broadcast_subscriber_id', $broadcast_subscriber->broadcast_subscriber_id)->update('broadcast_subscribers', $broadcast_subscriber_update_data);
							db()->where('user_id', $user->user_id)->update('users', ['is_broadcast_subscribed' => 1]);

							$user->is_broadcast_subscribed = 1;
							$broadcast_subscriber->status = 1;
							$broadcast_subscriber->email_activation_code = null;
							$broadcast_subscriber->unsubscribed_datetime = null;
							$broadcast_subscriber->last_datetime = $datetime;

							/* Send internal notification if needed */
							if(settings()->internal_notifications->admins_is_enabled && settings()->internal_notifications->new_newsletter_subscriber) {
								db()->insert('internal_notifications', [
									'for_who' => 'admin',
									'from_who' => 'system',
									'icon' => 'fas fa-newspaper',
									'title' => l('global.notifications.new_newsletter_subscriber.title'),
									'description' => sprintf(l('global.notifications.new_newsletter_subscriber.description'), $user->name, $user->email),
									'url' => 'admin/user-view/' . $user->user_id,
									'datetime' => $datetime,
								]);
							}

							/* Send webhook notification if needed */
							if(settings()->webhooks->broadcast_subscriber_new) {
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

						elseif($broadcast_subscriber->status == 1) {
							db()->where('user_id', $user->user_id)->update('users', ['is_broadcast_subscribed' => 1]);
							$user->is_broadcast_subscribed = 1;
						}
					}
				}

				/* Send a welcome email if needed */
				if(settings()->users->welcome_email_is_enabled) {
					$email_template = get_email_template(
						[],
						l('global.emails.user_welcome.subject'),
						[
							'{{NAME}}' => $user->name,
							'{{URL}}' => url(),
							'{{DASHBOARD_LINK}}' => url('dashboard'),
						],
						l('global.emails.user_welcome.body')
					);

					send_mail($user->email, $email_template->subject, $email_template->body);
				}

				/* Send notification to admin if needed */
				if(settings()->email_notifications->new_user && !empty(settings()->email_notifications->emails)) {
					/* Prepare the email */
					$email_template = get_email_template(
						[],
						l('global.emails.admin_new_user_notification.subject'),
						[
							'{{NAME}}' => str_replace('.', '. ', $user->name),
							'{{EMAIL}}' => $user->email,
							'{{SOURCE}}' => $user->source,
							'{{IP}}' => $user->ip,
							'{{COUNTRY_NAME}}' => $user->country ? get_country_from_country_code($user->country) : l('global.unknown'),
							'{{CITY_NAME}}' => $user->city_name ?? l('global.unknown'),
							'{{DEVICE_TYPE}}' => l('global.device.' . $user->device_type),
							'{{OS_NAME}}' => $user->os_name,
							'{{BROWSER_NAME}}' => $user->browser_name,
							'{{USER_LINK}}' => url('admin/user-view/' . $user->user_id),
						],
						l('global.emails.admin_new_user_notification.body')
					);

					send_mail(explode(',', settings()->email_notifications->emails), $email_template->subject, $email_template->body);
				}

				/* Send webhook notification if needed */
				if(settings()->webhooks->user_new) {
					fire_and_forget('post', settings()->webhooks->user_new, [
						'user_id' => $user->user_id,
						'email' => $user->email,
						'name' => $user->name,
						'source' => $user->source,
						'is_broadcast_subscribed' => $user->is_broadcast_subscribed,
						'datetime' => get_date(),
					], signature: true);
				}

				/* Send internal notification if needed */
				if(settings()->internal_notifications->admins_is_enabled && settings()->internal_notifications->new_user) {
					db()->insert('internal_notifications', [
						'for_who' => 'admin',
						'from_who' => 'system',
						'icon' => 'fas fa-user',
						'title' => l('global.notifications.new_user.title'),
						'description' => sprintf(l('global.notifications.new_user.description'), $user->name, $user->email),
						'url' => 'admin/user-view/' . $user->user_id,
						'datetime' => get_date(),
					]);
				}

				Logger::users($user->user_id, 'activate.success');

				/* Login and set a successful message */
				session_regenerate_id(true);
				session_set('user_id', $user->user_id);
				session_set('user_password_hash', md5($user->password));

				/* Set a nice success message */
				Alerts::add_success(l('activate_user.user_activation'));

				Logger::users($user->user_id, 'login.success');

				/* Clear the cache */
				cache()->deleteItemsByTag('user_id=' . $user->user_id);

				$redirect = append_query_param($redirect, 'welcome=' . $user->user_id);
				session_unset_key('redirect');

				redirect($redirect);

				break;

			case 'user_pending_email':

				if(!$user = db()->where('email_activation_code', $email_activation_code)->getOne('users', ['user_id', 'pending_email', 'email'])) {
					throw_404();
				}

				if(md5($user->pending_email) != $md5email) {
					throw_404();
				}

				/* Confirm the new email address and reset the email_activation_code */
				db()->where('user_id', $user->user_id)->update('users', [
					'email' => $user->pending_email,
					'pending_email' => null,
					'email_activation_code' => null,
				]);

				/* Sync broadcast subscriber email */
				if(settings()->content->broadcasts_is_enabled) {
					db()->where('user_id', $user->user_id)->update('broadcast_subscribers', [
						'email' => $user->pending_email,
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
								'email' => $user->email,
								'new_email' => $user->pending_email,
							])
						);
					}
				}

				Logger::users($user->user_id, 'email_change.success');

				/* Set a nice success message */
				Alerts::add_success(l('activate_user.user_pending_email'));

				/* Clear the cache */
				cache()->deleteItemsByTag('user_id=' . $user->user_id);

				redirect('account');

				break;
		}

	}

}
