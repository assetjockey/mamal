<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('campaigns') ?>"><?= l('campaigns.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('campaigns_import.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 text-truncate mb-4"><i class="fas fa-fw fa-xs fa-pager mr-1"></i> <?= l('campaigns_import.header') ?></h1>

    <div class="card">
        <div class="card-body">
            <form id="form" action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group" data-file-input-wrapper-size-limit="<?= get_max_upload() ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), get_max_upload()) ?>">
                    <label for="file"><i class="fas fa-fw fa-sm fa-file-csv text-muted mr-1"></i> <?= l('global.csv_file') ?></label>
                    <?= include_view(THEME_PATH . 'views/partials/file_input.php', ['uploads_file_key' => 'resources_csv', 'file_key' => 'file', 'already_existing_file' => null, 'is_required' => true]) ?>
                    <?= \Altum\Alerts::output_field_error('file') ?>
                    <small class="form-text text-muted"><?= sprintf(l('global.csv_file_help'),'<code>name</code>, <code>domain</code>', '<code>domain_id</code>, <code>branding_name</code>, <code>branding_url</code>, <code>email_reports</code>, <code>email_reports_is_enabled</code>, <code>is_enabled</code>'); ?></small>
                    <small class="form-text text-muted"><a href="<?= ASSETS_FULL_URL . 'csv/campaigns_example.csv' ?>" download="campaigns_example.csv" target="_blank"><?= l('global.csv_file_help2') ?></a></small>
                    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('resources_csv')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), get_max_upload()) ?></small>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary" data-is-ajax><?= l('global.submit') ?></button>
            </form>
        </div>
    </div>
</div>


<?php ob_start() ?>
<script>
    'use strict';

document.querySelector('#form').addEventListener('submit', event => {
        if(document.querySelector('#form').checkValidity()) {
            pause_submit_button(event.currentTarget);
        }
    });

</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

