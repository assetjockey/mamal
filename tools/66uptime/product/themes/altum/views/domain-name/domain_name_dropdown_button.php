<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" class="btn btn-link <?= $data->button_text_class ?? 'text-secondary' ?> dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
        <i class="fas fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="<?= url('domain-name/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-network-wired mr-2"></i> <?= l('global.view') ?></a>
        <a class="dropdown-item" href="<?= url('domain-name-update/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
        <a href="#" data-toggle="modal" data-target="#domain_name_duplicate_modal" data-domain-name-id="<?= $data->id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-clone mr-2"></i> <?= l('global.duplicate') ?></a>
        <a href="#" data-toggle="modal" data-target="#domain_name_delete_modal" data-domain-name-id="<?= $data->id ?>" data-resource-name="<?= $data->resource_name ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'domain_name',
    'resource_id' => 'domain_name_id',
    'has_dynamic_resource_name' => true,
    'path' => 'domain-names/delete'
]), 'modals', 'domain_name_delete_modal'); ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/duplicate_modal.php', ['modal_id' => 'domain_name_duplicate_modal', 'resource_id' => 'domain_name_id', 'path' => 'domain-names/duplicate']), 'modals', 'domain_name_duplicate_modal'); ?>
