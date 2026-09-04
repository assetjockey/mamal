<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('domain-names') ?>"><?= l('domain_names.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('domain_name_create.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 text-truncate"><i class="fas fa-fw fa-xs fa-network-wired mr-1"></i> <?= l('domain_name_create.header') ?></h1>
    <p></p>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('domain_name.input.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-group">
                    <label for="target"><i class="fas fa-fw fa-sm fa-network-wired text-muted mr-1"></i> <?= l('domain_name.input.target') ?></label>
                    <input type="text" id="target" name="target" class="form-control <?= \Altum\Alerts::has_field_errors('target') ? 'is-invalid' : null ?>" value="<?= $data->values['target'] ?>" placeholder="<?= l('domain_name.input.target_placeholder') ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('target') ?>
                </div>

                <div class="form-group">
                    <div class="d-flex flex-wrap flex-row justify-content-between">
                        <label><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= l('domain_name.input.whois_notifications') ?></label>
                        <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                    </div>

                    <div class="row">
                        <?php foreach($data->notification_handlers as $notification_handler): ?>
                            <div class="col-12 col-lg-6">
                                <div class="custom-control custom-checkbox my-2">
                                    <input id="whois_notifications_<?= $notification_handler->notification_handler_id ?>" name="whois_notifications[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->values['whois_notifications'] ?? []) ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label" for="whois_notifications_<?= $notification_handler->notification_handler_id ?>">
                                        <span class="mr-1"><?= $notification_handler->name ?></span>
                                        <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="whois_notifications_timing"><i class="fas fa-fw fa-sm fa-calendar text-muted mr-1"></i> <?= l('domain_name.input.whois_notifications_timing') ?></label>
                    <select id="whois_notifications_timing" name="whois_notifications_timing" class="custom-select" required="required">
                        <?php foreach($data->domain_name_timings as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $data->values['whois_notifications_timing'] == $key ? 'selected="selected"' : null ?>><?= sprintf(l('domain_name.input.whois_notifications_timing_input'), $value) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 bg-blue-50 my-3" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('domain_name.input.advanced') ?>
                </button>

                <div class="collapse" id="advanced_container">
                    <div class="form-group">
                        <label for="ssl_port"><i class="fas fa-fw fa-dna fa-sm text-muted mr-1"></i> <?= l('domain_name.input.ssl_port') ?></label>
                        <input type="number" min="0" max="100000" id="ssl_port" name="ssl_port" class="form-control <?= \Altum\Alerts::has_field_errors('ssl_port') ? 'is-invalid' : null ?>" value="<?= $data->values['ssl_port'] ?>" required="required" />
                        <?= \Altum\Alerts::output_field_error('ssl_port') ?>
                    </div>

                    <div>
                        <div class="form-group">
                            <div class="d-flex flex-wrap flex-row justify-content-between">
                                <label><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= l('domain_name.input.ssl_notifications') ?></label>
                                <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                            </div>

                            <div class="row">
                                <?php foreach($data->notification_handlers as $notification_handler): ?>
                                    <div class="col-12 col-lg-6">
                                        <div class="custom-control custom-checkbox my-2">
                                            <input id="ssl_notifications_<?= $notification_handler->notification_handler_id ?>" name="ssl_notifications[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->values['ssl_notifications'] ?? []) ? 'checked="checked"' : null ?>>
                                            <label class="custom-control-label" for="ssl_notifications_<?= $notification_handler->notification_handler_id ?>">
                                                <span class="mr-1"><?= $notification_handler->name ?></span>
                                                <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="ssl_notifications_timing"><i class="fas fa-fw fa-sm fa-calendar text-muted mr-1"></i> <?= l('domain_name.input.ssl_notifications_timing') ?></label>
                            <select id="ssl_notifications_timing" name="ssl_notifications_timing" class="custom-select" required="required">
                                <?php foreach($data->domain_name_timings as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= $data->values['ssl_notifications_timing'] == $key ? 'selected="selected"' : null ?>><?= sprintf(l('domain_name.input.ssl_notifications_timing_input'), $value) ?></option>
                                <?php endforeach ?>
                            </select>
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
                                    <option value="<?= $project_id ?>" <?= $data->values['project_id'] == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                                <?php endforeach ?>
                            </select>
                            <small class="form-text text-muted"><?= l('projects.project_id_help') ?></small>
                        </div>
                        <?php endif ?>

                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.create') ?></button>
            </form>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';
    
let active_notification_handlers_per_resource_limit = <?= (int) $this->user->plan_settings->active_notification_handlers_per_resource_limit ?>;

    if(active_notification_handlers_per_resource_limit != -1) {
        let process_notification_handlers_ssl = () => {
            let selected = document.querySelectorAll('[name="ssl_notifications[]"]:checked').length;

            if(selected >= active_notification_handlers_per_resource_limit) {
                document.querySelectorAll('[name="ssl_notifications[]"]:not(:checked)').forEach(element => element.setAttribute('disabled', 'disabled'));
            } else {
                document.querySelectorAll('[name="ssl_notifications[]"]:not(:checked)').forEach(element => element.removeAttribute('disabled'));
            }
        }

        document.querySelectorAll('[name="ssl_notifications[]"]').forEach(element => element.addEventListener('change', process_notification_handlers_ssl));

        process_notification_handlers_ssl();

        let process_notification_handlers_whois = () => {
            let selected = document.querySelectorAll('[name="whois_notifications[]"]:checked').length;

            if(selected >= active_notification_handlers_per_resource_limit) {
                document.querySelectorAll('[name="whois_notifications[]"]:not(:checked)').forEach(element => element.setAttribute('disabled', 'disabled'));
            } else {
                document.querySelectorAll('[name="whois_notifications[]"]:not(:checked)').forEach(element => element.removeAttribute('disabled'));
            }
        }

        document.querySelectorAll('[name="whois_notifications[]"]').forEach(element => element.addEventListener('change', process_notification_handlers_whois));

        process_notification_handlers_whois();
    }
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
