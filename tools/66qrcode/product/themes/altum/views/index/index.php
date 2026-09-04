<?php defined('ALTUMCODE') || die() ?>

<div class="index-background py-9">
    <div class="container">
        <?= \Altum\Alerts::output_alerts() ?>

        <div class="row justify-content-center">
            <div class="col-11 col-md-10 col-lg-7">
                <h1 class="index-header text-center mb-2"><?= l('index.header') ?></h1>
            </div>

            <div class="col-10 col-sm-8 col-lg-6">
                <p class="index-subheader text-center mb-5"><?= l('index.subheader') ?></p>
            </div>
        </div>

        <div class="d-flex flex-column flex-lg-row justify-content-center">
            <?php if(settings()->codes->qr_codes_is_enabled): ?>
                <a href="<?= is_logged_in() ? url('qr-code-create') : url('qr/text') ?>" class="btn btn-primary index-button mb-3 mb-lg-0 mr-lg-3">
                    <i class="fas fa-fw fa-sm fa-qrcode mr-1"></i> <?= l('index.qr') ?>
                </a>
            <?php endif ?>

            <?php if(settings()->codes->barcodes_is_enabled): ?>
                <a href="<?= is_logged_in() ? url('barcode-create') : url('barcode') ?>" class="btn btn-dark index-button mb-3 mb-lg-0 mr-lg-3">
                    <i class="fas fa-fw fa-sm fa-barcode mr-1"></i> <?= l('index.barcode') ?>
                </a>
            <?php endif ?>
        </div>

        <?php if(settings()->codes->qr_reader_is_enabled || settings()->codes->barcode_reader_is_enabled): ?>
            <div class="d-flex flex-row justify-content-center mt-3">
                <?php if(settings()->codes->qr_reader_is_enabled): ?>
                    <a href="<?= url('qr-reader') ?>" class="btn btn-gray-200 index-button-secondary mr-3" data-toggle="tooltip" title="<?= l('qr_reader.menu') ?>">
                        <i class="fas fa-fw fa-sm fa-glasses"></i>
                    </a>
                <?php endif ?>

                <?php if(settings()->codes->barcode_reader_is_enabled): ?>
                    <a href="<?= url('barcode-reader') ?>" class="btn btn-gray-200 index-button-secondary mr-3" data-toggle="tooltip" title="<?= l('barcode_reader.menu') ?>">
                        <i class="fas fa-fw fa-sm fa-print"></i>
                    </a>
                <?php endif ?>
            </div>
        <?php endif ?>

    </div>
</div>

<div class="container">
    <div class="row justify-content-center mt-8" data-aos="fade-up">
        <div class="col-12">
            <img src="<?= get_custom_image_if_any('index/hero.webp') ?>" class="img-fluid shadow rounded-lg zoom-animation-subtle" loading="lazy" alt="<?= l('index.hero_image_alt') ?>" />
        </div>
    </div>
</div>
<div class="my-5">&nbsp;</div>


