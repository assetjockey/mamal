<?php defined('ALTUMCODE') || die() ?>

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

            <?php if(isset($data->game_server->details->HostName)): ?>
                <div class="row">
                    <div class="col-12 col-xl-4 mb-2 mb-xl-0">
                        <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                            <i class="fas fa-fw fa-signature fa-xs text-muted mr-1"></i>
                            <?= l('game_server.hostname') ?>
                        </div>
                    </div>

                    <div class="col-12 col-xl-8">
                        <?= $data->game_server->details->HostName ?>
                    </div>
                </div>

                <div class="flex-fill my-3">
                    <hr class="border-gray-100" />
                </div>
            <?php endif ?>

            <?php if(isset($data->game_server->details->Map)): ?>
                <div class="row">
                    <div class="col-12 col-xl-4 mb-2 mb-xl-0">
                        <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                            <i class="fas fa-fw fa-map fa-xs text-muted mr-1"></i>
                            <?= l('game_server.map') ?>
                        </div>
                    </div>

                    <div class="col-12 col-xl-8">
                        <?= $data->game_server->details->Map ?>
                    </div>
                </div>

                <div class="flex-fill my-3">
                    <hr class="border-gray-100" />
                </div>
            <?php endif ?>

            <?php if(isset($data->game_server->details->Bots)): ?>
                <div class="row">
                    <div class="col-12 col-xl-4 mb-2 mb-xl-0">
                        <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                            <i class="fas fa-fw fa-robot fa-xs text-muted mr-1"></i>
                            <?= l('game_server.bots') ?>
                        </div>
                    </div>

                    <div class="col-12 col-xl-8">
                        <?= nr($data->game_server->details->Bots) ?>
                    </div>
                </div>

                <div class="flex-fill my-3">
                    <hr class="border-gray-100" />
                </div>
            <?php endif ?>

            <?php if(isset($data->game_server->details->Secure)): ?>
                <div class="row">
                    <div class="col-12 col-xl-4 mb-2 mb-xl-0">
                        <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                            <i class="fas fa-fw fa-shield fa-xs text-muted mr-1"></i>
                            <?= l('game_server.secure') ?>
                        </div>
                    </div>

                    <div class="col-12 col-xl-8">
                        <?= $data->game_server->details->Secure ? l('global.yes') : l('global.no') ?>
                    </div>
                </div>

                <div class="flex-fill my-3">
                    <hr class="border-gray-100" />
                </div>
            <?php endif ?>

            <?php if(isset($data->game_server->details->Password)): ?>
                <div class="row">
                    <div class="col-12 col-xl-4 mb-2 mb-xl-0">
                        <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                            <i class="fas fa-fw fa-lock fa-xs text-muted mr-1"></i>
                            <?= l('game_server.password') ?>
                        </div>
                    </div>

                    <div class="col-12 col-xl-8">
                        <?= $data->game_server->details->Password ? l('global.yes') : l('global.no') ?>
                    </div>
                </div>

                <div class="flex-fill my-3">
                    <hr class="border-gray-100" />
                </div>
            <?php endif ?>

        </div>
    </div>
</div>

<?php if(!is_null($data->game_server->details->players_list)): ?>
    <div class="card mt-3">
        <div class="card-header bg-white position-relative rounded">

            <a href="#" class="stretched-link text-decoration-none text-reset" data-toggle="collapse" data-target="#players_list" aria-expanded="true" aria-controls="players_list">
                <div class="d-flex justify-content-between">
                    <div>
                        <span class="small font-weight-bold"><?= l('game_server.players_list') ?></span>
                        <span class="ml-2 small text-muted">(<?= nr($data->game_server->online_players) ?>)</span>
                    </div>

                    <span class="badge bg-primary-50 text-primary-700">
                        <i class="fas fa-fw fa-sm fa-users"></i>
                    </span>
                </div>
            </a>

        </div>

        <div id="players_list" class="collapse">
            <div class="card-body">

                <div class="row">
                    <div class="col-12 col-xl-4 mb-2 mb-xl-0">
                        <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                            <i class="fas fa-fw fa-user fa-xs text-muted mr-1"></i>
                            <?= l('global.name') ?>
                        </div>
                    </div>

                    <div class="col-12 col-xl-8">
                        <div class="row">
                            <div class="col-6">
                                <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                                    <i class="fas fa-fw fa-crosshairs fa-xs text-muted mr-1"></i>
                                    <?= l('game_server.frags') ?>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                                    <i class="fas fa-fw fa-clock fa-xs text-muted mr-1"></i>
                                    <?= l('game_server.online_time') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-fill my-3">
                    <hr class="border-gray-100" />
                </div>

                <?php if(empty($data->game_server->details->players_list)): ?>
                    <div class="row">
                        <div class="col-12">
                            <p class="text-muted"><?= l('game_server.players_list.no_data') ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach($data->game_server->details->players_list as $player): ?>
                        <div class="row">
                            <div class="col-12 col-xl-4 mb-2 mb-xl-0">
                                <div class="text-truncate">
                                    <?= $player->Name ?: l('global.na') ?>
                                </div>
                            </div>

                            <div class="col-12 col-xl-8">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                                            <?= nr($player->Frags) ?>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                                            <?= $player->TimeF ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex-fill my-3">
                            <hr class="border-gray-100" />
                        </div>
                    <?php endforeach ?>
                <?php endif ?>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if(!is_null($data->game_server->details->variables)): ?>
    <div class="card mt-3">
        <div class="card-header bg-white position-relative rounded">

            <a href="#" class="stretched-link text-decoration-none text-reset" data-toggle="collapse" data-target="#variables" aria-expanded="true" aria-controls="variables">
                <div class="d-flex justify-content-between">
                    <div>
                        <span class="small font-weight-bold"><?= l('game_server.variables') ?></span>
                        <span class="ml-2 small text-muted">(<?= nr(count((array) $data->game_server->details->variables)) ?>)</span>
                    </div>

                    <span class="badge bg-primary-50 text-primary-700">
                        <i class="fas fa-fw fa-sm fa-code"></i>
                    </span>
                </div>
            </a>

        </div>

        <div id="variables" class="collapse">
            <div class="card-body">

                <?php foreach($data->game_server->details->variables as $key => $value): ?>
                    <div class="row">
                        <div class="col-12 col-xl-4 mb-2 mb-xl-0">
                            <div class="font-weight-500 font-size-little-small text-muted text-truncate">
                                <i class="fas fa-fw fa-key fa-xs text-muted mr-1"></i>
                                <?= $key ?>
                            </div>
                        </div>

                        <div class="col-12 col-xl-8">
                            <?= $value ?>
                        </div>
                    </div>

                    <div class="flex-fill my-3">
                        <hr class="border-gray-100" />
                    </div>
                <?php endforeach ?>

            </div>
        </div>
    </div>
<?php endif ?>
