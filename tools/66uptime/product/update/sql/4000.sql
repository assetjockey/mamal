UPDATE `settings` SET `value` = '{\"version\":\"40.0.0\", \"code\":\"4000\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

alter table tools_usage add total_submissions bigint default 0 null;

-- SEPARATOR --

alter table tools_usage add data text null;
-- SEPARATOR --