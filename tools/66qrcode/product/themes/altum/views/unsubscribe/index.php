<?php defined('ALTUMCODE') || die() ?>

<?= \Altum\Alerts::output_alerts() ?>

<?php if(!$data->user->is_broadcast_subscribed || !empty($_POST)): ?>

    <h1 class="h5"><?= l('unsubscribe.success.header') ?></h1>
    <p class="text-muted font-size-little-small"><?= l('unsubscribe.success.subheader') ?></p>

    <div class="mt-4 text-center">
        <a href="<?= url() ?>" class="btn btn-outline-primary btn-block">
            <?= l('unsubscribe.return') ?>
        </a>
    </div>

<?php else: ?>

    <h1 class="h5"><?= l('unsubscribe.header') ?></h1>
    <p class="text-muted font-size-little-small"><?= l('unsubscribe.subheader') ?></p>

    <div class="mt-4 text-center">
        <form action="" method="post" role="form">
            <input type="hidden" name="action" value="unsubscribe">
            <input type="hidden" name="token" value="<?= $data->token ?>">
            <input type="hidden" name="type" value="<?= $data->type ?>">
            <?php if($data->unsubscribe_code): ?>
                <input type="hidden" name="unsubscribe_code" value="<?= $data->unsubscribe_code ?>">
            <?php endif ?>

            <button type="submit" class="btn btn-primary btn-block">
                <?= l('unsubscribe.unsubscribe') ?>
            </button>

            <a href="<?= url() ?>" class="btn btn-outline-primary btn-block mt-3">
                <?= l('unsubscribe.return') ?>
            </a>
        </form>
    </div>

<?php endif ?>

