<?php defined('ALTUMCODE') || die() ?>

<p><?= sprintf(l('audits.email_report_error.p1', $data->row->language), remove_url_protocol_from_url($data->row->url)) ?></p>

<div>
    <table>
        <tbody>
            <tr>
                <th style="text-align: left;"><?= l('audits.host', $data->row->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= $data->row->host ?>
                    </span>
                </td>
            </tr>

            <tr>
                <th style="text-align: left;"><?= l('global.url', $data->row->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= $data->row->url ?>
                    </span>
                </td>
            </tr>

            <tr>
                <th style="text-align: left;"><?= l('audits.refresh_error_small', $data->row->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= $data->refresh_error ?>
                    </span>
                </td>
            </tr>

            <tr>
                <th style="text-align: left;"><?= l('audits.total_refreshes', $data->row->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?= nr($data->row->total_refreshes + 1) ?>
                    </span>
                </td>
            </tr>

            <?php if($data->row->settings->audit_check_interval): ?>
            <tr>
                <th style="text-align: left;"><?= l('audits.next_refresh_datetime', $data->row->language) ?></th>
                <td class="word-break-all">
                    <span>
                        <?php $next_refresh_datetime = (new \DateTime())->modify('+' . $data->row->settings->audit_check_interval . ' seconds')->format('Y-m-d H:i:s') ?>
                        <?= \Altum\Date::get_time_until($next_refresh_datetime) ?>
                    </span>
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
                            <a href="<?= url('audit/' . $data->row->audit_id) ?>">
                                <?= l('audits.email_report.button', $data->row->language) ?>
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
            <small><?= sprintf(l('audits.email_report.notice', $data->row->language), '<a href="' . url('audit-update/' . $data->row->audit_id) . '">', '</a>') ?></small>
        </td>
    </tr>
</table>
