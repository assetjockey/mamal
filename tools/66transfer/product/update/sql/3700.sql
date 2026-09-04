UPDATE `settings` SET `value` = '{\"version\":\"37.0.0\", \"code\":\"3700\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

ALTER TABLE `users` ADD COLUMN `latitude` DECIMAL(10, 8) NULL AFTER `ip`, ADD COLUMN `longitude` DECIMAL(11, 8) NULL AFTER `latitude`;

-- SEPARATOR --
-- X --

alter table teams_members add token varchar(32) null after user_id;

-- SEPARATOR --
