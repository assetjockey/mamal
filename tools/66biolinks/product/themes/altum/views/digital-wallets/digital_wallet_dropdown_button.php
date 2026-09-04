<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" class="btn btn-link <?= $data->button_text_class ?? 'text-secondary' ?> dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
        <i class="fas fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="<?= url('digital-wallet-update/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
        <?php if(digital_wallets_is_google_wallet_ready()): ?>
            <a href="#" data-toggle="modal" data-target="#share_modal" data-url="<?= url('digital-wallet-add/' . $data->hash . '?provider=google') ?>" class="dropdown-item"><i class="fab fa-fw fa-sm fa-google mr-2"></i> <?= l('digital_wallets.share_google_wallet') ?></a>
        <?php endif ?>
        <?php if(digital_wallets_is_apple_wallet_ready()): ?>
            <a href="#" data-toggle="modal" data-target="#share_modal" data-url="<?= url('digital-wallet-add/' . $data->hash . '?provider=apple') ?>" class="dropdown-item"><i class="fab fa-fw fa-sm fa-apple mr-2"></i> <?= l('digital_wallets.share_apple_wallet') ?></a>
        <?php endif ?>
        <a href="#" data-toggle="modal" data-target="#digital_wallet_delete_modal" data-digital-wallet-id="<?= $data->id ?>" data-resource-name="<?= $data->resource_name ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php if(!\Altum\Event::exists_content_type_key('javascript', 'share_modal')) include_view(THEME_PATH . 'views/partials/share_modal_js.php') ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
	'name' => 'digital_wallet',
	'resource_id' => 'digital_wallet_id',
	'has_dynamic_resource_name' => true,
	'path' => 'digital-wallets/delete'
]), 'modals'); ?>
