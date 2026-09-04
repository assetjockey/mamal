<?php defined('ALTUMCODE') || die() ?>

<?php if(settings()->main->breadcrumbs_is_enabled): ?>
    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('admin/users') ?>"><?= l('admin_users.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('admin_user_view.breadcrumb') ?></li>
        </ol>
    </nav>
<?php endif ?>

<div class="map-card mb-4">
    <div class="map-overlay"></div>

    <div class="map-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center text-truncate">
            <img src="<?= get_user_avatar($data->user->avatar, $data->user->email) ?>" class="table-avatar mr-3" alt="" />

            <div class="mr-3 text-truncate">
                <h1 class="h4 mb-0 text-truncate"><?= $data->user->name ?></h1>
                <p class="mb-0 small font-weight-500"><?= $data->user->email ?></p>
            </div>
        </div>

        <?= include_view(THEME_PATH . 'views/admin/users/admin_user_dropdown_button.php', ['id' => $data->user->user_id, 'resource_name' => $data->user->name, 'button_text_class' => 'btn-light text-secondary']) ?>
    </div>

    <div id="user_map" class="map-canvas"></div>
</div>

<?php ob_start() ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<style>
	.map-card {
		position: relative;
		overflow: hidden;
		border-radius: calc(var(--border-radius) * 2);
		background: var(--gray-100);
	}

	.map-canvas {
		height: 220px;
		width: 100%;
	}

	.map-header {
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		z-index: 1001;
		padding: 1.25rem 1.25rem 2.5rem 1.25rem;
	}

	.map-overlay {
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		height: 110px;
		z-index: 1000;
		pointer-events: none;
		background: linear-gradient(to bottom, rgba(0, 0, 0, 0.72) 0%, rgba(0, 0, 0, 0.42) 45%, rgba(0, 0, 0, 0) 100%);
	}

	.map-card .leaflet-control-attribution {
		font-size: 10px;
		background: rgba(255, 255, 255, 0.75) !important;
		border-radius: calc(var(--border-radius) * 2);
	}

	.map-card h1 {
		color: white;
	}

	.map-card p {
		color: white;
	}
</style>

<script defer>
    let user_location = {
        latitude: <?= json_encode($data->user_location['latitude']) ?>,
        longitude: <?= json_encode($data->user_location['longitude']) ?>,
        precision: 'city'
    };

    let zoom_level = 10;
    let circle_radius = 7000;

    if (user_location.precision === 'country') {
        zoom_level = 5;
        circle_radius = 90000;
    } else if (user_location.precision === 'city') {
        zoom_level = 10;
        circle_radius = 7000;
    } else if (user_location.precision === 'exact') {
        zoom_level = 13;
        circle_radius = 1500;
    }

    let user_map = L.map('user_map', {
        center: [user_location.latitude, user_location.longitude],
        zoom: zoom_level,
        scrollWheelZoom: false,
        dragging: false,
        doubleClickZoom: false,
        boxZoom: false,
        keyboard: false,
        tap: false,
        touchZoom: false,
        zoomControl: false
    });

    user_map.attributionControl.remove();

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(user_map);

    L.circle([user_location.latitude, user_location.longitude], {
        radius: circle_radius,
        color: '#3b82f6',
        weight: 1,
        opacity: 0.7,
        fillColor: '#60a5fa',
        fillOpacity: 0.1,
        interactive: false
    }).addTo(user_map);
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?= \Altum\Alerts::output_alerts() ?>

<?php //ALTUMCODE:DEMO if(DEMO) {$data->user->email = 'hidden@demo.com'; $data->user->name = $data->user->ip = $data->user->api_key = 'hidden on demo';} ?>

