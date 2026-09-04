<?php defined('ALTUMCODE') || die() ?>

<p><?= l('cron.is_not_ok.p1', $data->row->language) ?></p>

<div>
    <table>
        <tbody>
            <tr>
                <th style="text-align: left;"><?= l('global.name', $data->row->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= $data->row->name ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th style="text-align: left;"><?= l('cron.is_not_ok.start_datetime', $data->row->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= \Altum\Date::get() ?>
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
                            <a href="<?= url('incident/' . $data->incident_id) ?>">
                                <?= l('cron.is_not_ok.button', $data->row->language) ?>
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
            <small><?= sprintf(l('cron.is_not_ok.notice', $data->row->language), '<a href="' . url('heartbeat-update/' . $data->row->heartbeat_id) . '">', '</a>') ?></small>
        </td>
    </tr>
</table>
