<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="create_notification_data" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-circle-plus text-dark mr-2"></i>
                        <?= l('notification.create_data_modal.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form name="create_notification_data" method="post" role="form" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="request_type" value="create" />
                    <input type="hidden" name="notification_id" value="<?= $data->notification->notification_id ?>" />

                    <div class="notification-container"></div>

                    <button class="btn btn-block btn-gray-300 mb-4" type="button" data-toggle="collapse" data-target="#csv_container" aria-expanded="false" aria-controls="">
                        <?= l('notification.create_data_modal.csv_header') ?>
                    </button>

                    <div class="collapse" id="csv_container">
                        <div class="form-group">
                            <label for="csv"><i class="fas fa-fw fa-sm fa-file-csv text-muted mr-1"></i> <?= l('notification.create_data_modal.csv') ?></label>
                            <input id="csv" type="file" name="csv" accept=".csv" class="form-control-file altum-file-input" />
                        </div>
                    </div>

                    <button class="btn btn-block btn-gray-300 mb-4" type="button" data-toggle="collapse" data-target="#data_container" aria-expanded="false" aria-controls="">
                        <?= l('notification.create_data_modal.data_header') ?>
                    </button>

                    <div class="collapse" id="data_container">
                        <div id="keys_values">
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label><i class="fas fa-fw fa-sm fa-key text-muted mr-1"></i> <?= l('notification.create_data_modal.key') ?></label>
                                    <input type="text" class="form-control" name="key[]" placeholder="<?= l('notification.create_data_modal.key_placeholder') ?>" />
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group">
                                    <label><i class="fas fa-fw fa-sm fa-server text-muted mr-1"></i> <?= l('notification.create_data_modal.value') ?></label>
                                    <input type="text" class="form-control" name="value[]" placeholder="<?= l('notification.create_data_modal.value_placeholder') ?>" />
                                </div>
                            </div>
                        </div>
                    </div>

                        <div class="mb-4">
                            <button type="button" id="create_key_value" class="btn btn-outline-success btn-sm"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('notification.create_data_modal.create_key_value') ?></button>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-primary" data-is-ajax><?= l('global.submit') ?></button>
                    </div>
                </form>
            </div>

            <div id="key_value_sample" style="display: none;">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label><i class="fas fa-fw fa-sm fa-key text-muted mr-1"></i> <?= l('notification.create_data_modal.key') ?></label>
                            <input type="text" class="form-control" name="key[]" />
                        </div>
                    </div>

                    <div class="col">
                        <div class="form-group">
                            <label><i class="fas fa-fw fa-sm fa-server text-muted mr-1"></i> <?= l('notification.create_data_modal.value') ?></label>
                            <input type="text" class="form-control" name="value[]" />
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';
    
    /* Add new trigger rule handler */
    $('#create_key_value').on('click', () => {

        let key_value_sample = $('#key_value_sample').html();

        $('#keys_values').append(key_value_sample);
    });

    $('form[name="create_notification_data"]').on('submit', event => {
        let form = $('form[name="create_notification_data"]')[0];
        let data = new FormData(form);

        let notification_container = event.currentTarget.querySelector('.notification-container');
        notification_container.innerHTML = '';
        pause_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

        $.ajax({
            type: 'POST',
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            cache: false,
            url: `${url}notification-data-ajax`,
            data: data,
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
