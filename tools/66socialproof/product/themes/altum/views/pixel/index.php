<?php defined('ALTUMCODE') || die() ?>
<script>
    'use strict';
    
    (() => {
        let pixel_url_base = <?= json_encode(SITE_URL) ?>;
        let pixel_title = <?= json_encode(settings()->main->title) ?>;
        let pixel_key = <?= json_encode($data->pixel_key) ?>;
        let pixel_analytics = <?= json_encode((bool) settings()->notifications->analytics_is_enabled) ?>;
        let pixel_css_loaded = false;
        let pixel_css_failed = false;
        let campaign_domain = <?= json_encode($data->campaign->domain) ?>;
        if(campaign_domain.startsWith('www.')) {
            campaign_domain = campaign_domain.replace('www.', '');
        }

        /* Make sure the campaign loads only where expected */
        if(
            window.location.hostname !== campaign_domain && window.location.hostname !== `www.${campaign_domain}`
        ) {
            console.log(`${pixel_title} (${pixel_url_base}): Campaign does not match the set domain/subdomain.`);
            return;
        }

        <?php if (!empty($data->notifications)): ?>

        /* Make sure to include the external css file only once per pixel_key */
        const pixel_css_id = `${pixel_key}_pixel_css`;
        if(!document.getElementById(pixel_css_id)) {
            let pixel_css_link = document.createElement('link');
            pixel_css_link.id = pixel_css_id;
            pixel_css_link.href = '<?= ASSETS_FULL_URL . 'css/pixel.min.css' ?>';
            pixel_css_link.type = 'text/css';
            pixel_css_link.rel = 'stylesheet';
            pixel_css_link.media = 'screen,print';
            pixel_css_link.onload = function() { pixel_css_loaded = true; };
            pixel_css_link.onerror = function() { pixel_css_failed = true; };
            document.getElementsByTagName('head')[0].appendChild(pixel_css_link);
        } else {
            pixel_css_loaded = true;
        }

        /* Pixel header including all the needed code */
        <?php require_once ASSETS_PATH . 'js/pixel/pixel-header.js' ?>

        <?php foreach($data->notifications as $notification): ?>

            <?= \Altum\Notification::get($notification->type, $notification, $data->user)->javascript; ?>

        <?php endforeach ?>

        /* Send basic tracking data */
        send_tracking_data({type: 'track'});

        <?php endif ?>
    })();
</script>
