<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li><a href="<?= url('visitors') ?>"><?= l('visitors.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li class="active" aria-current="page"><?= l('visitor.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <?= \Altum\Alerts::output_alerts() ?>

    <div class="row">
        <div class="col-12 col-lg-4">
            <div class="d-flex align-items-center mb-4">
                <?php
                $icon = new \Jdenticon\Identicon([
                    'value' => $data->visitor->visitor_uuid_binary,
                    'size' => 80
                ]);
                ?>

                <img src="<?= $icon->getImageDataUri() ?>" class="visitor-avatar rounded-circle mr-3" alt="" />

                <h1 class="h4 m-0"><?= l('analytics.visitor') ?></h1>
            </div>

            <div class="card border-0">
                <div class="card-body">

                    <div class="row">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('global.ip') ?></small>
                        </div>
                        <div class="col-12">
                            <div><?= settings()->analytics->ip_storage_is_enabled ? ($data->visitor->ip ?: '**.***.***.*') : l('visitor.ip_private') ?></div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('visitor.custom_parameters') ?></small>
                        </div>

                        <div class="col-12">
                            <?php $data->visitor->custom_parameters = json_decode($data->visitor->custom_parameters, true); ?>

                            <?php if($data->visitor->custom_parameters && count($data->visitor->custom_parameters)): ?>

                                <div class="row">
                                    <?php foreach($data->visitor->custom_parameters as $key => $value): ?>
                                        <div class="col-4 text-muted font-weight-bold small"><?= $key ?></div>
                                        <div class="col-8 text-left small"><?= $value ?></div>
                                    <?php endforeach ?>
                                </div>

                            <?php else: ?>
                                <?= l('global.none') ?>
                            <?php endif ?>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('global.continent') ?></small>
                        </div>
                        <div class="col-12">
                            <div>
                                <div><?= $data->visitor->continent_code ? get_continent_from_continent_code($data->visitor->continent_code) : l('global.unknown') ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('global.country') ?></small>
                        </div>
                        <div class="col-12">
                            <div>
                                <img src="<?= ASSETS_FULL_URL . 'images/countries/' . ($data->visitor->country_code ? mb_strtolower($data->visitor->country_code) : 'unknown') . '.svg' ?>" class="img-fluid icon-favicon mr-1" />
                                <span class=""><?= $data->visitor->country_code ? get_country_from_country_code($data->visitor->country_code) :  l('global.unknown') ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('global.city') ?></small>
                        </div>
                        <div class="col-12">
                            <div>
                                <div><?= $data->visitor->city_name ?: l('global.unknown') ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('visitor.device_type') ?></small>
                        </div>
                        <div class="col-12">
                            <div><i class="fas fa-fw fa-<?= $data->visitor->device_type ?> fa-sm text-muted mr-1"></i> <?= l('global.device.' . $data->visitor->device_type) ?></div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('visitor.operating_system') ?></small>
                        </div>
                        <div class="col-12">
                            <div>
                                <img src="<?= ASSETS_FULL_URL . 'images/os/' . os_name_to_os_key($data->visitor->os_name) . '.svg' ?>" class="img-fluid icon-favicon-small mr-1" />
                                <?= $data->visitor->os_name . ' v' . $data->visitor->os_version ?>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('visitor.browser') ?></small>
                        </div>
                        <div class="col-12">
                            <div>
                                <img src="<?= ASSETS_FULL_URL . 'images/browsers/' . browser_name_to_browser_key($data->visitor->browser_name) . '.svg' ?>" class="img-fluid icon-favicon-small mr-1" />
                                <?= $data->visitor->browser_name . ' v' . $data->visitor->browser_version ?>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('visitor.browser_language') ?></small>
                        </div>
                        <div class="col-12">
                            <div><?= get_language_from_locale($data->visitor->browser_language) ?></div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('visitor.browser_timezone') ?></small>
                        </div>
                        <div class="col-12">
                            <div><?= $data->visitor->browser_timezone ?: l('global.unknown') ?></div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('visitor.screen_resolution') ?></small>
                        </div>
                        <div class="col-12">
                            <div><?= $data->visitor->screen_resolution ?></div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('visitor.total_goals_conversions') ?></small>
                        </div>
                        <div class="col-12">
                            <div><?= nr(count($data->visitor->goals_conversions_ids)) ?></div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('visitor.total_sessions') ?></small>
                        </div>
                        <div class="col-12">
                            <div><?= nr($data->visitor->total_sessions) ?></div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('visitor.date') ?></small>
                        </div>
                        <div class="col-12">
                            <div><span data-toggle="tooltip" title="<?= \Altum\Date::get($data->visitor->date, 1) ?>"><?= \Altum\Date::get_timeago($data->visitor->date, 2) ?></span></div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('visitor.last_date') ?></small>
                        </div>
                        <div class="col-12">
                            <div><span data-toggle="tooltip" title="<?= \Altum\Date::get($data->visitor->last_date, 1) ?>"><?= \Altum\Date::get_timeago($data->visitor->last_date, 2) ?></span></div>
                        </div>
                    </div>


                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="font-weight-bold text-muted"><?= l('visitor.average_time_per_session') ?></small>
                        </div>
                        <div class="col-12">
                            <div><?= \Altum\Date::seconds_to_his($data->average_time_per_session) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="d-flex align-items-lg-center flex-column flex-lg-row justify-content-between mt-5 mt-lg-0 mb-4">
                <h1 class="h4 m-0"><?= l('analytics.sessions') ?></h1>

                <div class="d-flex align-items-center">
                    <div
                            id="daterangepicker"
                            role="button"
                            class="btn btn-sm btn-light text-nowrap"
                            data-min-date="<?= \Altum\Date::get($this->website->datetime, 4) ?>"
                            data-max-date="<?= \Altum\Date::get('', 4) ?>"
                    >
                        <i class="fas fa-fw fa-calendar mr-lg-1"></i>
                        <span class="d-none d-lg-inline-block">
                            <?php if($data->datetime['start_date'] == $data->datetime['end_date']): ?>
                                <?= \Altum\Date::get($data->datetime['start_date'], 6, \Altum\Date::$default_timezone) ?>
                            <?php else: ?>
                                <?= \Altum\Date::get($data->datetime['start_date'], 6, \Altum\Date::$default_timezone) . ' - ' . \Altum\Date::get($data->datetime['end_date'], 6, \Altum\Date::$default_timezone) ?>
                            <?php endif ?>
                        </span>
                        <i class="fas fa-fw fa-caret-down d-none d-lg-inline-block ml-lg-1"></i>
                    </div>

                    <div class="ml-3">
                        <?= include_view(THEME_PATH . 'views/visitors/visitor_dropdown_button.php', ['id' => $data->visitor->visitor_id]) ?>
                    </div>
                </div>
            </div>

            <?php if(!$data->sessions_result->num_rows): ?>

                <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                    'filters_get' => $data->filters->get ?? [],
                    'name' => 'visitor',
                    'has_secondary_text' => false,
                ]); ?>

            <?php else: ?>

                <?php while($row = $data->sessions_result->fetch_object()): ?>

                    <div class="card border-0 mb-3">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="d-flex flex-column mb-2 mb-lg-0">
                                        <div>
                                            <span data-toggle="tooltip" title="<?= \Altum\Date::get($row->date, 2) ?>">
                                                <?= \Altum\Date::get_timeago($row->date) ?>
                                            </span>
                                        </div>

                                        <span class="text-muted">
                                            <?= \Altum\Date::get($row->date, 3) ?>
                                            <i class="fas fa-fw fa-sm fa-arrow-right"></i>
                                            <?= $row->pageviews == 1 ? l('global.unknown') : \Altum\Date::get($row->last_date, 3) ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-lg-4 d-flex flex-column justify-content-center mb-2 mb-lg-0">
                                    <a href="#" class="badge badge-primary" data-toggle="modal" data-target="#session_events_modal" data-session-id="<?= $row->session_id ?>">
                                        <i class="fas fa-fw fa-eye fa-sm mr-1"></i> <?= sprintf(l('visitor.pageviews'), '<strong>' . nr($row->pageviews) . '</strong>') ?>
                                    </a>

                                    <?php if($row->sessions_replays_session_id): ?>
                                        <a class="mt-2 badge badge-light" href="<?= url('replay/' . $row->sessions_replays_session_id) ?>">
                                            <i class="fas fa-fw fa-play-circle fa-sm mr-1"></i> <?= l('visitor.replays') ?>
                                        </a>
                                    <?php endif ?>
                                </div>

                                <div class="col-lg-4 d-flex align-items-center justify-content-lg-end">
                                    <div class="mb-2 mb-md-0">
                                        <span class="text-muted small" data-toggle="tooltip" title="<?= l('visitor.duration_tooltip') ?>">
                                            <?= $row->pageviews == 1 || !$row->last_date ? l('visitor.unknown_duration') : \Altum\Date::seconds_to_his((new \DateTime($row->last_date))->getTimestamp() - ((new \DateTime($row->date)))->getTimestamp()) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                <?php endwhile ?>

            <?php endif ?>

            <h1 class="h4 mt-5 mb-4" id="goals_conversions"><?= l('visitor.goals_conversions') ?></h1>

            <?php if(!count($data->goals)): ?>

                <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                    'filters_get' => $data->filters->get ?? [],
                    'name' => 'visitor',
                    'has_secondary_text' => false,
                ]); ?>

            <?php else: ?>

                <?php foreach($data->goals as $goal): ?>

                    <div class="card border-0 mb-3">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-4">
                                    <div>
                                        <?= $goal->path ?: '/' ?>
                                        <a href="<?= $this->website->scheme . $this->website->host . $this->website->path . $goal->path ?>" target="_blank" rel="nofollow noreferrer"><i class="fas fa-fw fa-xs fa-external-link-alt ml-1"></i></a>
                                    </div>

                                    <div class="small text-muted">
                                        <?= $goal->name ?>
                                    </div>
                                </div>

                                <div class="col-4 d-flex align-items-center">
                                    <span class="badge badge-light">
                                        <i class="<?= l('analytics.' . $goal->type . '.icon') ?> fa-fw fa-xs mr-1"></i>
                                        <?= l('analytics.' . $goal->type . '.name') ?>
                                    </span>
                                </div>

                                <div class="col-4 d-flex align-items-center justify-content-lg-end">
                                    <small class="text-muted" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($goal->datetime, 2) . '<br /><small>' . \Altum\Date::get($goal->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($goal->datetime) . ')</small>') ?>"><?= \Altum\Date::get($goal->datetime, 6) ?></small>
                                </div>
                            </div>

                        </div>
                    </div>

                <?php endforeach ?>

            <?php endif ?>

        </div>
    </div>

