<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li><a href="<?= url() ?>"><?= l('index.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li><a href="<?= url('api-documentation') ?>"><?= l('api_documentation.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li class="active" aria-current="page"><?= l('notifications.title') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 mb-4"><?= l('notifications.title') ?></h1>

    <div class="accordion">
        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#read_all" aria-expanded="true" aria-controls="read_all">
                        <span class="badge badge-success user-select-none mr-3"><i class="fas fa-fw fa-sm fa-list"></i></span> <?= l('api_documentation.read_all') ?>
                    </a>
                </h3>
            </div>

            <div id="read_all" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-success user-select-none mr-3">GET</span> <span class="text-muted"><?= SITE_URL ?>api/notifications/</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request GET \<br />
                                --url '<?= SITE_URL ?>api/notifications/' \<br />
                                --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive table-custom-container mb-4">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('api_documentation.parameters') ?></th>
                                <th><?= l('global.details') ?></th>
                                <th><?= l('global.description') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>search</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-font mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('api_documentation.filters.search') ?></td>
                            </tr>

                            <tr>
                                <td>search_by</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-font mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.filters.search_by'), '<code>' . implode('</code>, <code>', ['name']) . '</code>') ?></td>
                            </tr>

                            <tr>
                                <td>is_enabled</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span>
                                </td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>type</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-font mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', array_keys(require APP_PATH . 'includes/enabled_notifications.php')) . '</code>') ?></td>
                            </tr>

                            <tr>
                                <td>datetime_field</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-font mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['datetime', 'last_datetime']) . '</code>') ?></td>
                            </tr>

                            <tr>
                                <td>datetime_start</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-font mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('api_documentation.filters.datetime_start') ?></td>
                            </tr>

                            <tr>
                                <td>datetime_end</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-font mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('api_documentation.filters.datetime_end') ?></td>
                            </tr>

                            <tr>
                                <td>order_by</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-font mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.filters.order_by'), '<code>' . implode('</code>, <code>', ['notification_id', 'datetime', 'last_datetime', 'name', 'impressions', 'hovers', 'clicks', 'form_submissions']) . '</code>') ?></td>
                            </tr>

                            <tr>
                                <td>order_type</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-font mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('api_documentation.filters.order_by_type') ?></td>
                            </tr>
                            <tr>
                                <td>page</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= l('api_documentation.filters.page') ?></td>
                            </tr>
                            <tr>
                                <td>results_per_page</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.filters.results_per_page'), '<code>' . implode('</code>, <code>', [10, 25, 50, 100, 250, 500, 1000]) . '</code>', 25) ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.response') ?></label>
                        <pre data-shiki="json">
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "campaign_id": 1,
            "notification_key": "f56d2208dd5e9d9809c158e1d6a77f53",
            "name": "Example",
            "type": "reviews",
            "settings": {},
            "impressions": 0,
            "hovers": 0,
            "clicks": 0,
            "form_submissions": 0,
            "is_enabled": true,
            "datetime": "<?= get_date() ?>",
        }
    ],
    "meta": {
        "page": 1,
        "results_per_page": 25,
        "total": 1,
        "total_pages": 1
    },
    "links": {
        "first": "<?= SITE_URL ?>api/notifications?page=1",
        "last": "<?= SITE_URL ?>api/notifications?page=1",
        "next": null,
        "prev": null,
        "self": "<?= SITE_URL ?>api/notifications?page=1"
    }
}</pre>
                    </div>
                </div>
            </div>
        </div>


        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#read" aria-expanded="true" aria-controls="read">
                        <span class="badge badge-success user-select-none mr-3"><i class="fas fa-fw fa-sm fa-eye"></i></span> <?= l('api_documentation.read') ?>
                    </a>
                </h3>
            </div>

            <div id="read" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-success user-select-none mr-3">GET</span> <span class="text-muted"><?= SITE_URL ?>api/notifications/</span><span class="text-primary">{notification_id}</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request GET \<br />
                                --url '<?= SITE_URL ?>api/notifications/<span class="text-primary">{notification_id}</span>' \<br />
                                --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.response') ?></label>
                        <pre data-shiki="json">
{
    "data": {
        "id": 1,
        "user_id": 1,
        "campaign_id": 1,
        "notification_key": "f56d2208dd5e9d9809c158e1d6a77f53",
        "name": "Example",
        "type": "reviews",
        "settings": {},
        "impressions": 0,
        "hovers": 0,
        "clicks": 0,
        "form_submissions": 0,
        "is_enabled": true,
        "datetime": "<?= get_date() ?>",
    }
}</pre>
                    </div>
                </div>
            </div>
        </div>


        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#notifications_create" aria-expanded="true" aria-controls="notifications_create">
                        <span class="badge badge-info user-select-none mr-3"><i class="fas fa-fw fa-sm fa-plus"></i></span> <?= l('api_documentation.create') ?>
                    </a>
                </h3>
            </div>

            <div id="notifications_create" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-info user-select-none mr-3">POST</span> <span class="text-muted"><?= SITE_URL ?>api/notifications</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive table-custom-container mb-4">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('api_documentation.parameters') ?></th>
                                <th><?= l('global.details') ?></th>
                                <th><?= l('global.description') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>campaign_id</td>
                                <td>
                                    <span class="badge badge-danger"><i class="fas fa-fw fa-sm fa-asterisk mr-1"></i> <?= l('api_documentation.required') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>type</td>
                                <td>
                                    <span class="badge badge-danger"><i class="fas fa-fw fa-sm fa-asterisk mr-1"></i> <?= l('api_documentation.required') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>',  array_keys(require APP_PATH . 'includes/enabled_notifications.php')) . '</code>') ?></td>
                            </tr>
                            <tr>
                                <td>name</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>is_enabled</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request POST \<br />
                                --url '<?= SITE_URL ?>api/notifications' \<br />
                                --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \<br />
                                --header 'Content-Type: multipart/form-data' \<br />
                                --form 'type=<span class="text-primary">informational</span>' \<br />
                                --form 'is_enabled=<span class="text-primary">1</span>'
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.response') ?></label>
                        <pre data-shiki="json">
{
    "data": {
        "id": 1
    }
}</pre>
                    </div>
                </div>

            </div>
        </div>


        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#notifications_update" aria-expanded="true" aria-controls="notifications_update">
                        <span class="badge badge-info user-select-none mr-3"><i class="fas fa-fw fa-sm fa-pencil"></i></span> <?= l('api_documentation.update') ?>
                    </a>
                </h3>
            </div>

            <div id="notifications_update" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-info user-select-none mr-3">POST</span> <span class="text-muted"><?= SITE_URL ?>api/notifications/</span><span class="text-primary">{notification_id}</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive table-custom-container mb-4">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('api_documentation.parameters') ?></th>
                                <th><?= l('global.details') ?></th>
                                <th><?= l('global.description') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>name</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>is_enabled</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>trigger_all_pages</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>display_trigger</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>
                                    <?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['delay','time_on_site','pageviews','inactivity','exit_intent','scroll','target_visible','click','hover',]) . '</code>') ?>
                                </td>
                            </tr>

                            <tr>
                                <td>display_trigger_value</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>display_trigger_selector</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.available_when'), '<code>display_trigger = target_visible</code>') ?></td>
                            </tr>

                            <tr>
                                <td>display_delay_type_after_close</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>
                                    <?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['time_on_site', 'pageviews',]) . '</code>') ?>
                                </td>
                            </tr>

                            <tr>
                                <td>display_delay_value_after_close</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>display_frequency</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>
                                    <?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['all_time', 'once_per_session', 'once_per_browser']) . '</code>') ?>
                                </td>
                            </tr>

                            <tr>
                                <td>direction</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>
                                    <?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['rtl', 'ltr']) . '</code>') ?>
                                </td>
                            </tr>

                            <?php foreach(['display_continents', 'display_countries', 'display_languages', 'display_operating_systems', 'display_browsers', 'display_cities',] as $key): ?>
                                <tr>
                                    <td><?= $key ?></td>
                                    <td>
                                        <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                        <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-list mr-1"></i> <?= l('api_documentation.array') ?></span>
                                        <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                    </td>
                                    <td>-</td>
                                </tr>
                            <?php endforeach ?>

                            <tr>
                                <td>display_mobile</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>display_desktop</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>schedule</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>start_date</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>end_date</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>display_duration</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>display_position</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>
                                    <?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['top_left','top_center','top_right','middle_left','middle_center','middle_right','bottom_left','bottom_center','bottom_right','top','bottom','top_floating','bottom_floating']) . '</code>') ?>
                                </td>
                            </tr>

                            <tr>
                                <td>display_close_button</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>close_button_color</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>dark_mode_close_button_color</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>display_branding</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>shadow</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>
                                    <?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['','subtle','feather','3d','layered']) . '</code>') ?>
                                </td>
                            </tr>

                            <tr>
                                <td>border_width</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', range(0, 5)) . '</code>') ?></td>
                            </tr>

                            <tr>
                                <td>internal_padding</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>5-25</code>') ?></td>
                            </tr>

                            <tr>
                                <td>background_blur</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>0-30</code>') ?></td>
                            </tr>

                            <tr>
                                <td>custom_css</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>hover_animation</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>
                                    <?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['','fast_scale_up','slow_scale_up','fast_scale_down','slow_scale_down']) . '</code>') ?>
                                </td>
                            </tr>

                            <tr>
                                <td>on_animation</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>
                                    <?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['fadeIn','slideInUp','slideInDown','zoomIn', 'bounceIn']) . '</code>') ?>
                                </td>
                            </tr>

                            <tr>
                                <td>off_animation</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>
                                    <?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['fadeOut','slideOutUp','slideOutDown','zoomOut', 'bounceOut']) . '</code>') ?>
                                </td>
                            </tr>

                            <tr>
                                <td>animation</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>
                                    <?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['','heartbeat','bounce','flash', 'pulse']) . '</code>') ?>
                                </td>
                            </tr>

                            <tr>
                                <td>animation_interval</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr>
                                <td>font</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>
                                    <?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['inherit','Arial','Verdana','Helvetica','Tahoma','Trebuchet MS','Times New Roman','Georgia','Courier New','Monaco','Comic Sans MS','Courier','Impact','Futura','Luminari','Baskerville','Papyrus',]) . '</code>') ?>
                                </td>
                            </tr>

                            <tr>
                                <td>border_radius</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>
                                    <?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['straight','rounded','highly_rounded','round']) . '</code>') ?>
                                </td>
                            </tr>

                            <tr>
                                <td>dark_mode_is_enabled</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>notifications</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-list mr-1"></i> <?= l('api_documentation.array') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = email_collector, collector_bar, collector_modal, collector_two_modal, conversions, inline_conversions, conversions_counter, inline_conversions_counter, countdown_collector, request_collector, text_feedback</span>') ?> <?= l('api_documentation.notifications_handlers_ids') ?></td>
                            </tr>

                            <tr>
                                <td>translations[input_key][index]['key']</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('api_documentation.notifications_translations_help') ?></td>
                            </tr>

                            <tr>
                                <td>translations[input_key][index]['value']</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('api_documentation.notifications_translations_help') ?></td>
                            </tr>


                            <?php if(settings()->notifications->available_notifications->informational): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".informational_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        informational
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse informational_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational</span>') ?> <span class="badge badge-success"><i class="fas fa-fw fa-sm fa-language mr-1"></i> <?= l('api_documentation.notifications_translations_translatable') ?></span> </td>
                                </tr>
                                <tr class="collapse informational_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational</span>') ?> <span class="badge badge-success"><i class="fas fa-fw fa-sm fa-language mr-1"></i> <?= l('api_documentation.notifications_translations_translatable') ?></span> </td>
                                </tr>
                                <tr class="collapse informational_collapse">
                                    <td>image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_collapse">
                                    <td>image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_collapse">
                                    <td>url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational</span>') ?></td>
                                </tr>
                                <?php foreach(['title_color', 'description_color', 'background_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse informational_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse informational_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>


                            <?php if(settings()->notifications->available_notifications->informational_mini): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".informational_mini_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        informational_mini
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse informational_mini_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_mini</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_mini_collapse">
                                    <td>image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_mini</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_mini_collapse">
                                    <td>image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_mini</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_mini_collapse">
                                    <td>url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_mini</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_mini_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_mini</span>') ?></td>
                                </tr>
                                <?php foreach(['title_color', 'description_color', 'background_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse informational_mini_collapse">
                                        <td><?= $key ?></td>
                                        <td>
                                            <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                            <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                        </td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_mini</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse informational_mini_collapse">
                                        <td><?= $key ?></td>
                                        <td>
                                            <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                            <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                        </td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_mini</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->coupon): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".coupon_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        coupon
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse coupon_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon</span>') ?></td>
                                </tr>
                                <tr class="collapse coupon_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon</span>') ?></td>
                                </tr>
                                <tr class="collapse coupon_collapse">
                                    <td>image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon</span>') ?></td>
                                </tr>
                                <tr class="collapse coupon_collapse">
                                    <td>image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon</span>') ?></td>
                                </tr>
                                <tr class="collapse coupon_collapse">
                                    <td>coupon_code</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon</span>') ?> <span class="badge badge-success"><i class="fas fa-fw fa-sm fa-language mr-1"></i> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse coupon_collapse">
                                    <td>button_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon</span>') ?></td>
                                </tr>
                                <tr class="collapse coupon_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon</span>') ?></td>
                                </tr>
                                <tr class="collapse coupon_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>

                                <?php foreach(['title_color', 'description_color', 'background_color', 'button_background_color', 'button_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse coupon_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_button_background_color', 'dark_mode_button_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse coupon_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>


                            <?php if(settings()->notifications->available_notifications->live_counter): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".live_counter_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        live_counter / live_counter_bar / inline_live_counter
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse live_counter_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = live_counter, live_counter_bar, inline_live_counter</span>') ?></td>
                                </tr>
                                <tr class="collapse live_counter_collapse">
                                    <td>last_activity</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = live_counter, live_counter_bar, inline_live_counter</span>') ?></td>
                                </tr>
                                <tr class="collapse live_counter_collapse">
                                    <td>activity_type</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td>
                                        <?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['all','page']) . '</code>') ?>
                                        <?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = live_counter, live_counter_bar, inline_live_counter</span>') ?>
                                    </td>
                                </tr>
                                <tr class="collapse live_counter_collapse">
                                    <td>url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = live_counter, live_counter_bar, inline_live_counter</span>') ?></td>
                                </tr>
                                <tr class="collapse live_counter_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = live_counter, live_counter_bar, inline_live_counter</span>') ?></td>
                                </tr>
                                <tr class="collapse live_counter_collapse">
                                    <td>selector</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = inline_live_counter</span>') ?></td>
                                </tr>
                                <tr class="collapse live_counter_collapse">
                                    <td>inline_placement</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = inline_live_counter</span>') ?></td>
                                </tr>
                                <?php foreach(['margin_top', 'margin_right', 'margin_bottom', 'margin_left'] as $key): ?>
                                    <tr class="collapse live_counter_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = inline_live_counter</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                                <tr class="collapse live_counter_collapse">
                                    <td>display_minimum_activity</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = live_counter, live_counter_bar, inline_live_counter</span>') ?></td>
                                </tr>

                                <?php foreach(['description_color', 'background_color', 'number_background_color', 'number_color', 'pulse_background_color'] as $key): ?>
                                    <tr class="collapse live_counter_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = live_counter, live_counter_bar, inline_live_counter</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_number_background_color', 'dark_mode_number_color', 'dark_mode_pulse_background_color'] as $key): ?>
                                    <tr class="collapse live_counter_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = live_counter, live_counter_bar, inline_live_counter</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->email_collector): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".email_collector_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        email_collector
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse email_collector_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = email_collector</span>') ?></td>
                                </tr>
                                <tr class="collapse email_collector_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = email_collector</span>') ?></td>
                                </tr>
                                <tr class="collapse email_collector_collapse">
                                    <td>name_placeholder</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = email_collector</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse email_collector_collapse">
                                    <td>email_placeholder</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = email_collector</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse email_collector_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = email_collector</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse email_collector_collapse">
                                    <td>show_agreement</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = email_collector</span>') ?></td>
                                </tr>
                                <tr class="collapse email_collector_collapse">
                                    <td>agreement_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = email_collector</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse email_collector_collapse">
                                    <td>agreement_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = email_collector</span>') ?></td>
                                </tr>
                                <tr class="collapse email_collector_collapse">
                                    <td>thank_you_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = email_collector</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'description_color', 'background_color', 'button_background_color', 'button_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse email_collector_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = email_collector</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_button_background_color', 'dark_mode_button_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse email_collector_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = email_collector</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>


                            <?php if((settings()->notifications->available_notifications->conversions ?? true) || (settings()->notifications->available_notifications->inline_conversions ?? true)): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".conversions_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        conversions / inline_conversions
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse conversions_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?></td>
                                </tr>
                                <tr class="collapse conversions_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?></td>
                                </tr>
                                <tr class="collapse conversions_collapse">
                                    <td>image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?></td>
                                </tr>
                                <tr class="collapse conversions_collapse">
                                    <td>image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?></td>
                                </tr>
                                <tr class="collapse conversions_collapse">
                                    <td>url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?></td>
                                </tr>
                                <tr class="collapse conversions_collapse">
                                    <td>display_time</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?></td>
                                </tr>
                                <tr class="collapse conversions_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?></td>
                                </tr>
                                <tr class="collapse conversions_collapse">
                                    <td>conversions_count</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?></td>
                                </tr>
                                <tr class="collapse conversions_collapse">
                                    <td>in_between_delay</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?></td>
                                </tr>
                                <tr class="collapse conversions_collapse">
                                    <td>order</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td>
                                        <?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?>
                                        <br /><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['random', 'descending']) . '</code>') ?>
                                    </td>
                                </tr>
                                <tr class="collapse conversions_collapse">
                                    <td>data_trigger_auto</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?></td>
                                </tr>
                                <tr class="collapse conversions_collapse">
                                    <td>data_trigger_auto_type[index]</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-list mr-1"></i> <?= l('api_documentation.array') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?> <br /><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>',  ['exact', 'contains', 'starts_with', 'ends_with']) . '</code>') ?></td>
                                </tr>
                                <tr class="collapse conversions_collapse">
                                    <td>data_trigger_auto_value[index]</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-list mr-1"></i> <?= l('api_documentation.array') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?></td>
                                </tr>
                                <?php foreach(['title_color', 'description_color', 'date_color', 'background_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse conversions_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_date_color', 'dark_mode_background_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse conversions_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>


                            <?php if(settings()->notifications->available_notifications->conversions_counter): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".conversions_counter_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        conversions counter
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse conversions_counter_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions counter</span>') ?></td>
                                </tr>
                                <tr class="collapse conversions_counter_collapse">
                                    <td>last_activity</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions counter</span>') ?></td>
                                </tr>
                                <tr class="collapse conversions_counter_collapse">
                                    <td>url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions counter</span>') ?></td>
                                </tr>
                                <tr class="collapse conversions_counter_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions counter</span>') ?></td>
                                </tr>
                                <tr class="collapse conversions_counter_collapse">
                                    <td>display_minimum_activity</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions counter</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'description_color', 'background_color', 'number_background_color', 'number_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse conversions_counter_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions counter</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_number_background_color', 'dark_mode_number_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse conversions_counter_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions counter</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>
                            <tr class="collapse conversions_counter_collapse">
                                <td>data_trigger_auto</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions counter</span>') ?></td>
                            </tr>
                            <tr class="collapse conversions_collapse">
                                <td>data_trigger_auto_type[index]</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-list mr-1"></i> <?= l('api_documentation.array') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?> <br /><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>',  ['exact', 'contains', 'starts_with', 'ends_with']) . '</code>') ?></td>
                            </tr>
                            <tr class="collapse conversions_collapse">
                                <td>data_trigger_auto_value[index]</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-list mr-1"></i> <?= l('api_documentation.array') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = conversions, inline_conversions</span>') ?></td>
                            </tr>




                            <?php if(settings()->notifications->available_notifications->video): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".video_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        video
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse video_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = video</span>') ?></td>
                                </tr>
                                <tr class="collapse video_collapse">
                                    <td>video</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = video</span>') ?></td>
                                </tr>
                                <tr class="collapse video_collapse">
                                    <td>video_is_youtube</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = video</span>') ?></td>
                                </tr>
                                <tr class="collapse video_collapse">
                                    <td>youtube_video_id</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = video</span>') ?></td>
                                </tr>
                                <tr class="collapse video_collapse">
                                    <td>video_autoplay</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = video</span>') ?></td>
                                </tr>
                                <tr class="collapse video_collapse">
                                    <td>video_controls</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = video</span>') ?></td>
                                </tr>
                                <tr class="collapse video_collapse">
                                    <td>video_loop</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = video</span>') ?></td>
                                </tr>
                                <tr class="collapse video_collapse">
                                    <td>video_muted</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = video</span>') ?></td>
                                </tr>
                                <tr class="collapse video_collapse">
                                    <td>button_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = video</span>') ?></td>
                                </tr>
                                <tr class="collapse video_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = video</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse video_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = video</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'background_color', 'button_background_color', 'button_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse video_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = video</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_background_color', 'dark_mode_button_background_color', 'dark_mode_button_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse video_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = video</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>


                            <?php if(settings()->notifications->available_notifications->audio): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".audio_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        audio
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse audio_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = audio</span>') ?></td>
                                </tr>
                                <tr class="collapse audio_collapse">
                                    <td>audio</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = audio</span>') ?></td>
                                </tr>
                                <tr class="collapse audio_collapse">
                                    <td>audio_autoplay</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = audio</span>') ?></td>
                                </tr>
                                <tr class="collapse audio_collapse">
                                    <td>audio_controls</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = audio</span>') ?></td>
                                </tr>
                                <tr class="collapse audio_collapse">
                                    <td>audio_loop</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = audio</span>') ?></td>
                                </tr>
                                <tr class="collapse audio_collapse">
                                    <td>audio_muted</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = audio</span>') ?></td>
                                </tr>
                                <tr class="collapse audio_collapse">
                                    <td>button_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = audio</span>') ?></td>
                                </tr>
                                <tr class="collapse audio_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = audio</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse audio_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = audio</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'background_color', 'button_background_color', 'button_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse audio_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = audio</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_background_color', 'dark_mode_button_background_color', 'dark_mode_button_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse audio_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = audio</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->social_share): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".social_share_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        social_share
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse social_share_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = social_share</span>') ?></td>
                                </tr>
                                <tr class="collapse social_share_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = social_share</span>') ?></td>
                                </tr>
                                <tr class="collapse social_share_collapse">
                                    <td>share_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = social_share</span>') ?></td>
                                </tr>
                                <tr class="collapse social_share_collapse">
                                    <td>share_facebook</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = social_share</span>') ?></td>
                                </tr>
                                <tr class="collapse social_share_collapse">
                                    <td>share_x</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = social_share</span>') ?></td>
                                </tr>
                                <tr class="collapse social_share_collapse">
                                    <td>share_threads</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = social_share</span>') ?></td>
                                </tr>
                                <tr class="collapse social_share_collapse">
                                    <td>share_linkedin</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = social_share</span>') ?></td>
                                </tr>
                                <tr class="collapse social_share_collapse">
                                    <td>share_reddit</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = social_share</span>') ?></td>
                                </tr>
                                <tr class="collapse social_share_collapse">
                                    <td>share_pinterest</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = social_share</span>') ?></td>
                                </tr>
                                <tr class="collapse social_share_collapse">
                                    <td>share_tumblr</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = social_share</span>') ?></td>
                                </tr>
                                <tr class="collapse social_share_collapse">
                                    <td>share_telegram</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = social_share</span>') ?></td>
                                </tr>
                                <tr class="collapse social_share_collapse">
                                    <td>share_whatsapp</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = social_share</span>') ?></td>
                                </tr>
                                <?php foreach(['title_color', 'description_color', 'background_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse social_share_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = social_share</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse social_share_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = social_share</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->reviews): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".reviews_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        reviews
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse reviews_collapse">
                                    <td>url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = reviews</span>') ?></td>
                                </tr>
                                <tr class="collapse reviews_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = reviews</span>') ?></td>
                                </tr>
                                <tr class="collapse reviews_collapse">
                                    <td>reviews_count</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><?= l('api_documentation.integer') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = reviews</span>') ?></td>
                                </tr>
                                <tr class="collapse reviews_collapse">
                                    <td>in_between_delay</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><?= l('api_documentation.integer') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = reviews</span>') ?></td>
                                </tr>
                                <tr class="collapse reviews_collapse">
                                    <td>order</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = reviews</span>') ?></td>
                                </tr>
                                <tr class="collapse reviews_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><?= l('api_documentation.default') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = reviews</span>') ?></td>
                                </tr>
                                <tr class="collapse reviews_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><?= l('api_documentation.default') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = reviews</span>') ?></td>
                                </tr>
                                <tr class="collapse reviews_collapse">
                                    <td>image</td>
                                    <td><span class="badge badge-info"><?= l('api_documentation.default') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = reviews</span>') ?></td>
                                </tr>
                                <tr class="collapse reviews_collapse">
                                    <td>stars</td>
                                    <td><span class="badge badge-info"><?= l('api_documentation.default') ?></span> <span class="badge badge-secondary"><?= l('api_documentation.integer') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = reviews</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'description_color', 'background_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse reviews_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = reviews</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse reviews_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = reviews</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>


                            <?php if(settings()->notifications->available_notifications->rating_summary ?? true): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".rating_summary_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        rating_summary / inline_rating_summary
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse rating_summary_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = rating_summary, inline_rating_summary</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse rating_summary_collapse">
                                    <td>average_rating</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><?= l('api_documentation.float') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = rating_summary, inline_rating_summary</span>') ?></td>
                                </tr>
                                <tr class="collapse rating_summary_collapse">
                                    <td>reviews_count</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><?= l('api_documentation.integer') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = rating_summary, inline_rating_summary</span>') ?></td>
                                </tr>
                                <tr class="collapse rating_summary_collapse">
                                    <td>display_minimum_reviews</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><?= l('api_documentation.integer') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = rating_summary, inline_rating_summary</span>') ?></td>
                                </tr>
                                <tr class="collapse rating_summary_collapse">
                                    <td>url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = rating_summary, inline_rating_summary</span>') ?></td>
                                </tr>
                                <tr class="collapse rating_summary_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = rating_summary, inline_rating_summary</span>') ?></td>
                                </tr>
                                <tr class="collapse rating_summary_collapse">
                                    <td>selector</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = inline_rating_summary</span>') ?></td>
                                </tr>
                                <tr class="collapse rating_summary_collapse">
                                    <td>inline_placement</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = inline_rating_summary</span>') ?></td>
                                </tr>

                                <?php foreach(['rating_color', 'rating_background_color', 'stars_color', 'description_color', 'background_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse rating_summary_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = rating_summary, inline_rating_summary</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_rating_color', 'dark_mode_rating_background_color', 'dark_mode_stars_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse rating_summary_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = rating_summary, inline_rating_summary</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->emoji_feedback): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".emoji_feedback_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        emoji_feedback
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse emoji_feedback_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = emoji_feedback</span>') ?></td>
                                </tr>
                                <tr class="collapse emoji_feedback_collapse">
                                    <td>thank_you_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = emoji_feedback</span>') ?></td>
                                </tr>
                                <tr class="collapse emoji_feedback_collapse">
                                    <td>show_angry</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = emoji_feedback</span>') ?></td>
                                </tr>
                                <tr class="collapse emoji_feedback_collapse">
                                    <td>show_sad</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = emoji_feedback</span>') ?></td>
                                </tr>
                                <tr class="collapse emoji_feedback_collapse">
                                    <td>show_neutral</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = emoji_feedback</span>') ?></td>
                                </tr>
                                <tr class="collapse emoji_feedback_collapse">
                                    <td>show_happy</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = emoji_feedback</span>') ?></td>
                                </tr>
                                <tr class="collapse emoji_feedback_collapse">
                                    <td>show_excited</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = emoji_feedback</span>') ?></td>
                                </tr>
                                <?php foreach(['title_color', 'background_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse emoji_feedback_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = emoji_feedback</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_background_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse emoji_feedback_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = emoji_feedback</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->cookie_notification): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".cookie_notification_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        cookie_notification
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse cookie_notification_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = cookie_notification</span>') ?></td>
                                </tr>
                                <tr class="collapse cookie_notification_collapse">
                                    <td>image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = cookie_notification</span>') ?></td>
                                </tr>
                                <tr class="collapse cookie_notification_collapse">
                                    <td>image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-percentage mr-1"></i> <?= l('api_documentation.float') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = cookie_notification</span>') ?></td>
                                </tr>
                                <tr class="collapse cookie_notification_collapse">
                                    <td>url_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = cookie_notification</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse cookie_notification_collapse">
                                    <td>url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = cookie_notification</span>') ?></td>
                                </tr>
                                <tr class="collapse cookie_notification_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = cookie_notification</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <?php foreach(['description_color', 'background_color', 'button_background_color', 'button_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse cookie_notification_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = cookie_notification</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_button_background_color', 'dark_mode_button_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse cookie_notification_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = cookie_notification</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->score_feedback): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".score_feedback_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        score_feedback
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse score_feedback_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = score_feedback</span>') ?></td>
                                </tr>
                                <tr class="collapse score_feedback_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = score_feedback</span>') ?></td>
                                </tr>
                                <tr class="collapse score_feedback_collapse">
                                    <td>thank_you_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = score_feedback</span>') ?></td>
                                </tr>
                                <?php foreach(['title_color', 'description_color', 'background_color', 'button_background_color', 'button_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse score_feedback_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = score_feedback</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_button_background_color', 'dark_mode_button_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse score_feedback_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = score_feedback</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->request_collector): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".request_collector_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        request_collector
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse request_collector_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = request_collector</span>') ?></td>
                                </tr>
                                <tr class="collapse request_collector_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = request_collector</span>') ?></td>
                                </tr>
                                <tr class="collapse request_collector_collapse">
                                    <td>image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = request_collector</span>') ?></td>
                                </tr>
                                <tr class="collapse request_collector_collapse">
                                    <td>image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = request_collector</span>') ?></td>
                                </tr>
                                <tr class="collapse request_collector_collapse">
                                    <td>content_title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = request_collector</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse request_collector_collapse">
                                    <td>content_description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = request_collector</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse request_collector_collapse">
                                    <td>input_placeholder</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = request_collector</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse request_collector_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = request_collector</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse request_collector_collapse">
                                    <td>show_agreement</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = request_collector</span>') ?></td>
                                </tr>
                                <tr class="collapse request_collector_collapse">
                                    <td>agreement_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = request_collector</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse request_collector_collapse">
                                    <td>agreement_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = request_collector</span>') ?></td>
                                </tr>
                                <tr class="collapse request_collector_collapse">
                                    <td>thank_you_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = request_collector</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'description_color', 'content_title_color', 'content_description_color', 'background_color', 'button_background_color', 'button_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse request_collector_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = request_collector</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_content_title_color', 'dark_mode_content_description_color', 'dark_mode_background_color', 'dark_mode_button_background_color', 'dark_mode_button_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse request_collector_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = request_collector</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->countdown_collector): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".countdown_collector_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        countdown_collector
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse countdown_collector_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = countdown_collector</span>') ?></td>
                                </tr>
                                <tr class="collapse countdown_collector_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = countdown_collector</span>') ?></td>
                                </tr>
                                <tr class="collapse countdown_collector_collapse">
                                    <td>content_title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = countdown_collector</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse countdown_collector_collapse">
                                    <td>input_placeholder</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = countdown_collector</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse countdown_collector_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = countdown_collector</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse countdown_collector_collapse">
                                    <td>countdown_end_date</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = countdown_collector</span>') ?></td>
                                </tr>
                                <tr class="collapse countdown_collector_collapse">
                                    <td>show_agreement</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = countdown_collector</span>') ?></td>
                                </tr>
                                <tr class="collapse countdown_collector_collapse">
                                    <td>agreement_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = countdown_collector</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse countdown_collector_collapse">
                                    <td>agreement_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = countdown_collector</span>') ?></td>
                                </tr>
                                <tr class="collapse countdown_collector_collapse">
                                    <td>thank_you_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = countdown_collector</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'description_color', 'content_title_color', 'time_color', 'time_background_color', 'background_color', 'button_background_color', 'button_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse countdown_collector_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = countdown_collector</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_content_title_color', 'dark_mode_time_color', 'dark_mode_time_background_color', 'dark_mode_background_color', 'dark_mode_button_background_color', 'dark_mode_button_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse countdown_collector_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = countdown_collector</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->custom_html): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".custom_html_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        custom_html
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse custom_html_collapse">
                                    <td>html</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = custom_html</span>') ?></td>
                                </tr>
                                <?php foreach(['background_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse custom_html_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = custom_html</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_background_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse custom_html_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = custom_html</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->informational_bar): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".informational_bar_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        informational_bar
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse informational_bar_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_bar</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_bar_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_bar</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_bar_collapse">
                                    <td>image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_bar</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_bar_collapse">
                                    <td>image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-percentage mr-1"></i> <?= l('api_documentation.float') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_bar</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_bar_collapse">
                                    <td>url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-percentage mr-1"></i> <?= l('api_documentation.float') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_bar</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_bar_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_bar</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'description_color', 'background_color', 'border_color'] as $key): ?>
                                    <tr class="collapse informational_bar_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_bar</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_border_color'] as $key): ?>
                                    <tr class="collapse informational_bar_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_bar</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->informational_bar_mini): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".informational_bar_mini_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        informational_bar_mini
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse informational_bar_mini_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_bar_mini</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_bar_mini_collapse">
                                    <td>image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_bar_mini</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_bar_mini_collapse">
                                    <td>image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-percentage mr-1"></i> <?= l('api_documentation.float') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_bar_mini</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_bar_mini_collapse">
                                    <td>url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-percentage mr-1"></i> <?= l('api_documentation.float') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_bar_mini</span>') ?></td>
                                </tr>
                                <tr class="collapse informational_bar_mini_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_bar_mini</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'description_color', 'background_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse informational_bar_mini_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_bar_mini</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse informational_bar_mini_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = informational_bar_mini</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->image): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".image_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        image
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse image_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = image</span>') ?></td>
                                </tr>
                                <tr class="collapse image_collapse">
                                    <td>image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = image</span>') ?></td>
                                </tr>
                                <tr class="collapse image_collapse">
                                    <td>image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = image</span>') ?></td>
                                </tr>
                                <tr class="collapse image_collapse">
                                    <td>button_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = image</span>') ?></td>
                                </tr>
                                <tr class="collapse image_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = image</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <?php foreach(['title_color', 'background_color', 'button_background_color', 'button_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse image_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = image</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_background_color', 'dark_mode_button_background_color', 'dark_mode_button_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse image_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = image</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->collector_bar): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".collector_bar_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        collector_bar
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse collector_bar_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_bar</span>') ?></td>
                                </tr>
                                <tr class="collapse collector_bar_collapse">
                                    <td>input_placeholder</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_bar</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse collector_bar_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_bar</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse collector_bar_collapse">
                                    <td>show_agreement</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_bar</span>') ?></td>
                                </tr>
                                <tr class="collapse collector_bar_collapse">
                                    <td>agreement_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_bar</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse collector_bar_collapse">
                                    <td>agreement_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_bar</span>') ?></td>
                                </tr>
                                <tr class="collapse collector_bar_collapse">
                                    <td>thank_you_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_bar</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'background_color', 'button_background_color', 'button_color', 'border_color'] as $key): ?>
                                    <tr class="collapse collector_bar_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_bar</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_background_color', 'dark_mode_button_background_color', 'dark_mode_button_color', 'dark_mode_border_color'] as $key): ?>
                                    <tr class="collapse collector_bar_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_bar</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->coupon_bar): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".coupon_bar_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        coupon_bar
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse coupon_bar_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon_bar</span>') ?></td>
                                </tr>
                                <tr class="collapse coupon_bar_collapse">
                                    <td>coupon_code</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon_bar</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse coupon_bar_collapse">
                                    <td>url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-percentage mr-1"></i> <?= l('api_documentation.float') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon_bar</span>') ?></td>
                                </tr>
                                <tr class="collapse coupon_bar_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon_bar</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'background_color', 'coupon_code_color', 'coupon_code_background_color', 'coupon_code_border_color', 'border_color'] as $key): ?>
                                    <tr class="collapse coupon_bar_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon_bar</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_background_color', 'dark_mode_coupon_code_color', 'dark_mode_coupon_code_background_color', 'dark_mode_coupon_code_border_color', 'dark_mode_border_color'] as $key): ?>
                                    <tr class="collapse coupon_bar_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = coupon_bar</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->button_bar): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".button_bar_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        button_bar
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse button_bar_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = button_bar</span>') ?></td>
                                </tr>
                                <tr class="collapse button_bar_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = button_bar</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse button_bar_collapse">
                                    <td>url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = button_bar</span>') ?></td>
                                </tr>
                                <tr class="collapse button_bar_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = button_bar</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'background_color', 'button_color', 'button_background_color', 'border_color'] as $key): ?>
                                    <tr class="collapse button_bar_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = button_bar</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_background_color', 'dark_mode_button_color', 'dark_mode_button_background_color', 'dark_mode_border_color'] as $key): ?>
                                    <tr class="collapse button_bar_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = button_bar</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->collector_modal): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".collector_modal_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        collector_modal
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse collector_modal_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse collector_modal_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse collector_modal_collapse">
                                    <td>image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse collector_modal_collapse">
                                    <td>image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse collector_modal_collapse">
                                    <td>input_placeholder</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_modal</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse collector_modal_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_modal</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse collector_modal_collapse">
                                    <td>show_agreement</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse collector_modal_collapse">
                                    <td>agreement_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_modal</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse collector_modal_collapse">
                                    <td>agreement_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse collector_modal_collapse">
                                    <td>thank_you_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_modal</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'description_color', 'background_color', 'button_background_color', 'button_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse collector_modal_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_modal</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_button_background_color', 'dark_mode_button_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse collector_modal_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_modal</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>


                            <?php if(settings()->notifications->available_notifications->collector_two_modal): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".collector_two_modal_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        collector_two_modal
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse collector_two_modal_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_two_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse collector_two_modal_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_two_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse collector_two_modal_collapse">
                                    <td>image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_two_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse collector_two_modal_collapse">
                                    <td>image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_two_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse collector_two_modal_collapse">
                                    <td>input_placeholder</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_two_modal</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse collector_two_modal_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_two_modal</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse collector_two_modal_collapse">
                                    <td>show_agreement</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_two_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse collector_two_modal_collapse">
                                    <td>agreement_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_two_modal</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse collector_two_modal_collapse">
                                    <td>agreement_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_two_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse collector_two_modal_collapse">
                                    <td>thank_you_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_two_modal</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'description_color', 'background_color', 'button_background_color', 'button_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse collector_two_modal_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_two_modal</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_button_background_color', 'dark_mode_button_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse collector_two_modal_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = collector_two_modal</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>


                            <?php if(settings()->notifications->available_notifications->button_modal): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".button_modal_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        button_modal
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse button_modal_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = button_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse button_modal_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = button_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse button_modal_collapse">
                                    <td>image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = button_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse button_modal_collapse">
                                    <td>image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = button_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse button_modal_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = button_modal</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse button_modal_collapse">
                                    <td>button_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = button_modal</span>') ?></td>
                                </tr>
                                <tr class="collapse button_modal_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = button_modal</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'description_color', 'background_color', 'button_background_color', 'button_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse button_modal_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = button_modal</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_button_background_color', 'dark_mode_button_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse button_modal_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = button_modal</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->text_feedback): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".text_feedback_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        text_feedback
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse text_feedback_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = text_feedback</span>') ?></td>
                                </tr>
                                <tr class="collapse text_feedback_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = text_feedback</span>') ?></td>
                                </tr>
                                <tr class="collapse text_feedback_collapse">
                                    <td>input_placeholder</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = text_feedback</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse text_feedback_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = text_feedback</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse text_feedback_collapse">
                                    <td>thank_you_url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = text_feedback</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'description_color', 'background_color', 'button_background_color', 'button_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse text_feedback_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = text_feedback</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_button_background_color', 'dark_mode_button_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse text_feedback_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = text_feedback</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>





                            <?php if(settings()->notifications->available_notifications->engagement_links): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".engagement_links_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        engagement_links
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse engagement_links_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = engagement_links</span>') ?></td>
                                </tr>
                                <tr class="collapse engagement_links_collapse">
                                    <td>categories</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = engagement_links</span>') ?></td>
                                </tr>
                                <?php foreach(['title_color', 'categories_title_color', 'categories_description_color', 'categories_links_title_color', 'categories_links_description_color', 'categories_links_background_color', 'categories_links_border_color', 'background_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse text_feedback_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = text_feedback</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_categories_title_color', 'dark_mode_categories_description_color', 'dark_mode_categories_links_title_color', 'dark_mode_categories_links_description_color', 'dark_mode_categories_links_background_color', 'dark_mode_categories_links_border_color', 'dark_mode_background_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse text_feedback_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = text_feedback</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>




                            <?php if(settings()->notifications->available_notifications->whatsapp_chat): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".whatsapp_chat_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        whatsapp_chat
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse whatsapp_chat_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = whatsapp_chat</span>') ?></td>
                                </tr>
                                <tr class="collapse whatsapp_chat_collapse">
                                    <td>agent_image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = whatsapp_chat</span>') ?></td>
                                </tr>
                                <tr class="collapse whatsapp_chat_collapse">
                                    <td>agent_image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = whatsapp_chat</span>') ?></td>
                                </tr>
                                <tr class="collapse whatsapp_chat_collapse">
                                    <td>agent_name</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = whatsapp_chat</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse whatsapp_chat_collapse">
                                    <td>agent_description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = whatsapp_chat</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse whatsapp_chat_collapse">
                                    <td>agent_message</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = whatsapp_chat</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse whatsapp_chat_collapse">
                                    <td>agent_phone_number</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = whatsapp_chat</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse whatsapp_chat_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = whatsapp_chat</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>

                                <?php foreach(['header_agent_name_color', 'header_agent_description_color', 'header_background_color', 'content_background_color', 'content_agent_name_color', 'content_agent_message_color', 'content_agent_message_background_color', 'footer_background_color', 'footer_button_background_color', 'footer_button_color', 'title_color', 'background_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse whatsapp_chat_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = whatsapp_chat</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_header_agent_name_color', 'dark_mode_header_agent_description_color', 'dark_mode_header_background_color', 'dark_mode_content_background_color', 'dark_mode_content_agent_name_color', 'dark_mode_content_agent_message_color', 'dark_mode_content_agent_message_background_color', 'dark_mode_footer_background_color', 'dark_mode_footer_button_background_color', 'dark_mode_footer_button_color', 'dark_mode_title_color', 'dark_mode_background_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse whatsapp_chat_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = whatsapp_chat</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>

                            <?php if(isset(settings()->notifications->available_notifications->telegram_chat) ? settings()->notifications->available_notifications->telegram_chat : true): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".telegram_chat_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        telegram_chat
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse telegram_chat_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = telegram_chat</span>') ?></td>
                                </tr>
                                <tr class="collapse telegram_chat_collapse">
                                    <td>agent_image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = telegram_chat</span>') ?></td>
                                </tr>
                                <tr class="collapse telegram_chat_collapse">
                                    <td>agent_image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = telegram_chat</span>') ?></td>
                                </tr>
                                <tr class="collapse telegram_chat_collapse">
                                    <td>agent_name</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = telegram_chat</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse telegram_chat_collapse">
                                    <td>agent_description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = telegram_chat</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse telegram_chat_collapse">
                                    <td>agent_message</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = telegram_chat</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse telegram_chat_collapse">
                                    <td>agent_telegram</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = telegram_chat</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse telegram_chat_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = telegram_chat</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>

                                <?php foreach(['header_agent_name_color', 'header_agent_description_color', 'header_background_color', 'content_background_color', 'content_agent_name_color', 'content_agent_message_color', 'content_agent_message_background_color', 'footer_background_color', 'footer_button_background_color', 'footer_button_color', 'title_color', 'background_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse telegram_chat_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = telegram_chat</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_header_agent_name_color', 'dark_mode_header_agent_description_color', 'dark_mode_header_background_color', 'dark_mode_content_background_color', 'dark_mode_content_agent_name_color', 'dark_mode_content_agent_message_color', 'dark_mode_content_agent_message_background_color', 'dark_mode_footer_background_color', 'dark_mode_footer_button_background_color', 'dark_mode_footer_button_color', 'dark_mode_title_color', 'dark_mode_background_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse telegram_chat_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = telegram_chat</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>

                            <?php if(isset(settings()->notifications->available_notifications->facebook_messenger_chat) ? settings()->notifications->available_notifications->facebook_messenger_chat : true): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".facebook_messenger_chat_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        facebook_messenger_chat
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse facebook_messenger_chat_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = facebook_messenger_chat</span>') ?></td>
                                </tr>
                                <tr class="collapse facebook_messenger_chat_collapse">
                                    <td>agent_image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = facebook_messenger_chat</span>') ?></td>
                                </tr>
                                <tr class="collapse facebook_messenger_chat_collapse">
                                    <td>agent_image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = facebook_messenger_chat</span>') ?></td>
                                </tr>
                                <tr class="collapse facebook_messenger_chat_collapse">
                                    <td>agent_name</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = facebook_messenger_chat</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse facebook_messenger_chat_collapse">
                                    <td>agent_description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = facebook_messenger_chat</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse facebook_messenger_chat_collapse">
                                    <td>agent_message</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = facebook_messenger_chat</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse facebook_messenger_chat_collapse">
                                    <td>agent_facebook_messenger</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = facebook_messenger_chat</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>
                                <tr class="collapse facebook_messenger_chat_collapse">
                                    <td>button_text</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = facebook_messenger_chat</span>') ?> <?= l('api_documentation.notifications_translations_translatable') ?></span></td>
                                </tr>

                                <?php foreach(['header_agent_name_color', 'header_agent_description_color', 'header_background_color', 'content_background_color', 'content_agent_name_color', 'content_agent_message_color', 'content_agent_message_background_color', 'footer_background_color', 'footer_button_background_color', 'footer_button_color', 'title_color', 'background_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse facebook_messenger_chat_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = facebook_messenger_chat</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_header_agent_name_color', 'dark_mode_header_agent_description_color', 'dark_mode_header_background_color', 'dark_mode_content_background_color', 'dark_mode_content_agent_name_color', 'dark_mode_content_agent_message_color', 'dark_mode_content_agent_message_background_color', 'dark_mode_footer_background_color', 'dark_mode_footer_button_background_color', 'dark_mode_footer_button_color', 'dark_mode_title_color', 'dark_mode_background_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse facebook_messenger_chat_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = facebook_messenger_chat</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->badge): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".badge_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        badge
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse badge_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = badge</span>') ?></td>
                                </tr>
                                <tr class="collapse badge_collapse">
                                    <td>image</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = badge</span>') ?></td>
                                </tr>
                                <tr class="collapse badge_collapse">
                                    <td>image_alt</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = badge</span>') ?></td>
                                </tr>
                                <tr class="collapse badge_collapse">
                                    <td>url</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = badge</span>') ?></td>
                                </tr>
                                <tr class="collapse badge_collapse">
                                    <td>url_new_tab</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = badge</span>') ?></td>
                                </tr>

                                <tr class="collapse badge_collapse">
                                    <td>image_size</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = badge</span>') ?></td>
                                </tr>

                                <?php foreach(['background_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse badge_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = badge</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach([ 'dark_mode_background_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse badge_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = badge</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>



                            <?php if(settings()->notifications->available_notifications->contact_us): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="badge badge-light mr-1" data-toggle="collapse" data-target=".contact_us_collapse" data-tooltip title="<?= l('global.view') ?>" data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </a>
                                        contact_us
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="collapse contact_us_collapse">
                                    <td>title</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = contact_us</span>') ?></td>
                                </tr>
                                <tr class="collapse contact_us_collapse">
                                    <td>description</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = contact_us</span>') ?></td>
                                </tr>
                                <tr class="collapse contact_us_collapse">
                                    <td>contact_email</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = contact_us</span>') ?></td>
                                </tr>
                                <tr class="collapse contact_us_collapse">
                                    <td>contact_phone_number</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = contact_us</span>') ?></td>
                                </tr>
                                <tr class="collapse contact_us_collapse">
                                    <td>contact_whatsapp</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = contact_us</span>') ?></td>
                                </tr>
                                <tr class="collapse contact_us_collapse">
                                    <td>contact_telegram</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = contact_us</span>') ?></td>
                                </tr>
                                <tr class="collapse contact_us_collapse">
                                    <td>contact_facebook_messenger</td>
                                    <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                    <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = contact_us</span>') ?></td>
                                </tr>

                                <?php foreach(['title_color', 'description_color', 'background_color', 'border_color', 'shadow_color'] as $key): ?>
                                    <tr class="collapse contact_us_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = contact_us</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach(['dark_mode_title_color', 'dark_mode_description_color', 'dark_mode_background_color', 'dark_mode_border_color', 'dark_mode_shadow_color'] as $key): ?>
                                    <tr class="collapse contact_us_collapse">
                                        <td><?= $key ?></td>
                                        <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                        <td><?= sprintf(l('api_documentation.available_when'), '<span class="badge badge-light">type = contact_us</span>') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request POST \<br />
                                --url '<?= SITE_URL ?>api/notifications/<span class="text-primary">{notification_id}</span>' \<br />
                                --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \<br />
                                --header 'Content-Type: multipart/form-data' \<br />
                                --form 'name=<span class="text-primary">example</span>' \<br />
                                --form 'is_enabled=<span class="text-primary">1</span>'
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.response') ?></label>
                        <pre data-shiki="json">
{
    "data": {
        "id": 1
    }
}</pre>
                    </div>
                </div>

            </div>
        </div>


        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#delete" aria-expanded="true" aria-controls="delete">
                        <span class="badge badge-danger user-select-none mr-3"><i class="fas fa-fw fa-sm fa-trash-alt"></i></span> <?= l('api_documentation.delete') ?>
                    </a>
                </h3>
            </div>

            <div id="delete" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-danger user-select-none mr-3">DELETE</span> <span class="text-muted"><?= SITE_URL ?>api/notifications/</span><span class="text-primary">{notification_id}</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request DELETE \<br />
                                --url '<?= SITE_URL ?>api/notifications/<span class="text-primary">{notification_id}</span>' \<br />
                                --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \<br />
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <?php require THEME_PATH . 'views/partials/shiki_highlighter.php' ?>
