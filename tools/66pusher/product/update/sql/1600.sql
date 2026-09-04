UPDATE `settings` SET `value` = '{\"version\":\"16.0.0\", \"code\":\"1600\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

alter table domains add type tinyint default 0 null after custom_not_found_url;
-- SEPARATOR --