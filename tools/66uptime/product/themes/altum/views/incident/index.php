<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('incidents') ?>"><?= l('incidents.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>

                <?php if($data->incident->monitor_id): ?>
                    <li>
                        <a href="<?= url('monitor/' . $data->incident->monitor_id) ?>"><?= l('monitor.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?= url('heartbeat/' . $data->incident->heartbeat_id) ?>"><?= l('heartbeat.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                    </li>
                <?php endif ?>

                <li class="active" aria-current="page"><?= l('incident.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="card bg-blue-900 border-0 rounded-2x">
        <div class="card-body">
            <div class="row">
                <div class="col-auto d-flex align-items-center">
                    <?php if($data->incident->end_monitor_log_id || $data->incident->end_heartbeat_log_id): ?>
                        <div data-toggle="tooltip" title="<?= l('incidents.solved') ?>" class="pulse-animation pulse-animation-success">
                            <span class="pulse-circle"></span>
                            <i class="fas fa-fw fa-check-circle fa-3x text-primary-400"></i>
                        </div>
                    <?php else: ?>
                        <div data-toggle="tooltip" title="<?= l('incidents.unsolved') ?>" class="pulse-animation pulse-animation-danger">
                            <span class="pulse-circle"></span>
                            <i class="fas fa-fw fa-times-circle fa-3x text-danger"></i>
                        </div>
                    <?php endif ?>
                </div>

                <div class="col d-flex align-items-center">
                    <div>
                        <h1 class="h3 text-truncate text-white mb-0 mr-2">
                            <?= sprintf(l('incident.header'), ($data->incident->end_monitor_log_id || $data->incident->end_heartbeat_log_id) ? l('incidents.solved') : l('incidents.unsolved')) ?>
                        </h1>

                        <div class="font-size-small">
                            <?php if($data->incident->monitor_id): ?>
                                <a href="<?= url('monitor/' . $data->monitor->monitor_id) ?>" class="text-decoration-none">
                                    <?= $data->monitor->name ?>
                                </a>

                                <span class="text-gray-400">(<?= $data->monitor->target ?><?= $data->monitor->port ? ':' . $data->monitor->port : null ?>)</span>
                            <?php else: ?>
                                <a href="<?= url('heartbeat/' . $data->heartbeat->heartbeat_id) ?>" class="text-decoration-none">
                                    <?= $data->heartbeat->name ?>
                                </a>
                            <?php endif ?>
                        </div>
                    </div>
                </div>

                <div class="col-auto d-flex align-items-center">
                    <?= include_view(THEME_PATH . 'views/incidents/incident_dropdown_button.php', ['id' => $data->incident->incident_id, 'button_text_class' => 'text-white']) ?>
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
                                <i class="fas fa-fw fa-calendar-day fa-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <span class="text-muted font-size-small"><?= l('incidents.start_datetime') ?></span>
                        <div class="d-flex align-items-center">
                            <div class="card-title h6 m-0">
                                <span data-toggle="tooltip" title="<?= \Altum\Date::get($data->incident->start_datetime) ?>">
                                    <?= \Altum\Date::get_timeago($data->incident->start_datetime) ?>
                                </span>
                            </div>
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
                                <i class="fas fa-fw fa-calendar-week fa-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <span class="text-muted font-size-small"><?= l('incidents.end_datetime') ?></span>
                        <div class="d-flex align-items-center">
                            <div class="card-title h6 m-0">
                                <?php if($data->incident->end_datetime): ?>
                                    <span data-toggle="tooltip" title="<?= \Altum\Date::get($data->incident->end_datetime) ?>">
                                        <?= \Altum\Date::get_timeago($data->incident->end_datetime) ?>
                                    </span>
                                <?php else: ?>
                                    <?= l('incidents.end_datetime_null') ?>
                                <?php endif ?>
                            </div>
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
                                <i class="fas fa-fw fa-clock fa-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <span class="text-muted font-size-small"><?= l('incidents.length') ?></span>
                        <div class="d-flex align-items-center">
                            <div class="card-title h6 m-0">
                                <?= \Altum\Date::get_elapsed_time($data->incident->start_datetime, $data->incident->end_datetime, 2) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6 col-xl-4 p-3">
            <div class="card h-100">
                <div class="card-body d-flex p-3">
                    <div>
                        <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                            <div class="p-3 d-flex align-items-center justify-content-between">
                                <i class="fas fa-fw fa-times-circle fa-lg text-danger"></i>
                            </div>
                        </div>
                    </div>

                    <div class="text-truncate">
                        <span class="text-muted font-size-small"><?= l('incidents.error') ?></span>
                        <div class="d-flex align-items-center">
                            <div class="card-title h6 m-0 text-truncate">
                                <?php
                                $error = l('global.unknown');
                                if(isset($data->incident->error->type)) {
                                    if($data->incident->error->type == 'exception') {
                                        $error = $data->incident->error->message;
                                    } elseif(in_array($data->incident->error->type, ['response_status_code', 'response_body', 'response_header', 'ping_failed', 'parse_error', 'socket_connect_failed', 'socket_no_response', 'socket_create_failed'])) {
                                        $error = l('monitor.checks.error.' . $data->incident->error->type);
                                    } elseif(in_array($data->incident->error->type, ['connection_failed'])) {
                                        $error = sprintf(l('monitor.checks.error.connection_failed'), $data->incident->error->message);
                                    }
                                }

                                if($data->incident->heartbeat_id) {
                                    $error = l('heartbeat.checks.error.missed');
                                }
                                ?>

                                <span data-toggle="tooltip" title="<?= $error ?>">
                                    <?= $error ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 col-xl-4 p-3">
            <div class="card h-100 position-relative">
                <div class="card-body d-flex p-3">
                    <div>
                        <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x" data-toggle="tooltip" title="<?= l('monitor_logs.menu') ?>">
                            <div class="p-3 d-flex align-items-center justify-content-between">
                                <a href="<?= url(($data->incident->monitor_id ? 'monitor-logs/' . $data->incident->monitor_id : 'heartbeat/' . $data->incident->heartbeat_id) . '?start_date=' . $data->incident->start_datetime . '&end_date=' . $data->incident->end_datetime) ?>" class="stretched-link text-reset">
                                    <i class="fas fa-fw fa-undo fa-lg"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div>
                        <span class="text-muted font-size-small"><?= l('incidents.failed_checks') ?></span>
                        <div class="d-flex align-items-center">
                            <div class="card-title h6 m-0">
                                <?= nr($data->incident->failed_checks) ?>
                            </div>

                            <?php if(settings()->monitors_heartbeats->monitors_double_check_is_enabled): ?>
                                <div class="ml-2">
                                    <span data-toggle="tooltip" title="<?= l('incidents.failed_checks_help') ?>">
                                        <i class="fas fa-fw fa-sm fa-info-circle text-muted"></i>
                                    </span>
                                </div>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl p-3">
            <div class="card h-100">
                <div class="card-body d-flex p-3">
                    <div>
                        <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                            <div class="p-3 d-flex align-items-center justify-content-between">
                                <i class="fas fa-fw fa-comment fa-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div class="w-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted font-size-small"><?= l('incidents.comment') ?></span>

                            <button type="button" class="btn btn-sm btn-light ml-1" data-tooltip title="<?= l('global.update') ?>" data-toggle="modal" data-target="#incident_comment_modal" data-incident-id="<?= $data->incident->incident_id ?>" data-comment="<?= $data->incident->comment ?>" data-is-solved="<?= (int) !empty($data->incident->end_datetime) ?>">
                                <i class="fas fa-fw fa-sm fa-pen"></i>
                            </button>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="card-title h6 m-0">
                                <span id="incident_comment">
                                    <?= $data->incident->comment ?: l('global.none') ?>
                                </span>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th colspan="5"><?= l('incidents.events') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if($data->incident->end_datetime): ?>
                    <tr>
                        <td class="text-truncate">
                            <div class="d-flex align-items-center">
                                <div class="incident-icon incident-icon-solved mr-3">
                                    <i class="fas fa-fw fa-check-circle text-success"></i>
                                </div>

                                <div>
                                    <div>
                                        <?= l('incidents.events.solved') ?>
                                    </div>

                                    <span class="text-muted small">
                                        <?= \Altum\Date::get($data->incident->end_datetime) ?>
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endif ?>

                <?php if($data->incident->failed_checks > (settings()->monitors_heartbeats->monitors_double_check_is_enabled ? 2 : 1)): ?>
                    <tr>
                        <td class="text-truncate">
                            <div class="d-flex align-items-center">
                                <div class="incident-icon incident-icon-error mr-3">
                                    <i class="fas fa-fw fa-times text-danger"></i>
                                </div>

                                <div>
                                    <div>
                                        <?= sprintf(l('incidents.events.in_between_errors'), '<strong>' . nr($data->incident->failed_checks - (settings()->monitors_heartbeats->monitors_double_check_is_enabled ? 2 : 1)) . '</strong>') ?>
                                    </div>

                                    <span class="text-muted small">
                                        <?= \Altum\Date::get($data->incident->last_failed_check_datetime) ?> (<?= \Altum\Date::get_elapsed_time($data->incident->start_datetime, $data->incident->last_failed_check_datetime, 2) ?>)
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endif ?>


                <?php if (!empty($data->incident->notification_handlers_ids)): ?>
                    <?php $notification_handlers = require APP_PATH . 'includes/notification_handlers.php'; ?>

                    <?php foreach($data->incident->notification_handlers_ids as $notification_handler_id): ?>
                        <?php $notification_handler = $data->notification_handlers[$notification_handler_id] ?>

                        <tr>
                            <td class="text-truncate">
                                <div class="d-flex align-items-center">
                                    <div class="incident-icon mr-3" style="background: <?= $notification_handlers[$notification_handler->type]['background_color'] ?>">
                                        <i class="<?= $notification_handlers[$notification_handler->type]['icon'] ?> fa-fw" style="color: <?= $notification_handlers[$notification_handler->type]['color'] ?>"></i>
                                    </div>

                                    <div>
                                        <div class="font-size-small">
                                            <?= sprintf(l('incidents.events.notification_handler'), '<a href="' . url('notification-handler-update/' . $notification_handler_id) . '" class="font-weight-bold text-reset" target="_blank">' . $notification_handler->name . '</a>') ?>
                                        </div>

                                        <span class="text-muted small">
                                            <?= \Altum\Date::get($data->incident->start_datetime) ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php else: ?>
                    <tr>
                        <td class="text-truncate">
                            <div class="d-flex align-items-center">
                                <div class="incident-icon bg-gray-200 mr-3">
                                    <i class="fas fa-fw fa-bell-slash text-dark"></i>
                                </div>

                                <div>
                                    <div>
                                        <?= l('incidents.events.no_notification_handlers') ?>
                                    </div>

                                    <span class="text-muted small">
                                        <?= \Altum\Date::get($data->incident->start_datetime) ?>
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endif ?>

                <tr>
                    <td class="text-truncate">
                        <div class="d-flex align-items-center">
                            <div class="incident-icon incident-icon-error mr-3">
                                <?php if($data->incident->monitor_id && settings()->monitors_heartbeats->monitors_double_check_is_enabled): ?>
                                    <i class="fas fa-fw fa-redo text-danger"></i>
                                <?php else: ?>
                                    <i class="fas fa-fw fa-times-circle text-danger"></i>
                                <?php endif ?>
                            </div>

                            <div>
                                <div>
                                    <?php if($data->incident->monitor_id && settings()->monitors_heartbeats->monitors_double_check_is_enabled): ?>
                                        <?= sprintf(l('incidents.events.second_error'), '<strong>' . settings()->monitors_heartbeats->monitors_double_check_wait . '</strong>') ?>.
                                    <?php else: ?>
                                        <?= l('incidents.events.first_error') ?>: <strong><?= $error ?></strong>.
                                    <?php endif ?>
                                </div>

                                <span class="text-muted small">
                                    <?= \Altum\Date::get($data->incident->start_datetime) ?>
                                </span>
                            </div>
                        </div>
                    </td>
                </tr>

                <?php if($data->incident->monitor_id && settings()->monitors_heartbeats->monitors_double_check_is_enabled): ?>
                    <tr>
                        <td class="text-truncate">
                            <div class="d-flex align-items-center">
                                <div class="incident-icon incident-icon-error mr-3">
                                    <i class="fas fa-fw fa-times-circle text-danger"></i>
                                </div>

                                <div>
                                    <div>
                                        <?= l('incidents.events.first_error') ?>: <strong><?= $error ?></strong>.
                                    </div>

                                    <span class="text-muted small">
                                        <?php $start_datetime = date('Y-m-d H:i:s', strtotime($data->incident->start_datetime) - settings()->monitors_heartbeats->monitors_double_check_wait); ?>

                                        <?= \Altum\Date::get($start_datetime) ?>
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($data->incident->monitor_id): ?>
        <div class="mt-5">
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th colspan="4">
                            <?= l('monitor.checks.incident_checks') ?>
                            <span class="ml-3 small">
                                <a href="<?= url(($data->incident->monitor_id ? 'monitor-logs/' . $data->incident->monitor_id : 'heartbeat/' . $data->incident->heartbeat_id) . '?start_date=' . $data->incident->start_datetime . '&end_date=' . $data->incident->end_datetime . '&timezone=UTC') ?>">
                                    <?= l('global.view_all') ?>
                                </a>
                            </span>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="2"><?= l('global.status') ?></th>
                        <th><?= l('monitor.checks.response_time') ?></th>
                        <?php if($data->monitor->type == 'website'): ?>
                            <th><?= l('monitor.checks.response_status_code') ?></th>
                        <?php endif ?>
                        <th><?= l('monitor.checks.datetime') ?></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(!$data->total_monitor_logs): ?>
                        <tr>
                            <td colspan="4" class="text-muted"><?= l('monitor.checks.no_data') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php for($i = count($data->monitor_logs) - 1; $i >= count($data->monitor_logs) - 25; $i--): ?>

                            <?php
                            if(!isset($data->monitor_logs[$i])) {
                                continue;
                            }
                            ?>

                            <?php $incident_td_style = $data->monitor_logs[$i]->is_ok ? 'style="background: #00800026"' : null ?>

                            <tr>
                                <td class="text-nowrap" <?= $incident_td_style ?>>
                                    <?php if($data->monitor_logs[$i]->is_ok): ?>
                                        <i class="fas fa-fw fa-sm fa-check-circle text-success"></i>
                                    <?php else: ?>
                                        <i class="fas fa-fw fa-sm fa-times-circle text-danger"></i>
                                    <?php endif ?>

                                    <?php if(!$data->monitor_logs[$i]->is_ok): ?>
                                        <?php
                                        $error = l('global.unknown');

                                        if($data->monitor_logs[$i]->error->type == 'exception') {
                                            $error = $data->monitor_logs[$i]->error->message;
                                        } elseif(in_array($data->monitor_logs[$i]->error->type, ['response_status_code', 'response_body', 'response_header', 'ping_failed', 'parse_error', 'socket_connect_failed', 'socket_no_response', 'socket_create_failed'])) {
                                            $error = l('monitor.checks.error.' . $data->monitor_logs[$i]->error->type);
                                        } elseif(in_array($data->monitor_logs[$i]->error->type, ['connection_failed'])) {
                                            $error = sprintf(l('monitor.checks.error.connection_failed'), $data->monitor_logs[$i]->error->message);
                                        }
                                        ?>

                                        <span class="ml-3" data-toggle="tooltip" title="<?= $error ?>">
                                            <i class="fas fa-fw fa-sm fa-envelope-open-text text-muted"></i>
                                        </span>
                                    <?php endif ?>
                                </td>

                                <td class="text-nowrap" <?= $incident_td_style ?>>
                                    <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($data->ping_servers[$data->monitor_logs[$i]->ping_server_id]->country_code) . '.svg' ?>" class="img-fluid icon-favicon" data-toggle="tooltip" title="<?= get_country_from_country_code($data->ping_servers[$data->monitor_logs[$i]->ping_server_id]->country_code). ', ' . $data->ping_servers[$data->monitor_logs[$i]->ping_server_id]->city_name ?>" />
                                </td>

                                <td class="text-nowrap" <?= $incident_td_style ?>>
                                    <?= display_response_time($data->monitor_logs[$i]->response_time) ?>

                                    <?php if($data->monitor->average_response_time && $data->monitor_logs[$i]->response_time && $data->monitor->average_response_time != $data->monitor_logs[$i]->response_time): ?>
                                        <?php if($data->monitor_logs[$i]->response_time > $data->monitor->average_response_time): ?>
                                            <span class="badge badge-pill badge-danger ml-1" data-toggle="tooltip" title="<?= sprintf(l('monitor.checks.higher_than_average'), display_response_time(abs($data->monitor->average_response_time - $data->monitor_logs[$i]->response_time)), display_response_time($data->monitor->average_response_time)) ?>">
                                                <i class="fas fa-fw fa-arrow-up fa-sm"></i>
                                                <?= nr(get_percentage_change($data->monitor->average_response_time, $data->monitor_logs[$i]->response_time), 2) . '%'; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-pill badge-success ml-1" data-toggle="tooltip" title="<?= sprintf(l('monitor.checks.lower_than_average'), display_response_time(abs($data->monitor->average_response_time - $data->monitor_logs[$i]->response_time)), display_response_time($data->monitor->average_response_time)) ?>">
                                                <i class="fas fa-fw fa-arrow-down fa-sm"></i>
                                                <?= nr(get_percentage_change($data->monitor->average_response_time, $data->monitor_logs[$i]->response_time), 2) . '%'; ?>
                                            </span>
                                        <?php endif ?>
                                    <?php endif ?>
                                </td>

                                <?php if($data->monitor->type == 'website'): ?>
                                    <td class="text-nowrap" <?= $incident_td_style ?>>
                                        <span class="badge badge-light">
                                            <?= $data->monitor_logs[$i]->response_status_code ?>
                                        </span>
                                    </td>
                                <?php endif ?>

                                <td class="text-nowrap" <?= $incident_td_style ?>>
                                    <span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($data->monitor_logs[$i]->datetime, 1) ?>">
                                        <?= \Altum\Date::get_timeago($data->monitor_logs[$i]->datetime) ?>
                                    </span>
                                </td>

                                <td class="text-nowrap" <?= $incident_td_style ?>>
                                    <a href="<?= url('monitor-log/' . $data->monitor_logs[$i]->monitor_log_id) ?>" class="text-muted" data-toggle="tooltip" title="<?= l('global.view') ?>"><i class="fas fa-fw fa-sm fa-arrow-right"></i></a>
                                </td>
                            </tr>

                        <?php endfor ?>
                    <?php endif ?>

                    </tbody>
                </table>
            </div>
        </div>
    <?php endif ?>
</div>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/incidents/incident_comment_modal.php'), 'modals'); ?>
