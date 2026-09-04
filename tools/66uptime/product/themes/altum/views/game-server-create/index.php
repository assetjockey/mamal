<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('monitors') ?>"><?= l('monitors.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('game_server_create.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 text-truncate"><i class="fas fa-fw fa-xs fa-gamepad mr-1"></i> <?= l('game_server_create.header') ?></h1>
    <p></p>

    <div class="card">
        <div class="card-body">

            <form id="game_server_create" action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="target"><i class="fas fa-fw fa-sm fa-globe text-muted mr-1"></i> <?= l('game_server.input.target') ?></label>
                            <input type="text" id="target" name="target" class="form-control <?= \Altum\Alerts::has_field_errors('target') ? 'is-invalid' : null ?>" value="<?= $data->values['target'] ?>" placeholder="<?= l('game_server.input.target_placeholder') ?>" required="required" />
							<?= \Altum\Alerts::output_field_error('target') ?>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="port"><i class="fas fa-fw fa-sm fa-dna text-muted mr-1"></i> <?= l('game_server.input.port') ?></label>
                            <input type="number" min="0" max="100000" id="port" name="port" class="form-control <?= \Altum\Alerts::has_field_errors('port') ? 'is-invalid' : null ?>" value="<?= $data->values['port'] ?>" placeholder="<?= l('game_server.input.port_placeholder') ?>" required="required" />
							<?= \Altum\Alerts::output_field_error('port') ?>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="query_port"><i class="fas fa-fw fa-sm fa-puzzle-piece text-muted mr-1"></i> <?= l('game_server.input.query_port') ?></label>
                            <input type="number" min="0" max="100000" id="query_port" name="query_port" class="form-control <?= \Altum\Alerts::has_field_errors('query_port') ? 'is-invalid' : null ?>" value="<?= $data->values['query_port'] ?>" placeholder="<?= l('game_server.input.query_port_placeholder') ?>" required="required" />
                            <?= \Altum\Alerts::output_field_error('query_port') ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="type"><i class="fas fa-fw fa-sm fa-gamepad text-muted mr-1"></i> <?= l('game_server.input.type') ?></label>
                    <select id="type" name="type" class="custom-select" required="required">
                        <?php foreach($data->game_server_types as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $data->values['type'] == $key ? 'selected="selected"' : null ?> data-port="<?= $value['port'] ?>" data-query-port="<?= $value['query_port'] ?>"><?= $value['name'] ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="check_interval_seconds"><i class="fas fa-fw fa-sm fa-sync text-muted mr-1"></i> <?= l('game_server.input.check_interval_seconds') ?></label>
                    <select id="check_interval_seconds" name="check_interval_seconds" class="custom-select" required="required">
                        <?php foreach($data->game_server_check_intervals as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $data->values['check_interval_seconds'] == $key ? 'selected="selected"' : null ?> <?= !in_array($key, $this->user->plan_settings->monitors_check_intervals ?? []) ? 'disabled="disabled"' : null ?>><?= $value ?></option>
                        <?php endforeach ?>
                    </select>
                    <small class="form-text text-muted"><?= l('game_server.input.check_interval_seconds_help') ?></small>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 bg-blue-50 my-3" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('game_server.input.advanced') ?>
                </button>

                <div class="collapse" data-parent="#game_server_create" id="advanced_container">
                    <div class="form-group">
                        <label for="timeout_seconds"><i class="fas fa-fw fa-sm fa-exclamation-triangle text-muted mr-1"></i> <?= l('game_server.input.timeout_seconds') ?></label>
                        <select id="timeout_seconds" name="timeout_seconds" class="custom-select" required="required">
                            <?php foreach($data->monitor_timeouts as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $data->values['timeout_seconds'] == $key ? 'selected="selected"' : null ?>><?= $value ?></option>
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

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.create') ?></button>
            </form>

        </div>
    </div>
</div>


<?php ob_start() ?>
<script>
    'use strict';

    document.querySelector('#type').addEventListener('change', event => {
        let selected_option = event.currentTarget.options[event.currentTarget.selectedIndex];
        let port = selected_option.getAttribute('data-port');
        let query_port = selected_option.getAttribute('data-query-port');

        document.querySelector('#port').value = port;
        document.querySelector('#query_port').value = query_port;
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

