<?php defined('ALTUMCODE') || die() ?>

<div>
    <div <?= !\Altum\Plugin::is_active('chrome-extension') ? 'data-toggle="tooltip" title="' . sprintf(l('admin_plugins.no_access'), \Altum\Plugin::get('chrome-extension')->name ?? 'chrome-extension') . '"' : null ?>>
        <div class="<?= !\Altum\Plugin::is_active('chrome-extension') ? 'container-disabled' : null ?>">
            <div class="form-group custom-control custom-switch">
                <input id="is_enabled" name="is_enabled" type="checkbox" class="custom-control-input" <?= \Altum\Plugin::is_active('chrome-extension') && settings()->chrome_extension->is_enabled ? 'checked="checked"' : null?>>
                <label class="custom-control-label" for="is_enabled"><?= l('admin_settings.chrome_extension.is_enabled') ?></label>
            </div>

            <div class="form-group">
                <label for="chrome_web_store_url"><i class="fab fa-fw fa-sm fa-chrome text-muted mr-1"></i> <?= l('admin_settings.chrome_extension.chrome_web_store_url') ?></label>
                <input id="chrome_web_store_url" type="url" name="chrome_web_store_url" class="form-control" value="<?= settings()->chrome_extension->chrome_web_store_url ?>" />
                <small class="form-text text-muted"><?= l('admin_settings.chrome_extension.chrome_web_store_url_help') ?></small>
            </div>
        </div>
    </div>
</div>

<?php if(\Altum\Plugin::is_active('chrome-extension')): ?>
    <button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
<?php endif ?>

<?php ob_start() ?>
<script>
    'use strict';

    const is_enabled = document.getElementById('is_enabled');
    const chrome_web_store_url = document.getElementById('chrome_web_store_url');

    /* function to toggle required attributes */
    const toggle_required_fields = () => {
        if (is_enabled.checked) {
            chrome_web_store_url.setAttribute('required', 'required');
        } else {
            chrome_web_store_url.removeAttribute('required');
        }
    };

    /* run on page load */
    toggle_required_fields();

    /* run on checkbox toggle */
    is_enabled.addEventListener('change', toggle_required_fields);
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
