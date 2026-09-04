<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="annotation_update_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-pen text-dark mr-2"></i>
                        <?= l('annotation_update.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div id="annotation_update_modal_list" class="d-none mb-4"></div>

                <form name="annotation_update" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="annotation_id" value="" />

                    <div class="notification-container"></div>

                    <div class="form-group">
                        <label for="annotation_update_name"><i class="fas fa-fw fa-sm fa-signature text-gray-700 mr-1"></i> <?= l('global.name') ?></label>
                        <input id="annotation_update_name" type="text" class="form-control" name="name" maxlength="64" required="required" />
                    </div>

                    <div class="form-group">
                        <label for="annotation_update_chart_datetime"><i class="fas fa-fw fa-sm fa-calendar text-gray-700 mr-1"></i> <?= l('annotations.chart_datetime') ?></label>
                        <input id="annotation_update_chart_datetime" type="text" class="form-control" name="chart_datetime" autocomplete="off" required="required" />
                    </div>

                    <div class="mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-primary" data-is-ajax><?= l('global.update') ?></button>
                    </div>
                </form>

                <form name="annotation_delete" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="annotation_id" value="" />

                    <div class="mt-3">
                        <button type="submit" name="submit" class="btn btn-block btn-outline-danger" data-is-ajax><?= l('global.delete') ?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    let annotation_update_select = annotation => {
        let modal = document.querySelector('#annotation_update_modal');
        let update_form = modal.querySelector('form[name="annotation_update"]');
        let delete_form = modal.querySelector('form[name="annotation_delete"]');
        let input = update_form.querySelector('input[name="chart_datetime"]');

        update_form.querySelector('input[name="annotation_id"]').value = annotation.annotation_id;
        delete_form.querySelector('input[name="annotation_id"]').value = annotation.annotation_id;
        update_form.querySelector('input[name="name"]').value = annotation.name;
        input.value = annotation.chart_datetime;

        annotation_daterangepicker_initiate(input);
        $(input).data('daterangepicker').setStartDate(annotation.chart_datetime);
        $(input).data('daterangepicker').setEndDate(annotation.chart_datetime);

        modal.querySelectorAll('[data-annotation-update-select]').forEach(button => {
            button.classList.toggle('btn-primary', button.getAttribute('data-annotation-id') == annotation.annotation_id);
            button.classList.toggle('btn-light', button.getAttribute('data-annotation-id') != annotation.annotation_id);
        });
    };

    let annotation_update_open = annotations => {
        let modal = document.querySelector('#annotation_update_modal');
        let list = modal.querySelector('#annotation_update_modal_list');

        modal.querySelector('.notification-container').innerHTML = '';
        list.innerHTML = '';

        if(annotations.length > 1) {
            list.classList.remove('d-none');

            annotations.forEach(annotation => {
                let button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-sm btn-light btn-block text-left text-truncate';
                button.setAttribute('data-annotation-update-select', '');
                button.setAttribute('data-annotation-id', annotation.annotation_id);
                button.textContent = annotation.name;
                button.addEventListener('click', () => annotation_update_select(annotation));

                list.appendChild(button);
            });
        } else {
            list.classList.add('d-none');
        }

        annotation_update_select(annotations[0]);

        $('#annotation_update_modal').modal('show');
    };

    $('form[name="annotation_update"],form[name="annotation_delete"]').on('submit', event => {
        let type = event.currentTarget.getAttribute('name').replace('annotation_', '');
        let notification_container = document.querySelector('form[name="annotation_update"] .notification-container');
        notification_container.innerHTML = '';
        pause_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

        $.ajax({
            type: 'POST',
            url: `${url}annotations-ajax/${type}`,
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
