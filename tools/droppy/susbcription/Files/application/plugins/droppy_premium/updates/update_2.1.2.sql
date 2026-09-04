ALTER TABLE droppy_pm_subs ADD sub_plan INT(11) DEFAULT NULL AFTER sub_id;

CREATE TABLE `droppy_pm_plans` (
   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
   `plan_name` varchar(255) DEFAULT NULL,
   `plan_desc` varchar(255) DEFAULT NULL,
   `plan_features` longtext DEFAULT NULL,
   `plan_price` varchar(11) DEFAULT NULL,
   `plan_time` varchar(255) DEFAULT NULL,
   `plan_freq` int(11) DEFAULT NULL,
   `plan_max_size` int(10) DEFAULT NULL,
   `plan_password_enabled` varchar(100) DEFAULT NULL,
   `plan_expire_time` varchar(100) DEFAULT NULL,
   `plan_ad_enabled` varchar(100) DEFAULT NULL,
   `plan_stripe_id` varchar(100) DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO droppy_pm_plans (plan_name, plan_desc, plan_price, plan_time, plan_freq, plan_max_size, plan_password_enabled, plan_expire_time, plan_ad_enabled, plan_stripe_id)
SELECT item_name, subscription_desc, sub_price, recur_time, recur_freq, max_size, password_enabled, expire_time, ad_enabled, stripe_product
FROM droppy_pm_settings LIMIT 1;

UPDATE droppy_pm_subs SET sub_plan = 1;

UPDATE droppy_pm_settings SET plugin_version = '2.1.2';