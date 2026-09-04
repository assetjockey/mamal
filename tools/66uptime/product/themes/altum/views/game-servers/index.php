<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if($this->user->plan_settings->game_servers_limit != -1 && $data->total_game_servers > $this->user->plan_settings->game_servers_limit): ?>
        <div class="alert alert-danger">
            <i class="fas fa-fw fa-times-circle text-danger mr-2"></i> <?= sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), '<strong>' . $data->total_game_servers - $this->user->plan_settings->game_servers_limit, mb_strtolower(l('game_servers.title')) . '</strong>', '<a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '</a>') ?>
        </div>
    <?php endif ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-center mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-gamepad mr-1"></i> <?= l('game_servers.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('game_servers.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-lg-auto d-flex flex-wrap gap-3 d-print-none">
            <div>
                <?php if($this->user->plan_settings->game_servers_limit != -1 && $data->total_game_servers >= $this->user->plan_settings->game_servers_limit): ?>
                    <button type="button" class="btn btn-primary disabled" <?= get_plan_feature_limit_reached_info() ?>>
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('game_servers.create') ?>
                    </button>
                <?php else: ?>
                    <a href="<?= url('game-server-create') ?>" class="btn btn-primary" data-toggle="tooltip" data-html="true" title="<?= get_plan_feature_limit_info($data->total_game_servers, $this->user->plan_settings->game_servers_limit, isset($data->filters) ? !$data->filters->has_applied_filters : true) ?>">
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('game_servers.create') ?>
                    </a>
                <?php endif ?>
            </div>

