<?php defined('ALTUMCODE') || die() ?>

<?php if(!settings()->transfers->transfer_requests_is_enabled): ?>
    <div class="alert alert-info">
        <i class="fas fa-fw fa-info-circle mr-1"></i>
        <?= sprintf(l('global.info_message.admin_feature_disabled'), url('admin/settings/transfers')) ?>
    </div>
<?php endif ?>

<div class="d-flex flex-column flex-md-row justify-content-between mb-4">
    <h1 class="h3 mb-3 mb-md-0 text-truncate"><i class="fas fa-fw fa-xs fa-inbox text-primary-900 mr-2"></i> <?= l('admin_transfer_requests.header') ?></h1>

    <div class="d-flex position-relative d-print-none">
        <div>
            <div class="dropdown">
                <button type="button" class="btn btn-gray-300 dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                    <i class="fas fa-fw fa-sm fa-download"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-right d-print-none">
                    <a href="<?= url('admin/transfer-requests?' . $data->filters->get_get() . '&export=csv') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                        <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                    </a>
                    <a href="<?= url('admin/transfer-requests?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
                        <i class="fas fa-fw fa-sm fa-file-code mr-2"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                    </a>
                    <a href="#" class="dropdown-item <?= $this->user->plan_settings->export->pdf ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->pdf ? 'onclick="event.preventDefault(); window.print();"' : get_plan_feature_disabled_info() ?>>
                        <i class="fas fa-fw fa-sm fa-file-pdf mr-2"></i> <?= sprintf(l('global.export_to'), 'PDF') ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="ml-3">
            <div class="dropdown">
                <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-secondary' : 'btn-gray-300' ?> filters-button dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip data-html="true" title="<?= l('global.filters.tooltip') ?>" data-tooltip-hide-on-click>
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
                                <option value="url" <?= $data->filters->search_by == 'url' ? 'selected="selected"' : null ?>><?= l('transfer.url') ?></option>
                                <option value="name" <?= $data->filters->search_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                <option value="description" <?= $data->filters->search_by == 'description' ? 'selected="selected"' : null ?>><?= l('global.description') ?></option>
                            </select>
                        </div>

                        <?php if(settings()->transfers->projects_is_enabled): ?>
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

                        <?php if(settings()->transfers->domains_is_enabled): ?>
                            <div class="form-group px-4">
                                <div class="d-flex justify-content-between">
                                    <label for="filters_domain_id" class="small"><?= l('domains.domain_id') ?></label>
                                    <a href="<?= url('domain-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('global.create') ?></a>
                                </div>
                                <select name="domain_id" id="filters_domain_id" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <?php foreach($data->domains as $domain_id => $domain): ?>
                                        <option value="<?= $domain_id ?>" <?= isset($data->filters->filters['domain_id']) && $data->filters->filters['domain_id'] == $domain_id ? 'selected="selected"' : null ?>><?= $domain->host ?></option>
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
                                <option value="expiration_datetime" <?= $data->filters->datetime_field == 'expiration_datetime' ? 'selected="selected"' : null ?>><?= l('transfer_requests.expiration_datetime') ?></option>
                                <option value="last_submitted_datetime" <?= $data->filters->datetime_field == 'last_submitted_datetime' ? 'selected="selected"' : null ?>><?= l('transfer_requests.last_submitted_datetime') ?></option>
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
                                <option value="transfer_request_id" <?= $data->filters->order_by == 'transfer_request_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $data->filters->order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="expiration_datetime" <?= $data->filters->order_by == 'expiration_datetime' ? 'selected="selected"' : null ?>><?= l('transfer_requests.expiration_datetime') ?></option>
                                <option value="last_submitted_datetime" <?= $data->filters->order_by == 'last_submitted_datetime' ? 'selected="selected"' : null ?>><?= l('transfer_requests.last_submitted_datetime') ?></option>
                                <option value="pageviews" <?= $data->filters->order_by == 'pageviews' ? 'selected="selected"' : null ?>><?= l('transfer.pageviews') ?></option>
                                <option value="url" <?= $data->filters->order_by == 'url' ? 'selected="selected"' : null ?>><?= l('transfer.url') ?></option>
                                <option value="name" <?= $data->filters->order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                <option value="total_submissions" <?= $data->filters->order_by == 'total_submissions' ? 'selected="selected"' : null ?>><?= l('transfer_requests.total_submissions') ?></option>
                                <option value="total_files" <?= $data->filters->order_by == 'total_files' ? 'selected="selected"' : null ?>><?= l('transfer.total_files') ?></option>
                                <option value="total_size" <?= $data->filters->order_by == 'total_size' ? 'selected="selected"' : null ?>><?= l('transfer.total_size') ?></option>
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

        <div class="ml-3">
            <button id="bulk_enable" type="button" class="btn btn-gray-300" data-toggle="tooltip" title="<?= l('global.bulk_actions') ?>"><i class="fas fa-fw fa-sm fa-list"></i></button>

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

