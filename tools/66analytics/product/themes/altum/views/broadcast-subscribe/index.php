<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="text-center">
        <h1 class="h1 font-weight-700"><?= l('broadcast_subscribe.header') ?></h1>

        <p class="text-muted font-size-little-small mb-0"><?= l('broadcast_subscribe.subheader') ?></p>

        <div class="mb-4">&nbsp;</div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="row">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="email"><i class="fas fa-fw fa-sm fa-envelope text-muted mr-1"></i> <?= l('global.email') ?></label>
                            <input id="email" type="email" name="email" class="form-control <?= \Altum\Alerts::has_field_errors('email') ? 'is-invalid' : null ?>" value="<?= $data->values['email'] ?>" maxlength="320" required="required" />
                            <?= \Altum\Alerts::output_field_error('email') ?>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                            <input id="name" type="text" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" maxlength="64" required="required" />
                            <?= \Altum\Alerts::output_field_error('name') ?>
                        </div>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-primary btn-block"><?= l('broadcast_subscribe.submit') ?></button>
            </form>
        </div>
    </div>

    <div class="mt-4 row">
        <div class="col-12 col-lg-4 p-3" data-aos="fade-up" data-aos-delay="100">
            <div class="card mb-md-0 h-100 up-animation">
                <div class="card-body icon-zoom-animation">
                    <div class="rounded-2x bg-primary-50 text-primary contact-icon-wrapper d-flex align-items-center justify-content-center mb-3">
                        <i class="fas fa-fw fa-rocket text-primary"></i>
                    </div>

                    <h2 class="h6 mb-1"><?= l('broadcast_subscribe.widget.updates.header') ?></h2>

                    <small class="text-muted m-0"><?= l('broadcast_subscribe.widget.updates.subheader') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 p-3" data-aos="fade-up" data-aos-delay="200">
            <div class="card mb-md-0 h-100 up-animation">
                <div class="card-body icon-zoom-animation">
                    <div class="rounded-2x bg-primary-50 text-primary contact-icon-wrapper d-flex align-items-center justify-content-center mb-3">
                        <i class="fas fa-fw fa-tag text-primary"></i>
                    </div>

                    <h2 class="h6 mb-1"><?= l('broadcast_subscribe.widget.offers.header') ?></h2>

                    <small class="text-muted m-0"><?= l('broadcast_subscribe.widget.offers.subheader') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 p-3" data-aos="fade-up" data-aos-delay="300">
            <div class="card mb-md-0 h-100 up-animation">
                <div class="card-body icon-zoom-animation">
                    <div class="rounded-2x bg-primary-50 text-primary contact-icon-wrapper d-flex align-items-center justify-content-center mb-3">
                        <i class="fas fa-fw fa-user-shield text-primary"></i>
                    </div>

                    <h2 class="h6 mb-1"><?= l('broadcast_subscribe.widget.no_spam.header') ?></h2>

                    <small class="text-muted m-0"><?= l('broadcast_subscribe.widget.no_spam.subheader') ?></small>
                </div>
            </div>
        </div>
    </div>
</div>
