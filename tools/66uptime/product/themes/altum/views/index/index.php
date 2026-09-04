<?php defined('ALTUMCODE') || die() ?>

<div class="position-relative pt-6 pb-4">
    <div class="index-custom-hero-background"></div>

    <div class="index-custom-hero">
        <?php for($i = 1; $i <= 30; $i++): ?>
            <div class="index-custom-hero-bar"></div>
        <?php endfor ?>
    </div>

    <?php ob_start() ?>
    <script>
        'use strict';

        let process_bars = () => {
            for(let i = 1; i <= 30; i++) {
                let bar_height;

                if(i >= 1 && i <= 7) {
                    bar_height = Math.floor(Math.random() * (90 - 10 + 1)) + 10;
                } else if(i >= 8 && i <= 22) {
                    bar_height = Math.floor(Math.random() * (50 - 10 + 1)) + 10;
                } else if(i >= 23 && i <= 30) {
                    bar_height = Math.floor(Math.random() * (90 - 10 + 1)) + 10;
                }

                const bar_class = Math.floor(Math.random() * 9) + 1 === 1 ? 'bg-danger' : 'bg-success';

                const bar = document.querySelector(`.index-custom-hero div:nth-child(${i})`);
                bar.classList.remove('bg-danger','bg-success');
                bar.classList.add(bar_class);
                bar.style.height = `${bar_height}%`;
            }
        }

        process_bars();
        setInterval(process_bars, 3000);
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>


    <?= \Altum\Alerts::output_alerts() ?>

    <div class="container">
        <div class="row">
            <div class="col-12 col-lg-8 offset-lg-2 d-flex flex-column justify-content-center align-items-center text-center">
                <h1 class="index-header mb-4"><?= l('index.header') ?></h1>
                <p class="index-subheader"><?= sprintf(l('index.subheader'), '<span class="text-primary-800 font-weight-bold">', '</span>') ?></p>

                <div class="d-flex flex-column flex-lg-row mt-4 mb-4 index-button-wrapper">
                    <?php if(settings()->users->register_is_enabled): ?>
                        <a href="<?= url('register') ?>" class="btn btn-primary index-button mb-3 mb-lg-0 mr-lg-3">
                            <?= l('index.get_started') ?> <i class="fas fa-fw fa-sm fa-arrow-right"></i>
                        </a>
                    <?php endif ?>

                    <?php if(settings()->status_pages->example_url && settings()->status_pages->status_pages_is_enabled): ?>
                        <a href="<?= settings()->status_pages->example_url ?>" target="_blank" class="btn btn-outline-blue-500 index-button mb-3 mb-lg-0">
                            <?= l('index.example') ?> <i class="fas fa-fw fa-xs fa-external-link-alt"></i>
                        </a>
                    <?php endif ?>
                </div>

                <ul class="list-style-none d-flex flex-column flex-md-row my-4">
                    <?php $notification_handlers = require APP_PATH . 'includes/notification_handlers.php' ?>
                    <?php ob_start() ?>
                    <div class='d-flex flex-column'>
                        <?php foreach($notification_handlers as $key => $notification_handler): ?>
                            <span class='my-1'><?= l('notification_handlers.type_' . $key) ?></span>
                        <?php endforeach ?>
                    </div>
                    <?php $html = ob_get_clean() ?>

                    <?php if(settings()->status_pages->domains_is_enabled): ?>
                        <li class="d-flex align-items-center mb-2 mb-md-0 mx-md-3 badge badge-pill badge-light">
                            <i class="fas fa-fw mr-2 fa-check-circle text-primary"></i>
                            <span class="">
                                <?= l('index.feature.one') ?>
                            </span>
                        </li>
                    <?php endif ?>

                    <li class="d-flex align-items-center mb-2 mb-md-0 mx-md-3 badge badge-pill badge-light">
                        <i class="fas fa-fw mr-2 fa-check-circle text-primary"></i>
                        <span data-toggle="tooltip" data-html="true" title="<?= $html ?>">
                            <?= sprintf(l('index.feature.two'), count($notification_handlers)) ?>
                        </span>
                    </li>

                    <?php if(settings()->status_pages->status_pages_is_enabled): ?>
                        <li class="d-flex align-items-center mb-2 mb-md-0 mx-md-3 badge badge-pill badge-light">
                            <i class="fas fa-fw mr-2 fa-check-circle text-primary"></i>
                            <span class="">
                                <?= l('index.feature.three') ?>
                            </span>
                        </li>
                    <?php endif ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="my-3">&nbsp;</div>

