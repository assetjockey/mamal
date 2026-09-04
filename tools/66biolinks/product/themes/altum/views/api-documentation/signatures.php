<?php defined('ALTUMCODE') || die() ?>

<div class="container">
	<?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li><a href="<?= url() ?>"><?= l('index.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li><a href="<?= url('api-documentation') ?>"><?= l('api_documentation.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li class="active" aria-current="page"><?= l('signatures.title') ?></li>
            </ol>
        </nav>
	<?php endif ?>

    <h1 class="h4 mb-4"><?= l('signatures.title') ?></h1>

    <div class="accordion">
        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#signatures_read_all" aria-expanded="true" aria-controls="signatures_read_all">
                        <span class="badge badge-success user-select-none mr-3"><i class="fas fa-fw fa-sm fa-list"></i></span> <?= l('api_documentation.read_all') ?>
                    </a>
                </h3>
            </div>

            <div id="signatures_read_all" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-success user-select-none mr-3">GET</span> <span class="text-muted"><?= SITE_URL ?>api/signatures/</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request GET \<br />
                                --url '<?= SITE_URL ?>api/signatures/' \<br />
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
                                <td><?= sprintf(l('api_documentation.filters.order_by'), '<code>' . implode('</code>, <code>', ['signature_id', 'datetime', 'last_datetime', 'name']) . '</code>') ?></td>
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
            "project_id": 0,
            "name": "John Doe",
            "template": "mars",
            "settings": {
                "direction": "ltr",
                "is_removed_branding": true,
                "image_url": "",
                "sign_off": "Regards,",
                "full_name": "John Doe",
                "job_title": "Software Engineer",
                "department": "Engineering",
                "company": "Example Company",
                "email": "john@example.com",
                "website_name": "Example",
                "website_url": "https:\/\/example.com\/",
                "address": "123 Example Street",
                "address_url": "",
                "phone_number": "",
                "whatsapp": "",
                "facebook_messenger": "",
                "telegram": "",
                "disclaimer": "",
                "font_family": "arial",
                "font_size": 14,
                "width": 500,
                "image_width": 50,
                "image_border_radius": 0,
                "socials_width": 20,
                "socials_padding": 10,
                "separator_size": 0,
                "border_radius": 10,
                "background_color": "#f9f9f9",
                "border_color": "#e0e0e0",
                "theme_color": "#000000",
                "full_name_color": "#000000",
                "text_color": "#000000",
                "link_color": "#000000",
                "facebook": "johndoe",
                "threads": "johndoe",
                "x": "johndoe",
                "instagram": "johndoe",
                "youtube": "johndoe",
                "tiktok": "",
                "spotify": "",
                "pinterest": "",
                "linkedin": "",
                "snapchat": "",
                "twitch": "",
                "discord": "",
                "github": "",
                "reddit": ""
            },
            "last_datetime": null,
            "datetime": "<?= get_date() ?>"
        }
    ],
    "meta": {
        "page": 1,
        "results_per_page": 25,
        "total_results": 1,
        "total_pages": 1
    },
    "links": {
        "first": "<?= SITE_URL ?>api/signatures?page=1",
        "last": "<?= SITE_URL ?>api/signatures?page=1",
        "next": null,
        "prev": null,
        "self": "<?= SITE_URL ?>api/signatures?page=1"
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
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#signatures_read" aria-expanded="true" aria-controls="signatures_read">
                        <span class="badge badge-success user-select-none mr-3"><i class="fas fa-fw fa-sm fa-eye"></i></span> <?= l('api_documentation.read') ?>
                    </a>
                </h3>
            </div>

            <div id="signatures_read" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-success user-select-none mr-3">GET</span> <span class="text-muted"><?= SITE_URL ?>api/signatures/</span><span class="text-primary">{signature_id}</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request GET \<br />
                                --url '<?= SITE_URL ?>api/signatures/<span class="text-primary">{signature_id}</span>' \<br />
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
        "project_id": 0,
        "name": "Jane Doe",
        "template": "mars",
        "settings": {
            "direction": "ltr",
            "is_removed_branding": true,
            "image_url": "",
            "sign_off": "Regards,",
            "full_name": "Jane Doe",
            "job_title": "Software Engineer",
            "department": "Engineering",
            "company": "Example Company",
            "email": "jane@example.com",
            "website_name": "Example",
            "website_url": "https:\/\/example.com\/",
            "address": "123 Example Street",
            "address_url": "",
            "phone_number": "",
            "whatsapp": "",
            "facebook_messenger": "",
            "telegram": "",
            "disclaimer": "",
            "font_family": "arial",
            "font_size": 14,
            "width": 500,
            "image_width": 50,
            "image_border_radius": 0,
            "socials_width": 20,
            "socials_padding": 10,
            "separator_size": 0,
            "border_radius": 10,
            "background_color": "#f9f9f9",
            "border_color": "#e0e0e0",
            "theme_color": "#000000",
            "full_name_color": "#000000",
            "text_color": "#000000",
            "link_color": "#000000",
            "facebook": "janedoe",
            "threads": "janedoe",
            "x": "janedoe",
            "instagram": "janedoe",
            "youtube": "janedoe",
            "tiktok": "",
            "spotify": "",
            "pinterest": "",
            "linkedin": "",
            "snapchat": "",
            "twitch": "",
            "discord": "",
            "github": "",
            "reddit": ""
        },
        "last_datetime": null,
        "datetime": "<?= get_date() ?>"
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
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#signatures_create" aria-expanded="true" aria-controls="signatures_create">
                        <span class="badge badge-info user-select-none mr-3"><i class="fas fa-fw fa-sm fa-plus"></i></span> <?= l('api_documentation.create') ?>
                    </a>
                </h3>
            </div>

            <div id="signatures_create" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-info user-select-none mr-3">POST</span> <span class="text-muted"><?= SITE_URL ?>api/signatures</span>
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
                                <td><?= l('global.name') ?></td>
                            </tr>
							<?php if(settings()->links->projects_is_enabled || settings()->tools->projects_is_enabled): ?>
                            <tr>
                                <td>project_id</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= l('projects.project_id_help') ?></td>
                            </tr>
                            <?php endif ?>
                            <tr>
                                <td>template</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['plain_text', 'mars', 'mercury', 'venus', 'nova']) . '</code>') ?></td>
                            </tr>
                            <tr>
                                <td>direction</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>ltr</code>, <code>rtl</code>') ?></td>
                            </tr>
                            <tr>
                                <td>is_removed_branding</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span>
                                </td>
                                <td><?= l('signatures.is_removed_branding') ?></td>
                            </tr>
                            <tr>
                                <td>image_url</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.image_url_help') ?></td>
                            </tr>
                            <tr>
                                <td>sign_off</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.sign_off') ?></td>
                            </tr>
                            <tr>
                                <td>full_name</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.full_name') ?></td>
                            </tr>
                            <tr>
                                <td>job_title</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.job_title') ?></td>
                            </tr>
                            <tr>
                                <td>department</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.department') ?></td>
                            </tr>
                            <tr>
                                <td>company</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.company') ?></td>
                            </tr>
                            <tr>
                                <td>email</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('global.email') ?></td>
                            </tr>
                            <tr>
                                <td>website_name</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.website_name') ?></td>
                            </tr>
                            <tr>
                                <td>website_url</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.website_url') ?></td>
                            </tr>
                            <tr>
                                <td>address</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.address') ?></td>
                            </tr>
                            <tr>
                                <td>address_url</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.address_url_help') ?></td>
                            </tr>
                            <tr>
                                <td>phone_number</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.phone_number') ?></td>
                            </tr>
                            <tr>
                                <td>whatsapp</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.whatsapp') ?></td>
                            </tr>
                            <tr>
                                <td>facebook_messenger</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.facebook_messenger') ?></td>
                            </tr>
                            <tr>
                                <td>telegram</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.telegram') ?></td>
                            </tr>
                            <tr>
                                <td>disclaimer</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.disclaimer') ?></td>
                            </tr>
                            <tr>
                                <td>font_family</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['arial', 'verdana', 'helvetica', 'times_new_roman', 'georgia']) . '</code>') ?></td>
                            </tr>
                            <tr>
                                <td>font_size</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>12-18</code>') ?></td>
                            </tr>
                            <tr>
                                <td>width</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>300-600</code>') ?></td>
                            </tr>
                            <tr>
                                <td>image_width</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>45-150</code>') ?></td>
                            </tr>
                            <tr>
                                <td>image_border_radius</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>0-100</code>') ?></td>
                            </tr>
                            <tr>
                                <td>socials_width</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>15-30</code>') ?></td>
                            </tr>
                            <tr>
                                <td>socials_padding</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>5-15</code>') ?></td>
                            </tr>
                            <tr>
                                <td>separator_size</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>0-5</code>') ?></td>
                            </tr>
                            <tr>
                                <td>border_radius</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>0-30</code>') ?></td>
                            </tr>
                            <tr>
                                <td>background_color</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.background_color') ?></td>
                            </tr>
                            <tr>
                                <td>border_color</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.border_color') ?></td>
                            </tr>
                            <tr>
                                <td>theme_color</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.theme_color') ?></td>
                            </tr>
                            <tr>
                                <td>full_name_color</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.full_name_color') ?></td>
                            </tr>
                            <tr>
                                <td>text_color</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.text_color') ?></td>
                            </tr>
                            <tr>
                                <td>link_color</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.link_color') ?></td>
                            </tr>
                            <tr>
                                <td>facebook</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.facebook') ?></td>
                            </tr>
                            <tr>
                                <td>threads</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.threads') ?></td>
                            </tr>
                            <tr>
                                <td>x</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.x') ?></td>
                            </tr>
                            <tr>
                                <td>instagram</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.instagram') ?></td>
                            </tr>
                            <tr>
                                <td>youtube</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.youtube') ?></td>
                            </tr>
                            <tr>
                                <td>tiktok</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.tiktok') ?></td>
                            </tr>
                            <tr>
                                <td>spotify</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.spotify') ?></td>
                            </tr>
                            <tr>
                                <td>pinterest</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.pinterest') ?></td>
                            </tr>
                            <tr>
                                <td>linkedin</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.linkedin') ?></td>
                            </tr>
                            <tr>
                                <td>snapchat</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.snapchat') ?></td>
                            </tr>
                            <tr>
                                <td>twitch</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.twitch') ?></td>
                            </tr>
                            <tr>
                                <td>discord</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.discord') ?></td>
                            </tr>
                            <tr>
                                <td>github</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.github') ?></td>
                            </tr>
                            <tr>
                                <td>reddit</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.reddit') ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request POST \<br />
                                --url '<?= SITE_URL ?>api/signatures' \<br />
                                --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \<br />
                                --header 'Content-Type: multipart/form-data' \<br />
                                --form 'name=<span class="text-primary">Jane Doe</span>' \<br />
                                --form 'template=<span class="text-primary">nova</span>' \<br />
                                --form 'full_name=<span class="text-primary">Jane Doe</span>' \<br />
                                --form 'job_title=<span class="text-primary">Software Engineer</span>' \<br />
                                --form 'company=<span class="text-primary">Example Company</span>' \<br />
                                --form 'email=<span class="text-primary">jane@example.com</span>' \<br />
                                --form 'background_color=<span class="text-primary">#f8fafc</span>' \<br />
                                --form 'border_color=<span class="text-primary">#cbd5e1</span>' \<br />
                                --form 'theme_color=<span class="text-primary">#2563eb</span>'
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
        "project_id": 0,
        "name": "Jane Doe",
        "template": "nova",
        "settings": {
            "direction": "ltr",
            "is_removed_branding": false,
            "image_url": "",
            "sign_off": "Regards,",
            "full_name": "Jane Doe",
            "job_title": "Software Engineer",
            "department": "",
            "company": "Example Company",
            "email": "jane@example.com",
            "website_name": "",
            "website_url": "",
            "address": "",
            "address_url": "",
            "phone_number": "",
            "whatsapp": null,
            "facebook_messenger": "",
            "telegram": "",
            "disclaimer": "",
            "font_family": "arial",
            "font_size": 14,
            "width": 500,
            "image_width": 50,
            "image_border_radius": 0,
            "socials_width": 20,
            "socials_padding": 10,
            "separator_size": 1,
            "border_radius": 10,
            "background_color": "#f8fafc",
            "border_color": "#cbd5e1",
            "theme_color": "#2563eb",
            "full_name_color": "#000000",
            "text_color": "#000000",
            "link_color": "#000000",
            "facebook": "",
            "threads": "",
            "x": "",
            "instagram": "",
            "youtube": "",
            "tiktok": "",
            "spotify": "",
            "pinterest": "",
            "linkedin": "",
            "snapchat": "",
            "twitch": "",
            "discord": "",
            "github": "",
            "reddit": ""
        },
        "last_datetime": null,
        "datetime": "<?= get_date() ?>"
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
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#signatures_update" aria-expanded="true" aria-controls="signatures_update">
                        <span class="badge badge-info user-select-none mr-3"><i class="fas fa-fw fa-sm fa-pencil"></i></span> <?= l('api_documentation.update') ?>
                    </a>
                </h3>
            </div>

            <div id="signatures_update" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-info user-select-none mr-3">POST</span> <span class="text-muted"><?= SITE_URL ?>api/signatures/</span><span class="text-primary">{signature_id}</span>
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
                                <td><?= l('global.name') ?></td>
                            </tr>
                            <?php if(settings()->links->projects_is_enabled || settings()->tools->projects_is_enabled): ?>
                            <tr>
                                <td>project_id</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= l('projects.project_id_help') ?></td>
                            </tr>
                            </tbody>
                            <?php endif ?>
                            <tr>
                                <td>template</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['plain_text', 'mars', 'mercury', 'venus', 'nova']) . '</code>') ?></td>
                            </tr>
                            <tr>
                                <td>direction</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>ltr</code>, <code>rtl</code>') ?></td>
                            </tr>
                            <tr>
                                <td>is_removed_branding</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-toggle-on mr-1"></i> <?= l('api_documentation.boolean') ?></span>
                                </td>
                                <td><?= l('signatures.is_removed_branding') ?></td>
                            </tr>
                            <tr>
                                <td>image_url</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.image_url_help') ?></td>
                            </tr>
                            <tr>
                                <td>sign_off</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.sign_off') ?></td>
                            </tr>
                            <tr>
                                <td>full_name</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.full_name') ?></td>
                            </tr>
                            <tr>
                                <td>job_title</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.job_title') ?></td>
                            </tr>
                            <tr>
                                <td>department</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.department') ?></td>
                            </tr>
                            <tr>
                                <td>company</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.company') ?></td>
                            </tr>
                            <tr>
                                <td>email</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('global.email') ?></td>
                            </tr>
                            <tr>
                                <td>website_name</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.website_name') ?></td>
                            </tr>
                            <tr>
                                <td>website_url</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.website_url') ?></td>
                            </tr>
                            <tr>
                                <td>address</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.address') ?></td>
                            </tr>
                            <tr>
                                <td>address_url</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.address_url_help') ?></td>
                            </tr>
                            <tr>
                                <td>phone_number</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.phone_number') ?></td>
                            </tr>
                            <tr>
                                <td>whatsapp</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.whatsapp') ?></td>
                            </tr>
                            <tr>
                                <td>facebook_messenger</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.facebook_messenger') ?></td>
                            </tr>
                            <tr>
                                <td>telegram</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.telegram') ?></td>
                            </tr>
                            <tr>
                                <td>disclaimer</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.disclaimer') ?></td>
                            </tr>
                            <tr>
                                <td>font_family</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['arial', 'verdana', 'helvetica', 'times_new_roman', 'georgia']) . '</code>') ?></td>
                            </tr>
                            <tr>
                                <td>font_size</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>12-18</code>') ?></td>
                            </tr>
                            <tr>
                                <td>width</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>300-600</code>') ?></td>
                            </tr>
                            <tr>
                                <td>image_width</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>45-150</code>') ?></td>
                            </tr>
                            <tr>
                                <td>image_border_radius</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>0-100</code>') ?></td>
                            </tr>
                            <tr>
                                <td>socials_width</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>15-30</code>') ?></td>
                            </tr>
                            <tr>
                                <td>socials_padding</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>5-15</code>') ?></td>
                            </tr>
                            <tr>
                                <td>separator_size</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>0-5</code>') ?></td>
                            </tr>
                            <tr>
                                <td>border_radius</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>0-30</code>') ?></td>
                            </tr>
                            <tr>
                                <td>background_color</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.background_color') ?></td>
                            </tr>
                            <tr>
                                <td>border_color</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.border_color') ?></td>
                            </tr>
                            <tr>
                                <td>theme_color</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.theme_color') ?></td>
                            </tr>
                            <tr>
                                <td>full_name_color</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.full_name_color') ?></td>
                            </tr>
                            <tr>
                                <td>text_color</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.text_color') ?></td>
                            </tr>
                            <tr>
                                <td>link_color</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.link_color') ?></td>
                            </tr>
                            <tr>
                                <td>facebook</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.facebook') ?></td>
                            </tr>
                            <tr>
                                <td>threads</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.threads') ?></td>
                            </tr>
                            <tr>
                                <td>x</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.x') ?></td>
                            </tr>
                            <tr>
                                <td>instagram</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.instagram') ?></td>
                            </tr>
                            <tr>
                                <td>youtube</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.youtube') ?></td>
                            </tr>
                            <tr>
                                <td>tiktok</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.tiktok') ?></td>
                            </tr>
                            <tr>
                                <td>spotify</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.spotify') ?></td>
                            </tr>
                            <tr>
                                <td>pinterest</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.pinterest') ?></td>
                            </tr>
                            <tr>
                                <td>linkedin</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.linkedin') ?></td>
                            </tr>
                            <tr>
                                <td>snapchat</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.snapchat') ?></td>
                            </tr>
                            <tr>
                                <td>twitch</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.twitch') ?></td>
                            </tr>
                            <tr>
                                <td>discord</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.discord') ?></td>
                            </tr>
                            <tr>
                                <td>github</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.github') ?></td>
                            </tr>
                            <tr>
                                <td>reddit</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-signature mr-1"></i> <?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= l('signatures.reddit') ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request POST \<br />
                                --url '<?= SITE_URL ?>api/signatures/<span class="text-primary">{signature_id}</span>' \<br />
                                --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \<br />
                                --header 'Content-Type: multipart/form-data' \<br />
                                --form 'name=<span class="text-primary">Jane Doe Signature</span>' \<br />
                                --form 'border_radius=<span class="text-primary">16</span>' \<br />
                                --form 'background_color=<span class="text-primary">#fff7ed</span>' \<br />
                                --form 'border_color=<span class="text-primary">#fed7aa</span>' \<br />
                                --form 'theme_color=<span class="text-primary">#c2410c</span>'
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
        "project_id": 0,
        "name": "Jane Doe Signature",
        "template": "nova",
        "settings": {
            "direction": "ltr",
            "is_removed_branding": false,
            "image_url": "",
            "sign_off": "Regards,",
            "full_name": "Jane Doe",
            "job_title": "Software Engineer",
            "department": "",
            "company": "Example Company",
            "email": "jane@example.com",
            "website_name": "",
            "website_url": "",
            "address": "",
            "address_url": "",
            "phone_number": "",
            "whatsapp": null,
            "facebook_messenger": "",
            "telegram": "",
            "disclaimer": "",
            "font_family": "arial",
            "font_size": 14,
            "width": 500,
            "image_width": 50,
            "image_border_radius": 0,
            "socials_width": 20,
            "socials_padding": 10,
            "separator_size": 1,
            "border_radius": 16,
            "background_color": "#fff7ed",
            "border_color": "#fed7aa",
            "theme_color": "#c2410c",
            "full_name_color": "#000000",
            "text_color": "#000000",
            "link_color": "#000000",
            "facebook": "",
            "threads": "",
            "x": "",
            "instagram": "",
            "youtube": "",
            "tiktok": "",
            "spotify": "",
            "pinterest": "",
            "linkedin": "",
            "snapchat": "",
            "twitch": "",
            "discord": "",
            "github": "",
            "reddit": ""
        },
        "last_datetime": "<?= get_date() ?>",
        "datetime": "<?= get_date() ?>"
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
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#signatures_delete" aria-expanded="true" aria-controls="signatures_delete">
                        <span class="badge badge-danger user-select-none mr-3"><i class="fas fa-fw fa-sm fa-trash-alt"></i></span> <?= l('api_documentation.delete') ?>
                    </a>
                </h3>
            </div>

            <div id="signatures_delete" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-danger user-select-none mr-3">DELETE</span> <span class="text-muted"><?= SITE_URL ?>api/signatures/</span><span class="text-primary">{signature_id}</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request DELETE \<br />
                                --url '<?= SITE_URL ?>api/signatures/<span class="text-primary">{signature_id}</span>' \<br />
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
