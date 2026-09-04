<?php defined('ALTUMCODE') || die() ?>

<div class="row mt-5">

    <div class="col-12 col-lg-4 mb-4 mb-lg-0">
        <div class="card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 m-0"><?= l('global.countries') ?></h2>
                    </div>
                    <span class="round-circle-sm bg-gray-200 text-primary-700 p-3">
                        <i class="fas fa-fw fa-sm fa-flag"></i>
                    </span>
                </div>

                <div class="mt-4" id="countries_result" data-limit="-1"></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card border-0">
            <div class="card-body pt-4">
                <div id="countries_map"></div>
            </div>
        </div>
    </div>

</div>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/libraries/svgMap.min.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/svgMap.min.js?v=' . PRODUCT_CODE ?>"></script>
<script>
    'use strict';

    $(`#countries_map`).html($('#loading').html());

    /* Receive data */
    $('#countries_map').on('load', (event, data) => {

        data = (typeof data === 'string') ? JSON.parse(data) : data;

        let values = {};

        for (let row of data.rows) {
            if (!row.country_code) continue;

            values[row.country_code.toUpperCase()] = {
                visitors: parseInt(row.total, 10)
            };
        }

        $('#countries_map').html('');

        let hsl_to_hex = (hsl_string) => {
            /* Extract values */
            const match = hsl_string.match(/hsl\(\s*(\d+),\s*(\d+)%?,\s*(\d+)%?\s*\)/);
            if (!match) return null;
            let hue = parseInt(match[1], 10);
            let saturation = parseInt(match[2], 10) / 100;
            let lightness = parseInt(match[3], 10) / 100;

            /* Convert to rgb */
            let chroma = (1 - Math.abs(2 * lightness - 1)) * saturation;
            let x = chroma * (1 - Math.abs((hue / 60) % 2 - 1));
            let m = lightness - chroma / 2;
            let red1 = 0, green1 = 0, blue1 = 0;

            if (hue < 60) { red1 = chroma; green1 = x; blue1 = 0; }
            else if (hue < 120) { red1 = x; green1 = chroma; blue1 = 0; }
            else if (hue < 180) { red1 = 0; green1 = chroma; blue1 = x; }
            else if (hue < 240) { red1 = 0; green1 = x; blue1 = chroma; }
            else if (hue < 300) { red1 = x; green1 = 0; blue1 = chroma; }
            else { red1 = chroma; green1 = 0; blue1 = x; }

            let red = Math.round((red1 + m) * 255);
            let green = Math.round((green1 + m) * 255);
            let blue = Math.round((blue1 + m) * 255);

            /* Convert to hex */
            return '#' + [red, green, blue].map(x =>
                x.toString(16).padStart(2, '0')
            ).join('');
        }

        let css = window.getComputedStyle(document.body);

        new svgMap({
            targetElementID: 'countries_map',
            data: {
                data: {
                    visitors: {
                        name: '',
                        format: '{0} <?= l('analytics.visitors') ?>',
                        thousandSeparator: thousands_separator,
                    },
                },
                applyData: 'visitors',
                values: values,
            },
            colorMin: hsl_to_hex(css.getPropertyValue('--primary-100').trim()),
            colorMax: hsl_to_hex(css.getPropertyValue('--primary-800').trim()),
            colorNoData: hsl_to_hex(css.getPropertyValue('--gray-200').trim()),
            flagType: 'emoji',
            noDataText: <?= json_encode(l('global.no_data')) ?>
        });

    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
