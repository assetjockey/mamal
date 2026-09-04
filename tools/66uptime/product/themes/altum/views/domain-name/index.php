<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('domain-names') ?>"><?= l('domain_names.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('domain_name.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="card bg-blue-900 border-0 rounded-2x">
        <div class="card-body">
            <div class="row">
                <div class="col-auto d-flex align-items-center">
                    <?php if($data->domain_name->is_enabled): ?>
                        <?php if(!$data->domain_name->total_checks): ?>
                            <div data-toggle="tooltip" title="<?= l('domain_name.pending_check') ?>">
                                <i class="fas fa-fw fa-clock fa-3x text-gray-400"></i>
                            </div>
                        <?php else: ?>
                            <div class="pulse-animation pulse-animation-success">
                                <span class="pulse-circle"></span>
                                <i class="fas fa-fw fa-check-circle fa-3x text-primary-400"></i>
                            </div>
                        <?php endif ?>
                    <?php else: ?>
                        <div data-toggle="tooltip" title="<?= l('domain_name.is_enabled_paused') ?>">
                            <i class="fas fa-fw fa-pause-circle fa-3x text-warning"></i>
                        </div>
                    <?php endif ?>
                </div>

                <div class="col text-truncate">
                    <h1 class="h3 text-truncate text-white mb-0 mr-2"><?= sprintf(l('domain_name.header'), $data->domain_name->name) ?></h1>

                    <div class="text-gray-400">
                        <span><?= $data->domain_name->target ?></span>
                    </div>
                </div>

                <div class="col-auto">
                    <?= include_view(THEME_PATH . 'views/domain-name/domain_name_dropdown_button.php', ['id' => $data->domain_name->domain_name_id, 'resource_name' => $data->domain_name->name, 'button_text_class' => 'text-white']) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if(!$data->domain_name->last_check_datetime): ?>
        <div class="card  mt-4">
            <div class="card-body">
                <div class="d-flex flex-column align-items-center justify-content-center py-4">
                    <img src="<?= ASSETS_FULL_URL . 'images/processing.svg' ?>" class="col-10 col-md-7 col-lg-5 mb-3" alt="<?= l('domain_name.pending_check') ?>" />
                    <h2 class="h4 text-muted"><?= l('domain_name.no_data_simple') ?></h2>
                    <p class="text-muted"><?= sprintf(l('dns_monitor.no_data_help'), $data->domain_name->name) ?></p>
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if($data->domain_name->total_checks): ?>

        <div class="row justify-content-between my-3 mb-4">
            <div class="col-12 col-lg-6 p-3">
                <div class="card h-100">
                    <div class="card-body d-flex p-3">
                        <div>
                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-globe fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted font-size-small"><?= l('domain_name.total_checks') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0"><?= nr($data->domain_name->total_checks) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6 p-3">
                <div class="card h-100">
                    <div class="card-body d-flex p-3">
                        <div>
                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-calendar-check fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted font-size-small"><?= l('domain_name.last_check_datetime') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h6 m-0"><?= $data->domain_name->last_check_datetime ? \Altum\Date::get_timeago($data->domain_name->last_check_datetime) : l('global.na') ?></div>
                                <div class="ml-2">
                                    <span data-toggle="tooltip" title="<?= sprintf(l('domain_name.next_check_datetime'), \Altum\Date::get_time_until($data->domain_name->next_check_datetime)) ?>">
                                        <i class="fas fa-fw fa-info-circle text-muted"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if(!property_exists($data->domain_name->whois, 'end_datetime') && !property_exists($data->domain_name->whois, 'start_datetime') && !property_exists($data->domain_name->whois, 'updated_datetime')): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center justify-content-center py-4">
                        <img src="<?= ASSETS_FULL_URL . 'images/processing.svg' ?>" class="col-10 col-md-7 col-lg-5 mb-3" alt="<?= l('domain_name.no_dns_data') ?>" />
                        <h2 class="h4 text-muted"><?= l('domain_name.not_registered') ?></h2>
                    </div>
                </div>
            </div>
        <?php else: ?>

            <div class="mb-5">
                <h2 class="h5 mb-3"><i class="fas fa-fw fa-xs fa-network-wired mr-2"></i> <?= l('domain_names.table.domain_name') ?></h2>

                <div class="row justify-content-between">
                    <div class="col-12 p-3">
                        <div class="card h-100">
                            <div class="card-body d-flex p-3">
                                <div>
                                    <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                        <div class="p-3 d-flex align-items-center justify-content-between">
                                            <i class="fas fa-fw fa-calendar-day fa-lg"></i>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <span class="text-muted font-size-small"><?= l('domain_name.whois_start_datetime') ?></span>
                                    <div class="d-flex align-items-center">
                                        <div class="card-title h6 m-0" data-toggle="tooltip" title="<?= is_null($data->domain_name->whois->start_datetime) ? l('domain_name.no_data_simple') : \Altum\Date::get($data->domain_name->whois->start_datetime, 1) ?>">
                                            <?= is_null($data->domain_name->whois->start_datetime) ? l('domain_name.no_data_simple') : \Altum\Date::get($data->domain_name->whois->start_datetime, 2) ?>
                                        </div>

                                        <?php if(!is_null($data->domain_name->whois->start_datetime)): ?>
                                            <span class="text-muted small ml-2">(<?= \Altum\Date::get_time_until($data->domain_name->whois->start_datetime) ?>)</span>
                                        <?php endif ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 p-3">
                        <div class="card h-100">
                            <div class="card-body d-flex p-3">
                                <div>
                                    <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                        <div class="p-3 d-flex align-items-center justify-content-between">
                                            <i class="fas fa-fw fa-sync-alt fa-lg"></i>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <span class="text-muted font-size-small"><?= l('domain_name.whois_updated_datetime') ?></span>
                                    <div class="d-flex align-items-center">
                                        <div class="card-title h6 m-0" data-toggle="tooltip" title="<?= is_null($data->domain_name->whois->updated_datetime) ? l('domain_name.no_data_simple') : \Altum\Date::get($data->domain_name->whois->updated_datetime, 1) ?>">
                                            <?= is_null($data->domain_name->whois->updated_datetime) ? l('domain_name.no_data_simple') : \Altum\Date::get($data->domain_name->whois->updated_datetime, 2) ?>
                                        </div>

                                        <?php if(!is_null($data->domain_name->whois->updated_datetime)): ?>
                                            <span class="text-muted small ml-2">(<?= \Altum\Date::get_time_until($data->domain_name->whois->updated_datetime) ?>)</span>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 p-3">
                        <div class="card h-100">
                            <div class="card-body d-flex p-3">
                                <div>
                                    <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                        <div class="p-3 d-flex align-items-center justify-content-between">
                                            <i class="fas fa-fw fa-calendar-times fa-lg"></i>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <span class="text-muted font-size-small"><?= l('domain_name.whois_end_datetime') ?></span>
                                    <div class="d-flex align-items-center">
                                        <div class="card-title h6 m-0" data-toggle="tooltip" title="<?= is_null($data->domain_name->whois->end_datetime) ? l('domain_name.no_data_simple') : \Altum\Date::get($data->domain_name->whois->end_datetime, 1) ?>">
                                            <?= is_null($data->domain_name->whois->end_datetime) ? l('domain_name.no_data_simple') : \Altum\Date::get($data->domain_name->whois->end_datetime, 2) ?>
                                        </div>

                                        <?php if(!is_null($data->domain_name->whois->end_datetime)): ?>
                                            <span class="text-muted small ml-2">(<?= \Altum\Date::get_time_until($data->domain_name->whois->end_datetime) ?>)</span>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php $registrar = $data->domain_name->last_check_datetime && $data->domain_name->whois && property_exists($data->domain_name->whois, 'registrar') && !empty($data->domain_name->whois->registrar) ? $data->domain_name->whois->registrar : l('global.na'); ?>
                    <div class="col-12 p-3">
                        <div class="card h-100">
                            <div class="card-body d-flex p-3">
                                <div>
                                    <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                        <div class="p-3 d-flex align-items-center justify-content-between">
                                            <i class="fas fa-fw fa-atlas fa-lg"></i>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <span class="text-muted font-size-small"><?= l('domain_name.registrar') ?></span>
                                    <div class="d-flex align-items-center">
                                        <div class="card-title h6 m-0">
                                            <?= $registrar ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php $nameservers = $data->domain_name->last_check_datetime && $data->domain_name->whois && property_exists($data->domain_name->whois, 'registrar') && !empty($data->domain_name->whois->nameservers) ? implode(', ', (array) $data->domain_name->whois->nameservers) : l('global.na'); ?>
                    <div class="col-12 p-3">
                        <div class="card h-100">
                            <div class="card-body d-flex p-3">
                                <div>
                                    <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                        <div class="p-3 d-flex align-items-center justify-content-between">
                                            <i class="fas fa-fw fa-ethernet fa-lg"></i>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <span class="text-muted font-size-small"><?= l('domain_name.nameservers') ?></span>
                                    <div class="d-flex align-items-center">
                                        <div class="card-title h6 m-0">
                                            <?= count($data->domain_name->whois->nameservers) ?>
                                        </div>

                                        <div class="ml-2">
                                            <span data-toggle="tooltip" title="<?= $nameservers ?>">
                                                <i class="fas fa-fw fa-info-circle text-muted"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php foreach($data->domain_name->whois->nameservers ?? [] as $key => $nameserver): ?>
                    <div class="col-12 px-3 py-1">
                        <div class="card">
                            <div class="p-3 d-flex align-items-center justify-content-between">
                                <div class="small ">
                                    <span class="text-muted mr-3">#<?= $key+1 ?></span>

                                    <?= $nameserver ?>
                                </div>

                                <div>
                                    <button
                                            type="button"
                                            class="btn btn-light p-2 rounded index-widget-icon d-flex align-items-center justify-content-center"
                                            data-toggle="tooltip"
                                            title="<?= l('global.clipboard_copy') ?>"
                                            aria-label="<?= l('global.clipboard_copy') ?>"
                                            data-copy="<?= l('global.clipboard_copy') ?>"
                                            data-copied="<?= l('global.clipboard_copied') ?>"
                                            data-clipboard-text="<?= $nameserver ?>"
                                    >
                                        <i class="fas fa-fw fa-sm fa-copy text-muted"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach ?>
                </div>
            </div>

            <?php if($data->domain_name->last_check_datetime && $data->domain_name->ssl && property_exists($data->domain_name->ssl, 'end_datetime')): ?>
                <div>
                    <h2 class="h5 mb-3"><i class="fas fa-fw fa-xs fa-lock mr-2 <?= (new \DateTime($data->domain_name->ssl->end_datetime)) > (new \DateTime()) ? 'text-success' : 'text-danger' ?>"></i> <?= l('domain_name.ssl') ?></h2>

                    <div class="row justify-content-between">
                        <div class="col-12 p-3">
                            <div class="card h-100">
                                <div class="card-body d-flex p-3">
                                    <div>
                                        <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                            <div class="p-3 d-flex align-items-center justify-content-between">
                                                <i class="fas fa-fw fa-calendar-day fa-lg"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-truncate">
                                        <span class="text-muted font-size-small"><?= l('domain_name.ssl_start_datetime') ?></span>
                                        <div class="d-flex align-items-center">
                                            <div class="card-title h6 m-0 text-truncate" data-toggle="tooltip" title="<?= \Altum\Date::get($data->domain_name->ssl->start_datetime, 1) ?>">
                                                <?= \Altum\Date::get($data->domain_name->ssl->start_datetime, 2) ?>
                                            </div>

                                            <?php if(!is_null($data->domain_name->ssl->start_datetime)): ?>
                                                <span class="text-muted small ml-2">(<?= \Altum\Date::get_time_until($data->domain_name->ssl->start_datetime) ?>)</span>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 p-3">
                            <div class="card h-100">
                                <div class="card-body d-flex p-3">
                                    <div>
                                        <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                            <div class="p-3 d-flex align-items-center justify-content-between">
                                                <i class="fas fa-fw fa-calendar-times fa-lg"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-truncate">
                                        <span class="text-muted font-size-small"><?= l('domain_name.ssl_end_datetime') ?></span>
                                        <div class="d-flex align-items-center">
                                            <div class="card-title h6 m-0 text-truncate" data-toggle="tooltip" title="<?= \Altum\Date::get($data->domain_name->ssl->end_datetime, 1) ?>">
                                                <?= \Altum\Date::get($data->domain_name->ssl->end_datetime, 2) ?>
                                            </div>

                                            <?php if(!is_null($data->domain_name->ssl->end_datetime)): ?>
                                                <span class="text-muted small ml-2">(<?= \Altum\Date::get_time_until($data->domain_name->ssl->end_datetime) ?>)</span>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 p-3">
                            <div class="card h-100">
                                <div class="card-body d-flex p-3">
                                    <div>
                                        <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                            <div class="p-3 d-flex align-items-center justify-content-between">
                                                <i class="fas fa-fw fa-building fa-lg"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-truncate">
                                        <span class="text-muted font-size-small"><?= l('domain_name.ssl_organization') ?></span>
                                        <div class="d-flex align-items-center">
                                            <div class="card-title h6 m-0 text-truncate">
                                                <?= $data->domain_name->ssl->organization ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 p-3">
                            <div class="card h-100">
                                <div class="card-body d-flex p-3">
                                    <div>
                                        <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                            <div class="p-3 d-flex align-items-center justify-content-between">
                                                <i class="fas fa-fw fa-file-alt fa-lg"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-truncate">
                                        <span class="text-muted font-size-small"><?= l('domain_name.ssl_common_name') ?></span>
                                        <div class="d-flex align-items-center">
                                            <div class="card-title h6 m-0 text-truncate">
                                                <?= $data->domain_name->ssl->common_name ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 p-3">
                            <div class="card h-100">
                                <div class="card-body d-flex p-3">
                                    <div>
                                        <div class="card border-0 bg-blue-50 text-blue-800 mr-3 rounded-2x">
                                            <div class="p-3 d-flex align-items-center justify-content-between">
                                                <i class="fas fa-fw fa-calendar-times fa-lg"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-truncate">
                                        <span class="text-muted font-size-small"><?= l('domain_name.ssl_signature_type') ?></span>
                                        <div class="d-flex align-items-center">
                                            <div class="card-title h6 m-0 text-truncate">
                                                <?= $data->domain_name->ssl->signature_type ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>

        <?php endif ?>
    <?php endif ?>
</div>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>
