ALTER TABLE droppy_pm_settings
    ADD COLUMN payment_gateway VARCHAR(50) AFTER forgot_pass_email,
    ADD COLUMN stripe_key VARCHAR(255) AFTER payment_gateway,
    ADD COLUMN stripe_product VARCHAR(255) AFTER stripe_key,
    ADD COLUMN stripe_price VARCHAR(255) AFTER stripe_product,
    ADD COLUMN stripe_webhook VARCHAR(255) AFTER stripe_price,
    ADD COLUMN plugin_version VARCHAR(10) AFTER stripe_webhook;

ALTER TABLE droppy_pm_subs
    ADD COLUMN stripe_id VARCHAR(200) AFTER paypal_ordertime;

UPDATE droppy_pm_settings SET plugin_version = '2.0.6';