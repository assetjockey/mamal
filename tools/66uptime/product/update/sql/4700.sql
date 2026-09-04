UPDATE `settings` SET `value` = '{\"version\":\"47.0.0\", \"code\":\"4700\"}' WHERE `key` = 'product_info';
-- SEPARATOR --
alter table pages add plans_ids text null after pages_category_id;
-- SEPARATOR --