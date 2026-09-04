<?php defined('ALTUMCODE') || die() ?>

<p><?= sprintf(l('cron.monitor_email_report.p1', $data->row->language), '<strong>' . $data->row->name . '</strong>', '<strong>' . $data->row->target . ($data->row->port ? ':' . $data->row->port : null) . '</strong>') ?></p>

<div>
    <table>
        <tbody>
            <tr>
                <th style="text-align: left;"><?= l('cron.monitor_email_report.datetime_range', $data->row->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= \Altum\Date::get($data->start_date, 5) . ' - ' . \Altum\Date::get($data->end_date, 5) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th style="text-align: left;"><?= l('cron.monitor_email_report.uptime', $data->row->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= nr($data->monitor_logs_data['uptime'], settings()->monitors_heartbeats->decimals) . '%' ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th style="text-align: left;"><?= l('cron.monitor_email_report.average_response_time', $data->row->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= display_response_time($data->monitor_logs_data['average_response_time']) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th style="text-align: left;"><?= l('cron.monitor_email_report.total_monitor_logs', $data->row->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= nr($data->total_monitor_logs) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th style="text-align: left;"><?= l('cron.monitor_email_report.total_ok_checks', $data->row->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= nr($data->monitor_logs_data['total_ok_checks']) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th style="text-align: left;"><?= l('cron.monitor_email_report.total_not_ok_checks', $data->row->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= nr($data->monitor_logs_data['total_not_ok_checks']) ?>
                    </span>
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
                            <a href="<?= url('monitor/' . $data->row->monitor_id) ?>">
                                <?= l('cron.monitor_email_report.button', $data->row->language) ?>
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

<p>
    <small class="text-muted"><?= sprintf(l('cron.monitor_email_report.notice', $data->row->language), '<a href="' . url('monitor-update/' . $data->row->monitor_id) . '">', '</a>') ?></small>
</p>
