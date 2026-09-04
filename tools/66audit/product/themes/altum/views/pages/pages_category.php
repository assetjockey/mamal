<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li><a href="<?= url() ?>"><?= l('index.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li><a href="<?= url('pages') ?>"><?= l('pages.index.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li class="active" aria-current="page"><?= l('pages.pages_category.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4"><?= $data->pages_category->title ?></h1>

    <?php if (!empty($data->pages)): ?>
        <div class="mt-4">
            <div class="row">
                <?php foreach($data->pages as $row): ?>

                    <div class="col-12 mb-3">
                        <a href="<?= $row->type == 'internal' ? SITE_URL . ($row->language ? \Altum\Language::$active_languages[$row->language] . '/' : null) . 'page/' . $row->url : $row->url ?>" target="<?= $row->type == 'internal' ? '_self' : '_blank' ?>" class="text-decoration-none">
                            <div class="card">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <?php if(!empty($row->icon)): ?>
                                            <span class="rounded bg-primary-50 text-primary popular-page-icon-wrapper d-flex align-items-center justify-content-center mr-4"><i class="<?= $row->icon ?> fa-fw"></i></span>
                                        <?php endif ?>

                                        <div>
                                            <div class="h6 mb-0"><?= $row->title ?></div>

                                            <?php if($row->description): ?>
                                                <p class="text-muted font-size-xs mt-1 mb-0"><?= $row->description ?></p>
                                            <?php endif ?>
                                        </div>
                                    </div>

                                    <div>
                                        <span class="font-size-small text-muted">
                                            <i class="fas fa-fw fa-chevron-right"></i>
                                        </span>

                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                <?php endforeach ?>
            </div>
        </div>

    <?php else: ?>
        <div class="mt-4">
            <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                    'filters_get' => $data->filters->get ?? [],
                    'name' => 'pages',
                    'has_secondary_text' => true,
            ]); ?>
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
        "name": <?= json_encode(l('pages.title')) ?>,
                    "item": <?= json_encode(url('pages')) ?>
    },
    {
        "@type": "ListItem",
        "position": 3,
        "name": <?= json_encode($data->pages_category->title) ?>,
                    "item": <?= json_encode(SITE_URL . ($data->pages_category->language ? \Altum\Language::$active_languages[$data->pages_category->language] . '/' : null) . 'pages/' . $data->pages_category->url) ?>
    }
]
}
</script>

<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": <?= json_encode($data->pages_category->title) ?>,
        "description": <?= json_encode($data->pages_category->description) ?>,
        "url": <?= json_encode(SITE_URL . ($data->pages_category->language ? \Altum\Language::$active_languages[$data->pages_category->language] . '/' : null) . 'pages/' . $data->pages_category->url) ?>,
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": <?= json_encode(SITE_URL . ($data->pages_category->language ? \Altum\Language::$active_languages[$data->pages_category->language] . '/' : null) . 'pages/' . $data->pages_category->url) ?>
    },
    "isPartOf": {
        "@type": "CollectionPage",
        "name": <?= json_encode(l('pages.title')) ?>,
            "url": <?= json_encode(url('pages')) ?>
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
    }
}
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
