<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
 *
 * This software is proprietary software owned and licensed by AltumCode.
 * A valid license is required to use, modify, or distribute this software.
 * Unauthorized use, reproduction, modification, or distribution is prohibited.
 *
 * 🌍 Explore all AltumCode projects: https://altumcode.com/
 * 📧 Support & general inquiries: https://altumcode.com/contact
 * 📤 Download the latest version: https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 */
/* For demo purposes only */
defined('ALTUMCODE') || die();

if(isset($_GET['iframe_referral']) && $_GET['iframe_referral'] == '1') {
    setcookie('iframe_referral', '1', [
            'expires' => time() + 60 * 60 * 24 * 7,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax',
    ]);
}

$is_iframe_referral = (isset($_GET['iframe_referral']) && $_GET['iframe_referral'] == '1') || (isset($_COOKIE['iframe_referral']) && $_COOKIE['iframe_referral'] == '1');
?>

<?php if(isset($data->demo_url)): ?>
    <script>
        'use strict';

        if(window.location !== window.parent.location) {
            let demo_url = new URL(<?= json_encode($data->demo_url) ?>);
            demo_url.searchParams.set('iframe_referral', '1');

            window.top.location.href = demo_url.toString();
        }
    </script>
<?php endif ?>

<style>
    .ac-wrapper {
        position: fixed;
        width: calc(100% - 1.5rem);
        z-index: 1001;
        bottom: 0;
        margin: .75rem;
        font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
        min-height: 3rem;
        background: #161A38;
        padding: .5rem .75rem;
        display: flex;
        justify-content: space-between;
        flex-direction: row;
        align-items: center;
        font-size: .85rem;
        border-radius: 5rem;
        opacity: 0;
        animation: ac-fade-in-down .3s ease-in .6s forwards;
    }
    @media (min-width: 992px) {
        .ac-wrapper {
            border-radius: 5rem;
            width: auto;
            right: 0;
        }
    }
    .ac-altumcode-link {
        color: white;
        display: flex;
        align-items: center;
        align-self: center;
    }

    .ac-altumcode-link:hover {
        text-decoration: none;
        color: white;
    }

    .ac-altumcode-link:hover .ac-altumcode-image {
        transform: scale(1.15);
    }

    .ac-altumcode-link:active .ac-altumcode-image {
        transform: scale(.9);
    }

    .ac-altumcode-link-brand {
        font-weight: bold;
    }

    .ac-altumcode-image {
        width: 1.5rem;
        height: auto;
        margin-right: 1rem;
        transition: all .15s linear !important;
    }

    .ac-primary {
        padding: .25rem 1.25rem;
        background: #3f88fd;
        color: white;
        border-radius: 5rem;
        transition: all .15s linear !important;
        white-space: nowrap;
        font-weight: 500;
    }
    .ac-primary:hover {
        text-decoration: none;
        color: white;
        transform: scale(1.025);
    }
    .ac-primary:active {
        background: #295aa9;
        transform: scale(0.95);
    }
    .ac-secondary {
        padding: .25rem 0;
        color: hsl(255, 85%, 95%);
        border-radius: .25rem;
        transition: all .3s linear !important;
        white-space: nowrap;
        margin-right: 1.25rem;
    }
    .ac-secondary:hover {
        text-decoration: none;
        color: hsl(255, 85%, 85%);
    }
    .ac-cta-wrapper {
        display: flex;
        justify-content: space-around;
    }
    @media (min-width: 992px) {
        .ac-cta-wrapper {
            justify-content: initial;
        }
    }
    @keyframes ac-fade-in-down {
        from {
            opacity: 0;
            transform: translate3d(0, 100%, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }
    @media print {
        .ac-wrapper {
            display: none;
        }
    }

    /* Gradient background */
    .ac-bg-gradient {
        animation: ac-bg-gradient-animation 3s ease infinite alternate;
        background: linear-gradient(60deg, #e88d2a, #e3624c, #e33e72, #944e94, #46639e, #008b94, #009c86, #5ea773);
        background-size: 300% 300%;
    }

    @keyframes ac-bg-gradient-animation {
        0% {
            background-position: 0 50%;
        }
        50% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0 50%;
        }
    }
</style>

<div class="ac-wrapper">
    <a href="https://altumcode.com/" target="_blank" class="ac-altumcode-link">
        <img src="https://altumcode.com/themes/altum/assets/images/altumcode.svg" alt="AltumCode logo" class="ac-altumcode-image" />
    </a>

    <div class="ac-cta-wrapper">
        <a href="https://altumcode.com/contact" target="_blank" class="ac-secondary"><span>Support</span></a>

        <?php if($is_iframe_referral): ?>
            <a href="<?= $data->product_buy_url ?>" class="ac-primary ac-bg-gradient" target="_blank"><span>Check available discounts</span></a>
        <?php else: ?>
            <a href="<?= $data->product_buy_url ?>" class="ac-primary ac-bg-gradient" target="_blank"><span><?= 'Buy ' . $data->product_name ?></span></a>
        <?php endif ?>
    </div>
</div>
