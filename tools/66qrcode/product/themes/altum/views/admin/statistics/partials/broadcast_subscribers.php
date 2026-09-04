<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>
<div class="card mb-5">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="h5 text-truncate mb-0"><i class="fas fa-fw fa-users fa-xs text-primary-900 mr-2"></i> <?= l('admin_statistics.broadcast_subscribers.header') ?></h2>

            <div>
                <span class="badge <?= $data->total['broadcast_subscribers'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['broadcast_subscribers'] > 0 ? '+' : null) . nr($data->total['broadcast_subscribers']) ?></span>
            </div>
        </div>

        <div class="chart-container <?= $data->total['broadcast_subscribers'] ? null : 'd-none' ?>">
            <canvas id="broadcast_subscribers"></canvas>
        </div>
        <?= $data->total['broadcast_subscribers'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
    </div>
</div>

<div class="card mb-5">
    <div class="card-body">
        <h2 class="h5 mb-4"><i class="fas fa-fw fa-user-check fa-xs text-primary-900 mr-2"></i> <?= l('admin_statistics.broadcast_subscribers.statuses') ?></h2>

        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th><?= l('global.status') ?></th>
                    <th><?= l('admin_statistics.percentage') ?></th>
                    <th><?= l('admin_broadcast_subscribers.menu') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if(!empty($data->statuses)): ?>
                    <?php foreach($data->statuses as $status => $total): ?>
                        <tr>
                            <td class="text-nowrap">
                                <?php if($status == 1): ?>
                                    <?= l('admin_broadcast_subscribers.status.subscribed') ?>
                                <?php elseif($status == 2): ?>
                                    <?= l('admin_broadcast_subscribers.status.unsubscribed') ?>
                                <?php else: ?>
                                    <?= l('admin_broadcast_subscribers.status.pending') ?>
                                <?php endif ?>
                            </td>
                            <td class="text-nowrap">
                                <?= nr($total / $data->total['statuses'] * 100, 2) . '%' ?>
                            </td>
                            <td class="text-nowrap">
                                <?= nr($total) ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php else: ?>
                    <tr>
                        <td class="text-nowrap text-muted" colspan="3">
                            <?= l('global.no_data') ?>
                        </td>
                    </tr>
                <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-5">
    <div class="card-body">
        <h2 class="h5 mb-4"><i class="fas fa-fw fa-sign-in-alt fa-xs text-primary-900 mr-2"></i> <?= l('admin_statistics.broadcast_subscribers.sources') ?></h2>

        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th><?= l('admin_broadcast_subscribers.source') ?></th>
                    <th><?= l('admin_statistics.percentage') ?></th>
                    <th><?= l('admin_broadcast_subscribers.menu') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if(!empty($data->sources)): ?>
                    <?php foreach($data->sources as $source => $total): ?>
                        <tr>
                            <td class="text-nowrap">
                                <?php if(in_array($source, ['index', 'register', 'account'])): ?>
                                    <?= l('admin_broadcast_subscribers.source.' . $source) ?>
                                <?php else: ?>
                                    <?= l('global.unknown') ?>
                                <?php endif ?>
                            </td>
                            <td class="text-nowrap">
                                <?= nr($total / $data->total['sources'] * 100, 2) . '%' ?>
                            </td>
                            <td class="text-nowrap">
                                <?= nr($total) ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php else: ?>
                    <tr>
                        <td class="text-nowrap text-muted" colspan="3">
                            <?= l('global.no_data') ?>
                        </td>
                    </tr>
                <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-5">
    <div class="card-body">
        <h2 class="h5 mb-4"><i class="fas fa-fw fa-user-friends fa-xs text-primary-900 mr-2"></i> <?= l('admin_statistics.broadcast_subscribers.types') ?></h2>

        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th><?= l('admin_statistics.broadcast_subscribers.types') ?></th>
                    <th><?= l('admin_statistics.percentage') ?></th>
                    <th><?= l('admin_broadcast_subscribers.menu') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if($data->total['types']): ?>
                    <?php foreach($data->types as $type => $total): ?>
                        <tr>
                            <td class="text-nowrap">
                                <?= l('admin_statistics.broadcast_subscribers.type.' . $type) ?>
                            </td>
                            <td class="text-nowrap">
                                <?= nr($total / $data->total['types'] * 100, 2) . '%' ?>
                            </td>
                            <td class="text-nowrap">
                                <?= nr($total) ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php else: ?>
                    <tr>
                        <td class="text-nowrap text-muted" colspan="3">
                            <?= l('global.no_data') ?>
                        </td>
                    </tr>
                <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $html = ob_get_clean() ?>

<?php ob_start() ?>
<script>
    'use strict';

    let broadcast_subscribers_color = css.getPropertyValue('--primary');
    let broadcast_subscribers_color_gradient = null;

    /* Prepare chart */
    let broadcast_subscribers_chart = document.getElementById('broadcast_subscribers').getContext('2d');
    broadcast_subscribers_color_gradient = broadcast_subscribers_chart.createLinearGradient(0, 0, 0, 250);
    broadcast_subscribers_color_gradient.addColorStop(0, set_hex_opacity(broadcast_subscribers_color, 0.1));
    broadcast_subscribers_color_gradient.addColorStop(1, set_hex_opacity(broadcast_subscribers_color, 0.025));

    /* Display chart */
    new Chart(broadcast_subscribers_chart, {
        type: 'line',
        data: {
            labels: <?= $data->broadcast_subscribers_chart['labels'] ?>,
            datasets: [{
                label: <?= json_encode(l('admin_statistics.broadcast_subscribers.chart')) ?>,
                data: <?= isset($data->broadcast_subscribers_chart['broadcast_subscribers']) ? $data->broadcast_subscribers_chart['broadcast_subscribers'] : '[]' ?>,
                backgroundColor: broadcast_subscribers_color_gradient,
                borderColor: broadcast_subscribers_color,
                fill: true
            }]
        },
        options: chart_options
    });
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
