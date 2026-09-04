UPDATE `settings` SET `value` = '{\"version\":\"23.0.0\", \"code\":\"2300\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

update files set `file_uuid` =  '';

-- SEPARATOR --

alter table files modify file_uuid binary(16) null;

-- SEPARATOR --

update files set `file_uuid` =  UNHEX(REPLACE(UUID(), '-', ''));

-- SEPARATOR --