<?php defined('ALTUMCODE') || die(); ?>

<div class="notification-container"></div>

<div class="mt-5 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h4 text-truncate mb-0"><?= l('notification.data.header') ?></h2>

        <div class="d-flex align-items-center col-auto p-0">
            <button type="button" data-toggle="modal" data-target="#create_notification_data" class="btn btn-sm btn-primary"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('notification.data.create') ?></button>

            <?php if (!empty($data->conversions)): ?>
                <div class="ml-3">
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                            <i class="fas fa-fw fa-sm fa-download"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right d-print-none">
                            <a href="<?= url('notification/' . $data->notification->notification_id . '/data/' . $data->datetime['start_date'] . '/' . $data->datetime['end_date'] . '?page=' . ($_GET['page'] ?? 1) . '&json') ?>" target="_blank" class="dropdown-item">
                                <i class="fas fa-fw fa-sm fa-file-code mr-2"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                            </a>
                            <a href="#" class="dropdown-item <?= $this->user->plan_settings->export->pdf ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->pdf ? 'onclick="event.preventDefault(); window.print();"' : get_plan_feature_disabled_info() ?>>
                                <i class="fas fa-fw fa-sm fa-file-pdf mr-2"></i> <?= sprintf(l('global.export_to'), 'PDF') ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <div class="ml-3">
                <button id="bulk_enable" type="button" class="btn btn-sm btn-light <?= !empty($data->conversions) ? null : 'disabled' ?>" data-toggle="tooltip" title="<?= l('global.bulk_actions') ?>"><i class="fas fa-fw fa-sm fa-list"></i></button>

                <div id="bulk_group" class="btn-group d-none" role="group">
                    <div class="btn-group btn-group-sm dropdown" role="group">
                        <button id="bulk_actions" type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                            <?= l('global.bulk_actions') ?> <span id="bulk_counter" class="d-none"></span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="bulk_actions">
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#bulk_delete_modal"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
                        </div>
                    </div>

                    <button id="bulk_disable" type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" title="<?= l('global.close') ?>"><i class="fas fa-fw fa-times"></i></button>
                </div>
            </div>

            <div class="ml-3">
                <div
                        id="daterangepicker"
                        role="button"
                        class="btn btn-sm btn-light"
                        data-min-date="<?= \Altum\Date::get('2000-01-01', 4) ?>"
                        data-max-date="<?= \Altum\Date::get('', 4) ?>"
                >
                    <i class="fas fa-fw fa-calendar mr-lg-1"></i>
                    <span class="d-none d-lg-inline-block">
                        <?php if($data->datetime['start_date'] == $data->datetime['end_date']): ?>
                            <?= \Altum\Date::get($data->datetime['start_date'], 6, \Altum\Date::$default_timezone) ?>
                        <?php else: ?>
                            <?= \Altum\Date::get($data->datetime['start_date'], 6, \Altum\Date::$default_timezone) . ' - ' . \Altum\Date::get($data->datetime['end_date'], 6, \Altum\Date::$default_timezone) ?>
                        <?php endif ?>
                    </span>
                    <i class="fas fa-fw fa-caret-down d-none d-lg-inline-block ml-lg-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>


<?php if(!count($data->conversions)): ?>

    <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
        'filters_get' => $data->filters->get ?? [],
        'name' => 'global',
        'has_secondary_text' => false,
        'has_wrapper' => true,
    ]); ?>

<?php else: ?>

    <form id="table" action="<?= SITE_URL . 'notification-data-ajax/bulk' ?>" method="post" role="form">
        <input type="hidden" name="notification_id" value="<?= $data->notification->notification_id ?>" />
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
                    <th><?= l('notification.data.data') ?></th>
                    <th><?= l('notification.data.type') ?></th>
                    <th><?= l('notification.data.date') ?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="accordion" id="accordion">

                <?php foreach($data->conversions as $row): ?>
                    <tr data-id="<?= $row->id ?>">
                        <td data-bulk-table class="d-none">
                            <div class="custom-control custom-checkbox">
                                <input id="selected_id_<?= $row->id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->id ?>" />
                                <label class="custom-control-label" for="selected_id_<?= $row->id ?>"></label>
                            </div>
                        </td>

                        <td class="text-nowrap clickable" data-toggle="collapse" data-target="#<?= 'data_collapse_' . $row->id ?>" aria-expanded="true" aria-controls="<?= 'data_collapse_' . $row->id ?>">
                            <strong><?= l('notification.data.expand_data') ?></strong>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge badge-light">
                                <i class="fas fa-fw fa-sm fa-database mr-1"></i>
                                <?= l('notification.data.type_' . $row->type) ?>
                            </span>
                        </td>

                        <td class="text-nowrap"><span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($row->datetime, 1) ?>"><?= \Altum\Date::get($row->datetime, 2) ?></span></td>

                        <td>
                            <div class="d-flex justify-content-end">
                                <div class="dropdown">
                                    <button type="button" class="btn btn-link text-secondary dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
                                        <i class="fas fa-fw fa-ellipsis-v"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="#" class="dropdown-item" data-delete-notification-data="<?= l('delete_modal.subheader2') ?>" data-row-id="<?= $row->id ?>"><i class="fas fa-fw fa-trash-alt fa-sm mr-2"></i> <?= l('global.delete') ?></a>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr id="<?= 'data_collapse_' . $row->id ?>" data-id="<?= $row->id ?>" data-notification-id="<?= $row->notification_id ?>" class="collapse" data-parent="#accordion">
                        <td colspan="5">
                            <div class="d-flex justify-content-center">
                                <div class="spinner-grow"></div>
                            </div>
                        </td>
                    </tr>

                <?php endforeach ?>

                </tbody>
            </table>
        </div>
    </form>

    <div class="mt-3"><?= $data->pagination ?></div>
