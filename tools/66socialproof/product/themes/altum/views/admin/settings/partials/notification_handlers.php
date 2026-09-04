<?php defined('ALTUMCODE') || die() ?>

<?php $available_notification_handlers = require APP_PATH . 'includes/available_notification_handlers.php' ?>

<div id="notification_handlers">
    <div class="alert alert-info mb-3"><?= sprintf(l('admin_settings.documentation'), '<a href="' . PRODUCT_DOCUMENTATION_URL . '#notification-handlers" target="_blank">', '</a>') ?></div>

    <div class="form-group custom-control custom-switch">
        <input id="is_enabled" name="is_enabled" type="checkbox" class="custom-control-input" <?= settings()->notification_handlers->is_enabled ? 'checked="checked"' : null ?>>
        <label class="custom-control-label" for="is_enabled"><?= l('admin_settings.notification_handlers.is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.notification_handlers.is_enabled_help') ?></small>
    </div>

    <?php foreach($available_notification_handlers as $type => $value): ?>
        <?php if(in_array($type, ['whatsapp', 'twilio', 'twilio_call', 'sixsixtext_send_sms', 'sixsixtext_save_contact'])) continue; ?>

        <div class="form-group custom-control custom-switch">
            <input id="<?= $type . '_is_enabled' ?>" name="<?= $type . '_is_enabled' ?>" type="checkbox" class="custom-control-input" <?= settings()->notification_handlers->{$type . '_is_enabled'} ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="<?= $type . '_is_enabled' ?>">
                <i class="<?= $value['icon'] ?> fa-fw fa-sm text-muted mr-1"></i> <?= sprintf(l('admin_settings.notification_handlers.is_enabled_x'), l('notification_handlers.type_' . $type)) ?>
            </label>
        </div>
    <?php endforeach ?>

    <?php if(array_key_exists('sixsixtext_send_sms', $available_notification_handlers) || array_key_exists('sixsixtext_save_contact', $available_notification_handlers)): ?>
    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#sixsixtext_container" aria-expanded="false" aria-controls="sixsixtext_container">
        <i class="fas fa-fw fa-comment fa-sm mr-1"></i> <?= l('admin_settings.notification_handlers.sixsixtext') ?>
    </button>

    <div class="collapse" data-parent="#notification_handlers" id="sixsixtext_container">
        <div class="alert alert-info">
            <i class="fas fa-fw fa-sm fa-info-circle mr-2"></i>
            <?= l('admin_settings.notification_handlers.sixsixtext_help') ?>
        </div>

        <?php if(array_key_exists('sixsixtext_send_sms', $available_notification_handlers)): ?>
        <div class="form-group custom-control custom-switch">
            <input id="sixsixtext_send_sms_is_enabled" name="sixsixtext_send_sms_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->notification_handlers->sixsixtext_send_sms_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="sixsixtext_send_sms_is_enabled"><?= l('admin_settings.notification_handlers.sixsixtext_send_sms_is_enabled') ?></label>
            <small class="form-text text-muted"><?= l('admin_settings.notification_handlers.sixsixtext_send_sms_is_enabled_help') ?></small>
        </div>
        <?php endif ?>

        <?php if(array_key_exists('sixsixtext_save_contact', $available_notification_handlers)): ?>
        <div class="form-group custom-control custom-switch">
            <input id="sixsixtext_save_contact_is_enabled" name="sixsixtext_save_contact_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->notification_handlers->sixsixtext_save_contact_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="sixsixtext_save_contact_is_enabled"><?= l('admin_settings.notification_handlers.sixsixtext_save_contact_is_enabled') ?></label>
            <small class="form-text text-muted"><?= l('admin_settings.notification_handlers.sixsixtext_save_contact_is_enabled_help') ?></small>
        </div>
        <?php endif ?>

        <div class="form-group">
            <label for="sixsixtext_name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
            <input id="sixsixtext_name" name="sixsixtext_name" type="text" class="form-control" value="<?= settings()->notification_handlers->sixsixtext_name ?? '' ?>" />
            <small class="form-text text-muted"><?= l('admin_settings.notification_handlers.sixsixtext_name_help') ?></small>
        </div>

        <div class="form-group">
            <label for="sixsixtext_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('global.url') ?></label>
            <input id="sixsixtext_url" name="sixsixtext_url" type="url" class="form-control" value="<?= settings()->notification_handlers->sixsixtext_url ?? '' ?>" placeholder="<?= l('global.url_placeholder') ?>" />
            <small class="form-text text-muted"><?= l('admin_settings.notification_handlers.sixsixtext_url_help') ?></small>
        </div>
    </div>
    <?php endif ?>

    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#twilio_container" aria-expanded="false" aria-controls="twilio_container">
        <i class="fas fa-fw fa-braille fa-sm mr-1"></i> <?= l('admin_settings.notification_handlers.twilio') ?>
    </button>

    <div class="collapse" data-parent="#notification_handlers" id="twilio_container">
        <div class="form-group custom-control custom-switch">
            <input id="twilio_is_enabled" name="twilio_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->notification_handlers->twilio_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="twilio_is_enabled"><?= sprintf(l('admin_settings.notification_handlers.is_enabled_x'), l('notification_handlers.type_twilio')) ?></label>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="twilio_call_is_enabled" name="twilio_call_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->notification_handlers->twilio_call_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="twilio_call_is_enabled"><?= sprintf(l('admin_settings.notification_handlers.is_enabled_x'), l('notification_handlers.type_twilio_call')) ?></label>
        </div>

        <div class="form-group">
            <label for="twilio_sid"><i class="fas fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('admin_settings.notification_handlers.twilio_sid') ?></label>
            <input id="twilio_sid" type="text" name="twilio_sid" class="form-control" value="<?= settings()->notification_handlers->twilio_sid ?>" />
        </div>

        <div class="form-group">
            <label for="twilio_token"><i class="fas fa-fw fa-sm fa-lock text-muted mr-1"></i> <?= l('admin_settings.notification_handlers.twilio_token') ?></label>
            <input id="twilio_token" type="text" name="twilio_token" class="form-control" value="<?= settings()->notification_handlers->twilio_token ?>" />
        </div>

        <div class="form-group">
            <label for="twilio_number"><i class="fas fa-fw fa-sm fa-phone text-muted mr-1"></i> <?= l('admin_settings.notification_handlers.twilio_number') ?></label>
            <input id="twilio_number" type="text" name="twilio_number" class="form-control" value="<?= settings()->notification_handlers->twilio_number ?>" />
        </div>
    </div>

    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#whatsapp_container" aria-expanded="false" aria-controls="whatsapp_container">
        <i class="fab fa-fw fa-whatsapp fa-sm mr-1"></i> <?= l('admin_settings.notification_handlers.whatsapp') ?>
    </button>

    <div class="collapse" data-parent="#notification_handlers" id="whatsapp_container">
        <div class="form-group custom-control custom-switch">
            <input id="whatsapp_is_enabled" name="whatsapp_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->notification_handlers->whatsapp_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="whatsapp_is_enabled"><?= sprintf(l('admin_settings.notification_handlers.is_enabled_x'), l('notification_handlers.type_whatsapp')) ?></label>
        </div>

        <div class="form-group">
            <label for="whatsapp_number_id"><i class="fas fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('admin_settings.notification_handlers.whatsapp_number_id') ?></label>
            <input id="whatsapp_number_id" type="text" name="whatsapp_number_id" class="form-control" value="<?= settings()->notification_handlers->whatsapp_number_id ?>" />
        </div>

        <div class="form-group">
            <label for="whatsapp_access_token"><i class="fas fa-fw fa-sm fa-lock text-muted mr-1"></i> <?= l('admin_settings.notification_handlers.whatsapp_access_token') ?></label>
            <input id="whatsapp_access_token" type="text" name="whatsapp_access_token" class="form-control" value="<?= settings()->notification_handlers->whatsapp_access_token ?>" />
        </div>
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
