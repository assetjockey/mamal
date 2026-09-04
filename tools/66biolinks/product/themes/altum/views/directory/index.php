<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <div class="text-center mb-4">
        <h1 class="h1 font-weight-700">
            <?= l('directory.header') ?>
        </h1>

        <p class="text-muted font-size-little-small">
            <?= l('directory.subheader') ?>
        </p>

        <div class="d-flex justify-content-center flex-wrap gap-3 mt-4 d-print-none">
            <div class="dropdown">
                <button type="button" class="btn btn-sm btn-light dropdown-toggle-simple <?= !empty($data->links) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport">
                    <i class="fas fa-fw fa-sm fa-download mr-1"></i> <?= l('global.export') ?>
                </button>

                <div class="dropdown-menu dropdown-menu-right d-print-none">
                    <a href="<?= url('directory?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                        <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                    </a>
                    <a href="<?= url('directory?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
                        <i class="fas fa-fw fa-sm fa-file-code mr-2"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                    </a>
                    <a href="#" class="dropdown-item <?= $this->user->plan_settings->export->pdf ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->pdf ? 'onclick="event.preventDefault(); window.print();"' : get_plan_feature_disabled_info() ?>>
                        <i class="fas fa-fw fa-sm fa-file-pdf mr-2"></i> <?= sprintf(l('global.export_to'), 'PDF') ?>
                    </a>
                </div>
            </div>

            <div class="dropdown">
                <button type="button" class="btn btn-sm <?= $data->filters->has_applied_filters ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= !empty($data->links) || $data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip data-html="true" data-tooltip-hide-on-click>
                    <i class="fas fa-fw fa-sm fa-filter mr-1"></i> <?= l('global.filters.header') ?>
                </button>

                <div class="dropdown-menu dropdown-menu-right filters-dropdown">
                    <div class="dropdown-header d-flex justify-content-between">
                        <span class="h6 m-0"><?= l('global.filters.header') ?></span>

                        <?php if($data->filters->has_applied_filters): ?>
                            <a href="<?= url(\Altum\Router::$original_request) ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
                        <?php endif ?>
                    </div>

                    <div class="dropdown-divider"></div>

                    <form action="" method="get" role="form">
                        <div class="form-group px-4">
                            <label for="filters_search" class="small"><?= l('global.filters.search') ?></label>
                            <input type="search" name="search" id="filters_search" class="form-control form-control-sm" value="<?= $data->filters->search ?>" />
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_search_by" class="small"><?= l('global.filters.search_by') ?></label>
                            <select name="search_by" id="filters_search_by" class="custom-select custom-select-sm">
                                <option value="url" <?= $data->filters->order_by == 'url' ? 'selected="selected"' : null ?>><?= l('links.filters.url') ?></option>
                            </select>
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_order_by" class="small"><?= l('global.filters.order_by') ?></label>
                            <select name="order_by" id="filters_order_by" class="custom-select custom-select-sm">
                                <option value="clicks" <?= $data->filters->order_by == 'clicks' ? 'selected="selected"' : null ?>><?= l('links.filters.order_by_clicks') ?></option>
                                <option value="url" <?= $data->filters->order_by == 'url' ? 'selected="selected"' : null ?>><?= l('links.filters.url') ?></option>
                            </select>
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_order_type" class="small"><?= l('global.filters.order_type') ?></label>
                            <select name="order_type" id="filters_order_type" class="custom-select custom-select-sm">
                                <option value="ASC" <?= $data->filters->order_type == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                                <option value="DESC" <?= $data->filters->order_type == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                            </select>
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_results_per_page" class="small"><?= l('global.filters.results_per_page') ?></label>
                            <select name="results_per_page" id="filters_results_per_page" class="custom-select custom-select-sm">
                                <?php foreach($data->filters->allowed_results_per_page as $key): ?>
                                    <option value="<?= $key ?>" <?= $data->filters->results_per_page == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="form-group px-4 mt-4">
                            <button type="submit" name="submit" class="btn btn-sm btn-primary btn-block"><?= l('global.submit') ?></button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <div class="mb-4">&nbsp;</div>
    </div>

    <div>
        <?php if(!empty($data->links)): ?>
            <div class="row">
                <?php foreach($data->links as $row): ?>
                    <div class="col-lg-6 p-3">
                        <div class="custom-row">
                            <div class="row h-100">
                                <div class="col-xl-2 d-flex align-items-center justify-content-center">
                                    <a href="<?= $row->full_url ?>" target="_blank">
                                        <img src="<?= $row->settings->favicon ? \Altum\Uploads::get_full_url('favicons') . $row->settings->favicon : ($row->settings->seo->image ? \Altum\Uploads::get_full_url('block_images') . $row->settings->seo->image : get_gravatar('')) ?>" class="link-directory-avatar rounded-circle" />
                                    </a>
                                </div>

                                <div class="col-8 d-flex align-items-center">
                                    <div class="d-flex flex-column min-width-0">
                                        <div class="d-inline-block text-truncate">
                                            <a href="<?= $row->full_url ?>" target="_blank" class="font-weight-bold text-decoration-none"><?= $row->settings->seo->title ?: $row->url ?></a>

                                            <?php if($row->is_verified): ?>
                                                <span data-toggle="tooltip" title="<?= l('link.biolink.verified') ?>"><i class="fas fa-fw fa-xs fa-check-circle" style="color: #0086ff"></i></span>
                                            <?php endif ?>
                                        </div>

                                        <div class="d-flex align-items-center">
                                            <span class="text-muted text-truncate"><?= $row->settings->seo->meta_description ?? '' ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-4 col-xl-2 d-flex justify-content-end align-items-center">
                                    <div>
                                        <span data-toggle="tooltip" title="<?= l('links.clicks') ?>"><span class="badge badge-light"><i class="fas fa-fw fa-sm fa-chart-bar mr-1"></i> <?= nr($row->clicks) ?></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>

            <div class="mt-3"><?= $data->pagination ?></div>
        <?php else: ?>

            <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                    'filters_get' => $data->filters->get ?? [],
                    'name' => 'directory',
                    'has_secondary_text' => true,
            ]); ?>

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
        "name": <?= json_encode(l('directory.title')) ?>,
                    "item": <?= json_encode(url('directory')) ?>
    }
]
}
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
