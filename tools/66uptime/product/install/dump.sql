-- NULLED BY LOSTKOREAN - BABIA.TO --

CREATE TABLE `users` (
  `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `is_newsletter_subscribed` tinyint(4) NOT NULL DEFAULT '0',
  `has_pending_internal_notifications` tinyint(4) NOT NULL DEFAULT '0',
  `plan_id` varchar(16) NOT NULL DEFAULT '',
  `plan_expiration_date` datetime DEFAULT NULL,
  `plan_settings` longtext NULL,
  `plan_trial_done` tinyint(4) DEFAULT '0',
  `plan_expiry_reminder` tinyint(4) DEFAULT '0',
  `payment_subscription_id` varchar(64) DEFAULT NULL,
  `payment_processor` varchar(16) DEFAULT NULL,
  `payment_total_amount` float DEFAULT NULL,
  `payment_currency` varchar(4) DEFAULT NULL,
  `referral_key` varchar(32) DEFAULT NULL,
  `referred_by` varchar(32) DEFAULT NULL,
  `referred_by_has_converted` tinyint(4) DEFAULT '0',
  `language` varchar(32) DEFAULT 'english',
  `currency` varchar(4) DEFAULT NULL,
  `timezone` varchar(32) DEFAULT 'UTC',
  `preferences` text,
  `extra` text NULL,
  `datetime` datetime DEFAULT NULL,
  `next_cleanup_datetime` datetime DEFAULT CURRENT_TIMESTAMP NULL,
  `next_logs_cleanup_datetime` datetime DEFAULT NULL,
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
  `total_logins` int(11) DEFAULT '0',
  `user_deletion_reminder` tinyint(4) DEFAULT '0',
  `source` varchar(32) DEFAULT 'direct',
  PRIMARY KEY (`user_id`),
  KEY `plan_id` (`plan_id`),
  KEY `api_key` (`api_key`),
  KEY `idx_users_next_cleanup_datetime` (`next_cleanup_datetime`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `blog_posts_categories` (
  `blog_posts_category_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `url` varchar(256) NOT NULL,
  `title` varchar(256) NOT NULL DEFAULT '',
  `description` varchar(256) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  `language` varchar(32) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `last_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`blog_posts_category_id`),
  KEY `url` (`url`),
  KEY `blog_posts_categories_url_language_index` (`url`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `broadcasts` (
  `broadcast_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `subject` varchar(128) DEFAULT NULL,
  `content` text,
  `segment` varchar(64) DEFAULT NULL,
  `settings` text,
  `users_ids` longtext CHARACTER SET utf8mb4,
  `sent_users_ids` longtext,
  `sent_emails` int(10) UNSIGNED DEFAULT '0',
  `total_emails` int(10) UNSIGNED DEFAULT '0',
  `status` varchar(16) DEFAULT NULL,
  `views` bigint(20) UNSIGNED DEFAULT '0',
  `clicks` bigint(20) UNSIGNED DEFAULT '0',
  `last_sent_email_datetime` datetime DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `last_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`broadcast_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `status_pages` (
  `status_page_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `domain_id` bigint(20) UNSIGNED DEFAULT NULL,
  `monitors_ids` text,
  `heartbeats_ids` text DEFAULT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `url` varchar(128) DEFAULT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  `settings` text,
  `socials` text,
  `logo` varchar(40) DEFAULT NULL,
  `favicon` varchar(40) DEFAULT NULL,
  `opengraph` varchar(40) DEFAULT NULL,
  `password` varchar(128) DEFAULT NULL,
  `timezone` varchar(32) DEFAULT NULL,
  `theme` varchar(16) DEFAULT NULL,
  `custom_js` text,
  `custom_css` text,
  `pageviews` bigint(20) UNSIGNED DEFAULT '0',
  `is_se_visible` tinyint(4) UNSIGNED DEFAULT '1',
  `is_removed_branding` tinyint(4) UNSIGNED DEFAULT '0',
  `is_enabled` tinyint(4) UNSIGNED DEFAULT '1',
  `last_datetime` datetime DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`status_page_id`),
  KEY `user_id` (`user_id`),
  KEY `domain_id` (`domain_id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

INSERT INTO `users` (`user_id`, `email`, `password`, `name`, `api_key`, `type`, `status`, `plan_id`, `plan_expiration_date`, `plan_settings`, `referral_key`, `datetime`, `ip`, `last_activity`) VALUES
(1, 'admin', '$2y$10$uFNO0pQKEHSFcus1zSFlveiPCB3EvG9ZlES7XKgJFTAl5JbRGFCWy', 'AltumCode', md5(rand()), 1, 1, 'custom', '2030-01-01 12:00:00', '{"monitors_limit":-1,"monitors_check_intervals":["60","180","300","600","1800","3600","21600","43200","86400"],"heartbeats_limit":-1,"domain_names_limit":-1,"status_pages_limit":-1,"projects_limit":-1,"domains_limit":-1,"dns_monitors_limit":-1,"dns_monitors_check_intervals":["300","600","1800","3600","21600","43200","86400"],"server_monitors_limit":-1,"server_monitors_check_intervals":["60","300","600","900","1800"],"teams_limit":2,"team_members_limit":2,"logs_retention":-1,"statistics_retention":-1,"additional_domains":[],"analytics_is_enabled":true,"qr_is_enabled":true,"removable_branding_is_enabled":true,"custom_url_is_enabled":true,"password_protection_is_enabled":true,"search_engine_block_is_enabled":true,"custom_css_is_enabled":true,"custom_js_is_enabled":true,"email_reports_is_enabled":true,"api_is_enabled":true,"affiliate_commission_percentage":0,"no_ads":true,"notification_handlers_email_limit":-1,"notification_handlers_webhook_limit":-1,"notification_handlers_slack_limit":-1,"notification_handlers_discord_limit":-1,"notification_handlers_telegram_limit":-1,"notification_handlers_microsoft_teams_limit":-1}', md5(rand()), NOW(), '', NOW());

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `domains` (
  `domain_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status_page_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `scheme` varchar(8) NOT NULL DEFAULT '',
  `host` varchar(256) NOT NULL DEFAULT '',
  `custom_index_url` varchar(256) DEFAULT NULL,
  `custom_not_found_url` varchar(256) DEFAULT NULL,
  `type` tinyint(4) DEFAULT '1',
  `is_enabled` tinyint(4) DEFAULT '0',
  `datetime` datetime DEFAULT NULL,
  `last_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`domain_id`),
  KEY `user_id` (`user_id`),
  KEY `domains_host_index` (`host`),
  KEY `domains_type_index` (`type`),
  KEY `domains_ibfk_2` (`status_page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `pages_categories` (
  `pages_category_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `url` varchar(256) NOT NULL,
  `title` varchar(256) NOT NULL DEFAULT '',
  `description` varchar(256) DEFAULT NULL,
  `icon` varchar(32) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  `language` varchar(32) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `last_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`pages_category_id`),
  KEY `url` (`url`),
  KEY `pages_categories_url_language_index` (`url`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `ping_servers` (
  `ping_server_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `url` varchar(1024) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `country_code` varchar(8) DEFAULT NULL,
  `city_name` varchar(64) DEFAULT NULL,
  `api_key` varchar(128) DEFAULT NULL,
  `is_enabled` tinyint(4) DEFAULT NULL,
  `last_datetime` datetime DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`ping_server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

INSERT INTO `ping_servers` (`ping_server_id`, `url`, `name`, `country_code`, `city_name`, `api_key`, `is_enabled`, `last_datetime`, `datetime`) VALUES
(1, '', 'Default', 'US', 'New-York', NULL, 1, NULL, NOW());

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `plans` (
  `plan_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `description` varchar(256) NOT NULL DEFAULT '',
  `translations` text NULL,
  `prices` text NOT NULL,
  `trial_days` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `settings` longtext NOT NULL,
  `additional_settings` text NULL,
  `taxes_ids` text,
  `color` varchar(16) DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `order` int(10) UNSIGNED DEFAULT '0',
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(64) NOT NULL DEFAULT '',
  `value` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

SET @cron_key = MD5(RAND());

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

INSERT INTO `settings` (`key`, `value`) VALUES
('main', '{"title":"Your title","default_language":"english","default_theme_style":"light","default_timezone":"UTC","index_url":"","terms_and_conditions_url":"","privacy_policy_url":"","not_found_url":"","se_indexing":true,"display_index_plans":true,"default_results_per_page":25,"default_order_type":"DESC","auto_language_detection_is_enabled":true,"blog_is_enabled":true,"api_is_enabled":true,"logo_light":"","logo_dark":"","logo_email":"","opengraph":"","favicon":""}'),
('languages', '{"english":{"status":"active"}}'),
('users', '{"email_confirmation":false,"welcome_email_is_enabled":false,"register_is_enabled":true,"register_only_social_logins":false,"register_display_newsletter_checkbox":false,"auto_delete_unconfirmed_users":30,"auto_delete_inactive_users":90,"user_deletion_reminder":0,"blacklisted_domains":[],"blacklisted_countries":[],"login_lockout_is_enabled":true,"login_lockout_max_retries":3,"login_lockout_time":60,"lost_password_lockout_is_enabled":true,"lost_password_lockout_max_retries":3,"lost_password_lockout_time":60,"resend_activation_lockout_is_enabled":true,"resend_activation_lockout_max_retries":3,"resend_activation_lockout_time":60,"register_lockout_is_enabled":true,"register_lockout_max_registrations":3,"register_lockout_time":10}'),
('ads', '{"header":"","footer":""}'),
('captcha', '{"type":"basic","recaptcha_public_key":"","recaptcha_private_key":"","login_is_enabled":0,"register_is_enabled":0,"lost_password_is_enabled":0,"resend_activation_is_enabled":0}'),
('cron', concat('{"key":"', @cron_key, '"}')),
('email_notifications', '{"emails":"","new_user":false,"delete_user":false,"new_payment":false,"new_domain":false,"new_affiliate_withdrawal":false,"contact":false}'),
('internal_notifications', '{}'),
('content', '{"blog_is_enabled":true,"blog_share_is_enabled":true,"blog_categories_widget_is_enabled":true,"blog_popular_widget_is_enabled":true,"blog_views_is_enabled":true,"pages_is_enabled":true,"pages_share_is_enabled":true,"pages_popular_widget_is_enabled":true,"pages_views_is_enabled":true}'),
('sso', '{\"is_enabled\":\"0\"}'),
('facebook', '{"is_enabled":"0","app_id":"","app_secret":""}'),
('google', '{"is_enabled":"0","client_id":"","client_secret":""}'),
('twitter', '{"is_enabled":"0","consumer_api_key":"","consumer_api_secret":""}'),
('discord', '{"is_enabled":"0"}'),
('linkedin', '{"is_enabled":"0"}'),
('microsoft', '{"is_enabled":"0"}'),
('plan_custom', '{"plan_id":"custom","name":"Custom","status":1}'),
('plan_free', '{"plan_id":"free","name":"Free","days":null,"status":1,"settings":{"monitors_limit":-1,"monitors_check_intervals":["60","180","300","600","1800","3600","21600","43200","86400"],"heartbeats_limit":-1,"domain_names_limit":-1,"status_pages_limit":-1,"projects_limit":-1,"domains_limit":-1,"teams_limit":2,"team_members_limit":2,"logs_retention":-1,"statistics_retention":-1,"additional_domains":[],"analytics_is_enabled":true,"qr_is_enabled":true,"removable_branding_is_enabled":true,"custom_url_is_enabled":true,"password_protection_is_enabled":true,"search_engine_block_is_enabled":true,"custom_css_is_enabled":true,"custom_js_is_enabled":true,"email_reports_is_enabled":true,"api_is_enabled":true,"affiliate_commission_percentage":0,"no_ads":true,"notification_handlers_email_limit":-1,"notification_handlers_webhook_limit":-1,"notification_handlers_slack_limit":-1,"notification_handlers_discord_limit":-1,"notification_handlers_telegram_limit":-1}}'),
('payment', '{"is_enabled":"0","type":"both","brand_name":":)","currency":"USD","codes_is_enabled":"1"}'),
('paypal', '{"is_enabled":"0","mode":"sandbox","client_id":"","secret":""}'),
('stripe', '{"is_enabled":"0","publishable_key":"","secret_key":"","webhook_secret":""}'),
('offline_payment', '{"is_enabled":"0","instructions":"Your offline payment instructions go here.."}'),
('coinbase', '{"is_enabled":"0"}'),
('payu', '{"is_enabled":"0"}'),
('iyzico', '{\"is_enabled\":\"0\"}'),
('paystack', '{"is_enabled":"0"}'),
('razorpay', '{"is_enabled":"0"}'),
('mollie', '{"is_enabled":"0"}'),
('myfatoorah', '{}'),
('yookassa', '{"is_enabled":"0"}'),
('crypto_com', '{"is_enabled":"0"}'),
('paddle', '{"is_enabled":"0"}'),
('paddle_billing', '{"is_enabled":1,"mode":"sandbox","api_key":"","secret_key":"","client_side_token":"","currencies":["USD"]}'),
('mercadopago', '{"is_enabled":"0"}'),
('midtrans', '{\"is_enabled\":\"0\"}'),
('flutterwave', '{\"is_enabled\":\"0\"}'),
('lemonsqueezy', '{"is_enabled":false,"api_key":"","signing_secret":"","store_id":"","one_time_monthly_variant_id":"","one_time_annual_variant_id":"","one_time_lifetime_variant_id":"","recurring_monthly_variant_id":"","recurring_annual_variant_id":"","currencies":["USD"]}'),
('klarna', '{"is_enabled":1,"mode":"https:\/\/api.playground.klarna.com\/","username":"","password":"","currencies":["USD"]}'),
('plisio', '{\"is_enabled\":false,\"secret_key\":\"\",\"accepted_cryptocurrencies\":[\"DOGE\",\"SOL\",\"ETH\",\"BTC\"],\"default_cryptocurrency\":\"SOL\",\"currencies\":[\"USD\"]}'),
('plisio_whitelabel', '{\"is_enabled\":false,\"secret_key\":\"\",\"accepted_cryptocurrencies\":[\"DOGE\",\"SOL\",\"ETH\",\"BTC\"],\"default_cryptocurrency\":\"SOL\",\"currencies\":[\"USD\"]}'),
('revolut', '{\"is_enabled\":false,\"mode\":\"sandbox\",\"secret_key\":\"\",\"webhook_id\":\"\",\"currencies\":[\"USD\"]}'),
('smtp', '{"host":"","from":"","from_name":"","encryption":"tls","port":"587","auth":"1","username":"","password":""}'),
('theme', '{}'),
('custom', '{"head_js":"","head_css":""}'),
('socials', '{"youtube":"","facebook":"","twitter":"","instagram":"","tiktok":"","linkedin":"","whatsapp":"","email":""}'),
('announcements', '{"guests_id":"16e2fdd0e771da32ec9e557c491fe17d","guests_content":"","guests_text_color":"#ffffff","guests_background_color":"#000000","users_id":"16e2fdd0e771da32ec9e557c491fe17d","users_content":"","users_text_color":"#dbebff","users_background_color":"#000000"}'),
('business', '{"invoice_is_enabled":"0","name":"","address":"","city":"","county":"","zip":"","country":"","email":"","phone":"","tax_type":"","tax_id":"","custom_key_one":"","custom_value_one":"","custom_key_two":"","custom_value_two":""}'),
('webhooks', '{"user_new":"","user_delete":"","payment_new":"","code_redeemed":"","contact":""}'),
('status_pages', '{"blacklisted_domains":[],"blacklisted_keywords":[],"domains_is_enabled":"0","additional_domains_is_enabled":"0","main_domain_is_enabled":"1","logo_size_limit":"2","favicon_size_limit":"2"}'),
('monitors_heartbeats', '{"monitors_is_enabled": true,"dns_monitors_is_enabled": true,"server_monitors_is_enabled": true,"heartbeats_is_enabled": true,"domain_names_is_enabled": true,"email_reports_is_enabled": "0","monitors_ping_method": "exec","twilio_notifications_is_enabled": "0","twilio_sid": "","twilio_token": "","twilio_number": ""}'),
('tools', '{}'),
('affiliate', '{"is_enabled":"0","commission_type":"forever","minimum_withdrawal_amount":"1","commission_percentage":"25","withdrawal_notes":""}'),
('cookie_consent', '{"is_enabled":false,"logging_is_enabled":false,"necessary_is_enabled":true,"analytics_is_enabled":true,"targeting_is_enabled":true,"layout":"bar","position_y":"middle","position_x":"center"}'),
('notification_handlers', '{"twilio_sid":"","twilio_token":"","twilio_number":"","whatsapp_number_id":"","whatsapp_access_token":"","email_is_enabled":true,"webhook_is_enabled":true,"slack_is_enabled":true,"discord_is_enabled":true,"telegram_is_enabled":true,"microsoft_teams_is_enabled":true,"twilio_is_enabled":false,"twilio_call_is_enabled":false,"whatsapp_is_enabled":false}'),
('custom_images', '{}'),
('license', '{"license": "Nulled by LostKorean - babia.to", "type": "regular"}'),
('support', '{"key": "Nulled by LostKorean - babia.to", "expiry_datetime": "2100-01-01 00:00:00"}'),
('product_info', '{"version":"55.0.0", "code":"5500"}');

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `users_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
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
-- NULLED BY LOSTKOREAN - BABIA.TO --

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
-- NULLED BY LOSTKOREAN - BABIA.TO --

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
-- Nulled by LostKorean - babia.to --

CREATE TABLE `broadcasts_statistics` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `broadcast_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('view', 'click') DEFAULT NULL,
  `target` varchar(2048) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `broadcast_id` (`broadcast_id`),
  KEY `broadcasts_statistics_user_id_broadcast_id_type_index` (`broadcast_id`,`user_id`,`type`),
  KEY `broadcasts_statistics_ibfk_1` (`user_id`),
  CONSTRAINT `broadcasts_statistics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `broadcasts_statistics_ibfk_2` FOREIGN KEY (`broadcast_id`) REFERENCES `broadcasts` (`broadcast_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `projects` (
  `project_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `color` varchar(16) DEFAULT '#000',
  `last_datetime` datetime DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`project_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

ALTER TABLE `status_pages`
  ADD CONSTRAINT `status_pages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `status_pages_ibfk_2` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`domain_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `status_pages_ibfk_3` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

ALTER TABLE `domains`
  ADD CONSTRAINT `domains_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `domains_status_pages_status_page_id_fk` FOREIGN KEY (`status_page_id`) REFERENCES `status_pages` (`status_page_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `domain_names` (
  `domain_name_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target` varchar(256) NOT NULL,
  `ssl_port` int UNSIGNED DEFAULT 443 NULL,
  `whois` text,
  `whois_notifications` text,
  `ssl` text,
  `ssl_notifications` text,
  `total_checks` bigint(20) UNSIGNED DEFAULT '0',
  `last_check_datetime` datetime DEFAULT NULL,
  `next_check_datetime` datetime DEFAULT NULL,
  `is_enabled` tinyint(4) DEFAULT '1',
  `last_datetime` datetime DEFAULT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`domain_name_id`),
  KEY `project_id` (`project_id`),
  KEY `user_id` (`user_id`),
  KEY `domain_names_is_enabled_next_check_datetime_index` (`is_enabled`,`next_check_datetime`),
  CONSTRAINT `domain_names_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `domain_names_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `monitors` (
  `monitor_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `ping_servers_ids` text,
  `incident_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(36) DEFAULT NULL,
  `target` varchar(1024) DEFAULT NULL,
  `port` int(11) DEFAULT NULL,
  `settings` text,
  `details` text,
  `is_ok` tinyint(4) DEFAULT '1',
  `uptime` float DEFAULT '100',
  `uptime_seconds` int(10) UNSIGNED DEFAULT '0',
  `downtime` float DEFAULT '0',
  `downtime_seconds` int(10) UNSIGNED DEFAULT '0',
  `average_response_time` float DEFAULT NULL,
  `total_checks` bigint(20) UNSIGNED DEFAULT '0',
  `total_ok_checks` bigint(20) UNSIGNED DEFAULT NULL,
  `total_not_ok_checks` bigint(20) UNSIGNED DEFAULT '0',
  `last_check_datetime` datetime DEFAULT NULL,
  `next_check_datetime` datetime DEFAULT NULL,
  `main_ok_datetime` datetime DEFAULT NULL,
  `last_ok_datetime` datetime DEFAULT NULL,
  `main_not_ok_datetime` datetime DEFAULT NULL,
  `last_not_ok_datetime` datetime DEFAULT NULL,
  `notifications` text,
  `last_logs` text NULL,
  `email_reports_is_enabled` tinyint(4) DEFAULT '0',
  `email_reports_last_datetime` datetime DEFAULT NULL,
  `is_enabled` tinyint(4) NOT NULL DEFAULT '1',
  `last_datetime` datetime DEFAULT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`monitor_id`),
  KEY `user_id` (`user_id`),
  KEY `project_id` (`project_id`),
  KEY `monitor_incident_id` (`incident_id`),
  KEY `monitors_is_enabled_next_check_datetime_index` (`is_enabled`,`next_check_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `server_monitors` (
  `server_monitor_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `settings` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notifications` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uptime` int(11) DEFAULT NULL,
  `network_total_download` int(11) DEFAULT NULL,
  `network_download` int(11) DEFAULT NULL,
  `network_total_upload` int(11) DEFAULT NULL,
  `network_upload` int(11) DEFAULT NULL,
  `os_name` text DEFAULT NULL,
  `os_version` text DEFAULT NULL,
  `kernel_name` text DEFAULT NULL,
  `kernel_version` varchar(256) DEFAULT NULL,
  `kernel_release` text DEFAULT NULL,
  `cpu_architecture` text DEFAULT NULL,
  `cpu_usage` float DEFAULT NULL,
  `cpu_model` text DEFAULT NULL,
  `cpu_cores` int(11) DEFAULT NULL,
  `cpu_frequency` int(11) DEFAULT NULL,
  `ram_usage` float DEFAULT NULL,
  `ram_used` int(11) DEFAULT NULL,
  `ram_total` int(11) DEFAULT NULL,
  `disk_usage` float DEFAULT NULL,
  `disk_used` int(11) DEFAULT NULL,
  `disk_total` int(11) DEFAULT NULL,
  `cpu_load_1` float DEFAULT NULL,
  `cpu_load_5` float DEFAULT NULL,
  `cpu_load_15` float DEFAULT NULL,
  `total_logs` bigint(20) UNSIGNED DEFAULT 0,
  `is_enabled` tinyint(4) DEFAULT 1,
  `last_log_datetime` datetime DEFAULT NULL,
  `last_datetime` datetime DEFAULT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`server_monitor_id`),
  KEY `project_id` (`project_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `server_monitors_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `server_monitors_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `server_monitors_logs` (
  `server_monitor_log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `server_monitor_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cpu_usage` float DEFAULT NULL,
  `ram_usage` float DEFAULT NULL,
  `disk_usage` float DEFAULT NULL,
  `cpu_load_1` float DEFAULT NULL,
  `cpu_load_5` float DEFAULT NULL,
  `cpu_load_15` float DEFAULT NULL,
  `network_download` int DEFAULT NULL,
  `network_upload` int DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`server_monitor_log_id`),
  KEY `heartbeat_id` (`server_monitor_id`),
  KEY `user_id` (`user_id`),
  KEY `server_monitors_logs_server_monitor_id_datetime_index` (`server_monitor_id`,`datetime`),
  KEY `idx_user_id_datetime` (`user_id`, `datetime`),
  CONSTRAINT `server_monitors_logs_ibfk_1` FOREIGN KEY (`server_monitor_id`) REFERENCES `server_monitors` (`server_monitor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `server_monitors_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `heartbeats` (
  `heartbeat_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `incident_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(32) DEFAULT NULL,
  `settings` text,
  `last_logs` text NULL,
  `is_ok` tinyint(4) DEFAULT '1',
  `uptime` float DEFAULT '100',
  `uptime_seconds` int(10) UNSIGNED DEFAULT '0',
  `downtime` float DEFAULT '0',
  `downtime_seconds` int(10) UNSIGNED DEFAULT '0',
  `total_runs` bigint(20) UNSIGNED DEFAULT '0',
  `total_missed_runs` bigint(20) UNSIGNED DEFAULT '0',
  `main_run_datetime` datetime DEFAULT NULL,
  `last_run_datetime` datetime DEFAULT NULL,
  `next_run_datetime` datetime DEFAULT NULL,
  `main_missed_datetime` datetime DEFAULT NULL,
  `last_missed_datetime` datetime DEFAULT NULL,
  `notifications` text,
  `email_reports_is_enabled` tinyint(4) DEFAULT '0',
  `email_reports_last_datetime` datetime DEFAULT NULL,
  `is_enabled` tinyint(4) NOT NULL DEFAULT '1',
  `last_datetime` datetime DEFAULT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`heartbeat_id`),
  KEY `user_id` (`user_id`),
  KEY `project_id` (`project_id`),
  KEY `monitor_incident_id` (`incident_id`),
  KEY `heartbeats_code_idx` (`code`) USING BTREE,
  KEY `heartbeats_is_enabled_next_check_datetime_index` (`is_enabled`,`next_run_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `email_reports` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `monitor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `heartbeat_id` bigint(20) UNSIGNED DEFAULT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `datetime` (`datetime`),
  KEY `monitor_id` (`monitor_id`),
  KEY `heartbeat_id` (`heartbeat_id`),
  CONSTRAINT `email_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `email_reports_ibfk_2` FOREIGN KEY (`monitor_id`) REFERENCES `monitors` (`monitor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `email_reports_ibfk_3` FOREIGN KEY (`heartbeat_id`) REFERENCES `heartbeats` (`heartbeat_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `heartbeats_logs` (
  `heartbeat_log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `heartbeat_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_ok` tinyint(4) UNSIGNED DEFAULT '1',
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`heartbeat_log_id`),
  KEY `heartbeat_id` (`heartbeat_id`),
  KEY `user_id` (`user_id`),
  KEY `heartbeats_logs_heartbeat_id_datetime_index` (`heartbeat_id`,`datetime`),
  KEY `idx_user_id_datetime` (`user_id`, `datetime`),
  CONSTRAINT `heartbeats_logs_ibfk_1` FOREIGN KEY (`heartbeat_id`) REFERENCES `heartbeats` (`heartbeat_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `heartbeats_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `internal_notifications` (
  `internal_notification_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `for_who` varchar(16) DEFAULT NULL,
  `from_who` varchar(16) DEFAULT NULL,
  `icon` varchar(64) DEFAULT NULL,
  `title` varchar(128) DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `url` varchar(512) DEFAULT NULL,
  `is_read` tinyint(3) UNSIGNED DEFAULT '0',
  `datetime` datetime DEFAULT NULL,
  `read_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`internal_notification_id`),
  KEY `user_id` (`user_id`),
  KEY `users_notifications_for_who_idx` (`for_who`) USING BTREE,
  KEY `internal_notifications_datetime_index` (`datetime`),
  CONSTRAINT `internal_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `monitors_logs` (
  `monitor_log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `monitor_id` bigint(20) UNSIGNED NOT NULL,
  `ping_server_id` bigint(20) UNSIGNED DEFAULT '1',
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_ok` tinyint(3) UNSIGNED DEFAULT NULL,
  `response_time` float DEFAULT '0',
  `response_status_code` int(10) UNSIGNED DEFAULT NULL,
  `response_body` longtext NULL,
  `error` text,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`monitor_log_id`),
  KEY `monitor_id` (`monitor_id`),
  KEY `user_id` (`user_id`),
  KEY `monitors_logs_monitor_id_datetime_index` (`monitor_id`,`datetime`),
  KEY `ping_server_id` (`ping_server_id`),
  KEY `idx_user_id_datetime` (`user_id`, `datetime`),
  CONSTRAINT `monitors_logs_ibfk_1` FOREIGN KEY (`monitor_id`) REFERENCES `monitors` (`monitor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `monitors_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `monitors_logs_ibfk_4` FOREIGN KEY (`ping_server_id`) REFERENCES `ping_servers` (`ping_server_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `dns_monitors` (
  `dns_monitor_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target` varchar(256) NOT NULL,
  `settings` text,
  `notifications` text,
  `dns` text,
  `total_checks` bigint UNSIGNED DEFAULT '0',
  `total_changes` bigint UNSIGNED DEFAULT '0',
  `total_dns_types_found` bigint UNSIGNED DEFAULT '0',
  `total_dns_records_found` bigint UNSIGNED DEFAULT '0',
  `last_check_datetime` datetime DEFAULT NULL,
  `next_check_datetime` datetime DEFAULT NULL,
  `last_change_datetime` datetime DEFAULT NULL,
  `is_enabled` tinyint DEFAULT '1',
  `last_datetime` datetime DEFAULT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`dns_monitor_id`),
  KEY `project_id` (`project_id`),
  KEY `user_id` (`user_id`),
  KEY `dns_monitors_is_enabled_next_check_datetime_index` (`is_enabled`,`next_check_datetime`),
  CONSTRAINT `dns_monitors_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dns_monitors_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `dns_monitors_logs` (
  `dns_monitor_log_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `dns_monitor_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `dns` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `dns_changes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `total_dns_records_found` bigint UNSIGNED DEFAULT NULL,
  `total_dns_types_found` bigint UNSIGNED DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`dns_monitor_log_id`),
  KEY `heartbeat_id` (`dns_monitor_id`),
  KEY `user_id` (`user_id`),
  KEY `dns_monitors_logs_dns_monitor_id_datetime_index` (`dns_monitor_id`,`datetime`),
  KEY `idx_user_id_datetime` (`user_id`, `datetime`),
  CONSTRAINT `dns_monitors_logs_ibfk_1` FOREIGN KEY (`dns_monitor_id`) REFERENCES `dns_monitors` (`dns_monitor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dns_monitors_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

INSERT INTO `monitors` (`monitor_id`, `project_id`, `user_id`, `ping_servers_ids`, `incident_id`, `name`, `type`, `target`, `port`, `settings`, `details`, `is_ok`, `uptime`, `uptime_seconds`, `downtime`, `downtime_seconds`, `average_response_time`, `total_checks`, `total_ok_checks`, `total_not_ok_checks`, `last_check_datetime`, `next_check_datetime`, `main_ok_datetime`, `last_ok_datetime`, `main_not_ok_datetime`, `last_not_ok_datetime`, `notifications`, `email_reports_is_enabled`, `email_reports_last_datetime`, `is_enabled`, `last_datetime`, `datetime`) VALUES
(1, NULL, 1, '[1]', NULL, 'Example', 'website', 'https://example.com/', 0, '{"check_interval_seconds":3600,"timeout_seconds":3600,"request_method":"GET","request_body":"","request_basic_auth_username":"","request_basic_auth_password":"","request_headers":[],"response_status_code":200,"response_body":"","response_headers":[]}', '{"country_code":"US","city_name":"Norwell","continent_name":"North America"}', 1, 100, 0, 0, 0, 0, 0, 0, 0, '2023-10-17 12:12:29', '2023-10-17 12:12:29', '2023-10-17 12:12:29', '2023-10-17 12:12:29', '2023-10-17 12:12:29', '2023-10-17 12:12:29', NULL, 0, '2023-10-17 12:12:29', 1, NULL, '2023-10-17 12:12:29');

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `notification_handlers` (
  `notification_handler_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `settings` text,
  `is_enabled` tinyint(4) NOT NULL DEFAULT '1',
  `last_datetime` datetime DEFAULT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`notification_handler_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notification_handlers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `pages` (
  `page_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pages_category_id` bigint(20) UNSIGNED DEFAULT NULL,
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
  `open_in_new_tab` tinyint(4) DEFAULT '1',
  `order` int(11) DEFAULT '0',
  `total_views` bigint(20) UNSIGNED DEFAULT '0',
  `is_published` tinyint(4) DEFAULT '1',
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
-- Nulled by LostKorean - babia.to --

INSERT INTO `pages` (`page_id`, `pages_category_id`, `url`, `title`, `description`, `icon`, `keywords`, `editor`, `content`, `type`, `position`, `language`, `open_in_new_tab`, `order`, `total_views`, `is_published`, `datetime`, `last_datetime`) VALUES
(1, NULL, 'https://altumcode.com/', 'Software by AltumCode', '', NULL, NULL, NULL, '', 'external', 'bottom', NULL, 1, 1, 0, 1, '2023-10-17 12:12:19', '2023-10-17 12:12:19'),
(2, NULL, 'https://altumco.de/66uptime', 'Built with 66uptime', '', NULL, NULL, NULL, '', 'external', 'bottom', NULL, 1, 0, 0, 1, '2023-10-17 12:12:19', '2023-10-17 12:12:19');

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

INSERT INTO `pages` (`pages_category_id`, `footer_category_id`, `plans_ids`, `url`, `title`, `description`, `icon`, `keywords`, `editor`, `content`, `type`, `position`, `language`, `open_in_new_tab`, `order`, `total_views`, `is_published`, `datetime`, `last_datetime`) VALUES
(NULL, 'resources', NULL, 'blog', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 2, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'affiliate', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 4, 0, 1, NOW(), NULL),
(NULL, 'resources', NULL, 'contact', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 3, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'cookie_consent', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 5, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'push_notifications', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 6, 0, 1, NOW(), NULL),
(NULL, 'resources', NULL, 'pages', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 1, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'api-documentation', '', NULL, '', NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 3, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'plan', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 2, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'dashboard', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 1, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'tools', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'top', NULL, 0, 0, 0, 1, NOW(), NULL);

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `statistics` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status_page_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `country_code` varchar(8) DEFAULT NULL,
  `continent_code` ENUM('AF', 'AN', 'AS', 'EU', 'NA', 'OC', 'SA') DEFAULT NULL,
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
  KEY `status_page_id` (`status_page_id`),
  KEY `datetime` (`datetime`),
  KEY `project_id` (`project_id`),
  KEY `idx_user_id_datetime` (`user_id`, `datetime`),
  CONSTRAINT `statistics_ibfk_1` FOREIGN KEY (`status_page_id`) REFERENCES `status_pages` (`status_page_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `statistics_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `statistics_ibfk_3` FOREIGN KEY (`project_id`) REFERENCES `status_pages` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `statistics_users_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `incidents` (
  `incident_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `monitor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_monitor_log_id` bigint(20) UNSIGNED DEFAULT NULL,
  `end_monitor_log_id` bigint(20) UNSIGNED DEFAULT NULL,
  `heartbeat_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_heartbeat_log_id` bigint(20) UNSIGNED DEFAULT NULL,
  `end_heartbeat_log_id` bigint(20) UNSIGNED DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `start_datetime` datetime DEFAULT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `last_failed_check_datetime` datetime DEFAULT NULL,
  `failed_checks` bigint(20) UNSIGNED DEFAULT 1,
  `notification_handlers_ids` text DEFAULT NULL,
  `error` text DEFAULT NULL,
  PRIMARY KEY (`incident_id`),
  KEY `user_id` (`user_id`),
  KEY `monitor_id` (`monitor_id`),
  KEY `heartbeat_id` (`heartbeat_id`),
  KEY `incidents_heartbeat_id_start_datetime_end_datetime_index` (`heartbeat_id`,`start_datetime`,`end_datetime`),
  KEY `incidents_monitor_id_start_datetime_end_datetime_index` (`monitor_id`,`start_datetime`,`end_datetime`),
  CONSTRAINT `incidents_ibfk_3` FOREIGN KEY (`monitor_id`) REFERENCES `monitors` (`monitor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `incidents_ibfk_4` FOREIGN KEY (`heartbeat_id`) REFERENCES `heartbeats` (`heartbeat_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `incidents_ibfk_7` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

INSERT INTO `status_pages` (`status_page_id`, `domain_id`, `monitors_ids`, `project_id`, `user_id`, `url`, `name`, `description`, `settings`, `socials`, `logo`, `favicon`, `opengraph`, `password`, `timezone`, `theme`, `custom_js`, `custom_css`, `pageviews`, `is_se_visible`, `is_removed_branding`, `is_enabled`, `last_datetime`, `datetime`) VALUES
(1, NULL, '[1]', NULL, 1, 'example', 'Example', 'This is just a simple description for the example status page.', NULL, '{"facebook":"","instagram":"","twitter":"","email":"","website":""}', NULL, NULL, NULL, NULL, 'UTC', 'new-york', '', '', 0, 1, 0, 1, NULL, '2023-10-17 12:12:29');

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

ALTER TABLE `monitors`
  ADD CONSTRAINT `monitors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `monitors_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `monitors_ibfk_3` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`incident_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

ALTER TABLE `heartbeats`
  ADD CONSTRAINT `heartbeats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `heartbeats_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `heartbeats_ibfk_3` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`incident_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `game_servers` (
`game_server_id` bigint unsigned NOT NULL AUTO_INCREMENT,
`project_id` bigint unsigned DEFAULT NULL,
`user_id` bigint unsigned NOT NULL,
`name` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
`type` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
`target` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
`port` int DEFAULT NULL,
`query_port` int DEFAULT NULL,
`settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
`details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
`last_logs` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
`is_ok` tinyint DEFAULT '1',
`online_players` int unsigned DEFAULT NULL,
`uptime` float DEFAULT '100',
`maximum_online_players` int unsigned DEFAULT NULL,
`uptime_seconds` int unsigned DEFAULT '0',
`downtime` float DEFAULT '0',
`downtime_seconds` int unsigned DEFAULT '0',
`average_response_time` float DEFAULT NULL,
`average_online_players` float DEFAULT NULL,
`total_checks` bigint unsigned DEFAULT '0',
`total_ok_checks` bigint unsigned DEFAULT NULL,
`total_not_ok_checks` bigint unsigned DEFAULT '0',
`last_check_datetime` datetime DEFAULT NULL,
`next_check_datetime` datetime DEFAULT NULL,
`main_ok_datetime` datetime DEFAULT NULL,
`last_ok_datetime` datetime DEFAULT NULL,
`main_not_ok_datetime` datetime DEFAULT NULL,
`last_not_ok_datetime` datetime DEFAULT NULL,
`is_enabled` tinyint NOT NULL DEFAULT '1',
`last_datetime` datetime DEFAULT NULL,
`datetime` datetime NOT NULL,
PRIMARY KEY (`game_server_id`),
KEY `user_id` (`user_id`),
KEY `project_id` (`project_id`),
KEY `game_servers_is_enabled_next_check_datetime_index` (`is_enabled`,`next_check_datetime`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `game_servers_logs` (
`game_server_log_id` bigint unsigned NOT NULL AUTO_INCREMENT,
`game_server_id` bigint unsigned NOT NULL,
`user_id` bigint unsigned DEFAULT NULL,
`is_ok` tinyint unsigned DEFAULT NULL,
`response_time` float DEFAULT '0',
`online_players` int unsigned DEFAULT NULL,
`maximum_online_players` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
`error` text,
`datetime` datetime DEFAULT NULL,
PRIMARY KEY (`game_server_log_id`),
KEY `monitor_id` (`game_server_id`),
KEY `user_id` (`user_id`),
KEY `idx_user_datetime` (`user_id`,`datetime`),
KEY `monitors_logs_monitor_id_datetime_index` (`game_server_id`,`datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE `tools_usage` (
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
`tool_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
`total_views` bigint unsigned DEFAULT '0',
`total_submissions` bigint DEFAULT '0',
`data` text,
PRIMARY KEY (`id`),
UNIQUE KEY `tools_usage_tool_id_idx` (`tool_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
