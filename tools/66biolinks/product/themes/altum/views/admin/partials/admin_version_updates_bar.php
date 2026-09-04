<?php defined('ALTUMCODE') || die(); ?>

<?php if(ALTUMCODE == 66): ?>
<?php //ALTUMCODE:DEMO if(!DEMO) { ?>
<?php
$product_info = \Altum\Cache::cache_function_result('admin_product_info', null, function() {
    try {
        \Unirest\Request::timeout(3);
        $response = \Unirest\Request::get('https://66biolinks.com/info.php');

        if($response->code == 200) {
            return $response->body;
        } else {
            return null;
        }
    } catch (\Exception $exception) {
        return null;
    }
}, 86400 * 2);
?>

<?php if(
    $product_info
    && isset($product_info->latest_release_version)
    && !isset($_COOKIE['dismiss_version_updates'])
    && $product_info->latest_release_version_code != PRODUCT_CODE
): ?>
    <div class="alert alert-info mb-4">
        <div class="d-flex">
            <div class="mr-2 mt-1">
                <i class="fas fa-fw fa-check-circle text-info"></i>
            </div>

            <div class="w-100">
                <div class="d-flex justify-content-between align-items-center">
                    <strong>Update available — v<?= $product_info->latest_release_version ?></strong>

                    <button type="button" class="btn btn-link text-reset text-decoration-none font-weight-500 btn-sm" data-toggle="collapse" data-target="#new_version_container" aria-expanded="false" aria-controls="new_version_container">
                        View details <i class="fas fa-fw fa-sm fa-chevron-right"></i>
                    </button>
                </div>

                <div class="collapse" id="new_version_container">
                    <div class="mt-1 font-size-little-small">
                        A new software version is ready to install. Review the <a href="https://altumco.de/<?= PRODUCT_KEY ?>-changelog" target="_blank">changelog</a> before updating. <br />
                    </div>

                    <div class="mt-1 font-size-little-small">
                        Prefer not to do it yourself? We can update it for you.
                    </div>

                    <div class="mt-3">
                        <a href="https://altumco.de/downloads" target="_blank" class="btn btn-sm btn-primary font-weight-500 mr-3">
                            <i class="fas fa-fw fa-sm fa-download mr-1"></i> Download update
                        </a>

                        <a href="https://altumco.de/contact" target="_blank" class="btn btn-sm btn-secondary mr-3">
                            <i class="fas fa-fw fa-sm fa-envelope mr-1"></i> Contact us
                        </a>

                        <button type="button" class="btn btn-sm btn-light" data-tooltip title="Dismiss notification" data-dismiss="alert" data-dismiss-version-updates>
                            <i class="fas fa-fw fa-sm fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php ob_start() ?>
        <script>
            'use strict';
            document.querySelector('[data-dismiss-version-updates]').addEventListener('click', event => {
                set_cookie('dismiss_version_updates', 1, 7, <?= json_encode(COOKIE_PATH) ?>);
            });
        </script>
        <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
    </div>
<?php endif ?>
<?php //ALTUMCODE:DEMO } ?>
<?php endif ?>
