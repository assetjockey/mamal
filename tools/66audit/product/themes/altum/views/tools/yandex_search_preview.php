<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li><a href="<?= url('tools') ?>"><?= l('tools.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li class="active" aria-current="page"><?= l('tools.yandex_search_preview.name') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-center mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><?= l('tools.yandex_search_preview.name') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('tools.yandex_search_preview.description') ?>">
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
                .audit-search-preview-yandex-wrapper {
                    background: #fbfbfb;
                }

                [data-theme-style="dark"] .audit-search-preview-yandex-wrapper {
                    background: #111112;
                }

                .audit-search-preview-yandex-img {
                    width: 16px;
                    height: 16px;
                    margin-right: 10px;
                    margin-top: 15px;
                }

                .audit-search-preview-yandex-result-wrapper {
                    background: #ffffff;
                    padding: 12px 16px;
                    border-radius: 16px;
                    box-shadow: 0 4px 12px 0 rgba(0, 0, 0, .03);
                }

                [data-theme-style="dark"] .audit-search-preview-yandex-result-wrapper {
                    background: #18181a;
                }

                .audit-search-preview-yandex-url {
                    font-family: 'YS Text', -apple-system, BlinkMacSystemFont, Arial, Helvetica, sans-serif;
                    font-size: 14px;
                    font-weight: bold;
                    line-height: 17px;
                    color: #0b6301;
                }

                [data-theme-style="dark"] .audit-search-preview-yandex-url {
                    color: rgba(255, 255, 255, .87);
                }

                .audit-search-preview-yandex-title {
                    color: #000080;
                    font-family: 'YS Text', -apple-system, BlinkMacSystemFont, Arial, Helvetica, sans-serif;
                    font-size: 18px;
                    line-height: 24px;
                    font-weight: 400;
                    margin: 0;
                }

                [data-theme-style="dark"] .audit-search-preview-yandex-title {
                    color: #8cb3dd;
                }

                .audit-search-preview-yandex-description {
                    margin-top: 2px;
                    font-family: 'YS Text', -apple-system, BlinkMacSystemFont, Arial, Helvetica, sans-serif;
                    font-size: 14px;
                    line-height: 18px;
                    color: #333;
                }

                [data-theme-style="dark"] .audit-search-preview-yandex-description {
                    color: #c5c5c6;
                }
            </style>

            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between">
                        <span class="small font-weight-bold"><?= sprintf(l('audits.search_preview_x'), 'Yandex') ?></span>

                        <span class="badge bg-primary-50 text-primary-700">
                    <i class="fab fa-fw fa-sm fa-yandex"></i>
                </span>
                    </div>
                </div>

                <div class="card-body audit-search-preview-yandex-wrapper">
                    <div class="d-flex" style="max-width: 600px;">
                        <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($data->result['host']) ?>" class="audit-search-preview-yandex-img" loading="lazy" />

                        <div class="d-flex flex-column audit-search-preview-yandex-result-wrapper">
                            <h3 class="audit-search-preview-yandex-title"><?= $data->result['title'] ? e($data->result['title']) : l('audits.title_missing') ?></h3>
                            <div class="audit-search-preview-yandex-url"><?= remove_url_protocol_from_url($data->result['url']) ?></div>
                            <div class="audit-search-preview-yandex-description">
                                <?= $data->result['meta_description'] ? e($data->result['meta_description']) : l('audits.meta_description_missing') ?>
                            </div>
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

