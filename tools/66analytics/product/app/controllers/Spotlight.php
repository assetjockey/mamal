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

use Altum\Response;

defined('ALTUMCODE') || die();

class Spotlight extends Controller {

    public function index() {

        if(!settings()->main->admin_spotlight_is_enabled && !settings()->main->user_spotlight_is_enabled) {
            throw_404();
        }

        if($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw_404();
        }

        if(!\Altum\Csrf::check('global_token')) {
            Response::json(l('global.error_message.invalid_csrf_token'), 'error');
        }

        $available_pages = [];

        $available_pages[] = [
            'name' => l('index.title'),
            'url' => ''
        ];

        if(!is_logged_in()) {
            $available_pages[] = [
                'name' => l('login.title'),
                'url' => 'login'
            ];

            if(settings()->users->register_is_enabled) {
                $available_pages[] = [
                    'name' => l('register.title'),
                    'url' => 'register'
                ];
            }

            if(settings()->users->email_confirmation) {
                $available_pages[] = [
                    'name' => l('resend_activation.title'),
                    'url' => 'resend-activation'
                ];
            }

            $available_pages[] = [
                'name' => l('lost_password.title'),
                'url' => 'lost-password'
            ];

        }

        if(is_logged_in()) {
            $available_pages[] = [
                'name' => l('dashboard.menu'),
                'url' => 'dashboard'
            ];

            /* Per product */
            $available_pages[] = [
                'name' => l('realtime.menu'),
                'url'  => 'dashboard/realtime'
            ];

            $available_pages[] = [
                'name' => l('outbound_clicks.title'),
                'url'  => 'dashboard/outbound-clicks'
            ];

            $available_pages[] = [
                'name' => l('goals.title'),
                'url'  => 'dashboard/goals'
            ];

            $available_pages[] = [
                'name' => l('pageviews.title'),
                'url'  => 'pageviews'
            ];

            $available_pages[] = [
                'name' => l('visitors.title'),
                'url'  => 'visitors'
            ];

            if(settings()->analytics->websites_heatmaps_is_enabled) {
                $available_pages[] = [
                    'name' => l('heatmaps.title'),
                    'url'  => 'heatmaps'
                ];
            }

            if(settings()->analytics->sessions_replays_is_enabled) {
                $available_pages[] = [
                    'name' => l('replays.title'),
                    'url'  => 'replays'
                ];
            }

            $available_pages[] = [
                'name' => l('websites.title'),
                'url'  => 'websites'
            ];

            $available_pages[] = [
                'name' => l('website_create.title'),
                'url'  => 'website-create'
            ];

            $available_pages[] = [
                'name' => l('websites_import.title'),
                'url'  => 'websites-import'
            ];

            $available_pages[] = [
                'name' => l('teams.title'),
                'url'  => 'teams'
            ];

            if(settings()->analytics->domains_is_enabled) {
                $available_pages[] = [
                    'name' => l('domains.title'),
                    'url' => 'domains'
                ];
                $available_pages[] = [
                    'name' => l('domain_create.title'),
                    'url' => 'domain-create'
                ];
            }

            if(settings()->analytics->annotations_is_enabled) {
                $available_pages[] = [
                    'name' => l('annotations.title'),
                    'url' => 'annotations'
                ];
                $available_pages[] = [
                    'name' => l('annotation_create.title'),
                    'url' => 'annotation-create'
                ];
            }





            $available_pages[] = [
                'name' => l('account.title'),
                'url' => 'account'
            ];

            $available_pages[] = [
                'name' => l('account_preferences.title'),
                'url' => 'account-preferences'
            ];

            $available_pages[] = [
                'name' => l('account_plan.title'),
                'url' => 'account-plan'
            ];

            if(settings()->payment->is_enabled) {

                if(settings()->payment->codes_is_enabled) {
                    $available_pages[] = [
                        'name' => l('account_redeem_code.title'),
                        'url' => 'account-redeem-code'
                    ];
                }

                $available_pages[] = [
                    'name' => l('account_payments.title'),
                    'url' => 'account-payments'
                ];

                if(\Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled) {
                    $available_pages[] = [
                        'name' => l('referrals.title'),
                        'url' => 'referrals'
                    ];
                }
            }

            if(settings()->main->api_is_enabled) {
                $available_pages[] = [
                    'name' => l('account_api.title'),
                    'url' => 'account-api'
                ];
            }

            $available_pages[] = [
                'name' => l('account_logs.title'),
                'url' => 'account-logs'
            ];

            $available_pages[] = [
                'name' => l('account_delete.title'),
                'url' => 'account-delete'
            ];

            if(settings()->internal_notifications->users_is_enabled) {
                $available_pages[] = [
                    'name' => l('internal_notifications.title'),
                    'url' => 'internal-notifications'
                ];
            }

            if(user()->type == 1 && file_exists(APP_PATH . 'languages/admin/' . \Altum\Language::$name . '#' . \Altum\Language::$code . '.php')) {
                $admin_language = require APP_PATH . 'languages/admin/' . \Altum\Language::$name . '#' . \Altum\Language::$code . '.php';
                $whole_language = \Altum\Language::$languages[\Altum\Language::$name]['content'] = \Altum\Language::$languages[\Altum\Language::$name]['content'] + $admin_language;

                $available_pages[] = [
                    'name' => l('admin_index.title', clear_cache: true),
                    'url' => 'admin'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_settings.menu'),
                    'url' => 'admin/settings'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_users.title'),
                    'url' => 'admin/users'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_user_create.title'),
                    'url' => 'admin/user-create'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_users_logs.title'),
                    'url' => 'admin/users-logs'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_blog_posts.title'),
                    'url' => 'admin/blog-posts'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_blog_post_create.title'),
                    'url' => 'admin/blog-post-create'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_blog_posts_categories.title'),
                    'url' => 'admin/blog-posts-categories'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_blog_posts_category_create.title'),
                    'url' => 'admin/blog-posts-category-create'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_pages.title'),
                    'url' => 'admin/pages'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_page_create.title'),
                    'url' => 'admin/page-create'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_pages_categories.title'),
                    'url' => 'admin/pages-categories'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_pages_category_create.title'),
                    'url' => 'admin/pages-category-create'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_plans.title'),
                    'url' => 'admin/plans'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_plan_create.title'),
                    'url' => 'admin/plan-create'
                ];

                if(in_array(settings()->license->type, ['Extended License', 'extended'])) {
                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . l('admin_codes.title'),
                        'url' => 'admin/codes'
                    ];

                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . l('admin_code_create.title'),
                        'url' => 'admin/code-create'
                    ];

                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . l('admin_redeemed_codes.title'),
                        'url' => 'admin/redeemed-codes'
                    ];

                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . l('admin_taxes.title'),
                        'url' => 'admin/taxes'
                    ];

                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . l('admin_taxes_import.title'),
                        'url' => 'admin/taxes-import'
                    ];

                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . l('admin_tax_create.title'),
                        'url' => 'admin/tax-create'
                    ];

                    if(\Altum\Plugin::is_active('affiliate')) {
                        $available_pages[] = [
                            'name' => l('global.menu.admin') . ' - ' . l('admin_affiliates_withdrawals.title'),
                            'url' => 'admin/affiliates-withdrawals'
                        ];
                    }

                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . l('admin_payments.title'),
                        'url' => 'admin/payments'
                    ];

                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . l('admin_payment_create.title'),
                        'url' => 'admin/payment-create'
                    ];
                }

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_statistics.menu'),
                    'url' => 'admin/statistics'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_plugins.title'),
                    'url' => 'admin/plugins'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_languages.title'),
                    'url' => 'admin/languages'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_language_create.title'),
                    'url' => 'admin/language-create'
                ];

                $pages = [
                    'main',
                    'users',
                    'content'
                ];

                foreach ($pages as $page) {
                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . sprintf(l('admin_settings.title'), l('admin_settings.' . $page . '.tab')),
                        'url'  => 'admin/settings/' . $page
                    ];
                }

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . sprintf(l('admin_settings.title'), l('admin_settings.analytics.tab')),
                    'url'  => 'admin/settings/analytics'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . sprintf(l('admin_settings.title'), l('admin_settings.payment.tab')),
                    'url'  => 'admin/settings/payment'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . sprintf(l('admin_settings.title'), l('admin_settings.business.tab')),
                    'url'  => 'admin/settings/business'
                ];

                foreach(require APP_PATH . 'includes/payment_processors.php' as $key => $value) {
                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . sprintf(l('admin_settings.title'), l('admin_settings.' . $key . '.tab')),
                        'url'  => 'admin/settings/' . $key
                    ];
                }

                $pages = [
                    'affiliate',
                    'captcha',
                    'facebook',
                    'google',
                    'twitter',
                    'discord',
                    'linkedin',
                    'microsoft',
                    'ads',
                    'cookie_consent',
                    'socials',
                    'smtp',
                    'theme',
                    'custom',
                    'custom_images',
                    'announcements',
                    'internal_notifications',
                    'email_notifications',
                    'push_notifications',
                    'webhooks',
                    'offload',
                    'pwa',
                    'image_optimizer',
                    'dynamic_og_images',
                    'sso',
                    'cron',
                    'health',
                    'cache',
                    'license',
                    'support'
                ];

                foreach ($pages as $page) {
                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . sprintf(l('admin_settings.title'), l('admin_settings.' . $page . '.tab')),
                        'url'  => 'admin/settings/' . $page
                    ];
                }


                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_api_documentation.title'),
                    'url' => 'admin/api-documentation'
                ];

                if(\Altum\Plugin::is_active('teams')) {
                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . l('admin_teams.title'),
                        'url' => 'admin/teams'
                    ];

                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . l('admin_team_members.title'),
                        'url' => 'admin/team-members'
                    ];
                }

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_broadcasts.title'),
                    'url' => 'admin/broadcasts'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_broadcast_create.title'),
                    'url' => 'admin/broadcast-create'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_broadcast_subscribers.title'),
                    'url' => 'admin/broadcast-subscribers'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_statistics.broadcast_subscribers.menu'),
                    'url' => 'admin/statistics/broadcast_subscribers'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_internal_notifications.title'),
                    'url' => 'admin/internal-notifications'
                ];

                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_internal_notification_create.title'),
                    'url' => 'admin/internal-notification-create'
                ];

                if(\Altum\Plugin::is_active('push-notifications')) {
                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . l('admin_push_subscribers.title'),
                        'url' => 'admin/push-subscribers'
                    ];

                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . l('admin_push_notifications.title'),
                        'url' => 'admin/push-notifications'
                    ];

                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . l('admin_push_notification_create.title'),
                        'url' => 'admin/push-notification-create'
                    ];
                }

                if(\Altum\Plugin::is_active('image-optimizer')) {
                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . l('admin_image_optimizer.title'),
                        'url' => 'admin/image-optimizer'
                    ];
                }

                if(\Altum\Plugin::is_active('dynamic-og-images')) {
                    $available_pages[] = [
                        'name' => l('global.menu.admin') . ' - ' . l('admin_dynamic_og_images.title'),
                        'url' => 'admin/dynamic-og-images'
                    ];
                }

                /* Per product */
                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_websites.menu'),
                    'url'  => 'admin/websites'
                ];
                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_heatmaps.menu'),
                    'url'  => 'admin/heatmaps'
                ];
                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_replays.menu'),
                    'url'  => 'admin/replays'
                ];
                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_domains.menu'),
                    'url'  => 'admin/domains'
                ];
                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_domain_create.title'),
                    'url'  => 'admin/domain-create'
                ];
                $available_pages[] = [
                    'name' => l('global.menu.admin') . ' - ' . l('admin_annotations.menu'),
                    'url'  => 'admin/annotations'
                ];
            }

            $available_pages[] = [
                'name' => l('global.menu.logout'),
                'url' => 'logout'
            ];
        }

        if(settings()->payment->is_enabled) {
            $available_pages[] = [
                'name' => l('plan.title'),
                'url' => 'plan'
            ];
        }

        $available_pages[] = [
            'name' => l('help.title'),
            'url'  => 'help'
        ];

        foreach([
            'lightweight-tracking' => l('help.lightweight_tracking.menu'),
            'advanced-tracking' => l('help.advanced_tracking.menu'),
            'goals' => l('help.goals.menu'),
            'dnt' => l('help.dnt.menu'),
            'custom-parameters' => l('help.custom_parameters.menu'),
            'opt-out' => l('help.opt_out.menu'),
        ] as $url => $name) {
            $available_pages[] = [
                'name' => $name,
                'url' => 'help/' . $url
            ];
        }

        if(settings()->email_notifications->contact && !empty(settings()->email_notifications->emails)) {
            $available_pages[] = [
                'name' => l('contact.title'),
                'url' => 'contact'
            ];
        }

		if(settings()->content->broadcasts_is_enabled && settings()->content->broadcasts_display_index_box && (is_logged_in() || settings()->content->broadcasts_guests_is_enabled)) {
			$available_pages[] = [
				'name' => l('broadcast_subscribe.title'),
				'url' => 'broadcast-subscribe'
			];
		}

        if(settings()->main->api_is_enabled) {
            $available_pages[] = [
                'name' => l('api_documentation.title'),
                'url' => 'api-documentation'
            ];
        }

        if(settings()->payment->is_enabled) {
            if(\Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled) {
                $available_pages[] = [
                    'name' => l('affiliate.title'),
                    'url' => 'affiliate'
                ];
            }
        }

        if(settings()->content->blog_is_enabled) {
            $available_pages[] = [
                'name' => l('blog.title'),
                'url' => 'blog'
            ];
        }

        if(settings()->content->pages_is_enabled) {
            $available_pages[] = [
                'name' => l('pages.title'),
                'url' => 'pages'
            ];
        }

        Response::json('', 'success', $available_pages);

    }

}
