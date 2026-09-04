<?php defined('ALTUMCODE') || die() ?>

<div class="altum-animate altum-animate-fill-none altum-animate-fade-in">
    <div class="d-flex justify-content-between mb-2">
        <div>
            <small class="text-muted font-weight-bold text-uppercase mr-3"><?= l('dashboard.outbound_clicks.domain') ?></small>
        </div>

        <div>
            <small class="text-muted font-weight-bold text-uppercase"><?= l('analytics.' . $data->by) ?></small>
        </div>
    </div>

    <?php if(!$data->total_rows): ?>
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
            <?php $percentage = $data->total_sum > 0 ? round($row->total / $data->total_sum * 100, 1) : 0 ?>

            <div class="row-fade-show-icon position-relative">
                <div class="progress bg-gray-100 h-100 w-100 position-absolute">
                    <div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%;" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <div class="d-flex justify-content-between mb-1 row-fade-show-icon-content">
                    <div>
                        <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($row->host) ?>" class="img-fluid icon-favicon mr-2" loading="lazy" />
                        <a href="#" class="text-truncate text-body" data-target="#outbound_clicks_paths_modal" data-toggle="modal" data-host="<?= $row->host ?>" title="<?= $row->host ?>"><?= $row->host ?></a>
                        <a href="<?= 'https://' . $row->host ?>" target="_blank" rel="nofollow noopener" class="text-muted ml-1"><i class="fas fa-fw fa-xs fa-external-link-alt"></i></a>
                    </div>

                    <div class="d-flex justify-content-end">
                        <div class="col"><?= nr($row->total) ?></div>

                        <div class="col p-0 text-right row-fade-show-icon-percentage"><small class="text-muted"><?= $percentage ?>%</small></div>
                    </div>
                </div>
            </div>

        <?php endforeach ?>

        <?php if($data->total_rows > count($data->rows)): ?>
            <a href="<?= url($this->base_url_path . 'outbound-clicks') ?>" class="text-body font-size-small mt-2"><?= sprintf(l('global.view_x_more'), nr($data->total_rows - count($data->rows))) ?></a>
        <?php endif ?>

    <?php endif ?>
</div>
