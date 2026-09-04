<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
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

namespace Altum;

defined('ALTUMCODE') || die();

class CustomHooks {

    public static function return_default_user_preferences() {
        return [
            'white_label_title' => '',
            'white_label_footer_description' => '',
            'white_label_remove_socials' => false,
            'white_label_remove_footer_links' => false,
            'white_label_logo_light' => '',
            'white_label_logo_dark' => '',
            'white_label_favicon' => '',
            'default_results_per_page' => settings()->main->default_results_per_page,
            'default_order_type' => settings()->main->default_order_type,
            'links_default_order_by' => 'link_id',
            'qr_codes_default_order_by' => 'qr_code_id',
            'projects_default_order_by' => 'project_id',
            'pixels_default_order_by' => 'pixel_id',
            'domains_default_order_by' => 'domain_id',
            'signatures_default_order_by' => 'signature_id',
            'digital_wallets_default_order_by' => 'digital_wallet_id',
            'splash_pages_default_order_by' => 'splash_page_id',
            'data_default_order_by' => 'datum_id',
            'newsletters_default_order_by' => 'newsletter_id',
            'newsletter_subscribers_default_order_by' => 'newsletter_subscriber_id',
            'payment_processors_default_order_by' => 'payment_processor_id',
            'guests_payments_default_order_by' => 'guest_payment_id',
            'images_default_order_by' => 'image_id',
            'transcriptions_default_order_by' => 'transcription_id',
            'syntheses_default_order_by' => 'synthesis_id',
            'chats_default_order_by' => 'chat_id',
            'openai_api_key' => '',
            'links_auto_copy_link' => false,
            'links_autosave_settings' => false,
            'newsletters_smtp_is_enabled' => false,
            'newsletters_smtp_host' => '',
            'newsletters_smtp_port' => '',
            'newsletters_smtp_encryption' => 'tls',
            'newsletters_smtp_auth' => false,
            'newsletters_smtp_username' => '',
            'newsletters_smtp_password' => '',
            'newsletters_smtp_from' => '',
            'newsletters_smtp_from_name' => '',
            'newsletters_smtp_reply_to' => '',
            'newsletters_smtp_reply_to_name' => '',
            'excluded_ips' => [],
            'claim_url' => '',
            'domain_id' => null,
        ];
    }

    public static function user_initiate_registration($data = []) {

        /* Update the account preference if needed */
        if(isset($_GET['claim-url']) && settings()->links->claim_url_is_enabled) {
            $claim_url = get_slug($_GET['claim-url'], '-', false);
            $domain_id = isset($_GET['domain-id']) ? (int) $_GET['domain-id'] : null;

            session_set('claim_url', $claim_url);
            if($domain_id) session_set('domain_id', $domain_id);
        }

    }

    public static function user_finished_registration($data = []) {

        /* Update the account preference if needed */
        if(isset($_GET['claim-url']) || session_has('claim_url') && settings()->links->claim_url_is_enabled) {
            $claim_url = isset($_GET['claim-url']) ? get_slug($_GET['claim-url'], '-', false) : get_slug(session_get('claim_url'), '-', false);
            $domain_id = isset($_GET['domain-id']) ? (int) $_GET['domain-id'] : (session_has('domain_id') ? (int) session_get('domain_id') : null);

            if($domain_id) {
                db()->rawQuery("UPDATE `users` SET `preferences` = JSON_SET(`preferences`, '$.claim_url', ?, '$.domain_id', ?) WHERE `user_id` = ?", [$claim_url, $domain_id, $data['user_id']]);
            } else {
                db()->rawQuery("UPDATE `users` SET `preferences` = JSON_SET(`preferences`, '$.claim_url', ?) WHERE `user_id` = ?", [$claim_url, $data['user_id']]);
            }
        }

    }

