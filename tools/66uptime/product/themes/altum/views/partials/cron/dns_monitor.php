<?php defined('ALTUMCODE') || die() ?>

<p><?= l('cron.dns_monitor.p1', $data->row->language) ?></p>

<p><?= $data->content ?></p>

<div style="margin-top: 30px">
    <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
        <tbody>
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                    <tr>
                        <td>
                            <a href="<?= url('dns-monitor/' . $data->row->dns_monitor_id) ?>">
                                <?= l('cron.dns_monitor.button', $data->row->language) ?>
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
            <small><?= sprintf(l('cron.dns_monitor.notice', $data->row->language), '<a href="' . url('dns-monitor-update/' . $data->row->dns_monitor_id) . '">', '</a>') ?></small>
        </td>
    </tr>
</table>
