<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php
    $enabled_links = [];
    if(settings()->links->biolinks_is_enabled) $enabled_links[] = 'biolink';
    if(settings()->links->shortener_is_enabled) $enabled_links[] = 'link';
    if(settings()->links->files_is_enabled) $enabled_links[] = 'file';
    if(settings()->links->vcards_is_enabled) $enabled_links[] = 'vcard';
    if(settings()->links->events_is_enabled) $enabled_links[] = 'event';
    if(settings()->links->static_is_enabled) $enabled_links[] = 'static';
    $enabled_links_count = count($enabled_links);

    $col_class = match ($enabled_links_count) {
        1 => 'col-12',
        2,4 => 'col-12 col-sm-6',
        3,5,6 => 'col-12 col-sm-6 col-xl-4',
        default => null,
    }
    ?>

    <div class="mb-5">
        <div class="row m-n2 justify-content-between">
            <?php if(settings()->links->biolinks_is_enabled): ?>
                <div class="<?= $col_class ?> p-2 position-relative text-truncate">
                    <div class="card d-flex flex-row h-100 overflow-hidden">
                        <div class="pl-3 d-flex flex-column justify-content-center">
                            <a href="<?= url('links?type=biolink') ?>" class="stretched-link">
                                <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center" style="background: #eff6ff; color: #3b82f6;">
                                    <i class="fas fa-fw fa-sm fa-hashtag"></i>
                                </div>
                            </a>
                        </div>
                        <div class="card-body text-truncate d-flex align-items-center">
                            <div id="biolink_links_total" class="text-truncate">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if(settings()->links->shortener_is_enabled): ?>
                <div class="<?= $col_class ?> p-2 position-relative text-truncate">
                    <div class="card d-flex flex-row h-100 overflow-hidden">
                        <div class="pl-3 d-flex flex-column justify-content-center">
                            <a href="<?= url('links?type=link') ?>" class="stretched-link">
                                <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center" style="background: #f0fdfa; color: #14b8a6;">
                                    <i class="fas fa-fw fa-sm fa-link"></i>
                                </div>
                            </a>
                        </div>
                        <div class="card-body text-truncate d-flex align-items-center">
                            <div id="link_links_total" class="text-truncate">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if(settings()->links->files_is_enabled): ?>
                <div class="<?= $col_class ?> p-2 position-relative text-truncate">
                    <div class="card d-flex flex-row h-100 overflow-hidden">
                        <div class="pl-3 d-flex flex-column justify-content-center">
                            <a href="<?= url('links?type=file') ?>" class="stretched-link">
                                <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center" style="background: #ecfdf5; color: #10b981;">
                                    <i class="fas fa-fw fa-sm fa-file"></i>
                                </div>
                            </a>
                        </div>
                        <div class="card-body text-truncate d-flex align-items-center">
                            <div id="file_links_total" class="text-truncate">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if(settings()->links->vcards_is_enabled): ?>
                <div class="<?= $col_class ?> p-2 position-relative text-truncate">
                    <div class="card d-flex flex-row h-100 overflow-hidden">
                        <div class="pl-3 d-flex flex-column justify-content-center">
                            <a href="<?= url('links?type=vcard') ?>" class="stretched-link">
                                <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center" style="background: #ecfeff; color: #06b6d4;">
                                    <i class="fas fa-fw fa-sm fa-id-card"></i>
                                </div>
                            </a>
                        </div>
                        <div class="card-body text-truncate d-flex align-items-center">
                            <div id="vcard_links_total" class="text-truncate">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if(settings()->links->events_is_enabled): ?>
                <div class="<?= $col_class ?> p-2 position-relative text-truncate">
                    <div class="card d-flex flex-row h-100 overflow-hidden">
                        <div class="pl-3 d-flex flex-column justify-content-center">
                            <a href="<?= url('links?type=event') ?>" class="stretched-link">
                                <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center" style="background: #eef2ff; color: #6366f1;">
                                    <i class="fas fa-fw fa-sm fa-calendar"></i>
                                </div>
                            </a>
                        </div>
                        <div class="card-body text-truncate d-flex align-items-center">
                            <div id="event_links_total" class="text-truncate">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if(settings()->links->static_is_enabled): ?>
                <div class="<?= $col_class ?> p-2 position-relative text-truncate">
                    <div class="card d-flex flex-row h-100 overflow-hidden">
                        <div class="pl-3 d-flex flex-column justify-content-center">
                            <a href="<?= url('links?type=static') ?>" class="stretched-link">
                                <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center" style="background: #fdf4ff; color: #c026d3;">
                                    <i class="fas fa-fw fa-sm fa-file-code"></i>
                                </div>
                            </a>
                        </div>
                        <div class="card-body text-truncate d-flex align-items-center">
                            <div id="static_links_total" class="text-truncate">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>
        </div>

        <div class="card mt-5">
            <div class="card-body">
                <div class="chart-container d-none" id="pageviews_chart_container">
                    <canvas id="pageviews_chart"></canvas>
                </div>

                <div id="pageviews_chart_no_data" class="d-none">
                    <?= include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
                </div>

                <div id="pageviews_chart_loading" class="chart-container d-flex align-items-center justify-content-center">
                    <span class="spinner-border spinner-border-lg" role="status"></span>
                </div>

                <?php if(settings()->main->chart_cache): ?>
                    <small class="text-muted d-none" id="pageviews_chart_help">
                        <span data-toggle="tooltip" title="<?= sprintf(l('global.chart_help'), settings()->main->chart_cache ?? 12, settings()->main->chart_days ?? 30) ?>"><i class="fas fa-fw fa-sm fa-info-circle mr-1"></i></span>
                        <span class="d-lg-none"><?= sprintf(l('global.chart_help'), settings()->main->chart_cache ?? 12, settings()->main->chart_days ?? 30) ?></span>
                    </small>
                <?php endif ?>
            </div>
        </div>

        <?php require THEME_PATH . 'views/partials/js_chart_defaults.php' ?>
    </div>

    <?= $this->views['links_content'] ?>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    (async function fetch_statistics() {
        /* Send request to server */
        let response = await fetch(`${url}dashboard/get_stats_ajax`, {
            method: 'get',
        });

        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            /* :)  */
        }

        if(!response.ok) {
            /* :)  */
        }

        if(data.status == 'error') {
            /* :)  */
        } else if(data.status == 'success') {

            /* update link_links_total */
            const link_links_total_element = document.querySelector('#link_links_total');
            if (link_links_total_element) {
                let link_links_translation = <?= json_encode(l('dashboard.links')) ?>;
                let link_links_total = data.details.link_links_total ? nr(data.details.link_links_total) : 0;
                link_links_total_element.innerHTML = `<span class='h6' id='link_links_total'>${link_links_total} ${link_links_translation}</span>`
            }

            /* update file_links_total */
            const file_links_total_element = document.querySelector('#file_links_total');
            if (file_links_total_element) {
                let file_links_translation = <?= json_encode(l('dashboard.file_links')) ?>;
                let file_links_total = data.details.file_links_total ? nr(data.details.file_links_total) : 0;
                file_links_total_element.innerHTML = `<span class='h6' id='file_links_total'>${file_links_total} ${file_links_translation}</span>`;
            }

            /* update vcard_links_total */
            const vcard_links_total_element = document.querySelector('#vcard_links_total');
            if (vcard_links_total_element) {
                let vcard_links_translation = <?= json_encode(l('dashboard.vcard_links')) ?>;
                let vcard_links_total = data.details.vcard_links_total ? nr(data.details.vcard_links_total) : 0;
                vcard_links_total_element.innerHTML = `<span class='h6' id='vcard_links_total'>${vcard_links_total} ${vcard_links_translation}</span>`;
            }

            /* update biolink_links_total */
            const biolink_links_total_element = document.querySelector('#biolink_links_total');
            if (biolink_links_total_element) {
                let biolink_links_translation = <?= json_encode(l('dashboard.biolinks')) ?>;
                let biolink_links_total = data.details.biolink_links_total ? nr(data.details.biolink_links_total) : 0;
                biolink_links_total_element.innerHTML = `<span class='h6' id='biolink_links_total'>${biolink_links_total} ${biolink_links_translation}</span>`;
            }

            /* update event_links_total */
            const event_links_total_element = document.querySelector('#event_links_total');
            if (event_links_total_element) {
                let event_links_translation = <?= json_encode(l('dashboard.event_links')) ?>;
                let event_links_total = data.details.event_links_total ? nr(data.details.event_links_total) : 0;
                event_links_total_element.innerHTML = `<span class='h6' id='event_links_total'>${event_links_total} ${event_links_translation}</span>`;
            }

            /* update static_links_total */
            const static_links_total_element = document.querySelector('#static_links_total');
            if (static_links_total_element) {
                let static_links_translation = <?= json_encode(l('dashboard.static_links')) ?>;
                let static_links_total = data.details.static_links_total ? nr(data.details.static_links_total) : 0;
                static_links_total_element.innerHTML = `<span class='h6' id='static_links_total'>${static_links_total} ${static_links_translation}</span>`;
            }

            /* Remove loading */
            document.querySelector('#pageviews_chart_loading').classList.add('d-none');
            document.querySelector('#pageviews_chart_loading').classList.remove('d-flex');

            /* Chart */
            if(data.details.links_chart.is_empty) {
                document.querySelector('#pageviews_chart_no_data').classList.remove('d-none');
            } else {
                /* Display chart data */
                document.querySelector('#pageviews_chart_container').classList.remove('d-none');
                document.querySelector('#pageviews_chart_help') && document.querySelector('#pageviews_chart_help').classList.remove('d-none');

                let css = window.getComputedStyle(document.body);
                let pageviews_color = css.getPropertyValue('--primary');
                let visitors_color = css.getPropertyValue('--gray-300');
                let pageviews_color_gradient = null;
                let visitors_color_gradient = null;

                /* Chart */
                let pageviews_chart = document.getElementById('pageviews_chart').getContext('2d');

                /* Colors */
                pageviews_color_gradient = pageviews_chart.createLinearGradient(0, 0, 0, 250);
                pageviews_color_gradient.addColorStop(0, set_hex_opacity(pageviews_color, 0.6));
                pageviews_color_gradient.addColorStop(1, set_hex_opacity(pageviews_color, 0.1));

                visitors_color_gradient = pageviews_chart.createLinearGradient(0, 0, 0, 250);
                visitors_color_gradient.addColorStop(0, set_hex_opacity(visitors_color, 0.6));
                visitors_color_gradient.addColorStop(1, set_hex_opacity(visitors_color, 0.1));

                new Chart(pageviews_chart, {
                    type: 'line',
                    data: {
                        labels: JSON.parse(data.details.links_chart.labels ?? '[]'),
                        datasets: [
                            {
                                label: <?= json_encode(l('link.statistics.pageviews')) ?>,
                                data: JSON.parse(data.details.links_chart.pageviews ?? '[]'),
                                backgroundColor: pageviews_color_gradient,
                                borderColor: pageviews_color,
                                fill: true
                            },
                            {
                                label: <?= json_encode(l('link.statistics.visitors')) ?>,
                                data: JSON.parse(data.details.links_chart.visitors ?? '[]'),
                                backgroundColor: visitors_color_gradient,
                                borderColor: visitors_color,
                                fill: true
                            }
                        ]
                    },
                    options: chart_options
                });
            }
        }
    })();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
