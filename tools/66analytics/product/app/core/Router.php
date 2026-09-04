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

defined('ALTUMCODE') || die();

class Router {
    public static $params = [];
    public static $original_request = '';
    public static $original_request_query = '';
    public static $language_code = '';
    public static $path = '';
    public static $controller_key = 'index';
    public static $controller = 'Index';
    public static $controller_settings = [
        'wrapper' => 'wrapper',
        'no_authentication_check' => false,

        /* Enable / disable browser language detection & redirection */
        'no_browser_language_detection' => false,

        /* Enable / disable browser language detection & redirection */
        'allow_indexing' => true,

        /* Should we see a view for the controller? */
        'has_view' => true,

        /* Footer currency display */
        'currency_switcher' => false,

        /* If set on yes, ads won't show on these pages at all */
        'ads' => false,

        /* Authentication guard check (potential values: null, 'guest', 'user', 'admin') */
        'authentication' => null
    ];
    public static $method = 'index';
    public static $data = [];

    public static $routes = [
        '' => [
            'statistics' => [
                'controller' => 'Statistics',
                'settings' => [
                    //'wrapper' => 'statistics_wrapper',
                    'wrapper' => 'wrapper',
                    'ads' => true,
                ]
            ],

            'dashboard' => [
                'controller' => 'Dashboard',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'statistics-ajax-advanced' => [
                'controller' => 'StatisticsAjaxAdvanced',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'statistics-ajax-lightweight' => [
                'controller' => 'StatisticsAjaxLightweight',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'websites-ajax' => [
                'controller' => 'WebsitesAjax',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'goals-ajax' => [
                'controller' => 'GoalsAjax',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'annotations-ajax' => [
                'controller' => 'AnnotationsAjax',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'dashboard-views-ajax' => [
                'controller' => 'DashboardViewsAjax',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'realtime' => [
                'controller' => 'Realtime',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'pageviews' => [
                'controller' => 'Pageviews',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'pageviews-advanced' => [
                'controller' => 'PageviewsAdvanced',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'pageviews-lightweight' => [
                'controller' => 'PageviewsLightweight',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'visitors' => [
                'controller' => 'Visitors',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'visitor' => [
                'controller' => 'Visitor',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'session-ajax' => [
                'controller' => 'SessionAjax',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'heatmaps' => [
                'controller' => 'Heatmaps',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'heatmaps-ajax' => [
                'controller' => 'HeatmapsAjax',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'heatmap' => [
                'controller' => 'Heatmap',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'replays' => [
                'controller' => 'Replays',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'replay' => [
                'controller' => 'Replay',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'websites' => [
                'controller' => 'Websites',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'websites-import' => [
                'controller' => 'WebsitesImport',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'website-update' => [
                'controller' => 'WebsiteUpdate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'website-create' => [
                'controller' => 'WebsiteCreate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'annotations' => [
                'controller' => 'Annotations',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'annotation-update' => [
                'controller' => 'AnnotationUpdate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'annotation-create' => [
                'controller' => 'AnnotationCreate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'teams' => [
                'controller' => 'Teams',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'teams-ajax' => [
                'controller' => 'TeamsAjax',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'team' => [
                'controller' => 'Team',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'teams-associations-ajax' => [
                'controller' => 'TeamsAssociationsAjax',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'help' => [
                'controller' => 'Help'
            ],

            'domains' => [
                'controller' => 'Domains',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'domain-create' => [
                'controller' => 'DomainCreate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'domain-update' => [
                'controller' => 'DomainUpdate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'favicon' => [
                'controller' => 'Favicon',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_sessions' => false,
                    'allow_indexing' => false,
                ]
            ],

            /* Common routes */
            'index' => [
               'controller' => 'Index',
                'settings' => [
                    'currency_switcher' => true,
                ]
            ],

            'login' => [
                'controller' => 'Login',
                'settings' => [
                    'wrapper' => 'basic_wrapper',
                    'no_browser_language_detection' => true,
                ]
            ],

            'register' => [
                'controller' => 'Register',
                'settings' => [
                    'wrapper' => 'basic_wrapper',
                    'no_browser_language_detection' => true,
                ]
            ],

            'affiliate' => [
                'controller' => 'Affiliate'
            ],

            'pages' => [
                'controller' => 'Pages'
            ],

            'page' => [
                'controller' => 'Page'
            ],

            'blog' => [
                'controller' => 'Blog'
            ],

            'api-documentation' => [
                'controller' => 'ApiDocumentation',
            ],

			'contact' => [
				'controller' => 'Contact',
				'settings' => [
					'allow_team_access' => false,
				]
			],

			'broadcast-subscribe' => [
				'controller' => 'BroadcastSubscribe',
			],

            'activate-user' => [
                'controller' => 'ActivateUser'
            ],

            'lost-password' => [
                'controller' => 'LostPassword',
                'settings' => [
                    'wrapper' => 'basic_wrapper',
                ]
            ],

            'reset-password' => [
                'controller' => 'ResetPassword',
                'settings' => [
                    'wrapper' => 'basic_wrapper',
                ]
            ],

            'resend-activation' => [
                'controller' => 'ResendActivation',
                'settings' => [
                    'wrapper' => 'basic_wrapper',
                ]
            ],

            'sent-activation' => [
                'controller' => 'SentActivation',
                'settings' => [
                    'wrapper' => 'basic_wrapper',
                ]
            ],

            'logout' => [
                'controller' => 'Logout'
            ],

            'not-found' => [
                'controller' => 'NotFound',
            ],

            'maintenance' => [
                'controller' => 'Maintenance',
                'settings' => [
                    'wrapper' => 'basic_wrapper',
                ]
            ],

            'account' => [
                'controller' => 'Account',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'account-preferences' => [
                'controller' => 'AccountPreferences',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'account-plan' => [
                'controller' => 'AccountPlan',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'account-redeem-code' => [
                'controller' => 'AccountRedeemCode',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'no_ads' => true,
                ]
            ],

            'account-payments' => [
                'controller' => 'AccountPayments',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'account-logs' => [
                'controller' => 'AccountLogs',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'account-api' => [
                'controller' => 'AccountApi',
                'settings' => [
                    'wrapper'   => 'app_wrapper',
                ]
            ],

            'account-delete' => [
                'controller' => 'AccountDelete',
                'settings' => [
                    'wrapper'   => 'app_wrapper',
                ]
            ],

            'referrals' => [
                'controller' => 'Referrals',
                'settings' => [
                    'wrapper'   => 'app_wrapper',
                ]
            ],

            'referrals-users' => [
                'controller' => 'ReferralsUsers',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'allow_team_access' => false,
                ]
            ],

            'invoice' => [
                'controller' => 'Invoice',
                'settings' => [
                    'wrapper' => 'invoice/invoice_wrapper',
                    'allow_team_access' => false,
                ]
            ],

            'credit-notes' => [
                'controller' => 'CreditNotes',
                'settings' => [
                    'wrapper' => 'invoice/invoice_wrapper',
                    'allow_team_access' => false,
                ]
            ],

            'plan' => [
               'controller' => 'Plan',
                'settings' => [
                    'currency_switcher' => true,
                ],
            ],

            'pay' => [
                'controller' => 'Pay',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'currency_switcher' => true,
                ]
            ],

            'pay-billing' => [
                'controller' => 'PayBilling',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'pay-thank-you' => [
                'controller' => 'PayThankYou',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'pixel' => [
                'controller' => 'Pixel',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'pixel-track' => [
                'controller' => 'PixelTrack',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'internal-notifications' => [
                'controller' => 'InternalNotifications',
                'settings' => [
                    'ads' => true,
                    'allow_team_access' => false,
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'spotlight' => [
                'controller' => 'Spotlight',
                'settings' => [
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => true,
                ]
            ],

            'push-subscribers' => [
                'controller' => 'PushSubscribers',
                'settings' => [
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => true,
                ]
            ],

            'sso' => [
                'controller' => 'SSO',
                'settings' => [
                    'allow_team_access' => false,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_sessions' => true,
                ]
            ],

            /* Webhooks */
            'webhook-paypal' => [
                'controller' => 'WebhookPaypal',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-stripe' => [
                'controller' => 'WebhookStripe',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-payu' => [
                'controller' => 'WebhookPayu',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-iyzico' => [
                'controller' => 'WebhookIyzico',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-paystack' => [
                'controller' => 'WebhookPaystack',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-razorpay' => [
                'controller' => 'WebhookRazorpay',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-mollie' => [
                'controller' => 'WebhookMollie',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-onepay' => [
                'controller' => 'WebhookOnepay',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-yookassa' => [
                'controller' => 'WebhookYookassa',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-crypto-com' => [
                'controller' => 'WebhookCryptoCom',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-paddle' => [
                'controller' => 'WebhookPaddle',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
					'allow_sessions' => false,
                ]
            ],

            'webhook-paddle-billing' => [
                'controller' => 'WebhookPaddleBilling',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-mercadopago' => [
                'controller' => 'WebhookMercadopago',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-midtrans' => [
                'controller' => 'WebhookMidtrans',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-flutterwave' => [
                'controller' => 'WebhookFlutterwave',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-lemonsqueezy' => [
                'controller' => 'WebhookLemonsqueezy',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-myfatoorah' => [
                'controller' => 'WebhookMyfatoorah',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-klarna' => [
                'controller' => 'WebhookKlarna',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-plisio' => [
                'controller' => 'WebhookPlisio',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-plisio-whitelabel' => [
                'controller' => 'WebhookPlisioWhitelabel',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'webhook-revolut' => [
                'controller' => 'WebhookRevolut',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            /* Others */
            'cookie-consent' => [
                'controller' => 'CookieConsent',
                'settings' => [
                    'no_authentication_check' => true,
                    'no_browser_language_detection' => true,
                    'has_view' => false,
					'allow_sessions' => false,
                ]
            ],

            'sitemap' => [
                'controller' => 'Sitemap',
                'settings' => [
                    'no_authentication_check' => true,
                    'no_browser_language_detection' => true,
                    'has_view' => false,
                    'allow_sessions' => false,
                ]
            ],

            'cron' => [
                'controller' => 'Cron',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'broadcast' => [
                'controller' => 'Broadcast',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_sessions' => false,
                ]
            ],

            'unsubscribe' => [
                'controller' => 'Unsubscribe',
                'settings' => [
                    'wrapper' => 'basic_wrapper',
                ]
            ],

            'view' => [
                'controller' => 'View',
                'settings' => [
                    'no_authentication_check' => false,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_sessions' => true,
                ]
            ],
        ],

        'api' => [
            'domains' => [
                'controller' => 'ApiDomains',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'websites' => [
                'controller' => 'ApiWebsites',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'annotations' => [
                'controller' => 'ApiAnnotations',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'dashboard-views' => [
                'controller' => 'ApiDashboardViews',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'goals' => [
                'controller' => 'ApiGoals',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'goals-conversions' => [
                'controller' => 'ApiGoalsConversions',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'pageviews-advanced' => [
                'controller' => 'ApiPageviewsAdvanced',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'sessions' => [
                'controller' => 'ApiSessions',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'events-children' => [
                'controller' => 'ApiEventsChildren',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'outbound-clicks' => [
                'controller' => 'ApiOutboundClicks',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'replays' => [
                'controller' => 'ApiReplays',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'heatmaps' => [
                'controller' => 'ApiHeatmaps',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'visitors' => [
                'controller' => 'ApiVisitors',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'pageviews-lightweight' => [
                'controller' => 'ApiPageviewsLightweight',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'statistics' => [
                'controller' => 'ApiStatistics',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'teams' => [
                'controller' => 'ApiTeams',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                ]
            ],
            'teams-member' => [
                'controller' => 'ApiTeamsMember',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                ]
            ],
            'team-members' => [
                'controller' => 'ApiTeamMembers',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                ]
            ],
            'user' => [
                'controller' => 'ApiUser',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'payments' => [
                'controller' => 'ApiPayments',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'referrals-users' => [
                'controller' => 'ApiReferralsUsers',
                'settings' => [
                    'no_browser_language_detection' => true,
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'logs' => [
                'controller' => 'ApiLogs',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
        ],

        /* Admin Panel */
        'admin' => [
            'websites' => [
                'controller' => 'AdminWebsites'
            ],

            'annotations' => [
                'controller' => 'AdminAnnotations',
            ],

            'heatmaps' => [
                'controller' => 'AdminHeatmaps'
            ],

            'replays' => [
                'controller' => 'AdminReplays'
            ],

            'domains' => [
                'controller' => 'AdminDomains',
            ],

			'domain-create' => [
				'controller' => 'AdminDomainCreate',
			],

            'domain-update' => [
                'controller' => 'AdminDomainUpdate',
            ],

            /* Common routes */
            'index' => [
                'controller' => 'AdminIndex'
            ],

            'users' => [
                'controller' => 'AdminUsers'
            ],

            'user-create' => [
                'controller' => 'AdminUserCreate'
            ],

            'user-view' => [
                'controller' => 'AdminUserView'
            ],

            'user-update' => [
                'controller' => 'AdminUserUpdate'
            ],

            'users-logs' => [
                'controller' => 'AdminUsersLogs',
            ],

            'redeemed-codes' => [
                'controller' => 'AdminRedeemedCodes',
            ],

            'blog-posts' => [
                'controller' => 'AdminBlogPosts'
            ],

            'blog-post-create' => [
                'controller' => 'AdminBlogPostCreate'
            ],

            'blog-post-update' => [
                'controller' => 'AdminBlogPostUpdate'
            ],

            'blog-post-preview' => [
                'controller' => 'AdminBlogPostPreview'
            ],

            'blog-posts-categories' => [
                'controller' => 'AdminBlogPostsCategories'
            ],

            'blog-posts-category-create' => [
                'controller' => 'AdminBlogPostsCategoryCreate'
            ],

            'blog-posts-category-update' => [
                'controller' => 'AdminBlogPostsCategoryUpdate'
            ],

            'pages' => [
                'controller' => 'AdminPages'
            ],

            'page-create' => [
                'controller' => 'AdminPageCreate'
            ],

            'page-update' => [
                'controller' => 'AdminPageUpdate'
            ],

            'page-preview' => [
                'controller' => 'AdminPagePreview'
            ],

            'pages-categories' => [
                'controller' => 'AdminPagesCategories'
            ],

            'pages-category-create' => [
                'controller' => 'AdminPagesCategoryCreate'
            ],

            'pages-category-update' => [
                'controller' => 'AdminPagesCategoryUpdate'
            ],

            'plans' => [
                'controller' => 'AdminPlans'
            ],

            'plan-create' => [
                'controller' => 'AdminPlanCreate'
            ],

            'plan-update' => [
                'controller' => 'AdminPlanUpdate'
            ],

            'codes' => [
                'controller' => 'AdminCodes'
            ],

            'code-create' => [
                'controller' => 'AdminCodeCreate'
            ],

            'code-update' => [
                'controller' => 'AdminCodeUpdate'
            ],

            'taxes' => [
                'controller' => 'AdminTaxes'
            ],

            'taxes-import' => [
                'controller' => 'AdminTaxesImport'
            ],

            'tax-create' => [
                'controller' => 'AdminTaxCreate'
            ],

            'tax-update' => [
                'controller' => 'AdminTaxUpdate'
            ],

            'payments' => [
                'controller' => 'AdminPayments'
            ],

            'payment-create' => [
                'controller' => 'AdminPaymentCreate',
            ],

            'affiliates-withdrawals' => [
                'controller' => 'AdminAffiliatesWithdrawals',
            ],

            'statistics' => [
                'controller' => 'AdminStatistics'
            ],

            'plugins' => [
                'controller' => 'AdminPlugins',
            ],

            'languages' => [
                'controller' => 'AdminLanguages'
            ],

            'language-create' => [
                'controller' => 'AdminLanguageCreate'
            ],

            'language-update' => [
                'controller' => 'AdminLanguageUpdate'
            ],

            'settings' => [
                'controller' => 'AdminSettings'
            ],

            'api-documentation' => [
                'controller' => 'AdminApiDocumentation',
            ],

            'teams' => [
                'controller' => 'AdminTeams',
            ],

            'team-members' => [
                'controller' => 'AdminTeamMembers',
            ],

            'logs' => [
                'controller' => 'AdminLogs',
            ],

            'log' => [
                'controller' => 'AdminLog',
            ],

            'log-download' => [
                'controller' => 'AdminLogDownload',
                'settings' => [
                    'has_view' => false,
                ]
            ],

            'broadcasts' => [
                'controller' => 'AdminBroadcasts',
            ],

            'broadcast-view' => [
                'controller' => 'AdminBroadcastView',
            ],

            'broadcast-create' => [
                'controller' => 'AdminBroadcastCreate',
            ],

            'broadcast-update' => [
                'controller' => 'AdminBroadcastUpdate',
            ],

            'broadcast-subscribers' => [
                'controller' => 'AdminBroadcastSubscribers',
            ],

            'internal-notifications' => [
                'controller' => 'AdminInternalNotifications',
            ],

            'internal-notification-create' => [
                'controller' => 'AdminInternalNotificationCreate',
            ],

            'push-subscribers' => [
                'controller' => 'AdminPushSubscribers',
            ],

            'push-notifications' => [
                'controller' => 'AdminPushNotifications',
            ],

            'push-notification-create' => [
                'controller' => 'AdminPushNotificationCreate',
            ],

            'push-notification-update' => [
                'controller' => 'AdminPushNotificationUpdate',
            ],

            'invoice' => [
                'controller' => 'AdminInvoice',
            ],

             'credit-notes' => [
                'controller' => 'AdminCreditNotes',
            ],

            'dynamic-og-images' => [
                'controller' => 'AdminDynamicOgImages',
            ],

            'image-optimizer' => [
                'controller' => 'AdminImageOptimizer',
            ],
        ],

        'admin-api' => [
            'users' => [
                'controller' => 'AdminApiUsers',
                'settings' => [
					'no_browser_language_detection' => true,
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'payments' => [
                'controller' => 'AdminApiPayments',
                'settings' => [
					'no_browser_language_detection' => true,
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'plans' => [
                'controller' => 'AdminApiPlans',
                'settings' => [
					'no_browser_language_detection' => true,
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'sso' => [
                'controller' => 'AdminApiSSO',
                'settings' => [
					'no_browser_language_detection' => true,
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'dynamic-og-images' => [
                'controller' => 'AdminApiDynamicOgImages',
                'settings' => [
					'no_browser_language_detection' => true,
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'domains' => [
                'controller' => 'AdminApiDomains',
                'settings' => [
					'no_browser_language_detection' => true,
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'blog-posts' => [
                'controller' => 'AdminApiBlogPosts',
                'settings' => [
					'no_browser_language_detection' => true,
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'blog-posts-categories' => [
                'controller' => 'AdminApiBlogPostsCategories',
                'settings' => [
					'no_browser_language_detection' => true,
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'pages' => [
                'controller' => 'AdminApiPages',
                'settings' => [
					'no_browser_language_detection' => true,
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'pages-categories' => [
                'controller' => 'AdminApiPagesCategories',
                'settings' => [
					'no_browser_language_detection' => true,
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
        ],
    ];

    public static function parse_url() {

        $params = self::$params;

        if(isset($_GET['altum'])) {
            $params = explode('/', input_clean(rtrim($_GET['altum'], '/')));
        }

        if(php_sapi_name() == 'cli' && isset($_SERVER['argv'])) {
            $params = explode('/', input_clean(rtrim($_SERVER['argv'][1] ?? '', '/')));
            parse_str(implode('&', array_slice($_SERVER['argv'], 2)), $_GET);
        }

        self::$params = $params;

        return $params;

    }

    public static function get_params() {

        return self::$params = array_values(self::$params);
    }

    public static function parse_language() {

        /* Check for potential language set in the first parameter */
        if(!empty(self::$params[0]) && in_array(self::$params[0], Language::$active_languages)) {

            /* Set the language */
            $language_code = input_clean(self::$params[0]);
            Language::set_by_code($language_code);
            self::$language_code = $language_code;

            /* Unset the parameter so that it wont be used further */
            array_shift(self::$params);

        }

    }

    public static function parse_controller() {

        self::$original_request = input_clean(implode('/', self::$params));
        self::$original_request_query = http_build_query(array_diff_key($_GET, array_flip(['altum'])));

        /* Check if the current link accessed is actually the original url or not (multi domain use) */
        $original_url_host = parse_url(url(), PHP_URL_HOST);
        $request_url_host = php_sapi_name() == 'cli' ? null : explode(':', $_SERVER['HTTP_HOST'])[0];

        if($request_url_host && $request_url_host !== $original_url_host) {
            if(function_exists('idn_to_utf8')) {
                $request_url_host = idn_to_utf8($request_url_host);
            }

            /* Make sure the custom domain is attached */
            $domain = (new \Altum\Models\Domain())->get_domain_by_host($request_url_host);

            if($domain && $domain->is_enabled) {
                /* Set some route data */
                self::$data['domain'] = $domain;

                /* Redirect to a custom index url or original index of the main domain */
                if(empty(self::$params[0])) {
                    header('Location: ' . (self::$data['domain']->custom_index_url ?: url()));
                    die();
                }
            }
        }

        /* Reserved prefixes */
        if(!empty(self::$params[0]) && in_array(self::$params[0], ['admin', 'admin-api', 'api']) && $original_url_host == $request_url_host) {
            self::$path = self::$params[0];
            array_shift(self::$params);
        }

        if(!empty(self::$params[0])) {

            if(array_key_exists(self::$params[0], self::$routes[self::$path])) {

                self::$controller_key = self::$params[0];

                unset(self::$params[0]);

            } else {

                /* Check for a custom domain 404 redirect */
                if(isset(self::$data['domain']) && self::$data['domain']->custom_not_found_url) {
                    header('Location: ' . self::$data['domain']->custom_not_found_url);
                    die();
                }

                else {
                    /* Not found controller */
                    self::$path = '';
                    self::$controller_key = 'not-found';
                }
            }

        }

        /* Check for a custom index url redirect in case there is no link requested  */
        if(isset(self::$data['domain']) && !self::$data['domain']->custom_index_url) {
            self::$data['domain']->custom_index_url = url();
        }

        /* Check for a custom index url  */
        if(self::$controller_key == 'index' && $original_url_host != $request_url_host && isset(self::$data['domain']) && self::$data['domain']->custom_index_url) {
            header('Location: ' . self::$data['domain']->custom_index_url);
            die();
        }

        /* Save the current controller */
        if(!isset(self::$routes[self::$path][self::$controller_key])) {
            /* Not found controller */
            self::$path = '';
            self::$controller_key = 'not-found';
        }
        self::$controller = self::$routes[self::$path][self::$controller_key]['controller'];

        if(self::$path == 'admin' && !isset(self::$routes[self::$path][self::$controller_key]['settings'])) {
            self::$routes[self::$path][self::$controller_key]['settings'] = [
                'authentication' => 'admin',
                'allow_team_access' => false,
            ];
        }

        /* Make sure we also save the controller specific settings */
        if(isset(self::$routes[self::$path][self::$controller_key]['settings'])) {
            self::$controller_settings = array_merge(self::$controller_settings, self::$routes[self::$path][self::$controller_key]['settings']);
        }

        return self::$controller;

    }

    public static function get_controller($controller_ame, $path = '') {

        require_once APP_PATH . 'controllers/' . ($path != '' ? $path . '/' : null) . $controller_ame . '.php';

        /* Create a new instance of the controller */
        $class = 'Altum\\Controllers\\' . $controller_ame;

        /* Instantiate the controller class */
        $controller = new $class;

        return $controller;
    }

    public static function parse_method($controller) {

        $method = self::$method;

        /* Start the checks for existing potential methods */
        if(isset(self::get_params()[0])) {

            $original_first_param = self::$params[0];

            /* Try to check the methods with prettier URLs */
            self::$params[0] = str_replace('-', '_', self::$params[0]);

            /* Make sure to check the class method if set in the url */
            if(method_exists($controller, self::get_params()[0])) {
                $reflection = new \ReflectionMethod($controller, self::get_params()[0]);

                if(
                    $reflection->isPublic()
                    && !$reflection->isConstructor()
                    && $reflection->getDeclaringClass()->getName() == get_class($controller)
                    && $reflection->getNumberOfRequiredParameters() == 0
                ) {
                    $method = self::get_params()[0];
                    unset(self::$params[0]);
                }
            }

            /* Restore pretty URL if not used */
            else {
                self::$params[0] = $original_first_param;
            }
        }

        return self::$method = $method;

    }

}
