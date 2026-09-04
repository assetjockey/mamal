<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('transfers') ?>"><?= l('transfers.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('transfer.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-paper-plane mr-1"></i> <?= $data->transfer->name ?></h1>

        <div class="d-flex align-items-center col-auto p-0">
            <div>
                <button
                        id="url_copy"
                        type="button"
                        class="btn btn-link text-secondary"
                        data-toggle="tooltip"
                        title="<?= l('global.clipboard_copy') ?>"
                        aria-label="<?= l('global.clipboard_copy') ?>"
                        data-copy="<?= l('global.clipboard_copy') ?>"
                        data-copied="<?= l('global.clipboard_copied') ?>"
                        data-clipboard-text="<?= $data->transfer->full_url ?>"
                >
                    <i class="fas fa-fw fa-sm fa-copy"></i>
                </button>
            </div>

            <div data-toggle="tooltip" title="<?= l('global.share') ?>" aria-label="<?= l('global.share') ?>">
                <button
                        id="share"
                        type="button"
                        class="btn btn-link text-secondary"
                        data-toggle="modal"
                        data-target="#share_modal"
                        data-url="<?= $data->transfer->full_url ?>"
                >
                    <i class="fas fa-fw fa-sm fa-share-alt"></i>
                </button>
            </div>

            <?= include_view(THEME_PATH . 'views/transfers/transfer_dropdown_button.php', ['id' => $data->transfer->transfer_id, 'resource_name' => $data->transfer->name, 'full_url' => $data->transfer->full_url]) ?>
        </div>
    </div>

    <p class="text-truncate">
        <a href="<?= $data->transfer->full_url ?>" target="_blank">
            <i class="fas fa-fw fa-sm fa-external-link-alt text-muted mr-1"></i> <?= remove_url_protocol_from_url($data->transfer->full_url) ?>
        </a>
    </p>

    <div id="transfers_auto_copy_link" class="notification-container"></div>

    <div class="row mt-3">
        <!-- Total Files -->
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-copy text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate">
                    <?= sprintf(l('transfer.widget.total_files'), '<span class="h6">' . nr($data->files_stats['total_files']) . '</span>') ?>
                </div>
            </div>
        </div>

        <!-- Total Files Size -->
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-hdd text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate">
                    <?= sprintf(l('transfer.widget.total_files_size'), '<span class="h6">' . get_formatted_bytes($data->files_stats['total_size']) . '</span>') ?>
                </div>
            </div>
        </div>

        <!-- Downloads Limit -->
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden position-relative">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-download text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate">
                    <a href="<?= url('transfer-downloads/' . $data->transfer->transfer_id) ?>" class="text-reset text-decoration-none stretched-link">
                        <?= (new \Altum\Models\Transfers())->get_downloads_limit_text($data->transfer->downloads, $data->transfer->downloads_limit) ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Pageviews -->
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden position-relative">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-chart-bar text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate">
                    <a href="<?= url('transfer-statistics/' . $data->transfer->transfer_id) ?>" class="text-reset text-decoration-none stretched-link">
                        <?= sprintf(l('transfer.widget.pageviews'), '<span class="h6">' . nr($data->transfer->pageviews) . '</span>') ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Transfer Type (Link / Email) -->
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate" data-toggle="tooltip" title="<?= $data->transfer->type == 'link' ? $data->transfer->url : $data->transfer->email_to ?>">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <?php if($data->transfer->type == 'link'): ?>
                            <i class="fas fa-fw fa-sm fa-link text-primary"></i>
                        <?php else: ?>
                            <i class="fas fa-fw fa-sm fa-envelope text-primary"></i>
                        <?php endif ?>
                    </div>
                </div>
                <div class="card-body text-truncate">
                    <?= l('transfer.type.' . $data->transfer->type) ?>
                </div>
            </div>
        </div>

        <!-- Expiration Date -->
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-calendar text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate">
                    <?= (new \Altum\Models\Transfers())->get_expiration_datetime_text($data->transfer->expiration_datetime) ?>
                </div>
            </div>
        </div>

        <!-- Datetime (Created) -->
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate"
             data-toggle="tooltip"
             data-html="true"
             title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($data->transfer->datetime, 2) . '<br /><small>' . \Altum\Date::get($data->transfer->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->transfer->datetime) . ')</small>') ?>">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-clock text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate">
                    <?= $data->transfer->datetime ? \Altum\Date::get_timeago($data->transfer->datetime) : l('global.na') ?>
                </div>
            </div>
        </div>

        <!-- Datetime (Last) -->
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate"
             data-toggle="tooltip"
             data-html="true"
             title="<?= sprintf(l('global.last_datetime_tooltip'), ($data->transfer->last_datetime ? '<br />' . \Altum\Date::get($data->transfer->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($data->transfer->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->transfer->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-clock-rotate-left text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate">
                    <?= $data->transfer->last_datetime ? \Altum\Date::get_timeago($data->transfer->last_datetime) : l('global.na') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="my-5">
        <div class="d-flex align-items-center mb-3">
            <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-copy mr-1"></i> <?= l('transfer.table.files') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>
        </div>

        <?php if (!empty($data->files)): ?>
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th><?= l('global.name') ?></th>
                        <th><?= l('files.size') ?></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach($data->files as $row): ?>

                        <tr>
                            <td class="text-nowrap">
                                <span title="<?= $row->original_name ?>"><?= string_truncate($row->original_name, 32) ?></span>
                            </td>

                            <td class="text-nowrap">
                                <span class="badge badge-info"><?= get_formatted_bytes($row->size) ?></span>
                            </td>

                            <td class="text-nowrap">
                                <?php if($row->is_encrypted): ?>
                                    <span class="mr-2" data-toggle="tooltip" title="<?= l('transfers.file_encryption_is_enabled') . ': ' . l('global.yes') ?>">
                                        <i class="fas fa-fw fa-fingerprint text-primary"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" title="<?= l('transfers.file_preview_not_possible') ?>">
                                        <i class="fas fa-fw fa-eye-slash text-muted"></i>
                                    </span>
                                <?php else: ?>
                                    <span class="mr-2" data-toggle="tooltip" title="<?= l('transfers.file_encryption_is_enabled') . ': ' . l('global.no') ?>">
                                        <i class="fas fa-fw fa-fingerprint text-muted"></i>
                                    </span>

                                    <?php $file_extension = mb_strtolower(pathinfo($row->name, PATHINFO_EXTENSION)); ?>

                                    <?php if(in_array($file_extension, explode(',', settings()->transfers->preview_file_extensions))): ?>
                                        <a href="<?= url('preview/' . bin2hex($row->file_uuid)) ?>" target="_blank" class="mr-2" data-toggle="tooltip" title="<?= l('transfers.file_preview') ?>">
                                            <i class="fas fa-fw fa-eye text-primary"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="mr-2" data-toggle="tooltip" title="<?= l('transfers.file_preview_not_possible') ?>">
                                            <i class="fas fa-fw fa-eye-slash text-muted"></i>
                                        </span>
                                    <?php endif ?>
                                <?php endif ?>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <a href="<?= url('files/download/' . $row->file_id) ?>" target="_blank" class="btn btn-link dropdown-toggle-simple" data-tooltip title="<?= l('global.download') ?>" data-tooltip-hide-on-click>
                                        <i class="fas fa-fw fa-sm fa-download"></i>
                                    </a>

                                    <?= include_view(THEME_PATH . 'views/files/file_dropdown_button.php', ['id' => $row->file_id, 'resource_name' => $row->original_name]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>

                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                'filters_get' => $data->filters->get ?? [],
                'name' => 'transfer.files',
                'has_secondary_text' => false,
            ]); ?>
        <?php endif ?>

        <?php if(!$data->transfer->settings->file_encryption_is_enabled): ?>
        <div id="upload_main_dropzone" class="card py-3 upload-drag-over upload-drag-over-inactive position-relative mt-4">
            <div class="upload-hint-wrapper">
                <div class="upload-hint-badge font-size-smaller d-none d-lg-block"><?= l('transfer.drop_files_help') ?></div>
            </div>

            <div class="card-body">
                <form id="upload_form" action="<?= url('transfer/update_api') ?>" method="post" role="form" enctype="multipart/form-data" data-endpoint="transfer/update_api">
                    <input type="hidden" name="global_token" value="<?= \Altum\Csrf::get('global_token') ?>" />
                    <input type="hidden" name="transfer_id" value="<?= $data->transfer->transfer_id ?>" />
                    <input type="hidden" name="existing_total_files" value="<?= $data->transfer->total_files ?>" />
                    <input type="hidden" name="existing_total_size" value="<?= $data->transfer->total_size ?>" />

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

                                <?php if(!is_logged_in() && settings()->captcha->transfer_upload_is_enabled): ?>
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
        <?php endif ?>

        <div class="my-5">
            <div class="d-flex align-items-center mb-3">
                <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-chart-bar mr-1"></i> <?= l('transfer.table.latest_statistics') ?></h2>

                <div class="flex-fill">
                    <hr class="border-gray-100" />
                </div>
            </div>

            <?php if (!empty($data->statistics)): ?>
                <div class="table-responsive table-custom-container">
                    <table class="table table-custom">
                        <thead>
                        <tr>
                            <th class="">
                                <div><?= l('global.country') ?></div>
                                <div><?= l('global.city') ?></div>
                            </th>
                            <th class=""><?= l('transfer_statistics.table.device') ?></th>
                            <th class="">
                                <div><?= l('transfer_statistics.table.os') ?></div>
                                <div><?= l('transfer_statistics.table.browser') ?></div>
                            </th>
                            <th class=""><?= l('transfer_statistics.table.referrer') ?></th>
                            <th class=""><?= l('global.datetime') ?></th>
                        </tr>
                        </thead>

                        <tbody>

                        <?php foreach($data->statistics as $row): ?>
                            <tr>
                                <td class="text-nowrap">
                                    <div class="d-flex align-items-center">
                                        <div class="table-image-wrapper mr-3">
                                            <img src="<?= ASSETS_FULL_URL . 'images/countries/' . ($row->country_code ? mb_strtolower($row->country_code) : 'unknown') . '.svg' ?>" class="img-fluid icon-favicon" />
                                        </div>

                                        <div class="d-flex flex-column">
                                            <span class=""><?= $row->country_code ? get_country_from_country_code($row->country_code) : l('global.unknown') ?></span>
                                            <span class="text-muted small"><?= $row->city_name ?? l('global.unknown') ?></span>
                                        </div>
                                    </div>
                                </td>

                                <td class="text-nowrap">
                                <span class="badge badge-light">
                                    <?= $row->device_type ? '<i class="fas fa-fw fa-sm fa-' . $row->device_type . ' mr-1"></i>' . l('global.device.' . $row->device_type) : l('global.unknown') ?>
                                </span>
                                </td>

                                <td class="text-nowrap">
                                    <div>
                                        <img src="<?= ASSETS_FULL_URL . 'images/os/' . os_name_to_os_key($row->os_name) . '.svg' ?>" class="img-fluid icon-favicon-small mr-1" />
                                        <span class="font-size-small"><?= $row->os_name ?: l('global.unknown') ?></span>
                                    </div>
                                    <div>
                                        <img src="<?= ASSETS_FULL_URL . 'images/browsers/' . browser_name_to_browser_key($row->browser_name) . '.svg' ?>" class="img-fluid icon-favicon-small mr-1" />
                                        <span class="font-size-small"><?= $row->browser_name ?: l('global.unknown') ?></span>
                                    </div>
                                </td>

                                <td class="text-nowrap">
                                    <?php if(!$row->referrer_host): ?>
                                        <span><?= l('transfer_statistics.referrer_direct') ?></span>
                                    <?php else: ?>
                                        <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($row->referrer_host) ?>" class="img-fluid icon-favicon mr-1" loading="lazy" />
                                        <a href="<?= url('transfer-statistics/' . $data->transfer->transfer_id . '?type=referrer_path&referrer_host=' . $row->referrer_host . '&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" title="<?= $row->referrer_host ?>" class=""><?= $row->referrer_host ?></a>
                                        <a href="<?= 'https://' . $row->referrer_host ?>" target="_blank" rel="nofollow noopener" class="text-muted ml-1"><i class="fas fa-fw fa-xs fa-external-link-alt"></i></a>
                                    <?php endif ?>
                                </td>

                                <td class="text-nowrap">
                                    <span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($row->datetime, 1) ?>"><?= \Altum\Date::get_timeago($row->datetime) ?></span>
                                </td>
                            </tr>
                        <?php endforeach ?>

                        <tr>
                            <td colspan="5">
                                <a href="<?= url('transfer-statistics/' . $data->transfer->transfer_id . '?type=entries') ?>" class="text-muted">
                                    <i class="fas fa-angle-right fa-sm fa-fw mr-1"></i> <?= l('global.view_more') ?>
                                </a>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                    'filters_get' => $data->filters->get ?? [],
                    'name' => 'transfer.latest_statistics',
                    'has_secondary_text' => false,
                ]); ?>
            <?php endif ?>
        </div>

        <div class="my-5">
            <div class="d-flex align-items-center mb-3">
                <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-chart-bar mr-1"></i> <?= l('transfer.table.latest_downloads') ?></h2>

                <div class="flex-fill">
                    <hr class="border-gray-100" />
                </div>
            </div>

            <?php if (!empty($data->downloads)): ?>
                <div class="table-responsive table-custom-container">
                    <table class="table table-custom">
                        <thead>
                        <tr>
                            <th class="">
                                <div><?= l('global.country') ?></div>
                                <div><?= l('global.city') ?></div>
                            </th>
                            <th class=""><?= l('transfer_statistics.table.device') ?></th>
                            <th class="">
                                <div><?= l('transfer_statistics.table.os') ?></div>
                                <div><?= l('transfer_statistics.table.browser') ?></div>
                            </th>
                            <th class=""><?= l('transfer_statistics.table.referrer') ?></th>
                            <th class=""><?= l('global.datetime') ?></th>
                        </tr>
                        </thead>

                        <tbody>

                        <?php foreach($data->downloads as $row): ?>
                            <tr>
                                <td class="text-nowrap">
                                    <div class="d-flex align-items-center">
                                        <div class="table-image-wrapper mr-3">
                                            <img src="<?= ASSETS_FULL_URL . 'images/countries/' . ($row->country_code ? mb_strtolower($row->country_code) : 'unknown') . '.svg' ?>" class="img-fluid icon-favicon" />
                                        </div>

                                        <div class="d-flex flex-column">
                                            <span class=""><?= $row->country_code ? get_country_from_country_code($row->country_code) : l('global.unknown') ?></span>
                                            <span class="text-muted small"><?= $row->city_name ?? l('global.unknown') ?></span>
                                        </div>
                                    </div>
                                </td>

                                <td class="text-nowrap">
                        <span class="badge badge-light">
                            <?= $row->device_type ? '<i class="fas fa-fw fa-sm fa-' . $row->device_type . ' mr-1"></i>' . l('global.device.' . $row->device_type) : l('global.unknown') ?>
                        </span>
                                </td>

                                <td class="text-nowrap">
                                    <div>
                                        <img src="<?= ASSETS_FULL_URL . 'images/os/' . os_name_to_os_key($row->os_name) . '.svg' ?>" class="img-fluid icon-favicon-small mr-1" />
                                        <span class="font-size-small"><?= $row->os_name ?: l('global.unknown') ?></span>
                                    </div>
                                    <div>
                                        <img src="<?= ASSETS_FULL_URL . 'images/browsers/' . browser_name_to_browser_key($row->browser_name) . '.svg' ?>" class="img-fluid icon-favicon-small mr-1" />
                                        <span class="font-size-small"><?= $row->browser_name ?: l('global.unknown') ?></span>
                                    </div>
                                </td>

                                <td class="text-nowrap">
                                    <?php if(!$row->referrer_host): ?>
                                        <span><?= l('transfer_statistics.referrer_direct') ?></span>
                                    <?php else: ?>
                                        <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($row->referrer_host) ?>" class="img-fluid icon-favicon mr-1" loading="lazy" />
                                        <a href="<?= url('transfer-statistics/' . $data->transfer->transfer_id . '?type=referrer_path&referrer_host=' . $row->referrer_host . '&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" title="<?= $row->referrer_host ?>" class=""><?= $row->referrer_host ?></a>
                                        <a href="<?= 'https://' . $row->referrer_host ?>" target="_blank" rel="nofollow noopener" class="text-muted ml-1"><i class="fas fa-fw fa-xs fa-external-link-alt"></i></a>
                                    <?php endif ?>
                                </td>

                                <td class="text-nowrap">
                                    <span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($row->datetime, 1) ?>"><?= \Altum\Date::get_timeago($row->datetime) ?></span>
                                </td>
                            </tr>
                        <?php endforeach ?>

                        <tr>
                            <td colspan="5">
                                <a href="<?= url('transfer-statistics/' . $data->transfer->transfer_id . '?type=entries') ?>" class="text-muted">
                                    <i class="fas fa-angle-right fa-sm fa-fw mr-1"></i> <?= l('global.view_more') ?>
                                </a>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                    'filters_get' => $data->filters->get ?? [],
                    'name' => 'transfer.latest_downloads',
                    'has_secondary_text' => false,
                ]); ?>
            <?php endif ?>
        </div>

    </div>
</div>


<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'file',
    'resource_id' => 'file_id',
    'has_dynamic_resource_name' => true,
    'path' => 'files/delete'
]), 'modals'); ?>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

<?php ob_start() ?>
    <script>
        'use strict';

        const query_parameters = new URLSearchParams(window.location.search);

        if (query_parameters.has('auto_copy_link')) {
            let text = document.querySelector('#url_copy').getAttribute('data-clipboard-text');
            let notification_container = document.querySelector('#transfers_auto_copy_link');

            navigator.clipboard.writeText(text).then(() => {
                display_notifications(<?= json_encode(l('transfer.auto_copy_link.success')) ?>, 'success', notification_container);
            }).catch((error) => {
                display_notifications(<?= json_encode(l('transfer.auto_copy_link.error')) ?>, 'error', notification_container);
            });
        }
    </script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php include_once THEME_PATH . 'views/partials/uploader_js.php' ?>
