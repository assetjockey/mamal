<?php defined('ALTUMCODE') || die() ?>

<?php $available_plan_features = require APP_PATH . 'includes/available_plan_features.php' ?>
<?php $features = ((array) (settings()->payment->plan_features ?? [])) + array_fill_keys($available_plan_features, true) ?>
<?php $features_in_front = ((array) (settings()->payment->plan_features_in_front ?? [])) + array_fill_keys($available_plan_features, true) ?>

<?php $not_in_front_html = ''; ?>

<ul class="pricing-features">
    <?php foreach($features as $feature => $is_enabled): ?>
        <?php if(!$is_enabled) continue ?>

        <?php ob_start() ?>

        <?php if($feature == 'websites_limit'): ?>
            <li>
                <div>
                    <?= sprintf(l('global.plan_settings.websites_limit'), '<strong>' . ($data->plan_settings->websites_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->websites_limit)) . '</strong>') ?>
                </div>
                <i class="fas fa-fw fa-check fa-sm text-success"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'sessions_events_limit'): ?>
            <li>
                <div>
                    <?= sprintf(l('global.plan_settings.sessions_events_limit'), '<strong>' . ($data->plan_settings->sessions_events_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->sessions_events_limit)) . '</strong>') . ' <br /> <small class="text-muted">' . l('global.plan_settings.per_month') . '</small>' ?>
                </div>
                <i class="fas fa-fw fa-check fa-sm text-success"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'sessions_events_retention'): ?>
            <li>
                <div data-toggle="tooltip" title="<?= ($data->plan_settings->sessions_events_retention == -1 ? '' : $data->plan_settings->sessions_events_retention . ' ' . l('global.date.days')) ?>">
                    <?= sprintf(l('global.plan_settings.sessions_events_retention'), '<strong>' . ($data->plan_settings->sessions_events_retention == -1 ? l('global.unlimited') : \Altum\Date::days_format($data->plan_settings->sessions_events_retention)) . '</strong>') ?>
                </div>
                <i class="fas fa-fw fa-sm fa-check text-success"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'events_children_limit'): ?>
            <li>
                <div>
                    <?= sprintf(l('global.plan_settings.events_children_limit'), '<strong>' . ($data->plan_settings->events_children_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->events_children_limit)) . '</strong>') . ' <br /> <small class="text-muted">' . l('global.plan_settings.per_month') . '</small>' ?>
                </div>
                <i class="fas fa-fw fa-sm <?= !$data->plan_settings->events_children_limit ? 'fa-times text-muted' : 'fa-check text-success' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'events_children_retention' && $data->plan_settings->events_children_limit != 0): ?>
            <li>
                <div data-toggle="tooltip" title="<?= ($data->plan_settings->events_children_retention == -1 ? '' : $data->plan_settings->events_children_retention . ' ' . l('global.date.days')) ?>">
                    <?= sprintf(l('global.plan_settings.events_children_retention'), '<strong>' . ($data->plan_settings->events_children_retention == -1 ? l('global.unlimited') : \Altum\Date::days_format($data->plan_settings->events_children_retention)) . '</strong>') ?>
                </div>
                <i class="fas fa-fw fa-sm fa-check text-success"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'sessions_replays_limit' && settings()->analytics->sessions_replays_is_enabled): ?>
            <li>
                <div>
                    <?= sprintf(l('global.plan_settings.sessions_replays_limit'), '<strong>' . ($data->plan_settings->sessions_replays_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->sessions_replays_limit)) . '</strong>') . ' <br /> <small class="text-muted">' . l('global.plan_settings.per_month') . '</small>' ?>
                </div>
                <i class="fas fa-fw fa-sm <?= !$data->plan_settings->sessions_replays_limit ? 'fa-times text-muted' : 'fa-check text-success' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'sessions_replays_retention' && settings()->analytics->sessions_replays_is_enabled && $data->plan_settings->sessions_replays_limit != 0): ?>
            <li>
                <div data-toggle="tooltip" title="<?= ($data->plan_settings->sessions_replays_retention == -1 ? '' : $data->plan_settings->sessions_replays_retention . ' ' . l('global.date.days')) ?>">
                    <?= sprintf(l('global.plan_settings.sessions_replays_retention'), '<strong>' . ($data->plan_settings->sessions_replays_retention == -1 ? l('global.unlimited') : \Altum\Date::days_format($data->plan_settings->sessions_replays_retention)) . '</strong>') ?>
                </div>
                <i class="fas fa-fw fa-sm fa-check text-success"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'websites_heatmaps_limit' && settings()->analytics->websites_heatmaps_is_enabled): ?>
            <li>
                <div>
                    <?= sprintf(l('global.plan_settings.websites_heatmaps_limit'), '<strong>' . ($data->plan_settings->websites_heatmaps_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->websites_heatmaps_limit)) . '</strong>') ?>
                </div>
                <i class="fas fa-fw fa-sm <?= !$data->plan_settings->websites_heatmaps_limit ? 'fa-times text-muted' : 'fa-check text-success' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'websites_goals_limit'): ?>
            <li>
                <div>
                    <?= sprintf(l('global.plan_settings.websites_goals_limit'), '<strong>' . ($data->plan_settings->websites_goals_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->websites_goals_limit)) . '</strong>') ?>
                </div>
                <i class="fas fa-fw fa-sm <?= !$data->plan_settings->websites_goals_limit ? 'fa-times text-muted' : 'fa-check text-success' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'annotations_limit' && settings()->analytics->annotations_is_enabled): ?>
            <li>
                <div>
                    <?= sprintf(l('global.plan_settings.annotations_limit'), '<strong>' . ($data->plan_settings->annotations_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->annotations_limit)) . '</strong>') ?>
                </div>
                <i class="fas fa-fw fa-sm <?= !$data->plan_settings->annotations_limit ? 'fa-times text-muted' : 'fa-check text-success' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'dashboard_views_limit' && settings()->analytics->dashboard_views_is_enabled): ?>
            <li>
                <div>
                    <?= sprintf(l('global.plan_settings.dashboard_views_limit'), '<strong>' . ($data->plan_settings->dashboard_views_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->dashboard_views_limit)) . '</strong>') ?>
                </div>
                <i class="fas fa-fw fa-sm <?= !$data->plan_settings->dashboard_views_limit ? 'fa-times text-muted' : 'fa-check text-success' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'domains_limit' && settings()->analytics->domains_is_enabled): ?>
            <li>
                <div>
                    <?= sprintf(l('global.plan_settings.domains_limit'), '<strong>' . ($data->plan_settings->domains_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->domains_limit)) . '</strong>') ?>
                </div>
                <i class="fas fa-fw fa-sm <?= !$data->plan_settings->domains_limit ? 'fa-times text-muted' : 'fa-check text-success' ?>"></i>
            </li>
        <?php endif ?>

		<?php if($feature == 'additional_domains' && settings()->analytics->additional_domains_is_enabled): ?>
			<?php $additional_domains = (new \Altum\Models\Domain())->get_available_additional_domains(); ?>

            <li>
                <div>
					<?= sprintf(l('global.plan_settings.additional_domains'), '<strong>' . nr(count($data->plan_settings->additional_domains ?? [])) . '</strong>') ?>
                    <span class="mr-1" data-toggle="tooltip" title="<?= sprintf(l('global.plan_settings.additional_domains_help'), implode(', ', array_map(function($domain_id) use($additional_domains) { return $additional_domains[$domain_id]->host ?? null; }, $data->plan_settings->additional_domains ?? []))) ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
                <i class="fas fa-fw fa-sm <?= count($data->plan_settings->additional_domains ?? []) ? 'fa-check text-success' : 'fa-times' ?>"></i>
            </li>
		<?php endif ?>

        <?php if($feature == 'affiliate_commission_percentage' && \Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled): ?>
            <li>
                <div>
                    <?= sprintf(l('global.plan_settings.affiliate_commission_percentage'), '<strong>' . nr($data->plan_settings->affiliate_commission_percentage) . '%</strong>') ?>
                </div>
                <i class="fas fa-fw fa-sm <?= !$data->plan_settings->affiliate_commission_percentage ? 'fa-times text-muted' : 'fa-check text-success' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'email_reports_is_enabled' && settings()->analytics->email_reports_is_enabled): ?>
            <li>
                <div>
                    <?= settings()->analytics->email_reports_is_enabled ? l('global.plan_settings.email_reports_is_enabled_' . settings()->analytics->email_reports_is_enabled) : l('global.plan_settings.email_reports_is_enabled') ?>
                </div>
                <i class="fas fa-fw fa-sm <?=$data->plan_settings->email_reports_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'teams_is_enabled'): ?>
            <li>
                <div>
                    <?= l('global.plan_settings.teams_is_enabled') ?>
                </div>
                <i class="fas fa-fw fa-sm <?=$data->plan_settings->teams_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'no_ads'): ?>
            <li>
                <div>
                    <?= l('global.plan_settings.no_ads') ?>
                </div>
                <i class="fas fa-fw fa-sm <?=$data->plan_settings->no_ads ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'api_is_enabled' && settings()->main->api_is_enabled): ?>
            <li>
                <div>
                    <?= l('global.plan_settings.api_is_enabled') ?>
                </div>
                <i class="fas fa-fw fa-sm <?=$data->plan_settings->api_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'white_labeling_is_enabled' && settings()->main->white_labeling_is_enabled): ?>
            <li>
                <div>
                    <?= l('global.plan_settings.white_labeling_is_enabled') ?>
                </div>
                <i class="fas fa-fw fa-sm <?=$data->plan_settings->white_labeling_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == sprintf(l('global.plan_settings.export'), '')): ?>
            <?php $enabled_exports_count = count(array_filter((array) $data->plan_settings->export)); ?>
            <?php ob_start() ?>
            <div class='d-flex flex-column'>
                <?php foreach(['csv', 'json', 'pdf'] as $key): ?>
                    <?php if($data->plan_settings->export->{$key}): ?>
                        <span class='my-1'><?= sprintf(l('global.export_to'), mb_strtoupper($key)) ?></span>
                    <?php else: ?>
                        <s class='my-1'><?= sprintf(l('global.export_to'), mb_strtoupper($key)) ?></s>
                    <?php endif ?>
                <?php endforeach ?>
            </div>
            <?php $html = ob_get_clean() ?>

            <li>
                <div>
                    <?= sprintf(l('global.plan_settings.export'), $enabled_exports_count) ?>
                    <span class="mr-1" data-html="true" data-toggle="tooltip" title="<?= $html ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
                <i class="fas fa-fw fa-sm <?=$enabled_exports_count ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php
        if($features_in_front[$feature]) {
            echo ob_get_clean();
        } else {
            $not_in_front_html .= trim(ob_get_clean());
        }
        ?>

    <?php endforeach ?>

    <?php if(!empty($not_in_front_html)): ?>
        <div class="d-flex justify-content-between align-items-center my-3">
            <button type="button" class="btn btn-sm btn-outline-light btn-block text-reset text-decoration-none font-weight-bold px-5" data-toggle="collapse" data-target=".view_all_container">
                <i class="fas fa-fw fa-sm fa-plus-circle mr-1"></i> <?= l('global.view_all') ?>
            </button>
        </div>

        <div class="collapse view_all_container">
            <?= $not_in_front_html ?>
        </div>
    <?php endif ?>
</ul>
