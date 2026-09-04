<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="mb-3 d-flex justify-content-between">
        <div>
            <h1 class="h4 mb-0 text-truncate"><i class="fas fa-fw fa-xs fa-table-cells mr-1"></i> <?= l('dashboard.header') ?></h1>
        </div>
    </div>

    <?php $dashboard_features = ((array) $this->user->preferences->dashboard) + array_fill_keys(require APP_PATH . 'includes/available_dashboard_features.php', true) ?>

    <?php foreach($dashboard_features as $feature => $is_enabled): ?>

        <?php if($is_enabled && $feature == 'monitors' && settings()->monitors_heartbeats->monitors_is_enabled): ?>
            <div class="mt-4 mb-5">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-server mr-1"></i> <?= l('dashboard.monitors.header') ?></h2>

                    <div class="flex-fill">
                        <hr class="border-gray-50" />
                    </div>

                    <div class="ml-3">
                        <a href="<?= url('monitor-create') ?>" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('monitors.create') ?>"><i class="fas fa-fw fa-plus-circle fa-sm"></i></a>
                        <a href="<?= url('monitors') ?>" class="btn btn-sm btn-blue-100" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-server fa-sm"></i></a>
                    </div>
                </div>

                <?php if (!empty($data->monitors)): ?>

                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('monitors.table.monitor') ?></th>
                                <th><?= l('monitor.uptime') ?></th>
                                <th colspan="2"><?= l('monitor.average_response_time') ?></th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach($data->monitors as $row): ?>
                                <?php
                                $row->last_logs = json_decode($row->last_logs ?? '');
                                if(is_null($row->last_logs)) $row->last_logs = [[], [], [], [], [], [], []];
                                ?>

                                <?php
                                /* Determine the border color based on the status */
                                $uptime_class_name = match (true) {
                                    $row->uptime >= 90 => 'success',
                                    $row->uptime >= 50 => 'warning',
                                    $row->uptime >= 0 => 'danger',
                                };
                                ?>

                                <tr>
                                    <td class="text-nowrap">
                                        <div class="d-flex flex-column">
                                            <div><a href="<?= url('monitor/' . $row->monitor_id) ?>"><?= $row->name ?></a></div>

                                            <small class="text-muted">
                                                <?php if($row->is_enabled): ?>
                                                    <?php if(!$row->total_checks): ?>
                                                        <span class="mr-1" data-toggle="tooltip" title="<?= l('monitor.pending_check') ?>">
                                                    <i class="fas fa-fw fa-sm fa-clock text-muted"></i>
                                                </span>
                                                    <?php elseif($row->is_ok): ?>
                                                        <a href="<?= url('incident/' . $row->incident_id) ?>" class="text-decoration-none mr-1" data-toggle="tooltip" title="<?= l('monitor.is_ok') ?>">
                                                            <i class="fas fa-fw fa-sm fa-check-circle text-success"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="mr-1" data-toggle="tooltip" title="<?= l('monitor.is_not_ok') ?>">
                                                    <i class="fas fa-fw fa-sm fa-times-circle text-danger"></i>
                                                </span>
                                                    <?php endif ?>
                                                <?php else: ?>
                                                    <span class="mr-1" data-toggle="tooltip" title="<?= l('monitor.is_enabled_paused') ?>">
                                                <i class="fas fa-fw fa-sm fa-pause-circle text-warning"></i>
                                            </span>
                                                <?php endif ?>

                                                <span><?= $row->type == 'website' ? remove_url_protocol_from_url($row->target) : $row->target ?><?= $row->port ? ':' . $row->port : null ?></span>

                                                <?php if($row->type == 'website'): ?>
                                                    <a href="<?= $row->target ?>" target="_blank" rel="noreferrer">
                                                        <i class="fas fa-fw fa-xs fa-external-link-alt text-muted ml-1"></i>
                                                    </a>
                                                <?php endif ?>
                                            </small>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                <span class="badge badge-<?= $uptime_class_name ?>" data-toggle="tooltip" title="<?= sprintf(l('monitor.total_checks_tooltip'), nr($row->total_checks)) ?>">
                                    <?= nr($row->uptime, settings()->monitors_heartbeats->decimals) . '%' ?>
                                </span>
                                    </td>

                                    <td class="text-nowrap">
                                    <span class="badge badge-light" data-toggle="tooltip" title="<?= sprintf(l('monitor.total_ok_checks_tooltip'), nr($row->total_ok_checks)) ?>">
                                        <?= display_response_time($row->average_response_time) ?>
                                    </span>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex">
                                            <?php foreach($row->last_logs as $log): ?>
                                                <?php if(isset($log->is_ok)): ?>

                                                    <?php ob_start() ?>
                                                    <div class='d-flex flex-column text-left'>
                                                        <div class='d-flex flex-column my-1'>
                                                            <div><?= \Altum\Date::get($log->datetime, 1) ?></div>
                                                            <strong><?= \Altum\Date::get_timeago($log->datetime) ?></strong>
                                                        </div>

                                                        <div class='d-flex flex-column my-1'>
                                                            <div><?= l('monitor.is_ok_label') ?></div>
                                                            <strong><?= ($log->is_ok ? l('global.yes') : l('global.no')) ?></strong>
                                                        </div>

                                                        <div class='d-flex flex-column my-1'>
                                                            <div><?= l('monitor.response_time_label') ?></div>
                                                            <strong><?= display_response_time($log->response_time) ?></strong>
                                                        </div>

                                                        <div class='d-flex flex-column my-1'>
                                                            <div><?= l('monitor.checks.response_status_code') ?></div>
                                                            <strong><?= nr($log->response_status_code) ?></strong>
                                                        </div>

                                                        <div class='d-flex flex-column my-1'>
                                                            <div><?= l('monitor.ping_servers_checks.ping_server') ?></div>
                                                            <strong><?= isset($data->ping_servers[$log->ping_server_id]) ? get_countries_array()[$data->ping_servers[$log->ping_server_id]->country_code] . ', ' . $data->ping_servers[$log->ping_server_id]->city_name : null ?></strong>
                                                        </div>

                                                        <?php
                                                        $error = l('global.unknown');
                                                        if(isset($log->error->type)) {
                                                            if($log->error->type == 'exception') {
                                                                $error = $log->error->message;
                                                            } elseif(in_array($log->error->type, ['response_status_code', 'response_body', 'response_header', 'ping_failed', 'parse_error', 'socket_connect_failed', 'socket_no_response', 'socket_create_failed'])) {
                                                                $error = l('monitor.checks.error.' . $log->error->type);
                                                            } elseif(in_array($log->error->type, ['connection_failed'])) {
                                                                $error = sprintf(l('monitor.checks.error.connection_failed'), $log->error->message);
                                                            }
                                                        }
                                                        ?>

                                                        <?php if(!$log->is_ok): ?>
                                                        <div class='d-flex flex-column my-1'>
                                                            <div><?= l('monitor.checks.error') ?></div>
                                                            <strong><?= $error ?></strong>
                                                        </div>
                                                        <?php endif ?>
                                                    </div>

                                                    <?php $tooltip = ob_get_clean() ?>

                                                    <?php if($log->incident_id): ?>
                                                        <a
                                                                href="<?= url('incident/' . $log->incident_id) ?>"
                                                                class="status-badge <?= $log->is_ok ? 'bg-success' : 'bg-danger' ?> mr-1"
                                                                data-toggle="tooltip"
                                                                data-html="true"
                                                                data-custom-class=""
                                                                title="<?= $tooltip ?>"
                                                        ></a>
                                                    <?php else: ?>
                                                        <div
                                                                class="status-badge <?= $log->is_ok ? 'bg-success' : 'bg-danger' ?> mr-1"
                                                                data-toggle="tooltip"
                                                                data-html="true"
                                                                data-custom-class=""
                                                                title="<?= $tooltip ?>"
                                                        ></div>
                                                    <?php endif ?>

                                                <?php else: ?>
                                                    <div class="status-badge bg-gray-200 mr-1"></div>
                                                <?php endif ?>
                                            <?php endforeach ?>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center">
                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('monitors.filters.order_by_last_check_datetime') . '<br />' . \Altum\Date::get($row->last_check_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_check_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_check_datetime) . ')</small>' ?>">
                                            <i class="fas fa-fw fa-calendar-check text-muted"></i>
                                        </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                            <i class="fas fa-fw fa-calendar text-muted"></i>
                                        </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                        <i class="fas fa-fw fa-history text-muted"></i>
                                    </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <?= include_view(THEME_PATH . 'views/monitor/monitor_dropdown_button.php', ['id' => $row->monitor_id, 'resource_name' => $row->name]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>

                            </tbody>
                        </table>
                    </div>

                <?php else: ?>

                    <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                        'filters_get' => $data->filters->get ?? [],
                        'name' => 'monitors',
                        'has_secondary_text' => true,
                    ]); ?>

                <?php endif ?>
            </div>
        <?php endif ?>

        <?php if($is_enabled && $feature == 'heartbeats' && settings()->monitors_heartbeats->heartbeats_is_enabled): ?>
            <div class="mt-4 mb-5">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-heartbeat mr-1"></i> <?= l('dashboard.heartbeats.header') ?></h2>

                    <div class="flex-fill">
                        <hr class="border-gray-50" />
                    </div>

                    <div class="ml-3">
                        <a href="<?= url('heartbeat-create') ?>" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('heartbeats.create') ?>"><i class="fas fa-fw fa-plus-circle fa-sm"></i></a>
                        <a href="<?= url('heartbeats') ?>" class="btn btn-sm btn-blue-100" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-heartbeat fa-sm"></i></a>
                    </div>
                </div>

                <?php if (!empty($data->heartbeats)): ?>

                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('heartbeats.table.heartbeat') ?></th>
                                <th><?= l('heartbeat.uptime') ?></th>
                                <th><?= l('heartbeat.downtime') ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach($data->heartbeats as $row): ?>
                                <?php
                                $row->last_logs = json_decode($row->last_logs ?? '');
                                if(is_null($row->last_logs)) $row->last_logs = [[], [], [], [], [], [], []];
                                ?>

                                <?php
                                /* Determine the border color based on the status */
                                $uptime_class_name = match (true) {
                                    $row->uptime >= 90 => 'success',
                                    $row->uptime >= 50 => 'warning',
                                    $row->uptime >= 0 => 'danger',
                                };
                                ?>

                                <tr>
                                    <td class="text-nowrap">
                                        <div class="d-flex flex-column">
                                            <div><a href="<?= url('heartbeat/' . $row->heartbeat_id) ?>"><?= $row->name ?></a></div>

                                            <small class="text-muted">
                                                <?php if($row->is_enabled): ?>
                                                    <?php if(!$row->total_runs): ?>
                                                        <span class="mr-1" data-toggle="tooltip" title="<?= l('heartbeat.pending_run') ?>">
                                                    <i class="fas fa-fw fa-sm fa-clock text-muted"></i>
                                                </span>
                                                    <?php elseif($row->is_ok): ?>
                                                        <span class="mr-1" data-toggle="tooltip" title="<?= l('heartbeat.is_ok') ?>">
                                                            <i class="fas fa-fw fa-sm fa-check-circle text-success"></i>
                                                        </span>
                                                    <?php else: ?>
                                                        <a href="<?= url('incident/' . $row->incident_id) ?>" class="text-decoration-none mr-1" data-toggle="tooltip" title="<?= l('heartbeat.is_not_ok') ?>">
                                                            <i class="fas fa-fw fa-sm fa-times-circle text-danger"></i>
                                                        </a>
                                                    <?php endif ?>
                                                <?php else: ?>
                                                    <span class="mr-1" data-toggle="tooltip" title="<?= l('heartbeat.is_enabled_paused') ?>">
                                                        <i class="fas fa-fw fa-sm fa-pause-circle text-warning"></i>
                                                    </span>
                                                <?php endif ?>

                                                <span data-toggle="tooltip" title="<?= $row->last_run_datetime ? \Altum\Date::get($row->last_run_datetime, 1) : '' ?>"><?= sprintf(l('heartbeats.last_run_datetime'), $row->last_run_datetime ? \Altum\Date::get_timeago($row->last_run_datetime) : l('global.na')) ?></span>
                                            </small>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                <span class="badge badge-<?= $uptime_class_name ?>" data-toggle="tooltip" title="<?= sprintf(l('heartbeat.total_runs_tooltip'), nr($row->total_runs)) ?>">
                                    <?= nr($row->uptime, settings()->monitors_heartbeats->decimals) . '%' ?>
                                </span>
                                    </td>

                                    <td class="text-nowrap">
                                <span class="badge badge-light" data-toggle="tooltip" title="<?= sprintf(l('heartbeat.total_missed_runs_tooltip'), nr($row->total_missed_runs)) ?>">
                                    <?= nr($row->downtime, settings()->monitors_heartbeats->decimals) . '%' ?>
                                </span>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex">
                                            <?php foreach($row->last_logs as $log): ?>
                                                <?php if(isset($log->is_ok)): ?>

                                                    <?php ob_start() ?>
                                                    <div class='d-flex flex-column text-left'>
                                                        <div class='d-flex flex-column my-1'>
                                                            <div><?= \Altum\Date::get($log->datetime, 1) ?></div>
                                                            <strong><?= \Altum\Date::get_timeago($log->datetime) ?></strong>
                                                        </div>

                                                        <div class='d-flex flex-column my-1'>
                                                            <div><?= l('heartbeat.is_ok') ?></div>
                                                            <strong><?= ($log->is_ok ? l('global.yes') : l('global.no')) ?></strong>
                                                        </div>

                                                        <?php if(!$log->is_ok): ?>
                                                        <div class='d-flex flex-column my-1'>
                                                            <div><?= l('heartbeat.checks.error') ?></div>
                                                            <strong><?= l('heartbeat.checks.error.missed') ?></strong>
                                                        </div>
                                                        <?php endif ?>
                                                    </div>

                                                    <?php $tooltip = ob_get_clean() ?>

                                                    <?php if($log->incident_id): ?>
                                                        <a
                                                                href="<?= url('incident/' . $log->incident_id) ?>"
                                                                class="status-badge <?= $log->is_ok ? 'bg-success' : 'bg-danger' ?> mr-1"
                                                                data-toggle="tooltip"
                                                                data-html="true"
                                                                data-custom-class=""
                                                                title="<?= $tooltip ?>"
                                                        ></a>
                                                    <?php else: ?>
                                                        <div
                                                                class="status-badge <?= $log->is_ok ? 'bg-success' : 'bg-danger' ?> mr-1"
                                                                data-toggle="tooltip"
                                                                data-html="true"
                                                                data-custom-class=""
                                                                title="<?= $tooltip ?>"
                                                        ></div>
                                                    <?php endif ?>

                                                <?php else: ?>
                                                    <div class="status-badge bg-gray-200 mr-1"></div>
                                                <?php endif ?>
                                            <?php endforeach ?>
                                        </div>
                                    </td>

                                    <td class="text-truncate text-muted">
                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('heartbeats.filters.order_by_last_run_datetime') . '<br />' . \Altum\Date::get($row->last_run_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_run_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_run_datetime) . ')</small>' ?>">
                                        <i class="fas fa-fw fa-calendar-check text-muted"></i>
                                    </span>

                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                        <i class="fas fa-fw fa-calendar text-muted"></i>
                                    </span>

                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                        <i class="fas fa-fw fa-history text-muted"></i>
                                    </span>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <?= include_view(THEME_PATH . 'views/heartbeat/heartbeat_dropdown_button.php', ['id' => $row->heartbeat_id, 'resource_name' => $row->name]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>

                            </tbody>
                        </table>
                    </div>

                <?php else: ?>

                    <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                        'filters_get' => $data->filters->get ?? [],
                        'name' => 'heartbeats',
                        'has_secondary_text' => true,
                    ]); ?>

                <?php endif ?>
            </div>
        <?php endif ?>

        <?php if($is_enabled && $feature == 'domain_names' && settings()->monitors_heartbeats->domain_names_is_enabled): ?>
            <div class="mt-4 mb-5">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-network-wired mr-1"></i> <?= l('dashboard.domain_names.header') ?></h2>

                    <div class="flex-fill">
                        <hr class="border-gray-50" />
                    </div>

                    <div class="ml-3">
                        <a href="<?= url('domain-name-create') ?>" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('domain_names.create') ?>"><i class="fas fa-fw fa-plus-circle fa-sm"></i></a>
                        <a href="<?= url('domain-names') ?>" class="btn btn-sm btn-blue-100" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-globe fa-sm"></i></a>
                    </div>
                </div>

                <?php if (!empty($data->domain_names)): ?>

                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('domain_names.table.domain_name') ?></th>
                                <th><?= l('domain_name.whois') ?></th>
                                <th><?= l('domain_name.ssl') ?></th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach($data->domain_names as $row): ?>

                                <tr>
                                    <td class="text-nowrap">
                                        <div class="d-flex flex-column">
                                            <div><a href="<?= url('domain-name/' . $row->domain_name_id) ?>"><?= $row->name ?></a></div>

                                            <small class="text-muted">
                                                <?php if($row->is_enabled): ?>
                                                    <?php if(!$row->total_checks): ?>
                                                        <span class="mr-1" data-toggle="tooltip" title="<?= l('domain_name.pending_check') ?>">
                                                    <i class="fas fa-fw fa-sm fa-clock text-muted"></i>
                                                </span>
                                                    <?php else: ?>
                                                        <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($row->target) ?>" class="img-fluid icon-favicon-small mr-1" loading="lazy" />
                                                    <?php endif ?>
                                                <?php else: ?>
                                                    <span class="mr-1" data-toggle="tooltip" title="<?= l('domain_name.is_enabled_paused') ?>">
                                                <i class="fas fa-fw fa-sm fa-pause-circle text-warning"></i>
                                            </span>
                                                <?php endif ?>

                                                <?= $row->target ?>

                                                <a href="<?= 'https://' . $row->target ?>" class="text-muted" target="_blank" rel="noreferrer">
                                                    <i class="fas fa-fw fa-xs fa-external-link-alt text-muted ml-1"></i>
                                                </a>
                                            </small>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php if($row->last_check_datetime): ?>
                                            <?php if(!property_exists($row->whois, 'end_datetime') && !property_exists($row->whois, 'start_datetime') && !property_exists($row->whois, 'updated_datetime')): ?>
                                                <div>
                                            <span class="badge badge-light w-100">
                                                <i class="fas fa-fw fa-sm fa-info-circle mr-1"></i>

                                                <?= l('domain_name.not_registered') ?>
                                            </span>
                                                </div>
                                            <?php else: ?>
                                                <?php ob_start() ?>
                                                <div class='d-flex flex-column text-left'>
                                                    <div class='d-flex flex-column my-1'>
                                                        <div><?= l('domain_name.whois_start_datetime') ?></div>
                                                        <strong><?= is_null($row->whois->start_datetime) ? l('domain_name.no_data_simple') : \Altum\Date::get($row->whois->start_datetime, 2) ?></strong>
                                                    </div>

                                                    <div class='d-flex flex-column my-1'>
                                                        <div><?= l('domain_name.whois_updated_datetime') ?></div>
                                                        <strong><?= is_null($row->whois->updated_datetime) ? l('domain_name.no_data_simple') : \Altum\Date::get($row->whois->updated_datetime, 2) ?></strong>
                                                    </div>

                                                    <div class='d-flex flex-column my-1'>
                                                        <div><?= l('domain_name.whois_end_datetime') ?></div>
                                                        <strong><?= is_null($row->whois->end_datetime) ? l('domain_name.no_data_simple') : \Altum\Date::get($row->whois->end_datetime, 2) ?></strong>
                                                    </div>
                                                </div>
                                                <?php $tooltip = ob_get_clean(); ?>

                                                <?php if(is_null($row->whois->end_datetime)): ?>

                                                    <span class="badge badge-primary w-100" data-toggle="tooltip" title="<?= $tooltip ?>" data-html="true">
                                                <i class="fas fa-fw fa-sm fa-rotate mr-1"></i>
                                                <?= l('domain_name.whois_updated_datetime') . ' ' . \Altum\Date::get($row->whois->updated_datetime, 2) ?>
                                            </span>

                                                <?php else: ?>

                                                    <?php $is_valid = (new \DateTime($row->whois->end_datetime)) > (new \DateTime()) ?>

                                                    <span class="<?= $is_valid ? 'badge badge-primary' : 'badge badge-danger' ?> w-100" data-toggle="tooltip" title="<?= $tooltip ?>" data-html="true">
                                                <i class="fas fa-fw fa-sm <?= $is_valid ? 'fa-check' : 'fa-calendar-times' ?> mr-1"></i>
                                                <?= $is_valid ? sprintf(l('domain_name.x_time_left'), \Altum\Date::get_time_until($row->whois->end_datetime)) : l('domain_name.expired_on') . ' ' . \Altum\Date::get($row->whois->end_datetime, 2) ?>
                                            </span>
                                                <?php endif ?>
                                            <?php endif ?>
                                        <?php else: ?>
                                            <div>
                                        <span class="badge badge-light w-100">
                                            <i class="fas fa-fw fa-sm fa-clock text-muted mr-1"></i>

                                            <?= l('domain_name.pending_check') ?>
                                        </span>
                                            </div>
                                        <?php endif ?>
                                    </td>

                                    <td class="text-truncate">
                                        <?php if($row->last_check_datetime && $row->ssl && property_exists($row->ssl, 'end_datetime')): ?>
                                            <div>
                                                <?php ob_start() ?>
                                                <div class='d-flex flex-column text-left'>
                                                    <div class='d-flex flex-column my-1'>
                                                        <div><?= l('domain_name.ssl_start_datetime') ?></div>
                                                        <strong><?= \Altum\Date::get($row->ssl->start_datetime, 2) ?></strong>
                                                    </div>

                                                    <div class='d-flex flex-column my-1'>
                                                        <div><?= l('domain_name.ssl_end_datetime') ?></div>
                                                        <strong><?= \Altum\Date::get($row->ssl->end_datetime, 2) ?></strong>
                                                    </div>

                                                    <div class='d-flex flex-column my-1'>
                                                        <div><?= l('domain_name.ssl_organization') ?></div>
                                                        <strong><?= $row->ssl->organization ?></strong>
                                                    </div>

                                                    <div class='d-flex flex-column my-1'>
                                                        <div><?= l('domain_name.ssl_common_name') ?></div>
                                                        <strong><?= $row->ssl->common_name ?></strong>
                                                    </div>

                                                    <div class='d-flex flex-column my-1'>
                                                        <div><?= l('domain_name.ssl_signature_type') ?></div>
                                                        <strong><?= $row->ssl->signature_type ?></strong>
                                                    </div>
                                                </div>
                                                <?php $tooltip = ob_get_clean(); ?>

                                                <?php $is_valid = (new \DateTime($row->ssl->end_datetime)) > (new \DateTime()) ?>

                                                <span class="<?= $is_valid ? 'badge badge-success' : 'badge badge-danger' ?> w-100" data-toggle="tooltip" title="<?= $tooltip ?>" data-html="true">
                                            <i class="fas fa-fw fa-sm fa-lock mr-1"></i>
                                            <?= $is_valid ? sprintf(l('domain_name.x_time_left'), \Altum\Date::get_time_until($row->ssl->end_datetime)) : l('domain_name.expired_on') . ' ' . \Altum\Date::get($row->ssl->end_datetime, 2) ?>
                                        </span>
                                            </div>
                                        <?php else: ?>
                                            <div></div>
                                        <?php endif ?>
                                    </td>

                                    <td class="text-truncate text-muted">
                                        <?php $registrar = $row->last_check_datetime && $row->whois && property_exists($row->whois, 'registrar') && !empty($row->whois->registrar) ? $row->whois->registrar : l('global.na'); ?>
                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('domain_name.registrar') . '<br />' . $registrar ?>">
                                    <i class="fas fa-fw fa-atlas text-muted"></i>
                                </span>

                                        <?php $nameservers = $row->last_check_datetime && $row->whois && property_exists($row->whois, 'registrar') && !empty($row->whois->nameservers) ? implode(', ', (array) $row->whois->nameservers) : l('global.na'); ?>
                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('domain_name.nameservers') . '<br />' . $nameservers ?>">
                                    <i class="fas fa-fw fa-ethernet text-muted"></i>
                                </span>

                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('domain_name.last_check_datetime') . '<br />' . \Altum\Date::get($row->last_check_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_check_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_check_datetime) . ')</small>' ?>">
                                    <i class="fas fa-fw fa-calendar-check text-muted"></i>
                                </span>

                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                    <i class="fas fa-fw fa-calendar text-muted"></i>
                                </span>

                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                    <i class="fas fa-fw fa-history text-muted"></i>
                                </span>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <?= include_view(THEME_PATH . 'views/domain-name/domain_name_dropdown_button.php', ['id' => $row->domain_name_id, 'resource_name' => $row->name]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>

                            </tbody>
                        </table>
                    </div>

                <?php else: ?>

                    <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                        'filters_get' => $data->filters->get ?? [],
                        'name' => 'domain_names',
                        'has_secondary_text' => true,
                    ]); ?>

                <?php endif ?>
            </div>
        <?php endif ?>

        <?php if($is_enabled && $feature == 'status_pages' && settings()->monitors_heartbeats->monitors_is_enabled && settings()->status_pages->status_pages_is_enabled): ?>
            <?php if (!empty($data->status_pages)): ?>
                <div class="mt-4 mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-wifi mr-1"></i> <?= l('dashboard.status_pages.header') ?></h2>

                        <div class="flex-fill">
                            <hr class="border-gray-50" />
                        </div>

                        <div class="ml-3">
                            <a href="<?= url('status-page-create') ?>" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('status_pages.create') ?>"><i class="fas fa-fw fa-plus-circle fa-sm"></i></a>
                            <a href="<?= url('status-pages') ?>" class="btn btn-sm btn-blue-100" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-wifi fa-sm"></i></a>
                        </div>
                    </div>

                    <?php if (!empty($data->status_pages)): ?>

                        <div class="table-responsive table-custom-container">
                            <table class="table table-custom">
                                <thead>
                                <tr>
                                    <th><?= l('status_pages.table.status_page') ?></th>
                                    <th></th>
                                    <th><?= l('status_pages.table.pageviews') ?></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php foreach($data->status_pages as $row): ?>

                                    <tr>
                                        <td class="text-nowrap">
                                            <div class="d-flex align-items-center">
                                                <a href="<?= url('status-page-update/' . $row->status_page_id) ?>">
                                                    <?php if($row->logo): ?>
                                                        <img src="<?= \Altum\Uploads::get_full_url('status_pages_logos') . $row->logo ?>" class="status-page-table-logo rounded-circle mr-3" loading="lazy" />
                                                    <?php else: ?>
                                                        <div class="status-page-table-logo rounded-circle mr-3"></div>
                                                    <?php endif ?>
                                                </a>

                                                <div class="d-flex flex-column">
                                                    <div><a href="<?= url('status-page-update/' . $row->status_page_id) ?>"><?= $row->name ?></a></div>
                                                    <div class="small text-muted">
                                                        <?= remove_url_protocol_from_url($row->full_url) ?>

                                                        <a href="<?= $row->full_url ?>" target="_blank" rel="noreferrer">
                                                            <i class="fas fa-fw fa-xs fa-external-link-alt text-muted ml-1"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-nowrap">
                                            <?php if($row->project_id): ?>
                                                <a href="<?= url('status-pages?project_id=' . $row->project_id) ?>" class="text-decoration-none" data-toggle="tooltip" title="<?= l('projects.project_id') ?>">
                                    <span class="badge badge-light" style="color: <?= $data->projects[$row->project_id]->color ?> !important;">
                                        <?= $data->projects[$row->project_id]->name ?>
                                    </span>
                                                </a>
                                            <?php endif ?>
                                        </td>

                                        <td class="text-nowrap">
                                            <a href="<?= url('status-page-statistics/' . $row->status_page_id) ?>" class="badge badge-light text-decoration-none" data-toggle="tooltip" title="<?= l('status_page_statistics.pageviews') ?>">
                                                <i class="fas fa-fw fa-sm fa-chart-bar mr-1"></i> <?= nr($row->pageviews) ?>
                                            </a>
                                        </td>

                                        <td class="text-nowrap">
                                            <div class="d-flex align-items-center">
                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                    <i class="fas fa-fw fa-calendar text-muted"></i>
                                </span>

                                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                    <i class="fas fa-fw fa-history text-muted"></i>
                                </span>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="d-flex justify-content-end">
                                                <?= include_view(THEME_PATH . 'views/status-pages/status_page_dropdown_button.php', ['id' => $row->status_page_id, 'resource_name' => $row->name]) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach ?>

                                </tbody>
                            </table>
                        </div>

                    <?php else: ?>

                        <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                            'filters_get' => $data->filters->get ?? [],
                            'name' => 'status_pages',
                            'has_secondary_text' => true,
                        ]); ?>

                    <?php endif ?>
                </div>
            <?php endif ?>
        <?php endif ?>

        <?php if($is_enabled && $feature == 'dns_monitors' && settings()->monitors_heartbeats->dns_monitors_is_enabled): ?>
            <div class="mt-4 mb-5">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-plug mr-1"></i> <?= l('dashboard.dns_monitors.header') ?></h2>

                    <div class="flex-fill">
                        <hr class="border-gray-50" />
                    </div>

                    <div class="ml-3">
                        <a href="<?= url('dns-monitor-create') ?>" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('dns_monitors.create') ?>"><i class="fas fa-fw fa-plus-circle fa-sm"></i></a>
                        <a href="<?= url('dns-monitors') ?>" class="btn btn-sm btn-blue-100" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-server fa-sm"></i></a>
                    </div>
                </div>

                <?php if (!empty($data->dns_monitors)): ?>

                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th data-bulk-table class="d-none">
                                    <div class="custom-control custom-checkbox">
                                        <input id="bulk_select_all" type="checkbox" class="custom-control-input" />
                                        <label class="custom-control-label" for="bulk_select_all"></label>
                                    </div>
                                </th>
                                <th><?= l('dns_monitors.table.dns_monitor') ?></th>
                                <th><?= l('dns_monitor.total_checks') ?></th>
                                <th><?= l('dns_monitor.total_changes') ?></th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach($data->dns_monitors as $row): ?>

                                <tr>
                                    <td data-bulk-table class="d-none">
                                        <div class="custom-control custom-checkbox">
                                            <input id="selected_dns_monitor_id_<?= $row->dns_monitor_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->dns_monitor_id ?>" />
                                            <label class="custom-control-label" for="selected_dns_monitor_id_<?= $row->dns_monitor_id ?>"></label>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex flex-column">
                                            <div><a href="<?= url('dns-monitor/' . $row->dns_monitor_id) ?>"><?= $row->name ?></a></div>

                                            <small class="text-muted">
                                                <?php if($row->is_enabled): ?>
                                                    <?php if(!$row->total_checks): ?>
                                                        <span class="mr-1" data-toggle="tooltip" title="<?= l('dns_monitor.pending_check') ?>">
                                                    <i class="fas fa-fw fa-sm fa-clock text-muted"></i>
                                                </span>
                                                    <?php else: ?>
                                                        <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($row->target) ?>" class="img-fluid icon-favicon-small mr-1" loading="lazy" />
                                                    <?php endif ?>
                                                <?php else: ?>
                                                    <span class="mr-1" data-toggle="tooltip" title="<?= l('dns_monitor.is_enabled_paused') ?>">
                                                <i class="fas fa-fw fa-sm fa-pause-circle text-warning"></i>
                                            </span>
                                                <?php endif ?>

                                                <?= $row->target ?>

                                                <a href="<?= 'https://' . $row->target ?>" target="_blank" rel="noreferrer">
                                                    <i class="fas fa-fw fa-xs fa-external-link-alt text-muted ml-1"></i>
                                                </a>
                                            </small>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                <span class="badge badge-info" data-toggle="tooltip" data-html="true" title="<?= l('dns_monitor.last_check_datetime') . '<br />' . ($row->last_check_datetime ? \Altum\Date::get($row->last_check_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_check_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_check_datetime) . ')</small>' : l('global.na')) ?>">
                                    <i class="fas fa-fw fa-sm fa-globe mr-1"></i> <?= nr($row->total_checks) ?>
                                </span>
                                    </td>

                                    <td class="text-nowrap">
                                <span class="badge badge-light" data-toggle="tooltip" data-html="true" title="<?= l('dns_monitor.last_change_datetime') . '<br />' . ($row->last_change_datetime ? \Altum\Date::get($row->last_change_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_change_datetime, 3) . '</small>'  . '<br /><small>(' . \Altum\Date::get_timeago($row->last_change_datetime) . ')</small>' : l('global.na')) ?>">
                                    <i class="fas fa-fw fa-sm fa-bolt mr-1"></i> <?= nr($row->total_changes) ?>
                                </span>
                                    </td>

                                    <td class="text-truncate text-muted">
                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('dns_monitor.last_check_datetime') . '<br />' . ($row->last_check_datetime ? \Altum\Date::get($row->last_check_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_check_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_check_datetime) . ')</small>' : l('global.na')) ?>">
                                    <i class="fas fa-fw fa-calendar-check text-muted"></i>
                                </span>

                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('dns_monitor.last_change_datetime') . '<br />' . ($row->last_change_datetime ? \Altum\Date::get($row->last_change_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_change_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_change_datetime) . ')</small>' : l('global.na')) ?>">
                                    <i class="fas fa-fw fa-exchange-alt text-muted"></i>
                                </span>

                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                    <i class="fas fa-fw fa-calendar text-muted"></i>
                                </span>

                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                    <i class="fas fa-fw fa-history text-muted"></i>
                                </span>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <?= include_view(THEME_PATH . 'views/dns-monitor/dns_monitor_dropdown_button.php', ['id' => $row->dns_monitor_id, 'resource_name' => $row->name]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>

                            </tbody>
                        </table>
                    </div>

                <?php else: ?>

                    <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                        'filters_get' => $data->filters->get ?? [],
                        'name' => 'dns_monitors',
                        'has_secondary_text' => true,
                    ]); ?>

                <?php endif ?>
            </div>

        <?php endif ?>

        <?php if($is_enabled && $feature == 'server_monitors' && settings()->monitors_heartbeats->server_monitors_is_enabled): ?>
            <div class="mt-4 mb-5">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-microchip mr-1"></i> <?= l('dashboard.server_monitors.header') ?></h2>

                    <div class="flex-fill">
                        <hr class="border-gray-50" />
                    </div>

                    <div class="ml-3">
                        <a href="<?= url('server-monitor-create') ?>" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('server_monitors.create') ?>"><i class="fas fa-fw fa-plus-circle fa-sm"></i></a>
                        <a href="<?= url('server-monitors') ?>" class="btn btn-sm btn-blue-100" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-server fa-sm"></i></a>
                    </div>
                </div>

                <?php if (!empty($data->server_monitors)): ?>

                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th data-bulk-table class="d-none">
                                    <div class="custom-control custom-checkbox">
                                        <input id="bulk_select_all" type="checkbox" class="custom-control-input" />
                                        <label class="custom-control-label" for="bulk_select_all"></label>
                                    </div>
                                </th>
                                <th><?= l('server_monitors.table.server_monitor') ?></th>
                                <th><?= l('server_monitor.cpu_usage') ?></th>
                                <th><?= l('server_monitor.ram_usage') ?></th>
                                <th><?= l('server_monitor.disk_usage') ?></th>
                                <th><?= l('server_monitor.last_log_datetime') ?></th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach($data->server_monitors as $row): ?>

                                <tr>
                                    <td data-bulk-table class="d-none">
                                        <div class="custom-control custom-checkbox">
                                            <input id="selected_server_monitor_id_<?= $row->server_monitor_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->server_monitor_id ?>" />
                                            <label class="custom-control-label" for="selected_server_monitor_id_<?= $row->server_monitor_id ?>"></label>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex flex-column">
                                            <div><a href="<?= url('server-monitor/' . $row->server_monitor_id) ?>"><?= $row->name ?></a></div>

                                            <small class="text-muted">
                                                <?php if($row->is_enabled): ?>
                                                    <?php if(!$row->total_logs): ?>
                                                        <span class="mr-1" data-toggle="tooltip" title="<?= l('server_monitor.pending_log') ?>">
                                                    <i class="fas fa-fw fa-sm fa-clock text-muted"></i>
                                                </span>
                                                    <?php else: ?>
                                                        <span class="mr-1" data-toggle="tooltip" title="<?= l('server_monitor.is_enabled') ?>">
                                                    <i class="fas fa-fw fa-sm fa-check-circle text-success"></i>
                                                </span>
                                                    <?php endif ?>
                                                <?php else: ?>
                                                    <span class="mr-1" data-toggle="tooltip" title="<?= l('server_monitor.is_enabled_paused') ?>">
                                                <i class="fas fa-fw fa-sm fa-pause-circle text-warning"></i>
                                            </span>
                                                <?php endif ?>

                                                <span class="text-muted"><?= $row->target ?></span>
                                            </small>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="small font-weight-bold mb-1">
                                            <?= nr($row->cpu_usage, settings()->monitors_heartbeats->decimals) . '%' ?>
                                        </div>

                                        <div class="progress" style="height: 5px">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $row->cpu_usage ?>%;" aria-valuenow="<?= $row->cpu_usage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="small font-weight-bold mb-1">
                                            <?= nr($row->ram_usage, settings()->monitors_heartbeats->decimals) . '%' ?>
                                        </div>

                                        <div class="progress" style="height: 5px">
                                            <div class="progress-bar" role="progressbar" style="background: var(--blue); width: <?= $row->ram_usage ?>%;" aria-valuenow="<?= $row->ram_usage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="small font-weight-bold mb-1">
                                            <?= nr($row->disk_usage, settings()->monitors_heartbeats->decimals) . '%' ?>
                                        </div>

                                        <div class="progress" style="height: 5px">
                                            <div class="progress-bar" role="progressbar" style="background: var(--purple); width: <?= $row->disk_usage ?>%;" aria-valuenow="<?= $row->disk_usage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>

                                    <td class="text-nowrap text-muted small">
                                    <span data-toggle="tooltip" data-html="true" title="<?= l('server_monitor.last_log_datetime') . '<br />' . \Altum\Date::get($row->last_log_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_log_datetime, 3) . '</small>'  . '<br /><small>(' . \Altum\Date::get_timeago($row->last_log_datetime) . ')</small>' ?>">
                                        <?= $row->last_log_datetime ? \Altum\Date::get_timeago($row->last_log_datetime) : l('global.na') ?>
                                    </span>
                                    </td>

                                    <td class="text-truncate text-muted">
                                        <?php $date = $row->uptime ? (new \DateTime())->modify('-' . $row->uptime . ' seconds')->format('Y-m-d H:i:s') : null; ?>
                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('server_monitor.uptime') . '<br />' . ($date ? \Altum\Date::get_elapsed_time($date) : l('global.na')) . '</small>' ?>">
                                            <i class="fas fa-fw fa-clock text-muted"></i>
                                        </span>

                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('server_monitor.total_logs') . '<br />' . nr($row->total_logs) . '</small>' ?>">
                                            <i class="fas fa-fw fa-globe text-muted"></i>
                                        </span>

                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                            <i class="fas fa-fw fa-calendar text-muted"></i>
                                        </span>

                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                            <i class="fas fa-fw fa-history text-muted"></i>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <?= include_view(THEME_PATH . 'views/server-monitor/server_monitor_dropdown_button.php', ['id' => $row->server_monitor_id, 'resource_name' => $row->name]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>

                            </tbody>
                        </table>
                    </div>

                <?php else: ?>

                    <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                        'filters_get' => $data->filters->get ?? [],
                        'name' => 'server_monitors',
                        'has_secondary_text' => true,
                    ]); ?>

                <?php endif ?>
            </div>
        <?php endif ?>

        <?php if($is_enabled && $feature == 'game_servers' && settings()->monitors_heartbeats->game_servers_is_enabled): ?>
            <div class="mt-4 mb-5">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-gamepad mr-1"></i> <?= l('dashboard.game_servers.header') ?></h2>

                    <div class="flex-fill">
                        <hr class="border-gray-50" />
                    </div>

                    <div class="ml-3">
                        <a href="<?= url('game-server-create') ?>" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('game_servers.create') ?>"><i class="fas fa-fw fa-plus-circle fa-sm"></i></a>
                        <a href="<?= url('game-servers') ?>" class="btn btn-sm btn-blue-100" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-gamepad fa-sm"></i></a>
                    </div>
                </div>

                <?php if (!empty($data->game_servers)): ?>

                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th data-bulk-table class="d-none">
                                    <div class="custom-control custom-checkbox">
                                        <input id="bulk_select_all" type="checkbox" class="custom-control-input" />
                                        <label class="custom-control-label" for="bulk_select_all"></label>
                                    </div>
                                </th>
                                <th><?= l('game_servers.game_server') ?></th>
                                <th><?= l('game_servers.uptime') ?></th>
                                <th><?= l('game_servers.players') ?></th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach($data->game_servers as $row): ?>
                                <?php
                                $row->last_logs = json_decode($row->last_logs ?? '');
                                if(is_null($row->last_logs)) $row->last_logs = [[], [], [], [], [], [], []];
                                ?>

                                <?php
                                /* Determine the border color based on the status */
                                $uptime_class_name = match (true) {
                                    $row->uptime >= 90 => 'success',
                                    $row->uptime >= 50 => 'warning',
                                    $row->uptime >= 0 => 'danger',
                                };
                                ?>

                                <tr>
                                    <td data-bulk-table class="d-none">
                                        <div class="custom-control custom-checkbox">
                                            <input id="selected_game_server_id_<?= $row->game_server_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->game_server_id ?>" />
                                            <label class="custom-control-label" for="selected_game_server_id_<?= $row->game_server_id ?>"></label>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex flex-column">
                                            <div><a href="<?= url('game-server/' . $row->game_server_id) ?>"><?= $row->name ?></a></div>

                                            <small class="text-muted d-inline-flex align-items-center">
                                                <?php if($row->is_enabled): ?>
                                                    <?php if(!$row->total_checks): ?>
                                                        <span class="mr-2" data-toggle="tooltip" title="<?= l('game_servers.pending_check') ?>">
                                                            <i class="fas fa-fw fa-sm fa-clock text-muted"></i>
                                                        </span>
                                                    <?php elseif($row->is_ok): ?>
                                                        <span class="mr-2" data-toggle="tooltip" title="<?= l('game_servers.is_ok') ?>">
                                                            <i class="fas fa-fw fa-sm fa-check-circle text-success"></i>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-decoration-none mr-2" data-toggle="tooltip" title="<?= l('game_servers.is_not_ok') ?>">
                                                            <i class="fas fa-fw fa-sm fa-times-circle text-danger"></i>
                                                        </span>
                                                    <?php endif ?>
                                                <?php else: ?>
                                                    <span class="mr-2" data-toggle="tooltip" title="<?= l('game_servers.is_enabled_paused') ?>">
                                                        <i class="fas fa-fw fa-sm fa-pause-circle text-warning"></i>
                                                    </span>
                                                <?php endif ?>

                                                <img src="<?= ASSETS_FULL_URL . 'images/games/' . $data->game_server_types[$row->type]['icon'] ?>" class="img-fluid icon-favicon-small mr-2" data-toggle="tooltip" title="<?= $data->game_server_types[$row->type]['name'] ?>" />

                                                <span><?= $row->target . ($row->port ? ':' . $row->port : null) ?></span>
                                            </small>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php ob_start() ?>
                                        <div class='d-flex flex-column text-left'>
                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('game_servers.average_response_time') ?></div>
                                                <strong><?= display_response_time($row->average_response_time) ?></strong>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div>&bullet; <?= sprintf(l('game_server.total_checks_tooltip'), nr($row->total_checks)) ?></div>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div>&bullet; <?= sprintf(l('game_server.total_ok_checks_tooltip'), nr($row->total_ok_checks)) ?></div>
                                            </div>
                                        </div>
                                        <?php $tooltip = ob_get_clean() ?>
                                        <span class="badge badge-<?= $uptime_class_name ?>" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                            <?= nr($row->uptime, settings()->monitors_heartbeats->decimals) . '%' ?>
                                        </span>
                                    </td>

                                    <td class="text-nowrap">
                                        <span class="badge badge-light">
                                            <i class="fas fa-fw fa-sm fa-users mr-1"></i> <?= nr($row->online_players) . '/' . nr($row->maximum_online_players) ?>
                                        </span>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex">
                                            <?php foreach($row->last_logs as $log): ?>
                                                <?php if(isset($log->is_ok)): ?>

                                                    <?php ob_start() ?>
                                                    <div class='d-flex flex-column text-left'>
                                                        <div class='d-flex flex-column my-1'>
                                                            <div><?= \Altum\Date::get($log->datetime, 1) ?></div>
                                                            <strong><?= \Altum\Date::get_timeago($log->datetime) ?></strong>
                                                        </div>

                                                        <div class='d-flex flex-column my-1'>
                                                            <div><?= l('game_servers.is_ok') ?></div>
                                                            <strong><?= ($log->is_ok ? l('global.yes') : l('global.no')) ?></strong>
                                                        </div>

                                                        <div class='d-flex flex-column my-1'>
                                                            <div><?= l('game_servers.response_time') ?></div>
                                                            <strong><?= display_response_time($log->response_time) ?></strong>
                                                        </div>

                                                        <div class='d-flex flex-column my-1'>
                                                            <div><?= l('game_servers.players') ?></div>
                                                            <strong><?= nr($log->online_players) . '/' . nr($log->maximum_online_players) ?></strong>
                                                        </div>

                                                        <?php
                                                        $log_error = l('global.unknown');
                                                        if(isset($log->error->type)) {
                                                            if($log->error->type == 'exception') {
                                                                $log_error = $log->error->message;
                                                            }
                                                        }
                                                        ?>

                                                        <?php if(!$log->is_ok): ?>
                                                            <div class='d-flex flex-column my-1'>
                                                                <div><?= l('game_servers.checks.error') ?></div>
                                                                <strong><?= $log_error ?></strong>
                                                            </div>
                                                        <?php endif ?>
                                                    </div>

                                                    <?php $tooltip = ob_get_clean() ?>

                                                    <div
                                                            class="status-badge <?= $log->is_ok ? 'bg-success' : 'bg-danger' ?> mr-1"
                                                            data-toggle="tooltip"
                                                            data-html="true"
                                                            data-custom-class=""
                                                            title="<?= $tooltip ?>"
                                                    ></div>

                                                <?php else: ?>
                                                    <div class="status-badge bg-gray-200 mr-1"></div>
                                                <?php endif ?>
                                            <?php endforeach ?>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center">
                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('game_servers.last_check_datetime') . '<br />' . \Altum\Date::get($row->last_check_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_check_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_check_datetime) . ')</small>' ?>">
                                                <i class="fas fa-fw fa-calendar-check text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                                <i class="fas fa-fw fa-calendar text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                                <i class="fas fa-fw fa-history text-muted"></i>
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <?= include_view(THEME_PATH . 'views/game-server/game_server_dropdown_button.php', ['id' => $row->game_server_id, 'resource_name' => $row->name]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>

                            </tbody>
                        </table>
                    </div>

                <?php else: ?>

                    <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                            'filters_get' => $data->filters->get ?? [],
                            'name' => 'game_servers',
                            'has_secondary_text' => true,
                    ]); ?>

                <?php endif ?>
            </div>
        <?php endif ?>

    <?php endforeach ?>
</div>
