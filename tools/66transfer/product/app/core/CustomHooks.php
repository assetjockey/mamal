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
        $dashboard = array_fill_keys(require APP_PATH . 'includes/available_dashboard_features.php', true);

        return [
            'white_label_title' => '',
            'white_label_footer_description' => '',
            'white_label_remove_socials' => false,
            'white_label_remove_footer_links' => false,
            'white_label_logo_light' => null,
            'white_label_logo_dark' => null,
            'white_label_favicon' => null,
            'default_results_per_page' => settings()->main->default_results_per_page,
            'default_order_type' => settings()->main->default_order_type,
            'transfers_default_order_by' => 'transfer_id',
            'transfer_requests_default_order_by' => 'transfer_request_id',
            'notification_handlers_default_order_by' => 'notification_handler_id',
            'domains_default_order_by' => 'domain_id',
            'projects_default_order_by' => 'project_id',
            'transfers_default_type' => 'link',
            'transfers_default_downloads_limit' => null,
            'transfers_default_expiration_datetime' => null,
            'transfers_default_file_preview_is_enabled' => true,
            'transfers_default_gallery_file_preview_is_enabled' => true,
            'transfers_default_pixels_ids' => [],
            'transfers_default_project_id' => null,
            'transfers_default_is_removed_branding' => true,
            'transfers_default_custom_css' => '',
            'transfers_default_custom_js' => '',
            'transfers_default_download_notification_handlers_ids' => [],
            'transfers_default_pageview_notification_handlers_ids' => [],
            'transfers_auto_file_upload' => false,
            'transfers_auto_transfer_create' => false,
            'transfers_auto_copy_link' => true,
            'transfer_requests_default_expiration_datetime' => null,
            'transfer_requests_default_pixels_ids' => [],
            'transfer_requests_default_project_id' => null,
            'transfer_requests_default_is_removed_branding' => true,
            'transfer_requests_default_custom_css' => '',
            'transfer_requests_default_custom_js' => '',
            'transfer_requests_default_submission_notification_handlers_ids' => [],
            'transfer_requests_default_pageview_notification_handlers_ids' => [],
            'transfer_requests_auto_file_upload' => false,
            'transfer_requests_auto_submission_create' => false,
            'transfer_requests_auto_copy_link' => true,
            'dashboard' => $dashboard,
            'excluded_ips' => [],
        ];
    }

    public static function user_initiate_registration($data = []) {

    }

    public static function user_finished_registration($data = []) {
        $plan_settings = json_decode($data['plan_settings']);

        if($plan_settings->notification_handlers_email_limit > 0) {
            db()->insert('notification_handlers', [
                'user_id' => $data['user_id'],
                'type' => 'email',
                'name' => $data['email'],
                'settings' => json_encode([
                    'email' => $data['email']
                ]),
                'datetime' => get_date(),
            ]);
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

        /* Get all transfers related to the user */
        $transfers_ids = db()->where('user_id', $data['user']->user_id)->getValue('transfers', 'transfer_id', null);

        /* Delete all transfers related to the user */
        if($transfers_ids) {
            (new \Altum\Models\Transfers())->bulk_delete($transfers_ids, $data['user']->user_id);
        }

		/* Get all transfers related to the user */
		$transfer_requests_ids = db()->where('user_id', $data['user']->user_id)->getValue('transfer_requests', 'transfer_request_id', null);

		/* Delete all transfer_requests related to the user */
		if($transfer_requests_ids) {
			(new \Altum\Models\TransferRequests())->bulk_delete($transfer_requests_ids, $data['user']->user_id);
		}

    }

    public static function user_payment_finished($data = []) {
        extract($data);

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
            $prefixes = array_merge($prefixes, ['pay_billing.']);
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
        if(!settings()->notification_handlers->is_enabled) {
            $prefixes = array_merge($prefixes, ['notification_handlers.', 'notification_handler_update.', 'notification_handler_create.', 'global.plan_settings.notification_handlers']);
        }

        if(!settings()->transfers->domains_is_enabled) {
            $prefixes = array_merge($prefixes, ['domains.', 'domain_create.', 'domain_update.', 'domain_delete_modal.', 'global.emails.admin_new_domain_notification.']);
        }

        if(!settings()->transfers->pixels_is_enabled) {
            $prefixes = array_merge($prefixes, ['pixels.', 'pixel_create.', 'pixel_update.']);
        }

        if(!settings()->transfers->projects_is_enabled) {
            $prefixes = array_merge($prefixes, ['projects.', 'project_create.', 'project_update.']);
        }

        if(!settings()->transfers->transfer_requests_is_enabled) {
            $prefixes = array_merge($prefixes, ['r_transfer_request.', 'transfer_requests.', 'transfer_request_create.', 'transfer_request_update.', 'transfer_request_uploads.']);
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

        return $dynamic_page_link_keys;
    }

}
