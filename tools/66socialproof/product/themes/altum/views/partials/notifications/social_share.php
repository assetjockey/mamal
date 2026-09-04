<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>
<?php if($notification->settings->custom_css && $user->plan_settings->custom_css_is_enabled): ?>
<style>
    <?= $notification->settings->custom_css ?>
</style>
<?php endif ?>

<?php $shadow_color = hex_to_rgb($notification->settings->shadow_color ?? '#000000'); ?>
<div id="<?= !$is_preview ? 'notification_' . $notification->notification_id : null ?>" role="dialog" class="altumcode-wrapper <?= $notification->settings->dark_mode_is_enabled ? 'altumcode-wrapper-dark' : null ?> altumcode-wrapper-<?= $notification->settings->border_radius ?> <?= $notification->settings->shadow ? 'altumcode-wrapper-shadow-' . $notification->settings->shadow : null ?> <?= $notification->settings->hover_animation ? 'altumcode-wrapper-' . $notification->settings->hover_animation : null ?> <?= ($notification->settings->direction ?? 'ltr') == 'rtl' ? 'altumcode-rtl' : null ?> altumcode-social-share-wrapper" style='font-family: <?= $notification->settings->font ?? 'inherit' ?>!important;background-color: <?= $notification->settings->background_color ?>;border-width: <?= $notification->settings->border_width ?>px;border-color: <?= $notification->settings->border_color ?>;padding: <?= $notification->settings->internal_padding ?? 12 ?>px !important;<?= $notification->settings->background_blur ? 'backdrop-filter: blur(' . ($notification->settings->background_blur ?? 0). 'px);-webkit-backdrop-filter: blur(' . ($notification->settings->background_blur ?? 0). 'px)' : null ?>;'>

    <style>
        <?= '#notification_' . $notification->notification_id ?> {
            --shadow-r: <?= $shadow_color['r'] ?>;
            --shadow-g: <?= $shadow_color['g'] ?>;
            --shadow-b: <?= $shadow_color['b'] ?>;
        }

        <?php if(!$is_preview && $notification->settings->dark_mode_is_enabled): ?>
        <?php $dark_shadow_color = hex_to_rgb($notification->settings->dark_mode_shadow_color ?? '#000000'); ?>
        @media (prefers-color-scheme: dark) {
            <?= '#notification_' . $notification->notification_id ?> {
                background-color: <?= $notification->settings->dark_mode_background_color ?> !important;
                border-color: <?= $notification->settings->dark_mode_border_color ?> !important;
                --shadow-r: <?= $dark_shadow_color['r'] ?> !important;
                --shadow-g: <?= $dark_shadow_color['g'] ?> !important;
                --shadow-b: <?= $dark_shadow_color['b'] ?> !important;
            }

            <?= '#notification_' . $notification->notification_id ?> .altumcode-social-share-title {
                color: <?= $notification->settings->dark_mode_title_color ?> !important;
            }

            <?= '#notification_' . $notification->notification_id ?> .altumcode-social-share-description {
                color: <?= $notification->settings->dark_mode_description_color ?> !important;
            }

            <?= '#notification_' . $notification->notification_id ?> .altumcode-close  {
                color: <?= $notification->settings->dark_mode_close_button_color ?> !important;
            }
        }
        <?php endif ?>
    </style>

    <div class="altumcode-social-share-content">
        <div class="altumcode-social-share-header">
            <p class="altumcode-social-share-title" style="color: <?= $notification->settings->title_color ?>"><?= process_spintax($notification->settings->title) ?></p>

            <button class="altumcode-close" style="color: <?= $notification->settings->close_button_color ?>;<?= $notification->settings->display_close_button ? null : 'display: none;' ?>">×</button>
        </div>

        <div class="altumcode-social-share-buttons">

            <?php if($notification->settings->share_facebook): ?>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $notification->settings->share_url ? urlencode($notification->settings->share_url) : 'SOCIAL_SHARE_URL' ?>&amp;src=sdkpreparse" target="_blank" class="altumcode-social-share-button altumcode-social-share-button-facebook" title="Facebook">
                    <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M23.998 12c0-6.628-5.372-12-11.999-12C5.372 0 0 5.372 0 12c0 5.988 4.388 10.952 10.124 11.852v-8.384H7.078v-3.469h3.046V9.356c0-3.008 1.792-4.669 4.532-4.669 1.313 0 2.686.234 2.686.234v2.953H15.83c-1.49 0-1.955.925-1.955 1.874V12h3.328l-.532 3.469h-2.796v8.384c5.736-.9 10.124-5.864 10.124-11.853z"></path></svg>
                </a>
            <?php endif ?>

            <?php if($notification->settings->share_threads): ?>
                <a href="https://threads.com/intent/post?text=<?= $notification->settings->share_url ? urlencode($notification->settings->share_url) : 'SOCIAL_SHARE_URL' ?>&amp;src=sdkpreparse" target="_blank" class="altumcode-social-share-button altumcode-social-share-button-threads" title="Threads">
                    <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 153.14 178"><defs><linearGradient id="a" x1="41.76" y1="-17.79" x2="110.1" y2="180.87" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#515bd4"/><stop offset=".25" stop-color="#8134af"/><stop offset=".5" stop-color="#dd2a7b"/><stop offset=".75" stop-color="#f58529"/><stop offset="1" stop-color="#feda77"/></linearGradient></defs><path d="M119.16 82.5c-.76-.37-1.54-.72-2.33-1.06-1.37-25.32-15.21-39.81-38.43-40h-.32c-13.89 0-25.45 5.93-32.56 16.72L58.3 67c5.31-8.06 13.65-9.78 19.79-9.78h.21c7.65.05 13.42 2.27 17.15 6.61 2.72 3.15 4.54 7.52 5.44 13a97.8 97.8 0 0 0-22-1.06C56.85 77 42.66 89.92 43.61 107.81a27.6 27.6 0 0 0 12.73 22A39.4 39.4 0 0 0 80 135.75c11.55-.64 20.61-5 26.93-13.1 4.8-6.12 7.83-14.05 9.17-24a28.3 28.3 0 0 1 11.84 13c3.83 8.93 4.06 23.61-7.92 35.58-10.49 10.48-23.11 15-42.17 15.15-21.15-.15-37.14-6.93-47.54-20.15-9.7-12.47-14.73-30.35-14.91-53.23.18-22.88 5.21-40.76 15-53.14 10.4-13.22 26.39-20 47.54-20.15 21.3.15 37.57 7 48.36 20.25 5.3 6.51 9.29 14.7 11.92 24.25l15-4a81.1 81.1 0 0 0-15-30.29C124.26 8.91 104 .18 77.94 0h-.1c-26 .18-46.05 8.94-59.49 26C6.38 41.24.21 62.41 0 88.94v.12c.21 26.53 6.38 47.7 18.35 62.91 13.44 17.09 33.46 25.85 59.49 26h.1c23.14-.16 39.45-6.22 52.89-19.64 17.58-17.57 17-39.58 11.25-53.1-4.15-9.66-12.08-17.53-22.92-22.73m-39.95 37.56c-9.68.55-19.73-3.8-20.23-13.1-.37-6.9 4.91-14.6 20.83-15.52 1.82-.1 3.61-.15 5.36-.15a76 76 0 0 1 16.11 1.63c-1.84 22.91-12.59 26.62-22.07 27.14" style="fill:url(#a)"/></svg>
                </a>
            <?php endif ?>

            <?php if($notification->settings->share_linkedin): ?>
                <a href="https://www.linkedin.com/sharing/share-offsite/?mini=true&url=<?= $notification->settings->share_url ? urlencode($notification->settings->share_url) : 'SOCIAL_SHARE_URL' ?>" target="_blank" class="altumcode-social-share-button altumcode-social-share-button-linkedin" title="LinkedIn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor" stroke="currentColor"><path d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z"/></svg>
                </a>
            <?php endif ?>

            <?php if($notification->settings->share_pinterest): ?>
                <a href="https://pinterest.com/pin/create/link/?url=<?= $notification->settings->share_url ? urlencode($notification->settings->share_url) : 'SOCIAL_SHARE_URL' ?>" target="_blank" class="altumcode-social-share-button altumcode-social-share-button-pinterest" title="Pinterest">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" fill="currentColor" stroke="currentColor"><path d="M496 256c0 137-111 248-248 248-25.6 0-50.2-3.9-73.4-11.1 10.1-16.5 25.2-43.5 30.8-65 3-11.6 15.4-59 15.4-59 8.1 15.4 31.7 28.5 56.8 28.5 74.8 0 128.7-68.8 128.7-154.3 0-81.9-66.9-143.2-152.9-143.2-107 0-163.9 71.8-163.9 150.1 0 36.4 19.4 81.7 50.3 96.1 4.7 2.2 7.2 1.2 8.3-3.3.8-3.4 5-20.3 6.9-28.1.6-2.5.3-4.7-1.7-7.1-10.1-12.5-18.3-35.3-18.3-56.6 0-54.7 41.4-107.6 112-107.6 60.9 0 103.6 41.5 103.6 100.9 0 67.1-33.9 113.6-78 113.6-24.3 0-42.6-20.1-36.7-44.8 7-29.5 20.5-61.3 20.5-82.6 0-19-10.2-34.9-31.4-34.9-24.9 0-44.9 25.7-44.9 60.2 0 22 7.4 36.8 7.4 36.8s-24.5 103.8-29 123.2c-5 21.4-3 51.6-.9 71.2C65.4 450.9 0 361.1 0 256 0 119 111 8 248 8s248 111 248 248z"/></svg>
                </a>
            <?php endif ?>

            <?php if($notification->settings->share_reddit): ?>
                <a href="https://www.reddit.com/submit?url=<?= $notification->settings->share_url ? urlencode($notification->settings->share_url) : 'SOCIAL_SHARE_URL' ?>" target="_blank" class="altumcode-social-share-button altumcode-social-share-button-reddit" title="Reddit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor" stroke="currentColor"><path d="M201.5 305.5c-13.8 0-24.9-11.1-24.9-24.6 0-13.8 11.1-24.9 24.9-24.9 13.6 0 24.6 11.1 24.6 24.9 0 13.6-11.1 24.6-24.6 24.6zM504 256c0 137-111 248-248 248S8 393 8 256 119 8 256 8s248 111 248 248zm-132.3-41.2c-9.4 0-17.7 3.9-23.8 10-22.4-15.5-52.6-25.5-86.1-26.6l17.4-78.3 55.4 12.5c0 13.6 11.1 24.6 24.6 24.6 13.8 0 24.9-11.3 24.9-24.9s-11.1-24.9-24.9-24.9c-9.7 0-18 5.8-22.1 13.8l-61.2-13.6c-3-.8-6.1 1.4-6.9 4.4l-19.1 86.4c-33.2 1.4-63.1 11.3-85.5 26.8-6.1-6.4-14.7-10.2-24.1-10.2-34.9 0-46.3 46.9-14.4 62.8-1.1 5-1.7 10.2-1.7 15.5 0 52.6 59.2 95.2 132 95.2 73.1 0 132.3-42.6 132.3-95.2 0-5.3-.6-10.8-1.9-15.8 31.3-16 19.8-62.5-14.9-62.5zM302.8 331c-18.2 18.2-76.1 17.9-93.6 0-2.2-2.2-6.1-2.2-8.3 0-2.5 2.5-2.5 6.4 0 8.6 22.8 22.8 87.3 22.8 110.2 0 2.5-2.2 2.5-6.1 0-8.6-2.2-2.2-6.1-2.2-8.3 0zm7.7-75c-13.6 0-24.6 11.1-24.6 24.9 0 13.6 11.1 24.6 24.6 24.6 13.8 0 24.9-11.1 24.9-24.6 0-13.8-11-24.9-24.9-24.9z"/></svg>
                </a>
            <?php endif ?>

            <?php if($notification->settings->share_x): ?>
                <a href="https://twitter.com/intent/tweet?url=<?= $notification->settings->share_url ? urlencode($notification->settings->share_url) : 'SOCIAL_SHARE_URL' ?>" target="_blank" class="altumcode-social-share-button altumcode-social-share-button-x" title="X">
                    <svg width="1200" height="1227" viewBox="0 0 1200 1227" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M714.163 519.284L1160.89 0H1055.03L667.137 450.887L357.328 0H0L468.492 681.821L0 1226.37H105.866L515.491 750.218L842.672 1226.37H1200L714.137 519.284H714.163ZM569.165 687.828L521.697 619.934L144.011 79.6944H306.615L611.412 515.685L658.88 583.579L1055.08 1150.3H892.476L569.165 687.854V687.828Z" fill="white"/>
                    </svg>
                </a>
            <?php endif ?>

            <?php if($notification->settings->share_tumblr): ?>
                <a href="https://www.tumblr.com/widgets/share/tool?canonicalUrl=<?= $notification->settings->share_url ? urlencode($notification->settings->share_url) : 'SOCIAL_SHARE_URL' ?>" target="_blank" class="altumcode-social-share-button altumcode-social-share-button-tumblr" title="Tumblr">
                    <svg fill="white" height="800" width="800" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 260 260" xml:space="preserve"><path d="M210.857 197.545a5 5 0 0 0-5.119.223c-11.62 7.638-23.4 11.511-35.016 11.511-6.242 0-11.605-1.394-16.416-4.275-3.27-1.936-6.308-5.321-7.397-8.263-1.057-2.797-1.045-10.327-1.029-20.748l.005-63.543h52.795a5 5 0 0 0 5-5V62.802a5 5 0 0 0-5-5h-52.795V5a5 5 0 0 0-5-5h-35.566a5 5 0 0 0-4.964 4.397c-1.486 12.229-4.258 22.383-8.247 30.196-3.89 7.7-9.153 14.401-15.651 19.925-5.206 4.44-14.118 8.736-26.49 12.769a5 5 0 0 0-3.45 4.754v35.41a5 5 0 0 0 5 5H80.47v82.666c0 12.181 1.292 21.347 3.952 28.026 2.71 6.785 7.521 13.174 14.303 18.993 6.671 5.716 14.79 10.187 24.158 13.298 9.082 2.962 16.315 4.567 28.511 4.567 10.31 0 20.137-1.069 29.213-3.179 8.921-2.082 19.017-5.761 30.008-10.934a5 5 0 0 0 2.871-4.524v-39.417a5 5 0 0 0-2.629-4.402"/></svg>
                </a>
            <?php endif ?>

            <?php if($notification->settings->share_telegram): ?>
                <a href="https://t.me/share/url?url=<?= $notification->settings->share_url ? urlencode($notification->settings->share_url) : 'SOCIAL_SHARE_URL' ?>" target="_blank" class="altumcode-social-share-button altumcode-social-share-button-telegram" title="Telegram">
                    <svg width="800" height="800" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M24 0v24H0V0zM12.593 23.258l-.011.002-.071.035-.02.004-.014-.004-.071-.035q-.016-.005-.024.005l-.004.01-.017.428.005.02.01.013.104.074.015.004.012-.004.104-.074.012-.016.004-.017-.017-.427q-.004-.016-.017-.018m.265-.113-.013.002-.185.093-.01.01-.003.011.018.43.005.012.008.007.201.093q.019.005.029-.008l.004-.014-.034-.614q-.005-.018-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014-.034.614q.001.018.017.024l.015-.002.201-.093.01-.008.004-.011.017-.43-.003-.012-.01-.01z"/><path d="M19.777 4.43a1.5 1.5 0 0 1 2.062 1.626l-2.268 13.757c-.22 1.327-1.676 2.088-2.893 1.427-1.018-.553-2.53-1.405-3.89-2.294-.68-.445-2.763-1.87-2.507-2.884.22-.867 3.72-4.125 5.72-6.062.785-.761.427-1.2-.5-.5-2.302 1.738-5.998 4.381-7.22 5.125-1.078.656-1.64.768-2.312.656-1.226-.204-2.363-.52-3.291-.905-1.254-.52-1.193-2.244-.001-2.746z" fill="white"/></g></svg>
                </a>
            <?php endif ?>

            <?php if($notification->settings->share_whatsapp): ?>
                <a href="https://wa.me/?text=<?= $notification->settings->share_url ? urlencode($notification->settings->share_url) : 'SOCIAL_SHARE_URL' ?>" target="_blank" class="altumcode-social-share-button altumcode-social-share-button-whatsapp" title="WhatsApp">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="white" width="24" height="24" viewBox="0 0 24 24"><path d="m.057 24 1.687-6.163a11.87 11.87 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 0 1 8.413 3.488 11.82 11.82 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648zm11.387-5.464c-.074-.124-.272-.198-.57-.347s-1.758-.868-2.031-.967c-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165s-.347.223-.644.074-1.255-.462-2.39-1.475c-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074 2.095 3.2 5.076 4.487c.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413s.248-1.29.173-1.414"/></svg>
                </a>
            <?php endif ?>
        </div>

        <?php if($notification->settings->description): ?>
        <p class="altumcode-social-share-description" style="color: <?= $notification->settings->description_color ?>"><?= process_spintax($notification->settings->description) ?></p>
        <?php endif ?>

        <?php if(isset($notification->branding, $notification->branding->name, $notification->branding->url) && !empty($notification->branding->name) && !empty($notification->branding->url)): ?>
            <a href="<?= $notification->branding->url ?>" class="altumcode-site" style="display: <?= $notification->settings->display_branding ? 'inherit;' : 'none !important;' ?>"><?= $notification->branding->name ?></a>
        <?php else: ?>
            <span class="altumcode-site" style="display: <?= $notification->settings->display_branding ? 'inherit;' : 'none !important;' ?>"><?= settings()->notifications->branding ?></span>
        <?php endif ?>
    </div>

