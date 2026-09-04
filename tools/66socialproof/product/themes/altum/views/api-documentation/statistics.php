<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li><a href="<?= url() ?>"><?= l('index.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li><a href="<?= url('api-documentation') ?>"><?= l('api_documentation.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li class="active" aria-current="page"><?= l('statistics.title') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 mb-4"><?= l('statistics.title') ?></h1>

    <div class="accordion">
        <div class="card">
            <div class="card-header bg-white p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link text-decoration-none" data-toggle="collapse" data-target="#statistics_read_all" aria-expanded="true" aria-controls="statistics_read_all">
                        <span class="badge badge-success user-select-none mr-3"><i class="fas fa-fw fa-sm fa-list"></i></span> <?= l('api_documentation.read_all') ?>
                    </a>
                </h3>
            </div>

            <div id="statistics_read_all" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-success user-select-none mr-3">GET</span> <span class="text-muted"><?= SITE_URL ?>api/statistics/</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request GET \<br />
                                --url '<?= SITE_URL ?>api/statistics/' \<br />
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
                                <td>campaign_id</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>notification_id</td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-fw fa-sm fa-circle-notch mr-1"></i> <?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><i class="fas fa-fw fa-sm fa-hashtag mr-1"></i> <?= l('api_documentation.int') ?></span>
                                </td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>start_date</td>
                                <td><span class="badge badge-danger"><i class="fas fa-fw fa-sm fa-asterisk mr-1"></i> <?= l('api_documentation.required') ?></span></td>
                                <td><?= l('api_documentation.statistics.start_date') ?></td>
                            </tr>

                            <tr>
                                <td>end_date</td>
                                <td><span class="badge badge-danger"><i class="fas fa-fw fa-sm fa-asterisk mr-1"></i> <?= l('api_documentation.required') ?></span></td>
                                <td><?= l('api_documentation.statistics.end_date') ?></td>
                            </tr>

                            </tbody>
                        </table>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.response') ?></label>
                        <pre data-shiki="json">
{
    "data": {
        "statistics" : [
            {
                "impression": 5,
                "hover": 1,
                "click": 1,
                "form_submission": 0,
                "formatted_date": "2021-01"
            },
            {
                "impression": 50,
                "hover": 15,
                "click": 5,
                "form_submission": 3,
                "formatted_date": "2021-02"
            },
            {
                "impression": 15,
                "hover": 10,
                "click": 10,
                "form_submission": 5,
                "formatted_date": "2021-03"
            }
        ],
        "totals": {
            "impression": 70,
            "hover": 26,
            "click": 16,
            "ctr": "0.45",
            "form_submission": 8,
            "conversions": 0
        }
    ],
    "meta": {
        "page": 1,
        "results_per_page": 25,
        "total": 1,
        "total_pages": 1
    },
    "links": {
        "first": "<?= SITE_URL ?>api/statistics?page=1",
        "last": "<?= SITE_URL ?>api/statistics?page=1",
        "next": null,
        "prev": null,
        "self": "<?= SITE_URL ?>api/statistics?page=1"
    }
}
</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require THEME_PATH . 'views/partials/shiki_highlighter.php' ?>
