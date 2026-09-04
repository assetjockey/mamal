<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="text-center">
        <h1 class="h1 font-weight-700"><?= l('chrome_extension.header') ?></h1>

        <p class="text-muted font-size-little-small mb-4"><?= l('chrome_extension.subheader') ?></p>

        <a href="<?= settings()->chrome_extension->chrome_web_store_url ?>" class="btn btn-primary index-button rounded-2x border-0 mb-4">
            <?= l('chrome_extension.download') ?> <i class="fas fa-fw fa-sm fa-arrow-right"></i>
        </a>

        <?php if(is_logged_in()): ?>
        <div class="mt-3 font-size-small text-muted">
            <?= sprintf(l('chrome_extension.connect'), '<code class="font-weight-bold cursor-pointer" data-connect>' . l('chrome_extension.connect_button') . '</code>') ?>
        </div>
        <?php endif ?>
    </div>

    <?php if(is_logged_in()): ?>
    <div id="connect_details" class="row mt-3" hidden="hidden">
        <div class="col-12 p-3 text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                        <i class="fas fa-fw fa-sm fa-code text-muted"></i>
                    </div>
                </div>

                <div class="card-body text-truncate">
                    <div class="font-weight-bold text-muted small"><?= l('chrome_extension.api_key') ?></div>
                    <span><?= $this->user->api_key ?></span>
                </div>

                <div class="pr-3 d-flex flex-column justify-content-center">
                    <button
                            type="button"
                            class="btn btn-light p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center"
                            data-toggle="tooltip"
                            title="<?= l('global.clipboard_copy') ?>"
                            aria-label="<?= l('global.clipboard_copy') ?>"
                            data-copy="<?= l('global.clipboard_copy') ?>"
                            data-copied="<?= l('global.clipboard_copied') ?>"
                            data-clipboard-text="<?= $this->user->api_key ?>"
                    >
                        <i class="fas fa-fw fa-sm fa-copy text-muted"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-12 p-3 text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                        <i class="fas fa-fw fa-sm fa-link text-muted"></i>
                    </div>
                </div>

                <div class="card-body text-truncate">
                    <div class="font-weight-bold text-muted small"><?= l('chrome_extension.site_url') ?></div>
                    <span><?= SITE_URL ?></span>
                </div>

                <div class="pr-3 d-flex flex-column justify-content-center">
                    <button
                            type="button"
                            class="btn btn-light p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center"
                            data-toggle="tooltip"
                            title="<?= l('global.clipboard_copy') ?>"
                            aria-label="<?= l('global.clipboard_copy') ?>"
                            data-copy="<?= l('global.clipboard_copy') ?>"
                            data-copied="<?= l('global.clipboard_copied') ?>"
                            data-clipboard-text="<?= $data->subscriber->endpoint ?>"
                    >
                        <i class="fas fa-fw fa-sm fa-copy text-muted"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif ?>

    <div class="row mt-7 mx-n4">
        <div class="col-12 col-md-6 col-lg-4 p-4 up-animation">
            <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="100">
                <img src="<?= ASSETS_FULL_URL . 'images/chrome-extension/chrome-extension-popup.webp' ?>" class="img-fluid rounded-2x mb-4" loading="lazy" alt="<?= l('chrome_extension.popup.image_alt') ?>" />

                <div>
                    <div class="mb-2">
                        <span class="h5"><?= l('chrome_extension.popup.header') ?></span>
                    </div>
                    <span class="text-muted"><?= l('chrome_extension.popup.subheader') ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 p-4 up-animation">
            <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="200">
                <img src="<?= ASSETS_FULL_URL . 'images/chrome-extension/chrome-extension-context-menu.webp' ?>" class="img-fluid rounded-2x mb-4" loading="lazy" alt="<?= l('chrome_extension.context_menu.image_alt') ?>" />

                <div>
                    <div class="mb-2">
                        <span class="h5"><?= l('chrome_extension.context_menu.header') ?></span>
                    </div>
                    <span class="text-muted"><?= l('chrome_extension.context_menu.subheader') ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 p-4 up-animation">
            <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="300">
                <img src="<?= ASSETS_FULL_URL . 'images/chrome-extension/chrome-extension-last-shortened.webp' ?>" class="img-fluid rounded-2x mb-4" loading="lazy" alt="<?= l('chrome_extension.last_shortened.image_alt') ?>" />

                <div>
                    <div class="mb-2">
                        <span class="h5"><?= l('chrome_extension.last_shortened.header') ?></span>
                    </div>
                    <span class="text-muted"><?= l('chrome_extension.last_shortened.subheader') ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 p-4 up-animation">
            <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="400">
                <img src="<?= ASSETS_FULL_URL . 'images/chrome-extension/chrome-extension-domains.webp' ?>" class="img-fluid rounded-2x mb-4" loading="lazy" alt="<?= l('chrome_extension.domains.image_alt') ?>" />

                <div>
                    <div class="mb-2">
                        <span class="h5"><?= l('chrome_extension.domains.header') ?></span>
                    </div>
                    <span class="text-muted"><?= l('chrome_extension.domains.subheader') ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 p-4 up-animation">
            <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="500">
                <img src="<?= ASSETS_FULL_URL . 'images/chrome-extension/chrome-extension-settings.webp' ?>" class="img-fluid rounded-2x mb-4" loading="lazy" alt="<?= l('chrome_extension.settings.image_alt') ?>" />

                <div>
                    <div class="mb-2">
                        <span class="h5"><?= l('chrome_extension.settings.header') ?></span>
                    </div>
                    <span class="text-muted"><?= l('chrome_extension.settings.subheader') ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 p-4 up-animation">
            <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="600">
                <img src="<?= ASSETS_FULL_URL . 'images/chrome-extension/chrome-extension-shortcut.webp' ?>" class="img-fluid rounded-2x mb-4" loading="lazy" alt="<?= l('chrome_extension.shortcut.image_alt') ?>" />

                <div>
                    <div class="mb-2">
                        <span class="h5"><?= l('chrome_extension.shortcut.header') ?></span>
                    </div>
                    <span class="text-muted"><?= l('chrome_extension.shortcut.subheader') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-7">
        <h2 class="h4"><?= l('chrome_extension.faq.header') ?></h2>

        <?php
        $language_array = \Altum\Language::get(\Altum\Language::$name);
        if(\Altum\Language::$main_name != \Altum\Language::$name) {
            $language_array = array_merge(\Altum\Language::get(\Altum\Language::$main_name), $language_array);
        }

        $chrome_extension_language_keys = [];
        foreach ($language_array as $key => $value) {
            if(preg_match('/chrome_extension\.faq\.(\w+)\./', $key, $matches)) {
                $chrome_extension_language_keys[] = $matches[1];
            }
        }

        $chrome_extension_language_keys = array_unique($chrome_extension_language_keys);
        ?>

        <div class="accordion index-faq mt-4" id="faq_accordion">
            <?php foreach($chrome_extension_language_keys as $key): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="" id="<?= 'faq_accordion_' . $key ?>">
                            <h3 class="mb-0">
                                <button class="btn font-weight-500 btn-block d-flex justify-content-between text-gray-800 px-0 icon-zoom-animation no-focus text-left" type="button" data-toggle="collapse" data-target="<?= '#faq_accordion_answer_' . $key ?>" aria-expanded="true" aria-controls="<?= 'faq_accordion_answer_' . $key ?>">
                                    <span><?= l('chrome_extension.faq.' . $key . '.question') ?></span>

                                    <span data-icon>
                                        <i class="fas fa-fw fa-circle-chevron-down"></i>
                                    </span>
                                </button>
                            </h3>
                        </div>

                        <div id="<?= 'faq_accordion_answer_' . $key ?>" class="collapse text-muted mt-3" aria-labelledby="<?= 'faq_accordion_' . $key ?>" data-parent="#faq_accordion">
                            <?= l('chrome_extension.faq.' . $key . '.answer') ?>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>

    <?php ob_start() ?>
    <script>
        'use strict';

        $('#faq_accordion').on('show.bs.collapse', event => {
            let svg = event.target.parentElement.querySelector('[data-icon] svg')
            svg.style.transform = 'rotate(180deg)';
            svg.style.color = 'var(--primary)';
        })

        $('#faq_accordion').on('hide.bs.collapse', event => {
            let svg = event.target.parentElement.querySelector('[data-icon] svg')
            svg.style.color = 'var(--primary-800)';
            svg.style.removeProperty('transform');
        })
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
</div>

