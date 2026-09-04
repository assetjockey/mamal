<?php defined('ALTUMCODE') || die() ?>

    <div class="modal fade" id="server_monitor_uninstall_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="modal-title">
                            <i class="fas fa-fw fa-sm fa-times text-dark mr-2"></i>
                            <?= l('server_monitor_uninstall_modal.header') ?>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="tab-content">
                        <p class="text-muted"><?= l('server_monitor_uninstall_modal.subheader') ?></p>

                        <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#uninstall_linux" aria-expanded="false" aria-controls="uninstall_linux">
                            <i class="fab fa-fw fa-linux fa-sm mr-1"></i> <?= l('server_monitor_install_modal.linux') ?>
                        </button>

                        <div class="collapse" data-parent="#server_monitor_uninstall_modal" id="uninstall_linux">
                            <pre id="uninstall_code_linux" class="pre-custom rounded"></pre>

                            <div class="my-4">
                                <button type="button" class="btn btn-block btn-primary" data-clipboard-target="#uninstall_code_linux" data-copied="<?= l('global.clipboard_copied') ?>"><?= l('global.clipboard_copy') ?></button>
                            </div>
                        </div>

                        <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#uninstall_macos" aria-expanded="false" aria-controls="uninstall_macos">
                            <i class="fab fa-fw fa-apple fa-sm mr-1"></i> <?= l('server_monitor_install_modal.macos') ?>
                        </button>

                        <div class="collapse" data-parent="#server_monitor_uninstall_modal" id="uninstall_macos">
                            <pre id="uninstall_code_macos" class="pre-custom rounded"></pre>

                            <div class="my-4">
                                <button type="button" class="btn btn-block btn-primary" data-clipboard-target="#uninstall_code_macos" data-copied="<?= l('global.clipboard_copied') ?>"><?= l('global.clipboard_copy') ?></button>
                            </div>
                        </div>

                        <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#uninstall_windows" aria-expanded="false" aria-controls="uninstall_windows">
                            <i class="fab fa-fw fa-windows fa-sm mr-1"></i> <?= l('server_monitor_install_modal.windows') ?>
                        </button>

                        <div class="collapse" data-parent="#server_monitor_uninstall_modal" id="uninstall_windows">
                            <pre id="uninstall_code_windows" class="pre-custom rounded"></pre>

                            <div class="my-4">
                                <button type="button" class="btn btn-block btn-primary" data-clipboard-target="#uninstall_code_windows" data-copied="<?= l('global.clipboard_copied') ?>"><?= l('global.clipboard_copy') ?></button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

<?php ob_start() ?>
    <script>
    'use strict';

        $('#server_monitor_uninstall_modal').on('show.bs.modal', function(event) {
            let trigger_element = $(event.relatedTarget);
            let name = trigger_element.data('name');
            let site_title = <?= json_encode(get_slug(settings()->main->title)) ?>;

            let uninstall_linux = `script_name="${site_title}.sh"; (crontab -l 2>/dev/null | grep -v "$script_name") | crontab -; echo "The ${name} monitor from ${site_title} has been uninstalled."`;
            $('#uninstall_code_linux').text(uninstall_linux);

            let uninstall_macos = `script_name="${site_title}.sh"; (crontab -l 2>/dev/null | grep -v "$script_name") | crontab -; echo "The ${name} monitor from ${site_title} has been uninstalled."`;
            $('#uninstall_code_macos').text(uninstall_macos);

            let uninstall_windows = `Unregister-ScheduledTask -TaskName "${site_title}_Monitor" -Confirm:\$false; Remove-Item "$PWD\\${site_title}.ps1" -Force; Write-Host "The ${name} monitor from ${site_title} has been uninstalled."`;
            $('#uninstall_code_windows').text(uninstall_windows);

            new ClipboardJS('[data-clipboard-target]');

            $('[data-clipboard-target]').off('click').on('click', function() {
                let copy_button = $(this);
                let initial_text = copy_button.text();

                copy_button.text(copy_button.data('copied'));

                setTimeout(() => {
                    copy_button.text(initial_text);
                }, 2500);
            });
        });
    </script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
