UPDATE `settings` SET `value` = '{\"version\":\"30.0.0\", \"code\":\"3000\"}' WHERE `key` = 'product_info';

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

UPDATE `settings` SET `value` = '{\"license\":\"Nulled by LostKorean - babia.to\", \"type\":\"Extended License\"}' WHERE `key` = 'license';

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

UPDATE `settings` SET `value` = '{\"key\":\"Nulled by LostKorean - babia.to\", \"expiry_datetime\":\"2100-01-01 00:00:00\"}' WHERE `key` = 'support';

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

alter table users add preferences text after timezone;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE IF NOT EXISTS `server_monitors` (
  `server_monitor_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `settings` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notifications` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpu_usage` float DEFAULT NULL,
  `ram_usage` float DEFAULT NULL,
  `disk_usage` float DEFAULT NULL,
  `cpu_load_1` float DEFAULT NULL,
  `cpu_load_5` float DEFAULT NULL,
  `cpu_load_15` float DEFAULT NULL,
  `total_logs` bigint(20) UNSIGNED DEFAULT 0,
  `is_enabled` tinyint(4) DEFAULT 1,
  `last_log_datetime` datetime DEFAULT NULL,
  `last_datetime` datetime DEFAULT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`server_monitor_id`),
  UNIQUE KEY `domain_name_id` (`server_monitor_id`),
  KEY `project_id` (`project_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `server_monitors_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `server_monitors_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

CREATE TABLE IF NOT EXISTS `server_monitors_logs` (
  `server_monitor_log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `server_monitor_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cpu_usage` float DEFAULT NULL,
  `ram_usage` float DEFAULT NULL,
  `disk_usage` float DEFAULT NULL,
  `cpu_load_1` float DEFAULT NULL,
  `cpu_load_5` float DEFAULT NULL,
  `cpu_load_15` float DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`server_monitor_log_id`),
  UNIQUE KEY `monitors_log_id` (`server_monitor_log_id`) USING BTREE,
  KEY `heartbeat_id` (`server_monitor_id`),
  KEY `user_id` (`user_id`),
  KEY `datetime` (`datetime`) USING BTREE,
  CONSTRAINT `server_monitors_logs_ibfk_1` FOREIGN KEY (`server_monitor_id`) REFERENCES `server_monitors` (`server_monitor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `server_monitors_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --