<?php defined('ALTUMCODE') || die() ?>

<div class="accordion">
    <div class="card mt-5">
        <div class="card-header bg-white position-relative rounded">
            <a href="#" class="stretched-link text-decoration-none text-reset" data-toggle="collapse" data-target="#main" aria-expanded="true" aria-controls="main">
            <div class="d-flex justify-content-between">
                <span class="small font-weight-bold"><?= l('game_server.main') ?></span>

                <span class="badge bg-primary-50 text-primary-700">
                    <i class="fas fa-fw fa-sm fa-circle-info"></i>
                </span>
            </div>
            </a>

        </div>

        <div id="main" class="collapse show">
            <div class="card-body">

                <?php if(isset($data->game_server->details->favicon)): ?>
                    <div class="row">
                        <div class="col-12 col-xl-4 mb-2 mb-xl-0">
                            <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                                <i class="fas fa-fw fa-image fa-xs text-muted mr-1"></i>
                                <?= l('game_server.favicon') ?>
                            </div>
                        </div>

                        <div class="col-12 col-xl-8">
                            <img src="<?= $data->game_server->details->favicon ?>" class="game-server-icon" />
                        </div>
                    </div>

                    <div class="flex-fill my-3">
                        <hr class="border-gray-100" />
                    </div>
                <?php endif ?>

                <?php if(isset($data->game_server->details->version_name)): ?>
                    <div class="row">
                        <div class="col-12 col-xl-4 mb-2 mb-xl-0">
                            <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                                <i class="fas fa-fw fa-code fa-xs text-muted mr-1"></i>
                                <?= l('game_server.version') ?>
                            </div>
                        </div>

                        <div class="col-12 col-xl-8">
                            <?= $data->game_server->details->version_name . ($data->game_server->details->version_code ? ' (v' . $data->game_server->details->version_code . ')' : '') ?>
                        </div>
                    </div>

                    <div class="flex-fill my-3">
                        <hr class="border-gray-100" />
                    </div>
                <?php endif ?>

                <?php if(isset($data->game_server->details->mod_type)): ?>
                    <div class="row">
                        <div class="col-12 col-xl-4 mb-2 mb-xl-0">
                            <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                                <i class="fas fa-fw fa-fingerprint fa-xs text-muted mr-1"></i>
                                <?= l('game_server.mod_type') ?>
                            </div>
                        </div>

                        <div class="col-12 col-xl-8">
                            <?= $data->game_server->details->mod_type ?>
                        </div>
                    </div>

                    <div class="flex-fill my-3">
                        <hr class="border-gray-100" />
                    </div>
                <?php endif ?>

                <?php if(isset($data->game_server->details->description)): ?>
                    <div class="row">
                        <div class="col-12 col-xl-4 mb-2 mb-xl-0">
                            <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                                <i class="fas fa-fw fa-palette fa-xs text-muted mr-1"></i>
                                <?= l('game_server.motd') ?>
                            </div>
                        </div>

                        <div class="col-12 col-xl-8">
                            <?= $data->game_server->details->description_html ?>
                        </div>
                    </div>

                    <div class="flex-fill my-3">
                        <hr class="border-gray-100" />
                    </div>

                    <div class="row">
                        <div class="col-12 col-xl-4 mb-2 mb-xl-0">
                            <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                                <i class="fas fa-fw fa-signature fa-xs text-muted mr-1"></i>
                                <?= l('game_server.motd_plain_text') ?>
                            </div>
                        </div>

                        <div class="col-12 col-xl-8">
                            <?= $data->game_server->details->description ?>
                        </div>
                    </div>

                    <div class="flex-fill my-3">
                        <hr class="border-gray-100" />
                    </div>
                <?php endif ?>

            </div>
        </div>
    </div>
</div>
