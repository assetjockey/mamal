UPDATE `settings` SET `value` = '{\"version\":\"29.0.0\", \"code\":\"2900\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

UPDATE `settings` SET `value` = '{\"license\":\"Nulled by LostKorean - babia.to\", \"type\":\"Extended License\"}' WHERE `key` = 'license';

-- SEPARATOR --

UPDATE `settings` SET `value` = '{\"key\":\"Nulled by LostKorean - babia.to\", \"expiry_datetime\":\"2100-01-01 00:00:00\"}' WHERE `key` = 'support';

-- SEPARATOR --

CREATE TABLE `dns_monitors` (
  `dns_monitor_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(256) DEFAULT NULL,
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
  UNIQUE KEY `domain_name_id` (`dns_monitor_id`),
  KEY `project_id` (`project_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `dns_monitors_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dns_monitors_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

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
  UNIQUE KEY `monitors_log_id` (`dns_monitor_log_id`) USING BTREE,
  KEY `heartbeat_id` (`dns_monitor_id`),
  KEY `user_id` (`user_id`),
  KEY `datetime` (`datetime`) USING BTREE,
  CONSTRAINT `dns_monitors_logs_ibfk_1` FOREIGN KEY (`dns_monitor_id`) REFERENCES `dns_monitors` (`dns_monitor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dns_monitors_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
