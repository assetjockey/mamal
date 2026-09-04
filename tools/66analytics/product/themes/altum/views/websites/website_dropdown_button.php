<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" class="btn btn-link <?= $data->button_text_class ?? 'text-secondary' ?> dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
        <i class="fas fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="<?= url('website-update/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>

        <a href="#" data-toggle="modal" data-target="#website_reset_modal" data-website-id="<?= $data->id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-redo mr-2"></i> <?= l('global.reset') ?></a>

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


<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/x_reset_modal.php', ['modal_id' => 'website_reset_modal', 'resource_id' => 'website_id', 'path' => 'websites/reset']), 'modals', 'website_reset_modal'); ?>

<?php \Altum\Event::add_content((new \Altum\View('websites/website_delete_modal'))->run(), 'modals', 'website_delete_modal'); ?>
