UPDATE `settings` SET `value` = '{\"version\":\"36.0.0\", \"code\":\"3600\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

alter table users add next_cleanup_datetime datetime default CURRENT_TIMESTAMP null after datetime;

-- SEPARATOR --