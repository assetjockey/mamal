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

    public function index() {
		throw_404();
	}

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

		$this->users_plan_renewal_reminder();

        $this->check_support();

        $this->statistics_cleanup();

		$this->demo_auto_delete_links();

        /* Make sure the reset date month is different than the current one to avoid double resetting */
        $reset_date = settings()->cron->reset_date ? (new \DateTime(settings()->cron->reset_date))->format('m') : null;
        $current_date = (new \DateTime())->format('m');

        if($reset_date != $current_date) {
            /* Benchmark */
            $this->processing_time = microtime(true);

            $this->logs_cleanup();

            $this->users_logs_cleanup();

            $this->internal_notifications_cleanup();

            $this->users_qrcode_reset();

            $this->ai_qr_codes_temp_cleanup();

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
            $user->plan_settings = json_decode($user->plan_settings);

            if($user->plan_settings->statistics_retention == -1) continue;

            /* Clear out old notification statistics logs */
            $x_days_ago_datetime = (new \DateTime())->modify('-' . ($user->plan_settings->statistics_retention ?? 90) . ' days')->format('Y-m-d H:i:s');
            database()->query("DELETE FROM `statistics` WHERE `user_id` = {$user->user_id} AND `datetime` < '{$x_days_ago_datetime}'");

            if(DEBUG) {
                echo sprintf('statistics cleanup done for user_id %s', $user->user_id);
            }
        }

        /* Update users cleanup date */
        $next_cleanup_datetime = (new \DateTime())->modify('+1 days')->format('Y-m-d H:i:s');

        db()
            ->where('next_cleanup_datetime', $now_datetime, '<')
            ->where('status', 1)
            ->update('users', ['next_cleanup_datetime' => $next_cleanup_datetime]);

    }

	public function demo_auto_delete_links() {
		if(!defined('DEMO')) return;

		/* Delete links */
		$x_ago_datetime = (new \DateTime())->modify('-6 hours')->format('Y-m-d H:i:s');
		$links = db()->where('datetime', $x_ago_datetime, '<')->get('links');

		foreach($links as $link) {
			(new \Altum\Models\Link())->delete($link->link_id);
		}

		if(DEBUG) {
			echo sprintf('demo_auto_delete_links() -> links deletion cleanup done for the demo');
		}
	}

    public function email_reports() {

        $this->initiate();

        /* Only run when email reports are enabled */
        if(!settings()->links->email_reports_is_enabled) {
            $this->close();
            $this->update_cron_execution_datetimes('email_reports_datetime');
            return;
        }

        $date = get_date();

        /* Determine the email report frequency */
        $days_interval = 7;

        switch(settings()->links->email_reports_is_enabled) {
            case 'weekly':
                $days_interval = 7;
                break;

            case 'monthly':
                $days_interval = 30;
                break;
        }

        /* Minimum datetime query */
        $minimum_datetime = (new \DateTime($date))->modify('-' . $days_interval . ' days')->format('Y-m-d H:i:s');

        /* Get links ready for reports */
        $result = database()->query("
            SELECT
                `links`.`link_id`,
                `links`.`url`,
                `links`.`email_reports_last_datetime`,
                `users`.`user_id`,
                `users`.`email`,
                `users`.`plan_settings`,
                `users`.`language`,
                `users`.`anti_phishing_code`
            FROM
                `links`
            LEFT JOIN
                `users` ON `links`.`user_id` = `users`.`user_id`
            WHERE
                `users`.`status` = 1
                AND `links`.`is_enabled` = 1
                AND `links`.`email_reports_is_enabled` = 1
                AND `links`.`email_reports_last_datetime` <= '{$minimum_datetime}'
            LIMIT 25
        ");

        /* Go through each result */
        while($row = $result->fetch_object()) {
            $row->plan_settings = json_decode($row->plan_settings);

            /* Make sure the plan still allows email reports */
            if(!$row->plan_settings->email_reports_is_enabled) {
                db()->where('link_id', $row->link_id)->update('links', [
                    'email_reports_is_enabled' => 0,
                    'email_reports_last_datetime' => null,
                ]);
                continue;
            }

            /* Prepare dates */
            $previous_start_date = (new \DateTime($date))->modify('-' . ($days_interval * 2) . ' days')->format('Y-m-d H:i:s');
            $start_date = (new \DateTime($date))->modify('-' . $days_interval . ' days')->format('Y-m-d H:i:s');

            /* Get current statistics */
            $statistics_result = database()->query("
                SELECT
                     COUNT(`id`) AS `pageviews`,
                     SUM(`is_unique`) AS `visitors`
                FROM
                     `statistics`
                WHERE
                    `link_id` = {$row->link_id}
                    AND `datetime` >= '{$start_date}' AND `datetime` < '{$date}'
            ")->fetch_object();

            $statistics = [
                'pageviews' => (int) $statistics_result->pageviews,
                'visitors' => (int) $statistics_result->visitors,
            ];

            /* Get previous statistics */
            $previous_statistics_result = database()->query("
                SELECT
                     COUNT(`id`) AS `pageviews`,
                     SUM(`is_unique`) AS `visitors`
                FROM
                     `statistics`
                WHERE
                    `link_id` = {$row->link_id}
                    AND `datetime` >= '{$previous_start_date}' AND `datetime` < '{$start_date}'
            ")->fetch_object();

            $previous_statistics = [
                'pageviews' => (int) $previous_statistics_result->pageviews,
                'visitors' => (int) $previous_statistics_result->visitors,
            ];

            /* Prepare the email title */
            $replacers = [
                '{{LINK:URL}}' => $row->url,
                '{{START_DATE}}' => \Altum\Date::get($start_date, 5),
                '{{END_DATE}}' => \Altum\Date::get($date, 5),
            ];

            $email_title = str_replace(
                array_keys($replacers),
                array_values($replacers),
                l('cron.email_reports.title', $row->language)
            );

            /* Prepare the email content */
            $data = [
                'row'                       => $row,
                'statistics'                => $statistics,
                'previous_statistics'       => $previous_statistics,
                'previous_start_date'       => $previous_start_date,
                'start_date'                => $start_date,
                'date'                      => $date,
            ];

            $email_content = (new \Altum\View('partials/cron/email_reports', (array) $this))->run($data);

            /* Send the email */
            send_mail($row->email, $email_title, $email_content, ['anti_phishing_code' => $row->anti_phishing_code, 'language' => $row->language]);

            /* Update the link */
            db()->where('link_id', $row->link_id)->update('links', ['email_reports_last_datetime' => $date]);

            /* Insert email log */
            db()->insert('email_reports', [
                'user_id' => $row->user_id,
                'link_id' => $row->link_id,
                'datetime' => $date,
            ]);

            if(DEBUG) {
                echo sprintf('email_reports() - Email sent for user_id %s and link_id %s', $row->user_id, $row->link_id);
            }
        }

        $this->close();

        $this->update_cron_execution_datetimes('email_reports_datetime');
    }

    private function users_qrcode_reset() {
        db()->update('users', [
            'qrcode_ai_qr_codes_current_month' => 0,
        ]);

        cache()->clear();
    }

    private function ai_qr_codes_temp_cleanup() {
        /* Clear files caches */
        clearstatcache();

        $current_month = (new \DateTime())->format('m');

        $deleted_count = 0;

        /* Get the data */
        foreach(glob(\Altum\Uploads::get_full_path('ai_qr_codes/temp') . '*.png') as $file_path) {
            $file_last_modified = filemtime($file_path);

            if((new \DateTime())->setTimestamp($file_last_modified)->format('m') != $current_month) {
                unlink($file_path);
                $deleted_count++;
            }
        }

        if(DEBUG) {
            echo sprintf('ai_qr_codes_temp_cleanup: Deleted %s ai temp files.', $deleted_count);
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
