UPDATE `settings` SET `value` = '{\"version\":\"33.0.0\", \"code\":\"3300\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

alter table domain_names add `ssl_port` int unsigned default 443 null after target;

-- SEPARATOR --

CREATE TABLE `tools_usage` (
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
`tool_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
`total_views` bigint unsigned DEFAULT '0',
PRIMARY KEY (`id`),
UNIQUE KEY `tools_usage_tool_id_idx` (`tool_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- SEPARATOR --

alter table plans add translations text null after description;

-- SEPARATOR --

alter table plans drop column monthly_price;

-- SEPARATOR --

alter table plans drop column annual_price;

-- SEPARATOR --

alter table plans drop column lifetime_price;

-- SEPARATOR --

alter table users modify plan_settings longtext null;

-- SEPARATOR --

alter table plans modify settings longtext not null;

-- SEPARATOR --

alter table statistics add user_id bigint unsigned null after status_page_id;

-- SEPARATOR --

alter table statistics add constraint statistics_users_user_id_fk foreign key (user_id) references users (user_id) on update cascade on delete cascade;

-- SEPARATOR --

UPDATE statistics LEFT JOIN `status_pages` ON `statistics`.`status_page_id` = `status_pages`.`status_page_id` SET `statistics`.`user_id` = `status_pages`.`user_id`;

-- SEPARATOR --