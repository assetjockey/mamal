<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="heatmap_create" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-circle-plus text-dark mr-2"></i>
                        <?= l('heatmap_create_modal.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form name="heatmap_create" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />

                    <div class="notification-container"></div>

                    <div id="heatmap_create_name_container" class="form-group">
                        <label><i class="fas fa-fw fa-sm fa-signature text-gray-700 mr-1"></i> <?= l('global.name') ?></label>
                        <input type="text" class="form-control" name="name" required="required" />
                    </div>

                    <div id="heatmap_create_path_container" class="form-group">
                        <label><i class="fas fa-fw fa-sm fa-link text-gray-700 mr-1"></i> <?= l('heatmap_create_modal.path') ?></label>
                        <div class="input-group">
                            <div id="path_prepend" class="input-group-prepend">
                                <span class="input-group-text"><?= $this->website->host . $this->website->path . '/' ?></span>
                            </div>

                            <input type="text" name="path" class="form-control" placeholder="<?= l('heatmap_create_modal.path_placeholder') ?>" />
                        </div>
                        <small class="form-text text-muted"><?= l('heatmap_create_modal.path_help') ?></small>
                    </div>

                    <div class="form-group custom-control custom-switch">
                        <input id="is_bulk" name="is_bulk" type="checkbox" class="custom-control-input">
                        <label class="custom-control-label" for="is_bulk">Bulk generator</label>
                    </div>

                    <div id="bulk_container">
                        <div class="form-group">
                            <label for="paths"><i class="fas fa-fw fa-sm fa-link text-gray-700 mr-1"></i> Heatmap paths</label>
                            <textarea id="paths" name="paths" class="form-control" rows="10" placeholder="/&#10;/<?= l('heatmap_create_modal.path_placeholder') ?>&#10;/contact"></textarea>
                            <small class="form-text text-muted"><?= l('heatmap_create_modal.path_help') ?></small>
                        </div>
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

    /* Bulk generator */
    let heatmap_create_bulk_checker = () => {
        let is_bulk = document.querySelector('#is_bulk').checked;

        if(is_bulk) {
            document.querySelector('form[name="heatmap_create"] input[name="name"]').setAttribute('disabled', 'disabled');
            document.querySelector('form[name="heatmap_create"] input[name="path"]').setAttribute('disabled', 'disabled');
            document.querySelector('#heatmap_create_name_container').classList.add('d-none');
            document.querySelector('#heatmap_create_path_container').classList.add('d-none');
            document.querySelector('#paths').setAttribute('required', 'required');
            document.querySelector('#bulk_container').classList.remove('d-none');
        } else {
            document.querySelector('form[name="heatmap_create"] input[name="name"]').removeAttribute('disabled');
            document.querySelector('form[name="heatmap_create"] input[name="path"]').removeAttribute('disabled');
            document.querySelector('#heatmap_create_name_container').classList.remove('d-none');
            document.querySelector('#heatmap_create_path_container').classList.remove('d-none');
            document.querySelector('#paths').removeAttribute('required');
            document.querySelector('#bulk_container').classList.add('d-none');
        }
    }

    heatmap_create_bulk_checker();

    document.querySelector('#is_bulk').addEventListener('change', heatmap_create_bulk_checker);
    
$('form[name="heatmap_create"]').on('submit', event => {
        let notification_container = event.currentTarget.querySelector('.notification-container');
        notification_container.innerHTML = '';
        pause_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

        $.ajax({
            type: 'POST',
            url: `${url}heatmaps-ajax/create`,
            data: $(event.currentTarget).serialize(),
            dataType: 'json',
            success: (data) => {
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

                if(data.status == 'error') {
                    display_notifications(data.message, 'error', notification_container);
                }

                else if(data.status == 'success') {

                    display_notifications(data.message, 'success', notification_container);

                    setTimeout(() => {

                        /* Hide modal */
                        $('#heatmap_create').modal('hide');

                        /* Clear input values */
                        $('form[name="heatmap_create"] input').val('');
                        $('form[name="heatmap_create"] textarea').val('');

                        /* Fade out refresh */
                        redirect('heatmaps');

                        /* Remove the notification */
                        notification_container.innerHTML = '';

                    }, 1000);

                }
            },
            error: () => {
                enable_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));
                display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            },
        });

        event.preventDefault();
    })
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
