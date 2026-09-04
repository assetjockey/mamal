<?php defined('ALTUMCODE') || die() ?>

<div>
    <div class="alert alert-info mb-3"><?= sprintf(l('admin_settings.documentation'), '<a href="' . PRODUCT_DOCUMENTATION_URL . '#social-logins" target="_blank">', '</a>') ?></div>
    
    <div class="form-group custom-control custom-switch">
        <input id="is_enabled" name="is_enabled" type="checkbox" class="custom-control-input" <?= settings()->apple->is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="is_enabled"><?= l('admin_settings.apple.is_enabled') ?></label>
    </div>

    <div class="form-group">
        <label for="client_id"><i class="fas fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('admin_settings.apple.client_id') ?></label>
        <input id="client_id" type="text" name="client_id" class="form-control" value="<?= settings()->apple->client_id ?>" />
    </div>

    <div class="form-group">
        <label for="team_id"><i class="fas fa-fw fa-sm fa-users text-muted mr-1"></i> <?= l('admin_settings.apple.team_id') ?></label>
        <input id="team_id" type="text" name="team_id" class="form-control" value="<?= settings()->apple->team_id ?>" />
    </div>

    <div class="form-group">
        <label for="key_id"><i class="fas fa-fw fa-sm fa-key text-muted mr-1"></i> <?= l('admin_settings.apple.key_id') ?></label>
        <input id="key_id" type="text" name="key_id" class="form-control" value="<?= settings()->apple->key_id ?>" />
    </div>

    <div class="form-group">
        <label for="key_content"><i class="fas fa-fw fa-sm fa-lock text-muted mr-1"></i> <?= l('admin_settings.apple.key_content') ?></label>
        <textarea id="key_content" name="key_content" class="form-control" rows="6"><?= e(settings()->apple->key_content) ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.apple.key_content_help') ?></small>
    </div>

    <div class="form-group">
        <label for="callback_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('admin_settings.social_logins.callback_url') ?></label>
        <input type="text" id="callback_url" value="<?= SITE_URL . 'login/apple' ?>" class="form-control" onclick="this.select();" readonly="readonly" />
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