<div class="container">
    <div class="row">
        <!-- QR Templates Widget -->
        <?php if(settings()->codes->qr_codes_is_enabled): ?>
            <div class="col-12 col-md-6 col-lg-4 p-4">
                <div class="card d-flex flex-column justify-content-between h-100 bg-gray-50 rounded-2x" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="index-icon-container" style="background: #ecfdf5;">
                                <i class="fas fa-fw fa-qrcode" style="color: #10b981;"></i>
                            </span>
                        </div>
                        <div class="mb-1">
                            <span class="h6 mb-0"><?= l('index.qr_templates.header') ?></span>
                        </div>
                        <span class="text-muted small"><?= sprintf(l('index.qr_templates.subheader'), count($data->available_qr_codes)) ?></span>
                    </div>
                </div>
            </div>

            <!-- Privacy Widget -->
            <div class="col-12 col-md-6 col-lg-4 p-4">
                <div class="card d-flex flex-column justify-content-between h-100 bg-gray-50 rounded-2x" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="index-icon-container" style="background: #ecfeff;">
                                <i class="fas fa-fw fa-user-secret" style="color: #06b6d4;"></i>
                            </span>
                        </div>
                        <div class="mb-1">
                            <span class="h6 mb-0"><?= l('index.privacy.header') ?></span>
                        </div>
                        <span class="text-muted small"><?= l('index.privacy.subheader') ?></span>
                    </div>
                </div>
            </div>

            <!-- Customization Widget -->
            <div class="col-12 col-md-6 col-lg-4 p-4">
                <div class="card d-flex flex-column justify-content-between h-100 bg-gray-50 rounded-2x" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="index-icon-container" style="background: #eef2ff;">
                                <i class="fas fa-fw fa-tools" style="color: #6366f1;"></i>
                            </span>
                        </div>
                        <div class="mb-1">
                            <span class="h6 mb-0"><?= l('index.customization.header') ?></span>
                        </div>
                        <span class="text-muted small"><?= l('index.customization.subheader') ?></span>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <!-- Short URLs Widget -->
        <div class="col-12 col-md-6 col-lg-4 p-4">
            <div class="card d-flex flex-column justify-content-between h-100 bg-gray-50 rounded-2x" data-aos="fade-up" data-aos-delay="400">
                <div class="card-body">
                    <div class="mb-2">
                        <span class="index-icon-container" style="background: #eef2ff;">
                            <i class="fas fa-fw fa-link" style="color: #0672d4;"></i>
                        </span>
                    </div>
                    <div class="mb-1">
                        <span class="h6 mb-0"><?= l('index.short_urls.header') ?></span>
                    </div>
                    <span class="text-muted small"><?= l('index.short_urls.subheader') ?></span>
                </div>
            </div>
        </div>

        <!-- Projects Widget -->
        <div class="col-12 col-md-6 col-lg-4 p-4">
            <div class="card d-flex flex-column justify-content-between h-100 bg-gray-50 rounded-2x" data-aos="fade-up" data-aos-delay="500">
                <div class="card-body">
                    <div class="mb-2">
                        <span class="index-icon-container" style="background: #faf5ff;">
                            <i class="fas fa-fw fa-tasks" style="color: #a855f7;"></i>
                        </span>
                    </div>
                    <div class="mb-1">
                        <span class="h6 mb-0"><?= l('index.projects.header') ?></span>
                    </div>
                    <span class="text-muted small"><?= l('index.projects.subheader') ?></span>
                </div>
            </div>
        </div>

        <!-- Domains Widget -->
        <div class="col-12 col-md-6 col-lg-4 p-4">
            <div class="card d-flex flex-column justify-content-between h-100 bg-gray-50 rounded-2x" data-aos="fade-up" data-aos-delay="600">
                <div class="card-body">
                    <div class="mb-2">
                        <span class="index-icon-container" style="background: #fdf4ff;">
                            <i class="fas fa-fw fa-globe" style="color: #d946ef;"></i>
                        </span>
                    </div>
                    <div class="mb-1">
                        <span class="h6 mb-0"><?= l('index.domains.header') ?></span>
                    </div>
                    <span class="text-muted small"><?= l('index.domains.subheader') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>




<?php if(settings()->codes->qr_codes_is_enabled): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="row justify-content-between" data-aos="fade-up">
            <div class="col-12 col-md-5 text-center mb-5 mb-md-0" >
                <img src="<?= get_custom_image_if_any('index/static.webp') ?>" class="inverse-colors-animation img-fluid rounded-2x" loading="lazy" alt="<?= l('index.notification_example_image_alt') ?>" />
            </div>

            <div class="col-12 col-md-6 d-flex flex-column justify-content-center">
                <div class="mb-4">
                    <span class="p-3 bg-primary-100 rounded">
                        <i class="fas fa-fw fa-lg fa-qrcode text-primary"></i>
                    </span>
                </div>

                <div>
                    <h2 class="mb-4"><?= l('index.static.header') ?></h2>

                    <p class="text-muted mb-4"><?= l('index.static.subheader') ?></p>

                    <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.static.feature1') ?></div>
                    <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.static.feature2') ?></div>
                    <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.static.feature3') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="row justify-content-between" data-aos="fade-up">
            <div class="col-12 col-md-5 text-center mb-5 mb-md-0" >
                <img src="<?= get_custom_image_if_any('index/dynamic.webp') ?>" class="inverse-colors-animation img-fluid rounded-2x" loading="lazy" alt="<?= l('index.notification_example_image_alt') ?>" />
            </div>

            <div class="col-12 col-md-6 d-flex flex-column justify-content-center">
                <div class="mb-4">
                    <span class="p-3 bg-primary-100 rounded">
                        <i class="fas fa-fw fa-lg fa-link text-primary"></i>
                    </span>
                </div>

                <div>
                    <h2 class="mb-4"><?= l('index.dynamic.header') ?></h2>

                    <p class="text-muted mb-4"><?= l('index.dynamic.subheader') ?></p>

                    <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.dynamic.feature1') ?></div>
                    <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.dynamic.feature2') ?></div>
                    <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.dynamic.feature3') ?></div>
                    <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.dynamic.feature4') ?></div>
                    <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.dynamic.feature5') ?></div>
                </div>
            </div>
        </div>
    </div>

