<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="broadcast_subscriber_update_status_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-user-check text-dark mr-2"></i>
                        <?= l('admin_broadcast_subscribers.update_status_modal.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form name="broadcast_subscriber_update_status_modal" method="post" action="<?= url('admin/broadcast-subscribers/update_status') ?>" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="broadcast_subscriber_id" value="" />

                    <div class="form-group">
                        <label for="broadcast_subscriber_update_status"><?= l('global.status') ?></label>
                        <select id="broadcast_subscriber_update_status" name="status" class="custom-select">
                            <option value="1"><?= l('admin_broadcast_subscribers.status.subscribed') ?></option>
                            <option value="2"><?= l('admin_broadcast_subscribers.status.unsubscribed') ?></option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('global.update') ?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    $('#broadcast_subscriber_update_status_modal').on('show.bs.modal', event => {
        let broadcast_subscriber_id = $(event.relatedTarget).data('broadcast-subscriber-id');
        let status = $(event.relatedTarget).data('status');

        status = status == 0 ? 1 : status;

        $(event.currentTarget).find('input[name="broadcast_subscriber_id"]').val(broadcast_subscriber_id);
        $(event.currentTarget).find('select[name="status"]').val(status);
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'broadcast_subscriber_update_status_modal_js') ?>
