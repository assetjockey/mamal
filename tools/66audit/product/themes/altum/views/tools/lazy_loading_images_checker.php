<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li><a href="<?= url('tools') ?>"><?= l('tools.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li class="active" aria-current="page"><?= l('tools.lazy_loading_images_checker.name') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-center mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><?= l('tools.lazy_loading_images_checker.name') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('tools.lazy_loading_images_checker.description') ?>">
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
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <tbody>

                    <tr>
                        <td class="font-weight-bold">
                            <?= l('global.url') ?>
                        </td>
                        <td class="text-nowrap">
                            <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain(parse_url($data->values['url'], PHP_URL_HOST)) ?>" class="img-fluid icon-favicon mr-1" /> <?= remove_url_protocol_from_url($data->values['url']) ?>
                        </td>
                    </tr>

                    <?php if (!empty($data->result['images'])): ?>
                        <tr>
                            <td class="font-weight-bold">
                                <?= l('tools.result') ?>
                            </td>
                            <td class="text-nowrap">
                                <?php if($data->result['non_lazy_loading_images_count']): ?>
                                    <i class="fas fa-fw fa-sm fa-exclamation-circle text-warning mr-1"></i>
                                <?php else: ?>
                                    <i class="fas fa-fw fa-sm fa-check-circle text-success mr-1"></i>
                                <?php endif ?>

                                <?= sprintf(l('tools.lazy_loading_images_checker.result.non_lazy_loading_images_count_x'), nr($data->result['non_lazy_loading_images_count'])) ?>
                            </td>
                        </tr>

                        <tr>
                            <td class="font-weight-bold">

                            </td>
                            <td class="text-nowrap">
                                <?php if($data->result['lazy_loading_images_count']): ?>
                                    <i class="fas fa-fw fa-sm fa-check-circle text-success mr-1"></i>
                                <?php else: ?>
                                    <i class="fas fa-fw fa-sm fa-exclamation-circle text-warning mr-1"></i>
                                <?php endif ?>

                                <?= sprintf(l('tools.lazy_loading_images_checker.result.lazy_loading_images_count_x'), nr($data->result['lazy_loading_images_count'])) ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td class="font-weight-bold">
                                <?= l('tools.result') ?>
                            </td>
                            <td class="text-nowrap">
                                <?= l('tools.lazy_loading_images_checker.result.none') ?>
                            </td>
                        </tr>
                    <?php endif ?>

                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($data->result['images'])): ?>
            <div class="mt-4">
                <div class="table-responsive table-custom-container">
                    <table class="table table-custom">
                        <tbody>

                        <tr>
                            <td class="font-weight-bold">
                                <?= l('tools.lazy_loading_images_checker.result.non_lazy_loading_images_count') ?>
                            </td>
                            <td class="text-nowrap">
                                <span class="badge badge-light"><?= nr($data->result['non_lazy_loading_images_count']) ?></span>
                            </td>
                        </tr>

                        <?php foreach($data->result['images'] as $image): ?>
                            <?php if($image->loading == 'lazy') continue ?>

                            <tr>
                                <td class="text-nowrap">
                                    <div class="mb-2">
                                        <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain(parse_url($image->src, PHP_URL_HOST)) ?>" class="img-fluid icon-favicon-small mr-1" loading="lazy" />
                                        <a href="<?= e($image->src) ?>" target="_blank" rel="nofollow noreferrer" class="text-truncate"><?= e(string_truncate($image->src, 64)) ?></a>
                                    </div>

                                    <div>
                                        <code data-toggle="tooltip" title="<?= e($image->html) ?>"><?= e(string_truncate($image->html, 64)) ?></code>
                                    </div>
                                </td>

                                <td class="text-nowrap"></td>
                            </tr>

                        <?php endforeach ?>

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                <div class="table-responsive table-custom-container">
                    <table class="table table-custom">
                        <tbody>

                        <tr>
                            <td class="font-weight-bold">
                                <?= l('tools.lazy_loading_images_checker.result.lazy_loading_images_count') ?>
                            </td>
                            <td class="text-nowrap">
                                <span class="badge badge-light"><?= nr($data->result['lazy_loading_images_count']) ?></span>
                            </td>
                        </tr>

                        <?php foreach($data->result['images'] as $image): ?>
                            <?php if($image->loading != 'lazy') continue ?>

                            <tr>
                                <td class="text-nowrap">
                                    <div class="mb-2">
                                        <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain(parse_url($image->src, PHP_URL_HOST)) ?>" class="img-fluid icon-favicon-small mr-1" loading="lazy" />
                                        <a href="<?= e($image->src) ?>" target="_blank" rel="nofollow noreferrer" class="text-truncate"><?= e(string_truncate($image->src, 64)) ?></a>
                                    </div>

                                    <div>
                                        <code data-toggle="tooltip" title="<?= e($image->html) ?>"><?= e(string_truncate($image->html, 64)) ?></code>
                                    </div>
                                </td>

                                <td class="text-nowrap"></td>
                            </tr>
                        <?php endforeach ?>

                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif ?>

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

