<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
<nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('transfers') ?>"><?= l('transfers.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li>
                <a href="<?= url('transfer/' . $data->transfer->transfer_id) ?>"><?= l('transfer.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('transfer_qr.breadcrumb') ?></li>
        </ol>
    </nav>
<?php endif ?>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-paper-plane mr-1"></i> <?= sprintf(l('transfer_qr.header'), $data->transfer->name) ?></h1>

        <div class="d-flex align-items-center col-auto p-0">
            <div>
                <button
                        id="url_copy"
                        type="button"
                        class="btn btn-link text-secondary"
                        data-toggle="tooltip"
                        title="<?= l('global.clipboard_copy') ?>"
                        aria-label="<?= l('global.clipboard_copy') ?>"
                        data-copy="<?= l('global.clipboard_copy') ?>"
                        data-copied="<?= l('global.clipboard_copied') ?>"
                        data-clipboard-text="<?= $data->transfer->full_url ?>"
                >
                    <i class="fas fa-fw fa-sm fa-copy"></i>
                </button>
            </div>

            <?= include_view(THEME_PATH . 'views/transfers/transfer_dropdown_button.php', ['id' => $data->transfer->transfer_id, 'resource_name' => $data->transfer->name]) ?>
        </div>
    </div>

    <p class="text-truncate">
        <a href="<?= $data->transfer->full_url ?>" target="_blank">
            <i class="fas fa-fw fa-sm fa-external-link-alt text-muted mr-1"></i> <?= remove_url_protocol_from_url($data->transfer->full_url) ?>
        </a>
    </p>

    <div class="row">
        <div class="col-12 col-lg-6 mb-4 mb-lg-0 d-print-none">
            <div class="card">
                <div class="card-body">

                    <div class="form-group">
                        <label for="foreground_color"><?= l('transfer_qr.configurator.foreground_color') ?></label>
                        <input type="hidden" id="foreground_color" name="foreground_color" class="form-control" value="#000000" data-color-picker />
                    </div>

                    <div class="form-group">
                        <label for="background_color"><?= l('transfer_qr.configurator.background_color') ?></label>
                        <input type="hidden" id="background_color" name="background_color" class="form-control" value="#ffffff" data-color-picker />
                    </div>

                    <div class="form-group" data-range-counter>
                        <label for="corner_radius"><?= l('transfer_qr.configurator.corner_radius') ?></label>
                        <input type="range" id="corner_radius" name="corner_radius" class="form-control-range" min="0" max="0.5" step="0.1" value="0" />
                    </div>

                    <div class="form-group">
                        <label for="type"><?= l('transfer_qr.configurator.type') ?></label>
                        <select name="type" id="type" class="custom-select">
                            <option value="normal" selected="selected"><?= l('transfer_qr.configurator.type_normal') ?></option>
                            <option value="text"><?= l('transfer_qr.configurator.type_text') ?></option>
                            <option value="image"><?= l('transfer_qr.configurator.type_image') ?></option>
                        </select>
                    </div>

                    <div id="type_text" class="d-none">
                        <div class="form-group">
                            <label for="text"><?= l('transfer_qr.configurator.text') ?></label>
                            <input type="text" id="text" name="text" class="form-control" value="" />
                        </div>

                        <div class="form-group">
                            <label for="text_color"><?= l('transfer_qr.configurator.text_color') ?></label>
                            <input type="hidden" id="text_color" name="text_color" class="form-control" value="#000000" data-color-picker />
                        </div>

                        <div class="form-group" data-range-counter>
                            <label for="text_size"><?= l('transfer_qr.configurator.text_size') ?></label>
                            <input type="range" id="text_size" name="text_size" class="form-control-range" min="0.05" max="0.1" step="0.005" value="0.005" />
                        </div>
                    </div>

                    <div id="type_image" class="d-none">
                        <div class="form-group">
                            <label for="image"><?= l('global.image') ?></label>
                            <input id="image" type="file" name="image" accept=".png, .jpg, .jpeg" class="form-control-file altum-file-input" />
                            <img id="image-buffer" src="" class="d-none" />
                        </div>

                        <div class="form-group" data-range-counter>
                            <label for="image_size"><?= l('transfer_qr.configurator.image_size') ?></label>
                            <input type="range" id="image_size" class="form-control-range" min="0.05" max="0.2" step="0.005" value="0.005" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ecc"><?= l('transfer_qr.configurator.ecc') ?></label>
                        <select name="ecc" id="ecc" class="custom-select">
                            <option value="L" selected="selected"><?= l('transfer_qr.configurator.ecc_l') ?></option>
                            <option value="M"><?= l('transfer_qr.configurator.ecc_m') ?></option>
                            <option value="Q"><?= l('transfer_qr.configurator.ecc_q') ?></option>
                            <option value="H"><?= l('transfer_qr.configurator.ecc_h') ?></option>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card mb-4">
                <div id="qr"></div>
            </div>

            <div class="row mb-4">
                <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                    <button type="button" onclick="window.print();return false;" class="btn btn-block btn-outline-secondary d-print-none <?= $this->user->plan_settings->export->pdf ? null : 'disabled' ?>">
                        <i class="fas fa-fw fa-sm fa-file-pdf mr-1"></i> <?= l('transfer_qr.print') ?>
                    </button>
                </div>

                <div class="col-12 col-lg-6">
                    <button id="download" type="button" class="btn btn-block btn-primary d-print-none">
                        <i class="fas fa-fw fa-sm fa-download mr-1"></i> <?= l('global.download') ?>
                    </button>
                </div>
            </div>

            <p class="text-muted text-center"><?= l('transfer_qr.subheader') ?></p>
        </div>
    </div>

</div>


<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

<?php ob_start() ?>
    <script src="<?= ASSETS_FULL_URL . 'js/libraries/jquery-qrcode.min.js?v=' . PRODUCT_CODE ?>"></script>

    <script>
        'use strict';

        /* Download handler */
        document.querySelector('#download').addEventListener('click', () => {
            let a = document.createElement('a');
            a.href = document.querySelector('#qr img').getAttribute('src');
            a.download = 'qr.png';
            a.click();
        });

        let generate_qr = () => {
            let qr_url = <?= json_encode($data->transfer->full_url . '?referrer=qr') ?>;

            let mode = 0;
            let mode_size = 0.1;

            switch(document.querySelector('#type').value) {
                case 'normal':
                    mode = 0;
                    break;

                case 'text':
                    mode = 2;
                    mode_size = parseFloat(document.querySelector('#text_size').value)
                    break;

                case 'image':
                    mode = 4;
                    mode_size = parseFloat(document.querySelector('#image_size').value)
                    break;
            }

            let default_options = {
                // render method: 'canvas', 'image' or 'div'
                render: 'image',

                // version range somewhere in 1 .. 40
                minVersion: 1,
                maxVersion: 40,

                // error correction level: 'L', 'M', 'Q' or 'H'
                ecLevel: document.querySelector('#ecc').value,

                // offset in pixel if drawn onto existing canvas
                left: 0,
                top: 0,

                // size in pixel
                size: 1000,

                // code color or image element
                fill: document.querySelector('#foreground_color').value,

                // background color or image element, null for transparent background
                background: document.querySelector('#background_color').value,

                // content
                text: qr_url,

                // corner radius relative to module width: 0.0 .. 0.5
                radius: document.querySelector('#corner_radius').value,

                // quiet zone in modules
                quiet: 0,

                // modes
                // 0: normal
                // 1: label strip
                // 2: label box
                // 3: image strip
                // 4: image box
                mode: mode,

                mSize: mode_size,
                mPosX: 0.5,
                mPosY: 0.5,

                label: document.querySelector('#text').value,
                fontname: 'arial',
                fontcolor: document.querySelector('#text_color').value,

                image: document.querySelector('#image-buffer')
            };

            /* Delete already existing image generated */
            document.querySelector('#qr img') && document.querySelector('#qr img').remove();

            $('#qr').qrcode(default_options);
        }

        generate_qr();

        /* Corner radius */
        document.querySelector('#corner_radius').addEventListener('change', generate_qr);

        /* Type */
        document.querySelector('#type').addEventListener('change', event => {
            let type = document.querySelector('#type').value;

            switch(type) {
                case 'normal':
                    document.querySelector('#type_text').classList.add('d-none');
                    document.querySelector('#type_image').classList.add('d-none');
                    break;

                case 'text':
                    document.querySelector('#type_text').classList.remove('d-none');
                    document.querySelector('#type_image').classList.add('d-none')
                    break;

                case 'image':
                    document.querySelector('#type_text').classList.add('d-none');
                    document.querySelector('#type_image').classList.remove('d-none')
                    break;
            }

            generate_qr();

        });

        ['foreground_color', 'background_color', 'text', 'text_size', 'text_color', 'corner_radius'].forEach(name => {
            document.querySelector(`input[name="${name}"]`).addEventListener('change', generate_qr);
        })

        /* Ecc */
        document.querySelector(`select[name="ecc"]`).addEventListener('change', generate_qr);

        /* Image */
        document.querySelector('#image').addEventListener('change', () => {
            const input = document.querySelector('#image');

            if(input.files && input.files[0]) {
                const reader = new window.FileReader();

                reader.onload = event => {
                    document.querySelector('#image-buffer').setAttribute('src', event.target.result);

                    setTimeout(generate_qr, 250);
                };

                reader.readAsDataURL(input.files[0]);
            }
        });

        /* Image size */
        document.querySelector('#image_size').addEventListener('change', generate_qr);

    </script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>



<?php include_view(THEME_PATH . 'views/partials/color_picker_js.php') ?>
