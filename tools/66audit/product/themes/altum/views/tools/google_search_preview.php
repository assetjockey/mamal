<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li><a href="<?= url('tools') ?>"><?= l('tools.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li class="active" aria-current="page"><?= l('tools.google_search_preview.name') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-center mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><?= l('tools.google_search_preview.name') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('tools.google_search_preview.description') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <?= $this->views['ratings'] ?>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group" data-type="website">
                    <label for="url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('global.url') ?></label>
                    <input type="url" id="url" name="url" class="form-control <?= \Altum\Alerts::has_field_errors('url') ? 'is-invalid' : null ?>" value="<?= $data->values['url'] ?>" placeholder="<?= l('global.url_placeholder') ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('url') ?>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('global.submit') ?></button>
            </form>

        </div>
    </div>

    <?php if(isset($data->result)): ?>
        <div class="mt-4">

            <style>
                /* Audit search previews */
                .audit-search-preview-google-wrapper {
                    background: #ffffff;
                }

                [data-theme-style="dark"] .audit-search-preview-google-wrapper {
                    background: #101218;
                }

                .audit-search-preview-google-img-wrapper {
                    background-color: #f1f3f4;
                    border: 1px solid #dadce0;
                    border-radius: 50%;
                    display: inline-flex;
                    justify-content: center;
                    align-items: center;
                    height: 26px;
                    width: 26px;
                    margin-right: 12px;
                    flex-shrink: 0;
                    vertical-align: middle;
                }

                .audit-search-preview-google-img {
                    width: 18px;
                    height: 18px;
                }

                .audit-search-preview-google-host {
                    color: #202124;
                    font-size: 14px;
                }

                [data-theme-style="dark"] .audit-search-preview-google-host {
                    color: #dadce0;
                }

                .audit-search-preview-google-url {
                    color: #4d5156;
                    font-size: 12px;
                }

                [data-theme-style="dark"] .audit-search-preview-google-url {
                    color: #bdc1c6;
                }

                .audit-search-preview-google-title {
                    color: #1800ab;
                    font-family: Arial, sans-serif;
                    font-size: 20px;
                    font-weight: 400;
                    display: inline-block;
                    line-height: 1.3;
                    margin-bottom: 3px;
                    padding-top: 5px;
                }

                [data-theme-style="dark"] .audit-search-preview-google-title {
                    color: #7fa7ff;
                }

                .audit-search-preview-google-description {
                    font-family: Arial, sans-serif;
                    font-size: 14px;
                    font-weight: 400;
                    line-height: 22px;
                    color: #474747;
                }

                [data-theme-style="dark"] .audit-search-preview-google-description {
                    color: #cdcdcd;
                }
            </style>

            <div class="card mb-3">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between">
                        <span class="small font-weight-bold"><?= sprintf(l('audits.search_preview_x'), 'Google') ?></span>

                        <span class="badge bg-primary-50 text-primary-700">
                            <i class="fab fa-fw fa-sm fa-google"></i>
                        </span>
                    </div>
                </div>

                <div class="card-body audit-search-preview-google-wrapper">
                    <div class="d-flex flex-column" style="max-width: 650px;">
                        <div class="d-flex align-items-center">
                            <div class="audit-search-preview-google-img-wrapper">
                                <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($data->result['host']) ?>" class="audit-search-preview-google-img" loading="lazy" />
                            </div>

                            <div class="d-flex flex-column">
                                <div class="audit-search-preview-google-host"><?= $data->result['opengraph']['og:site_name'] ?? $data->result['host'] ?></div>
                                <div class="audit-search-preview-google-url"><?= $data->result['url'] ?></div>
                            </div>
                        </div>

                        <h3 class="audit-search-preview-google-title"><?= $data->result['title'] ? e($data->result['title']) : l('audits.title_missing') ?></h3>

                        <div class="audit-search-preview-google-description">
                            <?= $data->result['meta_description'] ? e($data->result['meta_description']) : l('audits.meta_description_missing') ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    <?php endif ?>

    <?php if(settings()->tools->last_submissions_is_enabled && isset($data->tools_usage[\Altum\Router::$method]) && !empty((array) $data->tools_usage[\Altum\Router::$method]->data)): ?>
        <div class="mt-5">
            <h2 class="small font-weight-bold text-uppercase text-muted mb-3"><i class="fas fa-fw fa-sm fa-plus text-primary mr-1"></i> <?= l('tools.last_submissions') ?></h2>

            <div class="card">
                <div class="card-body">

                    <div class="row">
                        <?php foreach((array) $data->tools_usage[\Altum\Router::$method]->data as $key => $value): ?>
                            <div class="col-12 col-lg-6">
                                <div class="text-truncate my-2">
                                    <a href="<?= url('tools/' . str_replace('_', '-', \Altum\Router::$method) . '?' . http_build_query((array) $value)) ?>" onclick="this.href += '&submit=1&token=<?= \Altum\Csrf::get() ?>'">
                                        <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain(parse_url($value->url, PHP_URL_HOST)) ?>" class="img-fluid icon-favicon mr-1" loading="lazy" />

                                        <?= remove_url_protocol_from_url($value->url) ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>

                </div>
            </div>
        </div>

    <?php endif ?>

    <?php require_once THEME_PATH . 'views/tools/js_dynamic_url_processor.php' ?>

    <?= $this->views['extra_content'] ?>

    <?= $this->views['similar_tools'] ?>

    <?= $this->views['popular_tools'] ?>
</div>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

