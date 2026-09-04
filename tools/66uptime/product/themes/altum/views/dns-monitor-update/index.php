<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('dns-monitors') ?>"><?= l('dns_monitors.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('dns_monitor_update.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-plug mr-1"></i> <?= sprintf(l('global.update_x'), $data->dns_monitor->name) ?></h1>

        <?= include_view(THEME_PATH . 'views/dns-monitor/dns_monitor_dropdown_button.php', ['id' => $data->dns_monitor->dns_monitor_id, 'resource_name' => $data->dns_monitor->name]) ?>
    </div>
    <p></p>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->dns_monitor->name ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-group">
                    <label for="target"><i class="fas fa-fw fa-sm fa-network-wired text-muted mr-1"></i> <?= l('dns_monitor.input.target') ?></label>
                    <input type="text" id="target" name="target" class="form-control <?= \Altum\Alerts::has_field_errors('target') ? 'is-invalid' : null ?>" value="<?= $data->dns_monitor->target ?>" placeholder="<?= l('dns_monitor.input.target_placeholder') ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('target') ?>
                </div>

                <div class="form-group">
                    <div class="d-flex flex-wrap flex-row justify-content-between">
                        <label><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= l('dns_monitor.input.notifications') ?></label>
                        <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                    </div>
                    <div class="mb-2"><small class="text-muted"><?= l('dns_monitor.input.notifications_help') ?></small></div>

                    <div class="row">
                        <?php foreach($data->notification_handlers as $notification_handler): ?>
                            <div class="col-12 col-lg-6">
                                <div class="custom-control custom-checkbox my-2">
                                    <input id="notifications_<?= $notification_handler->notification_handler_id ?>" name="notifications[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->dns_monitor->notifications ?? []) ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label" for="notifications_<?= $notification_handler->notification_handler_id ?>">
                                        <span class="mr-1"><?= $notification_handler->name ?></span>
                                        <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="dns_check_interval_seconds"><i class="fas fa-fw fa-sm fa-sync text-muted mr-1"></i> <?= l('dns_monitor.input.dns_check_interval_seconds') ?></label>
                    <select id="dns_check_interval_seconds" name="dns_check_interval_seconds" class="custom-select" required="required">
                        <?php foreach($data->dns_monitor_check_intervals as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $data->dns_monitor->settings->dns_check_interval_seconds == $key ? 'selected="selected"' : null ?> <?= !in_array($key, $this->user->plan_settings->dns_monitors_check_intervals ?? []) ? 'disabled="disabled"' : null ?>><?= $value ?></option>
                        <?php endforeach ?>
                    </select>
                    <small class="form-text text-muted"><?= l('dns_monitor.input.dns_check_interval_seconds_help') ?></small>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="is_enabled" name="is_enabled" type="checkbox" class="custom-control-input" <?= $data->dns_monitor->is_enabled ? 'checked="checked"' : null?>>
                    <label class="custom-control-label" for="is_enabled"><?= l('dns_monitor.input.is_enabled') ?></label>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 bg-blue-50 my-3" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('dns_monitor.input.advanced') ?>
                </button>

                <div class="collapse" id="advanced_container">
                    <div class="form-group">
                        <label><i class="fas fa-fw fa-sm fa-exchange-alt text-muted mr-1"></i> <?= l('dns_monitor.input.dns_types') ?></label>
                        <small class="form-text text-muted"><?= l('dns_monitor.input.dns_types_help') ?></small>

                        <div class="row">
                            <?php foreach($data->dns_types as $dns_type => $dns_type_code): ?>
                                <div class="col-12 col-lg-6">
                                    <div class="custom-control custom-checkbox my-2">
                                        <input id="dns_types_<?= $dns_type ?>" name="dns_types[]" value="<?= $dns_type ?>" type="checkbox" class="custom-control-input" <?= in_array($dns_type, $data->dns_monitor->settings->dns_types ?? []) ? 'checked="checked"' : null ?>>
                                        <label class="custom-control-label" for="dns_types_<?= $dns_type ?>">
                                            <span><?= $dns_type ?></span>
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
                                <option value="<?= $project_id ?>" <?= $data->dns_monitor->project_id == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
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
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

