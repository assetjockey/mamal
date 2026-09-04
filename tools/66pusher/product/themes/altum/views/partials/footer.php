<?php defined('ALTUMCODE') || die() ?>

<div class="d-flex flex-column flex-lg-row justify-content-between">
    <div class="mb-3 mb-lg-0">
        <a
                class="h5 text-decoration-none footer-heading"
                href="<?= url() ?>"
                data-logo
                data-light-value="<?= settings()->main->logo_light != '' ? settings()->main->logo_light_full_url : settings()->main->title ?>"
                data-light-class="<?= settings()->main->logo_light != '' ? 'mb-2 footer-logo' : 'mb-2' ?>"
                data-light-tag="<?= settings()->main->logo_light != '' ? 'img' : 'span' ?>"
                data-dark-value="<?= settings()->main->logo_dark != '' ? settings()->main->logo_dark_full_url : settings()->main->title ?>"
                data-dark-class="<?= settings()->main->logo_dark != '' ? 'mb-2 footer-logo' : 'mb-2' ?>"
                data-dark-tag="<?= settings()->main->logo_dark != '' ? 'img' : 'span' ?>"
        >
            <?php if(settings()->main->{'logo_' . \Altum\ThemeStyle::get()} != ''): ?>
                <img src="<?= settings()->main->{'logo_' . \Altum\ThemeStyle::get() . '_full_url'} ?>" class="footer-logo" alt="<?= l('global.accessibility.logo_alt') ?>" />
            <?php else: ?>
                <span><?= settings()->main->title ?></span>
            <?php endif ?>
        </a>
    </div>

    <div class="d-flex flex-row flex-truncate">
        <?php if(count(\Altum\Language::$active_languages) > 1): ?>
            <div class="dropdown mr-3 ml-lg-3 mr-lg-0">
                <button type="button" class="btn btn-link text-decoration-none p-0" id="language_switch" data-tooltip data-tooltip-hide-on-click title="<?= l('global.choose_language') ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-fw fa-sm fa-language"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="language_switch">
                    <?php foreach(\Altum\Language::$languages_ordered as $language): ?>
                        <?php if($language['status']): ?>
                            <?php
                            $new_url = match(\Altum\Router::$controller_key) {
                                'pages', 'page' => SITE_URL . $language['code'] . '/' . 'pages',
                                'blog' => SITE_URL . $language['code'] . '/' . 'blog',
                                default => SITE_URL . $language['code'] . '/' . \Altum\Router::$original_request . (\Altum\Router::$original_request_query ? '?' . \Altum\Router::$original_request_query : null)
                            };
                            ?>
                            <a href="<?= $new_url ?>" class="dropdown-item" data-set-language="<?= $language['name'] ?>">
                                <?php if($language['name'] == \Altum\Language::$name): ?>
                                    <i class="fas fa-fw fa-sm fa-check mr-2 text-success"></i>
                                <?php else: ?>
                                    <?php if($language['language_flag']): ?>
                                        <span class="mr-2"><?= $language['language_flag'] ?></span>
                                    <?php else: ?>
                                        <i class="fas fa-fw fa-sm fa-circle-notch mr-2 text-muted"></i>
                                    <?php endif ?>
                                <?php endif ?>

                                <?= $language['name'] ?>
                            </a>
                        <?php endif ?>
                    <?php endforeach ?>
                </div>
            </div>

        <?php ob_start() ?>
            <script>
                'use strict';

                document.querySelectorAll('[data-set-language]').forEach(element => element.addEventListener('click', event => {
                    let language = event.currentTarget.getAttribute('data-set-language');
                    set_cookie(`set_language`, language, 90, <?= json_encode(COOKIE_PATH) ?>);
                }));
            </script>
            <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
        <?php endif ?>

        <?php if(\Altum\Router::$controller_settings['currency_switcher'] && count((array) settings()->payment->currencies ?? []) > 1): ?>
            <div class="dropdown mr-3 ml-lg-3 mr-lg-0">
                <button type="button" class="btn btn-link text-decoration-none p-0" id="currency_switch" data-tooltip data-tooltip-hide-on-click title="<?= l('global.choose_currency') ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-fw fa-sm fa-money-check-alt"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="currency_switch">
                    <?php foreach((array) settings()->payment->currencies as $currency => $currency_data): ?>
                        <a href="#" class="dropdown-item" data-set-currency="<?= $currency ?>">
                            <?php if($currency == currency()): ?>
                                <i class="fas fa-fw fa-sm fa-check mr-2 text-success"></i>
                            <?php else: ?>
                                <span class="fas fa-fw text-muted mr-2"><?= $currency_data->symbol ?: '&nbsp;' ?></span>
                            <?php endif ?>

                            <?= $currency ?>
                        </a>
                    <?php endforeach ?>
                </div>
            </div>

        <?php ob_start() ?>
            <script>
                'use strict';

                document.querySelectorAll('[data-set-currency]').forEach(element => element.addEventListener('click', event => {
                    let currency = event.currentTarget.getAttribute('data-set-currency');
                    set_cookie(`set_currency`, currency, 90, <?= json_encode(COOKIE_PATH) ?>);
                    window.location.reload();
                    event.preventDefault();
                }));
            </script>
            <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
        <?php endif ?>

        <?php if(is_logged_in() && ((user()->type == 1 && settings()->main->admin_spotlight_is_enabled) || (settings()->main->user_spotlight_is_enabled && user()->type == 0))): ?>
            <div class="mr-3 ml-lg-3 mr-lg-0">
                <button type="button" class="btn btn-link text-decoration-none p-0" data-toggle="tooltip" title="<?= l('global.spotlight.tooltip') ?>" aria-label="<?= l('global.spotlight.tooltip') ?>" onclick="spotlight_display()" data-tooltip-hide-on-click>
                    <i class="fas fa-fw fa-sm fa-search"></i>
                </button>
            </div>
        <?php endif ?>

        <?php if(settings()->main->theme_style_change_is_enabled): ?>
            <div class="mr-3 ml-lg-3 mr-lg-0">
                <button type="button" id="switch_theme_style" class="btn btn-link text-decoration-none p-0" data-toggle="tooltip" title="<?= sprintf(l('global.theme_style'), (\Altum\ThemeStyle::get() == 'light' ? l('global.theme_style_dark') : l('global.theme_style_light'))) ?>" aria-label="<?= sprintf(l('global.theme_style'), (\Altum\ThemeStyle::get() == 'light' ? l('global.theme_style_dark') : l('global.theme_style_light'))) ?>" data-title-theme-style-light="<?= sprintf(l('global.theme_style'), l('global.theme_style_light')) ?>" data-title-theme-style-dark="<?= sprintf(l('global.theme_style'), l('global.theme_style_dark')) ?>">
                    <span data-theme-style="light" class="<?= \Altum\ThemeStyle::get() == 'light' ? null : 'd-none' ?>"><i class="fas fa-fw fa-sm fa-sun text-warning"></i></span>
                    <span data-theme-style="dark" class="<?= \Altum\ThemeStyle::get() == 'dark' ? null : 'd-none' ?>"><i class="fas fa-fw fa-sm fa-moon"></i></span>
                </button>
            </div>

            <?php include_view(THEME_PATH . 'views/partials/theme_style_js.php') ?>
        <?php endif ?>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6 col-lg-4 p-3">
        <?php $footer_description = is_logged_in() && settings()->main->white_labeling_is_enabled && $this->user->plan_settings->white_labeling_is_enabled && $this->user->preferences->white_label_footer_description ? $this->user->preferences->white_label_footer_description : l('global.footer.description') ?>
        <div class="text-muted font-size-small">
            <?= $footer_description ?>
        </div>

        <?php $display_socials = true ?>
        <?php $display_socials = !(is_logged_in() && settings()->main->white_labeling_is_enabled && $this->user->plan_settings->white_labeling_is_enabled && $this->user->preferences->white_label_remove_socials && \Altum\Router::$controller_key != 'invoice' && \Altum\Router::$path != 'admin'); ?>

        <?php if($display_socials): ?>
            <div class="d-flex flex-wrap gap-3 mt-4">
                <?php foreach(require APP_PATH . 'includes/admin_socials.php' as $key => $value): ?>
                    <?php if(isset(settings()->socials->{$key}) && !empty(settings()->socials->{$key})): ?>
                        <a href="<?= sprintf($value['format'], settings()->socials->{$key}) ?>" target="_blank" rel="noreferrer" data-toggle="tooltip" title="<?= $value['name'] ?>">
                            <div class="p-2 footer-social-wrapper" style="background-color: <?= $value['background_color'] ?>;">
                                <i class="<?= $value['icon'] ?> fa-fw fa-xs" style="color: <?= $value['color'] ?>;"></i>
                            </div>
                        </a>
                    <?php endif ?>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>

    <?php $display_footer_links = true ?>
    <?php $display_footer_links = !(is_logged_in() && settings()->main->white_labeling_is_enabled && $this->user->plan_settings->white_labeling_is_enabled && $this->user->preferences->white_label_remove_footer_links && \Altum\Router::$controller_key != 'invoice' && \Altum\Router::$path != 'admin'); ?>

    <?php if($display_footer_links): ?>
        <?php $footer_categories = explode(',', l('global.footer.categories')) ?>
        <div class="d-none d-lg-block col-lg-2"></div>

        <?php foreach($footer_categories as $footer_category_id): ?>
            <?php ob_start() ?>
            <?php foreach($data->pages as $row): ?>
                <?php if($row->footer_category_id != $footer_category_id) continue ?>
                <?php $page = process_dynamic_page_link($row) ?>
                <?php if(!$page) continue ?>

                <li class="mb-2">
                    <a
                            href="<?= $page['url'] ?>"
                            target="<?= $page['target'] ?>"
                    <?php foreach($page['attributes'] as $attribute_key => $attribute_value): ?><?= $attribute_key ?>="<?= $attribute_value ?>" <?php endforeach ?>
                    >
                    <?php if($page['icon']): ?>
                        <i class="<?= $page['icon'] ?> fa-fw fa-xs mr-1"></i>
                    <?php endif ?>

                    <?= $page['title'] ?>
                    </a>
                </li>
            <?php endforeach ?>
            <?php $footer_category_links_html = trim(ob_get_clean()) ?>

            <?php if(!empty($footer_category_links_html)): ?>
                <div class="col-sm-6 col-md-6 col-lg-3 p-3">
                    <ul class="list-style-none d-flex flex-column flex-wrap m-0 font-size-smaller">
                        <li class="mb-2 font-weight-600"><?= l('global.footer.categories.' . $footer_category_id) ?></li>

                        <?= $footer_category_links_html ?>
                    </ul>
                </div>
            <?php endif ?>
        <?php endforeach ?>
    <?php endif ?>
</div>

<div class="text-center text-muted font-size-xs mt-4"><?= sprintf(l('global.footer.copyright'), date('Y'), settings()->main->title) ?></div>