<?php endif ?>

<?php if(settings()->codes->ai_qr_codes_is_enabled): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <h2 class="text-center mb-5"><?= l('index.ai_qr_codes.header') ?> <i class="fas fa-fw fa-xs fa-robot text-primary ml-1"></i></h2>

        <div class="">
            <?php $ai_array = range(1, 12); ?>
            <?php $groups = array_chunk($ai_array, 6, true); ?>

            <?php for ($i = 0; $i < 2; $i++): ?>
                <div class="index-marquee-wrapper">
                    <?php $j = 1 ?>
                    <?php foreach($groups[$i] as $key => $value): ?>
                        <img src="<?= ASSETS_FULL_URL . 'images/index/ai/' . $value . '.png' ?>" class="img-fluid rounded-2x index-marquee-item mx-3 my-3 zoom-animation-subtle index-marquee-item-<?= $i % 2 == 0 ? 'left' : 'right' ?>" loading="lazy" alt="<?= sprintf(l('index.ai_qr_codes.alt'), $j) ?>" style="--n: <?= $j++ ?>" />
                    <?php endforeach ?>
                </div>
            <?php endfor ?>
        </div>
    </div>
<?php endif ?>

<?php if(settings()->codes->qr_codes_is_enabled): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="text-center mb-5">
            <h2><?= l('index.qr_codes.header') ?></h2>
            <p class="text-muted mt-3"><?= l('index.qr_codes.subheader') ?></p>
        </div>

        <div class="row">
            <?php foreach($data->available_qr_codes as $key => $value): ?>
                <div class="col-12 col-md-6 col-lg-4 p-3 position-relative">
                    <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('qr_codes.type.' . $key . '_description') ?>">
                        <div class="qr-template-icon-wrapper d-flex flex-column">
                            <div class="bg-primary-100 d-flex align-items-center justify-content-center rounded qr-template-icon">
                                <i class="<?= $value['icon'] ?> fa-fw text-primary-600"></i>
                            </div>
                        </div>

                        <div class="card-body">
                            <a href="<?= url('qr/' . $key) ?>" class="stretched-link text-decoration-none text-dark">
                                <strong><?= l('qr_codes.type.' . $key) ?></strong>
                            </a>
                            <p class=" text-muted text-break small m-0"><?= sprintf(l('index.qr_codes.choose'), l('qr_codes.type.' . $key))  ?> <i class="fas fa-fw fa-sm fa-arrow-right"></i> </p>
                        </div>
                    </div>
                </div>


            <?php endforeach ?>
        </div>
    </div>
<?php endif ?>



<?php if(settings()->codes->barcodes_is_enabled): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="text-center mb-5">
            <h2><i class="fas fa-fw fa-xs fa-barcode text-primary mr-2"></i><?= l('index.barcodes.header') ?></h2>
            <p class="text-muted mt-3"><?= l('index.barcodes.subheader') ?></p>
        </div>

        <div class="row">
            <?php foreach($data->available_barcodes as $key => $value): ?>
                <div class="col-12 col-md-6 col-lg-4 p-4">
                    <div class="card position-relative h-100 bg-gray-50">
                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            <h3 class="h4"><?= $key ?></h3>

                            <div class="d-flex justify-content-center mt-3 barcode-background">
                                <?php
                                $generator = new Picqer\Barcode\BarcodeGeneratorSVG();
                                echo $generator->getBarcode($value['example_value'], $key);
                                ?>
                            </div>

                            <a href="<?= url('barcode/' . str_replace('+', '-plus', $key)) ?>" class="btn btn-block btn-sm btn-light mt-4 text-muted stretched-link">
                                <?= sprintf(l('index.barcodes.choose'), $key) ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