<?php ob_start() ?>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": <?= json_encode(l('index.title')) ?>,
                    "item": <?= json_encode(url()) ?>
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": <?= json_encode(l('chrome_extension.title')) ?>,
                    "item": <?= json_encode(url('chrome_extension')) ?>
                }
            ]
        }
    </script>

<?php
$faqs = [];
foreach($chrome_extension_language_keys as $key) {
    $text = l('chrome_extension.faq.' . $key . '.answer');

    $faqs[] = [
        '@type' => 'Question',
        'name' => l('chrome_extension.faq.' . $key . '.question'),
        'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $text,
        ]
    ];
}
?>
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": <?= json_encode($faqs) ?>
    }
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php ob_start() ?>
    <link href="<?= ASSETS_FULL_URL . 'css/index-custom.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
    <link rel="stylesheet" href="<?= ASSETS_FULL_URL . 'css/libraries/aos.min.css?v=' . PRODUCT_CODE ?>">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
    <script src="<?= ASSETS_FULL_URL . 'js/libraries/aos.min.js?v=' . PRODUCT_CODE ?>"></script>

    <script>
        'use strict';

        AOS.init({
            duration: 600,
            easing: 'ease-out-cubic',
            once: true,
        });

        <?php if(is_logged_in()): ?>
        /* Connect button */
        let connect_button = document.querySelector('[data-connect]');

        if(connect_button) {
            /* Detect message incoming */
            window.addEventListener('message', event => {
                if(event.source !== window) return;
                if(event.data?.type !== 'extension_setup_result') return;

                if(event.data.success) {
                    connect_button.innerHTML = '<i class="fas fa-fw fa-sm fa-check mr-1"></i>' + <?= json_encode(l('chrome_extension.connected')) ?>;
                } else {
                    connect_button.innerHTML = '<i class="fas fa-fw fa-sm fa-times mr-1"></i>' + <?= json_encode(l('chrome_extension.not_connected')) ?>;
                    document.querySelector('#connect_details').hidden = false;
                }
            });

            /* Send the message to the extension */
            connect_button.addEventListener('click', event => {
                //connect_button.innerHTML = '<i class="fas fa-fw fa-sm fa-spinner fa-spin"></i>';

                window.postMessage({
                    type: 'extension_setup',
                    url: <?= json_encode(SITE_URL) ?>,
                    api_key: <?= json_encode($this->user->api_key) ?>
                }, window.location.origin);
            });
        }
        <?php endif ?>
    </script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
