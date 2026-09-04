<?php defined('ALTUMCODE') || die() ?>

<?php
/* Calculate engagement rates */
$open_rate = nr(get_percentage_between_two_numbers($data->total['unique_viewers'], $data->total['sent_emails']), 2) . '%';
$click_rate = nr(get_percentage_between_two_numbers($data->total['unique_clickers'], $data->total['sent_emails']), 2) . '%';
$click_to_open_rate = nr(get_percentage_between_two_numbers($data->total['unique_clickers'], $data->total['unique_viewers']), 2) . '%';
?>

<?php ob_start() ?>
<div class="card mb-5">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="h5 text-truncate mb-0"><i class="fas fa-fw fa-mail-bulk fa-xs text-primary-900 mr-2"></i> <?= l('admin_statistics.broadcasts.header') ?></h2>

            <div>
                <span class="badge <?= $data->total['broadcasts'] > 0 ? 'badge-success' : 'badge-secondary' ?>" data-toggle="tooltip" title="<?= l('admin_statistics.broadcasts.chart_broadcasts') ?>">
                    <?= ($data->total['broadcasts'] > 0 ? '+' : null) . nr($data->total['broadcasts']) ?>
                </span>
                <span class="badge <?= $data->total['sent_emails'] > 0 ? 'badge-success' : 'badge-secondary' ?>" data-toggle="tooltip" title="<?= l('admin_statistics.broadcasts.chart_sent_emails') ?>">
                    <?= ($data->total['sent_emails'] > 0 ? '+' : null) . nr($data->total['sent_emails']) ?>
                </span>
            </div>
        </div>

        <div class="chart-container <?= $data->total['broadcasts'] + $data->total['sent_emails'] ? null : 'd-none' ?>">
            <canvas id="broadcasts"></canvas>
        </div>
        <?= $data->total['broadcasts'] + $data->total['sent_emails'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
    </div>
</div>

<div class="mb-5">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <h2 class="h5 text-truncate mb-0"><i class="fas fa-fw fa-chart-line fa-xs text-primary-900 mr-2"></i> <?= l('admin_statistics.broadcasts.engagement') ?></h2>
    </div>

    <div class="row mx-n2 mb-4">
        <div class="col-12 col-sm-6 p-2 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-eye text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex flex-column justify-content-center">
                    <div class="text-truncate text-nowrap font-size-little-small font-weight-450">
                        <?= nr($data->total['views']) ?> <span class="text-muted"><?= l('admin_statistics.broadcasts.chart_views') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 p-2 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-mouse text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex flex-column justify-content-center">
                    <div class="text-truncate text-nowrap font-size-little-small font-weight-450">
                        <?= nr($data->total['clicks']) ?> <span class="text-muted"><?= l('admin_statistics.broadcasts.chart_clicks') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 p-2 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-user-check text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex flex-column justify-content-center">
                    <div class="text-truncate text-nowrap font-size-little-small font-weight-450">
                        <?= nr($data->total['unique_viewers']) ?> <span class="text-muted"><?= l('admin_statistics.broadcasts.unique_viewers') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 p-2 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-users text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex flex-column justify-content-center">
                    <div class="text-truncate text-nowrap font-size-little-small font-weight-450">
                        <?= nr($data->total['unique_clickers']) ?> <span class="text-muted"><?= l('admin_statistics.broadcasts.unique_clickers') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 p-2 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-percentage text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex flex-column justify-content-center">
                    <div class="text-truncate text-nowrap font-size-little-small font-weight-450">
                        <?= $open_rate ?> <span class="text-muted"><?= l('admin_statistics.broadcasts.open_rate') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 p-2 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-link text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex flex-column justify-content-center">
                    <div class="text-truncate text-nowrap font-size-little-small font-weight-450">
                        <?= $click_rate ?> <span class="text-muted"><?= l('admin_statistics.broadcasts.click_rate') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 p-2 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-chart-pie text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex flex-column justify-content-center">
                    <div class="text-truncate text-nowrap font-size-little-small font-weight-450">
                        <?= $click_to_open_rate ?> <span class="text-muted"><?= l('admin_statistics.broadcasts.click_to_open_rate') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="chart-container <?= $data->total['views'] + $data->total['clicks'] ? null : 'd-none' ?>">
                <canvas id="broadcasts_engagement"></canvas>
            </div>
            <?= $data->total['views'] + $data->total['clicks'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
        </div>
    </div>
</div>
<?php $html = ob_get_clean() ?>

<?php ob_start() ?>
    <script>
        'use strict';

        let broadcasts_color = css.getPropertyValue('--gray-500');
        let sent_emails_color = css.getPropertyValue('--primary');
        let broadcasts_engagement_views_color = css.getPropertyValue('--primary');
        let broadcasts_engagement_clicks_color = css.getPropertyValue('--success');

        /* Display chart */
        let broadcasts_chart = document.getElementById('broadcasts').getContext('2d');

        let sent_emails_color_gradient = broadcasts_chart.createLinearGradient(0, 0, 0, 250);
        sent_emails_color_gradient.addColorStop(0, set_hex_opacity(sent_emails_color, 0.1));
        sent_emails_color_gradient.addColorStop(1, set_hex_opacity(sent_emails_color, 0.025));

        let broadcasts_color_gradient = broadcasts_chart.createLinearGradient(0, 0, 0, 250);
        broadcasts_color_gradient.addColorStop(0, set_hex_opacity(broadcasts_color, 0.1));
        broadcasts_color_gradient.addColorStop(1, set_hex_opacity(broadcasts_color, 0.025));

        new Chart(broadcasts_chart, {
            type: 'line',
            data: {
                labels: <?= $data->broadcasts_chart['labels'] ?>,
                datasets: [
                    {
                        label: <?= json_encode(l('admin_statistics.broadcasts.chart_broadcasts')) ?>,
                        data: <?= isset($data->broadcasts_chart['broadcasts']) ? $data->broadcasts_chart['broadcasts'] : '[]' ?>,
                        backgroundColor: broadcasts_color_gradient,
                        borderColor: broadcasts_color,
                        fill: true
                    },
                    {
                        label: <?= json_encode(l('admin_statistics.broadcasts.chart_sent_emails')) ?>,
                        data: <?= isset($data->broadcasts_chart['sent_emails']) ? $data->broadcasts_chart['sent_emails'] : '[]' ?>,
                        backgroundColor: sent_emails_color_gradient,
                        borderColor: sent_emails_color,
                        fill: true
                    }
                ]
            },
            options: chart_options
        });

        /* Display engagement chart */
        let broadcasts_engagement_chart = document.getElementById('broadcasts_engagement').getContext('2d');

        let broadcasts_engagement_views_color_gradient = broadcasts_engagement_chart.createLinearGradient(0, 0, 0, 250);
        broadcasts_engagement_views_color_gradient.addColorStop(0, set_hex_opacity(broadcasts_engagement_views_color, 0.1));
        broadcasts_engagement_views_color_gradient.addColorStop(1, set_hex_opacity(broadcasts_engagement_views_color, 0.025));

        let broadcasts_engagement_clicks_color_gradient = broadcasts_engagement_chart.createLinearGradient(0, 0, 0, 250);
        broadcasts_engagement_clicks_color_gradient.addColorStop(0, set_hex_opacity(broadcasts_engagement_clicks_color, 0.1));
        broadcasts_engagement_clicks_color_gradient.addColorStop(1, set_hex_opacity(broadcasts_engagement_clicks_color, 0.025));

        new Chart(broadcasts_engagement_chart, {
            type: 'line',
            data: {
                labels: <?= $data->broadcasts_engagement_chart['labels'] ?>,
                datasets: [
                    {
                        label: <?= json_encode(l('admin_statistics.broadcasts.chart_views')) ?>,
                        data: <?= isset($data->broadcasts_engagement_chart['views']) ? $data->broadcasts_engagement_chart['views'] : '[]' ?>,
                        backgroundColor: broadcasts_engagement_views_color_gradient,
                        borderColor: broadcasts_engagement_views_color,
                        fill: true
                    },
                    {
                        label: <?= json_encode(l('admin_statistics.broadcasts.chart_clicks')) ?>,
                        data: <?= isset($data->broadcasts_engagement_chart['clicks']) ? $data->broadcasts_engagement_chart['clicks'] : '[]' ?>,
                        backgroundColor: broadcasts_engagement_clicks_color_gradient,
                        borderColor: broadcasts_engagement_clicks_color,
                        fill: true
                    }
                ]
            },
            options: chart_options
        });
    </script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
