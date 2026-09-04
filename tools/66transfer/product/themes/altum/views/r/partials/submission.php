<?php defined('ALTUMCODE') || die() ?>

<div id="upload_main_dropzone" class="card position-relative py-6 upload-drag-over upload-drag-over-inactive">
    <div class="upload-hint-wrapper upload-hint-wrapper-top">
        <div class="upload-hint-badge upload-hint-badge-arrow font-size-smaller"><?= l('transfer.drop_files_help') ?></div>
    </div>

    <div class="card-body">
        <?= \Altum\Alerts::output_alerts() ?>

        <div class="row justify-content-center">
            <div class="col-10">
                <div class="mb-5 text-center">
                    <h1 class="h3"><?= sprintf(l('r_transfer_request.submission.header'), $data->transfer_request->name)  ?></h1>
                    <?php if($data->transfer_request->description): ?>
                        <p class="text-muted"><?= $data->transfer_request->description ?></p>
                    <?php endif ?>

                    <?php if($data->transfer_request->expiration_datetime): ?>
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-fw fa-sm fa-hourglass-half mr-1"></i> <?= sprintf(l('r_transfer_request.submission.expiration_datetime'), \Altum\Date::get_time_until($data->transfer_request->expiration_datetime)) ?>
                        </p>
                    <?php endif ?>
                </div>
            </div>
        </div>

        <form id="upload_form" action="<?= url('transfer-request/update_api') ?>" method="post" role="form" enctype="multipart/form-data" data-endpoint="transfer-request/update_api">
            <input type="hidden" name="global_token" value="<?= \Altum\Csrf::get('global_token') ?>" />
            <input type="hidden" name="transfer_request_id" value="<?= $data->transfer_request->transfer_request_id ?>" />
            <input type="hidden" name="existing_total_files" value="<?= $data->transfer_request->total_files ?>" />
            <input type="hidden" name="existing_total_size" value="<?= $data->transfer_request->total_size ?>" />
            <input type="hidden" name="password" value="<?= $_COOKIE['transfer_request_password_' . $this->transfer_request->transfer_request_id] ?>" />

            <div class="notification-container"></div>

            <div class="row">
                <div class="col-12 col-lg-6 offset-lg-3">
                    <?php if($this->user->plan_settings->transfers_limit != 0 && ((!is_logged_in() && settings()->plan_guest->status != 0) || is_logged_in())): ?>
                        <button id="upload_select_files" type="button" class="btn btn-block btn-outline-primary select-files-button rounded-2x mb-3 mb-lg-0 mr-lg-3">
                            <i class="fas fa-fw fa-xs fa-plus-circle mr-1"></i> <?= l('transfer.select_files') ?>
                        </button>

                        <div class="mt-3 text-center">
                            <button id="upload_select_folders" type="button" class="btn btn-sm btn-link text-decoration-none text-muted">
                                <i class="fas fa-fw fa-sm fa-folder-plus mr-1"></i> <?= l('transfer.select_folder') ?>
                            </button>
                        </div>
                    <?php endif ?>
                </div>
            </div>

            <div id="upload_previews_wrapper" class="d-none mt-4">
                <div id="upload_previews_settings"></div>

                <div class="row">
                    <div class="col-12 col-lg-10 offset-lg-1" id="upload_previews_files">
                        <div class="row align-items-center bg-gray-100 rounded py-1 font-weight-bold">
                            <div class="col text-truncate text-muted">
                                <span id="upload_total_files"></span>
                            </div>
                            <div class="col-auto px-0">
                                <span id="upload_total_size" class="text-muted"></span>
                            </div>

                            <div class="col-auto pl-0">
                                <button id="upload_remove_all" type="button" class="btn btn-sm btn-link text-muted" title="<?= l('global.delete') ?>" data-dz-remove>
                                    <i class="fas fa-fw fa-sm fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>

                        <div id="upload_previews" class="upload-previews"></div>

                        <?php if(!is_logged_in() && settings()->captcha->transfer_request_upload_is_enabled): ?>
                            <div class="form-group mt-4 mb-0">
                                <?php $data->captcha->display() ?>
                            </div>
                        <?php endif ?>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-12 col-lg-10 offset-lg-1">
                        <button id="upload_submit" type="submit" name="submit" class="btn btn-block btn-primary submit-transfer-button mb-3 mb-lg-0 mr-lg-3" data-is-ajax>
                            <i class="fas fa-fw fa-xs fa-cloud-upload-alt mr-1"></i> <?= l('files.submit') ?>
                        </button>
                    </div>
                </div>
            </div>

            <template id="upload_preview_template" class="d-none">
                <div class="row align-items-center my-3 no-gutters" data-altum-uuid>
                    <div class="col text-truncate">
                        <i data-altum-icon></i>
                        <span class="ml-2" data-altum-name></span>
                    </div>

                    <div class="col-auto">
                        <span class="text-muted" data-altum-size></span>
                    </div>

                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-link text-muted" title="<?= l('global.delete') ?>" data-altum-remove>
                            <i class="fas fa-fw fa-sm fa-trash-alt"></i>
                        </button>
                    </div>

                    <div class="col-12">
                        <div class="progress" style="height: .5rem;font-size:.5rem;font-weight:bold;">
                            <div class="progress-bar" role="progressbar" style="width: 0;" aria-valuemin="0" aria-valuemax="100" data-altum-upload-progress></div>
                        </div>
                    </div>
                </div>
            </template>

        </form>

    </div>
</div>

<?php include_once THEME_PATH . 'views/partials/uploader_js.php' ?>

