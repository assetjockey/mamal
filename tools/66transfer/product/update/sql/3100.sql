UPDATE `settings` SET `value` = '{\"version\":\"31.0.0\", \"code\":\"3100\"}' WHERE `key` = 'product_info';
-- SEPARATOR --
UPDATE users SET email = LOWER(email);


-- SEPARATOR --

INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('klarna', '{"is_enabled":1,"mode":"https:\/\/api.playground.klarna.com\/","username":"","password":"","currencies":["USD"]}');

-- SEPARATOR --

INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('paddle_billing', '{"is_enabled":1,"mode":"sandbox","api_key":"","secret_key":"","client_side_token":"","currencies":["USD"]}');

-- SEPARATOR --

alter table plans add additional_settings text null after settings;

-- SEPARATOR --