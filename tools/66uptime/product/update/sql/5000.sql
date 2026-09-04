UPDATE `settings` SET `value` = '{\"version\":\"50.0.0\", \"code\":\"5000\"}' WHERE `key` = 'product_info';
-- SEPARATOR --
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('plisio', '{\"is_enabled\":false,\"secret_key\":\"\",\"accepted_cryptocurrencies\":[\"DOGE\",\"SOL\",\"ETH\",\"BTC\"],\"default_cryptocurrency\":\"SOL\",\"currencies\":[\"USD\"]}');
-- SEPARATOR --

INSERT INTO `settings` (`key`, `value`) VALUES ('revolut', '{\"is_enabled\":false,\"mode\":\"sandbox\",\"secret_key\":\"\",\"webhook_id\":\"\",\"currencies\":[\"USD\"]}');
-- SEPARATOR --

INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('plisio_whitelabel', '{\"is_enabled\":false,\"secret_key\":\"\",\"accepted_cryptocurrencies\":[\"DOGE\",\"SOL\",\"ETH\",\"BTC\"],\"default_cryptocurrency\":\"SOL\",\"currencies\":[\"USD\"]}');

-- SEPARATOR --

create index `status` on users (status);

-- SEPARATOR --

create index users_logs_datetime_index on users_logs (datetime);

-- SEPARATOR --

create index internal_notifications_datetime_index on internal_notifications (datetime);
-- SEPARATOR --

alter table users add next_logs_cleanup_datetime datetime null after next_cleanup_datetime;

-- SEPARATOR --

update users set next_logs_cleanup_datetime = NOW();

-- SEPARATOR --

create index monitors_is_enabled_next_check_datetime_index on monitors (is_enabled, next_check_datetime);
-- SEPARATOR --
create index dns_monitors_is_enabled_next_check_datetime_index on dns_monitors (is_enabled, next_check_datetime);
-- SEPARATOR --
create index heartbeats_is_enabled_next_check_datetime_index on heartbeats (is_enabled, next_run_datetime);
-- SEPARATOR --
create index domain_names_is_enabled_next_check_datetime_index on domain_names (is_enabled, next_check_datetime);
-- SEPARATOR --