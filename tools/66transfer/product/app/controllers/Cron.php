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
use Altum\Uploads;

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
            database()->query("DELETE FROM `downloads` WHERE `user_id` = {$user->user_id} AND `datetime` < '{$x_days_ago_datetime}'");

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

    public function transfers_cleanup() {

        $this->initiate();

        /* Get file uploads that haven't been fully uploaded or not associated with any transfer */
        $past_date = (new \DateTime())->modify('-12 hours')->format('Y-m-d H:i:s');

        $result = database()->query("
            SELECT `file_id`, `name`, `offload_id`
            FROM `files` 
            WHERE 
                `transfer_id` IS NULL
                AND `transfer_request_id` IS NULL
                AND `datetime` < '{$past_date}'
        ");

        /* Go through each result */
        while($file = $result->fetch_object()) {

            /* Delete the stored file */
            Uploads::delete_uploaded_file_and_potential_residue($file->name, 'files', $file->offload_id);

            /* Delete the resource */
            db()->where('file_id', $file->file_id)->delete('files');

        }

        /* Delete the expired transfers */
        $current_date = (new \DateTime())->format('Y-m-d H:i:s');

        $result = database()->query("
            SELECT `transfer_id` 
            FROM `transfers` 
            WHERE 
                `expiration_datetime` < '{$current_date}'
                OR `downloads` >= `downloads_limit`
            LIMIT 100
        ");

        /* Go through each result */
        while($transfer = $result->fetch_object()) {
            (new \Altum\Models\Transfers())->delete($transfer->transfer_id);
        }

        $this->close();

        /* mark cron execution */
        $this->update_cron_execution_datetimes('transfers_cleanup_datetime');
    }

    public function transfer_requests_cleanup() {

        $this->initiate();

        /* Delete the expired transfer requests */
        $current_date = (new \DateTime())->format('Y-m-d H:i:s');

        $result = database()->query("
            SELECT `transfer_request_id` 
            FROM `transfer_requests` 
            WHERE 
                `expiration_datetime` < '{$current_date}'
            LIMIT 100
        ");

        /* Go through each result */
        while($transfer_request = $result->fetch_object()) {
            (new \Altum\Models\TransferRequests())->delete($transfer_request->transfer_request_id);
        }

        $this->close();

        /* mark cron execution */
        $this->update_cron_execution_datetimes('transfer_requests_cleanup_datetime');
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

                /* Add new email address */
                $mail->addAddress($user->email);

                /* Unsubscribe token & setup */
                $secret = hash('sha256', settings()->license->license . '|' . settings()->cron->key . '|list-unsubscribe|v1', true);
                $token_expires_in_days = 90;
                $token = generate_unsubscribe_token($user->user_id, 60 * 60 * 24 * $token_expires_in_days, $secret);
                $unsubscribe_url = SITE_URL . 'unsubscribe?token=' . rawurlencode($token);

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
