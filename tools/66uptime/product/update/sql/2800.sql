UPDATE `settings` SET `value` = '{\"version\":\"28.0.0\", \"code\":\"2800\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('sso', '{}');

-- SEPARATOR --

INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('iyzico', '{}');

-- SEPARATOR --

INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('midtrans', '{}');

-- SEPARATOR --

INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('flutterwave', '{}');

-- SEPARATOR --

alter table plans add prices text null after description;

-- SEPARATOR --

update plans set prices = '{}';

-- SEPARATOR --

alter table users add currency varchar(4) null after language;

-- EXTENDED SEPARATOR --

alter table payments add total_amount_default_currency float null after total_amount;

-- SEPARATOR --

update payments set total_amount_default_currency = total_amount;