    public static function user_delete($data = []) {

        /* Delete the potentially uploaded user avatar */
        if($data['user']->avatar) {
            Uploads::delete_uploaded_file($data['user']->avatar, 'users');
        }

        /* Delete the potentially uploaded files on preference settings */
        if($data['user']->preferences->white_label_logo_light) {
            Uploads::delete_uploaded_file($data['user']->preferences->white_label_logo_light, 'users');
        }

        if($data['user']->preferences->white_label_logo_dark) {
            Uploads::delete_uploaded_file($data['user']->preferences->white_label_logo_dark, 'users');
        }

        if($data['user']->preferences->white_label_favicon) {
            Uploads::delete_uploaded_file($data['user']->preferences->white_label_favicon, 'users');
        }

        $user_id = $data['user']->user_id;

        /* Delete the potentially uploaded guest payment proofs of the user */
        if(\Altum\Plugin::is_installed('payment-blocks')) {
            $result = database()->query("SELECT `data` FROM `guests_payments` WHERE `user_id` = {$user_id}");

            while($guest_payment = $result->fetch_object()) {
                $guest_payment->data = json_decode($guest_payment->data ?? '');
                Uploads::delete_uploaded_file($guest_payment->data->payment_proof ?? null, 'payment_processors_offline_payment_proofs');
            }
        }

        /* Get all resources with uploads of the user */
        $links_ids = db()->where('user_id', $data['user']->user_id)->getValue('links', 'link_id', null);

        /* Delete all process to make sure everything is removed */
        if($links_ids) {
            (new \Altum\Models\Link())->bulk_delete($links_ids, $data['user']->user_id);
        }

        /* Get all resources with uploads of the user */
        $qr_codes_ids = db()->where('user_id', $data['user']->user_id)->getValue('qr_codes', 'qr_code_id', null);

        /* Delete all process to make sure everything is removed */
        if($qr_codes_ids) {
            (new \Altum\Models\QrCode())->bulk_delete($qr_codes_ids, $data['user']->user_id);
        }

        /* Get all resources with uploads of the user */
        $splash_pages_ids = db()->where('user_id', $data['user']->user_id)->getValue('splash_pages', 'splash_page_id', null);

        /* Delete all process to make sure everything is removed */
        if($splash_pages_ids) {
            (new \Altum\Models\SplashPages())->bulk_delete($splash_pages_ids, $data['user']->user_id);
        }

        /* Delete everything related to the digital wallets that the user owns */
        if(\Altum\Plugin::is_installed('digital-wallets')) {
            /* Get all resources with uploads of the user */
            $digital_wallets_ids = db()->where('user_id', $data['user']->user_id)->getValue('digital_wallets', 'digital_wallet_id', null);

            /* Delete all process to make sure everything is removed */
            if($digital_wallets_ids) {
                (new \Altum\Models\DigitalWallets())->bulk_delete($digital_wallets_ids, $data['user']->user_id);
            }
        }

        if(\Altum\Plugin::is_installed('aix')) {
            /* Delete everything related to the images that the user owns */
            $result = database()->query("SELECT `image_id`, `image` FROM `images` WHERE `user_id` = {$user_id}");

            while($image = $result->fetch_object()) {
                \Altum\Uploads::delete_uploaded_file($image->image, 'images');

                /* Delete the resource */
                db()->where('image_id', $image->image_id)->delete('images');
            }

            /* Delete everything related to the syntheses that the user owns */
            $result = database()->query("SELECT `synthesis_id`, `file` FROM `syntheses` WHERE `user_id` = {$user_id}");

            while($synthesis = $result->fetch_object()) {
                \Altum\Uploads::delete_uploaded_file($synthesis->file, 'syntheses');

                /* Delete the resource */
                db()->where('synthesis_id', $synthesis->synthesis_id)->delete('syntheses');
            }

            /* Delete everything related to the chat images that the user owns */
            $chats_ids = db()->where('user_id', $user_id)->getValue('chats', 'chat_id', null);

            if($chats_ids) {
                foreach($chats_ids as $chat_id) {
                    (new \Altum\Models\Chats())->delete($chat_id);
                }
            }
        }

    }