<div class="container">

    <div class="row">
        <div class="col-12 col-lg-4">
            <?php $i = 1; ?>
            <?php if(settings()->monitors_heartbeats->monitors_is_enabled): ?>
                <div class="zoom-animation-subtle zoom-animation-subtle-active mb-4">
                    <div class="card bg-gray-50 border border-gray-100 mb-md-0 position-relative" data-aos="fade-up" data-aos-delay="<?= $i++ * 100 ?>">
                        <div class="card-body icon-zoom-animation">
                            <div class="d-flex align-items-center">
                                <div class="index-icon-container mr-3">
                                    <i class="fas fa-fw fa-server"></i>
                                </div>

                                <a href="#tab-monitors" class="h6 m-0 stretched-link text-decoration-none text-blue-600"><?= l('index.monitors.header') ?></a>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="index-icon-container mr-3" style="opacity: 0;">
                                    <i class="fas fa-fw fa-server"></i>
                                </div>

                                <small class="text-muted m-0"><?= l('index.monitors.subheader') ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if(settings()->monitors_heartbeats->heartbeats_is_enabled): ?>
                <div class="zoom-animation-subtle zoom-animation-subtle-active mb-4">
                    <div class="card bg-gray-50 border border-gray-100 mb-md-0 position-relative" data-aos="fade-up" data-aos-delay="<?= $i++ * 100 ?>">
                        <div class="card-body icon-zoom-animation">
                            <div class="d-flex align-items-center">
                                <div class="index-icon-container mr-3">
                                    <i class="fas fa-fw fa-heartbeat"></i>
                                </div>

                                <a href="#tab-heartbeats" class="h6 m-0 stretched-link text-decoration-none text-blue-600"><?= l('index.heartbeats.header') ?></a>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="index-icon-container mr-3" style="opacity: 0;">
                                    <i class="fas fa-fw fa-heartbeat"></i>
                                </div>

                                <small class="text-muted m-0"><?= l('index.heartbeats.subheader') ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if(settings()->monitors_heartbeats->domain_names_is_enabled): ?>
                <div class="zoom-animation-subtle zoom-animation-subtle-active mb-4">
                    <div class="card bg-gray-50 border border-gray-100 mb-md-0 position-relative" data-aos="fade-up" data-aos-delay="<?= $i++ * 100 ?>">
                        <div class="card-body icon-zoom-animation">
                            <div class="d-flex align-items-center">
                                <div class="index-icon-container mr-3">
                                    <i class="fas fa-fw fa-network-wired"></i>
                                </div>

                                <a href="#tab-domain-names" class="h6 m-0 stretched-link text-decoration-none text-blue-600"><?= l('index.domain_names.header') ?></a>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="index-icon-container mr-3" style="opacity: 0;">
                                    <i class="fas fa-fw fa-network-wired"></i>
                                </div>

                                <small class="text-muted m-0"><?= l('index.domain_names.subheader') ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if(settings()->monitors_heartbeats->dns_monitors_is_enabled): ?>
                <div class="zoom-animation-subtle zoom-animation-subtle-active mb-4">
                    <div class="card bg-gray-50 border border-gray-100 mb-md-0 position-relative" data-aos="fade-up" data-aos-delay="<?= $i++ * 100 ?>">
                        <div class="card-body icon-zoom-animation">
                            <div class="d-flex align-items-center">
                                <div class="index-icon-container mr-3">
                                    <i class="fas fa-fw fa-plug"></i>
                                </div>

                                <a href="#tab-dns-monitor" class="h6 m-0 stretched-link text-decoration-none text-blue-600"><?= l('index.dns_monitors.header') ?></a>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="index-icon-container mr-3" style="opacity: 0;">
                                    <i class="fas fa-fw fa-plug"></i>
                                </div>

                                <small class="text-muted m-0"><?= l('index.dns_monitors.subheader') ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>
        </div>

        <div class="col-12 col-lg-8 d-flex justify-content-center align-items-center" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
            <?php if(settings()->monitors_heartbeats->monitors_is_enabled): ?>
                <div class="" id="tab-monitors">
                    <img src="<?= get_custom_image_if_any('index/monitor.webp') ?>" class="img-fluid shadow-lg rounded" loading="lazy" alt="<?= l('index.monitors_image_alt') ?>" />
                </div>
            <?php endif ?>

            <?php if(settings()->monitors_heartbeats->heartbeats_is_enabled): ?>
                <div class="d-none" id="tab-heartbeats">
                    <img src="<?= get_custom_image_if_any('index/heartbeat.webp') ?>" class="img-fluid shadow-lg rounded" loading="lazy" alt="<?= l('index.heartbeats_image_alt') ?>" />
                </div>
            <?php endif ?>

            <?php if(settings()->monitors_heartbeats->domain_names_is_enabled): ?>
                <div class="d-none" id="tab-domain-names">
                    <img src="<?= get_custom_image_if_any('index/domain-names.webp') ?>" class="img-fluid shadow-lg rounded" loading="lazy" alt="<?= l('index.domain_names_image_alt') ?>" />
                </div>
            <?php endif ?>

            <?php if(settings()->monitors_heartbeats->dns_monitors_is_enabled): ?>
                <div class="d-none" id="tab-dns-monitor">
                    <img src="<?= get_custom_image_if_any('index/dns-monitor.webp') ?>" class="img-fluid shadow-lg rounded" loading="lazy" alt="<?= l('index.dns_monitors_image_alt') ?>" />
                </div>
            <?php endif ?>
        </div>
    </div>

    <?php ob_start() ?>
    <script>
        'use strict';

        document.querySelectorAll('a[href^="#tab-"]').forEach(element => {
            element.addEventListener('click', event => {

                let target = element.getAttribute('href').replace('#', '');

                document.querySelectorAll('div[id^="tab-"]').forEach(image => {
                    image.classList.remove('d-none');
                    image.classList.add('d-none');
                });

                document.querySelector(`div[id="${target}"]`).classList.remove('d-none');

                event.preventDefault();
            })
        })
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
</div>

