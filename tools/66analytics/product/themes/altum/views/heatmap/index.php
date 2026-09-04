<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li><a href="<?= url('heatmaps') ?>"><?= l('heatmaps.breadcrumb') ?></a> <i class="fas fa-fw fa-angle-right"></i></li>
                <li class="active" aria-current="page"><?= l('heatmap.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="row">
        <div class="col-12 col-lg d-flex align-items-end mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-fire mr-1"></i> <?= $data->heatmap->name ?></h1>
        </div>

        <div class="col-12 col-lg-auto d-flex flex-wrap gap-3 d-print-none">
            <div>
                <div
                        id="daterangepicker"
                        type="button"
                        class="btn btn-sm btn-light text-nowrap"
                        data-min-date="<?= \Altum\Date::get($data->heatmap->datetime, 4) ?>"
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

            <div>
                <div class="btn-group dropdown" role="group">
                    <a href="<?= url('heatmap/' . $data->heatmap->heatmap_id . '/desktop/' . $data->heatmap_data_type . '?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" class="btn btn-sm <?= $data->snapshot_type == 'desktop' ? 'btn-primary' : 'btn-secondary' ?>" data-toggle="tooltip" title="<?= l('heatmap_retake_snapshots_modal.snapshot_id_desktop') ?>">
                        <i class="fas fa-fw fa-sm fa-desktop mr-1"></i> <?= $data->snapshot_type == 'desktop' ? sprintf(l('heatmap.x_' . $data->heatmap_data_type), '<span id="heatmap_data_count"><div class="spinner-grow spinner-grow-sm text-light mr-1" role="status"></div></span>') : null ?>
                    </a>
                    <a href="<?= url('heatmap/' . $data->heatmap->heatmap_id . '/tablet/' . $data->heatmap_data_type . '?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" class="btn btn-sm <?= $data->snapshot_type == 'tablet' ? 'btn-primary' : 'btn-secondary' ?>" data-toggle="tooltip" title="<?= l('heatmap_retake_snapshots_modal.snapshot_id_tablet') ?>">
                        <i class="fas fa-fw fa-sm fa-tablet mr-1"></i> <?= $data->snapshot_type == 'tablet' ? sprintf(l('heatmap.x_' . $data->heatmap_data_type), '<span id="heatmap_data_count"><div class="spinner-grow spinner-grow-sm text-light mr-1" role="status"></div></span>') : null ?>
                    </a>
                    <a href="<?= url('heatmap/' . $data->heatmap->heatmap_id . '/mobile/' . $data->heatmap_data_type . '?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" class="btn btn-sm <?= $data->snapshot_type == 'mobile' ? 'btn-primary' : 'btn-secondary' ?>" data-toggle="tooltip" title="<?= l('heatmap_retake_snapshots_modal.snapshot_id_mobile') ?>">
                        <i class="fas fa-fw fa-sm fa-mobile mr-1"></i> <?= $data->snapshot_type == 'mobile' ? sprintf(l('heatmap.x_' . $data->heatmap_data_type), '<span id="heatmap_data_count"><div class="spinner-grow spinner-grow-sm text-light mr-1" role="status"></div></span>') : null ?>
                    </a>
                </div>
            </div>

            <div>
                <div class="btn-group dropdown" role="group">
                    <a href="<?= url('heatmap/' . $data->heatmap->heatmap_id . '/' . $data->snapshot_type . '/clicks' . '?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" class="btn btn-sm <?= $data->heatmap_data_type == 'clicks' ? 'btn-primary' : 'btn-secondary' ?>" data-toggle="tooltip" title="<?= l('heatmap.clicks') ?>">
                        <i class="fas fa-fw fa-sm fa-mouse-pointer mr-1"></i> <?= $data->heatmap_data_type == 'clicks' ? sprintf(l('heatmap.' . $data->heatmap_data_type), '<span id="heatmap_data_count"><div class="spinner-grow spinner-grow-sm text-light mr-1" role="status"></div></span>') : null ?>
                    </a>
                    <a href="<?= url('heatmap/' . $data->heatmap->heatmap_id . '/' . $data->snapshot_type . '/scrolls' . '?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" class="btn btn-sm <?= $data->heatmap_data_type == 'scrolls' ? 'btn-primary' : 'btn-secondary' ?>" data-toggle="tooltip" title="<?= l('heatmap.scrolls') ?>">
                        <i class="fas fa-fw fa-sm fa-mouse mr-1"></i> <?= $data->heatmap_data_type == 'scrolls' ? sprintf(l('heatmap.' . $data->heatmap_data_type), '<span id="heatmap_data_count"><div class="spinner-grow spinner-grow-sm text-light mr-1" role="status"></div></span>') : null ?>
                    </a>
                </div>
            </div>

            <?php if(!$this->team): ?>
                <?= include_view(THEME_PATH . 'views/heatmaps/heatmap_dropdown_button.php', ['id' => $data->heatmap->heatmap_id, 'name' => $data->heatmap->name, 'is_enabled' => $data->heatmap->is_enabled, 'button_text_class' => 'btn-sm text-secondary']) ?>
            <?php endif ?>
        </div>
    </div>

    <div class="mt-2 mb-4">
        <?php if($data->heatmap->is_enabled): ?>
            <span class="badge badge-success" data-tooltip title="<?= l('heatmaps.is_enabled_true') ?>"><i class="fas fa-fw fa-sm fa-check-circle"></i></span>
        <?php else: ?>
            <span class="badge badge-warning" data-tooltip title="<?= l('heatmaps.is_enabled_false') ?>"><i class="fas fa-fw fa-sm fa-times-circle"></i></span>
        <?php endif ?>

        <span class="ml-1 badge badge-light">
            <?= $this->website->host . $this->website->path . $data->heatmap->path ?>
            <a href="<?= $this->website->scheme . $this->website->host . $this->website->path . $data->heatmap->path ?>" target="_blank" rel="nofollow noreferrer"><i class="fas fa-fw fa-xs fa-external-link-alt ml-1"></i></a>
        </span>
    </div>


    <div class="notification-container mb-3"></div>

    <?php if(!$data->has_snapshot): ?>

        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column align-items-center justify-content-center py-4">
                    <img src="<?= ASSETS_FULL_URL . 'images/collecting.svg' ?>" class="col-10 col-md-6 col-lg-4 mb-3" alt="<?= l('heatmap.no_data') ?>" />
                    <h2 class="h4 text-muted"><?= l('heatmap.no_data') ?></h2>
                    <p class="text-muted m-0"><?= l('heatmap.no_data_help') ?></a></p>
                </div>
            </div>
        </div>

    <?php else: ?>

        <div class="position-relative">
            <div id="heatmap-loading">
                <div class="heatmap-container bg-white shadow-md rounded-2x">
                    <div class="d-flex justify-content-center align-items-center py-5">
                        <div class="spinner-grow text-primary" role="status"></div>
                    </div>
                </div>
            </div>

            <div id="heatmap-container" class="heatmap-container shadow-md rounded-2x" style="display: none;">
                <div id="heatmap-inner" class="position-relative">
                    <canvas id="heatmap-canvas" class="heatmap-canvas"></canvas>
                </div>
            </div>
        </div>

    <?php endif ?>
</div>


<?php if($data->has_snapshot): ?>
    <?php ob_start() ?>
    <link href="<?= ASSETS_FULL_URL . 'css/libraries/daterangepicker.min.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
    <?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

    <?php ob_start() ?>
    <script src="<?= ASSETS_FULL_URL . 'js/libraries/simpleheat.js?v=' . PRODUCT_CODE ?>"></script>
    <script src="<?= ASSETS_FULL_URL . 'js/libraries/rrweb.mod.js?v=' . PRODUCT_CODE ?>"></script>
    <script src="<?= ASSETS_FULL_URL . 'js/libraries/moment.min.js?v=' . PRODUCT_CODE ?>"></script>
    <script src="<?= ASSETS_FULL_URL . 'js/libraries/daterangepicker.min.js?v=' . PRODUCT_CODE ?>"></script>
    <script src="<?= ASSETS_FULL_URL . 'js/libraries/moment-timezone-with-data-10-year-range.min.js?v=' . PRODUCT_CODE ?>"></script>

    <script>
        'use strict';

        let type = <?= json_encode($data->heatmap_data_type) ?>;
        let player = null;
        let counter = 0;

        /* TYPE: Clicks */
        let simpleheatdata = null; /* expects [x_norm,y_norm,count] */
        let heat = null;

        /* TYPE: Scrolls */
        let scrolls_data = null; /* expects [max_scroll, count] */
        let scrolls_text = <?= json_encode(l('heatmap.scrolls_heatmap_overlay')) ?>;

        /* Request the data */
        let ajax_request = $.ajax({
            type: 'GET',
            url: <?= json_encode(url('heatmap/read/' . $data->heatmap->heatmap_id . '/' . $data->snapshot_type . '/' . $data->heatmap_data_type . '?start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date'])) ?>,
            dataType: 'json',
            timeout: 15000,

            success: (result) => {
                try {
                    /* Generate the heatmap */
                    player = new rrweb_heat.Replayer(result.snapshot_data, {
                        root: document.querySelector('#heatmap-inner'),
                    });

                    player.play();

                    /* Save the data for the heatmap */
                    if(type == 'clicks') {
                        simpleheatdata = result.heatmap_data; /* [x_norm,y_norm,count] */
                    }
                    else if(type == 'scrolls') {
                        scrolls_data = result.heatmap_data; /* [max_scroll, count] */
                    }

                    /* Count */
                    counter = result.heatmap_data_count;
                    $('#heatmap_data_count').text(nr(result.heatmap_data_count));

                    /* Display it */
                    $('#heatmap-container').show();

                    /* Draw the heatmap after x time */
                    setTimeout(() => {
                        if(type == 'clicks') {
                            heatmap_draw_clicks();
                        }
                        else if(type == 'scrolls') {
                            heatmap_draw_scrolls();
                        }

                        /* Remove the loading state */
                        $('#heatmap-loading').html('');
                    }, 1000);

                } catch (error) {
                    console.log(error);
                }
            },

            error: (xhr, status) => {
                console.log(status, xhr);
            },
        });

        /* Prepare the heatmap */
        let heatmap_resize = () => {
            let $iframe = $('#heatmap-inner iframe');
            if(!$iframe.length) return;

            let width = $iframe.data('width') || $iframe[0].getBoundingClientRect().width;

            let height = 0;
            try {
                height = $iframe[0].contentWindow.document.body.scrollHeight;
            } catch (e) {
                return;
            }

            $('#heatmap-container').css('width', width);
            $('#heatmap-inner').css('width', width).css('height', height);

            $('#heatmap-canvas').attr('width', width).attr('height', height);
            $iframe.attr('width', width).attr('height', height);

            heatmap_proper_scale();
        };

        let heatmap_proper_scale = () => {
            let container_width = $('div[class="container"]').width();
            let heatmap_container_width = $('#heatmap-container').width();

            $('#heatmap-container').css('transform', '').css('margin-bottom', '').css('transform-origin', 'top left');

            if(heatmap_container_width > container_width) {
                let transform_scale = container_width / heatmap_container_width;
                $('#heatmap-container').css('transform', `scale(${transform_scale})`);

                let heatmap_height = $('#heatmap-container')[0].getBoundingClientRect().height;
                let margin_bottom = (1 - transform_scale) * heatmap_height;
                $('#heatmap-container').css('margin-bottom', `-${margin_bottom}px`);
            }
        };

        let debounce = (fn, wait) => {
            let timeout = null;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => fn(...args), wait);
            };
        };

        $(window).on('resize', debounce(() => {
            heatmap_proper_scale();
        }, 150));

        let heatmap_denormalize_data_clicks = (width, height) => {
            if(!simpleheatdata || !simpleheatdata.length) return [];

            let data = new Array(simpleheatdata.length);

            for(let i = 0; i < simpleheatdata.length; i++) {
                let x_norm = simpleheatdata[i][0] || 0;
                let y_norm = simpleheatdata[i][1] || 0;
                let value = simpleheatdata[i][2] || 1;

                /* Clamp just in case */
                if(x_norm < 0) x_norm = 0; else if(x_norm > 1) x_norm = 1;
                if(y_norm < 0) y_norm = 0; else if(y_norm > 1) y_norm = 1;

                data[i] = [
                    Math.round(x_norm * width),
                    Math.round(y_norm * height),
                    value
                ];
            }

            return data;
        };

        let heatmap_draw_clicks = () => {
            heatmap_resize();

            if(!heat) heat = simpleheat('heatmap-canvas');

            let canvas_width = parseInt($('#heatmap-canvas').attr('width')) || 0;
            let canvas_height = parseInt($('#heatmap-canvas').attr('height')) || 0;

            let denormalized_data = heatmap_denormalize_data_clicks(canvas_width, canvas_height);

            /* Weighted points support: [x,y,count] */
            let max_value = 0;
            for(let i = 0; i < denormalized_data.length; i++) {
                let value = denormalized_data[i][2] || 1;
                if(value > max_value) max_value = value;
            }

            heat.data(denormalized_data).max(max_value || 1).draw();
        };

        let heatmap_draw_scrolls = () => {
            heatmap_resize();

            if(!scrolls_data || !scrolls_data.length) return;

            let canvas = document.getElementById('heatmap-canvas');
            if(!canvas) return;

            let canvas_width = parseInt($('#heatmap-canvas').attr('width')) || 0;
            let canvas_height = parseInt($('#heatmap-canvas').attr('height')) || 0;
            if(!canvas_width || !canvas_height) return;

            let context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas_width, canvas_height);

            /* Initialize all buckets to 0 (backend can be sparse) */
            let bucket_counts = {};
            for(let bucket = 0; bucket <= 100; bucket += 10) {
                bucket_counts[bucket] = 0;
            }

            /* Fill from backend: expects [max_scroll_bucket, count] */
            for(let i = 0; i < scrolls_data.length; i++) {
                let max_scroll = parseInt(scrolls_data[i][0]);
                let count = parseInt(scrolls_data[i][1]) || 0;

                if(isNaN(max_scroll)) continue;

                if(max_scroll < 0) max_scroll = 0;
                else if(max_scroll > 100) max_scroll = 100;

                /* Backend should already be bucketed, but keep it safe */
                max_scroll = Math.floor(max_scroll / 10) * 10;

                bucket_counts[max_scroll] = (bucket_counts[max_scroll] || 0) + count;
            }

            /* Total rows represented by this dataset */
            let total = 0;
            for(let bucket = 0; bucket <= 100; bucket += 10) {
                total += bucket_counts[bucket] || 0;
            }
            if(total <= 0) return;

            /*
              Reach for zone end threshold:
              zone 0-10: count(max_scroll >= 10)
              zone 10-20: count(max_scroll >= 20)
              ...
              zone 90-100: count(max_scroll >= 100)
            */
            let reach_by_threshold = {};
            for(let threshold = 10; threshold <= 100; threshold += 10) {
                let reach = 0;

                for(let bucket = 0; bucket <= 100; bucket += 10) {
                    if(bucket >= threshold) {
                        reach += bucket_counts[bucket] || 0;
                    }
                }

                reach_by_threshold[threshold] = reach;
            }

            let max_reach = reach_by_threshold[10] || 0;
            if(max_reach <= 0) return;

            /* Text settings */
            context.textAlign = 'left';
            context.textBaseline = 'alphabetic';
            context.font = '18px Arial';

            let padding_x = 8;
            let padding_y = 6;
            let label_x = 6;

            for(let zone_index = 0; zone_index < 10; zone_index++) {
                let start_percentage = zone_index * 10;
                let end_percentage = (zone_index + 1) * 10;

                let reach = reach_by_threshold[end_percentage] || 0;

                let ratio = reach / max_reach;
                if(ratio < 0) ratio = 0;
                else if(ratio > 1) ratio = 1;

                let y_start = Math.round((start_percentage / 100) * canvas_height);
                let y_end = Math.round((end_percentage / 100) * canvas_height);
                if(y_end <= y_start) y_end = y_start + 1;

                /* Overlay fill */
                let alpha = 0.06 + (ratio * 0.42);
                context.fillStyle = `rgba(255, 0, 0, ${alpha})`;
                context.fillRect(0, y_start, canvas_width, y_end - y_start);

                /* Label at left, bottom of the zone */
                let percent_of = (part, total) => {
                    if (total === 0) return 0;
                    return nr((part / total) * 100, 2);
                }
                let percentage_reach = percent_of(reach, counter);
                let label_text = scrolls_text.replace('%1$s', `${end_percentage}%`).replace('%2$s', nr(reach)).replace('%3$s', nr(counter)).replace('%4$s', `${percentage_reach}%`);
                let label_y = y_end - 6;

                /* Keep label inside canvas */
                if(label_y < 12) label_y = 12;
                if(label_y > canvas_height - 2) label_y = canvas_height - 2;

                let text_width = Math.ceil(context.measureText(label_text).width);

                /* Small background for readability */
                context.fillStyle = 'rgba(0, 0, 0, 1)';
                context.fillRect(label_x - padding_x, label_y - 12 - padding_y, text_width + (padding_x * 2), 12 + (padding_y * 2));

                context.fillStyle = 'rgba(255, 255, 255, 0.95)';
                context.fillText(label_text, label_x, label_y);
            }
        };

        /* Daterangepicker */
        moment.tz.setDefault(<?= json_encode($this->user->timezone ?? \Altum\Date::$default_timezone) ?>);

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

            /* Redirect */
            redirect(`<?= url('heatmap/' . $data->heatmap->heatmap_id . '/' . $data->snapshot_type) ?>?start_date=${start.format('YYYY-MM-DD')}&end_date=${end.format('YYYY-MM-DD')}`, true);

        });
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
<?php else: ?>

    <?php ob_start() ?>
    <script>
        'use strict';

        /* Count */
        $('#heatmap_data_count').html('0');
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php endif ?>
