<?php defined('ALTUMCODE') || die() ?>

<div class="container <?= settings()->content->blog_columns == 1 ? 'col-lg-8' : null ?>">
    <?php if(settings()->main->breadcrumbs_is_enabled && !empty($_GET['search'])): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li><a href="<?= url() ?>"><?= l('index.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <?php if(!empty($_GET['search'])): ?>
                    <li><a href="<?= url('blog') ?>"><?= l('blog.breadcrumb') ?></a></li>
                <?php else: ?>
                    <li class="active" aria-current="page"><?= l('blog.breadcrumb') ?></li>
                <?php endif ?>
            </ol>
        </nav>
    <?php endif ?>

    <?php if(!empty($_GET['search'])): ?>
        <h1 class="h3 m-0"><?= sprintf(l('blog.header_search'), input_clean($_GET['search'])) ?></h1>
    <?php else: ?>
        <div class="text-center">

            <h1 class="h1 font-weight-700"><?= l('blog.header') ?></h1>

            <p class="text-muted font-size-little-small"><?= l('blog.subheader') ?></p>

            <a href="<?= SITE_URL . 'blog/feed' ?>" class="font-size-smaller text-decoration-none font-weight-600" style="color: var(--orange)" target="_blank">
                <i class="fas fa-fw fa-rss fa-sm mr-1"></i> <?= l('blog.rss') ?>
            </a>

            <div class="mb-4">&nbsp;</div>
        </div>
    <?php endif ?>

    <div class="row mt-4">
        <div class="<?= settings()->content->blog_columns == 1 ? 'col-12 mb-5' : 'col-12 col-lg-8 mb-lg-0' ?>">
            <?php if (!empty($data->blog_posts)): ?>
                <?php foreach($data->blog_posts as $blog_post): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <a href="<?= SITE_URL . ($blog_post->language ? \Altum\Language::$active_languages[$blog_post->language] . '/' : null) . 'blog/' . $blog_post->url ?>" class="text-decoration-none">
                                <h2 class="h4 mb-2"><?= $blog_post->title ?></h2>
                            </a>

                            <p class="small text-muted mb-4">
                                <span data-toggle="tooltip" title="<?= sprintf(l('global.last_datetime_tooltip'), \Altum\Date::get($blog_post->last_datetime, 2)) ?>">
                                    <i class="fas fa-fw fa-xs fa-calendar-alt mr-1"></i> <?= \Altum\Date::get($blog_post->datetime, 2) ?>
                                </span>

                                <?php if($blog_post->blog_posts_category_id && isset($data->blog_posts_categories[$blog_post->blog_posts_category_id])): ?>
                                    • <a href="<?= SITE_URL . ($data->blog_posts_categories[$blog_post->blog_posts_category_id]->language ? \Altum\Language::$active_languages[$data->blog_posts_categories[$blog_post->blog_posts_category_id]->language] . '/' : null) . 'blog/category/' . $data->blog_posts_categories[$blog_post->blog_posts_category_id]->url ?>" class="text-muted"><?= $data->blog_posts_categories[$blog_post->blog_posts_category_id]->title ?></a>
                                <?php endif ?>

                                <?php if(settings()->content->blog_views_is_enabled): ?>
                                    <span> • <?= sprintf(l('blog.total_views'), nr($blog_post->total_views)) ?></span>
                                <?php endif ?>
                            </p>

                            <?php if($blog_post->image): ?>
                                <a href="<?= SITE_URL . ($blog_post->language ? \Altum\Language::$active_languages[$blog_post->language] . '/' : null) . 'blog/' . $blog_post->url ?>">
                                    <img src="<?= \Altum\Uploads::get_full_url('blog') . $blog_post->image ?>" class="blog-post-image img-fluid w-100 rounded" alt="<?= $blog_post->image_description ?>" />
                                </a>
                            <?php endif ?>

                            <?php if($blog_post->description): ?>
                                <p class="m-0 mt-3"><?= $blog_post->description ?></p>
                            <?php endif ?>
                        </div>
                    </div>
                <?php endforeach ?>

                <div class="mt-3"><?= $data->pagination ?></div>
            <?php else: ?>
                <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                        'filters_get' => $data->filters->get ?? [],
                        'name' => 'blog',
                        'has_secondary_text' => true,
                ]); ?>
            <?php endif ?>
        </div>

        <?php if(settings()->content->blog_popular_widget_is_enabled || settings()->content->blog_categories_widget_is_enabled || settings()->content->blog_search_widget_is_enabled): ?>
            <div class="<?= settings()->content->blog_columns == 1 ? 'col-12' : 'col-12 col-lg-4' ?>">
                <?php if(settings()->content->blog_search_widget_is_enabled): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <form action="<?= url('blog') ?>" method="get" role="form">
                                <input type="hidden" name="search_by" value="title" />

                                <div class="input-group">
                                    <input type="search" name="search" class="form-control" value="<?= !empty($_GET['search']) ? input_clean($_GET['search']) : null ?>" placeholder="<?= l('global.search') ?>" aria-label="<?= l('global.search') ?>" />

                                    <div class="input-group-append">
                                        <button class="btn btn-outline-gray-200 text-dark" type="submit" data-toggle="tooltip" title="<?= l('global.submit') ?>"><i class="fas fa-fw fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif ?>

                <?php if(settings()->content->blog_categories_widget_is_enabled && count($data->blog_posts_categories)): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="h6 mb-3"><?= l('blog.categories') ?></h3>

                            <ul class="list-style-none m-0">
                                <?php foreach($data->blog_posts_categories as $blog_post_category): ?>
                                    <li class="mb-2 font-size-little-small">
                                        <a href="<?= SITE_URL . ($blog_post_category->language ? \Altum\Language::$active_languages[$blog_post_category->language] . '/' : null) . 'blog/category/' . $blog_post_category->url ?>"><?= $blog_post_category->title ?></a>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    </div>
                <?php endif ?>

                <?php if(settings()->content->blog_popular_widget_is_enabled && count($data->blog_posts_popular)): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="h6 mb-3"><?= l('blog.popular') ?></h3>

                            <ul class="list-style-none m-0">
                                <?php $i = 1; ?>
                                <?php foreach($data->blog_posts_popular as $blog_post): ?>
                                    <li class="d-flex align-items-start mb-3">
                                        <div class="text-gray-300 flex-shrink-0 mr-3" style="font-size: 1.5rem; font-weight: 800; line-height: normal;">
                                            <?= $i++ ?>
                                        </div>

                                        <div class="flex-grow-1 min-width-0 mr-3">
                                            <a href="<?= SITE_URL . ($blog_post->language ? \Altum\Language::$active_languages[$blog_post->language] . "/" : null) . "blog/" . $blog_post->url ?>" class="font-size-small font-weight-500 d-block">
                                                <?= $blog_post->title ?>
                                            </a>

                                            <div class="small">
                                                <?php if($blog_post->blog_posts_category_id && isset($data->blog_posts_categories[$blog_post->blog_posts_category_id])): ?>
                                                    <a href="<?= SITE_URL . ($data->blog_posts_categories[$blog_post->blog_posts_category_id]->language ? \Altum\Language::$active_languages[$data->blog_posts_categories[$blog_post->blog_posts_category_id]->language] . "/" : null) . "blog/category/" . $data->blog_posts_categories[$blog_post->blog_posts_category_id]->url ?>" class="text-muted">
                                                        <?= $data->blog_posts_categories[$blog_post->blog_posts_category_id]->title ?>
                                                    </a>

                                                    <?php if(settings()->content->blog_views_is_enabled): ?>
                                                        <span class="text-muted"> • </span>
                                                    <?php endif ?>
                                                <?php endif ?>

                                                <?php if(settings()->content->blog_views_is_enabled): ?>
                                                    <span class="text-muted"><?= sprintf(l("blog.total_views"), nr($blog_post->total_views)) ?></span>
                                                <?php endif ?>
                                            </div>
                                        </div>

                                        <div class="flex-shrink-0">
                                            <?php if($blog_post->image): ?>
                                                <img src="<?= \Altum\Uploads::get_full_url('blog') . $blog_post->image ?>" class="blog-post-image-popular rounded" alt="<?= $blog_post->image_description ?>" loading="lazy" />
                                            <?php else: ?>
                                                <div class="blog-post-image-popular"></div>
                                            <?php endif ?>
                                        </div>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    </div>
                <?php endif ?>
            </div>
        <?php endif ?>
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
                    "name": <?= json_encode(l('blog.title')) ?>,
                    "item": <?= json_encode(url('blog')) ?>
                }
            ]
        }
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
