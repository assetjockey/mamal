<?php defined('ALTUMCODE') || die() ?>

<?php
$data_period_label = match($data->data_period) {
    'last_24_hours' => l('websites.last_24_hours'),
    'last_7_days' => l('websites.last_7_days'),
    default => l('websites.this_month'),
};
?>

<div class="container">

    <?php if($this->user->plan_settings->websites_limit != -1 && $data->total_websites > $this->user->plan_settings->websites_limit): ?>
        <div class="alert alert-danger">
            <i class="fas fa-fw fa-times-circle text-danger mr-2"></i> <?= sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), '<strong>' . $data->total_websites - $this->user->plan_settings->websites_limit, mb_strtolower(l('websites.title')) . '</strong>', '<a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '</a>') ?>
        </div>
    <?php endif ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-end mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-pager mr-1"></i> <?= l('websites.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('websites.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-lg-auto d-flex flex-wrap gap-3 d-print-none">
            <div>
                <?php if(!$this->team): ?>
                    <?php if($this->user->plan_settings->websites_limit != -1 && count($this->websites) >= $this->user->plan_settings->websites_limit): ?>
                        <button type="button" class="btn btn-primary disabled" data-toggle="tooltip" title="<?= l('global.info_message.plan_feature_limit')  ?>">
                            <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('websites.create') ?>
                        </button>
                    <?php else: ?>
                        <a href="<?= url('website-create') ?>" class="btn btn-primary" data-toggle="tooltip" data-html="true" title="<?= get_plan_feature_limit_info($data->total_websites, $this->user->plan_settings->websites_limit, isset($data->filters) ? !$data->filters->has_applied_filters : true) ?>">
                            <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('websites.create') ?>
                        </a>
                    <?php endif ?>
                <?php endif ?>
            </div>

            <div>
                <a href="<?= url('websites-import') ?>" class="btn btn-outline-primary" data-toggle="tooltip" data-html="true" title="<?= l('websites_import.menu') ?>">
                    <i class="fas fa-fw fa-upload fa-sm"></i>
                </a>
            </div>

            <div>
                <div class="dropdown">
                    <button type="button" class="btn btn-light dropdown-toggle-simple <?= !empty($data->websites) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('websites?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('websites?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
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
                    <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= !empty($data->websites) || $data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip data-html="true" title="<?= l('global.filters.tooltip') ?>" data-tooltip-hide-on-click>
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
                                    <option value="host" <?= $data->filters->search_by == 'host' ? 'selected="selected"' : null ?>><?= l('websites.host') ?></option>
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
                                <label for="filters_tracking_type" class="small"><?= l('websites.tracking_type') ?></label>
                                <select name="tracking_type" id="filters_tracking_type" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <option value="advanced" <?= isset($data->filters->filters['tracking_type']) && $data->filters->filters['tracking_type'] == 'advanced' ? 'selected="selected"' : null ?>><?= l('websites.tracking_type_advanced') ?></option>
                                    <option value="lightweight" <?= isset($data->filters->filters['tracking_type']) && $data->filters->filters['tracking_type'] == 'lightweight' ? 'selected="selected"' : null ?>><?= l('websites.tracking_type_lightweight') ?></option>
                                </select>
                            </div>

                            <?php if(settings()->analytics->domains_is_enabled): ?>
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
                                    <option value="website_id" <?= $data->filters->order_by == 'website_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                    <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                    <option value="last_datetime" <?= $data->filters->order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                    <option value="name" <?= $data->filters->order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                    <option value="host" <?= $data->filters->order_by == 'host' ? 'selected="selected"' : null ?>><?= l('websites.host') ?></option>
                                    <option value="current_month_sessions_events" <?= $data->filters->order_by == 'current_month_sessions_events' ? 'selected="selected"' : null ?>><?= l('websites.sessions_events') ?></option>
                                    <option value="last_24_hours_pageviews" <?= $data->filters->order_by == 'last_24_hours_pageviews' ? 'selected="selected"' : null ?>><?= l('websites.last_24_hours') ?></option>
                                    <option value="last_7_days_pageviews" <?= $data->filters->order_by == 'last_7_days_pageviews' ? 'selected="selected"' : null ?>><?= l('websites.last_7_days') ?></option>
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

            <?php if(!$this->team): ?>
            <div>
                <button id="bulk_enable" type="button" class="btn btn-light <?= !empty($data->websites) ? null : 'disabled' ?>" data-toggle="tooltip" title="<?= l('global.bulk_actions') ?>"><i class="fas fa-fw fa-sm fa-list"></i></button>

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
            <?php endif ?>
        </div>
    </div>

    <?= \Altum\Alerts::output_alerts() ?>

    <?php if (!empty($data->websites)): ?>
        <form id="table" action="<?= SITE_URL . 'websites/bulk' ?>" method="post" role="form">
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
                        <th><?= l('websites.website') ?></th>
                        <th><?= l('websites.sessions_events') ?></th>
                        <th><?= l('websites.is_enabled') ?></th>
                        <th><?= l('websites.tracking_type') ?></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach($data->websites as $row): ?>
                        <tr data-website-id="<?= $row->website_id ?>">
                            <td data-bulk-table class="d-none">
                                <div class="custom-control custom-checkbox">
                                    <input id="selected_website_id_<?= $row->website_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->website_id ?>" />
                                    <label class="custom-control-label" for="selected_website_id_<?= $row->website_id ?>"></label>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <div class="d-flex flex-column">
                                    <div>
                                        <a href="<?= url('website-update/' . $row->website_id) ?>">
                                            <?= $row->name ?>
                                        </a>
                                    </div>

                                    <div class="small d-flex align-items-center text-muted">
                                        <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($row->host) ?>" class="img-fluid icon-favicon-small mr-1" loading="lazy" />

                                        <?= $row->host . $row->path ?>

                                        <a href="<?= 'https://' . $row->host . $row->path ?>" target="_blank" rel="noreferrer"><i class="fas fa-fw fa-xs fa-external-link-alt text-muted ml-1"></i></a>
                                    </div>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <div class="website-pageviews-stats" data-website-pageviews-stats="<?= $row->website_id ?>">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="mr-3">
                                            <a href="<?= url('dashboard?website_id=' . $row->website_id . '&redirect=dashboard') ?>" class="h5 mb-0" data-website-pageviews-total data-toggle="tooltip" title="<?= l('global.loading') ?>">...</a>

                                            <div class="small text-muted">
                                                <?= $data_period_label ?>
                                            </div>
                                        </div>

                                        <div class="website-pageviews-chart-container">
                                            <canvas data-website-pageviews-chart></canvas>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <?php ob_start() ?>
                                <div class='d-flex flex-column'>
                                    <div class='font-weight-bold mb-1'><?= l('websites.usage') ?> - <?= l('websites.this_month') ?></div>

                                    <div class='d-flex justify-content-between my-1 text-left'>
                                        <div class='mr-3'><?= l('websites.sessions_events') ?></div>
                                        <strong><?= nr($row->current_month_sessions_events) . '/' . ($this->user->plan_settings->sessions_events_limit === -1 ? '∞' : nr($this->user->plan_settings->sessions_events_limit)) ?></strong>
                                    </div>

                                    <?php if($row->tracking_type == 'advanced'): ?>
                                        <?php if($this->user->plan_settings->events_children_limit != 0): ?>
                                            <div class='d-flex justify-content-between my-1 text-left'>
                                                <div class='mr-3 text-truncate'><?= l('websites.events_children') ?></div>
                                                <div class='font-weight-bold'><?= nr($row->current_month_events_children) . '/' . ($this->user->plan_settings->events_children_limit === -1 ? '∞' : nr($this->user->plan_settings->events_children_limit)) ?></div>
                                            </div>
                                        <?php endif ?>

                                        <?php if(settings()->analytics->sessions_replays_is_enabled && $this->user->plan_settings->sessions_replays_limit != 0): ?>
                                            <div class='d-flex justify-content-between my-1 text-left'>
                                                <div class='mr-3 text-truncate'><?= l('websites.sessions_replays') ?></div>
                                                <div class='font-weight-bold'><?= nr($row->current_month_sessions_replays) . '/' . ($this->user->plan_settings->sessions_replays_limit === -1 ? '∞' : nr($this->user->plan_settings->sessions_replays_limit, 1, true)) ?></div>
                                            </div>
                                        <?php endif ?>

                                        <?php if(settings()->analytics->websites_heatmaps_is_enabled && $this->user->plan_settings->websites_heatmaps_limit != 0): ?>
                                            <div class='d-flex justify-content-between my-1 text-left'>
                                                <div class='mr-3 text-truncate'><?= l('websites.websites_heatmaps') ?></div>
                                                <div class='font-weight-bold'><?= nr($row->heatmaps) . '/' . ($this->user->plan_settings->websites_heatmaps_limit === -1 ? '∞' : nr($this->user->plan_settings->websites_heatmaps_limit)) ?></div>
                                            </div>
                                        <?php endif ?>
                                    <?php endif ?>

                                    <?php if($this->user->plan_settings->websites_goals_limit != 0): ?>
                                        <div class='d-flex justify-content-between my-1 text-left'>
                                            <div class='mr-3 text-truncate'><?= l('websites.websites_goals') ?></div>
                                            <div class='font-weight-bold'><?= nr($row->goals) . '/' . ($this->user->plan_settings->websites_goals_limit === -1 ? '∞' : nr($this->user->plan_settings->websites_goals_limit)) ?></div>
                                        </div>
                                    <?php endif ?>
                                </div>
                            <?php $usage_html = ob_get_clean() ?>

                            <td class="text-nowrap">
                                <div class="d-flex align-items-center">
                                    <div class="mx-1">
                                        <?php if($row->is_enabled == 1): ?>
                                            <span class="badge badge-success"><i class="fas fa-fw fa-sm fa-check mr-1"></i> <?= l('global.active') ?></span>
                                        <?php elseif($row->is_enabled == 0): ?>
                                            <span class="badge badge-warning"><i class="fas fa-fw fa-sm fa-eye-slash mr-1"></i> <?= l('global.disabled') ?></span>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <?php ob_start() ?>
                                <div class='d-flex flex-column'>
                                    <div class='font-weight-bold mb-1'><?= $row->tracking_type == 'advanced' ? l('websites.tracking_type_advanced') : l('websites.tracking_type_lightweight') ?></div>

                                    <?php if($row->tracking_type == 'advanced'): ?>
                                        <div class='d-flex justify-content-between my-1 text-left'>
                                            <div class='mr-3'><?= l('websites.events_children') ?></div>
                                            <strong><?= $this->user->plan_settings->events_children_limit != 0 && $row->events_children_is_enabled ? l('global.active') : l('global.disabled') ?></strong>
                                        </div>

                                        <?php if(settings()->analytics->sessions_replays_is_enabled): ?>
                                            <div class='d-flex justify-content-between my-1 text-left'>
                                                <div class='mr-3'><?= l('websites.sessions_replays') ?></div>
                                                <strong><?= $this->user->plan_settings->sessions_replays_limit != 0 && $row->sessions_replays_is_enabled ? l('global.active') : l('global.disabled') ?></strong>
                                            </div>
                                        <?php endif ?>
                                    <?php endif ?>

                                    <div class='d-flex justify-content-between my-1 text-left'>
                                        <div class='mr-3'><?= l('websites.outbound_clicks_is_enabled') ?></div>
                                        <strong><?= $row->outbound_clicks_is_enabled ? l('global.active') : l('global.disabled') ?></strong>
                                    </div>

                                    <?php if(settings()->analytics->email_reports_is_enabled): ?>
                                        <div class='d-flex justify-content-between my-1 text-left'>
                                            <div class='mr-3'><?= l('websites.email_reports') ?></div>
                                            <strong><?= $this->user->plan_settings->email_reports_is_enabled && $row->email_reports_is_enabled ? l('global.active') : l('global.disabled') ?></strong>
                                        </div>
                                    <?php endif ?>
                                </div>
                                <?php $tracking_html = ob_get_clean() ?>

                                <div class="d-flex align-items-center">
                                    <?php if($row->tracking_type == 'advanced'): ?>
                                        <span class="badge badge-info" data-toggle="tooltip" data-html="true" title="<?= $tracking_html ?>"><i class="fas fa-fw fa-sm fa-brain"></i></span>
                                    <?php endif ?>

                                    <?php if($row->tracking_type == 'lightweight'): ?>
                                        <span class="badge badge-primary" data-toggle="tooltip" data-html="true" title="<?= $tracking_html ?>"><i class="fas fa-fw fa-sm fa-feather"></i></span>
                                    <?php endif ?>

                                    <a href="<?= url('account-plan') ?>" class="badge badge-light ml-2" data-toggle="tooltip" data-html="true" title="<?= $usage_html ?>">
                                        <i class="fas fa-fw fa-sm fa-info-circle"></i>
                                    </a>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <div class="d-flex align-items-center justify-content-end">
                                    <div data-toggle="tooltip" data-html="true" title="<?= l('websites.public_statistics_is_enabled') . '<br />' . ($row->public_statistics_is_enabled ? l('global.active') : l('global.disabled')) ?>">
                                        <button
                                                type="button"
                                                class="btn btn-sm btn-gray-200"
                                                data-toggle="modal"
                                                data-target="#website_public_statistics_modal"
                                                data-pixel-key="<?= $row->pixel_key ?>"
                                                data-base-url="<?= $row->domain_id && isset($data->domains[$row->domain_id]) ? $data->domains[$row->domain_id]->scheme . $data->domains[$row->domain_id]->host . '/' : SITE_URL ?>"
                                                <?= $row->public_statistics_is_enabled ? null : 'disabled="disabled"' ?>
                                        ><i class="fas fa-fw fa-sm fa-paper-plane"></i></button>
                                    </div>

                                    <div class="ml-3" data-toggle="tooltip" title="<?= l('websites.pixel_key') ?>">
                                        <button
                                                type="button"
                                                class="btn btn-sm btn-primary-100"
                                                data-toggle="modal"
                                                data-target="#website_pixel_key_modal"
                                                data-tracking-type="<?= $row->tracking_type ?>"
                                                data-pixel-key="<?= $row->pixel_key ?>"
                                                data-url="<?= $row->scheme . $row->host . $row->path ?>"
                                                data-base-url="<?= $row->domain_id && isset($data->domains[$row->domain_id]) ? $data->domains[$row->domain_id]->scheme . $data->domains[$row->domain_id]->host . '/' : SITE_URL ?>"
                                        ><i class="fas fa-fw fa-sm fa-code"></i></button>
                                    </div>

                                    <?php if(!$this->team): ?>
                                        <?= include_view(THEME_PATH . 'views/websites/website_dropdown_button.php', ['id' => $row->website_id, 'resource_name' => $row->name]) ?>
                                    <?php endif ?>
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
            'name' => 'websites',
            'has_secondary_text' => true,
        ]); ?>

    <?php endif ?>

