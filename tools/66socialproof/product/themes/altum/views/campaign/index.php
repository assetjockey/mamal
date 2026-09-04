<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('campaigns') ?>"><?= l('campaigns.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('campaign.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="row mb-3">
        <div class="col text-truncate">
            <h1 class="h4 text-truncate font-weight-600">
                <span><?= $data->campaign->name ?></span>
            </h1>

            <div class="d-flex align-items-center text-muted">
                <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($data->campaign->domain) ?>" class="img-fluid icon-favicon-small mr-2" loading="lazy" />

                <div class="d-inline-block text-truncate"><?= $data->campaign->domain ?></div>

                <a href="<?= 'https://' . $data->campaign->domain ?>" class="small" target="_blank" rel="noreferrer"><i class="fas fa-fw fa-sm fa-external-link-alt text-muted ml-2"></i></a>
            </div>
        </div>

        <div class="col-auto">
            <div class="d-flex align-items-center">
                <div class="custom-control custom-switch" data-toggle="tooltip" title="<?= l('campaigns.table.is_enabled_tooltip') ?>">
                    <input
                            type="checkbox"
                            class="custom-control-input"
                            id="campaign_is_enabled_<?= $data->campaign->campaign_id ?>"
                            data-row-id="<?= $data->campaign->campaign_id ?>"
                            onchange="ajax_call_helper(event, 'campaigns-ajax', 'is_enabled_toggle')"
                            <?= $data->campaign->is_enabled ? 'checked="checked"' : null ?>
                    >
                    <label class="custom-control-label" for="campaign_is_enabled_<?= $data->campaign->campaign_id ?>"></label>
                </div>

                <?php if($data->method == 'settings'): ?>
                    <a href="<?= url('campaign/' . $data->campaign->campaign_id . '/statistics') ?>" class="btn btn-link text-secondary" data-toggle="tooltip" title="<?= l('statistics.link') ?>"><i class="fas fa-fw fa-sm fa-chart-bar"></i></a>
                <?php endif ?>

                <?php if($data->method == 'statistics'): ?>
                    <a href="<?= url('campaign/' . $data->campaign->campaign_id) ?>" class="btn btn-link text-secondary" data-toggle="tooltip" title="<?= l('campaign.notifications.link') ?>"><i class="fas fa-fw fa-window-maximize"></i></a>
                <?php endif ?>

                <div class="dropdown">
                    <button type="button" class="btn btn-link text-secondary dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
                        <i class="fas fa-fw fa-ellipsis-v"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="<?= url('campaign/' . $data->campaign->campaign_id) ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-pager mr-2"></i> <?= l('global.view') ?></a>
                        <a href="<?= url('campaign/' . $data->campaign->campaign_id . '/statistics') ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-chart-bar mr-2"></i> <?= l('statistics.link') ?></a>

                        <a href="#" data-toggle="modal" data-target="#campaign_update_modal" data-campaign-id="<?= $data->campaign->campaign_id ?>" data-name="<?= $data->campaign->name ?>" data-domain="<?= $data->campaign->domain ?>" data-email-reports="<?= $data->campaign->email_reports ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>

                        <a
                                href="#"
                                data-toggle="modal"
                                data-target="#campaign_pixel_key_modal"
                                data-pixel-key="<?= $data->campaign->pixel_key ?>"
                                data-base-url="<?= $data->campaign->domain_id ? $data->domains[$data->campaign->domain_id]->scheme . $data->domains[$data->campaign->domain_id]->host . '/' : SITE_URL ?>"
                                class="dropdown-item"
                        ><i class="fas fa-fw fa-sm fa-code mr-2"></i> <?= l('campaign.pixel_key') ?></a>

                        <div <?= $this->user->plan_settings->custom_branding ? null : get_plan_feature_disabled_info() ?>>
                            <a
                                    href="#"
                                    <?php if($this->user->plan_settings->custom_branding): ?>
                                        data-toggle="modal"
                                        data-target="#campaign_custom_branding_modal"
                                        data-campaign-id="<?= $data->campaign->campaign_id ?>"
                                        data-branding-name="<?= e($data->campaign->branding->name ?? '') ?>"
                                        data-branding-url="<?= $data->campaign->branding->url ?? '' ?>"
                                        class="dropdown-item"
                                    <?php else: ?>
                                        class="dropdown-item container-disabled"
                                    <?php endif ?>
                            >
                                <i class="fas fa-fw fa-sm fa-random mr-2"></i> <?= l('campaign.custom_branding') ?>
                            </a>
                        </div>

                        <a href="#" data-toggle="modal" data-target="#campaign_duplicate_modal" data-campaign-id="<?= $data->campaign->campaign_id ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-clone mr-2"></i> <?= l('global.duplicate') ?></a>

                        <a href="#" data-toggle="modal" data-target="#campaign_delete_modal" data-campaign-id="<?= $data->campaign->campaign_id ?>" data-resource-name="<?= $data->campaign->name ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?= \Altum\Alerts::output_alerts() ?>

    <?= $this->views['method'] ?>
</div>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/campaign/campaign_pixel_key_modal.php', ['domains' => $data->domains]), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/campaign/campaign_update_modal.php', ['domains' => $data->domains, 'user' => $this->user, 'notification_handlers' => $data->notification_handlers]), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/campaign/campaign_custom_branding_modal.php'), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/duplicate_modal.php', ['modal_id' => 'notification_duplicate_modal', 'resource_id' => 'notification_id', 'path' => 'notification/duplicate']), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/duplicate_modal.php', ['modal_id' => 'campaign_duplicate_modal', 'resource_id' => 'campaign_id', 'path' => 'campaign/duplicate']), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'campaign',
    'resource_id' => 'campaign_id',
    'has_dynamic_resource_name' => true,
    'path' => 'campaign/delete'
]), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'notification',
    'resource_id' => 'notification_id',
    'has_dynamic_resource_name' => true,
    'path' => 'notification/delete'
]), 'modals'); ?>

<?php ob_start() ?>
<script>
    'use strict';

    <?php if(isset($_GET['pixel_key_modal'])): ?>
    /* Open the pixel key modal */
    $('[data-pixel-key]').trigger('click');
    <?php endif ?>
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
