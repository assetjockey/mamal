ALTER TABLE droppy_pm_settings
ADD COLUMN enable_vat varchar(10) AFTER logo_url,
ADD COLUMN enable_address varchar(10) AFTER enable_vat,
ADD COLUMN enable_multi_user varchar(10) AFTER enable_address;

UPDATE droppy_pm_settings SET enable_vat = 'false', enable_address = 'false', enable_multi_user = 'false';

ALTER TABLE droppy_pm_subs
ADD COLUMN vat_number varchar(255) DEFAULT '' AFTER company,
ADD COLUMN address_street varchar(255) DEFAULT '' AFTER vat_number,
ADD COLUMN address_zip varchar(255) DEFAULT '' AFTER address_street,
ADD COLUMN address_city varchar(255) DEFAULT '' AFTER address_zip,
ADD COLUMN address_country varchar(255) DEFAULT '' AFTER address_city;

ALTER TABLE droppy_pm_users
ADD COLUMN parent_id int(12) DEFAULT NULL AFTER id;

UPDATE droppy_pm_settings SET plugin_version = '2.0.9';