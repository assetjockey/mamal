UPDATE `settings` SET `value` = '{\"version\":\"26.0.0\", \"code\":\"2600\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

alter table downloads add user_id bigint unsigned null after transfer_id;

-- SEPARATOR --

alter table downloads add constraint downloads_users_user_id_fk foreign key (user_id) references users (user_id) on update cascade on delete cascade;

-- SEPARATOR --