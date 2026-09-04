<?php

/**
 * Format the page title.
 */
function formatTitle(array|string $value): string
{
    if (is_array($value)) {
        return implode(" - ", array_filter($value));
    }

    return $value;
}

/**
 * Format a monetary amount.
 */
function formatMoney(float|int $amount, string $currency, bool $separator = true, bool $translate = true): string
{
    if (in_array(strtoupper($currency), config('currencies.zero_decimals'))) {
        return number_format($amount, 0, $translate ? __('.') : '.', $separator ? ($translate ? __(',') : ',') : false);
    } else {
        return number_format($amount, 2, $translate ? __('.') : '.', $separator ? ($translate ? __(',') : ',') : false);
    }
}

/**
 * Convert a number into a shorter, readable format.
 */
function shortenNumber(int|float $number, ?int $precision = 1): string
{
    $suffix = ["", "K", "M", "B", "T"];
    for ($i = 0; $i < count($suffix); $i++) {
        $divide = $number / pow(1000, $i);
        if ($divide < 1000) {
            return round($divide, $precision) . $suffix[$i];
        }
    }

    return $number;
}

/**
 * Get the captcha field name.
 */
function captchaFieldName(): ?string
{
    $fields = [
        'turnstile' => 'cf-turnstile-response',
        'hcaptcha' => 'h-captcha-response',
        'recaptcha' => 'g-recaptcha-response'
    ];

    $driver = mb_strtolower(config('settings.captcha_driver'));

    return $fields[$driver] ?? null;
}

/**
 * Get the space colors.
 */
function spaceColors(): array
{
    return [
        1 => 'success',
        2 => 'danger',
        3 => 'warning',
        4 => 'info',
        5 => 'dark',
        6 => 'primary'
    ];
}

/**
 * Get the space color.
 */
function spaceColor(?string $category): ?string
{
    $categories = spaceColors();

    return $categories[$category] ?? null;
}

/**
 * Get the icon slug for a browser name.
 */
function browserIcon(?string $browser): string
{
    $browsers = [
        'Chrome' => 'chrome',
        'Chromium' => 'chromium',
        'Firefox' => 'firefox',
        'Firefox Mobile' => 'firefox',
        'Edge' => 'edge',
        'Internet Explorer' => 'ie',
        'Mobile Internet Explorer' => 'ie',
        'Vivaldi' => 'vivaldi',
        'Brave' => 'brave',
        'Safari' => 'safari',
        'Opera' => 'opera',
        'Opera Mini' => 'opera',
        'Opera Mobile' => 'opera',
        'Opera Touch' => 'operatouch',
        'Yandex Browser' => 'yandex',
        'UC Browser' => 'ucbrowser',
        'Samsung Internet' => 'samsung',
        'QQ Browser' => 'qq',
        'BlackBerry Browser' => 'bbbrowser',
        'Maxthon' => 'maxthon'
    ];

    return $browsers[$browser] ?? 'unknown';
}

/**
 * Get the icon slug for an operating system name.
 */
function operatingSystemIcon(?string $operatingSystem): string
{
    $operatingSystems = [
        'Windows' => 'windows',
        'Linux' => 'linux',
        'Ubuntu' => 'ubuntu',
        'Windows Phone' => 'windows',
        'iOS' => 'apple',
        'OS X' => 'apple',
        'FreeBSD' => 'freebsd',
        'Android' => 'android',
        'Chrome OS' => 'chromeos',
        'BlackBerry OS' => 'bbos',
        'Tizen' => 'tizen',
        'KaiOS' => 'kaios',
        'BlackBerry Tablet OS' => 'bbos',
        'Fedora' => 'fedora'
    ];

    return $operatingSystems[$operatingSystem] ?? 'unknown';
}

/**
 * Get the icon slug for a device name.
 */
function deviceIcon(?string $device): string
{
    $devices = [
        'desktop' => 'desktop',
        'mobile' => 'mobile',
        'tablet' => 'tablet',
        'television' => 'tv',
        'gaming' => 'gaming',
        'watch' => 'watch'
    ];

    return $devices[$device] ?? 'unknown';
}

/**
 * Get the icon slug for a country name.
 */
function flagIcon(?string $string): string
{
    [$countryCode] = explode(':', $string);

    return !empty($countryCode) ? strtolower($countryCode) : 'unknown';
}
