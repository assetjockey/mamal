UPDATE `settings` SET `value` = '{\"version\":\"37.0.0\", \"code\":\"3700\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

alter table status_pages add heartbeats_ids text null after monitors_ids;

-- SEPARATOR --

alter table blog_posts add image_description varchar(256) null after description;
-- SEPARATOR --