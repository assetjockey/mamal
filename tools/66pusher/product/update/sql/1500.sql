UPDATE `settings` SET `value` = '{\"version\":\"15.0.0\", \"code\":\"1500\"}' WHERE `key` = 'product_info';
-- SEPARATOR --
alter table users add pusher_total_sent_push_notifications bigint unsigned default 0 null after pusher_sent_push_notifications_current_month;
-- SEPARATOR --
alter table pages add plans_ids text null after pages_category_id;
-- SEPARATOR --