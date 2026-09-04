<?php defined('ALTUMCODE') || die() ?>

<nav>
    <div class="d-flex flex-row align-items-center mt-4 nav gap-3 flex-wrap" role="tablist">
        <a class="btn btn-custom flex-grow-1 text-truncate active" data-toggle="pill" href="#paths" role="tab" aria-controls="paths_result" aria-selected="true">
            <?= l('dashboard.paths.header') ?>
        </a>

        <a class="btn btn-custom flex-grow-1 text-truncate mx-3" data-toggle="pill" href="#landing_paths" role="tab" aria-controls="landing_paths_result" aria-selected="false">
            <?= l('dashboard.landing_paths.header') ?>
        </a>

        <?php if($this->website->tracking_type == 'advanced'): ?>
        <a class="btn btn-custom flex-grow-1 text-truncate " data-toggle="pill" href="#exit_paths" role="tab" aria-controls="exit_paths_result" aria-selected="false">
            <?= l('dashboard.exit_paths.header') ?>
        </a>
        <?php endif ?>
    </div>
</nav>

<div class="tab-content mt-5">
    <div class="tab-pane fade show active" id="paths" role="tabpanel" aria-labelledby="paths_result">
        <div class="card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 m-0"><?= l('dashboard.paths.header') ?></h2>
                    </div>
                    <span class="round-circle-sm bg-gray-200 text-primary-700 p-3">
                        <i class="fas fa-fw fa-sm fa-copy"></i>
                    </span>
                </div>

                <div class="mt-4" id="paths_result" data-limit="-1" data-bounce-rate="true"></div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="landing_paths" role="tabpanel" aria-labelledby="landing_paths_result">
        <div class="card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 m-0"><?= l('dashboard.landing_paths.header') ?></h2>
                    </div>
                    <span class="round-circle-sm bg-gray-200 text-primary-700 p-3">
                        <i class="fas fa-fw fa-sm fa-plane-arrival"></i>
                    </span>
                </div>

                <div class="mt-4" id="landing_paths_result" data-limit="-1" data-bounce-rate="true"></div>
            </div>
        </div>
    </div>

    <?php if($this->website->tracking_type == 'advanced'): ?>
    <div class="tab-pane fade" id="exit_paths" role="tabpanel" aria-labelledby="exit_paths_result">
        <div class="card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 m-0"><?= l('dashboard.exit_paths.header') ?></h2>
                    </div>
                    <span class="round-circle-sm bg-gray-200 text-primary-700 p-3">
                        <i class="fas fa-fw fa-sm fa-door-open"></i>
                    </span>
                </div>

                <div class="mt-4" id="exit_paths_result" data-limit="-1" data-bounce-rate="true"></div>
            </div>
        </div>
    </div>
    <?php endif ?>
</div>

<?php ob_start() ?>
<script>
    'use strict';


</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
