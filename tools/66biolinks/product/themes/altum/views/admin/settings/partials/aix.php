<?php defined('ALTUMCODE') || die() ?>

<div>
    <h2 class="h5">OpenAI</h2>
    <p class="text-muted">Used for <code>NSFW moderation</code>, <code>AI Images</code>, <code>AI Transcriptions</code>, <code>AI Syntheses</code>, <code>AI Chats</code>.</p>
    <div class="form-group">
        <label for="openai_api_key"><i class="fas fa-fw fa-sm fa-key text-muted mr-1"></i> <?= l('admin_settings.aix.openai_api_key') ?></label>
        <textarea id="openai_api_key" name="openai_api_key" class="form-control"><?= settings()->aix->openai_api_key ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.aix.openai_api_key_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="input_moderation_is_enabled" name="input_moderation_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->aix->input_moderation_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="input_moderation_is_enabled"><?= l('admin_settings.aix.input_moderation_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.aix.input_moderation_is_enabled_help') ?></small>
    </div>

    <button class="btn btn-block btn-gray-100 font-size-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#images_container" aria-expanded="false" aria-controls="images_container">
        <i class="fas fa-fw fa-icons fa-sm mr-1"></i> <?= l('admin_images.menu') ?>
    </button>

    <div class="collapse" id="images_container">
        <div class="form-group custom-control custom-switch">
            <input id="images_is_enabled" name="images_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->aix->images_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="images_is_enabled"><?= l('admin_settings.aix.images_is_enabled') ?></label>
        </div>
    </div>

    <button class="btn btn-block btn-gray-100 font-size-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#transcriptions_container" aria-expanded="false" aria-controls="transcriptions_container">
        <i class="fas fa-fw fa-microphone-alt fa-sm mr-1"></i> <?= l('admin_transcriptions.menu') ?>
    </button>

    <div class="collapse" id="transcriptions_container">
        <div class="form-group custom-control custom-switch">
            <input id="transcriptions_is_enabled" name="transcriptions_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->aix->transcriptions_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="transcriptions_is_enabled"><?= l('admin_settings.aix.transcriptions_is_enabled') ?></label>
        </div>
    </div>

    <button class="btn btn-block btn-gray-100 font-size-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#chats_container" aria-expanded="false" aria-controls="chats_container">
        <i class="fas fa-fw fa-comments fa-sm mr-1"></i> <?= l('admin_chats.menu') ?>
    </button>

    <div class="collapse" id="chats_container">
        <div class="form-group custom-control custom-switch">
            <input id="chats_is_enabled" name="chats_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->aix->chats_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="chats_is_enabled"><?= l('admin_settings.aix.chats_is_enabled') ?></label>
        </div>
    </div>

    <button class="btn btn-block btn-gray-100 font-size-small font-weight-450 my-4" type="button" data-toggle="collapse" data-target="#syntheses_container" aria-expanded="false" aria-controls="syntheses_container">
        <i class="fas fa-fw fa-voicemail fa-sm mr-1"></i> <?= l('admin_syntheses.menu') ?>
    </button>

    <div class="collapse" id="syntheses_container">
        <div class="form-group custom-control custom-switch">
            <input id="syntheses_is_enabled" name="syntheses_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->aix->syntheses_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="syntheses_is_enabled"><?= l('admin_settings.aix.syntheses_is_enabled') ?></label>
        </div>
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