<?= \Altum\Alerts::output_alerts() ?>

<form id="table" action="<?= SITE_URL . 'admin/transfer-requests/bulk' ?>" method="post" role="form">
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
                <th><?= l('global.user') ?></th>
                <th><?= l('transfer_requests.transfer_request') ?></th>
                <th><?= l('transfer_requests.table.files') ?></th>
                <th><?= l('transfer_requests.table.pageviews') ?></th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($data->transfer_requests as $row): ?>
                <tr>
                    <td data-bulk-table class="d-none">
                        <div class="custom-control custom-checkbox">
                            <input id="selected_transfer_request_id_<?= $row->transfer_request_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->transfer_request_id ?>" />
                            <label class="custom-control-label" for="selected_transfer_request_id_<?= $row->transfer_request_id ?>"></label>
                        </div>
                    </td>

                    <td class="text-nowrap">
                        <?php if($row->user_name): ?>
                            <div class="d-flex">
                                <a href="<?= url('admin/user-view/' . $row->user_id) ?>">
                                    <img src="<?= get_user_avatar($row->user_avatar, $row->user_email) ?>" referrerpolicy="no-referrer" loading="lazy" class="user-avatar rounded-circle mr-3" alt="" />
                                </a>

                                <div class="d-flex flex-column">
                                    <div>
                                        <a href="<?= url('admin/user-view/' . $row->user_id) ?>"><?= $row->user_name ?></a>
                                    </div>

                                    <span class="text-muted small"><?= $row->user_email ?></span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-muted">
                                <?= l('global.none') ?>
                            </div>
                        <?php endif ?>
                    </td>

                    <td class="text-nowrap">
                        <span title="<?= $row->name ?>"><?= string_truncate($row->name, 32) ?></span> <a href="<?= url('transfer-request-redirect/' . $row->transfer_request_id) ?>" rel="noreferrer" target="_blank"><i class="fas fa-fw fa-xs fa-external-link-alt text-muted ml-1"></i></a>
                    </td>

                    <td class="text-nowrap">
                        <div class="d-flex flex-column">
                            <div class="badge badge-light mb-1"><?= sprintf(l('transfer_requests.total_submissions_x'), nr($row->total_submissions)) ?></div>

                            <span class="badge badge-info">
                                <?= nr($row->total_files) ?> • <?= get_formatted_bytes($row->total_size) ?>
                            </span>
                        </div>
                    </td>

                    <td class="text-nowrap">
                        <a href="<?= url('transfer-request-statistics/' . $row->transfer_request_id) ?>" class="badge badge-light text-decoration-none" data-toggle="tooltip" title="<?= l('transfer.pageviews') ?>">
                            <i class="fas fa-fw fa-sm fa-chart-bar mr-1"></i> <?= nr($row->pageviews) ?>
                        </a>
                    </td>

                    <td class="text-nowrap">
                        <div class="d-flex align-items-center">

                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('transfer_requests.expiration_datetime'), ($row->expiration_datetime ? '<br />' . \Altum\Date::get($row->expiration_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->expiration_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->expiration_datetime) . ')</small>' : '<br />' . l('transfer_requests.expiration_datetime_null')) ?>">
                                <i class="fas fa-fw fa-hourglass-half text-muted"></i>
                            </span>


                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                <i class="fas fa-fw fa-calendar text-muted"></i>
                            </span>

                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                <i class="fas fa-fw fa-history text-muted"></i>
                            </span>

                            <?php if($row->settings->password): ?>
                                <span class="mr-2" data-toggle="tooltip" title="<?= l('global.password') . ': ' . l('global.yes') ?>">
                                    <i class="fas fa-fw fa-lock text-muted"></i>
                                </span>
                            <?php else: ?>
                                <span class="mr-2" data-toggle="tooltip" title="<?= l('global.password') . ': ' . l('global.no') ?>">
                                    <i class="fas fa-fw fa-lock-open text-muted"></i>
                                </span>
                            <?php endif ?>
                        </div>
                    </td>

                    <td>
                        <div class="d-flex justify-content-end">
                            <?= include_view(THEME_PATH . 'views/admin/transfer-requests/admin_transfer_request_dropdown_button.php', ['id' => $row->transfer_request_id, 'resource_name' => $row->name]) ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach ?>

            </tbody>
        </table>
    </div>
</form>

<div class="mt-3"><?= $data->pagination ?></div>

<?php require THEME_PATH . 'views/partials/js_bulk.php' ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/bulk_delete_modal.php'), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_url.php', [
    'name' => 'transfer_request',
    'resource_id' => 'transfer_request_id',
    'has_dynamic_resource_name' => true,
    'path' => 'admin/transfer-requests/delete/'
]), 'modals'); ?>
