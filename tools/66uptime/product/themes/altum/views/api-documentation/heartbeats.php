<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li><a href="<?= url() ?>"><?= l('index.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li><a href="<?= url('api-documentation') ?>"><?= l('api_documentation.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li class="active" aria-current="page"><?= l('heartbeats.title') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 mb-4"><?= l('heartbeats.title') ?></h1>

    <div class="accordion">
        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#heartbeats_read_all" aria-expanded="true" aria-controls="heartbeats_read_all">
                        <span class="badge badge-success user-select-none mr-3"><i class="fas fa-fw fa-sm fa-list"></i></span> <?= l('api_documentation.read_all') ?>
                    </a>
                </h3>
            </div>

            <div id="heartbeats_read_all" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-success user-select-none mr-3">GET</span> <span class="text-muted"><?= SITE_URL ?>api/heartbeats/</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request GET \<br />
                                --url '<?= SITE_URL ?>api/heartbeats/' \<br />
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

                            <?php if(settings()->monitors_heartbeats->projects_is_enabled): ?>
                                <tr>
                                    <td>project_id</td>
                                    <td>
                                        <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                        <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                    </td>
                                    <td></td>
                                </tr>
                            <?php endif ?>

                            <tr>
                                <td>datetime_field</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-font mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['datetime', 'last_datetime', 'last_run_datetime']) . '</code>') ?></td>
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
                                <td><?= sprintf(l('api_documentation.filters.order_by'), '<code>' . implode('</code>, <code>', ['heartbeat_id', 'datetime', 'last_datetime', 'last_run_datetime', 'name', 'uptime']) . '</code>') ?></td>
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
            "project_id": 0,
            "name": "Demo cron",
            "code": "12345678901112131415",
            "settings": {
                "run_interval": 1,
                "run_interval_type": "minutes",
                "run_interval_grace": 1,
                "run_interval_grace_type": "seconds"
            },
            "is_ok": 1,
            "uptime": 75.5,
            "downtime": 24.5,
            "total_runs": 50,
            "total_missed_runs": 15,
            "last_run_datetime": "2021-03-30 19:17:22",
            "notifications": {
                "email_is_enabled": 0,
                "webhook": "",
                "slack": "",
                "twilio": ""
            },
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
        "first": "<?= SITE_URL ?>api/heartbeats?page=1",
        "last": "<?= SITE_URL ?>api/heartbeats?page=1",
        "next": null,
        "prev": null,
        "self": "<?= SITE_URL ?>api/heartbeats?page=1"
    }
}
</pre>
                    </div>
                </div>
            </div>
        </div>


        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#heartbeats_read" aria-expanded="true" aria-controls="heartbeats_read">
                        <span class="badge badge-success user-select-none mr-3"><i class="fas fa-fw fa-sm fa-eye"></i></span> <?= l('api_documentation.read') ?>
                    </a>
                </h3>
            </div>

            <div id="heartbeats_read" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-success user-select-none mr-3">GET</span> <span class="text-muted"><?= SITE_URL ?>api/heartbeats/</span><span class="text-primary">{heartbeat_id}</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request GET \<br />
                                --url '<?= SITE_URL ?>api/heartbeats/<span class="text-primary">{heartbeat_id}</span>' \<br />
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
        "project_id": 0,
        "name": "Demo cron",
        "code": "12345678901112131415",
        "settings": {
            "run_interval": 1,
            "run_interval_type": "minutes",
            "run_interval_grace": 1,
            "run_interval_grace_type": "seconds"
        },
        "is_ok": 1,
        "uptime": 75.5,
        "downtime": 24.5,
        "total_runs": 50,
        "total_missed_runs": 15,
        "last_run_datetime": "2021-03-30 19:17:22",
        "notifications": {
            "email_is_enabled": 0,
            "webhook": "",
            "slack": "",
            "twilio": ""
        },
        "is_enabled": true,
        "datetime": "<?= get_date() ?>",
    }
}
</pre>
                    </div>
                </div>
            </div>
        </div>


        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#heartbeats_create" aria-expanded="true" aria-controls="projects_create">
                        <span class="badge badge-info user-select-none mr-3"><i class="fas fa-fw fa-sm fa-plus"></i></span> <?= l('api_documentation.create') ?>
                    </a>
                </h3>
            </div>

            <div id="heartbeats_create" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-info user-select-none mr-3">POST</span> <span class="text-muted"><?= SITE_URL ?>api/heartbeats</span>
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
                                    <span class="badge badge-danger"><i class="fas fa-fw fa-sm fa-asterisk mr-1"></i> <?= l('api_documentation.required') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>run_interval</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>run_interval_type</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['seconds', 'minutes', 'hours', 'days']) . '</code>') ?></td>
                            </tr>
                            <tr>
                                <td>run_interval_grace</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>run_interval_grace_type</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['seconds', 'minutes', 'hours', 'days']) . '</code>') ?></td>
                            </tr>
                            <tr>
                                <td>is_ok_notifications</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-list mr-1"></i> <?= l('api_documentation.array') ?></span>
                                </td>
                                <td><?= l('api_documentation.notifications_handlers_ids') ?></td>
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
                                --url '<?= SITE_URL ?>api/heartbeats' \<br />
                                --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \<br />
                                --header 'Content-Type: multipart/form-data' \<br />
                                --form 'name=<span class="text-primary">Example</span>' \<br />
                                --form 'target=<span class="text-primary">example.com</span>' \<br />
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
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#heartbeats_update" aria-expanded="true" aria-controls="projects_update">
                        <span class="badge badge-info user-select-none mr-3"><i class="fas fa-fw fa-sm fa-pencil"></i></span> <?= l('api_documentation.update') ?>
                    </a>
                </h3>
            </div>

            <div id="heartbeats_update" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-info user-select-none mr-3">POST</span> <span class="text-muted"><?= SITE_URL ?>api/heartbeats/</span><span class="text-primary">{heartbeat_id}</span>
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
                                <td>run_interval</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>run_interval_type</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['seconds', 'minutes', 'hours', 'days']) . '</code>') ?></td>
                            </tr>
                            <tr>
                                <td>run_interval_grace</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>run_interval_grace_type</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['seconds', 'minutes', 'hours', 'days']) . '</code>') ?></td>
                            </tr>
                            <tr>
                                <td>is_ok_notifications</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-list mr-1"></i> <?= l('api_documentation.array') ?></span>
                                </td>
                                <td><?= l('api_documentation.notifications_handlers_ids') ?></td>
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
                                --url '<?= SITE_URL ?>api/heartbeats/<span class="text-primary">{heartbeat_id}</span>' \<br />
                                --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \<br />
                                --header 'Content-Type: multipart/form-data' \<br />
                                --form 'name=<span class="text-primary">Example</span>' \<br />
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
}
</pre>
                    </div>
                </div>

            </div>
        </div>


        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#heartbeats_delete" aria-expanded="true" aria-controls="heartbeats_delete">
                        <span class="badge badge-danger user-select-none mr-3"><i class="fas fa-fw fa-sm fa-trash-alt"></i></span> <?= l('api_documentation.delete') ?>
                    </a>
                </h3>
            </div>

            <div id="heartbeats_delete" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-danger user-select-none mr-3">DELETE</span> <span class="text-muted"><?= SITE_URL ?>api/heartbeats/</span><span class="text-primary">{heartbeat_id}</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request DELETE \<br />
                                --url '<?= SITE_URL ?>api/heartbeats/<span class="text-primary">{heartbeat_id}</span>' \<br />
                                --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \<br />
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require THEME_PATH . 'views/partials/shiki_highlighter.php' ?>
