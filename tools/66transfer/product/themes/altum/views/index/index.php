<?php defined('ALTUMCODE') || die() ?>

<div class="container mt-5">
	<?= \Altum\Alerts::output_alerts() ?>

    <div id="upload_main_dropzone" class="card py-6 upload-drag-over upload-drag-over-inactive position-relative">
        <div class="upload-hint-wrapper">
			<?php if(settings()->transfers->transfer_requests_is_enabled): ?>
                <a href="<?= url('transfer-request-create') ?>" class="upload-hint-badge text-decoration-none font-size-smaller <?= $this->user->plan_settings->transfer_requests_limit != 0 ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->transfer_requests_limit != 0 ? null : get_plan_feature_disabled_info() ?>>
                    <i class="fas fa-fw fa-sm fa-inbox mr-2"></i> <?= l('transfer_requests.transfer_request') ?>
                </a>
			<?php endif ?>

			<?php if($this->user->plan_settings->transfers_limit != 0 && ((!is_logged_in() && settings()->plan_guest->status != 0) || is_logged_in())): ?>
                <div class="upload-hint-badge font-size-smaller d-none d-lg-block ml-3"><?= l('transfer.drop_files_help') ?></div>
			<?php endif ?>
        </div>

        <div class="card-body">

            <div id="upload_header" class="row justify-content-center">
                <div class="col-11 col-md-10 col-lg-8 col-xl-7">
                    <h1 class="index-header text-center mb-2"><?= l('index.header') ?></h1>
                </div>

                <div class="col-10 col-sm-8 col-lg-7 col-xl-6">
                    <p class="index-subheader text-center mb-5"><?= l('index.subheader') ?></p>
                </div>
            </div>

            <form id="upload_form" action="<?= url('transfer/create_api') ?>" method="post" role="form" enctype="multipart/form-data" data-endpoint="transfer/create_api">
                <input type="hidden" name="global_token" value="<?= \Altum\Csrf::get('global_token') ?>" />

                <div class="notification-container"></div>

                <div class="row">
                    <div class="col-12 col-lg-6 offset-lg-3">
						<?php if($this->user->plan_settings->transfers_limit != 0 && ((!is_logged_in() && settings()->plan_guest->status != 0) || is_logged_in())): ?>
                            <button id="upload_select_files" type="button" class="btn btn-block btn-outline-primary select-files-button rounded-2x mb-3 mb-lg-0 mr-lg-3">
                                <i class="fas fa-fw fa-sm fa-plus-circle mr-1"></i> <?= l('transfer.select_files') ?>
                            </button>

                            <div class="mt-3 text-center">
                                <button id="upload_select_folders" type="button" class="btn btn-sm btn-link text-decoration-none text-muted">
                                    <i class="fas fa-fw fa-sm fa-folder-plus mr-1"></i> <?= l('transfer.select_folder') ?>
                                </button>
                            </div>
						<?php elseif(!is_logged_in() && settings()->users->register_is_enabled): ?>
                            <a href="<?= url('register') ?>" target="_blank" class="btn btn-block btn-outline-primary index-button mb-3 mb-lg-0 mr-lg-3">
                                <i class="fas fa-fw fa-sm fa-user-plus mr-1"></i> <?= l('index.register') ?>
                            </a>
						<?php endif ?>
                    </div>
                </div>

                <div class="mt-5">
                    <div id="upload_previews_wrapper" class="d-none">
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

								<?php if(!is_logged_in() && settings()->captcha->transfer_upload_is_enabled): ?>
                                    <div class="form-group">
										<?php $data->captcha->display() ?>
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
                                                    data-max-date="<?= $this->user->plan_settings->transfers_retention == -1 ? null : $potential_max_expiration_time ?>"
                                            />
											<?php if($this->user->plan_settings->transfers_retention == -1): ?>
                                                <small class="form-text text-muted"><?= l('transfer.expiration_datetime_help') ?></small>
											<?php endif ?>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="pills-protection" role="tabpanel" aria-labelledby="protection-tab">
                                        <div <?= $this->user->plan_settings->password_protection_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                                            <div class="form-group <?= $this->user->plan_settings->password_protection_is_enabled ? null : 'container-disabled' ?>" data-password-toggle-view data-password-toggle-view-show="<?= l('global.show') ?>" data-password-toggle-view-hide="<?= l('global.hide') ?>">
                                                <label for="password"><i class="fas fa-fw fa-lock fa-sm text-muted mr-1"></i> <?= l('global.password') ?></label>
                                                <input type="password" id="password" name="password" class="form-control form-control-sm" autocomplete="new-password" maxlength="64" />
                                                <small class="form-text text-muted"><?= l('transfer.password_help') ?></small>
                                            </div>
                                        </div>

                                        <div <?= $this->user->plan_settings->file_encryption_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                                            <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->file_encryption_is_enabled ? null : 'container-disabled' ?>">
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
                                                            <option value="<?= $project_id ?>" <?= $project_id == $this->user->preferences->transfers_default_project_id ? 'checked="checked"' : null ?>><?= $project->name ?></option>
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
</div>

