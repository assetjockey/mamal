<?php defined('ALTUMCODE') || die() ?>

<h1 class="h3 mb-4 text-truncate"><?= sprintf(l('admin_index.header'), $this->user->name) ?></h1>

<div class="mb-4 row justify-content-between">
    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('admin_index.biolink_links') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-hashtag text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= url('admin/links?type=biolink') ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="biolink_links">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>

                    <div class="mt-1 small text-muted">
                        <span id="biolink_links_current_month">
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
                        <small class="text-muted font-weight-bold"><?= l('admin_index.shortened_links') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-link text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= url('admin/links?type=link') ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="shortened_links">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>

                    <div class="mt-1 small text-muted">
                        <span id="shortened_links_current_month">
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
                        <small class="text-muted font-weight-bold"><?= l('admin_index.track_links') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-chart-bar text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= url('admin/statistics/track_links') ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="track_links">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>

                    <div class="mt-1 small text-muted">
                        <span id="track_links_current_month">
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
                        <small class="text-muted font-weight-bold"><?= l('admin_qr_codes.menu') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-qrcode text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= url('admin/qr-codes') ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="qr_codes">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>

                    <div class="mt-1 small text-muted">
                        <span id="qr_codes_current_month">
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
            document.querySelector('#biolink_links').innerHTML = data.details.biolink_links ? nr(data.details.biolink_links) : 0;
            document.querySelector('#biolink_links_current_month').innerHTML = data.details.biolink_links_current_month ? nr(data.details.biolink_links_current_month) : 0;

            document.querySelector('#shortened_links').innerHTML = data.details.shortened_links ? nr(data.details.shortened_links) : 0;
            document.querySelector('#shortened_links_current_month').innerHTML = data.details.shortened_links_current_month ? nr(data.details.shortened_links_current_month) : 0;

            document.querySelector('#track_links').innerHTML = data.details.track_links ? nr(data.details.track_links) : 0;
            document.querySelector('#track_links_current_month').innerHTML = data.details.track_links_current_month ? nr(data.details.track_links_current_month) : 0;

            document.querySelector('#qr_codes').innerHTML = data.details.qr_codes ? nr(data.details.qr_codes) : 0;
            document.querySelector('#qr_codes_current_month').innerHTML = data.details.qr_codes_current_month ? nr(data.details.qr_codes_current_month) : 0;

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

            // TEST MOCKUP DATA
            // let online_users = [{user_id: 13, name: 'Ethan Turner', email: 'ethan@example.com', latitude: 34.0522, longitude: -118.2437, city_name: 'Los Angeles', country_name: 'United States', device_type: 'desktop', active_ago: '33s ago'},
            //     {user_id: 14, name: 'Isabella Morris', email: 'isabella@example.com', latitude: 37.7749, longitude: -122.4194, city_name: 'San Francisco', country_name: 'United States', device_type: 'mobile', active_ago: '1m ago'},
            //     {user_id: 15, name: 'Mason Bailey', email: 'mason@example.com', latitude: 41.8781, longitude: -87.6298, city_name: 'Chicago', country_name: 'United States', device_type: 'desktop', active_ago: '21s ago'},
            //     {user_id: 16, name: 'Amelia Cooper', email: 'amelia@example.com', latitude: 25.7617, longitude: -80.1918, city_name: 'Miami', country_name: 'United States', device_type: 'mobile', active_ago: '47s ago'},
            //     {user_id: 17, name: 'Logan Parker', email: 'logan@example.com', latitude: 43.6532, longitude: -79.3832, city_name: 'Toronto', country_name: 'Canada', device_type: 'desktop', active_ago: '2m ago'},
            //     {user_id: 18, name: 'Harper Evans', email: 'harper@example.com', latitude: 45.5017, longitude: -73.5673, city_name: 'Montreal', country_name: 'Canada', device_type: 'mobile', active_ago: '18s ago'},
            //     {user_id: 19, name: 'Alexander Price', email: 'alexander@example.com', latitude: 19.4326, longitude: -99.1332, city_name: 'Mexico City', country_name: 'Mexico', device_type: 'desktop', active_ago: '56s ago'},
            //     {user_id: 20, name: 'Ella Ramirez', email: 'ella@example.com', latitude: 4.7110, longitude: -74.0721, city_name: 'Bogotá', country_name: 'Colombia', device_type: 'mobile', active_ago: '1m ago'},
            //     {user_id: 21, name: 'Daniel Foster', email: 'daniel@example.com', latitude: -34.6037, longitude: -58.3816, city_name: 'Buenos Aires', country_name: 'Argentina', device_type: 'desktop', active_ago: '42s ago'},
            //     {user_id: 22, name: 'Grace Hughes', email: 'grace@example.com', latitude: -33.9249, longitude: 18.4241, city_name: 'Cape Town', country_name: 'South Africa', device_type: 'mobile', active_ago: '27s ago'},
            //     {user_id: 23, name: 'Sebastian Bennett', email: 'sebastian@example.com', latitude: 30.0444, longitude: 31.2357, city_name: 'Cairo', country_name: 'Egypt', device_type: 'desktop', active_ago: '2m ago'},
            //     {user_id: 24, name: 'Chloe Wood', email: 'chloe@example.com', latitude: 6.5244, longitude: 3.3792, city_name: 'Lagos', country_name: 'Nigeria', device_type: 'mobile', active_ago: '35s ago'},
            //     {user_id: 25, name: 'Jack Richardson', email: 'jack@example.com', latitude: 25.2048, longitude: 55.2708, city_name: 'Dubai', country_name: 'United Arab Emirates', device_type: 'desktop', active_ago: '50s ago'},
            //     {user_id: 26, name: 'Lily Watson', email: 'lily@example.com', latitude: 24.7136, longitude: 46.6753, city_name: 'Riyadh', country_name: 'Saudi Arabia', device_type: 'mobile', active_ago: '1m ago'},
            //     {user_id: 27, name: 'Gabriel Brooks', email: 'gabriel@example.com', latitude: 19.0760, longitude: 72.8777, city_name: 'Mumbai', country_name: 'India', device_type: 'desktop', active_ago: '14s ago'},
            //     {user_id: 28, name: 'Zoe Sanders', email: 'zoe@example.com', latitude: 28.6139, longitude: 77.2090, city_name: 'New Delhi', country_name: 'India', device_type: 'mobile', active_ago: '39s ago'},
            //     {user_id: 29, name: 'Matthew Perry', email: 'matthew@example.com', latitude: 13.7563, longitude: 100.5018, city_name: 'Bangkok', country_name: 'Thailand', device_type: 'desktop', active_ago: '1m ago'},
            //     {user_id: 30, name: 'Hannah Coleman', email: 'hannah@example.com', latitude: 3.1390, longitude: 101.6869, city_name: 'Kuala Lumpur', country_name: 'Malaysia', device_type: 'mobile', active_ago: '23s ago'},
            //     {user_id: 31, name: 'David Jenkins', email: 'david@example.com', latitude: -6.2088, longitude: 106.8456, city_name: 'Jakarta', country_name: 'Indonesia', device_type: 'desktop', active_ago: '45s ago'},
            //     {user_id: 32, name: 'Sofia Powell', email: 'sofia@example.com', latitude: 14.5995, longitude: 120.9842, city_name: 'Manila', country_name: 'Philippines', device_type: 'mobile', active_ago: '2m ago'},
            //     {user_id: 33, name: 'Joseph Long', email: 'joseph@example.com', latitude: 37.5665, longitude: 126.9780, city_name: 'Seoul', country_name: 'South Korea', device_type: 'desktop', active_ago: '31s ago'},
            //     {user_id: 34, name: 'Aria Patterson', email: 'aria@example.com', latitude: 22.3193, longitude: 114.1694, city_name: 'Hong Kong', country_name: 'Hong Kong', device_type: 'mobile', active_ago: '1m ago'},
            //     {user_id: 35, name: 'Samuel Hughes', email: 'samuel@example.com', latitude: 55.7558, longitude: 37.6173, city_name: 'Moscow', country_name: 'Russia', device_type: 'desktop', active_ago: '58s ago'},
            //     {user_id: 36, name: 'Victoria Flores', email: 'victoria@example.com', latitude: 59.3293, longitude: 18.0686, city_name: 'Stockholm', country_name: 'Sweden', device_type: 'mobile', active_ago: '26s ago'},
            //     {user_id: 37, name: 'Owen Bryant', email: 'owen@example.com', latitude: 59.9139, longitude: 10.7522, city_name: 'Oslo', country_name: 'Norway', device_type: 'desktop', active_ago: '1m ago'},
            //     {user_id: 38, name: 'Nora Griffin', email: 'nora@example.com', latitude: 60.1699, longitude: 24.9384, city_name: 'Helsinki', country_name: 'Finland', device_type: 'mobile', active_ago: '17s ago'},
            //     {user_id: 39, name: 'Levi Hayes', email: 'levi@example.com', latitude: 50.0755, longitude: 14.4378, city_name: 'Prague', country_name: 'Czech Republic', device_type: 'desktop', active_ago: '43s ago'},
            //     {user_id: 40, name: 'Layla Myers', email: 'layla@example.com', latitude: 47.4979, longitude: 19.0402, city_name: 'Budapest', country_name: 'Hungary', device_type: 'mobile', active_ago: '36s ago'},
            //     {user_id: 41, name: 'Yuki Tanaka', email: 'yuki.tanaka@example.com', latitude: 35.6762, longitude: 139.6503, city_name: 'Tokyo', country_name: 'Japan', device_type: 'mobile', active_ago: '12s ago'},
            //     {user_id: 42, name: 'Mehmet Kaya', email: 'mehmet.kaya@example.com', latitude: 41.0082, longitude: 28.9784, city_name: 'Istanbul', country_name: 'Turkey', device_type: 'desktop', active_ago: '3m ago'},
            //     {user_id: 43, name: 'Ana Silva', email: 'ana.silva@example.com', latitude: -23.5505, longitude: -46.6333, city_name: 'São Paulo', country_name: 'Brazil', device_type: 'tablet', active_ago: '44s ago'},
            //     {user_id: 44, name: 'Luca Bianchi', email: 'luca.bianchi@example.com', latitude: 41.9028, longitude: 12.4964, city_name: 'Rome', country_name: 'Italy', device_type: 'desktop', active_ago: '1m ago'},
            //     {user_id: 45, name: 'Amina Hassan', email: 'amina.hassan@example.com', latitude: -1.2921, longitude: 36.8219, city_name: 'Nairobi', country_name: 'Kenya', device_type: 'mobile', active_ago: '29s ago'},
            //     {user_id: 46, name: 'Piotr Nowak', email: 'piotr.nowak@example.com', latitude: 52.2297, longitude: 21.0122, city_name: 'Warsaw', country_name: 'Poland', device_type: 'desktop', active_ago: '51s ago'},
            //     {user_id: 47, name: 'Marta Kowalska', email: 'marta.kowalska@example.com', latitude: 50.0647, longitude: 19.9450, city_name: 'Kraków', country_name: 'Poland', device_type: 'mobile', active_ago: '2m ago'},
            //     {user_id: 48, name: 'João Pereira', email: 'joao.pereira@example.com', latitude: 38.7223, longitude: -9.1393, city_name: 'Lisbon', country_name: 'Portugal', device_type: 'tablet', active_ago: '19s ago'},
            //     {user_id: 49, name: 'Fatima Zahra', email: 'fatima.zahra@example.com', latitude: 33.5731, longitude: -7.5898, city_name: 'Casablanca', country_name: 'Morocco', device_type: 'mobile', active_ago: '37s ago'},
            //     {user_id: 50, name: 'Nguyen Minh Anh', email: 'minh.anh.nguyen@example.com', latitude: 21.0285, longitude: 105.8542, city_name: 'Hanoi', country_name: 'Vietnam', device_type: 'desktop', active_ago: '1m ago'},
            //     {user_id: 51, name: 'Oleksandr Melnyk', email: 'oleksandr.melnyk@example.com', latitude: 50.4501, longitude: 30.5234, city_name: 'Kyiv', country_name: 'Ukraine', device_type: 'mobile', active_ago: '16s ago'},
            //     {user_id: 52, name: 'Elena Popescu', email: 'elena.popescu@example.com', latitude: 44.4268, longitude: 26.1025, city_name: 'Bucharest', country_name: 'Romania', device_type: 'desktop', active_ago: '48s ago'},
            //     {user_id: 53, name: 'George Papadopoulos', email: 'george.papadopoulos@example.com', latitude: 37.9838, longitude: 23.7275, city_name: 'Athens', country_name: 'Greece', device_type: 'mobile', active_ago: '2m ago'},
            //     {user_id: 54, name: 'Sven Larsen', email: 'sven.larsen@example.com', latitude: 55.6761, longitude: 12.5683, city_name: 'Copenhagen', country_name: 'Denmark', device_type: 'desktop', active_ago: '34s ago'},
            //     {user_id: 55, name: 'Zuzana Nováková', email: 'zuzana.novakova@example.com', latitude: 48.1486, longitude: 17.1077, city_name: 'Bratislava', country_name: 'Slovakia', device_type: 'mobile', active_ago: '27s ago'},
            //     {user_id: 56, name: 'Andrej Kovač', email: 'andrej.kovac@example.com', latitude: 46.0569, longitude: 14.5058, city_name: 'Ljubljana', country_name: 'Slovenia', device_type: 'tablet', active_ago: '54s ago'},
            //     {user_id: 57, name: 'Nino Beridze', email: 'nino.beridze@example.com', latitude: 41.7151, longitude: 44.8271, city_name: 'Tbilisi', country_name: 'Georgia', device_type: 'mobile', active_ago: '9s ago'},
            //     {user_id: 58, name: 'Arman Sargsyan', email: 'arman.sargsyan@example.com', latitude: 40.1792, longitude: 44.4991, city_name: 'Yerevan', country_name: 'Armenia', device_type: 'desktop', active_ago: '1m ago'},
            //     {user_id: 59, name: 'Leila Al Farsi', email: 'leila.alfarsi@example.com', latitude: 23.5880, longitude: 58.3829, city_name: 'Muscat', country_name: 'Oman', device_type: 'mobile', active_ago: '41s ago'},
            //     {user_id: 60, name: 'Tendai Moyo', email: 'tendai.moyo@example.com', latitude: -17.8252, longitude: 31.0335, city_name: 'Harare', country_name: 'Zimbabwe', device_type: 'desktop', active_ago: '2m ago'},
            //     {user_id: 61, name: 'Chipo Banda', email: 'chipo.banda@example.com', latitude: -15.3875, longitude: 28.3228, city_name: 'Lusaka', country_name: 'Zambia', device_type: 'mobile', active_ago: '22s ago'},
            //     {user_id: 62, name: 'Khaled Ben Ali', email: 'khaled.benali@example.com', latitude: 36.8065, longitude: 10.1815, city_name: 'Tunis', country_name: 'Tunisia', device_type: 'desktop', active_ago: '46s ago'},
            //     {user_id: 63, name: 'Camila Rojas', email: 'camila.rojas@example.com', latitude: -33.4489, longitude: -70.6693, city_name: 'Santiago', country_name: 'Chile', device_type: 'mobile', active_ago: '13s ago'},
            //     {user_id: 64, name: 'Diego Suárez', email: 'diego.suarez@example.com', latitude: -12.0464, longitude: -77.0428, city_name: 'Lima', country_name: 'Peru', device_type: 'desktop', active_ago: '57s ago'},
            //     {user_id: 65, name: 'Valentina Herrera', email: 'valentina.herrera@example.com', latitude: 10.4806, longitude: -66.9036, city_name: 'Caracas', country_name: 'Venezuela', device_type: 'tablet', active_ago: '1m ago'},
            //     {user_id: 66, name: 'Mikkel Jensen', email: 'mikkel.jensen@example.com', latitude: 56.1629, longitude: 10.2039, city_name: 'Aarhus', country_name: 'Denmark', device_type: 'mobile', active_ago: '24s ago'},
            //     {user_id: 67, name: 'Siobhán Murphy', email: 'siobhan.murphy@example.com', latitude: 53.3498, longitude: -6.2603, city_name: 'Dublin', country_name: 'Ireland', device_type: 'desktop', active_ago: '38s ago'},
            //     {user_id: 68, name: 'Alina Ionescu', email: 'alina.ionescu@example.com', latitude: 46.7712, longitude: 23.6236, city_name: 'Cluj-Napoca', country_name: 'Romania', device_type: 'mobile', active_ago: '11s ago'},
            //     {user_id: 69, name: 'Bence Horváth', email: 'bence.horvath@example.com', latitude: 46.2530, longitude: 20.1414, city_name: 'Szeged', country_name: 'Hungary', device_type: 'desktop', active_ago: '2m ago'},
            //     {user_id: 70, name: 'Inês Martins', email: 'ines.martins@example.com', latitude: 41.1579, longitude: -8.6291, city_name: 'Porto', country_name: 'Portugal', device_type: 'mobile', active_ago: '32s ago'}
            // ]

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
