ALTER TABLE droppy_settings ADD COLUMN `max_recipients` int(10) NULL DEFAULT 0;
ALTER TABLE droppy_settings ADD COLUMN `blocked_emails` MEDIUMTEXT NULL DEFAULT NULL;