<?php include_once THEME_PATH . 'views/partials/uploader_js.php' ?>

<div class="my-5">&nbsp;</div>

<div class="container">
    <div class="card">
        <div class="card-body py-6 text-center">
            <span class="h3"><?= sprintf(l('index.stats'), $data->total_files, nr($data->total_files, 0, true, true), $data->total_transfers, nr($data->total_transfers, 0, true, true)) ?></span>
        </div>
    </div>
</div>

<div class="my-5">&nbsp;</div>

<div class="container">
    <div class="row">
        <div class="col-12 col-lg p-4 position-relative text-truncate" data-aos="fade-up" data-aos-delay="100">
            <div class="card d-flex flex-row align-items-center h-100 overflow-hidden">
                <div class="p-3 d-flex flex-column justify-content-center">
                    <i class="fas fa-fw fa-2x fa-paper-plane text-primary"></i>
                </div>

                <div class="pl-2 py-3 pr-3 text-wrap">
                    <span class="h6"><?= l('index.widgets.type.header') ?></span>
                    <div class="small text-muted"><?= l('index.widgets.type.subheader') ?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg p-4 position-relative text-truncate" data-aos="fade-up" data-aos-delay="200">
            <div class="card d-flex flex-row align-items-center h-100 overflow-hidden">
                <div class="p-3 d-flex flex-column justify-content-center">
                    <i class="fas fa-fw fa-2x fa-chart-bar text-primary"></i>
                </div>

                <div class="pl-2 py-3 pr-3 text-wrap">
                    <span class="h6"><?= l('index.widgets.statistics.header') ?></span>
                    <div class="small text-muted"><?= l('index.widgets.statistics.subheader') ?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg p-4 position-relative text-truncate" data-aos="fade-up" data-aos-delay="300">
            <div class="card d-flex flex-row align-items-center h-100 overflow-hidden">
                <div class="p-3 d-flex flex-column justify-content-center">
                    <i class="fas fa-fw fa-2x fa-fingerprint text-primary"></i>
                </div>

                <div class="pl-2 py-3 pr-3 text-wrap">
                    <span class="h6"><?= l('index.widgets.encryption.header') ?></span>
                    <div class="small text-muted"><?= l('index.widgets.encryption.subheader') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
		<?php if(settings()->transfers->transfer_requests_is_enabled): ?>
            <div class="col-12 col-lg p-4 position-relative text-truncate" data-aos="fade-up" data-aos-delay="400">
                <div class="card d-flex flex-row align-items-center h-100 overflow-hidden">
                    <div class="p-3 d-flex flex-column justify-content-center">
                        <i class="fas fa-fw fa-2x fa-inbox text-primary"></i>
                    </div>

                    <div class="pl-2 py-3 pr-3 text-wrap">
                        <span class="h6"><?= l('index.widgets.requests.header') ?></span>
                        <div class="small text-muted"><?= l('index.widgets.requests.subheader') ?></div>
                    </div>
                </div>
            </div>
		<?php endif ?>

        <div class="col-12 col-lg p-4 position-relative text-truncate" data-aos="fade-up" data-aos-delay="400">
            <div class="card d-flex flex-row align-items-center h-100 overflow-hidden">
                <div class="p-3 d-flex flex-column justify-content-center">
                    <i class="fas fa-fw fa-2x fa-code text-primary"></i>
                </div>

                <div class="pl-2 py-3 pr-3 text-wrap">
                    <span class="h6"><?= l('index.widgets.custom_code.header') ?></span>
                    <div class="small text-muted"><?= l('index.widgets.custom_code.subheader') ?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg p-4 position-relative text-truncate" data-aos="fade-up" data-aos-delay="500">
            <div class="card d-flex flex-row align-items-center h-100 overflow-hidden">
                <div class="p-3 d-flex flex-column justify-content-center">
                    <i class="fas fa-fw fa-2x fa-eye text-primary"></i>
                </div>

                <div class="pl-2 py-3 pr-3 text-wrap">
                    <span class="h6"><?= l('index.widgets.file_previews.header') ?></span>
                    <div class="small text-muted"><?= l('index.widgets.file_previews.subheader') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="my-5">&nbsp;</div>

