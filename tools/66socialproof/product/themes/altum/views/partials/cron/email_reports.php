<?php defined('ALTUMCODE') || die() ?>

<p><?= sprintf(l('cron.email_reports.p1', $data->row->language), '<strong>' . e($data->row->domain) . '</strong>') ?></p>

<div style="margin-top: 30px">
    <h3><?= sprintf(l('cron.email_reports.summary', $data->row->language), \Altum\Date::get($data->start_date, 5) . ' - ' . \Altum\Date::get($data->date, 5)) ?></h3>

    <table>
        <tbody>
        <tr>
            <td><strong><?= l('notification.statistics.impressions_chart', $data->row->language) ?></strong></td>
            <td><span class="text-muted"><?= nr($data->statistics['impression'] ?? 0) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_statistics['impression'] ?? 0, $data->statistics['impression'] ?? 0) ?>

                <?php if(round($percentage) != 0): ?>
                    <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                <?php else: ?>
                    <span style="color: #6c757d !important;">0%</span>
                <?php endif ?>
            </td>
        </tr>

        <tr>
            <td><strong><?= l('notification.statistics.hovers_chart', $data->row->language) ?></strong></td>
            <td><span class="text-muted"><?= nr($data->statistics['hover'] ?? 0) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_statistics['hover'] ?? 0, $data->statistics['hover'] ?? 0) ?>

                <?php if(round($percentage) != 0): ?>
                    <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                <?php else: ?>
                    <span style="color: #6c757d !important;">0%</span>
                <?php endif ?>
            </td>
        </tr>

        <tr>
            <td><strong><?= l('notification.statistics.clicks_chart', $data->row->language) ?></strong></td>
            <td><span class="text-muted"><?= nr($data->statistics['click'] ?? 0) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_statistics['click'] ?? 0, $data->statistics['click'] ?? 0) ?>

                <?php if(round($percentage) != 0): ?>
                    <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                <?php else: ?>
                    <span style="color: #6c757d !important;">0%</span>
                <?php endif ?>
            </td>
        </tr>

        <tr>
            <td><strong><?= l('notification.statistics.form_submissions_chart', $data->row->language) ?></strong></td>
            <td><span class="text-muted"><?= nr($data->statistics['form_submission'] ?? 0) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_statistics['form_submission'] ?? 0, $data->statistics['form_submission'] ?? 0) ?>

                <?php if(round($percentage) != 0): ?>
                    <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                <?php else: ?>
                    <span style="color: #6c757d !important;">0%</span>
                <?php endif ?>
            </td>
        </tr>
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
            <td><?= l('notification.statistics.impressions_chart', $data->row->language) ?></td>
            <td><span class="text-muted"><?= nr($data->statistics['impression'] ?? 0) ?></span></td>
            <td><span class="text-muted"><?= nr($data->previous_statistics['impression'] ?? 0) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_statistics['impression'] ?? 0, $data->statistics['impression'] ?? 0) ?>

                <?php if(round($percentage) != 0): ?>
                    <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                <?php else: ?>
                    <span style="color: #6c757d !important;">0%</span>
                <?php endif ?>
            </td>
        </tr>

        <tr>
            <td><?= l('notification.statistics.hovers_chart', $data->row->language) ?></td>
            <td><span class="text-muted"><?= nr($data->statistics['hover'] ?? 0) ?></span></td>
            <td><span class="text-muted"><?= nr($data->previous_statistics['hover'] ?? 0) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_statistics['hover'] ?? 0, $data->statistics['hover'] ?? 0) ?>

                <?php if(round($percentage) != 0): ?>
                    <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                <?php else: ?>
                    <span style="color: #6c757d !important;">0%</span>
                <?php endif ?>
            </td>
        </tr>

        <tr>
            <td><?= l('notification.statistics.clicks_chart', $data->row->language) ?></td>
            <td><span class="text-muted"><?= nr($data->statistics['click'] ?? 0) ?></span></td>
            <td><span class="text-muted"><?= nr($data->previous_statistics['click'] ?? 0) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_statistics['click'] ?? 0, $data->statistics['click'] ?? 0) ?>

                <?php if(round($percentage) != 0): ?>
                    <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                <?php else: ?>
                    <span style="color: #6c757d !important;">0%</span>
                <?php endif ?>
            </td>
        </tr>

        <tr>
            <td><?= l('notification.statistics.form_submissions_chart', $data->row->language) ?></td>
            <td><span class="text-muted"><?= nr($data->statistics['form_submission'] ?? 0) ?></span></td>
            <td><span class="text-muted"><?= nr($data->previous_statistics['form_submission'] ?? 0) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_statistics['form_submission'] ?? 0, $data->statistics['form_submission'] ?? 0) ?>

                <?php if(round($percentage) != 0): ?>
                    <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                <?php else: ?>
                    <span style="color: #6c757d !important;">0%</span>
                <?php endif ?>
            </td>
        </tr>
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
                            <a href="<?= url('campaign/' . $data->row->campaign_id . '/statistics') ?>">
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
    <small style="color: #808080 !important;"><?= sprintf(l('cron.email_reports.notice', $data->row->language), '<a href="' . url('campaign/' . $data->row->campaign_id) . '">', '</a>') ?></small>
</p>
