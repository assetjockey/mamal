UPDATE `settings` SET `value` = '{\"version\":\"34.0.0\", \"code\":\"3400\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

alter table monitors add last_logs text null after notifications;

-- SEPARATOR --

alter table heartbeats add last_logs text null after settings;

-- SEPARATOR --

alter table users add extra text null after preferences;

-- SEPARATOR --

alter table monitors_logs add response_body longtext null after response_status_code;

-- SEPARATOR --