    public static function user_payment_finished($data = []) {
        extract($data);

        if(\Altum\Plugin::is_active('aix')) {
            db()->where('user_id', $user->user_id)->update('users', [
                'aix_images_current_month' => 0,
                'aix_transcriptions_current_month' => 0,
                'aix_chats_current_month' => 0,
                'aix_syntheses_current_month' => 0,
                'aix_synthesized_characters_current_month' => 0,
            ]);
        }

        if(settings()->links->static_ai_is_enabled) {
            db()->where('user_id', $user->user_id)->update('users', [
                'ai_static_prompts_current_month' => 0,
            ]);
        }
    }

    public static function generate_language_prefixes_to_skip($data = []) {

        $prefixes = [];

        /* Base features */
        if(!empty(settings()->main->index_url)) {
            $prefixes = array_merge($prefixes, ['index.']);
        }

        if(!settings()->email_notifications->contact) {
            $prefixes = array_merge($prefixes, ['contact.']);
        }

        if(!settings()->main->api_is_enabled) {
            $prefixes = array_merge($prefixes, ['api.', 'api_documentation.', 'account_api.', 'api_key_regenerate_modal.']);
        }

        if(!settings()->internal_notifications->admins_is_enabled) {
            $prefixes = array_merge($prefixes, ['global.notifications.']);
        }

        if(!settings()->internal_notifications->users_is_enabled) {
            $prefixes = array_merge($prefixes, ['internal_notifications.']);
        }

        if(!settings()->cookie_consent->is_enabled) {
            $prefixes = array_merge($prefixes, ['global.cookie_consent.']);
        }

        if(!settings()->ads->ad_blocker_detector_is_enabled){
            $prefixes = array_merge($prefixes, ['ad_blocker_detector_modal.']);
        }

        if(!settings()->content->blog_is_enabled) {
            $prefixes = array_merge($prefixes, ['blog.']);
        }

        if(!settings()->content->pages_is_enabled) {
            $prefixes = array_merge($prefixes, ['page.', 'pages.']);
        }

        if(!settings()->main->maintenance_is_enabled) {
            $prefixes = array_merge($prefixes, ['maintenance.']);
        }

        if(!settings()->users->register_is_enabled) {
            $prefixes = array_merge($prefixes, ['register.']);
        }

        if(!settings()->users->email_confirmation) {
            $prefixes = array_merge($prefixes, ['resend_activation.', 'sent_activation.']);
        }

		if(!settings()->content->broadcasts_is_enabled) {
			$prefixes = array_merge($prefixes, ['unsubscribe.',]);
		}

        if(!settings()->users->user_deletion_reminder) {
            $prefixes = array_merge($prefixes, ['global.emails.user_deletion_reminder.',]);
        }

        if(!settings()->users->auto_delete_inactive_users) {
            $prefixes = array_merge($prefixes, ['global.emails.auto_delete_inactive_users.',]);
        }

        /* Extended license */
        if(!settings()->payment->is_enabled) {
            $prefixes = array_merge($prefixes, ['plan.', 'pay.', 'pay_thank_you.', 'account_payments.', 'global.emails.user_payment.', 'global.emails.user_payment_cancelled.', 'account_plan.cancel.']);
        }

        if(!settings()->payment->is_enabled || !settings()->payment->taxes_and_billing_is_enabled) {
            $prefixes = array_merge($prefixes, ['pay_billing.', 'account.billing.']);
        }

        if(!settings()->payment->is_enabled || !settings()->payment->codes_is_enabled) {
            $prefixes = array_merge($prefixes, ['account_redeem_code.']);
        }

        if(!settings()->payment->is_enabled || !settings()->payment->invoice_is_enabled) {
            $prefixes = array_merge($prefixes, ['invoice.', 'credit_notes.']);
        }

		if(!settings()->payment->user_plan_expiry_reminder) {
			$prefixes = array_merge($prefixes, ['global.emails.user_plan_expiry_reminder.']);
		}

		if(!settings()->payment->user_plan_expiry_checker_is_enabled) {
			$prefixes = array_merge($prefixes, ['global.emails.user_plan_expired.']);
		}

		if(!settings()->users->auto_delete_inactive_users) {
			$prefixes = array_merge($prefixes, ['global.users.user_deletion_reminder.', 'global.emails.auto_delete_inactive_users.']);
		}

		if(!settings()->email_notifications->new_user) {
			$prefixes = array_merge($prefixes, ['global.emails.admin_new_user_notification.']);
		}

		if(!settings()->email_notifications->delete_user) {
			$prefixes = array_merge($prefixes, ['global.emails.admin_delete_user_notification.']);
		}

		if(!settings()->email_notifications->new_payment) {
			$prefixes = array_merge($prefixes, ['global.emails.admin_new_payment_notification.']);
		}

		if(!settings()->email_notifications->new_code_redeemed) {
			$prefixes = array_merge($prefixes, ['global.emails.admin_new_code_redeemed_notification.']);
		}

		if(!settings()->email_notifications->new_affiliate_withdrawal) {
			$prefixes = array_merge($prefixes, ['global.emails.admin_new_affiliate_withdrawal_notification.']);
		}

		if(!settings()->email_notifications->contact) {
			$prefixes = array_merge($prefixes, ['global.emails.admin_contact.']);
		}

		if(!settings()->users->welcome_email_is_enabled) {
			$prefixes = array_merge($prefixes, ['global.emails.user_welcome']);
		}

		if(!settings()->users->email_confirmation) {
			$prefixes = array_merge($prefixes, ['global.emails.user_activation.', 'global.emails.user_pending_email.']);
		}

        if(!settings()->main->white_labeling_is_enabled) {
            $prefixes = array_merge($prefixes, ['account_preferences.white_label']);
        }

        /* Plugins */
        if(!\Altum\Plugin::is_active('pwa') || !settings()->pwa->is_enabled) {
            $prefixes = array_merge($prefixes, ['pwa_install.']);
        }

        if(!\Altum\Plugin::is_active('push-notifications') || !settings()->push_notifications->is_enabled) {
            $prefixes = array_merge($prefixes, ['push_notifications_modal.']);
        }

        if(!\Altum\Plugin::is_active('teams')) {
            $prefixes = array_merge($prefixes, [
                'teams.',
                'team.',
                'team_create.',
                'team_update.',
                'team_members.',
                'team_member_create.',
                'team_member_update.',
                'teams_member.',
                'teams_member_delete_modal.',
                'teams_member_join_modal.',
                'teams_member_login_modal.',
                'global.emails.team_member_create',
                'teams_system.',
                'global.team_delegate_'
            ]);
        }

        if(!\Altum\Plugin::is_active('affiliate') || (\Altum\Plugin::is_active('affiliate') && !settings()->affiliate->is_enabled)) {
            $prefixes = array_merge($prefixes, ['referrals.', 'affiliate.', 'global.emails.user_affiliate_withdrawal_approved.']);
        }

        /* Per product features */
        if(!settings()->links->email_reports_is_enabled) {
            $prefixes = array_merge($prefixes, ['cron.email_reports.']);
        }

        if(!settings()->notification_handlers->is_enabled) {
            $prefixes = array_merge($prefixes, ['notification_handlers.', 'notification_handler_update.', 'notification_handler_create.', 'global.plan_settings.notification_handlers']);
        }

        /* Newsletters system */
        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            $prefixes = array_merge($prefixes, [
                'newsletters.',
                'newsletter_create.',
                'newsletter_update.',
                'newsletter_view.',
                'newsletter_subscribers.',
                'newsletter_subscriber_update_status_modal.',
                'newsletter_subscriber_create.',
                'newsletter_subscribers_import.',
                'newsletter_unsubscribe.',
                'account_preferences.newsletters_smtp',
                'global.emails.is_newsletter',
                'global.plan_settings.newsletters',
                'global.plan_settings.newsletter_',
                'global.plan_settings.no_forced_newsletters_custom_smtp',
                'admin_newsletters.',
                'admin_newsletter_subscribers.',
                'admin_statistics.newsletters.',
                'admin_statistics.newsletter_subscribers.',
                'admin_plans.plan.newsletters',
                'admin_plans.plan.newsletter_',
                'admin_plans.plan.force_newsletters_custom_smtp',
                'admin_settings.links.newsletters',
                'admin_settings.cron.newsletters',
            ]);
        }

