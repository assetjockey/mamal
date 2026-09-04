<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

defined('ALTUMCODE') || die();

/* Create the content for each tab */
$html = [];

/* Extra Javascript needed */
$javascript = '';
?>

<?php /* Clicks Chart */ ?>
<?php ob_start() ?>
<div class="card mb-5">
    <div class="card-body">
        <div class="chart-container">
            <canvas id="form_submissions_chart"></canvas>
        </div>
    </div>
</div>
<?php $html['charts'] = ob_get_clean() ?>

<?php ob_start() ?>
<div class="col-12 col-md-6 col-lg-6 p-3">
    <div class="card h-100">
        <div class="card-body d-flex">
            <div>
                <div class="card bg-gray-200 text-gray-700 mr-3">
                    <div class="p-3 d-flex align-items-center justify-content-between">
                        <i class="fas fa-fw fa-database fa-lg"></i>
                    </div>
                </div>
            </div>

            <div>
                <div class="card-title h4 m-0"><?= nr($data->logs_total['form_submission']) ?></div>
                <small class="form-text text-muted"><?= l('statistics.form_submissions_chart') ?></small>
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 col-lg-6 p-3">
    <div class="card h-100">
        <div class="card-body d-flex">
            <div>
                <div class="card bg-gray-200 text-gray-700 mr-3">
                    <div class="p-3 d-flex align-items-center justify-content-between">
                        <i class="fas fa-fw fa-align-center fa-lg"></i>
                    </div>
                </div>
            </div>

            <div>
                <div class="card-title h4 m-0"><?= nr($data->logs_total['conversions'], 2) . '%' ?></div>
                <small class="form-text text-muted"><?= l('notification.statistics.conversions_chart') ?></small>
            </div>
        </div>
    </div>
</div>
<?php $html['widgets'] = ob_get_clean() ?>

<?php ob_start() ?>
<script>
    'use strict';

    let form_submissions_chart = document.getElementById('form_submissions_chart').getContext('2d');

    gradient = form_submissions_chart.createLinearGradient(0, 0, 0, 250);
    gradient.addColorStop(0, 'rgba(226, 96, 174, 0.4)');
    gradient.addColorStop(1, 'rgba(226, 96, 174, 0.05)');

    new Chart(form_submissions_chart, {
        type: 'line',
        data: {
            labels: <?= $data->logs_chart['labels'] ?>,
            datasets: [{
                label: <?= json_encode(l('notification.statistics.form_submissions_chart')) ?>,
                data: <?= $data->logs_chart['form_submission'] ?? '[]' ?>,
                backgroundColor: gradient,
                borderColor: '#E260AE',
                fill: true
            }]
        },
        options: chart_options
    });
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
