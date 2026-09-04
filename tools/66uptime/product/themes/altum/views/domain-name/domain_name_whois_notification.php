<?php defined('ALTUMCODE') || die() ?>

<p><?= sprintf(l('domain_name.email_notifications.whois.p1', $data->row->language), $data->row->name, $data->row->target, $data->whois_expires_in_days, $data->whois_end_datetime, $data->timezone) ?></p>

<p>
    <small class="text-muted"><?= sprintf(l('domain_name.email_notifications.notice', $data->row->language), '<a href="' . url('domain-name-update/' . $data->row->domain_name_id) . '">', '</a>') ?></small>
</p>
