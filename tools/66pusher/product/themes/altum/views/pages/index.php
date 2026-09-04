<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <div class="text-center">

        <h1 class="h1 font-weight-700"><?= l('pages.header') ?></h1>

        <p class="text-muted font-size-little-small mb-0"><?= l('pages.subheader') ?></p>

        <div class="mb-4">&nbsp;</div>
    </div>

    <?php if(count($data->pages_categories) || count($data->popular_pages)): ?>

        <?php if (!empty($data->pages_categories)): ?>
            <div class="mt-4">
                <div class="row">
                    <?php foreach($data->pages_categories as $row): ?>
                        <div class="col-12 col-md-6 col-lg-4 p-3">
                            <a href="<?= SITE_URL . ($row->language ? \Altum\Language::$active_languages[$row->language] . '/' : null) . 'pages/' . $row->url ?>" class="text-decoration-none">
                                <div class="card h-100 p-3">
                                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                                        <?php if(!empty($row->icon)): ?>
                                            <span class="rounded-2x bg-primary-50 text-primary page-category-icon-wrapper d-flex align-items-center justify-content-center mb-4"><i class="<?= $row->icon ?> fa-fw fa-2x"></i></span>
                                        <?php endif ?>

                                        <div class="h5"><?= $row->title ?></div>

                                        <?php if($row->description): ?>
                                        <p class="text-muted font-size-small"><?= $row->description ?></p>
                                        <?php endif ?>

                                        <div class="small text-muted mt-3"><i class="fas fa-fw fa-sm fa-file-alt mr-1"></i> <?= sprintf(l('pages.resources'), $row->total_pages) ?></div>
                                    </div>
                                </div>
                            </a>
                        </div>

                    <?php endforeach ?>
                </div>
            </div>
        <?php endif ?>

        <?php if (!empty($data->popular_pages)): ?>
            <div class="mt-4">
                <h2 class="h5 mb-4"><?= l('pages.index.popular_pages') ?></h2>

                <div class="row">
                    <?php foreach($data->popular_pages as $row): ?>

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
        <?php endif ?>

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
                }
            ]
        }
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

