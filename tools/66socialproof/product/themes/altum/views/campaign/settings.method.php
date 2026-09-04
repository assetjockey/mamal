<?php defined('ALTUMCODE') || die() ?>

<?php if($this->user->plan_settings->notifications_limit != -1 && $data->notifications_total > $this->user->plan_settings->notifications_limit): ?>
    <div class="alert alert-danger">
        <i class="fas fa-fw fa-times-circle text-danger mr-2"></i> <?= sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), '<strong>' . $data->notifications_total - $this->user->plan_settings->notifications_limit, mb_strtolower(l('notifications.title')) . '</strong>', '<a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '</a>') ?>
    </div>
<?php endif ?>

<div class="row mt-5 mb-4">
    <div class="col-12 col-lg mb-3 mb-lg-0">
        <h2 class="h5"><?= l('campaign.notifications.link') ?></h2>
    </div>

    <div class="col-12 col-lg-auto d-flex flex-wrap gap-3 d-print-none">
        <div>
            <?php if($this->user->plan_settings->notifications_limit != -1 && $data->notifications_total >= $this->user->plan_settings->notifications_limit): ?>
                <button type="button" class="btn btn-primary disabled" <?= get_plan_feature_limit_reached_info() ?>>
                    <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('notifications.create') ?>
                </button>
            <?php else: ?>
                <a href="<?= url('notification-create/' . $data->campaign->campaign_id) ?>" class="btn btn-primary" data-toggle="tooltip" data-html="true" title="<?= get_plan_feature_limit_info($data->notifications_total, $this->user->plan_settings->notifications_limit, isset($data->filters) ? !$data->filters->has_applied_filters : true) ?>"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('notifications.create') ?></a>
            <?php endif ?>
        </div>

        <div>
            <div class="dropdown">
                <button type="button" class="btn btn-light dropdown-toggle-simple <?= !empty($data->notifications) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                    <i class="fas fa-fw fa-sm fa-download"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-right d-print-none">
                    <a href="<?= url('campaign/' . $data->campaign->campaign_id . '?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                        <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                    </a>
                    <a href="<?= url('campaign/' . $data->campaign->campaign_id . '?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
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
                <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= !empty($data->notifications) || $data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip data-html="true" title="<?= l('global.filters.tooltip') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-filter"></i></button>

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
                            <label for="filters_type" class="small"><?= l('global.type') ?></label>
                            <select name="type" id="filters_type" class="custom-select custom-select-sm">
                                <option value=""><?= l('global.all') ?></option>
                                <?php foreach(require APP_PATH . 'includes/enabled_notifications.php' as $notification_type => $notification_config): ?>

                                    <?php
                                    $notification_is_enabled = $this->user->plan_settings->enabled_notifications->{$notification_type} ?? false;

                                    if(!$notification_is_enabled) {
                                        continue;
                                    }

                                    ?>

                                    <option value="<?= $notification_type ?>" <?= isset($data->filters->filters['type']) && $data->filters->filters['type'] == '1' ? 'selected="selected"' : null ?>>
                                        <?= l('notification.' . mb_strtolower($notification_type) . '.name') ?>
                                    </option>
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
                                <option value="notification_id" <?= $data->filters->order_by == 'notification_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $data->filters->order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="name" <?= $data->filters->order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                <option value="impressions" <?= $data->filters->order_by == 'impressions' ? 'selected="selected"' : null ?>><?= l('notification.statistics.impressions_chart') ?></option>
                                <option value="hovers" <?= $data->filters->order_by == 'hovers' ? 'selected="selected"' : null ?>><?= l('notification.statistics.hovers_chart') ?></option>
                                <option value="clicks" <?= $data->filters->order_by == 'clicks' ? 'selected="selected"' : null ?>><?= l('notification.statistics.clicks_chart') ?></option>
                                <option value="form_submissions" <?= $data->filters->order_by == 'form_submissions' ? 'selected="selected"' : null ?>><?= l('notification.statistics.form_submissions_chart') ?></option>
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
            <button id="bulk_enable" type="button" class="btn btn-light <?= !empty($data->notifications) ? null : 'disabled' ?>" data-toggle="tooltip" title="<?= l('global.bulk_actions') ?>"><i class="fas fa-fw fa-sm fa-list"></i></button>

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

