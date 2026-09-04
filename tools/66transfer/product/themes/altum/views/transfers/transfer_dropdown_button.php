<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" class="btn btn-link <?= $data->button_text_class ?? 'text-secondary' ?> dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
        <i class="fas fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a href="<?= url('transfer-redirect/' . $data->id) ?>" class="dropdown-item" target="_blank" rel="noreferrer"><i class="fas fa-fw fa-sm fa-external-link-alt mr-2"></i> <?= l('global.view') ?></a>
        <a href="<?= url('transfer/' . $data->id) ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-eye mr-2"></i> <?= l('transfer.menu') ?></a>
        <a href="<?= url('transfer-update/' . $data->id) ?>" class="dropdown-item" ><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
        <div class="dropdown-divider"></div>
        <a href="#" data-toggle="modal" data-target="#share_modal" data-url="<?= $data->full_url ?>" class="dropdown-item" ><i class="fas fa-fw fa-sm fa-share-alt mr-2"></i> <?= l('global.share') ?></a>
        <a href="<?= url('transfer-statistics/' . $data->id) ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-chart-bar mr-2"></i> <?= l('transfer_statistics.menu') ?></a>
        <a href="<?= url('transfer-downloads/' . $data->id) ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-download mr-2"></i> <?= l('transfer_downloads.menu') ?></a>
        <div class="dropdown-divider"></div>
        <a href="#" data-toggle="modal" data-target="#transfer_delete_modal" data-transfer-id="<?= $data->id ?>" data-resource-name="<?= $data->resource_name ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'transfer',
    'resource_id' => 'transfer_id',
    'has_dynamic_resource_name' => true,
    'path' => 'transfers/delete'
]), 'modals', 'transfer_dropdown_modal'); ?>

<?php if(!\Altum\Event::exists_content_type_key('javascript', 'share_modal')) include_view(THEME_PATH . 'views/partials/share_modal_js.php') ?>
