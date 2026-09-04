<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>
<script>
    'use strict';

    let switch_theme_style = document.querySelector('#switch_theme_style');
    let css_theme_style = document.querySelector(`#css_theme_style`);

    if(switch_theme_style) {
        switch_theme_style.addEventListener('click', event => {
            let theme_style_element = document.querySelector('body[data-theme-style]');
            let theme_style = document.querySelector('body[data-theme-style]').getAttribute('data-theme-style');
            let new_theme_style = theme_style == 'light' ? 'dark' : 'light';

            /* Set a cookie with the new theme style */
            set_cookie(`theme_style`, new_theme_style, 90, <?= json_encode(COOKIE_PATH) ?>);

            /* Change the css and button on the page */
            theme_style_element.setAttribute('data-theme-style', new_theme_style);

            switch (new_theme_style) {
                case 'dark':
                    css_theme_style.setAttribute('href', <?= json_encode(ASSETS_FULL_URL . 'css/' . (\Altum\Router::$path == 'admin' ? 'admin-' : (settings()->theme->dark_is_enabled ? 'custom-bootstrap/' : null)) . \Altum\ThemeStyle::$themes['dark'][l('direction')] . '?v=' . PRODUCT_CODE) ?>);
                    document.body.classList.add('cc--darkmode');
                    break;

                case 'light':
                    css_theme_style.setAttribute('href', <?= json_encode(ASSETS_FULL_URL . 'css/' . (\Altum\Router::$path == 'admin' ? 'admin-' : (settings()->theme->light_is_enabled ? 'custom-bootstrap/' : null)) . \Altum\ThemeStyle::$themes['light'][l('direction')] . '?v=' . PRODUCT_CODE) ?>);
                    document.body.classList.remove('cc--darkmode');
                    break;
            }

            /* Refresh the logo/title */
            document.querySelectorAll('[data-logo]').forEach(element => {
                let new_brand_value = element.getAttribute(`data-${new_theme_style}-value`);
                let new_brand_class = element.getAttribute(`data-${new_theme_style}-class`);
                let new_brand_tag = element.getAttribute(`data-${new_theme_style}-tag`)
                let new_brand_html = new_brand_tag == 'img' ? `<img src="${new_brand_value}" class="${new_brand_class}" alt="<?= l('global.accessibility.logo_alt') ?>" />` : `<${new_brand_tag} class="${new_brand_class}">${new_brand_value}</${new_brand_tag}>`;
                element.innerHTML = new_brand_html;
            });


            switch_theme_style.setAttribute('data-original-title', switch_theme_style.getAttribute(`data-title-theme-style-${theme_style}`));
            switch_theme_style.querySelector(`[data-theme-style="${new_theme_style}"]`).classList.remove('d-none');
            switch_theme_style.querySelector(`[data-theme-style="${theme_style}"]`).classList.add('d-none');

            if (typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip === 'function' && typeof Popper !== 'undefined') {
                $(switch_theme_style).tooltip('hide').tooltip('show');
            }

            event.preventDefault();
        });

        document.addEventListener('keydown', event => {
            if((event.ctrlKey || event.metaKey) && event.key === 'i') {
                event.preventDefault();
                switch_theme_style.click();

                if (typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip === 'function' && typeof Popper !== 'undefined') {
                    $(switch_theme_style).tooltip('hide');
                }
            }
        });
    }
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