<div class="container">
    <div class="row">
        <div class="col-12 col-md-6 col-lg-4 p-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card d-flex flex-column justify-content-between h-100 up-animation">
                <div class="card-body">
                    <img src="<?= get_custom_image_if_any('index/password_protection.svg') ?>" class="index-card-image mb-4" loading="lazy" alt="<?= l('index.password_image_alt') ?>" />

                    <div class="mb-2">
                        <span class="h5"><?= l('index.cards.password_protection.header') ?></span>
                    </div>
                    <span class="text-muted"><?= l('index.cards.password_protection.subheader') ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 p-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card d-flex flex-column justify-content-between h-100 up-animation">
                <div class="card-body">
                    <img src="<?= get_custom_image_if_any('index/expiration.svg') ?>" class="index-card-image mb-4" loading="lazy" alt="<?= l('index.expiration_image_alt') ?>" />

                    <div class="mb-2">
                        <span class="h5"><?= l('index.cards.expiration.header') ?></span>
                    </div>
                    <span class="text-muted"><?= l('index.cards.expiration.subheader') ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 p-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card d-flex flex-column justify-content-between h-100 up-animation">
                <div class="card-body">
                    <img src="<?= get_custom_image_if_any('index/notification_handlers.svg') ?>" class="index-card-image mb-4" loading="lazy" alt="<?= l('index.branding_image_alt') ?>" />

                    <div class="mb-2">
                        <span class="h5"><?= l('index.cards.branding.header') ?></span>
                    </div>
                    <span class="text-muted"><?= l('index.cards.branding.subheader') ?></span>
                </div>
            </div>
        </div>

		<?php if(settings()->transfers->pixels_is_enabled): ?>
            <div class="col-12 col-md-6 col-lg-4 p-4" data-aos="fade-up" data-aos-delay="400">
                <div class="card d-flex flex-column justify-content-between h-100 up-animation">
                    <div class="card-body">
                        <img src="<?= get_custom_image_if_any('index/pixels.svg') ?>" class="index-card-image mb-4" loading="lazy" alt="<?= l('index.pixels_image_alt') ?>" />

                        <div class="mb-2">
                            <span class="h5"><?= l('index.cards.pixels.header') ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted"><?= l('index.cards.pixels.subheader') ?></span>
                        </div>
                        <div>
							<?php foreach(require APP_PATH . 'includes/t/pixels.php' as $item): ?>
                                <span data-toggle="tooltip" title="<?= $item['name'] ?>"><i class="<?= $item['icon'] ?> fa-fw mx-1" style="color: <?= $item['color'] ?>"></i></span>
							<?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
		<?php endif ?>

		<?php if(settings()->transfers->domains_is_enabled): ?>
            <div class="col-12 col-md-6 col-lg-4 p-4" data-aos="fade-up" data-aos-delay="500">
                <div class="card d-flex flex-column justify-content-between h-100 up-animation">
                    <div class="card-body">
                        <img src="<?= get_custom_image_if_any('index/domains.svg') ?>" class="index-card-image mb-4" loading="lazy" alt="<?= l('index.domains_image_alt') ?>" />

                        <div class="mb-2">
                            <span class="h5"><?= l('index.cards.domains.header') ?></span>
                        </div>
                        <span class="text-muted"><?= l('index.cards.domains.subheader') ?></span>
                    </div>
                </div>
            </div>
		<?php endif ?>

		<?php if(settings()->transfers->projects_is_enabled): ?>
            <div class="col-12 col-md-6 col-lg-4 p-4" data-aos="fade-up" data-aos-delay="600">
                <div class="card d-flex flex-column justify-content-between h-100 up-animation">
                    <div class="card-body">
                        <img src="<?= get_custom_image_if_any('index/projects.svg') ?>" class="index-card-image mb-4" loading="lazy" alt="<?= l('index.projects_image_alt') ?>" />

                        <div class="mb-2">
                            <span class="h5"><?= l('index.cards.projects.header') ?></span>
                        </div>
                        <span class="text-muted"><?= l('index.cards.projects.subheader') ?></span>
                    </div>
                </div>
            </div>
		<?php endif ?>
    </div>
