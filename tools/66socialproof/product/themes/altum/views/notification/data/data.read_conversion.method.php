<?php defined('ALTUMCODE') || die() ?>

<div class="row">
    <?php foreach((array) $data->conversion->data as $key => $value): ?>
        <div class="col-4 font-weight-bold"><?= $key ?></div>
        <div class="col-8"><?= $value ?></div>
    <?php endforeach ?>

    <div class="col-4 font-weight-bold"><?= l('notification.statistics.path') ?></div>
    <div class="col-8"><?= $data->conversion->path ?: l('global.unknown') ?></div>

    <div class="col-4 font-weight-bold">
        <?= l('notification.data.page_title') ?>
        <span data-toggle="tooltip" title="<?= sprintf(l('notification.data.variable'), 'page_title') ?>"><i class="fas fa-fw fa-sm fa-circle-question ml-1 text-muted"></i></span>
    </div>
    <div class="col-8"><?= $data->conversion->page_title ?: l('global.unknown') ?></div>

    <div class="col-4 font-weight-bold">
        <?= l('global.continent') ?>
        <span data-toggle="tooltip" title="<?= sprintf(l('notification.data.variable'), 'continent') ?>"><i class="fas fa-fw fa-sm fa-circle-question ml-1 text-muted"></i></span>
    </div>
    <div class="col-8"><span class=""><?= isset($data->conversion->location->continent) && $data->conversion->location->continent ? $data->conversion->location->continent : l('global.unknown') ?></span></div>

    <div class="col-4 font-weight-bold">
        <?= l('notification.data.continent_code') ?>
        <span data-toggle="tooltip" title="<?= sprintf(l('notification.data.variable'), 'continent_code') ?>"><i class="fas fa-fw fa-sm fa-circle-question ml-1 text-muted"></i></span>
    </div>
    <div class="col-8"><span class=""><?= isset($data->conversion->location->continent_code) && $data->conversion->location->continent_code ? $data->conversion->location->continent_code : l('global.unknown') ?></span></div>

    <div class="col-4 font-weight-bold">
        <?= l('global.country') ?>
        <span data-toggle="tooltip" title="<?= sprintf(l('notification.data.variable'), 'country') ?>"><i class="fas fa-fw fa-sm fa-circle-question ml-1 text-muted"></i></span>
    </div>
    <div class="col-8">
        <?php if(isset($data->conversion->location->country_code)): ?>
            <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($data->conversion->location->country_code) . '.svg' ?>" class="img-fluid icon-favicon mr-1" alt="<?= l('global.country') ?>" />
        <?php endif ?>
        <span class=""><?= isset($data->conversion->location->country) && $data->conversion->location->country ? $data->conversion->location->country : l('global.unknown') ?></span>
    </div>

    <div class="col-4 font-weight-bold">
        <?= l('notification.data.country_code') ?>
        <span data-toggle="tooltip" title="<?= sprintf(l('notification.data.variable'), 'country_code') ?>"><i class="fas fa-fw fa-sm fa-circle-question ml-1 text-muted"></i></span>
    </div>
    <div class="col-8"><span class=""><?= isset($data->conversion->location->country_code) && $data->conversion->location->country_code ? $data->conversion->location->country_code : l('global.unknown') ?></span></div>

    <div class="col-4 font-weight-bold">
        <?= l('global.city') ?>
        <span data-toggle="tooltip" title="<?= sprintf(l('notification.data.variable'), 'city') ?>"><i class="fas fa-fw fa-sm fa-circle-question ml-1 text-muted"></i></span>
    </div>
    <div class="col-8"><span class=""><?= isset($data->conversion->location->city) && $data->conversion->location->city ? $data->conversion->location->city : l('global.unknown') ?></span></div>
</div>
