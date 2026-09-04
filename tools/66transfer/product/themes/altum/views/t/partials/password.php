<?php defined('ALTUMCODE') || die() ?>

<div class="row justify-content-center">
    <div class="col-10">

        <div class="mb-4 d-flex align-items-center mb-3">
            <h1 class="h3 m-0"><?= l('t_transfer.password.header')  ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('t_transfer.password.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <?= \Altum\Alerts::output_alerts() ?>

        <form action="" method="post" role="form">
            <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
            <input type="hidden" name="type" value="password" />

            <div class="form-group">
                <label for="password"><?= l('global.password') ?></label>
                <input type="password" id="password" name="password" value="" class="form-control <?= \Altum\Alerts::has_field_errors('password') ? 'is-invalid' : null ?>" maxlength="64" required="required" />
                <?= \Altum\Alerts::output_field_error('password') ?>
            </div>

            <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.submit') ?></button>
        </form>

    </div>
</div>