<?php endif ?>

<div class="my-5">&nbsp;</div>

<div class="container">
    <div class="card rounded-2x py-4 bg-gray-900 border-0">
        <div class="card-body">
            <div class="text-center mb-4">
                <h2 class="text-white"><?= l('index.shortener_app_linking.header') ?></h2>
                <p class="text-muted font-weight-500"><?= l('index.shortener_app_linking.subheader') ?></p>
            </div>

            <div class="d-flex flex-wrap justify-content-center">
                <?php foreach(require APP_PATH . 'includes/app_linking.php' as $app_key => $app): ?>
                    <div class="mobile-app-icon-wrapper bg-gray-800 p-3 m-2 m-md-3 m-lg-4 icon-zoom-animation" data-toggle="tooltip" title="<?= $app['name'] ?>">
                        <span title="<?= $app['name'] ?>"><i class="<?= $app['icon'] ?> fa-fw fa-xl mx-1" style="color: <?= $app['color'] ?>"></i></span>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>
</div>

<?php if(settings()->links->pixels_is_enabled): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="card py-4 border-0">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h2><?= l('index.pixels.header') ?></h2>
                    <p class="text-muted"><?= l('index.pixels.subheader') ?></p>
                </div>

                <div class="row no-gutters">
                    <?php $i = 0; ?>
                    <?php foreach(require APP_PATH . 'includes/l/pixels.php' as $item): ?>
                        <div class="col-12 col-md-6 col-lg-4 p-4" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                            <div class="bg-gray-100 rounded-3x w-100 p-3 icon-zoom-animation text-truncate">
                                <i class="<?= $item['icon'] ?> fa-fw fa-lg mx-1" style="color: <?= $item['color'] ?>"></i>
                                <span class="h6"><?= $item['name'] ?></span>
                            </div>
                        </div>
                        <?php $i++ ?>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if(\Altum\Plugin::is_active('chrome-extension') && settings()->chrome_extension->is_enabled): ?>
    <div class="container mt-8">
        <div class="card py-4 border-0 index-highly-rounded">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="text-primary text-uppercase font-weight-600 small mb-3"><?= l('index.chrome_extension.name') ?></div>

                    <h2><?= l('index.chrome_extension.header') ?></h2>

                    <div class="d-flex justify-content-center">
                        <div class="col-lg-8">
                            <p class="text-muted"><?= l('index.chrome_extension.subheader') ?></p>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-lg-row align-items-center justify-content-center flex-wrap gap-3">
                    <span class="badge badge-light bg-white border">
                        <i class="fas fa-fw fa-xs fa-circle text-success mr-1"></i> <?= l('index.chrome_extension.domains') ?>
                    </span>

                    <span class="badge badge-light bg-white border">
                        <i class="fas fa-fw fa-xs fa-circle text-success mr-1"></i> <?= l('index.chrome_extension.context_menu') ?>
                    </span>

                    <span class="badge badge-light bg-white border">
                        <i class="fas fa-fw fa-xs fa-circle text-success mr-1"></i> <?= l('index.chrome_extension.last_shortened') ?>
                    </span>

                    <span class="badge badge-light bg-white border">
                        <i class="fas fa-fw fa-xs fa-circle text-success mr-1"></i> <?= l('index.chrome_extension.custom_alias') ?>
                    </span>

                    <span class="badge badge-light bg-white border" data-toggle="tooltip" title="<?= l('index.chrome_extension.shortcut_help') ?>">
                        <i class="fas fa-fw fa-xs fa-circle text-success mr-1"></i> <?= l('index.chrome_extension.shortcut') ?>
                    </span>
                </div>

                <div class="d-flex flex-column flex-lg-row align-items-center justify-content-center gap-3 mt-5">
                    <a href="<?= settings()->chrome_extension->chrome_web_store_url ?>" class="btn rounded-2x btn-primary px-4 py-2 mr-3 d-flex align-items-center">
						<?= l('index.chrome_extension.install') ?> <img src="<?= ASSETS_FULL_URL . 'images/browsers/chrome.svg' ?>" class="img-fluid icon-favicon ml-2" />
                    </a>

                    <a href="<?= url('chrome-extension') ?>" class="btn rounded-2x btn-light px-4 py-2">
						<?= l('index.chrome_extension.learn') ?> <i class="fas fa-fw fa-sm fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if(settings()->main->api_is_enabled): ?>
    <div class="py-6"></div>

    <div class="container">
        <div class="row align-items-center justify-content-between">
            <div class="col-12 col-lg-5 mb-5 mb-lg-0 d-flex flex-column justify-content-center" data-aos="fade-up"">
                <div class="text-uppercase font-weight-bold text-primary mb-3"><?= l('index.api.name') ?></div>

                <div>
                    <h2 class="mb-2"><?= l('index.api.header') ?></h2>
                    <p class="text-muted mb-4"><?= l('index.api.subheader') ?></p>

                    <div class="position-relative">
                        <div class="index-fade"></div>
                        <div class="row">
                            <div class="col">
                                <?php if(settings()->codes->ai_qr_codes_is_enabled): ?>
                                    <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('ai_qr_codes.title') ?></div>
                                <?php endif ?>

                                <?php if(settings()->codes->qr_codes_is_enabled): ?>
                                    <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('qr_codes.title') ?></div>
                                <?php endif ?>

                                <?php if(settings()->codes->barcodes_is_enabled): ?>
                                    <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('barcodes.title') ?></div>
                                <?php endif ?>

                                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('api_documentation.links') ?></div>
                            </div>

                            <div class="col">
                                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('api_documentation.statistics') ?></div>

                                <?php if(settings()->links->projects_is_enabled): ?>
                                    <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('projects.title') ?></div>
                                <?php endif ?>

                                <?php if(settings()->links->pixels_is_enabled): ?>
                                    <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('pixels.title') ?></div>
                                <?php endif ?>

                                <?php if(settings()->links->domains_is_enabled): ?>
                                    <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('domains.title') ?></div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>

                    <a href="<?= url('api-documentation') ?>" class="btn btn-block btn-outline-primary mt-5">
                        <?= l('api_documentation.menu') ?> <i class="fas fa-fw fa-xs fa-code ml-1"></i>
                    </a>
                </div>
            </div>

            <div class="col-12 col-lg-6" data-aos="fade-up" data-aos-delay="300">
                <div class="card rounded-2x bg-dark text-white">
                    <div class="card-body p-4 text-monospace reveal-effect text-break font-size-small" style="line-height: 1.75">
                        curl --request POST \<br />
                        --url '<?= SITE_URL ?>api/links' \<br />
                        --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \<br />
                        --header 'Content-Type: multipart/form-data' \<br />
                        --form 'url=<span class="text-primary">example</span>' \<br />
                        --form 'location_url=<span class="text-primary"><?= SITE_URL ?></span>' \<br />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* hide until words are wrapped to avoid flash */
        .reveal-effect { visibility: hidden; }

        /* base state for each word */
        .reveal-effect-prepared .reveal-effect-word {
            opacity: 0;
            filter: blur(6px);
            transform: translate3d(0, 8px, 0);
            display: inline-block;
            transition: opacity .5s ease, filter .5s ease, transform .5s ease;
        }

        /* animate in when container gets .reveal-effect-in */
        .reveal-effect-prepared.reveal-effect-in .reveal-effect-word {
            opacity: 1;
            filter: blur(0);
            transform: none;
        }
    </style>

    <script defer>
        /* wrap words in a text node while preserving existing HTML */
        const wrap_words_in_text_node = (text_node) => {
            /* split into words + spaces, keep spacing intact */
            const tokens = text_node.textContent.split(/(\s+)/);
            const fragment = document.createDocumentFragment();

            tokens.forEach((token) => {
                if (token.trim().length === 0) {
                    fragment.appendChild(document.createTextNode(token));
                } else {
                    const span_node = document.createElement('span');
                    span_node.className = 'reveal-effect-word';
                    span_node.textContent = token;
                    fragment.appendChild(span_node);
                }
            });

            text_node.parentNode.replaceChild(fragment, text_node);
        };

        /* prepare a container: wrap only pure text nodes, not tags */
        const prepare_reveal_container = (container_node) => {
            /* collect first to avoid live-walking issues while replacing */
            const walker = document.createTreeWalker(
                container_node,
                NodeFilter.SHOW_TEXT,
                { acceptNode: (node) => node.textContent.trim().length ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT }
            );
            const text_nodes = [];
            while (walker.nextNode()) { text_nodes.push(walker.currentNode); }
            text_nodes.forEach(wrap_words_in_text_node);

            /* add stagger */
            const word_nodes = container_node.querySelectorAll('.reveal-effect-word');
            word_nodes.forEach((word_node, index) => {
                word_node.style.transitionDelay = (index * 40) + 'ms';
            });

            /* mark as prepared and reveal visibility */
            container_node.classList.add('reveal-effect-prepared');
            container_node.style.visibility = 'visible';
        };

        /* set up scroll trigger */
        document.addEventListener('DOMContentLoaded', () => {
            const container_node = document.querySelector('.reveal-effect');
            if (!container_node) { return; }

            /* prepare once (preserves HTML) */
            prepare_reveal_container(container_node);

            /* trigger when in view */
            const on_intersect = (entries, observer) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        /* start the animation */
                        setTimeout(() => {
                            container_node.classList.add('reveal-effect-in');
                            observer.unobserve(container_node);
                        }, 200);
                    }
                });
            };

            const intersection_observer = new IntersectionObserver(on_intersect, {
                root: null,
                rootMargin: '0px 0px -10% 0px',
                threshold: 0.1
            });

            intersection_observer.observe(container_node);
        });
    </script>
