<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('links') ?>"><?= l('links.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('links_statistics.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-link mr-1"></i> <?= l('links_statistics.header') ?></h1>

        <div class="d-flex align-items-center col-auto p-0">
            <div data-toggle="tooltip" title="<?= l('statistics_reset_modal.header') ?>">
                <button
                        type="button"
                        class="btn btn-link text-secondary"
                        data-toggle="modal"
                        data-target="#link_statistics_reset_modal"
                        aria-label="<?= l('statistics_reset_modal.header') ?>"
                        data-start-date="<?= $data->datetime['start_date'] ?>"
                        data-end-date="<?= $data->datetime['end_date'] ?>"
                        data-user-id="<?= user()->user_id ?>"
                        data-project-id="<?= isset($_GET['project_id']) ? (int) $_GET['project_id'] : null ?>"
                >
                    <i class="fas fa-fw fa-sm fa-eraser"></i>
                </button>
            </div>

            <div>
                <div
                        id="daterangepicker"
                        role="button"
                        class="btn btn-sm btn-light text-nowrap"
                        data-min-date="<?= \Altum\Date::get($this->user->datetime, 4) ?>"
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
            </div>
        </div>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-6 mx-lg-n2 mb-3">
        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'overview' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=overview&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-list mr-1"></i>
                <?= l('link_statistics.overview') ?>
            </a>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'entries' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=entries&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-chart-bar mr-1"></i>
                <?= l('link_statistics.entries') ?>
            </a>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'continent_code' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=continent_code&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-globe-europe mr-1"></i>
                <?= l('global.continent') ?>
            </a>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'country' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=country&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-flag mr-1"></i>
                <?= l('global.countries') ?>
            </a>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'region_name' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=region_name&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-map-marker-alt mr-1"></i>
                <?= l('global.regions') ?>
            </a>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'city_name' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=city_name&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-city mr-1"></i>
                <?= l('global.cities') ?>
            </a>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'acquisition_channel' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=acquisition_channel&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-bullhorn mr-1"></i>
                <?= l('link_statistics.acquisition_channel') ?>
            </a>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <div class="dropdown w-100">
                <button type="button" class="btn btn-block btn-custom text-truncate dropdown-toggle <?= in_array($data->type, ['referrer_host', 'referrer_path']) ? 'active' : null ?>" data-toggle="dropdown" data-boundary="viewport">
                    <i class="fas fa-fw fa-sm fa-random mr-1"></i>
                    <?= l('link_statistics.referrer_host') ?>
                </button>

                <div class="dropdown-menu">
                    <a class="dropdown-item <?= in_array($data->type, ['referrer_host', 'referrer_path']) && $data->referrer_type == 'all' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=referrer_host&referrer_type=all&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                        <i class="fas fa-fw fa-sm fa-list mr-2"></i>
                        <?= l('link_statistics.referrers_all') ?>
                    </a>
                    <a class="dropdown-item <?= in_array($data->type, ['referrer_host', 'referrer_path']) && $data->referrer_type == 'ai' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=referrer_host&referrer_type=ai&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                        <i class="fas fa-fw fa-sm fa-robot mr-2"></i>
                        <?= l('link_statistics.referrers_ai') ?>
                    </a>
                    <a class="dropdown-item <?= in_array($data->type, ['referrer_host', 'referrer_path']) && $data->referrer_type == 'social_media' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=referrer_host&referrer_type=social_media&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                        <i class="fas fa-fw fa-sm fa-share-alt mr-2"></i>
                        <?= l('link_statistics.referrers_social_media') ?>
                    </a>
                    <a class="dropdown-item <?= in_array($data->type, ['referrer_host', 'referrer_path']) && $data->referrer_type == 'search_engines' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=referrer_host&referrer_type=search_engines&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                        <i class="fas fa-fw fa-sm fa-search mr-2"></i>
                        <?= l('link_statistics.referrers_search_engines') ?>
                    </a>
                    <a class="dropdown-item <?= in_array($data->type, ['referrer_host', 'referrer_path']) && $data->referrer_type == 'other_websites' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=referrer_host&referrer_type=other_websites&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                        <i class="fas fa-fw fa-sm fa-globe mr-2"></i>
                        <?= l('link_statistics.referrers_other_websites') ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <div class="dropdown w-100">
                <button type="button" class="btn btn-block btn-custom text-truncate dropdown-toggle <?= $data->type == 'crawler' ? 'active' : null ?>" data-toggle="dropdown" data-boundary="viewport">
                    <i class="fas fa-fw fa-sm fa-robot mr-1"></i>
                    <?= l('link_statistics.crawlers') ?>
                </button>

                <div class="dropdown-menu">
                    <a class="dropdown-item <?= $data->type == 'crawler' && $data->crawler_type == 'all' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=crawler&crawler_type=all&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                        <i class="fas fa-fw fa-sm fa-robot mr-2"></i>
                        <?= l('link_statistics.crawlers_all') ?>
                    </a>
                    <a class="dropdown-item <?= $data->type == 'crawler' && $data->crawler_type == 'ai' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=crawler&crawler_type=ai&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                        <i class="fas fa-fw fa-sm fa-brain mr-2"></i>
                        <?= l('link_statistics.crawlers_ai') ?>
                    </a>
                    <a class="dropdown-item <?= $data->type == 'crawler' && $data->crawler_type == 'search_engines' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=crawler&crawler_type=search_engines&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                        <i class="fas fa-fw fa-sm fa-search mr-2"></i>
                        <?= l('link_statistics.crawlers_search_engines') ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'device' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=device&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-laptop mr-1"></i>
                <?= l('link_statistics.device') ?>
            </a>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'os' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=os&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-server mr-1"></i>
                <?= l('link_statistics.os') ?>
            </a>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'timezone' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=timezone&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-clock mr-1"></i>
                <?= l('link_statistics.timezone') ?>
            </a>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'browser' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=browser&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-window-restore mr-1"></i>
                <?= l('link_statistics.browser') ?>
            </a>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'language' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=language&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-language mr-1"></i>
                <?= l('link_statistics.language') ?>
            </a>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= in_array($data->type, ['utm_source', 'utm_medium', 'utm_campaign']) ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=utm_source&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-link mr-1"></i>
                <?= l('link_statistics.utms') ?>
            </a>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'hour' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=hour&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-clock mr-1"></i>
                <?= l('link_statistics.hour') ?>
            </a>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'weekday' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=weekday&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-calendar-day mr-1"></i>
                <?= l('link_statistics.weekday') ?>
            </a>
        </div>

        <div class="p-1 p-lg-2 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'visitor_type' ? 'active' : null ?>" href="<?= url('links-statistics?' . $data->filters->get_get() . '&type=visitor_type&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fas fa-fw fa-sm fa-user mr-1"></i>
                <?= l('link_statistics.visitor_type') ?>
            </a>
        </div>
    </div>

    <?php if(!$data->has_data): ?>

        <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
            'filters_get' => $data->filters->get ?? [],
            'name' => 'link_statistics',
            'has_secondary_text' => true,
        ]); ?>

    <?php else: ?>

        <?= $this->views['statistics'] ?>

    <?php endif ?>

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
</div>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/links/link_delete_modal.php'), 'modals'); ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/statistics_reset_modal.php', ['modal_id' => 'link_statistics_reset_modal', 'resource_id' => isset($_GET['project_id']) ? 'project_id' : 'user_id', 'path' => 'links-statistics/reset']), 'modals'); ?>
