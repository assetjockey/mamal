<?php defined('ALTUMCODE') || die() ?>

<p><?= l('cron.server_monitor.p1', $data->user->language) ?></p>

<div>
    <table>
        <tbody>
            <tr>
                <th style="text-align: left;"><?= l('global.name', $data->user->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= $data->row->name?>
                    </span>
                </td>
            </tr>
            <tr>
                <th style="text-align: left;"><?= l('server_monitor.input.target', $data->user->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= $data->row->target ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th style="text-align: left;"><?= l('server_monitor.input.alert_metric', $data->user->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= l('server_monitor.' . $data->alert->metric, $data->user->language) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th style="text-align: left;"><?= l('server_monitor.input.alert_rule', $data->user->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= l('server_monitor.input.alert_rule.' . $data->alert->rule, $data->user->language) . ' ' . $data->alert->value . '%' ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th style="text-align: left;"><?= l('cron.server_monitor.alert_trigger', $data->user->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= sprintf(l('cron.server_monitor.alert_trigger_value', $data->user->language), $data->alert->trigger) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th style="text-align: left;"><?= sprintf(l('cron.server_monitor.current_metric_value', $data->user->language), l('server_monitor.' . $data->alert->metric, $data->user->language)) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= $data->{$data->alert->metric} . '%' ?>
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
                            <a href="<?= url('server-monitor/' . $data->row->server_monitor_id) ?>">
                                <?= l('cron.server_monitor.button', $data->user->language) ?>
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

<hr />
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td class="note align-center">
            <small><?= sprintf(l('cron.server_monitor.notice', $data->user->language), '<a href="' . url('server-monitor-update/' . $data->row->server_monitor_id) . '">', '</a>') ?></small>
        </td>
    </tr>
</table>
