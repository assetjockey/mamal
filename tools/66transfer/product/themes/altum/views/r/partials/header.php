<?php defined('ALTUMCODE') || die() ?>

<?php if(!$this->transfer_request->settings->is_removed_branding || ($this->transfer_request->settings->is_removed_branding && !$this->transfer_request_user->plan_settings->removable_branding_is_enabled)) :?>
    <div class="text-center mb-4">
        <a
                href="<?= url() ?>"
                target="_blank"
                data-logo
                data-light-value="<?= settings()->main->logo_light != '' ? settings()->main->logo_light_full_url : settings()->main->title ?>"
                data-light-class="<?= settings()->main->logo_light != '' ? 'transfer-logo' : 'h3' ?>"
                data-light-tag="<?= settings()->main->logo_light != '' ? 'img' : 'span' ?>"
                data-dark-value="<?= settings()->main->logo_dark != '' ? settings()->main->logo_dark_full_url : settings()->main->title ?>"
                data-dark-class="<?= settings()->main->logo_dark != '' ? 'transfer-logo' : 'h3' ?>"
                data-dark-tag="<?= settings()->main->logo_dark != '' ? 'img' : 'span' ?>"
        >
            <?php if(settings()->main->{'logo_' . \Altum\ThemeStyle::get()} != ''): ?>
                <img src="<?= settings()->main->{'logo_' . \Altum\ThemeStyle::get() . '_full_url'} ?>" class="transfer-logo" alt="<?= l('global.accessibility.logo_alt') ?>" />
            <?php else: ?>
                <span class="h3"><?= settings()->main->title ?></span>
            <?php endif ?>
        </a>
    </div>
<?php endif ?>
