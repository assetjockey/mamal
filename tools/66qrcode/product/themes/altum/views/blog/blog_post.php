<?php defined('ALTUMCODE') || die() ?>

<div class="container <?= settings()->content->blog_columns == 1 ? 'col-lg-8' : null ?>">
    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li><a href="<?= url() ?>"><?= l('index.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li><a href="<?= url('blog') ?>"><?= l('blog.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <?php if($data->blog_posts_category): ?>
                    <li><a href="<?= url('blog/category/' . $data->blog_posts_category->url) ?>"><?= $data->blog_posts_category->title ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <?php endif ?>
                <li class="active" aria-current="page"><?= $data->blog_post->title ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="row">
        <div class="<?= settings()->content->blog_columns == 1 ? 'col-12 mb-5' : 'col-12 col-lg-8 mb-lg-0' ?>">
            <div class="card">
                <div class="card-body">
                    <h1 class="h3 mb-2"><?= $data->blog_post->title ?></h1>

                    <p class="small text-muted mb-4">
                        <span data-toggle="tooltip" title="<?= sprintf(l('global.last_datetime_tooltip'), \Altum\Date::get($data->blog_post->last_datetime, 2)) ?>">
                            <i class="fas fa-fw fa-xs fa-calendar-alt mr-1"></i> <?= \Altum\Date::get($data->blog_post->datetime, 2) ?>
                        </span>

                        <?php if($data->blog_posts_category): ?>
                            • <a href="<?= SITE_URL . ($data->blog_posts_category->language ? \Altum\Language::$active_languages[$data->blog_posts_category->language] . '/' : null) . 'blog/category/' . $data->blog_posts_category->url ?>" class="text-muted"><?= $data->blog_posts_category->title ?></a>
                        <?php endif ?>

                        <?php if(settings()->content->blog_views_is_enabled): ?>
                            <span> • <?= sprintf(l('blog.total_views'), nr($data->blog_post->total_views)) ?></span>
                        <?php endif ?>

                        <?php $estimated_reading_time = string_estimate_reading_time($data->blog_post->content) ?>
                        <?php if($estimated_reading_time->minutes > 0 || $estimated_reading_time->seconds > 0): ?>
                            <span>•
                                <?= $estimated_reading_time->minutes ? sprintf(l('blog.estimated_reading_time'), $estimated_reading_time->minutes . ' ' . l('global.date.minutes')) : null ?>
                                <?= $estimated_reading_time->minutes == 0 && $estimated_reading_time->seconds ? sprintf(l('blog.estimated_reading_time'), $estimated_reading_time->seconds . ' ' . l('global.date.seconds')) : null ?>
                            </span>
                        <?php endif ?>
                    </p>

                    <?php if($data->blog_post->image): ?>
                        <img src="<?= \Altum\Uploads::get_full_url('blog') . $data->blog_post->image ?>" class="blog-post-image img-fluid w-100 rounded" alt="<?= $data->blog_post->image_description ?>" />
                    <?php endif ?>


                    <div class="content-wrapper mt-4">
                        <p class="content-description"><?= $data->blog_post->description ?></p>

                        <?php if(isset($data->blog_post->table_of_contents) && count($data->blog_post->table_of_contents)): ?>
                            <div class="my-4 p-4 border rounded-2x bg-gray-100 border-0">
                                <h2 class="h6 mb-3"><?= l('blog.table_of_contents') ?></h2>

                                <ul class="list-style-none m-0">
                                    <?php foreach($data->blog_post->table_of_contents as $table_of_contents_item): ?>
                                        <?php /* Indent nested headings */ ?>
                                        <?php $table_of_contents_item_padding_left = $table_of_contents_item['level'] > 2 ? ($table_of_contents_item['level'] - 2) * 1.25 : 0 ?>
                                        <li class="mb-2 font-size-small" style="padding-left: <?= $table_of_contents_item_padding_left ?>rem;">
                                            <a href="<?= url(\Altum\Router::$original_request) . '#' . $table_of_contents_item['id'] ?>"><?= $table_of_contents_item['title'] ?></a>
                                        </li>
                                    <?php endforeach ?>
                                </ul>
                            </div>
                        <?php endif ?>

                        <div class="content-body <?= $data->blog_post->editor == 'wysiwyg' ? 'ql-content' : null ?>">
                            <?= $data->blog_post->content ?>
                        </div>
                    </div>

                    <?= include_view(THEME_PATH . 'views/blog/ratings.php', [
                            'blog_post' => $data->blog_post,
                    ]); ?>

                    <?php /* Blog author */ ?>
                    <?php if(isset($data->blog_post->author) && $data->blog_post->author): ?>
                        <div class="p-3 bg-gray-100 rounded mt-4">
                            <?php $blog_author_has_url = isset($data->blog_post->author->url) && $data->blog_post->author->url ?>

                            <?php if($blog_author_has_url): ?>
                                <?php $blog_author_rel = isset($data->blog_post->author->url_nofollow) && $data->blog_post->author->url_nofollow ? 'nofollow noopener noreferrer' : 'noopener noreferrer' ?>
                                <a href="<?= $data->blog_post->author->url ?>" target="_blank" rel="<?= $blog_author_rel ?>" class="d-block text-reset text-decoration-none">
                            <?php else: ?>
                                <div>
                            <?php endif ?>

                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 mr-3">
                                        <?php if(isset($data->blog_post->author->avatar) && $data->blog_post->author->avatar): ?>
                                            <img src="<?= $data->blog_post->author->avatar ?>" class="rounded-circle" alt="<?= $data->blog_post->author->name ?>" loading="lazy" style="width: 4rem; height: 4rem; object-fit: cover;" />
                                        <?php else: ?>
                                            <div class="rounded-circle bg-gray-200 d-flex align-items-center justify-content-center" style="width: 4rem; height: 4rem;">
                                                <i class="fas fa-fw fa-user text-muted"></i>
                                            </div>
                                        <?php endif ?>
                                    </div>

                                    <div class="min-width-0">
                                        <div class="h6 mb-1"><?= $data->blog_post->author->name ?></div>

                                        <?php if(isset($data->blog_post->author->description) && $data->blog_post->author->description): ?>
                                            <div class="small text-muted"><?= $data->blog_post->author->description ?></div>
                                        <?php endif ?>
                                    </div>
                                </div>

                            <?php if($blog_author_has_url): ?>
                                </a>
                            <?php else: ?>
                                </div>
                            <?php endif ?>
                        </div>
                    <?php endif ?>
                </div>
            </div>

            <?php if(settings()->content->blog_share_is_enabled): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center flex-wrap gap-3">
                            <?= include_view(THEME_PATH . 'views/partials/share_buttons.php', ['url' => url(\Altum\Router::$original_request), 'class' => 'btn btn-gray-100', 'copy_to_clipboard' => true]) ?>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php /* Related posts */ ?>
            <?php if(settings()->content->blog_related_posts_is_enabled && count($data->blog_posts_related)): ?>
                <div class="mt-4">
                    <!-- <h2 class="h4 mb-4"><?= l('blog.related') ?></h2>-->

                    <div class="row">
                        <?php foreach($data->blog_posts_related as $blog_post_related): ?>
                            <div class="col-12 col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <?php if($blog_post_related->image): ?>
                                            <a href="<?= SITE_URL . ($blog_post_related->language ? \Altum\Language::$active_languages[$blog_post_related->language] . '/' : null) . 'blog/' . $blog_post_related->url ?>">
                                                <img src="<?= \Altum\Uploads::get_full_url('blog') . $blog_post_related->image ?>" class="blog-post-image-small img-fluid w-100 rounded mb-3" alt="<?= $blog_post_related->image_description ?>" loading="lazy" />
                                            </a>
                                        <?php endif ?>

                                        <a href="<?= SITE_URL . ($blog_post_related->language ? \Altum\Language::$active_languages[$blog_post_related->language] . '/' : null) . 'blog/' . $blog_post_related->url ?>" class="text-decoration-none">
                                            <h3 class="h6 mb-2"><?= $blog_post_related->title ?></h3>
                                        </a>

                                        <div class="small text-muted mb-3">
                                            <i class="fas fa-fw fa-xs fa-calendar-alt mr-1"></i> <?= \Altum\Date::get($blog_post_related->datetime, 2) ?>
                                        </div>

                                        <?php if($blog_post_related->description): ?>
                                            <p class="small text-muted mb-0"><?= string_truncate($blog_post_related->description, 140) ?></p>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
            <?php endif ?>
        </div>

        <?php if(settings()->content->blog_popular_widget_is_enabled || settings()->content->blog_latest_widget_is_enabled || settings()->content->blog_categories_widget_is_enabled || settings()->content->blog_search_widget_is_enabled): ?>
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

                <?php /* Latest posts */ ?>
                <?php if(settings()->content->blog_latest_widget_is_enabled && count($data->blog_posts_latest)): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="h6 mb-3"><?= l('blog.latest') ?></h3>

                            <ul class="list-style-none m-0">
                                <?php foreach($data->blog_posts_latest as $blog_post): ?>
                                    <li class="d-flex align-items-start mb-3">
                                        <div class="flex-grow-1 min-width-0 mr-3">
                                            <a href="<?= SITE_URL . ($blog_post->language ? \Altum\Language::$active_languages[$blog_post->language] . "/" : null) . "blog/" . $blog_post->url ?>" class="font-size-small font-weight-500 d-block">
                                                <?= $blog_post->title ?>
                                            </a>

                                            <div class="small">
                                                <?php if($blog_post->blog_posts_category_id && isset($data->blog_posts_categories[$blog_post->blog_posts_category_id])): ?>
                                                    <a href="<?= SITE_URL . ($data->blog_posts_categories[$blog_post->blog_posts_category_id]->language ? \Altum\Language::$active_languages[$data->blog_posts_categories[$blog_post->blog_posts_category_id]->language] . "/" : null) . "blog/category/" . $data->blog_posts_categories[$blog_post->blog_posts_category_id]->url ?>" class="text-muted">
                                                        <?= $data->blog_posts_categories[$blog_post->blog_posts_category_id]->title ?>
                                                    </a>

                                                    <span class="text-muted"> • </span>
                                                <?php endif ?>

                                                <span class="text-muted"><?= \Altum\Date::get($blog_post->datetime, 2) ?></span>
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
                },
                {
                    "@type": "ListItem",
                    "position": 3,
                    "name": <?= json_encode($data->blog_post->title) ?>,
                    "item": <?= json_encode(SITE_URL . ($data->blog_post->language ? \Altum\Language::$active_languages[$data->blog_post->language] . '/' : null) . 'blog/' . $data->blog_post->url) ?>
                }
            ]
        }
