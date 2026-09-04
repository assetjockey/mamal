<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="transfer_downloads_reset_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-eraser text-dark mr-2"></i>
                        <?= l('statistics_reset_modal.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form name="transfer_downloads_reset_modal" method="post" action="<?= url('transfer-downloads/reset') ?>" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="transfer_id" value="" />
                    <input type="hidden" name="start_date" value="" />
                    <input type="hidden" name="end_date" value="" />

                    <p class="text-muted"><?= l('statistics_reset_modal.subheader') ?></p>

                    <div class="mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('global.reset') ?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';
    
    /* On modal show load new data */
    $('#transfer_downloads_reset_modal').on('show.bs.modal', event => {
        let transfer_id = $(event.relatedTarget).data('transfer-id');
        let start_date = $(event.relatedTarget).data('start-date');
        let end_date = $(event.relatedTarget).data('end-date');

        $(event.currentTarget).find('input[name="transfer_id"]').val(transfer_id);
        $(event.currentTarget).find('input[name="start_date"]').val(start_date);
        $(event.currentTarget).find('input[name="end_date"]').val(end_date);
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
