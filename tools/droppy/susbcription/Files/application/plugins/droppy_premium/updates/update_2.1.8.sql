UPDATE droppy_pm_settings SET plugin_version = '2.1.8';
ALTER TABLE droppy_pm_plans ADD plan_active VARCHAR(10) DEFAULT 'true' AFTER plan_backgrounds;