        if(!\Altum\Plugin::is_active('pwa') || !settings()->pwa->is_enabled) {
            $prefixes = array_merge($prefixes, ['link.settings.pwa_']);
        }

        if(!settings()->tools->is_enabled) {
            $prefixes = array_merge($prefixes, ['tools.']);
        }

        if(!settings()->codes->qr_codes_is_enabled) {
            $prefixes = array_merge($prefixes, ['qr_codes.', 'qr_code_update.', 'qr_code_create.']);
        }

        if(!\Altum\Plugin::is_active('email-signatures') || !settings()->signatures->is_enabled) {
            $prefixes = array_merge($prefixes, ['signatures.', 'signature_update.', 'signature_create.']);
        }

        if(!\Altum\Plugin::is_active('digital-wallets') || !settings()->digital_wallets->is_enabled) {
            $prefixes = array_merge($prefixes, ['digital_wallets.', 'digital_wallet_update.', 'digital_wallet_create.', 'biolink_digital_wallet.']);
        }

        if(!\Altum\Plugin::is_active('payment-blocks')) {
            $prefixes = array_merge($prefixes, [
                'guests_payments.',
                'guests_payments_statistics.',
                'payment_processors.',
                'payment_processor_create.',
                'payment_processor_update.',
				'global.emails.guest_guest_payment_donation',
				'global.emails.user_guest_payment_product',
				'global.emails.guest_guest_payment_product',
				'global.emails.user_guest_payment_service',
                'global.emails.guest_guest_payment_service',
                'global.emails.user_guest_payment_donation',
                'guest_payment_approve_modal.',
                'guest_payment_cancel_modal.'
            ]);
        } else {
            $prefixes = array_values(array_filter($prefixes, fn($item) => $item !== 'pay.'));
        }

