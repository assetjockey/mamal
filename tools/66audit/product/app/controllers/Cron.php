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

        $this->audits_cleanup();

        /* Make sure the reset date month is different than the current one to avoid double resetting */
        $reset_date = settings()->cron->reset_date ? (new \DateTime(settings()->cron->reset_date))->format('m') : null;
        $current_date = (new \DateTime())->format('m');

        if($reset_date != $current_date) {
            /* Benchmark */
            $this->processing_time = microtime(true);

            $this->logs_cleanup();

            $this->users_logs_cleanup();

            $this->internal_notifications_cleanup();

            $this->users_audit_reset();

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

    private function audits_cleanup() {
        $current_date = (new \DateTime())->format('Y-m-d H:i:s');

        /* Delete the expired audits */
        db()->where('expiration_datetime', $current_date, '<')->delete('audits');

        /* Delete the expired old audits */
        db()->where('expiration_datetime', $current_date, '<')->delete('archived_audits');

        if(DEBUG) {
            echo 'audits cleanup done';
            echo 'archived_audits cleanup done';
        }
    }

    private function users_audit_reset() {
        db()->update('users', [
            'audit_audits_current_month' => 0,
        ]);

        cache()->clear();
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

    public function audits() {

        $this->initiate();

        $date = get_date();

        /* Determine how many checks to do */
        $query_limit = settings()->audits->audits_per_cron ?? 30;

        /* Cache notification handlers */
        $cached_notification_handlers = [];

        $result = database()->query("
            SELECT
                `audits`.*,
                `users`.`email`,
                `users`.`plan_settings`,
                `users`.`language`,
                `users`.`timezone`,
                `users`.`anti_phishing_code`
            FROM 
                `audits`
            LEFT JOIN 
                `users` ON `audits`.`user_id` = `users`.`user_id` 
            WHERE 
                `audits`.`next_refresh_datetime` IS NOT NULL
                AND `audits`.`next_refresh_datetime` <= '{$date}' 
                AND `users`.`status` = 1
            ORDER BY `audits`.`next_refresh_datetime`
            LIMIT {$query_limit}
        ");

        while($row = $result->fetch_object()) {
            $row->plan_settings = json_decode($row->plan_settings);
            $row->settings = json_decode($row->settings ?? '');
            $row->notifications = json_decode($row->notifications ?? '');

            /* Next refresh date */
            $next_refresh_datetime = $row->settings->audit_check_interval ? (new \DateTime())->modify('+' . $row->settings->audit_check_interval . ' seconds')->format('Y-m-d H:i:s') : null;

            $notification_handlers = [];

            /* Get available notification handlers */
            if(count($row->notifications)) {
                /* Get available notification handlers */
                if(isset($cached_notification_handlers[$row->user_id])) {
                    $notification_handlers = $cached_notification_handlers[$row->user_id];
                } else {
                    $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($row->user_id);
                    $cached_notification_handlers[$row->user_id] = $notification_handlers;
                }
            }

            if(DEBUG) printf("Starting to refresh %s audit...\n", $row->url);

            $refresh_error = false;

            /* Send the main request */
            try {
                $response = \Altum\Helpers\Audit::process_request($row->url);
            } catch(\Exception $exception) {
                error_log($exception->getMessage());
                $refresh_error = $exception->getMessage();
            }

            if($refresh_error && settings()->audits->double_check_is_enabled) {
                sleep(settings()->audits->double_check_wait ?? 3);

                /* Send the second request */
                try {
                    $response = \Altum\Helpers\Audit::process_request($row->url);
                } catch(\Exception $exception) {
                    error_log($exception->getMessage());
                    $refresh_error = $exception->getMessage();
                }
            }

            /* Set the error and skip */
            if($refresh_error) {
                /* Update the main audit */
                db()->where('audit_id', $row->audit_id)->update('audits', [
                    'next_refresh_datetime' => $next_refresh_datetime,
                    'refresh_error' => $refresh_error,
                    'is_queued' => 0,
                ]);

                /* Get user notified */
                /* Processing the notification handlers */
                if(!empty($notification_handlers)) {
                    /* Core data to be sent to the processor */
                    $notification_data = [
                        'website_id' => $row->website_id,
                        'audit_id' => $row->audit_id,
                        'url' => $row->url,
                        'refresh_error' => $refresh_error,
                        'total_refreshes' => $row->total_refreshes + 1,
                        'next_refresh_datetime' => $next_refresh_datetime,
                        'url_view' => url('audit/' . $row->audit_id),
                    ];

                    /* Build a plain caught-data string for the generic message */
                    $dynamic_message_data = \Altum\NotificationHandlers::build_dynamic_message_data($notification_data);

                    /* Compose the generic notification text */
                    $notification_message = sprintf(
                        l('audits.error_notification', $row->language),
                        remove_url_protocol_from_url($row->url),
                        $dynamic_message_data,
                        url('audit/' . $row->audit_id)
                    );

                    /* Prepare the email template used by the email handler */
                    $email_subject = sprintf(l('audits.email_report_error.subject', $row->language), $row->host);
                    $email_body = (new \Altum\View('audits/audit_email_report_error', (array) $this))->run([
                        'row' => $row,
                        'refresh_error' => $refresh_error,
                    ]);

                    $email_template = get_email_template(
                        /* Subject placeholders */
                        [
                            '{{WEBSITE_HOST}}' => $row->host,
                        ],
                        $email_subject,
                        [],
                        $email_body
                    );

                    /* Build the context passed to the NotificationHandlers class */
                    $context = [
                        /* User details */
                        'user' => $row,

                        /* Email */
                        'email_template' => $email_template,

                        /* Basic message for most integrations */
                        'message' => $notification_message,

                        /* Push notifications */
                        'push_title' => l('audits.error_push_notification.title', $row->language),
                        'push_description' => sprintf(l('audits.error_push_notification.description', $row->language), remove_url_protocol_from_url($row->url)),

                        /* WhatsApp */
                        'whatsapp_template' => 'audit_refresh_error',
                        'whatsapp_parameters' => [
                            remove_url_protocol_from_url($row->url),
                            url('audit/' . $row->audit_id),
                        ],

                        /* Twilio call */
                        'twilio_call_url' => SITE_URL .
                            'twiml/audits.error_notification?param1=' . urlencode(remove_url_protocol_from_url($row->url)) .
                            '&param2=' . urlencode($refresh_error) .
                            '&param3=' . urlencode(url('audit/' . $row->audit_id)),

                        /* Internal notification */
                        'internal_icon' => 'fas fa-exclamation-circle',

                        /* Discord */
                        'discord_color' => '14431557',

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

                continue;
            }

            /* Single URL processing */
            $data = \Altum\Helpers\Audit::process_request_response($row->url, $response);

            if(!$data['is_external_redirected']) {
                $data_not_found = \Altum\Helpers\Audit::process_not_found($data['parsed_url']);
                $data_robots = \Altum\Helpers\Audit::process_robots($data['parsed_url']);
            }

            /* Merge data */
            $data = $data + ($data_not_found ?? []) + ($data_robots ?? []);

            /* Clean data */
            $data = convert_array_to_utf8($data);

            /* Prepare full data to be inserted */
            $data_json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            /* Make sure it is not a new & queued audit */
            if($row->total_tests) {
                /* Insert a log of the current update as old */
                db()->insert('archived_audits', [
                    'audit_id' => $row->audit_id,
                    'user_id' => $row->user_id,
                    'domain_id' => $row->domain_id,
                    'uploader_id' => $row->uploader_id,
                    'website_id' => $row->website_id,
                    'host' => $row->host,
                    'url' => $row->url,
                    'resolved_url' => $row->resolved_url,
                    'ttfb' => $row->ttfb,
                    'response_time' => $row->response_time,
                    'average_download_speed' => $row->average_download_speed,
                    'page_size' => $row->page_size,
                    'http_requests' => $row->http_requests,
                    'is_https' => $row->is_https,
                    'is_ssl_valid' => $row->is_ssl_valid,
                    'http_protocol' => $row->http_protocol,
                    'title' => $row->title,
                    'meta_description' => $row->meta_description,
                    'meta_keywords' => $row->meta_keywords,
                    'data' => $row->data,
                    'issues' => $row->issues,
                    'score' => $row->score,
                    'total_tests' => $row->total_tests,
                    'passed_tests' => $row->passed_tests,
                    'total_issues' => $row->total_issues,
                    'major_issues' => $row->major_issues,
                    'moderate_issues' => $row->moderate_issues,
                    'minor_issues' => $row->minor_issues,
                    'refresh_error' => $row->refresh_error,
                    'is_external_redirected' => $row->is_external_redirected,
                    'total_redirects' => $row->total_redirects,
                    'expiration_datetime' => $row->expiration_datetime,
                    'datetime' => $row->last_refresh_datetime ?: $row->datetime,
                ]);

                /* Database query */
                db()->where('user_id', $row->user_id)->update('users', [
                    'audit_audits_current_month' => db()->inc()
                ]);
            }

            /* Prepare expiration date */
            $expiration_datetime = (new \DateTime())->modify('+' . ($row->plan_settings->audits_retention ?? 90) . ' days')->format('Y-m-d H:i:s');

            /* Check if we should continue the checking process */
            if($data['is_external_redirected']) {
                db()->where('audit_id', $row->audit_id)->update('audits', [
                    'host' => $data['parsed_url']['host'],
                    'resolved_url' => $data['resolved_url'],
                    'total_refreshes' => db()->inc(),
                    'is_queued' => 0,
                    'refresh_error' => '',
                    'is_external_redirected' => $data['is_external_redirected'],
                    'total_redirects' => $data['total_redirects'],
                    'next_refresh_datetime' => $next_refresh_datetime,
                    'last_refresh_datetime' => get_date(),
                    'expiration_datetime' => $expiration_datetime,
                ]);

                /* Processing the notification handlers */
                if(!empty($notification_handlers)) {
                    /* Core data to be sent to the processor */
                    $notification_data = [
                        'website_id' => $row->website_id,
                        'audit_id' => $row->audit_id,
                        'url' => $row->url,
                        'resolved_url' => $data['resolved_url'],
                        'total_redirects' => $data['total_redirects'],
                        'total_refreshes' => $row->total_refreshes + 1,
                        'next_refresh_datetime' => $next_refresh_datetime,
                        'url_view' => url('audit/' . $row->audit_id),
                    ];

                    /* Build a plain caught-data string for the generic message */
                    $dynamic_message_data = \Altum\NotificationHandlers::build_dynamic_message_data($notification_data);

                    /* Compose the generic notification text */
                    $notification_message = sprintf(
                        l('audits.external_redirect_notification', $row->language),
                        remove_url_protocol_from_url($row->url),
                        remove_url_protocol_from_url($data['resolved_url']),
                        $dynamic_message_data,
                        url('audit/' . $row->audit_id)
                    );

                    /* Prepare the email template used by the email handler */
                    $email_subject = sprintf(l('audits.email_report_external_redirection.subject', $row->language), $row->host);
                    $email_body = (new \Altum\View('audits/audit_email_report_external_redirection', (array) $this))->run([
                        'row' => $row,
                        'data' => $data,
                    ]);

                    $email_template = get_email_template(
                        /* Subject placeholders */
                        [
                            '{{WEBSITE_HOST}}' => $row->host,
                        ],
                        $email_subject,
                        [],
                        $email_body
                    );

                    /* Build the context passed to the NotificationHandlers class */
                    $context = [
                        /* User details */
                        'user' => $row,

                        /* Email */
                        'email_template' => $email_template,

                        /* Basic message for most integrations */
                        'message' => $notification_message,

                        /* Push notifications */
                        'push_title' => l('audits.external_redirect_push_notification.title', $row->language),
                        'push_description' => sprintf(l('audits.external_redirect_push_notification.description', $row->language), remove_url_protocol_from_url($row->url)),

                        /* WhatsApp */
                        'whatsapp_template' => 'audit_refresh_external_redirection',
                        'whatsapp_parameters' => [
                            remove_url_protocol_from_url($row->url),
                            remove_url_protocol_from_url($data['resolved_url']),
                            url('audit/' . $row->audit_id),
                        ],

                        /* Twilio call */
                        'twilio_call_url' => SITE_URL .
                            'twiml/audits.external_redirect_notification?param1=' . urlencode(remove_url_protocol_from_url($row->url)) .
                            '&param2=' . urlencode(remove_url_protocol_from_url($data['resolved_url'])) .
                            '&param3=' . urlencode(url('audit/' . $row->audit_id)),

                        /* Internal notification */
                        'internal_icon' => 'fas fa-exclamation-circle',

                        /* Discord */
                        'discord_color' => '14431557',

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
            }

            /* Normal process */
            else {
                /* Process data */
                $audit_data = \Altum\Helpers\Audit::process_audit_data($data);
                $issues = [
                    'major' => $audit_data['major_issues'],
                    'moderate' => $audit_data['moderate_issues'],
                    'minor' => $audit_data['minor_issues'],
                    'potential_major_issues' => $audit_data['potential_major_issues'],
                    'potential_moderate_issues' => $audit_data['potential_moderate_issues'],
                    'potential_minor_issues' => $audit_data['potential_minor_issues'],
                    'total_tests' => $audit_data['total_tests'],
                    'passed_tests' => $audit_data['passed_tests'],
                ];

                /* Score */
                $score = $audit_data['score'];

                /* Processing the notification handlers */
                if(!$row->is_queued && !empty($notification_handlers)) {
                    /* Core data to be sent to the processor */
                    $notification_data = [
                        'website_id' => $row->website_id,
                        'audit_id' => $row->audit_id,
                        'url' => $row->url,
                        'score' => $score,
                        'response_time' => $data['response_time'],
                        'total_tests' => $audit_data['total_tests'],
                        'passed_tests' => $audit_data['passed_tests'],
                        'total_issues' => $audit_data['total_issues'],
                        'major_issues' => $audit_data['found_major_issues'],
                        'moderate_issues' => $audit_data['found_moderate_issues'],
                        'minor_issues' => $audit_data['found_minor_issues'],
                        'total_refreshes' => $row->total_refreshes + 1,
                        'next_refresh_datetime' => $next_refresh_datetime,
                        'url_view' => url('audit/' . $row->audit_id),
                    ];

                    /* Build a plain caught-data string for the generic message */
                    $dynamic_message_data = \Altum\NotificationHandlers::build_dynamic_message_data($notification_data);

                    /* Compose the generic notification text */
                    $notification_message = sprintf(
                        l('audits.simple_notification', $row->language),
                        remove_url_protocol_from_url($row->url),
                        $score,
                        $audit_data['total_issues'],
                        $dynamic_message_data,
                        url('audit/' . $row->audit_id)
                    );

                    /* Prepare the email template used by the email handler */
                    $email_subject = sprintf(l('audits.email_report.subject', $row->language), $row->host, $score);
                    $email_body = (new \Altum\View('audits/audit_email_report', (array)$this))->run([
                        'row' => $row,
                        'issues' => $issues,
                        'audit' => $audit_data,
                        'data' => $data,
                    ]);

                    $email_template = get_email_template(
                        /* Subject placeholders */
                        [
                            '{{WEBSITE_HOST}}' => $row->host,
                            '{{AUDIT_SCORE}}' => $score,
                        ],
                        $email_subject,
                        [],
                        $email_body
                    );

                    /* Build the context passed to the NotificationHandlers class */
                    $context = [
                        /* User details */
                        'user' => $row,

                        /* Email */
                        'email_template' => $email_template,

                        /* Basic message for most integrations */
                        'message' => $notification_message,

                        /* Push notifications */
                        'push_title' => sprintf(l('audits.push_notification.title', $row->language), $score),
                        'push_description' => sprintf(l('audits.push_notification.description', $row->language), remove_url_protocol_from_url($row->url)),

                        /* WhatsApp */
                        'whatsapp_template' => 'audit_refresh',
                        'whatsapp_parameters' => [
                            remove_url_protocol_from_url($row->url),
                            $score,
                            $audit_data['total_issues'],
                            url('audit/' . $row->audit_id),
                        ],

                        /* Twilio call */
                        'twilio_call_url' => SITE_URL .
                            'twiml/audits.simple_notification?param1=' . urlencode(remove_url_protocol_from_url($row->url)) .
                            '&param2=' . $score .
                            '&param3=' . $audit_data['total_issues'] .
                            '&param4=' . urlencode(url('audit/' . $row->audit_id)),

                        /* Internal notification */
                        'internal_icon' => 'fas fa-bolt',

                        /* Discord */
                        'discord_color' => '3066993',

                        /* Slack */
                        'slack_emoji' => ':large_green_square:',
                    ];

                    /* Send notifications */
                    \Altum\NotificationHandlers::process(
                        $notification_handlers,
                        $row->notifications,
                        $notification_data,
                        $context
                    );
                }

                if(settings()->audits->ai_is_enabled && settings()->audits->openai_api_key) {
                    $in_parallel = (bool) settings()->audits->openai_webhook_secret_key;

                    $ai_summary = null;
                    $ai_summary_id = null;

                    $ai_result = \Altum\Helpers\Audit::process_ai_summary($data, $issues, $in_parallel);

                    if($in_parallel) {
                        $ai_summary_id = $ai_result;
                    } else {
                        $ai_summary = $ai_result;
                    }
                }

                /* Update the main audit */
                db()->where('audit_id', $row->audit_id)->update('audits', [
                    'host' => $data['parsed_url']['host'],
                    'url' => $data['url'],
                    'resolved_url' => $data['resolved_url'],
                    'ttfb' => $data['ttfb'],
                    'response_time' => $data['response_time'],
                    'average_download_speed' => $data['average_download_speed'],
                    'page_size' => $data['page_size'],
                    'http_requests' => $data['http_requests'],
                    'is_https' => $data['is_https'],
                    'is_ssl_valid' => $data['is_ssl_valid'],
                    'http_protocol' => $data['http_protocol'],
                    'title' => $data['title'],
                    'meta_description' => $data['meta_description'],
                    'meta_keywords' => $data['meta_keywords'],
                    'data' => $data_json,
                    'ai_summary' => $ai_summary ?? null,
                    'ai_summary_id' => $ai_summary_id ?? null,
                    'issues' => json_encode($issues),
                    'score' => $score,
                    'total_tests' => $audit_data['total_tests'],
                    'passed_tests' => $audit_data['passed_tests'],
                    'total_issues' => $audit_data['total_issues'],
                    'major_issues' => $audit_data['found_major_issues'],
                    'moderate_issues' => $audit_data['found_moderate_issues'],
                    'minor_issues' => $audit_data['found_minor_issues'],
                    'total_refreshes' => db()->inc(),
                    'total_redirects' => $data['total_redirects'],
                    'refresh_error' => '',
                    'is_external_redirected' => $data['is_external_redirected'],
                    'is_queued' => 0,
                    'next_refresh_datetime' => $next_refresh_datetime,
                    'last_refresh_datetime' => get_date(),
                    'expiration_datetime' => $expiration_datetime,
                ]);
            }

            (new \Altum\Models\Website())->refresh_stats($row->website_id);

            /* Clear the cache */
            cache()->deleteItem('audits_total?user_id=' . $row->user_id);
            cache()->deleteItem('audits_dashboard?user_id=' . $row->user_id);
        }

        $this->close();

        /* mark cron execution */
        $this->update_cron_execution_datetimes('audits_datetime');
    }

}