<div class="my-5">&nbsp;</div>

<div class="container">
    <?php if(settings()->monitors_heartbeats->game_servers_is_enabled): ?>
        <?php
        $game_server_types = require APP_PATH . 'includes/game_server_types.php';
        ?>

        <div class="row justify-content-between mt-9" data-aos="fade-up">
            <div class="col-12 col-md-7 text-center mb-5 mb-md-0">
                <img src="<?= get_custom_image_if_any('index/game-servers.webp') ?>" class="img-fluid shadow-lg rounded" loading="lazy" alt="<?= l('index.status_pages_image_alt') ?>" />
            </div>

            <div class="col-12 col-md-5 d-flex flex-column justify-content-center">
                <div>
                    <h2 class="mb-3"><?= l('index.game_servers.header') ?> <i class="fas fa-fw fa-xs fa-wifi text-muted ml-1"></i></h2>

                    <p class="text-muted mb-4"><?= l('index.game_servers.subheader') ?></p>

                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.game_servers.uptime') ?></div>
                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.game_servers.players') ?></div>
                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.game_servers.status') ?></div>
                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= sprintf(l('index.game_servers.games'), count($game_server_types)) ?></div>
                    <div class="font-size-small mb-2">
                        <?php foreach($game_server_types as $type => $value): ?>
                            <img src="<?= ASSETS_FULL_URL . 'images/games/' . $value['icon'] ?>" class="img-fluid icon-favicon-small ml-1 mr-1" data-toggle="tooltip" title="<?= $value['name'] ?>" />
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="my-5">&nbsp;</div>
    <?php endif ?>

    <?php if(settings()->monitors_heartbeats->server_monitors_is_enabled): ?>
        <div class="row justify-content-between" data-aos="fade-up">
            <div class="col-12 col-md-5 d-flex flex-column justify-content-center order-1 order-md-0">
                <div>
                    <h2 class="mb-3"><?= l('index.server_monitors.header') ?> <i class="fas fa-fw fa-xs fa-microchip text-muted ml-1"></i></h2>

                    <p class="text-muted mb-3"><?= l('index.server_monitors.subheader') ?></p>

                    <div class="d-flex mb-3">
                        <span class="badge mr-2" style="color: #772953; background: #FCE8F1;">
                            <i class="fab fa-fw fa-linux mr-1"></i>
                            Linux
                        </span>

                        <span class="badge mr-2" style="color: #005A9E; background: #D6EFFF;">
                            <i class="fab fa-fw fa-windows mr-1"></i>
                            Windows
                        </span>

                        <span class="badge mr-2" style="color: #2C2C2C; background: #E5E5E5;">
                            <i class="fab fa-fw fa-apple mr-1"></i>
                            MacOs
                        </span>
                    </div>

                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.server_monitors.cpu') ?></div>
                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.server_monitors.ram') ?></div>
                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.server_monitors.disk') ?></div>
                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.server_monitors.notifications') ?></div>
                </div>
            </div>

            <div class="col-12 col-md-7 text-center mb-5 mb-md-0 order-0 order-md-1">
                <img src="<?= get_custom_image_if_any('index/server-monitors.webp') ?>" class="img-fluid shadow-lg rounded" loading="lazy" alt="<?= l('index.server_monitors_image_alt') ?>" />
            </div>
        </div>

        <div class="my-5">&nbsp;</div>
    <?php endif ?>

    <?php if(settings()->status_pages->status_pages_is_enabled): ?>
        <div class="row justify-content-between mt-9" data-aos="fade-up">
            <div class="col-12 col-md-7 text-center mb-5 mb-md-0">
                <img src="<?= get_custom_image_if_any('index/status-page.webp') ?>" class="img-fluid shadow-lg rounded" loading="lazy" alt="<?= l('index.status_pages_image_alt') ?>" />
            </div>

            <div class="col-12 col-md-5 d-flex flex-column justify-content-center">
                <div>
                    <h2 class="mb-3"><?= l('index.status_pages.header') ?> <i class="fas fa-fw fa-xs fa-wifi text-muted ml-1"></i></h2>

                    <p class="text-muted mb-4"><?= l('index.status_pages.subheader') ?></p>

                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.status_pages.tracking') ?></div>
                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.status_pages.password') ?></div>
                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.status_pages.fast') ?></div>
                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.status_pages.advanced') ?></div>
                </div>
            </div>
        </div>

        <div class="my-5">&nbsp;</div>
    <?php endif ?>

    <?php if(settings()->monitors_heartbeats->monitors_is_enabled || settings()->monitors_heartbeats->heartbeats_is_enabled): ?>
        <div class="row justify-content-between" data-aos="fade-up">
            <div class="col-12 col-md-5 d-flex flex-column justify-content-center order-1 order-md-0">
                <div>
                    <h2 class="mb-3"><?= l('index.incidents.header') ?> <i class="fas fa-fw fa-xs fa-times-circle text-muted ml-1"></i></h2>

                    <p class="text-muted mb-4"><?= l('index.incidents.subheader') ?></p>

                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.incidents.custom') ?></div>
                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.incidents.notifications') ?></div>
                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.incidents.why') ?></div>
                    <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.incidents.comment') ?></div>
                </div>
            </div>

            <div class="col-12 col-md-7 text-center mb-5 mb-md-0 order-0 order-md-1">
                <img src="<?= get_custom_image_if_any('index/incidents.webp') ?>" class="img-fluid shadow-lg rounded" loading="lazy" alt="<?= l('index.incidents_image_alt') ?>" />
            </div>
        </div>
    <?php endif ?>
