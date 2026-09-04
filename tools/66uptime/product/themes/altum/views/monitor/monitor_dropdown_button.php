<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" class="btn btn-link <?= $data->button_text_class ?? 'text-secondary' ?> dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
        <i class="fas fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="<?= url('monitor/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-server mr-2"></i> <?= l('global.view') ?></a>
        <a class="dropdown-item" href="<?= url('monitor-logs/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-check-circle mr-2"></i> <?= l('monitor_logs.menu') ?></a>
        <a class="dropdown-item" href="<?= url('monitor-update/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
        <a href="#" data-toggle="modal" data-target="#monitor_duplicate_modal" data-monitor-id="<?= $data->id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-clone mr-2"></i> <?= l('global.duplicate') ?></a>
        <a href="#" data-toggle="modal" data-target="#monitor_delete_modal" data-monitor-id="<?= $data->id ?>" data-resource-name="<?= $data->resource_name ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'monitor',
    'resource_id' => 'monitor_id',
    'has_dynamic_resource_name' => true,
    'path' => 'monitors/delete'
]), 'modals', 'monitor_delete_modal'); ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/duplicate_modal.php', ['modal_id' => 'monitor_duplicate_modal', 'resource_id' => 'monitor_id', 'path' => 'monitors/duplicate']), 'modals', 'monitor_duplicate_modal'); ?>
