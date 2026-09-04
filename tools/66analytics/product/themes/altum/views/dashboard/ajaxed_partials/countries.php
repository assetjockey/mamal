<?php defined('ALTUMCODE') || die() ?>

<div class="altum-animate altum-animate-fill-none altum-animate-fade-in">
    <div class="d-flex justify-content-between mb-2">
        <div>
            <small class="text-muted font-weight-bold text-uppercase"><?= l('global.country') ?></small>
        </div>

        <div class="d-flex justify-content-end">
            <div class="col p-0">
                <small class="text-muted font-weight-bold text-uppercase"><?= l('analytics.' . $data->by) ?></small>
            </div>
        </div>
    </div>

    <?php if(!$data->total_sum): ?>
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <div>
                    <span class="text-muted"><?= l('dashboard.basic.no_data') ?></span>
                </div>

                <div class="d-flex justify-content-end">
                    <div class="col">-</div>

                    <div class="col p-0 text-right row-fade-show-icon-percentage"><small class="text-muted">-</small></div>
                </div>
            </div>
        </div>
    <?php else: ?>

        <?php foreach($data->rows as $row): ?>
            <?php $percentage = round($row->total / $data->total_sum * 100, 2) ?>

            <div class="row-fade-show-icon position-relative">
                <div class="progress bg-gray-100 h-100 w-100 position-absolute">
                    <div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%;" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <div class="d-flex justify-content-between mb-1 row-fade-show-icon-content">
                    <div class="d-flex align-items-center" style="min-width:0;">
                        <img src="<?= ASSETS_FULL_URL . 'images/countries/' . ($row->country_code ? mb_strtolower($row->country_code) : 'unknown') . '.svg' ?>" class="img-fluid icon-favicon mr-2 flex-shrink-0" />

                        <?php if($row->country_code && ($data->cities_link ?? true)): ?>
                            <a href="#" data-target="#cities_modal" data-toggle="modal" data-country-code="<?= $row->country_code ?>" class="text-truncate text-body" style="min-width:0;"><?= $row->country_code ? get_country_from_country_code($row->country_code) : l('global.unknown') ?></a>
                        <?php else: ?>
                            <span class="text-truncate" style="min-width:0;"><?= $row->country_code ? get_country_from_country_code($row->country_code) : l('global.unknown') ?></span>
                        <?php endif ?>

                        <?php if($row->country_code): ?>
                            <a href="#" data-add-filter data-filter-by="country_code" data-filter-rule="is" data-filter-value="<?= e($row->country_code) ?>" class="text-muted ml-1 flex-shrink-0" data-toggle="tooltip" title="<?= l('analytics.filters.create') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-xs fa-filter"></i></a>
                        <?php endif ?>
                    </div>

                    <div class="d-flex justify-content-end">
                        <div class="col"><?= nr($row->total) ?></div>

                        <div class="col p-0 text-right row-fade-show-icon-percentage"><small class="text-muted"><?= $percentage ?>%</small></div>
                    </div>
                </div>
            </div>

        <?php endforeach ?>

        <?php if($data->total_rows > count($data->rows)): ?>
            <a href="<?= url($this->base_url_path . 'countries') ?>" class="text-body font-size-small mt-2"><?= sprintf(l('global.view_x_more'), nr($data->total_rows - count($data->rows))) ?></a>
        <?php endif ?>

    <?php endif ?>
</div>
