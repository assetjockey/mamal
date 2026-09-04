UPDATE droppy_pm_settings SET plugin_version = '2.1.4';

ALTER TABLE droppy_pm_plans ADD plan_max_storage INT(12) DEFAULT NULL AFTER plan_ad_enabled;