<?php endif ?>

<?php if(settings()->main->display_index_testimonials): ?>
    <div class="my-5">&nbsp;</div>

    <div class="p-3 p-md-4 mt-5">
        <div class="py-7 bg-primary-100 rounded-2x">
            <div class="container">
                <div class="text-center">
                    <h2><?= l('index.testimonials.header') ?> <i class="fas fa-fw fa-xs fa-check-circle text-primary"></i></h2>
                </div>

                <?php
                $language_array = \Altum\Language::get(\Altum\Language::$name);
                if(\Altum\Language::$main_name != \Altum\Language::$name) {
                    $language_array = array_merge(\Altum\Language::get(\Altum\Language::$main_name), $language_array);
                }

                $testimonials_language_keys = [];
                foreach ($language_array as $key => $value) {
                    if(preg_match('/index\.testimonials\.(\w+)\./', $key, $matches)) {
                        $testimonials_language_keys[] = $matches[1];
                    }
                }

                $testimonials_language_keys = array_unique($testimonials_language_keys);
                ?>

                <div class="row mt-8 mx-n3">
                    <?php foreach($testimonials_language_keys as $key => $value): ?>
                        <div class="col-12 col-lg-4 mb-7 mb-lg-0 px-4" data-aos="fade-up" data-aos-delay="<?= $key * 100 ?>">
                            <div class="card border-0 zoom-animation-subtle">
                                <div class="card-body">
                                    <img src="<?= get_custom_image_if_any('index/testimonial-' . $value . '.webp') ?>" class="img-fluid index-testimonial-avatar" alt="<?= l('index.testimonials.' . $value . '.name') . ', ' . l('index.testimonials.' . $value . '.attribute') ?>" loading="lazy" />

                                    <div class="mt-5">
                                        <i class="fas fa-fw fa-sm fa-star text-warning mr-1"></i><i class="fas fa-fw fa-sm fa-star text-warning mr-1"></i><i class="fas fa-fw fa-sm fa-star text-warning mr-1"></i><i class="fas fa-fw fa-sm fa-star text-warning mr-1"></i><i class="fas fa-fw fa-sm fa-star text-warning mr-1 mr-1"></i>
                                    </div>

                                    <p class="mt-2">
                                        <span class="font-size-little-small"><?= l('index.testimonials.' . $value . '.text') ?></span>
                                    </p>

                                    <div class="d-flex justify-content-between mt-4">
                                        <div class="blockquote-footer">
                                            <span class="font-weight-bold"><?= l('index.testimonials.' . $value . '.name') ?></span>
                                            <div class="text-muted index-testimonial-comment"><?= l('index.testimonials.' . $value . '.attribute') ?></div>
                                        </div>

                                        <?php if(!empty(l('index.testimonials.' . $value . '.link'))): ?>
                                            <div class="ml-3">
                                                <a href="<?= l('index.testimonials.' . $value . '.link') ?>" target="_blank" rel="noreferrer noopener nofollow" class="btn btn-sm btn-light">
                                                    <i class="fas fa-fw fa-sm fa-external-link"></i>
                                                </a>
                                            </div>
                                        <?php endif ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if(settings()->main->display_index_plans): ?>
    <div class="my-5">&nbsp;</div>

    <div id="plans" class="container">
        <div class="text-center mb-5">
            <h2><?= l('index.pricing.header') ?></h2>
            <p class="text-muted"><?= l('index.pricing.subheader') ?></p>
        </div>

        <?= $this->views['plans'] ?>
    </div>
