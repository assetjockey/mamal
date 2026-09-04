UPDATE `settings` SET `value` = '{\"version\":\"38.0.0\", \"code\":\"3800\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

UPDATE settings SET `value` = JSON_SET(`value`, '$.blacklisted_domains', JSON_ARRAY()) WHERE `key` = 'users';

-- SEPARATOR --

UPDATE settings SET `value` = JSON_SET(`value`, '$.blacklisted_domains', JSON_ARRAY()) WHERE `key` = 'status_pages';

-- SEPARATOR --

UPDATE settings SET `value` = JSON_SET(`value`, '$.blacklisted_keywords', JSON_ARRAY()) WHERE `key` = 'status_pages';

-- SEPARATOR --

alter table statistics add continent_code varchar(8) null after country_code;
-- SEPARATOR --