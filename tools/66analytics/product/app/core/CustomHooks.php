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
namespace Altum;

use Altum\Models\SessionsReplays;

defined('ALTUMCODE') || die();

class CustomHooks {

    public static function return_default_user_preferences() {
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
            'websites_default_order_by' => 'website_id',
            'websites_data_period' => 'current_month',
            'heatmaps_default_order_by' => 'heatmap_id',
            'domains_default_order_by' => 'domain_id',
            'annotations_default_order_by' => 'annotation_id',
            'goals_default_order_by' => 'goal_id',
        ];
    }

    public static function user_finished_login($data = []) {

	}
	
    public static function user_initiate_registration($data = []) {

    }

    public static function user_finished_registration($data = []) {

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

        $replays = db()->where('user_id', $data['user']->user_id)->get('sessions_replays', ['replay_id']);

        foreach ($replays as $replay) {
            (new SessionsReplays())->delete($replay->replay_id);
        }
    }

    public static function user_payment_finished($data = []) {
        extract($data);

        db()->where('user_id', $user->user_id)->update('websites', [
            'current_month_sessions_events' => 0,
            'current_month_events_children' => 0,
            'current_month_sessions_replays' => 0,
            'plan_sessions_events_limit_notice' => 0,
            'plan_events_children_limit_notice' => 0,
            'plan_sessions_replays_limit_notice' => 0,
        ]);

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

		if(!settings()->content->broadcasts_is_enabled) {
			$prefixes = array_merge($prefixes, ['index.broadcasts.', 'broadcast_subscribe.']);
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

		if(!settings()->payment->user_plan_renewal_reminder) {
			$prefixes = array_merge($prefixes, ['global.emails.user_plan_renewal_reminder.']);
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

        /*
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
        } */

        if(!\Altum\Plugin::is_active('affiliate') || (\Altum\Plugin::is_active('affiliate') && !settings()->affiliate->is_enabled)) {
            $prefixes = array_merge($prefixes, ['referrals.', 'referrals_users.', 'affiliate.', 'global.emails.user_affiliate_withdrawal_approved.']);
        }

        /* Per product features */
		if(!settings()->analytics->email_reports_is_enabled) {
			$prefixes = array_merge($prefixes, ['cron.email_reports.']);
		}

        if(!settings()->analytics->domains_is_enabled) {
            $prefixes = array_merge($prefixes, ['domains.', 'domain_create.', 'domain_update.', 'domain_delete_modal.', 'global.emails.admin_new_domain_notification.']);
        }

        if(!settings()->analytics->websites_heatmaps_is_enabled) {
            $prefixes = array_merge($prefixes, ['heatmaps.', 'heatmap_.']);
        }

        if(!settings()->analytics->sessions_replays_is_enabled) {
            $prefixes = array_merge($prefixes, ['replays.', 'replay.']);
        }

        if(!settings()->analytics->annotations_is_enabled) {
            $prefixes = array_merge($prefixes, ['annotations.', 'annotation_update.', 'annotation_create.']);
        }

        if(!settings()->analytics->dashboard_views_is_enabled) {
            $prefixes = array_merge($prefixes, ['dashboard_views.']);
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
