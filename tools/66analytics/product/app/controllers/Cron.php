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

	public function index() {

		$this->initiate();

		$this->users_plan_expiry_checker();

		$this->users_deletion_reminder();

		$this->auto_delete_inactive_users();

		$this->auto_delete_unconfirmed_users();

		$this->websites_replays_cleanup();

		$this->websites_replays_offload();

		$this->analytics_cleanup();

		$this->users_plan_expiry_reminder();

		$this->users_plan_renewal_reminder();

		$this->check_support();

		$this->websites_sessions_events_notice();

		$this->websites_events_children_notice();

		$this->websites_sessions_replays_notice();

		/* Make sure the reset date month is different than the current one to avoid double resetting */
		$reset_date = settings()->cron->reset_date ? (new \DateTime(settings()->cron->reset_date))->format('m') : null;
		$current_date = (new \DateTime())->format('m');

		if($reset_date != $current_date) {
			/* Benchmark */
			$this->processing_time = microtime(true);

			$this->logs_cleanup();

			$this->users_logs_cleanup();

			$this->internal_notifications_cleanup();

			$this->websites_events_reset();

			/* Clear the cache */
			cache()->deleteItem('settings');

			$this->update_cron_execution_datetimes('reset_date');
		}

		$this->close();

		$this->update_cron_execution_datetimes('cron_datetime');
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

	private function websites_events_reset() {
		db()->update('websites', [
			'current_month_sessions_events' => 0,
			'current_month_events_children' => 0,
			'current_month_sessions_replays' => 0,
			'plan_sessions_events_limit_notice' => 0,
			'plan_events_children_limit_notice' => 0,
			'plan_sessions_replays_limit_notice' => 0,
		]);
	}

	private function websites_sessions_events_notice() {
		if(!settings()->analytics->email_notices_is_enabled) {
			return;
		}

		/* Get the users that need to be reminded */
		$result = database()->query("
            SELECT
                `website_id`,
                `users`.`user_id`,
                `plan_id`,
                `users`.`name`,
                `websites`.`name` AS `website_name`,
                `websites`.`host` AS `website_host`,
                `websites`.`path` AS `website_path`,
                `email`,
                `language`,
                `anti_phishing_code`,
                `plan_settings`
            FROM
                `users`
            LEFT JOIN
                `websites`
                ON `users`.`user_id` = `websites`.`user_id`
            WHERE
                `users`.`status` = 1
                AND JSON_UNQUOTE(JSON_EXTRACT(`users`.`plan_settings`, '$.sessions_events_limit')) != '-1'
                AND CAST(JSON_UNQUOTE(JSON_EXTRACT(`users`.`plan_settings`, '$.sessions_events_limit')) AS UNSIGNED) < `websites`.`current_month_sessions_events`
                AND `websites`.`plan_sessions_events_limit_notice` = 0
            LIMIT 25
        ");

		/* Go through each result */
		while($row = $result->fetch_object()) {
			$row->plan_settings = json_decode($row->plan_settings ?? '');

			db()->where('website_id', $row->website_id)->update('websites', [
				'plan_sessions_events_limit_notice' => 1,
			]);

			/* Clear the cache */
			cache()->deleteItemsByTag('user_id=' . $row->user_id);

			/* Prepare the email */
			$email_template = get_email_template(
				[],
				l('global.emails.user_sessions_events_limit.subject', $row->language),
				[
					'{{USER_PLAN_RENEW_LINK}}' => url('plan'),
					'{{NAME}}' => $row->name,
					'{{PLAN_NAME}}' => (new \Altum\Models\Plan())->get_plan_by_id($row->plan_id)->name,
					'{{WEBSITE_NAME}}' => $row->website_name . ' (' . $row->website_host . $row->website_path . ')',
					'{{SESSIONS_EVENTS_LIMIT}}' => $row->plan_settings->sessions_events_limit,
				],
				l('global.emails.user_sessions_events_limit.body', $row->language)
			);

			send_mail($row->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $row->anti_phishing_code, 'language' => $row->language]);

			if(DEBUG) {
				echo sprintf('User sessions events limit notice email sent for user_id %s', $row->user_id);
			}
		}
	}

	private function websites_events_children_notice() {
		if(!settings()->analytics->email_notices_is_enabled) {
			return;
		}

		/* Get the users that need to be reminded */
		$result = database()->query("
            SELECT
                `website_id`,
                `users`.`user_id`,
                `plan_id`,
                `users`.`name`,
                `websites`.`name` AS `website_name`,
                `websites`.`host` AS `website_host`,
                `websites`.`path` AS `website_path`,
                `email`,
                `language`,
                `anti_phishing_code`,
                `plan_settings`
            FROM
                `users`
            LEFT JOIN
                `websites`
                ON `users`.`user_id` = `websites`.`user_id`
            WHERE
                `users`.`status` = 1
                AND JSON_UNQUOTE(JSON_EXTRACT(`users`.`plan_settings`, '$.events_children_limit')) != '-1'
                AND CAST(JSON_UNQUOTE(JSON_EXTRACT(`users`.`plan_settings`, '$.events_children_limit')) AS UNSIGNED) < `websites`.`current_month_events_children`
                AND `websites`.`plan_events_children_limit_notice` = 0
            LIMIT 25
        ");

		/* Go through each result */
		while($row = $result->fetch_object()) {
			$row->plan_settings = json_decode($row->plan_settings ?? '');

			db()->where('website_id', $row->website_id)->update('websites', [
				'plan_events_children_limit_notice' => 1,
			]);

			/* Clear the cache */
			cache()->deleteItemsByTag('user_id=' . $row->user_id);

			/* Prepare the email */
			$email_template = get_email_template(
				[],
				l('global.emails.user_events_children_limit.subject', $row->language),
				[
					'{{USER_PLAN_RENEW_LINK}}' => url('plan'),
					'{{NAME}}' => $row->name,
					'{{PLAN_NAME}}' => (new \Altum\Models\Plan())->get_plan_by_id($row->plan_id)->name,
					'{{WEBSITE_NAME}}' => $row->website_name . ' (' . $row->website_host . $row->website_path . ')',
					'{{EVENTS_CHILDREN_LIMIT}}' => $row->plan_settings->events_children_limit,
				],
				l('global.emails.user_events_children_limit.body', $row->language)
			);

			send_mail($row->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $row->anti_phishing_code, 'language' => $row->language]);

			if(DEBUG) {
				echo sprintf('User events children limit notice email sent for user_id %s', $row->user_id);
			}
		}
	}

	private function websites_sessions_replays_notice() {
		if(!settings()->analytics->email_notices_is_enabled) {
			return;
		}

		/* Get the users that need to be reminded */
		$result = database()->query("
            SELECT
                `website_id`,
                `users`.`user_id`,
                `plan_id`,
                `users`.`name`,
                `websites`.`name` AS `website_name`,
                `websites`.`host` AS `website_host`,
                `websites`.`path` AS `website_path`,
                `email`,
                `language`,
                `anti_phishing_code`,
                `plan_settings`
            FROM
                `users`
            LEFT JOIN
                `websites`
                ON `users`.`user_id` = `websites`.`user_id`
            WHERE
                `users`.`status` = 1
                AND JSON_UNQUOTE(JSON_EXTRACT(`users`.`plan_settings`, '$.sessions_replays_limit')) != '-1'
                AND CAST(JSON_UNQUOTE(JSON_EXTRACT(`users`.`plan_settings`, '$.sessions_replays_limit')) AS UNSIGNED) < `websites`.`current_month_sessions_replays`
                AND `websites`.`plan_sessions_replays_limit_notice` = 0
            LIMIT 25
        ");

		/* Go through each result */
		while($row = $result->fetch_object()) {
			$row->plan_settings = json_decode($row->plan_settings ?? '');

			db()->where('website_id', $row->website_id)->update('websites', [
				'plan_sessions_replays_limit_notice' => 1,
			]);

			/* Clear the cache */
			cache()->deleteItemsByTag('user_id=' . $row->user_id);

			/* Prepare the email */
			$email_template = get_email_template(
				[],
				l('global.emails.user_sessions_replays_limit.subject', $row->language),
				[
					'{{USER_PLAN_RENEW_LINK}}' => url('plan'),
					'{{NAME}}' => $row->name,
					'{{PLAN_NAME}}' => (new \Altum\Models\Plan())->get_plan_by_id($row->plan_id)->name,
					'{{WEBSITE_NAME}}' => $row->website_name . ' (' . $row->website_host . $row->website_path . ')',
					'{{SESSIONS_REPLAYS_LIMIT}}' => $row->plan_settings->sessions_replays_limit,
				],
				l('global.emails.user_sessions_replays_limit.body', $row->language)
			);

			send_mail($row->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $row->anti_phishing_code, 'language' => $row->language]);

			if(DEBUG) {
				echo sprintf('User sessions replays limit notice email sent for user_id %s', $row->user_id);
			}
		}
	}

	private function analytics_cleanup() {
		$date = get_date();

		$website_ids = [];

		$result = database()->query("
            SELECT DISTINCT `website_id` FROM `sessions_events` WHERE `expiration_date` < '{$date}'
            UNION
            SELECT DISTINCT `website_id` FROM `lightweight_events` WHERE `expiration_date` < '{$date}'
        ");

		while($row = $result->fetch_object()) {
			$website_ids[] = (int) $row->website_id;
		}

		if(count($website_ids)) {
			db()->where('website_id', $website_ids, 'IN')->update('websites', [
				'pageviews_stats_last_datetime' => null,
			]);
		}

		db()->where('expiration_date', $date, '<')->delete('sessions_events');
		db()->where('expiration_date', $date, '<')->delete('lightweight_events');
		db()->where('expiration_date', $date, '<')->delete('goals_conversions');
		db()->where('expiration_date', $date, '<')->delete('events_children');
		db()->where('expiration_date', $date, '<')->delete('heatmap_snapshot_clicks');
	}

	private function websites_replays_cleanup() {
		$date = get_date();

		$check_datetime = (new \DateTime())->modify('-1 hour')->format('Y-m-d H:i:s');

		$result = database()->query("
			(
				SELECT `session_id`, `is_offloaded`, `datetime`
				FROM `sessions_replays`
				WHERE `is_too_short` = 1 AND `datetime` < '{$check_datetime}'
				LIMIT 15
			)
			UNION ALL
			(
				SELECT `session_id`, `is_offloaded`, `datetime`
				FROM `sessions_replays`
				WHERE `expiration_date` < '{$date}'
				LIMIT 15
			)
			LIMIT 30
		");

		if($result->num_rows) {
			\Altum\Cache::store_initialize();
		}

		while($row = $result->fetch_object()) {

			/* Delete DB replay entry */
			db()->where('session_id', $row->session_id)->delete('sessions_replays');

			/* Delete chunk index */
			$index_item = cache('store_adapter')->getItem('session_replay_keys_' . $row->session_id);
			$chunk_keys = $index_item->get() ?: [];
			cache('store_adapter')->deleteItem('session_replay_keys_' . $row->session_id);

			/* Delete each chunk */
			foreach($chunk_keys as $chunk_key) {
				cache('store_adapter')->deleteItem($chunk_key);
			}

			/* Delete offloaded storage if needed */
			if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url && $row->is_offloaded) {
				$file_name = base64_encode($row->session_id . $row->datetime) . '.txt';

				try {
					$s3 = new \Aws\S3\S3Client(get_aws_s3_config());

					$s3->deleteObject([
						'Bucket' => settings()->offload->storage_name,
						'Key' => UPLOADS_URL_PATH . 'store/' . $file_name,
					]);

				} catch (\Exception $exception) {
					dil($exception->getMessage());
				}
			}
		}
	}

	private function websites_replays_offload() {
		if(!\Altum\Plugin::is_active('offload') || !settings()->offload->uploads_url) {
			return;
		}

		$result = database()->query("
            SELECT
                `session_id`,
                `datetime`
            FROM
                `sessions_replays`
            WHERE
                DATE_ADD(`last_datetime`, INTERVAL 1 DAY) < NOW()
                AND `is_offloaded` = 0
            LIMIT 25;
        ");

		if($result->num_rows) {
			\Altum\Cache::store_initialize();
		}

		while($row = $result->fetch_object()) {

			/* Load all chunk keys */
			$index_item = cache('store_adapter')->getItem('session_replay_keys_' . $row->session_id);
			$chunk_keys = $index_item->get() ?: [];

			/* Load chunk data into array */
			$all_chunks = [];

			foreach($chunk_keys as $chunk_key) {
				$chunk_item = cache('store_adapter')->getItem($chunk_key)->get();

				if($chunk_item) {
					$all_chunks[] = $chunk_item;
				}
			}

			/* If nothing to upload, just mark offloaded and continue */
			if(empty($all_chunks)) {
				db()->where('session_id', $row->session_id)->update('sessions_replays', ['is_offloaded' => 1]);
				cache('store_adapter')->deleteItem('session_replay_keys_' . $row->session_id);
				continue;
			}

			$file_data = serialize($all_chunks);
			$file_name = base64_encode($row->session_id . $row->datetime) . '.txt';

			/* Upload to S3 */
			try {
				$s3 = new \Aws\S3\S3Client(get_aws_s3_config());

				$s3->putObject([
					'Bucket' => settings()->offload->storage_name,
					'Key' => UPLOADS_URL_PATH . 'store/' . $file_name,
					'ContentType' => 'text/plain',
					'Body' => $file_data,
					'ACL' => 'public-read'
				]);

			} catch (\Exception $exception) {
				dil($exception->getMessage());
			}

			/* Mark offloaded in DB */
			db()->where('session_id', $row->session_id)->update('sessions_replays', ['is_offloaded' => 1]);

			/* Delete all chunks and index */
			cache('store_adapter')->deleteItem('session_replay_keys_' . $row->session_id);

			foreach($chunk_keys as $chunk_key) {
				cache('store_adapter')->deleteItem($chunk_key);
			}
		}
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

	private function users_plan_renewal_reminder() {
		if(!settings()->payment->user_plan_renewal_reminder) {
			return;
		}

		/* Determine when to send the email reminder */
		$days = settings()->payment->user_plan_renewal_reminder;
		$date = get_date();
		$future_date = (new \DateTime())->modify('+' . $days . ' days')->format('Y-m-d H:i:s');

		/* Get active recurring subscriptions that will renew soon */
		$result = database()->query("
            SELECT
                `user_id`,
                `name`,
                `email`,
                `plan_id`,
                `plan_expiration_date`,
                `payment_processor`,
                `payment_total_amount`,
                `payment_currency`,
                `language`,
                `anti_phishing_code`
            FROM
                `users`
            WHERE
                `status` = 1
                AND `plan_id` <> 'free'
                AND `plan_renewal_reminder` = '0'
                AND `payment_subscription_id` IS NOT NULL
                AND `payment_subscription_id` <> ''
                AND `plan_expiration_date` > '{$date}'
				AND `plan_expiration_date` < '{$future_date}'
            LIMIT 25
        ");

		$plans = [];
		if($result->num_rows) {
			$plans = (new \Altum\Models\Plan())->get_plans();
		}

		/* Go through each result */
		while($user = $result->fetch_object()) {

			/* Determine the exact days until renewal */
			$days_until_renewal = (new \DateTime($user->plan_expiration_date))->diff((new \DateTime()))->days;

			/* Prepare the email */
			$email_template = get_email_template(
				[
					'{{DAYS_UNTIL_RENEWAL}}' => $days_until_renewal,
				],
				l('global.emails.user_plan_renewal_reminder.subject', $user->language),
				[
					'{{DAYS_UNTIL_RENEWAL}}' => $days_until_renewal,
					'{{PLAN_RENEWAL_DATE}}' => \Altum\Date::get($user->plan_expiration_date, 2),
					'{{USER_PLAN_LINK}}' => url('account-plan'),
					'{{NAME}}' => $user->name,
					'{{PLAN_NAME}}' => $plans[$user->plan_id]->name,
					'{{PAYMENT_PROCESSOR}}' => l('pay.custom_plan.' . $user->payment_processor, $user->language),
					'{{PAYMENT_TOTAL_AMOUNT}}' => nr($user->payment_total_amount, 2),
					'{{PAYMENT_CURRENCY}}' => $user->payment_currency,
				],
				l('global.emails.user_plan_renewal_reminder.body', $user->language)
			);

			send_mail($user->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]);

			/* Update user */
			db()->where('user_id', $user->user_id)->update('users', ['plan_renewal_reminder' => 1]);

			if(DEBUG) {
				echo sprintf('users_plan_renewal_reminder() -> Email sent for user_id %s', $user->user_id);
			}
		}

	}

	private function check_support() {
		if(ALTUMCODE != 66) return;
		if(!settings()->support->key) return;
		if(!isset(settings()->support->expiry_datetime)) return;
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

	public function email_reports() {

		$this->initiate();

		/* Only run this part if the email reports are enabled */
		if(!settings()->analytics->email_reports_is_enabled) {
			$this->close();
			$this->update_cron_execution_datetimes('email_reports_datetime');
			return;
		}

		$date = get_date();

		/* Determine the frequency of email reports */
		$days_interval = 7;

		switch(settings()->analytics->email_reports_is_enabled) {
			case 'weekly':
				$days_interval = 7;
				break;

			case 'monthly':
				$days_interval = 30;
				break;
		}

		/* Minimum datetime query */
		$minimum_datetime = (new \DateTime($date))->modify('-' . $days_interval . ' days')->format('Y-m-d H:i:s');

		/* Get potential websites from users that have almost all the conditions to get an email report right now */
		$result = database()->query("
            SELECT
                `websites`.`website_id`,
                `websites`.`name`,
                `websites`.`host`,
                `websites`.`path`,
                `websites`.`email_reports_last_date`,
                `websites`.`tracking_type`,
                `users`.`user_id`,
                `users`.`email`,
                `users`.`plan_settings`,
                `users`.`language`,
                `users`.`anti_phishing_code`
            FROM
                `websites`
            LEFT JOIN
                `users` ON `websites`.`user_id` = `users`.`user_id`
            WHERE
                `users`.`status` = 1
                AND `websites`.`is_enabled` = 1
                AND `websites`.`email_reports_is_enabled` = 1
				AND `websites`.`email_reports_last_date` <= '{$minimum_datetime}'
            LIMIT 25
        ");

		/* Go through each result */
		while($row = $result->fetch_object()) {
			$row->plan_settings = json_decode($row->plan_settings);

			/* Make sure the plan still lets the user get email reports */
			if(!$row->plan_settings->email_reports_is_enabled) {
				database()->query("UPDATE `websites` SET `email_reports_is_enabled` = 0 WHERE `website_id` = {$row->website_id}");
				continue;
			}

			/* Prepare */
			$previous_start_date = (new \DateTime())->modify('-' . $days_interval * 2 . ' days')->format('Y-m-d H:i:s');
			$start_date = (new \DateTime())->modify('-' . $days_interval . ' days')->format('Y-m-d H:i:s');

			/* Start getting information about the website to generate the statistics */
			switch($row->tracking_type) {
				case 'lightweight':
					$basic_analytics = database()->query("
                        SELECT
                            COUNT(*) AS `pageviews`,
                            COALESCE(SUM(CASE WHEN `type` = 'landing_page' THEN 1 ELSE 0 END), 0) AS `visitors`
                        FROM
                            `lightweight_events`
                        WHERE
                            `website_id` = {$row->website_id}
                            AND `date` >= '{$start_date}' AND `date` < '{$date}'
                    ")->fetch_object() ?? null;

					$previous_basic_analytics = database()->query("
                        SELECT
                            COUNT(*) AS `pageviews`,
                            COALESCE(SUM(CASE WHEN `type` = 'landing_page' THEN 1 ELSE 0 END), 0) AS `visitors`
                        FROM
                            `lightweight_events`
                        WHERE
                            `website_id` = {$row->website_id}
                            AND `date` >= '{$previous_start_date}' AND `date` < '{$start_date}'
                    ")->fetch_object() ?? null;
					break;

				case 'advanced':
					$basic_analytics = database()->query("
                        SELECT
                            COUNT(*) AS `pageviews`,
                            COUNT(DISTINCT `sessions_events`.`session_id`) AS `sessions`,
                            COUNT(DISTINCT `sessions_events`.`visitor_id`) AS `visitors`
                        FROM
                            `sessions_events`
                        LEFT JOIN
                            `websites_visitors` ON `sessions_events`.`visitor_id` = `websites_visitors`.`visitor_id`
                        WHERE
                            `sessions_events`.`website_id` = {$row->website_id}
                            AND `sessions_events`.`date` >= '{$start_date}' AND `sessions_events`.`date` < '{$date}'
                    ")->fetch_object() ?? null;

					$previous_basic_analytics = database()->query("
                        SELECT
                            COUNT(*) AS `pageviews`,
                            COUNT(DISTINCT `sessions_events`.`session_id`) AS `sessions`,
                            COUNT(DISTINCT `sessions_events`.`visitor_id`) AS `visitors`
                        FROM
                            `sessions_events`
                        LEFT JOIN
                            `websites_visitors` ON `sessions_events`.`visitor_id` = `websites_visitors`.`visitor_id`
                        WHERE
                            `sessions_events`.`website_id` = {$row->website_id}
                            AND `sessions_events`.`date` >= '{$previous_start_date}' AND `sessions_events`.`date` < '{$start_date}'
                    ")->fetch_object() ?? null;
					break;
			}

			/* Prepare the email title */
			$replacers = [
				'{{WEBSITE:NAME}}' => $row->name,
				'{{START_DATE}}' => \Altum\Date::get($start_date, 5),
				'{{END_DATE}}' => \Altum\Date::get('', 5),
			];

			$email_title = str_replace(
				array_keys($replacers),
				array_values($replacers),
				l('cron.email_reports.title', $row->language)
			);

			/* Prepare the View for the email content */
			$data = [
				'row'                       => $row,
				'basic_analytics'           => $basic_analytics,
				'previous_basic_analytics'  => $previous_basic_analytics,
				'previous_start_date'       => $previous_start_date,
				'start_date'                => $start_date,
				'date'                      => $date,
			];

			$email_content = (new \Altum\View('partials/cron/email_reports', (array) $this))->run($data);

			/* Send the email */
			send_mail($row->email, $email_title, $email_content, ['anti_phishing_code' => $row->anti_phishing_code, 'language' => $row->language]);

			/* Update the website */
			db()->where('website_id', $row->website_id)->update('websites', ['email_reports_last_date' => $date]);

			/* Insert email log */
			db()->insert('email_reports', [
				'user_id' => $row->user_id,
				'website_id' => $row->website_id,
				'datetime' => $date,
			]);

			if(DEBUG) {
				echo sprintf('Email sent for user_id %s and website_id %s', $row->user_id, $row->website_id);
			}
		}

		$this->close();

		/* mark cron execution */
		$this->update_cron_execution_datetimes('email_reports_datetime');
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
		$max_batch_size = isset(settings()->content->broadcasts_emails_per_cron) ? settings()->content->broadcasts_emails_per_cron : 40;

		/* Fetch a broadcast in "processing" status */
		$broadcast = db()->where('status', 'processing')->getOne('broadcasts');
		if(!$broadcast) {
			$this->close();
			$this->update_cron_execution_datetimes('broadcasts_datetime');
			return;
		}

		/* Prepare recipient processing data */
		$broadcast->users_ids = json_decode(isset($broadcast->users_ids) ? $broadcast->users_ids : '[]', true);
		$broadcast->users_ids = is_array($broadcast->users_ids) ? $broadcast->users_ids : [];
		$broadcast->sent_users_ids = json_decode(isset($broadcast->sent_users_ids) ? $broadcast->sent_users_ids : '[]', true);
		$broadcast->sent_users_ids = is_array($broadcast->sent_users_ids) ? $broadcast->sent_users_ids : [];
		$broadcast->settings = json_decode(isset($broadcast->settings) ? $broadcast->settings : '[]');
		$is_system_email = isset($broadcast->settings->is_system_email) ? (bool) $broadcast->settings->is_system_email : true;

		/* Find recipients left to process */
		$remaining_recipient_ids = array_values(array_diff($broadcast->users_ids, $broadcast->sent_users_ids));

		/* If no one is left, mark broadcast as "sent" and exit */
		if(empty($remaining_recipient_ids)) {

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

		/* Get all batch recipients at once */
		$recipient_ids_for_this_run = array_slice($remaining_recipient_ids, 0, $max_batch_size);

		if($is_system_email) {
			$recipients = db()
				->where('user_id', $recipient_ids_for_this_run, 'IN')
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

			$recipients_ids = array_column($recipients, 'user_id');
		}

		else {
			$recipients = db()
				->where('status', 1)
				->where('broadcast_subscriber_id', $recipient_ids_for_this_run, 'IN')
				->get('broadcast_subscribers', null, [
					'broadcast_subscriber_id',
					'user_id',
					'name',
					'email',
					'language',
					'continent_code',
					'country_code',
					'city_name',
					'device_type',
					'os_name',
					'browser_name',
					'browser_language',
					'unsubscribe_code'
				]);

			$recipients_ids = array_column($recipients, 'broadcast_subscriber_id');
		}

		/* Missing or inactive recipients */
		$missing_recipient_ids = array_diff($recipient_ids_for_this_run, $recipients_ids);

		/* Mark missing recipients as processed */
		if(count($missing_recipient_ids)) {
			$broadcast->sent_users_ids = array_merge($broadcast->sent_users_ids, $missing_recipient_ids);
		}

		/* Send emails only for existing recipients */
		if(!empty($recipients)) {

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

			/* Loop through recipients and send */
			foreach($recipients as $recipient) {

				$recipient_id = $is_system_email ? $recipient->user_id : $recipient->broadcast_subscriber_id;
				$recipient_country = $is_system_email ? $recipient->country : $recipient->country_code;

				/* Prepare placeholders and the final template */
				$vars = [
					'{{USER:NAME}}'             => $recipient->name,
					'{{USER:EMAIL}}'            => $recipient->email,
					'{{USER:CONTINENT_NAME}}'   => get_continent_from_continent_code($recipient->continent_code),
					'{{USER:COUNTRY_NAME}}'     => get_country_from_country_code($recipient_country),
					'{{USER:CITY_NAME}}'        => $recipient->city_name,
					'{{USER:DEVICE_TYPE}}'      => l('global.device.' . $recipient->device_type),
					'{{USER:OS_NAME}}'          => $recipient->os_name,
					'{{USER:BROWSER_NAME}}'     => $recipient->browser_name,
					'{{USER:BROWSER_LANGUAGE}}' => get_language_from_locale($recipient->browser_language),
				];

				$email_template = get_email_template(
					$vars,
					htmlspecialchars_decode($broadcast->subject),
					$vars,
					convert_editorjs_json_to_html($broadcast->content)
				);

				/* Tracking pixel & link rewriting */
				if(settings()->content->broadcasts_statistics_is_enabled) {
					$tracking_key = $is_system_email ? 'user_id' : 'broadcast_subscriber_id';
					$tracking_id = base64_encode('broadcast_id=' . $broadcast->broadcast_id . '&' . $tracking_key . '=' . $recipient_id);
					$email_template->body .= '<img src="' . SITE_URL . 'broadcast?id=' . rawurlencode($tracking_id) . '" style="display: none;" />';
					$email_template->body = preg_replace_callback('/<a\s+[^>]*href="([^"]+)"/i', function($matches) use ($tracking_id) {
						/* Keep links encoded */
						$url = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
						$tracking_url = SITE_URL . 'broadcast?id=' . rawurlencode($tracking_id) . '&url=' . rawurlencode($url);

						return str_replace($matches[1], $tracking_url, $matches[0]);
					}, $email_template->body);
				}

				/* Clear addresses from previous iteration */
				$mail->clearAddresses();

				/* Add new email address */
				$mail->addAddress($recipient->email);

				/* Unsubscribe token & setup */
				if($is_system_email) {
					$token_expires_in_days = 90;
					$secret = hash('sha256', settings()->license->license . '|' . settings()->cron->key . '|list-unsubscribe|v1', true);
					$token = generate_unsubscribe_token($recipient->user_id, 60 * 60 * 24 * $token_expires_in_days, $secret);
					$unsubscribe_url = SITE_URL . 'unsubscribe?token=' . rawurlencode($token);
				}

				else {
					/* Generate a permanent unsubscribe code */
					if(!$recipient->unsubscribe_code) {
						$recipient->unsubscribe_code = md5(uniqid('', true) . random_bytes(16));

						db()->where('broadcast_subscriber_id', $recipient->broadcast_subscriber_id)->update('broadcast_subscribers', [
							'unsubscribe_code' => $recipient->unsubscribe_code,
						]);
					}

					$unsubscribe_url = SITE_URL . 'unsubscribe?type=broadcast_subscriber&unsubscribe_code=' . rawurlencode($recipient->unsubscribe_code);
				}

				/* Add the mail headers for unsub */
				$mail->clearCustomHeaders();
				$mail->addCustomHeader('List-Unsubscribe', '<' . $unsubscribe_url . '>');
				$mail->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');

				/* Process the email title, template and body */
				extract(process_send_mail_template(
					$email_template->subject,
					$email_template->body,
					[
						'is_broadcast'       => true,
						'is_system_email'    => $is_system_email,
						'anti_phishing_code' => $is_system_email ? $recipient->anti_phishing_code : false,
						'language'           => $recipient->language,
						'unsubscribe_url'    => $unsubscribe_url,
					]
				));

				/* Set subject/body, then send */
				$mail->Subject = $title;
				$mail->Body = $email_template;
				$mail->AltBody = strip_tags($mail->Body);

				/* Send the email */
				$mail->send();

				/* Track sent recipient */
				$broadcast->sent_users_ids[] = $recipient_id;
			}

			/* Close this SMTP connection for the batch */
			$mail->smtpClose();
		}

		/* Prepare final counts */
		$broadcast->sent_users_ids = array_values(array_unique(array_map('intval', $broadcast->sent_users_ids)));
		$sent_emails_count = count($broadcast->sent_users_ids);

		/* Check if all recipients have been processed */
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
