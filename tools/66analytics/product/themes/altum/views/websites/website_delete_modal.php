<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="website_delete_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-trash-alt text-dark mr-2"></i>
                        <?= l('website_delete_modal.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form name="website_delete" method="post" action="<?= url('websites/delete') ?>" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="website_id" value="" />
                    <input type="hidden" name="original_request" value="<?= base64_encode(\Altum\Router::$original_request) ?>" />
                    <input type="hidden" name="original_request_query" value="<?= base64_encode(\Altum\Router::$original_request_query) ?>" />

                    <div class="notification-container"></div>

                    <p class="text-muted"><?= l('website_delete_modal.subheader') ?></p>

                    <div class="mt-4">
                        <button type="submit" name="submit" class="btn btn-lg btn-block btn-danger"><?= l('global.delete') ?></button>
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
    $('#website_delete_modal').on('show.bs.modal', event => {
        let website_id = $(event.relatedTarget).data('website-id');

        $(event.currentTarget).find('input[name="website_id"]').val(website_id);
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
