UPDATE `settings` SET `value` = '{\"version\":\"32.0.0\", \"code\":\"3200\"}' WHERE `key` = 'product_info';
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

alter table files add offload_id varchar(64) null after file_uuid;

-- SEPARATOR --

create index files_datetime_index on files (datetime);
-- SEPARATOR --