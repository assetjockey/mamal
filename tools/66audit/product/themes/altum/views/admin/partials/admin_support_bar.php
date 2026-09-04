<?php defined('ALTUMCODE') || die(); ?>

<?php if(ALTUMCODE == 66): ?>
<?php //ALTUMCODE:DEMO if(!DEMO) { ?>
<?php if(isset(settings()->support->expiry_datetime) && ALTUMCODE == 66): ?>
    <?php
    $expiry_datetime = (new \DateTime(settings()->support->expiry_datetime ?? null));
    $is_active = (new \DateTime()) <= $expiry_datetime;
    ?>

    <?php if(!$is_active && !isset($_COOKIE['dismiss_inactive_support'])): ?>
            <div class="alert alert-warning mb-4">
                <div class="d-flex">
                    <div class="mr-2 mt-1">
                        <i class="fas fa-fw fa-exclamation-triangle text-warning"></i>
                    </div>

                    <div class="w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Support expired</strong>

                            <button type="button" class="btn btn-link text-reset text-decoration-none font-weight-500 btn-sm" data-toggle="collapse" data-target="#inactive_support_container" aria-expanded="false" aria-controls="inactive_support_container">
                                View details <i class="fas fa-fw fa-sm fa-chevron-right"></i>
                            </button>
                        </div>

                        <div class="collapse" id="inactive_support_container">
                            <div class="mt-1 font-size-little-small">
                                Renew support to continue receiving help and unlock extra benefits:
                            </div>

                            <ul class="mt-2 font-size-small list-unstyled">
                                <li class="mt-1">🛡️ Email Shield plugin</li>
                                <li class="mt-1">🖼️ Dynamic OG Images plugin</li>
                                <li class="mt-1">📱 iOS widgets for all AltumCode products</li>
                                <li class="mt-1">💬 Private Telegram community access</li>
                            </ul>

                            <div class="mt-3">
                                <a href="https://altumco.de/club?utm_source=<?= PRODUCT_KEY ?>&utm_medium=admin_panel&utm_campaign=support_renewal" target="_blank" class="btn btn-sm btn-primary font-weight-500 mr-2">
                                    <i class="fas fa-fw fa-sm fa-sync-alt mr-1"></i> Renew support
                                </a>

                                <button type="button" class="btn btn-sm btn-light" data-dismiss="alert" data-tooltip title="Dismiss notification" data-dismiss-inactive-support data-tooltip-hide-on-click>
                                    <i class="fas fa-fw fa-sm fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php ob_start() ?>
                <script>
                    'use strict';
                    document.querySelector('[data-dismiss-inactive-support]').addEventListener('click', event => {
                        set_cookie('dismiss_inactive_support', 1, 5, <?= json_encode(COOKIE_PATH) ?>);
                    });
                </script>
                <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
            </div>
    <?php endif ?>
<?php endif ?>
<?php //ALTUMCODE:DEMO } ?>
<?php endif ?>
