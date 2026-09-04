<?php defined('ALTUMCODE') || die() ?>

(() => {
    let pixel_url_base = <?= json_encode(isset(\Altum\Router::$data['domain']) ? \Altum\Router::$data['domain']->url : url()) ?>;
    let pixel_key = <?= json_encode($data->pixel_key) ?>;
    <?php if($data->pixel_goals_is_enabled): ?>
        let pixel_exposed_identifier = <?= json_encode(settings()->analytics->pixel_exposed_identifier) ?>;
        let pixel_goals = <?= json_encode($data->pixel_goals) ?>;
    <?php endif ?>
    let pixel_query_parameters_tracking_is_enabled = <?= json_encode($data->pixel_query_parameters_tracking_is_enabled) ?>;

    <?php $file_extension = '.min.js'; ?>

    <?php require_once ASSETS_PATH . 'js/pixel/lightweight/pixel-helpers' . $file_extension ?>

    <?php require_once ASSETS_PATH . 'js/pixel/lightweight/pixel-header' . $file_extension ?>

    <?php if($data->pixel_goals_is_enabled): ?>
        <?php require_once ASSETS_PATH . 'js/pixel/lightweight/pixel-goals' . $file_extension ?>
    <?php endif ?>

    <?php if($data->pixel_scroll_goals_is_enabled): ?>
        <?php require_once ASSETS_PATH . 'js/pixel/lightweight/pixel-goals-scroll' . $file_extension ?>
    <?php endif ?>

    <?php if($data->pixel_outbound_clicks_is_enabled ?? false): ?>
        <?php require_once ASSETS_PATH . 'js/pixel/lightweight/pixel-outbound' . $file_extension ?>
    <?php endif ?>

    <?php require_once ASSETS_PATH . 'js/pixel/lightweight/pixel-footer' . $file_extension ?>

})();
