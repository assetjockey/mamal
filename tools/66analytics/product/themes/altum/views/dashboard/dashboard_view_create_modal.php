<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="dashboard_view_create_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-bookmark text-dark mr-2"></i>
                        <?= l('dashboard_views.create') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <p class="text-muted"><?= l('dashboard_views.create_help') ?></p>

                <form name="dashboard_view_create" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />

                    <div class="notification-container"></div>

                    <div class="form-group">
                        <label for="dashboard_view_create_name"><i class="fas fa-fw fa-sm fa-signature text-gray-700 mr-1"></i> <?= l('global.name') ?></label>
                        <input id="dashboard_view_create_name" type="text" class="form-control" name="name" maxlength="64" required="required" />
                    </div>

                    <div class="mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-primary" data-is-ajax><?= l('global.create') ?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    $('form[name="dashboard_view_create"]').on('submit', event => {
        let filters = dashboard_view_get_filters();
        let notification_container = event.currentTarget.querySelector('.notification-container');
        notification_container.innerHTML = '';

        if(!filters) {
            display_notifications(<?= json_encode(l('dashboard_views.error_message.no_filters')) ?>, 'error', notification_container);
            event.preventDefault();
            return;
        }

        pause_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

        $.ajax({
            type: 'POST',
            url: `${url}dashboard-views-ajax/create`,
            data: $(event.currentTarget).serialize() + `&filters=${encodeURIComponent(filters)}`,
            dataType: 'json',
            success: (data) => {
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

                if(data.status == 'error') {
                    display_notifications(data.message, 'error', notification_container);
                }

                else if(data.status == 'success') {
                    display_notifications(data.message, 'success', notification_container);

                    if(data.details.dashboard_view_id) {
                        set_cookie('dashboard_view_id', data.details.dashboard_view_id, 30, <?= json_encode(COOKIE_PATH) ?>);
                    }

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
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