<div class="mt-4 mb-5">
    <div class="row m-n2">
        <?php
        $status_map = [
                1 => [
                        'text' => l('admin_users.status_active'),
                        'icon' => 'fa-check-circle',
                        'text_class' => 'text-success',
                ],
                0 => [
                        'text' => l('admin_users.status_unconfirmed'),
                        'icon' => 'fa-eye-slash',
                        'text_class' => 'text-warning',
                ],
                2 => [
                        'text' => l('admin_users.status_disabled'),
                        'icon' => 'fa-times-circle',
                        'text_class' => 'text-danger',
                ],
        ];

        $user_status = $status_map[$data->user->status]
        ?>

        <div class="col-12 col-sm-6 col-xl-3 p-2 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('global.status') ?>">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm <?= $user_status['icon'] ?> <?= $user_status['text_class'] ?>"></i>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex flex-column justify-content-center">
                    <div class="text-truncate font-size-little-small font-weight-450">
                        <?= $user_status['text'] ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 p-2 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('admin_users.plan_id') ?>">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <a href="<?= url('admin/plan-update/' . $data->user->plan->plan_id) ?>" class="stretched-link">
                        <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                            <i class="fas fa-fw fa-sm fa-box-open text-primary"></i>
                        </div>
                    </a>
                </div>
                <div class="card-body text-truncate d-flex flex-column justify-content-center">
                    <div class="text-truncate font-size-little-small font-weight-450">
                        <?= $data->user->plan->name ?>
                    </div>
                    <div class="small">
                        <?= \Altum\Date::get($data->user->plan_expiration_date, 6) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 p-2 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('global.datetime') ?>">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-calendar text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex flex-column justify-content-center">
                    <div class="text-truncate font-size-little-small font-weight-450">
                        <?= \Altum\Date::get($data->user->datetime, 6) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 p-2 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('admin_users.last_activity') ?>">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-clock-rotate-left text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex flex-column justify-content-center">
                    <div class="text-truncate font-size-little-small font-weight-450">
                        <?= $data->user->last_activity ? \Altum\Date::get_timeago($data->user->last_activity) : l('global.na') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 p-2 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= get_country_from_country_code($data->user->country) ?>">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <img src="<?= ASSETS_FULL_URL . 'images/countries/' . ($data->user->country ? mb_strtolower($data->user->country) : 'unknown') . '.svg' ?>" class="img-fluid icon-favicon-small" />
                    </div>
                </div>
                <div class="card-body text-truncate d-flex flex-column justify-content-center">
                    <div class="text-truncate font-size-little-small font-weight-450">
						<?= $data->user->city_name ?: l('global.na') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 p-2 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('admin_users.source') ?>">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-sign-in-alt text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex flex-column justify-content-center">
                    <div class="text-truncate font-size-little-small font-weight-450">
						<?= l('admin_users.source.' .  $data->user->source) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 p-2 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('admin_users.total_logins') ?>">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-sync-alt text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex flex-column justify-content-center">
                    <div class="text-truncate font-size-little-small font-weight-450">
						<?= nr($data->user->total_logins) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 p-2 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('admin_users.total_earned') ?>">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <i class="fas fa-fw fa-sm fa-money-check-alt text-primary"></i>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex flex-column justify-content-center">
                    <div class="text-truncate font-size-little-small font-weight-450">
                        <?= $data->payments_total_earned ? nr($data->payments_total_earned) . ' ' . settings()->payment->default_currency : l('global.na') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="card mb-4">
    <div class="card-body position-relative">
        <h3 class="h6 m-0 d-flex align-items-center justify-content-between">
            <a href="#" class="stretched-link text-reset text-decoration-none" data-toggle="collapse" data-target="#account" aria-expanded="true" aria-controls="account">
				<?= l('admin_user_view.account') ?>
            </a>

            <span class="badge bg-primary-50 text-primary-700">
                <i class="fas fa-fw fa-sm fa-user"></i>
            </span>
        </h3>
    </div>

    <div id="account" class="collapse show">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="user_id" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('admin_users.user_id') ?></label>
                        <input id="user_id" type="text" class="form-control-plaintext" value="<?= $data->user->user_id ?>" readonly />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="type" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-user text-muted mr-1"></i> <?= l('admin_users.type') ?></label>
                        <input id="type" type="text" class="form-control-plaintext" value="<?= $data->user->type ? l('admin_users.type_admin') : l('admin_users.type_user') ?>" readonly />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body position-relative">
        <h3 class="h6 m-0 d-flex align-items-center justify-content-between">
            <a href="#" class="stretched-link text-reset text-decoration-none" data-toggle="collapse" data-target="#security" aria-expanded="false" aria-controls="security">
				<?= l('admin_user_view.security') ?>
            </a>

            <span class="badge bg-primary-50 text-primary-700">
                <i class="fas fa-fw fa-sm fa-shield-alt"></i>
            </span>
        </h3>
    </div>

    <div id="security" class="collapse show">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="twofa_is_enabled" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-lock text-muted mr-1"></i> <?= l('admin_users.twofa_is_enabled') ?></label>
                        <input id="twofa_is_enabled" type="text" class="form-control-plaintext" value="<?= $data->user->twofa_secret ? l('global.yes') : l('global.no') ?>" readonly />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="anti_phishing_code" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-user-shield text-muted mr-1"></i> <?= l('admin_users.anti_phishing_code') ?></label>
                        <input id="anti_phishing_code" type="text" class="form-control-plaintext" value="<?= $data->user->anti_phishing_code ? l('global.yes') : l('global.no') ?>" readonly />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="user_deletion_reminder" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-user-minus text-muted mr-1"></i> <?= l('admin_users.user_deletion_reminder') ?></label>
                        <input id="user_deletion_reminder" type="text" class="form-control-plaintext" value="<?= $data->user->user_deletion_reminder ? l('global.yes') : l('global.no') ?>" readonly />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="api_key" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-laptop-code text-muted mr-1"></i> <?= l('admin_users.api_key') ?></label>
                        <input id="api_key" type="text" class="form-control-plaintext" value="<?= $data->user->api_key ?>" readonly />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body position-relative">
        <h3 class="h6 m-0 d-flex align-items-center justify-content-between">
            <a href="#" class="stretched-link text-reset text-decoration-none" data-toggle="collapse" data-target="#location_device" aria-expanded="false" aria-controls="location_device">
				<?= l('admin_user_view.location_device') ?>
            </a>

            <span class="badge bg-primary-50 text-primary-700">
                <i class="fas fa-fw fa-sm fa-laptop-house"></i>
            </span>
        </h3>
    </div>

    <div id="location_device" class="collapse show">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="ip" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-network-wired text-muted mr-1"></i> <?= l('global.ip') ?></label>
                        <input id="ip" type="text" class="form-control-plaintext" value="<?= $data->user->ip ?>" readonly />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="continent_code" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-globe-europe text-muted mr-1"></i> <?= l('global.continent') ?></label>
                        <input id="continent_code" type="text" class="form-control-plaintext" value="<?= $data->user->continent_code ? get_continent_from_continent_code($data->user->continent_code) : l('global.unknown') ?>" readonly />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="country" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-flag text-muted mr-1"></i> <?= l('global.country') ?></label>
                        <input id="country" type="text" class="form-control-plaintext" value="<?= $data->user->country ? get_country_from_country_code($data->user->country) : l('global.unknown') ?>" readonly />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="city_name" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-city text-muted mr-1"></i> <?= l('global.city') ?></label>
                        <input id="city_name" type="text" class="form-control-plaintext" value="<?= $data->user->city_name ?? l('global.unknown') ?>" readonly />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="device_type" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-laptop text-muted mr-1"></i> <?= l('global.device') ?></label>
                        <input id="device_type" type="text" class="form-control-plaintext" value="<?= $data->user->device_type ? l('global.device.' . $data->user->device_type) : l('global.unknown') ?>" readonly />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="os_name" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-server text-muted mr-1"></i> <?= l('global.os_name') ?></label>
                        <input id="os_name" type="text" class="form-control-plaintext" value="<?= $data->user->os_name ?? l('global.unknown') ?>" readonly />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="browser_name" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-window-restore text-muted mr-1"></i> <?= l('global.browser_name') ?></label>
                        <input id="browser_name" type="text" class="form-control-plaintext" value="<?= $data->user->browser_name ?? l('global.unknown') ?>" readonly />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="browser_language" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-language text-muted mr-1"></i> <?= l('global.browser_language') ?></label>
                        <input id="browser_language" type="text" class="form-control-plaintext" value="<?= $data->user->browser_language ?? l('global.unknown') ?>" readonly />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body position-relative">
        <h3 class="h6 m-0 d-flex align-items-center justify-content-between">
            <a href="#" class="stretched-link text-reset text-decoration-none" data-toggle="collapse" data-target="#preferences" aria-expanded="false" aria-controls="preferences">
				<?= l('admin_user_view.preferences') ?>
            </a>

            <span class="badge bg-primary-50 text-primary-700">
                <i class="fas fa-fw fa-sm fa-sliders-h"></i>
            </span>
        </h3>
    </div>

    <div id="preferences" class="collapse show">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="language" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-language text-muted mr-1"></i> <?= l('global.language') ?></label>
                        <input id="language" type="text" class="form-control-plaintext" value="<?= $data->user->language ?>" readonly />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="timezone" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-clock text-muted mr-1"></i> <?= l('admin_users.timezone') ?></label>
                        <input id="timezone" type="text" class="form-control-plaintext" value="<?= $data->user->timezone ?>" readonly />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="is_newsletter_subscribed" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-mail-bulk text-muted mr-1"></i> <?= l('admin_users.is_newsletter_subscribed') ?></label>
                        <input id="is_newsletter_subscribed" type="text" class="form-control-plaintext" value="<?= $data->user->is_newsletter_subscribed ? l('global.yes') : l('global.no') ?>" readonly />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(in_array(settings()->license->type, ['SPECIAL', 'Extended License', 'extended'])): ?>
    <div class="card mb-4">
        <div class="card-body position-relative">
            <h3 class="h6 m-0 d-flex align-items-center justify-content-between">
                <a href="#" class="stretched-link text-reset text-decoration-none" data-toggle="collapse" data-target="#payment" aria-expanded="true" aria-controls="payment">
					<?= l('admin_user_view.payment') ?>
                </a>

                <span class="badge bg-primary-50 text-primary-700">
                    <i class="fas fa-fw fa-sm fa-credit-card"></i>
                </span>
            </h3>
        </div>

        <div id="payment" class="collapse">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="payment_processor" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-money-check-alt text-muted mr-1"></i> <?= l('admin_users.payment_processor') ?></label>
                            <input id="payment_processor" type="text" class="form-control-plaintext" value="<?= $data->user->payment_processor ? l('pay.custom_plan.' . $data->user->payment_processor) : l('global.none') ?>" readonly />
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="payment_total_amount" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-money-bill-alt text-muted mr-1"></i> <?= l('admin_users.payment_total_amount') ?></label>
                            <input id="payment_total_amount" type="text" class="form-control-plaintext" value="<?= $data->user->payment_total_amount ? nr($data->user->payment_total_amount, 2) . ' ' . $data->user->payment_currency : l('global.none') ?>" readonly />
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="payment_subscription_id" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-hand-holding-usd text-muted mr-1"></i> <?= l('admin_users.payment_subscription_id') ?></label>
                            <input id="payment_subscription_id" type="text" class="form-control-plaintext" value="<?= $data->user->payment_subscription_id ?: l('global.none') ?>" readonly />
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="plan_trial_done" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-calendar-minus text-muted mr-1"></i> <?= l('admin_users.plan_trial_done') ?></label>
                            <input id="plan_trial_done" type="text" class="form-control-plaintext" value="<?= $data->user->plan_trial_done ? l('global.yes') : l('global.no') ?>" readonly />
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="plan_expiry_reminder" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-business-time text-muted mr-1"></i> <?= l('admin_users.plan_expiry_reminder') ?></label>
                            <input id="plan_expiry_reminder" type="text" class="form-control-plaintext" value="<?= $data->user->plan_expiry_reminder ? l('global.yes') : l('global.no') ?>" readonly />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if(\Altum\Plugin::is_active('affiliate')): ?>
    <div class="card mb-4">
        <div class="card-body position-relative">
            <h3 class="h6 m-0 d-flex align-items-center justify-content-between">
                <a href="#" class="stretched-link text-reset text-decoration-none" data-toggle="collapse" data-target="#affiliate" aria-expanded="false" aria-controls="affiliate">
					<?= l('admin_user_view.affiliate') ?>
                </a>

                <span class="badge bg-primary-50 text-primary-700">
                    <i class="fas fa-fw fa-sm fa-users"></i>
                </span>
            </h3>
        </div>

        <div id="affiliate" class="collapse">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="referral_key" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-users text-muted mr-1"></i> <?= l('admin_users.referral_key') ?></label>
                            <input id="referral_key" type="text" class="form-control-plaintext" value="<?= $data->user->referral_key ?>" readonly />
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="referred_by" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-user-plus text-muted mr-1"></i> <?= l('admin_users.referred_by') ?></label>
							<?php if($data->user->referred_by): ?>
                                <div id="referred_by" class="form-control-plaintext">
                                    <a href="<?= url('admin/user-view/' . $data->user->referred_by) ?>"><?= $data->user->referred_by ?></a>
                                </div>
							<?php else: ?>
                                <input id="referred_by" type="text" class="form-control-plaintext" value="<?= l('global.none') ?>" readonly />
							<?php endif ?>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="referred_by_has_converted" class="font-weight-bold"><i class="fas fa-fw fa-sm fa-dollar-sign text-muted mr-1"></i> <?= l('admin_users.referred_by_has_converted') ?></label>
                            <input id="referred_by_has_converted" type="text" class="form-control-plaintext" value="<?= $data->user->referred_by_has_converted ? l('global.yes') : l('global.no') ?>" readonly />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if(in_array(settings()->license->type, ['Extended License', 'extended']) && settings()->payment->is_enabled && settings()->payment->taxes_and_billing_is_enabled): ?>
    <div class="accordion">
        <div class="card">
            <div class="card-body position-relative">
                <h3 class="h6 m-0 d-flex align-items-center justify-content-between">
                    <a href="#" class="stretched-link text-reset text-decoration-none" data-toggle="collapse" data-target="#billing" aria-expanded="true" aria-controls="billing">
                        <?= l('admin_user_view.billing') ?>
                    </a>

                    <span class="badge bg-primary-50 text-primary-700">
                        <i class="fas fa-fw fa-sm fa-circle-info"></i>
                    </span>
                </h3>
            </div>

            <div id="billing" class="collapse">
                <div class="card-body">

                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="billing_type" class="font-weight-bold"><?= l('account.billing.type') ?></label>
                                <input id="billing_type" type="text" class="form-control-plaintext" value="<?= l('account.billing.type_' . $data->user->billing->type) ?>" readonly />
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="billing_name" class="font-weight-bold"><?= l('account.billing.name') ?></label>
                                <input id="billing_name" type="text" name="billing_name" class="form-control-plaintext" value="<?= $data->user->billing->name ?>" readonly />
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="billing_address" class="font-weight-bold"><?= l('account.billing.address') ?></label>
                                <input id="billing_address" type="text" name="billing_address" class="form-control-plaintext" value="<?= $data->user->billing->address ?>" readonly />
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="billing_city" class="font-weight-bold"><?= l('global.city') ?></label>
                                <input id="billing_city" type="text" name="billing_city" class="form-control-plaintext" value="<?= $data->user->billing->city ?>" readonly />
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="billing_county" class="font-weight-bold"><?= l('account.billing.county') ?></label>
                                <input id="billing_county" type="text" name="billing_county" class="form-control-plaintext" value="<?= $data->user->billing->county ?>" readonly />
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="billing_zip" class="font-weight-bold"><?= l('account.billing.zip') ?></label>
                                <input id="billing_zip" type="text" name="billing_zip" class="form-control-plaintext" value="<?= $data->user->billing->zip ?>" readonly />
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="billing_country" class="font-weight-bold"><?= l('global.country') ?></label>
                                <input id="billing_country" type="text" class="form-control-plaintext" value="<?= $data->user->billing->country ?>" readonly />
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="billing_phone" class="font-weight-bold"><?= l('account.billing.phone') ?></label>
                                <input id="billing_phone" type="text" name="billing_phone" class="form-control-plaintext" value="<?= $data->user->billing->phone ?>" readonly />
                            </div>
                        </div>

                        <div class="col-12" id="billing_tax_id_container">
                            <div class="form-group">
                                <label for="billing_tax_id" class="font-weight-bold"><?= !empty(settings()->business->tax_type) ? settings()->business->tax_type : l('account.billing.tax_id') ?></label>
                                <input id="billing_tax_id" type="text" name="billing_tax_id" class="form-control-plaintext" value="<?= $data->user->billing->tax_id ?>" readonly />
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<div class="my-5 row justify-content-between">
    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('links.menu.biolink') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-hashtag text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= url('admin/links?type=biolink&user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="biolink_links">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('links.menu.link') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-link text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= url('admin/links?type=link&user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="shortened_links">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('links.menu.event') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-calendar text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= url('admin/links?type=event&user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="event_links">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('links.menu.file') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-file text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= url('admin/links?type=file&user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="file_links">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('links.menu.vcard') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-id-card text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= url('admin/links?type=vcard&user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="vcard_links">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('links.menu.static') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-code text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= url('admin/links?type=static&user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="static_links">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('admin_projects.menu') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-project-diagram text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= url('admin/projects?user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="projects">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('admin_splash_pages.menu') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-droplet text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= url('admin/splash-pages?user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="splash_pages">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col text-truncate">
                        <small class="text-muted font-weight-bold"><?= l('admin_pixels.menu') ?></small>
                    </div>

                    <div class="col-auto">
                        <span class="p-2 bg-primary-100 rounded">
                            <i class="fas fa-fw fa-sm fa-adjust text-primary"></i>
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-break">
                    <a href="<?= url('admin/pixels?user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="pixels">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>
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
                    <a href="<?= url('admin/qr-codes?user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="qr_codes">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if(\Altum\Plugin::is_active('newsletters')): ?>
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col text-truncate">
                            <small class="text-muted font-weight-bold"><?= l('admin_newsletters.menu') ?></small>
                        </div>

                        <div class="col-auto">
                            <span class="p-2 bg-primary-100 rounded">
                                <i class="fas fa-fw fa-sm fa-envelope-open-text text-primary"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mt-2 text-break">
                        <a href="<?= url('admin/newsletters?user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                            <span class="h4" id="newsletters">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col text-truncate">
                            <small class="text-muted font-weight-bold"><?= l('admin_newsletter_subscribers.menu') ?></small>
                        </div>

                        <div class="col-auto">
                            <span class="p-2 bg-primary-100 rounded">
                                <i class="fas fa-fw fa-sm fa-users text-primary"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mt-2 text-break">
                        <a href="<?= url('admin/newsletter-subscribers?user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                            <span class="h4" id="newsletter_subscribers">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if(\Altum\Plugin::is_active('email-signatures')): ?>
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col text-truncate">
                            <small class="text-muted font-weight-bold"><?= l('admin_signatures.menu') ?></small>
                        </div>

                        <div class="col-auto">
                            <span class="p-2 bg-primary-100 rounded">
                                <i class="fas fa-fw fa-sm fa-file-signature text-primary"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mt-2 text-break">
                        <a href="<?= url('admin/signatures?user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                            <span class="h4" id="signatures">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if(\Altum\Plugin::is_active('digital-wallets')): ?>
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col text-truncate">
                            <small class="text-muted font-weight-bold"><?= l('admin_digital_wallets.menu') ?></small>
                        </div>

                        <div class="col-auto">
                            <span class="p-2 bg-primary-100 rounded">
                                <i class="fas fa-fw fa-sm fa-wifi text-primary"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mt-2 text-break">
                        <a href="<?= url('admin/digital-wallets?user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                            <span class="h4" id="digital_wallets">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if(\Altum\Plugin::is_active('aix')): ?>
        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col text-truncate">
                            <small class="text-muted font-weight-bold"><?= l('admin_images.menu') ?></small>
                        </div>

                        <div class="col-auto">
                            <span class="p-2 bg-primary-100 rounded">
                                <i class="fas fa-fw fa-sm fa-icons text-primary"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mt-2 text-break">
                        <a href="<?= url('admin/images?user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                            <span class="h4" id="images">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col text-truncate">
                            <small class="text-muted font-weight-bold"><?= l('admin_transcriptions.menu') ?></small>
                        </div>

                        <div class="col-auto">
                            <span class="p-2 bg-primary-100 rounded">
                                <i class="fas fa-fw fa-sm fa-microphone-alt text-primary"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mt-2 text-break">
                        <a href="<?= url('admin/transcriptions?user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                            <span class="h4" id="transcriptions">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col text-truncate">
                            <small class="text-muted font-weight-bold"><?= l('admin_chats.menu') ?></small>
                        </div>

                        <div class="col-auto">
                            <span class="p-2 bg-primary-100 rounded">
                                <i class="fas fa-fw fa-sm fa-comments text-primary"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mt-2 text-break">
                        <a href="<?= url('admin/chats?user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                            <span class="h4" id="chats">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="card-body">
                    <div class="row">
                        <div class="col text-truncate">
                            <small class="text-muted font-weight-bold"><?= l('admin_syntheses.menu') ?></small>
                        </div>

                        <div class="col-auto">
                            <span class="p-2 bg-primary-100 rounded">
                                <i class="fas fa-fw fa-sm fa-voicemail text-primary"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mt-2 text-break">
                        <a href="<?= url('admin/syntheses?user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                            <span class="h4" id="syntheses">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>

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
                    <a href="<?= url('admin/domains?user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="domains">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>
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
                    <a href="<?= url('admin/payments?user_id=' . $data->user->user_id) ?>" class="stretched-link text-reset text-decoration-none">
                        <span class="h4" id="payments">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    (async function fetch_statistics() {
        let response = await fetch(`${url}admin/user-view/get_stats_ajax?user_id=<?= $data->user->user_id ?>`, {
            method: 'get',
        });

        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            /* :) */
        }

        if(!response.ok) {
            /* :) */
        }

        if(data.status == 'error') {
            /* :) */
        } else if(data.status == 'success') {
            let stats = [
                'biolink_links',
                'shortened_links',
                'file_links',
                'vcard_links',
                'event_links',
                'static_links',
                'projects',
                'pixels',
                'splash_pages',
                'qr_codes',
                'domains',
                'payments',
                'newsletters',
                'newsletter_subscribers',
                'signatures',
                'digital_wallets',
                'images',
                'transcriptions',
                'syntheses',
                'chats',
            ];

            stats.forEach(stat => {
                let element = document.querySelector(`#${stat}`);

                if(element) {
                    element.innerHTML = data.details[stat] ? nr(data.details[stat]) : 0;
                }
            });
        }
    })();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<div class="my-5 row justify-content-between">
    <div class="col-12 col-sm-6 p-3 position-relative text-truncate">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="pl-3 d-flex flex-column justify-content-center">
                <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                    <a href="<?= url('admin/users-logs?user_id=' . $data->user->user_id) ?>" class="stretched-link">
                        <i class="fas fa-fw fa-scroll text-primary"></i>
                    </a>
                </div>
            </div>
            <div class="card-body text-truncate d-flex align-items-center justify-content-between">
                <div class="text-truncate font-size-little-small font-weight-450">
                    <?= l('admin_users_logs.menu') ?>
                </div>

                <div>
                    <i class="fas fa-fw fa-chevron-right fa-sm text-muted"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 p-3 position-relative text-truncate">
        <div class="card d-flex flex-row h-100 overflow-hidden">
            <div class="pl-3 d-flex flex-column justify-content-center">
                <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                    <a href="<?= url('admin/internal-notifications?user_id=' . $data->user->user_id) ?>" class="stretched-link">
                        <i class="fas fa-fw fa-bell text-primary"></i>
                    </a>
                </div>
            </div>
            <div class="card-body text-truncate d-flex align-items-center justify-content-between">
                <div class="text-truncate font-size-little-small font-weight-450">
                    <?= l('admin_internal_notifications.menu') ?>
                </div>

                <div>
                    <i class="fas fa-fw fa-chevron-right fa-sm text-muted"></i>
                </div>
            </div>
        </div>
    </div>

    <?php if(\Altum\Plugin::is_active('push-notifications')): ?>
        <div class="col-12 col-sm-6 p-3 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <a href="<?= url('admin/push-subscribers?user_id=' . $data->user->user_id) ?>" class="stretched-link">
                            <i class="fas fa-fw fa-user-check text-primary"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex align-items-center justify-content-between">
                    <div class="text-truncate font-size-little-small font-weight-450">
                        <?= l('admin_push_subscribers.menu') ?>
                    </div>

                    <div>
                        <i class="fas fa-fw fa-chevron-right fa-sm text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if(in_array(settings()->license->type, ['SPECIAL', 'Extended License', 'extended'])): ?>
        <div class="col-12 col-sm-6 p-3 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <a href="<?= url('admin/redeemed-codes?user_id=' . $data->user->user_id) ?>" class="stretched-link">
                            <i class="fas fa-fw fa-tags text-primary"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex align-items-center justify-content-between">
                    <div class="text-truncate font-size-little-small font-weight-450">
                        <?= l('admin_redeemed_codes.menu') ?>
                    </div>

                    <div>
                        <i class="fas fa-fw fa-chevron-right fa-sm text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if(\Altum\Plugin::is_active('teams')): ?>
        <div class="col-12 col-sm-6 p-3 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <a href="<?= url('admin/teams?user_id=' . $data->user->user_id) ?>" class="stretched-link">
                            <i class="fas fa-fw fa-user-shield text-primary"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex align-items-center justify-content-between">
                    <div class="text-truncate font-size-little-small font-weight-450">
                        <?= l('admin_teams.menu') ?>
                    </div>

                    <div>
                        <i class="fas fa-fw fa-chevron-right fa-sm text-muted"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 p-3 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <a href="<?= url('admin/team-members?user_id=' . $data->user->user_id) ?>" class="stretched-link">
                            <i class="fas fa-fw fa-user-tag text-primary"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex align-items-center justify-content-between">
                    <div class="text-truncate font-size-little-small font-weight-450">
                        <?= l('admin_teams_member.menu') ?>
                    </div>

                    <div>
                        <i class="fas fa-fw fa-chevron-right fa-sm text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if(\Altum\Plugin::is_active('affiliate')): ?>
        <div class="col-12 col-sm-6 p-3 position-relative text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x widget-icon d-flex align-items-center justify-content-center bg-primary-50">
                        <a href="<?= url('admin/users?referred_by=' . $data->user->user_id) ?>" class="stretched-link">
                            <i class="fas fa-fw fa-wallet text-primary"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body text-truncate d-flex align-items-center justify-content-between">
                    <div class="text-truncate font-size-little-small font-weight-450">
                        <?= l('admin_user_view.referred_by') ?>
                    </div>

                    <div>
                        <i class="fas fa-fw fa-chevron-right fa-sm text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>
</div>
