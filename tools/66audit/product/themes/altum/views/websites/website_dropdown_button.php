<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" class="btn btn-link <?= $data->button_text_class ?? 'text-secondary' ?> dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
        <i class="fas fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="<?= url('website/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-eye mr-2"></i> <?= l('global.view') ?></a>

        <a
                href="#"
                data-toggle="modal"
                data-target="#website_refresh_modal"
                data-website-id="<?= $data->id ?>"
                class="dropdown-item"
        ><i class="fas fa-fw fa-sm fa-retweet mr-2"></i> <?= l('website_refresh_modal.button') ?></a>

        <a class="dropdown-item" href="<?= url('website-update/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>

        <a
                href="#"
                data-toggle="modal"
                data-target="#website_delete_modal"
                data-website-id="<?= $data->id ?>"
                data-resource-name="<?= $data->resource_name ?>"
                class="dropdown-item"
        ><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'website',
    'resource_id' => 'website_id',
    'has_dynamic_resource_name' => true,
    'path' => 'websites/delete'
]), 'modals', 'website_delete_modal'); ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/websites/website_refresh_modal.php'), 'modals', 'website_refresh_modal'); ?>
