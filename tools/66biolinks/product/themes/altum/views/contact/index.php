<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="text-center">
        <h1 class="h1 font-weight-700"><?= l('contact.header') ?></h1>

        <p class="text-muted font-size-little-small mb-0"><?= l('contact.subheader') ?></p>

        <div class="mb-4">&nbsp;</div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="row">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="email"><i class="fas fa-fw fa-sm fa-envelope text-muted mr-1"></i> <?= l('contact.email') ?></label>
                            <input id="email" type="email" name="email" class="form-control <?= \Altum\Alerts::has_field_errors('email') ? 'is-invalid' : null ?>" value="<?= $data->values['email'] ?>" maxlength="64" required="required" />
                            <?= \Altum\Alerts::output_field_error('email') ?>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('contact.name') ?></label>
                            <input id="name" type="text" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" maxlength="320" required="required" />
                            <?= \Altum\Alerts::output_field_error('name') ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="subject"><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('contact.subject') ?></label>
                    <input id="subject" type="text" name="subject" class="form-control <?= \Altum\Alerts::has_field_errors('subject') ? 'is-invalid' : null ?>" value="<?= $data->values['subject'] ?>" maxlength="128" required="required" />
                    <?= \Altum\Alerts::output_field_error('subject') ?>
                </div>

                <div class="form-group" data-character-counter="textarea">
                    <label for="message" class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-fw fa-sm fa-paragraph text-muted mr-1"></i> <?= l('contact.message') ?></span>
                        <small class="text-muted" data-character-counter-wrapper></small>
                    </label>
                    <textarea id="message" name="message" class="form-control <?= \Altum\Alerts::has_field_errors('message') ? 'is-invalid' : null ?>" minlength="32" maxlength="2048" required="required"><?= $data->values['message'] ?></textarea>
                    <?= \Altum\Alerts::output_field_error('message') ?>
                </div>

                <?php if(settings()->captcha->contact_is_enabled): ?>
                    <div class="form-group">
                        <?php $data->captcha->display() ?>
                    </div>
                <?php endif ?>

                <button type="submit" name="submit" class="btn btn-primary btn-block"><?= l('contact.submit') ?></button>
            </form>
        </div>
    </div>

    <div class="mt-4 row">
        <div class="col-12 col-lg-6 p-3" data-aos="fade-up" data-aos-delay="100">
            <div class="card mb-md-0 h-100 up-animation">
                <div class="card-body icon-zoom-animation">
                    <div class="rounded-2x bg-primary-50 text-primary contact-icon-wrapper d-flex align-items-center justify-content-center mb-3">
                        <i class="fas fa-fw fa-lock text-primary"></i>
                    </div>

                    <a href="<?= url('pages') ?>" class="stretched-link text-decoration-none">
                        <h2 class="h6 mb-1 text-reset"><?= l('contact.help.header') ?></h2>
                    </a>

                    <small class="text-muted m-0"><?= l('contact.help.subheader') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 p-3" data-aos="fade-up" data-aos-delay="200">
            <div class="card mb-md-0 h-100 up-animation">
                <div class="card-body icon-zoom-animation">
                    <div class="rounded-2x bg-primary-50 text-primary contact-icon-wrapper d-flex align-items-center justify-content-center mb-3">
                        <i class="fas fa-fw fa-user-shield text-primary"></i>
                    </div>

                    <h2 class="h6 mb-1"><?= l('contact.response.header') ?></h2>

                    <small class="text-muted m-0"><?= l('contact.response.subheader') ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start() ?>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": <?= json_encode(l('index.title')) ?>,
                    "item": <?= json_encode(url()) ?>
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": <?= json_encode(l('contact.title')) ?>,
                    "item": <?= json_encode(url('contact')) ?>
                }
            ]
        }
    </script>

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "ContactPage",
            "name": <?= json_encode(l('contact.title')) ?>,
            "description": <?= json_encode(l('contact.meta_description') ?? l('contact.title')) ?>,
            "url": <?= json_encode(url('contact')) ?>,
            "mainEntityOfPage": {
                "@type": "WebPage",
                "@id": <?= json_encode(url('contact')) ?>
            },
            "publisher": {
                "@type": "Organization",
                "name": <?= json_encode(settings()->main->title) ?>
                <?php if(settings()->main->{'logo_' . \Altum\ThemeStyle::get()}): ?>,
                "logo": {
                    "@type": "ImageObject",
                    "url": <?= json_encode(settings()->main->{'logo_' . \Altum\ThemeStyle::get() . '_full_url'}) ?>
                }
                <?php endif ?>
            },
            "about": {
                "@type": "Organization",
                "name": <?= json_encode(settings()->main->title) ?>,
                "url": <?= json_encode(url()) ?>,
                "contactPoint": {
                    "@type": "ContactPoint",
                    "url": <?= json_encode(url('contact')) ?>,
                    "contactType": "customer support"
                }
            }
        }
    </script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
