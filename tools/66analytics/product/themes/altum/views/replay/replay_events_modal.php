<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="replay_events_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="p-3">
                <div class="d-flex justify-content-between">
                    <div class="d-flex align-items-center">
                        <h5 class="modal-title"><?= l('replay_events_modal.header') ?></h5>
                        <div class="ml-2">
                            <span data-toggle="tooltip" title="<?= l('replay_events_modal.help') ?>">
                                <i class="fas fa-fw fa-info-circle text-muted"></i>
                            </span>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <div class="modal-body">
                <div class="notification-container"></div>

                <div id="replay_events_result"></div>
            </div>

        </div>
    </div>
</div>
