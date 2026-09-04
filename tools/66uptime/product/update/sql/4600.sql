UPDATE `settings` SET `value` = '{\"version\":\"46.0.0\", \"code\":\"4600\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

ALTER TABLE `monitors_logs` ADD INDEX `idx_user_id_datetime` (`user_id`, `datetime`);

-- SEPARATOR --

ALTER TABLE `heartbeats_logs` ADD INDEX `idx_user_id_datetime` (`user_id`, `datetime`);

-- SEPARATOR --

ALTER TABLE `dns_monitors_logs` ADD INDEX `idx_user_id_datetime` (`user_id`, `datetime`);

-- SEPARATOR --

ALTER TABLE `server_monitors_logs` ADD INDEX `idx_user_id_datetime` (`user_id`, `datetime`);

-- SEPARATOR --

ALTER TABLE `statistics` ADD INDEX `idx_user_id_datetime` (`user_id`, `datetime`);

-- SEPARATOR --

ALTER TABLE `users` ADD INDEX `idx_users_next_cleanup_datetime` (`next_cleanup_datetime`);

-- SEPARATOR --
alter table users modify twofa_secret varchar(32) collate utf8mb4_unicode_ci null;

-- SEPARATOR --

alter table broadcasts_statistics modify type enum ('view', 'click') null;

-- SEPARATOR --

alter table pages modify type enum ('internal', 'external') null;
-- SEPARATOR --

alter table users modify device_type enum ('mobile', 'tablet', 'desktop') null;

-- SEPARATOR --

alter table users_logs modify device_type enum ('mobile', 'tablet', 'desktop') null;

-- SEPARATOR --

alter table statistics modify device_type enum ('mobile', 'tablet', 'desktop') null;
-- SEPARATOR --

alter table users modify continent_code ENUM('AF', 'AN', 'AS', 'EU', 'NA', 'OC', 'SA') null;

-- SEPARATOR --

alter table users_logs modify continent_code ENUM('AF', 'AN', 'AS', 'EU', 'NA', 'OC', 'SA') null;

-- SEPARATOR --

alter table statistics modify continent_code ENUM('AF', 'AN', 'AS', 'EU', 'NA', 'OC', 'SA') null;
-- SEPARATOR --