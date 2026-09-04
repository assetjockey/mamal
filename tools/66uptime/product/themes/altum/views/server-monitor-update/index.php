<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('server-monitors') ?>"><?= l('server_monitors.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('server_monitor_update.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-microchip mr-1"></i> <?= sprintf(l('global.update_x'), $data->server_monitor->name) ?></h1>

        <?= include_view(THEME_PATH . 'views/server-monitor/server_monitor_dropdown_button.php', ['id' => $data->server_monitor->server_monitor_id, 'resource_name' => $data->server_monitor->name]) ?>
    </div>
    <p></p>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->server_monitor->name ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-group">
                    <label for="target"><i class="fas fa-fw fa-sm fa-network-wired text-muted mr-1"></i> <?= l('server_monitor.input.target') ?></label>
                    <input type="text" id="target" name="target" class="form-control <?= \Altum\Alerts::has_field_errors('target') ? 'is-invalid' : null ?>" value="<?= $data->server_monitor->target ?>" placeholder="<?= l('server_monitor.input.target_placeholder') ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('target') ?>
                </div>

                <div class="form-group">
                    <label for="server_check_interval_seconds"><i class="fas fa-fw fa-sm fa-sync text-muted mr-1"></i> <?= l('server_monitor.input.server_check_interval_seconds') ?></label>
                    <select id="server_check_interval_seconds" name="server_check_interval_seconds" class="custom-select" required="required">
                        <?php foreach($data->server_monitor_check_intervals as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $data->server_monitor->settings->server_check_interval_seconds == $key ? 'selected="selected"' : null ?> <?= !in_array($key, $this->user->plan_settings->server_monitors_check_intervals ?? []) ? 'disabled="disabled"' : null ?>><?= $value ?></option>
                        <?php endforeach ?>
                    </select>
                    <small class="form-text text-muted"><?= l('server_monitor.input.server_check_interval_seconds_help') ?></small>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="is_enabled" name="is_enabled" type="checkbox" class="custom-control-input" <?= $data->server_monitor->is_enabled ? 'checked="checked"' : null?>>
                    <label class="custom-control-label" for="is_enabled"><?= l('server_monitor.input.is_enabled') ?></label>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 bg-blue-50 my-3" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('server_monitor.input.advanced') ?>
                </button>

                <div class="collapse" id="advanced_container">

                    <label><i class="fas fa-fw fa-sm fa-exclamation-triangle text-muted mr-1"></i> <?= l('server_monitor.input.alerts') ?></label>
                    <div id="alerts">
                        <?php foreach($data->server_monitor->settings->alerts ?? [] as $key => $alert): ?>
                            <div class="form-row p-3 bg-gray-50 rounded mb-4">
                                <div class="form-group col-lg-3">
                                    <label for="alert_metric[<?= $key ?>]"><?= l('server_monitor.input.alert_metric') ?></label>
                                    <select name="alert_metric[<?= $key ?>]" id="alert_metric[<?= $key ?>]" class="custom-select" data-is-not-custom-select>
                                        <option value="cpu_usage" <?= $alert->metric == 'cpu_usage' ? 'selected="selected"' : null ?>><?= l('server_monitor.cpu_usage') ?></option>
                                        <option value="disk_usage" <?= $alert->metric == 'disk_usage' ? 'selected="selected"' : null ?>><?= l('server_monitor.disk_usage') ?></option>
                                        <option value="ram_usage" <?= $alert->metric == 'ram_usage' ? 'selected="selected"' : null ?>><?= l('server_monitor.ram_usage') ?></option>
                                    </select>
                                </div>

                                <div class="form-group col-lg-3">
                                    <label for="alert_rule[<?= $key ?>]"><?= l('server_monitor.input.alert_rule') ?></label>
                                    <select name="alert_rule[<?= $key ?>]" id="alert_rule[<?= $key ?>]" class="custom-select" data-is-not-custom-select>
                                        <option value="is_higher" <?= $alert->rule == 'is_higher' ? 'selected="selected"' : null ?>><?= l('server_monitor.input.alert_rule.is_higher') ?></option>
                                        <option value="is_lower" <?= $alert->rule == 'is_lower' ? 'selected="selected"' : null ?>><?= l('server_monitor.input.alert_rule.is_lower') ?></option>
                                    </select>
                                </div>

                                <div class="form-group col-lg-3">
                                    <label for="alert_value[<?= $key ?>]"><?= l('server_monitor.input.alert_value') ?></label>
                                    <div class="input-group">
                                        <input id="alert_value[<?= $key ?>]" type="number" min="1" max="99" name="alert_value[<?= $key ?>]" class="form-control" value="<?= $alert->value ?>" />

                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                %
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-lg-3">
                                    <label for="alert_trigger[<?= $key ?>]"><?= l('server_monitor.input.alert_trigger') ?></label>
                                    <input id="alert_trigger[<?= $key ?>]" type="number" min="1" max="10" name="alert_trigger[<?= $key ?>]" class="form-control" value="<?= $alert->trigger ?>" />
                                </div>

                                <div class="col-lg-12">
                                    <button type="button" data-remove="alert" class="btn btn-sm btn-block btn-outline-danger"><i class="fas fa-fw fa-sm fa-times mr-1"></i> <?= l('global.delete') ?></button>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <div class="mb-4">
                        <button data-add="alert" type="button" class="btn btn-block btn-sm btn-outline-success"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('server_monitor.input.alert_add') ?></button>
                    </div>

                    <div class="form-group">
                        <div class="d-flex flex-wrap flex-row justify-content-between">
                            <label><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= l('server_monitor.input.notifications') ?></label>
                            <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                        </div>
                        <div class="mb-2"><small class="text-muted"><?= l('server_monitor.input.notifications_help') ?></small></div>

                        <div class="row">
                            <?php foreach($data->notification_handlers as $notification_handler): ?>
                                <div class="col-12 col-lg-6">
                                    <div class="custom-control custom-checkbox my-2">
                                        <input id="notifications_<?= $notification_handler->notification_handler_id ?>" name="notifications[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->server_monitor->notifications ?? []) ? 'checked="checked"' : null ?>>
                                        <label class="custom-control-label" for="notifications_<?= $notification_handler->notification_handler_id ?>">
                                            <span class="mr-1"><?= $notification_handler->name ?></span>
                                            <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>

                    <?php if(settings()->monitors_heartbeats->projects_is_enabled): ?>
                    <div class="form-group">
                        <div class="d-flex flex-wrap flex-row justify-content-between">
                            <label for="project_id"><i class="fas fa-fw fa-sm fa-project-diagram text-muted mr-1"></i> <?= l('projects.project_id') ?></label>
                            <a href="<?= url('project-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('projects.create') ?></a>
                        </div>
                        <select id="project_id" name="project_id" class="custom-select">
                            <option value=" "><?= l('global.none') ?></option>
                            <?php foreach($data->projects as $project_id => $project): ?>
                                <option value="<?= $project_id ?>" <?= $data->server_monitor->project_id == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="form-text text-muted"><?= l('projects.project_id_help') ?></small>
                    </div>
                    <?php endif ?>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.update') ?></button>
            </form>

        </div>
    </div>
</div>

<template id="template_alert">
    <div class="form-row p-3 bg-gray-50 rounded mb-4">
        <div class="form-group col-lg-3">
            <label for="alert_metric[]"><?= l('server_monitor.input.alert_metric') ?></label>
            <select name="alert_metric[]" id="alert_metric[]" class="custom-select" data-is-not-custom-select>
                <option value="cpu_usage"><?= l('server_monitor.cpu_usage') ?></option>
                <option value="disk_usage"><?= l('server_monitor.disk_usage') ?></option>
                <option value="ram_usage"><?= l('server_monitor.ram_usage') ?></option>
            </select>
        </div>

        <div class="form-group col-lg-3">
            <label for="alert_rule[]"><?= l('server_monitor.input.alert_rule') ?></label>
            <select name="alert_rule[]" id="alert_rule[]" class="custom-select" data-is-not-custom-select>
                <option value="is_higher"><?= l('server_monitor.input.alert_rule.is_higher') ?></option>
                <option value="is_lower"><?= l('server_monitor.input.alert_rule.is_lower') ?></option>
            </select>
        </div>

        <div class="form-group col-lg-3">
            <label for="alert_value[]"><?= l('server_monitor.input.alert_value') ?></label>
            <div class="input-group">
                <input id="alert_value[]" type="number" min="1" max="99" name="alert_value[]" class="form-control" value="50" />

                <div class="input-group-append">
                    <span class="input-group-text">
                        %
                    </span>
                </div>
            </div>
        </div>

        <div class="form-group col-lg-3">
            <label for="alert_trigger[]"><?= l('server_monitor.input.alert_trigger') ?></label>
            <input id="alert_trigger[]" type="number" min="1" max="10" name="alert_trigger[]" class="form-control" value="1" />
        </div>

        <div class="col-lg-12">
            <button type="button" data-remove="alert" class="btn btn-sm btn-block btn-outline-danger"><i class="fas fa-fw fa-sm fa-times mr-1"></i> <?= l('global.delete') ?></button>
        </div>
    </div>
</template>

<?php ob_start() ?>
<script>
    'use strict';
    
let active_notification_handlers_per_resource_limit = <?= (int) $this->user->plan_settings->active_notification_handlers_per_resource_limit ?>;

    if(active_notification_handlers_per_resource_limit != -1) {
        let process_notification_handlers = () => {
            let selected = document.querySelectorAll('[name="notifications[]"]:checked').length;

            if(selected >= active_notification_handlers_per_resource_limit) {
                document.querySelectorAll('[name="notifications[]"]:not(:checked)').forEach(element => element.setAttribute('disabled', 'disabled'));
            } else {
                document.querySelectorAll('[name="notifications[]"]:not(:checked)').forEach(element => element.removeAttribute('disabled'));
            }
        }

        document.querySelectorAll('[name="notifications[]"]').forEach(element => element.addEventListener('change', process_notification_handlers));

        process_notification_handlers();
    }

    /* add new alert */
    let alert_add = event => {
        let clone = document.querySelector(`#template_alert`).content.cloneNode(true);

        let alerts_count = document.querySelectorAll(`#alerts .form-row`).length;

        if(alerts_count > 5) {
            return;
        }

        clone.querySelector(`[name="alert_metric[]"`).setAttribute('name', `alert_metric[${alerts_count}]`);
        clone.querySelector(`[name="alert_rule[]"`).setAttribute('name', `alert_rule[${alerts_count}]`);
        clone.querySelector(`[name="alert_value[]"`).setAttribute('name', `alert_value[${alerts_count}]`);
        clone.querySelector(`[name="alert_trigger[]"`).setAttribute('name', `alert_trigger[${alerts_count}]`);

        document.querySelector(`#alerts`).appendChild(clone);

        alert_remove_initiator();
    };

    document.querySelectorAll('[data-add]').forEach(element => {
        element.addEventListener('click', alert_add);
    })


    /* remove alert */
    let alert_remove = event => {
        event.currentTarget.closest('.form-row').remove();

        /* Reset tooltips */
        tooltips_initiate();
    };

    let alert_remove_initiator = () => {
        document.querySelectorAll('#alerts [data-remove]').forEach(element => {
            element.removeEventListener('click', alert_remove);
            element.addEventListener('click', alert_remove)
        })
    };

    alert_remove_initiator();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
