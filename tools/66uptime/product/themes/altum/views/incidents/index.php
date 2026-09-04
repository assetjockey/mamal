<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-center mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-exclamation-circle mr-1"></i> <?= l('incidents.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('incidents.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-lg-auto d-flex flex-wrap gap-3 d-print-none">
            <div class="">
                <div class="dropdown">
                    <button type="button" class="btn btn-light dropdown-toggle-simple <?= !empty($data->incidents) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('incidents?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('incidents?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
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
                    <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= !empty($data->incidents) || $data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip data-html="true" title="<?= l('global.filters.tooltip') ?>" data-tooltip-hide-on-click>
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
                                    <option value="comment" <?= $data->filters->search_by == 'comment' ? 'selected="selected"' : null ?>><?= l('incidents.comment') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_datetime_field" class="small"><?= l('global.filters.datetime_field') ?></label>
                                <select name="datetime_field" id="filters_datetime_field" class="custom-select custom-select-sm" data-toggle-datetime-filters="#filters_datetime">
                                    <option value=""><?= l('global.none') ?></option>
                                    <option value="start_datetime" <?= $data->filters->datetime_field == 'start_datetime' ? 'selected="selected"' : null ?>><?= l('incidents.start_datetime') ?></option>
                                    <option value="end_datetime" <?= $data->filters->datetime_field == 'end_datetime' ? 'selected="selected"' : null ?>><?= l('incidents.end_datetime') ?></option>
                                    <option value="last_failed_check_datetime" <?= $data->filters->datetime_field == 'last_failed_check_datetime' ? 'selected="selected"' : null ?>><?= l('incidents.last_failed_check_datetime') ?></option>
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
                                    <option value="incident_id" <?= $data->filters->order_by == 'incident_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                    <option value="start_datetime" <?= $data->filters->order_by == 'start_datetime' ? 'selected="selected"' : null ?>><?= l('incidents.start_datetime') ?></option>
                                    <option value="end_datetime" <?= $data->filters->order_by == 'end_datetime' ? 'selected="selected"' : null ?>><?= l('incidents.end_datetime') ?></option>
                                    <option value="last_failed_check_datetime" <?= $data->filters->order_by == 'last_failed_check_datetime' ? 'selected="selected"' : null ?>><?= l('incidents.last_failed_check_datetime') ?></option>
                                    <option value="failed_checks" <?= $data->filters->order_by == 'failed_checks' ? 'selected="selected"' : null ?>><?= l('incidents.failed_checks') ?></option>
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

    <?php if (!empty($data->incidents)): ?>
        <form id="table" action="<?= SITE_URL . 'incidents/bulk' ?>" method="post" role="form">
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
                        <th><?= l('global.status') ?></th>
                        <th><?= l('incidents.start_datetime') ?></th>
                        <th><?= l('incidents.end_datetime') ?></th>
                        <th><?= l('incidents.length') ?></th>
                        <th><?= l('incidents.comment') ?></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach($data->incidents as $row): ?>

                        <tr>
                            <td data-bulk-table class="d-none">
                                <div class="custom-control custom-checkbox">
                                    <input id="selected_incident_id_<?= $row->incident_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->incident_id ?>" />
                                    <label class="custom-control-label" for="selected_incident_id_<?= $row->incident_id ?>"></label>
                                </div>
                            </td>

                            <td class="text-truncate text-muted">
                                <?php if($row->end_datetime): ?>
                                    <span class="" data-toggle="tooltip" title="<?= l('incidents.solved') ?>">
                                        <i class="fas fa-fw fa-sm fa-check-circle text-success"></i>
                                    </span>
                                <?php else: ?>
                                    <span class="" data-toggle="tooltip" title="<?= l('incidents.unsolved') ?>">
                                        <i class="fas fa-fw fa-sm fa-times-circle text-danger"></i>
                                    </span>
                                <?php endif ?>

                                <?php
                                $error = l('global.unknown');
                                if(isset($row->error->type)):
                                    if($row->error->type == 'exception') {
                                        $error = $row->error->message;
                                    } elseif(in_array($row->error->type, ['response_status_code', 'response_body', 'response_header', 'ping_failed', 'parse_error', 'socket_connect_failed', 'socket_no_response', 'socket_create_failed'])) {
                                        $error = l('monitor.checks.error.' . $row->error->type);
                                    } elseif(in_array($row->error->type, ['connection_failed'])) {
                                        $error = sprintf(l('monitor.checks.error.connection_failed'), $row->error->message);
                                    }
                                ?>

                                    <span class="ml-3" data-toggle="tooltip" title="<?= $error ?>">
                                        <i class="fas fa-fw fa-sm fa-envelope-open-text text-muted"></i>
                                    </span>
                                <?php endif ?>

                                <span class="ml-3" data-toggle="tooltip" title="<?= sprintf(l('incidents.x_failed_checks'), nr($row->failed_checks)) ?>">
                                    <i class="fas fa-fw fa-sm fa-undo text-muted"></i>
                                </span>
                            </td>

                            <td class="text-truncate text-muted">
                                <div>
                                    <a href="<?= url('incident/' . $row->incident_id) ?>"><?= $row->monitor_name ?? $row->heartbeat_name ?></a>
                                </div>

                                <span class="small" data-toggle="tooltip" title="<?= \Altum\Date::get($row->start_datetime, 1) ?>">
                                    <?= \Altum\Date::get_timeago($row->start_datetime) ?>
                                </span>
                            </td>

                            <td class="text-truncate">
                                <?php if($row->end_datetime): ?>
                                    <span class="badge badge-success" data-toggle="tooltip" title="<?= \Altum\Date::get($row->end_datetime, 1) ?>">
                                        <i class="fas fa-fw fa-sm fa-check-circle mr-1"></i>
                                        <?= \Altum\Date::get_timeago($row->end_datetime) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-danger">
                                        <i class="fas fa-fw fa-sm fa-exclamation-circle mr-1"></i>
                                        <?= l('incidents.end_datetime_null') ?>
                                    </span>
                                <?php endif ?>
                            </td>

                            <td class="text-truncate">
                                <?= \Altum\Date::get_elapsed_time($row->start_datetime, $row->end_datetime, 2) ?>
                            </td>

                            <td class="text-truncate">
                                <a
                                        href="#"
                                        data-toggle="modal"
                                        data-target="#incident_comment_modal"
                                        data-incident-id="<?= $row->incident_id ?>"
                                        data-comment="<?= $row->comment ?>"
                                        data-is-solved="<?= (int) !empty($row->end_datetime) ?>"
                                        id="incident_id_<?= $row->incident_id ?>"
                                        title="<?= $row->comment ?: l('global.none') ?>"
                                        data-tooltip
                                        class="text-muted"
                                >
                                    <i class="fas fa-fw fa-sm fa-comment"></i>
                                </a>
                            </td>

                            <td class="text-truncate">
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/incidents/incident_dropdown_button.php', ['id' => $row->incident_id, 'comment' => $row->comment, 'end_datetime' => $row->end_datetime]) ?>
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
            'name' => 'incidents',
            'has_secondary_text' => true,
        ]); ?>

    <?php endif ?>
</div>

<?php require THEME_PATH . 'views/partials/js_bulk.php' ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/bulk_delete_modal.php'), 'modals'); ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/incidents/incident_comment_modal.php'), 'modals'); ?>
