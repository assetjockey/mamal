<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="campaign_update_modal" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-pen text-dark mr-2"></i>
                        <?= l('campaign_update_modal.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form name="campaign_update" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="request_type" value="update" />
                    <input type="hidden" name="campaign_id" value="" />

                    <div class="notification-container"></div>

                    <div class="form-group">
                        <label for="campaign_update_name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                        <input id="campaign_update_name" type="text" class="form-control" name="name"  />
                    </div>

                    <div class="form-group">
                        <label for="campaign_update_domain"><i class="fas fa-fw fa-sm fa-network-wired text-muted mr-1"></i> <?= l('campaigns.domain') ?></label>
                        <input id="campaign_update_domain" type="text" class="form-control" name="domain" placeholder="<?= l('campaigns.domain_placeholder') ?>" required="required" />
                        <small class="form-text text-muted"><?= l('campaigns.domain_help') ?></small>
                    </div>

                    <?php if(count($data->domains) && settings()->notifications->domains_is_enabled): ?>
                        <div class="form-group">
                            <label for="campaign_update_domain_id"><i class="fas fa-fw fa-sm fa-globe text-muted mr-1"></i> <?= l('campaigns.domain_id') ?></label>
                            <select id="campaign_update_domain_id" name="domain_id" class="custom-select">
                                <option value=" "><?= parse_url(SITE_URL, PHP_URL_HOST) ?></option>
                                <?php foreach($data->domains as $row): ?>
                                    <option value="<?= $row->domain_id ?>"><?= $row->host ?></option>
                                <?php endforeach ?>
                            </select>
                            <small class="form-text text-muted"><?= l('campaigns.domain_id_help') ?></small>
                        </div>
                    <?php endif ?>

                    <?php if(settings()->notification_handlers->is_enabled && settings()->notifications->email_reports_is_enabled): ?>
                        <div <?= $data->user->plan_settings->email_reports_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                            <div class="form-group <?= $data->user->plan_settings->email_reports_is_enabled ? null : 'container-disabled' ?>">
                                <div class="d-flex flex-wrap flex-row justify-content-between">
                                    <label><i class="fas fa-fw fa-sm fa-envelope-open-text text-muted mr-1"></i> <?= l('global.plan_settings.email_reports_is_enabled_' . settings()->notifications->email_reports_is_enabled) ?></label>
                                    <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                                </div>
                                <div class="mb-2"><small class="text-muted"><?= l('campaigns.email_reports_is_enabled_help') ?></small></div>

                                <div class="row">
                                    <?php foreach($data->notification_handlers as $notification_handler): ?>
                                        <?php if($notification_handler->type != 'email') continue ?>
                                        <div class="col-12 col-lg-6">
                                            <div class="custom-control custom-checkbox my-2">
                                                <input id="campaign_update_email_reports_<?= $notification_handler->notification_handler_id ?>" name="email_reports[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input">
                                                <label class="custom-control-label" for="campaign_update_email_reports_<?= $notification_handler->notification_handler_id ?>">
                                                    <span class="mr-1"><?= $notification_handler->name ?></span>
                                                    <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>

                    <div class="text-center mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-primary" data-is-ajax><?= l('global.submit') ?></button>
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
    $('#campaign_update_modal').on('show.bs.modal', event => {
        let campaign_id = $(event.relatedTarget).data('campaign-id');
        let domain_id = $(event.relatedTarget).data('domain-id');
        let name = $(event.relatedTarget).data('name');
        let domain = $(event.relatedTarget).data('domain');
        let email_reports = $(event.relatedTarget).data('email-reports');

        $(event.currentTarget).find(`input[name="email_reports[]"]`).removeAttr('checked');
        if(email_reports.length) {
            email_reports.forEach(notification_handler_id => {
                console.log(`input[name="email_reports[]"][value="${notification_handler_id}"]`);
                $(event.currentTarget).find(`input[name="email_reports[]"][value="${notification_handler_id}"]`).attr('checked', 'checked');
            })
        }
        $(event.currentTarget).find('input[name="campaign_id"]').val(campaign_id);
        $(event.currentTarget).find('select[name="domain_id"]').val(domain_id);
        $(event.currentTarget).find('input[name="name"]').val(name);
        $(event.currentTarget).find('input[name="domain"]').val(domain).trigger('change');

        $(event.currentTarget).find('select').trigger('change');
    });

    $('form[name="campaign_update"]').on('submit', event => {
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

                        /* Hide modal */
                        $('#campaign_update_modal').modal('hide');

                        /* Clear input values */
                        $('form[name="campaign_update"] input').val('');

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

<?php if(settings()->notification_handlers->is_enabled && settings()->notifications->email_reports_is_enabled): ?>
<script>
    'use strict';

let campaign_update_active_notification_handlers_per_resource_limit = <?= (int) $data->user->plan_settings->active_notification_handlers_per_resource_limit ?>;

    if(campaign_update_active_notification_handlers_per_resource_limit != -1) {
        let process_notification_handlers = () => {
            let selected = document.querySelectorAll('#campaign_update_modal [name="email_reports[]"]:checked').length;

            if(selected >= campaign_update_active_notification_handlers_per_resource_limit) {
                document.querySelectorAll('#campaign_update_modal [name="email_reports[]"]:not(:checked)').forEach(element => element.setAttribute('disabled', 'disabled'));
            } else {
                document.querySelectorAll('#campaign_update_modal [name="email_reports[]"]:not(:checked)').forEach(element => element.removeAttribute('disabled'));
            }
        }

        document.querySelectorAll('#campaign_update_modal [name="email_reports[]"]').forEach(element => element.addEventListener('change', process_notification_handlers));

        process_notification_handlers();
    }
</script>
<?php endif ?>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
