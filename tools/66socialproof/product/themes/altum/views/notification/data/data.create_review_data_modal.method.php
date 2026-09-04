<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="create_notification_data" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-circle-plus text-dark mr-2"></i>
                        <?= l('notification.create_review_data_modal.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form name="create_notification_data" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="request_type" value="create" />
                    <input type="hidden" name="notification_id" value="<?= $data->notification->notification_id ?>" />

                    <div class="notification-container"></div>

                    <div class="form-group">
                        <label><?= l('notification.create_review_data_modal.title') ?></label>
                        <input type="hidden" class="form-control" name="key[]" value="title" required="required" />
                        <input type="text" class="form-control" name="value[]" required="required" />
                    </div>

                    <div class="form-group">
                        <label><?= l('notification.create_review_data_modal.description') ?></label>
                        <input type="hidden" class="form-control" name="key[]" value="description" required="required" />
                        <input type="text" class="form-control" name="value[]" required="required" />
                    </div>

                    <div class="form-group">
                        <label><?= l('notification.settings.image') ?></label>
                        <input type="hidden" class="form-control" name="key[]" value="image" required="required" />
                        <input type="text" class="form-control" name="value[]" required="required" placeholder="<?= l('global.url_placeholder') ?>" />
                    </div>

                    <div class="form-group">
                        <label for=""><?= l('notification.settings.image_alt') ?></label>
                        <input type="hidden" class="form-control" name="key[]" value="image_alt" required="required" />
                        <input type="text" class="form-control" name="value[]" maxlength="100" />
                        <small class="form-text text-muted"><?= l('notification.settings.image_alt_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label><?= l('notification.create_review_data_modal.stars') ?></label>
                        <input type="hidden" class="form-control" name="key[]" value="stars" />
                        <input type="number" value="5" min="1" max="5" class="form-control" name="value[]" />
                        <small class="form-text text-muted"><?= l('notification.create_review_data_modal.stars_help') ?></small>
                    </div>

                    <div class="text-center mt-4">
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

    $('form[name="create_notification_data"]').on('submit', event => {
        let notification_container = event.currentTarget.querySelector('.notification-container');
        notification_container.innerHTML = '';
        pause_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

        $.ajax({
            type: 'POST',
            url: `${url}notification-data-ajax`,
            data: $(event.currentTarget).serialize(),
            dataType: 'json',
            success: (data) => {
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

                if(data.status == 'error') {
                    display_notifications(data.message, 'error', notification_container);
                }

                else if(data.status == 'success') {
                    display_notifications(data.message, 'success', notification_container);

                    setTimeout(() => {
                        /* Remove the notification */
                        notification_container.innerHTML = '';

                        /* Hide modal */
                        $('#create_notification_data').modal('hide');

                        /* Clear input values */
                        $('form[name="create_notification_data"] input').val('');

                        /* Redirect */
                        redirect(`<?= url('notification/' . $data->notification->notification_id . '/data') ?>`, true);
                    }, 500);
                }
            },
            error: () => {
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));
                display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            },
        });

        event.preventDefault();
    })
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
