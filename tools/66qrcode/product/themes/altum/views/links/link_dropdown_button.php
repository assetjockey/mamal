<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" class="btn btn-link <?= $data->button_text_class ?? 'text-secondary' ?> dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
        <i class="fas fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a href="<?= url($data->link->type == 'file' && ($data->link->qr_code_id ?? null) ? 'qr-code-update/' . $data->link->qr_code_id : 'link-update/' . $data->id) ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
        <a href="<?= url('link-statistics/' . $data->id) ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-chart-bar mr-2"></i> <?= l('link_statistics.link') ?></a>
        <a href="#" data-toggle="modal" data-target="#share_modal" data-url="<?= $data->link->full_url ?>" class="dropdown-item" ><i class="fas fa-fw fa-sm fa-share-alt mr-2"></i> <?= l('global.share') ?></a>
        <?php if($data->link->type != 'file'): ?>
            <a href="<?= url('qr-code-create?name=' . $data->link->url . '&project_id=' . $data->link->project_id . '&type=url&url=' . $data->link->full_url . '&link_id=' . $data->link->link_id . '&url_dynamic=1&url_dynamic_existing_link=1') ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-qrcode mr-2"></i> <?= l('qr_codes.create') ?></a>
        <?php endif ?>
        <a href="#" data-toggle="modal" data-target="#link_reset_modal" data-link-id="<?= $data->id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-redo mr-2"></i> <?= l('global.reset') ?></a>
        <?php if($data->link->type != 'file'): ?>
        <a href="#" data-toggle="modal" data-target="#link_duplicate_modal" data-link-id="<?= $data->id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-clone mr-2"></i> <?= l('global.duplicate') ?></a>
        <?php endif ?>
        <a href="#" data-toggle="modal" data-target="#link_delete_modal" data-link-id="<?= $data->id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/x_reset_modal.php', ['modal_id' => 'link_reset_modal', 'resource_id' => 'link_id', 'path' => 'links/reset']), 'modals', 'link_reset_modal'); ?>
<?php if(!\Altum\Event::exists_content_type_key('javascript', 'share_modal')) include_view(THEME_PATH . 'views/partials/share_modal_js.php') ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/duplicate_modal.php', ['modal_id' => 'link_duplicate_modal', 'resource_id' => 'link_id', 'path' => 'links/duplicate']), 'modals', 'link_duplicate_modal'); ?>
