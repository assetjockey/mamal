<?php defined('ALTUMCODE') || die() ?>

<script>
    'use strict';

    /* Some global variables */
    window.altum = {};
    let global_token = <?= json_encode(\Altum\Csrf::get('global_token')) ?>;
    let site_url = <?= json_encode(SITE_URL) ?>;
    let url = <?= json_encode(url()) ?>;
    let decimal_point = <?= json_encode(l('global.number.decimal_point')) ?>;
    let thousands_separator = <?= json_encode(l('global.number.thousands_separator')) ?>;
</script>