        foreach(require APP_PATH . 'includes/biolink_blocks.php' as $type => $value) {
            if(!settings()->links->available_biolink_blocks->{$type}) {
                $prefixes = array_merge($prefixes, [
                    'biolink_' . $type . '.',
                    'link.biolink.blocks.' . $type
                ]);
            }
        }

        if(!settings()->links->directory_is_enabled) {
            $prefixes = array_merge($prefixes, ['directory.']);
        }

        if(!settings()->links->domains_is_enabled) {
            $prefixes = array_merge($prefixes, ['domains.', 'domain_create.', 'domain_update.', 'domain_delete_modal.', 'global.emails.admin_new_domain_notification.']);
        }

        if(!settings()->links->biolinks_is_enabled || !settings()->links->biolinks_templates_is_enabled) {
            $prefixes = array_merge($prefixes, ['biolinks_templates.']);
        }

        if(!settings()->links->splash_page_is_enabled) {
            $prefixes = array_merge($prefixes, ['splash_pages.', 'splash_page_create.', 'splash_page_update.', 'link.splash.']);
        }

        if(!settings()->links->pixels_is_enabled) {
            $prefixes = array_merge($prefixes, ['pixels.', 'pixel_create.', 'pixel_update.']);
        }

        if(!settings()->links->biolinks_is_enabled) {
            $prefixes = array_merge($prefixes, ['biolinks_', 'biolink_', 'link.biolink.', 'data.', 'biolink_block_delete.', 'global.emails.user_data_collected', 'link.biolink_blocks.']);
        }

        if(!settings()->links->shortener_is_enabled){
            $prefixes = array_merge($prefixes, ['link_create.', 'create_link_modal.']);
        }

