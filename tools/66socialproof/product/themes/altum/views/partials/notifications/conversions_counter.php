<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>
<?php if($notification->settings->custom_css && $user->plan_settings->custom_css_is_enabled): ?>
    <style>
        <?= $notification->settings->custom_css ?>
    </style>
<?php endif ?>

<?php $shadow_color = hex_to_rgb($notification->settings->shadow_color ?? '#000000'); ?>
<div id="<?= !$is_preview ? 'notification_' . $notification->notification_id : null ?>" role="dialog" class="altumcode-wrapper <?= $notification->settings->dark_mode_is_enabled ? 'altumcode-wrapper-dark' : null ?> altumcode-wrapper-<?= $notification->settings->border_radius ?> <?= $notification->settings->shadow ? 'altumcode-wrapper-shadow-' . $notification->settings->shadow : null ?> <?= $notification->settings->hover_animation ? 'altumcode-wrapper-' . $notification->settings->hover_animation : null ?> <?= ($notification->settings->direction ?? 'ltr') == 'rtl' ? 'altumcode-rtl' : null ?> altumcode-conversions-counter-wrapper" style='font-family: <?= $notification->settings->font ?? 'inherit' ?>!important;background-color: <?= $notification->settings->background_color ?>;border-width: <?= $notification->settings->border_width ?>px;border-color: <?= $notification->settings->border_color ?>;padding: <?= $notification->settings->internal_padding ?? 12 ?>px !important;<?= $notification->settings->background_blur ? 'backdrop-filter: blur(' . ($notification->settings->background_blur ?? 0). 'px);-webkit-backdrop-filter: blur(' . ($notification->settings->background_blur ?? 0). 'px)' : null ?>;'>

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

            <?= '#notification_' . $notification->notification_id ?> .altumcode-conversions-counter-title {
                color: <?= $notification->settings->dark_mode_title_color ?> !important;
            }

            <?= '#notification_' . $notification->notification_id ?> .altumcode-close  {
                color: <?= $notification->settings->dark_mode_close_button_color ?> !important;
            }

            <?= '#notification_' . $notification->notification_id ?> .altumcode-conversions-counter-description {
                color: <?= $notification->settings->dark_mode_description_color ?> !important;
            }

            <?= '#notification_' . $notification->notification_id ?> .altumcode-conversions-counter-number {
                background-color: <?= $notification->settings->dark_mode_number_background_color ?> !important;
                color: <?= $notification->settings->dark_mode_number_color ?> !important;
            }
        }
        <?php endif ?>
    </style>

    <div class="altumcode-conversions-counter-content">

        <?php $counter = isset($notification->counter) && $notification->counter >= $notification->settings->display_minimum_activity ? $notification->counter : l('notification.conversions_counter.number_default') ?>
        <div class="altumcode-conversions-counter-number" style="background: <?= $notification->settings->number_background_color ?>; color: <?= $notification->settings->number_color ?>">
            <span class="count-up-animated" data-count-up-number="<?= $counter ?>"><?= $counter ?></span>
        </div>

        <div style="width: 100%!important;">
            <div class="altumcode-conversions-counter-header">
                <p class="altumcode-conversions-counter-title" style="color: <?= $notification->settings->title_color ?>"><?= process_spintax($notification->settings->title) ?></p>

                <div class="altumcode-conversions-counter-close">
                    <button class="altumcode-close" style="color: <?= $notification->settings->close_button_color ?>;<?= $notification->settings->display_close_button ? null : 'display: none;' ?>">×</button>
                </div>
            </div>

            <p class="altumcode-conversions-counter-description" style="color: <?= $notification->settings->description_color ?>"><?= sprintf(l('notification.conversions_counter.time_default'), $notification->settings->last_activity) ?></p>

            <?php if(isset($notification->branding, $notification->branding->name, $notification->branding->url) && !empty($notification->branding->name) && !empty($notification->branding->url)): ?>
                <a href="<?= $notification->branding->url ?>" class="altumcode-site" style="display: <?= $notification->settings->display_branding ? 'inherit;' : 'none !important;' ?>"><?= $notification->branding->name ?></a>
            <?php else: ?>
                <span class="altumcode-site" style="display: <?= $notification->settings->display_branding ? 'inherit;' : 'none !important;' ?>"><?= settings()->notifications->branding ?></span>
            <?php endif ?>
        </div>
    </div>
</div>
<?php $html = ob_get_clean(); ?>


<?php ob_start() ?>
<script>
    'use strict';

    new AltumCodeManager({
        should_show: <?= json_encode(isset($notification->counter) && $notification->counter >= $notification->settings->display_minimum_activity) ?>,
        content: <?= json_encode($html) ?>,
        display_mobile: <?= json_encode($notification->settings->display_mobile) ?>,
        display_desktop: <?= json_encode($notification->settings->display_desktop) ?>,
        display_trigger: <?= json_encode($notification->settings->display_trigger) ?>,
        display_trigger_value: <?= json_encode($notification->settings->display_trigger_value) ?>,
        display_trigger_selector: <?= json_encode($notification->settings->display_trigger_selector ?? ($notification->settings->selector ?? '')) ?>,
        display_delay_type_after_close: <?= json_encode($notification->settings->display_delay_type_after_close) ?>,
        display_delay_value_after_close: <?= json_encode($notification->settings->display_delay_value_after_close) ?>,
        duration: <?= $notification->settings->display_duration === -1 ? -1 : $notification->settings->display_duration * 1000 ?>,
        url: <?= json_encode($notification->settings->url) ?>,
        url_new_tab: <?= json_encode($notification->settings->url_new_tab) ?>,
        close: <?= json_encode($notification->settings->display_close_button) ?>,
        display_frequency: <?= json_encode($notification->settings->display_frequency) ?>,
        position: <?= json_encode($notification->settings->display_position) ?>,
        trigger_all_pages: <?= json_encode($notification->settings->trigger_all_pages) ?>,
        triggers: <?= json_encode($notification->settings->triggers) ?>,
        data_trigger_auto: <?= json_encode($notification->settings->data_trigger_auto) ?>,
        data_triggers_auto: <?= json_encode($notification->settings->data_triggers_auto) ?>,
        on_animation: <?= json_encode($notification->settings->on_animation) ?>,
        off_animation: <?= json_encode($notification->settings->off_animation) ?>,
        animation: <?= json_encode($notification->settings->animation) ?>,
        animation_interval: <?= (int) $notification->settings->animation_interval ?>,

        notification_id: <?= $notification->notification_id ?>
    }).initiate();
</script>
<?php $javascript = ob_get_clean(); ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
