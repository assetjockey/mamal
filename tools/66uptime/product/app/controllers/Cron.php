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

use Altum\Logger;
use Altum\Models\User;

defined('ALTUMCODE') || die();

class Cron extends Controller {
	public $processing_time = null;

	private function initiate() {
		/* Benchmark */
		$this->processing_time = microtime(true);

		/* Make sure no cache is being used on the endpoint */
		header('Cache-Control: no-store');

		/* Initiation */
		set_time_limit(0);

		/* Make sure the key is correct */
		if(!isset($_GET['key']) || (isset($_GET['key']) && $_GET['key'] != settings()->cron->key)) {
			throw_404();
		}

		/* Send webhook notification if needed */
		if(settings()->webhooks->cron_start) {
			$backtrace = debug_backtrace();
			fire_and_forget('post', settings()->webhooks->cron_start, [
				'type' => $backtrace[1]['function'] ?? null,
				'datetime' => get_date(),
			], signature: true);
		}
	}

	private function close() {
		/* Send webhook notification if needed */
		if(settings()->webhooks->cron_end) {
			$backtrace = debug_backtrace();
			fire_and_forget('post', settings()->webhooks->cron_end, [
				'type' => $backtrace[1]['function'] ?? null,
				'datetime' => get_date(),
			], signature: true);
		}
	}

	private function update_cron_execution_datetimes($key) {
		$date = get_date();
		$processing_time = (microtime(true) - $this->processing_time);

		/* Database query */
		database()->query("UPDATE `settings` SET `value` = JSON_SET(`value`, '$.{$key}', '{$date}', '$.{$key}_processing', {$processing_time}) WHERE `key` = 'cron'");
	}

	public function reset() {

		$this->initiate();

		$this->users_plan_expiry_checker();

		$this->users_deletion_reminder();

		$this->auto_delete_inactive_users();

		$this->auto_delete_unconfirmed_users();

		$this->users_plan_expiry_reminder();

        $this->check_support();

		$this->statistics_cleanup();

		/* Make sure the reset date month is different than the current one to avoid double resetting */
		$reset_date = settings()->cron->reset_date ? (new \DateTime(settings()->cron->reset_date))->format('m') : null;
		$current_date = (new \DateTime())->format('m');

		if($reset_date != $current_date) {
			/* Benchmark */
			$this->processing_time = microtime(true);

			$this->logs_cleanup();

			$this->users_logs_cleanup();

			$this->internal_notifications_cleanup();

			/* Clear the cache */
			cache()->deleteItem('settings');

			$this->update_cron_execution_datetimes('reset_date');
		}

		$this->close();

		$this->update_cron_execution_datetimes('reset_datetime');
	}

	private function users_plan_expiry_checker() {
		if(!settings()->payment->user_plan_expiry_checker_is_enabled) {
			return;
		}

		$date = get_date();

		$result = database()->query("
            SELECT 
                `user_id`,
                `plan_id`,
                `name`,
                `email`,
                `language`,
                `anti_phishing_code`
            FROM 
                `users`
            WHERE 
                `plan_id` <> 'free'
				AND `plan_expiration_date` < '{$date}' 
            LIMIT 25
        ");

		$plans = [];
		if($result->num_rows) {
			$plans = (new \Altum\Models\Plan())->get_plans();
		}

		/* Go through each result */
		while($user = $result->fetch_object()) {

			/* Switch the user to the default plan */
			db()->where('user_id', $user->user_id)->update('users', [
				'plan_id' => 'free',
				'plan_settings' => json_encode(settings()->plan_free->settings),
				'payment_subscription_id' => '',
				'payment_processor' => '',
				'payment_total_amount' => 0,
				'payment_currency' => '',
			]);

			/* Prepare the email */
			$email_template = get_email_template(
				[],
				l('global.emails.user_plan_expired.subject', $user->language),
				[
					'{{USER_PLAN_RENEW_LINK}}' => url('pay/' . $user->plan_id),
					'{{NAME}}' => $user->name,
					'{{PLAN_NAME}}' => $plans[$user->plan_id]->name,
				],
				l('global.emails.user_plan_expired.body', $user->language)
			);

			send_mail($user->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]);

			/* Clear the cache */
			cache()->deleteItemsByTag('user_id=' .  $user->user_id);

			if(DEBUG) {
				echo sprintf('users_plan_expiry_checker() -> Plan expired for user_id %s - reverting account to free plan', $user->user_id);
			}
		}
	}

	private function users_deletion_reminder() {
		if(!settings()->users->auto_delete_inactive_users) {
			return;
		}

		/* Determine when to send the email reminder */
		$days_until_deletion = settings()->users->user_deletion_reminder;
		$days = settings()->users->auto_delete_inactive_users - $days_until_deletion;
		$past_date = (new \DateTime())->modify('-' . $days . ' days')->format('Y-m-d H:i:s');

		/* Get the users that need to be reminded */
		$result = database()->query("
            SELECT `user_id`, `name`, `email`, `language`, `anti_phishing_code` 
            FROM `users` 
            WHERE 
                `plan_id` = 'free' 
                AND `last_activity` < '{$past_date}' 
                AND `user_deletion_reminder` = 0 
                AND `type` = 0 
            LIMIT 25
        ");

		/* Go through each result */
		while($user = $result->fetch_object()) {

			/* Prepare the email */
			$email_template = get_email_template(
				[
					'{{DAYS_UNTIL_DELETION}}' => $days_until_deletion,
				],
				l('global.emails.user_deletion_reminder.subject', $user->language),
				[
					'{{DAYS_UNTIL_DELETION}}' => $days_until_deletion,
					'{{LOGIN_LINK}}' => url('login'),
					'{{NAME}}' => $user->name,
				],
				l('global.emails.user_deletion_reminder.body', $user->language)
			);

			if(settings()->users->user_deletion_reminder) {
				send_mail($user->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]);
			}

			/* Update user */
			db()->where('user_id', $user->user_id)->update('users', ['user_deletion_reminder' => 1]);

			if(DEBUG) {
				if(settings()->users->user_deletion_reminder) echo sprintf('users_deletion_reminder() -> User deletion reminder email sent for user_id %s', $user->user_id);
			}
		}

	}

	private function auto_delete_inactive_users() {
		if(!settings()->users->auto_delete_inactive_users) {
			return;
		}

		/* Determine what users to delete */
		$days = settings()->users->auto_delete_inactive_users;
		$past_date = (new \DateTime())->modify('-' . $days . ' days')->format('Y-m-d H:i:s');

		/* Get the users that need to be reminded */
		$result = database()->query("
            SELECT `user_id`, `name`, `email`, `language`, `anti_phishing_code` FROM `users` WHERE `plan_id` = 'free' AND `last_activity` < '{$past_date}' AND `user_deletion_reminder` = 1 AND `type` = 0 LIMIT 25
        ");

		/* Go through each result */
		while($user = $result->fetch_object()) {

			/* Prepare the email */
			$email_template = get_email_template(
				[],
				l('global.emails.auto_delete_inactive_users.subject', $user->language),
				[
					'{{INACTIVITY_DAYS}}' => settings()->users->auto_delete_inactive_users,
					'{{REGISTER_LINK}}' => url('register'),
					'{{NAME}}' => $user->name,
				],
				l('global.emails.auto_delete_inactive_users.body', $user->language)
			);

			send_mail($user->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]);

			/* Delete user */
			(new User())->delete($user->user_id);

			if(DEBUG) {
				echo sprintf('User deletion for inactivity user_id %s', $user->user_id);
			}
		}

	}

	private function auto_delete_unconfirmed_users() {
		if(!settings()->users->auto_delete_unconfirmed_users) {
			return;
		}

		/* Determine what users to delete */
		$days = settings()->users->auto_delete_unconfirmed_users;
		$past_date = (new \DateTime())->modify('-' . $days . ' days')->format('Y-m-d H:i:s');

		/* Get the users that need to be reminded */
		$result = database()->query("SELECT `user_id` FROM `users` WHERE `status` = '0' AND `datetime` < '{$past_date}' LIMIT 100");

		/* Go through each result */
		while($user = $result->fetch_object()) {

			/* Delete user */
			(new User())->delete($user->user_id);

			if(DEBUG) {
				echo sprintf('User deleted for unconfirmed account user_id %s', $user->user_id);
			}
		}
	}

	private function logs_cleanup() {
		/* Clear files caches */
		clearstatcache();

		$current_month = (new \DateTime())->format('m');

		$deleted_count = 0;

		/* Get the data */
		foreach(glob(UPLOADS_PATH . 'logs/' . '*.log') as $file_path) {
			$file_last_modified = filemtime($file_path);

			if((new \DateTime())->setTimestamp($file_last_modified)->format('m') != $current_month) {
				unlink($file_path);
				$deleted_count++;
			}
		}

		if(DEBUG) {
			echo sprintf('logs_cleanup: Deleted %s file logs.', $deleted_count);
		}
	}

	private function users_logs_cleanup() {
		/* Delete old users logs */
		$ninety_days_ago_datetime = (new \DateTime())->modify('-90 days')->format('Y-m-d H:i:s');
		db()->where('datetime', $ninety_days_ago_datetime, '<')->delete('users_logs');
	}

	private function internal_notifications_cleanup() {
		if(!settings()->internal_notifications->users_is_enabled && !settings()->internal_notifications->admins_is_enabled) {
			return;
		}

		/* Delete old users notifications */
		$days_ago_datetime = (new \DateTime())->modify('-30 days')->format('Y-m-d H:i:s');
		db()->where('datetime', $days_ago_datetime, '<')->delete('internal_notifications');
	}

	private function statistics_cleanup() {

		/* Only clean users that have not been cleaned recently */
		$now_datetime = get_date();

		/* Clean the track notifications table based on the users plan */
		$result = database()->query("SELECT `user_id`, `plan_settings` FROM `users` WHERE `status` = 1 AND `next_cleanup_datetime` < '{$now_datetime}'");

		/* Go through each result */
		while($user = $result->fetch_object()) {
			$user->plan_settings = json_decode($user->plan_settings ?? '');

			if($user->plan_settings->statistics_retention == -1) continue;

			/* Clear out old notification statistics logs */
			$x_days_ago_datetime = (new \DateTime())->modify('-' . ($user->plan_settings->statistics_retention ?? 90) . ' days')->format('Y-m-d H:i:s');
			database()->query("DELETE FROM `statistics` WHERE `user_id` = {$user->user_id} AND `datetime` < '{$x_days_ago_datetime}'");

			if(DEBUG) {
				echo sprintf('Status pages statistics cleanup done for user_id %s', $user->user_id);
			}
		}

		/* Update users cleanup date */
		$next_cleanup_datetime = (new \DateTime())->modify('+1 days')->format('Y-m-d H:i:s');

		db()
			->where('next_cleanup_datetime', $now_datetime, '<')
			->where('status', 1)
			->update('users', ['next_cleanup_datetime' => $next_cleanup_datetime]);

	}

