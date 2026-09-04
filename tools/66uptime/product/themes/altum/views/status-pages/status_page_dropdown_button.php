<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" class="btn btn-link <?= $data->button_text_class ?? 'text-secondary' ?> dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
        <i class="fas fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="<?= url('status-page-redirect/' . $data->id) ?>" target="_blank" rel="noreferrer"><i class="fas fa-fw fa-sm fa-external-link-alt mr-2"></i> <?= l('status_pages.external_url') ?></a>
        <a class="dropdown-item" href="<?= url('status-page-qr/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-qrcode mr-2"></i> <?= l('status_page_qr.menu') ?></a>
        <a class="dropdown-item" href="<?= url('status-page-statistics/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-chart-bar mr-2"></i> <?= l('status_page_statistics.menu') ?></a>
        <a class="dropdown-item" href="<?= url('status-page-update/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
        <a href="#" data-toggle="modal" data-target="#status_page_duplicate_modal" data-status-page-id="<?= $data->id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-clone mr-2"></i> <?= l('global.duplicate') ?></a>
        <a href="#" data-toggle="modal" data-target="#status_page_reset_modal" data-status-page-id="<?= $data->id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-redo mr-2"></i> <?= l('global.reset') ?></a>
        <a href="#" data-toggle="modal" data-target="#status_page_delete_modal" data-status-page-id="<?= $data->id ?>" data-resource-name="<?= $data->resource_name ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'status_page',
    'resource_id' => 'status_page_id',
    'has_dynamic_resource_name' => true,
    'path' => 'status-pages/delete'
]), 'modals', 'status_page_delete_modal'); ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/duplicate_modal.php', ['modal_id' => 'status_page_duplicate_modal', 'resource_id' => 'status_page_id', 'path' => 'status-pages/duplicate']), 'modals', 'status_page_duplicate_modal'); ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/x_reset_modal.php', ['modal_id' => 'status_page_reset_modal', 'resource_id' => 'status_page_id', 'path' => 'status-pages/reset']), 'modals', 'status_page_reset_modal'); ?>
