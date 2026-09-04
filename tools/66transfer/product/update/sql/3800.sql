UPDATE `settings` SET `value` = '{\"version\":\"38.0.0\", \"code\":\"3800\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

UPDATE `settings` SET `value` = JSON_SET(`value`, '$.transfer_requests_is_enabled', true) WHERE `key` = 'transfers';

-- SEPARATOR --

UPDATE `settings` SET `value` = JSON_SET(`value`, '$.transfer_request_create_is_enabled', false, '$.transfer_request_upload_is_enabled', false) WHERE `key` = 'captcha';

-- SEPARATOR --

UPDATE `settings` SET `value` = JSON_SET(`value`, '$.settings.transfer_requests_limit', COALESCE(JSON_EXTRACT(`value`, '$.settings.transfers_limit'), 0)) WHERE `key` IN ('plan_free', 'plan_guest') AND JSON_VALID(`value`);

-- SEPARATOR --

UPDATE `plans` SET `settings` = JSON_SET(`settings`, '$.transfer_requests_limit', COALESCE(JSON_EXTRACT(`settings`, '$.transfers_limit'), 0)) WHERE JSON_VALID(`settings`);

-- SEPARATOR --

UPDATE `users` SET `plan_settings` = JSON_SET(`plan_settings`, '$.transfer_requests_limit', COALESCE(JSON_EXTRACT(`plan_settings`, '$.transfers_limit'), 0)) WHERE JSON_VALID(`plan_settings`);

-- SEPARATOR --

UPDATE `users` SET `preferences` = JSON_SET(
    `preferences`,
    '$.transfer_requests_default_order_by', 'transfer_request_id',
    '$.transfer_requests_default_expiration_datetime', 0,
    '$.transfer_requests_default_pixels_ids', JSON_ARRAY(),
    '$.transfer_requests_default_project_id', 0,
    '$.transfer_requests_default_is_removed_branding', true,
    '$.transfer_requests_default_custom_css', '',
    '$.transfer_requests_default_custom_js', '',
    '$.transfer_requests_default_submission_notification_handlers_ids', JSON_ARRAY(),
    '$.transfer_requests_default_pageview_notification_handlers_ids', JSON_ARRAY(),
    '$.transfer_requests_auto_file_upload', false,
    '$.transfer_requests_auto_submission_create', false,
    '$.transfer_requests_auto_copy_link', true,
    '$.dashboard.transfer_requests', true
) WHERE JSON_VALID(`preferences`);

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

ALTER TABLE `files` ADD COLUMN `transfer_request_id` bigint unsigned DEFAULT NULL AFTER `transfer_id`, ADD COLUMN `request_submission_id` bigint unsigned DEFAULT NULL AFTER `transfer_request_id`;

-- SEPARATOR --

ALTER TABLE `files` ADD INDEX `files_transfer_request_id_idx` (`transfer_request_id`), ADD INDEX `files_request_submission_id_idx` (`request_submission_id`);

-- SEPARATOR --

ALTER TABLE `files` ADD CONSTRAINT `files_transfer_requests_transfer_request_id_fk` FOREIGN KEY (`transfer_request_id`) REFERENCES `transfer_requests` (`transfer_request_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- SEPARATOR --

ALTER TABLE `statistics` MODIFY `transfer_id` bigint unsigned DEFAULT NULL;

-- SEPARATOR --

ALTER TABLE `statistics` ADD COLUMN `transfer_request_id` bigint unsigned DEFAULT NULL AFTER `transfer_id`;

-- SEPARATOR --

ALTER TABLE `statistics` ADD INDEX `statistics_transfer_request_id_idx` (`transfer_request_id`), ADD INDEX `idx_transfer_request_id_datetime` (`transfer_request_id`, `datetime`);

-- SEPARATOR --

ALTER TABLE `statistics` ADD CONSTRAINT `statistics_transfer_requests_transfer_request_id_fk` FOREIGN KEY (`transfer_request_id`) REFERENCES `transfer_requests` (`transfer_request_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- SEPARATOR --
