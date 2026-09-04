<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="server_monitor_install_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-code text-dark mr-2"></i>
                        <?= l('server_monitor_install_modal.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="tab-content">
                    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#linux_container" aria-expanded="false" aria-controls="linux_container">
                        <i class="fab fa-fw fa-linux fa-sm mr-1"></i> <?= l('server_monitor_install_modal.linux') ?>
                    </button>

                    <div class="collapse" data-parent="#server_monitor_install_modal" id="linux_container">
                        <p class="font-size-small text-muted"><?= l('server_monitor_install_modal.linux_help') ?></p>

                        <pre id="install_code_linux" class="pre-custom rounded"></pre>

                        <div class="my-4">
                            <button type="button" class="btn btn-block btn-primary" data-clipboard-target="#install_code_linux" data-copied="<?= l('global.clipboard_copied') ?>"><?= l('global.clipboard_copy') ?></button>
                        </div>
                    </div>

                    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#macos_container" aria-expanded="false" aria-controls="macos_container">
                        <i class="fab fa-fw fa-apple fa-sm mr-1"></i> <?= l('server_monitor_install_modal.macos') ?>
                    </button>

                    <div class="collapse" data-parent="#server_monitor_install_modal" id="macos_container">
                        <p class="font-size-small text-muted"><?= l('server_monitor_install_modal.macos_help') ?></p>

                        <pre id="install_code_macos" class="pre-custom rounded"></pre>

                        <div class="my-4">
                            <button type="button" class="btn btn-block btn-primary" data-clipboard-target="#install_code_macos" data-copied="<?= l('global.clipboard_copied') ?>"><?= l('global.clipboard_copy') ?></button>
                        </div>
                    </div>

                    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#windows_container" aria-expanded="false" aria-controls="windows_container">
                        <i class="fab fa-fw fa-windows fa-sm mr-1"></i> <?= l('server_monitor_install_modal.windows') ?>
                    </button>

                    <div class="collapse" data-parent="#server_monitor_install_modal" id="windows_container">
                        <p class="font-size-small text-muted"><?= l('server_monitor_install_modal.windows_help') ?></p>

                        <pre id="install_code_windows" class="pre-custom rounded"></pre>

                        <div class="my-4">
                            <button type="button" class="btn btn-block btn-primary" data-clipboard-target="#install_code_windows" data-copied="<?= l('global.clipboard_copied') ?>"><?= l('global.clipboard_copy') ?></button>
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

    let clipboard_instance = null;

    $('#server_monitor_install_modal').on('show.bs.modal', function(event) {
        let trigger_element = $(event.relatedTarget);
        let server_monitor_id = trigger_element.data('server-monitor-id');
        let api_key = trigger_element.data('api-key');
        let server_monitor_check_interval_seconds = trigger_element.data('server-check-interval-seconds');
        let name = trigger_element.data('name');
        let site_title = <?= json_encode(get_slug(settings()->main->title)) ?>;
        let cron = '';
        let interval_minutes = server_monitor_check_interval_seconds / 60;

        switch(server_monitor_check_interval_seconds) {
            case 60:
                cron = '* * * * *';
                break;
            default:
                cron = `*/${interval_minutes} * * * *`;
                break;
        }

        let install_html_linux = `script_name="${site_title}.sh"; curl -fsSL "${site_url}server-monitor-code/${server_monitor_id}/${api_key}?os=linux" -o "$PWD/$script_name" || wget -O "$PWD/$script_name" "${site_url}server-monitor-code/${server_monitor_id}/${api_key}?os=linux"; chmod +x "$PWD/$script_name"; (crontab -l 2>/dev/null | grep -v "$script_name"; echo "${cron} /bin/bash \"$PWD/$script_name\"") | crontab -; echo "Installed ${name} monitor from ${site_title}."`;
        $('#install_code_linux').text(install_html_linux);

        let install_html_macos = `script_name="${site_title}.sh"; script_path="$(pwd)/$script_name"; curl -fsSL "${site_url}server-monitor-code/${server_monitor_id}/${api_key}?os=macos" -o "$script_path" || wget -O "$script_path" "${site_url}server-monitor-code/${server_monitor_id}/${api_key}?os=macos"; chmod +x "$script_path"; (crontab -l 2>/dev/null | grep -v "$script_path"; echo "${cron} $script_path") | crontab -; echo "Installed ${name} monitor from ${site_title}."`;
        $('#install_code_macos').text(install_html_macos);

        let install_html_windows = `$u="${site_url}server-monitor-code/${server_monitor_id}/${api_key}?os=windows"; $n="${site_title}.ps1"; iwr $u -OutFile $n -UseBasicParsing; $a=New-ScheduledTaskAction -Execute "powershell.exe" -Argument "-EP Bypass -File \`"$PWD\\$n\`""; $d=New-TimeSpan -Days 365; $t=New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) -RepetitionInterval (New-TimeSpan -Minutes ${interval_minutes}) -RepetitionDuration $d; $p=New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest; Register-ScheduledTask -TaskName "${site_title}_Monitor" -Action $a -Trigger $t -Principal $p -Force; $ts = Get-ScheduledTask -TaskName "${site_title}_Monitor"; $ts.Settings.DisallowStartIfOnBatteries = \$false; $ts.Settings.StopIfGoingOnBatteries = \$false; $ts.Settings.StartWhenAvailable = \$true; $ts.Settings.IdleSettings.StopOnIdleEnd = \$false; $ts.Settings.IdleSettings.RestartOnIdle = \$false; Set-ScheduledTask -TaskName "${site_title}_Monitor" -Settings \$ts.Settings; Write-Host "Installed ${name} monitor from ${site_title}."`;        $('#install_code_windows').text(install_html_windows);

        /* Reinitialize ClipboardJS */
        if(clipboard_instance !== null) {
            clipboard_instance.destroy();
        }
        clipboard_instance = new ClipboardJS('[data-clipboard-target]');

        /* Copy feedback */
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