<?php endif ?>


<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/daterangepicker.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment-timezone-with-data-10-year-range.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    'use strict';

moment.tz.setDefault(<?= json_encode($this->user->timezone) ?>);

    /* Daterangepicker */
    $('#daterangepicker').daterangepicker({
        startDate: <?= json_encode($data->datetime['start_date']) ?>,
        endDate: <?= json_encode($data->datetime['end_date']) ?>,
        minDate: $('#daterangepicker').data('min-date'),
        maxDate: $('#daterangepicker').data('max-date'),
        ranges: {
            <?= json_encode(l('global.date.today')) ?>: [moment(), moment()],
            <?= json_encode(l('global.date.yesterday')) ?>: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            <?= json_encode(l('global.date.this_week')) ?>: [moment().startOf('week'), moment().endOf('week')],

            <?= json_encode(l('global.date.last_30_days')) ?>: [moment().subtract(29, 'days'), moment()],
                <?= json_encode(l('global.date.this_month')) ?>: [moment().startOf('month'), moment().endOf('month')],
            <?= json_encode(l('global.date.last_month')) ?>: [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                <?= json_encode(l('global.date.this_year')) ?>: [moment().startOf('year'), moment()],
                <?= json_encode(l('global.date.last_year')) ?>: [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
            <?= json_encode(l('global.date.all_time')) ?>: [moment($('#daterangepicker').data('min-date')), moment()]
        },
        alwaysShowCalendars: true,
        linkedCalendars: false,
        singleCalendar: true,
        locale: <?= json_encode(require APP_PATH . 'includes/daterangepicker_translations.php') ?>,
    }, (start, end, label) => {

        /* Redirect */
        redirect(`<?= url('notification/' . $data->notification->notification_id . '/data') ?>?start_date=${start.format('YYYY-MM-DD')}&end_date=${end.format('YYYY-MM-DD')}`, true);

    });


    /* Handle the opening and closing of the details for conversions */
    $('[id^="data_collapse_"]').on('show.bs.collapse', event => {
        let id = $(event.currentTarget).data('id');
        let notification_id = $(event.currentTarget).data('notification-id');
        let request_type = 'read_data_conversion';

        $.ajax({
            type: 'GET',
            url: `${url}notifications-ajax?id=${id}&notification_id=${notification_id}&global_token=${global_token}&request_type=${request_type}`,
            success: (result) => {

                $(event.currentTarget).find('td').html(result.details.html);

                /* Refresh tooltips */
                $('[data-toggle="tooltip"]').tooltip();

            },
            dataType: 'json'
        });


    });

    /* Delete handler for the conversion */
    $('[data-delete-notification-data]').on('click', event => {
        let message = $(event.currentTarget).attr('data-delete-notification-data');

        if(!confirm(message)) return false;

        /* Close all active dropdowns */
        $('.dropdown-menu').removeClass('show');
        $('.dropdown').removeClass('show');
        $('.dropdown-toggle').attr('aria-expanded', 'false');

        let notification_container = document.querySelector('.notification-container');
        notification_container.innerHTML = '';

        /* Continue with the deletion */
        ajax_call_helper(event, 'notification-data-ajax', 'delete', () => {

            display_notifications(<?= json_encode(l('global.success_message.delete2')) ?>, 'success', notification_container);

            document.querySelectorAll(`tr[data-id="${event.currentTarget.getAttribute('data-row-id')}"]`).forEach(element => element.remove());

        });
    });
</script>

<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php require THEME_PATH . 'views/partials/js_bulk.php' ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/bulk_delete_modal.php'), 'modals'); ?>
