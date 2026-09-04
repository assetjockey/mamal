<?php defined('ALTUMCODE') || die(); ?>

<?php
function get_health_checks() {
    return [
        'php_version' => [
            'status' => version_compare(PHP_VERSION, '8.3.0', '>=') && version_compare(PHP_VERSION, '8.6', '<')
        ],
        'openssl' => [
            'status' => extension_loaded('openssl')
        ],
        'mbstring' => [
            'status' => extension_loaded('mbstring') && function_exists('mb_get_info')
        ],
        'gd' => [
            'status' => extension_loaded('gd') && function_exists('gd_info')
        ],
        'mysqli' => [
            'status' => function_exists('mysqli_connect')
        ],
        'curl' => [
            'status' => function_exists('curl_version')
        ],
        'intl' => [
            'status' => extension_loaded('intl')
        ],
        'set_time_limit' => [
            'status' => function_exists('set_time_limit')
        ],
        'iconv' => [
            'status' => function_exists('iconv')
        ],
        'get_headers' => [
            'status' => function_exists('get_headers')
        ],
        'mime_content_type' => [
            'status' => function_exists('mime_content_type')
        ],
        'allow_url_fopen' => [
            'status' => filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)
        ],
    ];
}

function get_failed_health_checks_count($health_checks) {
    return count(array_filter($health_checks, function($health_check) {
        return !$health_check['status'];
    }));
}

$health_checks = get_health_checks();
$failed_health_checks_count = get_failed_health_checks_count($health_checks);
?>

<?php //ALTUMCODE:DEMO if(!DEMO) { ?>
<?php if(!isset($_COOKIE['dismiss_health_check']) && $failed_health_checks_count): ?>
    <div class="alert alert-danger alert-dismissible" role="alert">
        <i class="fas fa-fw fa-heartbeat mr-2"></i>
        <?= sprintf(l('admin_settings.health.error_message.failed_checks'), $failed_health_checks_count, url('admin/settings/health')) ?>

        <button type="button" class="close" data-dismiss="alert" data-dismiss-health-check>
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <?php ob_start() ?>
    <script>
        'use strict';

        document.querySelector('[data-dismiss-health-check]').addEventListener('click', event => {
            set_cookie('dismiss_health_check', 1, 30, <?= json_encode(COOKIE_PATH) ?>);
        });
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
<?php endif ?>
<?php //ALTUMCODE:DEMO } ?>
