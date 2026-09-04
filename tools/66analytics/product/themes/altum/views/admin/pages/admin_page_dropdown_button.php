<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" class="btn btn-link <?= $data->button_text_class ?? 'text-secondary' ?> dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
        <i class="fas fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <?php if($data->type != 'external'): ?>
            <a class="dropdown-item <?= $data->type == 'feature' ? 'disabled' : null ?>" href="<?= url('admin/page-preview/' . $data->id) ?>" target="_blank" rel="noreferrer"><i class="fas fa-fw fa-sm fa-eye mr-2"></i> <?= l('global.preview') ?></a>
        <?php endif ?>
        <?php if($data->type == 'external' || $data->is_published): ?>
            <a class="dropdown-item" href="<?= $data->type == 'internal' ? SITE_URL . ($data->language ? \Altum\Language::$active_languages[$data->language] . '/' : null) . 'page/' . $data->url : $data->url ?>" target="_blank" rel="noreferrer"><i class="fas fa-fw fa-sm fa-external-link-alt mr-2"></i> <?= l('global.view') ?></a>
        <?php endif ?>
        <a class="dropdown-item" href="admin/page-update/<?= $data->id ?>"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
        <a href="#" data-toggle="modal" data-target="#page_duplicate_modal" data-page-id="<?= $data->id ?>" class="dropdown-item <?= $data->type == 'feature' ? 'disabled' : null ?>"><i class="fas fa-fw fa-sm fa-clone mr-2"></i> <?= l('global.duplicate') ?></a>

        <a href="#" data-toggle="modal" data-target="#page_delete_modal" data-page-id="<?= $data->id ?>" data-resource-name="<?= $data->resource_name ?>" class="dropdown-item <?= $data->type == 'feature' ? 'disabled' : null ?>"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/duplicate_modal.php', ['modal_id' => 'page_duplicate_modal', 'resource_id' => 'page_id', 'path' => 'admin/pages/duplicate']), 'modals', 'page_duplicate_modal'); ?>
