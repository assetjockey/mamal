<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <div class="text-center">
        <h1 class="h1 font-weight-700"><?= l('affiliate.header') ?></h1>

        <p class="text-muted font-size-little-small mb-0"><?= l('affiliate.subheader') ?></p>

        <div class="mb-4">&nbsp;</div>
    </div>

    <div class="mt-4 row">
        <div class="col-12 col-lg-6 p-3" data-aos="fade-up" data-aos-delay="100">
            <div class="card mb-md-0 h-100 up-animation">
                <div class="card-body icon-zoom-animation">
                    <div class="rounded-2x bg-primary-50 text-primary contact-icon-wrapper d-flex align-items-center justify-content-center mb-3">
                        <i class="fas fa-fw fa-money-check-dollar text-primary"></i>
                    </div>

                    <h2 class="h6 mb-1 text-reset"><?= sprintf(l('affiliate.commission_percentage.header'), '<span class="text-primary">' . ($data->minimum_commission == $data->maximum_commission ? $data->minimum_commission : $data->minimum_commission . '% - ' . $data->maximum_commission) . '%</span>') ?></h2>

                    <small class="text-muted m-0"><?= l('affiliate.commission_percentage.subheader_' . settings()->affiliate->commission_type) ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 p-3" data-aos="fade-up" data-aos-delay="200">
            <div class="card mb-md-0 h-100 up-animation">
                <div class="card-body icon-zoom-animation">
                    <div class="rounded-2x bg-primary-50 text-primary contact-icon-wrapper d-flex align-items-center justify-content-center mb-3">
                        <i class="fas fa-fw fa-money-bill-transfer text-primary"></i>
                    </div>

                    <h2 class="h6 mb-1"><?= sprintf(l('affiliate.minimum_withdrawal_amount.header'), '<span class="text-primary">' . settings()->affiliate->minimum_withdrawal_amount . ' ' . settings()->payment->default_currency . '</span>') ?></h2>

                    <small class="text-muted m-0"><?= l('affiliate.minimum_withdrawal_amount.subheader') ?></small>
                </div>
            </div>
        </div>
    </div>


    <div class="mt-5">
        <h2 class="h4 mb-4"><?= l('affiliate.how.header') ?></h2>

        <div class="index-timeline">
            <div class="row justify-content-between">
                <div class="col-12 col-lg-12 mb-4" data-aos="fade-up">
                    <div class="timeline-item d-flex justify-content-center">
                        <div>
                            <div class="card border-0 bg-primary-100 text-primary mr-4">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-user-plus fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div class="card h-100 icon-zoom-animation w-100">
                            <div class="card-body">
                                <div class="d-flex flex-column">
                                    <span class="h6">1. <?= l('affiliate.how.one') ?></span>
                                    <small class="text-muted"><?= l('affiliate.how.one_help') ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-12 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="timeline-item d-flex justify-content-center">
                        <div>
                            <div class="card border-0 bg-primary-100 text-primary mr-4">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-link fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div class="card h-100 icon-zoom-animation w-100">
                            <div class="card-body">
                                <div class="d-flex flex-column">
                                    <span class="h6">2. <?= l('affiliate.how.two') ?></span>
                                    <small class="text-muted"><?= l('affiliate.how.two_help') ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-12 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="index-timeline-item d-flex justify-content-center">
                        <div>
                            <div class="card border-0 bg-primary-100 text-primary mr-4">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-wallet fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div class="card h-100 icon-zoom-animation w-100">
                            <div class="card-body">
                                <div class="d-flex flex-column">
                                    <span class="h6">3. <?= l('affiliate.how.three') ?></span>
                                    <small class="text-muted"><?= l('affiliate.how.three_help') ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-12" data-aos="fade-up" data-aos-delay="300">
                    <div class="timeline-item d-flex justify-content-center">
                        <div>
                            <div class="card border-0 bg-primary-100 text-primary mr-4">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fas fa-fw fa-money-bill fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div class="card h-100 icon-zoom-animation w-100">
                            <div class="card-body">
                                <div class="d-flex flex-column">
                                    <span class="h6">4. <?= l('affiliate.how.four') ?></span>
                                    <small class="text-muted"><?= l('affiliate.how.four_help') ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-7">
        <h2 class="h4"><?= l('plan.faq.header') ?></h2>

        <?php
        $language_array = \Altum\Language::get(\Altum\Language::$name);
        if(\Altum\Language::$main_name != \Altum\Language::$name) {
            $language_array = array_merge(\Altum\Language::get(\Altum\Language::$main_name), $language_array);
        }

        $affiliate_language_keys = [];
        foreach ($language_array as $key => $value) {
            if(preg_match('/affiliate\.faq\.(\w+)\./', $key, $matches)) {
                $affiliate_language_keys[] = $matches[1];
            }
        }

        $affiliate_language_keys = array_unique($affiliate_language_keys);
        ?>

        <div class="accordion index-faq mt-4" id="faq_accordion">
            <?php foreach($affiliate_language_keys as $key): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="" id="<?= 'faq_accordion_' . $key ?>">
                            <h3 class="mb-0">
                                <button class="btn font-weight-500 btn-block d-flex justify-content-between text-gray-800 px-0 icon-zoom-animation no-focus text-left" type="button" data-toggle="collapse" data-target="<?= '#faq_accordion_answer_' . $key ?>" aria-expanded="true" aria-controls="<?= 'faq_accordion_answer_' . $key ?>">
                                    <span><?= l('affiliate.faq.' . $key . '.question') ?></span>

                                    <span data-icon>
                                        <i class="fas fa-fw fa-circle-chevron-down"></i>
                                    </span>
                                </button>
                            </h3>
                        </div>

                        <div id="<?= 'faq_accordion_answer_' . $key ?>" class="collapse text-muted mt-3" aria-labelledby="<?= 'faq_accordion_' . $key ?>" data-parent="#faq_accordion">
                            <?php if($key == 'earn'): ?>
                                <?= sprintf(l('affiliate.faq.' . $key . '.answer'), $data->maximum_commission) ?>
                            <?php elseif($key == 'cookie'): ?>
                                <?= sprintf(l('affiliate.faq.' . $key . '.answer'), settings()->affiliate->tracking_duration) ?>
                            <?php else: ?>
                                <?= l('affiliate.faq.' . $key . '.answer') ?>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>

    <?php ob_start() ?>
    <script>
        'use strict';

        $('#faq_accordion').on('show.bs.collapse', event => {
            let svg = event.target.parentElement.querySelector('[data-icon] svg')
            svg.style.transform = 'rotate(180deg)';
            svg.style.color = 'var(--primary)';
        })

        $('#faq_accordion').on('hide.bs.collapse', event => {
            let svg = event.target.parentElement.querySelector('[data-icon] svg')
            svg.style.color = 'var(--primary-800)';
            svg.style.removeProperty('transform');
        })
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

    <?php if(settings()->users->register_is_enabled): ?>
        <div class="mt-5">
            <div class="card">
                <div class="card-body py-5 py-lg-6">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-12 col-lg-5">
                            <div class="text-center text-lg-left mb-4 mb-lg-0">
                                <h1 class="h2 text-gray-900"><?= l('affiliate.cta.header') ?></h1>
                                <p class="h6 text-gray-600"><?= l('affiliate.cta.subheader') ?></p>
                            </div>
                        </div>

                        <div class="col-12 col-lg-5 mt-4 mt-lg-0">
                            <div class="text-center text-lg-right">
                                <?php if(is_logged_in()): ?>
                                    <a href="<?= url('referrals') ?>" class="btn btn-outline-primary badge-pill index-button">
                                        <?= l('referrals.menu') ?> <i class="fas fa-fw fa-arrow-right"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="<?= url('register') ?>" class="btn btn-outline-primary badge-pill index-button">
                                        <?= l('index.cta.register') ?> <i class="fas fa-fw fa-arrow-right"></i>
                                    </a>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>
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
                    "name": <?= json_encode(l('affiliate.title')) ?>,
                    "item": <?= json_encode(url('affiliate')) ?>
                }
            ]
        }
</script>

<?php
$faqs = [];
foreach($affiliate_language_keys as $key) {
    if($key == 'earn') {
        $text = sprintf(l('affiliate.faq.' . $key . '.answer'), $data->maximum_commission);
    } elseif($key == 'cookie') {
        $text = sprintf(l('affiliate.faq.' . $key . '.answer'), settings()->affiliate->tracking_duration);
    } else {
        $text = l('affiliate.faq.' . $key . '.answer');
    }

    $faqs[] = [
            '@type' => 'Question',
            'name' => l('affiliate.faq.' . $key . '.question'),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $text,
            ]
    ];
}
?>
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": <?= json_encode($faqs) ?>
    }
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/index-custom.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<link rel="stylesheet" href="<?= ASSETS_FULL_URL . 'css/libraries/aos.min.css?v=' . PRODUCT_CODE ?>">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/aos.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    'use strict';

    AOS.init({
        duration: 600,
        easing: 'ease-out-cubic',
        once: true,
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

