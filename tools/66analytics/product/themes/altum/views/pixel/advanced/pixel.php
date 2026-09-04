<?php defined('ALTUMCODE') || die() ?>

(() => {
    let pixel_url_base = <?= json_encode(isset(\Altum\Router::$data['domain']) ? \Altum\Router::$data['domain']->url : url()) ?>;
    let pixel_key = <?= json_encode($data->pixel_key) ?>;
    let pixel_track_events_children = <?= json_encode($data->pixel_track_events_children) ?>;
    let pixel_track_sessions_replays = <?= json_encode($data->pixel_track_sessions_replays) ?>;
    let pixel_heatmaps = <?= json_encode($data->pixel_heatmaps) ?>;
    <?php if($data->pixel_goals_is_enabled): ?>
        let pixel_exposed_identifier = <?= json_encode(settings()->analytics->pixel_exposed_identifier) ?>;
        let pixel_goals = <?= json_encode($data->pixel_goals) ?>;
    <?php endif ?>
    let pixel_query_parameters_tracking_is_enabled = <?= json_encode($data->pixel_query_parameters_tracking_is_enabled) ?>;
    let sessions_replays_hide_text_selector = <?= json_encode($data->pixel_sessions_replays_hide_text_selector ?? null) ?>;

    /* Helper messages */
    let pixel_key_optout_message = <?= json_encode(l('pixel.info_message.optout')) ?>;

    <?php $file_extension = '.min.js'; ?>

    <?php require_once ASSETS_PATH . 'js/pixel/advanced/pixel-helpers' . $file_extension ?>

    <?php if(!empty($data->pixel_heatmaps) || $data->pixel_track_sessions_replays): ?>
        <?php require_once ASSETS_PATH . 'js/pixel/advanced/pixel-rr' . $file_extension ?>
        <?php require_once ASSETS_PATH . 'js/pixel/advanced/pixel-heatmaps-and-replays-core' . $file_extension ?>
    <?php endif ?>

    <?php require_once ASSETS_PATH . 'js/pixel/advanced/pixel-visitor-and-events' . $file_extension ?>

    <?php if($data->pixel_goals_is_enabled): ?>
        <?php require_once ASSETS_PATH . 'js/pixel/advanced/pixel-goals' . $file_extension ?>
    <?php endif ?>

    <?php if($data->pixel_scroll_goals_is_enabled): ?>
        <?php require_once ASSETS_PATH . 'js/pixel/advanced/pixel-goals-scroll' . $file_extension ?>
    <?php endif ?>

    <?php if($data->pixel_outbound_clicks_is_enabled ?? false): ?>
        <?php require_once ASSETS_PATH . 'js/pixel/lightweight/pixel-outbound' . $file_extension ?>
    <?php endif ?>

    <?php if($data->pixel_track_events_children ?? false): ?>
        <?php require_once ASSETS_PATH . 'js/pixel/advanced/pixel-events-children' . $file_extension ?>
    <?php endif ?>

    <?php if(!empty($data->pixel_heatmaps)): ?>
        <?php require_once ASSETS_PATH . 'js/pixel/advanced/pixel-heatmaps' . $file_extension ?>
    <?php endif ?>

    <?php require_once ASSETS_PATH . 'js/pixel/advanced/pixel-initiate' . $file_extension ?>
})();