</div>

<?php if(settings()->notification_handlers->is_enabled): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="text-center mb-4">
            <h2><?= l('index.notifications_handlers.header') ?> <i class="fas fa-fw fa-xs fa-bell ml-1"></i> </h2>
            <p class="text-muted"><?= l('index.notifications_handlers.subheader') ?></p>
        </div>

        <div class="row mx-n4">
			<?php $notification_handlers = require APP_PATH . 'includes/notification_handlers.php' ?>
			<?php $i = 0; ?>
			<?php foreach($notification_handlers as $key => $notification_handler): ?>
                <div class="col-6 col-lg-4 p-4" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                    <div class="bg-white index-highly-rounded w-100 p-4 icon-zoom-animation text-truncate text-center">
                        <div><i class="<?= $notification_handler['icon'] ?> fa-fw fa-xl mx-1" style="color: <?= $notification_handler['color'] ?>"></i></div>

                        <div class="mt-3 mb-0 h6 text-truncate"><?= l('notification_handlers.type_' . $key) ?></div>
                    </div>
                </div>
				<?php $i++ ?>
			<?php endforeach ?>
        </div>
    </div>
<?php endif ?>

<?php if(settings()->main->api_is_enabled): ?>
    <div class="py-6"></div>

    <div class="container">
        <div class="row align-items-center justify-content-between">
            <div class="col-12 col-lg-5 mb-5 mb-lg-0 d-flex flex-column justify-content-center" data-aos="fade-up"">
            <div class="text-uppercase font-weight-bold text-primary mb-3"><?= l('index.api.name') ?></div>

            <div>
                <h2 class="mb-2"><?= l('index.api.header') ?></h2>
                <p class="text-muted mb-4"><?= l('index.api.subheader') ?></p>

                <div class="position-relative">
                    <div class="index-fade"></div>
                    <div class="row">
                        <div class="col">
                            <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('api_documentation.files') ?></div>
                            <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('api_documentation.statistics') ?></div>
                            <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('api_documentation.downloads') ?></div>
                        </div>

                        <div class="col">
							<?php if(settings()->transfers->pixels_is_enabled): ?>
                                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('pixels.title') ?></div>
							<?php endif ?>

							<?php if(settings()->transfers->domains_is_enabled): ?>
                                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('domains.title') ?></div>
							<?php endif ?>

                            <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('projects.title') ?></div>
                        </div>
                    </div>
                </div>

                <a href="<?= url('api-documentation') ?>" class="btn btn-block btn-outline-primary mt-5">
					<?= l('api_documentation.menu') ?> <i class="fas fa-fw fa-xs fa-code ml-1"></i>
                </a>
            </div>
        </div>

        <div class="col-12 col-lg-6" data-aos="fade-up" data-aos-delay="300">
            <div class="card rounded-2x bg-dark text-white">
                <div class="card-body p-4 text-monospace reveal-effect text-break font-size-small" style="line-height: 1.75">
                    curl --request POST \<br />
                    --url '<?= SITE_URL ?>transfer/create_api' \<br />
                    --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \<br />
                    --header 'Content-Type: multipart/form-data' \<br />
                    --form 'uploaded_files[]=<span class="text-primary">de09be56-639f-4213-863b-5ea1aa065970</span>' \<br />
                    --form 'name=<span class="text-primary">Example name</span>' \<br />
                    --form 'description=<span class="text-primary">Example description</span>' \<br />
                </div>
            </div>
        </div>
    </div>
    </div>

    <style>
        /* hide until words are wrapped to avoid flash */
        .reveal-effect { visibility: hidden; }

        /* base state for each word */
        .reveal-effect-prepared .reveal-effect-word {
            opacity: 0;
            filter: blur(6px);
            transform: translate3d(0, 8px, 0);
            display: inline-block;
            transition: opacity .5s ease, filter .5s ease, transform .5s ease;
        }

        /* animate in when container gets .reveal-effect-in */
        .reveal-effect-prepared.reveal-effect-in .reveal-effect-word {
            opacity: 1;
            filter: blur(0);
            transform: none;
        }
    </style>

    <script defer>
        /* wrap words in a text node while preserving existing HTML */
        const wrap_words_in_text_node = (text_node) => {
            /* split into words + spaces, keep spacing intact */
            const tokens = text_node.textContent.split(/(\s+)/);
            const fragment = document.createDocumentFragment();

            tokens.forEach((token) => {
                if (token.trim().length === 0) {
                    fragment.appendChild(document.createTextNode(token));
                } else {
                    const span_node = document.createElement('span');
                    span_node.className = 'reveal-effect-word';
                    span_node.textContent = token;
                    fragment.appendChild(span_node);
                }
            });

            text_node.parentNode.replaceChild(fragment, text_node);
        };

        /* prepare a container: wrap only pure text nodes, not tags */
        const prepare_reveal_container = (container_node) => {
            /* collect first to avoid live-walking issues while replacing */
            const walker = document.createTreeWalker(
                container_node,
                NodeFilter.SHOW_TEXT,
                { acceptNode: (node) => node.textContent.trim().length ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT }
            );
            const text_nodes = [];
            while (walker.nextNode()) { text_nodes.push(walker.currentNode); }
            text_nodes.forEach(wrap_words_in_text_node);

            /* add stagger */
            const word_nodes = container_node.querySelectorAll('.reveal-effect-word');
            word_nodes.forEach((word_node, index) => {
                word_node.style.transitionDelay = (index * 40) + 'ms';
            });

            /* mark as prepared and reveal visibility */
            container_node.classList.add('reveal-effect-prepared');
            container_node.style.visibility = 'visible';
        };

        /* set up scroll trigger */
        document.addEventListener('DOMContentLoaded', () => {
            const container_node = document.querySelector('.reveal-effect');
            if (!container_node) { return; }

            /* prepare once (preserves HTML) */
            prepare_reveal_container(container_node);

            /* trigger when in view */
            const on_intersect = (entries, observer) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        /* start the animation */
                        setTimeout(() => {
                            container_node.classList.add('reveal-effect-in');
                            observer.unobserve(container_node);
                        }, 200);
                    }
                });
            };

            const intersection_observer = new IntersectionObserver(on_intersect, {
                root: null,
                rootMargin: '0px 0px -10% 0px',
                threshold: 0.1
            });

            intersection_observer.observe(container_node);
        });
    </script>
