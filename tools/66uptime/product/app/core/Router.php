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
        'authentication' => null,

        /* Teams */
        'allow_team_access' => null,

		/* Sessions */
		'allow_sessions' => true,
    ];
    public static $method = 'index';
    public static $data = [];

    public static $routes = [
        's' => [
            'status-page' => [
                'controller' => 'StatusPage',
                'settings' => [
                    'no_authentication_check' => true,
                    'no_browser_language_detection' => true,
                    'ads' => true,
                ]
            ],
            'monitor' => [
                'controller' => 'Monitor',
                'settings' => [
                    'no_authentication_check' => true,
                    'no_browser_language_detection' => true,
                    'ads' => true,
                ]
            ],
            'heartbeat' => [
                'controller' => 'Heartbeat',
                'settings' => [
                    'no_authentication_check' => true,
                    'no_browser_language_detection' => true,
                    'ads' => true,
                ]
            ],
        ],

        '' => [
            /* Logged in */
            'dashboard' => [
                'controller' => 'Dashboard',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'webhook-heartbeat' => [
                'controller' => 'WebhookHeartbeat',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_sessions' => false,
                ]
            ],

            'server-monitor-track' => [
                'controller' => 'ServerMonitorTrack',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_sessions' => false,
                ]
            ],

            'server-monitor-code' => [
                'controller' => 'ServerMonitorCode',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                ]
            ],

            'server-monitors' => [
                'controller' => 'ServerMonitors',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'server-monitors-import' => [
                'controller' => 'ServerMonitorsImport',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'server-monitor' => [
                'controller' => 'ServerMonitor',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'server-monitor-create' => [
                'controller' => 'ServerMonitorCreate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'server-monitor-update' => [
                'controller' => 'ServerMonitorUpdate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'monitors' => [
                'controller' => 'Monitors',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'monitors-import' => [
                'controller' => 'MonitorsImport',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'monitor' => [
                'controller' => 'Monitor',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'monitor-log' => [
                'controller' => 'MonitorLog',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'monitor-logs' => [
                'controller' => 'MonitorLogs',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'monitor-create' => [
                'controller' => 'MonitorCreate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'monitor-update' => [
                'controller' => 'MonitorUpdate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'game-servers' => [
                'controller' => 'GameServers',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'game-servers-import' => [
                'controller' => 'GameServersImport',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'game-server' => [
                'controller' => 'GameServer',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'game-server-logs' => [
                'controller' => 'GameServerLogs',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'game-server-create' => [
                'controller' => 'GameServerCreate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'game-server-update' => [
                'controller' => 'GameServerUpdate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'incidents' => [
                'controller' => 'Incidents',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'incident' => [
                'controller' => 'Incident',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'status-pages' => [
                'controller' => 'StatusPages',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'status-page-update' => [
                'controller' => 'StatusPageUpdate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'status-page-create' => [
                'controller' => 'StatusPageCreate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'status-page-statistics' => [
                'controller' => 'StatusPageStatistics',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'status-page-qr' => [
                'controller' => 'StatusPageQr',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'status-page-redirect' => [
                'controller' => 'StatusPageRedirect',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                ]
            ],

            'heartbeats' => [
                'controller' => 'Heartbeats',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'heartbeats-import' => [
                'controller' => 'HeartbeatsImport',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'heartbeat' => [
                'controller' => 'Heartbeat',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'heartbeat-update' => [
                'controller' => 'HeartbeatUpdate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'heartbeat-create' => [
                'controller' => 'HeartbeatCreate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'domain-names' => [
                'controller' => 'DomainNames',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'domain-names-import' => [
                'controller' => 'DomainNamesImport',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'domain-name' => [
                'controller' => 'DomainName',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'domain-name-update' => [
                'controller' => 'DomainNameUpdate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'domain-name-create' => [
                'controller' => 'DomainNameCreate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'dns-monitors' => [
                'controller' => 'DnsMonitors',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'dns-monitors-import' => [
                'controller' => 'DnsMonitorsImport',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'dns-monitor' => [
                'controller' => 'DnsMonitor',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'dns-monitor-logs' => [
                'controller' => 'DnsMonitorLogs',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'dns-monitor-update' => [
                'controller' => 'DnsMonitorUpdate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'dns-monitor-create' => [
                'controller' => 'DnsMonitorCreate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
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

            'projects' => [
                'controller' => 'Projects',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'project-create' => [
                'controller' => 'ProjectCreate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'project-update' => [
                'controller' => 'ProjectUpdate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'tools' => [
                'controller' => 'Tools',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'tools-rating' => [
                'controller' => 'ToolsRating',
                'settings' => [
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_indexing' => false,
                ]
            ],

            'notification-handlers' => [
                'controller' => 'NotificationHandlers',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'notification-handler-create' => [
                'controller' => 'NotificationHandlerCreate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'notification-handler-update' => [
                'controller' => 'NotificationHandlerUpdate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                ]
            ],

            'twiml' => [
                'controller' => 'Twiml',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'no_browser_language_detection' => true,
                    'allow_sessions' => false,
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
                ],
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
                    'allow_team_access' => false,
                ]
            ],

            'account-preferences' => [
                'controller' => 'AccountPreferences',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'allow_team_access' => false,
                ]
            ],

            'account-plan' => [
                'controller' => 'AccountPlan',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'allow_team_access' => false,
                ]
            ],

            'account-redeem-code' => [
                'controller' => 'AccountRedeemCode',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'allow_team_access' => false,
                ]
            ],

            'account-payments' => [
                'controller' => 'AccountPayments',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'allow_team_access' => false,
                ]
            ],

            'account-logs' => [
                'controller' => 'AccountLogs',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'allow_team_access' => false,
                ]
            ],

            'account-api' => [
                'controller' => 'AccountApi',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'allow_team_access' => false,
                ]
            ],

            'account-delete' => [
                'controller' => 'AccountDelete',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'allow_team_access' => false,
                ]
            ],

            'referrals' => [
                'controller' => 'Referrals',
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
                    'allow_team_access' => false,
                    'currency_switcher' => true,
                ]
            ],

            'pay-billing' => [
                'controller' => 'PayBilling',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'allow_team_access' => false,
                    'currency_switcher' => true,
                ]
            ],

            'pay-thank-you' => [
                'controller' => 'PayThankYou',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'allow_team_access' => false,
                    'currency_switcher' => true,
                ]
            ],

            'teams-system' => [
                'controller' => 'TeamsSystem',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                    'allow_team_access' => false,
                ]
            ],

            'teams' => [
                'controller' => 'Teams',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                    'allow_team_access' => false,
                ]
            ],

            'team-create' => [
                'controller' => 'TeamCreate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                    'allow_team_access' => false,
                ]
            ],

            'team-update' => [
                'controller' => 'TeamUpdate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                    'allow_team_access' => false,
                ]
            ],

            'team' => [
                'controller' => 'Team',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                    'allow_team_access' => false,
                ]
            ],

            'teams-members' => [
                'controller' => 'TeamsMembers',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                    'allow_team_access' => false,
                ]
            ],

            'team-member-create' => [
                'controller' => 'TeamMemberCreate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                    'allow_team_access' => false,
                ]
            ],

            'team-member-update' => [
                'controller' => 'TeamMemberUpdate',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                    'allow_team_access' => false,
                ]
            ],

            'teams-member' => [
                'controller' => 'TeamsMember',
                'settings' => [
                    'wrapper' => 'app_wrapper',
                    'ads' => true,
                    'allow_team_access' => false,
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
			'game-servers' => [
				'controller' => 'ApiGameServers',
				'settings' => [
					'no_authentication_check' => true,
					'has_view' => false,
					'allow_indexing' => false,
					'allow_sessions' => false,
				]
			],
            'monitors' => [
                'controller' => 'ApiMonitors',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'dns-monitors' => [
                'controller' => 'ApiDnsMonitors',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'server-monitors' => [
                'controller' => 'ApiServerMonitors',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'heartbeats' => [
                'controller' => 'ApiHeartbeats',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'domain-names' => [
                'controller' => 'ApiDomainNames',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'incidents' => [
                'controller' => 'ApiIncidents',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'status-pages' => [
                'controller' => 'ApiStatusPages',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'statistics' => [
                'controller' => 'ApiStatistics',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'projects' => [
                'controller' => 'ApiProjects',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'domains' => [
                'controller' => 'ApiDomains',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'notification-handlers' => [
                'controller' => 'ApiNotificationHandlers',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            /* Common routes */
            'teams' => [
                'controller' => 'ApiTeams',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'teams-member' => [
                'controller' => 'ApiTeamsMember',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'team-members' => [
                'controller' => 'ApiTeamMembers',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'user' => [
                'controller' => 'ApiUser',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
            'payments' => [
                'controller' => 'ApiPayments',
                'settings' => [
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
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],
        ],

        /* Admin Panel */
        /* Authentication is set by default to 'admin' */
        'admin' => [
			'game-servers' => [
				'controller' => 'AdminGameServers',
			],

            'status-pages' => [
                'controller' => 'AdminStatusPages',
            ],

            'monitors' => [
                'controller' => 'AdminMonitors',
            ],

            'incidents' => [
                'controller' => 'AdminIncidents',
            ],

            'heartbeats' => [
                'controller' => 'AdminHeartbeats',
            ],

            'domain-names' => [
                'controller' => 'AdminDomainNames',
            ],

            'dns-monitors' => [
                'controller' => 'AdminDnsMonitors',
            ],

            'server-monitors' => [
                'controller' => 'AdminServerMonitors',
            ],

            'projects' => [
                'controller' => 'AdminProjects',
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

            'notification-handlers' => [
                'controller' => 'AdminNotificationHandlers',
            ],

            'ping-servers' => [
                'controller' => 'AdminPingServers',
            ],

            'ping-server-create' => [
                'controller' => 'AdminPingServerCreate',
            ],

            'ping-server-update' => [
                'controller' => 'AdminPingServerUpdate',
            ],

            /* Common routes */
            'index' => [
                'controller' => 'AdminIndex',
            ],

            'users' => [
                'controller' => 'AdminUsers',
            ],

            'user-create' => [
                'controller' => 'AdminUserCreate',
            ],

            'user-view' => [
                'controller' => 'AdminUserView',
            ],

            'user-update' => [
                'controller' => 'AdminUserUpdate',
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
                'controller' => 'AdminPlans',
            ],

            'plan-create' => [
                'controller' => 'AdminPlanCreate',
            ],

            'plan-update' => [
                'controller' => 'AdminPlanUpdate',
            ],

            'codes' => [
                'controller' => 'AdminCodes',
            ],

            'code-create' => [
                'controller' => 'AdminCodeCreate',
            ],

            'code-update' => [
                'controller' => 'AdminCodeUpdate',
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
                'controller' => 'AdminTaxUpdate',
            ],

            'affiliates-withdrawals' => [
                'controller' => 'AdminAffiliatesWithdrawals',
            ],

            'payments' => [
                'controller' => 'AdminPayments'
            ],

            'payment-create' => [
                'controller' => 'AdminPaymentCreate',
            ],

            'statistics' => [
                'controller' => 'AdminStatistics',
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
                'controller' => 'AdminSettings',
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
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'payments' => [
                'controller' => 'AdminApiPayments',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'plans' => [
                'controller' => 'AdminApiPlans',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'sso' => [
                'controller' => 'AdminApiSSO',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'dynamic-og-images' => [
                'controller' => 'AdminApiDynamicOgImages',
                'settings' => [
                    'no_authentication_check' => true,
                    'has_view' => false,
                    'allow_indexing' => false,
                    'allow_sessions' => false,
                ]
            ],

            'domains' => [
                'controller' => 'AdminApiDomains',
                'settings' => [
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

        /* Detect custom domain */
        $original_url_host = parse_url(url(), PHP_URL_HOST);
        $request_url_host = php_sapi_name() == 'cli' ? null : explode(':', $_SERVER['HTTP_HOST'])[0];

        if($request_url_host && $request_url_host !== $original_url_host) {
            if(function_exists('idn_to_utf8')) {
                $request_url_host = idn_to_utf8($request_url_host);
            }

            $domain = (new \Altum\Models\Domain())->get_domain_by_host($request_url_host);

            if($domain && $domain->is_enabled) {
                self::$data['domain'] = $domain;
            }
        }

        $is_custom_domain = isset(self::$data['domain']);
        $is_exclusive_domain = $is_custom_domain && self::$data['domain']->status_page_id;

        /* Reserved prefixes */
        $is_protected_path = !empty(self::$params[0]) && in_array(self::$params[0], ['api', 'admin', 'admin-api'], true);

        if($is_protected_path) {
            self::$path = self::$params[0];
            array_shift(self::$params);
        }

        /* Try normal controller */
        if(!$is_exclusive_domain && !empty(self::$params[0]) && isset(self::$routes[self::$path][self::$params[0]]) && file_exists(APP_PATH . 'controllers/' . (self::$path ? self::$path . '/' : '') . self::$routes[self::$path][self::$params[0]]['controller'] . '.php')) {
            self::$controller_key = self::$params[0];
            array_shift(self::$params);
        }

        /* Fallback if to index redirect */
        if(!$is_protected_path && $is_custom_domain && !$is_exclusive_domain && empty(self::$params)) {
            header('Location: ' . (self::$data['domain']->custom_index_url ?: url()));
            die();
        }

        /* Status page routing only if no controller matched and not protected */
        if(
            !$is_protected_path // Make it not work for api, admin, admin api paths..
            && self::$controller_key === 'index' // No other than default controllers found
            && (
                $is_exclusive_domain // Is an exclusive status page domain
                || (!empty(self::$params[0])) // Has at least a slug for the status page
            )
        ) {

            $status_page_slug = null;
            $method = null;
            $id = null;
            $status_page = null;
            $is_not_found = false;

            if($is_exclusive_domain) {

                if(!empty(self::$params)) {
                    if(in_array(self::$params[0], ['monitor', 'heartbeat'], true) && isset(self::$params[1]) && is_numeric(self::$params[1])) {
                        $method = self::$params[0];
                        $id = (int) self::$params[1];
                    }

                    else {
                        /* Not found trigger */
                        $is_not_found = true;
                    }

                }

                $cache_key = 's_status_page?url=' . md5('exclusive') . '&domain_id=' . self::$data['domain']->domain_id;

            } else {

                if(!empty(self::$params)) {

                    /* Get the slug */
                    $status_page_slug = self::$params[0];

                    $total_params = count(self::$params);

                    if($total_params > 1) {
                        /* Easier to assume its not found then prove otherwise */
                        $is_not_found = true;

                        if (isset(self::$params[1], self::$params[2]) && in_array(self::$params[1], ['monitor', 'heartbeat'], true) && isset(self::$params[2]) && is_numeric(self::$params[2])) {
                            $method = self::$params[1];
                            $id = (int) self::$params[2];
                            $is_not_found = false;
                        }
                    }

                } else {
                    /* Not found trigger */
                    $is_not_found = true;
                }

                $cache_key = 's_status_page?url=' . md5($status_page_slug ?? '') . ($is_custom_domain ? '&domain_id=' . self::$data['domain']->domain_id : '');
            }

            if(!$is_not_found && ($status_page_slug || $is_exclusive_domain)) {

                $cache_item = cache()->getItem($cache_key);

                if($cache_item->get()) {
                    $status_page = $cache_item->get();
                } else {

                    if($is_exclusive_domain) {
                        $status_page = db()->where('status_page_id', self::$data['domain']->status_page_id)->getOne('status_pages');
                    } else {
                        $status_page = db()->where('url', $status_page_slug)->where('domain_id', $is_custom_domain ? self::$data['domain']->domain_id : null, $is_custom_domain ? '=' : 'IS')->getOne('status_pages');
                    }

                    if($status_page) {
                        cache()->save($cache_item->set($status_page)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('status_page_id=' . $status_page->status_page_id));
                    }
                }
            }

            if($status_page) {

                if($is_exclusive_domain) {
                    $status_page->full_url = self::$data['domain']->scheme . self::$data['domain']->host . '/';
                } elseif($is_custom_domain) {
                    $status_page->full_url = self::$data['domain']->scheme . self::$data['domain']->host . '/' . $status_page_slug . '/';
                } else {
                    $status_page->full_url = SITE_URL . $status_page_slug . '/';
                }

                self::$path = 's';
                self::$data['status_page'] = $status_page;

                if($method) {
                    self::$controller_key = $method;
                    self::$controller = ucfirst($method);
                    self::$data['method'] = $method;
                    self::$data['id'] = (int) $id;
                } else {
                    self::$controller_key = 'status-page';
                    self::$controller = 'StatusPage';
                }

                return self::$controller;
            }

            if($is_custom_domain) {
                header('Location: ' . (self::$data['domain']->custom_not_found_url ?: url('not-found')));
                die();
            }
        }

        /* 404 detection */
        if(self::$controller_key === 'index' && !empty(self::$params) && isset(self::$routes[self::$path][self::$params[0]]) && file_exists(APP_PATH . 'controllers/' . (self::$path ? self::$path . '/' : '') . self::$routes[self::$path][self::$params[0]]['controller'] . '.php')) {
            self::$path = '';
            self::$controller_key = 'not-found';
        }

        /* Save the current controller */
        if(!isset(self::$routes[self::$path][self::$controller_key])) {
            /* Not found controller */
            self::$path = '';
            self::$controller_key = 'not-found';
        }
        self::$controller = self::$routes[self::$path][self::$controller_key]['controller'];

        /* Admin path */
        if(self::$path == 'admin' && !isset(self::$routes[self::$path][self::$controller_key]['settings'])) {
            self::$routes[self::$path][self::$controller_key]['settings'] = [
                'authentication' => 'admin',
                'allow_team_access' => false,
            ];
        }

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

                /* Make sure the method is not private */
                $reflection = new \ReflectionMethod($controller, self::get_params()[0]);
                if($reflection->isPublic()) {
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
