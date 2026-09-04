<?php defined('ALTUMCODE') || die() ?>

<footer class="container mb-6 mt-4">
    <div class="d-flex align-items-center justify-content-center">
        <div class="d-flex flex-column">
            <?php if(!$this->transfer->settings->is_removed_branding || ($this->transfer->settings->is_removed_branding && !$this->transfer_user->plan_settings->removable_branding_is_enabled)) :?>
                <div class="text-center text-lg-left mb-2 small">
                    <?php
                    $replacers = [
                        '{{URL}}' => url(),
                        '{{WEBSITE_TITLE}}' => settings()->main->title,
                        '{{AFFILIATE_URL_TAG}}' => \Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled ? '?ref=' . $this->transfer_user->referral_key : null,
                    ];

                    settings()->transfers->branding = str_replace(
                        array_keys($replacers),
                        array_values($replacers),
                        settings()->transfers->branding
                    );
                    ?>

                    <?= settings()->transfers->branding ?>
                </div>
            <?php endif ?>

            <div class="text-center mb-2">
            </div>

            <?php if(settings()->main->theme_style_change_is_enabled): ?>
                <div class="mb-0 mb-lg-0 text-center">
                    <button type="button" id="switch_theme_style" class="btn btn-link text-decoration-none p-0" title="<?= sprintf(l('global.theme_style'), (\Altum\ThemeStyle::get() == 'light' ? l('global.theme_style_dark') : l('global.theme_style_light'))) ?>" aria-label="<?= sprintf(l('global.theme_style'), (\Altum\ThemeStyle::get() == 'light' ? l('global.theme_style_dark') : l('global.theme_style_light'))) ?>" data-title-theme-style-light="<?= sprintf(l('global.theme_style'), l('global.theme_style_light')) ?>" data-title-theme-style-dark="<?= sprintf(l('global.theme_style'), l('global.theme_style_dark')) ?>">
                        <span data-theme-style="light" class="<?= \Altum\ThemeStyle::get() == 'light' ? null : 'd-none' ?>">☀️</span>
                        <span data-theme-style="dark" class="<?= \Altum\ThemeStyle::get() == 'dark' ? null : 'd-none' ?>">🌙</span>
                    </button>
                </div>

                <?php include_view(THEME_PATH . 'views/partials/theme_style_js.php') ?>
            <?php endif ?>
        </div>
    </div>
</footer>

<?php ob_start() ?>
<?= $this->views['pixels'] ?? null ?>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
