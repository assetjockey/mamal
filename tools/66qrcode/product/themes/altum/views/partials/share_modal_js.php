<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>
<div class="modal fade" id="share_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-share-alt text-dark mr-2"></i>
                        <?= l('global.share') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="mb-3" data-qr></div>

                <div class="d-flex align-items-center flex-wrap gap-3">
                    <?= include_view(THEME_PATH . 'views/partials/share_buttons.php', ['url' => '%s', 'class' => 'btn btn-gray-100', 'print_is_enabled' => false]) ?>
                </div>

                <div class="form-group mt-4">
                    <div class="input-group">
                        <input id="share_modal_value" type="text" class="form-control" value="%s" onclick="this.select();" readonly="readonly" />

                        <div class="input-group-append dropdown">
                            <button
                                    type="button"
                                    class="btn btn-light border border-left-0 dropdown-toggle dropdown-toggle-simple"
                                    title="<?= l('global.download') ?>"
                                    data-toggle="dropdown"
                                    aria-expanded="false"
                                    data-tooltip
                                    data-tooltip-hide-on-click
                            >
                                <i class="fas fa-fw fa-sm fa-download"></i>
                            </button>

                            <div class="dropdown-menu">
                                <button type="button" class="dropdown-item" data-download-qr="png"><?= sprintf(l('global.download_as'), 'PNG') ?></button>
                                <button type="button" class="dropdown-item" data-download-qr="jpg"><?= sprintf(l('global.download_as'), 'JPG') ?></button>
                                <button type="button" class="dropdown-item" data-download-qr="webp"><?= sprintf(l('global.download_as'), 'WEBP') ?></button>
                            </div>
                        </div>

                        <div class="input-group-append">
                            <a
                                    href="%s"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-light border border-left-0"
                                    data-toggle="tooltip"
                                    title="<?= l('global.open_in_new_tab') ?>"
                            >
                                <i class="fas fa-fw fa-sm fa-external-link-alt"></i>
                            </a>
                        </div>

                        <div class="input-group-append">
                            <button
                                    id="share_modal_value_copy"
                                    type="button"
                                    class="btn btn-light border border-left-0"
                                    data-toggle="tooltip"
                                    title="<?= l('global.clipboard_copy') ?>"
                                    aria-label="<?= l('global.clipboard_copy') ?>"
                                    data-copy="<?= l('global.clipboard_copy') ?>"
                                    data-copied="<?= l('global.clipboard_copied') ?>"
                                    data-clipboard-text="%s"
                            >
                                <i class="fas fa-fw fa-sm fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<?php \Altum\Event::add_content(ob_get_clean(), 'modals', 'share_modal'); ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/jquery-qrcode.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    'use strict';

    /* On modal show load new data */
    $('#share_modal').on('show.bs.modal', event => {
        let url = $(event.relatedTarget).data('url');
        let qr = event.currentTarget.querySelector('[data-qr]');

        let generate_qr = (url) => {
            let default_options = {
                render: 'image',
                minVersion: 1,
                maxVersion: 40,
                ecLevel: 'L',
                left: 0,
                top: 0,
                size: 1000,
                text: url,
                quiet: 0,
                mode: 0,
                mSize: 0.1,
                mPosX: 0.5,
                mPosY: 0.5,
            };

            /* Delete already existing image generated */
            qr.querySelector('img') && qr.querySelector('img').remove();
            $(qr).qrcode(default_options);

            /* Set class to QR */
            qr.querySelector('img').classList.add('w-100');
            qr.querySelector('img').classList.add('rounded');

        }

        generate_qr(url);

        /* Download QR button */
        event.currentTarget.querySelectorAll('[data-download-qr]').forEach(element => {
            element.onclick = () => {
                let type = element.getAttribute('data-download-qr');
                let qr_img = qr.querySelector('img');
                let canvas = document.createElement('canvas');
                let context = canvas.getContext('2d');
                let image = new Image();

                image.onload = () => {
                    canvas.width = image.width;
                    canvas.height = image.height;

                    if(type == 'jpg') {
                        context.fillStyle = '#ffffff';
                        context.fillRect(0, 0, canvas.width, canvas.height);
                    }

                    context.drawImage(image, 0, 0);

                    let link = document.createElement('a');
                    link.download = 'qr-code.' + type;
                    link.href = canvas.toDataURL('image/' + (type == 'jpg' ? 'jpeg' : type));
                    link.click();
                };

                image.src = qr_img.src;
            };
        });

        /* Update url */
        event.currentTarget.querySelectorAll('a').forEach(element => {
            let new_href = element.getAttribute('href').replace('%s', url);
            element.setAttribute('href', new_href);
        });

        event.currentTarget.querySelector('#share_modal_value').value = url;
        event.currentTarget.querySelector('#share_modal_value_copy').setAttribute('data-clipboard-text', url);

        /* Refresh clipboard */
        let modal_clipboard = new ClipboardJS('[data-clipboard-text]', {
            container: document.getElementById('share_modal')
        });
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'share_modal'); ?>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>