</div>

<?php \Altum\Event::add_content((new \Altum\View('websites/website_pixel_key_modal', (array) $this))->run($data), 'modals'); ?>
<?php \Altum\Event::add_content((new \Altum\View('websites/website_public_statistics_modal', (array) $this))->run($data), 'modals'); ?>
<?php require THEME_PATH . 'views/partials/js_bulk.php' ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/bulk_delete_modal.php'), 'modals'); ?>

<?php if(!empty($data->websites)) require THEME_PATH . 'views/partials/js_chart_defaults.php' ?>

<?php ob_start() ?>
<style>
    .website-pageviews-stats {
        min-width: 220px;
    }

    .website-pageviews-chart-container {
        width: 115px;
        height: 38px;
        flex: 0 0 115px;
    }

    .website-pageviews-chart-container canvas {
        width: 115px !important;
        height: 38px !important;
    }
</style>
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script>
    'use strict';

    <?php if(isset($_GET['pixel_key_modal'])): ?>
    /* Open the pixel key modal */
    $('[data-target="#website_pixel_key_modal"][data-pixel-key="<?= $_GET['pixel_key_modal'] ?>"]').trigger('click');
    <?php endif ?>

    <?php if(!empty($data->websites)): ?>
    let website_pageviews_stats_elements = document.querySelectorAll('[data-website-pageviews-stats]');
    let website_pageviews_charts = {};
    let website_pageviews_label = <?= json_encode(l('analytics.pageviews')) ?>;

    let render_website_pageviews_chart = (element, chart_data) => {
        let canvas = element.querySelector('[data-website-pageviews-chart]');

        if(!canvas) {
            return;
        }

        let website_id = element.getAttribute('data-website-pageviews-stats');

        if(website_pageviews_charts[website_id]) {
            website_pageviews_charts[website_id].destroy();
        }

        let context = canvas.getContext('2d');
        let css = window.getComputedStyle(document.body);
        let color = css.getPropertyValue('--primary');

        website_pageviews_charts[website_id] = new Chart(context, {
            type: 'line',
            data: {
                labels: chart_data.labels,
                datasets: [
                    {
                        data: chart_data.pageviews,
                        backgroundColor: 'rgba(0,0,0,0)',
                        borderColor: color,
                        borderWidth: 2,
                        fill: false,
                        pointRadius: 0,
                        pointHoverRadius: 2,
                        label: <?= json_encode(l('websites.sessions_events')) ?>
                    }
                ]
            },
            options: {
                ...chart_options,
                animation: false,
                elements: {
                    line: {
                        tension: .35
                    },
                    point: {
                        radius: 0,
                        hoverRadius: 2
                    }
                },
                plugins: {
                    ...chart_options.plugins,
                    legend: {
                        display: false
                    },
                    tooltip: {
                        ...chart_options.plugins.tooltip,
                        displayColors: false,
                        padding: 6,
                        caretPadding: 4,
                        caretSize: 4,
                        titleSpacing: 0,
                        titleMarginBottom: 2,
                        titleFont: {
                            size: 10,
                            weight: 'bold'
                        },
                        bodySpacing: 0,
                        bodyFont: {
                            size: 11
                        },
                        callbacks: {
                            label: context => `${nr(context.raw)} ${website_pageviews_label}`
                        }
                    }
                },
                scales: {
                    x: {
                        display: false
                    },
                    y: {
                        display: false,
                        beginAtZero: true
                    }
                },
                layout: {
                    padding: 0
                },
                maintainAspectRatio: false,
            }
        });
    };

    let get_website_pageviews_stats = async website_ids => {
        let website_ids_string = website_ids.join(',');
        let data;

        try {
            let url_query = build_url_query({
                global_token,
                request_type: 'pageviews',
                website_ids: website_ids_string,
                period: <?= json_encode($data->data_period) ?>,
            });

            let response = await fetch(`${url}websites-ajax?${url_query}`);

            if(!response.ok) {
                throw response;
            }

            data = await response.json();

            if(data.status != 'success') {
                throw data;
            }
        } catch(error) {
            website_ids.forEach(website_id => {
                let element = document.querySelector(`[data-website-pageviews-stats="${website_id}"]`);

                if(!element) {
                    return;
                }

                let total = element.querySelector('[data-website-pageviews-total]');

                total.innerText = '-';
                total.setAttribute('title', <?= json_encode(l('global.error_message.basic')) ?>);
                total.setAttribute('data-original-title', <?= json_encode(l('global.error_message.basic')) ?>);
            });

            return;
        }

        for(let [website_id, stats] of Object.entries(data.details.websites)) {
            let element = document.querySelector(`[data-website-pageviews-stats="${website_id}"]`);

            if(!element) {
                continue;
            }

            let total = element.querySelector('[data-website-pageviews-total]');

            total.innerText = nr(stats.pageviews, 2, false, true);
            total.setAttribute('title', nr(stats.pageviews));
            total.setAttribute('data-original-title', nr(stats.pageviews));
        }

        for(let [website_id, stats] of Object.entries(data.details.websites)) {
            let element = document.querySelector(`[data-website-pageviews-stats="${website_id}"]`);

            if(element) {
                render_website_pageviews_chart(element, stats.chart);
            }
        }

        tooltips_initiate();
    };

    if(website_pageviews_stats_elements.length) {
        let website_ids = Array.from(website_pageviews_stats_elements).map(element => element.getAttribute('data-website-pageviews-stats'));

        (async () => {
            for(let i = 0; i < website_ids.length; i += 100) {
                await get_website_pageviews_stats(website_ids.slice(i, i + 100));
            }
        })();
    }
    <?php endif ?>
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
