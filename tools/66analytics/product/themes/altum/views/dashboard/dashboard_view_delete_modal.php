<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="dashboard_view_delete_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-trash-alt text-dark mr-2"></i>
                        <?= l('delete_modal.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <p class="text-muted text-break" id="dashboard_view_delete_modal_subheader"></p>

                <span class="d-none" id="dashboard_view_delete_modal_subheader_hidden">
                    <?= l('delete_modal.subheader1') ?>
                </span>

                <form name="dashboard_view_delete_modal_form" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="dashboard_view_id" value="" />

                    <div class="notification-container"></div>

                    <div class="mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-danger" data-is-ajax><?= l('global.delete') ?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    $('#dashboard_view_delete_modal').on('show.bs.modal', event => {
        let related_target = event.relatedTarget;
        let current_target = event.currentTarget;
        let dashboard_view_id = related_target.getAttribute('data-dashboard-view-id');
        let name = dashboard_views[dashboard_view_id].name;

        current_target.querySelector('form[name="dashboard_view_delete_modal_form"] input[name="dashboard_view_id"]').setAttribute('value', dashboard_view_id);
        current_target.querySelector('#dashboard_view_delete_modal_subheader').textContent = current_target.querySelector('#dashboard_view_delete_modal_subheader_hidden').textContent.replace('%s', name);
        current_target.querySelector('.notification-container').innerHTML = '';
    });

    $('form[name="dashboard_view_delete_modal_form"]').on('submit', event => {
        let notification_container = event.currentTarget.querySelector('.notification-container');
        let dashboard_view_id = event.currentTarget.querySelector('input[name="dashboard_view_id"]').value;
        notification_container.innerHTML = '';
        pause_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

        $.ajax({
            type: 'POST',
            url: `${url}dashboard-views-ajax/delete`,
            data: $(event.currentTarget).serialize(),
            dataType: 'json',
            success: (data) => {
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

                if(data.status == 'error') {
                    display_notifications(data.message, 'error', notification_container);
                }

                else if(data.status == 'success') {
                    display_notifications(data.message, 'success', notification_container);

                    if(get_cookie('dashboard_view_id') == dashboard_view_id) {
                        delete_cookie('dashboard_view_id', <?= json_encode(COOKIE_PATH) ?>);
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
