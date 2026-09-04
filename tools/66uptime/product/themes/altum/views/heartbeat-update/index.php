<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
<nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('heartbeats') ?>"><?= l('heartbeats.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li>
                <a href="<?= url('heartbeat/' . $data->heartbeat->heartbeat_id) ?>"><?= l('heartbeat.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('heartbeat_update.breadcrumb') ?></li>
        </ol>
    </nav>
<?php endif ?>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-heart-pulse mr-1"></i> <?= sprintf(l('global.update_x'), $data->heartbeat->name) ?></h1>

        <?= include_view(THEME_PATH . 'views/heartbeat/heartbeat_dropdown_button.php', ['id' => $data->heartbeat->heartbeat_id, 'resource_name' => $data->heartbeat->name]) ?>
    </div>
    <p></p>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->heartbeat->name ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-row">
                    <div class="form-group col">
                        <label for="run_interval"><i class="fas fa-fw fa-sm fa-sync text-muted mr-1"></i> <?= l('heartbeat.input.run_interval') ?></label>
                        <input type="number" min="1" step="1" id="run_interval" name="run_interval" class="form-control" value="<?= $data->heartbeat->settings->run_interval ?>" />
                    </div>

                    <div class="form-group col">
                        <label>&nbsp;</label>
                        <select id="run_interval_type" name="run_interval_type" class="custom-select">
                            <option value="minutes" <?= $data->heartbeat->settings->run_interval_type == 'minutes' ? 'selected="selected"' : null ?>><?= l('global.date.minutes') ?></option>
                            <option value="hours" <?= $data->heartbeat->settings->run_interval_type == 'hours' ? 'selected="selected"' : null ?>><?= l('global.date.hours') ?></option>
                            <option value="days" <?= $data->heartbeat->settings->run_interval_type == 'days' ? 'selected="selected"' : null ?>><?= l('global.date.days') ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col">
                        <label for="run_interval_grace"><i class="fas fa-fw fa-sm fa-hourglass-half text-muted mr-1"></i> <?= l('heartbeat.input.run_interval_grace') ?></label>
                        <input type="number" min="1" step="1" id="run_interval_grace" name="run_interval_grace" class="form-control" value="<?= $data->heartbeat->settings->run_interval_grace ?>" />
                        <small class="form-text text-muted"><?= l('heartbeat.input.run_interval_grace_help') ?></small>
                    </div>

                    <div class="form-group col">
                        <label>&nbsp;</label>
                        <select id="run_interval_grace_type" name="run_interval_grace_type" class="custom-select">
                            <option value="seconds" <?= $data->heartbeat->settings->run_interval_grace_type == 'seconds' ? 'selected="selected"' : null ?>><?= l('global.date.seconds') ?></option>
                            <option value="minutes" <?= $data->heartbeat->settings->run_interval_grace_type == 'minutes' ? 'selected="selected"' : null ?>><?= l('global.date.minutes') ?></option>
                            <option value="hours" <?= $data->heartbeat->settings->run_interval_grace_type == 'hours' ? 'selected="selected"' : null ?>><?= l('global.date.hours') ?></option>
                            <option value="days" <?= $data->heartbeat->settings->run_interval_grace_type == 'days' ? 'selected="selected"' : null ?>><?= l('global.date.days') ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="d-flex flex-wrap flex-row justify-content-between">
                        <label><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= l('heartbeat.input.is_ok_notifications') ?></label>
                        <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                    </div>
                    <div class="mb-2"><small class="text-muted"><?= l('heartbeat.input.is_ok_notifications_help') ?></small></div>

                    <div class="row">
                        <?php foreach($data->notification_handlers as $notification_handler): ?>
                            <div class="col-12 col-lg-6">
                                <div class="custom-control custom-checkbox my-2">
                                    <input id="is_ok_notifications_<?= $notification_handler->notification_handler_id ?>" name="is_ok_notifications[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->heartbeat->notifications->is_ok ?? []) ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label" for="is_ok_notifications_<?= $notification_handler->notification_handler_id ?>">
                                        <span class="mr-1"><?= $notification_handler->name ?></span>
                                        <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="is_enabled" name="is_enabled" type="checkbox" class="custom-control-input" <?= $data->heartbeat->is_enabled ? 'checked="checked"' : null?>>
                    <label class="custom-control-label" for="is_enabled"><?= l('heartbeat.input.is_enabled') ?></label>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 bg-blue-50 my-3" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('heartbeat.input.advanced') ?>
                </button>

                <div class="collapse" id="advanced_container">
                    <div>
                        <?php if(settings()->monitors_heartbeats->email_reports_is_enabled): ?>
                            <div <?= $this->user->plan_settings->email_reports_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                                <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->email_reports_is_enabled ? null : 'container-disabled' ?>">
                                    <input id="email_reports_is_enabled" name="email_reports_is_enabled" type="checkbox" class="custom-control-input" <?= $data->heartbeat->email_reports_is_enabled ? 'checked="checked"' : null?>>
                                    <label class="custom-control-label" for="email_reports_is_enabled"><?= l('heartbeat.input.email_reports_is_enabled') ?></label>
                                    <small class="form-text text-muted"><?= l('heartbeat.input.email_reports_is_enabled_help') ?></small>
                                </div>
                            </div>
                        <?php endif ?>

                        <?php if(settings()->monitors_heartbeats->projects_is_enabled): ?>
                        <div class="form-group">
                            <div class="d-flex flex-wrap flex-row justify-content-between">
                                <label for="project_id"><i class="fas fa-fw fa-sm fa-project-diagram text-muted mr-1"></i> <?= l('projects.project_id') ?></label>
                                <a href="<?= url('project-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('projects.create') ?></a>
                            </div>
                            <select id="project_id" name="project_id" class="custom-select">
                                <option value=" "><?= l('global.none') ?></option>
                                <?php foreach($data->projects as $project_id => $project): ?>
                                    <option value="<?= $project_id ?>" <?= $data->heartbeat->project_id == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                                <?php endforeach ?>
                            </select>
                            <small class="form-text text-muted"><?= l('projects.project_id_help') ?></small>
                        </div>
                        <?php endif ?>

                    </div>
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
            let selected = document.querySelectorAll('[name="is_ok_notifications[]"]:checked').length;

            if(selected >= active_notification_handlers_per_resource_limit) {
                document.querySelectorAll('[name="is_ok_notifications[]"]:not(:checked)').forEach(element => element.setAttribute('disabled', 'disabled'));
            } else {
                document.querySelectorAll('[name="is_ok_notifications[]"]:not(:checked)').forEach(element => element.removeAttribute('disabled'));
            }
        }

        document.querySelectorAll('[name="is_ok_notifications[]"]').forEach(element => element.addEventListener('change', process_notification_handlers));

        process_notification_handlers();
    }
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