</div>
<?php $html = ob_get_clean(); ?>


<?php ob_start() ?>
<script>
    'use strict';

new AltumCodeManager({
    content: <?= json_encode($html) ?>,
    display_mobile: <?= json_encode($notification->settings->display_mobile) ?>,
    display_desktop: <?= json_encode($notification->settings->display_desktop) ?>,
    display_trigger: <?= json_encode($notification->settings->display_trigger) ?>,
    display_trigger_value: <?= json_encode($notification->settings->display_trigger_value) ?>,
    display_trigger_selector: <?= json_encode($notification->settings->display_trigger_selector ?? ($notification->settings->selector ?? '')) ?>,
    display_delay_type_after_close: <?= json_encode($notification->settings->display_delay_type_after_close) ?>,
    display_delay_value_after_close: <?= json_encode($notification->settings->display_delay_value_after_close) ?>,
    duration: <?= $notification->settings->display_duration === -1 ? -1 : $notification->settings->display_duration * 1000 ?>,
    url: '',
    close: <?= json_encode($notification->settings->display_close_button) ?>,
    display_frequency: <?= json_encode($notification->settings->display_frequency) ?>,
    position: <?= json_encode($notification->settings->display_position) ?>,
    trigger_all_pages: <?= json_encode($notification->settings->trigger_all_pages) ?>,
    triggers: <?= json_encode($notification->settings->triggers) ?>,
    on_animation: <?= json_encode($notification->settings->on_animation) ?>,
    off_animation: <?= json_encode($notification->settings->off_animation) ?>,
    animation: <?= json_encode($notification->settings->animation) ?>,
    animation_interval: <?= (int) $notification->settings->animation_interval ?>,

    notification_id: <?= $notification->notification_id ?>
}).initiate({
    displayed: main_element => {

        main_element.querySelectorAll('.altumcode-social-share-buttons a').forEach(element => {
           element.setAttribute('href', element.getAttribute('href').replace('SOCIAL_SHARE_URL', window.location.href));
        });

        /* On click event to the button */
        main_element.querySelector('.altumcode-social-share-button').addEventListener('click', event => {

            let notification_id = main_element.getAttribute('data-notification-id');

            send_tracking_data({
                notification_id: notification_id,
                type: 'notification',
                subtype: 'click'
            });

        });

    }
});
</script>
<?php $javascript = ob_get_clean(); ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
