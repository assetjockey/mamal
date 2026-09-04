<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" class="btn btn-link <?= isset($data->button_text_class) ? $data->button_text_class : 'text-secondary' ?> dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
        <i class="fas fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a href="#" data-toggle="modal" data-target="#broadcast_subscriber_update_status_modal" data-broadcast-subscriber-id="<?= $data->id ?>" data-status="<?= $data->status ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-user-check mr-2"></i> <?= l('global.edit') ?></a>
        <?php if($data->status == 0): ?>
            <a href="#" data-toggle="modal" data-target="#broadcast_subscriber_resend_confirmation_modal" data-broadcast-subscriber-id="<?= $data->id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-paper-plane mr-2"></i> <?= l('admin_broadcast_subscribers.resend_confirmation') ?></a>
        <?php endif ?>
        <a href="#" data-toggle="modal" data-target="#broadcast_subscriber_delete_modal" data-broadcast-subscriber-id="<?= $data->id ?>" data-resource-name="<?= $data->resource_name ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/admin/broadcast-subscribers/broadcast_subscriber_update_status_modal.php'), 'modals', 'broadcast_subscriber_update_status_modal'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/admin/broadcast-subscribers/broadcast_subscriber_resend_confirmation_modal.php'), 'modals', 'broadcast_subscriber_resend_confirmation_modal'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_url.php', [
    'name' => 'broadcast_subscriber',
    'resource_id' => 'broadcast_subscriber_id',
    'has_dynamic_resource_name' => true,
    'path' => 'admin/broadcast-subscribers/delete/'
]), 'modals', 'broadcast_subscriber_delete_modal'); ?>
