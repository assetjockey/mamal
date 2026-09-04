<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <?php if(is_logged_in()): ?>
            <nav aria-label="breadcrumb">
                <ol class="custom-breadcrumbs small">
                    <li>
                        <a href="<?= url('websites') ?>"><?= l('websites.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                    </li>
                    <li class="active" aria-current="page"><?= l('website.breadcrumb') ?></li>
                </ol>
            </nav>
        <?php endif ?>
    <?php endif ?>

    <?php
    $website_score_class_name = match (true) {
        $data->website->score >= 80 => 'success',
        $data->website->score >= 50 => 'warning',
        $data->website->score >= 0 => 'danger',
    };

    $custom_score = null;

    if($data->website->total_tests == 0) {
        $custom_score = l('global.na');
    }

    $website_score_circle_attributes = [
        'progress' => $data->website->score,
        'size' => 125,
        'circleColor' => 'var(--gray-200)',
        'progressColor' => 'var(--' . $website_score_class_name . ')',
        'circleWidth' => '12px',
        'progressWidth' => '12px',
        'progressShape' => 'round',
        'textColor' => 'var(--' . $website_score_class_name . ')',
        'textSize' => [
            'fontSize' => 30
        ],
        'valueToggle' => true,
        'percentageToggle' => false,
        'custom_display_text' => $custom_score
    ];
    ?>

    <div class="card mb-2">
        <div class="d-flex flex-column flex-md-row">
            <div class="d-flex align-items-center justify-content-center">
                <div class="audit-score-circle">
                    <?= get_audit_score_circle($website_score_circle_attributes) ?>
                </div>
            </div>

            <div class="card-body text-truncate d-flex justify-content-between align-items-center">
                <div class="text-truncate">
                    <div class="d-flex align-items-center">
                        <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($data->website->host) ?>" class="img-fluid icon-favicon-small mr-1" loading="lazy" />

                        <span class="small text-muted text-truncate" data-toggle="tooltip" title="<?= $data->website->host ?>">
                            <?= string_truncate($data->website->host, 32) ?>
                        </span>
                    </div>

                    <h1 class="h4 text-truncate mb-0">
                        <?= sprintf(l('website.header'), $data->website->host) ?>

                        <a href="<?= $data->website->scheme . '://' . $data->website->host ?>" class="small" target="_blank" rel="noreferrer">
                            <i class="fas fa-fw fa-xs fa-external-link-alt text-muted ml-1"></i>
                        </a>
                    </h1>

                    <?php if($data->website->total_tests): ?>
                    <p class="small text-muted white-space-normal m-0">
                        <?= sprintf(l('websites.dynamic_description'), $data->website->score, $data->website->total_issues, $data->website->total_audits) ?>
                    </p>

                    <p class="small text-muted white-space-normal m-0">
                        <?php
                        if($data->websites_stats->average_score > 0) {
                            $percentage_difference = round((($data->website->score - $data->websites_stats->average_score) / $data->websites_stats->average_score) * 100, 2);
                        } else {
                            $percentage_difference = 0;
                        }
                        ?>

                        <?php if($data->website->score >= $data->websites_stats->average_score): ?>
                            <?= sprintf(l('websites.dynamic_description2.above_average'),
                                $data->website->host,
                                $data->websites_stats->average_score .
                                ' (' . $percentage_difference . '%) ' .
                                '<i class="fas fa-fw fa-sm fa-arrow-up text-success"></i>',
                                $data->websites_stats->total_websites,
                                $data->websites_stats->total_tests)
                            ?>
                        <?php else: ?>
                            <?= sprintf(l('websites.dynamic_description2.below_average'),
                                $data->website->host,
                                $data->websites_stats->average_score .
                                ' (' . abs($percentage_difference) . '%) ' .
                                '<i class="fas fa-fw fa-sm fa-arrow-down text-danger"></i>',
                                $data->websites_stats->total_websites,
                                $data->websites_stats->total_tests)
                            ?>
                        <?php endif ?>
                    </p>

                    <?php else: ?>
                    <p class="small text-muted white-space-normal m-0">
                        <?= l('global.no_data') ?>
                    </p>
                    <?php endif ?>
                </div>

                <div class="d-flex">
                    <div class="dropdown">
                        <button type="button" class="btn btn-light dropdown-toggle-simple ml-2" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                            <i class="fas fa-fw fa-sm fa-download"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right d-print-none">
                            <a href="<?= url('website/' . $data->website->website_id . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                                <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                            </a>
                            <a href="<?= url('website/' . $data->website->website_id . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
                                <i class="fas fa-fw fa-sm fa-file-code mr-2"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                            </a>
                            <a href="#" class="dropdown-item <?= $this->user->plan_settings->export->pdf ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->pdf ? 'onclick="event.preventDefault(); window.print();"' : get_plan_feature_disabled_info() ?>>
                                <i class="fas fa-fw fa-sm fa-file-pdf mr-2"></i> <?= sprintf(l('global.export_to'), 'PDF') ?>
                            </a>
                        </div>
                    </div>

                    <?= include_view(THEME_PATH . 'views/websites/website_dropdown_button.php', ['id' => $data->website->website_id, 'resource_name' => $data->website->host]) ?>
                </div>
            </div>
        </div>

        <?php if (!empty($data->audits) && $data->website->total_tests): ?>
        <div class="card-footer bg-white">
            <?php
            $major_issues_percentage = $data->website->total_tests ? number_format($data->website->major_issues * 100 / $data->website->total_tests, '2', '.', '') : 0;
            $moderate_issues_percentage = $data->website->total_tests ? number_format($data->website->moderate_issues * 100 / $data->website->total_tests, '2', '.', '') : 0;
            $minor_issues_percentage = $data->website->total_tests ? number_format($data->website->minor_issues * 100 / $data->website->total_tests, '2', '.', '') : 0;
            $passed_tests_percentage = $data->website->total_tests ? number_format($data->website->passed_tests * 100 / $data->website->total_tests, '2', '.', '') : 0;
            ?>

            <?php
            $score_bar_tooltip = '<div class=\'text-left\'>';
            $score_bar_tooltip .= '<div><i class=\'fas fa-fw fa-sm fa-exclamation-circle text-danger mr-1\'></i> ' . sprintf(l('audits.major_issues_x'), nr($data->website->major_issues)) . '</div>';
            $score_bar_tooltip .= '<div><i class=\'fas fa-fw fa-sm fa-exclamation-triangle text-warning mr-1\'></i> ' . sprintf(l('audits.moderate_issues_x'), nr($data->website->moderate_issues)) . '</div>';
            $score_bar_tooltip .= '<div><i class=\'fas fa-fw fa-sm fa-circle text-muted mr-1\'></i> ' . sprintf(l('audits.minor_issues_x'), nr($data->website->minor_issues)) . '</div>';
            $score_bar_tooltip .= '<div><i class=\'fas fa-fw fa-sm fa-check-circle text-success mr-1\'></i> ' . sprintf(l('audits.passed_tests_x'), nr($data->website->passed_tests)) . '</div>';
            $score_bar_tooltip .= '</div>';
            ?>

            <div class="d-flex flex-column flex-md-row flex-lg-column flex-xl-row audit-checks-bar-wrapper" data-toggle="tooltip" data-html="true" title="<?= $score_bar_tooltip ?>">
                <?php if($data->website->major_issues): ?>
                    <div class="audit-checks-bar-item bg-danger my-1 my-md-0 my-lg-1 my-xl-0" style="width: <?= $major_issues_percentage ?>%;"></div>
                <?php endif ?>

                <?php if($data->website->moderate_issues): ?>
                    <div class="audit-checks-bar-item bg-warning my-1 my-md-0 my-lg-1 my-xl-0" style="width: <?= $moderate_issues_percentage ?>%;"></div>
                <?php endif ?>

                <?php if($data->website->minor_issues): ?>
                    <div class="audit-checks-bar-item bg-gray-600 my-1 my-md-0 my-lg-1 my-xl-0" style="width: <?= $minor_issues_percentage ?>%;"></div>
                <?php endif ?>

                <?php if($data->website->passed_tests): ?>
                    <div class="audit-checks-bar-item bg-success my-1 my-md-0 my-lg-1 my-xl-0" style="width: <?= $passed_tests_percentage ?>%;"></div>
                <?php endif ?>
            </div>
        </div>
        <?php endif ?>
    </div>

    <div class="row">
        <div class="col-12 col-md-6 p-3 text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden position-relative">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x card-widget-icon d-flex align-items-center justify-content-center bg-audit">
                        <i class="fas fa-fw fa-sm fa-user-check text-audit"></i>
                    </div>
                </div>

                <div class="card-body text-truncate">
                    <a href="<?= url('audits?website_id=' . $data->website->website_id) ?>" class="text-reset text-decoration-none stretched-link">
                        <?= sprintf(l('websites.total_audits_x'), nr($data->website->total_audits)) ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 p-3 text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden position-relative">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x card-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                        <i class="fas fa-fw fa-sm fa-archive text-muted"></i>
                    </div>
                </div>

                <div class="card-body text-truncate">
                    <a href="<?= url('archived-audits?website_id=' . $data->website->website_id) ?>" class="text-reset text-decoration-none stretched-link">
                        <?= sprintf(l('websites.total_archived_audits_x'), nr($data->website->total_archived_audits)) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-n2">
        <div class="col-12 col-md-4 p-3 text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= l('websites.last_audit_datetime') . ($data->website->last_audit_datetime ? '<br />' . \Altum\Date::get($data->website->last_audit_datetime, 2) . '<br /><small>' . \Altum\Date::get($data->website->last_audit_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->website->last_audit_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x card-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                        <i class="fas fa-fw fa-sm fa-calendar-check text-muted"></i>
                    </div>
                </div>

                <div class="card-body text-truncate">
                    <?= $data->website->last_audit_datetime ? \Altum\Date::get_timeago($data->website->last_audit_datetime) : l('global.na') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4 p-3 text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($data->website->datetime, 2) . '<br /><small>' . \Altum\Date::get($data->website->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->website->datetime) . ')</small>') ?>">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x card-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                        <i class="fas fa-fw fa-sm fa-clock text-muted"></i>
                    </div>
                </div>

                <div class="card-body text-truncate">
                    <?= $data->website->datetime ? \Altum\Date::get_timeago($data->website->datetime) : l('global.na') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4 p-3 text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($data->website->last_datetime ? '<br />' . \Altum\Date::get($data->website->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($data->website->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->website->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                <div class="pl-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x card-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                        <i class="fas fa-fw fa-sm fa-clock-rotate-left text-muted"></i>
                    </div>
                </div>

                <div class="card-body text-truncate">
                    <?= $data->website->last_datetime ? \Altum\Date::get_timeago($data->website->last_datetime) : l('global.na') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <div class="d-flex align-items-center mb-3">
            <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-bolt mr-1 text-audit"></i> <?= l('audits.audit') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>

            <div class="ml-3">
                <a href="<?= url('audits?website_id=' . $data->website->website_id) ?>" class="btn btn-sm bg-audit text-audit" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-bolt fa-sm"></i></a>
            </div>
        </div>

        <?php if (!empty($data->audits)): ?>
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th data-bulk-table class="d-none">
                            <div class="custom-control custom-checkbox">
                                <input id="bulk_select_all" type="checkbox" class="custom-control-input" />
                                <label class="custom-control-label" for="bulk_select_all"></label>
                            </div>
                        </th>
                        <th><?= l('audits.audit') ?></th>
                        <th><?= l('audits.score') ?></th>
                        <th></th>
                        <th><?= l('audits.total_issues') ?></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach($data->audits as $row): ?>

                        <tr>
                            <td data-bulk-table class="d-none">
                                <div class="custom-control custom-checkbox">
                                    <input id="selected_audit_id_<?= $row->audit_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->audit_id ?>" />
                                    <label class="custom-control-label" for="selected_audit_id_<?= $row->audit_id ?>"></label>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <div>
                                    <a href="<?= url('audit/' . $row->audit_id) ?>">
                                        <?= string_truncate(remove_url_protocol_from_url($row->url), 32) ?>
                                    </a>
                                </div>

                                <div class="small">
                                    <img referrerpolicy="no-referrer" src="<?= get_favicon_url_from_domain($row->host) ?>" class="img-fluid icon-favicon-small mr-1" loading="lazy" />

                                    <a href="<?= url('website/' . $row->website_id) ?>" class="text-muted"><?= $row->host ?></a>

                                    <a href="<?= $row->url ?>" target="_blank" rel="noreferrer"><i class="fas fa-fw fa-xs fa-external-link-alt text-muted ml-1"></i></a>
                                </div>
                            </td>

                            <td class="text-nowrap text-muted">
                                <?php if($row->is_queued || $row->refresh_error || $row->is_external_redirected): ?>
                                    <span class="font-weight-bold small"><?= l('global.na') ?></span>
                                <?php else: ?>
                                    <span class="font-weight-bold small"><?= get_audit_score_display($row) ?></span>
                                <?php endif ?>
                            </td>

                            <td class="text-nowrap text-muted">
                                <?php if($row->is_queued || $row->refresh_error || $row->is_external_redirected): ?>
                                    <div class="d-flex align-items-center">
                                        <?php for($i = 0; $i <= 9; $i++): ?>
                                            <div class="audit-badge <?= $row->is_queued ? 'audit-badge-loading' : 'bg-gray-200' ?> mr-1"></div>
                                        <?php endfor ?>
                                    </div>
                                <?php else: ?>

                                    <div class="d-flex align-items-center">
                                        <?php $rounded_score = round($row->score / 10); ?>

                                        <?php for($i = 0; $i <= 9; $i++): ?>
                                            <?php
                                            if($i <= $rounded_score - 1) {
                                                $audit_badge_bg_class_name = match (true) {
                                                    $rounded_score >= 8 => 'success',
                                                    $rounded_score >= 5 => 'warning',
                                                    $rounded_score >= 0 => 'danger',
                                                };
                                            } else {
                                                $audit_badge_bg_class_name = 'gray-200';
                                            }
                                            ?>

                                            <div class="audit-badge bg-<?= $audit_badge_bg_class_name ?> mr-1"></div>
                                        <?php endfor ?>
                                    </div>
                                <?php endif ?>
                            </td>

                            <?php
                            $audit_badge_bg_class_name = 'success';
                            if($row->minor_issues > 0) $audit_badge_bg_class_name = 'light';
                            if($row->moderate_issues > 0) $audit_badge_bg_class_name = 'warning';
                            if($row->major_issues > 0) $audit_badge_bg_class_name = 'danger';

                            $badge_tooltip = '<div class=\'text-left\'>';
                            $badge_tooltip .= '<div><i class=\'fas fa-fw fa-sm fa-exclamation-circle text-danger mr-1\'></i> ' . sprintf(l('audits.major_issues_x'), nr($row->major_issues)) . '</div>';
                            $badge_tooltip .= '<div><i class=\'fas fa-fw fa-sm fa-exclamation-triangle text-warning mr-1\'></i> ' . sprintf(l('audits.moderate_issues_x'), nr($row->moderate_issues)) . '</div>';
                            $badge_tooltip .= '<div><i class=\'fas fa-fw fa-sm fa-circle text-muted mr-1\'></i> ' . sprintf(l('audits.minor_issues_x'), nr($row->minor_issues)) . '</div>';
                            $badge_tooltip .= '<div><i class=\'fas fa-fw fa-sm fa-check-circle text-success mr-1\'></i> ' . sprintf(l('audits.passed_tests_x'), nr($row->passed_tests)) . '</div>';
                            $badge_tooltip .= '</div>';
                            ?>

                            <td class="text-nowrap text-muted">
                                <?php if($row->is_queued): ?>
                                    <a href="<?= url('audit/' . $row->audit_id) ?>" class="badge badge-light">
                                        <?= l('audits.queued') ?>
                                    </a>
                                <?php elseif($row->refresh_error): ?>
                                    <a href="<?= url('audit/' . $row->audit_id) ?>" class="badge badge-danger" data-toggle="tooltip" title="<?= e($row->refresh_error) ?>">
                                        <?= l('audits.refresh_error_small') ?>
                                    </a>
                                <?php elseif($row->is_external_redirected): ?>
                                    <a href="<?= url('audit/' . $row->audit_id) ?>" class="badge badge-danger" data-toggle="tooltip" title="<?= l('audits.is_external_redirected') ?>">
                                        <?= l('audits.is_external_redirected_small') ?>
                                    </a>
                                <?php else: ?>
                                    <a href="<?= url('audit/' . $row->audit_id) ?>" class="badge badge-<?= $audit_badge_bg_class_name ?>" data-html="true" data-toggle="tooltip" title="<?= $badge_tooltip ?>">
                                        <?= sprintf(l('audits.total_issues_x'), nr($row->total_issues)) ?>
                                    </a>
                                <?php endif ?>
                            </td>

                            <td class="text-nowrap text-muted">
                                <a href="<?= url('archived-audits?audit_id=' . $row->audit_id) ?>" class="mr-2" data-toggle="tooltip" title="<?= l('archived_audits.menu') ?>">
                                    <i class="fas fa-fw fa-archive text-muted"></i>
                                </a>
                            </td>

                            <td class="text-nowrap text-muted">
                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('audits.last_refresh_datetime') . '<br />' . ($row->last_refresh_datetime ? (\Altum\Date::get($row->last_refresh_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_refresh_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_refresh_datetime) . ')</small>') : l('global.na')) ?>">
                                    <i class="fas fa-fw fa-calendar-check text-muted"></i>
                                </span>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('audits.next_refresh_datetime') . '<br />' . ($row->next_refresh_datetime ? (\Altum\Date::get($row->next_refresh_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->next_refresh_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_time_until($row->next_refresh_datetime) . ')</small>') : l('global.na')) ?>">
                                    <i class="fas fa-fw fa-retweet text-muted"></i>
                                </span>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                    <i class="fas fa-fw fa-calendar text-muted"></i>
                                </span>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                    <i class="fas fa-fw fa-history text-muted"></i>
                                </span>

                                <?php if($row->settings->password): ?>
                                    <span class="mr-2" data-toggle="tooltip" title="<?= l('global.password') . ': ' . l('global.yes') ?>">
                                        <i class="fas fa-fw fa-lock text-muted"></i>
                                    </span>
                                <?php else: ?>
                                    <span class="mr-2" data-toggle="tooltip" title="<?= l('global.password') . ': ' . l('global.no') ?>">
                                        <i class="fas fa-fw fa-lock-open text-muted"></i>
                                    </span>
                                <?php endif ?>

                                <?php if($row->settings->is_public): ?>
                                    <span class="mr-2" data-toggle="tooltip" title="<?= l('audits.input.is_public') . ': ' . l('global.yes') ?>">
                                        <i class="fas fa-fw fa-eye text-muted"></i>
                                    </span>
                                <?php else: ?>
                                    <span class="mr-2" data-toggle="tooltip" title="<?= l('audits.input.is_public') . ': ' . l('global.no') ?>">
                                        <i class="fas fa-fw fa-eye-slash text-muted"></i>
                                    </span>
                                <?php endif ?>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/audits/audit_dropdown_button.php', ['id' => $row->audit_id, 'resource_name' => remove_url_protocol_from_url($row->url), 'url' => $row->url, 'is_queued' => $row->is_queued]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>

                    </tbody>
                </table>
            </div>
        <?php else: ?>

            <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                'filters_get' => $data->filters->get ?? [],
                'name' => 'audits',
                'has_secondary_text' => true,
            ]); ?>

        <?php endif ?>
    </div>
</div>
