<?php defined('ALTUMCODE') || die() ?>

<footer class="container status-page-footer">
    <div class="row">
        <div class="col-lg-4">
            <div class="d-flex flex-column mb-4 mb-lg-0">
                <div><?= sprintf(l('global.footer.copyright'), date('Y'), $this->status_page->name) ?></div>

                <?php if(!$this->status_page->is_removed_branding || ($this->status_page->is_removed_branding && !$this->status_page_user->plan_settings->removable_branding_is_enabled)) :?>
                    <div class="mt-2 text-center small text-lg-left">
                        <?php
                        $replacers = [
                            '{{URL}}' => url(),
                                '{{DASHBOARD_LINK}}' => url('dashboard'),
                            '{{WEBSITE_TITLE}}' => settings()->main->title,
                            '{{AFFILIATE_URL_TAG}}' => \Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled ? '?ref=' . $this->status_page_user->referral_key : null,
                        ];

                        settings()->status_pages->branding = str_replace(
                            array_keys($replacers),
                            array_values($replacers),
                            settings()->status_pages->branding
                        );
                        ?>

                        <?= settings()->status_pages->branding ?>
                    </div>
                <?php endif ?>
            </div>
        </div>

        <div class="col-lg-4 mb-4 mb-lg-0 text-lg-center">
            <?php foreach(require APP_PATH . 'includes/s/socials.php' as $key => $value): ?>
                <?php if($this->status_page->socials->{$key}): ?>

                    <a href="<?= sprintf($value['format'], $this->status_page->socials->{$key}) ?>" target="_blank" class="mx-2" title="<?= $value['name'] ?>"><div class="svg-md text-muted d-inline-block"><?= include_view(ASSETS_PATH . '/images/icons/' . $key . '.svg') ?></div></a>

                <?php endif ?>
            <?php endforeach ?>
        </div>

            <div class="col-lg-4 mb-0 mb-lg-0 text-lg-right">
                <?php if(settings()->main->theme_style_change_is_enabled): ?>
                    <button type="button" id="switch_theme_style" class="btn btn-link text-decoration-none p-0" title="<?= sprintf(l('global.theme_style'), (\Altum\ThemeStyle::get() == 'light' ? l('global.theme_style_dark') : l('global.theme_style_light'))) ?>" aria-label="<?= sprintf(l('global.theme_style'), (\Altum\ThemeStyle::get() == 'light' ? l('global.theme_style_dark') : l('global.theme_style_light'))) ?>" data-title-theme-style-light="<?= sprintf(l('global.theme_style'), l('global.theme_style_light')) ?>" data-title-theme-style-dark="<?= sprintf(l('global.theme_style'), l('global.theme_style_dark')) ?>">
                        <span data-theme-style="light" class="<?= \Altum\ThemeStyle::get() == 'light' ? null : 'd-none' ?>">☀️</span>
                        <span data-theme-style="dark" class="<?= \Altum\ThemeStyle::get() == 'dark' ? null : 'd-none' ?>">🌙</span>
                    </button>

                    <?php include_view(THEME_PATH . 'views/partials/theme_style_js.php') ?>
                <?php endif ?>

                <a href="#" class="text-muted text-decoration-none ml-1" onclick="toggle_fullscreen(event)" title="<?= l('s_status_page.toggle_fullscreen') ?>">⿻</a>

                <?php ob_start() ?>
                <script>
                    'use strict';

                    let toggle_fullscreen = event => {
                        if((document.fullScreenElement && document.fullScreenElement !== null) ||
                            (!document.mozFullScreen && !document.webkitIsFullScreen)) {
                            if(document.documentElement.requestFullScreen) {
                                document.documentElement.requestFullScreen();
                            } else if(document.documentElement.mozRequestFullScreen) {
                                document.documentElement.mozRequestFullScreen();
                            } else if(document.documentElement.webkitRequestFullScreen) {
                                document.documentElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
                            }
                        } else {
                            if(document.cancelFullScreen) {
                                document.cancelFullScreen();
                            } else if(document.mozCancelFullScreen) {
                                document.mozCancelFullScreen();
                            } else if(document.webkitCancelFullScreen) {
                                document.webkitCancelFullScreen();
                            }
                        }
                        event.preventDefault();
                        event.stopPropagation();
                    }
                </script>
                <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
            </div>

    </div>
</footer>
