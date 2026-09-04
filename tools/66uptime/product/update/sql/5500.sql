UPDATE `settings` SET `value` = '{\"version\":\"51.0.0\", \"code\":\"5100\"}' WHERE `key` = 'product_info';

-- SEPARATOR --UPDATE `settings` SET `value` = '{\"version\":\"52.0.0\", \"code\":\"5200\"}' WHERE `key` = 'product_info';

-- SEPARATOR --
-- X --

alter table heartbeats_logs drop key monitors_log_id;

-- SEPARATOR --
-- X --

alter table monitors_logs drop key monitors_log_id;

-- SEPARATOR --
-- X --

alter table dns_monitors_logs drop key monitors_log_id;

-- SEPARATOR --
-- X --

alter table server_monitors_logs drop key monitors_log_id;

-- SEPARATOR --
-- X --

alter table incidents drop key monitor_incident_id;

-- SEPARATOR --
-- X --

alter table domain_names drop key domain_name_id;

-- SEPARATOR --
-- X --

alter table dns_monitors drop key domain_name_id;

-- SEPARATOR --
-- X --

alter table server_monitors drop key domain_name_id;

-- SEPARATOR --
-- X --

alter table status_pages drop key status_page_id;

-- SEPARATOR --
-- X --

alter table ping_servers drop key ping_server_id;

-- SEPARATOR --
-- X --

drop index datetime on heartbeats_logs;

-- SEPARATOR --
-- X --

create index heartbeats_logs_heartbeat_id_datetime_index on heartbeats_logs (heartbeat_id, datetime);

-- SEPARATOR --
-- X --

drop index datetime on monitors_logs;

-- SEPARATOR --
-- X --

create index monitors_logs_monitor_id_datetime_index on monitors_logs (monitor_id, datetime);

-- SEPARATOR --
-- X --

drop index datetime on dns_monitors_logs;

-- SEPARATOR --
-- X --

create index dns_monitors_logs_dns_monitor_id_datetime_index on dns_monitors_logs (dns_monitor_id, datetime);

-- SEPARATOR --
-- X --

drop index datetime on server_monitors_logs;

-- SEPARATOR --
-- X --

create index server_monitors_logs_server_monitor_id_datetime_index on server_monitors_logs (server_monitor_id, datetime);

-- SEPARATOR --
-- X --

alter table projects drop key project_id;
-- SEPARATOR --
-- X --

alter table notification_handlers drop key notification_handler_id;
-- SEPARATOR --
-- X --

alter table incidents drop foreign key incidents_ibfk_1;
-- SEPARATOR --
-- X --

alter table incidents drop foreign key incidents_ibfk_2;
-- SEPARATOR --
-- X --

alter table incidents drop foreign key incidents_ibfk_5;
-- SEPARATOR --
-- X --

alter table incidents drop foreign key incidents_ibfk_6;

-- SEPARATOR --
-- X --

drop index end_heartbeat_log_id on incidents;
-- SEPARATOR --
-- X --

drop index end_monitor_log_id on incidents;
-- SEPARATOR --
-- X --

drop index start_heartbeat_log_id on incidents;
-- SEPARATOR --
-- X --

drop index start_monitor_log_id on incidents;

-- SEPARATOR --
-- X --

create index incidents_heartbeat_id_start_datetime_end_datetime_index on incidents (heartbeat_id, start_datetime, end_datetime);
-- SEPARATOR --
-- X --

create index incidents_monitor_id_start_datetime_end_datetime_index on incidents (monitor_id, start_datetime, end_datetime);
-- SEPARATOR --UPDATE `settings` SET `value` = '{\"version\":\"53.0.0\", \"code\":\"5300\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

alter table ping_servers add api_key varchar(128) null after city_name;

-- SEPARATOR --

ALTER TABLE `server_monitors` MODIFY COLUMN `name` VARCHAR(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL;

-- SEPARATOR --

ALTER TABLE `status_pages` MODIFY COLUMN `name` VARCHAR(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL;

-- SEPARATOR --

ALTER TABLE `dns_monitors` MODIFY COLUMN `name` VARCHAR(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL;

-- SEPARATOR --

ALTER TABLE `domain_names` MODIFY COLUMN `name` VARCHAR(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL;

-- SEPARATOR --

ALTER TABLE `heartbeats` MODIFY COLUMN `name` VARCHAR(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL;

-- SEPARATOR --

ALTER TABLE `monitors` MODIFY COLUMN `name` VARCHAR(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL;

-- SEPARATOR --

CREATE TABLE `game_servers` (
`game_server_id` bigint unsigned NOT NULL AUTO_INCREMENT,
`project_id` bigint unsigned DEFAULT NULL,
`user_id` bigint unsigned NOT NULL,
`name` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
`type` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
`target` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
`port` int DEFAULT NULL,
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
-- SEPARATOR --UPDATE `settings` SET `value` = '{\"version\":\"54.0.0\", \"code\":\"5400\"}' WHERE `key` = 'product_info';
-- SEPARATOR --

alter table pages add footer_category_id varchar(64) null after pages_category_id;

-- SEPARATOR --

alter table pages modify type enum ('internal', 'external', 'feature') null;

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

INSERT INTO `pages` (`pages_category_id`, `footer_category_id`, `plans_ids`, `url`, `title`, `description`, `icon`, `keywords`, `editor`, `content`, `type`, `position`, `language`, `open_in_new_tab`, `order`, `total_views`, `is_published`, `datetime`, `last_datetime`) VALUES
(NULL, 'product', NULL, 'tools', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'top', NULL, 0, 0, 0, 1, NOW(), NULL);
-- SEPARATOR --UPDATE `settings` SET `value` = '{\"version\":\"55.0.0\", \"code\":\"5500\"}' WHERE `key` = 'product_info';
-- SEPARATOR --
alter table game_servers add query_port int null after port;
-- SEPARATOR --
update game_servers set query_port = port;
-- SEPARATOR --
ALTER TABLE `users` ADD COLUMN `latitude` DECIMAL(10, 8) NULL AFTER `ip`, ADD COLUMN `longitude` DECIMAL(11, 8) NULL AFTER `latitude`;
-- SEPARATOR --
-- X --

alter table teams_members add token varchar(32) null after user_id;
-- SEPARATOR --