<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?= $this->views['account_header_menu'] ?>

    <div class="d-flex align-items-center mb-3">
        <h1 class="h4 m-0"><?= l('account_preferences.header') ?></h1>

        <div class="ml-2">
            <span data-toggle="tooltip" title="<?= l('account_preferences.subheader') ?>">
                <i class="fas fa-fw fa-info-circle text-muted"></i>
            </span>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <form id="account_preferences" action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <?php if(settings()->main->white_labeling_is_enabled): ?>
                    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#white_labeling_container" aria-expanded="false" aria-controls="white_labeling_container">
                        <i class="fas fa-fw fa-cube fa-sm mr-1"></i> <?= l('account_preferences.white_labeling') ?>
                    </button>

                    <div class="collapse" data-parent="#account_preferences" id="white_labeling_container">
                        <div <?= $this->user->plan_settings->white_labeling_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                            <div class="<?= $this->user->plan_settings->white_labeling_is_enabled ? null : 'container-disabled' ?>">
                                <div class="form-group">
                                    <label for="white_label_title"><i class="fas fa-fw fa-sm fa-heading text-muted mr-1"></i> <?= l('account_preferences.white_label_title') ?></label>
                                    <input type="text" id="white_label_title" name="white_label_title" class="form-control <?= \Altum\Alerts::has_field_errors('white_label_title') ? 'is-invalid' : null ?>" value="<?= $this->user->preferences->white_label_title ?>" maxlength="32" />
                                    <?= \Altum\Alerts::output_field_error('white_label_title') ?>
                                </div>

                                <div class="form-group">
                                    <label for="white_label_footer_description"><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('account_preferences.white_label_footer_description') ?></label>
                                    <input type="text" id="white_label_footer_description" name="white_label_footer_description" class="form-control <?= \Altum\Alerts::has_field_errors('white_label_footer_description') ? 'is-invalid' : null ?>" value="<?= $this->user->preferences->white_label_footer_description ?>" maxlength="256" />
                                    <?= \Altum\Alerts::output_field_error('white_label_footer_description') ?>
                                </div>

                                <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= get_max_upload() ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), get_max_upload()) ?>">
                                    <label for="white_label_logo_light"><i class="fas fa-fw fa-sm fa-sun text-muted mr-1"></i> <?= l('account_preferences.white_label_logo_light') ?></label>
                                    <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'users', 'file_key' => 'white_label_logo_light', 'already_existing_image' => $this->user->preferences->white_label_logo_light]) ?>
                                    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('users')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), get_max_upload()) ?></small>
                                </div>

                                <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= get_max_upload() ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), get_max_upload()) ?>">
                                    <label for="white_label_logo_dark"><i class="fas fa-fw fa-sm fa-moon text-muted mr-1"></i> <?= l('account_preferences.white_label_logo_dark') ?></label>
                                    <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'users', 'file_key' => 'white_label_logo_dark', 'already_existing_image' => $this->user->preferences->white_label_logo_dark]) ?>
                                    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('users')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), get_max_upload()) ?></small>
                                </div>

                                <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= get_max_upload() ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), get_max_upload()) ?>">
                                    <label for="white_label_favicon"><i class="fas fa-fw fa-sm fa-icons text-muted mr-1"></i> <?= l('account_preferences.white_label_favicon') ?></label>
                                    <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'users', 'file_key' => 'white_label_favicon', 'already_existing_image' => $this->user->preferences->white_label_favicon]) ?>
                                    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('users')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), get_max_upload()) ?></small>
                                </div>

                                <div class="form-group custom-control custom-switch">
                                    <input id="white_label_remove_socials" name="white_label_remove_socials" type="checkbox" class="custom-control-input" <?= $this->user->preferences->white_label_remove_socials ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label" for="white_label_remove_socials"><?= l('account_preferences.white_label_remove_socials') ?></label>
                                    <?= \Altum\Alerts::output_field_error('white_label_remove_socials') ?>
                                </div>

                                <div class="form-group custom-control custom-switch">
                                    <input id="white_label_remove_footer_links" name="white_label_remove_footer_links" type="checkbox" class="custom-control-input" <?= $this->user->preferences->white_label_remove_footer_links ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label" for="white_label_remove_footer_links"><?= l('account_preferences.white_label_remove_footer_links') ?></label>
                                    <?= \Altum\Alerts::output_field_error('white_label_remove_footer_links') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif ?>

                <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#default_settings_container" aria-expanded="false" aria-controls="default_settings_container">
                    <i class="fas fa-fw fa-wrench fa-sm mr-1"></i> <?= l('account_preferences.default_settings') ?>
                </button>

                <div class="collapse" data-parent="#account_preferences" id="default_settings_container">
                    <div class="form-group">
                        <label for="default_results_per_page"><i class="fas fa-fw fa-sm fa-list-ol text-muted mr-1"></i> <?= l('account_preferences.default_results_per_page') ?></label>
                        <select id="default_results_per_page" name="default_results_per_page" class="custom-select <?= \Altum\Alerts::has_field_errors('default_results_per_page') ? 'is-invalid' : null ?>">
                            <?php foreach([10, 25, 50, 100, 250, 500, 1000] as $key): ?>
                                <option value="<?= $key ?>" <?= ($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page) == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                            <?php endforeach ?>
                        </select>
                        <?= \Altum\Alerts::output_field_error('default_results_per_page') ?>
                    </div>

                    <div class="form-group">
                        <label for="default_order_type"><i class="fas fa-fw fa-sm fa-sort text-muted mr-1"></i> <?= l('account_preferences.default_order_type') ?></label>
                        <select id="default_order_type" name="default_order_type" class="custom-select <?= \Altum\Alerts::has_field_errors('default_order_type') ? 'is-invalid' : null ?>">
                            <option value="ASC" <?= ($this->user->preferences->default_order_type ?? settings()->main->default_order_type) == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                            <option value="DESC" <?= ($this->user->preferences->default_order_type ?? settings()->main->default_order_type) == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('default_order_type') ?>
                    </div>

                    <?php if(settings()->monitors_heartbeats->monitors_is_enabled): ?>
                    <div class="form-group">
                        <label for="monitors_default_order_by"><i class="fas fa-fw fa-sm fa-server text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('monitors.title')) ?></label>
                        <select id="monitors_default_order_by" name="monitors_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('monitors_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="monitor_id" <?= $this->user->preferences->monitors_default_order_by == 'monitor_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->monitors_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->monitors_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="last_check_datetime" <?= $this->user->preferences->monitors_default_order_by == 'last_check_datetime' ? 'selected="selected"' : null ?>><?= l('monitors.filters.order_by_last_check_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->monitors_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="uptime" <?= $this->user->preferences->monitors_default_order_by == 'uptime' ? 'selected="selected"' : null ?>><?= l('monitors.filters.order_by_uptime') ?></option>
                            <option value="average_response_time" <?= $this->user->preferences->monitors_default_order_by == 'average_response_time' ? 'selected="selected"' : null ?>><?= l('monitors.filters.order_by_average_response_time') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('monitors_default_order_by') ?>
                    </div>
                    <?php endif ?>

					<?php if(settings()->monitors_heartbeats->heartbeats_is_enabled): ?>
                    <div class="form-group">
                        <label for="heartbeats_default_order_by"><i class="fas fa-fw fa-sm fa-heart-pulse text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('heartbeats.title')) ?></label>
                        <select id="heartbeats_default_order_by" name="heartbeats_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('heartbeats_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="heartbeat_id" <?= $this->user->preferences->heartbeats_default_order_by == 'heartbeat_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->heartbeats_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->heartbeats_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="last_run_datetime" <?= $this->user->preferences->heartbeats_default_order_by == 'last_run_datetime' ? 'selected="selected"' : null ?>><?= l('heartbeats.filters.order_by_last_run_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->heartbeats_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="uptime" <?= $this->user->preferences->heartbeats_default_order_by == 'uptime' ? 'selected="selected"' : null ?>><?= l('heartbeats.filters.order_by_uptime') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('heartbeats_default_order_by') ?>
                    </div>
                    <?php endif ?>

					<?php if(settings()->monitors_heartbeats->server_monitors_is_enabled): ?>
                    <div class="form-group">
                        <label for="server_monitors_default_order_by"><i class="fas fa-fw fa-sm fa-microchip text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('server_monitors.title')) ?></label>
                        <select id="server_monitors_default_order_by" name="server_monitors_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('server_monitors_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="server_monitor_id" <?= $this->user->preferences->server_monitors_default_order_by == 'server_monitor_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->server_monitors_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->server_monitors_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="last_log_datetime" <?= $this->user->preferences->server_monitors_default_order_by == 'last_log_datetime' ? 'selected="selected"' : null ?>><?= l('server_monitor.last_log_datetime') ?></option>
                            <option value="total_logs" <?= $this->user->preferences->server_monitors_default_order_by == 'total_logs' ? 'selected="selected"' : null ?>><?= l('server_monitor.total_logs') ?></option>
                            <option value="cpu_usage" <?= $this->user->preferences->server_monitors_default_order_by == 'cpu_usage' ? 'selected="selected"' : null ?>><?= l('server_monitor.cpu_usage') ?></option>
                            <option value="ram_usage" <?= $this->user->preferences->server_monitors_default_order_by == 'ram_usage' ? 'selected="selected"' : null ?>><?= l('server_monitor.ram_usage') ?></option>
                            <option value="disk_usage" <?= $this->user->preferences->server_monitors_default_order_by == 'disk_usage' ? 'selected="selected"' : null ?>><?= l('server_monitor.disk_usage') ?></option>
                            <option value="uptime" <?= $this->user->preferences->server_monitors_default_order_by == 'uptime' ? 'selected="selected"' : null ?>><?= l('server_monitor.uptime') ?></option>
                            <option value="name" <?= $this->user->preferences->server_monitors_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('server_monitors_default_order_by') ?>
                    </div>
                    <?php endif ?>

					<?php if(settings()->monitors_heartbeats->domain_names_is_enabled): ?>
                    <div class="form-group">
                        <label for="domain_names_default_order_by"><i class="fas fa-fw fa-sm fa-network-wired text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('domain_names.title')) ?></label>
                        <select id="domain_names_default_order_by" name="domain_names_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('domain_names_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="domain_name_id" <?= $this->user->preferences->domain_names_default_order_by == 'domain_name_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->domain_names_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->domain_names_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->domain_names_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="target" <?= $this->user->preferences->domain_names_default_order_by == 'target' ? 'selected="selected"' : null ?>><?= l('domain_name.input.target') ?></option>
                            <optgroup label="<?= l('domain_name.whois') ?>">
                                <option value="whois_start_datetime" <?= $this->user->preferences->domain_names_default_order_by == 'whois_start_datetime' ? 'selected="selected"' : null ?>><?= l('domain_name.whois_start_datetime') ?></option>
                                <option value="whois_updated_datetime" <?= $this->user->preferences->domain_names_default_order_by == 'whois_updated_datetime' ? 'selected="selected"' : null ?>><?= l('domain_name.whois_updated_datetime') ?></option>
                                <option value="whois_end_datetime" <?= $this->user->preferences->domain_names_default_order_by == 'whois_end_datetime' ? 'selected="selected"' : null ?>><?= l('domain_name.whois_end_datetime') ?></option>
                            </optgroup>
                            <optgroup label="<?= l('domain_name.ssl') ?>">
                                <option value="ssl_start_datetime" <?= $this->user->preferences->domain_names_default_order_by == 'ssl_start_datetime' ? 'selected="selected"' : null ?>><?= l('domain_name.ssl_start_datetime') ?></option>
                                <option value="ssl_end_datetime" <?= $this->user->preferences->domain_names_default_order_by == 'ssl_end_datetime' ? 'selected="selected"' : null ?>><?= l('domain_name.ssl_end_datetime') ?></option>
                            </optgroup>
                        </select>
                        <?= \Altum\Alerts::output_field_error('domain_names_default_order_by') ?>
                    </div>
                    <?php endif ?>

					<?php if(settings()->monitors_heartbeats->game_servers_is_enabled): ?>
                        <div class="form-group">
                            <label for="game_servers_default_order_by"><i class="fas fa-fw fa-sm fa-gamepad text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('game_servers.title')) ?></label>
                            <select id="game_servers_default_order_by" name="game_servers_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('game_servers_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="game_server_id" <?= $this->user->preferences->game_servers_default_order_by == 'game_server_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->game_servers_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $this->user->preferences->game_servers_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="last_check_datetime" <?= $this->user->preferences->game_servers_default_order_by == 'last_check_datetime' ? 'selected="selected"' : null ?>><?= l('game_servers.last_check_datetime') ?></option>
                                <option value="total_checks" <?= $this->user->preferences->game_servers_default_order_by == 'total_checks' ? 'selected="selected"' : null ?>><?= l('game_server.total_checks') ?></option>
                                <option value="uptime" <?= $this->user->preferences->game_servers_default_order_by == 'uptime' ? 'selected="selected"' : null ?>><?= l('game_servers.uptime') ?></option>
                                <option value="average_response_time" <?= $this->user->preferences->game_servers_default_order_by == 'average_response_time' ? 'selected="selected"' : null ?>><?= l('game_servers.average_response_time') ?></option>
                                <option value="average_online_players" <?= $this->user->preferences->game_servers_default_order_by == 'average_online_players' ? 'selected="selected"' : null ?>><?= l('game_servers.average_online_players') ?></option>
                                <option value="online_players" <?= $this->user->preferences->game_servers_default_order_by == 'online_players' ? 'selected="selected"' : null ?>><?= l('game_servers.online_players') ?></option>
                                <option value="maximum_online_players" <?= $this->user->preferences->game_servers_default_order_by == 'maximum_online_players' ? 'selected="selected"' : null ?>><?= l('game_servers.maximum_online_players') ?></option>
                                <option value="name" <?= $this->user->preferences->game_servers_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>game_servers.online_players
                            </select>
							<?= \Altum\Alerts::output_field_error('game_servers_default_order_by') ?>
                        </div>
					<?php endif ?>

                    <?php if(settings()->status_pages->status_pages_is_enabled): ?>
                    <div class="form-group">
                        <label for="status_pages_default_order_by"><i class="fas fa-fw fa-sm fa-wifi text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('status_pages.title')) ?></label>
                        <select id="status_pages_default_order_by" name="status_pages_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('status_pages_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="status_page_id" <?= $this->user->preferences->status_pages_default_order_by == 'status_page_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->status_pages_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->status_pages_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->status_pages_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="pageviews" <?= $this->user->preferences->status_pages_default_order_by == 'pageviews' ? 'selected="selected"' : null ?>><?= l('status_pages.table.pageviews') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('status_pages_default_order_by') ?>
                    </div>
					<?php endif ?>

                    <div class="form-group">
                        <label for="notification_handlers_default_order_by"><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('notification_handlers.title')) ?></label>
                        <select id="notification_handlers_default_order_by" name="notification_handlers_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('notification_handlers_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="notification_handler_id" <?= $this->user->preferences->notification_handlers_default_order_by == 'notification_handler_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->notification_handlers_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->notification_handlers_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->notification_handlers_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('notification_handlers_default_order_by') ?>
                    </div>

                    <?php if(settings()->status_pages->domains_is_enabled): ?>
                        <div class="form-group">
                            <label for="domains_default_order_by"><i class="fas fa-fw fa-sm fa-globe text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('domains.title')) ?></label>
                            <select id="domains_default_order_by" name="domains_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('domains_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="domain_id" <?= $this->user->preferences->domains_default_order_by == 'domain_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->domains_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $this->user->preferences->domains_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="host" <?= $this->user->preferences->domains_default_order_by == 'host' ? 'selected="selected"' : null ?>><?= l('domains.table.host') ?></option>
                            </select>
                            <?= \Altum\Alerts::output_field_error('domains_default_order_by') ?>
                        </div>
                    <?php endif ?>

                    <?php if(settings()->monitors_heartbeats->projects_is_enabled): ?>
                        <div class="form-group">
                            <label for="projects_default_order_by"><i class="fas fa-fw fa-sm fa-project-diagram text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('projects.title')) ?></label>
                            <select id="projects_default_order_by" name="projects_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('projects_default_order_by') ? 'is-invalid' : null ?>">
                                <option value="project_id" <?= $this->user->preferences->projects_default_order_by == 'project_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $this->user->preferences->projects_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $this->user->preferences->projects_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="name" <?= $this->user->preferences->projects_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            </select>
                            <?= \Altum\Alerts::output_field_error('projects_default_order_by') ?>
                        </div>
                    <?php endif ?>

                    <?php if(settings()->monitors_heartbeats->monitors_is_enabled || settings()->monitors_heartbeats->heartbeats_is_enabled): ?>
                    <div class="form-group">
                        <label for="incidents_default_order_by"><i class="fas fa-fw fa-sm fa-exclamation-circle text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('incidents.title')) ?></label>
                        <select id="incidents_default_order_by" name="incidents_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('incidents_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="incident_id" <?= $this->user->preferences->incidents_default_order_by == 'incident_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="start_datetime" <?= $this->user->preferences->incidents_default_order_by == 'start_datetime' ? 'selected="selected"' : null ?>><?= l('incidents.start_datetime') ?></option>
                            <option value="end_datetime" <?= $this->user->preferences->incidents_default_order_by == 'end_datetime' ? 'selected="selected"' : null ?>><?= l('incidents.end_datetime') ?></option>
                            <option value="last_failed_check_datetime" <?= $this->user->preferences->incidents_default_order_by == 'last_failed_check_datetime' ? 'selected="selected"' : null ?>><?= l('incidents.last_failed_check_datetime') ?></option>
                            <option value="failed_checks" <?= $this->user->preferences->incidents_default_order_by == 'failed_checks' ? 'selected="selected"' : null ?>><?= l('incidents.failed_checks') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('incidents_default_order_by') ?>
                    </div>
                    <?php endif ?>
                </div>

                <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#dashboard_settings_container" aria-expanded="false" aria-controls="dashboard_settings_container">
                    <i class="fas fa-fw fa-table-cells fa-sm mr-1"></i> <?= l('account_preferences.dashboard_features') ?>
                </button>

                <div class="collapse" data-parent="#account_preferences" id="dashboard_settings_container">
                    <div class="form-group">
                        <label><i class="fas fa-fw fa-sm fa-table-cells text-muted mr-1"></i> <?= l('account_preferences.dashboard_features') ?></label>
                    </div>

                    <div id="dashboard_features">
                        <?php $dashboard_features = ((array) $this->user->preferences->dashboard) + array_fill_keys(require APP_PATH . 'includes/available_dashboard_features.php', true) ?>
                        <?php $index = 0; ?>
                        <?php foreach($dashboard_features as $feature => $is_enabled): ?>
                        <div class="d-flex">
                            <span class="cursor-grab drag mr-3" data-toggle="tooltip" title="<?= l('global.drag_and_drop') ?>">
                                    <i class="fas fa-fw fa-sm fa-bars text-muted"></i>
                                </span>

                            <div class="form-group custom-control custom-checkbox" data-dashboard-feature>
                                <input id="<?= 'dashboard_' . $feature ?>" name="dashboard[<?= $index++ ?>]" value="<?= $feature ?>" type="checkbox" class="custom-control-input" <?= $is_enabled ? 'checked="checked"' : null ?>>
                                <label class="custom-control-label" for="<?= 'dashboard_' . $feature ?>"><?= l('dashboard.' . $feature . '.header') ?></label>
                            </div>
                        </div>
                        <?php endforeach ?>
                    </div>
                </div>

                <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#tracking_settings_container" aria-expanded="false" aria-controls="tracking_settings_container">
                    <i class="fas fa-fw fa-eye fa-sm mr-1"></i> <?= l('account_preferences.tracking_settings') ?>
                </button>

                <div class="collapse" data-parent="#account_preferences" id="tracking_settings_container">
                    <div class="form-group" data-character-counter="textarea">
                        <label for="excluded_ips" class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-fw fa-sm fa-eye-slash text-muted mr-1"></i> <?= l('account_preferences.excluded_ips') ?></span>
                            <small class="text-muted" data-character-counter-wrapper></small>
                        </label>
                        <textarea id="excluded_ips" class="form-control" name="excluded_ips" maxlength="500"><?= implode(',', $this->user->preferences->excluded_ips ?? []) ?></textarea>
                        <small class="form-text text-muted"><?= l('account_preferences.excluded_ips_help') ?></small>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('global.update') ?></button>
            </form>
        </div>
    </div>
</div>


<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/sortable.js?v=' . PRODUCT_CODE ?>"></script>
<script>
    'use strict';

    let sortable = Sortable.create(document.getElementById('dashboard_features'), {
        animation: 150,
        handle: '.drag',
        onUpdate: event => {

            /* Refresh tooltips */
            tooltips_initiate();

            document.querySelectorAll('#dashboard_features > div').forEach((elm, i) => {
                let input = elm.querySelector('input[type="checkbox"]');
                if(input) {
                    input.setAttribute('name', `dashboard[${i}]`);
                }
            });

        }
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
