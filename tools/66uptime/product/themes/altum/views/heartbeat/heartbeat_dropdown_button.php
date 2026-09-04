<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" class="btn btn-link <?= $data->button_text_class ?? 'text-secondary' ?> dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
        <i class="fas fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="<?= url('heartbeat/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-heartbeat mr-2"></i> <?= l('global.view') ?></a>
        <a class="dropdown-item" href="<?= url('heartbeat-update/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
        <a href="#" data-toggle="modal" data-target="#heartbeat_duplicate_modal" data-heartbeat-id="<?= $data->id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-clone mr-2"></i> <?= l('global.duplicate') ?></a>
        <a href="#" data-toggle="modal" data-target="#heartbeat_delete_modal" data-heartbeat-id="<?= $data->id ?>" data-resource-name="<?= $data->resource_name ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'heartbeat',
    'resource_id' => 'heartbeat_id',
    'has_dynamic_resource_name' => true,
    'path' => 'heartbeats/delete'
]), 'modals', 'heartbeat_delete_modal'); ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/duplicate_modal.php', ['modal_id' => 'heartbeat_duplicate_modal', 'resource_id' => 'heartbeat_id', 'path' => 'heartbeats/duplicate']), 'modals', 'heartbeat_duplicate_modal'); ?>
