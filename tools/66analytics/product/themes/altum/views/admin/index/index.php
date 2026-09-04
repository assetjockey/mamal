<?php defined('ALTUMCODE') || die() ?>

<h1 class="h3 mb-4 text-truncate"><?= sprintf(l('admin_index.header'), $this->user->name) ?></h1>

<div class="mb-4 row justify-content-between">
    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('admin_websites.menu') ?></small>
                    </div>
                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-pager text-primary"></i>
                        </span>
                    </div>
                </div>
                <div class="mt-2 text-break">
                    <a href="<?= url('admin/websites') ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="websites">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>

                    <div class="mt-1 small text-muted">
                        <span id="websites_current_month">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                        <?= mb_strtolower(l('global.date.this_month')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('admin_replays.menu') ?></small>
                    </div>
                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-video text-primary"></i>
                        </span>
                    </div>
                </div>
                <div class="mt-2 text-break">
                    <a href="<?= url('admin/replays') ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="replays">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>

                    <div class="mt-1 small text-muted">
                        <span id="replays_current_month">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                        <?= mb_strtolower(l('global.date.this_month')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('admin_heatmaps.menu') ?></small>
                    </div>
                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-fire text-primary"></i>
                        </span>
                    </div>
                </div>
                <div class="mt-2 text-break">
                    <a href="<?= url('admin/heatmaps') ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="heatmaps">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>

                    <div class="mt-1 small text-muted">
                        <span id="heatmaps_current_month">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                        <?= mb_strtolower(l('global.date.this_month')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('admin_index.goals') ?></small>
                    </div>
                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-bullseye text-primary"></i>
                        </span>
                    </div>
                </div>
                <div class="mt-2 text-break">
                    <a href="<?= url('admin/websites') ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="goals">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>

                    <div class="mt-1 small text-muted">
                        <span id="goals_current_month">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                        <?= mb_strtolower(l('global.date.this_month')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('admin_domains.menu') ?></small>
                    </div>
                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-globe text-primary"></i>
                        </span>
                    </div>
                </div>
                <div class="mt-2 text-break">
                    <a href="<?= url('admin/domains') ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="domains">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>

                    <div class="mt-1 small text-muted">
                        <span id="domains_current_month">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                        <?= mb_strtolower(l('global.date.this_month')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('admin_users.menu') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-users text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= url('admin/users') ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="users">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>

                    <div class="mt-1 small text-muted">
                        <span id="users_current_month">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                        <?= mb_strtolower(l('global.date.this_month')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('admin_payments.menu') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-funnel-dollar text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= in_array(settings()->license->type, ['Extended License', 'extended']) ? url('admin/payments') : url('admin/settings/payment') ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="payments">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>

                    <div class="mt-1 small text-muted">
                        <span id="payments_current_month">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                        <?= mb_strtolower(l('global.date.this_month')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('admin_index.payments_total_amount') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-credit-card text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= in_array(settings()->license->type, ['Extended License', 'extended']) ? url('admin/payments') : url('admin/settings/payment') ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="payments_total_amount">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                        <small><?= settings()->payment->default_currency ?></small>
                    </a>

                    <div class="mt-1 small text-muted">
                        <span id="payments_amount_current_month">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                        <?= settings()->payment->default_currency ?> <?= mb_strtolower(l('global.date.this_month')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="map-card mb-5">
    <div class="map-header d-flex justify-content-end align-items-center">
        <div>
            <span class="badge badge-success" data-toggle="tooltip" title="<?= l('admin_index.active_users_tooltip') ?>">
                <i class="fas fa-xs fa-fw fa-circle fa-fade mr-1"></i>
                <span id="active_users" data-translation="<?= l('admin_index.active_users') ?>"><?= l('global.loading') ?></span>
            </span>
        </div>
    </div>

    <div id="online_map" class="map-canvas"></div>
</div>

<?php ob_start() ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<style>
    .map-card {
        position: relative;
        overflow: hidden;
        border-radius: calc(var(--border-radius) * 2);
        background: var(--gray-100);
    }

    .map-canvas {
        height: 320px;
        width: 100%;
    }

    .map-header {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1001;
        padding: 1.25rem 1.25rem 2.5rem 1.25rem;
        pointer-events: none;
    }

    .map-header > div {
        pointer-events: auto;
    }

    .map-card .leaflet-tile-pane {
        filter: brightness(0.95) contrast(1.15) saturate(1.1);
    }

    .map-card .leaflet-control-attribution {
        font-size: 10px;
        background: rgba(255, 255, 255, 0.75) !important;
        border-radius: calc(var(--border-radius) * 2);
    }

    .map-card .leaflet-control-zoom,
    .map-card .leaflet-control-reset {
        border: 0 !important;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
    }

    .map-card .leaflet-control-zoom a,
    .map-card .leaflet-control-reset a {
        width: 34px;
        height: 34px;
        line-height: 34px;
        border: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        text-decoration: none;
    }

    .map-card .leaflet-control-reset {
        margin-top: -10px;
    }

    .online-user-marker {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: rgba(59, 130, 246, 0.95);
        border: 2px solid rgba(255, 255, 255, 0.95);
        box-shadow: 0 0 0 8px rgba(59, 130, 246, 0.16);
    }

    .marker-cluster-small,
    .marker-cluster-medium,
    .marker-cluster-large {
        background: rgba(37, 99, 235, 0.18);
    }

    .marker-cluster-small div,
    .marker-cluster-medium div,
    .marker-cluster-large div {
        background: rgba(37, 99, 235, 0.88);
        color: white;
        font-weight: 700;
        border: 2px solid rgba(255, 255, 255, 0.95);
        box-shadow: 0 0 0 10px rgba(37, 99, 235, 0.10);
    }

    .map-popup {
        min-width: 180px;
    }

    .map-popup-title {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }

    .map-popup-subtitle {
        margin-top: 4px;
        font-size: 12px;
        color: #64748b;
    }

    .map-popup-users {
        margin-top: 10px;
        font-size: 12px;
        color: #334155;
    }

    .map-popup-link {
        margin-top: 10px;
    }

	.marker-cluster span {
		line-height: 26px !important;
	}

	.leaflet-popup-content-wrapper, .leaflet-popup-tip {
        box-shadow: none !important;
	}

	.leaflet-popup-content-wrapper {
        border-radius: var(--border-radius);
    }

	.leaflet-popup-content {
        margin: 1rem;
    }

    .leaflet-container {
        font-family: inherit !important;
    }

	.leaflet-container a.leaflet-popup-close-button {
		margin: .5rem .5rem 0 0;
	}
</style>

<script defer>


    let default_center = [22, 10];
    let default_zoom = 2;
    let marker_bounds = [];

    let online_map = L.map('online_map', {
        center: default_center,
        zoom: default_zoom,
        minZoom: 2,
        scrollWheelZoom: true,
        dragging: true,
        doubleClickZoom: true,
        boxZoom: true,
        keyboard: false,
        tap: false,
        touchZoom: true,
        zoomControl: true,
        worldCopyJump: true
    });

    online_map.attributionControl.remove();

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(online_map);

    let cluster_group = L.markerClusterGroup({
        showCoverageOnHover: false,
        spiderfyOnMaxZoom: true,
        removeOutsideVisibleBounds: true,
        maxClusterRadius: 38
    });

    let reset_map_view = () => {
        if(marker_bounds.length) {
            online_map.fitBounds(L.latLngBounds(marker_bounds), {
                padding: [40, 40],
                maxZoom: 3
            });
        } else {
            online_map.setView(default_center, default_zoom);
        }
    };

    let ResetViewControl = L.Control.extend({
        options: {
            position: 'topleft'
        },

        onAdd: function() {
            let container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-reset');
            let button = L.DomUtil.create('a', '', container);

            button.href = '#';
            button.title = 'Reset zoom';
            button.setAttribute('role', 'button');
            button.setAttribute('aria-label', 'Reset zoom');
            button.innerHTML = '<i class="fas fa-fw fa-expand fa-sm"></i>';

            L.DomEvent.disableClickPropagation(container);
            L.DomEvent.on(button, 'click', function(event) {
                L.DomEvent.preventDefault(event);
                reset_map_view();
            });

            return container;
        }
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<div class="mb-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
        <h2 class="h4 mb-3 mb-md-0 text-truncate"><i class="fas fa-fw fa-xs fa-users text-primary-900 mr-2"></i> <?= l('admin_index.users') ?></h2>
    </div>

    <?php $result = database()->query("SELECT * FROM `users` ORDER BY `user_id` DESC LIMIT 5"); ?>
    <div class="table-responsive table-custom-container">
        <table class="table table-custom">
            <thead>
            <tr>
                <th><?= l('global.user') ?></th>
                <th><?= l('global.status') ?></th>
                <th><?= l('admin_users.plan_id') ?></th>
                <th><?= l('global.details') ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_object()): ?>
                <?php //ALTUMCODE:DEMO if(DEMO) {$row->email = 'hidden@demo.com'; $row->name = 'hidden on demo';} ?>
                <?php if(!isset($data->plans[$row->plan_id])) $data->plans[$row->plan_id] = (new \Altum\Models\Plan())->get_plan_by_id($row->plan_id) ?>
                <tr>
                    <td class="text-nowrap">
                        <div class="d-flex">
                            <a href="<?= url('admin/user-view/' . $row->user_id) ?>">
                                <img src="<?= get_user_avatar($row->avatar, $row->email) ?>" class="table-avatar mr-3" alt="" />
                            </a>

                            <div class="d-flex flex-column">
                                <div>
                                    <a href="<?= url('admin/user-view/' . $row->user_id) ?>" <?= $row->type == 1 ? 'class="font-weight-bold" data-toggle="tooltip" title="' . l('admin_users.type_admin') . '"' : null ?>><?= $row->name ?></a>
                                </div>

                                <span class="small text-muted"><?= $row->email ?></span>
                            </div>
                        </div>
                    </td>
                    <td class="text-nowrap">
                        <?php if($row->status == 0): ?>
                            <a href="<?= url('admin/users?status=0') ?>" class="badge badge-warning"><i class="fas fa-fw fa-sm fa-eye-slash mr-1"></i> <?= l('admin_users.status_unconfirmed') ?></a>
                        <?php elseif($row->status == 1): ?>
                            <a href="<?= url('admin/users?status=1') ?>" class="badge badge-success"><i class="fas fa-fw fa-sm fa-check mr-1"></i> <?= l('admin_users.status_active') ?></a>
                        <?php elseif($row->status == 2): ?>
                            <a href="<?= url('admin/users?status=2') ?>" class="badge badge-light"><i class="fas fa-fw fa-sm fa-times mr-1"></i> <?= l('admin_users.status_disabled') ?></a>
                        <?php endif ?>
                    </td>
                    <td class="text-nowrap">
                        <div class="d-flex flex-column">
                            <div>
                                <a href="<?= url('admin/plan-update/' . $row->plan_id) ?>" class="badge badge-light"><?= $data->plans[$row->plan_id]->name ?></a>
                            </div>

                            <?php if($row->plan_id != 'free'): ?>
                                <div>
                                    <small class="text-muted" data-toggle="tooltip" title="<?= l('admin_users.plan_expiration_date') ?>"><?= \Altum\Date::get($row->plan_expiration_date, 1) ?></small>
                                </div>
                            <?php endif ?>
                        </div>
                    </td>
                    <td class="text-nowrap">
                        <div class="d-flex align-items-center">
                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('admin_users.datetime') . '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>' ?>">
                                <i class="fas fa-fw fa-calendar text-muted"></i>
                            </span>

                            <a href="<?= url('admin/users?source=' . $row->source) ?>" class="mr-2" data-toggle="tooltip" title="<?= l('admin_users.source.' . $row->source) ?>">
                                <i class="fas fa-fw fa-sign-in-alt text-muted"></i>
                            </a>

                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('admin_users.last_activity') . '<br />' . \Altum\Date::get($row->last_activity, 2) . '<br /><small>' . \Altum\Date::get($row->last_activity, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_activity) . ')</small>' ?>">
                                <i class="fas fa-fw fa-history text-muted"></i>
                            </span>

                            <span class="mr-2" data-toggle="tooltip" title="<?= sprintf(l('admin_users.table.total_logins'), nr($row->total_logins)) ?>">
                                <i class="fas fa-fw fa-user-clock text-muted"></i>
                            </span>

                            <a href="<?= url('admin/users?continent_code=' . $row->continent_code) ?>" class="mr-2" data-toggle="tooltip" title="<?= get_continent_from_continent_code($row->continent_code ?? l('global.unknown')) ?>">
                                <i class="fas fa-fw fa-globe-europe text-muted"></i>
                            </a>

                            <a href="<?= url('admin/users?country=' . $row->country) ?>">
                                <?php if($row->country): ?>
                                    <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($row->country) . '.svg' ?>" class="icon-favicon mr-2" data-toggle="tooltip" title="<?= get_country_from_country_code($row->country) ?>" />
                                <?php else: ?>
                                    <span class="mr-2" data-toggle="tooltip" title="<?= l('global.unknown') ?>">
                                        <i class="fas fa-fw fa-flag text-muted"></i>
                                    </span>
                                <?php endif ?>
                            </a>

                            <a href="<?= url('admin/users?city_name=' . $row->city_name) ?>" class="mr-2" data-toggle="tooltip" title="<?= $row->city_name ?? l('global.unknown') ?>">
                                <i class="fas fa-fw fa-city text-muted"></i>
                            </a>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex justify-content-end">
                            <?= include_view(THEME_PATH . 'views/admin/users/admin_user_dropdown_button.php', ['id' => $row->user_id, 'resource_name' => $row->name]) ?>
                        </div>
                    </td>
                </tr>
            <?php endwhile ?>

            <tr>
                <td colspan="5">
                    <a href="<?= url('admin/users') ?>" class="text-muted text-decoration-none small">
                        <i class="fas fa-angle-right fa-sm fa-fw mr-1"></i> <?= l('global.view_more') ?>
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<?php if(in_array(settings()->license->type, ['SPECIAL', 'Extended License', 'extended'])): ?>
    <?php $result = database()->query("SELECT `payments`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar` FROM `payments` LEFT JOIN `users` ON `payments`.`user_id` = `users`.`user_id` ORDER BY `id` DESC LIMIT 5"); ?>

    <?php if($result->num_rows): ?>
        <div class="mb-5">
            <h2 class="h4 mb-4"><i class="fas fa-fw fa-xs fa-credit-card text-primary-900 mr-2"></i> <?= l('admin_index.payments') ?></h2>

            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th><?= l('global.user') ?></th>
                        <th><?= l('admin_payments.plan') ?></th>
                        <th><?= l('admin_payments.total_amount') ?></th>
                        <th><?= l('global.type') ?></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while($row = $result->fetch_object()): ?>

                        <?php //ALTUMCODE:DEMO if(DEMO) {$row->email = $row->user_email = 'hidden@demo.com'; $row->name = $row->user_name = 'hidden on demo';} ?>
                        <?php $row->taxes_ids = json_decode($row->taxes_ids ?? ''); ?>
                        <?php $row->refunds = json_decode($row->refunds ?? '[]'); ?>

                        <tr>
                            <td class="text-nowrap">
                                <div class="d-flex align-items-center">
                                    <?php if($row->user_name || $row->user_email): ?>
                                        <a href="<?= url('admin/user-view/' . $row->user_id) ?>">
                                            <img src="<?= get_user_avatar($row->user_avatar, $row->user_email) ?>" referrerpolicy="no-referrer" loading="lazy" class="table-avatar mr-3" alt="" />
                                        </a>

                                        <div class="d-flex flex-column">
                                            <div>
                                                <a href="<?= url('admin/user-view/' . $row->user_id) ?>"><?= $row->user_name ?></a>
                                            </div>

                                            <span class="text-muted small"><?= $row->user_email ?></span>
                                        </div>
                                    <?php else: ?>
                                        <img src="<?= get_user_avatar($row->user_avatar, $row->user_email) ?>" referrerpolicy="no-referrer" loading="lazy" class="table-avatar mr-3" alt="" />

                                        <div class="text-muted">
                                            <?= l('global.unknown') ?>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <?php if(isset($data->plans[$row->plan_id ?? ''])): ?>
                                    <a href="<?= url('admin/plan-update/' . $row->plan_id) ?>" class="badge badge-light">
                                        <?= $data->plans[$row->plan_id]->name ?>
                                    </a>
                                <?php else: ?>
                                    <span class="badge badge-light"><?= $row->plan->name ?? l('global.unknown') ?></span>
                                <?php endif ?>
                            </td>

                            <td class="text-nowrap">
                                <?php if($row->status == 'paid'): ?>
                                    <a href="<?= url('admin/payments?status=' . $row->status) ?>" class="badge badge-success mr-1" data-toggle="tooltip" title="<?= l('admin_payments.status.' . $row->status) ?>"><i class="fas fa-fw fa-sm fa-check"></i></a>
                                <?php elseif($row->status == 'pending'): ?>
                                    <a href="<?= url('admin/payments?status=' . $row->status) ?>" class="badge badge-warning mr-1" data-toggle="tooltip" title="<?= l('admin_payments.status.' . $row->status) ?>"><i class="fas fa-fw fa-sm fa-spinner fa-spin"></i></a>
                                <?php elseif($row->status == 'cancelled'): ?>
                                    <a href="<?= url('admin/payments?status=' . $row->status) ?>" class="badge badge-danger mr-1" data-toggle="tooltip" title="<?= l('admin_payments.status.' . $row->status) ?>"><i class="fas fa-fw fa-sm fa-times"></i></a>
                                <?php elseif($row->status == 'refunded'): ?>
                                    <a href="<?= url('admin/payments?status=' . $row->status) ?>" class="badge badge-light mr-1" data-toggle="tooltip" title="<?= $row->refunded_total == $row->total_amount ? l('admin_payments.status.fully_refunded') . ($row->refunds[0]->origin == 'chargeback' ? ' &bullet; ' . l('admin_payment_refund_modal.origin.chargeback') : null) : l('admin_payments.status.partially_refunded') ?>"><i class="fas fa-fw fa-sm fa-redo"></i></a>
                                <?php endif ?>

                                <span class="badge badge-success ml-1"><?= nr($row->total_amount, 2) . ' ' . $row->currency ?></span>
                            </td>

                            <td class="text-nowrap">
                                <?php if($row->type == 'one_time'): ?>
                                    <a href="<?= url('admin/payments?type=' . $row->type) ?>" class="badge badge-info mr-1" data-toggle="tooltip" title="<?= l('pay.custom_plan.' . $row->type . '_type') ?>"><i class="fas fa-fw fa-sm fa-bolt"></i></a>
                                <?php elseif($row->type == 'recurring'): ?>
                                    <a href="<?= url('admin/payments?type=' . $row->type) ?>" class="badge badge-primary mr-1" data-toggle="tooltip" title="<?= l('pay.custom_plan.' . $row->type . '_type') ?>"><i class="fas fa-fw fa-sm fa-sync fa-spin"></i></a>
                                <?php endif ?>

                                <span class="small text-muted"><?= l('pay.custom_plan.' . $row->frequency) ?></span>
                            </td>

                            <td class="text-nowrap">
                                <a href="<?= url('admin/payments?processor=' . $row->processor) ?>" class="badge badge-light">
                                    <i class="<?= $data->payment_processors[$row->processor]['icon'] ?> fa-fw mr-1" style="--brand-color: <?= $data->payment_processors[$row->processor]['color'] ?>;--brand-color-dark: <?= $data->payment_processors[$row->processor]['dark_color'] ?>; color: var(--brand-color)" data-custom-colors></i>
                                    <?= l('pay.custom_plan.' . $row->processor) ?>
                                </a>
                            </td>

                            <td class="text-nowrap">
                                <span class="mr-2 <?= $row->code ? null : 'opacity-0' ?>" data-toggle="tooltip" title="<?= $row->code ? $row->code . ' (-' . nr($row->discount_amount, 2) . ' ' . $row->currency . ')' : null ?>">
                                    <i class="fas fa-fw fa-sm fa-tag text-muted"></i>
                                </span>

                                <?php
                                $taxes_html = null;
                                if(count($row->taxes_ids ?? [])) {
                                    $taxes_html = l('admin_taxes.menu') . ': ';
                                    foreach($row->taxes_ids as $tax_id) {
                                        $taxes_html .= '<a href=\'' . url('admin/tax-update/' . $tax_id) . '\' target=\'_blank\' class=\'mr-1\'>' . $tax_id . '</a>';
                                    }
                                }
                                ?>
                                <a href="#" onclick="return false;" class="mr-2 text-decoration-none <?= $taxes_html ? null : 'opacity-0' ?>" data-toggle="popover" data-placement="top" data-container="body" data-html="true" data-content="<?= $taxes_html ?>">
                                    <i class="fas fa-fw fa-sm fa-paperclip text-muted"></i>
                                </a>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                    <i class="fas fa-fw fa-calendar text-muted"></i>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/admin/payments/admin_payment_dropdown_button.php', [
                                            'id' => $row->id,
                                            'payment_proof' => $row->payment_proof,
                                            'processor' => $row->processor,
                                            'status' => $row->status,
                                            'currency' => $row->currency,
                                            'refund_remaining_amount' => number_format($row->total_amount - $row->refunded_total, 2, '.', ''),
                                    ]) ?>
                                </div>
                            </td>
                        </tr>

                    <?php endwhile ?>

                    <tr>
                        <td colspan="6">
                            <a href="<?= url('admin/payments') ?>" class="text-muted text-decoration-none small">
                                <i class="fas fa-angle-right fa-sm fa-fw mr-1"></i> <?= l('global.view_more') ?>
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif ?>
<?php endif ?>

<?php if(settings()->internal_notifications->admins_is_enabled): ?>
    <?php if($data->internal_notifications): ?>
        <div class="mb-5">
            <h2 class="h4 mb-4"><i class="fas fa-fw fa-xs fa-bell text-primary-900 mr-2"></i> <?= l('admin_index.admins_notifications') ?></h2>

            <div class="card mb-5">
                <div class="card-body py-2">
                    <div>
                        <?php foreach($data->internal_notifications as $notification): ?>
                            <?php //ALTUMCODE:DEMO if(DEMO) {$notification->title = $notification->description = 'hidden on demo';} ?>

                            <div class="bg-gray-100 p-3 my-3 rounded <?= $notification->is_read ? null : 'border border-info' ?> position-relative">
                                <div class="d-flex align-items-center">
                                    <div class="p-3 bg-gray-50 mr-3 rounded">
                                        <i class="<?= $notification->icon ?> fa-fw fa-lg text-primary-900"></i>
                                    </div>

                                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between flex-fill">
                                        <div class="d-flex flex-column">
                                            <div class="font-weight-bold mb-1">
                                                <?php if($notification->url): ?>
                                                    <a href="<?= $notification->url ?>" class="stretched-link text-decoration-none text-body"><?= $notification->title ?></a>
                                                <?php else: ?>
                                                    <?= $notification->title ?>
                                                <?php endif ?>
                                            </div>

                                            <small class="text-muted"><?= $notification->description ?></small>
                                        </div>

                                        <div>
                                            <small class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($notification->datetime, 1) ?>"><?= \Altum\Date::get_timeago($notification->datetime) ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>
<?php endif ?>

<div class="row justify-content-between">
    <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body text-truncate">
                <small class="text-muted"><i class="fas fa-fw fa-sm fa-code mr-1"></i> <?= PRODUCT_NAME ?></small>

                <div class="mt-2"><span class="h6"><?= 'v' . PRODUCT_VERSION ?></span></div>
            </div>

            <div class="pr-4 d-flex flex-column justify-content-center">
                <a href="<?= PRODUCT_URL ?>" class="stretched-link">
                    <i class="fas fa-fw fa-angle-right text-gray-500"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body text-truncate">
                <small class="text-muted"><i class="fas fa-fw fa-sm fa-book mr-1"></i> Read documentation</small>

                <div class="mt-2"><span class="h6">Docs</span></div>
            </div>

            <div class="pr-4 d-flex flex-column justify-content-center">
                <a href="<?= PRODUCT_DOCUMENTATION_URL ?>" class="stretched-link" target="_blank">
                    <i class="fas fa-fw fa-angle-right text-gray-500"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body text-truncate">
                <small class="text-muted"><i class="fas fa-fw fa-sm fa-history mr-1"></i> Read changelog</small>

                <div class="mt-2"><span class="h6">Changelog</span></div>
            </div>

            <div class="pr-4 d-flex flex-column justify-content-center">
                <a href="<?= PRODUCT_CHANGELOG_URL ?>" class="stretched-link" target="_blank">
                    <i class="fas fa-fw fa-angle-right text-gray-500"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body text-truncate">
                <small class="text-muted"><i class="fas fa-fw fa-sm fa-globe mr-1"></i> Official website</small>

                <div class="mt-2"><span class="h6">altumcode.com</span></div>
            </div>

            <div class="pr-4 d-flex flex-column justify-content-center">
                <a href="https://altumco.de/site" class="stretched-link" target="_blank">
                    <i class="fas fa-fw fa-angle-right text-gray-500"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body text-truncate">
                <small class="text-muted"><i class="fas fa-fw fa-sm fa-envelope mr-1"></i> Get support</small>

                <div class="mt-2"><span class="h6">support@altumcode.com</span></div>
            </div>

            <div class="pr-4 d-flex flex-column justify-content-center">
                <a href="https://altumcode.com/contact" class="stretched-link" target="_blank">
                    <i class="fas fa-fw fa-angle-right text-gray-500"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body text-truncate">
                <small class="text-muted"><i class="fab fa-fw fa-sm fa-twitter mr-1"></i> X</small>

                <div class="mt-2"><span class="h6">@altumcode</span></div>
            </div>

            <div class="pr-4 d-flex flex-column justify-content-center">
                <a href="https://altumco.de/twitter" class="stretched-link" target="_blank">
                    <i class="fas fa-fw fa-angle-right text-gray-500"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    $('[data-toggle="popover"]').popover();

    (async function fetch_statistics() {
        /* Send request to server */
        let response = await fetch(`${url}admin/index/get_stats_ajax`, {
            method: 'get',
        });

        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            /* :)  */
        }


        if(!response.ok) {
            /* :)  */
        }

        if(data.status == 'error') {
            /* :)  */
        } else if(data.status == 'success') {
            document.querySelector('#websites').innerHTML = data.details.websites ? nr(data.details.websites) : 0;
            document.querySelector('#websites_current_month').innerHTML = data.details.websites_current_month ? nr(data.details.websites_current_month) : 0;

            document.querySelector('#heatmaps').innerHTML = data.details.heatmaps ? nr(data.details.heatmaps) : 0;
            document.querySelector('#heatmaps_current_month').innerHTML = data.details.heatmaps_current_month ? nr(data.details.heatmaps_current_month) : 0;

            document.querySelector('#goals').innerHTML = data.details.goals ? nr(data.details.goals) : 0;
            document.querySelector('#goals_current_month').innerHTML = data.details.goals_current_month ? nr(data.details.goals_current_month) : 0;

            document.querySelector('#replays').innerHTML = data.details.replays ? nr(data.details.replays) : 0;
            document.querySelector('#replays_current_month').innerHTML = data.details.replays_current_month ? nr(data.details.replays_current_month) : 0;

            document.querySelector('#domains').innerHTML = data.details.domains ? nr(data.details.domains) : 0;
            document.querySelector('#domains_current_month').innerHTML = data.details.domains_current_month ? nr(data.details.domains_current_month) : 0;

            document.querySelector('#payments_total_amount').innerHTML = data.details.payments_total_amount ? nr(data.details.payments_total_amount) : 0;
            document.querySelector('#users_current_month').innerHTML = data.details.users_current_month ? nr(data.details.users_current_month) : 0;

            document.querySelector('#users').innerHTML = data.details.users ? nr(data.details.users) : 0;
            document.querySelector('#payments_current_month').innerHTML = data.details.payments_current_month ? nr(data.details.payments_current_month) : 0;

            document.querySelector('#payments').innerHTML = data.details.payments ? nr(data.details.payments) : 0;
            document.querySelector('#payments_amount_current_month').innerHTML = data.details.payments_amount_current_month ? nr(data.details.payments_amount_current_month) : 0;

            let active_users = data.details.active_users ? nr(data.details.active_users) : 0;
            document.querySelector('#active_users').innerHTML = document.querySelector('#active_users').getAttribute('data-translation').replace('%s', active_users);

            /* Map population */
            let online_users = data.details.online_users || [];

            /* Add the online users */
            online_users.forEach(user => {
                marker_bounds.push([user.latitude, user.longitude]);

                let marker = L.marker([user.latitude, user.longitude], {
                    icon: L.divIcon({
                        className: '',
                        html: '<div class="online-user-marker"></div>',
                        iconSize: [14, 14],
                        iconAnchor: [7, 7]
                    })
                });

                marker.bindPopup(
                    `<div class="map-popup">
                    <div class="map-popup-title">${user.name}</div>
                    <div class="map-popup-subtitle">${user.country}, ${user.city_name}</div>
                    <div class="map-popup-users text-truncate">${user.email}<br>${user.device_type} · ${user.active_ago}</div>
                    <div class="map-popup-link"><a href="${url}admin/user-view/${user.user_id}" class="btn btn-block btn-sm btn-primary text-white" target="_blank" rel="noopener noreferrer"><i class="fas fa-fw fa-xs fa-external-link-alt mr-1"></i> View user</a></div>
                    </div>`
                );

                cluster_group.addLayer(marker);
            });

            online_map.addLayer(cluster_group);
            online_map.addControl(new ResetViewControl());
            reset_map_view();

        }
    })();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
