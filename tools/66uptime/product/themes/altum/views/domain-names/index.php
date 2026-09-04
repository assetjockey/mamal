<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if($this->user->plan_settings->domain_names_limit != -1 && $data->total_domain_names > $this->user->plan_settings->domain_names_limit): ?>
        <div class="alert alert-danger">
            <i class="fas fa-fw fa-times-circle text-danger mr-2"></i> <?= sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), '<strong>' . $data->total_domain_names - $this->user->plan_settings->domain_names_limit, mb_strtolower(l('domain_names.title')) . '</strong>', '<a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '</a>') ?>
        </div>
    <?php endif ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-center mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-network-wired mr-1"></i> <?= l('domain_names.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('domain_names.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-lg-auto d-flex flex-wrap gap-3 d-print-none">
            <div>
                <?php if($this->user->plan_settings->domain_names_limit != -1 && $data->total_domain_names >= $this->user->plan_settings->domain_names_limit): ?>
                    <button type="button" class="btn btn-primary disabled" <?= get_plan_feature_limit_reached_info() ?>>
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('domain_names.create') ?>
                    </button>
                <?php else: ?>
                    <a href="<?= url('domain-name-create') ?>" class="btn btn-primary" data-toggle="tooltip" data-html="true" title="<?= get_plan_feature_limit_info($data->total_domain_names, $this->user->plan_settings->domain_names_limit, isset($data->filters) ? !$data->filters->has_applied_filters : true) ?>">
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('domain_names.create') ?>
                    </a>
                <?php endif ?>
            </div>

            <div>
                <a href="<?= url('domain-names-import') ?>" class="btn btn-outline-primary" data-toggle="tooltip" data-html="true" title="<?= l('domain_names_import.menu') ?>">
                    <i class="fas fa-fw fa-upload fa-sm"></i>
                </a>
            </div>

            <div>
                <div class="dropdown">
                    <button type="button" class="btn btn-light dropdown-toggle-simple <?= !empty($data->domain_names) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('domain-names?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('domain-names?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-code mr-2"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                        </a>
                        <a href="#" class="dropdown-item <?= $this->user->plan_settings->export->pdf ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->pdf ? 'onclick="event.preventDefault(); window.print();"' : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-pdf mr-2"></i> <?= sprintf(l('global.export_to'), 'PDF') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div>
                <div class="dropdown">
                    <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= !empty($data->domain_names) || $data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip data-html="true" title="<?= l('global.filters.tooltip') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-filter"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right filters-dropdown">
                        <div class="dropdown-header d-flex justify-content-between">
                            <span class="h6 m-0"><?= l('global.filters.header') ?></span>

                            <?php if($data->filters->has_applied_filters): ?>
                                <a href="<?= url(\Altum\Router::$original_request) ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
                            <?php endif ?>
                        </div>

                        <div class="dropdown-divider"></div>

                        <form action="" method="get" role="form">
                            <div class="form-group px-4">
                                <label for="filters_search" class="small"><?= l('global.filters.search') ?></label>
                                <input type="search" name="search" id="filters_search" class="form-control form-control-sm" value="<?= $data->filters->search ?>" />
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_search_by" class="small"><?= l('global.filters.search_by') ?></label>
                                <select name="search_by" id="filters_search_by" class="custom-select custom-select-sm">
                                    <option value="name" <?= $data->filters->search_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                    <option value="target" <?= $data->filters->search_by == 'target' ? 'selected="selected"' : null ?>><?= l('domain_name.input.target') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_is_enabled" class="small"><?= l('global.status') ?></label>
                                <select name="is_enabled" id="filters_is_enabled" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <option value="1" <?= isset($data->filters->filters['is_enabled']) && $data->filters->filters['is_enabled'] == '1' ? 'selected="selected"' : null ?>><?= l('global.active') ?></option>
                                    <option value="0" <?= isset($data->filters->filters['is_enabled']) && $data->filters->filters['is_enabled'] == '0' ? 'selected="selected"' : null ?>><?= l('global.disabled') ?></option>
                                </select>
                            </div>

                            <?php if(settings()->monitors_heartbeats->projects_is_enabled): ?>
                            <div class="form-group px-4">
                                <div class="d-flex justify-content-between">
                                    <label for="filters_project_id" class="small"><?= l('projects.project_id') ?></label>
                                    <a href="<?= url('project-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('global.create') ?></a>
                                </div>
                                <select name="project_id" id="filters_project_id" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <?php foreach($data->projects as $project_id => $project): ?>
                                        <option value="<?= $project_id ?>" <?= isset($data->filters->filters['project_id']) && $data->filters->filters['project_id'] == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <?php endif ?>

                            <div class="form-group px-4">
                                <label for="filters_datetime_field" class="small"><?= l('global.filters.datetime_field') ?></label>
                                <select name="datetime_field" id="filters_datetime_field" class="custom-select custom-select-sm" data-toggle-datetime-filters="#filters_datetime">
                                    <option value=""><?= l('global.none') ?></option>
                                    <option value="datetime" <?= $data->filters->datetime_field == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                    <option value="last_datetime" <?= $data->filters->datetime_field == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                    <option value="whois_start_datetime" <?= $data->filters->datetime_field == 'whois_start_datetime' ? 'selected="selected"' : null ?>><?= l('domain_name.whois_start_datetime') ?></option>
                                    <option value="whois_updated_datetime" <?= $data->filters->datetime_field == 'whois_updated_datetime' ? 'selected="selected"' : null ?>><?= l('domain_name.whois_updated_datetime') ?></option>
                                    <option value="whois_end_datetime" <?= $data->filters->datetime_field == 'whois_end_datetime' ? 'selected="selected"' : null ?>><?= l('domain_name.whois_end_datetime') ?></option>
                                    <option value="ssl_start_datetime" <?= $data->filters->datetime_field == 'ssl_start_datetime' ? 'selected="selected"' : null ?>><?= l('domain_name.ssl_start_datetime') ?></option>
                                    <option value="ssl_end_datetime" <?= $data->filters->datetime_field == 'ssl_end_datetime' ? 'selected="selected"' : null ?>><?= l('domain_name.ssl_end_datetime') ?></option>
                                </select>
                            </div>

                            <div id="filters_datetime">
                                <div class="form-group px-4">
                                    <label for="filters_datetime_start" class="small"><?= l('global.filters.datetime_start') ?></label>
                                    <input type="datetime-local" name="datetime_start" id="filters_datetime_start" class="form-control form-control-sm" value="<?= $data->filters->datetime_start ?? null ?>" />
                                </div>

                                <div class="form-group px-4">
                                    <label for="filters_datetime_end" class="small"><?= l('global.filters.datetime_end') ?></label>
                                    <input type="datetime-local" name="datetime_end" id="filters_datetime_end" class="form-control form-control-sm" value="<?= $data->filters->datetime_end ?? null ?>" />
                                </div>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_order_by" class="small"><?= l('global.filters.order_by') ?></label>
                                <select name="order_by" id="filters_order_by" class="custom-select custom-select-sm">
                                    <option value="domain_name_id" <?= $data->filters->order_by == 'domain_name_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                    <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                    <option value="last_datetime" <?= $data->filters->order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                    <option value="name" <?= $data->filters->order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                    <option value="target" <?= $data->filters->order_by == 'target' ? 'selected="selected"' : null ?>><?= l('domain_name.input.target') ?></option>
                                    <optgroup label="<?= l('domain_name.whois') ?>">
                                        <option value="whois_start_datetime" <?= $data->filters->order_by == 'whois_start_datetime' ? 'selected="selected"' : null ?>><?= l('domain_name.whois_start_datetime') ?></option>
                                        <option value="whois_updated_datetime" <?= $data->filters->order_by == 'whois_updated_datetime' ? 'selected="selected"' : null ?>><?= l('domain_name.whois_updated_datetime') ?></option>
                                        <option value="whois_end_datetime" <?= $data->filters->order_by == 'whois_end_datetime' ? 'selected="selected"' : null ?>><?= l('domain_name.whois_end_datetime') ?></option>
                                    </optgroup>
                                    <optgroup label="<?= l('domain_name.ssl') ?>">
                                        <option value="ssl_start_datetime" <?= $data->filters->order_by == 'ssl_start_datetime' ? 'selected="selected"' : null ?>><?= l('domain_name.ssl_start_datetime') ?></option>
                                        <option value="ssl_end_datetime" <?= $data->filters->order_by == 'ssl_end_datetime' ? 'selected="selected"' : null ?>><?= l('domain_name.ssl_end_datetime') ?></option>
                                    </optgroup>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_order_type" class="small"><?= l('global.filters.order_type') ?></label>
                                <select name="order_type" id="filters_order_type" class="custom-select custom-select-sm">
                                    <option value="ASC" <?= $data->filters->order_type == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                                    <option value="DESC" <?= $data->filters->order_type == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_results_per_page" class="small"><?= l('global.filters.results_per_page') ?></label>
                                <select name="results_per_page" id="filters_results_per_page" class="custom-select custom-select-sm">
                                    <?php foreach($data->filters->allowed_results_per_page as $key): ?>
                                        <option value="<?= $key ?>" <?= $data->filters->results_per_page == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4 mt-4">
                                <button type="submit" name="submit" class="btn btn-sm btn-primary btn-block"><?= l('global.submit') ?></button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <div>
                <button id="bulk_enable" type="button" class="btn btn-light" data-toggle="tooltip" title="<?= l('global.bulk_actions') ?>"><i class="fas fa-fw fa-sm fa-list"></i></button>

                <div id="bulk_group" class="btn-group d-none" role="group">
                    <div class="btn-group dropdown" role="group">
                        <button id="bulk_actions" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                            <?= l('global.bulk_actions') ?> <span id="bulk_counter" class="d-none"></span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="bulk_actions">
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#bulk_delete_modal"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
                        </div>
                    </div>

                    <button id="bulk_disable" type="button" class="btn btn-secondary" data-toggle="tooltip" title="<?= l('global.close') ?>"><i class="fas fa-fw fa-times"></i></button>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($data->domain_names)): ?>
        <form id="table" action="<?= SITE_URL . 'domain-names/bulk' ?>" method="post" role="form">
            <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
            <input type="hidden" name="type" value="" data-bulk-type />
            <input type="hidden" name="original_request" value="<?= base64_encode(\Altum\Router::$original_request) ?>" />
            <input type="hidden" name="original_request_query" value="<?= base64_encode(\Altum\Router::$original_request_query) ?>" />

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
                            <td data-bulk-table class="d-none">
                                <div class="custom-control custom-checkbox">
                                    <input id="selected_domain_name_id_<?= $row->domain_name_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->domain_name_id ?>" />
                                    <label class="custom-control-label" for="selected_domain_name_id_<?= $row->domain_name_id ?>"></label>
                                </div>
                            </td>

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
        </form>

        <div class="mt-3"><?= $data->pagination ?></div>
    <?php else: ?>


        <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
            'filters_get' => $data->filters->get ?? [],
            'name' => 'domain_names',
            'has_secondary_text' => true,
        ]); ?>

    <?php endif ?>
</div>

<?php require THEME_PATH . 'views/partials/js_bulk.php' ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/bulk_delete_modal.php'), 'modals'); ?>
