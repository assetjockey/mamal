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
            <canvas id="clicks_chart"></canvas>
        </div>
    </div>
</div>
<?php $html['charts'] = ob_get_clean() ?>

<?php ob_start() ?>
<script>
    'use strict';
    
    let clicks_chart = document.getElementById('clicks_chart').getContext('2d');

    gradient = clicks_chart.createLinearGradient(0, 0, 0, 250);
    gradient.addColorStop(0, 'rgba(96, 187, 226, 0.4)');
    gradient.addColorStop(1, 'rgba(96, 187, 226, 0.05)');

    new Chart(clicks_chart, {
        type: 'line',
        data: {
            labels: <?= $data->logs_chart['labels'] ?>,
            datasets: [{
                label: <?= json_encode(l('notification.statistics.clicks_chart')) ?>,
                data: <?= $data->logs_chart['click'] ?? '[]' ?>,
                backgroundColor: gradient,
                borderColor: '#60BBE2',
                fill: true
            }]
        },
        options: chart_options
    });
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
