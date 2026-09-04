<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('monitors') ?>"><?= l('monitors.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('monitor_create.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 text-truncate"><i class="fas fa-fw fa-xs fa-server mr-1"></i> <?= l('monitor_create.header') ?></h1>
    <p></p>

    <div class="card">
        <div class="card-body">

            <form id="monitor_create" action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-group">
                    <label for="type"><i class="fas fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('monitor.input.type') ?></label>
                    <div class="row btn-group-toggle m-n2" data-toggle="buttons">
                        <div class="p-2 col-12 col-lg-4">
                            <label class="btn btn-light btn-block font-size-small mb-0 text-truncate <?=  $data->values['type'] == 'website' ? 'active"' : null?>">
                                <input type="radio" name="type" value="website" class="custom-control-input" <?=  $data->values['type'] == 'website' ? 'checked="checked"' : null?> required="required" />
                                <i class="fas fa-globe fa-fw fa-sm mr-1"></i> <?= l('monitor.input.type_website') ?>
                            </label>
                        </div>

                        <div class="p-2 col-12 col-lg-4">
                            <label class="btn btn-light btn-block font-size-small mb-0 text-truncate <?=  $data->values['type'] == 'ping' ? 'active"' : null?>">
                                <input type="radio" name="type" value="ping" class="custom-control-input" <?=  $data->values['type'] == 'ping' ? 'checked="checked"' : null?> required="required" />
                                <i class="fas fa-network-wired fa-fw fa-sm mr-1"></i> <?= l('monitor.input.type_ping') ?>
                            </label>
                        </div>

                        <div class="p-2 col-12 col-lg-4">
                            <label class="btn btn-light btn-block font-size-small mb-0 text-truncate <?=  $data->values['type'] == 'port' ? 'active"' : null?>">
                                <input type="radio" name="type" value="port" class="custom-control-input" <?=  $data->values['type'] == 'port' ? 'checked="checked"' : null?> required="required" />
                                <i class="fas fa-plug fa-fw fa-sm mr-1"></i> <?= l('monitor.input.type_port') ?>
                            </label>
                        </div>
                    </div>
                    <small id="type_website_help" data-type="website" class="form-text text-muted"><?= l('monitor.input.type_website_help') ?></small>
                    <small id="type_ping_help" data-type="ping" class="form-text text-muted"><?= l('monitor.input.type_ping_help') ?></small>
                    <small id="type_port_help" data-type="port" class="form-text text-muted"><?= l('monitor.input.type_port_help') ?></small>
                </div>

                <div class="form-group" data-type="website">
                    <label for="target_website_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('global.url') ?></label>
                    <input type="url" id="target_website_url" name="target" class="form-control <?= \Altum\Alerts::has_field_errors('target') ? 'is-invalid' : null ?>" value="<?= $data->values['target'] ?>" placeholder="<?= l('global.url_placeholder') ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('target') ?>
                </div>

                <div class="form-group" data-type="ping">
                    <label for="target_ping_host"><i class="fas fa-fw fa-sm fa-globe text-muted mr-1"></i> <?= l('global.host') ?></label>
                    <input type="text" id="target_ping_host" name="target" class="form-control" value="<?= $data->values['target'] ?>" placeholder="<?= l('global.host_placeholder') ?>" required="required" />
                </div>

                <div class="row" data-type="port">
                    <div class="col-lg-9">
                        <div class="form-group" data-type="port">
                            <label for="target_port_host"><i class="fas fa-fw fa-sm fa-globe text-muted mr-1"></i> <?= l('global.host') ?></label>
                            <input type="text" id="target_port_host" name="target" class="form-control" value="<?= $data->values['target'] ?>" placeholder="<?= l('global.host_placeholder') ?>" required="required" />
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group" data-type="port">
                            <label for="target_port_port"><i class="fas fa-fw fa-sm fa-dna text-muted mr-1"></i> <?= l('monitor.input.target_port') ?></label>
                            <input type="number" min="0" max="100000" id="target_port_port" name="port" class="form-control" value="<?= $data->values['port'] ?>" required="required" />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="d-flex flex-wrap flex-row justify-content-between">
                        <label><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= l('monitor.input.is_ok_notifications') ?></label>
                        <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                    </div>
                    <div class="mb-2"><small class="text-muted"><?= l('monitor.input.is_ok_notifications_help') ?></small></div>

                    <div class="row">
                        <?php foreach($data->notification_handlers as $notification_handler): ?>
                            <div class="col-12 col-lg-6">
                                <div class="custom-control custom-checkbox my-2">
                                    <input id="is_ok_notifications_<?= $notification_handler->notification_handler_id ?>" name="is_ok_notifications[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->values['is_ok_notifications'] ?? []) ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label" for="is_ok_notifications_<?= $notification_handler->notification_handler_id ?>">
                                        <span class="mr-1"><?= $notification_handler->name ?></span>
                                        <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="check_interval_seconds"><i class="fas fa-fw fa-sm fa-sync text-muted mr-1"></i> <?= l('monitor.input.check_interval_seconds') ?></label>
                    <select id="check_interval_seconds" name="check_interval_seconds" class="custom-select" required="required">
                        <?php foreach($data->monitor_check_intervals as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $data->values['check_interval_seconds'] == $key ? 'selected="selected"' : null ?> <?= !in_array($key, $this->user->plan_settings->monitors_check_intervals ?? []) ? 'disabled="disabled"' : null ?>><?= $value ?></option>
                        <?php endforeach ?>
                    </select>
                    <small class="form-text text-muted"><?= l('monitor.input.check_interval_seconds_help') ?></small>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 bg-blue-50 my-3" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('monitor.input.advanced') ?>
                </button>

                <div class="collapse" data-parent="#monitor_create" id="advanced_container">
                    <div class="form-group">
                        <div><i class="fas fa-fw fa-sm fa-map-marked-alt text-muted mr-1"></i><?= l('monitor.input.ping_servers_ids') ?></div>
                        <div><small class="form-text text-muted"><?= l('monitor.input.ping_servers_ids_help') ?></small></div>

                        <div class="row">
                            <?php foreach($data->ping_servers as $ping_server): ?>
                                <div class="col-12 col-lg-6">
                                    <div class="custom-control custom-checkbox my-2">
                                        <input id="ping_server_id_<?= $ping_server->ping_server_id ?>" name="ping_servers_ids[]" value="<?= $ping_server->ping_server_id ?>" type="checkbox" class="custom-control-input" <?= in_array($ping_server->ping_server_id, $data->values['ping_servers_ids']) ? 'checked="checked"' : null ?> <?= !in_array($ping_server->ping_server_id, $this->user->plan_settings->monitors_ping_servers ?? []) ? 'disabled="disabled"' : null ?>>
                                        <label class="custom-control-label d-flex align-items-center" for="ping_server_id_<?= $ping_server->ping_server_id ?>">
                                            <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($ping_server->country_code) . '.svg' ?>" class="img-fluid icon-favicon mr-1" />
                                            <span class="mr-1"><?= $ping_server->city_name ?></span>
                                            <small class="badge badge-light badge-pill"><?= $ping_server->name ?></small>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="timeout_seconds"><i class="fas fa-fw fa-sm fa-exclamation-triangle text-muted mr-1"></i> <?= l('monitor.input.timeout_seconds') ?></label>
                        <select id="timeout_seconds" name="timeout_seconds" class="custom-select" required="required">
                            <?php foreach($data->monitor_timeouts as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $data->values['timeout_seconds'] == $key ? 'selected="selected"' : null ?>><?= $value ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <?php if(settings()->monitors_heartbeats->monitors_ipv6_ping_is_enabled): ?>
                        <div class="form-group" data-type="ping">
                            <label for="target_ping_ipv"><i class="fas fa-fw fa-sm fa-ethernet text-muted mr-1"></i> <?= l('monitor.input.ping_ipv') ?></label>
                            <select id="target_ping_ipv" name="ping_ipv" class="custom-select" required="required">
                                <option value="ipv4" <?= $data->values['ping_ipv'] == 'ipv4' ? 'selected="selected"' : null ?>>IPv4</option>
                                <option value="ipv4" <?= $data->values['ping_ipv'] == 'ipv6' ? 'selected="selected"' : null ?>>IPv6</option>
                            </select>
                        </div>
                    <?php endif ?>

                    <div class="form-group custom-control custom-switch" data-type="website">
                        <input id="cache_buster_is_enabled" name="cache_buster_is_enabled" type="checkbox" class="custom-control-input" <?= $data->values['cache_buster_is_enabled'] ? 'checked="checked"' : null?>>
                        <label class="custom-control-label" for="cache_buster_is_enabled"><?= l('monitor.input.cache_buster_is_enabled') ?></label>
                        <small class="form-text text-muted"><?= l('monitor.input.cache_buster_is_enabled_help') ?></small>
                    </div>

                    <div class="form-group custom-control custom-switch" data-type="website">
                        <input id="verify_ssl_is_enabled" name="verify_ssl_is_enabled" type="checkbox" class="custom-control-input" <?= $data->values['verify_ssl_is_enabled'] ? 'checked="checked"' : null?>>
                        <label class="custom-control-label" for="verify_ssl_is_enabled"><?= l('monitor.input.verify_ssl_is_enabled') ?></label>
                        <small class="form-text text-muted"><?= l('monitor.input.verify_ssl_is_enabled_help') ?></small>
                    </div>

                    <?php if(settings()->monitors_heartbeats->email_reports_is_enabled): ?>
                        <div <?= $this->user->plan_settings->email_reports_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                            <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->email_reports_is_enabled ? null : 'container-disabled' ?>">
                                <input id="email_reports_is_enabled" name="email_reports_is_enabled" type="checkbox" class="custom-control-input" <?= $data->values['email_reports_is_enabled'] ? 'checked="checked"' : null?>>
                                <label class="custom-control-label" for="email_reports_is_enabled"><?= l('heartbeat.input.email_reports_is_enabled') ?></label>
                                <small class="form-text text-muted"><?= l('monitor.input.email_reports_is_enabled_help') ?></small>
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
                                    <option value="<?= $project_id ?>" <?= $data->values['project_id'] == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                                <?php endforeach ?>
                            </select>
                            <small class="form-text text-muted"><?= l('projects.project_id_help') ?></small>
                        </div>
                    <?php endif ?>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 bg-blue-50 my-3" type="button" data-toggle="collapse" data-target="#custom_request_container" aria-expanded="false" aria-controls="custom_request_container" data-type="website">
                    <i class="fas fa-fw fa-share fa-sm mr-1"></i> <?= l('monitor.input.custom_request') ?>
                </button>

                <div class="collapse" data-parent="#monitor_create" id="custom_request_container">
                    <div data-type="website">

                        <div class="form-group">
                            <label for="request_method"><?= l('monitor.input.request_method') ?></label>
                            <select id="request_method" name="request_method" class="custom-select" required="required">
                                <option value="GET" <?= $data->values['request_method'] == 'GET' ? 'selected="selected"' : null ?>>GET</option>
                                <option value="POST" <?= $data->values['request_method'] == 'POST' ? 'selected="selected"' : null ?>>POST</option>
                                <option value="HEAD" <?= $data->values['request_method'] == 'HEAD' ? 'selected="selected"' : null ?>>HEAD</option>
                                <option value="OPTIONS" <?= $data->values['request_method'] == 'OPTIONS' ? 'selected="selected"' : null ?>>OPTIONS</option>
                                <option value="PUT" <?= $data->values['request_method'] == 'PUT' ? 'selected="selected"' : null ?>>PUT</option>
                                <option value="PATCH" <?= $data->values['request_method'] == 'PATCH' ? 'selected="selected"' : null ?>>PATCH</option>
                            </select>
                        </div>

                        <div class="form-group custom-control custom-switch" data-request-method-follow-redirects>
                            <input id="follow_redirects" name="follow_redirects" type="checkbox" class="custom-control-input" <?= $data->values['follow_redirects'] ? 'checked="checked"' : null?>>
                            <label class="custom-control-label" for="follow_redirects"><?= l('monitor.input.follow_redirects') ?></label>
                            <small class="form-text text-muted"><?= l('monitor.input.follow_redirects_help') ?></small>
                        </div>

                        <div class="form-group" data-request-method-request-body>
                            <label for="request_body"><?= l('monitor.input.request_body') ?></label>
                            <textarea id="request_body" name="request_body" class="form-control" maxlength="10000"><?= $data->values['request_body'] ?></textarea>
                            <small class="form-text text-muted"><?= l('monitor.input.request_body_help') ?></small>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-lg-5">
                                <label for="request_basic_auth_username"><?= l('monitor.input.request_basic_auth_username') ?></label>
                                <input type="text" id="request_basic_auth_username" name="request_basic_auth_username" class="form-control" value="<?= $data->values['request_basic_auth_username'] ?>" maxlength="256" autocomplete="off" />
                            </div>

                            <div class="form-group col-lg-5">
                                <label for="request_basic_auth_password"><?= l('monitor.input.request_basic_auth_password') ?></label>
                                <input type="text" id="request_basic_auth_password" name="request_basic_auth_password" class="form-control" value="<?= $data->values['request_basic_auth_password'] ?>" maxlength="256" autocomplete="off" />
                            </div>
                        </div>

                        <label><?= l('monitor.input.request_headers') ?></label>
                        <div id="request_headers">
                            <?php foreach($data->values['request_headers'] as $key => $request_header): ?>
                                <div class="form-row">
                                    <div class="form-group col-lg-5">
                                        <input type="text" name="request_header_name[<?= $key ?>]" class="form-control" value="<?= $request_header->name ?>" placeholder="<?= l('monitor.input.request_header_name') ?>" />
                                    </div>

                                    <div class="form-group col-lg-5">
                                        <input type="text" name="request_header_value[<?= $key ?>]" class="form-control" value="<?= $request_header->value ?>" placeholder="<?= l('monitor.input.request_header_value') ?>" />
                                    </div>

                                    <div class="form-group col-lg-2 text-center">
                                        <button type="button" data-remove="request" class="btn btn-block btn-outline-danger" title="<?= l('global.delete') ?>"><i class="fas fa-fw fa-times"></i></button>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                        <div class="mb-3">
                            <button data-add="request" type="button" class="btn btn-sm btn-outline-success"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('monitor.input.request_header_add') ?></button>
                        </div>

                    </div>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 bg-blue-50 my-3" type="button" data-toggle="collapse" data-target="#custom_response_container" aria-expanded="false" aria-controls="custom_response_container" data-type="website" data-request-method-response-button>
                    <i class="fas fa-fw fa-reply fa-sm mr-1"></i> <?= l('monitor.input.custom_response') ?>
                </button>

                <div class="collapse" data-parent="#monitor_create" id="custom_response_container" data-request-method-response-container>
                    <div data-type="website">
                        <div class="alert alert-info"><?= l('monitor.input.custom_response_help') ?></div>

                        <div class="form-group">
                            <label for="response_status_code"><?= l('monitor.input.response_status_code') ?></label>
                            <input type="text" id="response_status_code" name="response_status_code" class="form-control" value="<?= is_array($data->values['response_status_code']) ? implode(',', $data->values['response_status_code']) : $data->values['response_status_code'] ?>" required="required" />
                            <small class="form-text text-muted"><?= l('monitor.input.response_status_code_help') ?></small>
                        </div>

                        <div class="form-group">
                            <label for="response_body"><?= l('monitor.input.response_body') ?></label>
                            <textarea id="response_body" name="response_body" maxlength="10000" class="form-control"><?= $data->values['response_body'] ?></textarea>
                            <small class="form-text text-muted"><?= l('monitor.input.response_body_help') ?></small>
                        </div>

                        <label><?= l('monitor.input.response_headers') ?></label>
                        <div id="response_headers">
                            <?php foreach($data->values['response_headers'] as $key => $response_header): ?>
                                <div class="form-row">
                                    <div class="form-group col-lg-5">
                                        <input type="text" name="response_header_name[<?= $key ?>]" class="form-control" value="<?= $response_header->name ?>" placeholder="<?= l('monitor.input.response_header_name') ?>" />
                                    </div>

                                    <div class="form-group col-lg-5">
                                        <input type="text" name="response_header_value[<?= $key ?>]" class="form-control" value="<?= $response_header->value ?>" placeholder="<?= l('monitor.input.response_header_value') ?>" />
                                    </div>

                                    <div class="form-group col-lg-2 text-center">
                                        <button type="button" data-remove="response" class="btn btn-block btn-outline-danger" title="<?= l('global.delete') ?>"><i class="fas fa-fw fa-times"></i></button>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                        <div class="mb-3">
                            <button data-add="response" type="button" class="btn btn-sm btn-outline-success"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('monitor.input.request_header_add') ?></button>
                        </div>

                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.create') ?></button>
            </form>

        </div>
    </div>
</div>

<template id="template_request_header">
    <div class="form-row">
        <div class="form-group col-lg-5">
            <input type="text" name="request_header_name[]" class="form-control" value="" maxlength="128" placeholder="<?= l('monitor.input.request_header_name') ?>" />
        </div>

        <div class="form-group col-lg-5">
            <input type="text" name="request_header_value[]" class="form-control" value="" maxlength="256" placeholder="<?= l('monitor.input.request_header_value') ?>" />
        </div>

        <div class="form-group col-lg-2 text-center">
            <button type="button" data-remove="request" class="btn btn-block btn-outline-danger" title="<?= l('global.delete') ?>"><i class="fas fa-fw fa-times"></i></button>
        </div>
    </div>
</template>

<template id="template_response_header">
    <div class="form-row">
        <div class="form-group col-lg-5">
            <input type="text" name="response_header_name[]" class="form-control" value="" maxlength="128" placeholder="<?= l('monitor.input.response_header_name') ?>" />
        </div>

        <div class="form-group col-lg-5">
            <input type="text" name="response_header_value[]" class="form-control" value="" maxlength="256" placeholder="<?= l('monitor.input.response_header_value') ?>" />
        </div>

        <div class="form-group col-lg-2 text-center">
            <button type="button" data-remove="response" class="btn btn-block btn-outline-danger" title="<?= l('global.delete') ?>"><i class="fas fa-fw fa-times"></i></button>
        </div>
    </div>
</template>

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

    type_handler('input[name="type"]', 'data-type');
    document.querySelector('input[name="type"]') && document.querySelectorAll('input[name="type"]').forEach(element => element.addEventListener('change', () => { type_handler('input[name="type"]', 'data-type'); request_method_handler(); }));

    /* Handle request method change */
    let request_method_handler = () => {
        let request_method_value = document.querySelector('select[name="request_method"]').value;
        if(document.querySelector('input[name="type"]:checked').value !== 'website') {
            request_method_value = 'HEAD';
        }

        switch(request_method_value) {
            case 'POST':
            case 'PUT':
            case 'PATCH':
                document.querySelector('[data-request-method-request-body]').classList.remove('d-none');
                document.querySelector('[data-request-method-follow-redirects]').classList.remove('d-none');
                document.querySelector('[data-request-method-response-container]').classList.remove('d-none');
                document.querySelector('[data-request-method-response-button]').classList.remove('d-none');
                break;

            case 'HEAD':
                document.querySelector('[data-request-method-request-body]').classList.add('d-none');
                document.querySelector('[data-request-method-follow-redirects]').classList.add('d-none');
                document.querySelector('[data-request-method-response-container]').classList.add('d-none');
                document.querySelector('[data-request-method-response-button]').classList.add('d-none');
                break;

            case 'OPTIONS':
            case 'GET':
                document.querySelector('[data-request-method-request-body]').classList.add('d-none');
                document.querySelector('[data-request-method-follow-redirects]').classList.remove('d-none');
                document.querySelector('[data-request-method-response-container]').classList.remove('d-none');
                document.querySelector('[data-request-method-response-button]').classList.remove('d-none');
                break;
        }
    }
    document.querySelector('select[name="request_method"]').addEventListener('change', request_method_handler);
    request_method_handler();

    /* add new request header */
    let header_add = event => {
        let type = event.currentTarget.getAttribute('data-add');

        let clone = document.querySelector(`#template_${type}_header`).content.cloneNode(true);

        let request_headers_count = document.querySelectorAll(`#${type}_headers .form-row`).length;

        if(request_headers_count > 20) {
            return;
        }

        clone.querySelector(`input[name="${type}_header_name[]"`).setAttribute('name', `${type}_header_name[${request_headers_count}]`);
        clone.querySelector(`input[name="${type}_header_value[]"`).setAttribute('name', `${type}_header_value[${request_headers_count}]`);

        document.querySelector(`#${type}_headers`).appendChild(clone);

        header_remove_initiator();
    };

    document.querySelectorAll('[data-add]').forEach(element => {
        element.addEventListener('click', header_add);
    })


    /* remove request header */
    let header_remove = event => {
        event.currentTarget.closest('.form-row').remove();
    };

    let header_remove_initiator = () => {
        document.querySelectorAll('#request_headers [data-remove], #response_headers [data-remove]').forEach(element => {
            element.removeEventListener('click', header_remove);
            element.addEventListener('click', header_remove)
        })
    };

    header_remove_initiator();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