<?php endif ?>

<?php if(settings()->main->display_index_faq): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="text-center mb-5">
            <h2><?= l('index.faq.header') ?></h2>
        </div>

        <?php
        $language_array = \Altum\Language::get(\Altum\Language::$name);
        if(\Altum\Language::$main_name != \Altum\Language::$name) {
            $language_array = array_merge(\Altum\Language::get(\Altum\Language::$main_name), $language_array);
        }

        $faq_language_keys = [];
        foreach ($language_array as $key => $value) {
            if(preg_match('/index\.faq\.(\w+)\./', $key, $matches)) {
                $faq_language_keys[] = $matches[1];
            }
        }

        $faq_language_keys = array_unique($faq_language_keys);
        ?>

        <div class="accordion index-faq" id="faq_accordion">
            <?php foreach($faq_language_keys as $key): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="" id="<?= 'faq_accordion_' . $key ?>">
                            <h3 class="mb-0">
                                <button class="btn btn-lg font-weight-500 btn-block d-flex justify-content-between text-gray-800 px-0 icon-zoom-animation no-focus text-left" type="button" data-toggle="collapse" data-target="<?= '#faq_accordion_answer_' . $key ?>" aria-expanded="true" aria-controls="<?= 'faq_accordion_answer_' . $key ?>">
                                    <span class="text-left"><?= l('index.faq.' . $key . '.question') ?></span>

                                    <span data-icon>
                                        <i class="fas fa-fw fa-circle-chevron-down"></i>
                                    </span>
                                </button>
                            </h3>
                        </div>

                        <div id="<?= 'faq_accordion_answer_' . $key ?>" class="collapse text-muted mt-2" aria-labelledby="<?= 'faq_accordion_' . $key ?>" data-parent="#faq_accordion">
                            <?= l('index.faq.' . $key . '.answer') ?>
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
<?php endif ?>

