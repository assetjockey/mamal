<?php defined('ALTUMCODE') || die() ?>

<nav>
    <div class="d-flex flex-row align-items-center mt-4 nav gap-3 flex-wrap" role="tablist">
        <a class="btn btn-custom flex-grow-1 text-truncate active" data-toggle="pill" href="#referrers" role="tab" aria-controls="referrers_result" aria-selected="true">
            <?= l('dashboard.referrers.header') ?>
        </a>

        <a class="btn btn-custom flex-grow-1 text-truncate" data-toggle="pill" href="#social_media_referrers" role="tab" aria-controls="social_media_referrers_result" aria-selected="false">
            <?= l('dashboard.social_media_referrers.header') ?>
        </a>

        <a class="btn btn-custom flex-grow-1 text-truncate" data-toggle="pill" href="#search_engines_referrers" role="tab" aria-controls="search_engines_referrers_result" aria-selected="false">
            <?= l('dashboard.search_engines_referrers.header') ?>
        </a>

        <a class="btn btn-custom flex-grow-1 text-truncate " data-toggle="pill" href="#ai_referrers" role="tab" aria-controls="ai_referrers_result" aria-selected="false">
            <?= l('dashboard.ai_referrers.header') ?>
        </a>
    </div>
</nav>

<div class="tab-content mt-5">
    <div class="tab-pane fade show active" id="referrers" role="tabpanel" aria-labelledby="referrers_result">
        <div class="card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 m-0"><?= l('dashboard.referrers.header') ?></h2>
                    </div>
                    <span class="round-circle-sm bg-gray-200 text-primary-700 p-3">
                        <i class="fas fa-fw fa-sm fa-random"></i>
                    </span>
                </div>

                <div class="mt-4" id="referrers_result" data-limit="-1" data-bounce-rate="true"></div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="social_media_referrers" role="tabpanel" aria-labelledby="social_media_referrers_result">
        <div class="card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 m-0"><?= l('dashboard.social_media_referrers.header') ?></h2>
                    </div>
                    <span class="round-circle-sm bg-gray-200 text-primary-700 p-3">
                        <i class="fas fa-fw fa-sm fa-share-alt"></i>
                    </span>
                </div>

                <div class="mt-4" id="social_media_referrers_result" data-limit="-1" data-bounce-rate="true"></div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="search_engines_referrers" role="tabpanel" aria-labelledby="search_engines_referrers_result">
        <div class="card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 m-0"><?= l('dashboard.search_engines_referrers.header') ?></h2>
                    </div>
                    <span class="round-circle-sm bg-gray-200 text-primary-700 p-3">
                        <i class="fas fa-fw fa-sm fa-search"></i>
                    </span>
                </div>

                <div class="mt-4" id="search_engines_referrers_result" data-limit="-1" data-bounce-rate="true"></div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="ai_referrers" role="tabpanel" aria-labelledby="ai_referrers_result">
        <div class="card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 m-0"><?= l('dashboard.ai_referrers.header') ?></h2>
                    </div>
                    <span class="round-circle-sm bg-gray-200 text-primary-700 p-3">
                        <i class="fas fa-fw fa-sm fa-search"></i>
                    </span>
                </div>

                <div class="mt-4" id="ai_referrers_result" data-limit="-1" data-bounce-rate="true"></div>
            </div>
        </div>
    </div>
</div>
