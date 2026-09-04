<?php defined('ALTUMCODE') || die() ?>

<?php $onepay = settings()->onepay ?? (object) [] ?>

<div>
    <?php if(!in_array(settings()->license->type, ['Extended License', 'extended'])): ?>
        <div class="alert alert-primary" role="alert">
            You need to own the Extended License in order to activate the payment system.
        </div>
    <?php endif ?>

    <div class="<?= !in_array(settings()->license->type, ['Extended License', 'extended']) ? 'container-disabled' : null ?>">
        <div class="alert alert-info mb-3"><?= sprintf(l('admin_settings.documentation'), '<a href="' . PRODUCT_DOCUMENTATION_URL . '#' . \Altum\Router::$method . '" target="_blank">', '</a>') ?></div>

        <div class="form-group custom-control custom-switch">
            <input id="is_enabled" name="is_enabled" type="checkbox" class="custom-control-input" <?= ($onepay->is_enabled ?? false) ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="is_enabled"><?= l('admin_settings.onepay.is_enabled') ?></label>
        </div>

        <div class="form-group">
            <label for="instance_name"><i class="fas fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('admin_settings.onepay.instance_name') ?></label>
            <input id="instance_name" type="text" name="instance_name" class="form-control" value="<?= $onepay->instance_name ?? '' ?>" />
            <small class="form-text text-muted"><?= l('admin_settings.onepay.instance_name_help') ?></small>
        </div>

        <div class="form-group">
            <label for="api_key"><i class="fas fa-fw fa-sm fa-key text-muted mr-1"></i> <?= l('admin_settings.onepay.api_key') ?></label>
            <input id="api_key" type="text" name="api_key" class="form-control" value="<?= $onepay->api_key ?? '' ?>" />
        </div>

        <div class="form-group">
            <label for="webhook_signing_key"><i class="fas fa-fw fa-sm fa-lock text-muted mr-1"></i> <?= l('admin_settings.onepay.webhook_signing_key') ?></label>
            <input id="webhook_signing_key" type="text" name="webhook_signing_key" class="form-control" value="<?= $onepay->webhook_signing_key ?? '' ?>" />
            <small class="form-text text-muted"><?= l('admin_settings.onepay.webhook_signing_key_help') ?></small>
        </div>

        <div class="form-group">
            <label for="look_and_feel_profile"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('admin_settings.onepay.look_and_feel_profile') ?></label>
            <input id="look_and_feel_profile" type="text" name="look_and_feel_profile" class="form-control" value="<?= isset($onepay->look_and_feel_profile) ? $onepay->look_and_feel_profile : '' ?>" />
            <small class="form-text text-muted"><?= l('admin_settings.onepay.look_and_feel_profile_help') ?></small>
        </div>

        <div class="form-group">
            <label for="subscription_cancellation_interval"><i class="fas fa-fw fa-sm fa-calendar-times text-muted mr-1"></i> <?= l('admin_settings.onepay.subscription_cancellation_interval') ?></label>
            <input id="subscription_cancellation_interval" type="text" name="subscription_cancellation_interval" class="form-control" value="<?= isset($onepay->subscription_cancellation_interval) ? $onepay->subscription_cancellation_interval : '' ?>" placeholder="P1M" />
            <small class="form-text text-muted"><?= l('admin_settings.onepay.subscription_cancellation_interval_help') ?></small>
        </div>

        <div class="form-group">
            <label><i class="fas fa-fw fa-sm fa-coins text-muted mr-1"></i> <?= l('admin_settings.payment.currencies') ?></label>
            <div class="row">
                <?php foreach((array) settings()->payment->currencies as $currency => $currency_data): ?>
                    <div class="col-12 col-lg-4">
                        <div class="custom-control custom-checkbox my-2">
                            <input id="<?= 'currency_' . $currency ?>" name="currencies[]" value="<?= $currency ?>" type="checkbox" class="custom-control-input" <?= in_array($currency, $onepay->currencies ?? []) ? 'checked="checked"' : null ?>>
                            <label class="custom-control-label d-flex align-items-center" for="<?= 'currency_' . $currency ?>">
                                <span><?= $currency ?></span>
                            </label>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>

        <div class="form-group">
            <label for="webhook_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('admin_settings.payment.webhook_url') ?></label>
            <input type="text" id="webhook_url" value="<?= SITE_URL . 'webhook-onepay' ?>" class="form-control" onclick="this.select();" readonly="readonly" />
        </div>
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
