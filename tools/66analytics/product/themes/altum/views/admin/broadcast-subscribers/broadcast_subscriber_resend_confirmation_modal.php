<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="broadcast_subscriber_resend_confirmation_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-paper-plane text-dark mr-2"></i>
                        <?= l('admin_broadcast_subscribers.resend_confirmation') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <p class="text-muted"><?= l('admin_broadcast_subscribers.resend_confirmation_modal.subheader') ?></p>

                <form name="broadcast_subscriber_resend_confirmation_modal" method="post" action="<?= url('admin/broadcast-subscribers/resend_confirmation') ?>" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="broadcast_subscriber_id" value="" />

                    <div class="mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('admin_broadcast_subscribers.resend_confirmation') ?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    $('#broadcast_subscriber_resend_confirmation_modal').on('show.bs.modal', event => {
        let broadcast_subscriber_id = $(event.relatedTarget).data('broadcast-subscriber-id');

        $(event.currentTarget).find('input[name="broadcast_subscriber_id"]').val(broadcast_subscriber_id);
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'broadcast_subscriber_resend_confirmation_modal_js') ?>