<?php if(settings()->users->register_is_enabled): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="card border-0 index-cta py-5 py-lg-6 rounded-2x" data-aos="fade-up">
            <div class="card-body">
                <div class="row align-items-center justify-content-center">
                    <div class="col-12 col-lg-5">
                        <div class="text-center text-lg-left mb-4 mb-lg-0">
                            <h2 class="h1"><?= l('index.cta.header') ?></h2>
                            <p class="h5"><?= l('index.cta.subheader') ?></p>
                        </div>
                    </div>

                    <div class="col-12 col-lg-5 mt-4 mt-lg-0">
                        <div class="text-center text-lg-right">
                            <?php if(is_logged_in()): ?>
                                <a href="<?= url('dashboard') ?>" class="btn btn-primary zoom-animation">
                                    <?= l('dashboard.menu') ?> <i class="fas fa-fw fa-arrow-right"></i>
                                </a>
                            <?php else: ?>
                                <a href="<?= url('register') ?>" class="btn btn-primary zoom-animation">
                                    <?= l('index.cta.register') ?> <i class="fas fa-fw fa-arrow-right"></i>
                                </a>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>


<?php if (!empty($data->blog_posts)): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="text-center mb-5">
            <h2><?= sprintf(l('index.blog.header'), '<span class="text-primary">', '</span>') ?></h2>
        </div>

        <div class="row mx-n2 mx-lg-n3">
            <?php foreach($data->blog_posts as $blog_post): ?>
                <div class="col-12 col-lg-4 px-2 py-4 px-lg-3">
                    <div class="card h-100 zoom-animation-subtle position-relative">
                        <div class="card-body">
                            <?php if($blog_post->image): ?>
                                <a href="<?= SITE_URL . ($blog_post->language ? \Altum\Language::$active_languages[$blog_post->language] . '/' : null) . 'blog/' . $blog_post->url ?>" aria-label="<?= $blog_post->title ?>">
                                    <img src="<?= \Altum\Uploads::get_full_url('blog') . $blog_post->image ?>" class="blog-post-image-small img-fluid w-100 rounded mb-4" alt="<?= $blog_post->image_description ?>" loading="lazy" />
                                </a>
                            <?php endif ?>

                            <a href="<?= SITE_URL . ($blog_post->language ? \Altum\Language::$active_languages[$blog_post->language] . '/' : null) . 'blog/' . $blog_post->url ?>" class="stretched-link text-decoration-none">
                                <h3 class="h5 card-title d-inline"><?= $blog_post->title ?></h3>
                            </a>

                            <p class="text-muted mt-2 mb-0 font-size-small"><?= $blog_post->description ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
