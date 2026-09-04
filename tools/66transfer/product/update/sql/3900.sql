UPDATE `settings` SET `value` = '{\"version\":\"39.0.0\", \"code\":\"3900\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

UPDATE `settings` SET `value` = JSON_SET(`value`, '$.transfer_requests_cleanup_datetime', '') WHERE `key` = 'cron';

-- SEPARATOR --
