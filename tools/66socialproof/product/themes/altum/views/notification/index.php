<?php defined('ALTUMCODE') || die() ?>

<div class="container">

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('campaigns') ?>"><?= l('campaigns.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?= url('campaign/' . $data->notification->campaign_id) ?>"><?= l('campaign.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('notification.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="row mb-3">
        <div class="col text-truncate">
            <h1 class="h4 text-truncate font-weight-600"><span><?= $data->notification->name ?></span></h1>

            <div class="row">
                <div class="col-auto text-truncate">
                    <div class="d-flex align-items-center text-muted">
                        <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($data->campaign->domain) ?>" class="img-fluid icon-favicon-small mr-2" loading="lazy" />

                        <div class="d-inline-block text-truncate"><?= $data->campaign->domain ?></div>

                        <a href="<?= 'https://' . $data->campaign->domain ?>" class="small" target="_blank" rel="noreferrer"><i class="fas fa-fw fa-sm fa-external-link-alt text-muted ml-2"></i></a>
                    </div>
                </div>

                <div class="col">
                    <span class="text-muted">
                        <i class="<?= l('notification.' . mb_strtolower($data->notification->type) . '.icon') ?> fa-sm mr-1"></i> <?= l('notification.' . mb_strtolower($data->notification->type) . '.name') ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-auto">
            <div class="d-flex align-items-center">
                <div class="custom-control custom-switch" data-toggle="tooltip" title="<?= l('notifications.table.is_enabled_tooltip') ?>">
                    <input
                            type="checkbox"
                            class="custom-control-input"
                            id="campaign_is_enabled_<?= $data->notification->notification_id ?>"
                            data-row-id="<?= $data->notification->notification_id ?>"
                            onchange="ajax_call_helper(event, 'notifications-ajax', 'is_enabled_toggle')"
                            <?= $data->notification->is_enabled ? 'checked="checked"' : null ?>
                    >
                    <label class="custom-control-label" for="campaign_is_enabled_<?= $data->notification->notification_id ?>"></label>
                </div>

                <?php if(in_array('settings', $data->notification->settings->enabled_methods) && $data->method != 'settings'): ?>
                    <a href="<?= url('notification/' . $data->notification->notification_id . '/settings') ?>" class="btn btn-link text-secondary" data-toggle="tooltip" title="<?= l('notification.settings.link') ?>"><i class="fas fa-fw fa-sm fa-cogs"></i></a>
                <?php endif ?>

                <?php if(in_array('statistics', $data->notification->settings->enabled_methods) && $data->method != 'statistics'): ?>
                    <a href="<?= url('notification/' . $data->notification->notification_id . '/statistics') ?>" class="btn btn-link text-secondary" data-toggle="tooltip" title="<?= l('notification.statistics.link') ?>"><i class="fas fa-fw fa-sm fa-chart-bar"></i></a>
                <?php endif ?>

                <?php if(in_array('data', $data->notification->settings->enabled_methods) && $data->method != 'data'): ?>
                    <a href="<?= url('notification/' . $data->notification->notification_id . '/data') ?>" class="btn btn-link text-secondary" data-toggle="tooltip" title="<?= l('notification.data.link') ?>"><i class="fas fa-fw fa-sm fa-database"></i></a>
                <?php endif ?>

                <div class="dropdown">
                    <button type="button" class="btn btn-link text-secondary dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
                        <i class="fas fa-fw fa-ellipsis-v"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="<?= url('notification/' . $data->notification->notification_id) ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
                        <a href="<?= url('notification/' . $data->notification->notification_id . '/statistics') ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-chart-bar mr-2"></i> <?= l('notification.statistics.link') ?></a>
                        <a href="#" data-toggle="modal" data-target="#notification_duplicate_modal" data-notification-id="<?= $data->notification->notification_id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-clone mr-2"></i> <?= l('global.duplicate') ?></a>
                        <a href="#" data-toggle="modal" data-target="#notification_reset_modal" data-notification-id="<?= $data->notification->notification_id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-redo mr-2"></i> <?= l('global.reset') ?></a>
                        <a href="#" data-toggle="modal" data-target="#notification_delete_modal" data-notification-id="<?= $data->notification->notification_id ?>" data-resource-name="<?= $data->notification->name ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?= \Altum\Alerts::output_alerts() ?>

    <?= $this->views['method'] ?>

</div>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/libraries/pickr.min.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen">
<link href="<?= ASSETS_FULL_URL . 'css/libraries/daterangepicker.min.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<link href="<?= ASSETS_FULL_URL . 'css/pixel.min.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/duplicate_modal.php', ['modal_id' => 'notification_duplicate_modal', 'resource_id' => 'notification_id', 'path' => 'notification/duplicate']), 'modals'); ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'notification',
    'resource_id' => 'notification_id',
    'has_dynamic_resource_name' => true,
    'path' => 'notification/delete'
]), 'modals'); ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/x_reset_modal.php', ['modal_id' => 'notification_reset_modal', 'resource_id' => 'notification_id', 'path' => 'notification/reset']), 'modals', 'notification_reset_modal'); ?>

