<?php defined('ALTUMCODE') || die() ?>

<?php if(settings()->main->breadcrumbs_is_enabled): ?>
<nav aria-label="breadcrumb">
    <ol class="custom-breadcrumbs small">
        <li>
            <a href="<?= url('admin/ping-servers') ?>"><?= l('admin_ping_servers.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
        </li>
        <li class="active" aria-current="page"><?= l('admin_ping_server_update.breadcrumb') ?></li>
    </ol>
</nav>
<?php endif ?>

<div class="mb-4">
    <div class="d-flex justify-content-between">
        <h1 class="h3 mb-0 text-truncate"><i class="fas fa-fw fa-xs fa-map-marked-alt text-primary-900 mr-2"></i> <?= l('admin_ping_server_update.header') ?></h1>

        <?= include_view(THEME_PATH . 'views/admin/ping-servers/admin_ping_server_dropdown_button.php', ['id' => $data->ping_server->ping_server_id, 'resource_name' => $data->ping_server->name]) ?>
    </div>
</div>

<div class="alert alert-info" role="alert">
    <?= l('admin_ping_server_create.subheader') ?>
</div>

<?= \Altum\Alerts::output_alerts() ?>

<div class="card <?= \Altum\Alerts::has_field_errors() ? 'border-danger' : null ?>">
    <div class="card-body">

        <form action="" method="post" role="form">
            <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

            <div class="form-group">
                <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                <input id="name" type="text" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" name="name" value="<?= $data->ping_server->name ?>" required="required" />
                <?= \Altum\Alerts::output_field_error('name') ?>
            </div>

            <div class="form-group">
                <label for="url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('admin_ping_servers.url') ?></label>
                <input id="url" type="url" class="form-control <?= \Altum\Alerts::has_field_errors('url') ? 'is-invalid' : null ?>" name="url" value="<?= $data->ping_server->url ?>" placeholder="<?= l('admin_ping_servers.url_placeholder') ?>" <?= $data->ping_server->ping_server_id == 1 ? 'readonly="readonly"' : 'required="required"' ?> />
                <?= \Altum\Alerts::output_field_error('url') ?>
            </div>

            <div class="form-group">
                <label for="country_code"><i class="fas fa-fw fa-sm fa-flag text-muted mr-1"></i> <?= l('global.country') ?></label>
                <select id="country_code" name="country_code" class="custom-select">
                    <?php foreach(get_countries_array() as $country_code => $country_name): ?>
                        <option value="<?= $country_code ?>" <?= $data->ping_server->country_code == $country_code ? 'selected="selected"' : null ?>><?= $country_name ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="form-group">
                <label for="city_name"><i class="fas fa-fw fa-sm fa-city text-muted mr-1"></i> <?= l('global.city') ?></label>
                <input id="city_name" type="text" class="form-control <?= \Altum\Alerts::has_field_errors('city_name') ? 'is-invalid' : null ?>" name="city_name" value="<?= $data->ping_server->city_name ?>" required="required" />
                <?= \Altum\Alerts::output_field_error('city_name') ?>
                <small class="form-text text-muted"><?= l('admin_ping_servers.city_name_help') ?></small>
            </div>

            <div class="form-group">
                <label for="api_key"><i class="fas fa-fw fa-sm fa-key text-muted mr-1"></i> <?= l('admin_ping_servers.api_key') ?></label>
                <input id="api_key" type="text" class="form-control <?= \Altum\Alerts::has_field_errors('api_key') ? 'is-invalid' : null ?>" name="api_key" value="<?= $data->ping_server->api_key ?>" maxlength="128" />
                <?= \Altum\Alerts::output_field_error('api_key') ?>
                <small class="form-text text-muted"><?= l('admin_ping_servers.api_key_help') ?></small>
            </div>

            <div class="form-group custom-control custom-switch">
                <input id="is_enabled" name="is_enabled" type="checkbox" class="custom-control-input" <?= $data->ping_server->is_enabled ? 'checked="checked"' : null ?> <?= $data->ping_server->ping_server_id == 1 ? 'disabled="disabled"' : null ?>>
                <label class="custom-control-label" for="is_enabled"><?= l('global.status') ?></label>
            </div>

            <button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
        </form>

    </div>
</div>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_url.php', [
    'name' => 'ping_server',
    'resource_id' => 'ping_server_id',
    'has_dynamic_resource_name' => true,
    'path' => 'admin/ping-servers/delete/'
]), 'modals'); ?>
