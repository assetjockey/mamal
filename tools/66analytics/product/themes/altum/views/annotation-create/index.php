<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('annotations') ?>"><?= l('annotations.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('annotation_create.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 text-truncate mb-4"><i class="fas fa-fw fa-xs fa-comments mr-1"></i> <?= l('annotation_create.header') ?></h1>

    <div class="card">
        <div class="card-body">

            <form id="annotation_create" action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-group">
                    <label for="chart_datetime"><i class="fas fa-fw fa-sm fa-calendar text-muted mr-1"></i> <?= l('annotations.chart_datetime') ?></label>
                    <input
                            id="chart_datetime"
                            type="text"
                            class="form-control"
                            name="chart_datetime"
                            value="<?= \Altum\Date::get($data->values['chart_datetime'], 1) ?>"
                            autocomplete="off"
                            data-daterangepicker
                    />
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.create') ?></button>
            </form>

        </div>
    </div>
</div>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/libraries/daterangepicker.min.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/daterangepicker.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment-timezone-with-data-10-year-range.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    'use strict';

    /* Daterangepicker */
    let locale = <?= json_encode(require APP_PATH . 'includes/daterangepicker_translations.php') ?>;
    $('[data-daterangepicker]').daterangepicker({
        minDate: "<?= (new \DateTime($this->website->datetime, new \DateTimeZone(\Altum\Date::$default_timezone)))->setTimezone(new \DateTimeZone($this->user->timezone))->format('Y-m-d H:i:s'); ?>",
        maxDate: new Date(),
        alwaysShowCalendars: true,
        singleCalendar: true,
        singleDatePicker: true,
        locale: {...locale, format: 'YYYY-MM-DD HH:mm:ss'},
        timePicker: true,
        timePicker24Hour: true,
        timePickerSeconds: true,
    }, (start, end, label) => {});
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
