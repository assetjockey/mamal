<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="audits_bulk_refresh_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-retweet text-dark mr-2"></i>
                        <?= l('audits_bulk_refresh_modal.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <p class="text-muted"><?= l('audits_bulk_refresh_modal.subheader') ?></p>

                <div class="mt-4">
                    <button type="submit" name="submit" form="table" class="btn btn-lg btn-block btn-primary" onclick="document.querySelector('#table input[data-bulk-type]').value = 'refresh'">
                        <?= l('audits_bulk_refresh_modal.button') ?>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
