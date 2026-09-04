<?php defined('ALTUMCODE') || die() ?>
<!DOCTYPE html>
<html lang="<?= $this->status_page->settings->language_code ?? \Altum\Language::$default_code ?>" dir="<?= l('direction') ?>">
    <head>
        <title><?= \Altum\Title::get() ?></title>
        <base href="<?= SITE_URL ?>">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

        <?php if(\Altum\Plugin::is_active('pwa') && settings()->pwa->is_enabled): ?>
            <?php if($this->status_page_user->plan_settings->custom_pwa_is_enabled && $this->status_page->settings->pwa_is_enabled && !empty($this->status_page->settings->pwa_file_name)): ?>
                <link rel="manifest" href="<?= SITE_URL . UPLOADS_URL_PATH . \Altum\Uploads::get_path('pwa') . $this->status_page->settings->pwa_file_name . '.json' ?>" />
                <meta name="theme-color" content="<?= $this->status_page->settings->pwa_theme_color ?>"/>
            <?php else: ?>
                <link rel="manifest" href="<?= SITE_URL . UPLOADS_URL_PATH . \Altum\Uploads::get_path('pwa') . 'manifest.json' ?>" />
                <meta name="theme-color" content="<?= settings()->pwa->theme_color ?>"/>
            <?php endif ?>

			<?php if(settings()->pwa->is_fullscreen ?? true): ?>
                <meta name="apple-mobile-web-app-capable" content="yes">
                <meta name="mobile-web-app-capable" content="yes">
                <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
			<?php endif ?>

			<?= pwa_generate_dynamic_splash_screen_links() ?>
        <?php endif ?>

        <?php if(\Altum\Meta::$description): ?>
            <meta name="description" content="<?= \Altum\Meta::$description ?>" />
        <?php endif ?>
        <?php if(\Altum\Meta::$keywords): ?>
            <meta name="keywords" content="<?= \Altum\Meta::$keywords ?>" />
        <?php endif ?>

        <?php \Altum\Meta::output() ?>

        <?php if(\Altum\Meta::$canonical): ?>
            <link rel="canonical" href="<?= \Altum\Meta::$canonical ?>" />
        <?php endif ?>

        <?php if(isset($this->status_page) && (!$this->status_page_user->plan_settings->search_engine_visibility_is_enabled || !$this->status_page->is_se_visible)): ?>
            <meta name="robots" content="noindex">
        <?php endif ?>

        <?php if(isset($this->status_page) && $this->status_page->favicon): ?>
            <link href="<?= \Altum\Uploads::get_full_url('status_pages_favicons') . $this->status_page->favicon ?>" rel="icon" />
        <?php else: ?>
            <link href="<?= !empty(settings()->main->favicon) ? settings()->main->favicon_full_url : 'data:,' ?>" rel="icon" />
        <?php endif ?>

        <link href="<?= ASSETS_FULL_URL . 'css/' . \Altum\ThemeStyle::get_file() . '?v=' . PRODUCT_CODE ?>" id="css_theme_style" rel="stylesheet" media="screen,print">
        <?php foreach(['status-page-custom.' . (DEBUG ? null : 'min.') . 'css'] as $file): ?>
            <link href="<?= ASSETS_FULL_URL . 'css/' . $file . '?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
        <?php endforeach ?>

        <?= \Altum\Event::get_content('head') ?>

        <?php if(is_logged_in() && !user()->plan_settings->export->pdf): ?>
            <style>@media print { body { display: none; } }</style>
        <?php endif ?>

        <?php if(!empty(settings()->custom->head_js_status_page)): ?>
            <?= get_settings_custom_head_js('head_js_status_page') ?>
        <?php endif ?>

        <?php if(!empty(settings()->custom->head_css_status_page)): ?>
            <style><?= settings()->custom->head_css_status_page ?></style>
        <?php endif ?>

        <?php if(!empty($this->status_page->custom_css) && $this->status_page_user->plan_settings->custom_css_is_enabled): ?>
            <style><?= $this->status_page->custom_css ?></style>
        <?php endif ?>

        <?php if($this->status_page->settings->font_family): ?>
            <?php $fonts = require APP_PATH . 'includes/s/fonts.php' ?>
            <?php if($fonts[$this->status_page->settings->font_family]['font_css_url']): ?>
                <link href="<?= $fonts[$this->status_page->settings->font_family]['font_css_url'] ?>" rel="stylesheet">
            <?php endif ?>

            <?php if($fonts[$this->status_page->settings->font_family]['font-family']): ?>
                <style>html, body {font-family: <?= $fonts[$this->status_page->settings->font_family]['font-family'] ?> !important;}</style>
            <?php endif ?>
        <?php endif ?>
        <style>html {font-size: <?= (int) ($this->status_page->settings->font_size ?? 16) . 'px' ?> !important;}</style>
    </head>

    <body class="<?= l('direction') == 'rtl' ? 'rtl' : null ?> <?= $this->status_page->theme ?>" data-theme-style="<?= \Altum\ThemeStyle::get() ?>">
        <?php if(!empty(settings()->custom->body_content_status_page)): ?>
            <?= settings()->custom->body_content_status_page ?>
        <?php endif ?>

        <?php require THEME_PATH . 'views/partials/cookie_consent.php' ?>
        <?php require THEME_PATH . 'views/partials/ad_blocker_detector.php' ?>

        <?php if(
            \Altum\Plugin::is_active('pwa')
            && settings()->pwa->is_enabled
            && $this->status_page->settings->pwa_is_enabled
            && $this->status_page->settings->pwa_display_install_bar
        ) echo include_view(\Altum\Plugin::get('pwa')->path . 'views/partials/pwa_custom.php', [
                'id' => md5($this->status_page->status_page_id),
                'display_delay' => $this->status_page->settings->pwa_display_install_bar_delay
            ]) ?>

        <?php require THEME_PATH . 'views/s/partials/ads_header.php' ?>

        <main class="altum-animate altum-animate-fill-none altum-animate-fade-in">
            <?= $this->views['content'] ?>
        </main>

        <?php require THEME_PATH . 'views/s/partials/ads_footer.php' ?>

        <?= $this->views['footer'] ?>

        <?php if(settings()->status_pages->report_is_enabled): ?>
            <div id="info" class="link-info">
                <a href="<?= url('contact?subject=' . urlencode(sprintf(l('s_status_page.report.subject'), remove_url_protocol_from_url($this->status_page->full_url))) . '&message=' . urlencode(l('s_status_page.report.message'))) ?>" target="_blank" title="<?= l('s_status_page.report') ?>">
                    <div class="svg-sm d-flex"><?= include_view(ASSETS_PATH . '/images/icons/warning.svg') ?></div>
                </a>
            </div>
        <?php endif ?>

        <?= \Altum\Event::get_content('modals') ?>

        <?php require THEME_PATH . 'views/partials/js_global_variables.php' ?>

        <?php foreach(['libraries/jquery.slim.min.js', 'libraries/popper.min.js', 'libraries/bootstrap.min.js', 'custom.' . (DEBUG ? null : 'min.') . 'js'] as $file): ?>
            <script src="<?= ASSETS_FULL_URL ?>js/<?= $file ?>?v=<?= PRODUCT_CODE ?>"></script>
        <?php endforeach ?>

        <?= \Altum\Event::get_content('javascript') ?>

        <?php if(!empty($this->status_page->custom_js) && $this->status_page_user->plan_settings->custom_js_is_enabled): ?>
            <?= $this->status_page->custom_js ?>
        <?php endif ?>
    </body>
</html>
