<?php defined('ALTUMCODE') || die() ?>

<nav class="p-2 bg-white status-page-navbar d-lg-none">
    <div class="container">
        <div class="d-flex">
            <?php if($data->status_page->logo): ?>
                <a href="<?= $data->status_page->full_url ?>">
                    <img src="<?= \Altum\Uploads::get_full_url('status_pages_logos') . $data->status_page->logo ?>" class="img-fluid status-page-navbar-logo mr-3" alt="<?= $data->status_page->name ?>" loading="lazy" />
                </a>
            <?php endif ?>

            <?php if($data->status_page->settings->display_header_text ?? true): ?>
            <div class="d-flex flex-column min-width-0">
                <div class="text-truncate">
                    <a class="font-weight-bold status-page-title" href="<?= $data->status_page->full_url ?>">
                        <?= $data->status_page->name ?>
                    </a>
                </div>
                <small class="text-truncate text-muted"><?= $data->status_page->description ?></small>
            </div>
            <?php endif ?>
        </div>
    </div>
</nav>

<div class="container mt-5 d-none d-lg-block">
    <div class="d-flex align-items-center position-relative">
        <?php if($data->status_page->logo): ?>
            <div>
                <a href="<?= $data->status_page->full_url ?>">
                    <img src="<?= \Altum\Uploads::get_full_url('status_pages_logos') . $data->status_page->logo ?>" class="img-fluid status-page-logo mr-4" alt="<?= $data->status_page->name ?>" loading="lazy" />
                </a>
            </div>
        <?php endif ?>

        <?php if($data->status_page->settings->display_header_text ?? true): ?>
            <div class="d-flex flex-column">
                <div>
                    <a href="<?= $data->status_page->full_url ?>" class="status-page-title">
                        <h1 class="h2 mb-0"><?= $data->status_page->name ?></h1>
                    </a>
                </div>

                <?php if($data->status_page->description): ?>
                    <span class="status-page-description text-muted">
                    <?= $data->status_page->description ?>
                </span>
                <?php endif ?>
            </div>
        <?php endif ?>
    </div>
</div>