</div>

<?php if(settings()->tools->is_enabled && $data->enabled_tools): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <h2 class="text-center mb-3"><?= sprintf(l('index.tools.header'), nr($data->enabled_tools)) ?> <i class="fas fa-fw fa-xs fa-screwdriver-wrench text-muted ml-1"></i></h2>

        <p class="text-muted text-center mb-4"><?= l('index.tools.subheader') ?></p>

        <div class="row position-relative">
            <div class="index-fade"></div>
            <?php foreach($data->tools as $key => $value): ?>
                <?php if(settings()->tools->available_tools->{$key}): ?>
                    <div class="col-12 col-lg-4 p-3 position-relative" data-tool-id="<?= $key ?>" data-tool-name="<?= l('tools.' . $key . '.name') ?>">
                        <div class="card d-flex flex-row h-100 overflow-hidden">
                            <div class="tool-icon-wrapper d-flex flex-column justify-content-center">
                                <div class="bg-primary-100 d-flex align-items-center justify-content-center rounded tool-icon">
                                    <i class="<?= $value['icon'] ?> fa-fw text-primary-600"></i>
                                </div>
                            </div>

                            <div class="card-body text-truncate">
                                <a href="<?= url('tools/' . str_replace('_', '-', $key)) ?>" class="stretched-link text-decoration-none text-dark">
                                    <strong><?= l('tools.' . $key . '.name') ?></strong>
                                </a>
                                <p class="text-truncate text-muted small m-0"><?= l('tools.' . $key . '.description') ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif ?>
            <?php endforeach ?>
        </div>
    </div>

