<?php defined('ALTUMCODE') || die() ?>

<div class="container">
	<?= \Altum\Alerts::output_alerts() ?>

	<?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('transfer-requests') ?>"><?= l('transfer_requests.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('transfer_request.breadcrumb') ?></li>
            </ol>
        </nav>
	<?php endif ?>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-inbox mr-1"></i> <?= $data->transfer_request->name ?></h1>

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
                        data-clipboard-text="<?= $data->transfer_request->full_url ?>"
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
                        data-url="<?= $data->transfer_request->full_url ?>"
                >
                    <i class="fas fa-fw fa-sm fa-share-alt"></i>
                </button>
            </div>

			<?= include_view(THEME_PATH . 'views/transfer-requests/transfer_request_dropdown_button.php', ['id' => $data->transfer_request->transfer_request_id, 'resource_name' => $data->transfer_request->name, 'full_url' => $data->transfer_request->full_url]) ?>
        </div>
    </div>

    <p class="text-truncate">
        <a href="<?= $data->transfer_request->full_url ?>" target="_blank">
            <i class="fas fa-fw fa-sm fa-external-link-alt text-muted mr-1"></i> <?= remove_url_protocol_from_url($data->transfer_request->full_url) ?>
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

        <!-- Submissions -->
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden position-relative">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-upload text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate">
                    <a href="<?= url('transfer-request-submissions/' . $data->transfer_request->transfer_request_id) ?>" class="text-reset text-decoration-none stretched-link">
						<?= sprintf(l('transfer_requests.total_submissions_x'), '<span class="h6">' . nr($data->transfer_request->total_submissions) . '</span>') ?>
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
                    <a href="<?= url('transfer-request-statistics/' . $data->transfer_request->transfer_request_id) ?>" class="text-reset text-decoration-none stretched-link">
						<?= sprintf(l('transfer.widget.pageviews'), '<span class="h6">' . nr($data->transfer_request->pageviews) . '</span>') ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Has password -->
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate" data-toggle="tooltip" title="<?= ($data->transfer_request->settings->password ? l('global.yes') : l('global.no')) ?>">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
						<?php if($data->transfer_request->settings->password): ?>
                            <i class="fas fa-fw fa-sm fa-lock text-primary"></i>
						<?php else: ?>
                            <i class="fas fa-fw fa-sm fa-lock-open text-muted"></i>
						<?php endif ?>
                    </div>
                </div>
                <div class="card-body text-truncate">
					<?= l('global.password') ?>
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
					<?= (new \Altum\Models\Transfers())->get_expiration_datetime_text($data->transfer_request->expiration_datetime) ?>
                </div>
            </div>
        </div>

        <!-- Datetime (Created) -->
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate"
             data-toggle="tooltip"
             data-html="true"
             title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($data->transfer_request->datetime, 2) . '<br /><small>' . \Altum\Date::get($data->transfer_request->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->transfer_request->datetime) . ')</small>') ?>">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-clock text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate">
					<?= $data->transfer_request->datetime ? \Altum\Date::get_timeago($data->transfer_request->datetime) : l('global.na') ?>
                </div>
            </div>
        </div>

        <!-- Datetime (Last) -->
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate"
             data-toggle="tooltip"
             data-html="true"
             title="<?= sprintf(l('global.last_datetime_tooltip'), ($data->transfer_request->last_datetime ? '<br />' . \Altum\Date::get($data->transfer_request->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($data->transfer_request->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->transfer_request->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-clock-rotate-left text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate">
					<?= $data->transfer_request->last_datetime ? \Altum\Date::get_timeago($data->transfer_request->last_datetime) : l('global.na') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="my-5">
        <div class="d-flex align-items-center mb-3">
            <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-upload mr-1"></i> <?= l('transfer_request.submissions_and_files') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>
        </div>

		<?php if(!empty($data->request_submissions)): ?>
			<?php $i = 1; ?>

			<?php foreach($data->request_submissions as $request_submission): ?>
                <div class="card mb-4">
                    <div class="card-header bg-white border-bottom border-gray-100 text-muted font-size-small">
                        <div class="d-flex justify-content-between">
                            <div class="d-flex align-items-lg-center flex-column flex-lg-row">
                                <span class="font-weight-bold"><?= sprintf(l('transfer_request.submission_x'), $i++) ?></span>

                                <div class="font-weight-normal d-flex align-items-center mt-1 mt-lg-0 ml-lg-3">
									<?php if($request_submission->device_type): ?>
                                        <span class="mr-2" data-toggle="tooltip" title="<?= l('global.device.' . $request_submission->device_type) ?>">
                                            <i class="fas fa-fw fa-sm fa-<?= $request_submission->device_type ?> text-muted"></i>
                                        </span>
									<?php endif ?>


                                    <img src="<?= ASSETS_FULL_URL . 'images/os/' . os_name_to_os_key($request_submission->os_name) . '.svg' ?>" class="img-fluid icon-favicon mr-2" data-toggle="tooltip" title="<?= $request_submission->os_name ?: l('global.unknown') ?>" />

                                    <img src="<?= ASSETS_FULL_URL . 'images/browsers/' . browser_name_to_browser_key($request_submission->browser_name) . '.svg' ?>" class="img-fluid icon-favicon mr-2" data-toggle="tooltip" title="<?= $request_submission->browser_name ?: l('global.unknown') ?>" />

                                    <span class="mr-2" data-toggle="tooltip" title="<?= get_continent_from_continent_code($request_submission->continent_code ?? l('global.unknown')) ?>">
                                        <i class="fas fa-fw fa-globe-europe text-muted"></i>
                                    </span>

									<?php if($request_submission->country_code): ?>
                                        <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($request_submission->country_code) . '.svg' ?>" class="icon-favicon mr-2" data-toggle="tooltip" title="<?= get_country_from_country_code($request_submission->country_code) ?>" />
									<?php else: ?>
                                        <span class="mr-2" data-toggle="tooltip" title="<?= l('global.unknown') ?>">
                                            <i class="fas fa-fw fa-flag text-muted"></i>
                                        </span>
									<?php endif ?>

                                    <span class="mr-2" data-toggle="tooltip" title="<?= $request_submission->city_name ?? l('global.unknown') ?>">
                                        <span class="font-size-smaller font-weight-500"><?= $request_submission->city_name ?: get_country_from_country_code($request_submission->country_code) ?></span>
                                    </span>
                                </div>
                            </div>

                            <div class="font-weight-normal">
                                <span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($request_submission->datetime, 1) ?>">
                                    <i class="fas fa-fw fa-sm fa-clock mr-1"></i><?= \Altum\Date::get_timeago($request_submission->datetime) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
						<?php if(!empty($request_submission->files)): ?>
							<?php foreach($request_submission->files as $row): ?>
                                <div class="row align-items-center">
                                    <div class="col-5 col-lg-6 text-truncate">
                                        <span title="<?= $row->original_name ?>"><?= string_truncate($row->original_name, 48) ?></span>
                                    </div>

                                    <div class="col-4 col-lg-2">
                                        <span class="badge badge-info"><?= get_formatted_bytes($row->size) ?></span>
                                    </div>

                                    <div class="col-1 col-lg-2">
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
                                    </div>

                                    <div class="col-2 col-lg-2">
                                        <div class="d-flex justify-content-end">
                                            <a href="<?= url('files/download/' . $row->file_id) ?>" target="_blank" class="btn btn-link dropdown-toggle-simple" data-tooltip title="<?= l('global.download') ?>" data-tooltip-hide-on-click>
                                                <i class="fas fa-fw fa-sm fa-download"></i>
                                            </a>
											<?= include_view(THEME_PATH . 'views/files/file_dropdown_button.php', ['id' => $row->file_id, 'resource_name' => $row->original_name]) ?>
                                        </div>
                                    </div>
                                </div>
							<?php endforeach ?>
						<?php else: ?>
                            <tr>
                                <td colspan="4" class="text-muted">
									<?= l('global.none') ?>
                                </td>
                            </tr>
						<?php endif ?>
                    </div>
                </div>
			<?php endforeach ?>
		<?php else: ?>
			<?= include_view(THEME_PATH . 'views/partials/no_data.php', [
				'filters_get' => $data->filters->get ?? [],
				'name' => 'transfer_request.submissions',
				'has_secondary_text' => false,
			]); ?>
		<?php endif ?>
    </div>

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
                                    <a href="<?= url('transfer-request-statistics/' . $data->transfer_request->transfer_request_id . '?type=referrer_path&referrer_host=' . $row->referrer_host . '&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" title="<?= $row->referrer_host ?>" class=""><?= $row->referrer_host ?></a>
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
                            <a href="<?= url('transfer-request-statistics/' . $data->transfer_request->transfer_request_id . '?type=entries') ?>" class="text-muted">
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
