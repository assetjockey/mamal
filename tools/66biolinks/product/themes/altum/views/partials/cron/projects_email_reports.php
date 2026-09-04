<?php defined('ALTUMCODE') || die() ?>

<p><?= sprintf(l('cron.projects_email_reports.p1', $data->row->language), '<a href="' . url('project-update/' . $data->row->project_id) . '" target="_blank">' . $data->row->name . '</a>') ?></p>

<div style="margin-top: 30px">
    <h3><?= sprintf(l('cron.email_reports.summary', $data->row->language), \Altum\Date::get($data->start_date, 5) . ' - ' . \Altum\Date::get($data->date, 5)) ?></h3>

    <table>
        <tbody>
        <tr>
            <td><strong><?= l('link.statistics.pageviews', $data->row->language) ?></strong></td>
            <td><span class="text-muted"><?= nr($data->statistics['pageviews'] ?? 0) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_statistics['pageviews'] ?? 0, $data->statistics['pageviews'] ?? 0) ?>

                <?php if(round($percentage) != 0): ?>
                    <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                <?php else: ?>
                    <span style="color: #6c757d !important;">0%</span>
                <?php endif ?>
            </td>
        </tr>

        <tr>
            <td><strong><?= l('link.statistics.visitors', $data->row->language) ?></strong></td>
            <td><span class="text-muted"><?= nr($data->statistics['visitors'] ?? 0) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_statistics['visitors'] ?? 0, $data->statistics['visitors'] ?? 0) ?>

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
            <td><?= l('link.statistics.pageviews', $data->row->language) ?></td>
            <td><span class="text-muted"><?= nr($data->statistics['pageviews'] ?? 0) ?></span></td>
            <td><span class="text-muted"><?= nr($data->previous_statistics['pageviews'] ?? 0) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_statistics['pageviews'] ?? 0, $data->statistics['pageviews'] ?? 0) ?>

                <?php if(round($percentage) != 0): ?>
                    <?= round($percentage) > 0 ? '<span style="color: #28a745 !important;">+' . round($percentage) . '%</span>' : '<span style="color: #dc3545 !important;">' . round($percentage) . '%</span>' ?>
                <?php else: ?>
                    <span style="color: #6c757d !important;">0%</span>
                <?php endif ?>
            </td>
        </tr>

        <tr>
            <td><?= l('link.statistics.visitors', $data->row->language) ?></td>
            <td><span class="text-muted"><?= nr($data->statistics['visitors'] ?? 0) ?></span></td>
            <td><span class="text-muted"><?= nr($data->previous_statistics['visitors'] ?? 0) ?></span></td>
            <td>
                <?php $percentage = get_percentage_change($data->previous_statistics['visitors'] ?? 0, $data->statistics['visitors'] ?? 0) ?>

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

<?php if(count($data->links)): ?>
    <div style="margin-top: 30px">
        <h3><?= l('cron.projects_email_reports.links', $data->row->language) ?></h3>

        <table>
            <thead>
            <tr>
                <th style="text-align: left;"><?= l('global.url', $data->row->language) ?></th>
                <th style="text-align: left;"><?= l('global.type', $data->row->language) ?></th>
                <th style="text-align: left;"><?= l('link.statistics.pageviews', $data->row->language) ?></th>
                <th style="text-align: left;"><?= l('link.statistics.visitors', $data->row->language) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($data->links as $link): ?>
                <tr>
                    <td><a href="<?= url('link/' . $link->link_id . '/statistics') ?>" target="_blank"><?= $link->url ?></a></td>
                    <td><?= l('link.breadcrumb.' . $link->type, $data->row->language) ?></td>
                    <td><?= nr($link->pageviews ?? 0) ?></td>
                    <td><?= nr($link->visitors ?? 0) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>

<div style="margin-top: 30px">
    <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
        <tbody>
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                    <tr>
                        <td>
                            <a href="<?= url('links-statistics?project_id=' . $data->row->project_id) ?>">
                                <?= l('cron.projects_email_reports.button', $data->row->language) ?>
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
    <small style="color: #808080 !important;"><?= sprintf(l('cron.projects_email_reports.notice', $data->row->language), '<a href="' . url('project-update/' . $data->row->project_id) . '">', '</a>') ?></small>
</p>
