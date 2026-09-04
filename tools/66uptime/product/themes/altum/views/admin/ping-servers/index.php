<?php defined('ALTUMCODE') || die() ?>

<div class="d-flex flex-column flex-md-row justify-content-between mb-4">
    <h1 class="h3 mb-3 mb-md-0 text-truncate"><i class="fas fa-fw fa-xs fa-map-marked-alt text-primary-900 mr-2"></i> <?= l('admin_ping_servers.header') ?></h1>

    <div class="d-flex position-relative d-print-none">
        <div>
            <?php if(\Altum\Plugin::is_installed('ping-servers') || \Altum\Plugin::is_active('ping-servers')): ?>
                <a href="<?= url('admin/ping-server-create') ?>" class="btn btn-primary text-nowrap"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('admin_ping_server_create.menu') ?></a>
            <?php else: ?>
                <button type="button" class="btn btn-primary disabled" data-toggle="tooltip" title="<?= sprintf(l('admin_plugins.no_access'), \Altum\Plugin::get('ping-servers')->name ?? 'ping-servers')?>"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('admin_ping_server_create.menu') ?></button>
            <?php endif ?>
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
                                <option value="name" <?= $data->filters->search_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
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
                            <label for="filters_country_code" class="small"><?= l('global.country') ?></label>
                            <select name="country_code" id="filters_country_code" class="custom-select custom-select-sm">
                                <option value=""><?= l('global.all') ?></option>
                                <?php foreach(get_countries_array() as $country_code => $country_name): ?>
                                    <option value="<?= $country_code ?>" <?= isset($data->filters->filters['country_code']) && $data->filters->filters['country_code'] == $country_code ? 'selected="selected"' : null ?>><?= $country_name ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_datetime_field" class="small"><?= l('global.filters.datetime_field') ?></label>
                            <select name="datetime_field" id="filters_datetime_field" class="custom-select custom-select-sm" data-toggle-datetime-filters="#filters_datetime">
                                <option value=""><?= l('global.none') ?></option>
                                <option value="datetime" <?= $data->filters->datetime_field == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $data->filters->datetime_field == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
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
                                <option value="ping_server_id" <?= $data->filters->order_by == 'ping_server_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $data->filters->order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
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
    </div>
</div>

<?= \Altum\Alerts::output_alerts() ?>

<div class="table-responsive table-custom-container">
    <table class="table table-custom">
        <thead>
            <tr>
                <th><?= l('admin_ping_servers.ping_server') ?></th>
                <th><?= l('admin_ping_servers.location') ?></th>
                <th><?= l('admin_ping_servers.monitors') ?></th>
                <th><?= l('global.status') ?></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($data->ping_servers as $row): ?>
        <tr>
            <td class="text-nowrap">
                <div class="d-flex flex-column">
                    <div>
                        <a href="<?= url('admin/ping-server-update/' . $row->ping_server_id) ?>"><?= $row->name ?></a>
                    </div>
                    <span class="small text-muted"><?= remove_url_protocol_from_url($row->url) ?></span>
                </div>
            </td>
            <td class="text-nowrap">
                <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($row->country_code) . '.svg' ?>" class="img-fluid icon-favicon mr-1" />
                <span><?= $row->city_name ?></span>
            </td>
            <td class="text-nowrap">
                <a href="<?= url('admin/monitors?ping_servers_ids=' . $row->ping_server_id) ?>" class="badge badge-light">
                    <i class="fas fa-fw fa-sm fa-server mr-1"></i> <?= nr($row->monitors) ?>
                </a>
            </td>
            <td class="text-nowrap">
                <?php if($row->is_enabled == 0): ?>
                <span class="badge badge-warning"><i class="fas fa-fw fa-sm fa-eye-slash mr-1"></i> <?= l('global.disabled') ?>
                <?php elseif($row->is_enabled == 1): ?>
                <span class="badge badge-success"><i class="fas fa-fw fa-sm fa-check mr-1"></i> <?= l('global.active') ?>
                <?php endif ?>
            </td>
            <td class="text-nowrap">
                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                    <i class="fas fa-fw fa-calendar text-muted"></i>
                </span>

                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                    <i class="fas fa-fw fa-history text-muted"></i>
                </span>
            </td>
            <td>
                <div class="d-flex justify-content-end">
                    <?= include_view(THEME_PATH . 'views/admin/ping-servers/admin_ping_server_dropdown_button.php', ['id' => $row->ping_server_id, 'resource_name' => $row->name]) ?>
                </div>
            </td>
        </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>

<div class="mt-3"><?= $data->pagination ?></div>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_url.php', [
    'name' => 'ping_server',
    'resource_id' => 'ping_server_id',
    'has_dynamic_resource_name' => true,
    'path' => 'admin/ping-servers/delete/'
]), 'modals'); ?>
