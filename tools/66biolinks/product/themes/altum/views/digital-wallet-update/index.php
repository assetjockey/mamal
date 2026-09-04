<?php defined('ALTUMCODE') || die() ?>

<div class="container">
	<?= \Altum\Alerts::output_alerts() ?>

	<?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('digital-wallets') ?>"><?= l('digital_wallets.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('digital_wallet_update.breadcrumb') ?></li>
            </ol>
        </nav>
	<?php endif ?>

    <div class="d-flex justify-content-between align-items-end mb-4">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-wifi mr-1"></i> <?= l('digital_wallet_update.header') ?></h1>

		<?= include_view(THEME_PATH . 'views/digital-wallets/digital_wallet_dropdown_button.php', ['id' => $data->digital_wallet->digital_wallet_id, 'hash' => $data->digital_wallet->digital_wallet_hash, 'resource_name' => $data->digital_wallet->name]) ?>
	</div>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" maxlength="128" required="required" />
                    <small class="form-text text-muted"><?= l('digital_wallets.name_help') ?></small>
					<?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-group">
                    <label for="title"><i class="fas fa-fw fa-pen fa-sm text-muted mr-1"></i> <?= l('global.title') ?></label>
                    <input type="text" id="title" name="title" class="form-control <?= \Altum\Alerts::has_field_errors('title') ? 'is-invalid' : null ?>" value="<?= $data->values['title'] ?>" maxlength="128" required="required" />
                    <small class="form-text text-muted"><?= l('digital_wallets.title_help') ?></small>
					<?= \Altum\Alerts::output_field_error('title') ?>
                </div>

                <div class="form-group">
                    <label for="subtitle"><i class="fas fa-fw fa-heading fa-sm text-muted mr-1"></i> <?= l('digital_wallets.subtitle') ?></label>
                    <input type="text" id="subtitle" name="subtitle" class="form-control" value="<?= $data->values['subtitle'] ?>" maxlength="128" />
                    <small class="form-text text-muted"><?= l('digital_wallets.subtitle_help') ?></small>
                </div>

                <div class="form-group">
                    <label for="link_id"><i class="fas fa-fw fa-link fa-sm text-muted mr-1"></i> <?= l('digital_wallets.link_id') ?></label>
                    <select id="link_id" name="link_id" class="custom-select">
                        <option value=" "><?= l('digital_wallets.custom') ?></option>
						<?php foreach($data->links as $row): ?>
                            <option value="<?= $row->link_id ?>" data-location-url="<?= e($row->full_url) ?>" <?= $data->values['link_id'] == $row->link_id ? 'selected="selected"' : null ?>>
								<?= remove_url_protocol_from_url($row->full_url) . ($row->location_url ? ' -> ' . remove_url_protocol_from_url($row->location_url) : null) ?>
                            </option>
						<?php endforeach ?>
                    </select>
                    <small class="form-text text-muted"><?= l('digital_wallets.link_id_help') ?></small>
					<?= \Altum\Alerts::output_field_error('link_id') ?>
                </div>

                <div class="form-group" data-location-url-wrapper>
                    <label for="location_url"><i class="fas fa-fw fa-link fa-sm text-muted mr-1"></i> <?= l('link.settings.location_url') ?></label>
                    <input type="url" id="location_url" name="location_url" class="form-control <?= \Altum\Alerts::has_field_errors('location_url') ? 'is-invalid' : null ?>" value="<?= $data->values['location_url'] ?>" maxlength="2048" placeholder="<?= l('global.url_placeholder') ?>" required="required" />
                    <small class="form-text text-muted"><?= l('digital_wallets.location_url_help') ?></small>
					<?= \Altum\Alerts::output_field_error('location_url') ?>
                </div>

                <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= settings()->digital_wallets->logo_size_limit ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), settings()->digital_wallets->logo_size_limit) ?>">
                    <label for="logo"><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('digital_wallets.logo') ?></label>
					<?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'digital_wallets', 'file_key' => 'logo', 'already_existing_image' => $data->digital_wallet->settings->logo ?? null]) ?>
					<?= \Altum\Alerts::output_field_error('logo') ?>
                    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('digital_wallets')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->digital_wallets->logo_size_limit) ?></small>
                </div>

                <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= settings()->digital_wallets->image_size_limit ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), settings()->digital_wallets->image_size_limit) ?>">
                    <label for="image"><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('digital_wallets.image') ?></label>
					<?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'digital_wallets', 'file_key' => 'image', 'already_existing_image' => $data->digital_wallet->settings->image ?? null, 'input_data' => 'data-crop data-aspect-ratio="3"']) ?>
					<?= \Altum\Alerts::output_field_error('image') ?>
                    <small class="form-text text-muted"><?= l('digital_wallets.image_help') . ' ' . sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('digital_wallets')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->digital_wallets->image_size_limit) ?></small>
                </div>

                <div class="form-group">
                    <label for="background_color"><i class="fas fa-fw fa-fill fa-sm text-muted mr-1"></i> <?= l('digital_wallets.background_color') ?></label>
                    <input type="hidden" id="background_color" name="background_color" class="form-control <?= \Altum\Alerts::has_field_errors('background_color') ? 'is-invalid' : null ?>" value="<?= $data->values['background_color'] ?>" data-color-picker />
					<?= \Altum\Alerts::output_field_error('background_color') ?>
                </div>

                <div class="form-group">
                    <label for="phone"><i class="fas fa-fw fa-phone fa-sm text-muted mr-1"></i> <?= l('global.phone') ?></label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?= $data->values['phone'] ?>" maxlength="32" />
					<?= \Altum\Alerts::output_field_error('phone') ?>
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-fw fa-envelope fa-sm text-muted mr-1"></i> <?= l('global.email') ?></label>
                    <input type="email" id="email" name="email" class="form-control <?= \Altum\Alerts::has_field_errors('email') ? 'is-invalid' : null ?>" value="<?= $data->values['email'] ?>" maxlength="320" placeholder="<?= l('global.email_placeholder') ?>" />
					<?= \Altum\Alerts::output_field_error('email') ?>
                </div>

                <div class="form-group">
                    <label for="website"><i class="fas fa-fw fa-globe fa-sm text-muted mr-1"></i> <?= l('digital_wallets.website') ?></label>
                    <input type="url" id="website" name="website" class="form-control <?= \Altum\Alerts::has_field_errors('website') ? 'is-invalid' : null ?>" value="<?= $data->values['website'] ?>" maxlength="2048" placeholder="<?= l('global.url_placeholder') ?>" />
					<?= \Altum\Alerts::output_field_error('website') ?>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.update') ?></button>
            </form>

        </div>
    </div>
</div>

<?php include_view(THEME_PATH . 'views/partials/color_picker_js.php') ?>
<?php include_view(THEME_PATH . 'views/partials/js_cropper.php') ?>

<?php ob_start() ?>
<script>
    'use strict';

    let toggle_location_url = () => {
        let link_id = document.querySelector('#link_id');
        let location_url = document.querySelector('#location_url');
        let location_url_wrapper = document.querySelector('[data-location-url-wrapper]');
        let selected_option = link_id.options[link_id.selectedIndex];

        if(selected_option.dataset.locationUrl) {
            location_url.value = selected_option.dataset.locationUrl;
            location_url.removeAttribute('required');
            location_url.setAttribute('disabled', 'disabled');
            location_url_wrapper.classList.add('d-none');
        } else {
            location_url.removeAttribute('disabled');
            location_url.setAttribute('required', 'required');
            location_url_wrapper.classList.remove('d-none');
        }
    };

    document.querySelector('#link_id').addEventListener('change', toggle_location_url);

    toggle_location_url();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