<?php endif ?>

<?php if(settings()->main->display_index_testimonials): ?>
    <div class="my-5">&nbsp;</div>

    <div class="p-3 p-md-4">
        <div class="py-7 bg-primary-100 rounded-2x">
            <div class="container">
                <div class="text-center">
                    <h2><?= l('index.testimonials.header') ?> <i class="fas fa-fw fa-xs fa-check-circle text-primary"></i></h2>
                </div>

				<?php
				$language_array = \Altum\Language::get(\Altum\Language::$name);
				if(\Altum\Language::$main_name != \Altum\Language::$name) {
					$language_array = array_merge(\Altum\Language::get(\Altum\Language::$main_name), $language_array);
				}

				$testimonials_language_keys = [];
				foreach ($language_array as $key => $value) {
					if(preg_match('/index\.testimonials\.(\w+)\./', $key, $matches)) {
						$testimonials_language_keys[] = $matches[1];
					}
				}

				$testimonials_language_keys = array_unique($testimonials_language_keys);
				?>

                <div class="row mt-8 mx-n3">
					<?php foreach($testimonials_language_keys as $key => $value): ?>
                        <div class="col-12 col-lg-4 mb-7 mb-lg-0 px-4" data-aos="fade-up" data-aos-delay="<?= $key * 100 ?>">
                            <div class="card border-0 zoom-animation-subtle">
                                <div class="card-body">
                                    <img src="<?= get_custom_image_if_any('index/testimonial-' . $value . '.webp') ?>" class="img-fluid index-testimonial-avatar" alt="<?= l('index.testimonials.' . $value . '.name') . ', ' . l('index.testimonials.' . $value . '.attribute') ?>" loading="lazy" />

                                    <p class="mt-5">
                                        <span class="text-gray-800 font-weight-bold text-muted h5">“</span>
                                        <span class="font-size-little-small"><?= l('index.testimonials.' . $value . '.text') ?></span>
                                        <span class="text-gray-800 font-weight-bold text-muted h5">”</span>
                                    </p>

                                    <div class="blockquote-footer mt-4">
                                        <span class="font-weight-bold"><?= l('index.testimonials.' . $value . '.name') ?></span><br /> <span class="text-muted index-testimonial-comment"><?= l('index.testimonials.' . $value . '.attribute') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
					<?php endforeach ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>


