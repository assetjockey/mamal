UPDATE `settings` SET `value` = '{\"version\":\"45.0.0\", \"code\":\"4500\"}' WHERE `key` = 'product_info';
-- SEPARATOR --

INSERT INTO `settings` (`key`, `value`) VALUES ('custom_images', '{}');

-- SEPARATOR --

alter table users add avatar varchar(40) null after name;

-- SEPARATOR --

ALTER TABLE server_monitors MODIFY COLUMN kernel_version VARCHAR(256) DEFAULT NULL;

-- SEPARATOR --

alter table incidents add error text null;

-- SEPARATOR --

alter table incidents
    add failed_checks bigint unsigned default 1 null after end_datetime;
-- SEPARATOR --

alter table incidents
    add notification_handlers_ids text null after failed_checks;

-- SEPARATOR --

alter table incidents
    add last_failed_check_datetime datetime null after end_datetime;

-- SEPARATOR --