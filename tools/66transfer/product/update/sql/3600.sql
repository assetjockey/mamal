UPDATE `settings` SET `value` = '{\"version\":\"36.0.0\", \"code\":\"3600\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

alter table pages add footer_category_id varchar(64) null after pages_category_id;

-- SEPARATOR --

alter table pages modify type enum ('internal', 'external', 'feature') null;

-- SEPARATOR --

INSERT INTO `pages` (`pages_category_id`, `footer_category_id`, `plans_ids`, `url`, `title`, `description`, `icon`, `keywords`, `editor`, `content`, `type`, `position`, `language`, `open_in_new_tab`, `order`, `total_views`, `is_published`, `datetime`, `last_datetime`) VALUES
(NULL, 'resources', NULL, 'blog', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 2, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'affiliate', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 4, 0, 1, NOW(), NULL),
(NULL, 'resources', NULL, 'contact', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 3, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'cookie_consent', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 5, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'push_notifications', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 6, 0, 1, NOW(), NULL),
(NULL, 'resources', NULL, 'pages', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 1, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'api-documentation', '', NULL, '', NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 3, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'plan', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 2, 0, 1, NOW(), NULL),
(NULL, 'product', NULL, 'dashboard', '', NULL, NULL, NULL, NULL, NULL, 'feature', 'bottom', NULL, 0, 1, 0, 1, NOW(), NULL);

-- SEPARATOR --
