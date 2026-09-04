<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="campaign_custom_branding_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-random text-dark mr-2"></i>
                        <?= l('campaign_custom_branding_modal.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form name="campaign_custom_branding" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="request_type" value="custom_branding" />
                    <input type="hidden" name="campaign_id" value="" />

                    <div class="notification-container"></div>

                    <div class="form-group">
                        <label for="custom_branding_name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('campaign_custom_branding_modal.name') ?></label>
                        <input id="custom_branding_name" type="text" class="form-control" name="name" value="<?= e($data->campaign->branding->name ?? '') ?>" maxlength="512" />
                        <small class="form-text text-muted" data-toggle="tooltip" title="<?= l('notification.settings.html_info_tooltip') ?>"><?= l('notification.settings.html_info') ?></small>
                        <small class="form-text text-muted"><?= l('campaign_custom_branding_modal.name_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="custom_branding_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('global.url') ?></label>
                        <input id="custom_branding_url" type="url" class="form-control" name="url" value="<?= $data->campaign->branding->url ?? '' ?>" placeholder="<?= l('global.url_placeholder') ?>" maxlength="2048" />
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-primary" data-is-ajax><?= l('global.update') ?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    /* On modal show load new data */
    $('#campaign_custom_branding_modal').on('show.bs.modal', event => {
        let campaign_id = $(event.relatedTarget).data('campaign-id');
        let branding_name = $(event.relatedTarget).data('branding-name');
        let branding_url = $(event.relatedTarget).data('branding-url');

        $(event.currentTarget).find('input[name="campaign_id"]').val(campaign_id);
        $(event.currentTarget).find('input[name="name"]').val(branding_name);
        $(event.currentTarget).find('input[name="url"]').val(branding_url);
    });

    $('form[name="campaign_custom_branding"]').on('submit', event => {
        let notification_container = event.currentTarget.querySelector('.notification-container');
        notification_container.innerHTML = '';
        pause_submit_button(event.currentTarget.querySelector('[type="submit"][name="submit"]'));

        $.ajax({
            type: 'POST',
            url: `${url}campaigns-ajax`,
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
                        /* Remove the notification */
                        notification_container.innerHTML = '';

                        /* Hide modal */
                        $('#campaign_custom_branding_modal').modal('hide');

                        /* Clear input values */
                        $('form[name="create_notification_data"] input').val('');
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
