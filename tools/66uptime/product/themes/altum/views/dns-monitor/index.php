<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('dns-monitors') ?>"><?= l('dns_monitors.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('dns_monitor.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="card bg-blue-900 border-0 rounded-2x">
        <div class="card-body">
            <div class="row">
                <div class="col-auto d-flex align-items-center">
                    <?php if($data->dns_monitor->is_enabled): ?>
                        <?php if(!$data->dns_monitor->total_checks): ?>
                            <div data-toggle="tooltip" title="<?= l('dns_monitor.pending_check') ?>">
                                <i class="fas fa-fw fa-clock fa-3x text-gray-400"></i>
                            </div>
                        <?php else: ?>
                            <div class="pulse-animation pulse-animation-success">
                                <span class="pulse-circle"></span>
                                <i class="fas fa-fw fa-check-circle fa-3x text-primary-400"></i>
                            </div>
                        <?php endif ?>
                    <?php else: ?>
                        <div data-toggle="tooltip" title="<?= l('dns_monitor.is_enabled_paused') ?>">
                            <i class="fas fa-fw fa-pause-circle fa-3x text-warning"></i>
                        </div>
                    <?php endif ?>
                </div>

                <div class="col text-truncate">
                    <h1 class="h3 text-truncate text-white mb-0 mr-2"><?= sprintf(l('dns_monitor.header'), $data->dns_monitor->name) ?></h1>

                    <div class="text-gray-400">
                        <span><?= $data->dns_monitor->target ?></span>
                    </div>
                </div>

                <div class="col-auto">
                    <?= include_view(THEME_PATH . 'views/dns-monitor/dns_monitor_dropdown_button.php', ['id' => $data->dns_monitor->dns_monitor_id, 'resource_name' => $data->dns_monitor->name, 'button_text_class' => 'text-white']) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if(!$data->dns_monitor->total_checks): ?>
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex flex-column align-items-center justify-content-center py-4">
                    <img src="<?= ASSETS_FULL_URL . 'images/processing.svg' ?>" class="col-10 col-md-7 col-lg-5 mb-3" alt="<?= l('dns_monitor.no_data') ?>" />
                    <h2 class="h4 text-muted"><?= l('dns_monitor.no_data') ?></h2>
                    <p class="text-muted"><?= sprintf(l('dns_monitor.no_data_help'), $data->dns_monitor->name) ?></p>
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if($data->dns_monitor->total_checks): ?>

        <div class="row justify-content-between my-3 mb-4">
            <div class="col-12 col-lg-6 col-xl-4 p-3">
                <div class="card h-100">
                    <div class="card-body d-flex p-3">
                        <div>
                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-globe fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted font-size-small"><?= l('dns_monitor.total_checks') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0"><?= nr($data->dns_monitor->total_checks) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6 col-xl-4 p-3">
                <div class="card h-100">
                    <div class="card-body d-flex p-3">
                        <div>
                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-calendar-check fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted font-size-small"><?= l('dns_monitor.last_check_datetime') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0"><?= $data->dns_monitor->last_check_datetime ? \Altum\Date::get_timeago($data->dns_monitor->last_check_datetime) : l('global.na') ?></div>
                                <div class="ml-2">
                                    <span data-toggle="tooltip" title="<?= sprintf(l('dns_monitor.dns_check_interval_seconds_tooltip'), $data->dns_monitor->settings->dns_check_interval_seconds) ?>">
                                        <i class="fas fa-sm fa-fw fa-info-circle text-muted"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6 col-xl-4 p-3">
                <div class="card h-100">
                    <div class="card-body d-flex p-3">
                        <div>
                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-bolt fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted font-size-small"><?= l('dns_monitor.total_changes') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0"><?= nr($data->dns_monitor->total_changes) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6 col-xl-4 p-3">
                <div class="card h-100">
                    <div class="card-body d-flex p-3">
                        <div>
                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-exchange-alt fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted font-size-small"><?= l('dns_monitor.last_change_datetime') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0"><?= $data->dns_monitor->last_change_datetime ? \Altum\Date::get_timeago($data->dns_monitor->last_change_datetime) : l('global.na') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if(!$data->dns_monitor->total_dns_records_found): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center justify-content-center py-4">
                        <img src="<?= ASSETS_FULL_URL . 'images/processing.svg' ?>" class="col-10 col-md-7 col-lg-5 mb-3" alt="<?= l('dns_monitor.no_dns_data') ?>" />
                        <h2 class="h4 text-muted"><?= l('dns_monitor.no_dns_data') ?></h2>
                        <p class="text-muted"><?= l('dns_monitor.no_dns_data_help') ?></p>
                    </div>
                </div>
            </div>
        <?php else: ?>

            <?php if(isset($data->dns_monitor->dns->A) && count($data->dns_monitor->dns->A)): ?>
                <div class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <h2 class="h6 m-0"><?= sprintf(l('dns_monitor.x_records'), '<span class="badge badge-light">' . strtoupper('a') . '</span>') ?></h2>

                        <div class="ml-2">
                            <a href="https://www.cloudflare.com/learning/dns/dns-records/dns-a-record/" target="_blank">
                                <i class="fas fa-fw fa-info-circle text-muted"></i>
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('dns_monitor.host') ?></th>
                                <th><?= l('global.ip') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($data->dns_monitor->dns->A as $record): ?>

                                <tr>
                                    <td class="text-nowrap" style="width: 25%;">
                                        <span class="text-muted"><?= $record->host ?></span>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->ip ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif ?>

            <?php if(isset($data->dns_monitor->dns->AAAA) && count($data->dns_monitor->dns->AAAA)): ?>
                <div class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <h2 class="h6 m-0"><?= sprintf(l('dns_monitor.x_records'), '<span class="badge badge-light">' . strtoupper('aaaa') . '</span>') ?></h2>

                        <div class="ml-2">
                            <a href="https://www.cloudflare.com/learning/dns/dns-records/dns-aaaa-record/" target="_blank">
                                <i class="fas fa-fw fa-info-circle text-muted"></i>
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('dns_monitor.host') ?></th>
                                <th><?= l('dns_monitor.target') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($data->dns_monitor->dns->AAAA as $record): ?>

                                <tr>
                                    <td class="text-nowrap" style="width: 25%;">
                                        <span class="text-muted"><?= $record->host ?></span>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->ipv6 ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif ?>

            <?php if(isset($data->dns_monitor->dns->CNAME) && count($data->dns_monitor->dns->CNAME)): ?>
                <div class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <h2 class="h6 m-0"><?= sprintf(l('dns_monitor.x_records'), '<span class="badge badge-light">' . strtoupper('cname') . '</span>') ?></h2>

                        <div class="ml-2">
                            <a href="https://www.cloudflare.com/learning/dns/dns-records/dns-cname-record/" target="_blank">
                                <i class="fas fa-fw fa-info-circle text-muted"></i>
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('dns_monitor.host') ?></th>
                                <th><?= l('dns_monitor.target') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($data->dns_monitor->dns->CNAME as $record): ?>

                                <tr>
                                    <td class="text-nowrap" style="width: 25%;">
                                        <span class="text-muted"><?= $record->host ?></span>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->target ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif ?>

            <?php if(isset($data->dns_monitor->dns->MX) && count($data->dns_monitor->dns->MX)): ?>
                <div class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <h2 class="h6 m-0"><?= sprintf(l('dns_monitor.x_records'), '<span class="badge badge-light">' . strtoupper('mx') . '</span>') ?></h2>

                        <div class="ml-2">
                            <a href="https://www.cloudflare.com/learning/dns/dns-records/dns-mx-record/" target="_blank">
                                <i class="fas fa-fw fa-info-circle text-muted"></i>
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('dns_monitor.host') ?></th>
                                <th><?= l('dns_monitor.priority') ?></th>
                                <th><?= l('dns_monitor.target') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($data->dns_monitor->dns->MX as $record): ?>

                                <tr>
                                    <td class="text-nowrap" style="width: 25%;">
                                        <span class="text-muted"><?= $record->host ?></span>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->pri ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->target ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif ?>

            <?php if(isset($data->dns_monitor->dns->NS) && count($data->dns_monitor->dns->NS)): ?>
                <div class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <h2 class="h6 m-0"><?= sprintf(l('dns_monitor.x_records'), '<span class="badge badge-light">' . strtoupper('ns') . '</span>') ?></h2>

                        <div class="ml-2">
                            <a href="https://www.cloudflare.com/learning/dns/dns-records/dns-ns-record/" target="_blank">
                                <i class="fas fa-fw fa-info-circle text-muted"></i>
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('dns_monitor.host') ?></th>
                                <th><?= l('dns_monitor.ns') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($data->dns_monitor->dns->NS as $record): ?>

                                <tr>
                                    <td class="text-nowrap" style="width: 25%;">
                                        <span class="text-muted"><?= $record->host ?></span>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->target ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif ?>

            <?php if(isset($data->dns_monitor->dns->TXT) && count($data->dns_monitor->dns->TXT)): ?>
                <div class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <h2 class="h6 m-0"><?= sprintf(l('dns_monitor.x_records'), '<span class="badge badge-light">' . strtoupper('txt') . '</span>') ?></h2>

                        <div class="ml-2">
                            <a href="https://www.cloudflare.com/learning/dns/dns-records/dns-txt-record/" target="_blank">
                                <i class="fas fa-fw fa-info-circle text-muted"></i>
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('dns_monitor.host') ?></th>
                                <th><?= l('dns_monitor.entries') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($data->dns_monitor->dns->TXT as $record): ?>

                                <tr>
                                    <td class="text-nowrap" style="width: 25%;">
                                        <span class="text-muted"><?= $record->host ?></span>
                                    </td>


                                    <td class="text-nowrap">
                                        <?= $record->txt ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif ?>

            <?php if(isset($data->dns_monitor->dns->SOA) && count($data->dns_monitor->dns->SOA)): ?>
                <div class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <h2 class="h6 m-0"><?= sprintf(l('dns_monitor.x_records'), '<span class="badge badge-light">' . strtoupper('soa') . '</span>') ?></h2>

                        <div class="ml-2">
                            <a href="https://www.cloudflare.com/learning/dns/dns-records/dns-soa-record/" target="_blank">
                                <i class="fas fa-fw fa-info-circle text-muted"></i>
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('dns_monitor.host') ?></th>
                                <th><?= l('dns_monitor.mname') ?></th>
                                <th><?= l('dns_monitor.rname') ?></th>
                                <th><?= l('dns_monitor.serial') ?></th>
                                <th><?= l('dns_monitor.refresh') ?></th>
                                <th><?= l('dns_monitor.retry') ?></th>
                                <th><?= l('dns_monitor.expire') ?></th>
                                <th><?= l('dns_monitor.minimum_ttl') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($data->dns_monitor->dns->SOA as $record): ?>

                                <tr>
                                    <td class="text-nowrap" style="width: 25%;">
                                        <span class="text-muted"><?= $record->host ?></span>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->mname ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->rname ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->serial ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->refresh ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->retry ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->expire ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->{'minimum-ttl'} ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif ?>

            <?php if(isset($data->dns_monitor->dns->CAA) && count($data->dns_monitor->dns->CAA)): ?>
                <div class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <h2 class="h6 m-0"><?= sprintf(l('dns_monitor.x_records'), '<span class="badge badge-light">' . strtoupper('caa') . '</span>') ?></h2>

                        <div class="ml-2">
                            <a href="https://www.cloudflare.com/learning/dns/dns-records/dns-caa-record/" target="_blank">
                                <i class="fas fa-fw fa-info-circle text-muted"></i>
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('dns_monitor.host') ?></th>
                                <th><?= l('dns_monitor.flags') ?></th>
                                <th><?= l('dns_monitor.tag') ?></th>
                                <th><?= l('dns_monitor.value') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($data->dns_monitor->dns->CAA as $record): ?>

                                <tr>
                                    <td class="text-nowrap" style="width: 25%;">
                                        <span class="text-muted"><?= $record->host ?></span>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->flags ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->tag ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= $record->value ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif ?>

        <?php endif ?>

        <div class="mt-5">
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th colspan="5">
                            <?= l('dns_monitor.logs.last_checks') ?>
                            <span class="ml-3 small">
                                <a href="<?= url('dns-monitor-logs/' . $data->dns_monitor->dns_monitor_id) ?>"><?= l('global.view_all') ?></a>
                            </span>
                        </th>
                    </tr>
                    <tr>
                        <th><?= l('global.type') ?></th>
                        <th></th>
                        <th><?= l('dns_monitor.old') ?></th>
                        <th><?= l('dns_monitor.new') ?></th>
                        <th><?= l('dns_monitor.logs.datetime') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(!$data->total_dns_monitor_logs): ?>
                        <tr>
                            <td colspan="5" class="text-muted"><?= l('dns_monitor.logs.no_data') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($data->dns_monitor_logs as $dns_monitor_log): ?>
                            <?php foreach($dns_monitor_log->dns_changes as $dns_change): ?>
                                <?php
                                unset($dns_change->old->host);
                                unset($dns_change->new->host);
                                ?>
                                <tr>
                                    <td class="text-nowrap">
                                        <?php if($dns_change->type == 'changed'): ?>
                                            <i class="fas fa-fw fa-sm fa-exchange-alt text-info mr-1"></i>
                                        <?php elseif($dns_change->type == 'added'): ?>
                                            <i class="fas fa-fw fa-sm fa-plus-circle text-success mr-1"></i>
                                        <?php else: ?>
                                            <i class="fas fa-fw fa-sm fa-minus-circle text-danger mr-1"></i>
                                        <?php endif ?>

                                        <?= l('dns_monitor.' . $dns_change->type) ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <span class="badge badge-success"><?= strtoupper($dns_change->dns_type) ?> </span>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php if($dns_change->type == 'changed'): ?>
                                            <?= implode(' ', array_values((array) $dns_change->old)) ?>
                                        <?php elseif($dns_change->type == 'added'): ?>
                                            -
                                        <?php else: ?>
                                            <?= implode(' ', array_values((array) $dns_change->old)) ?>
                                        <?php endif ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php if($dns_change->type == 'changed'): ?>
                                            <?= implode(' ', array_values((array) $dns_change->new)) ?>
                                        <?php elseif($dns_change->type == 'added'): ?>
                                            <?= implode(' ', array_values((array) $dns_change->new)) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif ?>
                                    </td>

                                    <td class="text-nowrap">
                                    <span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($dns_monitor_log->datetime, 1) ?>">
                                        <?= \Altum\Date::get_timeago($dns_monitor_log->datetime) ?>
                                    </span>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php endforeach ?>
                    <?php endif ?>

                    </tbody>
                </table>
            </div>
        </div>

    <?php endif ?>
</div>
