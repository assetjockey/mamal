<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('monitors') ?>"><?= l('monitors.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?= url('monitor/' . $data->monitor->monitor_id) ?>"><?= l('monitor.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?= url('monitor-logs/' . $data->monitor->monitor_id) ?>"><?= l('monitor_logs.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('monitor_log.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="card bg-blue-900 border-0 rounded-2x">
        <div class="card-body">
            <div class="row">
                <div class="col-auto d-flex align-items-center">
                    <?php if($data->monitor->is_enabled): ?>
                        <?php if(!$data->monitor->total_checks): ?>
                            <div data-toggle="tooltip" title="<?= l('monitor.pending_check') ?>">
                                <i class="fas fa-fw fa-clock fa-3x text-gray-400"></i>
                            </div>
                        <?php elseif($data->monitor->is_ok): ?>
                            <div data-toggle="tooltip" title="<?= l('monitor.is_ok') ?>" class="pulse-animation pulse-animation-success">
                                <span class="pulse-circle"></span>
                                <i class="fas fa-fw fa-check-circle fa-3x text-primary-400"></i>
                            </div>
                        <?php else: ?>
                            <div data-toggle="tooltip" title="<?= l('monitor.is_not_ok') ?>" class="pulse-animation pulse-animation-danger">
                                <span class="pulse-circle"></span>
                                <i class="fas fa-fw fa-times-circle fa-3x text-danger"></i>
                            </div>
                        <?php endif ?>
                    <?php else: ?>
                        <div data-toggle="tooltip" title="<?= l('monitor.is_enabled_paused') ?>">
                            <i class="fas fa-fw fa-pause-circle fa-3x text-warning"></i>
                        </div>
                    <?php endif ?>
                </div>

                <div class="col text-truncate">
                    <h1 class="h3 text-truncate text-white mb-0 mr-2"><?= sprintf(l('monitor_log.header'), $data->monitor->name) ?></h1>

                    <div class="text-gray-400">
                        <span><?= $data->monitor->target ?><?= $data->monitor->port ? ':' . $data->monitor->port : null ?></span>
                    </div>
                </div>

                <div class="col-auto">
                    <?= include_view(THEME_PATH . 'views/monitor/monitor_dropdown_button.php', ['id' => $data->monitor->monitor_id, 'resource_name' => $data->monitor->name, 'button_text_class' => 'text-white']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-between mt-4">
        <div class="col-12 col-xl-4 p-3">
            <div class="card h-100">
                <div class="card-body d-flex p-3">
                    <div>
                        <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                            <div class="p-3 d-flex align-items-center justify-content-between">
                                <i class="fas fa-fw <?= $data->monitor_log->is_ok ? 'fa-check' : 'fa-times' ?> fa-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <span class="text-muted font-size-small"><?= l('global.status') ?></span>
                        <div class="d-flex align-items-center">
                            <div class="card-title h6 m-0"><?= $data->monitor_log->is_ok ? l('monitor.is_ok') : l('monitor.is_not_ok') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4 p-3">
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
                        <span class="text-muted font-size-small"><?= l('monitor.checks.response_time') ?></span>
                        <div class="d-flex align-items-center">
                            <div class="card-title h6 m-0"><?= display_response_time($data->monitor_log->response_time) ?></div>

                            <?php if($data->monitor_log->response_time && $data->monitor->average_response_time && $data->monitor_log->response_time != $data->monitor->average_response_time): ?>
                                <?php if($data->monitor_log->response_time > $data->monitor->average_response_time): ?>
                                    <span class="badge badge-pill badge-danger ml-1" data-toggle="tooltip" title="<?= sprintf(l('monitor.checks.higher_than_average'), display_response_time(abs($data->monitor->average_response_time - $data->monitor_log->response_time)), display_response_time($data->monitor->average_response_time)) ?>">
                                        <i class="fas fa-fw fa-arrow-up fa-sm"></i>
                                        <?= nr(get_percentage_change($data->monitor->average_response_time, $data->monitor_log->response_time), 2) . '%'; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-pill badge-success ml-1" data-toggle="tooltip" title="<?= sprintf(l('monitor.checks.lower_than_average'), display_response_time(abs($data->monitor->average_response_time - $data->monitor_log->response_time)), display_response_time($data->monitor->average_response_time)) ?>">
                                        <i class="fas fa-fw fa-arrow-down fa-sm"></i>
                                        <?= nr(get_percentage_change($data->monitor->average_response_time, $data->monitor_log->response_time), 2) . '%'; ?>
                                    </span>
                                <?php endif ?>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4 p-3">
            <div class="card h-100">
                <div class="card-body d-flex p-3">
                    <div>
                        <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                            <div class="p-3 d-flex align-items-center justify-content-between">
                                <i class="fas fa-fw fa-stream fa-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <span class="text-muted font-size-small"><?= l('monitor.checks.response_status_code') ?></span>
                        <div class="d-flex align-items-center">
                            <div class="card-title h6 m-0"><?= $data->monitor_log->response_status_code ?></div>
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
                                <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($data->ping_servers[$data->monitor_log->ping_server_id]->country_code) . '.svg' ?>" class="img-fluid icon-favicon" style="width: 1.5rem;" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <span class="text-muted font-size-small"><?= l('monitor.ping_servers_checks.ping_server') ?></span>
                        <div class="d-flex align-items-center">
                            <div class="card-title h6 m-0">
                                <?= get_country_from_country_code($data->ping_servers[$data->monitor_log->ping_server_id]->country_code). ', ' . $data->ping_servers[$data->monitor_log->ping_server_id]->city_name ?>
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
                                <i class="fas fa-fw fa-calendar-check fa-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <span class="text-muted font-size-small"><?= l('monitor.checks.datetime') ?></span>
                        <div class="d-flex align-items-center">
                            <div class="card-title h6 m-0"><?= \Altum\Date::get_timeago($data->monitor_log->datetime) ?></div>

                            <div class="ml-2">
                                <span data-toggle="tooltip" title="<?= \Altum\Date::get($data->monitor_log->datetime, 1) ?>">
                                    <i class="fas fa-sm fa-fw fa-info-circle text-muted"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(!$data->monitor_log->is_ok): ?>
        <div class="row">
            <div class="col-12 col-xl p-3">
                <div class="card h-100">
                    <div class="card-body d-flex p-3">
                        <div>
                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-times-circle fa-lg text-danger"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted font-size-small"><?= l('monitor.checks.error') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0">
                                    <?php
                                    $data->monitor_log->error = json_decode($data->monitor_log->error ?? '');
                                    $error = l('global.unknown');

                                    if($data->monitor_log->error->type == 'exception') {
                                        $error = $data->monitor_log->error->message;
                                    } elseif(in_array($data->monitor_log->error->type, ['response_status_code', 'response_body', 'response_header', 'ping_failed', 'parse_error', 'socket_connect_failed', 'socket_no_response', 'socket_create_failed'])) {
                                        $error = l('monitor.checks.error.' . $data->monitor_log->error->type);
                                    } elseif(in_array($data->monitor_log->error->type, ['connection_failed'])) {
                                        $error = sprintf(l('monitor.checks.error.connection_failed'), $data->monitor_log->error->message);
                                    }
                                    ?>

                                    <?= $error ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if(($data->monitor_log->error->type ?? null) == 'response_body'): ?>
        <div class="card">
            <div class="card-body">
                <div class="text-muted font-weight-bold mb-3">
                    <i class="fas fa-fw fa-reply mr-1"></i>
                    <?= l('monitor.checks.response_body') ?>
                </div>

                <pre class="pre-custom rounded">
<?= $data->monitor_log->response_body ? e($data->monitor_log->response_body) : l('global.none') ?>
                </pre>
            </div>
        </div>
        <?php endif ?>
    <?php endif ?>
</div>

