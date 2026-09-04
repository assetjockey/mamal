<?php defined('ALTUMCODE') || die() ?>

<div class="container">
	<?= \Altum\Alerts::output_alerts() ?>

    <div class="mb-3 d-flex justify-content-between">
        <div>
            <h1 class="h4 mb-0 text-truncate"><i class="fas fa-fw fa-xs fa-table-cells mr-1"></i> <?= l('dashboard.header') ?></h1>
        </div>
    </div>

    <div id="upload_main_dropzone" class="card py-3 upload-drag-over upload-drag-over-inactive position-relative">
        <div class="upload-hint-wrapper upload-hint-wrapper-top">
            <?php if(settings()->transfers->transfer_requests_is_enabled): ?>
            <a href="<?= url('transfer-request-create') ?>" class="upload-hint-badge text-decoration-none font-size-smaller <?= $this->user->plan_settings->transfer_requests_limit != 0 ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->transfer_requests_limit != 0 ? null : get_plan_feature_disabled_info() ?>>
                <i class="fas fa-fw fa-sm fa-inbox mr-2"></i> <?= l('transfer_requests.transfer_request') ?>
            </a>
            <?php endif ?>

            <div class="upload-hint-badge upload-hint-badge-arrow font-size-smaller d-none d-lg-block ml-3"><?= l('transfer.drop_files_help') ?></div>
        </div>

        <div class="card-body">
            <form id="upload_form" action="<?= url('transfer/create_api') ?>" method="post" role="form" enctype="multipart/form-data" data-endpoint="transfer/create_api">
                <input type="hidden" name="global_token" value="<?= \Altum\Csrf::get('global_token') ?>" />

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
						<?php elseif(!is_logged_in() && settings()->users->register_is_enabled): ?>
                            <a href="<?= url('register') ?>" target="_blank" class="btn btn-block btn-outline-primary index-button mb-3 mb-lg-0 mr-lg-3">
                                <i class="fas fa-fw fa-xs fa-user-plus mr-1"></i> <?= l('index.register') ?>
                            </a>
						<?php endif ?>
                    </div>
                </div>

                <div id="upload_previews_wrapper" class="d-none mt-4">
                    <div class="row">
                        <div class="col-12 col-lg-5 offset-lg-1 mb-4 mb-lg-0" id="upload_previews_settings">

                            <div class="form-group mb-3">
                                <div class="row btn-group-toggle" data-toggle="buttons">
                                    <div class="col">
                                        <label class="btn btn-sm btn-light btn-block active">
                                            <input type="radio" name="type" value="link" class="custom-control-input" <?= ($this->user->preferences->transfers_default_type ?? 'link') == 'link' ? 'checked="checked"' : null ?> required="required" />
                                            <i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('transfer.type.link') ?>
                                        </label>
                                    </div>
									<?php if(settings()->transfers->email_transfer_is_enabled): ?>
                                        <div class="col">
                                            <label class="btn btn-sm btn-light btn-block">
                                                <input type="radio" name="type" value="email" class="custom-control-input" <?= ($this->user->preferences->transfers_default_type ?? 'link') == 'email' ? 'checked="checked"' : null ?> required="required" />
                                                <i class="fas fa-fw fa-sm fa-envelope text-muted mr-1"></i> <?= l('transfer.type.email') ?>
                                            </label>
                                        </div>
									<?php endif ?>
                                </div>
                            </div>

                            <div class="form-group row mb-3 d-none" data-type="email">
                                <label class="col-sm-4 col-form-label col-form-label-sm text-truncate" for="email_to"><i class="fas fa-fw fa-envelope fa-sm text-muted mr-1"></i> <?= l('transfer.email_to') ?></label>
                                <div class="col-sm-8">
                                    <input type="email" id="email_to" name="email_to" value="" class="form-control form-control-sm" placeholder="<?= l('transfer.email_to_placeholder') ?>" maxlength="320" />
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label class="col-sm-4 col-form-label col-form-label-sm text-truncate" for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                                <div class="col-sm-8">
                                    <input type="text" id="name" name="name" value="" class="form-control form-control-sm" maxlength="256" />
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label class="col-sm-4 col-form-label col-form-label-sm text-truncate" for="description"><i class="fas fa-fw fa-pen fa-sm text-muted mr-1"></i> <?= l('transfer.description') ?></label>
                                <div class="col-sm-8">
                                    <input type="text" id="description" name="description" value="<?= l('transfer.description.default') ?>" class="form-control form-control-sm" maxlength="256" />
                                </div>
                            </div>

							<?php if(count($data->domains) && (settings()->transfers->domains_is_enabled || settings()->transfers->additional_domains_is_enabled)): ?>
                                <div class="form-group row mb-3">
                                    <label class="col-sm-4 col-form-label col-form-label-sm text-truncate" for="domain_id"><i class="fas fa-fw fa-globe fa-sm text-muted mr-1"></i> <?= l('transfer.domain_id') ?></label>
                                    <div class="col-sm-8">
                                        <select id="domain_id" name="domain_id" class="custom-select custom-select-sm">
											<?php if(settings()->transfers->main_domain_is_enabled || \Altum\Authentication::is_admin()): ?>
                                                <option value=" "><?= remove_url_protocol_from_url(SITE_URL) ?></option>
											<?php endif ?>

											<?php foreach($data->domains as $row): ?>
                                                <option value="<?= $row->domain_id ?>"><?= remove_url_protocol_from_url($row->url) ?></option>
											<?php endforeach ?>
                                        </select>
                                    </div>
                                </div>

                                <div <?= $this->user->plan_settings->custom_url_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                                    <div class="<?= $this->user->plan_settings->custom_url_is_enabled ? null : 'container-disabled' ?>">
                                        <div class="form-group row mb-3">
                                            <label class="col-sm-4 col-form-label col-form-label-sm text-truncate" for="url"><i class="fas fa-fw fa-link fa-sm text-muted mr-1"></i> <?= l('transfer.url') ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" id="url" name="url" class="form-control form-control-sm" maxlength="<?= ($this->user->plan_settings->url_maximum_characters ?? 64) ?>" placeholder="<?= l('global.url_slug_placeholder') ?>" onchange="update_this_value(this, get_slug)" onkeyup="update_this_value(this, get_slug)" />
												<?= \Altum\Alerts::output_field_error('url') ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
							<?php else: ?>
                                <div <?= $this->user->plan_settings->custom_url_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                                    <div class="<?= $this->user->plan_settings->custom_url_is_enabled ? null : 'container-disabled' ?>">
                                        <div class="form-group">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><?= remove_url_protocol_from_url(SITE_URL) ?></span>
                                                </div>
                                                <input type="text" id="url" name="url" class="form-control form-control-sm" maxlength="<?= ($this->user->plan_settings->url_maximum_characters ?? 64) ?>" placeholder="<?= l('global.url_slug_placeholder') ?>" onchange="update_this_value(this, get_slug)" onkeyup="update_this_value(this, get_slug)" aria-label="<?= l('transfer.url') ?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
							<?php endif ?>

                            <ul class="nav nav-pills d-flex flex-fill flex-row mb-3" role="tablist">
                                <li class="nav-item flex-fill text-center" role="presentation" data-toggle="tooltip" title="<?= l('transfer.expiration_tab') ?>" data-tooltip-hide-on-click>
                                    <a class="nav-link" id="expiration-tab" data-toggle="pill" href="#pills-expiration" role="tab" aria-controls="pills-home" aria-selected="true">
                                        <i class="fas fa-fw fa-clock"></i>
                                    </a>
                                </li>
                                <li class="nav-item flex-fill text-center" role="presentation" data-toggle="tooltip" title="<?= l('transfer.protection_tab') ?>" data-tooltip-hide-on-click>
                                    <a class="nav-link" id="protection-tab" data-toggle="pill" href="#pills-protection" role="tab" aria-controls="pills-protection" aria-selected="false">
                                        <i class="fas fa-fw fa-lock"></i>
                                    </a>
                                </li>

								<?php if(settings()->transfers->pixels_is_enabled): ?>
                                    <li class="nav-item flex-fill text-center" role="presentation" data-toggle="tooltip" title="<?= l('transfer.pixels_tab') ?>" data-tooltip-hide-on-click>
                                        <a class="nav-link" id="pixels-tab" data-toggle="pill" href="#pills-pixels" role="tab" aria-controls="pills-pixels" aria-selected="false">
                                            <i class="fas fa-fw fa-adjust"></i>
                                        </a>
                                    </li>
								<?php endif ?>

                                <li class="nav-item flex-fill text-center" role="presentation" data-toggle="tooltip" title="<?= l('transfer.advanced_tab') ?>" data-tooltip-hide-on-click>
                                    <a class="nav-link" id="advanced-tab" data-toggle="pill" href="#pills-advanced" role="tab" aria-controls="pills-advanced" aria-selected="false">
                                        <i class="fas fa-fw fa-user-tie"></i>
                                    </a>
                                </li>

								<?php if(settings()->notification_handlers->is_enabled): ?>
                                    <li class="nav-item flex-fill text-center" role="presentation" data-toggle="tooltip" title="<?= l('transfer.notification_handlers_tab') ?>" data-tooltip-hide-on-click>
                                        <a class="nav-link" id="notification-handlers-tab" data-toggle="pill" href="#pills-notification-handlers" role="tab" aria-controls="pills-notification-handlers" aria-selected="false">
                                            <i class="fas fa-fw fa-bell"></i>
                                        </a>
                                    </li>
								<?php endif ?>
                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane fade" id="pills-expiration" role="tabpanel" aria-labelledby="expiration-tab">
                                    <div class="form-group">
										<?php
										$downloads_limit = $this->user->preferences->transfers_default_downloads_limit;
										if($this->user->plan_settings->downloads_per_transfer_limit != -1 && $downloads_limit >= $this->user->plan_settings->downloads_per_transfer_limit) {
											$downloads_limit = $this->user->plan_settings->downloads_per_transfer_limit;
										}
										?>
                                        <label for="downloads_limit"><i class="fas fa-fw fa-download fa-sm text-muted mr-1"></i> <?= l('transfer.downloads_limit') ?></label>
                                        <input type="number" id="downloads_limit" name="downloads_limit" class="form-control form-control-sm" min="1" max="<?= $this->user->plan_settings->downloads_per_transfer_limit == -1 ? null : $this->user->plan_settings->downloads_per_transfer_limit ?>" value="<?= $downloads_limit ?>" />
										<?php if($this->user->plan_settings->downloads_per_transfer_limit == -1): ?>
                                            <small class="form-text text-muted"><?= l('transfer.downloads_limit_help') ?></small>
										<?php endif ?>
                                    </div>

									<?php
									$potential_max_expiration_time = $this->user->plan_settings->transfers_retention == -1 ? null : (new \DateTime())->modify('+' . $this->user->plan_settings->transfers_retention . ' days')->setTimezone(new \DateTimeZone($this->user->timezone))->format('Y-m-d H:i:s');
									$potential_min_expiration_time = (new \DateTime())->setTimezone(new \DateTimeZone($this->user->timezone))->format('Y-m-d H:i:s');

									$expiration_datetime = $potential_max_expiration_time;

									if(
										$this->user->preferences->transfers_default_expiration_datetime
										&& ($this->user->plan_settings->transfers_retention == -1 || $this->user->preferences->transfers_default_expiration_datetime <= $this->user->plan_settings->transfers_retention)
									) {
										$expiration_datetime = (new \DateTime())->setTimezone(new \DateTimeZone($this->user->timezone))->modify('+' . $this->user->preferences->transfers_default_expiration_datetime . ' days')->format('Y-m-d H:i:s');
									}
									?>
                                    <div class="form-group">
                                        <label for="expiration_datetime"><i class="fas fa-fw fa-clock fa-sm text-muted mr-1"></i> <?= l('transfer.expiration_datetime') ?></label>
                                        <input
                                                type="text"
                                                id="expiration_datetime"
                                                name="expiration_datetime"
                                                class="form-control form-control-sm"
                                                value="<?= $expiration_datetime ?>"
                                                autocomplete="off"
                                                data-min-date="<?= $potential_min_expiration_time ?>"
                                                data-max-date="<?= $potential_max_expiration_time ?>"
                                        />
										<?php if($this->user->plan_settings->transfers_retention == -1): ?>
                                            <small class="form-text text-muted"><?= l('transfer.expiration_datetime_help') ?></small>
										<?php endif ?>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="pills-protection" role="tabpanel" aria-labelledby="protection-tab">
                                    <div <?= $this->user->plan_settings->password_protection_is_enabled ? null : get_plan_feature_disabled_info() ?> >
                                        <div class="form-group <?= $this->user->plan_settings->password_protection_is_enabled ? null : 'container-disabled' ?>" data-password-toggle-view data-password-toggle-view-show="<?= l('global.show') ?>" data-password-toggle-view-hide="<?= l('global.hide') ?>">
                                            <label for="password"><i class="fas fa-fw fa-lock fa-sm text-muted mr-1"></i> <?= l('global.password') ?></label>
                                            <input type="password" id="password" name="password" class="form-control form-control-sm" autocomplete="new-password" maxlength="64" />
											<?php if($this->user->plan_settings->downloads_per_transfer_limit == -1): ?>
                                                <small class="form-text text-muted"><?= l('transfer.password_help') ?></small>
											<?php endif ?>
                                        </div>
                                    </div>

                                    <div <?= $this->user->plan_settings->file_encryption_is_enabled ? null : get_plan_feature_disabled_info() ?> <?= $this->user->plan_settings->file_encryption_is_enabled && $this->user->preferences->transfers_auto_file_upload ? 'data-toggle="tooltip" title="' . l('transfer.file_encryption_auto_file_upload') . '"' : null ?>>
                                        <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->file_encryption_is_enabled ? null : 'container-disabled' ?> <?= $this->user->preferences->transfers_auto_file_upload ? 'container-disabled' : null ?>">
                                            <input id="file_encryption_is_enabled" name="file_encryption_is_enabled" type="checkbox" class="custom-control-input" disabled="disabled" <?= $this->user->plan_settings->file_encryption_is_enabled ? null : 'data-plan-feature-no-access' ?>>
                                            <label class="custom-control-label" for="file_encryption_is_enabled"><?= l('transfer.file_encryption') ?></label>
                                            <small class="form-text text-muted"><?= l('transfer.file_encryption_help') ?></small>
                                        </div>
                                    </div>

                                    <div class="form-group custom-control custom-switch">
                                        <input id="file_preview_is_enabled" name="file_preview_is_enabled" type="checkbox" class="custom-control-input" <?= $this->user->preferences->transfers_default_file_preview_is_enabled ? 'checked="checked"' : null ?> <?= $this->user->plan_settings->file_preview_is_enabled ? null : 'data-plan-feature-no-access' ?>>
                                        <label class="custom-control-label" for="file_preview_is_enabled"><?= l('transfer.file_preview') ?></label>
                                        <small class="form-text text-muted"><span data-toggle="tooltip" title="<?= settings()->transfers->preview_file_extensions ?>"><?= l('transfer.file_preview_help') ?></span></small>
                                    </div>

                                    <div class="form-group custom-control custom-switch">
                                        <input id="gallery_file_preview_is_enabled" name="gallery_file_preview_is_enabled" type="checkbox" class="custom-control-input" <?= $this->user->preferences->transfers_default_gallery_file_preview_is_enabled ? 'checked="checked"' : null ?> <?= $this->user->plan_settings->gallery_file_preview_is_enabled ? null : 'data-plan-feature-no-access' ?>>
                                        <label class="custom-control-label" for="gallery_file_preview_is_enabled"><?= l('transfer.gallery_file_preview') ?></label>
                                        <small class="form-text text-muted"><?= l('transfer.gallery_file_preview_help') ?></small>
                                    </div>
                                </div>

								<?php if(settings()->transfers->pixels_is_enabled): ?>
                                    <div class="tab-pane fade" id="pills-pixels" role="tabpanel" aria-labelledby="pixels-tab">
                                        <div <?= $this->user->plan_settings->pixels_limit != 0 ? null : get_plan_feature_disabled_info() ?>>
                                            <div class="form-group <?= $this->user->plan_settings->pixels_limit != 0 ? null : 'container-disabled' ?>">
                                                <div class="d-flex flex-wrap flex-row justify-content-between">
                                                    <label><i class="fas fa-fw fa-adjust fa-sm text-muted mr-1"></i> <?= l('transfer.pixels') ?></label>
                                                    <a href="<?= url('pixel-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('pixels.create') ?></a>
                                                </div>
                                                <div class="row">
													<?php $available_pixels = require APP_PATH . 'includes/t/pixels.php'; ?>
													<?php foreach($data->pixels as $pixel): ?>
                                                        <div class="col-12">
                                                            <div class="custom-control custom-checkbox my-2">
                                                                <input id="pixel_id_<?= $pixel->pixel_id ?>" name="pixels_ids[]" value="<?= $pixel->pixel_id ?>" type="checkbox" class="custom-control-input" <?= in_array($pixel->pixel_id, $this->user->preferences->transfers_default_pixels_ids ?? []) ? 'checked="checked"' : null ?>>
                                                                <label class="custom-control-label d-flex align-items-center" for="pixel_id_<?= $pixel->pixel_id ?>">
                                                                    <span class="text-truncate" title="<?= $pixel->name ?>"><?= $pixel->name ?></span>
                                                                    <small class="badge badge-light ml-1" data-toggle="tooltip" title="<?= $available_pixels[$pixel->type]['name'] ?>">
                                                                        <i class="<?= $available_pixels[$pixel->type]['icon'] ?> fa-fw fa-sm" style="color: <?= $available_pixels[$pixel->type]['color'] ?>"></i>
                                                                    </small>
                                                                </label>
                                                            </div>
                                                        </div>
													<?php endforeach ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								<?php endif ?>

                                <div class="tab-pane fade" id="pills-advanced" role="tabpanel" aria-labelledby="advanced-tab">
									<?php if(settings()->transfers->projects_is_enabled): ?>
                                        <div <?= $this->user->plan_settings->projects_limit != 0 ? null : get_plan_feature_disabled_info() ?>>
                                            <div class="form-group <?= $this->user->plan_settings->projects_limit != 0 ? null : 'container-disabled' ?>">
                                                <div class="d-flex flex-wrap flex-row justify-content-between">
                                                    <label for="project_id"><?= l('projects.project_id') ?></label>
                                                    <a href="<?= url('project-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('projects.create') ?></a>
                                                </div>
                                                <select id="project_id" name="project_id" class="custom-select custom-select-sm">
                                                    <option value=" "><?= l('global.none') ?></option>
													<?php foreach($data->projects as $project_id => $project): ?>
                                                        <option value="<?= $project_id ?>" <?= $project_id == $this->user->preferences->transfers_default_project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
													<?php endforeach ?>
                                                </select>
                                                <small class="form-text text-muted"><?= l('projects.project_id_help') ?></small>
                                            </div>
                                        </div>
									<?php endif ?>

                                    <div <?= $this->user->plan_settings->removable_branding_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                                        <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->removable_branding_is_enabled ? null : 'container-disabled' ?>">
                                            <input id="is_removed_branding" name="is_removed_branding" type="checkbox" class="custom-control-input" <?= $this->user->plan_settings->removable_branding_is_enabled ? ($this->user->preferences->transfers_default_is_removed_branding ? 'checked="checked"' : null) : 'disabled="disabled"' ?>>
                                            <label class="custom-control-label" for="is_removed_branding"><?= l('transfer.is_removed_branding') ?></label>
                                            <small class="form-text text-muted"><?= l('transfer.is_removed_branding_help') ?></small>
                                        </div>
                                    </div>

                                    <div <?= $this->user->plan_settings->custom_css_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                                        <div class="form-group <?= $this->user->plan_settings->custom_css_is_enabled ? null : 'container-disabled' ?>" data-character-counter="textarea">
                                            <label for="custom_css" class="d-flex justify-content-between align-items-center">
                                                <span><i class="fab fa-fw fa-sm fa-css3 text-muted mr-1"></i> <?= l('global.custom_css') ?></span>
                                                <small class="text-muted" data-character-counter-wrapper></small>
                                            </label>
                                            <textarea id="custom_css" class="form-control" name="custom_css" maxlength="10000" placeholder="<?= l('global.custom_css_placeholder') ?>"><?= $this->user->preferences->transfers_default_custom_css ?></textarea>
                                            <small class="form-text text-muted"><?= l('global.custom_css_help') ?></small>
                                        </div>
                                    </div>

                                    <div <?= $this->user->plan_settings->custom_js_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                                        <div class="form-group <?= $this->user->plan_settings->custom_js_is_enabled ? null : 'container-disabled' ?>" data-character-counter="textarea">
                                            <label for="custom_js" class="d-flex justify-content-between align-items-center">
                                                <span><i class="fab fa-fw fa-sm fa-js text-muted mr-1"></i> <?= l('global.custom_js') ?></span>
                                                <small class="text-muted" data-character-counter-wrapper></small>
                                            </label>
                                            <textarea id="custom_js" class="form-control" name="custom_js" maxlength="10000" placeholder="<?= l('global.custom_js_placeholder') ?>"><?= $this->user->preferences->transfers_default_custom_js ?></textarea>
                                            <small class="form-text text-muted"><?= l('global.custom_js_help') ?></small>
                                        </div>
                                    </div>
                                </div>

								<?php if(settings()->notification_handlers->is_enabled): ?>
                                    <div class="tab-pane fade" id="pills-notification-handlers" role="tabpanel" aria-labelledby="notification-handlers-tab">
                                        <div <?= $this->user->plan_id != 'guest' ? null : get_plan_feature_disabled_info() ?>>

                                            <div class="form-group <?= $this->user->plan_settings != 'guest' ? null : 'container-disabled' ?>">
                                                <div class="d-flex flex-wrap flex-row justify-content-between">
                                                    <label><i class="fas fa-fw fa-bell fa-sm text-muted mr-1"></i> <?= l('transfer.download_notification_handlers') ?></label>
                                                    <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                                                </div>

                                                <div class="row">
													<?php foreach($data->notification_handlers as $notification_handler): ?>
                                                        <div class="col-12">
                                                            <div class="custom-control custom-checkbox my-2">
                                                                <input id="download_notification_handlers_ids_<?= $notification_handler->notification_handler_id ?>" name="download_notification_handlers_ids[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $this->user->preferences->transfers_default_download_notification_handlers_ids ?? []) ? 'checked="checked"' : null ?>>
                                                                <label class="custom-control-label" for="download_notification_handlers_ids_<?= $notification_handler->notification_handler_id ?>">
                                                                    <span class="mr-1"><?= $notification_handler->name ?></span>
                                                                    <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                                                </label>
                                                            </div>
                                                        </div>
													<?php endforeach ?>
                                                </div>
                                            </div>

                                            <div class="form-group <?= $this->user->plan_settings != 'guest' ? null : 'container-disabled' ?>">
                                                <div class="d-flex flex-wrap flex-row justify-content-between">
                                                    <label><i class="fas fa-fw fa-bell fa-sm text-muted mr-1"></i> <?= l('transfer.pageview_notification_handlers') ?></label>
                                                    <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                                                </div>

                                                <div class="row">
													<?php foreach($data->notification_handlers as $notification_handler): ?>
                                                        <div class="col-12">
                                                            <div class="custom-control custom-checkbox my-2">
                                                                <input id="pageview_notification_handlers_ids_<?= $notification_handler->notification_handler_id ?>" name="pageview_notification_handlers_ids[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $this->user->preferences->transfers_default_pageview_notification_handlers_ids ?? []) ? 'checked="checked"' : null ?>>
                                                                <label class="custom-control-label" for="pageview_notification_handlers_ids_<?= $notification_handler->notification_handler_id ?>">
                                                                    <span class="mr-1"><?= $notification_handler->name ?></span>
                                                                    <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                                                </label>
                                                            </div>
                                                        </div>
													<?php endforeach ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								<?php endif ?>
                            </div>

							<?php if(!is_logged_in() && settings()->captcha->transfer_upload_is_enabled): ?>
                                <div class="form-group">
									<?php $data->captcha->display() ?>
                                </div>
							<?php endif ?>
                        </div>

                        <div class="col-12 col-lg-5" id="upload_previews_files">
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
                        </div>
                    </div>

                    <div class="row mt-5">
                        <div class="col-12 col-lg-10 offset-lg-1">
                            <button id="upload_submit" type="submit" name="submit" class="btn btn-block btn-primary submit-transfer-button mb-3 mb-lg-0 mr-lg-3" data-is-ajax>
                                <i class="fas fa-fw fa-xs fa-cloud-upload-alt mr-1"></i> <?= l('transfer.submit') ?>
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

    <div class="row my-3">
        <div class="col-12 col-lg-6 col-xl-3 p-3 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= get_plan_feature_limit_info($data->total_transfers, $this->user->plan_settings->transfers_limit) ?>">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <a href="<?= url('transfers') ?>" class="stretched-link">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                            <i class="fas fa-fw fa-sm fa-paper-plane text-primary"></i>
                        </div>
                    </a>
                </div>

                <div class="card-body text-truncate">
					<?= sprintf(l('dashboard.total_transfers'), '<span class="h6">' . nr($data->total_transfers) . '</span>') ?>

                    <div class="progress" style="height: .25rem;">
                        <div class="progress-bar <?= $this->user->plan_settings->transfers_limit == -1 ? 'bg-success' : null ?>" role="progressbar" style="width: <?= $this->user->plan_settings->transfers_limit == 0 || $this->user->plan_settings->transfers_limit == -1 ? 0 : ($data->total_transfers / $this->user->plan_settings->transfers_limit * 100) ?>%" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if(settings()->transfers->transfer_requests_is_enabled): ?>
            <div class="col-12 col-lg-6 col-xl-3 p-3 position-relative text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= get_plan_feature_limit_info($data->total_transfer_requests, $this->user->plan_settings->transfer_requests_limit) ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <a href="<?= url('transfer-requests') ?>" class="stretched-link">
                            <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                                <i class="fas fa-fw fa-sm fa-inbox text-primary"></i>
                            </div>
                        </a>
                    </div>

                    <div class="card-body text-truncate">
                        <?= sprintf(l('dashboard.total_transfer_requests'), '<span class="h6">' . nr($data->total_transfer_requests) . '</span>') ?>

                        <div class="progress" style="height: .25rem;">
                            <div class="progress-bar <?= $this->user->plan_settings->transfer_requests_limit == -1 ? 'bg-success' : null ?>" role="progressbar" style="width: <?= $this->user->plan_settings->transfer_requests_limit == 0 || $this->user->plan_settings->transfer_requests_limit == -1 ? 0 : ($data->total_transfers / $this->user->plan_settings->transfer_requests_limit * 100) ?>%" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <div class="col-12 col-lg-6 col-xl-3 p-3 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-copy text-primary"></i>
                    </div>
                </div>

                <div class="card-body text-truncate">
					<?= sprintf(l('dashboard.total_files'), '<span class="h6">' . nr($this->user->total_files) . '</span>') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 col-xl-3 p-3 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-hdd text-primary"></i>
                    </div>
                </div>

                <div class="card-body text-truncate">
					<?= sprintf(l('dashboard.total_files_size'), '<span class="h6">' . get_formatted_bytes($this->user->total_files_size) . ' / ' . ($this->user->plan_settings->storage_size_limit != -1 ? get_formatted_bytes($this->user->plan_settings->storage_size_limit * 1000 * 1000) : '∞') . '</span>') ?>

					<?php if($this->user->plan_settings->storage_size_limit != -1 && $this->user->plan_settings->storage_size_limit != 0): ?>
                        <div class="progress" style="height: .25rem;">
                            <div class="progress-bar" role="progressbar" style="width: <?= $this->user->total_files_size / ($this->user->plan_settings->storage_size_limit * 1000 * 1000) * 100 . '%' ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
					<?php endif ?>
                </div>
            </div>
        </div>
    </div>

    <?php $dashboard_features = ((array) $this->user->preferences->dashboard) + array_fill_keys(require APP_PATH . 'includes/available_dashboard_features.php', true) ?>

    <?php foreach($dashboard_features as $feature => $is_enabled): ?>

    <?php if($is_enabled && $feature == 'transfers'): ?>
        <div class="my-5">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-paper-plane mr-1 text-website"></i> <?= l('dashboard.transfers_header') ?></h2>

                    <div class="flex-fill">
                        <hr class="border-gray-100" />
                    </div>

                    <div class="ml-3">
                        <a href="<?= url('transfers') ?>" class="btn btn-sm btn-primary-100" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-paper-plane fa-sm"></i></a>
                    </div>
                </div>

                <?php if (!empty($data->transfers)): ?>
                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('transfers.table.transfer') ?></th>
                                <th><?= l('transfers.table.files') ?></th>
                                <th><?= l('transfers.table.expiration') ?></th>
                                <th><?= l('transfers.table.pageviews') ?></th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach($data->transfers as $row): ?>

                                <tr>
                                    <td class="text-nowrap">
                                        <div class="d-flex flex-column">
                                            <div><a href="<?= url('transfer/' . $row->transfer_id) ?>"><?= $row->name ?></a></div>
                                            <div class="small">
                                                <?= remove_url_protocol_from_url($row->full_url) ?>
                                                <a href="<?= $row->full_url ?>" class="text-muted" target="_blank" rel="noreferrer">
                                                    <i class="fas fa-fw fa-xs fa-external-link-alt text-muted ml-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <span class="badge badge-info">
                                            <?= nr($row->total_files) ?>
                                            <span class="text-muted">• <?= get_formatted_bytes($row->total_size) ?></span>
                                        </span>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex flex-column small">
                                            <div class="mb-1">
                                                <a href="<?= url('transfer-downloads/' . $row->transfer_id) ?>" class="text-muted">
                                                    <i class="fas fa-fw fa-sm fa-download mr-1"></i>
                                                    <?= (new \Altum\Models\Transfers())->get_downloads_limit_text($row->downloads, $row->downloads_limit) ?>
                                                </a>
                                            </div>
                                            <div class="text-muted">
                                                <i class="fas fa-fw fa-sm fa-hourglass-half mr-1"></i>
                                                <?= (new \Altum\Models\Transfers())->get_expiration_datetime_text($row->expiration_datetime) ?>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <a href="<?= url('transfer-statistics/' . $row->transfer_id) ?>" class="badge badge-light text-decoration-none" data-toggle="tooltip" title="<?= l('transfer.pageviews') ?>">
                                            <i class="fas fa-fw fa-sm fa-chart-bar mr-1"></i> <?= nr($row->pageviews) ?>
                                        </a>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center">
                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                                <i class="fas fa-fw fa-calendar text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                                <i class="fas fa-fw fa-history text-muted"></i>
                                            </span>

                                            <?php if($row->settings->password): ?>
                                                <span class="mr-2" data-toggle="tooltip" title="<?= l('global.password') . ': ' . l('global.yes') ?>">
                                                    <i class="fas fa-fw fa-lock text-muted"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="mr-2" data-toggle="tooltip" title="<?= l('global.password') . ': ' . l('global.no') ?>">
                                                    <i class="fas fa-fw fa-lock-open text-muted"></i>
                                                </span>
                                            <?php endif ?>

                                            <?php if($row->settings->file_encryption_is_enabled): ?>
                                                <span class="mr-2" data-toggle="tooltip" title="<?= l('transfers.file_encryption_is_enabled') . ': ' . l('global.yes') ?>">
                                                    <i class="fas fa-fw fa-fingerprint text-primary"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="mr-2" data-toggle="tooltip" title="<?= l('transfers.file_encryption_is_enabled') . ': ' . l('global.no') ?>">
                                                    <i class="fas fa-fw fa-fingerprint text-muted"></i>
                                                </span>
                                            <?php endif ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <button
                                                    id="url_copy"
                                                    type="button"
                                                    class="btn btn-link text-secondary"
                                                    data-toggle="tooltip"
                                                    title="<?= l('global.clipboard_copy') ?>"
                                                    aria-label="<?= l('global.clipboard_copy') ?>"
                                                    data-copy="<?= l('global.clipboard_copy') ?>"
                                                    data-copied="<?= l('global.clipboard_copied') ?>"
                                                    data-clipboard-text="<?= $row->full_url ?>"
                                            >
                                                <i class="fas fa-fw fa-sm fa-copy"></i>
                                            </button>

                                            <?= include_view(THEME_PATH . 'views/transfers/transfer_dropdown_button.php', ['id' => $row->transfer_id, 'resource_name' => $row->name, 'full_url' => $row->full_url]) ?>
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
                            'name' => 'transfers',
                            'has_secondary_text' => true,
                    ]); ?>

                <?php endif ?>

            </div>
    <?php endif ?>

    <?php if($is_enabled && $feature == 'transfer_requests' && settings()->transfers->transfer_requests_is_enabled): ?>
            <div class="mt-5">
                    <div class="d-flex align-items-center mb-3">
                        <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-inbox mr-1 text-website"></i> <?= l('dashboard.transfer_requests_header') ?></h2>

                        <div class="flex-fill">
                            <hr class="border-gray-100" />
                        </div>

                        <div class="ml-3">
                            <a href="<?= url('transfer-requests') ?>" class="btn btn-sm btn-primary-100" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-inbox fa-sm"></i></a>
                        </div>
                    </div>

                    <?php if (!empty($data->transfer_requests)): ?>
                        <div class="table-responsive table-custom-container">
                            <table class="table table-custom">
                                <thead>
                                <tr>
                                    <th><?= l('transfer_requests.transfer_request') ?></th>
                                    <th><?= l('transfer_requests.table.files') ?></th>
                                    <th><?= l('transfer_requests.table.pageviews') ?></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php foreach($data->transfer_requests as $row): ?>
                                    <tr>
                                        <td class="text-nowrap">
                                            <div class="d-flex flex-column">
                                                <div><a href="<?= url('transfer-request/' . $row->transfer_request_id) ?>" title="<?= $row->name ?>"><?= string_truncate($row->name, 32) ?></a></div>
                                                <div class="small">
                                                    <?= remove_url_protocol_from_url($row->full_url) ?>
                                                    <a href="<?= $row->full_url ?>" class="text-muted" target="_blank" rel="noreferrer">
                                                        <i class="fas fa-fw fa-xs fa-external-link-alt text-muted ml-1"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-nowrap">
                                            <div class="d-flex flex-column">
                                                <div class="badge badge-light mb-1"><?= sprintf(l('transfer_requests.total_submissions_x'), nr($row->total_submissions)) ?></div>

                                                <span class="badge badge-info">
                                                    <?= nr($row->total_files) ?> • <?= get_formatted_bytes($row->total_size) ?>
                                                </span>
                                            </div>
                                        </td>

                                        <td class="text-nowrap">
                                            <a href="<?= url('transfer-request-statistics/' . $row->transfer_request_id) ?>" class="badge badge-light text-decoration-none" data-toggle="tooltip" title="<?= l('transfer.pageviews') ?>">
                                                <i class="fas fa-fw fa-sm fa-chart-bar mr-1"></i> <?= nr($row->pageviews) ?>
                                            </a>
                                        </td>

                                        <td class="text-nowrap">
                                            <div class="d-flex align-items-center">

                                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('transfer_requests.expiration_datetime'), ($row->expiration_datetime ? '<br />' . \Altum\Date::get($row->expiration_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->expiration_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->expiration_datetime) . ')</small>' : '<br />' . l('transfer_requests.expiration_datetime_null')) ?>">
                                                    <i class="fas fa-fw fa-hourglass-half text-muted"></i>
                                                </span>


                                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                                    <i class="fas fa-fw fa-calendar text-muted"></i>
                                                </span>

                                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                                    <i class="fas fa-fw fa-history text-muted"></i>
                                                </span>

                                                <?php if($row->settings->password): ?>
                                                    <span class="mr-2" data-toggle="tooltip" title="<?= l('global.password') . ': ' . l('global.yes') ?>">
                                                        <i class="fas fa-fw fa-lock text-muted"></i>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="mr-2" data-toggle="tooltip" title="<?= l('global.password') . ': ' . l('global.no') ?>">
                                                        <i class="fas fa-fw fa-lock-open text-muted"></i>
                                                    </span>
                                                <?php endif ?>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="d-flex justify-content-end">
                                                <button
                                                        id="url_copy"
                                                        type="button"
                                                        class="btn btn-link text-secondary"
                                                        data-toggle="tooltip"
                                                        title="<?= l('global.clipboard_copy') ?>"
                                                        aria-label="<?= l('global.clipboard_copy') ?>"
                                                        data-copy="<?= l('global.clipboard_copy') ?>"
                                                        data-copied="<?= l('global.clipboard_copied') ?>"
                                                        data-clipboard-text="<?= $row->full_url ?>"
                                                >
                                                    <i class="fas fa-fw fa-sm fa-copy"></i>
                                                </button>

                                                <?= include_view(THEME_PATH . 'views/transfer-requests/transfer_request_dropdown_button.php', ['id' => $row->transfer_request_id, 'resource_name' => $row->name, 'full_url' => $row->full_url]) ?>
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
                                'name' => 'transfers',
                                'has_secondary_text' => true,
                        ]); ?>

                    <?php endif ?>

                </div>
    <?php endif ?>

    <?php endforeach ?>