<!--            <div>-->
<!--                <a href="--><?php //= url('game-servers-import') ?><!--" class="btn btn-outline-primary" data-toggle="tooltip" data-html="true" title="--><?php //= l('game_servers_import.menu') ?><!--">-->
<!--                    <i class="fas fa-fw fa-upload fa-sm"></i>-->
<!--                </a>-->
<!--            </div>-->

            <div>
                <div class="dropdown">
                    <button type="button" class="btn btn-light dropdown-toggle-simple <?= !empty($data->game_servers) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('game-servers?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('game-servers?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
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
                    <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= !empty($data->game_servers) || $data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip data-html="true" title="<?= l('global.filters.tooltip') ?>" data-tooltip-hide-on-click>
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
                                    <option value="target" <?= $data->filters->search_by == 'target' ? 'selected="selected"' : null ?>><?= l('game_server.input.target') ?></option>
                                    <option value="port" <?= $data->filters->search_by == 'port' ? 'selected="selected"' : null ?>><?= l('game_server.input.port') ?></option>
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

                            <div class="form-group px-4">
                                <label for="filters_type" class="small"><?= l('game_server.input.type') ?></label>
                                <select name="type" id="filters_type" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <?php foreach($data->game_server_types as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= isset($data->filters->filters['type']) && $data->filters->filters['type'] == $key ? 'selected="selected"' : null ?>><?= $value['name'] ?></option>
                                    <?php endforeach ?>
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
                                    <option value="last_check_datetime" <?= $data->filters->datetime_field == 'last_check_datetime' ? 'selected="selected"' : null ?>><?= l('game_servers.last_check_datetime') ?></option>
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
                                    <option value="game_server_id" <?= $data->filters->order_by == 'game_server_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                    <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                    <option value="last_datetime" <?= $data->filters->order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                    <option value="last_check_datetime" <?= $data->filters->order_by == 'last_check_datetime' ? 'selected="selected"' : null ?>><?= l('game_servers.last_check_datetime') ?></option>
                                    <option value="total_checks" <?= $data->filters->order_by == 'total_checks' ? 'selected="selected"' : null ?>><?= l('game_server.total_checks') ?></option>
                                    <option value="uptime" <?= $data->filters->order_by == 'uptime' ? 'selected="selected"' : null ?>><?= l('game_servers.uptime') ?></option>
                                    <option value="average_response_time" <?= $data->filters->order_by == 'average_response_time' ? 'selected="selected"' : null ?>><?= l('game_servers.average_response_time') ?></option>
                                    <option value="average_online_players" <?= $data->filters->order_by == 'average_online_players' ? 'selected="selected"' : null ?>><?= l('game_servers.average_online_players') ?></option>
                                    <option value="online_players" <?= $data->filters->order_by == 'online_players' ? 'selected="selected"' : null ?>><?= l('game_servers.online_players') ?></option>
                                    <option value="maximum_online_players" <?= $data->filters->order_by == 'maximum_online_players' ? 'selected="selected"' : null ?>><?= l('game_servers.maximum_online_players') ?></option>
                                    <option value="name" <?= $data->filters->order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
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

    <?php if (!empty($data->game_servers)): ?>
        <form id="table" action="<?= SITE_URL . 'game_servers/bulk' ?>" method="post" role="form">
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
                        <th><?= l('game_servers.game_server') ?></th>
                        <th><?= l('game_servers.uptime') ?></th>
                        <th><?= l('game_servers.players') ?></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach($data->game_servers as $row): ?>

                        <?php
                        /* Determine the border color based on the status */
                        $uptime_class_name = match (true) {
                            $row->uptime >= 90 => 'success',
                            $row->uptime >= 50 => 'warning',
                            $row->uptime >= 0 => 'danger',
                        };
                        ?>

                        <tr>
                            <td data-bulk-table class="d-none">
                                <div class="custom-control custom-checkbox">
                                    <input id="selected_game_server_id_<?= $row->game_server_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->game_server_id ?>" />
                                    <label class="custom-control-label" for="selected_game_server_id_<?= $row->game_server_id ?>"></label>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <div class="d-flex flex-column">
                                    <div><a href="<?= url('game-server/' . $row->game_server_id) ?>"><?= $row->name ?></a></div>

                                    <small class="text-muted d-inline-flex align-items-center">
                                        <?php if($row->is_enabled): ?>
                                            <?php if(!$row->total_checks): ?>
                                                <span class="mr-2" data-toggle="tooltip" title="<?= l('game_servers.pending_check') ?>">
                                                    <i class="fas fa-fw fa-sm fa-clock text-muted"></i>
                                                </span>
                                            <?php elseif($row->is_ok): ?>
                                                <span class="mr-2" data-toggle="tooltip" title="<?= l('game_servers.is_ok') ?>">
                                                    <i class="fas fa-fw fa-sm fa-check-circle text-success"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-decoration-none mr-2" data-toggle="tooltip" title="<?= l('game_servers.is_not_ok') ?>">
                                                    <i class="fas fa-fw fa-sm fa-times-circle text-danger"></i>
                                                </span>
                                            <?php endif ?>
                                        <?php else: ?>
                                            <span class="mr-2" data-toggle="tooltip" title="<?= l('game_servers.is_enabled_paused') ?>">
                                                <i class="fas fa-fw fa-sm fa-pause-circle text-warning"></i>
                                            </span>
                                        <?php endif ?>

                                        <img src="<?= ASSETS_FULL_URL . 'images/games/' . $data->game_server_types[$row->type]['icon'] ?>" class="img-fluid icon-favicon-small mr-2" data-toggle="tooltip" title="<?= $data->game_server_types[$row->type]['name'] ?>" />

                                        <span><?= $row->target . ($row->port ? ':' . $row->port : null) ?></span>
                                    </small>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <?php ob_start() ?>
                                <div class='d-flex flex-column text-left'>
                                    <div class='d-flex flex-column my-1'>
                                        <div><?= l('game_servers.average_response_time') ?></div>
                                        <strong><?= display_response_time($row->average_response_time) ?></strong>
                                    </div>

                                    <div class='d-flex flex-column my-1'>
                                        <div>&bullet; <?= sprintf(l('game_server.total_checks_tooltip'), nr($row->total_checks)) ?></div>
                                    </div>

                                    <div class='d-flex flex-column my-1'>
                                        <div>&bullet; <?= sprintf(l('game_server.total_ok_checks_tooltip'), nr($row->total_ok_checks)) ?></div>
                                    </div>
                                </div>
                                <?php $tooltip = ob_get_clean() ?>
                                <span class="badge badge-<?= $uptime_class_name ?>" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                    <?= nr($row->uptime, settings()->monitors_heartbeats->decimals) . '%' ?>
                                </span>
                            </td>

                            <td class="text-nowrap">
                                <span class="badge badge-light">
                                    <i class="fas fa-fw fa-sm fa-users mr-1"></i> <?= nr($row->online_players) . '/' . nr($row->maximum_online_players) ?>
                                </span>
                            </td>

                            <td class="text-nowrap">
                                <div class="d-flex">
                                    <?php foreach($row->last_logs as $log): ?>
                                        <?php if(isset($log->is_ok)): ?>

                                            <?php ob_start() ?>
                                            <div class='d-flex flex-column text-left'>
                                                <div class='d-flex flex-column my-1'>
                                                    <div><?= \Altum\Date::get($log->datetime, 1) ?></div>
                                                    <strong><?= \Altum\Date::get_timeago($log->datetime) ?></strong>
                                                </div>

                                                <div class='d-flex flex-column my-1'>
                                                    <div><?= l('game_servers.is_ok') ?></div>
                                                    <strong><?= ($log->is_ok ? l('global.yes') : l('global.no')) ?></strong>
                                                </div>

                                                <div class='d-flex flex-column my-1'>
                                                    <div><?= l('game_servers.response_time') ?></div>
                                                    <strong><?= display_response_time($log->response_time) ?></strong>
                                                </div>

                                                <div class='d-flex flex-column my-1'>
                                                    <div><?= l('game_servers.players') ?></div>
                                                    <strong><?= nr($log->online_players) . '/' . nr($log->maximum_online_players) ?></strong>
                                                </div>

                                                <?php
                                                $log_error = l('global.unknown');
                                                if(isset($log->error->type)) {
                                                    if($log->error->type == 'exception') {
                                                        $log_error = $log->error->message;
                                                    }
                                                }
                                                ?>

                                                <?php if(!$log->is_ok): ?>
                                                <div class='d-flex flex-column my-1'>
                                                    <div><?= l('game_servers.checks.error') ?></div>
                                                    <strong><?= $log_error ?></strong>
                                                </div>
                                                <?php endif ?>
                                            </div>

                                            <?php $tooltip = ob_get_clean() ?>

                                            <div
                                                    class="status-badge <?= $log->is_ok ? 'bg-success' : 'bg-danger' ?> mr-1"
                                                    data-toggle="tooltip"
                                                    data-html="true"
                                                    data-custom-class=""
                                                    title="<?= $tooltip ?>"
                                            ></div>

                                        <?php else: ?>
                                            <div class="status-badge bg-gray-200 mr-1"></div>
                                        <?php endif ?>
                                    <?php endforeach ?>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <div class="d-flex align-items-center">
                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('game_servers.last_check_datetime') . '<br />' . \Altum\Date::get($row->last_check_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_check_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_check_datetime) . ')</small>' ?>">
                                        <i class="fas fa-fw fa-calendar-check text-muted"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                        <i class="fas fa-fw fa-calendar text-muted"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                        <i class="fas fa-fw fa-history text-muted"></i>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/game-server/game_server_dropdown_button.php', ['id' => $row->game_server_id, 'resource_name' => $row->name]) ?>
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
            'name' => 'game_servers',
            'has_secondary_text' => true,
        ]); ?>

    <?php endif ?>
</div>

<?php require THEME_PATH . 'views/partials/js_bulk.php' ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/bulk_delete_modal.php'), 'modals'); ?>
