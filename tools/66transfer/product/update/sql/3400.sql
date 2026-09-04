UPDATE `settings` SET `value` = '{\"version\":\"34.0.0\", \"code\":\"3400\"}' WHERE `key` = 'product_info';

-- SEPARATOR --
-- X --

alter table projects drop key project_id;

-- SEPARATOR --
-- X --

alter table notification_handlers drop key notification_handler_id;

-- SEPARATOR --
-- X --

alter table transfers drop key transfer_id;

-- SEPARATOR --
