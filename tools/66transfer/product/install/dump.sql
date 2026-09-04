CREATE TABLE `users` (
  `user_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(320) NOT NULL,
  `password` varchar(128) DEFAULT NULL,
  `name` varchar(64) NOT NULL,
  `avatar` varchar(40) DEFAULT NULL,
  `billing` text,
  `api_key` varchar(32) DEFAULT NULL,
  `token_code` varchar(32) DEFAULT NULL,
  `twofa_secret` varchar(32) DEFAULT NULL,
  `anti_phishing_code` varchar(8) DEFAULT NULL,
  `one_time_login_code` varchar(32) DEFAULT NULL,
  `pending_email` varchar(128) DEFAULT NULL,
  `email_activation_code` varchar(32) DEFAULT NULL,
  `lost_password_code` varchar(32) DEFAULT NULL,
  `type` tinyint NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '0',
  `is_newsletter_subscribed` tinyint NOT NULL DEFAULT '0',
  `has_pending_internal_notifications` tinyint NOT NULL DEFAULT '0',
  `plan_id` varchar(16) NOT NULL DEFAULT '',
  `plan_expiration_date` datetime DEFAULT NULL,
  `plan_settings` text,
  `plan_trial_done` tinyint DEFAULT '0',
  `plan_expiry_reminder` tinyint DEFAULT '0',
  `payment_subscription_id` varchar(64) DEFAULT NULL,
  `payment_processor` varchar(16) DEFAULT NULL,
  `payment_total_amount` float DEFAULT NULL,
  `payment_currency` varchar(4) DEFAULT NULL,
  `referral_key` varchar(32) DEFAULT NULL,
  `referred_by` varchar(32) DEFAULT NULL,
  `referred_by_has_converted` tinyint DEFAULT '0',
  `language` varchar(32) DEFAULT 'english',
  `currency` varchar(4) DEFAULT NULL,
  `timezone` varchar(32) DEFAULT 'UTC',
  `preferences` text,
  `extra` text,
  `datetime` datetime DEFAULT NULL,
  `next_cleanup_datetime` datetime DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(64) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `continent_code` ENUM('AF', 'AN', 'AS', 'EU', 'NA', 'OC', 'SA') DEFAULT NULL,
  `country` varchar(8) DEFAULT NULL,
  `city_name` varchar(32) DEFAULT NULL,
  `device_type` enum('mobile', 'tablet', 'desktop') DEFAULT NULL,
  `browser_language` varchar(32) DEFAULT NULL,
  `browser_name` varchar(32) DEFAULT NULL,
  `os_name` varchar(16) DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `total_logins` int DEFAULT '0',
  `user_deletion_reminder` tinyint(4) DEFAULT '0',
  `source` varchar(32) DEFAULT 'direct',
  `total_files_size` bigint unsigned DEFAULT '0',
  `total_files` bigint unsigned DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `plan_id` (`plan_id`),
  KEY `api_key` (`api_key`),
  KEY `idx_users_next_cleanup_datetime` (`next_cleanup_datetime`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

INSERT INTO `users` (`user_id`, `email`, `password`, `api_key`, `referral_key`, `name`, `type`, `status`, `plan_id`, `plan_expiration_date`, `plan_settings`, `datetime`, `ip`, `last_activity`, `preferences`)
VALUES (1,'admin','$2y$10$uFNO0pQKEHSFcus1zSFlveiPCB3EvG9ZlES7XKgJFTAl5JbRGFCWy', md5(rand()), md5(rand()), 'AltumCode',1,1,'custom','2030-01-01 12:00:00', '{"storage_size_limit":50000,"transfers_limit":-1,"transfer_requests_limit":-1,"transfer_size_limit":1000,"files_per_transfer_limit":10,"downloads_per_transfer_limit":-1,"transfers_retention":-1,"projects_limit":-1,"pixels_limit":-1,"domains_limit":-1,"teams_limit":-1,"team_members_limit":-1,"statistics_retention":365,"additional_domains_is_enabled":true,"analytics_is_enabled":true,"qr_is_enabled":true,"custom_url_is_enabled":true,"password_protection_is_enabled":true,"file_encryption_is_enabled":true,"removable_branding_is_enabled":true,"custom_css_is_enabled":true,"custom_js_is_enabled":true,"api_is_enabled":true,"affiliate_commission_percentage":0,"no_ads":true,"notification_handlers_email_limit":5,"notification_handlers_webhook_limit":5,"notification_handlers_slack_limit":5,"notification_handlers_discord_limit":5,"notification_handlers_telegram_limit":5}', NOW(),'',NOW(), '{"default_results_per_page":100,"default_order_type":"DESC","transfers_default_order_by":"transfer_id","transfer_requests_default_order_by":"transfer_request_id","transfer_requests_default_expiration_datetime":0,"transfer_requests_default_pixels_ids":[],"transfer_requests_default_project_id":0,"transfer_requests_default_is_removed_branding":true,"transfer_requests_default_custom_css":"","transfer_requests_default_custom_js":"","transfer_requests_default_submission_notification_handlers_ids":[],"transfer_requests_default_pageview_notification_handlers_ids":[],"transfer_requests_auto_file_upload":false,"transfer_requests_auto_submission_create":false,"transfer_requests_auto_copy_link":true,"dashboard":{"transfer_requests":true}}');

-- SEPARATOR --

CREATE TABLE `users_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `type` varchar(64) DEFAULT NULL,
  `ip` varchar(64) DEFAULT NULL,
  `device_type` enum('mobile', 'tablet', 'desktop') DEFAULT NULL,
  `os_name` varchar(16) DEFAULT NULL,
  `continent_code` ENUM('AF', 'AN', 'AS', 'EU', 'NA', 'OC', 'SA') DEFAULT NULL,
  `country_code` varchar(8) DEFAULT NULL,
  `city_name` varchar(32) DEFAULT NULL,
  `browser_language` varchar(32) DEFAULT NULL,
  `browser_name` varchar(32) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_logs_user_id` (`user_id`),
  KEY `users_logs_ip_type_datetime_index` (`ip`,`type`,`datetime`),
  KEY `users_logs_datetime_index` (`datetime`),
  CONSTRAINT `users_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

CREATE TABLE `plans` (
  `plan_id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `description` varchar(256) NOT NULL DEFAULT '',
  `translations` text NOT NULL,
  `prices` text NOT NULL,
  `trial_days` int unsigned NOT NULL DEFAULT '0',
  `settings` longtext NOT NULL,
  `additional_settings` text NULL,
  `taxes_ids` text,
  `color` varchar(16) DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `order` int(10) unsigned DEFAULT '0',
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEPARATOR --

CREATE TABLE `pages_categories` (
`pages_category_id` bigint unsigned NOT NULL AUTO_INCREMENT,
`url` varchar(256) NOT NULL,
`title` varchar(256) NOT NULL DEFAULT '',
`description` varchar(256) DEFAULT NULL,
`icon` varchar(32) DEFAULT NULL,
`order` int NOT NULL DEFAULT '0',
`language` varchar(32) DEFAULT NULL,
`datetime` datetime DEFAULT NULL,
`last_datetime` datetime DEFAULT NULL,
PRIMARY KEY (`pages_category_id`),
KEY `url` (`url`),
KEY `pages_categories_url_language_index` (`url`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- SEPARATOR --

CREATE TABLE `pages` (
  `page_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pages_category_id` bigint unsigned DEFAULT NULL,
  `footer_category_id` varchar(64) DEFAULT NULL,
  `plans_ids` text DEFAULT NULL,
  `url` varchar(256) NOT NULL,
  `title` varchar(256) NOT NULL DEFAULT '',
  `description` varchar(256) DEFAULT NULL,
  `icon` varchar(32) DEFAULT NULL,
  `keywords` varchar(256) CHARACTER SET utf8mb4 DEFAULT NULL,
  `editor` varchar(16) DEFAULT NULL,
  `content` longtext,
  `type` enum('internal', 'external', 'feature') DEFAULT NULL,
  `position` varchar(16) NOT NULL DEFAULT '',
  `language` varchar(32) DEFAULT NULL,
  `open_in_new_tab` tinyint DEFAULT '1',
  `order` int DEFAULT '0',
  `total_views` bigint unsigned DEFAULT '0',
  `is_published` tinyint DEFAULT '1',
  `datetime` datetime DEFAULT NULL,
  `last_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`page_id`),
  KEY `pages_pages_category_id_index` (`pages_category_id`),
  KEY `pages_url_index` (`url`),
  KEY `pages_is_published_index` (`is_published`),
  KEY `pages_language_index` (`language`),
  CONSTRAINT `pages_ibfk_1` FOREIGN KEY (`pages_category_id`) REFERENCES `pages_categories` (`pages_category_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

INSERT INTO `pages` (`pages_category_id`, `url`, `title`, `description`, `content`, `type`, `position`, `order`, `total_views`, `datetime`, `last_datetime`) VALUES
(NULL, 'https://altumcode.com/', 'Software by AltumCode', '', '', 'external', 'bottom', 1, 0, NOW(), NOW()),
(NULL, 'https://altumco.de/66transfer', 'Built with 66transfer', '', '', 'external', 'bottom', 0, 0, NOW(), NOW());

-- SEPARATOR --

INSERT INTO `pages` (`pages_category_id`, `footer_category_id`, `plans_ids`, `url`, `title`, `description`, `icon`, `keywords`, `editor`, `content`, `type`, `position`, `language`, `open_in_new_tab`, `order`, `total_views`, `is_published`, `datetime`, `last_datetime`) VALUES
(NULL, 'resources', NULL, 'blog', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 2, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'affiliate', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 4, 0, 1, NOW(), NULL),
(NULL, 'resources', NULL, 'contact', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 3, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'cookie_consent', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 5, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'push_notifications', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 6, 0, 1, NOW(), NULL),
(NULL, 'resources', NULL, 'pages', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 1, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'api-documentation', '', NULL, '', NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 3, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'plan', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 2, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'dashboard', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 1, 0, 1, NOW(), NULL);

-- SEPARATOR --

CREATE TABLE `blog_posts_categories` (
`blog_posts_category_id` bigint unsigned NOT NULL AUTO_INCREMENT,
`url` varchar(256) NOT NULL,
`title` varchar(256) NOT NULL DEFAULT '',
`description` varchar(256) DEFAULT NULL,
`order` int NOT NULL DEFAULT '0',
`language` varchar(32) DEFAULT NULL,
`datetime` datetime DEFAULT NULL,
`last_datetime` datetime DEFAULT NULL,
PRIMARY KEY (`blog_posts_category_id`),
KEY `url` (`url`),
KEY `blog_posts_categories_url_language_index` (`url`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- SEPARATOR --

CREATE TABLE `blog_posts` (
`blog_post_id` bigint unsigned NOT NULL AUTO_INCREMENT,
`blog_posts_category_id` bigint unsigned DEFAULT NULL,
`url` varchar(256) NOT NULL,
`title` varchar(256) NOT NULL DEFAULT '',
`description` varchar(256) DEFAULT NULL,
`image_description` varchar(256) DEFAULT NULL,
`keywords` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
`image` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
`editor` varchar(16) DEFAULT NULL,
`content` longtext,
`language` varchar(32) DEFAULT NULL,
`total_views` bigint unsigned DEFAULT '0',
`average_rating` float unsigned NOT NULL DEFAULT 0,
`total_ratings` bigint unsigned NOT NULL DEFAULT 0,
`is_published` tinyint DEFAULT '1',
`datetime` datetime DEFAULT NULL,
`last_datetime` datetime DEFAULT NULL,
PRIMARY KEY (`blog_post_id`),
KEY `blog_post_id_index` (`blog_post_id`),
KEY `blog_post_url_index` (`url`),
KEY `blog_posts_category_id` (`blog_posts_category_id`),
KEY `blog_posts_is_published_index` (`is_published`),
KEY `blog_posts_language_index` (`language`),
CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`blog_posts_category_id`) REFERENCES `blog_posts_categories` (`blog_posts_category_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

CREATE TABLE `blog_posts_ratings` (
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
`blog_post_id` bigint unsigned DEFAULT NULL,
`user_id` bigint unsigned DEFAULT NULL,
`ip_binary` varbinary(16) DEFAULT NULL,
`rating` tinyint(1) DEFAULT NULL,
`datetime` datetime DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `blog_posts_ratings_blog_post_id_ip_binary_idx` (`blog_post_id`,`ip_binary`) USING BTREE,
KEY `user_id` (`user_id`),
CONSTRAINT `blog_posts_ratings_ibfk_1` FOREIGN KEY (`blog_post_id`) REFERENCES `blog_posts` (`blog_post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `blog_posts_ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

CREATE TABLE `broadcasts` (
`broadcast_id` bigint unsigned NOT NULL AUTO_INCREMENT,
`name` varchar(64) DEFAULT NULL,
`subject` varchar(128) DEFAULT NULL,
`content` text,
`segment` varchar(64) DEFAULT NULL,
`settings` text COLLATE utf8mb4_unicode_ci,
`users_ids` longtext CHARACTER SET utf8mb4,
`sent_users_ids` longtext,
`sent_emails` int unsigned DEFAULT '0',
`total_emails` int unsigned DEFAULT '0',
`status` varchar(16) DEFAULT NULL,
`views` bigint unsigned DEFAULT '0',
`clicks` bigint unsigned DEFAULT '0',
`last_sent_email_datetime` datetime DEFAULT NULL,
`datetime` datetime DEFAULT NULL,
`last_datetime` datetime DEFAULT NULL,
PRIMARY KEY (`broadcast_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

CREATE TABLE `broadcasts_statistics` (
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
`user_id` bigint unsigned DEFAULT NULL,
`broadcast_id` bigint unsigned DEFAULT NULL,
`type` enum('view', 'click') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
`target` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
`datetime` datetime DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `broadcast_id` (`broadcast_id`),
KEY `broadcasts_statistics_user_id_broadcast_id_type_index` (`broadcast_id`,`user_id`,`type`),
CONSTRAINT `broadcasts_statistics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `broadcasts_statistics_ibfk_2` FOREIGN KEY (`broadcast_id`) REFERENCES `broadcasts` (`broadcast_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

CREATE TABLE `internal_notifications` (
  `internal_notification_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `for_who` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_who` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint unsigned DEFAULT '0',
  `datetime` datetime DEFAULT NULL,
  `read_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`internal_notification_id`),
  KEY `user_id` (`user_id`),
  KEY `users_notifications_for_who_idx` (`for_who`) USING BTREE,
  KEY `internal_notifications_datetime_index` (`datetime`),
  CONSTRAINT `internal_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

CREATE TABLE `settings` (
`id` int NOT NULL AUTO_INCREMENT,
`key` varchar(64) NOT NULL DEFAULT '',
`value` longtext NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

SET @cron_key = MD5(RAND());

-- SEPARATOR --

INSERT INTO `settings` (`key`, `value`)
VALUES
('main', '{"title":"Your title","default_language":"english","default_theme_style":"light","default_timezone":"UTC","index_url":"","terms_and_conditions_url":"","privacy_policy_url":"","not_found_url":"","ai_scraping_is_allowed":true,"se_indexing":true,"display_index_plans":true,"display_index_testimonials":true,"display_index_faq":true,"display_index_latest_blog_posts":true,"default_results_per_page":100,"default_order_type":"DESC","auto_language_detection_is_enabled":true,"blog_is_enabled":false,"api_is_enabled":true,"theme_style_change_is_enabled":true,"logo_light":"","logo_dark":"","logo_email":"","opengraph":"","favicon":"","openai_api_key":"","openai_model":"gpt-4o","force_https_is_enabled":false,"broadcasts_statistics_is_enabled":true,"breadcrumbs_is_enabled":true,"display_pagination_when_no_pages":false,"chart_cache":12,"chart_days":30}'),
('languages', '{"english":{"status":"active"}}'),
('users', '{"email_confirmation":false,"welcome_email_is_enabled":false,"register_is_enabled":true,"register_only_social_logins":false,"register_social_login_require_password":false,"register_display_newsletter_checkbox":false,"login_rememberme_checkbox_is_checked":true,"login_rememberme_cookie_days":90,"auto_delete_unconfirmed_users":3,"auto_delete_inactive_users":30,"user_deletion_reminder":0,"blacklisted_domains":[],"blacklisted_countries":[],"login_lockout_is_enabled":true,"login_lockout_max_retries":3,"login_lockout_time":10,"lost_password_lockout_is_enabled":true,"lost_password_lockout_max_retries":3,"lost_password_lockout_time":10,"resend_activation_lockout_is_enabled":true,"resend_activation_lockout_max_retries":3,"resend_activation_lockout_time":10,"register_lockout_is_enabled":true,"register_lockout_max_registrations":3,"register_lockout_time":10}'),
('ads', '{"header":"","footer":"","header_transfers":"","footer_transfers":""}'),
('captcha', '{"type":"basic","recaptcha_public_key":"","recaptcha_private_key":"","login_is_enabled":0,"register_is_enabled":0,"lost_password_is_enabled":0,"resend_activation_is_enabled":0,"transfer_request_create_is_enabled":false,"transfer_request_upload_is_enabled":false}'),
('cron', concat('{\"key\":\"', @cron_key, '\",\"transfer_requests_cleanup_datetime\":\"\"}')),
('email_notifications', '{"emails":"","new_user":false,"delete_user":false,"new_payment":false,"new_domain":false,"new_affiliate_withdrawal":false,"contact":false}'),
('internal_notifications', '{"users_is_enabled":true,"admins_is_enabled":true,"new_user":true,"delete_user":true,"new_newsletter_subscriber":true,"new_payment":true,"new_affiliate_withdrawal":true}'),
('content', '{"blog_is_enabled":true,"blog_share_is_enabled":true,"blog_search_widget_is_enabled":false,"blog_categories_widget_is_enabled":true,"blog_popular_widget_is_enabled":true,"blog_views_is_enabled":true,"pages_is_enabled":true,"pages_share_is_enabled":true,"pages_popular_widget_is_enabled":true,"pages_views_is_enabled":true}'),
('sso', '{"is_enabled":true,"display_menu_items":true,"websites":{}}'),
('facebook', '{"is_enabled":false,"app_id":"","app_secret":""}'),
('google', '{"is_enabled":false,"client_id":"","client_secret":""}'),
('twitter', '{"is_enabled":false,"consumer_api_key":"","consumer_api_secret":""}'),
('discord', '{"is_enabled":false,"client_id":"","client_secret":""}'),
('linkedin', '{"is_enabled":false,"client_id":"","client_secret":""}'),
('microsoft', '{"is_enabled":false,"client_id":"","client_secret":""}'),
('plan_custom', '{"plan_id":"custom","name":"Custom","description":"Contact us for enterprise pricing.","price":"Custom","custom_button_url":"mailto:sample@example.com","color":null,"status":2,"settings":{}}'),
('plan_free', '{"plan_id":"free","name":"Free","description":"Free, forever","price":"Free","color":"#135ac4","status":1,"settings":{"storage_size_limit":500,"transfers_limit":50,"transfer_requests_limit":50,"transfer_size_limit":50,"files_per_transfer_limit":5,"downloads_per_transfer_limit":10,"transfers_retention":10,"projects_limit":5,"pixels_limit":5,"domains_limit":5,"teams_limit":1,"team_members_limit":3,"statistics_retention":180,"additional_domains_is_enabled":true,"analytics_is_enabled":true,"qr_is_enabled":true,"custom_url_is_enabled":true,"password_protection_is_enabled":true,"file_encryption_is_enabled":true,"removable_branding_is_enabled":true,"custom_css_is_enabled":false,"custom_js_is_enabled":false,"api_is_enabled":true,"affiliate_commission_percentage":10,"no_ads":true,"notification_handlers_email_limit":1,"notification_handlers_webhook_limit":1,"notification_handlers_slack_limit":0,"notification_handlers_discord_limit":0,"notification_handlers_telegram_limit":0}}'),
('plan_guest', '{"plan_id":"guest","name":"Guest","description":"Free for everyone","price":"No account","color":null,"status":1,"settings":{"storage_size_limit":-1,"transfers_limit":-1,"transfer_requests_limit":-1,"transfer_size_limit":5,"files_per_transfer_limit":3,"downloads_per_transfer_limit":3,"transfers_retention":3,"projects_limit":0,"pixels_limit":0,"domains_limit":0,"teams_limit":0,"team_members_limit":0,"statistics_retention":0,"additional_domains_is_enabled":true,"analytics_is_enabled":false,"qr_is_enabled":false,"custom_url_is_enabled":true,"password_protection_is_enabled":true,"file_encryption_is_enabled":false,"removable_branding_is_enabled":false,"custom_css_is_enabled":false,"custom_js_is_enabled":false,"api_is_enabled":false,"affiliate_commission_percentage":0,"no_ads":false,"notification_handlers_email_limit":0,"notification_handlers_webhook_limit":0,"notification_handlers_slack_limit":0,"notification_handlers_discord_limit":0,"notification_handlers_telegram_limit":0}}'),
('payment', '{"is_enabled":false,"type":"both","default_payment_frequency":"monthly","currencies":{"USD":{"code":"USD","symbol":"$","default_payment_processor":"offline_payment"}},"default_currency":"USD","codes_is_enabled":true,"taxes_and_billing_is_enabled":true,"invoice_is_enabled":true,"user_plan_expiry_reminder":0,"user_plan_expiry_checker_is_enabled":0,"currency_exchange_api_key":""}'),
('paypal', '{\"is_enabled\":\"0\",\"mode\":\"sandbox\",\"client_id\":\"\",\"secret\":\"\"}'),
('stripe', '{\"is_enabled\":\"0\",\"publishable_key\":\"\",\"secret_key\":\"\",\"webhook_secret\":\"\"}'),
('offline_payment', '{\"is_enabled\":\"0\",\"instructions\":\"Your offline payment instructions go here..\"}'),
('coinbase', '{"is_enabled":false,"api_key":"","webhook_secret":"","currencies":["USD"]}'),
('payu', '{"is_enabled":false,"mode":"sandbox","merchant_pos_id":"","signature_key":"","oauth_client_id":"","oauth_client_secret":"","currencies":["USD"]}'),
('iyzico', '{"is_enabled":false,"mode":"live","api_key":"","secret_key":"","currencies":["USD"]}'),
('paystack', '{"is_enabled":false,"public_key":"","secret_key":"","currencies":["USD"]}'),
('razorpay', '{"is_enabled":false,"key_id":"","key_secret":"","webhook_secret":"","currencies":["USD"]}'),
('mollie', '{"is_enabled":false,"api_key":""}'),
('myfatoorah', '{}'),
('yookassa', '{"is_enabled":false,"shop_id":"","secret_key":""}'),
('crypto_com', '{"is_enabled":false,"publishable_key":"","secret_key":"","webhook_secret":""}'),
('paddle', '{"is_enabled":false,"mode":"sandbox","vendor_id":"","api_key":"","public_key":"","currencies":["USD"]}'),
('paddle_billing', '{"is_enabled":1,"mode":"sandbox","api_key":"","secret_key":"","client_side_token":"","currencies":["USD"]}'),
('mercadopago', '{"is_enabled":false,"access_token":"","currencies":["USD"]}'),
('midtrans', '{"is_enabled":false,"server_key":"","mode":"sandbox","currencies":["USD"]}'),
('flutterwave', '{"is_enabled":false,"secret_key":"","currencies":["USD"]}'),
('smtp', '{"from_name":"AltumCode","from":"","reply_to_name":"","reply_to":"","cc":"","bcc":"","host":"","encryption":"tls","port":"","auth":0,"username":"","password":"","display_socials":false,"company_details":""}'),
('lemonsqueezy', '{"is_enabled":false,"api_key":"","signing_secret":"","store_id":"","one_time_monthly_variant_id":"","one_time_annual_variant_id":"","one_time_lifetime_variant_id":"","recurring_monthly_variant_id":"","recurring_annual_variant_id":"","currencies":["USD"]}'),
('klarna', '{"is_enabled":1,"mode":"https:\/\/api.playground.klarna.com\/","username":"","password":"","currencies":["USD"]}'),
('plisio', '{\"is_enabled\":false,\"secret_key\":\"\",\"accepted_cryptocurrencies\":[\"DOGE\",\"SOL\",\"ETH\",\"BTC\"],\"default_cryptocurrency\":\"SOL\",\"currencies\":[\"USD\"]}'),
('plisio_whitelabel', '{\"is_enabled\":false,\"secret_key\":\"\",\"accepted_cryptocurrencies\":[\"DOGE\",\"SOL\",\"ETH\",\"BTC\"],\"default_cryptocurrency\":\"SOL\",\"currencies\":[\"USD\"]}'),
('revolut', '{\"is_enabled\":false,\"mode\":\"sandbox\",\"secret_key\":\"\",\"webhook_id\":\"\",\"currencies\":[\"USD\"]}'),
('theme', '{"light_is_enabled": false, "dark_is_enabled": false}'),
('custom', '{"body_content":"","head_js":"","head_css":"","head_js_transfers":"","head_css_transfers":"","body_content_transfers":""}'),
('socials', '{"threads":"","youtube":"","facebook":"","x":"","instagram":"","tiktok":"","linkedin":"","whatsapp":"","email":""}'),
('announcements', '{"guests_is_enabled":0,"guests_id":"035cc337f6de075434bc24807b7ad9af","guests_content":"","guests_text_color":"#000000","guests_background_color":"#000000","users_is_enabled":0,"users_id":"035cc337f6de075434bc24807b7ad9af","users_content":"","users_text_color":"#000000","users_background_color":"#000000","translations":{"english":{"guests_content":"","users_content":""}}}'),
('business', '{\"invoice_is_enabled\":\"0\",\"name\":\"\",\"address\":\"\",\"city\":\"\",\"county\":\"\",\"zip\":\"\",\"country\":\"\",\"email\":\"\",\"phone\":\"\",\"tax_type\":\"\",\"tax_id\":\"\",\"custom_key_one\":\"\",\"custom_value_one\":\"\",\"custom_key_two\":\"\",\"custom_value_two\":\"\"}'),
('webhooks', '{"user_new":"","user_delete":"","payment_new":"","code_redeemed":"","contact":"","cron_start":"","cron_end":"","domain_new":"","domain_update":""}'),
('affiliate', '{\"is_enabled\":\"0\",\"commission_type\":\"forever\",\"minimum_withdrawal_amount\":\"1\",\"commission_percentage\":\"25\",\"withdrawal_notes\":\"\"}'),
('cookie_consent', '{"is_enabled":false,"logging_is_enabled":false,"necessary_is_enabled":true,"analytics_is_enabled":true,"targeting_is_enabled":true,"layout":"bar","position_y":"middle","position_x":"center"}'),
('transfers', '{"branding":"Download page by 66transfer","email_transfer_is_enabled":true,"transfer_requests_is_enabled":true,"blacklisted_file_extensions":"","chunk_size_limit":1,"domains_is_enabled":true,"additional_domains_is_enabled":true,"main_domain_is_enabled":true,"blacklisted_domains":[],"blacklisted_keywords":[],"twilio_notifications_is_enabled":false,"twilio_sid":"","twilio_token":"","twilio_number":""}'),
('notification_handlers', '{"twilio_sid":"","twilio_token":"","twilio_number":"","whatsapp_number_id":"","whatsapp_access_token":"","email_is_enabled":true,"webhook_is_enabled":true,"slack_is_enabled":true,"discord_is_enabled":true,"telegram_is_enabled":true,"microsoft_teams_is_enabled":true,"twilio_is_enabled":false,"twilio_call_is_enabled":false,"whatsapp_is_enabled":false}'),
('custom_images', '{}'),
('license', '{"license":"babia.to","type":"regular"}'),
('support', '{}'),
('product_info', '{"version":"39.0.0", "code":"3900"}');

-- SEPARATOR --

CREATE TABLE `domains` (
`domain_id` bigint unsigned NOT NULL AUTO_INCREMENT,
`user_id` bigint unsigned DEFAULT NULL,
`scheme` varchar(8) NOT NULL DEFAULT '',
`host` varchar(128) NOT NULL DEFAULT '',
`custom_index_url` varchar(256) DEFAULT NULL,
`custom_not_found_url` varchar(256) DEFAULT NULL,
`type` tinyint DEFAULT '1',
`is_enabled` tinyint DEFAULT '0',
`datetime` datetime DEFAULT NULL,
`last_datetime` datetime DEFAULT NULL,
PRIMARY KEY (`domain_id`),
KEY `user_id` (`user_id`),
KEY `domains_host_index` (`host`),
KEY `domains_type_index` (`type`),
CONSTRAINT `domains_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

CREATE TABLE `projects` (
`project_id` bigint unsigned NOT NULL AUTO_INCREMENT,
`user_id` bigint unsigned DEFAULT NULL,
`name` varchar(128) DEFAULT NULL,
`color` varchar(16) DEFAULT '#000',
`last_datetime` datetime DEFAULT NULL,
`datetime` datetime DEFAULT NULL,
PRIMARY KEY (`project_id`),
KEY `user_id` (`user_id`),
CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEPARATOR --

CREATE TABLE `notification_handlers` (
`notification_handler_id` bigint unsigned NOT NULL AUTO_INCREMENT,
`user_id` bigint unsigned DEFAULT NULL,
`type` varchar(32) DEFAULT NULL,
`name` varchar(128) DEFAULT NULL,
`settings` text,
`is_enabled` tinyint NOT NULL DEFAULT '1',
`last_datetime` datetime DEFAULT NULL,
`datetime` datetime NOT NULL,
PRIMARY KEY (`notification_handler_id`),
KEY `user_id` (`user_id`),
CONSTRAINT `notification_handlers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

CREATE TABLE `pixels` (
`pixel_id` bigint unsigned NOT NULL AUTO_INCREMENT,
`user_id` bigint unsigned NOT NULL,
`type` varchar(64) NOT NULL,
`name` varchar(64) NOT NULL,
`pixel` varchar(64) NOT NULL,
`last_datetime` datetime DEFAULT NULL,
`datetime` datetime NOT NULL,
PRIMARY KEY (`pixel_id`),
KEY `user_id` (`user_id`),
CONSTRAINT `pixels_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

CREATE TABLE `transfers` (
`transfer_id` bigint unsigned NOT NULL AUTO_INCREMENT,
`domain_id` bigint unsigned DEFAULT NULL,
`project_id` bigint unsigned DEFAULT NULL,
`uploader_id` varchar(32) DEFAULT NULL,
`user_id` bigint unsigned DEFAULT NULL,
`pixels_ids` text,
`name` varchar(64) DEFAULT NULL,
`description` varchar(256) DEFAULT NULL,
`type` enum('link', 'email') DEFAULT NULL,
`email_to` varchar(320) DEFAULT NULL,
`url` varchar(128) DEFAULT NULL,
`settings` text,
`notifications` text,
`total_files` int unsigned DEFAULT '0',
`total_size` bigint unsigned DEFAULT '0',
`pageviews` bigint unsigned DEFAULT '0',
`downloads` bigint unsigned DEFAULT '0',
`downloads_limit` bigint unsigned DEFAULT NULL,
`expiration_datetime` datetime DEFAULT NULL,
`last_datetime` datetime DEFAULT NULL,
`datetime` datetime DEFAULT NULL,
PRIMARY KEY (`transfer_id`),
KEY `user_id` (`user_id`),
KEY `domain_id` (`domain_id`),
KEY `project_id` (`project_id`),
KEY `idx_user_id_datetime` (`user_id`, `datetime`),
KEY `transfers_uploader_id_idx` (`uploader_id`) USING BTREE,
CONSTRAINT `transfers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `transfers_ibfk_2` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`domain_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `transfers_ibfk_3` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

CREATE TABLE `transfer_requests` (
`transfer_request_id` bigint unsigned NOT NULL AUTO_INCREMENT,
`domain_id` bigint unsigned DEFAULT NULL,
`project_id` bigint unsigned DEFAULT NULL,
`uploader_id` varchar(32) DEFAULT NULL,
`user_id` bigint unsigned DEFAULT NULL,
`pixels_ids` text,
`name` varchar(64) DEFAULT NULL,
`description` varchar(256) DEFAULT NULL,
`url` varchar(128) DEFAULT NULL,
`settings` text,
`notifications` text,
`total_submissions` int unsigned DEFAULT '0',
`total_files` int unsigned DEFAULT '0',
`total_size` bigint unsigned DEFAULT '0',
`pageviews` bigint unsigned DEFAULT '0',
`is_enabled` tinyint unsigned DEFAULT '1',
`expiration_datetime` datetime DEFAULT NULL,
`last_submission_datetime` datetime DEFAULT NULL,
`last_submitted_datetime` datetime DEFAULT NULL,
`last_datetime` datetime DEFAULT NULL,
`datetime` datetime DEFAULT NULL,
PRIMARY KEY (`transfer_request_id`),
KEY `user_id` (`user_id`),
KEY `domain_id` (`domain_id`),
KEY `project_id` (`project_id`),
KEY `idx_user_id_datetime` (`user_id`, `datetime`),
KEY `transfer_requests_uploader_id_idx` (`uploader_id`) USING BTREE,
KEY `transfer_requests_url_domain_id_idx` (`url`, `domain_id`) USING BTREE,
KEY `transfer_requests_expiration_datetime_idx` (`expiration_datetime`) USING BTREE,
CONSTRAINT `transfer_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `transfer_requests_ibfk_2` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`domain_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `transfer_requests_ibfk_3` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

CREATE TABLE `requests_submissions` (
`request_submission_id` bigint unsigned NOT NULL AUTO_INCREMENT,
`transfer_request_id` bigint unsigned NOT NULL,
`uploader_id` varchar(32) DEFAULT NULL,
`total_files` int unsigned DEFAULT '0',
`total_size` bigint unsigned DEFAULT '0',
`continent_code` ENUM('AF', 'AN', 'AS', 'EU', 'NA', 'OC', 'SA') DEFAULT NULL,
`country_code` varchar(8) DEFAULT NULL,
`os_name` varchar(16) DEFAULT NULL,
`city_name` varchar(128) DEFAULT NULL,
`browser_name` varchar(32) DEFAULT NULL,
`referrer_host` varchar(256) DEFAULT NULL,
`referrer_path` varchar(1024) DEFAULT NULL,
`device_type` enum('mobile', 'tablet', 'desktop') DEFAULT NULL,
`browser_language` varchar(16) DEFAULT NULL,
`utm_source` varchar(128) DEFAULT NULL,
`utm_medium` varchar(128) DEFAULT NULL,
`utm_campaign` varchar(128) DEFAULT NULL,
`is_unique` tinyint DEFAULT '0',
`datetime` datetime NOT NULL,
PRIMARY KEY (`request_submission_id`),
KEY `datetime` (`datetime`),
KEY `transfer_request_id` (`transfer_request_id`) USING BTREE,
KEY `idx_transfer_request_id_datetime` (`transfer_request_id`, `datetime`),
CONSTRAINT `requests_submissions_ibfk_1` FOREIGN KEY (`transfer_request_id`) REFERENCES `transfer_requests` (`transfer_request_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

CREATE TABLE `files` (
  `file_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `file_uuid` binary(16) DEFAULT NULL,
  `offload_id` varchar(64) DEFAULT NULL,
  `uploader_id` varchar(32) DEFAULT NULL,
  `transfer_id` bigint unsigned DEFAULT NULL,
  `transfer_request_id` bigint unsigned DEFAULT NULL,
  `request_submission_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `name` varchar(80) DEFAULT NULL,
  `original_name` varchar(300) DEFAULT NULL,
  `size` bigint unsigned DEFAULT NULL,
  `status` varchar(32) DEFAULT NULL,
  `is_encrypted` tinyint unsigned DEFAULT '0',
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`file_id`),
  KEY `files_file_uuid_idx` (`file_uuid`) USING BTREE,
  KEY `transfer_id` (`transfer_id`),
  KEY `files_transfer_request_id_idx` (`transfer_request_id`),
  KEY `files_request_submission_id_idx` (`request_submission_id`),
  KEY `user_id` (`user_id`),
  KEY `files_datetime_index` (`datetime`),
  CONSTRAINT `files_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `transfers` (`transfer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `files_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `files_transfer_requests_transfer_request_id_fk` FOREIGN KEY (`transfer_request_id`) REFERENCES `transfer_requests` (`transfer_request_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

CREATE TABLE `statistics` (
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
`transfer_id` bigint unsigned DEFAULT NULL,
`transfer_request_id` bigint unsigned DEFAULT NULL,
`user_id` bigint unsigned DEFAULT NULL,
`project_id` bigint unsigned DEFAULT NULL,
`continent_code` ENUM('AF', 'AN', 'AS', 'EU', 'NA', 'OC', 'SA') DEFAULT NULL,
`country_code` varchar(8) DEFAULT NULL,
`os_name` varchar(16) DEFAULT NULL,
`city_name` varchar(128) DEFAULT NULL,
`browser_name` varchar(32) DEFAULT NULL,
`referrer_host` varchar(256) DEFAULT NULL,
`referrer_path` varchar(1024) DEFAULT NULL,
`device_type` enum('mobile', 'tablet', 'desktop') DEFAULT NULL,
`browser_language` varchar(16) DEFAULT NULL,
`utm_source` varchar(128) DEFAULT NULL,
`utm_medium` varchar(128) DEFAULT NULL,
`utm_campaign` varchar(128) DEFAULT NULL,
`is_unique` tinyint(4) DEFAULT '0',
`datetime` datetime NOT NULL,
PRIMARY KEY (`id`),
KEY `datetime` (`datetime`),
KEY `transfer_id` (`transfer_id`) USING BTREE,
KEY `statistics_transfer_request_id_idx` (`transfer_request_id`),
KEY `idx_transfer_request_id_datetime` (`transfer_request_id`, `datetime`),
KEY `statistics_users_user_id_fk` (`user_id`),
KEY `idx_user_id_datetime` (`user_id`, `datetime`),
CONSTRAINT `statistics_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `transfers` (`transfer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `statistics_users_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `statistics_transfer_requests_transfer_request_id_fk` FOREIGN KEY (`transfer_request_id`) REFERENCES `transfer_requests` (`transfer_request_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEPARATOR --

CREATE TABLE `downloads` (
`download_id` bigint unsigned NOT NULL AUTO_INCREMENT,
`transfer_id` bigint unsigned DEFAULT NULL,
`user_id` bigint unsigned DEFAULT NULL,
`continent_code` ENUM('AF', 'AN', 'AS', 'EU', 'NA', 'OC', 'SA') DEFAULT NULL,
`country_code` varchar(8) DEFAULT NULL,
`os_name` varchar(16) DEFAULT NULL,
`city_name` varchar(128) DEFAULT NULL,
`browser_name` varchar(32) DEFAULT NULL,
`referrer_host` varchar(256) DEFAULT NULL,
`referrer_path` varchar(1024) DEFAULT NULL,
`device_type` enum('mobile', 'tablet', 'desktop') DEFAULT NULL,
`browser_language` varchar(16) DEFAULT NULL,
`utm_source` varchar(128) DEFAULT NULL,
`utm_medium` varchar(128) DEFAULT NULL,
`utm_campaign` varchar(128) DEFAULT NULL,
`is_unique` tinyint DEFAULT '0',
`datetime` datetime NOT NULL,
PRIMARY KEY (`download_id`),
KEY `datetime` (`datetime`),
KEY `transfer_id` (`transfer_id`) USING BTREE,
CONSTRAINT `downloads_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `transfers` (`transfer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `downloads_users_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