<?php endif ?>


<?php if(settings()->content->broadcasts_is_enabled && settings()->content->broadcasts_display_index_box && (is_logged_in() || settings()->content->broadcasts_guests_is_enabled)): ?>
    <div class="py-3"></div>

    <div class="container mt-5">
        <div class="mb-5">
            <div class="card">
                <div class="card-body">
                    <h2 class="h3"><?= l('index.broadcasts.header') ?></h2>
                    <p class="text-muted"><?= l('index.broadcasts.subheader') ?></p>

                    <form id="broadcast_form" method="post" action="<?= url('broadcast-subscribe') ?>" role="form">
                        <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                        <div class="row gap-3">
                            <div class="col-lg-auto">
                                <div class="form-group mb-0">
                                    <input id="broadcast_email" type="email" name="email" class="form-control form-control-lg" value="<?= is_logged_in() && !$this->user->is_broadcast_subscribed ? $this->user->email : null ?>" placeholder="<?= l('global.email') ?>" aria-label="<?= l('global.email') ?>" maxlength="320" required="required" />
                                </div>
                            </div>

                            <div class="col-lg-auto">
                                <div class="form-group mb-0">
                                    <input id="broadcast_name" type="text" name="name" class="form-control form-control-lg" value="<?= is_logged_in() && !$this->user->is_broadcast_subscribed ? $this->user->name : null ?>" placeholder="<?= l('global.name') ?>" aria-label="<?= l('global.name') ?>" maxlength="64" required="required" />
                                </div>
                            </div>

                            <div class="col">
                                <button type="submit" name="submit" class="btn btn-lg btn-outline-primary" <?= is_logged_in() && $this->user->is_broadcast_subscribed ? 'disabled="disabled" data-toggle="tooltip" title="' . l('index.broadcasts.is_subscribed') . '"' : null ?>><?= l('index.broadcasts.subscribe') ?></button>
                            </div>
                        </div>
                    </form>

                    <?php if(is_logged_in() && $this->user->is_broadcast_subscribed): ?>
                        <div class="small mt-3">
                            <i class="fas fa-fw fa-sm fa-check-circle text-success mr-1"></i> <span class="text-muted"><?= l('index.broadcasts.is_subscribed') ?></span> <a href="<?= url('account') ?>"><?= l('index.broadcasts.manage_subscription') ?></a>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php ob_start() ?>
<link rel="stylesheet" href="<?= ASSETS_FULL_URL . 'css/libraries/aos.min.css?v=' . PRODUCT_CODE ?>">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/aos.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    'use strict';

    AOS.init({
        duration: 650,
        easing: 'ease-out-cubic',
        once: true,
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php ob_start() ?>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": <?= json_encode(settings()->main->title) ?>,
    "url": <?= json_encode(url()) ?>,
    <?php if(settings()->main->{'logo_' . \Altum\ThemeStyle::get()}): ?>
        "logo": {
            "@type": "ImageObject",
            "url": <?= json_encode(settings()->main->{'logo_' . \Altum\ThemeStyle::get() . '_full_url'}) ?>
        },
    <?php endif ?>
    "slogan": <?= json_encode(l('index.header')) ?>,
    "contactPoint": {
        "@type": "ContactPoint",
        "url": <?= json_encode(url('contact')) ?>,
        "contactType": "customer support"
    }
}
</script>

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
                }
            ]
        }
</script>

<?php if(settings()->main->display_index_faq): ?>
    <?php
    $faqs = [];
    foreach($faq_language_keys as $key) {
        $faqs[] = [
                '@type' => 'Question',
                'name' => l('index.faq.' . $key . '.question'),
                'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => l('index.faq.' . $key . '.answer'),
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
<?php endif ?>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/index-custom.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>
