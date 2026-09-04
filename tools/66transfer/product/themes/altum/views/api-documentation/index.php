<?php defined('ALTUMCODE') || die() ?>

<div class="container">

    <div class="text-center">
        <h1 class="h1 font-weight-700"><?= l('api_documentation.header') ?></h1>

        <p class="text-muted font-size-little-small mb-0"><?= l('api_documentation.subheader') ?></p>

        <div class="mb-4">&nbsp;</div>
    </div>

    <div class="card mb-5">
        <div class="card-body font">
			<?php if(is_logged_in()): ?>
                <div class="form-group">
                    <label for="api_key"><?= l('api_documentation.api_key') ?></label>
					<?php
					//ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) $this->user->api_key = 'hidden on demo';
					?>

                    <input type="text" id="api_key" value="<?= $this->user->api_key ?>" class="form-control" onclick="this.select();" readonly="readonly" />

                </div>
			<?php else: ?>
                <div class="mb-3">
                    <a href="<?= url('account-api') ?>" target="_blank" class="btn btn-block btn-outline-primary"><?= l('api_documentation.api_key_retrieve') ?></a>
                </div>
			<?php endif ?>

            <div class="form-group mb-0">
                <label for="base_url"><?= l('api_documentation.base_url') ?></label>
                <input type="text" id="base_url" value="<?= SITE_URL . 'api' ?>" class="form-control" onclick="this.select();" readonly="readonly" />
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <div class="mb-4">
                <h2 class="h5"><?= l('api_documentation.authentication.header') ?></h2>
                <p class="text-muted font-size-little-small"><?= l('api_documentation.authentication.subheader') ?></p>
            </div>

            <div class="form-group">
                <div class="bg-gray-100 rounded-2x card-body">Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span></div>
            </div>

            <div class="form-group mb-0">
                <label><?= l('api_documentation.example') ?></label>
                <div class="card bg-gray-100 border-0">
                    <div class="card-body">
                        curl --request GET \<br />
                        --url '<?= SITE_URL . 'api/' ?><span class="text-primary">{endpoint}</span>' \<br />
                        --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <div class="mb-4">
                <h2 class="h5"><?= l('api_documentation.errors.header') ?></h2>
                <p class="text-muted font-size-little-small"><?= l('api_documentation.errors.subheader') ?></p>
            </div>

            <div class="form-group">
                <label><?= l('api_documentation.example') ?></label>
                <pre class="mb-0" data-shiki="json">
{
    "errors": [
        {
            "title": <?= json_encode(l('api.error_message.no_access')) ?>,
            "status": 401
        }
    ]
}</pre>
            </div>

            <div class="form-group mb-0">
                <label><?= l('api_documentation.status_codes') ?></label>

                <div class="card bg-gray-100 border-0 mb-3">
                    <div class="card-body">
                        <span class="badge badge-success user-select-none mr-3">200</span> <span class="text-muted font-size-small"><?= l('api_documentation.status_codes.200') ?></span>
                    </div>
                </div>

                <div class="card bg-gray-100 border-0 mb-3">
                    <div class="card-body">
                        <span class="badge badge-warning user-select-none mr-3">400</span> <span class="text-muted font-size-small"><?= l('api_documentation.status_codes.400') ?></span>
                    </div>
                </div>

                <div class="card bg-gray-100 border-0 mb-3">
                    <div class="card-body">
                        <span class="badge badge-danger user-select-none mr-3">401</span> <span class="text-muted font-size-small"><?= l('api_documentation.status_codes.401') ?></span>
                    </div>
                </div>

                <div class="card bg-gray-100 border-0 mb-3">
                    <div class="card-body">
                        <span class="badge badge-danger user-select-none mr-3">404</span> <span class="text-muted font-size-small"><?= l('api_documentation.status_codes.404') ?></span>
                    </div>
                </div>

                <div class="card bg-gray-100 border-0 mb-3">
                    <div class="card-body">
                        <span class="badge badge-warning user-select-none mr-3">429</span> <span class="text-muted font-size-small"><?= l('api_documentation.status_codes.429') ?></span>
                    </div>
                </div>

                <div class="card bg-gray-100 border-0">
                    <div class="card-body">
                        <span class="badge badge-danger user-select-none mr-3">500</span> <span class="text-muted font-size-small"><?= l('api_documentation.status_codes.500') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-body text-muted font-size-little-small">
            <i class="fas fa-fw fa-sm fa-info-circle mr-1"></i> <?= sprintf(l('api_documentation.timezone_info'), \Altum\Date::$default_timezone) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/user') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-user text-primary-600"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center font-weight-450">
					<?= l('api_documentation.user') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/files') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-copy text-primary-600"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center font-weight-450">
					<?= l('api_documentation.files') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/transfers') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-paper-plane text-primary-600"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center font-weight-450">
					<?= l('transfers.title') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/statistics') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-chart-bar text-primary-600"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center font-weight-450">
					<?= l('api_documentation.statistics') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/downloads') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-download text-primary-600"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center font-weight-450">
					<?= l('api_documentation.downloads') ?>
                </div>
            </div>
        </div>

		<?php if(settings()->transfers->transfer_requests_is_enabled): ?>
            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/transfer-requests') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-inbox text-primary-600"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-center font-weight-450">
						<?= l('transfer_requests.title') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/transfer-request-statistics') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-chart-bar text-primary-600"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-center font-weight-450">
						<?= l('api_documentation.transfer_request_statistics') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/transfer-request-submissions-statistics') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-upload text-primary-600"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-center font-weight-450">
						<?= l('api_documentation.transfer_request_submissions_statistics') ?>
                    </div>
                </div>
            </div>
		<?php endif ?>

		<?php if(settings()->transfers->projects_is_enabled): ?>
            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/projects') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-project-diagram text-primary-600"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-center font-weight-450">
						<?= l('projects.title') ?>
                    </div>
                </div>
            </div>
		<?php endif ?>

		<?php if(settings()->transfers->pixels_is_enabled): ?>
            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/pixels') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-adjust text-primary-600"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-center font-weight-450">
						<?= l('pixels.title') ?>
                    </div>
                </div>
            </div>
		<?php endif ?>

		<?php if(settings()->transfers->domains_is_enabled): ?>
            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/domains') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-globe text-primary-600"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-center font-weight-450">
						<?= l('domains.title') ?>
                    </div>
                </div>
            </div>
		<?php endif ?>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/notification-handlers') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-bell text-primary-600"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center font-weight-450">
					<?= l('api_documentation.notification_handlers') ?>
                </div>
            </div>
        </div>

		<?php if(\Altum\Plugin::is_active('teams')): ?>
            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/teams') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-user-cog text-primary-600"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-center font-weight-450">
						<?= l('teams.title') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/team-members') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-users-cog text-primary-600"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-center font-weight-450">
						<?= l('api_documentation.team_members') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <a href="<?= url('api-documentation/teams-member') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-user-tag text-primary-600"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-center font-weight-450">
						<?= l('api_documentation.teams_member') ?>
                    </div>
                </div>
            </div>
		<?php endif ?>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/payments') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-credit-card text-primary-600"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center font-weight-450">
					<?= l('account_payments.title') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 p-3 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                        <a href="<?= url('api-documentation/users-logs') ?>" class="stretched-link">
                            <i class="fas fa-fw fa-sm fa-scroll text-primary-600"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center font-weight-450">
					<?= l('account_logs.title') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start() ?>
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": <?= json_encode(l('index.title')) ?>,
                    "item": <?= json_encode(url()) ?>
    },
	{
		"@type": "ListItem",
		"position": 2,
		"name": <?= json_encode(l('api_documentation.title')) ?>,
                    "item": <?= json_encode(url('api-documentation')) ?>
    }
]
}
</script>

<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": <?= json_encode(l('api_documentation.title')) ?>,
        "url": <?= json_encode(url('api-documentation')) ?>,
        "author": {
            "@type": "Organization",
            "name": <?= json_encode(settings()->main->title) ?>,
            "url": <?= json_encode(url()) ?>
    },
	"publisher": {
		"@type": "Organization",
		"name": <?= json_encode(settings()->main->title) ?>
            <?php if(settings()->main->{'logo_' . \Altum\ThemeStyle::get()}): ?>,
            "logo": {
                "@type": "ImageObject",
                "url": <?= json_encode(settings()->main->{'logo_' . \Altum\ThemeStyle::get() . '_full_url'}) ?>
            }
            <?php endif ?>
    },
	"mainEntityOfPage": {
		"@type": "WebPage",
		"@id": <?= json_encode(url('api-documentation')) ?>
    }
}
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php require THEME_PATH . 'views/partials/shiki_highlighter.php' ?>
