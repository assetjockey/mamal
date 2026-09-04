<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="annotation_create_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-comments text-dark mr-2"></i>
                        <?= l('annotation_create.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form name="annotation_create" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />

                    <div class="notification-container"></div>

                    <div class="form-group">
                        <label for="annotation_create_name"><i class="fas fa-fw fa-sm fa-signature text-gray-700 mr-1"></i> <?= l('global.name') ?></label>
                        <input id="annotation_create_name" type="text" class="form-control" name="name" maxlength="64" required="required" />
                    </div>

                    <div class="form-group">
                        <label for="annotation_create_chart_datetime"><i class="fas fa-fw fa-sm fa-calendar text-gray-700 mr-1"></i> <?= l('annotations.chart_datetime') ?></label>
                        <input id="annotation_create_chart_datetime" type="text" class="form-control" name="chart_datetime" autocomplete="off" required="required" />
                    </div>

                    <div class="mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-primary" data-is-ajax><?= l('global.create') ?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    let annotation_daterangepicker_options = {
        minDate: <?= json_encode((new \DateTime($this->website->datetime, new \DateTimeZone(\Altum\Date::$default_timezone)))->setTimezone(new \DateTimeZone($this->user->timezone))->format('Y-m-d H:i:s')) ?>,
        maxDate: new Date(),
        alwaysShowCalendars: true,
        singleCalendar: true,
        singleDatePicker: true,
        locale: {...<?= json_encode(require APP_PATH . 'includes/daterangepicker_translations.php') ?>, format: 'YYYY-MM-DD HH:mm:ss'},
        timePicker: true,
        timePicker24Hour: true,
        timePickerSeconds: true,
    };

    let annotation_daterangepicker_initiate = input => {
        if(input.getAttribute('data-daterangepicker-initiated')) {
            return;
        }

        /* Daterangepicker */
        $(input).daterangepicker(annotation_daterangepicker_options, (start, end, label) => {});
        input.setAttribute('data-daterangepicker-initiated', 'true');
    };

    let annotation_create_open = chart_datetime => {
        let modal = document.querySelector('#annotation_create_modal');
        let input = modal.querySelector('input[name="chart_datetime"]');

        input.value = chart_datetime;
        modal.querySelector('.notification-container').innerHTML = '';

        annotation_daterangepicker_initiate(input);
        $(input).data('daterangepicker').setStartDate(chart_datetime);
        $(input).data('daterangepicker').setEndDate(chart_datetime);

        $('#annotation_create_modal').modal('show');
    };

    $('#annotation_create_modal').on('show.bs.modal', event => {
        if(!event.relatedTarget) {
            return;
        }

        let chart_datetime = event.relatedTarget.getAttribute('data-chart-datetime') || <?= json_encode(\Altum\Date::get('', 1)) ?>;
        let input = event.currentTarget.querySelector('input[name="chart_datetime"]');

        input.value = chart_datetime;
        event.currentTarget.querySelector('.notification-container').innerHTML = '';

        annotation_daterangepicker_initiate(input);
        $(input).data('daterangepicker').setStartDate(chart_datetime);
        $(input).data('daterangepicker').setEndDate(chart_datetime);
    });

    $('form[name="annotation_create"]').on('submit', event => {
        let notification_container = event.currentTarget.querySelector('.notification-container');
        notification_container.innerHTML = '';
        pause_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

        $.ajax({
            type: 'POST',
            url: `${url}annotations-ajax/create`,
            data: $(event.currentTarget).serialize(),
            dataType: 'json',
            success: (data) => {
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

                if(data.status == 'error') {
                    display_notifications(data.message, 'error', notification_container);
                }

                else if(data.status == 'success') {
                    display_notifications(data.message, 'success', notification_container);

                    setTimeout(() => window.location.href = window.location.href, 1000);
                }
            },
            error: () => {
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));
                display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            },
        });

        event.preventDefault();
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