</div>


<input type="hidden" name="start_date" value="<?= \Altum\Date::get($data->datetime['start_date'], 1) ?>" />
<input type="hidden" name="end_date" value="<?= \Altum\Date::get($data->datetime['end_date'], 1) ?>" />
<input type="hidden" name="visitor_id" value="<?= $data->visitor->visitor_id ?>" />

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/libraries/daterangepicker.min.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/daterangepicker.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment-timezone-with-data-10-year-range.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    'use strict';

moment.tz.setDefault(<?= json_encode($this->user->timezone) ?>);

    /* Daterangepicker */
    $('#daterangepicker').daterangepicker({
        startDate: <?= json_encode($data->datetime['start_date']) ?>,
        endDate: <?= json_encode($data->datetime['end_date']) ?>,
        minDate: $('#daterangepicker').data('min-date'),
        maxDate: $('#daterangepicker').data('max-date'),
        ranges: {
            <?= json_encode(l('global.date.today')) ?>: [moment(), moment()],
            <?= json_encode(l('global.date.yesterday')) ?>: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            <?= json_encode(l('global.date.this_week')) ?>: [moment().startOf('week'), moment().endOf('week')],

            <?= json_encode(l('global.date.last_30_days')) ?>: [moment().subtract(29, 'days'), moment()],
                <?= json_encode(l('global.date.this_month')) ?>: [moment().startOf('month'), moment().endOf('month')],
            <?= json_encode(l('global.date.last_month')) ?>: [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                <?= json_encode(l('global.date.this_year')) ?>: [moment().startOf('year'), moment()],
                <?= json_encode(l('global.date.last_year')) ?>: [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
            <?= json_encode(l('global.date.all_time')) ?>: [moment($('#daterangepicker').data('min-date')), moment()]
        },
        alwaysShowCalendars: true,
        linkedCalendars: false,
        singleCalendar: true,
        locale: <?= json_encode(require APP_PATH . 'includes/daterangepicker_translations.php') ?>,
    }, (start, end, label) => {

        <?php
        parse_str(\Altum\Router::$original_request_query, $original_request_query_array);
        $modified_request_query_array = array_diff_key($original_request_query_array, ['start_date' => '', 'end_date' => '']);
        ?>

        /* Redirect */
        redirect(`<?= url(\Altum\Router::$original_request . '?' . http_build_query($modified_request_query_array)) ?>&start_date=${start.format('YYYY-MM-DD')}&end_date=${end.format('YYYY-MM-DD')}`, true);

    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