<?php if(settings()->main->display_index_plans): ?>
    <div class="my-5">&nbsp;</div>

    <div id="plans" class="container">
        <div class="text-center mb-5">
            <h2><?= l('index.pricing.header') ?></h2>
            <p class="text-muted"><?= l('index.pricing.subheader') ?></p>
        </div>

		<?= $this->views['plans'] ?>
    </div>
<?php endif ?>

<?php if(settings()->main->display_index_faq): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="text-center mb-5">
            <h2><?= l('index.faq.header') ?></h2>
        </div>

		<?php
		$language_array = \Altum\Language::get(\Altum\Language::$name);
		if(\Altum\Language::$main_name != \Altum\Language::$name) {
			$language_array = array_merge(\Altum\Language::get(\Altum\Language::$main_name), $language_array);
		}

		$faq_language_keys = [];
		foreach ($language_array as $key => $value) {
			if(preg_match('/index\.faq\.(\w+)\./', $key, $matches)) {
				$faq_language_keys[] = $matches[1];
			}
		}

		$faq_language_keys = array_unique($faq_language_keys);
		?>

        <div class="accordion index-faq" id="faq_accordion">
			<?php foreach($faq_language_keys as $key): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="" id="<?= 'faq_accordion_' . $key ?>">
                            <h3 class="mb-0">
                                <button class="btn btn-lg font-weight-500 btn-block d-flex justify-content-between text-gray-800 px-0 icon-zoom-animation no-focus text-left" type="button" data-toggle="collapse" data-target="<?= '#faq_accordion_answer_' . $key ?>" aria-expanded="true" aria-controls="<?= 'faq_accordion_answer_' . $key ?>">
                                    <span class="text-left"><?= l('index.faq.' . $key . '.question') ?></span>

                                    <span data-icon>
                                        <i class="fas fa-fw fa-circle-chevron-down"></i>
                                    </span>
                                </button>
                            </h3>
                        </div>

                        <div id="<?= 'faq_accordion_answer_' . $key ?>" class="collapse text-muted mt-2" aria-labelledby="<?= 'faq_accordion_' . $key ?>" data-parent="#faq_accordion">
							<?= l('index.faq.' . $key . '.answer') ?>
                        </div>
                    </div>
                </div>
			<?php endforeach ?>
        </div>
    </div>

	<?php ob_start() ?>
    <script>
        'use strict';

        $('#faq_accordion').on('show.bs.collapse', event => {
            let svg = event.target.parentElement.querySelector('[data-icon] svg')
            svg.style.transform = 'rotate(180deg)';
            svg.style.color = 'var(--primary)';
        })

        $('#faq_accordion').on('hide.bs.collapse', event => {
            let svg = event.target.parentElement.querySelector('[data-icon] svg')
            svg.style.color = 'var(--primary-800)';
            svg.style.removeProperty('transform');
        })
    </script>
	<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
<?php endif ?>