        if(!settings()->links->biolinks_is_enabled && !settings()->links->shortener_is_enabled && !settings()->links->files_is_enabled && !settings()->links->vcards_is_enabled && !settings()->links->events_is_enabled && !settings()->links->static_is_enabled) {
            $prefixes = array_merge($prefixes, ['links_statistics.']);
        }

        if(!settings()->links->files_is_enabled) {
            $prefixes = array_merge($prefixes, ['create_file_modal.']);
        }

        if(!settings()->links->vcards_is_enabled) {
            $prefixes = array_merge($prefixes, ['create_vcard_modal.']);
        }

        if(!settings()->links->events_is_enabled) {
            $prefixes = array_merge($prefixes, ['create_event_modal.']);
        }

        if(!settings()->links->static_is_enabled) {
            $prefixes = array_merge($prefixes, ['create_static_modal.']);
        }

        if(!\Altum\Plugin::is_active('aix')) {
            $prefixes = array_merge($prefixes, ['account_preferences.aix']);
        }

        if(!\Altum\Plugin::is_active('aix') || !settings()->aix->images_is_enabled) {
            $prefixes = array_merge($prefixes, ['images.', 'image_create.', 'image_update.']);
        }

        if(!\Altum\Plugin::is_active('aix') || !settings()->aix->transcriptions_is_enabled) {
            $prefixes = array_merge($prefixes, ['transcriptions.', 'transcription_create.', 'transcription_update.']);
        }

        if(!\Altum\Plugin::is_active('aix') || !settings()->aix->chats_is_enabled) {
            $prefixes = array_merge($prefixes, ['chats.', 'chat.', 'chat_create.']);
        }

        if(!\Altum\Plugin::is_active('aix') || !settings()->aix->syntheses_is_enabled) {
            $prefixes = array_merge($prefixes, ['syntheses.', 'synthesis_create.', 'synthesis_update.']);
        }

        if(!settings()->links->projects_is_enabled) {
            $prefixes = array_merge($prefixes, ['projects.', 'project_create.', 'project_update.']);
        }

        if(!\Altum\Plugin::is_active('chrome-extension') || !settings()->chrome_extension->is_enabled) {
            $prefixes = array_merge($prefixes, ['chrome_extension.', 'index.chrome_extension.']);
        }

        return $prefixes;

    }

    public static function generate_admin_feature_pages_settings_links() {

        $dynamic_page_link_keys = [
            'pages' => [
                'url' => url('admin/settings/content'),
                'name' => l('admin_settings.content.tab')
            ],
            'api-documentation' => [
                'url' => url('admin/settings/main'),
                'name' => l('admin_settings.main.tab')
            ],
            'blog' => [
                'url' => url('admin/settings/content'),
                'name' => l('admin_settings.content.tab')
            ],
            'affiliate' => [
                'url' => url('admin/settings/affiliate'),
                'name' => l('admin_settings.affiliate.tab')
            ],
            'plan' => [
                'url' => url('admin/settings/payment'),
                'name' => l('admin_settings.payment.tab')
            ],
            'dashboard' => [
                'url' => null,
                'name' => null
            ],
            'contact' => [
                'url' => url('admin/settings/email_notifications'),
                'name' => l('admin_settings.email_notifications.tab')
            ],
            'cookie_consent' => [
                'url' => url('admin/settings/cookie_consent'),
                'name' => l('admin_settings.cookie_consent.tab')
            ],
            'push_notifications' => [
                'url' => url('admin/settings/push_notifications'),
                'name' => l('admin_settings.push_notifications.tab')
            ],
        ];

        /* Per product features */
        $dynamic_page_link_keys['tools'] = [
            'url' => url('admin/settings/tools'),
            'name' => l('admin_settings.tools.tab')
        ];

        $dynamic_page_link_keys['directory'] = [
            'url' => url('admin/settings/links'),
            'name' => l('admin_settings.links.tab')
        ];

        $dynamic_page_link_keys['chrome-extension'] = [
            'url' => null,
            'name' => l('admin_settings.links.tab')
        ];

        return $dynamic_page_link_keys;
    }

}
