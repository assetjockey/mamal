<?php defined('ALTUMCODE') || die() ?>

<footer class="container status-page-footer">
    <div class="row">
        <div class="col-lg-4">
            <div class="d-flex flex-column mb-4 mb-lg-0">
                <div><?= sprintf(l('global.footer.copyright'), date('Y'), $this->website->name) ?></div>


            </div>
        </div>

        <div class="col-lg-4 mb-4 mb-lg-0 text-lg-center">

        </div>

        <?php if(settings()->main->theme_style_change_is_enabled): ?>

            <div class="col-lg-4 mb-0 mb-lg-0 text-lg-right">
                <button type="button" id="switch_theme_style" class="btn btn-link text-decoration-none p-0" title="<?= sprintf(l('global.theme_style'), (\Altum\ThemeStyle::get() == 'light' ? l('global.theme_style_dark') : l('global.theme_style_light'))) ?>" aria-label="<?= sprintf(l('global.theme_style'), (\Altum\ThemeStyle::get() == 'light' ? l('global.theme_style_dark') : l('global.theme_style_light'))) ?>" data-title-theme-style-light="<?= sprintf(l('global.theme_style'), l('global.theme_style_light')) ?>" data-title-theme-style-dark="<?= sprintf(l('global.theme_style'), l('global.theme_style_dark')) ?>">
                    <span data-theme-style="light" class="<?= \Altum\ThemeStyle::get() == 'light' ? null : 'd-none' ?>">☀️</span>
                    <span data-theme-style="dark" class="<?= \Altum\ThemeStyle::get() == 'dark' ? null : 'd-none' ?>">🌙</span>
                </button>

                <?php include_view(THEME_PATH . 'views/partials/theme_style_js.php') ?>
            </div>

        <?php endif ?>
    </div>
</footer>
