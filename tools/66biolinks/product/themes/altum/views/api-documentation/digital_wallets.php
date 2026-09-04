<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li><a href="<?= url() ?>"><?= l('index.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li><a href="<?= url('api-documentation') ?>"><?= l('api_documentation.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li class="active" aria-current="page"><?= l('digital_wallets.title') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 mb-4"><?= l('digital_wallets.title') ?></h1>

    <div class="accordion">
        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#digital_wallets_read_all" aria-expanded="true" aria-controls="digital_wallets_read_all">
                        <span class="badge badge-success user-select-none mr-3"><i class="fas fa-fw fa-sm fa-list"></i></span> <?= l('api_documentation.read_all') ?>
                    </a>
                </h3>
            </div>

            <div id="digital_wallets_read_all" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-success user-select-none mr-3">GET</span> <span class="text-muted"><?= SITE_URL ?>api/digital-wallets/</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request GET \<br />
                                --url '<?= SITE_URL ?>api/digital-wallets/' \<br />
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
                                <td>domain_id</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= l('domains.domain_id') ?></td>
                            </tr>
                            <tr>
                                <td>link_id</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= l('links.link_id') ?></td>
                            </tr>
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
                                <td><?= sprintf(l('api_documentation.filters.search_by'), '<code>name</code>') ?></td>
                            </tr>
                            <tr>
                                <td>datetime_field</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-font mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>datetime</code>, <code>last_datetime</code>') ?></td>
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
                                <td><?= sprintf(l('api_documentation.filters.order_by'), '<code>digital_wallet_id</code>, <code>domain_id</code>, <code>link_id</code>, <code>pageviews</code>, <code>last_datetime</code>, <code>name</code>, <code>datetime</code>') ?></td>
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
                                <td><?= sprintf(l('api_documentation.filters.results_per_page'), '<code>10</code>, <code>25</code>, <code>50</code>, <code>100</code>, <code>250</code>, <code>500</code>, <code>1000</code>', 25) ?></td>
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
            "hash": "aB3dE5gH7jK9mN2p",
            "wallet_urls": {
                "google": "<?= SITE_URL ?>digital-wallet-add/aB3dE5gH7jK9mN2p?provider=google",
                "apple": "<?= SITE_URL ?>digital-wallet-add/aB3dE5gH7jK9mN2p?provider=apple"
            },
            "user_id": 1,
            "domain_id": null,
            "link_id": null,
            "location_url": "https://example.com",
            "name": "Business card",
            "pageviews": 0,
            "settings": {
                "title": "PHP Developer",
                "subtitle": "AltumCode",
                "logo": "logo.png",
                "image": "banner.png",
                "background_color": "#111827",
                "phone": "+40 000 000 000",
                "email": "hello@example.com",
                "website": "https://example.com"
            },
            "last_datetime": null,
            "datetime": "<?= get_date() ?>"
        }
    ],
    "meta": {
        "page": 1,
        "total_pages": 1,
        "results_per_page": 25,
        "total_results": 1
    },
    "links": {
        "first": "<?= SITE_URL ?>api/digital-wallets?page=1",
        "last": "<?= SITE_URL ?>api/digital-wallets?page=1",
        "next": null,
        "prev": null,
        "self": "<?= SITE_URL ?>api/digital-wallets?page=1"
    }
}</pre>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#digital_wallets_read" aria-expanded="true" aria-controls="digital_wallets_read">
                        <span class="badge badge-success user-select-none mr-3"><i class="fas fa-fw fa-sm fa-eye"></i></span> <?= l('api_documentation.read') ?>
                    </a>
                </h3>
            </div>

            <div id="digital_wallets_read" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-success user-select-none mr-3">GET</span> <span class="text-muted"><?= SITE_URL ?>api/digital-wallets/</span><span class="text-primary">{digital_wallet_id}</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request GET \<br />
                                --url '<?= SITE_URL ?>api/digital-wallets/<span class="text-primary">{digital_wallet_id}</span>' \<br />
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
        "hash": "aB3dE5gH7jK9mN2p",
        "wallet_urls": {
            "google": "<?= SITE_URL ?>digital-wallet-add/aB3dE5gH7jK9mN2p?provider=google",
            "apple": "<?= SITE_URL ?>digital-wallet-add/aB3dE5gH7jK9mN2p?provider=apple"
        },
        "user_id": 1,
        "domain_id": null,
        "link_id": null,
        "location_url": "https://example.com",
        "name": "Business card",
        "pageviews": 0,
        "settings": {
            "title": "PHP Developer",
            "subtitle": "AltumCode",
            "logo": "logo.png",
            "image": "banner.png",
            "background_color": "#111827",
            "phone": "+40 000 000 000",
            "email": "hello@example.com",
            "website": "https://example.com"
        },
        "last_datetime": null,
        "datetime": "<?= get_date() ?>"
    }
}</pre>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#digital_wallets_create" aria-expanded="true" aria-controls="digital_wallets_create">
                        <span class="badge badge-info user-select-none mr-3"><i class="fas fa-fw fa-sm fa-plus"></i></span> <?= l('api_documentation.create') ?>
                    </a>
                </h3>
            </div>

            <div id="digital_wallets_create" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-info user-select-none mr-3">POST</span> <span class="text-muted"><?= SITE_URL ?>api/digital-wallets</span>
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
                                <td><span class="badge badge-danger"><i class="fas fa-fw fa-sm fa-asterisk mr-1"></i> <?= l('api_documentation.required') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('digital_wallets.name_help') ?></td>
                            </tr>
                            <tr>
                                <td>title</td>
                                <td><span class="badge badge-danger"><i class="fas fa-fw fa-sm fa-asterisk mr-1"></i> <?= l('api_documentation.required') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('digital_wallets.title_help') ?></td>
                            </tr>
                            <tr>
                                <td>location_url</td>
                                <td><span class="badge badge-danger"><i class="fas fa-fw fa-sm fa-asterisk mr-1"></i> <?= l('api_documentation.required') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('digital_wallets.location_url_help') ?></td>
                            </tr>
                            <tr>
                                <td>link_id</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span></td>
                                <td><?= l('digital_wallets.link_id_help') ?></td>
                            </tr>
                            <tr>
                                <td>subtitle</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('digital_wallets.subtitle_help') ?></td>
                            </tr>
                            <tr>
                                <td>logo</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                <td><?= l('digital_wallets.logo') ?></td>
                            </tr>
                            <tr>
                                <td>image</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                <td><?= l('digital_wallets.image_help') ?></td>
                            </tr>
                            <tr>
                                <td>background_color</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('digital_wallets.background_color') ?></td>
                            </tr>
                            <tr>
                                <td>phone</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('global.phone') ?></td>
                            </tr>
                            <tr>
                                <td>email</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('global.email') ?></td>
                            </tr>
                            <tr>
                                <td>website</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('digital_wallets.website') ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request POST \<br />
                                --url '<?= SITE_URL ?>api/digital-wallets' \<br />
                                --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \<br />
                                --header 'Content-Type: multipart/form-data' \<br />
                                --form 'name=<span class="text-primary">Business card</span>' \<br />
                                --form 'title=<span class="text-primary">PHP Developer</span>' \<br />
                                --form 'subtitle=<span class="text-primary">AltumCode</span>' \<br />
                                --form 'location_url=<span class="text-primary">https://example.com</span>' \<br />
                                --form 'background_color=<span class="text-primary">#111827</span>' \<br />
                                --form 'email=<span class="text-primary">hello@example.com</span>'
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.response') ?></label>
                        <pre data-shiki="json">
{
    "data": {
        "id": 1,
        "hash": "aB3dE5gH7jK9mN2p",
        "wallet_urls": {
            "google": "<?= SITE_URL ?>digital-wallet-add/aB3dE5gH7jK9mN2p?provider=google",
            "apple": "<?= SITE_URL ?>digital-wallet-add/aB3dE5gH7jK9mN2p?provider=apple"
        },
        "user_id": 1,
        "domain_id": null,
        "link_id": null,
        "location_url": "https://example.com",
        "name": "Business card",
        "pageviews": 0,
        "settings": {
            "title": "PHP Developer",
            "subtitle": "AltumCode",
            "logo": "logo.png",
            "image": "banner.png",
            "background_color": "#111827",
            "phone": "+40 000 000 000",
            "email": "hello@example.com",
            "website": "https://example.com"
        },
        "last_datetime": null,
        "datetime": "<?= get_date() ?>"
    }
}</pre>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#digital_wallets_update" aria-expanded="true" aria-controls="digital_wallets_update">
                        <span class="badge badge-info user-select-none mr-3"><i class="fas fa-fw fa-sm fa-pencil"></i></span> <?= l('api_documentation.update') ?>
                    </a>
                </h3>
            </div>

            <div id="digital_wallets_update" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-info user-select-none mr-3">POST</span> <span class="text-muted"><?= SITE_URL ?>api/digital-wallets/</span><span class="text-primary">{digital_wallet_id}</span>
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
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('digital_wallets.name_help') ?></td>
                            </tr>
                            <tr>
                                <td>title</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('digital_wallets.title_help') ?></td>
                            </tr>
                            <tr>
                                <td>location_url</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('digital_wallets.location_url_help') ?></td>
                            </tr>
                            <tr>
                                <td>link_id</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span></td>
                                <td><?= l('digital_wallets.link_id_help') ?></td>
                            </tr>
                            <tr>
                                <td>subtitle</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('digital_wallets.subtitle_help') ?></td>
                            </tr>
                            <tr>
                                <td>logo</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                <td><?= l('digital_wallets.logo') ?></td>
                            </tr>
                            <tr>
                                <td>image</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-file mr-1"></i> <?= l('api_documentation.file') ?></span></td>
                                <td><?= l('digital_wallets.image_help') ?></td>
                            </tr>
                            <tr>
                                <td>background_color</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('digital_wallets.background_color') ?></td>
                            </tr>
                            <tr>
                                <td>phone</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('global.phone') ?></td>
                            </tr>
                            <tr>
                                <td>email</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('global.email') ?></td>
                            </tr>
                            <tr>
                                <td>website</td>
                                <td><span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span> <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span></td>
                                <td><?= l('digital_wallets.website') ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request POST \<br />
                                --url '<?= SITE_URL ?>api/digital-wallets/<span class="text-primary">{digital_wallet_id}</span>' \<br />
                                --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \<br />
                                --header 'Content-Type: multipart/form-data' \<br />
                                --form 'name=<span class="text-primary">Updated business card</span>' \<br />
                                --form 'title=<span class="text-primary">Senior PHP Developer</span>'
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.response') ?></label>
                        <pre data-shiki="json">
{
    "data": {
        "id": 1,
        "hash": "aB3dE5gH7jK9mN2p",
        "wallet_urls": {
            "google": "<?= SITE_URL ?>digital-wallet-add/aB3dE5gH7jK9mN2p?provider=google",
            "apple": "<?= SITE_URL ?>digital-wallet-add/aB3dE5gH7jK9mN2p?provider=apple"
        },
        "user_id": 1,
        "domain_id": null,
        "link_id": null,
        "location_url": "https://example.com",
        "name": "Updated business card",
        "pageviews": 0,
        "settings": {
            "title": "Senior PHP Developer",
            "subtitle": "AltumCode",
            "logo": "logo.png",
            "image": "banner.png",
            "background_color": "#111827",
            "phone": "+40 000 000 000",
            "email": "hello@example.com",
            "website": "https://example.com"
        },
        "last_datetime": "<?= get_date() ?>",
        "datetime": "<?= get_date() ?>"
    }
}</pre>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#digital_wallets_delete" aria-expanded="true" aria-controls="digital_wallets_delete">
                        <span class="badge badge-danger user-select-none mr-3"><i class="fas fa-fw fa-sm fa-trash-alt"></i></span> <?= l('api_documentation.delete') ?>
                    </a>
                </h3>
            </div>

            <div id="digital_wallets_delete" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-danger user-select-none mr-3">DELETE</span> <span class="text-muted"><?= SITE_URL ?>api/digital-wallets/</span><span class="text-primary">{digital_wallet_id}</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request DELETE \<br />
                                --url '<?= SITE_URL ?>api/digital-wallets/<span class="text-primary">{digital_wallet_id}</span>' \<br />
                                --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require THEME_PATH . 'views/partials/shiki_highlighter.php' ?>