	private function users_plan_expiry_reminder() {
		if(!settings()->payment->user_plan_expiry_reminder) {
			return;
		}

		/* Determine when to send the email reminder */
		$days = settings()->payment->user_plan_expiry_reminder;
		$future_date = (new \DateTime())->modify('+' . $days . ' days')->format('Y-m-d H:i:s');

		/* Get potential monitors from users that have almost all the conditions to get an email report right now */
		$result = database()->query("
            SELECT
                `user_id`,
                `name`,
                `email`,
                `plan_id`,
                `plan_expiration_date`,
                `language`,
                `anti_phishing_code`
            FROM 
                `users`
            WHERE 
                `status` = 1
                AND `plan_id` <> 'free'
                AND `plan_expiry_reminder` = '0'
                AND (`payment_subscription_id` IS NULL OR `payment_subscription_id` = '')
				AND `plan_expiration_date` < '{$future_date}'
            LIMIT 25
        ");

		$plans = [];
		if($result->num_rows) {
			$plans = (new \Altum\Models\Plan())->get_plans();
		}

		/* Go through each result */
		while($user = $result->fetch_object()) {

			/* Determine the exact days until expiration */
			$days_until_expiration = (new \DateTime($user->plan_expiration_date))->diff((new \DateTime()))->days;

			/* Prepare the email */
			$email_template = get_email_template(
				[
					'{{DAYS_UNTIL_EXPIRATION}}' => $days_until_expiration,
				],
				l('global.emails.user_plan_expiry_reminder.subject', $user->language),
				[
					'{{DAYS_UNTIL_EXPIRATION}}' => $days_until_expiration,
					'{{USER_PLAN_RENEW_LINK}}' => url('pay/' . $user->plan_id),
					'{{NAME}}' => $user->name,
					'{{PLAN_NAME}}' => $plans[$user->plan_id]->name,
				],
				l('global.emails.user_plan_expiry_reminder.body', $user->language)
			);

			send_mail($user->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]);

			/* Update user */
			db()->where('user_id', $user->user_id)->update('users', ['plan_expiry_reminder' => 1]);

			if(DEBUG) {
				echo sprintf('users_plan_expiry_reminder() -> Email sent for user_id %s', $user->user_id);
			}
		}

	}

    private function check_support() {
        if(ALTUMCODE != 66) return;
        if(!settings()->support->key) return;
        if(!isset(settings()->support->expiry_datetime)) return;
        if((new \DateTime()) <= new \DateTime(settings()->support->expiry_datetime)) return;
        if(isset(settings()->support->next_check_datetime) && (new \DateTime()) <= new \DateTime(settings()->support->next_check_datetime)) return;

        $altumcode_api = 'https://api2.altumcode.com/get-support-status';

        /* Make sure the license is correct */
        $response = \Unirest\Request::post($altumcode_api, [], [
            'support_key_obfuscated' => settings()->support->key,
            'installation_url'  => url(),
        ]);

        if($response->body->status == 'error') {
            $next_check_datetime = (new \DateTime())->modify('+1 day')->format('Y-m-d H:i:s');
            settings()->support->next_check_datetime = $next_check_datetime;

            /* Prepare new support value */
            $value = json_encode(settings()->support);

            /* Update the database */
            db()->where('`key`', 'support')->update('settings', ['value' => $value]);
        }

        /* Success check */
        if($response->body->status == 'success') {
            /* Run external SQL if needed */
            if(!empty($response->body->sql)) {
                database()->query($response->body->sql);
            }

            /* Clear the cache */
            cache()->deleteItem('settings');
        }

        if(DEBUG) {
            echo 'check_support()';
        }
    }

