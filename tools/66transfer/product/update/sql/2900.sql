UPDATE `settings` SET `value` = '{\"version\":\"29.0.0\", \"code\":\"2900\"}' WHERE `key` = 'product_info';
-- SEPARATOR --
alter table pages add plans_ids text null after pages_category_id;
-- SEPARATOR --