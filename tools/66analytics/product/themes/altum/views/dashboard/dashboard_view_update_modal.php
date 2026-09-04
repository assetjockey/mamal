<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="dashboard_view_update_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-pen text-dark mr-2"></i>
                        <?= l('dashboard_views.update') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form name="dashboard_view_update" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="dashboard_view_id" value="" />

                    <div class="notification-container"></div>

                    <div class="form-group">
                        <label for="dashboard_view_update_name"><i class="fas fa-fw fa-sm fa-signature text-gray-700 mr-1"></i> <?= l('global.name') ?></label>
                        <input id="dashboard_view_update_name" type="text" class="form-control" name="name" maxlength="64" required="required" />
                    </div>

                    <div class="mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-primary" data-is-ajax><?= l('global.update') ?></button>
                    </div>

                    <div class="mt-3">
                        <button type="button" id="dashboard_view_update_filters" class="btn btn-block btn-outline-secondary">
                            <i class="fas fa-fw fa-sm fa-sync mr-1"></i> <?= l('dashboard_views.overwrite') ?>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    $('#dashboard_view_update_modal').on('show.bs.modal', event => {
        let dashboard_view_id = $(event.relatedTarget).attr('data-dashboard-view-id');
        let name = dashboard_views[dashboard_view_id].name;

        $(event.currentTarget).find('input[name="dashboard_view_id"]').val(dashboard_view_id);
        $(event.currentTarget).find('input[name="name"]').val(name);
        $(event.currentTarget).find('.notification-container').html('');
    });

    $('form[name="dashboard_view_update"]').on('submit', event => {
        let notification_container = event.currentTarget.querySelector('.notification-container');
        notification_container.innerHTML = '';
        pause_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

        $.ajax({
            type: 'POST',
            url: `${url}dashboard-views-ajax/update`,
            data: $(event.currentTarget).serialize(),
            dataType: 'json',
            success: (data) => {
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

                if(data.status == 'error') {
                    display_notifications(data.message, 'error', notification_container);
                }

                else if(data.status == 'success') {
                    display_notifications(data.message, 'success', notification_container);

                    setTimeout(() => window.location.href = window.location.href, 1000);
                }
            },
            error: () => {
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));
                display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            },
        });

        event.preventDefault();
    });

    $('#dashboard_view_update_filters').on('click', event => {
        let form = event.currentTarget.closest('form');
        let filters = dashboard_view_get_filters();
        let notification_container = form.querySelector('.notification-container');
        notification_container.innerHTML = '';

        if(!filters) {
            display_notifications(<?= json_encode(l('dashboard_views.error_message.no_filters')) ?>, 'error', notification_container);
            event.preventDefault();
            return;
        }

        pause_submit_button(event.currentTarget);

        $.ajax({
            type: 'POST',
            url: `${url}dashboard-views-ajax/update`,
            data: $(form).serialize() + `&filters=${encodeURIComponent(filters)}`,
            dataType: 'json',
            success: (data) => {
                enable_submit_button(event.currentTarget);

                if(data.status == 'error') {
                    display_notifications(data.message, 'error', notification_container);
                }

                else if(data.status == 'success') {
                    display_notifications(data.message, 'success', notification_container);

                    set_cookie('dashboard_view_id', form.querySelector('input[name="dashboard_view_id"]').value, 30, <?= json_encode(COOKIE_PATH) ?>);

                    setTimeout(() => window.location.href = window.location.href, 1000);
                }
            },
            error: () => {
                enable_submit_button(event.currentTarget);
                display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            },
        });

        event.preventDefault();
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