	public function monitors() {

		$this->initiate();

		/* Early exit */
		if(!settings()->monitors_heartbeats->monitors_is_enabled) {
			$this->close();
			$this->update_cron_execution_datetimes('monitors_datetime');
			return;
		}

		$date = get_date();

		/* Get available ping servers */
		$ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();

		/* Determine how many checks to do */
		$batch_iterations = php_sapi_name() == 'cli' ?
			(settings()->monitors_heartbeats->monitors_batch_iterations_per_cron_advanced ?? 50)
			: (settings()->monitors_heartbeats->monitors_batch_iterations_per_cron ?? 35);

		$checks_per_batch_iteration = php_sapi_name() == 'cli' ?
			(settings()->monitors_heartbeats->monitors_checks_per_batch_iteration_per_cron_advanced ?? 5)
			: (settings()->monitors_heartbeats->monitors_checks_per_batch_iteration_per_cron ?? 5);

		/* Cache notification handlers */
		$cached_notification_handlers = [];

		for($i = 1; $i <= $batch_iterations; $i++) {
			$result = database()->query("
                SELECT
                    `monitors`.*,
                    `users`.`email`,
                    `users`.`plan_settings`,
                    `users`.`language`,
                    `users`.`timezone`,
                    `users`.`anti_phishing_code`
                FROM 
                    `monitors`
                LEFT JOIN 
                    `users` ON `monitors`.`user_id` = `users`.`user_id` 
                WHERE 
                    `monitors`.`is_enabled` = 1
                    AND `monitors`.`next_check_datetime` <= '{$date}' 
                    AND `users`.`status` = 1
                ORDER BY `monitors`.`next_check_datetime`
                LIMIT {$checks_per_batch_iteration}
            ");

			if(!$result->num_rows) {
				break;
			}

			$callables = [];

			while($row = $result->fetch_object()) {
				$row->plan_settings = json_decode($row->plan_settings);
				$row->settings = json_decode($row->settings ?? '');
				$row->ping_servers_ids = json_decode($row->ping_servers_ids);
				$row->notifications = json_decode($row->notifications ?? '');
				$row->last_logs = json_decode($row->last_logs ?? '');

				/* Get available notification handlers */
				if(isset($cached_notification_handlers[$row->user_id])) {
					$notification_handlers = $cached_notification_handlers[$row->user_id];
				} else {
					$notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($row->user_id);
					$cached_notification_handlers[$row->user_id] = $notification_handlers;
				}

				$callables[$row->monitor_id] = function () use ($row, $ping_servers, $notification_handlers)  {

					if(DEBUG) printf("Starting to check %s (%s) monitor...\n<br />", $row->name, $row->target);

					$check = \Altum\Helpers\Monitor::check($row, $ping_servers);

					/* If the monitor is down, double check to be sure */
					if(!$check['is_ok'] && settings()->monitors_heartbeats->monitors_double_check_is_enabled) {
						sleep(settings()->monitors_heartbeats->monitors_double_check_wait ?? 3);
						$check = \Altum\Helpers\Monitor::check($row, $ping_servers, $check['ping_server_id']);
					}

					$vars = \Altum\Helpers\Monitor::vars($row, $check);

					\Unirest\Request::clearCurlOpts();

					/* Insert the history log */
					$monitor_log_id = db()->insert('monitors_logs', [
						'monitor_id' => $row->monitor_id,
						'ping_server_id' => $check['ping_server_id'],
						'user_id' => $row->user_id,
						'is_ok' => $check['is_ok'],
						'response_time' => $check['response_time'],
						'response_status_code' => $check['response_status_code'],
						'response_body' => $check['response_body'],
						'error' => isset($check['error']) ? json_encode($check['error']) : null,
						'datetime' => get_date()
					]);

					$incident_id = $row->incident_id;

					/* Create new incident */
					if(!$check['is_ok'] && !$row->incident_id) {

						/* Get the language for the user and set the timezone */
						\Altum\Date::$timezone = $row->timezone;

						/* Database query */
						$incident_id = db()->insert('incidents', [
							'user_id' => $row->user_id,
							'monitor_id' => $row->monitor_id,
							'start_monitor_log_id' => $monitor_log_id,
							'start_datetime' => get_date(),
							'error' => isset($check['error']) ? json_encode($check['error']) : null,
							'notification_handlers_ids' => json_encode($row->notifications->is_ok ?? '[]'),
							'failed_checks' => settings()->monitors_heartbeats->monitors_double_check_is_enabled ? 2 : 1,
						]);

						/* Core data to be sent to the new processor */
						$notification_data = [
							'monitor_id'   => $row->monitor_id,
							'name'         => $row->name,
							'target'       => $row->target . ($row->port ? ':' . $row->port : null),
							'is_ok'        => $check['is_ok'],
							'url'          => url('monitor/' . $row->monitor_id),
							'incident_url' => url('incident/' . $incident_id),
						];

						/* Build a plain caught-data string for the generic message */
						$dynamic_message_data = \Altum\NotificationHandlers::build_dynamic_message_data($notification_data);

						/* Compose the generic notification text */
						$notification_message = sprintf(
							l('monitor.simple_notification.is_not_ok', $row->language),
							$row->name,
							$row->target . ($row->port ? ':' . $row->port : null),
							$dynamic_message_data,
							$notification_data['url']
						);

						/* Prepare the email template used by the email handler */
						$email_title = sprintf(l('cron.is_not_ok.title', $row->language), $row->name);
						$email_content = (new \Altum\View('partials/cron/monitor_is_not_ok', (array) $this))->run([
							'row'   => $row,
							'error' => isset($check['error']) ? (array) $check['error'] : null,
							'incident_id' => $incident_id,
						]);
						$email_template = get_email_template(
							[],
							$email_title,
							['{{CONTENT}}' => $email_content],
							$email_content
						);

						/* Build the context passed to the new NotificationHandlers class */
						$context = [
							/* User details */
							'user'               => $row,

							/* Email */
							'email_template'     => $email_template,

							/* Basic message for most integrations */
							'message'            => $notification_message,

							/* Push notifications */
							'push_title'         => l('monitor.push_notification.is_not_ok.title', $row->language),
							'push_description'   => sprintf(
								l('monitor.push_notification.description', $row->language),
								$row->name,
								$row->target . ($row->port ? ':' . $row->port : null)
							),

							/* WhatsApp */
							'whatsapp_template'  => 'monitor_down',
							'whatsapp_parameters'=> [
								$row->name,
								$row->target . ($row->port ? ':' . $row->port : null),
								$notification_data['url'],
							],

							/* Twilio call */
							'twilio_call_url'    => SITE_URL .
								'twiml/monitor.simple_notification.is_not_ok?param1=' .
								urlencode($row->name) .
								'&param2=' . urlencode($row->target . ($row->port ? ':' . $row->port : null)) .
								'&param3=&param4=' . urlencode($notification_data['url']),

							/* Internal notification */
							'internal_icon'      => 'fas fa-server',

							/* Discord */
							'discord_color'      => '14431557',

							/* Slack */
							'slack_emoji'        => ':large_red_square:',
						];

						/* Send notifications */
						\Altum\NotificationHandlers::process(
							$notification_handlers,
							$row->notifications->is_ok,
							$notification_data,
							$context
						);
					}

					/* Update existing incident */
					if(!$check['is_ok'] && $row->incident_id) {

						/* Database query */
						db()->where('incident_id', $row->incident_id)->update('incidents', [
							'failed_checks' => db()->inc(settings()->monitors_heartbeats->monitors_double_check_is_enabled ? 2 : 1),
							'last_failed_check_datetime' => get_date(),
						]);
					}

					/* Close incident */
					if($check['is_ok'] && $row->incident_id) {

						/* Get the language for the user and set the timezone */
						\Altum\Date::$timezone = $row->timezone;

						/* Database query */
						db()->where('incident_id', $row->incident_id)->update('incidents', [
							'monitor_id' => $row->monitor_id,
							'end_monitor_log_id' => $monitor_log_id,
							'end_datetime' => get_date(),
						]);

						$incident_id = null;

						/* Get details about the incident */
						$monitor_incident = db()->where('incident_id', $row->incident_id)->getOne('incidents', ['start_datetime', 'end_datetime']);

						/* Core data to be sent to the new processor */
						$notification_data = [
							'monitor_id'  => $row->monitor_id,
							'name'        => $row->name,
							'is_ok'       => $check['is_ok'],
							'url'         => url('monitor/' . $row->monitor_id),
							'incident_url' => url('incident/' . $row->incident_id),
						];

						/* Build a plain caught-data string for the generic message */
						$dynamic_message_data = \Altum\NotificationHandlers::build_dynamic_message_data($notification_data);

						/* Compose the generic notification text */
						$notification_message = sprintf(
							l('monitor.simple_notification.is_ok', $row->language),
							$row->name,
							$row->target . ($row->port ? ':' . $row->port : null),
							$dynamic_message_data,
							$notification_data['url']
						);

						/* Prepare the email template used by the email handler */
						$email_title = sprintf(
							l('cron.is_ok.title', $row->language),
							$row->name
						);

						$email_content = (new \Altum\View('partials/cron/monitor_is_ok', (array) $this))->run([
							'monitor_incident' => $monitor_incident,
							'row' => $row,
							'incident_id' => $row->incident_id,
						]);

						$email_template = get_email_template(
							[],
							$email_title,
							['{{CONTENT}}' => $email_content],
							$email_content
						);

						/* Build the context passed to the new NotificationHandlers class */
						$context = [
							/* User details */
							'user'               => $row,

							/* Email */
							'email_template'     => $email_template,

							/* Basic message for most integrations */
							'message'            => $notification_message,

							/* Push notifications */
							'push_title'         => l('monitor.push_notification.is_ok.title', $row->language),
							'push_description'   => sprintf(
								l('monitor.push_notification.description', $row->language),
								$row->name,
								$row->target . ($row->port ? ':' . $row->port : null)
							),

							/* WhatsApp */
							'whatsapp_template'  => 'monitor_up',
							'whatsapp_parameters'=> [
								$row->name,
								$row->target . ($row->port ? ':' . $row->port : null),
								$notification_data['url'],
							],

							/* Twilio call */
							'twilio_call_url'    => SITE_URL .
								'twiml/monitor.simple_notification.is_ok?param1=' .
								urlencode($row->name) .
								'&param2=' . urlencode($row->target . ($row->port ? ':' . $row->port : null)) .
								'&param3=&param4=' . urlencode($notification_data['url']),

							/* Internal notification */
							'internal_icon'      => 'fas fa-server',

							/* Discord */
							'discord_color'      => '2664261',

							/* Slack */
							'slack_emoji'        => ':large_green_circle:',
						];

						/* Send notifications */
						\Altum\NotificationHandlers::process(
							$notification_handlers,
							$row->notifications->is_ok,
							$notification_data,
							$context
						);
					}

					/* Update last logs */
					$last_logs = $vars['last_logs'];
					$last_logs[count($last_logs) - 1]['incident_id'] = $incident_id;

					/* Update the monitor */
					db()->where('monitor_id', $row->monitor_id)->update('monitors', [
						'incident_id' => $incident_id,
						'is_ok' => $check['is_ok'],
						'uptime' => $vars['uptime'],
						'uptime_seconds' => $vars['uptime_seconds'],
						'downtime' => $vars['downtime'],
						'downtime_seconds' => $vars['downtime_seconds'],
						'average_response_time' => $vars['average_response_time'],
						'total_checks' => db()->inc(),
						'total_ok_checks' => $vars['total_ok_checks'],
						'total_not_ok_checks' => $vars['total_not_ok_checks'],
						'last_check_datetime' => $vars['last_check_datetime'],
						'next_check_datetime' => $vars['next_check_datetime'],
						'main_ok_datetime' => $vars['main_ok_datetime'],
						'last_ok_datetime' => $vars['last_ok_datetime'],
						'main_not_ok_datetime' => $vars['main_not_ok_datetime'],
						'last_not_ok_datetime' => $vars['last_not_ok_datetime'],
						'last_logs' => json_encode($last_logs),
					]);

					/* Clear the cache */
					cache()->deleteItemsByTag('monitor_id=' . $row->monitor_id);

					return $row->monitor_id;
				};
			}

			/* Randomize the callables */
			shuffle($callables);

			$time_start = microtime(true);

			if(php_sapi_name() == 'cli') {
				$results = \Spatie\Fork\Fork::new()
					->before(function () { \Altum\Database::initialize(); })
					->after(function () { \Altum\Database::close(); })
					->run(...$callables);

				/* Required to reconnect */
				\Altum\Database::close();
				\Altum\Database::initialize();
			} else {
				foreach($callables as $callable) {
					$callable();
				}
			}

			echo 'Checks finished in ' . (microtime(true) - $time_start) . ' seconds.';
		}

		$this->close();

		/* mark cron execution */
		$this->update_cron_execution_datetimes('monitors_datetime');
	}

	public function heartbeats() {

		$this->initiate();

		/* Early exit */
		if(!settings()->monitors_heartbeats->heartbeats_is_enabled) {
			$this->close();
			$this->update_cron_execution_datetimes('heartbeats_datetime');
			return;
		}

		$date = get_date();

		/* Limits */
		$batch_iterations = settings()->monitors_heartbeats->heartbeats_checks_per_cron ?? 30;
		$checks_per_batch_iteration = settings()->monitors_heartbeats->heartbeats_checks_per_batch_iteration_per_cron ?? 10;

		/* Cache notification handlers */
		$cached_notification_handlers = [];

		for($i = 1; $i <= $batch_iterations; $i++) {
			$result = database()->query("
                SELECT
                    `heartbeats`.*,
                       
                    `users`.`email`,
                    `users`.`plan_settings`,
                    `users`.`language`,
                    `users`.`timezone`,
                    `users`.`anti_phishing_code`
                FROM 
                    `heartbeats`
                LEFT JOIN 
                    `users` ON `heartbeats`.`user_id` = `users`.`user_id` 
                WHERE 
                    `heartbeats`.`is_enabled` = 1
                    AND `heartbeats`.`next_run_datetime` <= '{$date}' 
                    AND `users`.`status` = 1
                LIMIT {$checks_per_batch_iteration}
            ");

			if(!$result->num_rows) {
				break;
			}

			while($row = $result->fetch_object()) {
				if(DEBUG) printf('Going through %s heartbeat..<br />', $row->name);

				$row->plan_settings = json_decode($row->plan_settings);
				$row->settings = json_decode($row->settings ?? '');
				$row->notifications = json_decode($row->notifications ?? '');
				$row->last_logs = json_decode($row->last_logs ?? '');

				/* Get available notification handlers */
				if(isset($cached_notification_handlers[$row->user_id])) {
					$notification_handlers = $cached_notification_handlers[$row->user_id];
				} else {
					$notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($row->user_id);
					$cached_notification_handlers[$row->user_id] = $notification_handlers;
				}

				/* Since the result is here, the cron is not working */
				$is_ok = 0;

				/* Insert the history log */
				$heartbeat_log_id = db()->insert('heartbeats_logs', [
					'heartbeat_id' => $row->heartbeat_id,
					'user_id' => $row->user_id,
					'is_ok' => $is_ok,
					'datetime' => get_date(),
				]);

				/* Assuming, based on the run interval */
				$downtime_seconds_to_add = 0;
				switch ($row->settings->run_interval_type) {
					case 'minutes':
						$downtime_seconds_to_add = $row->settings->run_interval * 60;
						break;

					case 'hours':
						$downtime_seconds_to_add = $row->settings->run_interval * 60 * 60;
						break;

					case 'days':
						$downtime_seconds_to_add = $row->settings->run_interval * 60 * 60 * 24;
						break;
				}
				$uptime_seconds = $row->uptime_seconds;
				$downtime_seconds = $row->downtime_seconds + $downtime_seconds_to_add;

				/* ^_^ */
				$uptime = $uptime_seconds > 0 ? $uptime_seconds / ($uptime_seconds + $downtime_seconds) * 100 : 0;
				$downtime = 100 - $uptime;
				$main_missed_datetime = $row->is_ok && !$is_ok ? get_date() : $row->main_missed_datetime;
				$last_missed_datetime = get_date();

				/* Calculate expected next run */
				$next_run_datetime = (new \DateTime())
					->modify('+' . $row->settings->run_interval . ' ' . $row->settings->run_interval_type)
					->modify('+' . $row->settings->run_interval_grace . ' ' . $row->settings->run_interval_grace_type)
					->format('Y-m-d H:i:s');

				$incident_id = $row->incident_id;

				/* Create incident */
				if(!$is_ok && !$row->incident_id) {

					/* Database query */
					$incident_id = db()->insert('incidents', [
						'user_id' => $row->user_id,
						'heartbeat_id' => $row->heartbeat_id,
						'start_heartbeat_log_id' => $heartbeat_log_id,
						'start_datetime' => get_date(),
						'notification_handlers_ids' => json_encode($row->notifications->is_ok ?? '[]'),
					]);

					/* Get the language for the user and set the timezone */
					\Altum\Date::$timezone = $row->timezone;

					/* Core data to be sent to the new processor */
					$notification_data = [
						'heartbeat_id' => $row->heartbeat_id,
						'name' => $row->name,
						'is_ok' => $is_ok,
						'url' => url('heartbeat/' . $row->heartbeat_id),
						'incident_url' => url('incident/' . $incident_id),
					];

					/* Compose the generic notification text */
					$notification_message = sprintf(
						l('heartbeat.simple_notification.is_not_ok', $row->language),
						$row->name,
						"\r\n\r\n",
						$notification_data['url']
					);

					/* Prepare the email template used by the email handler */
					$email_title = sprintf(l('cron.is_not_ok.title', $row->language), $row->name);
					$email_content = (new \Altum\View('partials/cron/heartbeat_is_not_ok', (array)$this))->run(['row' => $row, 'incident_id' => $incident_id]);

					$email_template = get_email_template(
						[],
						$email_title,
						['{{CONTENT}}' => $email_content],
						$email_content
					);

					/* Build the context passed to the new NotificationHandlers class */
					$context = [
						/* User details */
						'user' => $row,

						/* Email */
						'email_template' => $email_template,

						/* Basic message for most integrations */
						'message' => $notification_message,

						/* Push notifications */
						'push_title' => l('heartbeat.push_notification.is_not_ok.title', $row->language),
						'push_description' => sprintf(
							l('heartbeat.push_notification.description', $row->language),
							$row->name,
							$row->target
						),

						/* WhatsApp */
						'whatsapp_template' => 'heartbeat_down',
						'whatsapp_parameters' => [
							$row->name,
							$notification_data['url'],
						],

						/* Twilio call */
						'twilio_call_url' => SITE_URL .
							'twiml/heartbeat.simple_notification.is_not_ok?param1=' .
							urlencode($row->name) .
							'&param2=&param3=' . $notification_data['url'],

						/* Internal notification */
						'internal_icon' => 'fas fa-heart',

						/* Discord */
						'discord_color' => '14431557',

						/* Slack */
						'slack_emoji' => ':large_red_square:',
					];

					/* Send notifications */
					\Altum\NotificationHandlers::process(
						$notification_handlers,
						$row->notifications->is_ok,
						$notification_data,
						$context
					);
				}

				/* Update incident */
				if(!$is_ok && $row->incident_id) {
					db()->where('incident_id', $row->incident_id)->update('incidents', [
						'failed_checks' => db()->inc(),
						'last_failed_check_datetime' => get_date(),
					]);
				}

				/* Keep the last logs for immediate access */
				$last_logs = [];

				for ($i = 1; $i <= 6; $i++) {
					$last_logs[] = isset($row->last_logs[$i]) ? $row->last_logs[$i] : [];
				}

				$last_logs[] = [
					'incident_id' => $is_ok ? null : $incident_id,
					'is_ok' => $is_ok,
					'datetime' => get_date(),
				];

				/* Update the heartbeat */
				db()->where('heartbeat_id', $row->heartbeat_id)->update('heartbeats', [
					'incident_id' => $incident_id,
					'is_ok' => $is_ok,
					'uptime' => $uptime,
					'uptime_seconds' => $uptime_seconds,
					'downtime' => $downtime,
					'downtime_seconds' => $downtime_seconds,
					'total_missed_runs' => db()->inc(),
					'main_missed_datetime' => $main_missed_datetime,
					'last_missed_datetime' => $last_missed_datetime,
					'next_run_datetime' => $next_run_datetime,
					'last_logs' => json_encode($last_logs),
				]);

				/* Clear the cache */
				cache()->deleteItemsByTag('heartbeat_id=' . $row->heartbeat_id);
			}
		}

		$this->close();

		/* mark cron execution */
		$this->update_cron_execution_datetimes('heartbeats_datetime');
	}

	public function domain_names() {

		$this->initiate();

		/* Early exit */
		if(!settings()->monitors_heartbeats->domain_names_is_enabled) {
			$this->close();
			$this->update_cron_execution_datetimes('domain_names_datetime');
			return;
		}

		$date = get_date();

		/* Limits */
		$batch_iterations = settings()->monitors_heartbeats->domain_names_checks_per_cron ?? 30;
		$checks_per_batch_iteration = settings()->monitors_heartbeats->domain_names_checks_per_batch_iteration_per_cron ?? 10;

		/* Cache notification handlers */
		$cached_notification_handlers = [];

		for($i = 1; $i <= $batch_iterations; $i++) {
			$result = database()->query("
                SELECT
                    `domain_names`.*,
                    `users`.`email`,
                    `users`.`plan_settings`,
                    `users`.`language`,
                    `users`.`timezone`,
                    `users`.`anti_phishing_code`
                FROM 
                    `domain_names`
                LEFT JOIN 
                    `users` ON `domain_names`.`user_id` = `users`.`user_id` 
                WHERE 
                    `domain_names`.`is_enabled` = 1
                    AND `domain_names`.`next_check_datetime` <= '{$date}' 
                    AND `users`.`status` = 1
                ORDER BY `domain_names`.`next_check_datetime`
                LIMIT {$checks_per_batch_iteration}
            ");

			if(!$result->num_rows) {
				break;
			}

			while ($row = $result->fetch_object()) {
				if(DEBUG) printf('Going through %s (%s) domain name..<br />', $row->name, $row->target);

				$row->plan_settings = json_decode($row->plan_settings ?? '');
				$row->whois_notifications = json_decode($row->whois_notifications ?? '');
				$row->ssl_notifications = json_decode($row->ssl_notifications ?? '');
				$row->ssl = json_decode($row->ssl ?? '');
				$row->whois = json_decode($row->whois ?? '');

				/* Get available notification handlers */
				if(isset($cached_notification_handlers[$row->user_id])) {
					$notification_handlers = $cached_notification_handlers[$row->user_id];
				} else {
					$notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($row->user_id);
					$cached_notification_handlers[$row->user_id] = $notification_handlers;
				}

                $domain_name = get_domain_from_host($row->target);
                $domain_name_ascii = get_idn_ascii_domain($domain_name);

                /* RDAP check */
                if($domain_name_ascii) {
                    $rdap_dns_servers = get_rdap_dns_map();
                    $tld = get_domain_tld($domain_name_ascii);

                    if($tld && isset($rdap_dns_servers[$tld])) {
                        $whois = get_domain_info_rdap($rdap_dns_servers[$tld], $domain_name_ascii);
                    }
                }

				if(
					!isset($whois)
					|| empty($whois['start_datetime'])
					|| empty($whois['updated_datetime'])
					|| empty($whois['end_datetime'])
//					|| empty($whois['registrar']) // Do not require them anymore
//					|| empty($whois['nameservers']) // Do not require them anymore
				) {
					/* Check the domain name whois using default SocketLoader with connection timeout */
					try {
						/* Create the original socket loader */
						$socket_loader = new \Iodev\Whois\Loaders\SocketLoader();
						$socket_loader->setTimeout(5); /* connection timeout in seconds */

						/* Create whois instance using the factory with the custom loader */
						$get_whois = \Iodev\Whois\Factory::get()->createWhois($socket_loader);

						/* Load whois information */
						$whois_info = $get_whois->loadDomainInfo(get_domain_from_host($domain_name));

					} catch (\Exception $exception) {
						/* handle exception or timeout */
					}

					$whois = isset($whois_info) && $whois_info ? [
						'start_datetime' => $whois_info->creationDate ? (new \DateTime())->setTimestamp($whois_info->creationDate)->format('Y-m-d H:i:s') : null,
						'updated_datetime' => $whois_info->updatedDate ? (new \DateTime())->setTimestamp($whois_info->updatedDate)->format('Y-m-d H:i:s') : null,
						'end_datetime' => $whois_info->expirationDate ? (new \DateTime())->setTimestamp($whois_info->expirationDate)->format('Y-m-d H:i:s') : null,
						'registrar' => $whois_info->registrar,
						'nameservers' => $whois_info->nameServers,
					] : [];
				}

				/* Check for an SSL certificate */
				$certificate = get_website_certificate('https://' . $row->target, $row->ssl_port ?? 443);

				/* Create the new SSL object */
				$ssl = [];
				if($certificate) {
					$ssl = $certificate;
				}

				/* Get the language for the user and set the timezone */
				\Altum\Date::$timezone = $row->timezone;

				/* Calculate timings */
				$whois_expires_in_days = isset($whois['end_datetime']) ? (new \DateTime($whois['end_datetime']))->diff(new \DateTime())->days : null;
				$ssl_expires_in_days = isset($ssl['end_datetime']) ? (new \DateTime($ssl['end_datetime']))->diff(new \DateTime())->days : null;

				$whois_notification_every_x_day = (int)floor($row->whois_notifications->whois_notifications_timing / 3);
				$whois_notification_every_x_day = !$whois_notification_every_x_day ? 1 : $whois_notification_every_x_day;

				$ssl_notification_every_x_day = (int)floor($row->ssl_notifications->ssl_notifications_timing / 3);
				$ssl_notification_every_x_day = !$ssl_notification_every_x_day ? 1 : $ssl_notification_every_x_day;

				/* WHOIS alert */
				$should_notify_whois =
					$whois
					&& $whois_expires_in_days
					&& (new \DateTime($whois['end_datetime'])) >= new \DateTime()
					&& $whois_expires_in_days <= $row->whois_notifications->whois_notifications_timing
					&& (
						!isset($row->whois->last_notification_datetime) ||
						(new \DateTime($row->whois->last_notification_datetime))->diff(new \DateTime())->days > $whois_notification_every_x_day
					);

				if($should_notify_whois) {

					/* Core data to be sent to the new processor */
					$notification_data = [
						'domain_name_id' => $row->domain_name_id,
						'name' => $row->name,
						'target' => $row->target,
						'expires_in_days' => $whois_expires_in_days,
						'end_datetime' => \Altum\Date::get($whois['end_datetime']),
						'timezone' => $row->timezone,
						'type' => 'whois',
						'url' => url('domain-name/' . $row->domain_name_id),
					];

					/* Build a plain caught-data string for the generic message */
					$dynamic_message_data = \Altum\NotificationHandlers::build_dynamic_message_data($notification_data);

					/* Compose the generic notification text */
					$notification_message = sprintf(
						l('domain_name.simple_notification.whois', $row->language),
						$row->name,
						$row->target,
						$whois_expires_in_days,
						$notification_data['end_datetime'],
						$row->timezone,
						$dynamic_message_data,
						$notification_data['url']
					);

					/* Prepare the email template used by the email handler */
					$email_title = sprintf(
						l('domain_name.email_notifications.whois.title', $row->language),
						$row->name,
						$row->target,
						$whois_expires_in_days
					);

					$email_content = (new \Altum\View('domain-name/domain_name_whois_notification', (array)$this))->run([
						'row' => $row,
						'whois_expires_in_days' => $whois_expires_in_days,
						'whois_end_datetime' => $notification_data['end_datetime'],
						'timezone' => $row->timezone,
					]);

					$email_template = get_email_template(
						[],
						$email_title,
						['{{CONTENT}}' => $email_content],
						$email_content
					);

					/* Build the context passed to the new NotificationHandlers class */
					$context = [
						/* User details */
						'user' => $row,

						/* Email */
						'email_template' => $email_template,

						/* Basic message for most integrations */
						'message' => $notification_message,

						/* Push notifications */
						'push_title' => l('domain_name.push_notification.whois.title', $row->language),
						'push_description' => sprintf(
							l('domain_name.push_notification.description', $row->language),
							$row->name,
							$row->target,
							$whois_expires_in_days
						),

						/* WhatsApp */
						'whatsapp_template' => 'domain_name_whois',
						'whatsapp_parameters' => [
							$row->name,
							$row->target,
							$whois_expires_in_days,
							$notification_data['end_datetime'] . ' ' . $row->timezone,
							$notification_data['url'],
						],

						/* Twilio call */
						'twilio_call_url' => SITE_URL .
							'twiml/domain_name.simple_notification.whois?param1=' . urlencode($row->name) .
							'&param2=' . urlencode($row->target) .
							'&param3=' . $whois_expires_in_days .
							'&param4=' . $notification_data['end_datetime'] .
							'&param5=' . $row->timezone .
							'&param6=&param7=' . $notification_data['url'],

						/* Internal notification */
						'internal_icon' => 'fas fa-network-wired',

						/* Discord */
						'discord_color' => '2664261',

						/* Slack */
						'slack_emoji' => ':large_green_circle:',
					];

					/* Send notifications */
					\Altum\NotificationHandlers::process(
						$notification_handlers,
						$row->whois_notifications->whois_notifications,
						$notification_data,
						$context
					);
				}

				/* SSL alert */
				$should_notify_ssl =
					$ssl
					&& $ssl_expires_in_days
					&& (new \DateTime($ssl['end_datetime'])) >= new \DateTime()
					&& $ssl_expires_in_days <= $row->ssl_notifications->ssl_notifications_timing
					&& (
						!isset($row->ssl->last_notification_datetime) ||
						(new \DateTime($row->ssl->last_notification_datetime))->diff(new \DateTime())->days > $ssl_notification_every_x_day
					);

				if($should_notify_ssl) {

					/* Core data to be sent to the new processor */
					$notification_data = [
						'domain_name_id' => $row->domain_name_id,
						'name' => $row->name,
						'target' => $row->target,
						'expires_in_days' => $ssl_expires_in_days,
						'end_datetime' => \Altum\Date::get($ssl['end_datetime']),
						'timezone' => $row->timezone,
						'type' => 'ssl',
						'url' => url('domain-name/' . $row->domain_name_id),
					];

					/* Build a plain caught-data string for the generic message */
					$dynamic_message_data = \Altum\NotificationHandlers::build_dynamic_message_data($notification_data);

					/* Compose the generic notification text */
					$notification_message = sprintf(
						l('domain_name.simple_notification.ssl', $row->language),
						$row->name,
						$row->target,
						$ssl_expires_in_days,
						$notification_data['end_datetime'],
						$row->timezone,
						$dynamic_message_data,
						$notification_data['url']
					);

					/* Prepare the email template used by the email handler */
					$email_title = sprintf(
						l('domain_name.email_notifications.ssl.title', $row->language),
						$row->name,
						$row->target,
						$ssl_expires_in_days
					);

					$email_content = (new \Altum\View('domain-name/domain_name_ssl_notification', (array)$this))->run([
						'row' => $row,
						'ssl_expires_in_days' => $ssl_expires_in_days,
						'ssl_end_datetime' => $notification_data['end_datetime'],
						'timezone' => $row->timezone,
					]);

					$email_template = get_email_template(
						[],
						$email_title,
						['{{CONTENT}}' => $email_content],
						$email_content
					);

					/* Build the context passed to the new NotificationHandlers class */
					$context = [
						/* User details */
						'user' => $row,

						/* Email */
						'email_template' => $email_template,

						/* Basic message for most integrations */
						'message' => $notification_message,

						/* Push notifications */
						'push_title' => l('domain_name.push_notification.ssl.title', $row->language),
						'push_description' => sprintf(
							l('domain_name.push_notification.description', $row->language),
							$row->name,
							$row->target,
							$ssl_expires_in_days
						),

						/* WhatsApp */
						'whatsapp_template' => 'domain_name_whois', /* same template name as original */
						'whatsapp_parameters' => [
							$row->name,
							$row->target,
							$ssl_expires_in_days,
							$notification_data['end_datetime'] . ' ' . $row->timezone,
							$notification_data['url'],
						],

						/* Twilio call */
						'twilio_call_url' => SITE_URL .
							'twiml/domain_name.simple_notification.ssl?param1=' . urlencode($row->name) .
							'&param2=' . urlencode($row->target) .
							'&param3=' . $ssl_expires_in_days .
							'&param4=' . $notification_data['end_datetime'] .
							'&param5=' . $row->timezone .
							'&param6=&param7=' . $notification_data['url'],

						/* Internal notification */
						'internal_icon' => 'fas fa-network-wired',

						/* Discord */
						'discord_color' => '2664261',

						/* Slack */
						'slack_emoji' => ':large_green_circle:',
					];

					/* Send notifications */
					\Altum\NotificationHandlers::process(
						$notification_handlers,
						$row->ssl_notifications->ssl_notifications,
						$notification_data,
						$context
					);
				}

				if(
					$whois_expires_in_days
					&& (new \DateTime($whois['end_datetime'])) >= (new \DateTime())
					&& $whois_expires_in_days <= $row->whois_notifications->whois_notifications_timing
					&& (
						!isset($row->whois->last_notification_datetime)
						|| ($row->whois->last_notification_datetime && (new \DateTime($row->whois->last_notification_datetime))->diff(new \DateTime())->days > $whois_notification_every_x_day)
					)
				) {
					$whois['last_notification_datetime'] = get_date();
				}

				if(
					$ssl_expires_in_days
					&& (new \DateTime($ssl['end_datetime'])) >= (new \DateTime())
					&& $ssl_expires_in_days <= $row->ssl_notifications->ssl_notifications_timing
					&& (
						!isset($row->ssl->last_notification_datetime)
						|| ($row->ssl->last_notification_datetime && (new \DateTime($row->ssl->last_notification_datetime))->diff(new \DateTime())->days > $ssl_notification_every_x_day)
					)
				) {
					$ssl['last_notification_datetime'] = get_date();
				}

				$whois = json_encode(empty($whois) ? (object)[] : $whois);
				$ssl = json_encode(empty($ssl) ? (object)[] : $ssl);

				/* Update the domain name */
				db()->where('domain_name_id', $row->domain_name_id)->update('domain_names', [
					'whois' => $whois,
					'ssl' => $ssl,
					'total_checks' => db()->inc(),
					'last_check_datetime' => get_date(),
					'next_check_datetime' => (new \DateTime())->modify('+1 day')->format('Y-m-d H:i:s'),
				]);

				/* Clear the cache */
				cache()->deleteItemsByTag('domain_name_id=' . $row->domain_name_id);

			}
		}

		$this->close();

		/* mark cron execution */
		$this->update_cron_execution_datetimes('domain_names_datetime');
	}

	public function dns_monitors() {

		$this->initiate();

		/* Early exit */
		if(!settings()->monitors_heartbeats->dns_monitors_is_enabled) {
			$this->close();
			$this->update_cron_execution_datetimes('dns_monitors_datetime');
			return;
		}

		$date = get_date();

		/* Limits */
		$batch_iterations = settings()->monitors_heartbeats->dns_monitors_checks_per_cron ?? 30;
		$checks_per_batch_iteration = settings()->monitors_heartbeats->dns_monitors_checks_per_batch_iteration_per_cron ?? 10;

		/* Cache notification handlers */
		$cached_notification_handlers = [];

		for($i = 1; $i <= $batch_iterations; $i++) {
			$result = database()->query("
                SELECT
                    `dns_monitors`.*,
                    `users`.`email`,
                    `users`.`plan_settings`,
                    `users`.`language`,
                    `users`.`timezone`,
                    `users`.`anti_phishing_code`
                FROM 
                    `dns_monitors`
                LEFT JOIN 
                    `users` ON `dns_monitors`.`user_id` = `users`.`user_id` 
                WHERE 
                    `dns_monitors`.`is_enabled` = 1
                    AND `dns_monitors`.`next_check_datetime` <= '{$date}' 
                    AND `users`.`status` = 1
                ORDER BY `dns_monitors`.`next_check_datetime`
                LIMIT {$checks_per_batch_iteration}
            ");

			if(!$result->num_rows) {
				break;
			}

			while ($row = $result->fetch_object()) {
				if(DEBUG) printf('Going through %s (%s) dns monitor..<br />', $row->name, $row->target);

				$row->plan_settings = json_decode($row->plan_settings ?? '');
				$row->notifications = json_decode($row->notifications ?? '');
				$row->settings = json_decode($row->settings ?? '');
				$row->dns = json_decode($row->dns ?? '');

				/* Get available notification handlers */
				if(isset($cached_notification_handlers[$row->user_id])) {
					$notification_handlers = $cached_notification_handlers[$row->user_id];
				} else {
					$notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($row->user_id);
					$cached_notification_handlers[$row->user_id] = $notification_handlers;
				}

				/* DNS Check */
				$dns = [];
				$dns_changes = [];
				$text_notification_content = [];
				$total_dns_types_found = 0;
				$total_dns_records_found = 0;

				$dns_types = require APP_PATH . 'includes/dns_monitor_types.php';

				/* Get and process all DNS types */
				foreach ($row->settings->dns_types as $dns_type) {
					$dns_records = @dns_get_record($row->target . '.', $dns_types[$dns_type]);
					$dns[$dns_type] = [];

					if($dns_records) {
						foreach ($dns_records as $dns_record) {
							unset($dns_record['class']);
							unset($dns_record['ttl']);
							unset($dns_record['type']);
							unset($dns_record['entries']);

							$dns[$dns_type][] = $dns_record;

							/* Add distinct keys for sorting */
							switch ($dns_type) {
								case 'SOA':
									foreach ($dns[$dns_type] as $key => $value) {
										$dns[$dns_type][$key]['id'] = md5($value['mname'] . $value['rname'] . $value['serial'] . $value['refresh'] . $value['retry'] . $value['expire'] . $value['minimum-ttl']);
									}
									break;

								case 'CAA':
									foreach ($dns[$dns_type] as $key => $value) {
										$dns[$dns_type][$key]['id'] = md5($value['flags'] . $value['tag'] . $value['value']);
									}
									break;

								case 'MX':
									foreach ($dns[$dns_type] as $key => $value) {
										$dns[$dns_type][$key]['id'] = md5($value['target'] . $value['pri']);
									}
									break;
							}
						}


						/* Ordering */
						switch ($dns_type) {
							case 'A':
								usort($dns[$dns_type], function ($a, $b) {
									return strcmp($a['ip'], $b['ip']);
								});
								break;

							case 'NS':
							case 'CNAME':
								usort($dns[$dns_type], function ($a, $b) {
									return strcmp($a['target'], $b['target']);
								});
								break;

							case 'TXT':
								usort($dns[$dns_type], function ($a, $b) {
									return strcmp($a['txt'], $b['txt']);
								});
								break;

							case 'AAAA':
								usort($dns[$dns_type], function ($a, $b) {
									return strcmp($a['ipv6'], $b['ipv6']);
								});
								break;

							case 'SOA':
							case 'MX':
							case 'CAA':
								usort($dns[$dns_type], function ($a, $b) {
									return strcmp($a['id'], $b['id']);
								});
								break;
						}
					}

					$total_dns_types_found += count($dns[$dns_type]) ? 1 : 0;
					$total_dns_records_found += count($dns[$dns_type]);
				}

				/* Potential checks against the previous DNS records */
				if($row->dns && $row->total_checks > 0) {
					foreach ($row->settings->dns_types as $dns_type) {
						$old_count = count($row->dns->{$dns_type} ?? []);
						$new_count = count($dns[$dns_type]);
						$total_count = max($old_count, $new_count);

						/* Go over each of the old dns record type */
						for ($i = 0; $i < $total_count; $i++) {
							/* Check if old value exists */
							if(!isset($row->dns->{$dns_type}[$i])) {
								$dns_changes[] = [
									'dns_type' => $dns_type,
									'type' => 'added',
									'old' => [],
									'new' => $dns[$dns_type][$i],
								];

								if(in_array($dns_type, ['SOA', 'CAA', 'MX'])) {
									unset($dns[$dns_type][$i]['id']);
								}

								$array_of_dns_values = array_diff_key(array_values($dns[$dns_type][$i]), array_flip(['id']));

								$text_notification_content[] = l('dns_monitor.added') . ': ' . $dns_type . ' ' . implode(' ', $array_of_dns_values);
							}

							/* Check if new value exists */
							if(!isset($dns[$dns_type][$i])) {
								$dns_changes[] = [
									'dns_type' => $dns_type,
									'type' => 'removed',
									'old' => $row->dns->{$dns_type}[$i],
									'new' => [],
								];

								$array_of_dns_values = array_diff_key(array_values((array)$row->dns->{$dns_type}[$i]), array_flip(['id']));

								$text_notification_content[] = l('dns_monitor.removed') . ': ' . $dns_type . ' ' . implode(' ', $array_of_dns_values);
							}

							/* Checks based on the type of the DNS */
							if(isset($row->dns->{$dns_type}[$i]) && isset($dns[$dns_type][$i])) {
								$changed = null;

								switch ($dns_type) {
									case 'A':

										if($row->dns->{$dns_type}[$i]->ip !== $dns[$dns_type][$i]['ip']) {
											$changed = true;
										}

										break;

									case 'CAA':

										if($row->dns->{$dns_type}[$i]->tag !== $dns[$dns_type][$i]['tag']) {
											$changed = true;
										}

										if($row->dns->{$dns_type}[$i]->value !== $dns[$dns_type][$i]['value']) {
											$changed = true;
										}

										if($row->dns->{$dns_type}[$i]->flags !== $dns[$dns_type][$i]['flags']) {
											$changed = true;
										}

										break;

									case 'MX':

										if($row->dns->{$dns_type}[$i]->target !== $dns[$dns_type][$i]['target']) {
											$changed = true;
										}

										if($row->dns->{$dns_type}[$i]->pri !== $dns[$dns_type][$i]['pri']) {
											$changed = true;
										}

										break;

									case 'CNAME':
									case 'NS':

										if($row->dns->{$dns_type}[$i]->target !== $dns[$dns_type][$i]['target']) {
											$changed = true;
										}

										break;

									case 'TXT':

										if($row->dns->{$dns_type}[$i]->txt !== $dns[$dns_type][$i]['txt']) {
											$changed = true;
										}

										break;

									case 'SOA':

										if($row->dns->{$dns_type}[$i]->id !== $dns[$dns_type][$i]['id']) {
											$changed = true;
										}

										break;

									case 'AAAA':

										if($row->dns->{$dns_type}[$i]->ipv6 !== $dns[$dns_type][$i]['ipv6']) {
											$changed = true;
										}

										break;
								}

								if($changed) {
									$dns_changes[] = [
										'dns_type' => $dns_type,
										'type' => 'changed',
										'old' => $row->dns->{$dns_type}[$i],
										'new' => $dns[$dns_type][$i],
									];

									$array_of_dns_values_old = array_diff_key((array)$row->dns->{$dns_type}[$i], array_flip(['id']));
									$array_of_dns_values_new = array_diff_key(array_values((array)$dns[$dns_type][$i]), array_flip(['id']));

									$text_notification_content[] =
										l('dns_monitor.changed') . ' (' . l('dns_monitor.old') . '): ' . $dns_type . ' ' . implode(' ', array_values($array_of_dns_values_old))
										. "{LINEBREAK}"
										. l('dns_monitor.changed') . ' (' . l('dns_monitor.new') . '): ' . $dns_type . ' ' . implode(' ', array_values($array_of_dns_values_new));

								}
							}
						}
					}
				}

				/* Get the language for the user and set the timezone */
				\Altum\Date::$timezone = $row->timezone;

				/* Only send notifications if DNS has changed */
				$dns_has_changed = count($dns_changes);

				if($dns_has_changed) {
					/* Core data to be sent to the new processor */
					$notification_data = [
						'dns_monitor_id' => $row->dns_monitor_id,
						'name' => $row->name,
						'target' => $row->target,
						'dns_changes_json' => json_encode($dns_changes),
						'url' => url('dns-monitor/' . $row->dns_monitor_id),
					];

					/* Build a plain caught-data string for the generic message */
					$dynamic_message_data = \Altum\NotificationHandlers::build_dynamic_message_data($notification_data);

					/* Collect the human-readable DNS changes block */
					$changes_block = implode("\r\n\r\n", str_replace('{LINEBREAK}', "\r\n", $text_notification_content));

					/* Compose the generic notification text */
					$notification_message = sprintf(
						l('dns_monitor.simple_notification', $row->language),
						$row->name,
						$row->target,
						"\r\n\r\n{$changes_block}\r\n\r\n",
						$notification_data['url']
					);

					/* Prepare the email template used by the email handler */
					$email_title = sprintf(l('cron.dns_monitor.title', $row->language), $row->name);
					$email_content = (new \Altum\View('partials/cron/dns_monitor', (array)$this))->run([
						'row' => $row,
						'dns_changes' => $dns_changes,
						'content' => implode('<br /><br />', str_replace('{LINEBREAK}', '<br />', $text_notification_content)),
					]);
					$email_template = get_email_template(
						[],
						$email_title,
						['{{CONTENT}}' => $email_content],
						$email_content
					);

					/* Build the context passed to the new NotificationHandlers class */
					$context = [
						/* User (needed for anti-phishing & language) */
						'user' => $row,

						/* Email */
						'email_template' => $email_template,

						/* Basic message for most integrations */
						'message' => $notification_message,

						/* Push notifications */
						'push_title' => l('dns_monitor.push_notification.title', $row->language),
						'push_description' => sprintf(
							l('dns_monitor.push_notification.description', $row->language),
							$row->name,
							$row->target
						),

						/* WhatsApp */
						'whatsapp_template' => 'dns_monitor',
						'whatsapp_parameters' => [
							$row->name,
							$row->target,
							$notification_data['url'],
						],

						/* Twilio call */
						'twilio_call_url' => SITE_URL .
							'twiml/dns_monitor.simple_notification?param1=' . urlencode($row->name) .
							'&param2=' . urlencode($row->target) .
							'&param3=' . urlencode($changes_block) .
							'&param4=' . urlencode($notification_data['url']),

						/* Internal notification */
						'internal_icon' => 'fas fa-plug',

						/* Slack */
						'slack_emoji' => ':large_red_square:',
					];

					/* Send notifications */
					\Altum\NotificationHandlers::process(
						$notification_handlers,
						$row->notifications,
						$notification_data,
						$context
					);
				}

				$dns = json_encode($dns);
				$dns_changes = json_encode($dns_changes);

				/* Insert the DNS monitor log */
				if($dns_has_changed) {
					$dns_monitor_log_id = db()->insert('dns_monitors_logs', [
						'dns_monitor_id' => $row->dns_monitor_id,
						'user_id' => $row->user_id,
						'dns' => $dns,
						'dns_changes' => $dns_changes,
						'total_dns_types_found' => $total_dns_types_found,
						'total_dns_records_found' => $total_dns_records_found,
						'datetime' => get_date(),
					]);
				}

				/* Calculate expected next run */
				$next_check_datetime = (new \DateTime())->modify('+' . $row->settings->dns_check_interval_seconds . ' seconds')->format('Y-m-d H:i:s');
				//$next_check_datetime = (new \DateTime())->modify('+30 seconds')->format('Y-m-d H:i:s');

				/* Update the DNS monitor */
				db()->where('dns_monitor_id', $row->dns_monitor_id)->update('dns_monitors', [
					'dns' => $dns,
					'total_checks' => db()->inc(),
					'total_changes' => $dns_has_changed ? db()->inc() : $row->total_changes,
					'total_dns_types_found' => $total_dns_types_found,
					'total_dns_records_found' => $total_dns_records_found,
					'last_check_datetime' => get_date(),
					'last_change_datetime' => $dns_has_changed ? get_date() : $row->last_change_datetime,
					'next_check_datetime' => $next_check_datetime,
				]);

				/* Clear the cache */
				cache()->deleteItemsByTag('dns_monitor_id=' . $row->dns_monitor_id);

			}
		}

		$this->close();

		/* mark cron execution */
		$this->update_cron_execution_datetimes('dns_monitors_datetime');
	}

	public function game_servers() {

		$this->initiate();

		/* Early exit */
		if(!settings()->monitors_heartbeats->game_servers_is_enabled) {
			$this->close();
			$this->update_cron_execution_datetimes('game_servers_datetime');
			return;
		}

		$date = get_date();

		/* Limits */
		$batch_iterations = settings()->monitors_heartbeats->game_servers_checks_per_cron ?? 30;
		$checks_per_batch_iteration = settings()->monitors_heartbeats->game_servers_checks_per_batch_iteration_per_cron ?? 10;

		/* Cache notification handlers */
		$cached_notification_handlers = [];

		/* Get game server types */
		$game_server_types = require APP_PATH . 'includes/game_server_types.php';

		for($i = 1; $i <= $batch_iterations; $i++) {
			$result = database()->query("
                SELECT
                    `game_servers`.*,
                    `users`.`email`,
                    `users`.`plan_settings`,
                    `users`.`language`,
                    `users`.`timezone`,
                    `users`.`anti_phishing_code`
                FROM 
                    `game_servers`
                LEFT JOIN 
                    `users` ON `game_servers`.`user_id` = `users`.`user_id` 
                WHERE 
                    `game_servers`.`is_enabled` = 1
                    AND `game_servers`.`next_check_datetime` <= '{$date}' 
                    AND `users`.`status` = 1
                ORDER BY `game_servers`.`next_check_datetime`
                LIMIT {$checks_per_batch_iteration}
            ");

			if(!$result->num_rows) {
				break;
			}

			while ($row = $result->fetch_object()) {
				if(DEBUG) printf('Going through %s (%s) game server..<br />', $row->name, $row->target);

				$row->plan_settings = json_decode($row->plan_settings ?? '');
				$row->notifications = json_decode($row->notifications ?? '');
				$row->settings = json_decode($row->settings ?? '');
				$row->last_logs = json_decode($row->last_logs ?? '[]');

				/* Get available notification handlers */
				if(isset($cached_notification_handlers[$row->user_id])) {
					$notification_handlers = $cached_notification_handlers[$row->user_id];
				} else {
					$notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($row->user_id);
					$cached_notification_handlers[$row->user_id] = $notification_handlers;
				}

				/* Prepare some variables */
				$is_ok = 1;
				$online_players = 0;
				$maximum_online_players = 0;
				$details = [];

				/* Calculate latency */
				$ping_start = microtime(true);

				/* Make sure $query is null by default */
				$query = null;

				/* Process the query */
				switch ($game_server_types[$row->type]['protocol']) {

					/* Minecraft */
					case 'minecraft':

						try {
							$query = new \xPaw\MinecraftPing($row->target, $row->query_port, $row->settings->timeout_seconds);

							$is_ok = 1;
							$response = $query->Query();
							$online_players = $response['players']['online'] ?? 0;
							$maximum_online_players = $response['players']['max'] ?? 0;
							$description = minecraft_description_to_plain_and_html($response['description'] ?? []);
							$details = [
								'version_code' => $response['version']['protocol'] ?? '',
								'version_name' => $response['version']['name'] ?? '',
								'favicon' => $response['favicon'] ?? '',
								'mod_type' => $response['modinfo']['type'] ?? '',
								'description' => $description['plain_text'] ?? '',
								'description_html' => $description['html'] ?? '',
							];
						} catch (\xPaw\MinecraftPingException $exception) {

							$is_ok = 0;
							$error = [
								'type' => 'exception',
								'message' => $exception->getMessage()
							];

							if(DEBUG) printf('Issue with the query.. %s<br />', $exception->getMessage());

						} finally {
							if($query ?? false) $query->close();
						}

						break;

					/* goldsource, source */
					case 'gold_source':
					case 'source':

						$engine = $game_server_types[$row->type]['protocol'] == 'gold_source' ? \xPaw\SourceQuery\SourceQuery::GOLDSOURCE : \xPaw\SourceQuery\SourceQuery::SOURCE;

						try {
							$query = new \xPaw\SourceQuery\SourceQuery();
							$query->Connect($row->target, $row->query_port, $row->settings->timeout_seconds, $engine);

							$is_ok = 1;

							$response = $query->GetInfo();

						} catch (\Exception $exception) {

							$is_ok = 0;
							$error = [
								'type' => 'exception',
								'message' => $exception->getMessage()
							];

							if(DEBUG) printf('Issue with the query.. %s<br />', $exception->getMessage());

						}

						try {
							$response_players_list = $query->GetPlayers();
						} catch (\Exception $exception) {
							echo '<br /><br />response players list: ' . print_r($exception, true);
							$response_players_list = null;
						}

						try {
							$response_variables = $query->GetRules();
						} catch (\Exception $exception) {
							echo '<br /><br />response vars list: ' . print_r($exception, true);

							$response_variables = null;
						}

						$online_players = $response['Players'] ?? 0;
						$maximum_online_players = $response['MaxPlayers'] ?? 0;

						unset($response['Players']);
						unset($response['MaxPlayers']);

						$details = [
							'players_list' => $response_players_list,
							'variables' => $response_variables,
							...$response,
						];

						if($query ?? false) $query->Disconnect();

						break;

				}

				/* Latency */
				$response_time = round((microtime(true) - $ping_start) * 1000, 4);

				/* Calculate expected next run */
				$row->settings->check_interval_seconds= ($row->settings->check_interval_seconds ?? 5);
				$next_check_datetime = (new \DateTime())->modify('+' . $row->settings->check_interval_seconds . ' seconds')->format('Y-m-d H:i:s');

				/* Calculate the rest of the things */
				$uptime_seconds = $is_ok ? $row->uptime_seconds + $row->settings->check_interval_seconds : $row->uptime_seconds;
				$downtime_seconds = !$is_ok ? $row->downtime_seconds + $row->settings->check_interval_seconds : $row->downtime_seconds;

				/* Recalculate uptime and downtime */
				$uptime = $uptime_seconds > 0 ? $uptime_seconds / ($uptime_seconds + $downtime_seconds) * 100 : 0;
				$downtime = 100 - $uptime;

				$total_ok_checks = $is_ok ? $row->total_ok_checks + 1 : $row->total_ok_checks;
				$total_not_ok_checks = !$is_ok ? $row->total_not_ok_checks + 1 : $row->total_not_ok_checks;
				$last_check_datetime = get_date();
				$last_ok_datetime = $is_ok ? get_date() : $row->last_ok_datetime;
				$last_not_ok_datetime = !$is_ok ? get_date() : $row->last_not_ok_datetime;
				$average_response_time = $response_time ? ($row->average_response_time + $response_time) / ($row->total_ok_checks == 0 ? 1 : 2) : $row->average_response_time;
				$average_online_players = $online_players ? round(($row->average_online_players + $online_players) / ($row->total_ok_checks == 0 ? 1 : 2)) : $row->average_online_players;

				/* Does the monitor have history */
				if($row->last_check_datetime) {
					$main_ok_datetime = !$row->is_ok && $is_ok ? get_date() : $row->main_ok_datetime;
					$main_not_ok_datetime = $row->is_ok && !$is_ok ? get_date() : $row->main_not_ok_datetime;
				} else {
					$main_ok_datetime = $is_ok ? get_date() : null;
					$main_not_ok_datetime = !$is_ok ? get_date() : null;
				}

				/* Keep the last logs for immediate access */
				$last_logs = [];

				for($i = 1; $i <= 6; $i++) {
					$last_logs[] = isset($row->last_logs[$i]) ? $row->last_logs[$i] : [];
				}

				$last_logs[] = [
					'is_ok' => $is_ok,
					'response_time' => $response_time,
					'online_players' => $online_players,
					'maximum_online_players' => $maximum_online_players,
					'error' => $error ?? null,
					'datetime' => get_date(),
				];

				/* Update the game server */
				db()->where('game_server_id', $row->game_server_id)->update('game_servers', [
					'is_ok' => $is_ok,
					'online_players' => $online_players,
					'maximum_online_players' => $maximum_online_players,
					'details' => json_encode($details, JSON_INVALID_UTF8_SUBSTITUTE),

					'uptime' => $uptime,
					'uptime_seconds' => $uptime_seconds,
					'downtime' => $downtime,
					'downtime_seconds' => $downtime_seconds,
					'average_response_time' => $average_response_time,
					'average_online_players' => $average_online_players,
					'total_checks' => db()->inc(),
					'total_ok_checks' => $total_ok_checks,
					'total_not_ok_checks' => $total_not_ok_checks,

					'last_check_datetime' => $last_check_datetime,
					'next_check_datetime' => $next_check_datetime,
					'main_ok_datetime' => $main_ok_datetime,
					'last_ok_datetime' => $last_ok_datetime,
					'main_not_ok_datetime' => $main_not_ok_datetime,
					'last_not_ok_datetime' => $last_not_ok_datetime,
					'last_logs' => json_encode($last_logs),
				]);

				/* Insert the history log */
				$game_server_log_id = db()->insert('game_servers_logs', [
					'game_server_id' => $row->game_server_id,
					'user_id' => $row->user_id,
					'is_ok' => $is_ok,
					'response_time' => $response_time,
					'online_players' => $online_players,
					'maximum_online_players' => $maximum_online_players,
					'error' => isset($error) ? json_encode($error) : null,
					'datetime' => get_date()
				]);

				/* Clear the cache */
				cache()->deleteItemsByTag('game_server_id=' . $row->game_server_id);

			}
		}

		$this->close();

		/* mark cron execution */
		$this->update_cron_execution_datetimes('game_servers_datetime');
	}

	public function monitors_logs_cleanup() {

		$this->initiate();

		/* Early exit */
		if(!settings()->monitors_heartbeats->monitors_is_enabled) {
			$this->close();
			$this->update_cron_execution_datetimes('monitors_logs_cleanup_datetime');
			return;
		}

		/* Only clean users that have not been cleaned recently */
		$now_datetime = get_date();

		/* Clean the track notifications table based on the users plan */
		$result = database()->query("SELECT `user_id`, `plan_settings` FROM `users` WHERE `status` = 1 AND `next_logs_cleanup_datetime` < '{$now_datetime}'");

		/* Go through each result */
		while($user = $result->fetch_object()) {
			/* Update user cleanup date */
			db()->where('user_id', $user->user_id)->update('users', ['next_logs_cleanup_datetime' => (new \DateTime())->modify('+1 days')->format('Y-m-d H:i:s')]);

			$user->plan_settings = json_decode($user->plan_settings ?? '');

			if($user->plan_settings->logs_retention == -1) continue;

			/* Clear out old logs */
			$x_days_ago_datetime = (new \DateTime())->modify('-' . ($user->plan_settings->logs_retention ?? 90) . ' days')->format('Y-m-d H:i:s');

			database()->query("DELETE FROM `monitors_logs` WHERE `user_id` = {$user->user_id} AND `datetime` < '{$x_days_ago_datetime}'");
			database()->query("DELETE FROM `dns_monitors_logs` WHERE `user_id` = {$user->user_id} AND `datetime` < '{$x_days_ago_datetime}'");
			database()->query("DELETE FROM `heartbeats_logs` WHERE `user_id` = {$user->user_id} AND `datetime` < '{$x_days_ago_datetime}'");
			database()->query("DELETE FROM `server_monitors_logs` WHERE `user_id` = {$user->user_id} AND `datetime` < '{$x_days_ago_datetime}'");
			database()->query("DELETE FROM `game_servers_logs` WHERE `user_id` = {$user->user_id} AND `datetime` < '{$x_days_ago_datetime}'");

			if(DEBUG) {
				echo sprintf('User logs cleanup done for user_id %s', $user->user_id);
			}
		}

		$this->close();

		/* mark cron execution */
		$this->update_cron_execution_datetimes('monitors_logs_cleanup_datetime');
	}

	public function monitors_email_reports() {

		$this->initiate();

		/* Early exit */
		if(!settings()->monitors_heartbeats->email_reports_is_enabled) {
			$this->close();
			$this->update_cron_execution_datetimes('monitors_email_reports_datetime');
			return;
		}

		$date = get_date();

		/* Limits */
		$limit = settings()->monitors_heartbeats->monitors_email_reports_per_cron ?? 10;

		/* Determine the frequency of email reports */
		$days_interval = 7;

		switch(settings()->monitors_heartbeats->email_reports_is_enabled) {
			case 'weekly':
				$days_interval = 7;

				break;

			case 'monthly':
				$days_interval = 30;

				break;
		}

		/* Get potential monitors from users that have almost all the conditions to get an email report right now */
		$result = database()->query("
            SELECT
                `monitors`.`monitor_id`,
                `monitors`.`name`,
                `monitors`.`target`,
                `monitors`.`port`,
                `monitors`.`email_reports_last_datetime`,
                `users`.`user_id`,
                `users`.`email`,
                `users`.`plan_settings`,
                `users`.`language`
            FROM 
                `monitors`
            LEFT JOIN 
                `users` ON `monitors`.`user_id` = `users`.`user_id` 
            WHERE 
                `users`.`status` = 1
                AND `monitors`.`is_enabled` = 1 
                AND `monitors`.`email_reports_is_enabled` = 1
				AND DATE_ADD(`monitors`.`email_reports_last_datetime`, INTERVAL {$days_interval} DAY) <= '{$date}'
            LIMIT {$limit}
        ");

		/* Go through each result */
		while($row = $result->fetch_object()) {
			$row->plan_settings = json_decode($row->plan_settings);

			/* Make sure the plan still lets the user get email reports */
			if(!$row->plan_settings->email_reports_is_enabled) {
				db()->where('monitor_id', $row->monitor_id)->update('monitors', ['email_reports_is_enabled' => 0]);
				continue;
			}

			/* Prepare */
			$start_date = (new \DateTime())->modify('-' . $days_interval . ' days')->format('Y-m-d H:i:s');

			/* Monitor logs */
			$monitor_logs = [];

			$monitor_logs_result = database()->query("
                SELECT 
                    `is_ok`,
                    `response_time`,
                    `datetime`
                FROM 
                    `monitors_logs`
                WHERE 
                    `monitor_id` = {$row->monitor_id} 
                    AND (`datetime` BETWEEN '{$start_date}' AND '{$date}')
            ");

			$total_ok_checks = 0;
			$total_not_ok_checks = 0;
			$total_response_time = 0;

			while($monitor_log = $monitor_logs_result->fetch_object()) {
				$monitor_logs[] = $monitor_log;

				$total_ok_checks = $monitor_log->is_ok ? $total_ok_checks + 1 : $total_ok_checks;
				$total_not_ok_checks = !$monitor_log->is_ok ? $total_not_ok_checks + 1 : $total_not_ok_checks;
				$total_response_time += $monitor_log->response_time;
			}

			/* Monitor incidents */
			$monitor_incidents = [];

			$monitor_incidents_result = database()->query("
                SELECT 
                    `start_datetime`,
                    `end_datetime`
                FROM 
                    `incidents`
                WHERE 
                    `monitor_id` = {$row->monitor_id} 
                    AND `start_datetime` >= '{$start_date}' 
                    AND `end_datetime` <= '{$date}'
            ");

			while($monitor_incident = $monitor_incidents_result->fetch_object()) {
				$monitor_incidents[] = $monitor_incident;
			}

			/* calculate some data */
			$total_monitor_logs = count($monitor_logs);
			$uptime = $total_ok_checks > 0 ? $total_ok_checks / ($total_ok_checks + $total_not_ok_checks) * 100 : 0;
			$downtime = 100 - $uptime;
			$average_response_time = $total_ok_checks > 0 ? $total_response_time / $total_ok_checks : 0;

			/* Prepare the email title */
			$replacers = [
				'{{MONITOR:NAME}}' => $row->name,
				'{{START_DATE}}' => \Altum\Date::get($start_date, 5),
				'{{END_DATE}}' => \Altum\Date::get('', 5),
			];

			$email_title = str_replace(
				array_keys($replacers),
				array_values($replacers),
				l('cron.monitor_email_report.title', $row->language)
			);

			/* Prepare the View for the email content */
			$data = [
				'row'                       => $row,
				'monitor_logs'              => $monitor_logs,
				'total_monitor_logs'        => $total_monitor_logs,
				'monitor_logs_data' => [
					'uptime'                => $uptime,
					'downtime'              => $downtime,
					'average_response_time' => $average_response_time,
					'total_ok_checks'       => $total_ok_checks,
					'total_not_ok_checks'   => $total_not_ok_checks
				],
				'monitor_incidents'         => $monitor_incidents,

				'start_date'                => $start_date,
				'end_date'                  => $date
			];

			$email_content = (new \Altum\View('partials/cron/monitor_email_report', (array) $this))->run($data);

			/* Send the email */
			send_mail($row->email, $email_title, $email_content);

			/* Update the store */
			db()->where('monitor_id', $row->monitor_id)->update('monitors', ['email_reports_last_datetime' => $date]);

			/* Insert email log */
			db()->insert('email_reports', ['user_id' => $row->user_id, 'monitor_id' => $row->monitor_id, 'datetime' => $date]);

			if(DEBUG) {
				echo sprintf('Email sent for user_id %s and monitor_id %s', $row->user_id, $row->monitor_id);
			}
		}

		$this->close();

		/* mark cron execution */
		$this->update_cron_execution_datetimes('monitors_email_reports_datetime');
	}

	public function heartbeats_email_reports() {

		$this->initiate();

		/* Early exit */
		if(!settings()->monitors_heartbeats->email_reports_is_enabled) {
			$this->close();
			$this->update_cron_execution_datetimes('heartbeats_email_reports_datetime');
			return;
		}

		$date = get_date();

		/* Limits */
		$limit = settings()->monitors_heartbeats->heartbeats_email_reports_per_cron ?? 10;

		/* Determine the frequency of email reports */
		$days_interval = 7;

		switch(settings()->monitors_heartbeats->email_reports_is_enabled) {
			case 'weekly':
				$days_interval = 7;

				break;

			case 'monthly':
				$days_interval = 30;

				break;
		}

		/* Get potential heartbeats from users that have almost all the conditions to get an email report right now */
		$result = database()->query("
            SELECT
                `heartbeats`.`heartbeat_id`,
                `heartbeats`.`name`,
                `heartbeats`.`email_reports_last_datetime`,
                `users`.`user_id`,
                `users`.`email`,
                `users`.`plan_settings`,
                `users`.`language`
            FROM 
                `heartbeats`
            LEFT JOIN 
                `users` ON `heartbeats`.`user_id` = `users`.`user_id` 
            WHERE 
                `users`.`status` = 1
                AND `heartbeats`.`is_enabled` = 1 
                AND `heartbeats`.`email_reports_is_enabled` = 1
				AND DATE_ADD(`heartbeats`.`email_reports_last_datetime`, INTERVAL {$days_interval} DAY) <= '{$date}'
            LIMIT {$limit}
        ");

		/* Go through each result */
		while($row = $result->fetch_object()) {
			$row->plan_settings = json_decode($row->plan_settings);

			/* Make sure the plan still lets the user get email reports */
			if(!$row->plan_settings->email_reports_is_enabled) {
				db()->where('heartbeat_id', $row->heartbeat_id)->update('heartbeats', ['email_reports_is_enabled' => 0]);
				continue;
			}

			/* Prepare */
			$start_date = (new \DateTime())->modify('-' . $days_interval . ' days')->format('Y-m-d H:i:s');

			/* Monitor logs */
			$heartbeat_logs = [];

			$heartbeat_logs_result = database()->query("
                SELECT 
                    `is_ok`,
                    `datetime`
                FROM 
                    `heartbeats_logs`
                WHERE 
                    `heartbeat_id` = {$row->heartbeat_id} 
                    AND (`datetime` BETWEEN '{$start_date}' AND '{$date}')
            ");

			$total_runs = 0;
			$total_missed_runs = 0;

			while($heartbeat_log = $heartbeat_logs_result->fetch_object()) {
				$heartbeat_logs[] = $heartbeat_log;

				$total_runs = $heartbeat_log->is_ok ? $total_runs + 1 : $total_runs;
				$total_missed_runs = !$heartbeat_log->is_ok ? $total_missed_runs + 1 : $total_missed_runs;
			}

			/* Monitor incidents */
			$heartbeat_incidents = [];

			$heartbeat_incidents_result = database()->query("
                SELECT 
                    `start_datetime`,
                    `end_datetime`
                FROM 
                    `incidents`
                WHERE 
                    `heartbeat_id` = {$row->heartbeat_id} 
                    AND `start_datetime` >= '{$start_date}' 
                    AND `end_datetime` <= '{$date}'
            ");

			while($heartbeat_incident = $heartbeat_incidents_result->fetch_object()) {
				$heartbeat_incidents[] = $heartbeat_incident;
			}

			/* calculate some data */
			$total_heartbeat_logs = count($heartbeat_logs);
			$uptime = $total_runs > 0 ? $total_runs / ($total_runs + $total_missed_runs) * 100 : 0;
			$downtime = 100 - $uptime;

			/* Prepare the email title */
			$replacers = [
				'{{HEARTBEAT:NAME}}' => $row->name,
				'{{START_DATE}}' => \Altum\Date::get($start_date, 5),
				'{{END_DATE}}' => \Altum\Date::get('', 5),
			];

			$email_title = str_replace(
				array_keys($replacers),
				array_values($replacers),
				l('cron.heartbeat_email_report.title', $row->language)
			);

			/* Prepare the View for the email content */
			$data = [
				'row'                       => $row,
				'heartbeat_logs'            => $heartbeat_logs,
				'total_heartbeat_logs'      => $total_heartbeat_logs,
				'heartbeat_logs_data' => [
					'uptime'                => $uptime,
					'downtime'              => $downtime,
					'total_runs'            => $total_runs,
					'total_missed_runs'     => $total_missed_runs
				],
				'heartbeat_incidents'       => $heartbeat_incidents,

				'start_date'                => $start_date,
				'end_date'                  => $date
			];

			$email_content = (new \Altum\View('partials/cron/heartbeat_email_report', (array) $this))->run($data);

			/* Send the email */
			send_mail($row->email, $email_title, $email_content);

			/* Update the store */
			db()->where('heartbeat_id', $row->heartbeat_id)->update('heartbeats', ['email_reports_last_datetime' => $date]);

			/* Insert email log */
			db()->insert('email_reports', ['user_id' => $row->user_id, 'heartbeat_id' => $row->heartbeat_id, 'datetime' => $date]);

			if(DEBUG) {
				echo sprintf('Email sent for user_id %s and heartbeat_id %s', $row->user_id, $row->heartbeat_id);
			}
		}

		$this->close();

		/* mark cron execution */
		$this->update_cron_execution_datetimes('heartbeats_email_reports_datetime');
	}

	public function broadcasts() {

		$this->initiate();

		/* Only run this part if the broadcasts system is enabled */
		if(!settings()->content->broadcasts_is_enabled) {
			$this->close();
			$this->update_cron_execution_datetimes('broadcasts_datetime');
			return;
		}

		/* We'll send up to X emails per run */
		$max_batch_size = settings()->content->broadcasts_emails_per_cron ?? 40;

		/* Fetch a broadcast in "processing" status */
		$broadcast = db()->where('status', 'processing')->getOne('broadcasts');
		if(!$broadcast) {
			$this->close();
			$this->update_cron_execution_datetimes('broadcasts_datetime');
			return;
		}

		$broadcast->users_ids = json_decode($broadcast->users_ids ?? '[]', true);
		$broadcast->sent_users_ids = json_decode($broadcast->sent_users_ids ?? '[]', true);
		$broadcast->settings = json_decode($broadcast->settings ?? '[]');

		/* Find which users are left to process */
		$remaining_user_ids = array_values(array_diff($broadcast->users_ids, $broadcast->sent_users_ids));

		/* If no one is left, mark broadcast as "sent" and exit */
		if(empty($remaining_user_ids)) {

			$sent_emails_count = count($broadcast->sent_users_ids);

			db()->where('broadcast_id', $broadcast->broadcast_id)->update('broadcasts', [
				'sent_emails'              => $sent_emails_count,
				'sent_users_ids'           => json_encode($broadcast->sent_users_ids),
				'status'                   => 'sent',
				'last_sent_email_datetime' => get_date(),
			]);

			$this->close();
			$this->update_cron_execution_datetimes('broadcasts_datetime');

			return;
		}

		/* Get all batch users at once in one go */
		$user_ids_for_this_run = array_slice($remaining_user_ids, 0, $max_batch_size);

		$users = db()
			->where('user_id', $user_ids_for_this_run, 'IN')
			->get('users', null, [
				'user_id',
				'name',
				'email',
				'language',
				'anti_phishing_code',
				'continent_code',
				'country',
				'city_name',
				'device_type',
				'os_name',
				'browser_name',
				'browser_language'
			]);

		$users_ids = array_column($users, 'user_id');

		/* Non existing users in this batch */
		$missing_user_ids = array_diff($user_ids_for_this_run, $users_ids);

		/* Mark non existing users as processed (sent) */
		$broadcast->sent_users_ids = array_merge($broadcast->sent_users_ids, $missing_user_ids);

		/* Send emails only for existing users */
		if(!empty($users)) {

			/* Initialize PHPMailer once for this batch */
			$mail = new \PHPMailer\PHPMailer\PHPMailer();
			$mail->CharSet = 'UTF-8';
			$mail->isSMTP();
			$mail->isHTML(true);

			/* SMTP connection settings */
			$mail->SMTPAuth = settings()->smtp->auth;
			$mail->Host = settings()->smtp->host;
			$mail->Port = settings()->smtp->port;
			$mail->Username = settings()->smtp->username;
			$mail->Password = settings()->smtp->password;

			if(settings()->smtp->encryption != '0') {
				$mail->SMTPSecure = settings()->smtp->encryption;
			}

			/* Keep the SMTP connection alive */
			$mail->SMTPKeepAlive = true;

			/* Set From / Reply-to */
			$mail->setFrom(settings()->smtp->from, settings()->smtp->from_name);
			if(!empty(settings()->smtp->reply_to) && !empty(settings()->smtp->reply_to_name)) {
				$mail->addReplyTo(settings()->smtp->reply_to, settings()->smtp->reply_to_name);
			} else {
				$mail->addReplyTo(settings()->smtp->from, settings()->smtp->from_name);
			}

			/* Optional CC/BCC */
			if(settings()->smtp->cc) {
				foreach (explode(',', settings()->smtp->cc) as $cc_email) {
					$mail->addCC(trim($cc_email));
				}
			}
			if(settings()->smtp->bcc) {
				foreach (explode(',', settings()->smtp->bcc) as $bcc_email) {
					$mail->addBCC(trim($bcc_email));
				}
			}

			/* Loop through users and send */
			foreach($users as $user) {

				/* Prepare placeholders and the final template */
				$vars = [
					'{{USER:NAME}}'             => $user->name,
					'{{USER:EMAIL}}'            => $user->email,
					'{{USER:CONTINENT_NAME}}'   => get_continent_from_continent_code($user->continent_code),
					'{{USER:COUNTRY_NAME}}'     => get_country_from_country_code($user->country),
					'{{USER:CITY_NAME}}'        => $user->city_name,
					'{{USER:DEVICE_TYPE}}'      => l('global.device.' . $user->device_type),
					'{{USER:OS_NAME}}'          => $user->os_name,
					'{{USER:BROWSER_NAME}}'     => $user->browser_name,
					'{{USER:BROWSER_LANGUAGE}}' => get_language_from_locale($user->browser_language),
				];

				$email_template = get_email_template(
					$vars,
					htmlspecialchars_decode($broadcast->subject),
					$vars,
					convert_editorjs_json_to_html($broadcast->content)
				);

				/* Tracking pixel & link rewriting */
				if(settings()->content->broadcasts_statistics_is_enabled) {
					$tracking_id = base64_encode('broadcast_id=' . $broadcast->broadcast_id . '&user_id=' . $user->user_id);
					$email_template->body .= '<img src="' . SITE_URL . 'broadcast?id=' . $tracking_id . '" style="display: none;" />';
					$email_template->body = preg_replace(
						'/<a href=\"(.+)\"/',
						'<a href="' . SITE_URL . 'broadcast?id=' . $tracking_id . '&url=$1"',
						$email_template->body
					);
				}

				/* Clear addresses from previous iteration */
				$mail->clearAddresses();
				$mail->clearCCs();
				$mail->clearBCCs();

				/* Add new email address */
				$mail->addAddress($user->email);

				/* Unsubscribe token & setup */
				$secret = hash('sha256', settings()->license->license . '|' . settings()->cron->key . '|list-unsubscribe|v1', true);
				$token_expires_in_days = 90;
				$token = generate_unsubscribe_token($user->user_id, 60 * 60 * 24 * $token_expires_in_days, $secret);
				$unsubscribe_url = SITE_URL . 'unsubscribe?token=' . rawurlencode($token);

				/* Add the mail headers for unsub */
				$mail->addCustomHeader('List-Unsubscribe', '<' . $unsubscribe_url . '>');
				$mail->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');

				/* Process the email title, template and body */
				extract(process_send_mail_template(
					$email_template->subject,
					$email_template->body,
					[
						'is_broadcast'       => true,
						'is_system_email'    => $broadcast->settings->is_system_email,
						'anti_phishing_code' => $user->anti_phishing_code,
						'language'           => $user->language,
						'unsubscribe_url'    => $unsubscribe_url,
					]
				));

				/* Set subject/body, then send */
				$mail->Subject = $title;
				$mail->Body = $email_template;
				$mail->AltBody = strip_tags($mail->Body);

				/* SEND */
				$mail->send();

				/* Track who we just processed (sent or attempted) */
				$broadcast->sent_users_ids[] = $user->user_id;
			}

			/* Close this SMTP connection for the batch */
			$mail->smtpClose();
		}

		/* Total "sent" (processed) */
		$sent_emails_count = count($broadcast->sent_users_ids);

		/* Check if all users (existing or not) have been processed */
		$all_users_processed = empty(array_diff($broadcast->users_ids, $broadcast->sent_users_ids));

		/* Update broadcast once for the entire batch */
		db()->where('broadcast_id', $broadcast->broadcast_id)->update('broadcasts', [
			'sent_emails'              => $sent_emails_count,
			'sent_users_ids'           => json_encode($broadcast->sent_users_ids),
			'status'                   => $all_users_processed ? 'sent' : 'processing',
			'last_sent_email_datetime' => get_date(),
		]);

		/* Debugging */
		if(DEBUG) {
			echo '<br />' . 'broadcasts() - broadcast_id - ' . $broadcast->broadcast_id;
		}

		$this->close();

		$this->update_cron_execution_datetimes('broadcasts_datetime');
	}

	public function push_notifications() {
		if(\Altum\Plugin::is_active('push-notifications')) {

			$this->initiate();

			require_once \Altum\Plugin::get('push-notifications')->path . 'controllers/Cron.php';

			$this->close();

			/* mark cron execution */
			$this->update_cron_execution_datetimes('push_notifications_datetime');
		}
	}

}
