<?php defined('ALTUMCODE') || die() ?>


<div class="altum-animate altum-animate-fill-none altum-animate-fade-in">
    <div class="d-flex justify-content-between mb-2">
        <div>
            <small class="text-muted font-weight-bold text-uppercase"><?= l('dashboard.paths.path') ?></small>
        </div>

        <div class="d-flex justify-content-end">
            <?php if($data->options['bounce_rate']): ?>
                <div class="col">
                    <small class="text-muted font-weight-bold text-uppercase"><?= l('analytics.' . $data->by) ?></small>
                </div>

                <div class="col p-0 text-right">
                    <small class="text-muted font-weight-bold text-uppercase"><?= l('analytics.bounce_rate') ?></small>
                </div>
            <?php else: ?>
                <div class="col p-0">
                    <small class="text-muted font-weight-bold text-uppercase"><?= l('analytics.' . $data->by) ?></small>
                </div>
            <?php endif ?>
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
            <?php $bounce_rate = !isset($row->bounces) || is_null($row->bounces) ? null : round($row->bounces / $row->total * 100, 1) ?>

            <div class="row-fade-show-icon position-relative">
                <div class="progress bg-gray-100 h-100 w-100 position-absolute">
                    <div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%;" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <div class="d-flex justify-content-between mb-1 row-fade-show-icon-content">
                    <div class="d-flex align-items-center" style="min-width:0;">
                        <span title="<?= $row->path ?>" class="text-truncate" style="min-width:0;"><?= $row->path ?></span>
                        <a href="<?= $this->website->scheme . $this->website->host . $this->website->path . $row->path ?>" target="_blank" rel="nofollow noopener" class="text-muted ml-1 flex-shrink-0"><i class="fas fa-fw fa-xs fa-external-link-alt"></i></a>
                        <a href="#" data-add-filter data-filter-by="path" data-filter-rule="is" data-filter-value="<?= e($row->path) ?>" class="text-muted ml-1 flex-shrink-0" data-toggle="tooltip" title="<?= l('analytics.filters.create') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-xs fa-filter"></i></a>
                    </div>

                    <div class="d-flex justify-content-end">
                        <div class="col">
                            <span>
                                <?= nr($row->total) ?>
                            </span>
                        </div>

                        <div class="col p-0 text-right row-fade-show-icon-percentage"><small class="text-muted"><?= $percentage ?>%</small></div>

                        <?php if($data->options['bounce_rate']): ?>
                            <div class="col p-0 text-right" style="min-width:70px;"><small class="text-muted"><?= !is_null($bounce_rate) ? $bounce_rate . '%' : l('global.na') ?></small></div>
                        <?php endif ?>
                    </div>
                </div>
            </div>

        <?php endforeach ?>

        <?php if($data->total_rows > count($data->rows)): ?>
        <a href="<?= url($this->base_url_path . 'paths') ?>" class="text-body font-size-small mt-2"><?= sprintf(l('global.view_x_more'), nr($data->total_rows - count($data->rows))) ?></a>
        <?php endif ?>

    <?php endif ?>
</div>