</script>

<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": <?= json_encode($data->blog_post->title) ?>,
        "description": <?= json_encode($data->blog_post->description) ?>,
        "url": <?= json_encode(SITE_URL . ($data->blog_post->language ? \Altum\Language::$active_languages[$data->blog_post->language] . '/' : null) . 'blog/' . $data->blog_post->url) ?>,
        <?php if($data->blog_post->image): ?>
        "image": <?= json_encode(\Altum\Uploads::get_full_url('blog') . $data->blog_post->image) ?>,
        <?php endif ?>

    <?php if(isset($data->blog_post->author) && $data->blog_post->author): ?>
    "author": {
        "@type": "Person",
        "name": <?= json_encode($data->blog_post->author->name) ?>
        <?php if(isset($data->blog_post->author->url) && $data->blog_post->author->url): ?>,
        "url": <?= json_encode($data->blog_post->author->url) ?>
        <?php endif ?>
        <?php if(isset($data->blog_post->author->avatar) && $data->blog_post->author->avatar): ?>,
        "image": <?= json_encode($data->blog_post->author->avatar) ?>
        <?php endif ?>
        <?php if(isset($data->blog_post->author->description) && $data->blog_post->author->description): ?>,
        "description": <?= json_encode($data->blog_post->author->description) ?>
        <?php endif ?>
    },
    <?php else: ?>
    "author": {
        "@type": "Organization",
        "name": <?= json_encode(settings()->main->title) ?>,
        "url": <?= json_encode(SITE_URL) ?>
    },
    <?php endif ?>

    "publisher": {
        "@type": "Organization",
        "name": <?= json_encode(settings()->main->title) ?>
        <?php if(settings()->main->{'logo_' . \Altum\ThemeStyle::get()} != ''): ?>,
        "logo": {
            "@type": "ImageObject",
            "url": <?= json_encode(settings()->main->{'logo_' . \Altum\ThemeStyle::get() . '_full_url'}) ?>
        }
        <?php endif ?>
    },
    "datePublished": <?= json_encode((new \DateTime($data->blog_post->datetime))->format('Y-m-d\TH:i:sP')) ?>,
    <?php /* Last modification date */ ?>
    <?php if($data->blog_post->last_datetime): ?>
    "dateModified": <?= json_encode((new \DateTime($data->blog_post->last_datetime))->format('Y-m-d\TH:i:sP')) ?>,
    <?php endif ?>
    "keywords": <?= json_encode($data->blog_post->keywords) ?>,
    "wordCount": <?= (int) str_word_count(strip_tags($data->blog_post->content ?? '')) ?>,
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": <?= json_encode(SITE_URL . ($data->blog_post->language ? \Altum\Language::$active_languages[$data->blog_post->language] . '/' : null) . 'blog/' . $data->blog_post->url) ?>
    }
}
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php /* Content stylesheet */ ?>
<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/content.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head', 'content_css') ?>