</div>

<?php include_once THEME_PATH . 'views/partials/uploader_js.php' ?>

<?php ob_start() ?>
<script>
    'use strict';

    let active_notification_handlers_per_resource_limit = <?= (int) $this->user->plan_settings->active_notification_handlers_per_resource_limit ?>;

    if(active_notification_handlers_per_resource_limit != -1) {
        let process_notification_handlers = () => {
            let download_notifications = document.querySelectorAll('[name="download_notification_handlers_ids[]"]:checked').length;
            let pageview_notifications = document.querySelectorAll('[name="pageview_notification_handlers_ids[]"]:checked').length;

            if(download_notifications >= active_notification_handlers_per_resource_limit) {
                document.querySelectorAll('[name="download_notification_handlers_ids[]"]:not(:checked)').forEach(element => element.setAttribute('disabled', 'disabled'));
            } else {
                document.querySelectorAll('[name="download_notification_handlers_ids[]"]:not(:checked)').forEach(element => element.removeAttribute('disabled'));
            }

            if(pageview_notifications >= active_notification_handlers_per_resource_limit) {
                document.querySelectorAll('[name="pageview_notification_handlers_ids[]"]:not(:checked)').forEach(element => element.setAttribute('disabled', 'disabled'));
            } else {
                document.querySelectorAll('[name="pageview_notification_handlers_ids[]"]:not(:checked)').forEach(element => element.removeAttribute('disabled'));
            }
        }

        document.querySelectorAll('[name="download_notification_handlers_ids[]"]').forEach(element => element.addEventListener('change', process_notification_handlers));
        document.querySelectorAll('[name="pageview_notification_handlers_ids[]"]').forEach(element => element.addEventListener('change', process_notification_handlers));

        process_notification_handlers();
    }
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>
