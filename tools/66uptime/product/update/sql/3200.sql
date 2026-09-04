UPDATE `settings` SET `value` = '{\"version\":\"32.0.0\", \"code\":\"3200\"}' WHERE `key` = 'product_info';

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

UPDATE `settings` SET `value` = '{\"license\":\"Nulled by LostKorean - babia.to\", \"type\":\"Extended License\"}' WHERE `key` = 'license';

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

UPDATE `settings` SET `value` = '{\"key\":\"Nulled by LostKorean - babia.to\", \"expiry_datetime\":\"2100-01-01 00:00:00\"}' WHERE `key` = 'support';

-- SEPARATOR --
-- Nulled by LostKorean - babia.to --

INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('notification_handlers', '{"twilio_sid":"","twilio_token":"","twilio_number":"","whatsapp_number_id":"","whatsapp_access_token":"","email_is_enabled":true,"webhook_is_enabled":true,"slack_is_enabled":true,"discord_is_enabled":true,"telegram_is_enabled":true,"microsoft_teams_is_enabled":true,"twilio_is_enabled":false,"twilio_call_is_enabled":false,"whatsapp_is_enabled":false}');