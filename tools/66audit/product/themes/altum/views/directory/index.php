<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12 col-lg mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 text-truncate"><i class="fas fa-fw fa-xs fa-sitemap mr-1"></i> <?= l('top_websites.header') ?></h1>
            <p class="mb-0 small text-muted"><?= sprintf(l('top_websites.subheader'), '<strong>' . $data->total_websites . '</strong>') ?></p>

        </div>

        <div class="col-12 col-lg-auto d-flex flex-wrap gap-3 d-print-none">
            <div>
                <div class="dropdown">
                    <button type="button" class="btn btn-light dropdown-toggle-simple <?= !empty($data->websites) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('top-websites?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('top-websites?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-code mr-2"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                        </a>
                        <a href="#" class="dropdown-item <?= $this->user->plan_settings->export->pdf ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->pdf ? 'onclick="event.preventDefault(); window.print();"' : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-pdf mr-2"></i> <?= sprintf(l('global.export_to'), 'PDF') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div>
                <div class="dropdown">
                    <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= !empty($data->websites) || $data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip data-html="true" title="<?= l('global.filters.tooltip') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-filter"></i>
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
                                    <option value="host" <?= $data->filters->order_by == 'host' ? 'selected="selected"' : null ?>><?= l('global.host') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_order_by" class="small"><?= l('global.filters.order_by') ?></label>
                                <select name="order_by" id="filters_order_by" class="custom-select custom-select-sm">
                                    <option value="score" <?= $data->filters->order_by == 'score' ? 'selected="selected"' : null ?>><?= l('audits.score') ?></option>
                                    <option value="host" <?= $data->filters->order_by == 'host' ? 'selected="selected"' : null ?>><?= l('global.url') ?></option>
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
        </div>
    </div>

    <div>
        <?php if(!empty($data->websites)): ?>
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th><?= l('global.name') ?></th>
                        <th><?= l('audits.score') ?></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

					<?php foreach($data->websites as $row): ?>

                        <tr>
                            <td class="text-nowrap">
                                <div>
                                    <?php if($row->settings->is_public && empty($row->settings->password)): ?>
                                    <a href="<?= url('website/' . $row->website_id) ?>">
                                        <?= $row->host ?>
                                    </a>
                                    <?php else: ?>
                                        <?= $row->host ?>
                                    <?php endif ?>
                                </div>

                                <div class="small">
                                    <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($row->host) ?>" class="img-fluid icon-favicon-small mr-1" loading="lazy" />

                                    <span class="text-muted"><?= $row->host ?></span>

                                    <a href="<?= $row->scheme . $row->host ?>" target="_blank" rel="noreferrer"><i class="fas fa-fw fa-xs fa-external-link-alt text-muted ml-1"></i></a>
                                </div>
                            </td>


                            <td class="text-nowrap text-muted">
                                <span class="font-weight-bold small"><?= get_website_score_display($row) ?></span>
                            </td>

                            <td class="text-nowrap text-muted">
                                <div class="d-flex align-items-center">
									<?php $rounded_score = round($row->score / 10); ?>

									<?php for($i = 0; $i <= 9; $i++): ?>
										<?php
										if($i <= $rounded_score - 1) {
											$audit_badge_bg_class_name = match (true) {
												$rounded_score >= 8 => 'success',
												$rounded_score >= 5 => 'warning',
												$rounded_score >= 0 => 'danger',
											};
										} else {
											$audit_badge_bg_class_name = 'gray-200';
										}
										?>

                                        <div class="audit-badge bg-<?= $audit_badge_bg_class_name ?> mr-1"></div>
									<?php endfor ?>
                                </div>
                            </td>


                            <td class="text-nowrap text-muted">
                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('websites.last_audit_datetime') . '<br />' . \Altum\Date::get($row->last_audit_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_audit_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_audit_datetime) . ')</small>' ?>">
                                    <i class="fas fa-fw fa-calendar-check text-muted"></i>
                                </span>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                    <i class="fas fa-fw fa-calendar text-muted"></i>
                                </span>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                    <i class="fas fa-fw fa-history text-muted"></i>
                                </span>
                            </td>
                        </tr>
					<?php endforeach ?>

                    </tbody>
                </table>
            </div>

            <div class="mt-3"><?= $data->pagination ?></div>
        <?php else: ?>

            <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                    'filters_get' => $data->filters->get ?? [],
                    'name' => 'top_websites',
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
                    "name": <?= json_encode(l('top_websites.title')) ?>,
                    "item": <?= json_encode(url('directory')) ?>
                }
            ]
        }
    </script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
