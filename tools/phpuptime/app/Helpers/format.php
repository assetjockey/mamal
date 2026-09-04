<?php

/**
 * Format the page titles.
 */
function formatTitle(array|string $value): string
{
    if (is_array($value)) {
        return implode(" - ", $value);
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
 * Get the icon slug for a country name.
 */
function flagIcon(?string $string): string
{
    [$countryCode] = explode(':', $string);

    return !empty($countryCode) ? strtolower($countryCode) : 'unknown';
}

/**
 * Get the monitor status color.
 */
function monitorStatusColor($status): ?string
{
    $statuses = [
        'pending' => 'secondary',
        'offline' => 'danger',
        'paused' => 'warning',
        'maintenance' => 'info',
        'online' => 'success',
    ];

    return $statuses[$status] ?? null;
}

/**
 * Get the monitor SSL status color.
 */
function monitorSslStatusColor(\App\Models\Monitor $monitor): string
{
    if ($monitor->ssl_created_at && $monitor->ssl_ends_at) {
        if ($monitor->ssl_created_at->isFuture() || $monitor->ssl_ends_at->isPast()) {
            return 'danger';
        } elseif (now()->isBetween($monitor->ssl_ends_at->subDays($monitor->ssl_alert_days), $monitor->ssl_ends_at)) {
            return 'warning';
        } else {
            return 'success';
        }
    }

    return 'secondary';
}

/**
 * Get the monitor domain name status color.
 */
function monitorDomainStatusColor(\App\Models\Monitor $monitor): string
{
    if ($monitor->domain_created_at && $monitor->domain_ends_at) {
        if ($monitor->domain_created_at->isFuture() || $monitor->domain_ends_at->isPast()) {
            return 'danger';
        } elseif (now()->isBetween($monitor->domain_ends_at->subDays($monitor->domain_alert_days), $monitor->domain_ends_at)) {
            return 'warning';
        } else {
            return 'success';
        }
    }

    return 'secondary';
}

/**
 * Add a space between numbers and time units.
 */
function addSpacingToTimeUnits(string $value): string
{
    return preg_replace('/(\d+)([a-z]+)/i', '$1 $2', $value);
}

/**
 * Calculate the uptime percentage, and truncate the result to a fixed number of decimals.
 */
function formatUptimePercentageNumber(float|int $totalTime, float|int $downtime, int $decimals, string $decimalsSeparator = ".", string $thousandsSeparator = ","): string
{
    // Calculate the uptime percentage
    $uptimePercentage = (($totalTime - $downtime) / $totalTime) * 100;

    // Calculate the factor to shift the decimal point
    $factor = pow(10, $decimals);

    return number_format((floor($uptimePercentage * $factor) / $factor), $decimals, $decimalsSeparator, $thousandsSeparator);
}