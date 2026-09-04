<?php defined('ALTUMCODE') || die() ?>

<div class="row mt-5">
    <div class="col-12">
        <div class="card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 m-0"><?= l('global.continents') ?></h2>
                    </div>
                    <span class="round-circle-sm bg-gray-200 text-primary-700 p-3">
                        <i class="fas fa-fw fa-sm fa-globe-europe"></i>
                    </span>
                </div>

                <div class="mt-4" id="continents_result" data-limit="-1"></div>
            </div>
        </div>
    </div>
</div>
