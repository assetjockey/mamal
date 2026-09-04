<?php defined('ALTUMCODE') || die() ?>

<p><?= sprintf(l('cron.email_reports.p1', $data->row->language), '<strong>' . e($data->row->host . $data->row->path) . '</strong>') ?></p>

<div style="margin-top: 30px">
    <h3><?= sprintf(l('cron.email_reports.summary', $data->row->language), \Altum\Date::get($data->start_date, 5) . ' - ' . \Altum\Date::get($data->date, 5)) ?></h3>

    <table>
        <tbody>
        <tr>
            <td><strong><?= l('analytics.visitors', $data->row->language) ?></strong></td>
            <td><span class="text-muted"><?= nr($data->basic_analytics->visitors) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_basic_analytics->visitors, $data->basic_analytics->visitors) ?>

                <?php if(round($percentage) != 0): ?>
                    <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                <?php else: ?>
                    <span style="color: #6c757d !important;">0%</span>
                <?php endif ?>
            </td>
        </tr>

        <tr>
            <td><strong><?= l('analytics.pageviews', $data->row->language) ?></strong></td>
            <td><span class="text-muted"><?= nr($data->basic_analytics->pageviews) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_basic_analytics->pageviews, $data->basic_analytics->pageviews) ?>

                <?php if(round($percentage) != 0): ?>
                    <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                <?php else: ?>
                    <span style="color: #6c757d !important;">0%</span>
                <?php endif ?>
            </td>
        </tr>

        <?php if($data->row->tracking_type == 'advanced'): ?>
            <tr>
                <td><strong><?= l('analytics.sessions', $data->row->language) ?></strong></td>
                <td><span class="text-muted"><?= nr($data->basic_analytics->sessions) ?></span></td>
                <td>
                    <?php $percentage = get_percentage_change($data->previous_basic_analytics->sessions, $data->basic_analytics->sessions) ?>

                    <?php if(round($percentage) != 0): ?>
                        <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                    <?php else: ?>
                        <span style="color: #6c757d !important;">0%</span>
                    <?php endif ?>
                </td>
            </tr>
        <?php endif ?>
        </tbody>
    </table>
</div>

<div style="margin-top: 30px">
    <h3><?= l('cron.email_reports.period_comparison', $data->row->language) ?></h3>

    <table>
        <thead>
        <tr>
            <td><strong><?= l('cron.email_reports.metric', $data->row->language) ?></strong></td>
            <td><strong><?= l('cron.email_reports.current_period', $data->row->language) ?></strong></td>
            <td><strong><?= l('cron.email_reports.previous_period', $data->row->language) ?></strong></td>
            <td><strong><?= l('cron.email_reports.change', $data->row->language) ?></strong></td>
        </tr>
        </thead>

        <tbody>
        <tr>
            <td><?= l('analytics.visitors', $data->row->language) ?></td>
            <td><span class="text-muted"><?= nr($data->basic_analytics->visitors) ?></span></td>
            <td><span class="text-muted"><?= nr($data->previous_basic_analytics->visitors) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_basic_analytics->visitors, $data->basic_analytics->visitors) ?>

                <?php if(round($percentage) != 0): ?>
                    <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                <?php else: ?>
                    <span style="color: #6c757d !important;">0%</span>
                <?php endif ?>
            </td>
        </tr>

        <tr>
            <td><?= l('analytics.pageviews', $data->row->language) ?></td>
            <td><span class="text-muted"><?= nr($data->basic_analytics->pageviews) ?></span></td>
            <td><span class="text-muted"><?= nr($data->previous_basic_analytics->pageviews) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_basic_analytics->pageviews, $data->basic_analytics->pageviews) ?>

                <?php if(round($percentage) != 0): ?>
                    <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                <?php else: ?>
                    <span style="color: #6c757d !important;">0%</span>
                <?php endif ?>
            </td>
        </tr>

        <?php if($data->row->tracking_type == 'advanced'): ?>
            <tr>
                <td><?= l('analytics.sessions', $data->row->language) ?></td>
                <td><span class="text-muted"><?= nr($data->basic_analytics->sessions) ?></span></td>
                <td><span class="text-muted"><?= nr($data->previous_basic_analytics->sessions) ?></span></td>
                <td>
                    <?php $percentage = get_percentage_change($data->previous_basic_analytics->sessions, $data->basic_analytics->sessions) ?>

                    <?php if(round($percentage) != 0): ?>
                        <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                    <?php else: ?>
                        <span style="color: #6c757d !important;">0%</span>
                    <?php endif ?>
                </td>
            </tr>
        <?php endif ?>
        </tbody>
    </table>
</div>

<div style="margin-top: 30px">
    <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
        <tbody>
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                    <tr>
                        <td>
                            <a href="<?= url('dashboard?website_id=' . $data->row->website_id) ?>">
                                <?= l('cron.email_reports.button', $data->row->language) ?>
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<p style="text-align: center;">
    <small style="color: #808080 !important;"><?= sprintf(l('cron.email_reports.notice', $data->row->language), '<a href="' . url('websites') . '">', '</a>') ?></small>
</p>
