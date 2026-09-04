ALTER TABLE droppy_pm_settings MODIFY COLUMN expire_time VARCHAR(100);
UPDATE droppy_pm_settings SET plugin_version = '2.1.1';