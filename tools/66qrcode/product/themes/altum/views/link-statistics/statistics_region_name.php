<?php defined('ALTUMCODE') || die() ?>

<div class="card my-3">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <h3 class="h5 text-truncate m-0"><?= $data->country_code ? sprintf(l('link_statistics.region_name_from_country'), get_country_from_country_code($data->country_code)) : l('global.regions') ?></h3>

                <div class="ml-2">
                    <span data-toggle="tooltip" title="<?= l('link_statistics.region_name_help') ?>">
                        <i class="fas fa-fw fa-info-circle text-muted"></i>
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center col-auto p-0">
                <div class="dropdown">
                    <button type="button" class="btn btn-light dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('link-statistics/' . $data->link->link_id . '?' . \Altum\Router::$original_request_query . '&export=csv') ?>" target="_blank" class="dropdown-item">
                            <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('link-statistics/' . $data->link->link_id . '?' . \Altum\Router::$original_request_query . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-code mr-2"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                        </a>
                        <a href="#" class="dropdown-item <?= $this->user->plan_settings->export->pdf ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->pdf ? 'onclick="event.preventDefault(); window.print();"' : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-pdf mr-2"></i> <?= sprintf(l('global.export_to'), 'PDF') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if(!count($data->rows)): ?>
            <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                'filters_get' => isset($data->filters) ? $data->filters->get : [],
                'name' => 'global',
                'has_secondary_text' => false,
                'has_wrapper' => false,
            ]); ?>
        <?php else: ?>

            <?php foreach($data->rows as $row): ?>
                <?php
                /* Prepare region data */
                $country_code = $data->country_code ? $data->country_code : $row->country_code;
                $region_name = $row->region_name ? $row->region_name : l('global.unknown');

                $percentage = round($row->total / $data->total_sum * 100, 2);
                ?>

                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-1">
                        <div class="text-truncate">
                            <img src="<?= ASSETS_FULL_URL . 'images/countries/' . ($country_code ? mb_strtolower($country_code) : 'unknown') . '.svg' ?>" class="img-fluid icon-favicon mr-1" data-toggle="tooltip" title="<?= $country_code ? get_country_from_country_code($country_code) : l('global.unknown') ?>" />
                            <?php if($row->region_name): ?>
                                <a href="<?= url('link-statistics/' . $data->link->link_id . '?type=city_name&country_code=' . $country_code . '&region_name=' . urlencode($row->region_name) . '&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" class=""><?= $region_name ?></a>
                            <?php else: ?>
                                <span class=""><?= $region_name ?></span>
                            <?php endif ?>
                        </div>

                        <div>
                            <small class="text-muted"><?= nr($percentage, 2, false) . '%' ?></small>
                            <span class="ml-3"><?= nr($row->total) ?></span>
                        </div>
                    </div>

                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%;" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            <?php endforeach ?>

        <?php endif ?>
    </div>
</div>
