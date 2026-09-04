<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if($this->user->plan_settings->transfers_limit != -1 && $data->total_transfers > $this->user->plan_settings->transfers_limit): ?>
        <div class="alert alert-danger">
            <i class="fas fa-fw fa-times-circle text-danger mr-2"></i> <?= sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), '<strong>' . $data->total_transfers - $this->user->plan_settings->transfers_limit, mb_strtolower(l('transfers.title')) . '</strong>', '<a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '</a>') ?>
        </div>
    <?php endif ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-center mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-paper-plane mr-1"></i> <?= l('transfers.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('transfers.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-lg-auto d-flex flex-wrap gap-3 d-print-none">
            <div>
                <?php if($this->user->plan_settings->transfers_limit != -1 && $data->total_transfers >= $this->user->plan_settings->transfers_limit): ?>
                    <button type="button" class="btn btn-primary disabled" <?= get_plan_feature_limit_reached_info() ?>>
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('transfers.create') ?>
                    </button>
                <?php else: ?>
                    <a href="<?= url('dashboard') ?>" class="btn btn-primary" data-toggle="tooltip" data-html="true" title="<?= get_plan_feature_limit_info($data->total_transfers, $this->user->plan_settings->transfers_limit, isset($data->filters) ? !$data->filters->has_applied_filters : true) ?>">
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('transfers.create') ?>
                    </a>
                <?php endif ?>
            </div>

            <div>
                <div class="dropdown">
                    <button type="button" class="btn btn-light dropdown-toggle-simple <?= !empty($data->transfers) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('transfers?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('transfers?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
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
                    <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= !empty($data->transfers) || $data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip data-html="true" title="<?= l('global.filters.tooltip') ?>" data-tooltip-hide-on-click>
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
                                    <option value="description" <?= $data->filters->search_by == 'description' ? 'selected="selected"' : null ?>><?= l('transfer.description') ?></option>
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
                                    <option value="expiration_datetime" <?= $data->filters->datetime_field == 'expiration_datetime' ? 'selected="selected"' : null ?>><?= l('transfers.expiration_datetime') ?></option>
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
                                    <option value="transfer_id" <?= $data->filters->order_by == 'transfer_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                    <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                    <option value="last_datetime" <?= $data->filters->order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                    <option value="expiration_datetime" <?= $data->filters->order_by == 'expiration_datetime' ? 'selected="selected"' : null ?>><?= l('transfers.expiration_datetime') ?></option>
                                    <option value="pageviews" <?= $data->filters->order_by == 'pageviews' ? 'selected="selected"' : null ?>><?= l('transfer.pageviews') ?></option>
                                    <option value="downloads" <?= $data->filters->order_by == 'downloads' ? 'selected="selected"' : null ?>><?= l('transfer.downloads') ?></option>
                                    <option value="url" <?= $data->filters->order_by == 'url' ? 'selected="selected"' : null ?>><?= l('transfer.url') ?></option>
                                    <option value="name" <?= $data->filters->order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                    <option value="downloads_limit" <?= $data->filters->order_by == 'downloads_limit' ? 'selected="selected"' : null ?>><?= l('transfer.downloads_limit') ?></option>
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

    <?php if (!empty($data->transfers)): ?>
        <?php if($data->transfers_chart): ?>
        <div class="card mb-4">
            <div class="card-body">
                <div class="chart-container <?= !$data->transfers_chart['is_empty'] ? null : 'd-none' ?>">
                    <canvas id="pageviews_chart"></canvas>
                </div>
                <?= !$data->transfers_chart['is_empty'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>

                <?php if(!$data->transfers_chart['is_empty'] && settings()->main->chart_cache ?? 12): ?>
                    <small class="text-muted">
                        <span data-toggle="tooltip" title="<?= sprintf(l('global.chart_help'), settings()->main->chart_cache ?? 12, settings()->main->chart_days ?? 30) ?>"><i class="fas fa-fw fa-sm fa-info-circle mr-1"></i></span>
                        <span class="d-lg-none"><?= sprintf(l('global.chart_help'), settings()->main->chart_cache ?? 12, settings()->main->chart_days ?? 30) ?></span>
                    </small>
                <?php endif ?>
            </div>
        </div>

<?php require THEME_PATH . 'views/partials/js_chart_defaults.php' ?>

    <?php ob_start() ?>

        <script>
    'use strict';

            if(document.getElementById('pageviews_chart')) {
                let css = window.getComputedStyle(document.body);
                let pageviews_color = css.getPropertyValue('--primary');
                let visitors_color = css.getPropertyValue('--gray-400');
                let pageviews_color_gradient = null;
                let visitors_color_gradient = null;

                /* Chart */
                let pageviews_chart = document.getElementById('pageviews_chart').getContext('2d');

                /* Colors */
                pageviews_color_gradient = pageviews_chart.createLinearGradient(0, 0, 0, 250);
                pageviews_color_gradient.addColorStop(0, set_hex_opacity(pageviews_color, 0.6));
                pageviews_color_gradient.addColorStop(1, set_hex_opacity(pageviews_color, 0.1));

                visitors_color_gradient = pageviews_chart.createLinearGradient(0, 0, 0, 250);
                visitors_color_gradient.addColorStop(0, set_hex_opacity(visitors_color, 0.6));
                visitors_color_gradient.addColorStop(1, set_hex_opacity(visitors_color, 0.1));

                new Chart(pageviews_chart, {
                    type: 'line',
                    data: {
                        labels: <?= $data->transfers_chart['labels'] ?? '[]' ?>,
                        datasets: [
                            {
                                label: <?= json_encode(l('transfer_statistics.pageviews')) ?>,
                                data: <?= $data->transfers_chart['pageviews'] ?? '[]' ?>,
                                backgroundColor: pageviews_color_gradient,
                                borderColor: pageviews_color,
                                fill: true
                            },
                            {
                                label: <?= json_encode(l('transfer_statistics.visitors')) ?>,
                                data: <?= $data->transfers_chart['visitors'] ?? '[]' ?>,
                                backgroundColor: visitors_color_gradient,
                                borderColor: visitors_color,
                                fill: true
                            }
                        ]
                    },
                    options: chart_options
                });
            }
        </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
    <?php endif ?>

        <form id="table" action="<?= SITE_URL . 'transfers/bulk' ?>" method="post" role="form">
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
                        <th><?= l('transfers.table.transfer') ?></th>
                        <th><?= l('transfers.table.files') ?></th>
                        <th><?= l('transfers.table.expiration') ?></th>
                        <th><?= l('transfers.table.pageviews') ?></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach($data->transfers as $row): ?>

                        <tr>
                            <td data-bulk-table class="d-none">
                                <div class="custom-control custom-checkbox">
                                    <input id="selected_transfer_id_<?= $row->transfer_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->transfer_id ?>" />
                                    <label class="custom-control-label" for="selected_transfer_id_<?= $row->transfer_id ?>"></label>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <div class="d-flex flex-column">
                                    <div><a href="<?= url('transfer/' . $row->transfer_id) ?>" title="<?= $row->name ?>"><?= string_truncate($row->name, 32) ?></a></div>
                                    <div class="small">
                                        <?= remove_url_protocol_from_url($row->full_url) ?>
                                        <a href="<?= $row->full_url ?>" class="text-muted" target="_blank" rel="noreferrer">
                                            <i class="fas fa-fw fa-xs fa-external-link-alt text-muted ml-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <span class="badge badge-info">
                                    <?= nr($row->total_files) ?>
                                    <span>• <?= get_formatted_bytes($row->total_size) ?></span>
                                </span>
                            </td>

                            <td class="text-nowrap">
                                <div class="d-flex flex-column small">
                                    <div class="mb-1">
                                        <a href="<?= url('transfer-downloads/' . $row->transfer_id) ?>" class="text-muted">
                                            <i class="fas fa-fw fa-sm fa-download mr-1"></i>
                                            <?= (new \Altum\Models\Transfers())->get_downloads_limit_text($row->downloads, $row->downloads_limit) ?>
                                        </a>
                                    </div>
                                    <div class="text-muted">
                                        <i class="fas fa-fw fa-sm fa-hourglass-half mr-1"></i>
                                        <?= (new \Altum\Models\Transfers())->get_expiration_datetime_text($row->expiration_datetime) ?>
                                    </div>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <a href="<?= url('transfer-statistics/' . $row->transfer_id) ?>" class="badge badge-light text-decoration-none" data-toggle="tooltip" title="<?= l('transfer.pageviews') ?>">
                                    <i class="fas fa-fw fa-sm fa-chart-bar mr-1"></i> <?= nr($row->pageviews) ?>
                                </a>
                            </td>

                            <td class="text-nowrap">
                                <div class="d-flex align-items-center">
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

                                    <?php if($row->settings->file_encryption_is_enabled): ?>
                                        <span class="mr-2" data-toggle="tooltip" title="<?= l('transfers.file_encryption_is_enabled') . ': ' . l('global.yes') ?>">
                                            <i class="fas fa-fw fa-fingerprint text-primary"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="mr-2" data-toggle="tooltip" title="<?= l('transfers.file_encryption_is_enabled') . ': ' . l('global.no') ?>">
                                            <i class="fas fa-fw fa-fingerprint text-muted"></i>
                                        </span>
                                    <?php endif ?>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <button
                                            id="url_copy"
                                            type="button"
                                            class="btn btn-link text-secondary"
                                            data-toggle="tooltip"
                                            title="<?= l('global.clipboard_copy') ?>"
                                            aria-label="<?= l('global.clipboard_copy') ?>"
                                            data-copy="<?= l('global.clipboard_copy') ?>"
                                            data-copied="<?= l('global.clipboard_copied') ?>"
                                            data-clipboard-text="<?= $row->full_url ?>"
                                    >
                                        <i class="fas fa-fw fa-sm fa-copy"></i>
                                    </button>

                                    <?= include_view(THEME_PATH . 'views/transfers/transfer_dropdown_button.php', ['id' => $row->transfer_id, 'resource_name' => $row->name, 'full_url' => $row->full_url]) ?>
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
            'name' => 'transfers',
            'has_secondary_text' => true,
        ]); ?>

    <?php endif ?>
</div>

<?php require THEME_PATH . 'views/partials/js_bulk.php' ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/bulk_delete_modal.php'), 'modals'); ?>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>