<?php endif ?>

<div class="my-5">&nbsp;</div>

<div class="p-3 p-md-4">
    <div class="bg-blue-900 py-8 up-animation rounded-2x">
        <div class="container text-center">
            <span class="text-white h2"><?= sprintf(l('index.stats'), nr($data->total_monitors_logs), nr($data->total_monitors), nr($data->total_status_pages)) ?></span>
        </div>
    </div>
</div>

<div class="my-5">&nbsp;</div>

<div class="container">
    <div class="row mx-n2 mx-md-n4">
        <?php if(settings()->monitors_heartbeats->monitors_is_enabled): ?>
            <div class="col-12 col-md-6 col-lg-4 px-2 py-4 px-md-4 up-animation">
                <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="100">
                    <img src="<?= get_custom_image_if_any('index/ping-servers.jpg') ?>" class="img-fluid rounded-2x mb-4" loading="lazy" alt="<?= l('index.ping_servers_image_alt') ?>" />

                    <div>
                        <div class="mb-2">
                            <span class="h5"><?= l('index.ping_servers.header') ?></span>
                        </div>
                        <span class="text-muted"><?= l('index.ping_servers.subheader') ?></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4 px-2 py-4 px-md-4 up-animation">
                <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="200">
                    <img src="<?= get_custom_image_if_any('index/custom-request.jpg') ?>" class="img-fluid rounded-2x mb-4" loading="lazy" alt="<?= l('index.custom_requests_image_alt') ?>" />

                    <div>
                        <div class="mb-2">
                            <span class="h5"><?= l('index.custom_request.header') ?></span>
                        </div>
                        <span class="text-muted"><?= l('index.custom_request.subheader') ?></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4 px-2 py-4 px-md-4 up-animation">
                <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="300">
                    <img src="<?= get_custom_image_if_any('index/custom-response.jpg') ?>" class="img-fluid rounded-2x mb-4" loading="lazy" alt="<?= l('index.custom_responses_image_alt') ?>" />

                    <div>
                        <div class="mb-2">
                            <span class="h5"><?= l('index.custom_response.header') ?></span>
                        </div>
                        <span class="text-muted"><?= l('index.custom_response.subheader') ?></span>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <div class="col-12 col-md-6 col-lg-4 px-2 py-4 px-md-4 up-animation">
            <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="400">
                <img src="<?= get_custom_image_if_any('index/notifications.jpg') ?>" class="img-fluid rounded-2x mb-4" loading="lazy" alt="<?= l('index.email_notifications_image_alt') ?>" />

                <div>
                    <div class="mb-2">
                        <span class="h5"><?= l('index.notifications.header') ?></span>
                    </div>
                    <span class="text-muted"><?= l('index.notifications.subheader') ?></span>
                </div>
            </div>
        </div>

        <?php if(settings()->monitors_heartbeats->projects_is_enabled): ?>
            <div class="col-12 col-md-6 col-lg-4 px-2 py-4 px-md-4 up-animation">
                <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="500">
                    <img src="<?= get_custom_image_if_any('index/projects.jpg') ?>" class="img-fluid rounded-2x mb-4" loading="lazy" alt="<?= l('index.projects_image_alt') ?>" />

                    <div>
                        <div class="mb-2">
                            <span class="h5"><?= l('index.projects.header') ?></span>
                        </div>
                        <span class="text-muted"><?= l('index.projects.subheader') ?></span>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <?php if(settings()->status_pages->domains_is_enabled): ?>
            <div class="col-12 col-md-6 col-lg-4 px-2 py-4 px-md-4 up-animation">
                <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="600">
                    <img src="<?= get_custom_image_if_any('index/custom-domains.jpg') ?>" class="img-fluid rounded-2x mb-4" loading="lazy" alt="<?= l('index.domains_image_alt') ?>" />

                    <div>
                        <div class="mb-2">
                            <span class="h5"><?= l('index.custom_domains.header') ?></span>
                        </div>
                        <span class="text-muted"><?= l('index.custom_domains.subheader') ?></span>
                    </div>
                </div>
            </div>
        <?php endif ?>
    </div>
</div>