<?php if(settings()->users->register_is_enabled): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="card border-0 index-cta py-4" data-aos="fade-up">
            <div class="card-body">
                <div class="card-body row align-items-center justify-content-center">
                    <div class="col-12 col-lg-5">
                        <div class="text-center text-lg-left mb-4 mb-lg-0">
                            <h2 class="h1"><?= l('index.cta.header') ?></h2>
                            <p class="h6"><?= l('index.cta.subheader') ?></p>
                        </div>
                    </div>

                    <div class="col-12 col-lg-5 mt-4 mt-lg-0">
                        <div class="text-center text-lg-right">
							<?php if(is_logged_in()): ?>
                                <a href="<?= url('dashboard') ?>" class="btn btn-primary index-button">
									<?= l('dashboard.menu') ?> <i class="fas fa-fw fa-arrow-right"></i>
                                </a>
							<?php else: ?>
                                <a href="<?= url('register') ?>" class="btn btn-primary index-button">
									<?= l('index.cta.register') ?> <i class="fas fa-fw fa-arrow-right"></i>
                                </a>
							<?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if (!empty($data->blog_posts)): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="text-center mb-5">
            <h2><?= sprintf(l('index.blog.header'), '<span class="text-primary">', '</span>') ?></h2>
        </div>

        <div class="row mx-n2 mx-lg-n3">
			<?php foreach($data->blog_posts as $blog_post): ?>
                <div class="col-12 col-lg-4 px-2 py-4 px-lg-3">
                    <div class="card h-100 zoom-animation-subtle position-relative">
                        <div class="card-body">
							<?php if($blog_post->image): ?>
                                <a href="<?= SITE_URL . ($blog_post->language ? \Altum\Language::$active_languages[$blog_post->language] . '/' : null) . 'blog/' . $blog_post->url ?>" aria-label="<?= $blog_post->title ?>">
                                    <img src="<?= \Altum\Uploads::get_full_url('blog') . $blog_post->image ?>" class="blog-post-image-small img-fluid w-100 rounded mb-4" alt="<?= $blog_post->image_description ?>" loading="lazy" />
                                </a>
							<?php endif ?>

                            <a href="<?= SITE_URL . ($blog_post->language ? \Altum\Language::$active_languages[$blog_post->language] . '/' : null) . 'blog/' . $blog_post->url ?>" class="stretched-link text-decoration-none">
                                <h3 class="h5 card-title d-inline"><?= $blog_post->title ?></h3>
                            </a>

                            <p class="text-muted mt-2 mb-0 font-size-small"><?= $blog_post->description ?></p>
                        </div>
                    </div>
                </div>
			<?php endforeach ?>
        </div>
    </div>
<?php endif ?>


<?php ob_start() ?>
<link rel="stylesheet" href="<?= ASSETS_FULL_URL . 'css/libraries/aos.min.css?v=' . PRODUCT_CODE ?>">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/aos.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    'use strict';

    AOS.init({
        duration: 650,
        easing: 'ease-out-cubic',
        once: true,
    });
</script>

<script>
    let count_up_animation = (element, final_append = '', max_duration = 3000, start_on_view = false) => {
        let start_time = null;
        let has_started = false;
        let target = parseInt(element.getAttribute('data-count-up-number'));

        const ease_out = progress => 1 - Math.pow(1 - progress, 8);

        const step = timestamp => {
            if (!start_time) start_time = timestamp;

            const elapsed = timestamp - start_time;
            const progress = Math.min(elapsed / max_duration, 1);
            const eased = ease_out(progress);

            const value = Math.round(eased * target);
            element.textContent = nr(value, 0, false, true);

            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                element.textContent = nr(target, 0, false, true) + final_append;
            }
        };

        const start_animation = () => {
            if (has_started) return;
            has_started = true;
            requestAnimationFrame(step);
        };

        if (!start_on_view) {
            start_animation();
            return;
        }

        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;

                observer.unobserve(element);
                start_animation();
            });
        }, {
            threshold: 0.3
        });

        observer.observe(element);
    };

    document.querySelectorAll('[data-count-up-number]').forEach(element => {
        let duration = element.getAttribute('data-count-up-duration') || 3000;
        let final_append = element.getAttribute('data-count-up-append') || '';
        count_up_animation(element, final_append, duration, true);
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

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

<?php ob_start() ?>
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": <?= json_encode(settings()->main->title) ?>,
    "url": <?= json_encode(url()) ?>,
	<?php if(settings()->main->{'logo_' . \Altum\ThemeStyle::get()}): ?>
        "logo": {
            "@type": "ImageObject",
            "url": <?= json_encode(settings()->main->{'logo_' . \Altum\ThemeStyle::get() . '_full_url'}) ?>
        },
    <?php endif ?>
    "slogan": <?= json_encode(l('index.header')) ?>,
    "contactPoint": {
        "@type": "ContactPoint",
        "url": <?= json_encode(url('contact')) ?>,
        "contactType": "customer support"
    }
}
</script>

<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": <?= json_encode(l('index.title')) ?>,
                    "item": <?= json_encode(url()) ?>
    }
]
}
</script>

<?php if(settings()->main->display_index_faq): ?>
	<?php
	$faqs = [];
	foreach($faq_language_keys as $key) {
		$faqs[] = [
			'@type' => 'Question',
			'name' => l('index.faq.' . $key . '.question'),
			'acceptedAnswer' => [
				'@type' => 'Answer',
				'text' => l('index.faq.' . $key . '.answer'),
			]
		];
	}
	?>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": <?= json_encode($faqs) ?>
        }
    </script>
<?php endif ?>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/index-custom.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>