<?php if (!empty($data->notifications)): ?>
    <form id="table" action="<?= SITE_URL . 'notification/bulk' ?>" method="post" role="form">
        <input type="hidden" name="campaign_id" value="<?= $data->campaign->campaign_id ?>" />
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
                    <th><?= l('notifications.notification') ?></th>
                    <th><?= l('notification.statistics.link') ?></th>
                    <th class="d-none d-md-table-cell"><?= l('notifications.table.display_trigger') ?></th>
                    <th class="d-none d-md-table-cell"><?= l('notifications.table.display_duration') ?></th>
                    <th><?= l('global.status') ?></th>
                    <th class="d-none d-md-table-cell"></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                <?php foreach($data->notifications as $row): ?>
                    <tr>
                        <td data-bulk-table class="d-none">
                            <div class="custom-control custom-checkbox">
                                <input id="selected_notification_id_<?= $row->notification_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->notification_id ?>" />
                                <label class="custom-control-label" for="selected_notification_id_<?= $row->notification_id ?>"></label>
                            </div>
                        </td>

                        <td class="text-nowrap">
                            <div class="d-flex align-items-center">
                                <div class="notification-avatar rounded-circle mr-3" style="background-color: <?= $data->notifications_config[$row->type]['notification_background_color'] ?>; color: <?= $data->notifications_config[$row->type]['notification_color'] ?>">
                                    <i class="<?= l('notification.' . mb_strtolower($row->type) . '.icon') ?>"></i>
                                </div>

                                <div class="d-flex flex-column">
                                    <a href="<?= url('notification/' . $row->notification_id) ?>"><?= $row->name ?></a>

                                    <div class="text-muted font-size-small mt-1">
                                        <?= l('notification.' . mb_strtolower($row->type) . '.name') ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="text-nowrap">
                            <?php ob_start() ?>
                            <div class='d-flex flex-column text-left'>
                                <div class='d-flex flex-column my-1'>
                                    <div><?= l('notification.statistics.impressions_chart') ?></div>
                                    <strong>
                                        <?= nr($row->impressions) ?>
                                    </strong>
                                </div>

                                <div class='d-flex flex-column my-1'>
                                    <div><?= l('notification.statistics.hovers_chart') ?></div>
                                    <strong>
                                        <?= nr($row->hovers) . '/' . nr($row->impressions) ?>

                                        <span class='text-muted'>
                                            <?= ' (' . nr(get_percentage_between_two_numbers($row->hovers, $row->impressions)) . '%' . ')' ?>
                                        </span>
                                    </strong>
                                </div>

                                <div class='d-flex flex-column my-1'>
                                    <div><?= l('notification.statistics.clicks_chart') ?></div>
                                    <strong>
                                        <?= nr($row->clicks) . '/' . nr($row->impressions) ?>

                                        <span class='text-muted'>
                                            <?= ' (' . nr(get_percentage_between_two_numbers($row->clicks, $row->impressions)) . '%' . ')' ?>
                                        </span>
                                    </strong>
                                </div>

                                <?php if(in_array($row->type, ['collector_modal', 'collector_two_modal', 'countdown_collector', 'email_collector', 'request_collector', 'text_feedback', 'score_feedback', 'emoji_feedback'])): ?>
                                <div class='d-flex flex-column my-1'>
                                    <div><?= l('notification.statistics.form_submissions_chart') ?></div>
                                    <strong>
                                        <?= nr($row->form_submissions) . '/' . nr($row->impressions) ?>

                                        <span class='text-muted'>
                                            <?= ' (' . nr(get_percentage_between_two_numbers($row->form_submissions, $row->impressions)) . '%' . ')' ?>
                                        </span>
                                    </strong>
                                </div>
                                <?php endif ?>
                            </div>
                            <?php $tooltip = ob_get_clean(); ?>

                            <a href="<?= url('notification/' . $row->notification_id . '/statistics') ?>" class="badge badge-primary" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                <i class="fas fa-fw fa-sm fa-eye mr-1"></i> <?= nr($row->impressions) ?>
                            </a>
                        </td>

                        <td class="text-nowrap d-none d-md-table-cell">
                            <div class="text-muted d-flex flex-column">

                                <?php
                                switch($row->settings->display_trigger) {
                                    case 'delay':
                                    case 'time_on_site':
                                    case 'inactivity':
                                    case 'target_visible':

                                        echo '<span>' . $row->settings->display_trigger_value . ' <small>' . l('global.date.seconds') . '</small></span>';
                                        echo '<small>' . l('notification.settings.display_trigger_' . $row->settings->display_trigger) . '</small>';

                                        break;

                                    case 'scroll':

                                        echo $row->settings->display_trigger_value . '%';
                                        echo '<small>' . l('notification.settings.display_trigger_' . $row->settings->display_trigger)  . '</small>';

                                        break;

                                    case 'exit_intent':

                                        echo l('notification.settings.display_trigger_' . $row->settings->display_trigger);

                                        break;

                                    case 'pageviews':

                                        echo nr($row->settings->display_trigger_value) . ' ';
                                        echo l('notification.settings.display_trigger_' . $row->settings->display_trigger);

                                        break;

                                    case 'click':
                                    case 'hover':

                                        echo '<span>' . $row->settings->display_trigger_value . '</span>';
                                        echo '<small>' . l('notification.settings.display_trigger_' . $row->settings->display_trigger) . '</small>';

                                        break;
                                }
                                ?>

                            </div>
                        </td>

                        <td class="text-nowrap d-none d-md-table-cell">
                            <span><?= $row->settings->display_duration == -1 ? l('notifications.table.display_duration_unlimited') : $row->settings->display_duration . ' <small>' . l('global.date.seconds') . '</small>' ?></span>
                        </td>

                        <td class="text-nowrap">
                            <div class="d-flex">
                                <div class="custom-control custom-switch" data-toggle="tooltip" title="<?= l('notifications.table.is_enabled_tooltip') ?>">
                                    <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="notification_is_enabled_<?= $row->notification_id ?>"
                                            data-row-id="<?= $row->notification_id ?>"
                                            onchange="ajax_call_helper(event, 'notifications-ajax', 'is_enabled_toggle')"
                                        <?= $row->is_enabled ? 'checked="checked"' : null ?>
                                    >
                                    <label class="custom-control-label" for="notification_is_enabled_<?= $row->notification_id ?>"></label>
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
                                        <a href="<?= url('notification/' . $row->notification_id) ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
                                        <a href="<?= url('notification/' . $row->notification_id . '/statistics') ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-chart-bar mr-2"></i> <?= l('notification.statistics.link') ?></a>
                                        <a href="#" data-toggle="modal" data-target="#notification_duplicate_modal" data-notification-id="<?= $row->notification_id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-clone mr-2"></i> <?= l('global.duplicate') ?></a>
                                        <a href="#" data-toggle="modal" data-target="#notification_reset_modal" data-notification-id="<?= $row->notification_id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-redo mr-2"></i> <?= l('global.reset') ?></a>
                                        <a href="#" data-toggle="modal" data-target="#notification_delete_modal" data-notification-id="<?= $row->notification_id ?>" data-resource-name="<?= $row->name ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
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
        'name' => 'notifications',
        'has_secondary_text' => true,
    ]); ?>

<?php endif ?>

<?php require THEME_PATH . 'views/partials/js_bulk.php' ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/bulk_delete_modal.php'), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/x_reset_modal.php', ['modal_id' => 'notification_reset_modal', 'resource_id' => 'notification_id', 'path' => 'notification/reset']), 'modals', 'notification_reset_modal'); ?>