<?php if(settings()->notification_handlers->is_enabled): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="card py-4 border-0 index-highly-rounded">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h2><?= l('index.notifications_handlers.header') ?> <i class="fas fa-fw fa-xs fa-bell ml-1"></i> </h2>
                    <p class="text-muted"><?= l('index.notifications_handlers.subheader') ?></p>
                </div>

                <div class="row mx-n4">
                    <?php $i = 0; ?>
                    <?php foreach($notification_handlers as $key => $notification_handler): ?>
                        <div class="col-6 col-lg-4 p-4" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                            <div class="bg-gray-100 index-highly-rounded w-100 p-4 icon-zoom-animation text-truncate text-center">
                                <div><i class="<?= $notification_handler['icon'] ?> fa-fw fa-xl mx-1" style="color: <?= $notification_handler['color'] ?>"></i></div>

                                <div class="mt-3 mb-0 h6 text-truncate"><?= l('notification_handlers.type_' . $key) ?></div>
                            </div>
                        </div>
                        <?php $i++ ?>
                    <?php endforeach ?>
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
                            <?php if(settings()->monitors_heartbeats->monitors_is_enabled): ?>
                                <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('monitors.title') ?></div>
                            <?php endif ?>

                            <?php if(settings()->monitors_heartbeats->dns_monitors_is_enabled): ?>
                                <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('dns_monitors.title') ?></div>
                            <?php endif ?>

                            <?php if(settings()->monitors_heartbeats->server_monitors_is_enabled): ?>
                                <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('server_monitors.title') ?></div>
                            <?php endif ?>

                            <?php if(settings()->monitors_heartbeats->heartbeats_is_enabled): ?>
                                <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('heartbeats.title') ?></div>
                            <?php endif ?>
                        </div>

                        <div class="col">
                            <?php if(settings()->monitors_heartbeats->domain_names_is_enabled): ?>
                                <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('domain_names.title') ?></div>
                            <?php endif ?>

                            <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('api_documentation.notification_handlers') ?></div>

                            <?php if(settings()->status_pages->status_pages_is_enabled): ?>
                                <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('status_pages.title') ?></div>
                                <div class="font-size-small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('api_documentation.statistics') ?></div>
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
                    --url '<?= SITE_URL ?>api/monitors' \<br />
                    --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \<br />
                    --header 'Content-Type: multipart/form-data' \<br />
                    --form 'name=<span class="text-primary">Example</span>' \<br />
                    --form 'target=<span class="text-primary">https://example.com/</span>' \<br />
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

    <div class="p-3 p-md-4">
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

                                    <p class="mt-5">
                                        <span class="text-gray-800 font-weight-bold text-muted h5">“</span>
                                        <span class="font-size-little-small"><?= l('index.testimonials.' . $value . '.text') ?></span>
                                        <span class="text-gray-800 font-weight-bold text-muted h5">”</span>
                                    </p>

                                    <div class="blockquote-footer mt-4">
                                        <span class="font-weight-bold"><?= l('index.testimonials.' . $value . '.name') ?></span><br /> <span class="text-muted index-testimonial-comment"><?= l('index.testimonials.' . $value . '.attribute') ?></span>
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
            <small class="text-primary font-weight-bold text-uppercase"><?= l('index.pricing.subheader') ?></small>
            <h2 class="mt-2"><?= l('index.pricing.header') ?></h2>
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

                        <div id="<?= 'faq_accordion_answer_' . $key ?>" class="collapse text-muted mt-3" aria-labelledby="<?= 'faq_accordion_' . $key ?>" data-parent="#faq_accordion">
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
        <div class="card border-0 index-cta py-5 py-lg-6" data-aos="fade-up">
            <div class="card-body">
                <div class="row align-items-center justify-content-center">
                    <div class="col-12 col-lg-5">
                        <div class="text-center text-lg-left mb-4 mb-lg-0">
                            <h2><?= l('index.cta.header') ?></h2>
                            <p class="h5"><?= l('index.cta.subheader') ?></p>
                        </div>
                    </div>

                    <div class="col-12 col-lg-5 mt-4 mt-lg-0">
                        <div class="text-center text-lg-right">
                            <?php if(is_logged_in()): ?>
                                <a href="<?= url('dashboard') ?>" class="btn btn-outline-primary zoom-animation">
                                    <?= l('dashboard.menu') ?> <i class="fas fa-fw fa-arrow-right"></i>
                                </a>
                            <?php else: ?>
                                <a href="<?= url('register') ?>" class="btn btn-outline-primary zoom-animation">
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
