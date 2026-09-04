<?php defined('ALTUMCODE') || die() ?>

<div class="container">

    <?= \Altum\Alerts::output_alerts() ?>

    <?php if($this->user->plan_settings->campaigns_limit != -1 && $data->total_campaigns > $this->user->plan_settings->campaigns_limit): ?>
        <div class="alert alert-danger">
            <i class="fas fa-fw fa-times-circle text-danger mr-2"></i> <?= sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), '<strong>' . $data->total_campaigns - $this->user->plan_settings->campaigns_limit, mb_strtolower(l('campaigns.title')) . '</strong>', '<a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '</a>') ?>
        </div>
    <?php endif ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-end mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-pager mr-1"></i> <?= l('dashboard.campaigns_header') ?></h1>
        </div>

        <div class="col-12 col-lg-auto d-flex flex-wrap gap-3 d-print-none">
            <div>
                <?php if($this->user->plan_settings->campaigns_limit != -1 && $data->campaigns_total >= $this->user->plan_settings->campaigns_limit): ?>
                    <button type="button" class="btn btn-primary disabled" <?= get_plan_feature_limit_reached_info() ?>>
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('campaigns.create') ?>
                    </button>
                <?php else: ?>
                    <button type="button" data-toggle="modal" data-target="#campaign_create_modal" class="btn btn-primary" data-tooltip data-toggle="tooltip" data-html="true" title="<?= get_plan_feature_limit_info($data->campaigns_total, $this->user->plan_settings->campaigns_limit, isset($data->filters) ? !$data->filters->has_applied_filters : true) ?>"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('campaigns.create') ?></button>
                <?php endif ?>
            </div>

            <div>
                <a href="<?= url('campaigns-import') ?>" class="btn btn-outline-primary" data-toggle="tooltip" data-html="true" title="<?= l('campaigns_import.menu') ?>">
                    <i class="fas fa-fw fa-upload fa-sm"></i>
                </a>
            </div>

            <div>
                <div class="dropdown">
                    <button type="button" class="btn btn-light dropdown-toggle-simple <?= !empty($data->campaigns) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('campaigns?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('campaigns?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
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
                    <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= !empty($data->campaigns) || $data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip data-html="true" title="<?= l('global.filters.tooltip') ?>" data-tooltip-hide-on-click>
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
                                    <option value="domain" <?= $data->filters->search_by == 'domain' ? 'selected="selected"' : null ?>><?= l('campaigns.domain') ?></option>
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

                            <?php if(settings()->notifications->domains_is_enabled): ?>
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
                                    <option value="campaign_id" <?= $data->filters->order_by == 'campaign_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                    <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                    <option value="last_datetime" <?= $data->filters->order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                    <option value="name" <?= $data->filters->order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                    <option value="domain" <?= $data->filters->order_by == 'domain' ? 'selected="selected"' : null ?>><?= l('campaigns.domain') ?></option>
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
                <button id="bulk_enable" type="button" class="btn btn-light <?= !empty($data->campaigns) ? null : 'disabled' ?>" data-toggle="tooltip" title="<?= l('global.bulk_actions') ?>"><i class="fas fa-fw fa-sm fa-list"></i></button>

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

    <?php if (!empty($data->campaigns)): ?>
        <form id="table" action="<?= SITE_URL . 'campaign/bulk' ?>" method="post" role="form">
            <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
            <input type="hidden" name="type" value="" data-bulk-type />
            <input type="hidden" name="original_request" value="<?= base64_encode(\Altum\Router::$original_request) ?>" />
            <input type="hidden" name="original_request_query" value="<?= base64_encode(\Altum\Router::$original_request_query) ?>" />

            <div class="table-responsive table-custom-container mt-3">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th data-bulk-table class="d-none">
                            <div class="custom-control custom-checkbox">
                                <input id="bulk_select_all" type="checkbox" class="custom-control-input" />
                                <label class="custom-control-label" for="bulk_select_all"></label>
                            </div>
                        </th>
                        <th><?= l('campaigns.campaign') ?></th>
                        <th><?= l('global.status') ?></th>
                        <th class="d-none d-md-table-cell"></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach($data->campaigns as $row): ?>
                        <tr>
                            <td data-bulk-table class="d-none">
                                <div class="custom-control custom-checkbox">
                                    <input id="selected_campaign_id_<?= $row->campaign_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->campaign_id ?>" />
                                    <label class="custom-control-label" for="selected_campaign_id_<?= $row->campaign_id ?>"></label>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <a href="<?= url('campaign/' . $row->campaign_id) ?>"><?= $row->name ?></a>

                                <div class="small d-flex align-items-center text-muted">
                                    <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($row->domain) ?>" class="img-fluid icon-favicon-small mr-1" />

                                    <?= $row->domain ?>

                                    <a href="<?= 'https://' . $row->domain ?>" target="_blank" rel="noreferrer"><i class="fas fa-fw fa-xs fa-external-link-alt text-muted ml-1"></i></a>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <a href="<?= url('campaign/' . $row->campaign_id) ?>" class="badge badge-light">
                                    <i class="fas fa-fw fa-sm fa-window-maximize mr-1"></i> <?= sprintf(l('campaigns.x_notifications'), nr($row->notifications_count)) ?>
                                </a>
                            </td>

                            <td class="text-nowrap">
                                <div class="d-flex">
                                    <div class="custom-control custom-switch" data-toggle="tooltip" title="<?= l('campaigns.table.is_enabled_tooltip') ?>">
                                        <input
                                                type="checkbox"
                                                class="custom-control-input"
                                                id="campaign_is_enabled_<?= $row->campaign_id ?>"
                                                data-row-id="<?= $row->campaign_id ?>"
                                                onchange="ajax_call_helper(event, 'campaigns-ajax', 'is_enabled_toggle')"
                                            <?= $row->is_enabled ? 'checked="checked"' : null ?>
                                        >
                                        <label class="custom-control-label" for="campaign_is_enabled_<?= $row->campaign_id ?>"></label>
                                    </div>
                                </div>
                            </td>

                            <td class="text-nowrap d-none d-md-table-cell">
                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                    <i class="fas fa-fw fa-calendar text-muted"></i>
                                </span>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                    <i class="fas fa-fw fa-history text-muted"></i>
                                </span>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-link text-secondary dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
                                            <i class="fas fa-fw fa-ellipsis-v"></i>
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="<?= url('campaign/' . $row->campaign_id) ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-pager mr-2"></i> <?= l('global.view') ?></a>
                                            <a href="<?= url('campaign/' . $row->campaign_id . '/statistics') ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-chart-bar mr-2"></i> <?= l('statistics.link') ?></a>
                                            <a href="#" data-toggle="modal" data-target="#campaign_update_modal" data-campaign-id="<?= $row->campaign_id ?>" data-name="<?= $row->name ?>" data-domain="<?= $row->domain ?>" data-domain-id="<?= $row->domain_id ?>" data-email-reports="<?= $row->email_reports ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>

                                            <a
                                                    href="#"
                                                    data-toggle="modal"
                                                    data-target="#campaign_pixel_key_modal"
                                                    data-pixel-key="<?= $row->pixel_key ?>"
                                                    data-campaign-id="<?= $row->campaign_id ?>"
                                                    data-base-url="<?= $row->domain_id ? $data->domains[$row->domain_id]->scheme . $data->domains[$row->domain_id]->host . '/' : SITE_URL ?>"
                                                    class="dropdown-item"
                                            ><i class="fas fa-fw fa-sm fa-code mr-2"></i> <?= l('campaign.pixel_key') ?></a>

                                            <div <?= $this->user->plan_settings->custom_branding ? null : get_plan_feature_disabled_info() ?>>
                                                <a
                                                        href="#"
                                                    <?php if($this->user->plan_settings->custom_branding): ?>
                                                        data-toggle="modal"
                                                        data-target="#campaign_custom_branding_modal"
                                                        data-campaign-id="<?= $row->campaign_id ?>"
                                                        data-branding-name="<?= e($row->branding->name ?? '') ?>"
                                                        data-branding-url="<?= $row->branding->url ?? '' ?>"
                                                        class="dropdown-item"
                                                    <?php else: ?>
                                                        class="dropdown-item container-disabled"
                                                    <?php endif ?>
                                                >
                                                    <i class="fas fa-fw fa-sm fa-random mr-2"></i> <?= l('campaign.custom_branding') ?>
                                                </a>
                                            </div>

                                            <a href="#" data-toggle="modal" data-target="#campaign_duplicate_modal" data-campaign-id="<?= $row->campaign_id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-clone mr-2"></i> <?= l('global.duplicate') ?></a>

                                            <a href="#" data-toggle="modal" data-target="#campaign_delete_modal" data-campaign-id="<?= $row->campaign_id ?>" data-resource-name="<?= $row->name ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
                                        </div>
                                    </div>
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
            'name' => 'campaigns',
            'has_secondary_text' => true,
        ]); ?>

    <?php endif ?>

</div>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/campaign/campaign_create_modal.php', ['domains' => $data->domains, 'user' => $this->user, 'notification_handlers' => $data->notification_handlers]), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/campaign/campaign_pixel_key_modal.php', ['domains' => $data->domains]), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/campaign/campaign_update_modal.php', ['domains' => $data->domains, 'user' => $this->user, 'notification_handlers' => $data->notification_handlers]), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/duplicate_modal.php', ['modal_id' => 'campaign_duplicate_modal', 'resource_id' => 'campaign_id', 'path' => 'campaign/duplicate']), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/campaign/campaign_custom_branding_modal.php'), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'campaign',
    'resource_id' => 'campaign_id',
    'has_dynamic_resource_name' => true,
    'path' => 'campaign/delete'
]), 'modals'); ?>

<?php require THEME_PATH . 'views/partials/js_bulk.php' ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/bulk_delete_modal.php'), 'modals'); ?>
