UPDATE `settings` SET `value` = '{\"version\":\"27.0.0\", \"code\":\"2700\"}' WHERE `key` = 'product_info';
-- SEPARATOR --

INSERT INTO `settings` (`key`, `value`) VALUES ('custom_images', '{}');

-- SEPARATOR --

alter table users add avatar varchar(40) null after name;
-- SEPARATOR --