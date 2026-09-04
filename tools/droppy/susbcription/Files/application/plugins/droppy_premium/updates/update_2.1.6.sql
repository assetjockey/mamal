UPDATE droppy_pm_settings SET plugin_version = '2.1.6';

CREATE TABLE `droppy_pm_backgrounds` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `src` text DEFAULT NULL,
    `url` text DEFAULT NULL,
    `duration` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE droppy_pm_settings MODIFY plan_expire_time LONGTEXT;
ALTER TABLE droppy_pm_plans ADD plan_backgrounds VARCHAR(11) DEFAULT 'false' AFTER plan_max_storage;