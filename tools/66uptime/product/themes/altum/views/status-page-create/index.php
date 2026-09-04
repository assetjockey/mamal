<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
<nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('status-pages') ?>"><?= l('status_pages.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('status_page_create.breadcrumb') ?></li>
        </ol>
    </nav>
<?php endif ?>

    <h1 class="h4 text-truncate"><i class="fas fa-fw fa-xs fa-wifi mr-1"></i> <?= l('status_page_create.header') ?></h1>
    <p></p>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <?php if(count($data->domains) && (settings()->status_pages->domains_is_enabled || settings()->status_pages->additional_domains_is_enabled)): ?>
                    <div class="form-group">
                        <label for="domain_id"><i class="fas fa-fw fa-sm fa-globe text-muted mr-1"></i> <?= l('status_page.domain_id') ?></label>
                        <select id="domain_id" name="domain_id" class="custom-select">
                            <?php if(settings()->status_pages->main_domain_is_enabled || \Altum\Authentication::is_admin()): ?>
                                <option value=" "><?= remove_url_protocol_from_url(SITE_URL) ?></option>
                            <?php endif ?>

                            <?php foreach($data->domains as $row): ?>
                                <option value="<?= $row->domain_id ?>" data-type="<?= $row->type ?>" <?= $data->values['domain_id'] && $data->values['domain_id'] == $row->domain_id ? 'selected="selected"' : null ?>><?= remove_url_protocol_from_url($row->url) ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="form-text text-muted"><?= l('status_page.domain_id_help') ?></small>
                    </div>

                    <div id="is_main_status_page_wrapper" class="form-group custom-control custom-switch">
                        <input id="is_main_status_page" name="is_main_status_page" type="checkbox" class="custom-control-input" <?= $data->values['is_main_status_page'] ? 'checked="checked"' : null ?>>
                        <label class="custom-control-label" for="is_main_status_page"><?= l('status_page.is_main_status_page') ?></label>
                        <small class="form-text text-muted"><?= l('status_page.is_main_status_page_help') ?></small>
                    </div>

                    <div <?= $this->user->plan_settings->custom_url_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                        <div class="<?= $this->user->plan_settings->custom_url_is_enabled ? null : 'container-disabled' ?>">
                            <div class="form-group">
                                <label for="url"><i class="fas fa-fw fa-sm fa-bolt text-muted mr-1"></i> <?= l('status_page.url') ?></label>
                                <input type="text" id="url" name="url" class="form-control <?= \Altum\Alerts::has_field_errors('url') ? 'is-invalid' : null ?>" value="<?= $data->values['url'] ?>" maxlength="<?= ($this->user->plan_settings->url_maximum_characters ?? 64) ?>" onchange="update_this_value(this, get_slug)" onkeyup="update_this_value(this, get_slug)" placeholder="<?= l('global.url_slug_placeholder') ?>" />
                                <?= \Altum\Alerts::output_field_error('url') ?>
                                <small class="form-text text-muted"><?= l('status_page.url_help') ?></small>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div <?= $this->user->plan_settings->custom_url_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                        <div class="<?= $this->user->plan_settings->custom_url_is_enabled ? null : 'container-disabled' ?>">
                            <label for="url"><i class="fas fa-fw fa-sm fa-bolt text-muted mr-1"></i> <?= l('status_page.url') ?></label>
                            <div class="mb-3">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><?= remove_url_protocol_from_url(SITE_URL) ?></span>
                                    </div>
                                    <input type="text" id="url" name="url" class="form-control <?= \Altum\Alerts::has_field_errors('url') ? 'is-invalid' : null ?>" value="<?= $data->values['url'] ?>" maxlength="<?= ($this->user->plan_settings->url_maximum_characters ?? 64) ?>" onchange="update_this_value(this, get_slug)" onkeyup="update_this_value(this, get_slug)" placeholder="<?= l('global.url_slug_placeholder') ?>" />
                                    <?= \Altum\Alerts::output_field_error('url') ?>
                                </div>
                                <small class="form-text text-muted"><?= l('status_page.url_help') ?></small>
                            </div>
                        </div>
                    </div>
                <?php endif ?>

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" maxlength="256" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('global.description') ?></label>
                    <input type="text" id="description" name="description" class="form-control" value="<?= $data->values['description'] ?>" maxlength="256" />
                    <small class="form-text text-muted"><?= l('status_page.description_help') ?></small>
                </div>

                <div class="form-group">
                    <div class="d-flex flex-wrap flex-row justify-content-between">
                        <label for="monitors_ids"><i class="fas fa-fw fa-sm fa-server text-muted mr-1"></i> <?= l('status_page.monitors_ids') ?></label>
                        <a href="<?= url('monitor-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('global.create') ?></a>
                    </div>
                    <select id="monitors_ids" name="monitors_ids[]" class="custom-select" multiple="multiple">
                        <?php foreach($data->monitors as $monitor): ?>
                            <option value="<?= $monitor->monitor_id ?>" <?= in_array($monitor->monitor_id, $data->values['monitors_ids'])  ? 'selected="selected"' : null ?>>
                                <?= $monitor->name . ' - ' . $monitor->target ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                    <small class="form-text text-muted"><?= l('status_page.monitors_ids_help') ?></small>
                </div>

                <div class="form-group">
                    <div class="d-flex flex-wrap flex-row justify-content-between">
                        <label for="heartbeats_ids"><i class="fas fa-fw fa-sm fa-heartbeat text-muted mr-1"></i> <?= l('status_page.heartbeats_ids') ?></label>
                        <a href="<?= url('heartbeat-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('global.create') ?></a>
                    </div>
                    <select id="heartbeats_ids" name="heartbeats_ids[]" class="custom-select" multiple="multiple">
                        <?php foreach($data->heartbeats as $heartbeat): ?>
                            <option value="<?= $heartbeat->heartbeat_id ?>" <?= in_array($heartbeat->heartbeat_id, $data->values['heartbeats_ids'])  ? 'selected="selected"' : null ?>>
                                <?= $heartbeat->name ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                    <small class="form-text text-muted"><?= l('status_page.heartbeats_ids_help') ?></small>
                </div>

                <p><small class="form-text text-muted"><i class="fas fa-fw fa-sm fa-info-circle"></i> <?= l('status_page_create.info') ?></small></p>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.create') ?></button>
            </form>

        </div>
    </div>
</div>


<?php ob_start() ?>
<script>
    'use strict';

/* Is main status_page handler */
    let is_main_status_page_handler = () => {
        if(document.querySelector('#is_main_status_page').checked) {
            document.querySelector('#url').setAttribute('disabled', 'disabled');
        } else {
            document.querySelector('#url').removeAttribute('disabled');
        }
    }

    document.querySelector('#is_main_status_page') && document.querySelector('#is_main_status_page').addEventListener('change', is_main_status_page_handler);

    /* Domain Id Handler */
    let domain_id_handler = () => {
        let domain_id = document.querySelector('select[name="domain_id"]').value;

        if(document.querySelector(`select[name="domain_id"] option[value="${domain_id}"]`).getAttribute('data-type') == '0') {
            document.querySelector('#is_main_status_page_wrapper').classList.remove('d-none');
        } else {
            document.querySelector('#is_main_status_page_wrapper').classList.add('d-none');
            document.querySelector('#is_main_status_page').checked = false;
        }

        is_main_status_page_handler();
    }

    domain_id_handler();

    document.querySelector('select[name="domain_id"]') && document.querySelector('select[name="domain_id"]').addEventListener('change', domain_id_handler);
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
